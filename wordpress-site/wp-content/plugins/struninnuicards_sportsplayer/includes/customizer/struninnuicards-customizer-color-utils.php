<?php
/**
 * Customizer - Color Utils
 * 
 * @package Struninn UI Cards - Sports Player
 * 
 * @since 1.0.0
 * 
 * @author Odin Design Themes (https://odindesignthemes.com/)
 * 
 */

if (!function_exists('struninnuicards_sportsplayer_customizer_color_options_get')) {
  /**
   * Returns color settings.
   * 
   * @since 1.0.0
   * 
   * @param string $preset              Color preset, one of: 'light', 'dark'. Default: 'light'. 
   * @return array $color_settings      Color settings.
   */
  function struninnuicards_sportsplayer_customizer_color_options_get($preset = 'light') {
    $color_settings = [
      'light' => [
        /**
         * Box
         */
        '--struninnuicard-sportsplayer-light-background-color'     => [
          'label'   => esc_html_x('Box Background', '(Customizer) Color Option - Label', 'struninnuicards_sportsplayer'),
          'type'    => 'color',
          'default' => '#ffffff'
        ],
        '--struninnuicard-sportsplayer-light-background-alt-color' => [
          'label'   => esc_html_x('Darker Box Background', '(Customizer) Color Option - Label', 'struninnuicards_sportsplayer'),
          'type'    => 'color',
          'default' => '#f9f9f9'
        ],

        /**
         * Lines
         */
        '--struninnuicard-sportsplayer-light-line-color'    => [
          'label'   => esc_html_x('Line', '(Customizer) Color Option - Label', 'struninnuicards_sportsplayer'),
          'type'    => 'color',
          'default' => '#e1e1e1'
        ],

        /**
         * Text
         */
        '--struninnuicard-sportsplayer-light-text-color'    => [
          'label'   => esc_html_x('Text', '(Customizer) Color Option - Label', 'struninnuicards_sportsplayer'),
          'type'    => 'color',
          'default' => '#22212c'
        ],
        /**
         * Text
         */
        '--struninnuicard-sportsplayer-light-text-over-image-color' => [
          'label'   => esc_html_x('Text Over Image', '(Customizer) Color Option - Label', 'struninnuicards_sportsplayer'),
          'type'    => 'color',
          'default' => '#ffffff'
        ]
      ],
      'dark' => [
        /**
         * Box
         */
        '--struninnuicard-sportsplayer-dark-background-color'     => [
          'label'   => esc_html_x('Box Background', '(Customizer) Color Option - Label', 'struninnuicards_sportsplayer'),
          'type'    => 'color',
          'default' => '#2b2a3b'
        ],
        '--struninnuicard-sportsplayer-dark-background-alt-color' => [
          'label'   => esc_html_x('Darker Box Background', '(Customizer) Color Option - Label', 'struninnuicards_sportsplayer'),
          'type'    => 'color',
          'default' => '#21202e'
        ],

        /**
         * Lines
         */
        '--struninnuicard-sportsplayer-dark-line-color'    => [
          'label'   => esc_html_x('Line', '(Customizer) Color Option - Label', 'struninnuicards_sportsplayer'),
          'type'    => 'color',
          'default' => '#38374b'
        ],

        /**
         * Text
         */
        '--struninnuicard-sportsplayer-dark-text-color'    => [
          'label'   => esc_html_x('Text', '(Customizer) Color Option - Label', 'struninnuicards_sportsplayer'),
          'type'    => 'color',
          'default' => '#ffffff'
        ],
        /**
         * Text
         */
        '--struninnuicard-sportsplayer-dark-text-over-image-color' => [
          'label'   => esc_html_x('Text Over Image', '(Customizer) Color Option - Label', 'struninnuicards_sportsplayer'),
          'type'    => 'color',
          'default' => '#ffffff'
        ]
      ]
    ];

    return $color_settings[$preset];
  }
}

if (!function_exists('struninnuicards_sportsplayer_customizer_color_styles_get')) {
  /**
   * Returns current color style variables.
   * 
   * @since 1.0.0
   * 
   * @return string $color_styles      Current color style variables.
   */
  function struninnuicards_sportsplayer_customizer_color_styles_get() {
    $color_presets = ['light', 'dark'];

    $current_color_options = [];

    foreach ($color_presets as $color_preset) {
      $current_color_options = array_merge($current_color_options, struninnuicards_sportsplayer_customizer_color_options_get($color_preset));
    }

    $custom_user_colors = get_option('struninnuicards_sportsplayer_color', []);

    foreach ($current_color_options as $color_name => $color_data) {
      $user_entered_custom_color = array_key_exists($color_name, $custom_user_colors);

      // if user entered a color for this, use it
      if ($user_entered_custom_color) {
        $color_value = $custom_user_colors[$color_name];
      } else {
      // set color default otherwise
        $color_value = $color_data['default'];
      }
      
      $custom_user_colors[$color_name] = $color_value;
    }

    $color_styles = ':root {';

    foreach ($custom_user_colors as $color_name => $color_value) {
      $color_styles .= $color_name . ': ' . $color_value . ';';
    }

    $color_styles .= '}';

    return $color_styles;
  }
}

?>