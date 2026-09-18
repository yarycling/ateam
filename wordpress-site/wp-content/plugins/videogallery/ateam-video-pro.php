<?php
/**
 * Plugin Name: ATeam Video Pro
 * Description: A premium video gallery plugin with YouTube-inspired UI, categories, and lightbox playback.
 * Version: 1.0.0
 * Author: RedTent Media
 * Author URI: https://redtentmedialtd.com
 * Text Domain: ateam-video-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ATV_VG_PATH', plugin_dir_path( __FILE__ ) );
define( 'ATV_VG_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main Plugin Class
 */
class ATV_Video_Gallery {
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'public_enqueue' ) );
		
		require_once ATV_VG_PATH . 'includes/class-atv-admin.php';
		require_once ATV_VG_PATH . 'includes/class-atv-frontend.php';

		new ATV_Admin();
		new ATV_Frontend();
	}

	public function register_post_types() {
		// Register Taxonomy
		register_taxonomy( 'atv_category', 'atv_video', array(
			'labels' => array(
				'name' => 'Video Categories',
				'singular_name' => 'Video Category',
			),
			'hierarchical' => true,
			'show_ui' => true,
			'show_admin_column' => true,
			'show_in_rest' => true,
		) );

		// Register Post Type
		register_post_type( 'atv_video', array(
			'labels' => array(
				'name' => 'Videos',
				'singular_name' => 'Video',
				'add_new' => 'Add New Video',
			),
			'public' => true,
			'has_archive' => false,
			'supports' => array( 'title', 'editor', 'thumbnail' ),
			'menu_icon' => 'dashicons-video-alt3',
			'show_in_rest' => true,
		) );
	}

	public function admin_enqueue() {
		wp_enqueue_media();
		wp_enqueue_style( 'atv-admin-css', ATV_VG_URL . 'assets/css/admin.css', array(), '1.0.0' );
		wp_enqueue_script( 'atv-admin-js', ATV_VG_URL . 'assets/js/admin.js', array( 'jquery' ), '1.0.0', true );
		
		wp_localize_script( 'atv-admin-js', 'atvAdmin', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'atv_vg_nonce' )
		) );
	}

	public function public_enqueue() {
		wp_enqueue_style( 'google-fonts', 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap' );
		wp_enqueue_style( 'atv-public-css', ATV_VG_URL . 'assets/css/public.css', array(), '1.0.0' );
		wp_enqueue_script( 'atv-public-js', ATV_VG_URL . 'assets/js/public.js', array( 'jquery' ), '1.0.0', true );
		wp_localize_script( 'atv-public-js', 'atvFrontend', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' )
		) );
	}
}

new ATV_Video_Gallery();
