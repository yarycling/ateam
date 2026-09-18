<?php
/**
 * Plugin Name: A Team Bandroom Booking
 * Description: Configurable Bandroom booking enquiry popup with live pricing and email notifications.
 * Version: 1.0.1
 * Author: A Team Band
 * Text Domain: ateam-bandroom-booking
 */

if (!defined('ABSPATH')) {
    exit;
}

final class ATeam_Bandroom_Booking {
    private const OPTION_KEY = 'ateam_bandroom_booking_settings';
    private const POST_TYPE = 'ateam_band_booking';

    public function hooks(): void {
        add_action('init', array($this, 'register_post_type'));
        add_action('admin_menu', array($this, 'register_menu'));
        add_action('admin_init', array($this, 'handle_admin_actions'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_footer', array($this, 'render_popup'));
        add_action('wp_ajax_ateam_bandroom_submit_booking', array($this, 'submit_booking'));
        add_action('wp_ajax_nopriv_ateam_bandroom_submit_booking', array($this, 'submit_booking'));
        add_shortcode('ateam_bandroom_booking_button', array($this, 'render_button_shortcode'));
    }

    public function register_post_type(): void {
        register_post_type(self::POST_TYPE, array(
            'labels' => array(
                'name' => 'Bandroom Bookings',
                'singular_name' => 'Bandroom Booking',
                'menu_name' => 'Bookings',
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'ateam-bandroom-booking',
            'supports' => array('title', 'custom-fields'),
            'capability_type' => 'post',
            'map_meta_cap' => true,
        ));
    }

    public function register_menu(): void {
        add_menu_page('Bandroom Booking', 'Bandroom Booking', 'manage_options', 'ateam-bandroom-booking', array($this, 'render_settings_page'), 'dashicons-calendar-alt', 61);
        add_submenu_page('ateam-bandroom-booking', 'Settings', 'Settings', 'manage_options', 'ateam-bandroom-booking', array($this, 'render_settings_page'));
    }

    private function defaults(): array {
        return array(
            'first_hour_rate' => '500',
            'additional_hour_rate' => '300',
            'recording_hour_rate' => '0',
            'engineer_hour_rate' => '0',
            'notification_email' => get_option('admin_email'),
            'currency_symbol' => '$',
            'opening_time' => '08:00',
            'closing_time' => '23:00',
            'time_interval' => '30',
        );
    }

    private function settings(): array {
        return wp_parse_args((array) get_option(self::OPTION_KEY, array()), $this->defaults());
    }

    public function handle_admin_actions(): void {
        if (!is_admin() || !current_user_can('manage_options') || empty($_POST['ateam_bandroom_booking_action'])) {
            return;
        }
        check_admin_referer('ateam_bandroom_booking_action', 'ateam_bandroom_booking_nonce');
        $action = sanitize_key(wp_unslash($_POST['ateam_bandroom_booking_action']));
        if ('save_settings' === $action) {
            $source = wp_unslash($_POST);
            update_option(self::OPTION_KEY, array(
                'first_hour_rate' => $this->number($source['first_hour_rate'] ?? 500),
                'additional_hour_rate' => $this->number($source['additional_hour_rate'] ?? 300),
                'recording_hour_rate' => $this->number($source['recording_hour_rate'] ?? 0),
                'engineer_hour_rate' => $this->number($source['engineer_hour_rate'] ?? 0),
                'notification_email' => sanitize_email($source['notification_email'] ?? ''),
                'currency_symbol' => sanitize_text_field($source['currency_symbol'] ?? '$'),
                'opening_time' => $this->valid_time($source['opening_time'] ?? '08:00'),
                'closing_time' => $this->valid_time($source['closing_time'] ?? '23:00'),
                'time_interval' => in_array((string) ($source['time_interval'] ?? '30'), array('15', '30', '60'), true) ? (string) $source['time_interval'] : '30',
            ));
            $this->redirect('settings_saved');
        }
        if ('send_test_email' === $action) {
            $settings = $this->settings();
            $email = sanitize_email(wp_unslash($_POST['test_email'] ?? $settings['notification_email']));
            $sent = $email && wp_mail($email, 'A Team Bandroom Booking Test Email', '<p>This is a successful test email from the A Team Bandroom Booking plugin.</p>', $this->mail_headers());
            $this->redirect($sent ? 'test_sent' : 'test_failed');
        }
    }

    private function redirect(string $notice): void {
        wp_safe_redirect(add_query_arg(array('page' => 'ateam-bandroom-booking', 'ateam_booking_notice' => $notice), admin_url('admin.php')));
        exit;
    }

    public function render_settings_page(): void {
        $s = $this->settings();
        $notice = sanitize_key(wp_unslash($_GET['ateam_booking_notice'] ?? ''));
        echo '<div class="wrap"><h1>Bandroom Booking</h1>';
        if ('settings_saved' === $notice) { echo '<div class="notice notice-success is-dismissible"><p>Booking settings saved.</p></div>'; }
        if ('test_sent' === $notice) { echo '<div class="notice notice-success is-dismissible"><p>WordPress accepted the test email for delivery. Check the recipient inbox and spam folder.</p></div>'; }
        if ('test_failed' === $notice) { echo '<div class="notice notice-error"><p>WordPress could not send the test email. Check your site mail configuration.</p></div>'; }
        echo '<form method="post"><input type="hidden" name="ateam_bandroom_booking_action" value="save_settings">';
        wp_nonce_field('ateam_bandroom_booking_action', 'ateam_bandroom_booking_nonce');
        echo '<table class="form-table" role="presentation"><tbody>';
        $this->settings_field('First hour rate', 'first_hour_rate', $s['first_hour_rate'], 'number', 'The cost for the first booking hour.');
        $this->settings_field('Additional hourly rate', 'additional_hour_rate', $s['additional_hour_rate'], 'number', 'The cost for each hour after the first.');
        $this->settings_field('Recording add-on per hour', 'recording_hour_rate', $s['recording_hour_rate'], 'number', 'Set to 0 if recording is not charged.');
        $this->settings_field('House engineer add-on per hour', 'engineer_hour_rate', $s['engineer_hour_rate'], 'number', 'Set to 0 if the house engineer is not charged.');
        $this->settings_field('Booking notification email', 'notification_email', $s['notification_email'], 'email', 'All new booking enquiries are sent here.');
        $this->settings_field('Currency symbol', 'currency_symbol', $s['currency_symbol'], 'text', 'For example $, TT$, or USD$.');
        $this->settings_field('Opening time', 'opening_time', $s['opening_time'], 'time', 'Earliest selectable booking start time.');
        $this->settings_field('Closing time', 'closing_time', $s['closing_time'], 'time', 'Latest selectable booking end time.');
        echo '<tr><th scope="row"><label for="time_interval">Time interval</label></th><td><select id="time_interval" name="time_interval">';
        foreach (array('15' => '15 minutes', '30' => '30 minutes', '60' => '1 hour') as $value => $label) { echo '<option value="' . esc_attr($value) . '"' . selected($s['time_interval'], $value, false) . '>' . esc_html($label) . '</option>'; }
        echo '</select><p class="description">Available start and end time increments.</p></td></tr>';
        echo '</tbody></table>'; submit_button('Save Booking Settings'); echo '</form>';
        echo '<hr><h2>Test Email</h2><p>Send a test through the same WordPress mail system used for booking notifications.</p><form method="post">';
        wp_nonce_field('ateam_bandroom_booking_action', 'ateam_bandroom_booking_nonce');
        echo '<input type="hidden" name="ateam_bandroom_booking_action" value="send_test_email"><p><label for="test_email">Recipient email<br><input class="regular-text" id="test_email" name="test_email" type="email" value="' . esc_attr($s['notification_email']) . '" required></label></p>'; submit_button('Send Test Email', 'secondary'); echo '</form>';
        echo '<hr><h2>Open the Popup</h2><p>Add the class <code>ateam-bandroom-booking-trigger</code> to any existing link or button. You can also use:</p><code>[ateam_bandroom_booking_button label="Book a Session"]</code><p>The popup trigger link may also use <code>href="#ateam-bandroom-booking"</code>.</p></div>';
    }

    private function settings_field(string $label, string $name, string $value, string $type, string $description): void {
        echo '<tr><th scope="row"><label for="' . esc_attr($name) . '">' . esc_html($label) . '</label></th><td><input class="regular-text" id="' . esc_attr($name) . '" name="' . esc_attr($name) . '" type="' . esc_attr($type) . '" value="' . esc_attr($value) . '"' . ('number' === $type ? ' min="0" step="0.01"' : '') . ' required><p class="description">' . esc_html($description) . '</p></td></tr>';
    }

    public function enqueue_assets(): void {
        $s = $this->settings();
        wp_enqueue_style('ateam-bandroom-booking', plugin_dir_url(__FILE__) . 'assets/booking.css', array(), '1.0.0');
        wp_enqueue_script('ateam-bandroom-booking', plugin_dir_url(__FILE__) . 'assets/booking.js', array(), '1.0.2', true);
        wp_localize_script('ateam-bandroom-booking', 'ATeamBandroomBooking', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ateam_bandroom_booking'),
            'currentDate' => current_time('Y-m-d'),
            'firstHourRate' => (float) $s['first_hour_rate'],
            'additionalHourRate' => (float) $s['additional_hour_rate'],
            'recordingHourRate' => (float) $s['recording_hour_rate'],
            'engineerHourRate' => (float) $s['engineer_hour_rate'],
            'currency' => $s['currency_symbol'],
            'openingTime' => $s['opening_time'],
            'closingTime' => $s['closing_time'],
            'interval' => (int) $s['time_interval'],
        ));
    }

    public function render_popup(): void {
        $s = $this->settings();
        if (is_admin()) { return; }
        $config = array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ateam_bandroom_booking'),
            'currentDate' => current_time('Y-m-d'),
            'firstHourRate' => (float) $s['first_hour_rate'],
            'additionalHourRate' => (float) $s['additional_hour_rate'],
            'recordingHourRate' => (float) $s['recording_hour_rate'],
            'engineerHourRate' => (float) $s['engineer_hour_rate'],
            'currency' => $s['currency_symbol'],
            'openingTime' => $s['opening_time'],
            'closingTime' => $s['closing_time'],
            'interval' => (int) $s['time_interval'],
        );
        echo '<script>window.ATeamBandroomBooking = ' . wp_json_encode($config) . ';</script>';
        echo '<div class="ateam-booking-modal" id="ateam-bandroom-booking" aria-hidden="true"><div class="ateam-booking-modal__backdrop" data-ateam-booking-close></div><section class="ateam-booking-modal__dialog" aria-label="Book the Bandroom" role="dialog" aria-modal="true"><button class="ateam-booking-modal__close" type="button" aria-label="Close booking form" data-ateam-booking-close>&times;</button><div class="ateam-booking-modal__heading"><p>Bandroom Booking</p><h2>Book a Session</h2></div><form id="ateam-bandroom-booking-form" class="ateam-booking-form"><div class="ateam-booking-form__grid"><label>Name<input name="name" type="text" required></label><label>Email<input name="email" type="email" required></label><label>Phone<input name="phone" type="tel" required></label><label>Date<input name="date" type="date" required></label><label>Start time<select name="start_time" required></select></label><label>End time<select name="end_time" required></select></label></div><fieldset><legend>Add-ons</legend><label class="ateam-booking-check"><input name="recording" type="checkbox" value="1"><span>Recording <small>' . esc_html($this->rate_label((float) $s['recording_hour_rate'], $s['currency_symbol'])) . '</small></span></label><label class="ateam-booking-check"><input name="engineer" type="checkbox" value="1"><span>House Engineer <small>' . esc_html($this->rate_label((float) $s['engineer_hour_rate'], $s['currency_symbol'])) . '</small></span></label></fieldset><label>Notes <textarea name="notes" rows="3" placeholder="Tell us anything we should know about your session."></textarea></label><div class="ateam-booking-summary"><div><span>Session duration</span><strong data-ateam-booking-hours>Select your time</strong></div><div><span>Estimated total</span><strong data-ateam-booking-total>' . esc_html($s['currency_symbol']) . '0.00</strong></div></div><p class="ateam-booking-form__message" role="status"></p><button class="ateam-booking-form__submit" type="submit">Send Booking Enquiry</button><p class="ateam-booking-form__fineprint">This sends an enquiry only. A Team will confirm availability with you.</p></form></section></div>';
    }

    public function render_button_shortcode(array $atts): string {
        $atts = shortcode_atts(array('label' => 'Book a Session', 'class' => ''), $atts, 'ateam_bandroom_booking_button');
        return '<a href="#ateam-bandroom-booking" class="ateam-bandroom-booking-trigger ' . esc_attr($atts['class']) . '">' . esc_html($atts['label']) . '</a>';
    }

    public function submit_booking(): void {
        check_ajax_referer('ateam_bandroom_booking', 'nonce');
        $source = wp_unslash($_POST);
        $name = sanitize_text_field($source['name'] ?? '');
        $email = sanitize_email($source['email'] ?? '');
        $phone = sanitize_text_field($source['phone'] ?? '');
        $date = sanitize_text_field($source['date'] ?? '');
        $start = $this->valid_time($source['start_time'] ?? '');
        $end = $this->valid_time($source['end_time'] ?? '');
        $notes = sanitize_textarea_field($source['notes'] ?? '');
        $s = $this->settings();
        if (!$name || !is_email($email) || !$phone || !$this->valid_date($date) || !$start || !$end) {
            wp_send_json_error(array('message' => 'Please complete every required booking field.'), 400);
        }
        if ($date < current_time('Y-m-d') || ($date === current_time('Y-m-d') && $start <= current_time('H:i')) || !$this->available_time($start, $s) || !$this->available_time($end, $s)) {
            wp_send_json_error(array('message' => 'Please choose an available future date and time.'), 400);
        }
        $duration = $this->duration($start, $end);
        if ($duration < 1) { wp_send_json_error(array('message' => 'Please choose a session of at least one hour.'), 400); }
        $recording = !empty($source['recording']);
        $engineer = !empty($source['engineer']);
        $total = $this->total($duration, $recording, $engineer, $s);
        $booking_id = wp_insert_post(array('post_type' => self::POST_TYPE, 'post_status' => 'publish', 'post_title' => $date . ' ' . $start . ' - ' . $name));
        if (!$booking_id || is_wp_error($booking_id)) { wp_send_json_error(array('message' => 'Your booking could not be saved. Please try again.'), 500); }
        $details = array('name' => $name, 'email' => $email, 'phone' => $phone, 'date' => $date, 'start' => $start, 'end' => $end, 'hours' => $duration, 'recording' => $recording, 'engineer' => $engineer, 'notes' => $notes, 'total' => $total);
        foreach ($details as $key => $value) { update_post_meta($booking_id, '_ateam_booking_' . $key, $value); }
        $subject = 'New Bandroom Booking Enquiry - ' . $date;
        $message = $this->email_html($details, $s);
        $sent = wp_mail($s['notification_email'], $subject, $message, $this->mail_headers($name, $email));
        update_post_meta($booking_id, '_ateam_booking_email_sent', $sent ? '1' : '0');
        if (!$sent) { wp_send_json_error(array('message' => 'Your enquiry was saved, but the notification email could not be sent. Please contact A Team directly.'), 500); }
        wp_send_json_success(array('message' => 'Your booking enquiry has been sent. A Team will confirm availability with you shortly.'));
    }

    private function email_html(array $d, array $s): string {
        $addons = array(); if ($d['recording']) { $addons[] = 'Recording'; } if ($d['engineer']) { $addons[] = 'House Engineer'; }
        return '<h2>New Bandroom Booking Enquiry</h2><table cellpadding="8" cellspacing="0" border="1" style="border-collapse:collapse;border-color:#ddd"><tr><th align="left">Name</th><td>' . esc_html($d['name']) . '</td></tr><tr><th align="left">Email</th><td>' . esc_html($d['email']) . '</td></tr><tr><th align="left">Phone</th><td>' . esc_html($d['phone']) . '</td></tr><tr><th align="left">Date</th><td>' . esc_html($d['date']) . '</td></tr><tr><th align="left">Session</th><td>' . esc_html($d['start'] . ' to ' . $d['end'] . ' (' . $this->hours_label($d['hours']) . ')') . '</td></tr><tr><th align="left">Add-ons</th><td>' . esc_html($addons ? implode(', ', $addons) : 'None') . '</td></tr><tr><th align="left">Estimated total</th><td><strong>' . esc_html($this->money($d['total'], $s['currency_symbol'])) . '</strong></td></tr><tr><th align="left">Notes</th><td>' . nl2br(esc_html($d['notes'] ?: 'None')) . '</td></tr></table>';
    }

    private function mail_headers(string $reply_name = '', string $reply_email = ''): array {
        $host = preg_replace('/^www\./', '', (string) wp_parse_url(home_url(), PHP_URL_HOST));
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ATeam Online Band Room Booking <wordpress@' . $host . '>',
        );
        $reply_email = sanitize_email($reply_email);
        $reply_name = trim(str_replace(array("\r", "\n"), '', sanitize_text_field($reply_name)));
        if ($reply_name && is_email($reply_email)) {
            $headers[] = 'Reply-To: ' . $reply_name . ' <' . $reply_email . '>';
        }
        return $headers;
    }

