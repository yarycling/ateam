<?php

/**
 * Plugin Name: Credit/Debit Card (via WiPay)
 * Plugin URI: https://wipaycaribbean.com/
 * Description: <strong>WiPay</strong> is a payment facilitator and payment aggregator which provides this WooCommerce plugin to empower users to accept Credit/Debit Card payments online.
 * Version: 2.1.0
 * Author: WiPay Development Team
 * Author URI: https://github.com/WiPayDevelopment
 * Text Domain: wipay_credit
 */


/**
 * Prevent direct access to file from URL
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if WooCommerce is active; if not, exit.
 **/
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    exit;
}

/**
 * Define important constants and globals.
 */
global $total_nofee;
global $total_fee;
$total_nofee = 0;
$total_fee = 0;
const WIPAY_PLUGIN_DEFAULTS = [
    'advanced_security_enabled'         => 'yes',
    'auto_complete'                     => 'yes',
    'country_code'                      => 'TT',
    'developer_options_enable_logging'  => 'no',
    'enabled'                           => 'yes',
    'fee_checkout_show'                 => 'always',
    'fee_structure'                     => 'merchant_absorb',
    'image_size'                        => 'logo_sm.png',
    'live_account_number'               => '',
    'live_api_key'                      => '',
    'sandbox_enabled'                   => 'yes',
    'wpfn_get_configuration'            => 'https://tt.wipayfinancial.com/plugins/wpwc/configuration',
];
defined('WIPAY_PLUGIN_CLASS')       || define('WIPAY_PLUGIN_CLASS', 'WC_Gateway_Wipay');
defined('WIPAY_PLUGIN_DESCRIPTION') || define('WIPAY_PLUGIN_DESCRIPTION', get_file_data(__FILE__, array('Description' => 'Description'), false)['Description']);
defined('WIPAY_PLUGIN_ID')          || define('WIPAY_PLUGIN_ID', get_file_data(__FILE__, array('Text Domain' => 'Text Domain'), false)['Text Domain']);
defined('WIPAY_PLUGIN_NAME')        || define('WIPAY_PLUGIN_NAME', get_file_data(__FILE__, array('Plugin Name' => 'Plugin Name'), false)['Plugin Name']);
defined('WIPAY_PLUGIN_URI')         || define('WIPAY_PLUGIN_URI', get_file_data(__FILE__, array('Plugin URI' => 'Plugin URI'), false)['Plugin URI']);
defined('WIPAY_PLUGIN_VERSION')     || define('WIPAY_PLUGIN_VERSION', get_file_data(__FILE__, array('Version' => 'Version'), false)['Version']);

/**
 * HELPER FUNCTION: Logs important data for key features in the plugin.
 */
function wipay_log($value)
{
    if (wipay_get_option('developer_options_enable_logging') === 'yes') {
        if (is_null(wipay_get_option('unique_id'))) {
            wipay_configure();
        }
        if (!is_null(wipay_get_option('developer_options_logger_timezone'))) {
            date_default_timezone_set(wipay_get_option('developer_options_logger_timezone'));
        }
        $key = date('Y-m-d H:i:s');
        $dbt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        if (!is_null($dbt)) {
            $key .= (wipay_get_environment() === 'sandbox' ? ' *[' : ' [') . end(...[explode(wipay_get_separator($dbt[0]['file']), $dbt[0]['file'])]) . '@' . $dbt[1]['function'] . '():' . $dbt[0]['line'] . ']';
        }

        $log_filepath = wipay_filepath('/logs/logs_' . md5(plugin_dir_path(__FILE__) . wipay_get_option('unique_id')) . '.json');

        $log_filesize_number = number_format(0, 2, '.', '');
        if (file_exists($log_filepath)) {
            $log_filesize_number = number_format(filesize($log_filepath) / 1000, 2, '.', '');
        }
        // Ensure that the log file never exceeds 512 KB
        if (floatval($log_filesize_number) < 512.0) {
            $fp = fopen($log_filepath, 'a');
            fwrite($fp, json_encode([
                $key => $value,
            ]) . "\n");
            fclose($fp);
        }
    }
}

/**
 * HELPER FUNCTION: Makes constructing a direct file path to a file in the directory much easier.
 */
function wipay_filepath($path = '')
{
    return __DIR__ . preg_replace('/\//', DIRECTORY_SEPARATOR, $path);
}

/**
 * HELPER FUNCTION: Gets the current account_number based on configuration for GWPL.
 */
function wipay_get_account_number(string $environment = null)
{
    return strval(($environment ?? wipay_get_environment()) === 'sandbox'
        ? wipay_get_option('sandbox_account_number')
        : wipay_get_option('live_account_number'));
}

