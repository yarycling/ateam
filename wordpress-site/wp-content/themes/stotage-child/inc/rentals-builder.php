<?php
/**
 * Rentals Builder admin and frontend.
 */

if (!defined('ABSPATH')) {
    exit;
}

final class ATeam_Rentals_Builder {
    private const OPTION_KEY = 'ateam_rentals_page_settings';
    private const OPTION_SEEDED = 'ateam_rentals_seeded_v1';
    private const RENTALS_SLUG = 'rentals';
    private const META_ACTIVE = '_ateam_rentals_active';
    private const META_ORDER = '_ateam_rentals_order';
    private const META_CATEGORY_ID = '_ateam_rentals_category_id';
    private const META_DESCRIPTION = '_ateam_rentals_description';
    private const META_SUB_ITEMS = '_ateam_rentals_sub_items';
    private const META_BG_IMAGE_ID = '_ateam_rentals_bg_image_id';
    private const META_ICON_LIBRARY = '_ateam_rentals_icon_library';
    private const META_ICON_VALUE = '_ateam_rentals_icon_value';
    private const META_ICON_IMAGE_ID = '_ateam_rentals_icon_image_id';
    private const META_SECTION_HEADING = '_ateam_rentals_section_heading';
    private const META_INTRO = '_ateam_rentals_intro';
    private const META_PRICE = '_ateam_rentals_price';
    private const META_CURRENCY = '_ateam_rentals_currency';
    private const META_UNIT = '_ateam_rentals_unit';
    private const META_RENDER_ENABLED = 'enabled';

    private array $page_hooks = array();