    private function total(float $hours, bool $recording, bool $engineer, array $s): float {
        $total = (float) $s['first_hour_rate'] + max(0, $hours - 1) * (float) $s['additional_hour_rate'];
        if ($recording) { $total += $hours * (float) $s['recording_hour_rate']; }
        if ($engineer) { $total += $hours * (float) $s['engineer_hour_rate']; }
        return round($total, 2);
    }
    private function number($value): string { return (string) max(0, (float) $value); }
    private function valid_time(string $time): string { return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time) ? $time : ''; }
    private function valid_date(string $date): bool { $value = DateTime::createFromFormat('Y-m-d', $date); return $value && $value->format('Y-m-d') === $date; }
    private function available_time(string $time, array $settings): bool {
        $minute = (int) $settings['time_interval'];
        $value = strtotime($time);
        return $value >= strtotime($settings['opening_time']) && $value <= strtotime($settings['closing_time']) && ((int) date('i', $value) % $minute === 0);
    }
    private function duration(string $start, string $end): float { return round((strtotime($end) - strtotime($start)) / HOUR_IN_SECONDS, 2); }
    private function money(float $value, string $symbol): string { return $symbol . number_format($value, 2); }
    private function rate_label(float $rate, string $symbol): string { return $rate > 0 ? $this->money($rate, $symbol) . ' per hour' : 'Included'; }
    private function hours_label(float $hours): string { return rtrim(rtrim(number_format($hours, 2), '0'), '.') . ($hours === 1.0 ? ' hour' : ' hours'); }
}

$GLOBALS['ateam_bandroom_booking'] = new ATeam_Bandroom_Booking();
$GLOBALS['ateam_bandroom_booking']->hooks();