/**
 * HELPER FUNCTION: Gets the current account_number based on configuration for GWPL.
 */
function wipay_get_api_key(string $environment = null)
{
    return strval(($environment ?? wipay_get_environment()) === 'sandbox'
        ? wipay_get_option('sandbox_api_key')
        : wipay_get_option('live_api_key'));
}

/**
 * HELPER FUNCTION: Gets the current environment based on configuration for GWPL.
 */
function wipay_get_environment()
{
    return strval(strval(wipay_get_option('sandbox_enabled')) === 'yes'
        ? 'sandbox'
        : 'live');
}

/**
 * HELPER FUNCTION: Gets the directory separator character.
 */
function wipay_get_separator($path)
{
    return sizeof(explode("\\", $path)) > 1 ? "\\" : "/";
}

/**
 * HELPER FUNCTION: Get the stored option value in the DB for this plugin.
 */
function wipay_get_option($option)
{
    return isset(get_option('woocommerce_' . WIPAY_PLUGIN_ID . '_settings')[$option]) ? get_option('woocommerce_' . WIPAY_PLUGIN_ID . '_settings')[$option] : null;
}

/**
 * HELPER FUNCTION: API communication, parsing and logging in once function.
 */
function wipay_make_api_request(
    string $method,
    string $url,
    array $parameters
): array {
    $headers = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/x-www-form-urlencoded'
    ];
    $raw = wp_remote_request($url, [
        'method' => $method,
        'headers' => $headers,
        'body' => $parameters,
    ]);
    $response = json_decode(wp_remote_retrieve_body($raw), true);
    wipay_log([
        'url' => $url,
        'method' => $method,
        'headers' => $headers,
        'body' => $parameters,
        'raw' => $raw ?? [],
        'response' => $response ?? [],
    ]);
    return array_merge(['code' => $raw['response']['code'] ?? 500], $response);
}

/**
 * HELPER FUNCTION: Makes updating existing and new options for this plugin easy.
 */
function wipay_update_option(array $options)
{
    update_option('woocommerce_' . WIPAY_PLUGIN_ID . '_settings', array_merge(
        get_option('woocommerce_' . WIPAY_PLUGIN_ID . '_settings'),
        $options
    ));
}

/**
 * HELPER FUNCTION: Fetch the latest configuration for the plugin from the WiPay server, on demand.
 */
function wipay_configure()
{
    //
    error_log('wipay_configure');

    //
    $response = wipay_make_api_request(
        'GET',
        WIPAY_PLUGIN_DEFAULTS['wpfn_get_configuration'],
        ['country_code' => strval(wipay_get_option('country_code'))]
    );
    wipay_update_option($response ?? []);

    //
    if (is_null(wipay_get_option('unique_id'))) {
        wipay_update_option([
            'unique_id' => uniqid('', true),
        ]);
    }

    //
    return isset($response)
        ? 'success'
        : 'failed';
}

/**
 * HELPER FUNCTION: Fetch the latest details on any given WooCommerce order from the WiPay server, on demand.
 */
function wipay_manual_verify($order_id)
{
    $total = '';
    $transaction_id = '';
    $environment = wipay_get_environment();

    $order = wc_get_order($order_id);
    foreach ($order->get_meta_data() as $meta_data) {
        switch (strval($meta_data->key)) {
            case 'environment':
                $environment = $meta_data->value;
                break;
            case 'total_nofee':
                $total = number_format(floatval($meta_data->value), 2, '.', '');
                break;
            case 'transaction_id':
                $transaction_id = $meta_data->value;
                break;
        }
    }

    $body = [
        'country_code'   => strval(wipay_get_option('country_code')),
        'environment'    => strval($environment),
        'transaction_id' => strval($transaction_id),
    ];
    if (empty($transaction_id)) {
        $body = array_merge($body, [
            'account_number' => wipay_get_account_number($environment),
            'order_id'       => strval($order_id),
        ]);
    }
    $body = array_filter($body);

    $response = wipay_make_api_request(
        'GET',
        wipay_get_option('wpfn_get_transaction_result'),
        $body
    );
    if (isset($response)) {
        //
        $authenticated = false;
        $payment_status = 'unsuccessful';
        //
        if (isset($response['hash'])) {
            $authenticated = md5($body['transaction_id'] . $total . wipay_get_api_key($body['environment'])) === strval($response['hash']);
        }
        //
        if (isset($response['transaction_id']) && !isset($body['transaction_id'])) {
            $order->update_meta_data('transaction_id', $response['transaction_id']);
        }
        //
        if (isset($response['payment_status'])) {
            $payment_status = $response['payment_status'];
            switch ($payment_status) {
                case 'successful':
                    if (strval(wipay_get_option('auto_complete')) === 'yes') {
                        $order->update_status('completed');
                    } else {
                        $order->update_status('processing');
                    }
                    break;
                case 'unsuccessful':
                default:
                    $order->update_status('failed');
                    break;
            }
        }
        //
        if (isset($response['details'])) {
            $details = $response['details'];
            if ($payment_status === 'successful' && !$authenticated) {
                $auth_fail_message = $response['auth_fail_message'] ?? 'Authentication unsuccessful! Hash check failed.';
                $details = "{$details} {$auth_fail_message}";
            }
            $order->update_meta_data('transaction_details', $details);
        }
    } else {
        //
        $order->update_status('failed');
        $order->update_meta_data('transaction_details', '[0-R1]: No WiPay response. Please contact WiPay for further assistance.');
    }
    $order->save();

    return isset($response)
        ? 'success'
        : 'failed';
}

