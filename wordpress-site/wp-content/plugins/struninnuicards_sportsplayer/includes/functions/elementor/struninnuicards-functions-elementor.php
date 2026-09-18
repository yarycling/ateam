<?php
/**
 * Functions - Elementor
 * 
 * @package Struninn UI Cards - Sports Player
 * 
 * @since 1.0.0
 * 
 * @author Odin Design Themes (https://odindesignthemes.com/)
 * 
 */
if (!function_exists('struninnuicards_sportsplayer_elementor_widgets_register')) {
  /**
   * Registers Elementor widgets.
   * 
   * @since 1.0.0
   * 
   * @param object $widgets_manager     Elementot widgets manager instance.
   */
  function struninnuicards_sportsplayer_elementor_widgets_register($widgets_manager) {
    require_once STRUNINNUICARDS_SPORTSPLAYER_PATH . 'includes/functions/elementor/widgets/sportsplayer-card/StruninnUICards_Elementor_Widget_SportsPlayer_Card_V1.php';
    require_once STRUNINNUICARDS_SPORTSPLAYER_PATH . 'includes/functions/elementor/widgets/sportsplayer-card/StruninnUICards_Elementor_Widget_SportsPlayer_Card_V2.php';
    require_once STRUNINNUICARDS_SPORTSPLAYER_PATH . 'includes/functions/elementor/widgets/sportsplayer-card/StruninnUICards_Elementor_Widget_SportsPlayer_Card_V3.php';
    require_once STRUNINNUICARDS_SPORTSPLAYER_PATH . 'includes/functions/elementor/widgets/sportsplayer-card/StruninnUICards_Elementor_Widget_SportsPlayer_Card_V4.php';

    $widgets_manager->register(new StruninnUICards_Elementor_Widget_SportsPlayer_Card_V1());
    $widgets_manager->register(new StruninnUICards_Elementor_Widget_SportsPlayer_Card_V2());
    $widgets_manager->register(new StruninnUICards_Elementor_Widget_SportsPlayer_Card_V3());
    $widgets_manager->register(new StruninnUICards_Elementor_Widget_SportsPlayer_Card_V4());
  }
}

add_action('elementor/widgets/register', 'struninnuicards_sportsplayer_elementor_widgets_register');

if (!function_exists('struninnuicards_elementor_widget_categories_register')) {
  /**
   * Registers Elementor categories.
   * 
   * @since 1.0.0
   * 
   * @param object $elements_manager      Elementor elements manager instance.
   */
  function struninnuicards_elementor_widget_categories_register($elements_manager) {
    $elements_manager->add_category(
      'struninnuicards',
      [
        'title' => esc_html__('Struninn UI Cards', 'struninnuicards_sportsplayer')
      ]
    );
  }
}

add_action('elementor/elements/categories_registered', 'struninnuicards_elementor_widget_categories_register');