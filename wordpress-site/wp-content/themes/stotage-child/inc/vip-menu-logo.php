<?php
/**
 * Adds the VIP Cooler Fete logo treatment to the main site menu.
 */

if (!defined('ABSPATH')) {
    exit;
}

final class ATeam_VIP_Menu_Logo {
    private const OPTION_ENABLED = 'ateam_vip_menu_logo_enabled';
    private const OPTION_URL = 'ateam_vip_menu_logo_url';
    private const LOGO_FILE = '/assets/images/class-vip-cooler-fete-logo.png';

    public function hooks(): void {
        add_filter('wp_nav_menu_items', array($this, 'inject_menu_logo'), 20, 2);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'), 30);
        add_action('wp_footer', array($this, 'print_style_fallback'), 2);
        add_action('admin_menu', array($this, 'register_settings_page'));
        add_action('admin_init', array($this, 'handle_settings_save'));
    }

    public function enabled(): bool {
        return '0' !== (string) get_option(self::OPTION_ENABLED, '1');
    }

    public function logo_url(): string {
        return STOTAGE_CHILD_URI . self::LOGO_FILE;
    }

    public function link_url(): string {
        $saved = trim((string) get_option(self::OPTION_URL, ''));
        return $saved ? $saved : home_url('/events/');
    }

    public function inject_menu_logo(string $items, stdClass $args): string {
        if (!$this->enabled() || is_admin() || !$this->is_header_menu($args) || stripos($items, 'ateam-vip-menu-logo-item') !== false) {
            return $items;
        }

        $logo = $this->menu_logo_item();
        if (stripos($items, 'menu-item-12265') !== false) {
            return preg_replace('/(<li\b[^>]*\bmenu-item-12265\b[^>]*>.*?<\/li>)/is', '$1' . $logo, $items, 1) ?: $items . $logo;
        }

        return $items . $logo;
    }

    public function enqueue_assets(): void {
        if (!$this->enabled() || is_admin()) {
            return;
        }
        wp_enqueue_style('ateam-vip-menu-logo', STOTAGE_CHILD_URI . '/assets/css/vip-menu-logo.css', array(), STOTAGE_CHILD_VERSION);
        wp_enqueue_script('ateam-vip-menu-logo', STOTAGE_CHILD_URI . '/assets/js/vip-menu-logo.js', array(), STOTAGE_CHILD_VERSION, true);
        wp_localize_script('ateam-vip-menu-logo', 'ATeamVIPMenuLogo', array(
            'logoUrl' => $this->logo_url(),
            'linkUrl' => $this->link_url(),
            'enabled' => true,
        ));
    }

    public function print_style_fallback(): void {
        if (!$this->enabled() || is_admin()) {
            return;
        }
        if (wp_style_is('ateam-vip-menu-logo', 'done')) {
            return;
        }
        echo '<link rel="stylesheet" id="ateam-vip-menu-logo-fallback-css" href="' . esc_url(STOTAGE_CHILD_URI . '/assets/css/vip-menu-logo.css') . '?ver=' . esc_attr(STOTAGE_CHILD_VERSION) . '" media="all">' . "\n";
    }

    public function register_settings_page(): void {
        add_theme_page('VIP Menu Logo', 'VIP Menu Logo', 'manage_options', 'ateam-vip-menu-logo', array($this, 'render_settings_page'));
    }

    public function handle_settings_save(): void {
        if (!is_admin() || !current_user_can('manage_options') || empty($_POST['ateam_vip_menu_logo_action'])) {
            return;
        }
        check_admin_referer('ateam_vip_menu_logo_action', 'ateam_vip_menu_logo_nonce');
        update_option(self::OPTION_ENABLED, empty($_POST['enabled']) ? '0' : '1');
        update_option(self::OPTION_URL, esc_url_raw(wp_unslash($_POST['link_url'] ?? '')));
        wp_safe_redirect(add_query_arg(array('page' => 'ateam-vip-menu-logo', 'notice' => 'saved'), admin_url('themes.php')));
        exit;
    }

    public function render_settings_page(): void {
        echo '<div class="wrap"><h1>VIP Menu Logo</h1>';
        if (isset($_GET['notice'])) {
            echo '<div class="notice notice-success is-dismissible"><p>VIP menu logo settings saved.</p></div>';
        }
        echo '<p>Use this switch to roll back the header logo treatment without editing code.</p>';
        echo '<form method="post">';
        wp_nonce_field('ateam_vip_menu_logo_action', 'ateam_vip_menu_logo_nonce');
        echo '<input type="hidden" name="ateam_vip_menu_logo_action" value="save">';
        echo '<table class="form-table" role="presentation"><tbody>';
        echo '<tr><th scope="row">Enable logo in menu</th><td><label><input type="checkbox" name="enabled" value="1"' . checked($this->enabled(), true, false) . '> Show the Class VIP Cooler Fete logo in the header menu</label></td></tr>';
        echo '<tr><th scope="row"><label for="link_url">Logo link URL</label></th><td><input class="regular-text" id="link_url" name="link_url" type="url" value="' . esc_attr($this->link_url()) . '"><p class="description">Defaults to the Events page if left empty.</p></td></tr>';
        echo '<tr><th scope="row">Current logo</th><td><img src="' . esc_url($this->logo_url()) . '" alt="" style="max-width:180px;height:auto;background:#111;padding:12px;"></td></tr>';
        echo '</tbody></table>';
        submit_button('Save Settings');
        echo '</form></div>';
    }

    private function is_header_menu(stdClass $args): bool {
        $location = isset($args->theme_location) ? (string) $args->theme_location : '';
        if (!$location) {
            return true;
        }
        foreach (array('main', 'primary', 'header', 'menu') as $needle) {
            if (stripos($location, $needle) !== false) {
                return true;
            }
        }
        return false;
    }

    private function menu_logo_item(): string {
        return '<li class="menu-item ateam-vip-menu-logo-item"><a class="ateam-vip-menu-logo-link" href="' . esc_url($this->link_url()) . '"><img src="' . esc_url($this->logo_url()) . '" alt="Class VIP Cooler Fete"></a></li>';
    }
}

$GLOBALS['ateam_vip_menu_logo'] = new ATeam_VIP_Menu_Logo();
$GLOBALS['ateam_vip_menu_logo']->hooks();