/**
 * HELPER FUNCTION: Fetch the user's latest Fee configuration and calculates the fee, all from WiPay's server, on demand.
 */
function wipay_get_fees($amount = '0')
{
    return wipay_make_api_request(
        'GET',
        wipay_get_option('wpfn_get_fees'),
        [
            'amount_raw'     => $amount,
            'amount'         => strval(number_format((is_numeric($amount) ? $amount : '0'), 2, '.', '')),
            'account_number' => wipay_get_account_number(),
            'country_code'   => strval(wipay_get_option('configured_country_code')),
            'environment'    => wipay_get_environment(),
            'fee_structure'  => strval(wipay_get_option('fee_structure')),
            'currency'       => strtoupper(strval(get_woocommerce_currency())),
        ]
    );
}

/**
 * HELPER FUNCTION: Selectively load/enqueue css, js and logic based on the current URI.
 */
function wipay_page_selector()
{
    function in_url($needle)
    {
        return (bool) preg_match("/{$needle}/i", $_SERVER['REQUEST_URI']);
    }

    if (in_url('wp-admin')) {
        if (in_url('wc-settings')) {
            wp_enqueue_script('wipay_admin_wc_settings', plugins_url('/js/admin_wc_settings.js', __FILE__), array('jquery'), WIPAY_PLUGIN_VERSION, true);
        }
        if (in_url(strtolower(WIPAY_PLUGIN_CLASS)) || in_url(strtolower(WIPAY_PLUGIN_ID))) {
            wp_enqueue_style('wipay_settings_css', plugins_url('/css/settings.css', __FILE__));

            wp_enqueue_script('wipay_cookie', plugins_url('/js/lib/cookie.js', __FILE__), array('jquery'), WIPAY_PLUGIN_VERSION, true);
            wp_enqueue_script('wipay_utils', plugins_url('/js/lib/utils.js', __FILE__), array('jquery'), WIPAY_PLUGIN_VERSION, true);

            // load script to add preview styles and logic
            wp_enqueue_script('wipay_settings', plugins_url('/js/settings.js', __FILE__), array('jquery'), WIPAY_PLUGIN_VERSION, true);
            wp_localize_script('wipay_settings', 'options', get_option('woocommerce_' . WIPAY_PLUGIN_ID . '_settings'));
            wp_localize_script('wipay_settings', 'version', WIPAY_PLUGIN_VERSION);
            wp_localize_script('wipay_settings', 'woocommerce', [
                'currency' => get_woocommerce_currency(),
            ]);
            wp_localize_script('wipay_settings', 'wipay', [
                'logfile_name' => 'logs_' . md5(plugin_dir_path(__FILE__) . wipay_get_option('unique_id')) . '.json',
                'logfile_url' => plugins_url('/logs/logs_' . md5(plugin_dir_path(__FILE__) . wipay_get_option('unique_id')) . '.json', __FILE__),
                'logo' => plugins_url('/img/wipay/logo.png', __FILE__),
                'utils_url' => plugins_url('/js/lib/utils.js', __FILE__),
            ]);

            $status_clear = 'failed';
            $cookie_clear = 'clear_logs_request';
            if (isset($_COOKIE[$cookie_clear]) && !is_null($_COOKIE[$cookie_clear])) {
                file_put_contents(wipay_filepath('/logs/logs_' . md5(plugin_dir_path(__FILE__) . wipay_get_option('unique_id')) . '.json'), "");
                $status_clear = 'success';
            }
            wp_localize_script('wipay_settings', 'response', [
                'status_clear' => $status_clear,
            ]);
            wp_localize_script('wipay_settings', 'cookie', [
                'update' => constant(WIPAY_PLUGIN_CLASS . "::RESYNC_COOKIE_NAME"),
                'clear' => $cookie_clear,
            ]);
        }
    }
    if (!in_url('wp-admin')) {
        if (in_url('checkout') || in_url('cart') || in_url('order-pay')) {

            // for order-pay page, determine if the order is stored as an order made through the WiPay plugin
            $is_wipay = false;
            if (in_url('order-pay')) {
                $is_wipay = wc_get_order((int) wc_get_order_id_by_order_key($_REQUEST['key']))->get_payment_method() === WIPAY_PLUGIN_ID;
            }

            // load jQuery to add fee styles and logic
            wp_enqueue_script('wipay_checkout', plugins_url('/js/checkout.js', __FILE__), array('jquery'), WIPAY_PLUGIN_VERSION, true);
            wp_localize_script('wipay_checkout', 'options', [
                'image_size' => wipay_get_option('image_size'),
                'is_wipay' => $is_wipay,
            ]);
        }
    }
}

