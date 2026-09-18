<?php
/**
 * Customizer - Font Utils
 * 
 * @package Struninn UI Cards - Sports Player
 * 
 * @since 1.0.0
 * 
 * @author Odin Design Themes (https://odindesignthemes.com/)
 * 
 */

if (!function_exists('struninnuicards_sportsplayer_customizer_font_styles_get')) {
  /**
   * Returns current font style variables.
   * 
   * @since 1.0.0
   * 
   * @return string $font_styles      Current font style variables.
   */
  function struninnuicards_sportsplayer_customizer_font_styles_get() {
    $use_plugin_font = get_option('struninnuicards_sportsplayer_font_setting_use_plugin_font', '1');
    $font_scale = get_option('struninnuicards_sportsplayer_font_setting_scale', 1);
    
    $font_styles = ':root {';

    if ($use_plugin_font === '1') {
      $font_styles .= '--struninnuicard-sportsplayer-font-family: "Kumbh Sans", sans-serif;';
    }

    $font_styles .= '--struninnuicard-sportsplayer-font-scale-factor: ' . $font_scale . ';';

    $font_styles .= '}';

    return $font_styles;
  }
}

?>