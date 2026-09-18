<?php

/**
 * Prevent direct access to file from URL
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * This class will inherit the CLASSNAME defined as constant WIPAY_PLUGIN_CLASS.
 * 
 * This was deliberately done, so as to define the name of this class in one place.
 */
class CLASSNAME extends WC_Payment_Gateway
{
    // This is used to keep the spacer IDs unique for the admin configuration form.
    public $index = null;

    // This is used to get the Update button press from clientside to perform a wipay_configuration() serverside
    const RESYNC_COOKIE_NAME = 'get_configuration_request';

    /**
     * HELPER FUNCTION: Uniquely name the spacer elements in the admin settings - so they show up properly.
     */
    private function next_spacer_index()
    {
        if (is_null($this->index)) {
            $this->index = 0;
        }
        $this->index += 1;
        return $this->index;
    }

    /**
     * HELPER FUNCTION: build the platform select array for the settings form field based on the stored option after configuring the plugin
     */
    private function wipay_get_supported_platforms()
    {
        // defaults
        $this->country_code_default = '';
        $this->country_code_options = [];

        // from the plugin's configuration, get the supported platforms and build the appropriate array for the form field
        foreach (wipay_get_option('supported_platforms') as $country_code => $country_description) {
            // only options that do not have disabled attached will be added to the list
            if (!preg_match("/" . "disable" . "/i", $country_description)) {
                // set the default country_code based on the description in this array
                if (preg_match("/" . "default" . "/i", $country_description)) {
                    $this->country_code_default = $country_code;
                }
                $this->country_code_options[$country_code] = __($country_description, 'woocommerce');
            }
        }
    }

    /**
     * HELPER FUNCTION: build the fee structure select array for the settings form field based on the stored option after configuring the plugin
     */
    private function wipay_get_supported_fee_structures()
    {
        // defaults
        $this->fee_structure_default = '';
        $this->fee_structure_options = [];

        // from the plugin's configuration, get the supported platforms and build the appropriate array for the form field
        foreach (wipay_get_option('supported_fee_structures') as $fee_structure => $fee_structure_description) {
            // only options that do not have disabled attached will be added to the list
            if (!preg_match("/" . "disable" . "/i", $fee_structure_description)) {
                // set the default fee_structure based on the description in this array
                if (preg_match("/" . "default" . "/i", $fee_structure_description)) {
                    $this->fee_structure_default = $fee_structure;
                }
                $this->fee_structure_options[$fee_structure] = __($fee_structure_description, 'woocommerce');
            }
        }
    }

    /**
     * Create a new WiPay Gateway instance.
     */
    public function __construct()
    {
        // Define some required class variables
        $this->id                   = WIPAY_PLUGIN_ID;
        $this->has_fields           = true;
        $this->method_title         = WIPAY_PLUGIN_NAME;
        $this->method_description   = WIPAY_PLUGIN_DESCRIPTION;
        $this->title                = wipay_get_environment() === 'sandbox' ? 'Credit/Debit Card (SANDBOX)' : 'Credit/Debit Card';
        $this->icon                 = plugins_url('/img/' . wipay_get_option('image_size'), __FILE__);

        // Setup the admin settings form
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();

        // Save options
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        // Payment listener/API hook
        add_action('woocommerce_api_' . strtolower(WIPAY_PLUGIN_CLASS), array($this, 'wipay_response'));
    }