/**
 * Set timeout to 15s for HTTP requests.
 */
add_filter('http_request_timeout', function ($time) {
    return 15;
});

/**
 * When the plugin is installed, fetch and store all important plugin options based on configuration from wipay server
 */
register_activation_hook(__FILE__, function () {
    update_option('woocommerce_' . WIPAY_PLUGIN_ID . '_settings', array_merge(
        WIPAY_PLUGIN_DEFAULTS,
        wipay_make_api_request(
            'GET',
            WIPAY_PLUGIN_DEFAULTS['wpfn_get_configuration'],
            ['country_code' => WIPAY_PLUGIN_DEFAULTS['country_code']]
        )
    ));
    if (is_null(wipay_get_option('unique_id'))) {
        wipay_update_option([
            'unique_id' => uniqid('', true)
        ]);
    }
});

/**
 * Define and initialize WiPay's plugin class.
 */
function wipay_init_gateway()
{
    error_log('wipay_init_gateway');
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }
    // define the wipay gatway class with the name defined as the WIPAY_PLUGIN_CLASS constant
    eval(str_replace('CLASSNAME', WIPAY_PLUGIN_CLASS, str_replace('<?php', '', file_get_contents(wipay_filepath('/class.php')))));

    // always keep plugin options configured to the correct platform/country
    if (is_null(wipay_get_option('configured_country_code')) || wipay_get_option('configured_country_code') !== wipay_get_option('country_code')) {
        wipay_configure();
    }
}
add_action('plugins_loaded', 'wipay_init_gateway', 0, 0);

/**
 * Call the function that will load the correct css, js and/or logic for the user's current wp page.
 * 
 * This is called after plugins_loaded action, so we can properly fetch woocommerce orders on the fly.
 */
function wipay_init()
{
    wipay_page_selector();
}
add_action('init', 'wipay_init', 10, 0);

/**
 * Hook into the update option callback to ensure that the plugin options are configured to the correct platform/country.
 */
function wipay_update_hook($old_value, $new_value)
{
    if (array_key_exists('country_code', array_diff($new_value, $old_value))) {
        wipay_configure();
    }
    if ($new_value['developer_options_enable_logging'] !== $old_value['developer_options_enable_logging'] && $new_value['developer_options_enable_logging'] === 'yes') {
        wipay_log(plugin_dir_path(__FILE__) . wipay_get_option('unique_id'));
        wipay_log(get_option('woocommerce_' . WIPAY_PLUGIN_ID . '_settings'));
    }
}
add_action('update_option' . '_woocommerce_' . WIPAY_PLUGIN_ID . '_settings', 'wipay_update_hook', 10, 2);

/**
 * Link to the plugin settings directly from the WP Admin > Plugins screen.
 */
