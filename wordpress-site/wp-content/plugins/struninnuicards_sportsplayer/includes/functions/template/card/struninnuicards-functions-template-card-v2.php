<?php
/**
 * Functions - Template - Card V2
 * 
 * @package Struninn UI Cards - Sports Player
 * 
 * @since 1.0.0
 * 
 * @author Odin Design Themes (https://odindesignthemes.com/)
 * 
 */

if (!function_exists('struninnuicards_sportsplayer_template_card_v2_args_default_get')) {
  /**
   * Returns card v2 template default args.
   * 
   * @since 1.0.0
   * 
   * @return array $card_args_default        Default card v2 template args.
   */
  function struninnuicards_sportsplayer_template_card_v2_args_default_get() {
    $card_args_default = [
      'theme'                   => 'light',
      'color_primary'           => '#00ade3',
      'width'                   => 0,
      'image_url'               => 'sample',
      'badge_url'               => '',
      'title'                   => 'Jessica Parker',
      'title_link'              => '#',
      'text'                    => 'Blue Demons',
      'number'                  => '27',
      'tag'                     => 'FORWARD',
      'facebook_link'           => '#',
      'x_link'                  => '#',
      'instagram_link'          => '#'
    ];

    return $card_args_default;
  }
}

if (!function_exists('struninnuicards_sportsplayer_template_card_v2_get')) {
  /**
   * Returns card v2 template.
   * 
   * @since 1.0.0
   * 
   * @param array $args {
   *   @type string $theme                        Card color scheme, one of: 'light', 'dark'.
   *   @type string $color_primary                Primary card color.
   *   @type int    $width                        Card width, a value of 0 to lets the card auto adjust size.
   *   @type string $image_url                    Card image URL. A value of 'sample' shows an example of how the image will display.
   *   @type string $badge_url                    Card badge URL.
   *   @type string $title                        Title.
   *   @type string $title_link                   Title link URL.
   *   @type string $text                         Text that displays under the title.
   *   @type string $number                       Number that displays at the bottom right of the image.
   *   @type string $tag                          Tag text.
   *   @type string $facebook_link                Facebook link URL.
   *   @type string $x_link                       X link URL.
   *   @type string $instagram_link               Instagram link URL.
   * }
   * @return string $template                     Card v2 HTML template.
   */
  function struninnuicards_sportsplayer_template_card_v2_get($args = []) {
    $defaults = struninnuicards_sportsplayer_template_card_v2_args_default_get();

    $options = array_merge($defaults, $args);

    $card_image_classes = $options['image_url'] === 'sample' ? ' card__image_sample' : '';

    ob_start();

  ?>
    <!-- STRUNINN UI CARD SPORTS PLAYER -->
    <div class="struninnuicard-sportsplayer struninnuicard-sportsplayer_theme-<?php echo esc_attr($options['theme']); ?>">
      <!-- CARD -->
      <div class="card" style="--card-primary-color: <?php echo esc_attr($options['color_primary']); ?>;<?php if ($options['width'] !== 0) : ?> max-width: <?php echo esc_attr($options['width']); ?>px;<?php endif; ?>">
        <!-- CARD BODY -->
        <div class="card__body">
        <?php if ($options['image_url'] !== '') : ?>
          <!-- CARD IMAGE CONTAINER -->
          <div class="card__image-container<?php echo esc_attr($card_image_classes); ?>">
          <?php if ($options['image_url'] !== 'sample') : ?>
            <img class="card__image" src="<?php echo esc_url($options['image_url']); ?>" alt="">
          <?php endif; ?>
          <?php if ($options['badge_url'] !== '') : ?>
            <div class="half-square-top half-square-top_left"></div>
            <div class="card__badge-container card__badge-container_medium">
              <img class="card__badge-image" src="<?php echo esc_url($options['badge_url']); ?>" alt="">
            </div>
          <?php endif; ?>

          <?php if ($options['number'] !== '') : ?>
            <div class="half-square-bottom half-square-bottom_bottom half-square-bottom_right"></div>
            <p class="card__number card__number_bottom card__number_right"><?php echo esc_html($options['number']); ?></p>
          <?php endif; ?>

          <?php if ($options['tag'] !== '') : ?>
            <div class="tag tag_rounded tag_bottom tag_center">
              <p class="tag__text"><?php echo esc_html($options['tag']); ?></p>
            </div>
          <?php endif; ?>
          </div>
          <!-- /CARD IMAGE CONTAINER -->
        <?php endif; ?>

          <!-- CARD BODY CONTENT -->
          <div class="card__body-content">
            <!-- CARD ROW -->
            <div class="card__row card__row_centered">
            <?php if (array_key_exists('title_link', $options) && $options['title_link'] !== '') : ?>
              <a class="card__link" href="<?php echo esc_url($options['title_link']); ?>">
            <?php endif; ?>
              <h2 class="card__title"><?php echo esc_html($options['title']); ?></h2>
            <?php if (array_key_exists('title_link', $options) && $options['title_link'] !== '') : ?>
              </a>
            <?php endif; ?>
            </div>
            <!-- /CARD ROW -->

            <!-- CARD ROW -->
            <div class="card__row card__row_centered">
            <?php if ($options['text'] !== '') : ?>
              <p class="card__text"><?php echo esc_html($options['text']); ?></p>
            <?php endif; ?>
            </div>
            <!-- /CARD ROW -->
          </div>
          <!-- /CARD BODY CONTENT -->
        </div>
        <!-- /CARD BODY -->

      <?php if (($options['facebook_link'] != '') || ($options['x_link'] != '') || ($options['instagram_link'] != '')) : ?>
        <!-- CARD FOOTER -->
        <div class="card-footer card-footer_darker card-footer_small">
          <!-- CARD ROW -->
          <div class="card__row card__row_centered">
            <div class="card__column">
              <div class="card__metadata">
              <?php

                if (($options['facebook_link'] != '') || ($options['x_link'] != '') || ($options['instagram_link'] != '')) {
                  $social_links_args = [
                    'social_links' => []
                  ];

                  if ($options['facebook_link'] != '') {
                    $social_links_args['social_links']['facebook'] = $options['facebook_link'];
                  }

                  if ($options['x_link'] != '') {
                    $social_links_args['social_links']['x'] = $options['x_link'];
                  }

                  if ($options['instagram_link'] != '') {
                    $social_links_args['social_links']['instagram'] = $options['instagram_link'];
                  }

                  /**
                   * Social Links
                   */
                  struninnuicards_sportsplayer_template_social_links($social_links_args);
                }

              ?>
              </div>
            </div>
          </div>
          <!-- /CARD ROW -->
        </div>
        <!-- /CARD FOOTER -->
      <?php endif; ?>
      </div>
      <!-- /CARD -->
    </div>
    <!-- /STRUNINN UI CARD SPORTS PLAYER -->
  <?php

    $template = ob_get_clean();

    return $template;
  }
}

if (!function_exists('struninnuicards_sportsplayer_template_card_v2')) {
  /**
   * Prints card v2 template.
   * 
   * @since 1.0.0
   * 
   * @param array   $args           Card template args.
   */
  function struninnuicards_sportsplayer_template_card_v2($args = []) {
    $template = struninnuicards_sportsplayer_template_card_v2_get($args);

    $template_allowed_html = [
      'div' => [
        'class' => [],
        'style' => []
      ],
      'img' => [
        'class' => [],
        'src'   => [],
        'alt'   => []
      ],
      'h2' => [
        'class' => []
      ],
      'p' => [
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