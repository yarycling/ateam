<?php
/**
 * Editable Bandroom page.
 */

if (!defined('ABSPATH')) {
    exit;
}

final class ATeam_Bandroom_Builder {
    private const OPTION_KEY = 'ateam_bandroom_page_settings';
    private const PAGE_SLUG = 'bandroom';
    private array $page_hooks = array();

    public function hooks(): void {
        add_action('admin_menu', array($this, 'register_admin_menu'));
        add_action('admin_init', array($this, 'handle_save'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('wp_head', array($this, 'print_frontend_style'), 99);
        add_filter('template_include', array($this, 'override_template'), 99);
    }

    public function register_admin_menu(): void {
        $this->page_hooks[] = add_menu_page('Bandroom Builder', 'Bandroom Builder', 'edit_pages', 'bandroom-builder', array($this, 'render_settings_page'), 'dashicons-microphone', 60);
    }

    public function enqueue_admin_assets(string $hook): void {
        if (!in_array($hook, $this->page_hooks, true)) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_style('ateam-rentals-admin', STOTAGE_CHILD_URI . '/assets/css/rentals-admin.css', array(), STOTAGE_CHILD_VERSION);
        wp_enqueue_script('ateam-rentals-admin', STOTAGE_CHILD_URI . '/assets/js/rentals-admin.js', array('jquery'), STOTAGE_CHILD_VERSION, true);
        wp_localize_script('ateam-rentals-admin', 'ATeamRentalsAdmin', array(
            'chooseImage' => 'Choose image',
            'useImage' => 'Use image',
        ));
    }

    public function enqueue_frontend_assets(): void {
        wp_enqueue_style('dashicons');
        foreach (array('elementor-icons-fa-solid', 'elementor-icons-fa-regular') as $handle) {
            if (wp_style_is($handle, 'registered')) {
                wp_enqueue_style($handle);
            }
        }
        wp_enqueue_style('ateam-bandroom-frontend', STOTAGE_CHILD_URI . '/assets/css/bandroom-frontend.css', array('stotage-child-style'), STOTAGE_CHILD_VERSION);
    }

    public function print_frontend_style(): void {
        if (!$this->is_bandroom_page()) {
            return;
        }
        echo '<link rel="stylesheet" id="ateam-bandroom-frontend-css" href="' . esc_url(STOTAGE_CHILD_URI . '/assets/css/bandroom-frontend.css') . '?ver=' . esc_attr(STOTAGE_CHILD_VERSION) . '" media="all">' . "\n";
    }

    public function override_template(string $template): string {
        if (!$this->is_bandroom_page()) {
            return $template;
        }
        $custom = STOTAGE_CHILD_DIR . '/templates/page-bandroom.php';
        return file_exists($custom) ? $custom : $template;
    }

    private function defaults(): array {
        return array(
            'enabled' => '1',
            'breadcrumb_parent' => 'Home',
            'breadcrumb_current' => 'Bandroom',
            'hero_title' => 'A Team Bandroom',
            'hero_tags' => 'Rental | Rehearsal | Recording',
            'hero_text' => 'A professional space built for musicians, creators and bands to rehearse, record and create without limits.',
            'hero_bg_image_id' => 0,
            'price_one' => '500',
            'price_one_label' => 'First Hour',
            'price_two' => '300',
            'price_two_label' => 'Every Additional Hour',
            'pricing_note' => 'Flexible hourly rates for all your rehearsal and recording needs.',
            'booking_label' => 'Book a Session',
            'booking_url' => 'mailto:info@ateambandtt.com',
            'cta_title' => 'Ready to create?',
            'cta_text' => 'Book your session today and experience a space designed for great music.',
            'cta_label' => 'Book a Session',
            'cta_url' => 'mailto:info@ateambandtt.com',
            'cta_bg_image_id' => 0,
            'features' => array(
                array('title' => 'Backline', 'text' => 'Drum Kit  •  LP Congas  •  LP Bongos\nBass Amp  •  Guitar & Keyboard Stands\nPercussion', 'icon' => 'fas fa-drum', 'image_id' => 0),
                array('title' => 'Sound', 'text' => 'Behringer WING  •  Midas DP48 IEMs\nJBL PRX  •  Sennheiser G4  •  Shure Drum Mics\nDI Boxes  •  Playback', 'icon' => 'fas fa-wave-square', 'image_id' => 0),
                array('title' => 'Production + Visuals', 'text' => 'Dedicated Production Room\nIntegrated TV Screens\nFull Facility View & Control', 'icon' => 'fas fa-tv', 'image_id' => 0),
                array('title' => 'Recording Studio', 'text' => 'iMac  •  Logic Pro  •  Ableton Live  •  Apollo Twin\n$300/hr Studio Only\n$500/hr With House Engineer', 'icon' => 'fas fa-microphone-alt', 'image_id' => 0),
                array('title' => 'Amenities', 'text' => 'Kitchen  •  Male/Female Toilets\nSecure Parking for up to 5 Vehicles', 'icon' => 'fas fa-users', 'image_id' => 0),
            ),
        );
    }

    public function get_settings(): array {
        $settings = wp_parse_args((array) get_option(self::OPTION_KEY, array()), $this->defaults());
        $settings['features'] = array_values(array_pad((array) $settings['features'], 5, array()));
        foreach ($settings['features'] as $index => $feature) {
            $settings['features'][$index] = wp_parse_args((array) $feature, $this->defaults()['features'][$index]);
        }
        return $settings;
    }

    public function handle_save(): void {
        if (!is_admin() || !current_user_can('edit_pages') || 'POST' !== ($_SERVER['REQUEST_METHOD'] ?? '') || empty($_POST['ateam_bandroom_action'])) {
            return;
        }
        check_admin_referer('ateam_bandroom_action', 'ateam_bandroom_nonce');
        $source = wp_unslash($_POST);
        $features = array();
        foreach (range(0, 4) as $index) {
            $features[] = array(
                'title' => sanitize_text_field($source['feature_title'][$index] ?? ''),
                'text' => sanitize_textarea_field($source['feature_text'][$index] ?? ''),
                'icon' => sanitize_text_field($source['feature_icon'][$index] ?? ''),
                'image_id' => absint($source['feature_image_id'][$index] ?? 0),
            );
        }
        $settings = array(
            'enabled' => empty($source['enabled']) ? '0' : '1',
            'breadcrumb_parent' => sanitize_text_field($source['breadcrumb_parent'] ?? ''),
            'breadcrumb_current' => sanitize_text_field($source['breadcrumb_current'] ?? ''),
            'hero_title' => sanitize_text_field($source['hero_title'] ?? ''),
            'hero_tags' => sanitize_text_field($source['hero_tags'] ?? ''),
            'hero_text' => sanitize_textarea_field($source['hero_text'] ?? ''),
            'hero_bg_image_id' => absint($source['hero_bg_image_id'] ?? 0),
            'price_one' => sanitize_text_field($source['price_one'] ?? ''),
            'price_one_label' => sanitize_text_field($source['price_one_label'] ?? ''),
            'price_two' => sanitize_text_field($source['price_two'] ?? ''),
            'price_two_label' => sanitize_text_field($source['price_two_label'] ?? ''),
            'pricing_note' => sanitize_textarea_field($source['pricing_note'] ?? ''),
            'booking_label' => sanitize_text_field($source['booking_label'] ?? ''),
            'booking_url' => $this->link($source['booking_url'] ?? ''),
            'cta_title' => sanitize_text_field($source['cta_title'] ?? ''),
            'cta_text' => sanitize_textarea_field($source['cta_text'] ?? ''),
            'cta_label' => sanitize_text_field($source['cta_label'] ?? ''),
            'cta_url' => $this->link($source['cta_url'] ?? ''),
            'cta_bg_image_id' => absint($source['cta_bg_image_id'] ?? 0),
            'features' => $features,
        );
        update_option(self::OPTION_KEY, $settings);
        wp_safe_redirect(add_query_arg(array('page' => 'bandroom-builder', 'bandroom_notice' => 'saved'), admin_url('admin.php')));
        exit;
    }

    public function render_settings_page(): void {
        $s = $this->get_settings();
        echo '<div class="wrap ateam-rentals-wrap"><h1>Bandroom Builder</h1>';
        if (isset($_GET['bandroom_notice'])) { echo '<div class="notice notice-success is-dismissible"><p>Bandroom settings saved.</p></div>'; }
        echo '<p>Manage every visible piece of the Bandroom page here. Images open from the WordPress Media Library.</p><form method="post" class="ateam-rentals-form">';
        wp_nonce_field('ateam_bandroom_action', 'ateam_bandroom_nonce');
        echo '<input type="hidden" name="ateam_bandroom_action" value="save">';
        $this->checkbox('Enable custom Bandroom page', 'enabled', '1' === $s['enabled']);
        echo '<div class="ateam-rentals-admin-grid"><div class="ateam-rentals-panel"><h2>Hero</h2>';
        $this->text('Breadcrumb parent', 'breadcrumb_parent', $s['breadcrumb_parent']); $this->text('Breadcrumb current page', 'breadcrumb_current', $s['breadcrumb_current']);
        $this->text('Hero title', 'hero_title', $s['hero_title']); $this->text('Hero tags (separate with |)', 'hero_tags', $s['hero_tags']); $this->textarea('Hero description', 'hero_text', $s['hero_text']); $this->media('Hero background image', 'hero_bg_image_id', (int) $s['hero_bg_image_id']);
        echo '</div><div class="ateam-rentals-panel"><h2>Pricing and booking</h2>';
        $this->text('First rate', 'price_one', $s['price_one']); $this->text('First rate label', 'price_one_label', $s['price_one_label']); $this->text('Additional rate', 'price_two', $s['price_two']); $this->text('Additional rate label', 'price_two_label', $s['price_two_label']); $this->textarea('Pricing note', 'pricing_note', $s['pricing_note']); $this->text('Booking button label', 'booking_label', $s['booking_label']); $this->url('Booking button URL', 'booking_url', $s['booking_url']);
        echo '</div><div class="ateam-rentals-panel"><h2>Bottom call to action</h2>';
        $this->text('CTA title', 'cta_title', $s['cta_title']); $this->textarea('CTA text', 'cta_text', $s['cta_text']); $this->text('CTA button label', 'cta_label', $s['cta_label']); $this->url('CTA button URL', 'cta_url', $s['cta_url']); $this->media('CTA background image', 'cta_bg_image_id', (int) $s['cta_bg_image_id']);
        echo '</div></div><h2>Feature rows</h2><div class="ateam-rentals-admin-grid">';
        foreach ($s['features'] as $index => $feature) {
            echo '<div class="ateam-rentals-panel"><h2>Row ' . esc_html((string) ($index + 1)) . '</h2>';
            $this->text('Title', 'feature_title[' . $index . ']', $feature['title']); $this->textarea('Description (new line creates a new line)', 'feature_text[' . $index . ']', $feature['text']); $this->text('Font Awesome icon class', 'feature_icon[' . $index . ']', $feature['icon']); $this->media('Feature image', 'feature_image_id[' . $index . ']', (int) $feature['image_id']); echo '</div>';
        }
        echo '</div>'; submit_button('Save Bandroom Page'); echo '</form></div>';
    }

    private function text(string $label, string $name, string $value): void { echo '<label><span>' . esc_html($label) . '</span><input type="text" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '"></label>'; }
    private function url(string $label, string $name, string $value): void { echo '<label><span>' . esc_html($label) . '</span><input type="text" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" placeholder="https://example.com or #ateam-bandroom-booking"></label>'; }
    private function link($value): string { $value = trim((string) $value); return str_starts_with($value, '#') ? sanitize_text_field($value) : esc_url_raw($value); }
    private function textarea(string $label, string $name, string $value): void { echo '<label><span>' . esc_html($label) . '</span><textarea name="' . esc_attr($name) . '" rows="4">' . esc_textarea($value) . '</textarea></label>'; }
    private function checkbox(string $label, string $name, bool $checked): void { echo '<label class="ateam-rentals-checkbox"><input type="checkbox" name="' . esc_attr($name) . '" value="1"' . checked($checked, true, false) . '><span>' . esc_html($label) . '</span></label>'; }
    private function media(string $label, string $name, int $id): void {
        $preview = $id ? wp_get_attachment_image_url($id, 'medium') : '';
        echo '<div class="ateam-rentals-media-field"><span>' . esc_html($label) . '</span><input class="ateam-rentals-media-input" type="hidden" name="' . esc_attr($name) . '" value="' . esc_attr((string) $id) . '"><div class="ateam-rentals-media-preview">' . ($preview ? '<img src="' . esc_url($preview) . '" alt="">' : '<em>No image selected.</em>') . '</div><button type="button" class="button ateam-rentals-media-button">Choose Image</button></div>';
    }

    private function is_bandroom_page(): bool {
        return function_exists('is_page') && is_page(self::PAGE_SLUG);
    }

    private function image_url(int $id): string { return $id ? (string) wp_get_attachment_image_url($id, 'full') : ''; }

    public function render_frontend(): void {
        $s = $this->get_settings();
        if ('1' !== $s['enabled']) { the_content(); return; }
        $hero = $this->image_url((int) $s['hero_bg_image_id']);
        echo '<div class="ateam-bandroom-page">';
        echo '<section class="ateam-bandroom-hero"' . ($hero ? ' style="--bandroom-hero:url(' . esc_url($hero) . ')"' : '') . '><div class="ateam-bandroom-wrap">';
        echo '<div class="ateam-bandroom-breadcrumb"><span>' . esc_html($s['breadcrumb_parent']) . '</span><b>&gt;</b> ' . esc_html($s['breadcrumb_current']) . '</div>';
        echo '<h1>' . nl2br(esc_html($s['hero_title'])) . '</h1><p class="ateam-bandroom-tags">' . esc_html(str_replace('|', '  •  ', $s['hero_tags'])) . '</p><p class="ateam-bandroom-hero-text">' . nl2br(esc_html($s['hero_text'])) . '</p>';
        echo '<div class="ateam-bandroom-pricing"><div class="ateam-bandroom-dollar">$</div><div class="ateam-bandroom-rate"><strong>$' . esc_html($s['price_one']) . '</strong><span>' . esc_html($s['price_one_label']) . '</span></div><div class="ateam-bandroom-divider"></div><div class="ateam-bandroom-rate"><strong>$' . esc_html($s['price_two']) . '</strong><span>' . esc_html($s['price_two_label']) . '</span></div><div class="ateam-bandroom-booking"><a href="' . esc_url($s['booking_url']) . '"><i class="far fa-calendar-alt"></i> ' . esc_html($s['booking_label']) . '</a><p>' . esc_html($s['pricing_note']) . '</p></div></div>';
        echo '</div></section><section class="ateam-bandroom-features"><div class="ateam-bandroom-wrap"><div class="ateam-bandroom-feature-list">';
        foreach ($s['features'] as $feature) {
            $image = $this->image_url((int) $feature['image_id']);
            echo '<article class="ateam-bandroom-feature"><div class="ateam-bandroom-feature-copy"><i class="' . esc_attr($feature['icon']) . '" aria-hidden="true"></i><div><h2>' . esc_html($feature['title']) . '</h2><p>' . nl2br(esc_html($feature['text'])) . '</p></div></div><div class="ateam-bandroom-feature-image"' . ($image ? ' style="background-image:url(' . esc_url($image) . ')"' : '') . '></div></article>';
        }
        $cta = $this->image_url((int) $s['cta_bg_image_id']);
        echo '</div><section class="ateam-bandroom-cta"' . ($cta ? ' style="--bandroom-cta:url(' . esc_url($cta) . ')"' : '') . '><div><h2>' . esc_html($s['cta_title']) . '</h2><p>' . esc_html($s['cta_text']) . '</p></div><a href="' . esc_url($s['cta_url']) . '">' . esc_html($s['cta_label']) . ' <span>&rarr;</span></a></section></div></section></div>';
    }
}

$GLOBALS['ateam_bandroom_builder'] = new ATeam_Bandroom_Builder();
$GLOBALS['ateam_bandroom_builder']->hooks();
