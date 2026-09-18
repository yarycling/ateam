<?php
/**
 * Plugin Name: ATeam Gallery Pro
 * Plugin URI:  #
 * Description: A modern, high-performance photo gallery for the A-Team Soca band.
 * Version:     1.0.0
 * Author:      RedTent Media
 * Author URI:  #
 * Text Domain: ateam-gallery-pro
 * License:     GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'ATEAM_GALLERY_PRO_VERSION', '1.0.0' );
define( 'ATEAM_GALLERY_PRO_PATH', plugin_dir_path( __FILE__ ) );
define( 'ATEAM_GALLERY_PRO_URL', plugin_dir_url( __FILE__ ) );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require ATEAM_GALLERY_PRO_PATH . 'includes/class-ateam-gallery-pro.php';

/**
 * Begins execution of the plugin.
 */
function run_ateam_gallery_pro() {
	$plugin = new ATeam_Gallery_Pro();
	$plugin->run();
}
run_ateam_gallery_pro();
