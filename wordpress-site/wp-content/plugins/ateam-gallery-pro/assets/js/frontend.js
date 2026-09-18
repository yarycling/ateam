(function($) {
    'use strict';

    $(document).ready(function() {
        const $container = $('#ateam-gallery-container');
        const $grid = $container.find('.ateam-grid');
        const $loadMore = $('#ateam-load-more');
        const $navItems = $('.ateam-nav-item, .ateam-dropdown-item');
        const $lightbox = $('#ateam-lightbox');
        
        let currentPage = 1;
        let currentCategory = 'all';
        let perPage = $container.data('per-page') || 12;

        // --- Filtering Logic ---
        $navItems.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $this = $(this);
            const categoryId = $this.data('id');
            
            // Remove active from all
            $('.ateam-nav-item, .ateam-dropdown-item').removeClass('active');
            
            if ($this.hasClass('ateam-dropdown-item')) {
                $this.addClass('active');
                $this.closest('.ateam-nav-item').addClass('active');
            } else {
                $this.addClass('active');
            }

            currentCategory = categoryId;
            currentPage = 1;
            
            filterGallery(false);
        });

        function filterGallery(append = false) {
            $container.addClass('ateam-loading');

            $.ajax({
                url: ateam_gallery_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'ateam_filter_gallery',
                    nonce: ateam_gallery_ajax.nonce,
                    category: currentCategory,
                    paged: currentPage,
                    per_page: perPage
                },
                success: function(response) {
                    if (response.success) {
                        if (append) {
                            $grid.append(response.data.html);
                        } else {
                            $grid.fadeOut(200, function() {
                                $grid.html(response.data.html).fadeIn(200);
                            });
                        }

                        if (currentPage >= response.data.max_pages) {
                            $loadMore.hide();
                        } else {
                            $loadMore.show();
                        }
                    }
                    $container.removeClass('ateam-loading');
                }
            });
        }

        // --- Load More ---
        $loadMore.on('click', function() {
            currentPage++;
            filterGallery(true);
        });

        // --- Lightbox ---
        let currentItemIndex = 0;
        let items = [];

        function updateLightboxList() {
            items = [];
            $('.ateam-card').each(function() {
                const $card = $(this);
                items.push({
                    id: $card.attr('data-id'),
                    category: $card.attr('data-category'),
                    image: $card.attr('data-image'),
                    thumb: $card.find('.ateam-card-img').attr('src'),
                    title: $card.attr('data-title'),
                    desc: $card.attr('data-desc'),
                    location: $card.attr('data-location'),
                    date: $card.attr('data-date')
                });
            });
        }

        $(document).on('click', '.ateam-card', function() {
            updateLightboxList();
            const id = $(this).attr('data-id');
            currentItemIndex = items.findIndex(item => String(item.id) === String(id));
            if (currentItemIndex !== -1) {
                openLightbox(currentItemIndex);
            }
        });

        function openLightbox(index) {
            const item = items[index];
            if (!item) return;

            // Reset image first
            $('#ateam-lightbox-img').attr('src', '');
            $('#ateam-lightbox-img').attr('src', item.image);
            $('#ateam-lightbox-title').text(item.title);
            $('#ateam-lightbox-location').text(item.location || '');
            $('#ateam-lightbox-desc').html(item.desc);
            
            // Populate related grid
            populateRelatedGrid(item.category, item.id);

            $lightbox.addClass('active');
            $('body').css('overflow', 'hidden');
        }

        function populateRelatedGrid(categoryId, currentId) {
            const $relatedContainer = $('#ateam-lightbox-related');
            $relatedContainer.empty();

            const relatedItems = items.filter(item => item.category === categoryId);

            relatedItems.forEach(item => {
                const activeClass = item.id === currentId ? 'active' : '';
                const itemHtml = `
                    <div class="related-item ${activeClass}" data-id="${item.id}">
                        <img src="${item.thumb}" alt="${item.title}">
                    </div>
                `;
                $relatedContainer.append(itemHtml);
            });

            // Click listener for related items
            $relatedContainer.find('.related-item').on('click', function() {
                const id = $(this).attr('data-id');
                const index = items.findIndex(it => String(it.id) === String(id));
                if (index !== -1) {
                    currentItemIndex = index;
                    openLightbox(index);
                }
            });
        }

        $('.ateam-close').on('click', function() {
            $lightbox.removeClass('active');
            $('body').css('overflow', 'auto');
        });

        $('.ateam-prev').on('click', function() {
            currentItemIndex = (currentItemIndex - 1 + items.length) % items.length;
            openLightbox(currentItemIndex);
        });

        $('.ateam-next').on('click', function() {
            currentItemIndex = (currentItemIndex + 1) % items.length;
            openLightbox(currentItemIndex);
        });

        $(document).on('keydown', function(e) {
            if (!$lightbox.hasClass('active')) return;
            if (e.key === 'Escape') $('.ateam-close').click();
            if (e.key === 'ArrowLeft') $('.ateam-prev').click();
            if (e.key === 'ArrowRight') $('.ateam-next').click();
        });
    });

})(jQuery);

