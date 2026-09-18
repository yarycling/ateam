(function($) {
    'use strict';

    $(document).ready(function() {
        let mediaFrame;
        let selectedImages = [];

        // --- Bulk Add Logic ---
        $('#ateam-select-images').on('click', function(e) {
            e.preventDefault();

            if (mediaFrame) {
                mediaFrame.open();
                return;
            }

            mediaFrame = wp.media({
                title: 'Select Images für Gallery',
                button: { text: 'Add to Bulk List' },
                multiple: true,
                library: { type: 'image' }
            });

            mediaFrame.on('select', function() {
                const selection = mediaFrame.state().get('selection');
                selectedImages = [];
                $('#ateam-selected-preview').empty();

                selection.each(function(attachment) {
                    attachment = attachment.toJSON();
                    selectedImages.push(attachment.id);
                    
                    $('#ateam-selected-preview').append(
                        `<div class="preview-item">
                            <img src="${attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url}" />
                         </div>`
                    );
                });

                if (selectedImages.length > 0) {
                    $('#ateam-process-bulk').prop('disabled', false);
                } else {
                    $('#ateam-process-bulk').prop('disabled', true);
                }
            });

            mediaFrame.open();
        });

        $('#ateam-bulk-add-form').on('submit', function(e) {
            e.preventDefault();

            const categoryId = $('#bulk_category').val();
            const $status = $('#ateam-bulk-status');
            const $btn = $('#ateam-process-bulk');

            if (!categoryId || selectedImages.length === 0) return;

            $btn.prop('disabled', true).text('Processing...');
            $status.html('<p>Creating items, please wait...</p>');

            $.ajax({
                url: ateam_bulk_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'ateam_bulk_add_items',
                    nonce: ateam_bulk_ajax.nonce,
                    category_id: categoryId,
                    image_ids: selectedImages
                },
                success: function(response) {
                    if (response.success) {
                        $status.html(`<p style="color: green;">${response.data.message}</p>`);
                        $('#ateam-selected-preview').empty();
                        selectedImages = [];
                        $('#bulk_category').val('');
                    } else {
                        $status.html(`<p style="color: red;">Error: ${response.data}</p>`);
                    }
                    $btn.text('Create Gallery Items');
                },
                error: function() {
                    $status.html('<p style="color: red;">A server error occurred.</p>');
                    $btn.text('Create Gallery Items');
                }
            });
        });
    });

})(jQuery);

