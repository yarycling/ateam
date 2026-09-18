<?php
/**
 * Plugin Name: Struninn UI Cards - Sports Player
 * Plugin URI: https://themeforest.net/user/odin_design
 * Description: Sports Player UI Cards.
 * Version: 1.0.0
 * Author: Odin Design Themes
 * Author URI: https://themeforest.net/user/odin_design
 * License: https://themeforest.net/licenses/
 * License URI: https://themeforest.net/licenses/
 * Text Domain: struninnuicards_sportsplayer
 * Domain Path: /languages
 */

/**
 * Paths and urls
 * 
 * @since 1.0.0
 */
if (!defined('STRUNINNUICARDS_SPORTSPLAYER_PATH')) {
  define('STRUNINNUICARDS_SPORTSPLAYER_PATH', plugin_dir_path(__FILE__));
}

if (!defined('STRUNINNUICARDS_SPORTSPLAYER_URL')) {
  define('STRUNINNUICARDS_SPORTSPLAYER_URL', plugin_dir_url(__FILE__));
}

if (!function_exists('struninnuicards_sportsplayer_scripts_load')) {
  /**
   * Load plugin styles and scripts.
   * 
   * @since 1.0.0
   */
  function struninnuicards_sportsplayer_scripts_load() {
    /**
     * Styles
     */
    // load fonts
    wp_enqueue_style('struninnuicards_sportsplayer-fonts', 'https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@400;700;900&display=swap');
    // Main
    wp_enqueue_style('struninnuicards_sportsplayer-styles', STRUNINNUICARDS_SPORTSPLAYER_URL . 'css/style.css', [], '1.0.0');

    // Load current color CSS variables
    $color_styles = struninnuicards_sportsplayer_customizer_color_styles_get();

    // add user custom theme colors
    wp_add_inline_style('struninnuicards_sportsplayer-styles', $color_styles);

    // Load current user selected font styles
    $font_styles = struninnuicards_sportsplayer_customizer_font_styles_get();

    // add user custom font styles
    wp_add_inline_style('struninnuicards_sportsplayer-styles', $font_styles);

    /**
     * Scripts
     */
    // Main
    wp_enqueue_script('struninnuicards_sportsplayer-script', STRUNINNUICARDS_SPORTSPLAYER_URL . 'js/main.js', [], '1.0.0', true);
  }
}

add_action('wp_enqueue_scripts', 'struninnuicards_sportsplayer_scripts_load');

if (!function_exists('struninnuicards_sportsplayer_translations_load')) {
  /**
   * Load translations.
   * 
   * @since 1.0.0
   */
  function struninnuicards_sportsplayer_translations_load() {
    load_plugin_textdomain('struninnuicards_sportsplayer', false, dirname(plugin_basename(__FILE__)) . '/languages');
  }
}

add_action('init', 'struninnuicards_sportsplayer_translations_load');

/**
 * Load customizer options
 * 
 * @since 1.0.0
 */
require_once STRUNINNUICARDS_SPORTSPLAYER_PATH . 'includes/customizer/struninnuicards-customizer.php';

/**
 * Load functions
 * 
 * @since 1.0.0
 */
require_once STRUNINNUICARDS_SPORTSPLAYER_PATH . 'includes/functions/struninnuicards-functions.php';

?>