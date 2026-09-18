(function ($) {
    function refreshTypeSections() {
        const type = $('[data-ateam-popup-type]').val();
        $('[data-ateam-section="event"]').toggle(type === 'event');
        $('[data-ateam-section="ad-video"]').toggle(type === 'ad' || type === 'video');
        $('[data-required-for-event]').prop('required', type === 'event');
    }

    function refreshPageList() {
        const allPages = $('[data-ateam-all-pages]').is(':checked');
        $('[data-ateam-page-list] input').prop('disabled', allPages);
        $('[data-ateam-page-list]').toggleClass('is-disabled', allPages);
    }

    function bindMediaPickers() {
        $('.ateam-popup-media-button').on('click', function (event) {
            event.preventDefault();
            const $field = $(this).closest('.ateam-popup-media-field');
            const $input = $field.find('.ateam-popup-media-input');
            const $preview = $field.find('.ateam-popup-media-preview');
            const frame = wp.media({
                title: ATeamPopupAdsAdmin.chooseMedia,
                button: { text: ATeamPopupAdsAdmin.useMedia },
                multiple: false
            });

            frame.on('select', function () {
                const attachment = frame.state().get('selection').first().toJSON();
                $input.val(attachment.id);
                $preview.html('<code>' + attachment.filename + '</code>');
            });

            frame.open();
        });

        $('.ateam-popup-media-clear').on('click', function (event) {
            event.preventDefault();
            const $field = $(this).closest('.ateam-popup-media-field');
            $field.find('.ateam-popup-media-input').val('');
            $field.find('.ateam-popup-media-preview').html('<em>No media selected.</em>');
        });
    }

    $(function () {
        bindMediaPickers();
        refreshTypeSections();
        refreshPageList();
        $('[data-ateam-popup-type]').on('change', refreshTypeSections);
        $('[data-ateam-all-pages]').on('change', refreshPageList);
    });
})(jQuery);