    /**
     * Define all the form fields for setting options for this plugin in the admin panel.
     */
    function init_form_fields()
    {
        // if the cookie is present (set in js via button click)
        if (isset($_COOKIE[self::RESYNC_COOKIE_NAME]) && !is_null($_COOKIE[self::RESYNC_COOKIE_NAME])) {
            wp_localize_script('wipay_settings', 'response', [
                'status_update' => wipay_configure(),
            ]);
        }
        // configure the plugin before attaching front-end logic to the form fields
        $this->wipay_get_supported_platforms();
        $this->wipay_get_supported_fee_structures();

        $log_filename = 'logs_' . md5(plugin_dir_path(__FILE__) . wipay_get_option('unique_id')) . '.json';
        $log_filepath = wipay_filepath('/logs/' . $log_filename);
        $log_filesize = number_format(0, 2, '.', ',') . ' KB';
        $log_filesize_number = number_format(0, 2, '.', '');
        if (file_exists($log_filepath)) {
            $log_filesize = number_format(filesize($log_filepath) / 1000, 2, '.', ',') . ' KB';
            $log_filesize_number = number_format(filesize($log_filepath) / 1000, 2, '.', '');
        }

        $this->form_fields = [
            'main_settings_title' => [
                'title'         => __('<hr><span class="dashicons dashicons-star-filled"></span> Main Settings', 'woocommerce'),
                'type'          => 'title',
                'description'   => __('These settings directly control how this plugin interacts with your Store and your WiPay account.', 'woocommerce')
            ],
            'enabled' => [
                'title'     => __('Enable Plugin', 'woocommerce'),
                'type'      => 'checkbox',
                'label'     => __('
					<br>
					If <strong>checked</strong>, this plugin will be <u>enabled</u> and the Credit/Debit Card option <u>will</u> appear at checkout. <small>(default)</small><br>
					If <strong>unchecked</strong>, this plugin will be <u>disabled</u> and the Credit/Debit Card option <u>will not</u> appear at checkout.
				', 'woocommerce'),
                'default'   => WIPAY_PLUGIN_DEFAULTS['enabled']
            ],
            'sandbox_enabled' => [
                'title'     => __('SandBox Mode', 'woocommerce'),
                'type'      => 'checkbox',
                'label'     => __('
					<br>
					If <strong>checked</strong> <small>(default)</small>:<br>
					&nbsp;&nbsp;&nbsp;&nbsp;i. payments will be within the <u>SandBox</u> environment<br>
					&nbsp;&nbsp;&nbsp;&nbsp;ii. the <u>SandBox Credentials</u> will be used<br>
					If <strong>unchecked</strong>:<br>
					&nbsp;&nbsp;&nbsp;&nbsp;i. payments will be within the <u>LIVE</u> environment<br>
					&nbsp;&nbsp;&nbsp;&nbsp;ii. the <u>LIVE Credentials</u> will be used
				', 'woocommerce'),
                'default'   => WIPAY_PLUGIN_DEFAULTS['sandbox_enabled']
            ],
            'country_code' => [
                'title'         => __('Platform', 'woocommerce'),
                'type'          => 'select',
                'description'   => __('
					This controls which platform region this plugin will communicate with to process transactions.<br>
					<br>
					This should be the platform region you\'ve registered with for your WiPay Account.
				', 'woocommerce'),
                'default'       => $this->country_code_default,
                'desc_tip'      => true,
                'options'       => $this->country_code_options,
                'css'           => 'min-width:150px;'
            ],
            'currency_info' => [
                'title' => __('Currency', 'woocommerce'),
                'type'  => 'checkbox',
                'label' => __('Your <i>store\'s currency</i> is determined by your <u>WooCommerce configuration</u>. Your <i>store\'s current currency</i> is shown below. To change this <i>currency</i>, please press the <span style="color: #007cba !important; font-weight: bold !important;">Change</span> button below to go to your <u>WooCommerce settings</u>. There, you may configure your preferred <i>currency</i> under the <strong>Currency options</strong> section.', 'woocommerce')
            ],
            'currency_box' => [
                'title' => __(' ', 'woocommerce'),
                'type'  => 'checkbox',
                'label' => __('
					<div id="currency_box">
						<div id="currency_current">
							Current currency:<br>
							<br>
							<strong id="wipay_currency" style="font-size: 16px !important;">' . get_woocommerce_currency() . '</strong>
						</div>
						<div id="currency_settings">
							<a id="wc_settings" class="button-primary" href="' . admin_url('admin.php?page=wc-settings') . '">
								<div id="wc_settings_icon"><span class="dashicons dashicons-update"></span></div>
								<div id="wc_settings_text">Change</div>
							</a>
						</div>
					</div>
				', 'woocommerce')
            ],
            'spacer_' . $this->next_spacer_index() => [
                'title' => __(' ', 'woocommerce'),
                'type'  => 'title'
            ],
            'general_settings_title' => [
                'title'         => __('<hr><span class="dashicons dashicons-admin-generic"></span> General Settings', 'woocommerce'),
                'type'          => 'title',
                'description'   => __('These settings let you directly control and configure this plugin\'s behaviour and actions', 'woocommerce')
            ],
            'advanced_security' => [
                'title'     => __('Advanced Security', 'woocommerce'),
                'type'      => 'checkbox',
                'label'     => __('
					<br>
					If <strong>checked</strong>, enhanced security will be enforced at Checkout. <u>LIVE</u> environment only. <small>(default)</small><br>
					If <strong>unchecked</strong>, default security will be enforced at Checkout.
				', 'woocommerce'),
                'default'   => WIPAY_PLUGIN_DEFAULTS['advanced_security_enabled']
            ],
            'auto_complete' => [
                'title'     => __('Auto Complete', 'woocommerce'),
                'type'      => 'checkbox',
                'label'     => __('
					<br>
					If <strong>checked</strong>, this plugin will change each Order\'s <u>Status</u> from <strong style="color: #777777;">Pending</strong> to <strong style="color: #0000ff;">Complete</strong> upon successful payment. <small>(default)</small><br>
					If <strong>unchecked</strong>, this plugin will change each Order\'s <u>Status</u> from <strong style="color: #777777;">Pending</strong> to <strong style="color: #00aa00;">Processing</strong> upon successful payment.
				', 'woocommerce'),
                'default'   => WIPAY_PLUGIN_DEFAULTS['auto_complete']
            ],
            'fee_structure' => [
                'title'         => __('Fee Structure', 'woocommerce'),
                'type'          => 'select',
                'description'   => __('This controls how the <strong>WiPay Transaction Fee</strong> for Credit/Debit Card is handled.', 'woocommerce'),
                'default'       => $this->fee_structure_default,
                'desc_tip'      => true,
                'options'       => $this->fee_structure_options,
                'css'           => 'min-width:150px;'
            ],
            'fee_box' => [
                'title'     => __(' ', 'woocommerce'),
                'type'      => 'checkbox',
                'label'     => __('
					<div id="fee_box">
						<div id="customer">
							Customer Fee<span class="sandbox"></span>: <span class="wipay_help">' . wc_help_tip('This is the fee that is <strong>added</strong> to the Total the Customer pays (at Checkout), <strong>before processing</strong>.', true) . '</span><br>
							<br>
							<strong id="customer_fee" style="font-size: 16px !important;"></strong>
						</div>
						<div id="merchant">
							Merchant Fee<span class="sandbox"></span>: <span class="wipay_help">' . wc_help_tip('This is the fee that is <strong>subtracted</strong> from the Total the Customer pays, <strong>after processing</strong>.<br><br>The resulting value is the amount you will receive in your WiPay account.', true) . '</span><br>
							<br>
							<strong id="merchant_fee" style="font-size: 16px !important;"></strong>
						</div>
					</div>
				', 'woocommerce')
            ],
            'spacer_' . $this->next_spacer_index() => [
                'title' => __(' ', 'woocommerce'),
                'type'  => 'title'
            ],
            'fee_checkout_show' => [
                'title'         => __('Fee Structure Display', 'woocommerce'),
                'type'          => 'select',
                'description'   => __('This controls how the <strong>WiPay Transaction Fee</strong> for Credit/Debit Card is displayed at the <strong>Checkout</strong> page.', 'woocommerce'),
                'default'       => WIPAY_PLUGIN_DEFAULTS['fee_checkout_show'],
                'desc_tip'      => true,
                'options'       => [
                    'always'        => __('Always (default)', 'woocommerce'),
                    'significant'   => __('Customer Fee greater than $0.00 only', 'woocommerce'),
                    'never'         => __('Never', 'woocommerce')
                ],
                'css'           => 'min-width:150px;'
            ],
            'image_size' => [
                'title'         => __('Image Size', 'woocommerce'),
                'type'          => 'select',
                'description'   => __('This controls the size of the image that is adjacent to WiPay\'s payment option at the <u>Checkout Page</u>', 'woocommerce'),
                'default'       => WIPAY_PLUGIN_DEFAULTS['image_size'],
                'desc_tip'      => true,
                'options'       => [
                    'logo_lg.png'   => __('Large', 'woocommerce'),
                    'logo_md.png'   => __('Medium', 'woocommerce'),
                    'logo_sm.png'   => __('Small (default)', 'woocommerce')
                ],
                'css'           => 'min-width:150px;'
            ],
            'image_preview' => [
                'title' => __(' ', 'woocommerce'),
                'type'  => 'checkbox',
                'label' => __('<div id="logo_preview_text">Preview:</div><img id="image_preview" src="' . $this->icon . '">', 'woocommerce')
            ],
            'spacer_' . $this->next_spacer_index() => [
                'title' => __(' ', 'woocommerce'),
                'type'  => 'title'
            ],
            'live_title' => [
                'title'         => __('<hr><span class="dashicons dashicons-businessman"></span> LIVE Credentials', 'woocommerce'),
                'type'          => 'title',
                'description'   => __('The following options configures the credentials that are used if \'<strong>SandBox Mode</strong>\' is <strong>unchecked</strong>.', 'woocommerce')
            ],
            'live_account_number' => [
                'title'         => __('WiPay Account Number', 'woocommerce'),
                'type'          => 'text',
                'description'   => __('This is your WiPay Account Number.<br><br>This only applies if \'<strong>SandBox Mode</strong>\' is <strong>unchecked</strong>.', 'woocommerce'),
                'default'       => WIPAY_PLUGIN_DEFAULTS['live_account_number'],
                'desc_tip'      => true,
                'placeholder'   => 'Please enter your WiPay Account Number',
                'custom_attributes' => [
                    'maxlength' => '10',
                    'oninput'   => "this.value = this.value.replace(/\D/gm, '');"
                ]
            ],
            'live_api_key' => [
                'title'         => __('WiPay API Key', 'woocommerce'),
                'type'          => 'text',
                'description'   => __('This is your WiPay API Key.<br><br>This only applies if \'<strong>SandBox Mode</strong>\' is <strong>unchecked</strong>.', 'woocommerce'),
                'default'       => WIPAY_PLUGIN_DEFAULTS['live_api_key'],
                'desc_tip'      => true,
                'placeholder'   => 'Please enter your WiPay API Key',
                'custom_attributes' => [
                    'maxlength' => '16'
                ]
            ],
            'spacer_' . $this->next_spacer_index() => [
                'title' => __(' ', 'woocommerce'),
                'type'  => 'title',
            ],
            'sandbox_title' => [
                'title'         => __('<hr><span class="dashicons dashicons-admin-users"></span> SandBox Credentials', 'woocommerce'),
                'type'          => 'title',
                'description'   => __('The following options configures the credentials that are used if \'<strong>SandBox Mode</strong>\' is <strong>checked</strong>.', 'woocommerce')
            ],
            'sandbox_account_number' => [
                'title'         => __('WiPay Account Number', 'woocommerce'),
                'type'          => 'text',
                'description'   => __('This is the SandBox WiPay Account Number.<br><br>This only applies if \'<strong>SandBox Mode</strong>\' is <strong>checked</strong>.', 'woocommerce'),
                'default'       => wipay_get_option('sandbox_account_number'),
                'desc_tip'      => true,
                'placeholder'   => 'Please enter your SandBox WiPay Account Number',
                'custom_attributes' => [
                    'readonly' => 'readonly'
                ]
            ],
            'sandbox_api_key' => [
                'title'         => __('WiPay API Key', 'woocommerce'),
                'type'          => 'text',
                'description'   => __('This is the SandBox WiPay API Key.<br><br>This only applies if \'<strong>SandBox Mode</strong>\' is <strong>checked</strong>.', 'woocommerce'),
                'default'       => wipay_get_option('sandbox_api_key'),
                'desc_tip'      => true,
                'placeholder'   => 'Please enter your SandBox WiPay API Key',
                'custom_attributes' => [
                    'readonly' => 'readonly'
                ]
            ],
            'spacer_' . $this->next_spacer_index() => [
                'title' => __(' ', 'woocommerce'),
                'type'  => 'title'
            ],
            'developer_options_title' => [
                'title'         => __('<hr><span class="dashicons dashicons-editor-code"></span> Developer Options', 'woocommerce'),
                'type'          => 'title',
                'description'   => __('These options help profile and debug plugin behaviours.', 'woocommerce')
            ],
            'developer_options_enable_logging' => [
                'title'     => __('Enable Logging', 'woocommerce'),
                'type'      => 'checkbox',
                'label'     => __('
					<br>
					If <strong>checked</strong>, important information on every feature in this plugin will be automatically logged.<br>
					If <strong>unchecked</strong>, no information is logged. <small>(default)</small>
				', 'woocommerce'),
                'default'   => WIPAY_PLUGIN_DEFAULTS['developer_options_enable_logging']
            ],
            'developer_options_download_log' => [
                'title' => __(' ', 'woocommerce'),
                'type'  => 'checkbox',
                'label' => __('
					<div id="download_log_box">
						<div id="download_log_details">
							Log file:<br>
							<br>
							<strong id="download_log_file" style="font-size: 16px !important;">' . $log_filename . '<br><small>(' . $log_filesize . ')</small></strong>
						</div>
						<div id="download_log_buttons">
							<div id="log_clear" class="button-primary">
								<div id="log_clear_icon"><span class="dashicons dashicons-no"></span></div>
								<div id="log_clear_text">Clear</div>
							</div>
							<div id="log_download" class="button-primary">
								<div id="log_download_icon"><span class="dashicons dashicons-download"></span></div>
								<div id="log_download_text">Download</div>
							</div>
						</div>
					</div>
					' . ($log_filesize_number >= 512
                    ?     '<br><div class="warning-text">
							<span class="dashicons dashicons-warning"></span>
							<span><strong>WARNING:</strong> Your <u>Log File</u> is <i>too</i> big! No further data will be logged until you <span class="clear-text"><strong>Clear</strong></span> the file.<span>
						</div>'
                    :     '') . '
				', 'woocommerce'),
            ],
        ];
    }

    /**
     * Once `$this->has_fields` is `true` in `__construct()`, this function will run automatically `@../checkout`.
     */
    public function payment_fields()
    {
        echo 'Make this payment using your <strong>MasterCard®</strong> or <strong>VISA®</strong> Credit/Debit Card through the <strong>WiPay™ Secure Online Gateway</strong>.';
    }

    /**
     * Redirects to the configured WiPay payment gateway.
     */
    function process_payment($order_id)
    {
        // Get the order and it's data
        $order = new WC_Order($order_id);
        $order_data = json_decode(json_encode($order->get_data()));

        // make GWPL request
        $response = wipay_make_api_request(
            'POST',
            wipay_get_option('wpfn_payments_request'),
            [
                'account_number' => wipay_get_account_number(),
                'avs'            => wipay_get_option('advanced_security') === 'yes'
                    ? (wipay_get_environment() === 'live'
                        ? 1
                        : 0)
                    : 0,
                'country_code'   => wipay_get_option('configured_country_code'),
                'currency'       => $order_data->currency,
                // NOTE: AVS fields are extracted from this data on core
                'data'           => json_encode($order_data),
                'environment'    => wipay_get_environment(),
                'fee_structure'  => $order->get_meta('fee_structure'),
                'method'         => 'credit_card',
                'order_id'       => json_encode($order_data->id),
                'origin'         => WIPAY_PLUGIN_ID,
                'response_url'   => str_replace('https:', 'http:', add_query_arg('wc-api', WIPAY_PLUGIN_CLASS, home_url('/'))),
                'total'          => number_format($order->get_meta('total_nofee'), 2, '.', ''),
                'version'        => WIPAY_PLUGIN_VERSION,
            ]
        );

        if (
            in_array($response['code'], range(200, 399)) &&
            isset($response['transaction_id']) &&
            isset($response['url'])
        ) {
            // SUCCESS: save transaction_id to the order's meta data
            $order->update_meta_data('transaction_id', $response['transaction_id']);
            // NOTE: transaction_id used to get the transaction results (serverside HTTP GET request) at the thankyou page.
            $order->save();
            // redirect to hosted page
            return [
                'result'    => 'success',
                'redirect'  => $response['url'],
            ];
        } else {
            // ERROR: default error message
            $message = 'Error processing checkout. Please try again.';
            if (isset($response['message'])) {
                // use error message from GWPL (if set)
                $message = $response['message'];
            }
            // show the error on Checkout page (frontend)
            wc_add_notice(__('Checkout error:', 'woothemes') . " $message", 'error');
            return;
        }
    }

    /**
     * Interpret response of the Credit/Debit Card checkout page after payment.
     */
    function wipay_response()
    {
        wipay_log(json_encode($_REQUEST));

        // Ensure that output buffer is clean (prevent any possible unnecessary padded data)
        @ob_clean();

        // Get the Order ID, and store the Transaction ID in the metadata.
        $wc_order = wc_get_order((int) $_REQUEST['order_id']);

        // Empty cart and clear session	
        WC()->cart->empty_cart();

        // Redirect to the thankyou page
        wp_redirect($wc_order->get_checkout_order_received_url());
    }
}