    public function hooks(): void {
        add_action('init', array($this, 'register_post_types'));
        add_action('after_switch_theme', array($this, 'seed_defaults'));
        add_action('admin_menu', array($this, 'register_admin_menu'));
        add_action('admin_init', array($this, 'handle_post_actions'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_filter('template_include', array($this, 'override_rentals_template'), 99);
        add_action('wp_ajax_ateam_rentals_save_order', array($this, 'ajax_save_order'));
    }

    public function register_post_types(): void {
        $common = array(
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => false,
            'show_in_menu' => false,
            'show_in_rest' => false,
            'exclude_from_search' => true,
            'supports' => array('title', 'editor', 'revisions'),
            'capability_type' => 'page',
            'map_meta_cap' => true,
        );

        register_post_type('rental_category', array_merge($common, array(
            'labels' => array('name' => 'Rental Categories', 'singular_name' => 'Rental Category'),
        )));

        register_post_type('rental_item', array_merge($common, array(
            'labels' => array('name' => 'Rental Items', 'singular_name' => 'Rental Item'),
        )));

        register_post_type('rental_price', array_merge($common, array(
            'labels' => array('name' => 'Rental Prices', 'singular_name' => 'Rental Price'),
        )));
    }

    public function seed_defaults(): void {
        if (get_option(self::OPTION_SEEDED)) {
            return;
        }

        $defaults = array(
            'enabled' => '1',
            'hero_eyebrow' => 'A Team Home',
            'breadcrumb_label' => 'Rentals',
            'hero_title' => 'Rentals',
            'hero_intro' => 'Professional equipment rentals for your next event. High-quality gear. Reliable performance. Unforgettable sound.',
            'hero_intro_spacing' => '20',
            'hero_spacer_image_id' => 0,
            'hero_bg_image_id' => 0,
            'pricing_title' => 'Rental Rates',
            'pricing_intro' => '',
            'categories_title' => '',
            'categories_intro' => '',
            'enquiry_label' => 'Contact us',
            'enquiry_url' => 'mailto:info@ateambandtt.com',
            'cta_title' => 'Book the Bandroom',
            'cta_text' => 'For bookings and availability, contact us directly.',
            'cta_button_label' => 'Contact us',
            'cta_button_url' => 'mailto:info@ateambandtt.com',
            'cta_bg_image_id' => 0,
        );
        update_option(self::OPTION_KEY, $defaults);

        $this->seed_price('First Hour', '500', '$', '', 'Perfect for smaller events and short performances.', 'fas fa-stopwatch', 'fontawesome', 0);
        $this->seed_price('Every Additional Hour', '300', '$', '', 'Affordable hourly rate for extended entertainment.', 'fas fa-clock', 'fontawesome', 1);

        $included_backline = $this->seed_category('Included Backline', '', '', 0, 0);
        $sound_system = $this->seed_category('Sound System', '', '', 0, 1);
        $recording_studio = $this->seed_category('Recording Studio', '', '', 0, 2);
        $facility = $this->seed_category('Facility Amenities', '', '', 0, 3);
        $ideal_for = $this->seed_category('Ideal For', '', '', 0, 4);

        $this->seed_item($included_backline, 'Drums & Percussion', '', array('Full Drum Kit & Accessories', 'LP Congas', 'LP Bongos', 'Percussion Toys', 'Percussion Table'), 'fas fa-drum', 'fontawesome', 0);
        $this->seed_item($included_backline, 'Guitar & Bass', '', array('Bass Amp & Cabling', 'Guitar Stands'), 'fas fa-guitar', 'fontawesome', 1);
        $this->seed_item($included_backline, 'Keys', '', array('Keyboard Stands', 'Keyboards Available Upon Request'), 'fas fa-keyboard', 'fontawesome', 2);

        $this->seed_item($sound_system, 'Audio Console', '', array('Behringer Wing 48-Channel Digital Console'), 'fas fa-sliders-h', 'fontawesome', 0);
        $this->seed_item($sound_system, 'Monitoring', '', array('Midas DP48 Personal IEM Monitoring System', 'Personal SD card recording capabilities'), 'fas fa-volume-up', 'fontawesome', 1);
        $this->seed_item($sound_system, 'PA System', '', array('JBL PRX Series Speakers'), 'fas fa-bullhorn', 'fontawesome', 2);
        $this->seed_item($sound_system, 'Microphones & Inputs', '', array('Sennheiser G4 Wireless Microphones', 'Shure Drum Microphone Package', 'DI Boxes', '8-12 Channel Playback Interface'), 'fas fa-microphone', 'fontawesome', 3);
        $this->seed_item($sound_system, 'Visuals & Media', 'Integrated TV screens positioned throughout the facility for:', array('Graphic Content', 'Confidence Monitoring', 'Playback Displays', 'Production Support'), 'fas fa-photo-video', 'fontawesome', 4);
        $this->seed_item($sound_system, 'Production Room', 'Dedicated isolated production room with full visual access to the bandroom and integrated control of audio and video systems.', array('FOH Engineering', 'Broadcast Mixing', 'Video Programming', 'Production Coordination'), 'fas fa-desktop', 'fontawesome', 5);

        $this->seed_item($recording_studio, 'Studio Equipment', '', array('Apple iMac', 'Logic Pro', 'Ableton Live', 'Apollo Twin Interface'), 'fas fa-record-vinyl', 'fontawesome', 0);
        $this->seed_item($recording_studio, 'Studio Rates', '', array('$300/hr - Studio Only', '$500/hr - With House Engineer'), 'fas fa-dollar-sign', 'fontawesome', 1);

        $this->seed_item($facility, 'Location', '', array('Scott Bushe Street, bordering the Woodbrook community, Port of Spain.'), 'fas fa-map-marker-alt', 'fontawesome', 0);
        $this->seed_item($facility, 'Kitchen Area', '', array('Microwave', 'Refrigerator', 'Coffee/Tea Station', 'Kettle'), 'fas fa-mug-hot', 'fontawesome', 1);
        $this->seed_item($facility, 'Washrooms', '', array('Designated Male & Female Toilets'), 'fas fa-restroom', 'fontawesome', 2);
        $this->seed_item($facility, 'Parking', '', array('Secure on-compound parking for up to 5 vehicles'), 'fas fa-parking', 'fontawesome', 3);

        $this->seed_item($ideal_for, 'Recommended Use', '', array('Band Rehearsals', 'Production Rehearsals', 'Live Recording Sessions', 'Content Creation', 'Corporate Show Prep', 'Virtual Performances', 'Playback Programming', 'Artist Development Sessions'), 'bootstrap-icons bi-music-note-list', 'bootstrap', 0);

        update_option(self::OPTION_SEEDED, '1');
    }

    private function seed_price(string $title, string $price, string $currency, string $unit, string $description, string $icon_value, string $icon_library, int $order): void {
        if ($this->find_seed_post('rental_price', $title)) {
            return;
        }
        $post_id = wp_insert_post(array(
            'post_type' => 'rental_price',
            'post_status' => 'publish',
            'post_title' => $title,
            'post_content' => $description,
        ));
        if ($post_id && !is_wp_error($post_id)) {
            update_post_meta($post_id, self::META_ACTIVE, '1');
            update_post_meta($post_id, self::META_ORDER, $order);
            update_post_meta($post_id, self::META_PRICE, $price);
            update_post_meta($post_id, self::META_CURRENCY, $currency);
            update_post_meta($post_id, self::META_UNIT, $unit);
            update_post_meta($post_id, self::META_ICON_LIBRARY, $icon_library);
            update_post_meta($post_id, self::META_ICON_VALUE, $icon_value);
            update_post_meta($post_id, self::META_ICON_IMAGE_ID, 'image' === $icon_library ? absint($icon_value) : 0);
        }
    }

    private function seed_category(string $title, string $section_heading, string $intro, int $bg_image_id, int $order): int {
        $existing = $this->find_seed_post('rental_category', $title);
        if ($existing) {
            return $existing;
        }
        $post_id = wp_insert_post(array(
            'post_type' => 'rental_category',
            'post_status' => 'publish',
            'post_title' => $title,
            'post_content' => $intro,
        ));
        if ($post_id && !is_wp_error($post_id)) {
            update_post_meta($post_id, self::META_ACTIVE, '1');
            update_post_meta($post_id, self::META_ORDER, $order);
            update_post_meta($post_id, self::META_SECTION_HEADING, $section_heading);
            update_post_meta($post_id, self::META_INTRO, $intro);
            update_post_meta($post_id, self::META_BG_IMAGE_ID, $bg_image_id);
            return (int) $post_id;
        }
        return 0;
    }

    private function seed_item(int $category_id, string $title, string $description, array $sub_items, string $icon_value, string $icon_library, int $order): void {
        if ($this->find_seed_post('rental_item', $title)) {
            return;
        }
        $post_id = wp_insert_post(array(
            'post_type' => 'rental_item',
            'post_status' => 'publish',
            'post_title' => $title,
            'post_content' => $description,
        ));
        if ($post_id && !is_wp_error($post_id)) {
            update_post_meta($post_id, self::META_ACTIVE, '1');
            update_post_meta($post_id, self::META_ORDER, $order);
            update_post_meta($post_id, self::META_CATEGORY_ID, $category_id);
            update_post_meta($post_id, self::META_DESCRIPTION, $description);
            update_post_meta($post_id, self::META_SUB_ITEMS, array_values($sub_items));
            update_post_meta($post_id, self::META_ICON_LIBRARY, $icon_library);
            update_post_meta($post_id, self::META_ICON_VALUE, $icon_value);
            update_post_meta($post_id, self::META_BG_IMAGE_ID, 0);
        }
    }

    private function find_seed_post(string $post_type, string $title): int {
        $post = get_page_by_title($title, OBJECT, $post_type);
        return $post ? (int) $post->ID : 0;
    }

    public function register_admin_menu(): void {
        $capability = 'edit_pages';
        $this->page_hooks[] = add_menu_page('Rentals Builder', 'Rentals Builder', $capability, 'rentals-builder', array($this, 'render_dashboard_page'), 'dashicons-format-gallery', 59);
        $this->page_hooks[] = add_submenu_page('rentals-builder', 'Dashboard', 'Dashboard', $capability, 'rentals-builder', array($this, 'render_dashboard_page'));
        $this->page_hooks[] = add_submenu_page('rentals-builder', 'Page Settings', 'Page Settings', $capability, 'rentals-builder-settings', array($this, 'render_settings_page'));
        $this->page_hooks[] = add_submenu_page('rentals-builder', 'Pricing', 'Pricing', $capability, 'rentals-builder-pricing', array($this, 'render_pricing_page'));
        $this->page_hooks[] = add_submenu_page('rentals-builder', 'Categories', 'Categories', $capability, 'rentals-builder-categories', array($this, 'render_categories_page'));
        $this->page_hooks[] = add_submenu_page('rentals-builder', 'Items', 'Items', $capability, 'rentals-builder-items', array($this, 'render_items_page'));
        $this->page_hooks[] = add_submenu_page('rentals-builder', 'Icon Library', 'Icon Library', $capability, 'rentals-builder-icons', array($this, 'render_icons_page'));
    }

    public function enqueue_admin_assets(string $hook): void {
        if (!in_array($hook, $this->page_hooks, true)) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_style('dashicons');
        wp_enqueue_style(
            'ateam-rentals-admin',
            STOTAGE_CHILD_URI . '/assets/css/rentals-admin.css',
            array(),
            STOTAGE_CHILD_VERSION
        );
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script(
            'ateam-rentals-admin',
            STOTAGE_CHILD_URI . '/assets/js/rentals-admin.js',
            array('jquery', 'jquery-ui-sortable'),
            STOTAGE_CHILD_VERSION,
            true
        );

        wp_localize_script('ateam-rentals-admin', 'ATeamRentalsAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ateam_rentals_order'),
            'chooseImage' => 'Choose image',
            'useImage' => 'Use image',
        ));
    }

