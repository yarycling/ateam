/**
 * ATEAM EVENTS PRO - Admin JavaScript
 *
 * @package ATeam_Events_Pro
 */

(function ($) {
    'use strict';

    $(document).ready(function () {

        // ===== Toggle: Multi-day event =====
        $('#ateam_event_multiday').on('change', function () {
            if ($(this).is(':checked')) {
                $('#ateam-end-date-field').slideDown(250);
            } else {
                $('#ateam-end-date-field').slideUp(250);
                $('#ateam_event_end_date').val('');
            }
        });

        // ===== Toggle: All day event =====
        $('#ateam_event_all_day').on('change', function () {
            if ($(this).is(':checked')) {
                $('#ateam-time-fields').slideUp(250);
            } else {
                $('#ateam-time-fields').slideDown(250);
            }
        });

        // ===== Toggle: Virtual event =====
        $('#ateam_event_virtual').on('change', function () {
            if ($(this).is(':checked')) {
                $('#ateam-virtual-fields').slideDown(250);
                $('#ateam-location-fields').slideUp(250);
            } else {
                $('#ateam-virtual-fields').slideUp(250);
                $('#ateam-location-fields').slideDown(250);
            }
        });

        // ===== Color Picker initialization =====
        if ($.fn.wpColorPicker) {
            $('.ateam-color-picker').wpColorPicker();
        }

        // ===== Smooth transitions for meta boxes =====
        $('.ateam-meta-wrapper').each(function () {
            $(this).css('opacity', 0).animate({ opacity: 1 }, 400);
        });

        // ===== Validate date fields =====
        $('form#post').on('submit', function () {
            var eventDate = $('#ateam_event_date').val();

            if (!eventDate) {
                // Highlight the required field
                $('#ateam_event_date').css({
                    'border-color': '#EF4444',
                    'box-shadow': '0 0 0 3px rgba(239, 68, 68, 0.12)'
                });

                // Scroll to the field
                $('html, body').animate({
                    scrollTop: $('#ateam_event_date').offset().top - 100
                }, 400);

                return false;
            }

            // Validate end date is after start date if multi-day
            if ($('#ateam_event_multiday').is(':checked')) {
                var endDate = $('#ateam_event_end_date').val();
                if (endDate && endDate < eventDate) {
                    alert('End date must be after the start date.');
                    $('#ateam_event_end_date').focus();
                    return false;
                }
            }

            // Validate end time is after start time
            var startTime = $('#ateam_event_start_time').val();
            var endTime = $('#ateam_event_end_time').val();
            if (startTime && endTime && endTime <= startTime && !$('#ateam_event_multiday').is(':checked')) {
                alert('End time must be after the start time.');
                $('#ateam_event_end_time').focus();
                return false;
            }
        });

        // ===== Copy shortcode to clipboard =====
        $(document).on('click', '.ateam-code-block', function () {
            var text = $(this).text().trim();
            var $el = $(this);

            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function () {
                    showCopyFeedback($el);
                });
            } else {
                // Fallback
                var textarea = document.createElement('textarea');
                textarea.value = text;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                showCopyFeedback($el);
            }
        });

        function showCopyFeedback($el) {
            var original = $el.text();
            $el.text('✓ Copied!').css({
                'background': '#22C55E',
                'color': '#fff'
            });

            setTimeout(function () {
                $el.text(original).css({
                    'background': '',
                    'color': ''
                });
            }, 1500);
        }

        // ===== Input focus animation =====
        $(document).on('focus', '.ateam-input', function () {
            $(this).closest('.ateam-meta-field').find('label').css('color', '#6C63FF');
        });

        $(document).on('blur', '.ateam-input', function () {
            $(this).closest('.ateam-meta-field').find('label').css('color', '');
        });

        // ===== Media Uploader for Default Image =====
        var mediaUploader;
        $(document).on('click', '.ateam-select-image', function (e) {
            e.preventDefault();
            var $button = $(this);
            var $wrapper = $button.closest('.ateam-image-selector-wrap');

            if (mediaUploader) {
                mediaUploader.open();
                return;
            }

            mediaUploader = wp.media({
                title: 'Select Default Event Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false
            });

            mediaUploader.on('select', function () {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $wrapper.find('.ateam-image-id').val(attachment.id);
                $wrapper.find('.ateam-image-preview img').attr('src', attachment.url);
                $wrapper.find('.ateam-image-preview').show();
                $wrapper.find('.ateam-remove-image').show();
            });

            mediaUploader.open();
        });

        $(document).on('click', '.ateam-remove-image', function (e) {
            e.preventDefault();
            var $button = $(this);
            var $wrapper = $button.closest('.ateam-image-selector-wrap');
            $wrapper.find('.ateam-image-id').val('');
            $wrapper.find('.ateam-image-preview').hide();
            $button.hide();
        });

    });

})(jQuery);
