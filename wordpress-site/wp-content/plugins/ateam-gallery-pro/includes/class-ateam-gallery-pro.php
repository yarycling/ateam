<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ATeam_Gallery_Pro {

	protected $loader;
	protected $plugin_name;
	protected $version;

	public function __construct() {
		$this->plugin_name = 'ateam-gallery-pro';
		$this->version = ATEAM_GALLERY_PRO_VERSION;

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependencies() {
		require_once ATEAM_GALLERY_PRO_PATH . 'includes/class-ateam-gallery-pro-cpt.php';
		require_once ATEAM_GALLERY_PRO_PATH . 'includes/class-ateam-gallery-pro-ajax.php';
		require_once ATEAM_GALLERY_PRO_PATH . 'includes/class-ateam-gallery-pro-shortcode.php';
	}

	private function define_admin_hooks() {
		$plugin_admin = new ATeam_Gallery_Pro_CPT();
		add_action( 'init', array( $plugin_admin, 'register_post_type' ) );
		add_action( 'init', array( $plugin_admin, 'register_taxonomy' ) );
		add_action( 'init', array( $this, 'add_custom_image_sizes' ) );
		add_action( 'add_meta_boxes', array( $plugin_admin, 'add_gallery_meta_boxes' ) );
		add_action( 'save_post', array( $plugin_admin, 'save_gallery_meta' ) );
		
		// Admin Columns
		add_filter( 'manage_gallery_item_posts_columns', array( $plugin_admin, 'add_custom_columns' ) );
		add_action( 'manage_gallery_item_posts_custom_column', array( $plugin_admin, 'render_custom_columns' ), 10, 2 );

		// Scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	private function define_public_hooks() {
		$plugin_shortcode = new ATeam_Gallery_Pro_Shortcode();
		add_shortcode( 'ateam_gallery', array( $plugin_shortcode, 'render_shortcode' ) );

		$plugin_ajax = new ATeam_Gallery_Pro_AJAX();
		add_action( 'wp_ajax_ateam_filter_gallery', array( $plugin_ajax, 'filter_gallery' ) );
		add_action( 'wp_ajax_nopriv_ateam_filter_gallery', array( $plugin_ajax, 'filter_gallery' ) );
		add_action( 'wp_ajax_ateam_bulk_add_items', array( $plugin_ajax, 'bulk_add_items' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );
	}

	public function enqueue_admin_assets() {
		wp_enqueue_media();
		wp_enqueue_style( $this->plugin_name . '-admin', ATEAM_GALLERY_PRO_URL . 'assets/css/admin.css', array(), $this->version, 'all' );
		wp_enqueue_script( $this->plugin_name . '-admin', ATEAM_GALLERY_PRO_URL . 'assets/js/admin.js', array( 'jquery' ), $this->version, false );

		wp_localize_script( $this->plugin_name . '-admin', 'ateam_bulk_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'ateam_bulk_nonce' )
		) );
	}

	public function enqueue_public_assets() {
		wp_enqueue_style( 'google-fonts-poppins', 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap', array(), null );
		wp_enqueue_style( $this->plugin_name . '-frontend', ATEAM_GALLERY_PRO_URL . 'assets/css/frontend.css', array(), $this->version, 'all' );
		
		wp_enqueue_script( $this->plugin_name . '-frontend', ATEAM_GALLERY_PRO_URL . 'assets/js/frontend.js', array( 'jquery' ), $this->version, true );
		
		wp_localize_script( $this->plugin_name . '-frontend', 'ateam_gallery_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'ateam_gallery_nonce' )
		) );
	}

	public function add_custom_image_sizes() {
		add_image_size( 'ateam-gallery-thumb', 600, 600, true );
	}

	public function run() {
		// Execution already handled by hooks
	}
}
