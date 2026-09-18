(function ($) {
    function bindMediaPickers() {
        $('.ateam-rentals-media-button').on('click', function (event) {
            event.preventDefault();
            const $button = $(this);
            const $field = $button.closest('.ateam-rentals-media-field');
            const $input = $field.find('.ateam-rentals-media-input');
            const $preview = $field.find('.ateam-rentals-media-preview');
            const frame = wp.media({
                title: ATeamRentalsAdmin.chooseImage,
                button: { text: ATeamRentalsAdmin.useImage },
                multiple: false
            });

            frame.on('select', function () {
                const attachment = frame.state().get('selection').first().toJSON();
                $input.val(attachment.id);
                $preview.html('<img src="' + attachment.url + '" alt="">');
            });

            frame.open();
        });
    }

    function bindSortables() {
        $('.ateam-rentals-sortable').sortable({
            axis: 'y',
            cursor: 'move'
        });

        $('.ateam-rentals-save-order').on('click', function () {
            const $panel = $(this).closest('.ateam-rentals-panel');
            const $tbody = $panel.find('.ateam-rentals-sortable');
            const order = $tbody.find('tr').map(function () {
                return $(this).data('post-id');
            }).get();
            const postType = $tbody.data('post-type');
            const $notice = $panel.find('.ateam-rentals-inline-notice');

            $notice.removeClass('is-error is-success').text('Saving order...');

            $.post(ATeamRentalsAdmin.ajaxUrl, {
                action: 'ateam_rentals_save_order',
                nonce: ATeamRentalsAdmin.nonce,
                post_type: postType,
                order: order
            }).done(function (response) {
                if (response.success) {
                    $notice.addClass('is-success').text(response.data.message);
                } else {
                    $notice.addClass('is-error').text(response.data && response.data.message ? response.data.message : 'Unable to save order.');
                }
            }).fail(function () {
                $notice.addClass('is-error').text('Unable to save order.');
            });
        });
    }

    $(function () {
        bindMediaPickers();
        bindSortables();
    });
})(jQuery);

