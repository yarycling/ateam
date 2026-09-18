<?php
/**
 * Plugin Name: ATEAM EVENTS PRO
 * Plugin URI: https://ateamdigital.com/plugins/events-pro
 * Description: A professional events management plugin for WordPress. Create and showcase events with locations, dates, times, and featured images.
 * Version: 1.0.0
 * Author: ATeam Digital
 * Author URI: https://ateamdigital.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ateam-events-pro
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants
define( 'ATEAM_EVENTS_PRO_VERSION', '1.0.0' );
define( 'ATEAM_EVENTS_PRO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ATEAM_EVENTS_PRO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ATEAM_EVENTS_PRO_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin class
 */
final class ATeam_Events_Pro {

    /**
     * Single instance
     */
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Load required files
     */
    private function load_dependencies() {
        require_once ATEAM_EVENTS_PRO_PLUGIN_DIR . 'includes/class-ateam-events-cpt.php';
        require_once ATEAM_EVENTS_PRO_PLUGIN_DIR . 'includes/class-ateam-events-meta.php';
        require_once ATEAM_EVENTS_PRO_PLUGIN_DIR . 'includes/class-ateam-events-shortcode.php';
        require_once ATEAM_EVENTS_PRO_PLUGIN_DIR . 'includes/class-ateam-events-widget.php';
        require_once ATEAM_EVENTS_PRO_PLUGIN_DIR . 'includes/class-ateam-events-settings.php';
        require_once ATEAM_EVENTS_PRO_PLUGIN_DIR . 'includes/class-ateam-events-gcal.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Activation / Deactivation
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        // Init
        add_action( 'init', array( $this, 'load_textdomain' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );

        // Admin menu
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

        // Plugin action links
        add_filter( 'plugin_action_links_' . ATEAM_EVENTS_PRO_BASENAME, array( $this, 'plugin_action_links' ) );

        // Initialize components
        ATeam_Events_CPT::get_instance();
        ATeam_Events_Meta::get_instance();
        ATeam_Events_Shortcode::get_instance();
        ATeam_Events_Widget::get_instance();
        ATeam_Events_Settings::get_instance();
        ATeam_Events_GCal::get_instance();
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Register CPT so rewrite rules can be flushed
        ATeam_Events_CPT::get_instance()->register_post_type();
        flush_rewrite_rules();

        // Set default options
        $defaults = array(
            'events_per_page'   => 9,
            'date_format'       => 'F j, Y',
            'time_format'       => 'g:i A',
            'primary_color'     => '#6C63FF',
            'secondary_color'   => '#FF6584',
            'enable_archive'    => 1,
            'map_provider'      => 'none',
        );

        foreach ( $defaults as $key => $value ) {
            if ( false === get_option( 'ateam_events_' . $key ) ) {
                update_option( 'ateam_events_' . $key, $value );
            }
        }

        // Store version
        update_option( 'ateam_events_pro_version', ATEAM_EVENTS_PRO_VERSION );
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }

    /**
     * Load text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'ateam-events-pro',
            false,
            dirname( ATEAM_EVENTS_PRO_BASENAME ) . '/languages'
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets( $hook ) {
        $screen = get_current_screen();

        // Only load on our plugin pages and post type
        $is_events_screen = (
            ( isset( $screen->post_type ) && 'ateam_event' === $screen->post_type ) ||
            ( strpos( $hook, 'ateam-events' ) !== false )
        );

        if ( ! $is_events_screen ) {
            return;
        }

        // Google Fonts
        wp_enqueue_style(
            'ateam-events-google-fonts',
            'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap',
            array(),
            null
        );

        // Admin CSS
        wp_enqueue_style(
            'ateam-events-admin',
            ATEAM_EVENTS_PRO_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            ATEAM_EVENTS_PRO_VERSION
        );

        // WordPress media uploader
        wp_enqueue_media();

        // Date/time picker
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_style( 'jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css', array(), '1.13.2' );

        // Admin JS
        wp_enqueue_script(
            'ateam-events-admin',
            ATEAM_EVENTS_PRO_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery', 'jquery-ui-datepicker', 'wp-color-picker' ),
            ATEAM_EVENTS_PRO_VERSION,
            true
        );

        wp_enqueue_style( 'wp-color-picker' );

        wp_localize_script( 'ateam-events-admin', 'ateamEventsAdmin', array(
            'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
            'nonce'      => wp_create_nonce( 'ateam_events_nonce' ),
            'pluginUrl'  => ATEAM_EVENTS_PRO_PLUGIN_URL,
            'i18n'       => array(
                'selectImage'  => __( 'Select Event Image', 'ateam-events-pro' ),
                'useImage'     => __( 'Use This Image', 'ateam-events-pro' ),
                'removeImage'  => __( 'Remove Image', 'ateam-events-pro' ),
            ),
        ) );
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Google Fonts
        wp_enqueue_style(
            'ateam-events-google-fonts',
            'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap',
            array(),
            null
        );

        // Frontend CSS
        wp_enqueue_style(
            'ateam-events-frontend',
            ATEAM_EVENTS_PRO_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            ATEAM_EVENTS_PRO_VERSION
        );

        // Frontend JS
        wp_enqueue_script(
            'ateam-events-frontend',
            ATEAM_EVENTS_PRO_PLUGIN_URL . 'assets/js/frontend.js',
            array( 'jquery' ),
            ATEAM_EVENTS_PRO_VERSION,
            true
        );

        wp_localize_script( 'ateam-events-frontend', 'ateamEventsFront', array(
            'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
            'nonce'        => wp_create_nonce( 'ateam_events_front_nonce' ),
            'restUrl'      => esc_url_raw( rest_url( 'ateam-events/v1/' ) ),
            'restNonce'    => wp_create_nonce( 'wp_rest' ),
            'primaryColor' => get_option( 'ateam_events_primary_color', '#6C63FF' ),
        ) );
    }

    /**
     * Add dashboard submenu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=ateam_event',
            __( 'Dashboard', 'ateam-events-pro' ),
            __( 'Dashboard', 'ateam-events-pro' ),
            'manage_options',
            'ateam-events-dashboard',
            array( $this, 'render_dashboard' )
        );
    }

    /**
     * Render dashboard page
     */
    public function render_dashboard() {
        $total_events    = wp_count_posts( 'ateam_event' );
        $published       = isset( $total_events->publish ) ? $total_events->publish : 0;
        $draft           = isset( $total_events->draft ) ? $total_events->draft : 0;

        // Upcoming events
        $upcoming = new WP_Query( array(
            'post_type'      => 'ateam_event',
            'posts_per_page' => 5,
            'post_status'    => 'publish',
            'meta_key'       => '_ateam_event_date',
            'orderby'        => 'meta_value',
            'order'          => 'ASC',
            'meta_query'     => array(
                array(
                    'key'     => '_ateam_event_date',
                    'value'   => current_time( 'Y-m-d' ),
                    'compare' => '>=',
                    'type'    => 'DATE',
                ),
            ),
        ) );

        // Past events count
        $past = new WP_Query( array(
            'post_type'      => 'ateam_event',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => array(
                array(
                    'key'     => '_ateam_event_date',
                    'value'   => current_time( 'Y-m-d' ),
                    'compare' => '<',
                    'type'    => 'DATE',
                ),
            ),
        ) );
        $past_count = $past->found_posts;

        include ATEAM_EVENTS_PRO_PLUGIN_DIR . 'templates/admin/dashboard.php';
    }

    /**
     * Plugin action links
     */
    public function plugin_action_links( $links ) {
        $settings_link = '<a href="' . admin_url( 'edit.php?post_type=ateam_event&page=ateam-events-settings' ) . '">' . __( 'Settings', 'ateam-events-pro' ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }
}

// Initialize plugin
ATeam_Events_Pro::get_instance();
