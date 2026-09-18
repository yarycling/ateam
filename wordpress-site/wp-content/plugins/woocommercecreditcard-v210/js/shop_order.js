(function ($) {

    // look for an ajaxComplete event only if the order-preview link is pressed
    var op_click = false;
    $('a.order-preview').on('click', function (e) {
        op_click = true;
    });

    var order_id = '';
    $(document).ajaxComplete(function (event, xhr, settings) {
        // once the ajaxComplete is a proper response, configure the pop=up modal to add the Manual Verify button
        if (op_click && xhr.responseJSON != null && settings.type === 'GET') {

            // reset to false to no bother looking for any other ajaxComplete
            op_click = false;

            // apply the logic only if the order was made through the wipay plugin
            var isWIPAY = false;
            $.each(xhr.responseJSON.data.data, function (index, value) {
                if (index === 'payment_method') {
                    if (value === payment.method) {
                        isWIPAY = true;
                    }
                }
            });
            if (isWIPAY) {
                // add the Manual Verify button and apply proper css etc to make the frontend look legit
                order_id = xhr.responseJSON.data.order_number;
                var div_inner = $('footer > div.inner > a.button.button-primary.button-large').closest('div.inner');
                div_inner.attr('style', '\
                    display: flex;\
                    flex-direction: row;\
                    justify-content: flex-end;\
                    align-items: center;\
                ')
                var original_inner = div_inner.html();
                div_inner.html('\
                    <div id="wipay_manual_verify" class="button button-primary button-large" title="Manually Verify this Order">\
                        <div id="wipay_manual_verify_icon"><span class="dashicons dashicons-update-alt"></span></div>\
                        <div id="wipay_manual_verify_text">Manual Verify</div>\
                    </div>\
                    <div>' + original_inner + '</div>\
                ');
                style_button({
                    id: 'wipay_manual_verify',
                    width: '140px',
                });
            }
        }
    });

    // display the converted Totals correctly
    $.each($('small.conv'), function (index, value) {
        var tips = $(value).siblings('span.tips');
        var conv = $(value).detach();
        tips.after(conv.get(0));
    });

    $(document).ready(function () {

        // this is loaded after serverside sees it as "true", so we can always safely invalidate the cookie
        if (getCookie(cookie.name) != null) {

            // select the table row in which the verification request was processed on serverside
            var verified_tr = $('tr#post-' + response.order_id);

            // scroll to the selected table row in the woocommerce settings
            $('html, body').animate({
                scrollTop: verified_tr.offset().top - verified_tr.height()
            }, 1000, function () {

                // after the scroll animation, perform animation routine to make the table row pulse
                // it will pulse green or red depending on the status of the verify from serverside
                var class_name = 'verify-failed';
                if (response.status === 'success') {
                    class_name = 'verify-success';
                }

                // add class to being animation
                verified_tr.addClass(class_name);

                // when the animation ends, remove the class to animate it back to normal
                verified_tr.one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend animationend", function () {
                    setTimeout(function () {
                        if (verified_tr.hasClass(class_name)) {
                            verified_tr.removeClass(class_name);
                        }
                    }, 1000);
                })
            });

            // invalidate the cookie
            eraseCookie(cookie.name);
        }

    });

    // Once the manual verify button is pressed, set the cookie and reload the page
    // this causes the serverside to see it and process the request
    $(document).on('click', 'div#wipay_manual_verify', function (event) {

        $(this).closest('div.wc-backbone-modal-content').block({
            message: "<br>Please wait!<br><br>The Transaction result is being verified...<br><br>",
            css: {
                padding: '5px',
            },
            overlayCSS: {
                margin: '5px',
                width: 'calc(100% - 10px)',
                height: 'calc(100% - 10px)',
            },
        });

        setCookie(cookie.name, order_id, 1);
        window.location.reload(true);
    });

})(jQuery);