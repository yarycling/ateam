<?php
/**
 * Custom Post Type Registration
 *
 * @package ATeam_Events_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ATeam_Events_CPT {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', array( $this, 'register_post_type' ) );
        add_action( 'init', array( $this, 'register_taxonomy' ) );
        add_filter( 'manage_ateam_event_posts_columns', array( $this, 'custom_columns' ) );
        add_action( 'manage_ateam_event_posts_custom_column', array( $this, 'custom_column_content' ), 10, 2 );
        add_filter( 'manage_edit-ateam_event_sortable_columns', array( $this, 'sortable_columns' ) );
        add_action( 'pre_get_posts', array( $this, 'sort_by_event_date' ) );
    }

    /**
     * Register the Event custom post type
     */
    public function register_post_type() {
        $labels = array(
            'name'                  => _x( 'Events', 'Post type general name', 'ateam-events-pro' ),
            'singular_name'         => _x( 'Event', 'Post type singular name', 'ateam-events-pro' ),
            'menu_name'             => _x( 'ATeam Events', 'Admin Menu text', 'ateam-events-pro' ),
            'name_admin_bar'        => _x( 'Event', 'Add New on Toolbar', 'ateam-events-pro' ),
            'add_new'               => __( 'Add New Event', 'ateam-events-pro' ),
            'add_new_item'          => __( 'Add New Event', 'ateam-events-pro' ),
            'new_item'              => __( 'New Event', 'ateam-events-pro' ),
            'edit_item'             => __( 'Edit Event', 'ateam-events-pro' ),
            'view_item'             => __( 'View Event', 'ateam-events-pro' ),
            'all_items'             => __( 'All Events', 'ateam-events-pro' ),
            'search_items'          => __( 'Search Events', 'ateam-events-pro' ),
            'parent_item_colon'     => __( 'Parent Events:', 'ateam-events-pro' ),
            'not_found'             => __( 'No events found.', 'ateam-events-pro' ),
            'not_found_in_trash'    => __( 'No events found in Trash.', 'ateam-events-pro' ),
            'featured_image'        => _x( 'Event Cover Image', 'Overrides the "Featured Image" phrase', 'ateam-events-pro' ),
            'set_featured_image'    => _x( 'Set event cover image', 'Overrides the "Set featured image" phrase', 'ateam-events-pro' ),
            'remove_featured_image' => _x( 'Remove event cover image', 'Overrides the "Remove featured image" phrase', 'ateam-events-pro' ),
            'use_featured_image'    => _x( 'Use as event cover image', 'Overrides the "Use as featured image" phrase', 'ateam-events-pro' ),
            'archives'              => _x( 'Event Archives', 'The post type archive label used in nav menus', 'ateam-events-pro' ),
            'filter_items_list'     => _x( 'Filter events list', 'Screen reader text', 'ateam-events-pro' ),
            'items_list_navigation' => _x( 'Events list navigation', 'Screen reader text', 'ateam-events-pro' ),
            'items_list'            => _x( 'Events list', 'Screen reader text', 'ateam-events-pro' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'events', 'with_front' => false ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-calendar-alt',
            'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
            'show_in_rest'       => true,
        );

        register_post_type( 'ateam_event', $args );
    }

    /**
     * Register Event Category taxonomy
     */
    public function register_taxonomy() {
        $labels = array(
            'name'              => _x( 'Event Categories', 'taxonomy general name', 'ateam-events-pro' ),
            'singular_name'     => _x( 'Event Category', 'taxonomy singular name', 'ateam-events-pro' ),
            'search_items'      => __( 'Search Categories', 'ateam-events-pro' ),
            'all_items'         => __( 'All Categories', 'ateam-events-pro' ),
            'parent_item'       => __( 'Parent Category', 'ateam-events-pro' ),
            'parent_item_colon' => __( 'Parent Category:', 'ateam-events-pro' ),
            'edit_item'         => __( 'Edit Category', 'ateam-events-pro' ),
            'update_item'       => __( 'Update Category', 'ateam-events-pro' ),
            'add_new_item'      => __( 'Add New Category', 'ateam-events-pro' ),
            'new_item_name'     => __( 'New Category Name', 'ateam-events-pro' ),
            'menu_name'         => __( 'Categories', 'ateam-events-pro' ),
        );

        register_taxonomy( 'event_category', array( 'ateam_event' ), array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'event-category' ),
            'show_in_rest'      => true,
        ) );
    }

    /**
     * Custom admin columns
     */
    public function custom_columns( $columns ) {
        $new_columns = array();
        $new_columns['cb']             = $columns['cb'];
        $new_columns['ateam_thumb']    = __( 'Image', 'ateam-events-pro' );
        $new_columns['title']          = $columns['title'];
        $new_columns['ateam_date']     = __( 'Event Date', 'ateam-events-pro' );
        $new_columns['ateam_time']     = __( 'Event Time', 'ateam-events-pro' );
        $new_columns['ateam_location'] = __( 'Location', 'ateam-events-pro' );
        $new_columns['ateam_status']   = __( 'Status', 'ateam-events-pro' );

        if ( isset( $columns['taxonomy-event_category'] ) ) {
            $new_columns['taxonomy-event_category'] = $columns['taxonomy-event_category'];
        }

        $new_columns['date'] = $columns['date'];

        return $new_columns;
    }

    /**
     * Custom column content
     */
    public function custom_column_content( $column, $post_id ) {
        switch ( $column ) {
            case 'ateam_thumb':
                if ( has_post_thumbnail( $post_id ) ) {
                    echo get_the_post_thumbnail( $post_id, array( 60, 60 ), array( 'style' => 'border-radius:8px;object-fit:cover;' ) );
                } else {
                    echo '<span class="dashicons dashicons-format-image" style="font-size:40px;width:60px;height:60px;line-height:60px;color:#ccc;"></span>';
                }
                break;

            case 'ateam_date':
                $event_date = get_post_meta( $post_id, '_ateam_event_date', true );
                if ( $event_date ) {
                    $date_format = get_option( 'ateam_events_date_format', 'F j, Y' );
                    echo '<strong>' . date_i18n( $date_format, strtotime( $event_date ) ) . '</strong>';
                } else {
                    echo '<span style="color:#999;">—</span>';
                }
                break;

            case 'ateam_time':
                $start_time = get_post_meta( $post_id, '_ateam_event_start_time', true );
                $end_time   = get_post_meta( $post_id, '_ateam_event_end_time', true );
                if ( $start_time ) {
                    $time_format = get_option( 'ateam_events_time_format', 'g:i A' );
                    echo date_i18n( $time_format, strtotime( $start_time ) );
                    if ( $end_time ) {
                        echo ' – ' . date_i18n( $time_format, strtotime( $end_time ) );
                    }
                } else {
                    echo '<span style="color:#999;">—</span>';
                }
                break;

            case 'ateam_location':
                $venue = get_post_meta( $post_id, '_ateam_event_venue', true );
                $addr  = get_post_meta( $post_id, '_ateam_event_address', true );
                if ( $venue ) {
                    echo '<strong>' . esc_html( $venue ) . '</strong>';
                    if ( $addr ) {
                        echo '<br><small style="color:#666;">' . esc_html( $addr ) . '</small>';
                    }
                } else {
                    echo '<span style="color:#999;">—</span>';
                }
                break;

            case 'ateam_status':
                $event_date = get_post_meta( $post_id, '_ateam_event_date', true );
                if ( $event_date ) {
                    $today = current_time( 'Y-m-d' );
                    if ( $event_date >= $today ) {
                        echo '<span style="background:#E8F5E9;color:#2E7D32;padding:4px 10px;border-radius:12px;font-size:12px;font-weight:600;">Upcoming</span>';
                    } else {
                        echo '<span style="background:#FBE9E7;color:#D84315;padding:4px 10px;border-radius:12px;font-size:12px;font-weight:600;">Past</span>';
                    }
                }
                break;
        }
    }

    /**
     * Make event date column sortable
     */
    public function sortable_columns( $columns ) {
        $columns['ateam_date'] = 'ateam_event_date';
        return $columns;
    }

    /**
     * Handle sorting by event date
     */
    public function sort_by_event_date( $query ) {
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }

        if ( 'ateam_event_date' === $query->get( 'orderby' ) ) {
            $query->set( 'meta_key', '_ateam_event_date' );
            $query->set( 'orderby', 'meta_value' );
        }
    }
}
