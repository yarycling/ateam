(function ($) {
    var has_error = false;
    var is_wipay = options.is_wipay === '1';
    var is_order_pay = false;
    if (/order-pay/i.test(window.location.href)) {
        is_order_pay = true;
    }
    

    // style the fee inside the order review table
    function style_fee() {
        var fee_th = $('tr.fee > th');
        $.each($('tr > th'), function (index, value) {
            var tr_text = $.trim($(value).text());
            if (/WiPay/i.test(tr_text)) {
                fee_th = $(value);
                is_wipay = true;
            }
            if (/ERROR/i.test(tr_text)) {
                has_error = true;
            }
        });
        if (fee_th.length > 0) {
            var color = '';
            if (has_error) {
                color = 'color:red!important;'
                fee_th.closest('tr').css({
                    'box-shadow': 'inset 0 0 0 1000px rgba(255, 0, 0, 0.2)',
                });
                $('button#place_order').css({
                    'pointer-events': 'none',
                    'opacity': '0.4',
                });
            }
            var fee_th_html = fee_th.html();
            if (fee_th_html.substr(-1) === ':') {
                fee_th_html = fee_th_html.substr(0, fee_th_html.length - 1);
            }
            if (!/<span/i.test(fee_th_html)) {
                var fee_th_html_split = fee_th_html.split(':');
                if (fee_th.innerWidth() > 600) {
                    // if the table is wide enough, no need for a breakline
                    fee_th.html(fee_th_html_split[0] + ':<span style="font-weight:normal!important;' + color + '">' + fee_th_html_split[fee_th_html_split.length - 1] + '</span>');
                } else {
                    // if the table is not wide enough, breakline
                    fee_th.html(fee_th_html_split[0] + ':<br><span style="font-weight:normal!important;' + color + '">' + fee_th_html_split[fee_th_html_split.length - 1] + '</span>');
                }
            }
        }
    }

    // change where to put the logo image
    function style_payment_method() {
        if (!/logo_sm/i.test(options.image_size)) {
            // if the logo is not sm size, place the logo in the line below (more aesthetically pleasing)
            $("li.wc_payment_method.payment_method_wipay_credit > label").css({
                "display": "flex",
                "flex-direction": "column",
                "align-items": "flex-start",
            });
            $("li.wc_payment_method.payment_method_wipay_credit > label > img").css({
                "margin": "0px",
            });
        }
        // else logo is sm size, place the logo in the default position (single-line, inline with text)
    }

    function select_payment_method() {
        if (is_order_pay && is_wipay) {
            var min_jq_v = '1.9'.split('.').join('');
            var cur_jq_v = $.trim($().jquery).replace(/[^0-9.]/gi, '').split('.').slice(0, -1).join('');
            if (cur_jq_v > min_jq_v) {
                $('input#payment_method_wipay_credit').prop("checked", true);
            } else {
                $('input#payment_method_wipay_credit').attr('checked', 'checked');
            }
            $('input#payment_method_wipay_credit').trigger("click");
        }
    }

    // Apply visual styles to payment method image and fee text
    style_fee();
    style_payment_method();
    select_payment_method();
    $(document).ajaxComplete(function (event, xhr, settings) {
        style_fee();
        style_payment_method();
        select_payment_method();
    });

    /* FIX: fees loaded via cURL remains in-tact */
    var fee_row;
    $('body').one('update_checkout', function (e) {
        if ($('tr.fee').length > 0) {
            fee_row = $('tr.fee')[0];
        }
    });
    $('body').one('updated_checkout', function (e) {
        if (fee_row != null) {
            $('tr.fee').replaceWith(fee_row);
        }
    });

    // Reload the Checkout page when the payment method is changed (at Checkout)
    $('form.checkout').on('change', 'input[name^="payment_method"]', function () {
        $('body').trigger('update_checkout');
    });

})(jQuery);