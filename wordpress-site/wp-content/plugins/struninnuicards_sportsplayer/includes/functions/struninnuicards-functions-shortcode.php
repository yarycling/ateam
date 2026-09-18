<?php
/**
 * Functions - Shortcode
 * 
 * @package Struninn UI Cards - Sports Player
 * 
 * @since 1.0.0
 * 
 * @author Odin Design Themes (https://odindesignthemes.com/)
 * 
 */

if (!function_exists('struninnuicards_sportsplayer_shortcode_args_parse')) {
  /**
   * Returns parsed shortcode args.
   * 
   * @since 1.0.0
   * 
   * @param array   $shortcode_args               Shortcode args.
   * @return array  $parsed_shortcode_args        Parsed shortcode args.
   */
  function struninnuicards_sportsplayer_shortcode_args_parse($shortcode_args) {
    $parsed_shortcode_args = [];

    foreach ((array) $shortcode_args as $shortcode_arg_key => $shortcode_arg_value) {
      $value = $shortcode_arg_value;

      if ($shortcode_arg_key === 'additional_wrapper_classes') {
        $value = explode(',', str_replace(' ', '', $value));
      } else if ($value === 'true') {
        $value = true;
      } else if ($value === 'false') {
        $value = false;
      }

      $parsed_shortcode_args[$shortcode_arg_key] = $value;
    }

    return $parsed_shortcode_args;
  }
}

add_filter('struninnuicards_sportsplayer_shortcode_args', 'struninnuicards_sportsplayer_shortcode_args_parse');

if (!function_exists('struninnuicards_sportsplayer_shortcode_register')) {
  /**
   * Register shortcodes.
   * 
   * @since 1.0.0
   */
  function struninnuicards_sportsplayer_shortcode_register() {
    $card_template_versions = ['v1', 'v2', 'v3', 'v4'];

    $shortcodes = [];

    foreach ($card_template_versions as $card_template_version) {
      $shortcodes['struninnuicards_sportsplayer_card_' . $card_template_version] = [
        'shortcode_callback'    => 'struninnuicards_sportsplayer_template_card_' . $card_template_version . '_get',
        'default_args_callback' => 'struninnuicards_sportsplayer_template_card_' . $card_template_version . '_args_default_get'
      ];
    }

    foreach ($shortcodes as $shortcode_name => $shortcode_data) {
      add_shortcode($shortcode_name, function ($args) use ($shortcode_name, $shortcode_data) {
        $shortcode_args_defaults = call_user_func($shortcode_data['default_args_callback']);
    
        $shortcode_args = apply_filters('struninnuicards_sportsplayer_shortcode_args', $args);

        $args = shortcode_atts($shortcode_args_defaults, $shortcode_args, $shortcode_name);

        return call_user_func($shortcode_data['shortcode_callback'], $args);
      });
    }
  }
}

struninnuicards_sportsplayer_shortcode_register();

?>