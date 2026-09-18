(function ($) {

    $(document).ready(function () {

        var original_title = $('div#order_data > h2').html();
        $('div#order_data > h2').attr('style',
            'display: flex !important;' +
            'justify-content: space-between !important;' +
            'align-items: center !important;' +
            'margin-bottom: 5px !important;'
        );
        $('div#order_data > h2').html('\
            <div>' + original_title + '</div>\
            <div id="wipay_manual_verify" class="button-primary" title="Manually Verify this Order">\
                <div id="wipay_manual_verify_icon"><span class="dashicons dashicons-update-alt"></span></div>\
                <div id="wipay_manual_verify_text">Manual Verify</div>\
            </div>\
        ');
        style_button({
            id: 'wipay_manual_verify',
            width: '140px',
        });


        $.each($('div.wc-order-totals-items > table.wc-order-totals > tbody > tr'), function (index, value) {
            var label = $(this).find('td.label');
            if (/Order Total/i.test($.trim(label.text()))) {
                label.html('Order Total (' + order.currency + '):');
            }
        });


        // this is loaded after serverside sees it as "true", so we can always safely invalidate the cookie
        if (getCookie(cookie.name) === "true") {
            eraseCookie(cookie.name);
            if (response.status === "success") {
                $($('div.wrap > h1.wp-heading-inline').siblings('hr.wp-header-end')[0]).after('\
                    <div id="message" class="updated notice notice-success">\
                        <p>WiPay Manual Verification on this Order was successful.</p>\
                    </div>\
                ');
            } else {
                $($('div.wrap > h1.wp-heading-inline').siblings('hr.wp-header-end')[0]).after('\
                    <div id="message" class="error notice notice-error">\
                        <p>Sorry, WiPay Manual Verification on this Order was unsuccessful. Please try again.</p>\
                    </div>\
                ');
            }
        }

    });


    $(document).on('click', 'div#wipay_manual_verify', function (event) {
        $(this).closest('div.inside').block({
            message: "<br>Please wait!<br><br>The Transaction result is being verified...<br><br>",
            css: {
                padding: '5px',
            },
        });
        setCookie(cookie.name, true, 1);
        window.location.reload(true);
    });

})(jQuery);