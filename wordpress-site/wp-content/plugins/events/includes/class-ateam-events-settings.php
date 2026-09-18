<?php
/**
 * Plugin Settings Page
 *
 * @package ATeam_Events_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ATeam_Events_Settings {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    /**
     * Add settings page under Events menu
     */
    public function add_settings_page() {
        add_submenu_page(
            'edit.php?post_type=ateam_event',
            __( 'Settings', 'ateam-events-pro' ),
            __( 'Settings', 'ateam-events-pro' ),
            'manage_options',
            'ateam-events-settings',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // General section
        add_settings_section(
            'ateam_events_general',
            __( 'General Settings', 'ateam-events-pro' ),
            array( $this, 'general_section_cb' ),
            'ateam-events-settings'
        );

        // Events per page
        register_setting( 'ateam_events_settings', 'ateam_events_events_per_page', array(
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 9,
        ) );
        add_settings_field(
            'ateam_events_events_per_page',
            __( 'Events Per Page', 'ateam-events-pro' ),
            array( $this, 'number_field_cb' ),
            'ateam-events-settings',
            'ateam_events_general',
            array( 'option' => 'ateam_events_events_per_page', 'default' => 9, 'min' => 1, 'max' => 50 )
        );

        // Grid Columns
        register_setting( 'ateam_events_settings', 'ateam_events_grid_columns', array(
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 3,
        ) );
        add_settings_field(
            'ateam_events_grid_columns',
            __( 'Default Grid Columns', 'ateam-events-pro' ),
            array( $this, 'number_field_cb' ),
            'ateam-events-settings',
            'ateam_events_general',
            array( 'option' => 'ateam_events_grid_columns', 'default' => 3, 'min' => 1, 'max' => 6 )
        );

        // Date format
        register_setting( 'ateam_events_settings', 'ateam_events_date_format', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'F j, Y',
        ) );
        add_settings_field(
            'ateam_events_date_format',
            __( 'Date Format', 'ateam-events-pro' ),
            array( $this, 'select_field_cb' ),
            'ateam-events-settings',
            'ateam_events_general',
            array(
                'option'  => 'ateam_events_date_format',
                'default' => 'F j, Y',
                'choices' => array(
                    'F j, Y'  => date_i18n( 'F j, Y' ),
                    'j F Y'   => date_i18n( 'j F Y' ),
                    'm/d/Y'   => date_i18n( 'm/d/Y' ),
                    'd/m/Y'   => date_i18n( 'd/m/Y' ),
                    'Y-m-d'   => date_i18n( 'Y-m-d' ),
                    'M j, Y'  => date_i18n( 'M j, Y' ),
                ),
            )
        );

        // Time format
        register_setting( 'ateam_events_settings', 'ateam_events_time_format', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'g:i A',
        ) );
        add_settings_field(
            'ateam_events_time_format',
            __( 'Time Format', 'ateam-events-pro' ),
            array( $this, 'select_field_cb' ),
            'ateam-events-settings',
            'ateam_events_general',
            array(
                'option'  => 'ateam_events_time_format',
                'default' => 'g:i A',
                'choices' => array(
                    'g:i A' => '1:30 PM',
                    'g:i a' => '1:30 pm',
                    'H:i'   => '13:30',
                ),
            )
        );

        // Appearance section
        add_settings_section(
            'ateam_events_appearance',
            __( 'Appearance', 'ateam-events-pro' ),
            array( $this, 'appearance_section_cb' ),
            'ateam-events-settings'
        );

        // Primary color
        register_setting( 'ateam_events_settings', 'ateam_events_primary_color', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default'           => '#6C63FF',
        ) );
        add_settings_field(
            'ateam_events_primary_color',
            __( 'Primary Color', 'ateam-events-pro' ),
            array( $this, 'color_field_cb' ),
            'ateam-events-settings',
            'ateam_events_appearance',
            array( 'option' => 'ateam_events_primary_color', 'default' => '#6C63FF' )
        );

        // Secondary color
        register_setting( 'ateam_events_settings', 'ateam_events_secondary_color', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default'           => '#FF6584',
        ) );
        add_settings_field(
            'ateam_events_secondary_color',
            __( 'Secondary Color', 'ateam-events-pro' ),
            array( $this, 'color_field_cb' ),
            'ateam-events-settings',
            'ateam_events_appearance',
            array( 'option' => 'ateam_events_secondary_color', 'default' => '#FF6584' )
        );

        // Default Event Image
        register_setting( 'ateam_events_settings', 'ateam_events_default_image', array(
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
        ) );
        add_settings_field(
            'ateam_events_default_image',
            __( 'Default Event Image', 'ateam-events-pro' ),
            array( $this, 'image_field_cb' ),
            'ateam-events-settings',
            'ateam_events_appearance',
            array( 'option' => 'ateam_events_default_image' )
        );


        // Google Calendar Section
        add_settings_section(
            'ateam_events_gcal',
            __( 'Google Calendar Sync', 'ateam-events-pro' ),
            array( $this, 'gcal_section_cb' ),
            'ateam-events-settings'
        );

        register_setting( 'ateam_events_settings', 'ateam_events_gcal_client_id', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ) );
        add_settings_field(
            'ateam_events_gcal_client_id',
            __( 'Google Client ID', 'ateam-events-pro' ),
            array( $this, 'text_field_cb' ),
            'ateam-events-settings',
            'ateam_events_gcal',
            array( 'option' => 'ateam_events_gcal_client_id' )
        );

        register_setting( 'ateam_events_settings', 'ateam_events_gcal_client_secret', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ) );
        add_settings_field(
            'ateam_events_gcal_client_secret',
            __( 'Google Client Secret', 'ateam-events-pro' ),
            array( $this, 'text_field_cb' ),
            'ateam-events-settings',
            'ateam_events_gcal',
            array( 'option' => 'ateam_events_gcal_client_secret' )
        );

        register_setting( 'ateam_events_settings', 'ateam_events_gcal_calendar_id', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'primary',
        ) );
        add_settings_field(
            'ateam_events_gcal_calendar_id',
            __( 'Google Calendar ID', 'ateam-events-pro' ),
            array( $this, 'text_field_cb' ),
            'ateam-events-settings',
            'ateam_events_gcal',
            array( 'option' => 'ateam_events_gcal_calendar_id', 'default' => 'primary' )
        );


        register_setting( 'ateam_events_settings', 'ateam_events_gcal_sync_enabled', array(
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 0,
        ) );
        add_settings_field(
            'ateam_events_gcal_sync_enabled',
            __( 'Enable Sync', 'ateam-events-pro' ),
            array( $this, 'checkbox_field_cb' ),
            'ateam-events-settings',
            'ateam_events_gcal',
            array( 'option' => 'ateam_events_gcal_sync_enabled' )
        );

    }

    /**
     * Callbacks
     */
    public function general_section_cb() {
        echo '<p>' . __( 'Configure how events are displayed on your site.', 'ateam-events-pro' ) . '</p>';
    }

    public function appearance_section_cb() {
        echo '<p>' . __( 'Customize the look and feel of your events.', 'ateam-events-pro' ) . '</p>';
    }

    public function gcal_section_cb() {
        echo '<p>' . __( 'Sync your events with Google Calendar. You will need to create a project in the Google Cloud Console and enable the Google Calendar API.', 'ateam-events-pro' ) . '</p>';
        
        $token = get_option( 'ateam_events_gcal_token' );
        if ( ! empty( $token ) ) {
            echo '<div class="notice notice-success inline"><p>' . __( 'Connected to Google Calendar.', 'ateam-events-pro' ) . ' <a href="' . admin_url( 'admin-post.php?action=ateam_events_gcal_disconnect' ) . '" class="button button-secondary button-small">' . __( 'Disconnect', 'ateam-events-pro' ) . '</a></p></div>';
            
            if ( get_option( 'ateam_events_gcal_sync_enabled' ) ) {
                echo '<p><a href="' . admin_url( 'admin-post.php?action=ateam_events_gcal_manual_sync' ) . '" class="button button-secondary">' . __( 'Sync Now (Google → WordPress)', 'ateam-events-pro' ) . '</a>';
                $last_sync = get_option( 'ateam_events_gcal_last_sync' );
                if ( $last_sync ) {
                    echo ' <span class="description" style="margin-left:10px;">' . sprintf( __( 'Last synced: %s', 'ateam-events-pro' ), date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_sync ) ) . '</span>';
                }
                echo '</p>';
                echo '<p><a href="' . admin_url( 'admin-post.php?action=ateam_events_clear_all' ) . '" class="button button-link-delete" onclick="return confirm(\'Are you sure you want to delete ALL synced events? This cannot be undone.\');">' . __( 'Clear All Synced Events', 'ateam-events-pro' ) . '</a></p>';
            }

            // Debug Log
            $logs = get_option( 'ateam_events_gcal_debug_log', array() );
            if ( ! empty( $logs ) ) {
                echo '<div style="margin-top: 20px;">';
                echo '<h3>' . __( 'Sync Log (Last 100 entries)', 'ateam-events-pro' ) . '</h3>';
                echo '<div style="background: #f0f0f1; border: 1px solid #ccd0d4; padding: 10px; height: 200px; overflow-y: scroll; font-family: monospace; font-size: 12px; line-height: 1.4;">';
                foreach ( array_reverse( $logs ) as $log ) {
                    echo esc_html( $log ) . '<br>';
                }
                echo '</div>';
                echo '<p><a href="' . admin_url( 'admin-post.php?action=ateam_events_gcal_clear_log' ) . '" class="button button-link-delete">' . __( 'Clear Log', 'ateam-events-pro' ) . '</a></p>';
                echo '</div>';
            }
        } else {
            $client_id = get_option( 'ateam_events_gcal_client_id' );
            $client_secret = get_option( 'ateam_events_gcal_client_secret' );
            if ( ! empty( $client_id ) && ! empty( $client_secret ) ) {
                echo '<p><a href="' . admin_url( 'admin-post.php?action=ateam_events_gcal_auth' ) . '" class="button button-primary">' . __( 'Connect to Google Calendar', 'ateam-events-pro' ) . '</a></p>';
            } else {
                echo '<p class="description">' . __( 'Please enter your Client ID and Client Secret first to connect.', 'ateam-events-pro' ) . '</p>';
            }
        }
    }

    public function text_field_cb( $args ) {
        $value = get_option( $args['option'] );
        printf(
            '<input type="text" name="%s" value="%s" class="regular-text" />',
            esc_attr( $args['option'] ),
            esc_attr( $value )
        );
    }

    public function checkbox_field_cb( $args ) {
        $value = get_option( $args['option'], 0 );
        printf(
            '<input type="checkbox" name="%s" value="1" %s />',
            esc_attr( $args['option'] ),
            checked( $value, 1, false )
        );
    }

    public function number_field_cb( $args ) {
        $value = get_option( $args['option'], $args['default'] );
        printf(
            '<input type="number" name="%s" value="%s" min="%d" max="%d" class="small-text" />',
            esc_attr( $args['option'] ),
            esc_attr( $value ),
            $args['min'],
            $args['max']
        );
    }

    public function select_field_cb( $args ) {
        $value = get_option( $args['option'], $args['default'] );
        echo '<select name="' . esc_attr( $args['option'] ) . '">';
        foreach ( $args['choices'] as $key => $label ) {
            printf(
                '<option value="%s" %s>%s</option>',
                esc_attr( $key ),
                selected( $value, $key, false ),
                esc_html( $label )
            );
        }
        echo '</select>';
    }

    public function color_field_cb( $args ) {
        $value = get_option( $args['option'], $args['default'] );
        printf(
            '<input type="text" name="%s" value="%s" class="ateam-color-picker" data-default-color="%s" />',
            esc_attr( $args['option'] ),
            esc_attr( $value ),
            esc_attr( $args['default'] )
        );
    }

    public function image_field_cb( $args ) {
        $image_id = get_option( $args['option'] );
        $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
        ?>
        <div class="ateam-image-selector-wrap">
            <div class="ateam-image-preview" style="margin-bottom: 10px; <?php echo $image_url ? '' : 'display:none;'; ?>">
                <img src="<?php echo esc_url( $image_url ); ?>" style="max-width: 150px; height: auto; border: 1px solid #ccd0d4; border-radius: 4px;" />
            </div>
            <input type="hidden" name="<?php echo esc_attr( $args['option'] ); ?>" value="<?php echo esc_attr( $image_id ); ?>" class="ateam-image-id" />
            <button type="button" class="button ateam-select-image"><?php _e( 'Select Image', 'ateam-events-pro' ); ?></button>
            <button type="button" class="button ateam-remove-image" style="<?php echo $image_url ? '' : 'display:none;'; ?>"><?php _e( 'Remove', 'ateam-events-pro' ); ?></button>
        </div>
        <?php
    }


    /**
     * Render the settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap ateam-settings-wrap">
            <div class="ateam-settings-header">
                <div class="ateam-settings-brand">
                    <h1>
                        <span class="ateam-logo-icon">⚡</span>
                        <?php _e( 'ATEAM EVENTS PRO', 'ateam-events-pro' ); ?>
                        <span class="ateam-version-badge">v<?php echo ATEAM_EVENTS_PRO_VERSION; ?></span>
                    </h1>
                    <p class="ateam-settings-desc"><?php _e( 'Configure your events plugin settings below.', 'ateam-events-pro' ); ?></p>
                </div>
            </div>

            <div class="ateam-settings-body">
                <?php
                if ( isset( $_GET['status'] ) ) {
                    $status = sanitize_text_field( $_GET['status'] );
                    $message = '';
                    $type = 'success';

                    switch ( $status ) {
                        case 'connected':
                            $message = __( 'Successfully connected to Google Calendar!', 'ateam-events-pro' );
                            break;
                        case 'synced':
                            $message = __( 'Events synchronized successfully.', 'ateam-events-pro' );
                            break;
                        case 'disconnected':
                            $message = __( 'Disconnected from Google Calendar.', 'ateam-events-pro' );
                            $type = 'info';
                            break;
                        case 'cleared':
                            $message = __( 'All synced events have been removed.', 'ateam-events-pro' );
                            $type = 'info';
                            break;
                    }


                    if ( $message ) {
                        echo '<div class="notice notice-' . $type . ' is-dismissible"><p>' . $message . '</p></div>';
                    }
                }
                ?>
                <form method="post" action="options.php">
                    <?php
                    settings_fields( 'ateam_events_settings' );
                    do_settings_sections( 'ateam-events-settings' );
                    ?>

                    <div class="ateam-settings-section">
                        <h2><?php _e( 'Shortcodes Reference', 'ateam-events-pro' ); ?></h2>
                        <table class="ateam-shortcodes-table widefat">
                            <thead>
                                <tr>
                                    <th><?php _e( 'Shortcode', 'ateam-events-pro' ); ?></th>
                                    <th><?php _e( 'Description', 'ateam-events-pro' ); ?></th>
                                    <th><?php _e( 'Parameters', 'ateam-events-pro' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>[ateam_events_hero_slider]</code></td>
                                    <td><?php _e( 'Premium full-width curved hero slider', 'ateam-events-pro' ); ?></td>
                                    <td><code>count, category</code></td>
                                </tr>
                                <tr>
                                    <td><code>[ateam_events]</code></td>
                                    <td><?php _e( 'Display events in a responsive grid', 'ateam-events-pro' ); ?></td>
                                    <td><code>count, category, columns, show_past, order</code></td>
                                </tr>
                                <tr>
                                    <td><code>[ateam_events_list]</code></td>
                                    <td><?php _e( 'Display events in a compact list format', 'ateam-events-pro' ); ?></td>
                                    <td><code>count, category, show_past</code></td>
                                </tr>
                                <tr>
                                    <td><code>[ateam_events_calendar]</code></td>
                                    <td><?php _e( 'Display a monthly calendar with events', 'ateam-events-pro' ); ?></td>
                                    <td><code>month, year</code></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <?php submit_button( __( 'Save Settings', 'ateam-events-pro' ) ); ?>
                </form>
            </div>
        </div>
        <?php
    }
}
