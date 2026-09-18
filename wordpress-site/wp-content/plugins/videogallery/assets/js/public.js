jQuery(document).ready(function($) {
    const $lightbox = $('#atv-lightbox');
    const $catModal = $('#atv-category-modal');
    const $mainPlayer = $('#atv-main-player');

    // Open Lightbox
    $(document).on('click', '.atv-video-card', function() {
        const $card = $(this);
        const videoUrl = $card.data('video-url');
        const title = $card.data('title');
        const desc = $card.data('desc');
        const catId = $card.data('cat-id');
        const postId = $card.data('id');

        // Track View
        $.post(atvFrontend.ajaxUrl, {
            action: 'atv_track_view',
            post_id: postId
        });

        openLightbox(videoUrl, title, desc, catId);
    });

    // Category Tab Filtering
    $(document).on('click', '.atv-tab', function() {
        const $tab = $(this);
        const filter = $tab.data('filter');

        // Toggle active class
        $('.atv-tab').removeClass('active');
        $tab.addClass('active');

        // Filter Main Grid ONLY (Exclude Sidebar)
        $('#atv-main-grid .atv-video-card').each(function() {
            const $card = $(this);
            const categories = String($card.data('categories')).split(',');

            if (filter === 'all' || categories.includes(String(filter))) {
                $card.fadeIn(300).removeClass('hidden');
            } else {
                $card.hide().addClass('hidden');
            }
        });
    });

    function openLightbox(videoUrl, title, desc, catId) {
        $mainPlayer.attr('src', videoUrl);
        $('#atv-player-title').text(title);
        $('#atv-player-desc').text(desc);
        
        // Populate Related Slider from current visible category if possible, or just all visible
        const $slider = $('#atv-related-slider').empty();
        $('.atv-video-card:not(.hidden)').each(function() {
            const $v = $(this);
            if ($v.data('video-url') === videoUrl) return; // Skip current

            const $item = $(`
                <div class="atv-related-item" data-video-url="${$v.data('video-url')}" data-title="${$v.data('title')}" data-desc="${$v.data('desc')}" data-cat-id="${$v.data('cat-id')}">
                    <img src="${$v.find('img').attr('src')}" alt="${$v.data('title')}">
                    <h4>${$v.data('title')}</h4>
                </div>
            `);
            $slider.append($item);
        });

        $lightbox.fadeIn();
        $('body').css('overflow', 'hidden');
    }

    // Slider Item Click
    $(document).on('click', '.atv-related-item', function() {
        const $item = $(this);
        openLightbox($item.data('video-url'), $item.data('title'), $item.data('desc'), $item.data('cat-id'));
    });

    // Close Modals
    $('.atv-close').on('click', function() {
        $lightbox.fadeOut();
        $catModal.fadeOut();
        $mainPlayer.attr('src', '');
        $('body').css('overflow', 'auto');
    });

    $(window).on('click', function(event) {
        if ($(event.target).is('.atv-modal')) {
            $('.atv-close').trigger('click');
        }
    });

    // Escape Key
    $(document).keyup(function(e) {
        if (e.key === "Escape") {
            $('.atv-close').trigger('click');
        }
    });
});
