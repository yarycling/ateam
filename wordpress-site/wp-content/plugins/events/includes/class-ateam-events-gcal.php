<?php
/**
 * Google Calendar Integration
 *
 * @package ATeam_Events_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ATeam_Events_GCal {

    private static $instance = null;
    private $is_syncing = false;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Internal Log for Debugging
     */
    private function log( $message ) {
        error_log( 'ATEAM EVENTS: ' . $message );
        $logs = get_option( 'ateam_events_gcal_debug_log', array() );
        $logs[] = '[' . date( 'Y-m-d H:i:s' ) . '] ' . $message;
        // Keep only last 100 lines
        if ( count( $logs ) > 100 ) {
            array_shift( $logs );
        }
        update_option( 'ateam_events_gcal_debug_log', $logs );
    }

    private function __construct() {
        // OAuth Handlers
        add_action( 'admin_post_ateam_events_gcal_auth', array( $this, 'handle_auth_redirect' ) );
        add_action( 'admin_post_ateam_events_gcal_callback', array( $this, 'handle_auth_callback' ) );
        add_action( 'admin_post_ateam_events_gcal_disconnect', array( $this, 'handle_disconnect' ) );
        add_action( 'admin_post_ateam_events_gcal_manual_sync', array( $this, 'handle_manual_sync' ) );
        add_action( 'admin_post_ateam_events_gcal_clear_log', array( $this, 'handle_clear_log' ) );
        add_action( 'admin_post_ateam_events_clear_all', array( $this, 'handle_clear_all_events' ) );




        // Sync WP -> GCal
        add_action( 'save_post_ateam_event', array( $this, 'sync_to_gcal' ), 20, 2 );
        add_action( 'wp_trash_post', array( $this, 'delete_from_gcal' ) );

        // Cron Sync GCal -> WP
        add_action( 'ateam_events_gcal_sync_cron', array( $this, 'sync_from_gcal' ) );
        
        if ( ! wp_next_scheduled( 'ateam_events_gcal_sync_cron' ) ) {
            wp_schedule_event( time(), 'hourly', 'ateam_events_gcal_sync_cron' );
        }
    }

    /**
     * Get Google API Access Token
     */
    private function get_access_token() {
        $token = get_option( 'ateam_events_gcal_token' );
        if ( empty( $token ) ) {
            return false;
        }

        // Check if expired
        if ( time() >= $token['expires_at'] ) {
            return $this->refresh_access_token( $token['refresh_token'] );
        }

        return $token['access_token'];
    }

    /**
     * Refresh Google API Access Token
     */
    private function refresh_access_token( $refresh_token ) {
        $client_id     = get_option( 'ateam_events_gcal_client_id' );
        $client_secret = get_option( 'ateam_events_gcal_client_secret' );

        if ( empty( $client_id ) || empty( $client_secret ) ) {
            return false;
        }

        $response = wp_remote_post( 'https://oauth2.googleapis.com/token', array(
            'body' => array(
                'client_id'     => $client_id,
                'client_secret' => $client_secret,
                'refresh_token' => $refresh_token,
                'grant_type'    => 'refresh_token',
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $data['access_token'] ) ) {
            $token = get_option( 'ateam_events_gcal_token' );
            $token['access_token'] = $data['access_token'];
            $token['expires_at']   = time() + $data['expires_in'] - 60;
            update_option( 'ateam_events_gcal_token', $token );
            return $data['access_token'];
        }

        return false;
    }

    /**
     * Handle Redirect to Google Auth
     */
    public function handle_auth_redirect() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }

        $client_id = get_option( 'ateam_events_gcal_client_id' );
        if ( empty( $client_id ) ) {
            wp_redirect( admin_url( 'edit.php?post_type=ateam_event&page=ateam-events-settings&error=missing_id' ) );
            exit;
        }

        $redirect_uri = admin_url( 'admin-post.php?action=ateam_events_gcal_callback' );
        $scope        = 'https://www.googleapis.com/auth/calendar';
        
        $auth_url = add_query_arg( array(
            'client_id'       => $client_id,
            'redirect_uri'    => $redirect_uri,
            'response_type'   => 'code',
            'scope'           => $scope,
            'access_type'     => 'offline',
            'prompt'          => 'consent',
            'state'           => wp_create_nonce( 'ateam_gcal_auth' ),
        ), 'https://accounts.google.com/o/oauth2/v2/auth' );

        wp_redirect( $auth_url );
        exit;
    }

    /**
     * Handle OAuth Callback
     */
    public function handle_auth_callback() {
        if ( ! isset( $_GET['code'] ) || ! isset( $_GET['state'] ) || ! wp_verify_nonce( $_GET['state'], 'ateam_gcal_auth' ) ) {
            wp_die( 'Invalid request' );
        }

        $client_id     = get_option( 'ateam_events_gcal_client_id' );
        $client_secret = get_option( 'ateam_events_gcal_client_secret' );
        $redirect_uri  = admin_url( 'admin-post.php?action=ateam_events_gcal_callback' );

        $response = wp_remote_post( 'https://oauth2.googleapis.com/token', array(
            'body' => array(
                'code'          => $_GET['code'],
                'client_id'     => $client_id,
                'client_secret' => $client_secret,
                'redirect_uri'  => $redirect_uri,
                'grant_type'    => 'authorization_code',
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            wp_die( 'Error connecting to Google API' );
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $data['access_token'] ) ) {
            $token = array(
                'access_token'  => $data['access_token'],
                'refresh_token' => isset( $data['refresh_token'] ) ? $data['refresh_token'] : '',
                'expires_at'    => time() + $data['expires_in'] - 60,
            );
            update_option( 'ateam_events_gcal_token', $token );
            wp_redirect( admin_url( 'edit.php?post_type=ateam_event&page=ateam-events-settings&status=connected' ) );
        } else {
            wp_die( 'Failed to retrieve access token' );
        }
        exit;
    }

    /**
     * Disconnect Google Calendar
     */
    public function handle_disconnect() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }
        delete_option( 'ateam_events_gcal_token' );
        wp_redirect( admin_url( 'edit.php?post_type=ateam_event&page=ateam-events-settings&status=disconnected' ) );
        exit;
    }

    /**
     * Handle Manual Sync Action
     */
    public function handle_manual_sync() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }

        // Force sync regardless of last sync time
        delete_option( 'ateam_events_gcal_last_sync' );
        $this->sync_from_gcal();

        wp_redirect( admin_url( 'edit.php?post_type=ateam_event&page=ateam-events-settings&status=synced' ) );
        exit;
    }

    /**
     * Handle Clear Log Action
     */
    public function handle_clear_log() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }
        delete_option( 'ateam_events_gcal_debug_log' );
        wp_redirect( admin_url( 'edit.php?post_type=ateam_event&page=ateam-events-settings' ) );
        exit;
    }

    /**
     * Handle Clear All Events Action
     */
    public function handle_clear_all_events() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }

        $events = new WP_Query( array(
            'post_type'      => 'ateam_event',
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'meta_query'     => array(
                array(
                    'key'     => '_ateam_event_gcal_id',
                    'compare' => 'EXISTS',
                ),
            ),
        ) );

        if ( $events->have_posts() ) {
            foreach ( $events->posts as $p ) {
                wp_delete_post( $p->ID, true );
            }
        }

        // Also reset sync timestamp
        delete_option( 'ateam_events_gcal_last_sync' );

        wp_redirect( admin_url( 'edit.php?post_type=ateam_event&page=ateam-events-settings&status=cleared' ) );
        exit;
    }



    /**
     * Sync WordPress Event to Google Calendar
     */
    public function sync_to_gcal( $post_id, $post ) {
        if ( $this->is_syncing || get_post_status( $post_id ) !== 'publish' ) {
            return;
        }

        if ( ! get_option( 'ateam_events_gcal_sync_enabled' ) ) {
            return;
        }

        $access_token = $this->get_access_token();
        if ( ! $access_token ) {
            return;
        }

        $gcal_id = get_post_meta( $post_id, '_ateam_event_gcal_id', true );
        
        $start_date = get_post_meta( $post_id, '_ateam_event_date', true );
        $end_date   = get_post_meta( $post_id, '_ateam_event_end_date', true );
        $start_time = get_post_meta( $post_id, '_ateam_event_start_time', true );
        $end_time   = get_post_meta( $post_id, '_ateam_event_end_time', true );
        $is_all_day = get_post_meta( $post_id, '_ateam_event_all_day', true );
        
        if ( empty( $start_date ) ) return;

        $event_data = array(
            'summary'     => $post->post_title,
            'description' => $post->post_content,
            'location'    => get_post_meta( $post_id, '_ateam_event_venue', true ) . ' ' . get_post_meta( $post_id, '_ateam_event_address', true ),
        );

        if ( $is_all_day ) {
            $event_data['start'] = array( 'date' => $start_date );
            $event_data['end']   = array( 'date' => !empty($end_date) ? date('Y-m-d', strtotime($end_date . ' +1 day')) : date('Y-m-d', strtotime($start_date . ' +1 day')) );
        } else {
            $event_data['start'] = array( 'dateTime' => $start_date . 'T' . ($start_time ?: '00:00') . ':00', 'timeZone' => get_option('timezone_string') ?: 'UTC' );
            $event_data['end']   = array( 'dateTime' => ($end_date ?: $start_date) . 'T' . ($end_time ?: '23:59') . ':00', 'timeZone' => get_option('timezone_string') ?: 'UTC' );
        }

        $url = 'https://www.googleapis.com/calendar/v3/calendars/primary/events';
        $method = 'POST';

        if ( ! empty( $gcal_id ) ) {
            $url .= '/' . $gcal_id;
            $method = 'PUT';
        }

        $response = wp_remote_request( $url, array(
            'method'  => $method,
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json',
            ),
            'body' => json_encode( $event_data ),
        ) );

        if ( ! is_wp_error( $response ) ) {
            $data = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( isset( $data['id'] ) ) {
                update_post_meta( $post_id, '_ateam_event_gcal_id', $data['id'] );
                if ( isset( $data['hangoutLink'] ) ) {
                    update_post_meta( $post_id, '_ateam_event_gcal_meet_link', $data['hangoutLink'] );
                }
            }
        }
    }

    /**
     * Delete from Google Calendar when trashed in WP
     */
    public function delete_from_gcal( $post_id ) {
        $gcal_id = get_post_meta( $post_id, '_ateam_event_gcal_id', true );
        if ( empty( $gcal_id ) ) {
            return;
        }

        $access_token = $this->get_access_token();
        if ( ! $access_token ) {
            return;
        }

        wp_remote_request( 'https://www.googleapis.com/calendar/v3/calendars/primary/events/' . $gcal_id, array(
            'method'  => 'DELETE',
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
            ),
        ) );
    }

    /**
     * Sync from Google Calendar to WordPress
     */
    public function sync_from_gcal() {
        $this->log( 'Starting sync from Google Calendar...' );
        
        if ( ! get_option( 'ateam_events_gcal_sync_enabled' ) ) {
            $this->log( 'Sync is not enabled in settings.' );
            return;
        }

        $access_token = $this->get_access_token();
        if ( ! $access_token ) {
            $this->log( 'Failed to get access token. Check if connected.' );
            return;
        }



        $this->is_syncing = true;

        $last_sync = get_option( 'ateam_events_gcal_last_sync' );
        $calendar_id = get_option( 'ateam_events_gcal_calendar_id', 'primary' );
        $url = 'https://www.googleapis.com/calendar/v3/calendars/' . urlencode( $calendar_id ) . '/events';
        $args = array(
            'singleEvents' => 'false', // Stop expanding recurring events
            'timeMin'      => date( 'Y-m-d\TH:i:s\Z', strtotime( 'today' ) ),
            'maxResults'   => 100,
        );



        // If not a manual sync (which clears last_sync), we could use updatedMin
        // but for now let's just fetch all upcoming to be safe and ensure they are updated.

        $url = add_query_arg( $args, $url );
        $this->log( 'Requesting URL: ' . $url );

        $response = wp_remote_get( $url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            $this->log( 'API Request Error: ' . $response->get_error_message() );
            $this->is_syncing = false;
            return;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        $this->log( 'API Response Code: ' . $code );

        if ( $code !== 200 ) {
            $this->log( 'API Error Response: ' . substr( $body, 0, 500 ) );
            $this->is_syncing = false;
            return;
        }

        $data = json_decode( $body, true );

        if ( isset( $data['items'] ) ) {
            $this->log( 'Found ' . count( $data['items'] ) . ' items in Google Calendar.' );
            foreach ( $data['items'] as $item ) {


                if ( $item['status'] === 'cancelled' ) {
                    // Find and delete
                    $existing = new WP_Query( array(
                        'post_type'  => 'ateam_event',
                        'meta_key'   => '_ateam_event_gcal_id',
                        'meta_value' => $item['id'],
                        'posts_per_page' => 1,
                    ) );
                    if ( $existing->have_posts() ) {
                        wp_trash_post( $existing->posts[0]->ID );
                    }
                    continue;
                }

                $this->update_wp_event_from_gcal( $item );
            }
            update_option( 'ateam_events_gcal_last_sync', time() );
        }

        $this->is_syncing = false;
    }

    /**
     * Helper to update/create WP event from GCal item
     */
    private function update_wp_event_from_gcal( $item ) {
        $this->log( 'Processing GCal item: ' . ( isset( $item['summary'] ) ? $item['summary'] : 'Untitled' ) );
        
        $existing = new WP_Query( array(


            'post_type'  => 'ateam_event',
            'meta_key'   => '_ateam_event_gcal_id',
            'meta_value' => $item['id'],
            'posts_per_page' => 1,
            'post_status'    => array( 'publish', 'pending', 'draft', 'future' ),
        ) );

        $post_id = $existing->have_posts() ? $existing->posts[0]->ID : 0;

        $post_data = array(
            'post_title'   => isset( $item['summary'] ) ? $item['summary'] : '(No Title)',
            'post_content' => isset( $item['description'] ) ? $item['description'] : '',
            'post_type'    => 'ateam_event',
            'post_status'  => 'publish',
        );

        if ( $post_id ) {
            $post_data['ID'] = $post_id;
            wp_update_post( $post_data );
        } else {
            $post_id = wp_insert_post( $post_data );
            update_post_meta( $post_id, '_ateam_event_gcal_id', $item['id'] );
        }

        // Meta data
        $start_raw = isset( $item['start']['dateTime'] ) ? $item['start']['dateTime'] : $item['start']['date'];
        $end_raw   = isset( $item['end']['dateTime'] ) ? $item['end']['dateTime'] : $item['end']['date'];
        
        $is_all_day = ! isset( $item['start']['dateTime'] );
        
        $start_ts = strtotime( $start_raw );
        $end_ts   = strtotime( $end_raw );

        update_post_meta( $post_id, '_ateam_event_date', date( 'Y-m-d', $start_ts ) );
        update_post_meta( $post_id, '_ateam_event_all_day', $is_all_day ? '1' : '0' );
        
        if ( ! $is_all_day ) {
            $this->log( 'Timed Event detected. Start: ' . date( 'H:i', $start_ts ) );
            update_post_meta( $post_id, '_ateam_event_start_time', date( 'H:i', $start_ts ) );
            update_post_meta( $post_id, '_ateam_event_end_time', date( 'H:i', $end_ts ) );
        } else {
            $this->log( 'All-day Event detected.' );
        }


        if ( isset( $item['location'] ) ) {
            update_post_meta( $post_id, '_ateam_event_venue', $item['location'] );
        }

        if ( isset( $item['hangoutLink'] ) ) {
            update_post_meta( $post_id, '_ateam_event_gcal_meet_link', $item['hangoutLink'] );
        }
    }
}
