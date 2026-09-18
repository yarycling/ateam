jQuery(document).ready(function () {

    "use strict";

    itemHeight();

    jQuery("[data-fancybox]").fancybox({
        loop: true,
        titlePosition: 'inside',
        transitionIn: 'elastic',
        transitionOut: 'elastic',
        easingIn: 'easeOutBack',
        easingOut: 'easeInBack',
        thumbs: {
            autoStart: true
        },
        buttons: [
            'zoom',
            'close'
        ]
    });

    jQuery(".layout-cover.slider_layout").each(function (index) {
        var post_id = jQuery(this).data('post_slider_id');
        
        jQuery('.layout-cover.slider_layout.' + post_id).owlCarousel({
            items: jQuery('.layout-cover.slider_layout.' + post_id).data('clm_desktop'),
            itemsDesktop: [980, jQuery('.layout-cover.slider_layout.' + post_id).data('clm_small_desktop')],
            itemsTablet: [800, jQuery('.layout-cover.slider_layout.' + post_id).data('clm_tablet')],
            itemsMobile: [600, jQuery('.layout-cover.slider_layout.' + post_id).data('clm_mobile')],
            pagination: true,
            autoPlay: true,
            navigation: true,
            navigationText: [
                "<span></span>",
                "<span></span>"
            ],
        });
    });

    // Filter toggle active class
    jQuery('.filter-nav .nav-item ').on( 'click' , function (e) {
        e.preventDefault();

        // uniqID
        let id = '#' + jQuery(this).data('shortcode');

        // reset active class
        jQuery(id + ' .nav-item ').removeClass("active");

        // add active class to selected
        jQuery(this).addClass("active");

        // return needed to make function work
        return false;
    });

    // Filter For Grid and Masonry
    jQuery(".filter-nav .nav-item").on('click', function (e) {
        e.preventDefault();

        // uniqID
        let id = '#' + jQuery(this).data('shortcode');

        // layout type
        let LayoutType = jQuery(this).data('layout-type');

        // assigns class to selected item
        var selectedClass = jQuery(this).attr("data-filter");

        // get responsive column
        var clm_desktop = parseInt(jQuery(this).parent().next().attr("data-clm_desktop"));
        var clm_small_desktop = parseInt(jQuery(this).parent().next().attr("data-clm_small_desktop"));
        var clm_tablet = parseInt(jQuery(this).parent().next().attr("data-clm_tablet"));
        var clm_mobile = parseInt(jQuery(this).parent().next().attr("data-clm_mobile"));

        if(LayoutType != "slider_layout") {
            if (selectedClass == "all") {
                // fades in all portfolio items
                jQuery(id + " .gspwp-content").show(300);
            } else {
                // fades out all portfolio items
                jQuery(id + " .gspwp-content").hide(300);
    
                // fades in selected category
                jQuery(id + " .gspwp-content." + selectedClass).show(300);
            }
        }

        if (LayoutType == "masonry_layout") {

            var DeviceColumnNumber = clm_desktop + 1

            /**
             *  > 721px to <= 980px
             */
            if (window.matchMedia('(min-width:721px) and (max-width: 980px)').matches) {
                DeviceColumnNumber = clm_small_desktop
            }

            /**
             *  > 601px to <= 720px
             */
            if (window.matchMedia('(min-width: 768px) and (max-width: 1366px)').matches) {
                DeviceColumnNumber = clm_tablet
            }

            /**
             * <= 480px
             */
            if (window.matchMedia('screen and (max-width: 600px)').matches) {
                DeviceColumnNumber = clm_mobile
            }

            setTimeout(() => {
                jQuery('.masonry_layout').masonry({
                    itemSelector: '.gspwp-content',
                    isResizable: true,
                    columnWidth: DeviceColumnNumber,
                    isAnimated: true
                });
            }, 600);

        }
    });

    // Slider With filter filters 
    jQuery('.filter-nav .nav-item').on('click', function (e) {
        e.preventDefault();

        // uniqID
        let id = '#' + jQuery(this).data('shortcode');
        // filter cat
        let cat = jQuery(this).data('filter');
        // layout type
        let LayoutType = jQuery(this).data('layout-type');

        if (LayoutType == "slider_layout") {
            var elem;

            if (cat == 'all') {
                jQuery(id + ' .hold-filter-item .gspwp-content').each(function () {
                    var owl = jQuery(id + " .owl-carousel").data('owlCarousel');
                    elem = jQuery(this).parent().html();

                    owl.addItem(elem);
                    jQuery(this).parent().remove();
                });
            } else {
                jQuery(id + ' .hold-filter-item .gspwp-content.' + cat).each(function () {
                    var owl = jQuery(id + " .owl-carousel").data('owlCarousel');
                    elem = jQuery(this).parent().html();

                    owl.addItem(elem);
                    jQuery(this).parent().remove();
                });

                jQuery(id + ' .slider_layout .gspwp-content:not(.gspwp-content.' + cat + ')').each(function () {
                    var owl = jQuery(id + " .owl-carousel").data('owlCarousel');
                    var targetPos = jQuery(this).parent().index();
                    elem = jQuery(this).parent();

                    jQuery(elem).clone().appendTo(jQuery(id + ' .hold-filter-item'));
                    owl.removeItem(targetPos);
                });
            }
        }
    });

});

jQuery(window).resize(function () {
    itemHeight();
    gsAnimateElement();
    gsMasonry();
});

jQuery(window).load(function () {
    gsMasonry();
    gsAnimateElement();
});

jQuery(window).scroll(function () {
    gsAnimateElement();
});

function gsMasonry() {
    jQuery('.masonry_layout').imagesLoaded(function () {
        jQuery('.masonry_layout').masonry({
            itemSelector: '.gspwp-content',
            isResizable: true,
            columnWidth: 0,
            isAnimated: true
        });
    });
}

var windowHeight = jQuery(window).height();
function gsAnimateElement() {
    var windowTop;
    windowTop = jQuery(window).scrollTop(); // calculate distance from top of window
    // loop through each item to check when it animates
    jQuery('.gspwp_animate').each(function () {
        var gsElement = jQuery(this);
        if (gsElement.hasClass('animated')) {
            return true;
        } // if already animated skip to the next item
        var gsTopOffse = gsElement.offset().top; // element's distance from top of page in pixels
        if (windowTop > (gsTopOffse - (windowHeight * .99))) {
            // animate when top of the window is 3/4 above the element
            gsElement.addClass('animated');
            effect = gsElement.attr('data-effect');
            gsElement.addClass(effect);
        }
    });
}

function itemHeight() {
    jQuery('.inspirational.layla figure').each(function () {
        var $itemHeight = jQuery(this).height();
        $itemHeight = $itemHeight - 30;
        jQuery(this).css('height', $itemHeight + 'px');
    });
}