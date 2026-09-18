(function ($) {
    var timeout;

    // fade in the plugin interface to prevent css changes via js from 'popping in'.
    $('#mainform').fadeIn(2500, function () {
        // do nothing
    });

    // show a message (user feedback)
    function show_message_logging(status, message, sub_message = null) {
        // construct the full message
        var full_message = '\
            <div id="response_message" class="' + (status === 'error' ? 'error' : 'updated') + ' inline" style="margin-bottom:0px!important;">\
                <p style="margin:0!important;"><strong>' + message + '</strong>' + (sub_message != null ? '<br>' + sub_message : '') + '</p>\
            </div>\
        ';

        // show the full message, replace old message with new one if a new message is requested to be shown
        if ($('div#response_message').length !== 0) {
            $('div#response_message').detach();
        }
        $('div#download_log_box').closest('fieldset').after(full_message);

        // effectively extend the timeout if a new message is shown
        if (timeout != null) {
            clearTimeout(timeout);
        }
        timeout = setTimeout(function () {
            $('div#response_message').fadeOut('slow', function () {
                $(this).detach();
            });
        }, 5000);
    }

    function clear_validation_message(id) {
        $(id).removeClass('required-fail');
        $(id + '-fail').remove();
        $(id).removeClass('required-success');
        $(id + '-success').remove();
    }

    function add_validation_error(id) {
        clear_validation_message(id);
        var el = $(id);
        if (el.is('input:text')) {
            el.addClass('required-fail');
            el.closest('fieldset').after('\
                <div id="' + el.attr('id') + '-fail" class="error fail message">\
                    <span class="dashicons dashicons-no inline-space"></span>\
                    <span>Invalid: ' + el.attr('placeholder') + '</span>\
                </div>\
            ');
        }
    }

    function add_validation_success(id) {
        clear_validation_message(id);
        var el = $(id);
        if (el.is('input:text')) {
            el.addClass('required-success');
            el.closest('fieldset').after('\
                <div id="' + el.attr('id') + '-success" class="updated success message">\
                    <span class="dashicons dashicons-yes inline-space"></span>\
                    <span>Looks good!</span>\
                </div>\
            ');
        }
    }

    function validate_live_credentials() {
        var lan = $('input#woocommerce_wipay_credit_live_account_number');
        var lak = $('input#woocommerce_wipay_credit_live_api_key');

        if (lan.val().length !== 10) {
            add_validation_error('#' + lan.attr('id'));
        } else {
            add_validation_success('#' + lan.attr('id'));
        }
        if (lak.val().length < 10) {
            add_validation_error('#' + lak.attr('id'));
        } else {
            add_validation_success('#' + lak.attr('id'));
        }
    }

    // stops the label of the option from being clickable (to change the option) - only the checkbox
    function enable_only_checkbox(id) {
        $(id).closest('label').attr('style',
            'pointer-events: none;'
        );
        $(id).attr('style',
            'pointer-events: all;'
        );
    }

    // for highly custom options (eg. image size picker) that uses woocommerce's checkbox markup, remove the actual checkbox
    function remove_checkbox(id) {
        $(id).css({
            'display': 'none',
            'pointer-events': 'none',
        });
        $(id).detach();
    }

    // helper function to update the fee structure values based on the user's changed input in the options
    function update_fee_structure() {
        var account_number = options.live_account_number;
        var environment = 'live';
        if (options.sandbox_enabled === 'yes') {
            account_number = options.sandbox_account_number;
            environment = 'sandbox';
        }
        if (/wp-admin/i.test(location.href)) {
            if ($('input#woocommerce_wipay_credit_sandbox_enabled').is(':checked')) {
                account_number = $('input#woocommerce_wipay_credit_sandbox_account_number').val();
                environment = 'sandbox';
                $.each($('.sandbox'), function (index, value) {
                    $(value).html(' (SandBox)');
                });
            } else {
                account_number = $('input#woocommerce_wipay_credit_live_account_number').val();
                environment = 'live';
                $.each($('.sandbox'), function (index, value) {
                    $(value).html('');
                });
            }
        }

        var country_code = options.country_code;
        if (/wp-admin/i.test(location.href)) {
            country_code = $('select#woocommerce_wipay_credit_country_code').val();
        }

        var fee_structure = options.fee_structure;
        if (/wp-admin/i.test(location.href)) {
            fee_structure = $('select#woocommerce_wipay_credit_fee_structure').val();
        }

        var currency = woocommerce.currency;
        if (/wp-admin/i.test(location.href)) {
            currency = $.trim($('strong#wipay_currency').text());
        }

        $.ajax({
            method: 'GET',
            url: options.wpfn_get_fees,
            headers: {
                'Accept': 'application/json',
            },
            data: {
                amount: money_format(0, currency).amount,
                account_number: account_number,
                country_code: country_code,
                environment: environment,
                fee_structure: fee_structure,
                currency: currency,
            },
            beforeSend: function (jqXHR, settings) {
                $('div#fee_box').closest('td.forminp').block({
                    message: null
                });
            },
            complete: function (jqXHR, textStatus) {
                if (!$('div.blockUI.blockMsg.blockElement').is(':visible')) {
                    $('div#fee_box').closest('td.forminp').unblock();
                }
            },
        }).done(function (data, textStatus, jqXHR) {
            $('#customer_fee').html(data.credit_card.cus_desc);
            $('#merchant_fee').html(data.credit_card.mer_desc);
        }).fail(function (jqXHR, textStatus, errorThrown) {
            $('#customer_fee').html('-');
            $('#merchant_fee').html('-');
            var message = ': ';
            if (jqXHR.responseJSON) {
                if (jqXHR.responseJSON.message) {
                    message += jqXHR.responseJSON.message
                }
            }
            $('div#fee_box').closest('td.forminp').block({
                message: jqXHR.status + ' ' + errorThrown + (message === ': ' ? '' : message),
                css: {
                    cursor: 'auto',
                    'z-index': '1000',
                },
                overlayCSS: {
                    cursor: 'auto',
                },
            });
        });
    }

    // helper function to update the supported currencies based on the user-selected platform in the options
    function update_supported_currencies() {
        var country_code = options.country_code;
        if (/wp-admin/i.test(location.href)) {
            country_code = $('select#woocommerce_wipay_credit_country_code').val();
        }

        $.ajax({
            method: 'GET',
            url: options.wpfn_get_supported_currencies,
            headers: {
                'Accept': 'application/json',
            },
            data: {
                country_code: country_code,
            },
            beforeSend: function (jqXHR, settings) {
                $('span#supported_currencies').html('-');
                $('span#supported_currencies').closest('fieldset').block({
                    message: null
                });
            },
            complete: function (jqXHR, textStatus) {
                $('span#supported_currencies').closest('fieldset').unblock();
            },
        }).done(function (data, textStatus, jqXHR) {
            var currencies = [];
            $.each(data, function (index, value) {
                currencies.push('<strong>' + value + '</strong>');
            });
            $('span#supported_currencies').html(currencies.join(', '));
        }).fail(function (jqXHR, textStatus, errorThrown) {
            var message = ': ';
            if (jqXHR.responseJSON) {
                if (jqXHR.responseJSON.message) {
                    message += jqXHR.responseJSON.message
                }
            }
            $('span#supported_currencies').html('<strong>' + jqXHR.status + ' ' + errorThrown + (message === ': ' ? '' : message) + '</strong>');
        });
    }

    $(document).ready(function () {

        // Plugin title
        if ($('h2 > small.wc-admin-breadcrumb > a').length > 0) {
            $('h2 > small.wc-admin-breadcrumb > a').closest('h2').attr('style',
                'display: flex !important;' +
                'justify-content: space-between !important;' +
                'align-items: center !important;'
            );
            var original_title = $('h2 > small.wc-admin-breadcrumb > a').closest('h2').html();
            $('h2 > small.wc-admin-breadcrumb > a').closest('h2').html('\
                <div>' + original_title + '</div>\
                <div id="wipay_update" class="button-primary" title="Update plugin">\
                    <div id="wipay_update_icon"><span class="dashicons dashicons-backup"></span></span></div>\
                    <div id="wipay_update_text">Update</div>\
                </div>\
            ');
            style_button({
                id: 'wipay_update',
                color: '#1c55a0',
                width: '110px',
            });
        }
        $('#wipay_update').closest('h2').after('\
            <div id="wipay_logo">\
                <img src="' + wipay.logo + '" alt="" style="pointer-events: none !important;">\
            </div>\
        ');
        $('div#wipay_logo').attr('style', '\
            display: flex !important;\
            justify-content: center !important;\
            align-items: center !important;\
        ');

        var subtitle = $($('div#wipay_logo').siblings('p')[0]);
        subtitle.attr('style', '\
            text-align: center !important;\
        ');
        subtitle.html(subtitle.html() + '<br><br>' + '<strong>v' + version + '-' + options.configured_country_code + '</strong>');

        // Enable Plugin
        enable_only_checkbox('input#woocommerce_wipay_credit_enabled');

        // Platform
        $('select#woocommerce_wipay_credit_country_code').on('change', function () {
            update_supported_currencies();
            update_fee_structure();
        });
        $('select#woocommerce_wipay_credit_country_code').after('\
            <div style="margin-top: 5px;">\
                <small>Supported currencies: <span id="supported_currencies"></span></small>\
            </div>\
        ');

        // SandBox Mode
        enable_only_checkbox('input#woocommerce_wipay_credit_sandbox_enabled');
        $('input#woocommerce_wipay_credit_sandbox_enabled').on('change', function () {
            update_fee_structure();
            validate_live_credentials();
        });

        // Auto Complete
        enable_only_checkbox('input#woocommerce_wipay_credit_auto_complete');

        // Currency description
        $('input#woocommerce_wipay_credit_currency_info').closest('td.forminp').css({
            'padding-left': '0px',
            'padding-right': '0px',
            'padding-bottom': '0px',
        });
        $('input#woocommerce_wipay_credit_currency_info').closest('label').attr('style',
            'margin: 0px !important;' +
            'cursor: text !important;'
        );
        remove_checkbox('input#woocommerce_wipay_credit_currency_info');

        // Currency box
        $('input#woocommerce_wipay_credit_currency_box').closest('label').attr('style',
            'margin: 0px !important;' +
            'width: 100% !important;' +
            'pointer-events: none !important;'
        );
        remove_checkbox('input#woocommerce_wipay_credit_currency_box');
        $('div#currency_box').closest('td.forminp').css({
            'padding': '10px 10px',
            'background-color': '#dfdcde',
            'border-radius': '3px',
        });
        $('div#currency_box').css({
            'display': 'flex',
            'align-items': 'center',
        });
        $('div#currency_current').css({
            'flex-grow': '1',
        });
        $('div#currency_settings').css({
            'pointer-events': 'all',
        });
        style_button({
            id: 'wc_settings',
            color: '#007cba',
            width: '110px',
        });
        $('div#currency_settings').on('click', function () {
            // set this as true so as to show the animated effect in WooCommerce > Settings > General page
            localStorage.setItem('wipay-currency-settings', 'true');
        });

        // Fee Structure
        $('select#woocommerce_wipay_credit_fee_structure').closest('td.forminp').css({
            'padding-bottom': '0px',
        });
        $('select#woocommerce_wipay_credit_fee_structure').on('change', function () {
            update_fee_structure();
        });
        $('input#woocommerce_wipay_credit_fee_box').closest('label').attr('style',
            'margin: 0px !important;' +
            'width: 100% !important;' +
            'pointer-events: none !important;'
        );
        remove_checkbox('input#woocommerce_wipay_credit_fee_box');
        $('div#fee_box').closest('td.forminp').css({
            'padding': '10px 10px',
            'background-color': '#dfdcde',
            'border-radius': '3px',
        });
        $('div#fee_box').css({
            'display': 'flex',
            'align-items': 'center',
        });
        $('div#customer').css({
            'width': '50%',
            'border-right': '1px solid #999',
            'margin-right': '10px',
        });
        $('div#merchant').css({
            'width': '50%',
        });
        $.each($('.wipay_help'), function (index, value) {
            $(value).attr('style',
                'pointer-events: all !important;'
            );
        });

        // Logo preview
        $('select#woocommerce_wipay_credit_image_size').closest('td.forminp').css({
            'padding-bottom': '0px',
        });
        $('select#woocommerce_wipay_credit_image_size').on('click change', function () {
            // dynamically change the image to the one that is selected by the user
            var src = $('img#image_preview').attr('src');
            var src_split = src.split('/');
            src_split[src_split.length - 1] = $(this).val();
            $('img#image_preview').attr('src', src_split.join('/'));
        });
        var logo_preview_text = $('div#logo_preview_text').detach();
        remove_checkbox('input#woocommerce_wipay_credit_image_preview');
        $('img#image_preview').closest('fieldset').before(logo_preview_text);
        $('div#logo_preview_text').css({
            'width': '100%',
        });
        $('img#image_preview').closest('td.forminp').css({
            'align-items': 'center',
            'justify-content': 'center',
            'display': 'flex',
            'padding-top': '10px',
            'padding-bottom': '5px',
            'background-color': '#dfdcde',
            'pointer-events': 'none',
            'flex-direction': 'column',
            'border-radius': '3px',
        });

        // LIVE credentials
        var keydown_val = '';
        $(document).on('keydown keyup', 'input#woocommerce_wipay_credit_live_account_number', function (event) {
            validate_live_credentials();
            if (event.type === 'keydown') {
                keydown_val = $(this).val();
            }
            if (event.type === 'keyup') {
                if (!$('input#woocommerce_wipay_credit_sandbox_enabled').is(':checked')) {
                    if ($(this).val() !== keydown_val) {
                        if ($(this).val().length === 10) {
                            update_fee_structure();
                        }
                    }
                }
            }
        });
        $(document).on('keydown keyup', 'input#woocommerce_wipay_credit_live_api_key', function (event) {
            validate_live_credentials();
        });

        // Developer options
        enable_only_checkbox('input#woocommerce_wipay_credit_developer_options_enable_logging');
        $('input#woocommerce_wipay_credit_developer_options_enable_logging').closest('td.forminp').css({
            'padding-bottom': '0px',
        });
        $('input#woocommerce_wipay_credit_developer_options_download_log').closest('label').attr('style', '\
            margin: 0px !important;\
            width: 100% !important;\
            pointer-events: none !important;\
        ');
        remove_checkbox('input#woocommerce_wipay_credit_developer_options_download_log');
        style_button({
            id: 'log_clear',
            color: '#e3342f',
            margin: '0px 0px 5px 0px',
            width: '110px',
        });
        $('div#log_clear').css({
            'margin-bottom': '5px',
        });
        style_button({
            id: 'log_download',
            color: '#38c172',
            width: '110px',
        });
        $('div#download_log_box').closest('td.forminp').css({
            'padding': '10px 10px',
            'background-color': '#dfdcde',
            'border-radius': '3px',
        });
        $('div#download_log_box').css({
            'display': 'flex',
            'align-items': 'center',
        });
        $('div#download_log_details').css({
            'width': '50%',
        });
        $('div#download_log_buttons').css({
            'width': '50%',
            'display': 'flex',
            'flex-direction': 'column',
            'align-items': 'flex-end',
        });

        update_fee_structure();
        update_supported_currencies();
        validate_live_credentials();

        if (getCookie(cookie.update) === "true") {
            eraseCookie(cookie.update);
            if (response.status_update === "success") {
                $('div#wipay_update').closest('h2').before('\
                    <div id="message" class="updated inline">\
                        <p><strong>Plugin update successful.</strong></p>\
                    </div>\
                ');
            } else {
                $('div#wipay_update').closest('h2').before('\
                    <div id="message" class="error inline">\
                        <p><strong>Plugin update failed. Please try again later.</strong></p>\
                    </div>\
                ');
            }
        }

        if (getCookie(cookie.clear) === "true") {
            eraseCookie(cookie.clear);
            if (response.status_clear === "success") {
                show_message_logging('success', 'File was successfully cleared.');
            } else {
                show_message_logging('error', 'Sorry, the File was unable to cleared.', 'Please try again.');
            }
        }

        $('button[name=save]').css({ 'display': 'none' }).after('\
            <div id="save" class="button-primary">\
                <div id="save_icon"><span class="dashicons dashicons-yes-alt"></span></div>\
                <div id="save_text"><span style="font-size:1.5em;">Save & Reload</span></div>\
            </div>\
        ');
        style_button({
            'border-radius': '2em',
            'height': '40px',
            'id': 'save',
        });
        $('p.submit').css({
            'padding': '1.5em 0 0 0',
        });

        setTimeout(function () {
            $('input#woocommerce_wipay_credit_live_account_number').trigger($.Event("keydown", { keyCode: 37 }));
        }, 250);

    });

    $(document).on('click', 'div#wipay_update', function (event) {
        $('#mainform').block({
            message: "<br>Please wait!<br><br>The Plugin settings are being updated...<br><br>",
            css: {
                padding: '5px',
                'z-index': '1000',
            },
        });
        setCookie(cookie.update, true, 1);
        window.location.reload(true);
    });

    $(document).on('click', 'div#log_clear', function (event) {
        $('div#download_log_box').closest('td.forminp').block({
            message: null,
        });
        setCookie(cookie.clear, true, 1);
        window.location.reload(true);
    });

    $(document).on('click', 'div#log_download', function (event) {
        $.ajax({
            method: "GET",
            url: wipay.logfile_url,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            data: {},
            beforeSend: function (jqXHR, settings) {
                $('div#download_log_box').closest('td.forminp').block({
                    message: null,
                });
            },
        }).always(function (jqXHRdata, textStatus, jqXHRerror) {
            $('div#download_log_box').closest('td.forminp').unblock();
            if (jqXHRdata == null && jqXHRerror.status == 200) {
                show_message_logging('error', 'Log file is empty!');
            } else {
                if (jqXHRdata.status === 200) {
                    downloader(jqXHRdata.responseText, 'application/json', wipay.logfile_name);
                    show_message_logging('success', 'File was successfully downloaded.');
                } else {
                    $error_message = '[' + jqXHRdata.status + ']: ' + jqXHRdata.statusText;
                    show_message_logging('error', 'Sorry, an error occurred when trying to download file:', $error_message);
                }
            }
        });
    });

    $(document).on('click', 'div#save', function (event) {
        $('button[name=save]').click();
    });

})(jQuery);