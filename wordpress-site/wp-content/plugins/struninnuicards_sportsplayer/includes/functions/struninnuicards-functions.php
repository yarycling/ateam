<?php
/**
 * Functions
 * 
 * @package Struninn UI Cards - Sports Player
 * 
 * @since 1.0.0
 * 
 * @author Odin Design Themes (https://odindesignthemes.com/)
 * 
 */

/**
 * Template
 * 
 * @since 1.0.0
 */
require_once STRUNINNUICARDS_SPORTSPLAYER_PATH . 'includes/functions/template/struninnuicards-functions-template.php';

/**
 * Shortcode
 * 
 * @since 1.0.0
 */
require_once STRUNINNUICARDS_SPORTSPLAYER_PATH . 'includes/functions/struninnuicards-functions-shortcode.php';

/**
 * Load Elementor related functions
 * if the plugin is installed and active
 */
if (class_exists('Elementor\Plugin')) {
  /**
   * Elementor
   * 
   * @since 1.0.0
   */
  require_once STRUNINNUICARDS_SPORTSPLAYER_PATH . 'includes/functions/elementor/struninnuicards-functions-elementor.php';
}

?>