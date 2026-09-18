<?php
/**
 * Functions - Template - Part
 * 
 * @package Struninn UI Cards - Sports Player
 * 
 * @since 1.0.0
 * 
 * @author Odin Design Themes (https://odindesignthemes.com/)
 * 
 */

if (!function_exists('struninnuicards_sportsplayer_template_icon_get')) {
  /**
   * Returns icon template.
   * 
   * @since 1.0.0
   * 
   * @param array $args {
   *   @type string $additional_wrapper_classes       Classes to add to the wrapper.
   * }
   * @return string $template                         Icon HTML template.
   */
  function struninnuicards_sportsplayer_template_icon_get($args = []) {
    ob_start();

    $defaults = [
      'additional_wrapper_classes'  => false
    ];

    $options = array_merge($defaults, $args);

    $wrapper_classes = [
      'icon_'. $options['icon']
    ];

    if ($options['additional_wrapper_classes']) {
      $wrapper_classes[] = $options['additional_wrapper_classes'];
    }

  ?>
    <!-- ICON -->
    <svg class="<?php echo esc_attr(implode(' ', $wrapper_classes)); ?>">
      <use href="#svg-<?php echo esc_attr($options['icon']); ?>"></use>
    </svg>
    <!-- ICON -->
  <?php

    $template = ob_get_clean();

    return $template;
  }
}

if (!function_exists('struninnuicards_sportsplayer_template_icon')) {
  /**
   * Prints icon template.
   * 
   * @since 1.0.0
   */
  function struninnuicards_sportsplayer_template_icon($args = []) {
    $template = struninnuicards_sportsplayer_template_icon_get($args);

    $template_allowed_html = [
      'svg' => [
        'class' => []
      ],
      'use' => [
        'href' => []
      ]
    ];
    
    echo wp_kses($template, $template_allowed_html);
  }
}

if (!function_exists('struninnuicards_sportsplayer_template_social_links_get')) {
  /**
   * Returns social links template.
   * 
   * @since 1.0.0
   * 
   * @param array $args {
   *   @type array $social_links        Name value pairs, where name is social network name and value is link URL.
   * }
   * @return string $template           Social links HTML template.
   */
  function struninnuicards_sportsplayer_template_social_links_get($args = []) {
    ob_start();

    $defaults = [
      'social_links'  => []
    ];

    $options = array_merge($defaults, $args);

  ?>
    <div class="social__links">
    <?php

      foreach ($options['social_links'] as $social_network_name => $social_network_url) {
        /**
         * Social Link
         */
        struninnuicards_sportsplayer_template_social_link([
          'name'  => $social_network_name,
          'url'   => $social_network_url
        ]);
      }

    ?>
    </div>
  <?php

    $template = ob_get_clean();

    return $template;
  }
}

if (!function_exists('struninnuicards_sportsplayer_template_social_links')) {
  /**
   * Prints social links template.
   * 
   * @since 1.0.0
   */
  function struninnuicards_sportsplayer_template_social_links($args = []) {
    $template = struninnuicards_sportsplayer_template_social_links_get($args);

    $template_allowed_html = [
      'div' => [
        'class' => []
      ],
      'a' => [
        'class'   => [],
        'href'    => [],
        'target'  => []
      ],
      'svg' => [
        'class' => []
      ],
      'use' => [
        'href' => []
      ]
    ];
    
    echo wp_kses($template, $template_allowed_html);
  }
}

if (!function_exists('struninnuicards_sportsplayer_template_social_link_get')) {
  /**
   * Returns social link template.
   * 
   * @since 1.0.0
   * 
   * @param array $args {
   *   @type string   $name             Icon name.
   *   @type string   $url              Social link URL.
   * }
   * @return string $template           Social link HTML template.
   */
  function struninnuicards_sportsplayer_template_social_link_get($args = []) {
    ob_start();

    $defaults = [
      'url' => '#'
    ];

    $options = array_merge($defaults, $args);

  ?>
    <a class="social__link" href="<?php echo esc_attr($options['url']); ?>" target="_blank">
    <?php

      /**
       * Icon
       */
      struninnuicards_sportsplayer_template_icon([
        'additional_wrapper_classes'  => 'social__icon',
        'icon'                        => $options['name']
      ]);

    ?>
    </a>
  <?php

    $template = ob_get_clean();

    return $template;
  }
}

if (!function_exists('struninnuicards_sportsplayer_template_social_link')) {
  /**
   * Prints social links template.
   * 
   * @since 1.0.0
   */
  function struninnuicards_sportsplayer_template_social_link($args = []) {
    $template = struninnuicards_sportsplayer_template_social_link_get($args);

    $template_allowed_html = [
      'a' => [
        'class'   => [],
        'href'    => [],
        'target'  => []
      ],
      'svg' => [
        'class' => []
      ],
      'use' => [
        'href' => []
      ]
    ];
    
    echo wp_kses($template, $template_allowed_html);
  }
}

?>