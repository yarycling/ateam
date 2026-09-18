(function ($) {
    $(document).ready(function () {

        // wipay-currency-settings is set true only when 'Change' button is pressed in wipay plugin settings
        if (localStorage.getItem('wipay-currency-settings') === 'true') {

            // set it to false, so the rest of logic will not run again (eg. on page reload)
            localStorage.setItem('wipay-currency-settings', 'false');

            // make sure we are on the correct page (WooCommerce > Settings > General)
            if ($('div#pricing_options-description').length > 0) {

                // add animation class to animate the heading with font-size changes
                $('html > head').append($(
                    '<style>' +
                        '.heading-animate {' +
                            'font-size: 1.6em !important;' +
                        '}' +
                    '</style>'
                ));

                // define as a variable so we can just reuse later
                var currency_options_heading = $('div#pricing_options-description').siblings('h2').last();

                // set the initial css which defines the animation
                currency_options_heading.css({
                    "font-size": "1.3em",
                    "transition": "all 0.25s ease",
                });

                // scroll to the Currency options section in the woocommerce settings
                $('html, body').animate({
                    scrollTop: $("div#pricing_options-description").offset().top
                }, 1000, function () {
                    
                    // after the scroll animation, perform animation routine to make the heading "pulse"
                    currency_options_heading.addClass('heading-animate');

                    // when the animation ends, remove the class to animate it back to normal
                    currency_options_heading.one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend animationend", function () {
                        if (currency_options_heading.hasClass('heading-animate')) {
                            currency_options_heading.removeClass('heading-animate');
                        }
                    })
                });
            }
        }

    });
})(jQuery);