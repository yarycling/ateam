<?php
/**
 * Plugin Name: A Team Popup Ads
 * Description: Event, video, and ad popups with page targeting, media uploads, sliders, and session frequency controls.
 * Version: 1.0.0
 * Author: A Team Band
 * Text Domain: ateam-popup-ads
 */

if (!defined('ABSPATH')) {
    exit;
}

final class ATeam_Popup_Ads {
    private const POST_TYPE = 'ateam_popup_ad';
    private const NONCE_ACTION = 'ateam_popup_ads_action';
    private const NONCE_NAME = 'ateam_popup_ads_nonce';
    private const META_PREFIX = '_ateam_popup_';
    private array $page_hooks = array();

    public function hooks(): void {
        add_action('init', array($this, 'register_post_type'));
        add_action('admin_menu', array($this, 'register_admin_menu'));
        add_action('admin_init', array($this, 'handle_admin_actions'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('wp_footer', array($this, 'render_frontend'));
    }

    public function register_post_type(): void {
        register_post_type(self::POST_TYPE, array(
            'labels' => array(
                'name' => 'Popup Ads',
                'singular_name' => 'Popup Ad',
            ),
            'public' => false,
            'show_ui' => false,
            'show_in_menu' => false,
            'supports' => array('title'),
            'capability_type' => 'post',
            'map_meta_cap' => true,
        ));
    }

    public function register_admin_menu(): void {
        $this->page_hooks[] = add_menu_page('Popup Ads', 'Popup Ads', 'manage_options', 'ateam-popup-ads', array($this, 'render_list_page'), 'dashicons-format-image', 62);
        $this->page_hooks[] = add_submenu_page('ateam-popup-ads', 'All Popups', 'All Popups', 'manage_options', 'ateam-popup-ads', array($this, 'render_list_page'));
        $this->page_hooks[] = add_submenu_page('ateam-popup-ads', 'Add Popup', 'Add Popup', 'manage_options', 'ateam-popup-ads-edit', array($this, 'render_edit_page'));
    }

    public function enqueue_admin_assets(string $hook): void {
        if (!in_array($hook, $this->page_hooks, true)) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_style('ateam-popup-ads-admin', plugin_dir_url(__FILE__) . 'assets/admin.css', array(), '1.0.0');
        wp_enqueue_script('ateam-popup-ads-admin', plugin_dir_url(__FILE__) . 'assets/admin.js', array('jquery'), '1.0.0', true);
        wp_localize_script('ateam-popup-ads-admin', 'ATeamPopupAdsAdmin', array(
            'chooseMedia' => 'Choose media',
            'useMedia' => 'Use this media',
        ));
    }

    public function enqueue_frontend_assets(): void {
        if (!$this->get_matching_popups()) {
            return;
        }
        wp_enqueue_style('dashicons');
        wp_enqueue_style('ateam-popup-ads', plugin_dir_url(__FILE__) . 'assets/frontend.css', array(), '1.0.0');
        wp_enqueue_script('ateam-popup-ads', plugin_dir_url(__FILE__) . 'assets/frontend.js', array(), '1.0.0', true);
    }

    public function handle_admin_actions(): void {
        if (!is_admin() || !current_user_can('manage_options') || 'POST' !== ($_SERVER['REQUEST_METHOD'] ?? '')) {
            return;
        }
        $action = isset($_POST['ateam_popup_action']) ? sanitize_key(wp_unslash($_POST['ateam_popup_action'])) : '';
        if (!$action) {
            return;
        }
        check_admin_referer(self::NONCE_ACTION, self::NONCE_NAME);

        if ('save' === $action) {
            $post_id = $this->save_popup($_POST);
            $this->redirect('ateam-popup-ads-edit', array('edit' => $post_id, 'notice' => 'saved'));
        }
        if ('toggle' === $action) {
            $post_id = absint($_POST['post_id'] ?? 0);
            $enabled = (string) get_post_meta($post_id, $this->meta_key('enabled'), true);
            update_post_meta($post_id, $this->meta_key('enabled'), '1' === $enabled ? '0' : '1');
            $this->redirect('ateam-popup-ads', array('notice' => 'updated'));
        }
        if ('delete' === $action) {
            $post_id = absint($_POST['post_id'] ?? 0);
            if ($post_id) {
                wp_delete_post($post_id, true);
            }
            $this->redirect('ateam-popup-ads', array('notice' => 'deleted'));
        }
    }

    private function save_popup(array $source): int {
        $source = wp_unslash($source);
        $post_id = absint($source['post_id'] ?? 0);
        $type = $this->valid_type($source['popup_type'] ?? 'event');
        $title = sanitize_text_field($source['admin_title'] ?? '');
        if (!$title) {
            $title = strtoupper($type) . ' Popup';
        }

        $postarr = array(
            'post_type' => self::POST_TYPE,
            'post_status' => 'publish',
            'post_title' => $title,
            'menu_order' => absint($source['display_order'] ?? 0),
        );
        if ($post_id) {
            $postarr['ID'] = $post_id;
        }
        $saved_id = wp_insert_post($postarr);
        if (!$saved_id || is_wp_error($saved_id)) {
            return 0;
        }

        $page_ids = array();
        if (!empty($source['page_ids']) && is_array($source['page_ids'])) {
            $page_ids = array_values(array_map('absint', $source['page_ids']));
        }

        $meta = array(
            'enabled' => empty($source['enabled']) ? '0' : '1',
            'type' => $type,
            'frequency' => 'session' === ($source['frequency'] ?? '') ? 'session' : 'every',
            'delay' => max(0, absint($source['delay'] ?? 0)),
            'all_pages' => empty($source['all_pages']) ? '0' : '1',
            'page_ids' => $page_ids,
            'media_type' => 'video' === ($source['media_type'] ?? '') ? 'video' : 'image',
            'image_id' => absint($source['image_id'] ?? 0),
            'video_id' => absint($source['video_id'] ?? 0),
            'video_url' => esc_url_raw($source['video_url'] ?? ''),
            'click_url' => esc_url_raw($source['click_url'] ?? ''),
            'ad_title' => sanitize_text_field($source['ad_title'] ?? ''),
            'ad_button_text' => sanitize_text_field($source['ad_button_text'] ?? ''),
            'ad_button_url' => esc_url_raw($source['ad_button_url'] ?? ''),
            'event_heading' => sanitize_text_field($source['event_heading'] ?? ''),
            'event_subheading' => sanitize_text_field($source['event_subheading'] ?? ''),
            'event_date' => sanitize_text_field($source['event_date'] ?? ''),
            'event_time' => sanitize_text_field($source['event_time'] ?? ''),
            'event_location' => sanitize_text_field($source['event_location'] ?? ''),
            'event_description' => wp_kses_post($source['event_description'] ?? ''),
            'event_description_size' => max(10, min(42, absint($source['event_description_size'] ?? 18))),
            'event_description_color' => sanitize_hex_color($source['event_description_color'] ?? '#ffffff') ?: '#ffffff',
            'event_button_text' => sanitize_text_field($source['event_button_text'] ?? ''),
            'event_button_url' => esc_url_raw($source['event_button_url'] ?? ''),
            'event_button_subtext' => sanitize_text_field($source['event_button_subtext'] ?? ''),
        );

        foreach ($meta as $key => $value) {
            update_post_meta((int) $saved_id, $this->meta_key($key), $value);
        }
        return (int) $saved_id;
    }

    private function redirect(string $page, array $args = array()): void {
        wp_safe_redirect(add_query_arg(array_merge(array('page' => $page), $args), admin_url('admin.php')));
        exit;
    }

    public function render_list_page(): void {
        $popups = $this->get_all_popups();
        echo '<div class="wrap ateam-popup-admin"><h1 class="wp-heading-inline">Popup Ads</h1> <a class="page-title-action" href="' . esc_url(add_query_arg(array('page' => 'ateam-popup-ads-edit'), admin_url('admin.php'))) . '">Add New</a>';
        $this->render_notice();
        echo '<table class="widefat striped"><thead><tr><th>Title</th><th>Type</th><th>Status</th><th>Pages</th><th>Frequency</th><th>Delay</th><th>Actions</th></tr></thead><tbody>';
        if (!$popups) {
            echo '<tr><td colspan="7">No popups yet.</td></tr>';
        }
        foreach ($popups as $popup) {
            $data = $this->popup_data($popup);
            echo '<tr><td><strong>' . esc_html($popup->post_title) . '</strong></td>';
            echo '<td>' . esc_html(strtoupper($data['type'])) . '</td>';
            echo '<td>' . esc_html('1' === $data['enabled'] ? 'Enabled' : 'Disabled') . '</td>';
            echo '<td>' . esc_html('1' === $data['all_pages'] ? 'All pages' : $this->page_names($data['page_ids'])) . '</td>';
            echo '<td>' . esc_html('session' === $data['frequency'] ? 'Once per session' : 'Every visit') . '</td>';
            echo '<td>' . esc_html((string) $data['delay']) . 's</td><td>';
            echo '<a href="' . esc_url(add_query_arg(array('page' => 'ateam-popup-ads-edit', 'edit' => $popup->ID), admin_url('admin.php'))) . '">Edit</a> | ';
            echo $this->row_form($popup->ID, 'toggle', '1' === $data['enabled'] ? 'Disable' : 'Enable');
            echo ' | ' . $this->row_form($popup->ID, 'delete', 'Delete', true);
            echo '</td></tr>';
        }
        echo '</tbody></table></div>';
    }

    public function render_edit_page(): void {
        $post_id = isset($_GET['edit']) ? absint($_GET['edit']) : 0;
        $popup = $post_id ? get_post($post_id) : null;
        $data = $popup ? $this->popup_data($popup) : $this->default_data();
        $pages = get_pages(array('sort_column' => 'post_title'));
        echo '<div class="wrap ateam-popup-admin"><h1>' . esc_html($popup ? 'Edit Popup' : 'Add Popup') . '</h1>';
        $this->render_notice();
        echo '<form method="post" class="ateam-popup-form">';
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);
        echo '<input type="hidden" name="ateam_popup_action" value="save"><input type="hidden" name="post_id" value="' . esc_attr((string) $post_id) . '">';
        echo '<div class="ateam-popup-grid"><section class="ateam-popup-panel"><h2>Popup Setup</h2>';
        $this->field('Admin title', 'admin_title', $popup ? $popup->post_title : '');
        echo '<label><span>Popup type</span><select name="popup_type" data-ateam-popup-type>';
        foreach (array('event' => 'EVENT', 'video' => 'VIDEO', 'ad' => 'AD') as $value => $label) {
            echo '<option value="' . esc_attr($value) . '"' . selected($data['type'], $value, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select></label>';
        $this->number_field('Display order', 'display_order', $popup ? (int) $popup->menu_order : 0, 0);
        $this->checkbox('Enabled', 'enabled', '1' === $data['enabled']);
        echo '<label><span>Display frequency</span><select name="frequency"><option value="every"' . selected($data['frequency'], 'every', false) . '>Every time selected pages load</option><option value="session"' . selected($data['frequency'], 'session', false) . '>Once per popup per browser session</option></select></label>';
        $this->number_field('Delay before popup opens (seconds)', 'delay', (int) $data['delay'], 0);
        echo '</section><section class="ateam-popup-panel"><h2>Page Targeting</h2>';
        $this->checkbox('Show on all pages', 'all_pages', '1' === $data['all_pages'], 'data-ateam-all-pages');
        echo '<div class="ateam-popup-pages" data-ateam-page-list>';
        foreach ($pages as $page) {
            $checked = in_array((int) $page->ID, (array) $data['page_ids'], true);
            echo '<label class="ateam-popup-check"><input type="checkbox" name="page_ids[]" value="' . esc_attr((string) $page->ID) . '"' . checked($checked, true, false) . '> <span>' . esc_html($page->post_title) . '</span></label>';
        }
        echo '</div></section></div>';

        echo '<section class="ateam-popup-panel" data-ateam-section="media"><h2>Media</h2>';
        echo '<label><span>Media type</span><select name="media_type"><option value="image"' . selected($data['media_type'], 'image', false) . '>Image</option><option value="video"' . selected($data['media_type'], 'video', false) . '>Video</option></select></label>';
        $this->media_field('Image / event flyer', 'image_id', (int) $data['image_id']);
        $this->media_field('Uploaded video', 'video_id', (int) $data['video_id']);
        $this->field('YouTube or Vimeo URL', 'video_url', $data['video_url'], 'url');
        $this->field('Optional click-through URL for media', 'click_url', $data['click_url'], 'url');
        echo '</section>';

        echo '<section class="ateam-popup-panel" data-ateam-section="event"><h2>Event Details</h2>';
        $this->field('Heading', 'event_heading', $data['event_heading'], 'text', true);
        $this->field('Sub heading', 'event_subheading', $data['event_subheading'], 'text', true);
        $this->field('Date', 'event_date', $data['event_date'], 'text', true);
        $this->field('Time', 'event_time', $data['event_time'], 'text', true);
        $this->field('Location', 'event_location', $data['event_location'], 'text', true);
        $this->textarea('Short Description', 'event_description', $data['event_description'], true);
        $this->number_field('Description font size (px)', 'event_description_size', (int) $data['event_description_size'], 10);
        $this->field('Description text color', 'event_description_color', $data['event_description_color'], 'color');
        $this->field('Button Text', 'event_button_text', $data['event_button_text'], 'text', true);
        $this->field('Button Link', 'event_button_url', $data['event_button_url'], 'url', true);
        $this->field('Button sub text', 'event_button_subtext', $data['event_button_subtext']);
        echo '</section>';

        echo '<section class="ateam-popup-panel" data-ateam-section="ad-video"><h2>Ad / Video Text</h2>';
        $this->field('Title', 'ad_title', $data['ad_title']);
        $this->field('Button Text', 'ad_button_text', $data['ad_button_text']);
        $this->field('Button Link', 'ad_button_url', $data['ad_button_url'], 'url');
        echo '</section>';

        submit_button($popup ? 'Update Popup' : 'Create Popup');
        echo '</form></div>';
    }

    private function render_notice(): void {
        $notice = isset($_GET['notice']) ? sanitize_key(wp_unslash($_GET['notice'])) : '';
        if (!$notice) {
            return;
        }
        $messages = array(
            'saved' => 'Popup saved.',
            'updated' => 'Popup updated.',
            'deleted' => 'Popup deleted.',
        );
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($messages[$notice] ?? 'Done.') . '</p></div>';
    }

    private function row_form(int $post_id, string $action, string $label, bool $confirm = false): string {
        $html = '<form method="post" class="ateam-popup-row-form"' . ($confirm ? ' onsubmit="return confirm(\'Delete this popup permanently?\');"' : '') . '>';
        $html .= wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME, true, false);
        $html .= '<input type="hidden" name="ateam_popup_action" value="' . esc_attr($action) . '">';
        $html .= '<input type="hidden" name="post_id" value="' . esc_attr((string) $post_id) . '">';
        $html .= '<button type="submit" class="button-link">' . esc_html($label) . '</button></form>';
        return $html;
    }

    private function field(string $label, string $name, string $value, string $type = 'text', bool $required = false): void {
        echo '<label><span>' . esc_html($label) . '</span><input type="' . esc_attr($type) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '"' . ($required ? ' data-required-for-event="1"' : '') . '></label>';
    }

    private function textarea(string $label, string $name, string $value, bool $required = false): void {
        echo '<label><span>' . esc_html($label) . '</span><textarea name="' . esc_attr($name) . '" rows="5"' . ($required ? ' data-required-for-event="1"' : '') . '>' . esc_textarea($value) . '</textarea></label>';
    }

    private function number_field(string $label, string $name, int $value, int $min): void {
        echo '<label><span>' . esc_html($label) . '</span><input type="number" name="' . esc_attr($name) . '" value="' . esc_attr((string) $value) . '" min="' . esc_attr((string) $min) . '" step="1"></label>';
    }

    private function checkbox(string $label, string $name, bool $checked, string $attrs = ''): void {
        echo '<label class="ateam-popup-check"><input type="checkbox" name="' . esc_attr($name) . '" value="1"' . checked($checked, true, false) . ($attrs ? ' ' . $attrs : '') . '> <span>' . esc_html($label) . '</span></label>';
    }

    private function media_field(string $label, string $name, int $attachment_id): void {
        $preview = $attachment_id ? wp_get_attachment_url($attachment_id) : '';
        echo '<div class="ateam-popup-media-field"><span>' . esc_html($label) . '</span><input class="ateam-popup-media-input" type="hidden" name="' . esc_attr($name) . '" value="' . esc_attr((string) $attachment_id) . '">';
        echo '<div class="ateam-popup-media-preview">' . ($preview ? '<code>' . esc_html(basename((string) $preview)) . '</code>' : '<em>No media selected.</em>') . '</div><button type="button" class="button ateam-popup-media-button">Choose / Upload</button><button type="button" class="button-link ateam-popup-media-clear">Clear</button></div>';
    }

    public function render_frontend(): void {
        $popups = $this->get_matching_popups();
        if (!$popups) {
            return;
        }
        $first = $this->popup_data($popups[0]);
        $type = $first['type'];
        $slides = array_values(array_filter($popups, function ($popup) use ($type) {
            return $this->popup_data($popup)['type'] === $type;
        }));
        if (!$slides) {
            return;
        }
        $delay = min(array_map(function ($popup) {
            return (int) $this->popup_data($popup)['delay'];
        }, $slides));
        echo '<div class="ateam-popup" data-ateam-popup data-delay="' . esc_attr((string) $delay) . '" aria-hidden="true">';
        echo '<div class="ateam-popup__backdrop" data-ateam-popup-close></div><div class="ateam-popup__dialog ateam-popup__dialog--' . esc_attr($type) . '" role="dialog" aria-modal="true" aria-label="Popup advertisement">';
        echo '<button class="ateam-popup__close" type="button" aria-label="Close popup" data-ateam-popup-close>&times;</button>';
        if (count($slides) > 1) {
            echo '<button class="ateam-popup__nav ateam-popup__nav--prev" type="button" data-ateam-popup-prev aria-label="Previous popup">&#8249;</button><button class="ateam-popup__nav ateam-popup__nav--next" type="button" data-ateam-popup-next aria-label="Next popup">&#8250;</button>';
        }
        echo '<div class="ateam-popup__slides">';
        foreach ($slides as $index => $popup) {
            $data = $this->popup_data($popup);
            echo '<article class="ateam-popup__slide' . (0 === $index ? ' is-active' : '') . '" data-popup-id="' . esc_attr((string) $popup->ID) . '" data-frequency="' . esc_attr($data['frequency']) . '">';
            echo $this->slide_html($data);
            echo '</article>';
        }
        echo '</div></div></div>';
    }

    private function slide_html(array $data): string {
        if ('event' === $data['type']) {
            return $this->event_slide_html($data);
        }
        return $this->standard_slide_html($data);
    }

    private function event_slide_html(array $data): string {
        $description_style = 'font-size:' . (int) $data['event_description_size'] . 'px;color:' . esc_attr($data['event_description_color']);
        $html = '<div class="ateam-popup-event">';
        $html .= '<div class="ateam-popup-event__media">' . $this->media_html($data) . '</div>';
        $html .= '<div class="ateam-popup-event__copy">';
        $html .= '<p class="ateam-popup-event__subheading">' . esc_html($data['event_subheading']) . '</p>';
        $html .= '<h2>' . esc_html($data['event_heading']) . '</h2>';
        $html .= '<ul class="ateam-popup-event__facts">';
        $html .= '<li><span class="dashicons dashicons-calendar-alt"></span>' . esc_html($data['event_date']) . '</li>';
        $html .= '<li><span class="dashicons dashicons-clock"></span>' . esc_html($data['event_time']) . '</li>';
        $html .= '<li><span class="dashicons dashicons-location"></span>' . esc_html($data['event_location']) . '</li>';
        $html .= '</ul>';
        $html .= '<div class="ateam-popup-event__description" style="' . esc_attr($description_style) . '">' . wp_kses_post(wpautop($data['event_description'])) . '</div>';
        if ($data['event_button_text'] && $data['event_button_url']) {
            $html .= '<p class="ateam-popup-event__action"><a href="' . esc_url($data['event_button_url']) . '">' . esc_html($data['event_button_text']) . '</a></p>';
        }
        if ($data['event_button_subtext']) {
            $html .= '<p class="ateam-popup-event__button-subtext">' . esc_html($data['event_button_subtext']) . '</p>';
        }
        $html .= '</div></div>';
        return $html;
    }

    private function standard_slide_html(array $data): string {
        $html = '<div class="ateam-popup-standard">';
        $html .= '<div class="ateam-popup-standard__media">' . $this->media_html($data) . '</div>';
        if ($data['ad_title'] || $data['ad_button_text']) {
            $html .= '<div class="ateam-popup-standard__copy">';
            if ($data['ad_title']) {
                $html .= '<h2>' . esc_html($data['ad_title']) . '</h2>';
            }
            if ($data['ad_button_text'] && $data['ad_button_url']) {
                $html .= '<a href="' . esc_url($data['ad_button_url']) . '">' . esc_html($data['ad_button_text']) . '</a>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    }

    private function media_html(array $data): string {
        $html = '';
        if ('video' === $data['media_type'] || 'video' === $data['type']) {
            if ($data['video_url']) {
                $embed = $this->autoplay_embed((string) $data['video_url']);
                if ($embed) {
                    return '<div class="ateam-popup-embed">' . $embed . '</div>';
                }
            }
            if ($data['video_id']) {
                $src = wp_get_attachment_url((int) $data['video_id']);
                if ($src) {
                    $html = '<video src="' . esc_url($src) . '" autoplay muted playsinline loop controls></video>';
                }
            }
        }
        if (!$html && $data['image_id']) {
            $image = wp_get_attachment_image((int) $data['image_id'], 'large', false, array('alt' => ''));
            $html = $image ?: '';
        }
        if ($html && $data['click_url']) {
            return '<a href="' . esc_url($data['click_url']) . '">' . $html . '</a>';
        }
        return $html;
    }

    private function autoplay_embed(string $url): string {
        $embed = wp_oembed_get($url);
        if (!$embed) {
            return '';
        }
        return preg_replace_callback('/src="([^"]+)"/', function (array $matches): string {
            $src = add_query_arg(array(
                'autoplay' => '1',
                'mute' => '1',
                'muted' => '1',
                'playsinline' => '1',
            ), html_entity_decode($matches[1]));
            return 'src="' . esc_url($src) . '"';
        }, $embed) ?: $embed;
    }

    private function get_matching_popups(): array {
        if (!is_singular('page')) {
            return array();
        }
        $page_id = (int) get_queried_object_id();
        $matches = array();
        foreach ($this->get_all_popups() as $popup) {
            $data = $this->popup_data($popup);
            if ('1' !== $data['enabled']) {
                continue;
            }
            if ('1' === $data['all_pages'] || in_array($page_id, (array) $data['page_ids'], true)) {
                $matches[] = $popup;
            }
        }
        return $matches;
    }

    private function get_all_popups(): array {
        return get_posts(array(
            'post_type' => self::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => array('menu_order' => 'ASC', 'date' => 'DESC'),
        ));
    }

    private function popup_data(WP_Post $post): array {
        $data = $this->default_data();
        foreach ($data as $key => $default) {
            $value = get_post_meta($post->ID, $this->meta_key($key), true);
            if ('' !== $value && array() !== $value) {
                $data[$key] = $value;
            }
        }
        $data['type'] = $this->valid_type($data['type']);
        $data['page_ids'] = array_values(array_map('absint', (array) $data['page_ids']));
        return $data;
    }

    private function default_data(): array {
        return array(
            'enabled' => '1',
            'type' => 'event',
            'frequency' => 'every',
            'delay' => 0,
            'all_pages' => '0',
            'page_ids' => array(),
            'media_type' => 'image',
            'image_id' => 0,
            'video_id' => 0,
            'video_url' => '',
            'click_url' => '',
            'ad_title' => '',
            'ad_button_text' => '',
            'ad_button_url' => '',
            'event_heading' => '',
            'event_subheading' => '',
            'event_date' => '',
            'event_time' => '',
            'event_location' => '',
            'event_description' => '',
            'event_description_size' => 18,
            'event_description_color' => '#ffffff',
            'event_button_text' => '',
            'event_button_url' => '',
            'event_button_subtext' => '',
        );
    }

    private function page_names(array $page_ids): string {
        $names = array();
        foreach ($page_ids as $page_id) {
            $title = get_the_title((int) $page_id);
            if ($title) {
                $names[] = $title;
            }
        }
        return $names ? implode(', ', $names) : 'No pages selected';
    }

    private function meta_key(string $key): string {
        return self::META_PREFIX . $key;
    }

    private function valid_type(string $type): string {
        return in_array($type, array('event', 'video', 'ad'), true) ? $type : 'event';
    }
}

$GLOBALS['ateam_popup_ads'] = new ATeam_Popup_Ads();
$GLOBALS['ateam_popup_ads']->hooks();