    public function enqueue_frontend_assets(): void {
        if (!$this->is_rentals_page()) {
            return;
        }

        wp_enqueue_style('dashicons');
        wp_enqueue_style(
            'ateam-rentals-bootstrap-icons',
            get_template_directory_uri() . '/assets/fonts/bootstrap-icons/css/bootstrap-icons.min.css',
            array(),
            STOTAGE_CHILD_VERSION
        );
        wp_enqueue_style(
            'ateam-rentals-caseicon',
            get_template_directory_uri() . '/assets/css/pxl-caseicon.min.css',
            array(),
            STOTAGE_CHILD_VERSION
        );
        foreach (array('elementor-icons-fa-solid', 'elementor-icons-fa-regular', 'elementor-icons-fa-brands') as $handle) {
            if (wp_style_is($handle, 'registered')) {
                wp_enqueue_style($handle);
            }
        }
        wp_enqueue_style(
            'ateam-rentals-fonts',
            'https://fonts.googleapis.com/css2?family=Bubblegum+Sans&family=Instrument+Serif:ital@0;1&display=swap',
            array(),
            null
        );
        wp_enqueue_style(
            'ateam-rentals-frontend',
            STOTAGE_CHILD_URI . '/assets/css/rentals-frontend.css',
            array(),
            STOTAGE_CHILD_VERSION
        );
    }

    public function override_rentals_template(string $template): string {
        if (!$this->is_rentals_page()) {
            return $template;
        }

        $custom = STOTAGE_CHILD_DIR . '/templates/page-rentals.php';
        return file_exists($custom) ? $custom : $template;
    }

    public function ajax_save_order(): void {
        if (!current_user_can('edit_pages')) {
            wp_send_json_error(array('message' => 'Permission denied.'), 403);
        }

        check_ajax_referer('ateam_rentals_order', 'nonce');

        $post_type = isset($_POST['post_type']) ? sanitize_key(wp_unslash($_POST['post_type'])) : '';
        $order = isset($_POST['order']) ? (array) $_POST['order'] : array();
        $allowed = array('rental_price', 'rental_category', 'rental_item');
        if (!in_array($post_type, $allowed, true)) {
            wp_send_json_error(array('message' => 'Invalid type.'), 400);
        }

        foreach ($order as $index => $post_id) {
            update_post_meta((int) $post_id, self::META_ORDER, (int) $index);
        }

        wp_send_json_success(array('message' => 'Order saved.'));
    }

    public function handle_post_actions(): void {
        if (!is_admin() || !current_user_can('edit_pages')) {
            return;
        }

        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
        if (strpos($page, 'rentals-builder') !== 0) {
            return;
        }

        if ('POST' !== $_SERVER['REQUEST_METHOD']) {
            return;
        }

        $action = isset($_POST['ateam_rentals_action']) ? sanitize_key(wp_unslash($_POST['ateam_rentals_action'])) : '';
        if (!$action) {
            return;
        }

        check_admin_referer('ateam_rentals_action', 'ateam_rentals_nonce');

        if ('save_settings' === $action) {
            $settings = $this->sanitize_settings($_POST);
            update_option(self::OPTION_KEY, $settings);
            $this->redirect_with_notice($page, 'saved');
        }

        if ('save_price' === $action) {
            $this->save_price($_POST);
            $this->redirect_with_notice('rentals-builder-pricing', 'saved');
        }

        if ('save_category' === $action) {
            $this->save_category($_POST);
            $this->redirect_with_notice('rentals-builder-categories', 'saved');
        }

        if ('save_item' === $action) {
            $this->save_item($_POST);
            $this->redirect_with_notice('rentals-builder-items', 'saved');
        }

        if (in_array($action, array('deactivate_record', 'activate_record', 'delete_record'), true)) {
            $this->toggle_record($action, $_POST);
            $this->redirect_with_notice($page, 'updated');
        }
    }

    private function redirect_with_notice(string $page, string $notice): void {
        wp_safe_redirect(add_query_arg(array(
            'page' => $page,
            'rentals_notice' => $notice,
        ), admin_url('admin.php')));
        exit;
    }

    private function sanitize_settings(array $source): array {
        return array(
            'enabled' => empty($source['enabled']) ? '0' : '1',
            'hero_title' => sanitize_text_field(wp_unslash($source['hero_title'] ?? '')),
            'hero_intro' => wp_kses_post(wp_unslash($source['hero_intro'] ?? '')),
            'hero_intro_spacing' => (string) max(0, absint($source['hero_intro_spacing'] ?? 20)),
            'hero_spacer_image_id' => absint($source['hero_spacer_image_id'] ?? 0),
            'hero_bg_image_id' => absint($source['hero_bg_image_id'] ?? 0),
            'hero_eyebrow' => sanitize_text_field(wp_unslash($source['hero_eyebrow'] ?? '')),
            'breadcrumb_label' => sanitize_text_field(wp_unslash($source['breadcrumb_label'] ?? '')),
            'pricing_title' => sanitize_text_field(wp_unslash($source['pricing_title'] ?? '')),
            'pricing_intro' => wp_kses_post(wp_unslash($source['pricing_intro'] ?? '')),
            'categories_title' => sanitize_text_field(wp_unslash($source['categories_title'] ?? '')),
            'categories_intro' => wp_kses_post(wp_unslash($source['categories_intro'] ?? '')),
            'enquiry_label' => sanitize_text_field(wp_unslash($source['enquiry_label'] ?? '')),
            'enquiry_url' => esc_url_raw(wp_unslash($source['enquiry_url'] ?? '')),
            'cta_title' => sanitize_text_field(wp_unslash($source['cta_title'] ?? '')),
            'cta_text' => wp_kses_post(wp_unslash($source['cta_text'] ?? '')),
            'cta_button_label' => sanitize_text_field(wp_unslash($source['cta_button_label'] ?? '')),
            'cta_button_url' => esc_url_raw(wp_unslash($source['cta_button_url'] ?? '')),
            'cta_bg_image_id' => absint($source['cta_bg_image_id'] ?? 0),
        );
    }

    private function save_price(array $source): void {
        $post_id = absint($source['post_id'] ?? 0);
        $title = sanitize_text_field(wp_unslash($source['title'] ?? ''));
        $description = sanitize_textarea_field(wp_unslash($source['description'] ?? ''));
        $icon_library = sanitize_text_field(wp_unslash($source['icon_library'] ?? ''));
        $icon_value = 'image' === $icon_library
            ? (string) absint($source['icon_image_id'] ?? 0)
            : sanitize_text_field(wp_unslash($source['icon_value'] ?? ''));

        $postarr = array(
            'post_type' => 'rental_price',
            'post_status' => 'publish',
            'post_title' => $title,
            'post_content' => $description,
        );
        if ($post_id) {
            $postarr['ID'] = $post_id;
        }

        $saved_id = wp_insert_post($postarr);
        if ($saved_id && !is_wp_error($saved_id)) {
            update_post_meta($saved_id, self::META_ACTIVE, empty($source['active']) ? '0' : '1');
            update_post_meta($saved_id, self::META_ORDER, absint($source['display_order'] ?? 0));
            update_post_meta($saved_id, self::META_PRICE, sanitize_text_field(wp_unslash($source['price'] ?? '')));
            update_post_meta($saved_id, self::META_CURRENCY, sanitize_text_field(wp_unslash($source['currency'] ?? '')));
            update_post_meta($saved_id, self::META_UNIT, sanitize_text_field(wp_unslash($source['unit'] ?? '')));
            update_post_meta($saved_id, self::META_BG_IMAGE_ID, absint($source['bg_image_id'] ?? 0));
            update_post_meta($saved_id, self::META_ICON_LIBRARY, $icon_library);
            update_post_meta($saved_id, self::META_ICON_VALUE, $icon_value);
            update_post_meta($saved_id, self::META_ICON_IMAGE_ID, 'image' === $icon_library ? absint($icon_value) : 0);
        }
    }

