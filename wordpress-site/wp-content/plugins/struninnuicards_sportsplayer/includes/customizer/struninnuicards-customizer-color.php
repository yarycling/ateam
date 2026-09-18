<?php
/**
 * Customizer - Color
 * 
 * @package Struninn UI Cards - Sports Player
 * 
 * @since 1.0.0
 * 
 * @author Odin Design Themes (https://odindesignthemes.com/)
 * 
 */

if (!function_exists('struninnuicards_sportsplayer_customizer_color')) {
  /**
   * Registers customizer sections, controls and settings.
   * 
   * @since 1.0.0
   * 
   * @param WP_Customize_Manager $wp_customize    Customize Manager instance.
   */
  function struninnuicards_sportsplayer_customizer_color($wp_customize) {
    $color_presets = ['light', 'dark'];

    $color_preset_panel_title = [
      'light' => esc_html_x('Colors - Light Theme', '(Customizer) Color Options - Panel - Title', 'struninnuicards_sportsplayer'),
      'dark'  => esc_html_x('Colors - Dark Theme', '(Customizer) Color Options - Panel - Title', 'struninnuicards_sportsplayer')
    ];

    foreach ($color_presets as $color_preset) {
      $color_options = struninnuicards_sportsplayer_customizer_color_options_get($color_preset);

      $section_name = 'struninnuicards_sportsplayer_color_' . $color_preset . '_section';

      /**
       * Options section
       */
      $wp_customize->add_section($section_name, [
        'title'       => $color_preset_panel_title[$color_preset],
        'description' => esc_html_x('From here, you can change plugin colors.', '(Customizer) Color Options - Panel - Description', 'struninnuicards_sportsplayer'),
        'priority'    => 100,
        'panel'       => 'struninnuicards_sportsplayer_customizer'
      ]);

      foreach($color_options as $color_name => $color_data) {
        $color_settings_name = 'struninnuicards_sportsplayer_color[' . $color_name . ']';

        $wp_control_data = [
          'label'       => $color_data['label'],
          'section'     => $section_name
        ];

        if (array_key_exists('description', $color_data)) {
          $wp_control_data['description'] = $color_data['description'];
        }

        /**
         * Setting
         */
        $wp_customize->add_setting($color_settings_name, [
          'type'              => 'option',
          'capability'        => 'manage_options',
          'default'           => $color_data['default'],
          'sanitize_callback' => 'sanitize_hex_color'
        ]);

        /**
         * Control
         */
        $wp_customize->add_control(
          new WP_Customize_Color_Control(
            $wp_customize,
            $color_settings_name,
            $wp_control_data
          )
        );
      }
    }
  }
}

add_action('customize_register', 'struninnuicards_sportsplayer_customizer_color');