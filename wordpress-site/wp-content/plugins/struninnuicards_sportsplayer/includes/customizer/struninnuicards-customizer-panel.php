<?php
/**
 * Customizer - Panel
 * 
 * @package Struninn UI Cards - Sports Player
 * 
 * @since 1.0.0
 * 
 * @author Odin Design Themes (https://odindesignthemes.com/)
 * 
 */

if (!function_exists('struninnuicards_sportsplayer_customizer_panel')) {
  /**
   * Creates the customizer main panel
   * 
   * @param WP_Customize_Manager $wp_customize    Customize Manager instance.
   */
  function struninnuicards_sportsplayer_customizer_panel($wp_customize) {
    /**
     * Panel
     */
    $wp_customize->add_panel('struninnuicards_sportsplayer_customizer', [
      'title'       => esc_html_x('Struninn UI Cards - Sports Player', '(Customizer) Struninn UI Cards - Sports Player - Panel - Title', 'struninnuicards_sportsplayer'),
      'description' => esc_html_x('From here, you can customize plugin options.', '(Customizer) Struninn UI Cards - Sports Player - Panel - Description', 'struninnuicards_sportsplayer'),
      'priority'    => 500
    ]);
  }
}

add_action('customize_register', 'struninnuicards_sportsplayer_customizer_panel');

?>