    private function save_category(array $source): void {
        $post_id = absint($source['post_id'] ?? 0);
        $title = sanitize_text_field(wp_unslash($source['title'] ?? ''));
        $intro = sanitize_textarea_field(wp_unslash($source['intro'] ?? ''));
        $postarr = array(
            'post_type' => 'rental_category',
            'post_status' => 'publish',
            'post_title' => $title,
            'post_content' => $intro,
        );
        if ($post_id) {
            $postarr['ID'] = $post_id;
        }

        $saved_id = wp_insert_post($postarr);
        if ($saved_id && !is_wp_error($saved_id)) {
            update_post_meta($saved_id, self::META_ACTIVE, empty($source['active']) ? '0' : '1');
            update_post_meta($saved_id, self::META_ORDER, absint($source['display_order'] ?? 0));
            update_post_meta($saved_id, self::META_SECTION_HEADING, sanitize_text_field(wp_unslash($source['section_heading'] ?? '')));
            update_post_meta($saved_id, self::META_INTRO, $intro);
            update_post_meta($saved_id, self::META_BG_IMAGE_ID, absint($source['bg_image_id'] ?? 0));
        }
    }

    private function save_item(array $source): void {
        $post_id = absint($source['post_id'] ?? 0);
        $title = sanitize_text_field(wp_unslash($source['title'] ?? ''));
        $description = sanitize_textarea_field(wp_unslash($source['description'] ?? ''));
        $icon_library = sanitize_text_field(wp_unslash($source['icon_library'] ?? ''));
        $icon_value = 'image' === $icon_library
            ? (string) absint($source['icon_image_id'] ?? 0)
            : sanitize_text_field(wp_unslash($source['icon_value'] ?? ''));
        $postarr = array(
            'post_type' => 'rental_item',
            'post_status' => 'publish',
            'post_title' => $title,
            'post_content' => $description,
        );
        if ($post_id) {
            $postarr['ID'] = $post_id;
        }

        $saved_id = wp_insert_post($postarr);
        if ($saved_id && !is_wp_error($saved_id)) {
            $sub_items = preg_split('/\r\n|\r|\n/', (string) wp_unslash($source['sub_items'] ?? ''));
            $sub_items = array_values(array_filter(array_map('sanitize_text_field', $sub_items)));
            update_post_meta($saved_id, self::META_ACTIVE, empty($source['active']) ? '0' : '1');
            update_post_meta($saved_id, self::META_ORDER, absint($source['display_order'] ?? 0));
            update_post_meta($saved_id, self::META_CATEGORY_ID, absint($source['category_id'] ?? 0));
            update_post_meta($saved_id, self::META_DESCRIPTION, $description);
            update_post_meta($saved_id, self::META_SUB_ITEMS, $sub_items);
            update_post_meta($saved_id, self::META_BG_IMAGE_ID, absint($source['bg_image_id'] ?? 0));
            update_post_meta($saved_id, self::META_ICON_LIBRARY, $icon_library);
            update_post_meta($saved_id, self::META_ICON_VALUE, $icon_value);
            update_post_meta($saved_id, self::META_ICON_IMAGE_ID, 'image' === $icon_library ? absint($icon_value) : 0);
        }
    }

    private function toggle_record(string $action, array $source): void {
        $post_id = absint($source['post_id'] ?? 0);
        if (!$post_id) {
            return;
        }
        if ('delete_record' === $action) {
            wp_delete_post($post_id, true);
            return;
        }
        update_post_meta($post_id, self::META_ACTIVE, 'activate_record' === $action ? '1' : '0');
    }

    private function get_settings(): array {
        return wp_parse_args((array) get_option(self::OPTION_KEY, array()), array(
            'enabled' => '1',
            'hero_title' => 'Rentals',
            'hero_intro' => '',
            'hero_intro_spacing' => '20',
            'hero_spacer_image_id' => 0,
            'hero_bg_image_id' => 0,
            'hero_eyebrow' => '',
            'breadcrumb_label' => '',
            'pricing_title' => 'Rental Rates',
            'pricing_intro' => '',
            'categories_title' => '',
            'categories_intro' => '',
            'enquiry_label' => '',
            'enquiry_url' => '',
            'cta_title' => '',
            'cta_text' => '',
            'cta_button_label' => '',
            'cta_button_url' => '',
            'cta_bg_image_id' => 0,
        ));
    }

