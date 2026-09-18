<?php
/**
 * Functions - Elementor - Widget
 * 
 * @package Struninn UI Cards - Sports Player
 * 
 * @since 1.0.0
 * 
 * @author Odin Design Themes (https://odindesignthemes.com/)
 * 
 */

if (!class_exists('StruninnUICards_Elementor_Widget_SportsPlayer_Card_V1')) {
  /**
   * Video List Elementor Widget.
   * 
   * @since 1.0.0
   */
  class StruninnUICards_Elementor_Widget_SportsPlayer_Card_V1 extends \Elementor\Widget_Base {
    public function __construct($data = [], $args = null) {
      parent::__construct($data, $args);

      wp_register_script('struninnuicards-elementor-widget-sportsplayer-card', STRUNINNUICARDS_SPORTSPLAYER_URL . 'js/elementor/widget/sportsplayer-card/sportsplayer-card.js', ['elementor-frontend'], '1.0.0', true);
    }

    public function get_name() {
      return 'StruninnUICards_Elementor_Widget_SportsPlayer_card_v1';
    }
  
    public function get_title() {
      return esc_html__('Struninn UI Card - Sports Player V1', 'struninnuicards_sportsplayer');
    }
  
    public function get_icon() {
      return 'eicon-info-box';
    }
  
    public function get_categories() {
      return ['struninnuicards'];
    }
  
    public function get_keywords() {
      return ['struninn', 'struninnuicards', 'card', 'sports'];
    }
  
    protected function register_controls() {
      $this->start_controls_section(
        'struninnuicards_sportsplayer_card_content',
        [
          'label' => esc_html__('Content', 'struninnuicards_sportsplayer'),
          'tab'   => \Elementor\Controls_Manager::TAB_CONTENT
        ]
      );

      $defaults = struninnuicards_sportsplayer_template_card_v1_args_default_get();

      $this->add_control(
        'struninnuicards_sportsplayer_card_theme',
        [
          'label'       => esc_html__('Theme', 'struninnuicards_sportsplayer'),
          'type'        => \Elementor\Controls_Manager::SELECT,
          'default'     => $defaults['theme'],
          'options'     => [
            'light' => esc_html__('Light', 'struninnuicards_sportsplayer'),
            'dark'  => esc_html__('Dark', 'struninnuicards_sportsplayer')
          ]
        ]
      );

      $this->add_control(
        'struninnuicards_sportsplayer_card_width',
        [
          'label'       => esc_html__('Width', 'struninnuicards_sportsplayer'),
          'description' => esc_html__('Use a value of 0 to let the card auto adjust size.', 'struninnuicards_sportsplayer'),
          'type'        => \Elementor\Controls_Manager::NUMBER,
          'min'         => 0,
          'step'        => 1,
          'default'     => $defaults['width']
        ]
      );

      $this->add_control(
        'struninnuicards_sportsplayer_card_image',
        [
          'label'   => esc_html__('Image', 'struninnuicards_sportsplayer'),
          'type'    => \Elementor\Controls_Manager::MEDIA,
          'default' => [
            'url' => $defaults['image_url']
          ]
        ]
      );

      $this->add_control(
        'struninnuicards_sportsplayer_card_badge',
        [
          'label'   => esc_html__('Badge', 'struninnuicards_sportsplayer'),
          'type'    => \Elementor\Controls_Manager::MEDIA,
          'default' => [
            'url' => $defaults['badge_url']
          ]
        ]
      );

      $this->add_control(
        'struninnuicards_sportsplayer_card_title',
        [
          'label'       => esc_html__('Title', 'struninnuicards_sportsplayer'),
          'type'        => \Elementor\Controls_Manager::TEXT,
          'input_type'  => 'text',
          'default'     => $defaults['title']
        ]
      );

      $this->add_control(
        'struninnuicards_sportsplayer_card_title_link',
        [
          'label'       => esc_html__('Title Link', 'struninnuicards_sportsplayer'),
          'type'        => \Elementor\Controls_Manager::URL,
          'options'     => false,
          'default'     => [
            'url' => $defaults['title_link']
          ]
        ]
      );

      $this->add_control(
        'struninnuicards_sportsplayer_card_text',
        [
          'label'       => esc_html__('Text', 'struninnuicards_sportsplayer'),
          'type'        => \Elementor\Controls_Manager::TEXT,
          'input_type'  => 'text',
          'default'     => $defaults['text']
        ]
      );

      $this->add_control(
        'struninnuicards_sportsplayer_card_number',
        [
          'label'       => esc_html__('Number', 'struninnuicards_sportsplayer'),
          'type'        => \Elementor\Controls_Manager::TEXT,
          'input_type'  => 'text',
          'default'     => $defaults['number']
        ]
      );

      $this->add_control(
        'struninnuicards_sportsplayer_card_footer_left_title',
        [
          'label'       => esc_html__('Footer Left Title', 'struninnuicards_sportsplayer'),
          'type'        => \Elementor\Controls_Manager::TEXT,
          'input_type'  => 'text',
          'default'     => $defaults['footer_left_title']
        ]
      );

      $this->add_control(
        'struninnuicards_sportsplayer_facebook_link',
        [
          'label'   => esc_html__('Facebook URL', 'struninnuicards_sportsplayer'),
          'type'        => \Elementor\Controls_Manager::URL,
          'options'     => false,
          'default' => [
            'url' => $defaults['facebook_link']
          ]
        ]
      );

      $this->add_control(
        'struninnuicards_sportsplayer_x_link',
        [
          'label'   => esc_html__('X URL', 'struninnuicards_sportsplayer'),
          'type'        => \Elementor\Controls_Manager::URL,
          'options'     => false,
          'default' => [
            'url' => $defaults['x_link']
          ]
        ]
      );

      $this->add_control(
        'struninnuicards_sportsplayer_instagram_link',
        [
          'label'   => esc_html__('Instagram URL', 'struninnuicards_sportsplayer'),
          'type'        => \Elementor\Controls_Manager::URL,
          'options'     => false,
          'default' => [
            'url' => $defaults['instagram_link']
          ]
        ]
      );

      $this->end_controls_section();

      $this->start_controls_section(
        'struninnuicards_sportsplayer_card_style',
        [
          'label' => esc_html__('Colors', 'struninnuicards_sportsplayer'),
          'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ]
      );

      $this->add_control(
        'struninnuicards_sportsplayer_color_primary',
        [
          'label'   => esc_html__('Primary Color', 'struninnuicards_sportsplayer'),
          'type'    => \Elementor\Controls_Manager::COLOR,
          'default' => $defaults['color_primary']
        ]
      );

      $this->end_controls_section();
    }
  
    protected function render() {
      $settings = $this->get_settings_for_display();

      $args = [
        'theme'             => $settings['struninnuicards_sportsplayer_card_theme'],
        'color_primary'     => $settings['struninnuicards_sportsplayer_color_primary'],
        'width'             => $settings['struninnuicards_sportsplayer_card_width'],
        'image_url'         => $settings['struninnuicards_sportsplayer_card_image']['url'],
        'badge_url'         => $settings['struninnuicards_sportsplayer_card_badge']['url'],
        'title'             => $settings['struninnuicards_sportsplayer_card_title'],
        'title_link'        => $settings['struninnuicards_sportsplayer_card_title_link']['url'],
        'text'              => $settings['struninnuicards_sportsplayer_card_text'],
        'number'            => $settings['struninnuicards_sportsplayer_card_number'],
        'footer_left_title' => $settings['struninnuicards_sportsplayer_card_footer_left_title'],
        'facebook_link'     => $settings['struninnuicards_sportsplayer_facebook_link']['url'],
        'x_link'            => $settings['struninnuicards_sportsplayer_x_link']['url'],
        'instagram_link'    => $settings['struninnuicards_sportsplayer_instagram_link']['url']
      ];
    
      struninnuicards_sportsplayer_template_card_v1($args);
    }

    public function get_script_depends() {
      return ['struninnuicards-elementor-widget-sportsplayer-card'];
    }
  }
}

?>