function wipay_register_settings($links)
{
    $settings_link = '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=' . strtolower(WIPAY_PLUGIN_CLASS)) . '">Settings</a>';
    array_push($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wipay_register_settings', 10, 1);

/**
 * Adds meta links to the plugin in the WP Admin > Plugins screen.
 */
function wipay_add_plugin_meta_links($links)
{
    $is_wipay_plugin = false;
    $version_index = null;
    $site_index = null;
    foreach ($links as $index => $link) {
        if (strpos(json_encode($link), 'Version') !== false) {
            $version_index = $index;
        }
        if (strpos(json_encode($link), 'Visit plugin site') !== false) {
            $site_index = $index;
        }
        if (strpos(json_encode($link), 'WiPay Development Team') !== false) {
            $is_wipay_plugin = true;
        }
    }

    if ($is_wipay_plugin) {
        $is_wipay_plugin = false;
        if (!is_null($version_index)) {
            $links[$version_index] = 'Version ' . WIPAY_PLUGIN_VERSION . '-' . get_option('woocommerce_wipay_credit_settings')['country_code'];
        }
        if (!is_null($site_index)) {
            $links[$site_index] = '<a href="' . wipay_get_option('wpca_home') . '">' . esc_html__('Visit plugin site', WIPAY_PLUGIN_ID) . '</a>';
        }
        $links[] = '<a href="' . wipay_get_option('wpca_documentation') . '">' . esc_html__('Documentation', WIPAY_PLUGIN_ID) . '</a>';
    }

    return $links;
}
add_filter("plugin_row_meta", 'wipay_add_plugin_meta_links', 10, 1);

/**
 * Register a custom payment gateway for WooCommerce.
 */
function wipay_add_credit_payment_gateway($methods)
{
    $methods[] = WIPAY_PLUGIN_CLASS;
    return $methods;
}
add_filter('woocommerce_payment_gateways', 'wipay_add_credit_payment_gateway', 10, 1);

/**
 * When in the item edit order page, add wipay css, js and logic to support the manual verify functionality.
 * 
 * Refer: wipay_manual_verify()
 */
function wipay_manual_verify_request($item_id, $item, $product)
{
    // Only for "line item" order items
    if (!$item->is_type('line_item')) {
        return;
    }

    if (gettype($product) != 'object' || !method_exists($product, 'get_id')) {
        return;
    }

    if ($product->get_id() && is_admin()) {
        // if the cookie is present (set in js via button click)
        $status = 'failed';
        $cookie_name = 'get_verify_request';
        if (array_key_exists($cookie_name, $_COOKIE) && !is_null($_COOKIE[$cookie_name])) {
            $status = wipay_manual_verify((int) $item->get_order_id());
        }
        // enqueue all the required scripts
        wp_enqueue_script('wipay_cookie', plugins_url('/js/lib/cookie.js', __FILE__), array('jquery'), WIPAY_PLUGIN_VERSION, true);
        wp_enqueue_script('wipay_utils', plugins_url('/js/lib/utils.js', __FILE__), array('jquery'), WIPAY_PLUGIN_VERSION, true);
        wp_enqueue_script('wipay_post', plugins_url('/js/post.js', __FILE__), array('jquery'), WIPAY_PLUGIN_VERSION, true);
        wp_localize_script('wipay_post', 'order', [
            'currency' => wc_get_order($item->get_order_id())->get_currency(),
        ]);
        wp_localize_script('wipay_post', 'cookie', [
            'name' => $cookie_name,
        ]);
        wp_localize_script('wipay_post', 'response', [
            'status' => $status,
        ]);
    }
}
add_action('woocommerce_after_order_itemmeta', 'wipay_manual_verify_request', 10, 3);

/**
 * Modifies the Total at Checkout to reflect a correct grand Total.
 */
function custom_calculated_total($total)
{
    global $fee_calculations;

    // if the payment method is not this plugin return what was sent
    if (WC()->session->get('chosen_payment_method') === WIPAY_PLUGIN_ID) {
        return number_format($fee_calculations['credit_card']['cus_t'] ?? $total, 2, '.', '');
    } else {
        return number_format($total, 2, '.', '');
    }
}
add_filter('woocommerce_calculated_total', 'custom_calculated_total');

/**	
 * Modifies the Orders table in the admin panel to reflect useful WiPay information on each Order.
 */
function wipay_add_checkout_fee_for_gateway()
{
    global $woocommerce;
    global $total_nofee;
    global $total_fee;
    global $fee_calculations;

    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    // Subtotal calculation
    $subtotal_before_tax = $woocommerce->cart->get_totals()['subtotal'];
    $subtotal_taxes_total = $woocommerce->cart->get_totals()['subtotal_tax'];
    $subtotal_with_tax = $subtotal_before_tax + $subtotal_taxes_total;

    // Shipping calculation
    $shipping_before_tax = $woocommerce->cart->get_totals()['shipping_total'];
    $shipping_taxes_total = 0;
    foreach ($woocommerce->cart->get_totals()['shipping_taxes'] as $key => $value) {
        $shipping_taxes_total += $value;
    }
    $shipping_with_tax = $shipping_before_tax + $shipping_taxes_total;

    // Discount calculation
    $discount_before_tax = $woocommerce->cart->get_totals()['discount_total'];
    $discount_taxes_total = $woocommerce->cart->get_totals()['discount_tax'];
    $discount_with_tax = $discount_before_tax - $discount_taxes_total;

    // Cart Contents calculation
    $cart_contents_before_tax = $woocommerce->cart->get_totals()['cart_contents_total'];
    $cart_contents_taxes_total = 0;
    foreach ($woocommerce->cart->get_totals()['cart_contents_taxes'] as $key => $value) {
        $cart_contents_taxes_total += $value;
    }
    $cart_contents_with_tax = $cart_contents_before_tax + $cart_contents_taxes_total;

    // Select the larger of the two (if it occurs)
    $subtotal = $subtotal_with_tax;
    if (number_format($cart_contents_with_tax, 2, '.', '') > number_format($subtotal_with_tax, 2, '.', '')) {
        $subtotal = $cart_contents_with_tax;
    }

    // Fee calculation
    $fee_before_tax = $woocommerce->cart->get_totals()['fee_total'];
    $fee_taxes_total = 0;
    foreach ($woocommerce->cart->get_totals()['fee_taxes'] as $key => $value) {
        $fee_taxes_total += $value;
    }
    $fee_with_tax = $fee_before_tax + $fee_taxes_total;

    // Cart Total (unaffected by WiPay's fee)
    $total_nofee = number_format((($subtotal - $discount_with_tax) + $shipping_with_tax + $fee_with_tax), 2, '.', '');

    // Get the fees for this total, based on the plugin's configuration
    $fee_calculations = wipay_get_fees($total_nofee);

    // Customer's Total WiPay fee for Credit/Debit Card
    $total_fee = number_format($fee_calculations['credit_card']['cus_t_fee'], 2, '.', '');

    // Add (and show) the fee only if it's this plugin
    if (WC()->session->get('chosen_payment_method') === WIPAY_PLUGIN_ID) {
        if (!is_null($fee_calculations['credit_card'])) {
            switch (wipay_get_option('fee_checkout_show')) {
                case 'always':
                    // Always show the fee
                    WC()->cart->add_fee('WiPay Transaction Fee: ' . $fee_calculations['credit_card']['fee_name'] . ' (' . $fee_calculations['credit_card']['cus_desc'] . ')', $total_fee);
                    break;
                case 'significant':
                    // Only show the fee if it's over $0.00
                    if (floatval($total_fee) > 0) {
                        WC()->cart->add_fee('WiPay Transaction Fee: ' . $fee_calculations['credit_card']['fee_name'] . ' (' . $fee_calculations['credit_card']['cus_desc'] . ')', $total_fee);
                    }
                    break;
                case 'never':
                    // Don't show the fee
                    break;
            }
        } else {
            WC()->cart->add_fee('WiPay Transaction Fee: '  . 'ERROR - ' . isset($fee_calculations['message']) ? $fee_calculations['message'] : 'Unexpected error. Please contant WiPay for further assistance.', number_format(0, 2, '.', ''));
        }
    }
}
add_action('woocommerce_cart_calculate_fees', 'wipay_add_checkout_fee_for_gateway', 10, 0);

/**
 * Hook into when the Order is updated at checkout to add some WiPay-specific Order metadata.
 * 
 * This metadata is useful to show more WiPay-specifc detail about each Order.
 */
function wipay_update_order_meta($order_id)
{
    global $total_nofee;
    global $total_fee;

    $order = new WC_Order($order_id);
    $order->update_meta_data('environment', wipay_get_environment());
    $order->update_meta_data('fee_structure', get_option('woocommerce_wipay_credit_settings')['fee_structure']);
    $order->update_meta_data('total_nofee', $total_nofee);
    $order->update_meta_data('total_fee', $total_fee);
    $order->save();
}
add_action('woocommerce_checkout_update_order_meta', 'wipay_update_order_meta', 10, 1);

/**
 * Add custom text to the 'Place Order' button at Checkout.
 */
function wipay_add_custom_button_text($available_gateways)
{
    // applied to Checkout page only
    if (!is_checkout()) {
        return $available_gateways;
    }
    if (array_key_exists(WIPAY_PLUGIN_ID, $available_gateways)) {
        $available_gateways[WIPAY_PLUGIN_ID]->order_button_text = __('Pay for order', 'woocommerce');
    }
    return $available_gateways;
}
add_filter('woocommerce_available_payment_gateways', 'wipay_add_custom_button_text');

/**
 * Adds some custom-defined admin Order table headers.
 */
function wipay_add_custom_order_headers($columns)
{
    $new_columns = array();

    foreach ($columns as $column_name => $column_info) {
        $new_columns[$column_name] = $column_info;
        if ($column_name === 'order_status') {
            $new_columns['order_transaction_id']        = __('Transaction ID', WIPAY_PLUGIN_ID);
            $new_columns['order_transaction_details']   = __('Transaction Details', WIPAY_PLUGIN_ID);
            $new_columns['order_total_nofee']           = __('Total<br>(no Fee)', WIPAY_PLUGIN_ID);
        }
        if ($column_name === 'order_total') {
            $new_columns[$column_name]                  = __('Total<br>(Paid)', WIPAY_PLUGIN_ID);
            $new_columns['order_currency']              = __('Currency', WIPAY_PLUGIN_ID);
        }
    }
    return $new_columns;
}
add_filter('manage_edit-shop_order_columns', 'wipay_add_custom_order_headers', 10, 1);

/**
 * Correlate the custom-defined admin Order table headers with Order metadata.
 */
function wipay_add_custom_order_data($column)
{
    global $post;
    if (class_exists('WOOCS')) {
        global $WOOCS;
    }

    // load custom css for admin orders page
    wp_enqueue_style('wipay_shop_order_css', plugins_url('/css/shop_order.css', __FILE__));

    // load jquery to add fee styles and logic
    wp_enqueue_script('wipay_cookie', plugins_url('/js/lib/cookie.js', __FILE__), array('jquery'), WIPAY_PLUGIN_VERSION, true);
    wp_enqueue_script('wipay_utils', plugins_url('/js/lib/utils.js', __FILE__), array('jquery'), WIPAY_PLUGIN_VERSION, true);

    $cookie_name = 'get_admin_verify_request';
    if (isset($_COOKIE[$cookie_name]) && !is_null($_COOKIE[$cookie_name])) {
        $status = wipay_manual_verify((int) $_COOKIE[$cookie_name]);
        wp_localize_script('wipay_utils', 'response', [
            'order_id' => $_COOKIE[$cookie_name],
            'status' => $status,
        ]);
        unset($_COOKIE[$cookie_name]);
    }
    wp_enqueue_script('wipay_shop_order', plugins_url('/js/shop_order.js', __FILE__), array('jquery'), WIPAY_PLUGIN_VERSION, true);
    wp_localize_script('wipay_shop_order', 'cookie', [
        'name' => $cookie_name,
    ]);
    wp_localize_script('wipay_shop_order', 'payment', [
        'method' => WIPAY_PLUGIN_ID,
    ]);

    $environment = '';
    $transaction_id = '';
    $transaction_details = '';
    $total_nofee = number_format(0, 2, '.', '');
    foreach (wc_get_order($post->ID)->get_meta_data() as $meta_data) {
        if ($meta_data->key === 'environment') {
            $environment = $meta_data->value;
        }
        if ($meta_data->key === 'transaction_id') {
            $transaction_id = $meta_data->value;
        }
        if ($meta_data->key === 'transaction_details') {
            $transaction_details = $meta_data->value;
        }
        if ($meta_data->key === 'total_nofee') {
            $total_nofee = number_format($meta_data->value, 2, '.', '');
            if (class_exists('WOOCS')) {
                $total_nofee = number_format($meta_data->value, 2, $WOOCS->decimal_sep, '');
            }
        }
    }

    if ($column === 'order_environment') {
        echo strtoupper($environment);
    }
    if ($column === 'order_transaction_id') {
        echo $transaction_id;
    }
    if ($column === 'order_transaction_details') {
        echo $transaction_details;
    }
    if ($column === 'order_total_nofee') {
        if (class_exists('WOOCS')) {
            $converted_total = number_format($total_nofee / $WOOCS->get_currencies()[wc_get_order($post->ID)->currency]['rate'], 2, $WOOCS->decimal_sep, '');
            if ($total_nofee === $converted_total) {
                echo wc_price($total_nofee);
            } else {
                echo wc_price($total_nofee) . '<br><small>(' . wc_price($converted_total) . ' ' . get_woocommerce_currency() . ')</small>';
            }
        } else {
            echo wc_price($total_nofee);
        }
    }
    if ($column === 'order_total') {
        $total = wc_get_order($post->ID)->get_total();
        if (class_exists('WOOCS')) {
            $converted_total = number_format($total / $WOOCS->get_currencies()[wc_get_order($post->ID)->currency]['rate'], 2, $WOOCS->decimal_sep, '');
            if ($total !== $converted_total) {
                echo '<small class="conv"><br>(' . wc_price($converted_total) . ' ' . get_woocommerce_currency() . ')</small>';
            }
        }
    }
    if ($column === 'order_currency') {
        echo wc_get_order($post->ID)->get_currency();
    }
}
add_action('manage_shop_order_posts_custom_column', 'wipay_add_custom_order_data', 10, 1);

/**
 * On the Thank You page, show a themed WooCommerce notice about some details of the Transaction for that particular order, for payments through this plugin only.
 * 
 * Before the page is displayed, get the transaction result from WiPay's server and then update the order data on woocommerce.
 */
function wipay_add_content_thankyou_wipay_credit()
{
    $total = '';
    $transaction_id = '';
    $environment = wipay_get_environment();
    $order_id = wc_get_order_id_by_order_key($_REQUEST['key']);

    $order = wc_get_order($order_id);
    foreach ($order->get_meta_data() as $meta_data) {
        switch (strval($meta_data->key)) {
            case 'environment':
                /**
                 * Prefer getting $environment from the order meta data (incase it was changed via settings)
                 * 
                 * This ensures that the request stays true respective to the transaction.
                 */
                $environment = $meta_data->value;
                break;
            case 'total_nofee':
                /**
                 * NOTE: This is used for hash validation on successful payment responses.
                 */
                $total = number_format(floatval($meta_data->value), 2, '.', '');
                break;
            case 'transaction_id':
                /**
                 * NOTE: This should always be present.
                 */
                $transaction_id = $meta_data->value;
                break;
        }
    }

    $body = [
        'country_code'   => strval(wipay_get_option('country_code')),
        'environment'    => strval($environment),
        'transaction_id' => strval($transaction_id),
    ];
    if (empty($transaction_id)) {
        $body = array_merge($body, [
            'account_number' => wipay_get_account_number($environment),
            'order_id'       => strval($order_id),
        ]);
    }
    $body = array_filter($body);

    $response = wipay_make_api_request(
        'GET',
        wipay_get_option('wpfn_get_transaction_result'),
        $body
    );
    if (isset($response)) {
        //
        $authenticated = false;
        $payment_status = 'unsuccessful';
        //
        if (isset($response['hash'])) {
            $authenticated = md5($body['transaction_id'] . $total . wipay_get_api_key($body['environment'])) === strval($response['hash']);
        }
        //
        if (isset($response['transaction_id']) && !isset($body['transaction_id'])) {
            $order->update_meta_data('transaction_id', $response['transaction_id']);
        }
        //
        if (isset($response['payment_status'])) {
            $payment_status = $response['payment_status'];
            switch ($payment_status) {
                case 'successful':
                    if (strval(wipay_get_option('auto_complete')) === 'yes') {
                        $order->update_status('completed');
                    } else {
                        $order->update_status('processing');
                    }
                    break;
                case 'unsuccessful':
                default:
                    $order->update_status('failed');
                    break;
            }
        }
        //
        if (isset($response['details'])) {
            $details = $response['details'];
            if ($payment_status === 'successful' && !$authenticated) {
                $auth_fail_message = $response['auth_fail_message'] ?? 'Authentication unsuccessful! Hash check failed.';
                $details = "{$details} {$auth_fail_message}";
            }
            $order->update_meta_data('transaction_details', $details);
        }
        //
        $response['authenticated'] = $authenticated ? 'yes' : 'no';
    } else {
        //
        $transaction_details = '[0-R0]: No WiPay response. Please try Manual Verification.';
        $order->update_status('failed');
        $order->update_meta_data('transaction_details', $transaction_details);
        //
        $tr = '<tr>
			<th style="text-align:right!important;">Account #</th>
			<td style="text-align:left!important;">' . wipay_get_account_number($environment) . '</td>
		</tr>';
        $tr = $tr . '<tr>
			<th style="text-align:right!important;">Order ID</th>
			<td style="text-align:left!important;">' . $order_id . '</td>
		</tr>';
        if (!empty($transaction_id)) {
            $tr = '<tr>
				<th style="text-align:right!important;">Transaction ID</th>
				<td style="text-align:left!important;">' . $transaction_id . '</td>
			</tr>';
        }
        $tr = '<tr><td style="text-align:center!important;" colspan="2">' . $transaction_details . '</td></tr>' . $tr;
        $response['message'] = '
		<div class="woocommerce-notices-wrapper">
			<div class="woocommerce-error" role="alert">
				<div style="padding:0!important;margin:0!important;text-align:center!important;">
					<h3 style="padding:0!important;margin:0!important;color:black!important;">
						<span style="font-weight:600!important;">
							Sorry, no verification response was received from WiPay. Please contact the site manager to Manually Verify this Order.
						</span>
					</h3> 
				</div>
				<table style="padding:0!important;margin:1em 0 1em 0!important;width:100%;color:black!important;">
                    <colgroup>
                        <col span="1" style="width: 35%;">
                        <col span="1" style="width: 65%;">
                    </colgroup>
                    ' . $tr . '
				</table>
				<div style="padding:0!important;margin:0!important;text-align:center!important;color:black!important;">
					<small>
						If you believe you are seeing this message in error, please <a href="' . (wipay_get_option('wpca_contact_us') ?? '') . '" style="text-decoration:underline!important; font-weight:500!important; color:#4169e1!important;">Contact Us at WiPay</a> to enquire further.
					</small>
				</div>
			</div>
		</div>
		';
    }
    $order->save();

    wp_enqueue_script('wipay_order_received', plugins_url('/js/order_received.js', __FILE__), array('jquery'), WIPAY_PLUGIN_VERSION, true);
    wp_localize_script('wipay_order_received', 'response', $response);
}
add_action('woocommerce_thankyou_wipay_credit', 'wipay_add_content_thankyou_wipay_credit', 10, 0);