    private function get_records(string $post_type, bool $active = true): array {
        return get_posts(array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_key' => self::META_ORDER,
            'orderby' => array(
                'meta_value_num' => 'ASC',
                'title' => 'ASC',
            ),
            'meta_query' => array(
                array(
                    'key' => self::META_ACTIVE,
                    'value' => $active ? '1' : '0',
                ),
            ),
        ));
    }

    private function count_records(string $post_type, bool $active): int {
        return count($this->get_records($post_type, $active));
    }

    private function admin_tabs(string $base_page, string $post_type): void {
        $active_tab = isset($_GET['status']) && 'inactive' === $_GET['status'] ? 'inactive' : 'active';
        $active_count = $this->count_records($post_type, true);
        $inactive_count = $this->count_records($post_type, false);
        echo '<h2 class="nav-tab-wrapper ateam-rentals-tabs">';
        echo '<a class="nav-tab ' . ('active' === $active_tab ? 'nav-tab-active' : '') . '" href="' . esc_url(add_query_arg(array('page' => $base_page, 'status' => 'active'), admin_url('admin.php'))) . '">Active (' . esc_html((string) $active_count) . ')</a>';
        echo '<a class="nav-tab ' . ('inactive' === $active_tab ? 'nav-tab-active' : '') . '" href="' . esc_url(add_query_arg(array('page' => $base_page, 'status' => 'inactive'), admin_url('admin.php'))) . '">Inactive (' . esc_html((string) $inactive_count) . ')</a>';
        echo '</h2>';
    }

    public function render_dashboard_page(): void {
        $rentals_page = get_page_by_path(self::RENTALS_SLUG);
        $settings = $this->get_settings();
        echo '<div class="wrap ateam-rentals-wrap"><h1>Rentals Builder</h1>';
        $this->render_notice();
        echo '<div class="ateam-rentals-dashboard-grid">';
        echo '<div class="ateam-rentals-panel"><h2>Page</h2><p><strong>Rentals page:</strong> ' . ($rentals_page ? esc_html($rentals_page->post_title . ' (#' . $rentals_page->ID . ')') : 'Missing') . '</p><p><strong>Managed rendering:</strong> ' . ('1' === $settings['enabled'] ? 'Enabled' : 'Disabled') . '</p></div>';
        echo '<div class="ateam-rentals-panel"><h2>Content Summary</h2><p><strong>Pricing cards:</strong> ' . esc_html((string) $this->count_records('rental_price', true)) . '</p><p><strong>Categories:</strong> ' . esc_html((string) $this->count_records('rental_category', true)) . '</p><p><strong>Items:</strong> ' . esc_html((string) $this->count_records('rental_item', true)) . '</p></div>';
        echo '<div class="ateam-rentals-panel"><h2>Theme Context</h2><p><strong>Active theme:</strong> ' . esc_html(wp_get_theme()->get('Name')) . '</p><p><strong>Fonts reused:</strong> Bubblegum Sans, Instrument Serif</p><p><strong>Icons reused:</strong> Bootstrap Icons, Font Awesome</p></div>';
        echo '</div></div>';
    }

    public function render_settings_page(): void {
        $settings = $this->get_settings();
        echo '<div class="wrap ateam-rentals-wrap"><h1>Page Settings</h1>';
        $this->render_notice();
        echo '<form method="post" class="ateam-rentals-form">';
        wp_nonce_field('ateam_rentals_action', 'ateam_rentals_nonce');
        echo '<input type="hidden" name="ateam_rentals_action" value="save_settings">';
        $this->render_checkbox_field('Enable Rentals Page Content', 'enabled', '1' === $settings['enabled']);
        $this->render_text_field('Hero title', 'hero_title', $settings['hero_title']);
        $this->render_text_field('Hero eyebrow text', 'hero_eyebrow', $settings['hero_eyebrow']);
        $this->render_text_field('Breadcrumb label', 'breadcrumb_label', $settings['breadcrumb_label']);
        $this->render_textarea_field('Hero introductory text', 'hero_intro', $settings['hero_intro']);
        $this->render_number_field('Spacing from breadcrumb/title area to intro text (px)', 'hero_intro_spacing', (int) $settings['hero_intro_spacing']);
        $this->render_media_field('Hero spacer image', 'hero_spacer_image_id', (int) $settings['hero_spacer_image_id']);
        $this->render_media_field('Hero background image', 'hero_bg_image_id', (int) $settings['hero_bg_image_id']);
        $this->render_text_field('Pricing section title', 'pricing_title', $settings['pricing_title']);
        $this->render_textarea_field('Pricing section introduction', 'pricing_intro', $settings['pricing_intro']);
        $this->render_text_field('Categories area title', 'categories_title', $settings['categories_title']);
        $this->render_textarea_field('Categories area introduction', 'categories_intro', $settings['categories_intro']);
        $this->render_text_field('Enquiry button label', 'enquiry_label', $settings['enquiry_label']);
        $this->render_url_field('Enquiry button URL', 'enquiry_url', $settings['enquiry_url']);
        $this->render_text_field('Bottom CTA title', 'cta_title', $settings['cta_title']);
        $this->render_textarea_field('Bottom CTA text', 'cta_text', $settings['cta_text']);
        $this->render_text_field('Bottom CTA button label', 'cta_button_label', $settings['cta_button_label']);
        $this->render_url_field('Bottom CTA button URL', 'cta_button_url', $settings['cta_button_url']);
        $this->render_media_field('Bottom CTA background image', 'cta_bg_image_id', (int) $settings['cta_bg_image_id']);
        submit_button('Save Settings');
        echo '</form></div>';
    }

    public function render_pricing_page(): void {
        $status = isset($_GET['status']) && 'inactive' === $_GET['status'] ? 'inactive' : 'active';
        $editing = isset($_GET['edit']) ? get_post((int) $_GET['edit']) : null;
        $records = $this->get_records('rental_price', 'active' === $status);
        echo '<div class="wrap ateam-rentals-wrap"><h1>Pricing</h1>';
        $this->render_notice();
        $this->admin_tabs('rentals-builder-pricing', 'rental_price');
        echo '<div class="ateam-rentals-admin-grid">';
        echo '<div class="ateam-rentals-panel">';
        echo '<h2>' . ($editing ? 'Edit Pricing Card' : 'Add New Pricing Card') . '</h2>';
        echo '<form method="post" class="ateam-rentals-form">';
        wp_nonce_field('ateam_rentals_action', 'ateam_rentals_nonce');
        echo '<input type="hidden" name="ateam_rentals_action" value="save_price">';
        echo '<input type="hidden" name="post_id" value="' . esc_attr($editing ? (string) $editing->ID : '0') . '">';
        $this->render_text_field('Pricing title', 'title', $editing ? $editing->post_title : '');
        $this->render_text_field('Price', 'price', $editing ? (string) get_post_meta($editing->ID, self::META_PRICE, true) : '');
        $this->render_text_field('Currency', 'currency', $editing ? (string) get_post_meta($editing->ID, self::META_CURRENCY, true) : '$');
        $this->render_text_field('Unit label', 'unit', $editing ? (string) get_post_meta($editing->ID, self::META_UNIT, true) : '');
        $this->render_textarea_field('Short description', 'description', $editing ? $editing->post_content : '');
        $this->render_media_field('Background image', 'bg_image_id', $editing ? (int) get_post_meta($editing->ID, self::META_BG_IMAGE_ID, true) : 0);
        $this->render_icon_fields(
            $editing ? (string) get_post_meta($editing->ID, self::META_ICON_LIBRARY, true) : 'fontawesome',
            $editing ? (string) get_post_meta($editing->ID, self::META_ICON_VALUE, true) : 'fas fa-stopwatch',
            $editing ? (int) get_post_meta($editing->ID, self::META_ICON_IMAGE_ID, true) : 0
        );
        $this->render_number_field('Display order', 'display_order', $editing ? (int) get_post_meta($editing->ID, self::META_ORDER, true) : 0);
        $this->render_checkbox_field('Active', 'active', !$editing || '1' === get_post_meta($editing->ID, self::META_ACTIVE, true));
        submit_button($editing ? 'Update Pricing Card' : 'Add Pricing Card');
        echo '</form></div>';
        $this->render_records_table('rental_price', $records, 'rentals-builder-pricing', true);
        echo '</div></div>';
    }

    public function render_categories_page(): void {
        $status = isset($_GET['status']) && 'inactive' === $_GET['status'] ? 'inactive' : 'active';
        $editing = isset($_GET['edit']) ? get_post((int) $_GET['edit']) : null;
        $records = $this->get_records('rental_category', 'active' === $status);
        echo '<div class="wrap ateam-rentals-wrap"><h1>Categories</h1>';
        $this->render_notice();
        $this->admin_tabs('rentals-builder-categories', 'rental_category');
        echo '<div class="ateam-rentals-admin-grid">';
        echo '<div class="ateam-rentals-panel">';
        echo '<h2>' . ($editing ? 'Edit Category' : 'Add New Category') . '</h2>';
        echo '<form method="post" class="ateam-rentals-form">';
        wp_nonce_field('ateam_rentals_action', 'ateam_rentals_nonce');
        echo '<input type="hidden" name="ateam_rentals_action" value="save_category">';
        echo '<input type="hidden" name="post_id" value="' . esc_attr($editing ? (string) $editing->ID : '0') . '">';
        $this->render_text_field('Category name', 'title', $editing ? $editing->post_title : '');
        $this->render_text_field('Section heading override', 'section_heading', $editing ? (string) get_post_meta($editing->ID, self::META_SECTION_HEADING, true) : '');
        $this->render_textarea_field('Introductory text', 'intro', $editing ? (string) get_post_meta($editing->ID, self::META_INTRO, true) : '');
        $this->render_media_field('Background image', 'bg_image_id', $editing ? (int) get_post_meta($editing->ID, self::META_BG_IMAGE_ID, true) : 0);
        $this->render_number_field('Display order', 'display_order', $editing ? (int) get_post_meta($editing->ID, self::META_ORDER, true) : 0);
        $this->render_checkbox_field('Active', 'active', !$editing || '1' === get_post_meta($editing->ID, self::META_ACTIVE, true));
        submit_button($editing ? 'Update Category' : 'Add Category');
        echo '</form></div>';
        $this->render_records_table('rental_category', $records, 'rentals-builder-categories', true);
        echo '</div></div>';
    }

    public function render_items_page(): void {
        $status = isset($_GET['status']) && 'inactive' === $_GET['status'] ? 'inactive' : 'active';
        $editing = isset($_GET['edit']) ? get_post((int) $_GET['edit']) : null;
        $records = $this->get_records('rental_item', 'active' === $status);
        $categories = $this->get_records('rental_category', true);
        echo '<div class="wrap ateam-rentals-wrap"><h1>Items</h1>';
        $this->render_notice();
        $this->admin_tabs('rentals-builder-items', 'rental_item');
        echo '<div class="ateam-rentals-admin-grid">';
        echo '<div class="ateam-rentals-panel">';
        echo '<h2>' . ($editing ? 'Edit Item' : 'Add New Item') . '</h2>';
        echo '<form method="post" class="ateam-rentals-form">';
        wp_nonce_field('ateam_rentals_action', 'ateam_rentals_nonce');
        echo '<input type="hidden" name="ateam_rentals_action" value="save_item">';
        echo '<input type="hidden" name="post_id" value="' . esc_attr($editing ? (string) $editing->ID : '0') . '">';
        $this->render_text_field('Item title', 'title', $editing ? $editing->post_title : '');
        echo '<label><span>Category</span><select name="category_id">';
        foreach ($categories as $category) {
            $selected = $editing && (int) get_post_meta($editing->ID, self::META_CATEGORY_ID, true) === (int) $category->ID ? ' selected' : '';
            echo '<option value="' . esc_attr((string) $category->ID) . '"' . $selected . '>' . esc_html($category->post_title) . '</option>';
        }
        echo '</select></label>';
        $this->render_textarea_field('Description', 'description', $editing ? (string) get_post_meta($editing->ID, self::META_DESCRIPTION, true) : '');
        $this->render_textarea_field('Sub-items (one per line)', 'sub_items', $editing ? implode("\n", (array) get_post_meta($editing->ID, self::META_SUB_ITEMS, true)) : '');
        $this->render_media_field('Background image', 'bg_image_id', $editing ? (int) get_post_meta($editing->ID, self::META_BG_IMAGE_ID, true) : 0);
        $this->render_icon_fields(
            $editing ? (string) get_post_meta($editing->ID, self::META_ICON_LIBRARY, true) : 'fontawesome',
            $editing ? (string) get_post_meta($editing->ID, self::META_ICON_VALUE, true) : 'fas fa-star',
            $editing ? (int) get_post_meta($editing->ID, self::META_ICON_IMAGE_ID, true) : 0
        );
        $this->render_number_field('Display order', 'display_order', $editing ? (int) get_post_meta($editing->ID, self::META_ORDER, true) : 0);
        $this->render_checkbox_field('Active', 'active', !$editing || '1' === get_post_meta($editing->ID, self::META_ACTIVE, true));
        submit_button($editing ? 'Update Item' : 'Add Item');
        echo '</form></div>';
        $this->render_records_table('rental_item', $records, 'rentals-builder-items', true);
        echo '</div></div>';
    }

    public function render_icons_page(): void {
        echo '<div class="wrap ateam-rentals-wrap"><h1>Icon Library</h1>';
        echo '<div class="ateam-rentals-panel"><p>Supported libraries reuse the site stack already present on production.</p>';
        echo '<ul class="ateam-rentals-icon-list">';
        echo '<li><strong>Font Awesome</strong>: example <code>fas fa-drum</code>, <code>fas fa-guitar</code>, <code>fas fa-clock</code></li>';
        echo '<li><strong>Bootstrap Icons</strong>: example <code>bootstrap-icons bi-music-note-list</code>, <code>bootstrap-icons bi-mic</code></li>';
        echo '<li><strong>Case Icon</strong>: example <code>caseicon caseicon-next</code> when needed</li>';
        echo '</ul></div></div>';
    }

    private function render_records_table(string $post_type, array $records, string $page, bool $sortable): void {
        echo '<div class="ateam-rentals-panel"><h2>Records</h2>';
        echo '<div class="ateam-rentals-inline-notice" aria-live="polite"></div>';
        if ($sortable) {
            echo '<p class="description">Drag and drop rows, then click Save Order.</p>';
        }
        echo '<table class="widefat striped"><thead><tr><th>Title</th><th>Status</th><th>Order</th><th>Actions</th></tr></thead>';
        echo '<tbody class="' . ($sortable ? 'ateam-rentals-sortable' : '') . '" data-post-type="' . esc_attr($post_type) . '">';
        foreach ($records as $record) {
            $actions = array(
                '<a href="' . esc_url(add_query_arg(array('page' => $page, 'edit' => $record->ID), admin_url('admin.php'))) . '">Edit</a>',
            );
            $is_active = '1' === get_post_meta($record->ID, self::META_ACTIVE, true);
            $actions[] = $this->action_form($page, $record->ID, $is_active ? 'deactivate_record' : 'activate_record', $is_active ? 'Deactivate' : 'Reactivate');
            $actions[] = $this->action_form($page, $record->ID, 'delete_record', 'Delete Permanently', true);
            echo '<tr data-post-id="' . esc_attr((string) $record->ID) . '">';
            echo '<td><strong>' . esc_html($record->post_title) . '</strong></td>';
            echo '<td>' . esc_html($is_active ? 'Active' : 'Inactive') . '</td>';
            echo '<td>' . esc_html((string) get_post_meta($record->ID, self::META_ORDER, true)) . '</td>';
            echo '<td>' . implode(' | ', $actions) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        if ($sortable) {
            echo '<p><button type="button" class="button button-secondary ateam-rentals-save-order">Save Order</button></p>';
        }
        echo '</div>';
    }

    private function action_form(string $page, int $post_id, string $action, string $label, bool $confirm = false): string {
        $html = '<form method="post" class="ateam-rentals-inline-form"' . ($confirm ? ' onsubmit="return confirm(\'Delete this record permanently?\');"' : '') . '>';
        $html .= wp_nonce_field('ateam_rentals_action', 'ateam_rentals_nonce', true, false);
        $html .= '<input type="hidden" name="ateam_rentals_action" value="' . esc_attr($action) . '">';
        $html .= '<input type="hidden" name="post_id" value="' . esc_attr((string) $post_id) . '">';
        $html .= '<input type="hidden" name="page" value="' . esc_attr($page) . '">';
        $html .= '<button type="submit" class="button-link">' . esc_html($label) . '</button></form>';
        return $html;
    }

    private function render_notice(): void {
        if (empty($_GET['rentals_notice'])) {
            return;
        }
        $message = 'updated' === $_GET['rentals_notice'] ? 'Changes saved.' : 'Saved successfully.';
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
    }

    private function render_text_field(string $label, string $name, string $value): void {
        echo '<label><span>' . esc_html($label) . '</span><input type="text" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '"></label>';
    }

    private function render_url_field(string $label, string $name, string $value): void {
        echo '<label><span>' . esc_html($label) . '</span><input type="url" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '"></label>';
    }

    private function render_textarea_field(string $label, string $name, string $value): void {
        echo '<label><span>' . esc_html($label) . '</span><textarea name="' . esc_attr($name) . '" rows="4">' . esc_textarea($value) . '</textarea></label>';
    }

    private function render_number_field(string $label, string $name, int $value): void {
        echo '<label><span>' . esc_html($label) . '</span><input type="number" name="' . esc_attr($name) . '" value="' . esc_attr((string) $value) . '" min="0" step="1"></label>';
    }

    private function render_checkbox_field(string $label, string $name, bool $checked): void {
        echo '<label class="ateam-rentals-checkbox"><input type="checkbox" name="' . esc_attr($name) . '" value="1"' . checked($checked, true, false) . '> <span>' . esc_html($label) . '</span></label>';
    }

    private function render_media_field(string $label, string $name, int $attachment_id): void {
        $preview = $attachment_id ? wp_get_attachment_image_url($attachment_id, 'medium') : '';
        echo '<div class="ateam-rentals-media-field"><span>' . esc_html($label) . '</span>';
        echo '<input type="hidden" name="' . esc_attr($name) . '" value="' . esc_attr((string) $attachment_id) . '" class="ateam-rentals-media-input">';
        echo '<div class="ateam-rentals-media-preview">' . ($preview ? '<img src="' . esc_url($preview) . '" alt="">' : '<em>No image selected.</em>') . '</div>';
        echo '<button type="button" class="button ateam-rentals-media-button">Choose Image</button></div>';
    }

    private function render_icon_fields(string $library, string $value, int $image_id = 0): void {
        if ('image' === $library && !$image_id) {
            $image_id = absint($value);
        }
        echo '<div class="ateam-rentals-icon-fields">';
        echo '<label><span>Icon library</span><select name="icon_library">';
        $libraries = array('fontawesome' => 'Font Awesome', 'bootstrap' => 'Bootstrap Icons', 'caseicon' => 'Case Icon', 'image' => 'Media Library Image');
        foreach ($libraries as $library_key => $library_label) {
            echo '<option value="' . esc_attr($library_key) . '"' . selected($library, $library_key, false) . '>' . esc_html($library_label) . '</option>';
        }
        echo '</select></label>';
        $this->render_text_field('Icon class', 'icon_value', 'image' === $library ? '' : $value);
        $this->render_media_field('Icon image', 'icon_image_id', $image_id);
        echo '</div>';
    }

    private function is_rentals_page(): bool {
        if (!function_exists('is_page')) {
            return false;
        }
        $page = get_page_by_path(self::RENTALS_SLUG);
        return $page ? is_page((int) $page->ID) : is_page(self::RENTALS_SLUG);
    }

    public function get_frontend_payload(): array {
        $settings = $this->get_settings();
        $categories = $this->get_records('rental_category', true);
        $items = $this->get_records('rental_item', true);
        $prices = $this->get_records('rental_price', true);
        $item_groups = array();
        foreach ($items as $item) {
            $item_groups[(int) get_post_meta($item->ID, self::META_CATEGORY_ID, true)][] = $item;
        }
        return array(
            'settings' => $settings,
            'prices' => $prices,
            'categories' => $categories,
            'item_groups' => $item_groups,
        );
    }

    public function render_frontend(): void {
        $payload = $this->get_frontend_payload();
        $settings = $payload['settings'];
        if ('1' !== $settings[self::META_RENDER_ENABLED]) {
            while (have_posts()) {
                the_post();
                the_content();
            }
            return;
        }

        $hero_image = $settings['hero_bg_image_id'] ? wp_get_attachment_image_url((int) $settings['hero_bg_image_id'], 'full') : '';
        $hero_spacer = $settings['hero_spacer_image_id'] ? wp_get_attachment_image((int) $settings['hero_spacer_image_id'], 'full', false, array('class' => 'ateam-rentals-divider-image', 'alt' => '')) : '';
        $cta_image = $settings['cta_bg_image_id'] ? wp_get_attachment_image_url((int) $settings['cta_bg_image_id'], 'full') : '';
        echo '<div class="ateam-rentals-page">';
        $hero_styles = array();
        if ($hero_image) {
            $hero_styles[] = '--hero-image:url(' . esc_url($hero_image) . ')';
        }
        $hero_styles[] = '--hero-intro-spacing:' . (int) $settings['hero_intro_spacing'] . 'px';
        echo '<section class="ateam-rentals-hero" style="' . esc_attr(implode(';', $hero_styles)) . '">';
        echo '<div class="ateam-rentals-shell">';
        echo '<p class="ateam-rentals-breadcrumb">' . esc_html($settings['hero_eyebrow']) . ' <span>&rarr;</span> ' . esc_html($settings['breadcrumb_label']) . '</p>';
        echo '<h1>' . esc_html($settings['hero_title']) . '</h1>';
        if ($hero_spacer) {
            echo '<div class="ateam-rentals-divider ateam-rentals-divider--image">' . $hero_spacer . '</div>';
        } else {
            echo '<div class="ateam-rentals-divider"><span></span></div>';
        }
        echo '<div class="ateam-rentals-hero-intro">' . wp_kses_post(wpautop($settings['hero_intro'])) . '</div>';
        if ($settings['enquiry_label'] && $settings['enquiry_url']) {
            echo '<p class="ateam-rentals-hero-actions"><a class="ateam-rentals-button" href="' . esc_url($settings['enquiry_url']) . '">' . esc_html($settings['enquiry_label']) . '</a></p>';
        }
        echo '</div></section>';

        echo '<section class="ateam-rentals-section ateam-rentals-pricing"><div class="ateam-rentals-shell">';
        echo '<h2>' . esc_html($settings['pricing_title']) . '</h2>';
        if ($settings['pricing_intro']) {
            echo '<div class="ateam-rentals-section-intro">' . wp_kses_post(wpautop($settings['pricing_intro'])) . '</div>';
        }
        echo '<div class="ateam-rentals-price-grid">';
        foreach ($payload['prices'] as $price) {
            $price_bg_id = (int) get_post_meta($price->ID, self::META_BG_IMAGE_ID, true);
            $price_bg_url = $price_bg_id ? wp_get_attachment_image_url($price_bg_id, 'large') : '';
            echo '<article class="ateam-rentals-price-card"' . ($price_bg_url ? ' style="--price-card-image:url(' . esc_url($price_bg_url) . ')"' : '') . '>';
            echo '<div class="ateam-rentals-price-card__inner">';
            echo '<div class="ateam-rentals-price-icon"><span class="ateam-rentals-price-icon__disc">' . $this->render_icon_html(
                (string) get_post_meta($price->ID, self::META_ICON_LIBRARY, true),
                (string) get_post_meta($price->ID, self::META_ICON_VALUE, true),
                (int) get_post_meta($price->ID, self::META_ICON_IMAGE_ID, true)
            ) . '</span></div>';
            echo '<div class="ateam-rentals-price-copy">';
            echo '<p class="ateam-rentals-card-kicker">' . esc_html($price->post_title) . '</p>';
            echo '<h3>' . esc_html((string) get_post_meta($price->ID, self::META_CURRENCY, true) . (string) get_post_meta($price->ID, self::META_PRICE, true)) . '</h3>';
            $unit = (string) get_post_meta($price->ID, self::META_UNIT, true);
            if ($unit) {
                echo '<p class="ateam-rentals-price-unit">' . esc_html($unit) . '</p>';
            }
            echo '<p class="ateam-rentals-price-description">' . esc_html($price->post_content) . '</p>';
            echo '</div>';
            echo '</div>';
            echo '</article>';
        }
        echo '</div></div></section>';

        echo '<section class="ateam-rentals-section ateam-rentals-categories"><div class="ateam-rentals-shell">';
        if ($settings['categories_title']) {
            echo '<h2>' . esc_html($settings['categories_title']) . '</h2>';
        }
        if ($settings['categories_intro']) {
            echo '<div class="ateam-rentals-section-intro">' . wp_kses_post(wpautop($settings['categories_intro'])) . '</div>';
        }
        foreach ($payload['categories'] as $category) {
            $bg_id = (int) get_post_meta($category->ID, self::META_BG_IMAGE_ID, true);
            $bg_url = $bg_id ? wp_get_attachment_image_url($bg_id, 'large') : '';
            echo '<div class="ateam-rentals-category-block">';
            echo '<h2>' . esc_html(get_post_meta($category->ID, self::META_SECTION_HEADING, true) ?: $category->post_title) . '</h2>';
            if (get_post_meta($category->ID, self::META_INTRO, true)) {
                echo '<div class="ateam-rentals-section-intro">' . wp_kses_post(wpautop((string) get_post_meta($category->ID, self::META_INTRO, true))) . '</div>';
            }
            echo '<div class="ateam-rentals-item-grid">';
            foreach ($payload['item_groups'][(int) $category->ID] ?? array() as $item) {
                $item_bg_id = (int) get_post_meta($item->ID, self::META_BG_IMAGE_ID, true);
                $image_url = $item_bg_id ? wp_get_attachment_image_url($item_bg_id, 'large') : $bg_url;
                echo '<article class="ateam-rentals-item-card" style="' . esc_attr($image_url ? '--card-image:url(' . esc_url($image_url) . ')' : '') . '">';
                echo '<div class="ateam-rentals-item-card__overlay"></div>';
                echo '<div class="ateam-rentals-item-card__content">';
                echo '<div class="ateam-rentals-item-card__layout">';
                echo '<div class="ateam-rentals-item-icon">' . $this->render_icon_html(
                    (string) get_post_meta($item->ID, self::META_ICON_LIBRARY, true),
                    (string) get_post_meta($item->ID, self::META_ICON_VALUE, true),
                    (int) get_post_meta($item->ID, self::META_ICON_IMAGE_ID, true)
                ) . '</div>';
                echo '<div class="ateam-rentals-item-copy">';
                echo '<h3>' . esc_html($item->post_title) . '</h3>';
                $description = (string) get_post_meta($item->ID, self::META_DESCRIPTION, true);
                if ($description) {
                    echo '<p class="ateam-rentals-item-description">' . esc_html($description) . '</p>';
                }
                $sub_items = (array) get_post_meta($item->ID, self::META_SUB_ITEMS, true);
                if ($sub_items) {
                    echo '<ul>';
                    foreach ($sub_items as $sub_item) {
                        echo '<li>' . esc_html((string) $sub_item) . '</li>';
                    }
                    echo '</ul>';
                }
                echo '</div>';
                echo '</div>';
                echo '</div></article>';
            }
            echo '</div></div>';
        }
        echo '</div></section>';

        if ($settings['cta_title'] || $settings['cta_text']) {
            echo '<section class="ateam-rentals-cta" style="' . esc_attr($cta_image ? '--cta-image:url(' . esc_url($cta_image) . ')' : '') . '"><div class="ateam-rentals-shell">';
            if ($settings['cta_title']) {
                echo '<h2>' . esc_html($settings['cta_title']) . '</h2>';
            }
            if ($settings['cta_text']) {
                echo wp_kses_post(wpautop($settings['cta_text']));
            }
            if ($settings['cta_button_label'] && $settings['cta_button_url']) {
                echo '<p><a class="ateam-rentals-button" href="' . esc_url($settings['cta_button_url']) . '">' . esc_html($settings['cta_button_label']) . '</a></p>';
            }
            echo '</div></section>';
        }

        echo '</div>';
    }

    private function render_icon_html(string $library, string $value, int $image_id = 0): string {
        if (!$value && !$image_id) {
            return '';
        }
        if ('image' === $library) {
            $image_id = $image_id ?: absint($value);
            if (!$image_id) {
                return '';
            }
            $image = wp_get_attachment_image($image_id, 'medium', false, array(
                'class' => 'ateam-rentals-icon-image',
                'alt' => '',
            ));
            return $image ?: '';
        }
        $class = esc_attr($value);
        return '<i class="' . $class . '" aria-hidden="true"></i>';
    }
}

$GLOBALS['ateam_rentals_builder'] = new ATeam_Rentals_Builder();
$GLOBALS['ateam_rentals_builder']->hooks();
