(function ($) {

    // forces the page to reload once, upon loading - workaround to correctly show the transaction result
    if (window.localStorage) {
        if (!localStorage.getItem('load_once')) {
            $('div#content').html('<div></div>');
            $('body').attr('style', 'position:absolute!important; top:0!important; right:0!important; bottom:0!important; left:0!important;')
            $('body').block({
                message: "<br>Please wait!<br><br>The Transaction result is being verified...<br><br>"
            });
            localStorage['load_once'] = true;
            window.location.reload(true);
        } else {
            localStorage.removeItem('load_once');
        }
    }

    $(document).ready(function () {

        // makes sure that the custom notice is added only once
        var notice = $('div.woocommerce > div.woocommerce-order > p.woocommerce-notice');
        if (notice.length > 1) {
            $.each(notice, function (index, value) {
                if (index === 0) {
                    notice = $(value);
                }
            });
        }
        notice.after(response.message);

        // show authentication error
        if (response.payment_status === 'successful') {
            if (response.authenticated === 'no') {
                $('#auth_alert').css({ 'display': 'block' });
                $('#auth_details').css({ 'display': 'inline' });
            }
        }

    });

})(jQuery);