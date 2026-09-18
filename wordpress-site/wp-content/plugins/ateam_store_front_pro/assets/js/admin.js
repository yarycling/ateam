jQuery(document).ready(function($) {
    // Media Uploader
    $('.ateam-upload-button').on('click', function(e) {
        e.preventDefault();
        var button = $(this);
        var container = button.closest('.ateam-media-uploader');
        var input = container.find('.ateam-media-id');
        var preview = container.find('.ateam-media-preview');
        var remove = container.find('.ateam-remove-button');

        var frame = wp.media({
            title: 'Select Image',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            input.val(attachment.id);
            preview.html('<img src="' + attachment.url + '" style="max-width:200px; height:auto; display:block; border:1px solid #ccc; padding:5px;">');
            remove.show();
        });

        frame.open();
    });

    $('.ateam-remove-button').on('click', function(e) {
        e.preventDefault();
        var button = $(this);
        var container = button.closest('.ateam-media-uploader');
        container.find('.ateam-media-id').val('');
        container.find('.ateam-media-preview').empty();
        button.hide();
    });
});
