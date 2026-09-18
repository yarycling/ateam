<?php
/**
 * Functions - Template - Card V4
 * 
 * @package Struninn UI Cards - Sports Player
 * 
 * @since 1.0.0
 * 
 * @author Odin Design Themes (https://odindesignthemes.com/)
 * 
 */

if (!function_exists('struninnuicards_sportsplayer_template_card_v4_args_default_get')) {
  /**
   * Returns card v4 template default args.
   * 
   * @since 1.0.0
   * 
   * @return array $card_args_default        Default card v4 template args.
   */
  function struninnuicards_sportsplayer_template_card_v4_args_default_get() {
    $card_args_default = [
      'theme'                   => 'light',
      'color_primary'           => '#a35aff',
      'width'                   => 0,
      'image_url'               => 'sample',
      'badge_url'               => '',
      'pretitle'                => 'LAUREN',
      'title'                   => 'SAKURAMI',
      'title_link'              => '#',
      'number'                  => '48',
      'footer_left_title'       => 'PURPLE TEAM',
      'footer_right_title'      => 'FULL BACK',
      'facebook_link'           => '#',
      'x_link'                  => '#',
      'instagram_link'          => '#'
    ];

    return $card_args_default;
  }
}

if (!function_exists('struninnuicards_sportsplayer_template_card_v4_get')) {
  /**
   * Returns card v4 template.
   * 
   * @since 1.0.0
   * 
   * @param array $args {
   *   @type string $theme                        Card color scheme, one of: 'light', 'dark'.
   *   @type string $color_primary                Primary card color.
   *   @type int    $width                        Card width, a value of 0 to lets the card auto adjust size.
   *   @type string $image_url                    Card image URL. A value of 'sample' shows an example of how the image will display.
   *   @type string $badge_url                    Card badge URL.
   *   @type string $pretitle                     Text that displays before the title.
   *   @type string $title                        Title.
   *   @type string $title_link                   Title link URL.
   *   @type string $number                       Number that displays in the middle of the footer.
   *   @type string $footer_left_title            Footer left title.
   *   @type string $footer_right_title           Footer right title.
   *   @type string $facebook_link                Facebook link URL.
   *   @type string $x_link                       X link URL.
   *   @type string $instagram_link               Instagram link URL.
   * }
   * @return string $template                     Card v4 HTML template.
   */
  function struninnuicards_sportsplayer_template_card_v4_get($args = []) {
    $defaults = struninnuicards_sportsplayer_template_card_v4_args_default_get();

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
          
            <div class="half-square-bottom half-square-bottom_long half-square-bottom_inverted half-square-bottom_bottom"></div>
            <div class="half-square-bottom half-square-bottom_long half-square-bottom_bottom half-square-bottom_right"></div>

          <?php if ($options['badge_url'] !== '') : ?>
            <div class="card__badge-container card__badge-container_medium card__badge-container_center card__badge-container_bottom">
              <img class="card__badge-image" src="<?php echo esc_url($options['badge_url']); ?>" alt="">
            </div>
          <?php endif; ?>
          </div>
          <!-- /CARD IMAGE CONTAINER -->
        <?php endif; ?>

          <!-- CARD BODY CONTENT -->
          <div class="card__body-content<?php if ($options['badge_url'] !== '') : ?> card__body-content_spaced card__body-content_small-padding<?php endif; ?>">
            <!-- CARD ROW -->
            <div class="card__row card__row_centered">
              <h2 class="card__title card__title_bold card__title_tiny"><?php echo esc_html($options['pretitle']); ?></h2>
            </div>
            <!-- /CARD ROW -->

            <!-- CARD ROW -->
            <div class="card__row card__row_centered card__row_joined">
            <?php if (array_key_exists('title_link', $options) && $options['title_link'] !== '') : ?>
              <a class="card__link" href="<?php echo esc_url($options['title_link']); ?>">
            <?php endif; ?>
              <h2 class="card__title card__title_bold card__title_big"><?php echo esc_html($options['title']); ?></h2>
            <?php if (array_key_exists('title_link', $options) && $options['title_link'] !== '') : ?>
              </a>
            <?php endif; ?>
            </div>
            <!-- /CARD ROW -->

            <!-- CARD ROW -->
            <div class="card__row card__row_centered">
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
            <!-- /CARD ROW -->
          </div>
          <!-- /CARD BODY CONTENT -->
        </div>
        <!-- /CARD BODY -->

      <?php if (($options['footer_left_title'] !== '') || ($options['footer_right_title'] !== '') || ($options['number'] !== '')) : ?>
        <!-- CARD FOOTER -->
        <div class="card-footer card-footer_darker card-footer_smaller">
          <!-- CARD ROW -->
          <div class="card__row">
          <?php if ($options['number'] !== '') : ?>
            <div class="tag tag_small tag_float-center">
              <p class="tag__text"><?php echo esc_html($options['number']); ?></p>
            </div>
          <?php endif; ?>

            <div class="card__column">
              <p class="card__text card__text_small"><?php echo esc_html($options['footer_left_title']); ?></p>
            </div>

            <div class="card__column">
              <p class="card__text card__text_small"><?php echo esc_html($options['footer_right_title']); ?></p>
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

if (!function_exists('struninnuicards_sportsplayer_template_card_v4')) {
  /**
   * Prints card v4 template.
   * 
   * @since 1.0.0
   * 
   * @param array   $args           Card template args.
   */
  function struninnuicards_sportsplayer_template_card_v4($args = []) {
    $template = struninnuicards_sportsplayer_template_card_v4_get($args);

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