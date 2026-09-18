<?php
/**
 * Stotage child theme bootstrap.
 */

if (!defined('ABSPATH')) {
    exit;
}

define('STOTAGE_CHILD_VERSION', '1.0.12');
define('STOTAGE_CHILD_DIR', get_stylesheet_directory());
define('STOTAGE_CHILD_URI', get_stylesheet_directory_uri());

require_once STOTAGE_CHILD_DIR . '/inc/rentals-builder.php';
require_once STOTAGE_CHILD_DIR . '/inc/bandroom-builder.php';
require_once STOTAGE_CHILD_DIR . '/inc/vip-menu-logo.php';

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'stotage-child-style',
        get_stylesheet_uri(),
        array('stotage-style'),
        STOTAGE_CHILD_VERSION
    );
}, 20);
