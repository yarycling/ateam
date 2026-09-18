<?php
/**
 * Customizer - Font
 * 
 * @package Struninn UI Cards - Sports Player
 * 
 * @since 1.0.0
 * 
 * @author Odin Design Themes (https://odindesignthemes.com/)
 * 
 */

if (!function_exists('struninnuicards_sportsplayer_customizer_font')) {
  /**
   * Registers customizer sections, controls and settings.
   * 
   * @since 1.0.0
   * 
   * @param WP_Customize_Manager $wp_customize    Customize Manager instance.
   */
  function struninnuicards_sportsplayer_customizer_font($wp_customize) {
    /**
     * Options section
     */
    $wp_customize->add_section('struninnuicards_sportsplayer_font', [
      'title'       => esc_html_x('Font', '(Customizer) Font Options - Panel - Title', 'struninnuicards_sportsplayer'),
      'description' => esc_html_x('From here, you can change font options.', '(Customizer) Font Options - Panel - Description', 'struninnuicards_sportsplayer'),
      'priority'    => 200,
      'panel'       => 'struninnuicards_sportsplayer_customizer'
    ]);

    /**
     * Font Type
     */
    $wp_customize->add_setting('struninnuicards_sportsplayer_font_setting_use_plugin_font', [
      'type'              => 'option',
      'capability'        => 'manage_options',
      'sanitize_callback' => 'sanitize_text_field',
      'default'           => '1'
    ]);

    $wp_customize->add_control('struninnuicards_sportsplayer_font_setting_use_plugin_font', [
      'label'       => esc_html_x('Use plugin font', '(Customizer) Font Options - Use Plugin Font - Title', 'struninnuicards_sportsplayer'),
      'description' => esc_html_x('Uncheck this option if you don\'t want to use the plugin font.', '(Customizer) Font Options - Use Plugin Font - Description', 'struninnuicards_sportsplayer'),
      'type'        => 'checkbox',
      'section'     => 'struninnuicards_sportsplayer_font'
    ]);

    /**
     * Font Scale
     */
    $wp_customize->add_setting('struninnuicards_sportsplayer_font_setting_scale', [
      'type'              => 'option',
      'capability'        => 'manage_options',
      'sanitize_callback' => 'sanitize_text_field',
      'default'           => 1
    ]);

    $wp_customize->add_control('struninnuicards_sportsplayer_font_setting_scale', [
      'label'       => esc_html_x('Font - Scale', '(Customizer) Font Options - Scale - Title', 'struninnuicards_sportsplayer'),
      'description' => esc_html_x('Allows to increase (> 1) or decrease (< 1) proportional font size of all text elements. Some elements positions may be affected when using high or low values, so further style adjustments may be needed.', '(Customizer) Font Options - Scale - Description', 'struninnuicards_sportsplayer'),
      'type'        => 'number',
      'input_attrs' => [
        'min'   => 0,
        'max'   => 2,
        'step'  => .01
      ],
      'section'     => 'struninnuicards_sportsplayer_font'
    ]);
  }
}

add_action('customize_register', 'struninnuicards_sportsplayer_customizer_font');