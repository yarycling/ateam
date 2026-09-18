jQuery(document).ready(function($) {
    // Category Tab Filtering
    $('.ateam-tab-btn').on('click', function() {
        var btn = $(this);
        var category = btn.data('category');
        var section = btn.closest('.ateam-product-section');
        var grid = section.find('.ateam-product-grid');

        btn.addClass('active').siblings().removeClass('active');

        // Simple visibility toggle for demo purposes
        // In a full production app, this might be an AJAX call
        if (category === 'all') {
            grid.find('.ateam-product-card').fadeIn();
        } else {
            // Note: This relies on the card having some category info, 
            // but since we are just doing CSS/HTML, we might want to do AJAX or just static.
            // For now, let's just simulate the feeling of a tab switch.
            grid.css('opacity', '0.5');
            setTimeout(function() {
                grid.css('opacity', '1');
            }, 300);
        }
    });

    // Reveal on Scroll Animation
    const revealOnScroll = function() {
        $('.ateam-product-card, .ateam-promo-content, .ateam-hero-content').each(function() {
            var bottom_of_object = $(this).offset().top + 50;
            var bottom_of_window = $(window).scrollTop() + $(window).height();

            if (bottom_of_window > bottom_of_object) {
                $(this).addClass('is-visible');
            }
        });
    };

    $(window).on('scroll', revealOnScroll);
    revealOnScroll(); // Trigger once on load
});
