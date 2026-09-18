jQuery(document).ready(function () {
    "use strict";
    
    /* Number Validation START */
    jQuery(".gspwp-number-cover input[type='number']").keydown(function (e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            // Allow: Ctrl+A, Command+A
            (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
            // let it happen, don't do anything
            return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
    /* Number Validation END */

    /* Color Picker START */
    jQuery('.gspwp-color-picker').wpColorPicker({
        // a callback to fire whenever the color changes to a valid color
        change: function (event, ui) {
            // Change only if the color picker is the user choice
        },
        // a callback to fire when the input is emptied or an invalid color
        clear: function () {
        },
        // hide the color picker controls on load
        hide: true,
        // show a group of common colors beneath the square
        // or, supply an array of colors to customize further
        palettes: true
    });
    /* Color Picker END */

    /* Gallery Upload START */
    jQuery('#gspwp_gallery_image_select').on('click', function (event) {
        event.preventDefault();

        // If the media frame already exists, reopen it.
        if (file_frame) {
            file_frame.open();
            return;
        }
        // Create the media frame.
        var file_frame = wp.media.frame = wp.media({
            frame: "post",
            state: "gs-gallery-images",
            library: { type: 'image' },
            button: { text: 'Edit Image Order' },
            multiple: true
        });

        // Create Featured Gallery state. This is essentially the Gallery state, but selection behavior is altered.
        file_frame.states.add([
            new wp.media.controller.Library({
                id: 'gs-gallery-images',
                title: gspwp_script_translations.select_gallery,
                priority: 20,
                toolbar: 'main-gallery',
                filterable: 'uploaded',
                library: wp.media.query(file_frame.options.library),
                multiple: file_frame.options.multiple ? 'reset' : false,
                editable: true,
                allowLocalEdits: true,
                displaySettings: true,
                displayUserSettings: true
            }),
        ]);

        file_frame.on('open', function () {
            var selection = file_frame.state().get('selection');
            var library = file_frame.state('gallery-edit').get('library');
            var ids = jQuery('#gspwp_gallery_images').val();
            if (ids) {
                var idsArray = ids.split(',');
                var attachment
                idsArray.forEach(function (id) {
                    attachment = wp.media.attachment(id);
                    attachment.fetch();
                    selection.add(attachment ? [attachment] : []);
                });
                file_frame.setState('gallery-edit');
                idsArray.forEach(function (id) {
                    attachment = wp.media.attachment(id);
                    attachment.fetch();
                    library.add(attachment ? [attachment] : []);
                });
            }
        });

        // When an image is selected, run a callback.
        file_frame.on('update', function () {
            var imageIDArray = [];
            var imageHTML = '';
            var metadataString = '';
            var gallery_post_id = jQuery('#gspwp_gallery_post_id').val();

            var images = file_frame.state().get('library');
            images.each(function (attachment) {
                imageIDArray.push(attachment.attributes.id);
            });
            metadataString = imageIDArray.join(",");
            if (metadataString.length > 0) {
                jQuery("#gspwp_gallery_images").val(metadataString);
                jQuery(".gspwp_gallery_option_input ul").html(imageHTML);
                jQuery('#gspwp_gallery_image_select').text('Edit');
                jQuery('#gspwp_gallery_image_removeall').removeClass('hidden');
                jQuery('#gspwp_gallery_image_removeall').addClass('visible');
            }

            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'gspwp_gallery_images_ajax',
                    postid: gallery_post_id,
                    mediadata: metadataString,
                },
                success: function (response) {
                    jQuery('.gspwp_gallery_cover').html(response);
                }
            });

        });

        // Finally, open the modal
        file_frame.open();

        event.stopImmediatePropagation();

    });

    jQuery('#gspwp_gallery_image_removeall').on('click', function (event) {
        event.preventDefault();
        if (confirm('Are you sure you want to remove all images?')) {
            jQuery(".gspwp_gallery_cover").html("");
            jQuery("#gspwp_gallery_images").val("");
            jQuery('#gspwp_gallery_image_removeall').addClass('hidden');
            jQuery('#gspwp_gallery_image_removeall').removeClass('visible');
            jQuery('#gspwp_gallery_image_select').text('Select Image');
        }

        event.stopImmediatePropagation();
    });

    jQuery(document).on('click', '.gspwp_gallery_option_input ul li button', function (event) {
        event.preventDefault();
        if (confirm(gspwp_script_translations.confirmation)) {
            var removedImage = jQuery(this).parent().children('img').attr('id');
            var oldGallery = jQuery("#gspwp_gallery_images").val();
            var newGallery = oldGallery.replace(',' + removedImage, '').replace(removedImage + ',', '').replace(removedImage, '');
            jQuery(this).parent('li').remove();
            jQuery("#gspwp_gallery_images").val(newGallery);
            if (newGallery == "") {
                jQuery('#gspwp_gallery_image_select').text('Select Image');
                jQuery('#gspwp_gallery_image_removeall').removeClass('visible');
                jQuery('#gspwp_gallery_image_removeall').addClass('hidden');
            }
        }
    });
    /* Gallery Upload END */

    /* Options Tab START */
    jQuery('.gspwp-options-tabs li:first-child').addClass('active');
    var $id = jQuery('.gspwp-options-tabs li.active').attr('data-id');
    jQuery('.gspwp-options-panel').hide();
    jQuery($id).show();

    jQuery('.gspwp-options-tabs .gspwp-options-tab').on('click', function (e) {
        e.preventDefault();
        if (!jQuery(this).hasClass('disable')) {
            jQuery('.gspwp-options-tab').removeClass('active');
            jQuery(this).addClass('active');
            var $id = jQuery(this).attr('data-id');
            jQuery('.gspwp-options-panel').hide();
            jQuery($id).show();
        }
    });
    /* Options Tab END */

    /* Layout Effect chage START */
    var $layout_effect = jQuery('#layout_effect').find("option:selected").parent("optgroup").attr("class");
    jQuery('#layout_effect_group').attr('value', $layout_effect);

    jQuery('#layout_effect').on('change', function () {
        var $layout_effect = jQuery(this).find("option:selected").parent("optgroup").attr("class");
        jQuery('#layout_effect_group').attr('value', $layout_effect);
    });
    /* Layout Effect chage END */

    /* Image Custom Size START */
    jQuery('.gspwp-image-custom-size').hide();
    if (jQuery('#image_size').val() == 'custom') {
        jQuery('.gspwp-image-custom-size').show();
    }
    jQuery('#image_size').on('change', function () {
        if (jQuery(this).val() == 'custom') {
            jQuery('.gspwp-image-custom-size').show();
        } else {
            jQuery('.gspwp-image-custom-size').hide();
        }
    });
    /* Image Custom Size END */

    var $effect = jQuery('#layout_effect').val();

    /* Hiding Options */
    gsSetOptionVisibility($effect);
    jQuery('#layout_effect').on('change', function () {
        gsSetOptionVisibility(jQuery(this).val());
        gsSetDefaultValue(jQuery(this).val());
    });

    /* Google Fonts */
    var $title_font = jQuery('.title_font_selected').val();
    var $content_font = jQuery('.content_font_selected').val();
    var $filter_title_font = jQuery('.filter_title_font_selected').val();
    jQuery.getJSON('https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyBwIX97bVWr3-6AIUvGkcNnmFgirefZ6Sw', function (data) {
        jQuery.each(data.items, function (index, font) {
            if ($title_font == font.family) {
                jQuery('#title_font').append(jQuery('<option selected="selected"></option>').attr('value', font.family).text(font.family));
            } else {
                jQuery('#title_font').append(jQuery('<option></option>').attr('value', font.family).text(font.family));
            }

            if ($filter_title_font == font.family) {
                jQuery('#filter_title_font').append(jQuery('<option selected="selected"></option>').attr('value', font.family).text(font.family));
            } else {
                jQuery('#filter_title_font').append(jQuery('<option></option>').attr('value', font.family).text(font.family));
            }

            if ($content_font == font.family) {
                jQuery('#content_font').append(jQuery('<option selected="selected"></option>').attr('value', font.family).text(font.family));
            } else {
                jQuery('#content_font').append(jQuery('<option></option>').attr('value', font.family).text(font.family));
            }
        });
    });
    /* Select refresh */
    jQuery('#title_font').select2().trigger('change');
    jQuery('#content_font').select2().trigger('change');
    jQuery('#filter_title_font').select2().trigger('change');

    /* Select */
    jQuery('.gspwp-select-cover select').select2({
        minimumResultsForSearch: Infinity,
        placeholder: jQuery(this).data('placeholder'),
    });

    /* Slider tab Active-Deactive START */
    if (jQuery('#layout_type').val() != 'slider') {
        jQuery('.slider-setting-data').addClass('disable');
    } else {
        jQuery('.slider-setting-data').removeClass('disable');
    }
    jQuery('#layout_type').on('change', function () {
        if (jQuery(this).val() != 'slider') {
            jQuery('.slider-setting-data').addClass('disable');
        } else {
            jQuery('.slider-setting-data').removeClass('disable');
        }
    });
    /* Slider tab Active-Deactive END */

    /* Filter design tab Active-Deactive START*/
    if (jQuery("input[name='options[want_category_filter]']:checked").val() == 0 && jQuery("input[name='options[dynamic_post_content]']:checked").val() == 0) {
        jQuery('.filter-design').removeClass('disable');
    } else {
        jQuery('.filter-design').addClass('disable');
    }

    jQuery("input[name='options[want_category_filter]']").change(function () {
        if (jQuery(this).val() == 0 && jQuery("input[name='options[dynamic_post_content]']:checked").val() == 0) {
            jQuery('.filter-design').removeClass('disable');
        } else {
            jQuery('.filter-design').addClass('disable');
        }
    });

    jQuery("input[name='options[dynamic_post_content]']").change(function () {
        if (jQuery(this).val() == 0 && jQuery("input[name='options[want_category_filter]']:checked").val() == 0) {
            jQuery('.filter-design').removeClass('disable');
            console.log('if');
        } else {
            console.log('else');
            jQuery('.filter-design').addClass('disable');
        }
    });
    /* Filter design tab Active-Deactive END*/

    /* Slider Arrow-Navigation Options Hide-Show START */
    var $id = jQuery('.gspwp-slider-menu li.active').attr('data-id');
    jQuery('.gspwp-slider-options').hide();
    jQuery("#" + $id).show();
    jQuery('.gspwp-slider-menu li').on('click', function () {
        jQuery('.gspwp-slider-menu li').removeClass('active');
        jQuery(this).addClass('active');
        jQuery('.gspwp-slider-options').hide();
        var $id = jQuery('.gspwp-slider-menu li.active').attr('data-id');
        jQuery("#" + $id).show();
    })
    /* Slider Arrow-Navigation Options Hide-Show START */

    /* Slider Arrow Option Hide-Show START */
    if (jQuery("input[name='options[display_arrow]']:checked").val() == 1) {
        jQuery('.gspwp-arrow-options').hide();
    } else {
        jQuery('.gspwp-arrow-options').show();
    }
    jQuery("input[name='options[display_arrow]']").change(function () {
        if (jQuery(this).val() == 1) {
            jQuery('.gspwp-arrow-options').hide();
        } else {
            jQuery('.gspwp-arrow-options').show();
        }
    });
    /* Slider Arrow Option Hide-Show END */

    /* Slider Navigation Option Hide-Show START */
    if (jQuery("input[name='options[display_navigation]']:checked").val() == 1) {
        jQuery('.gspwp-navigation-options').hide();
    } else {
        jQuery('.gspwp-navigation-options').show();
    }
    jQuery("input[name='options[display_navigation]']").change(function () {
        if (jQuery(this).val() == 1) {
            jQuery('.gspwp-navigation-options').hide();
        } else {
            jQuery('.gspwp-navigation-options').show();
        }
    });
    /* Slider Navigation Option Hide-Show END */

    /* Title Option Hide-Show START */
    if (jQuery("input[name='options[display_title]']:checked").val() == 1) {
        jQuery('.gspwp-title-options').hide();
    } else {
        jQuery('.gspwp-title-options').show();
    }
    jQuery("input[name='options[display_title]']").change(function () {
        if (jQuery(this).val() == 1) {
            jQuery('.gspwp-title-options').hide();
        } else {
            jQuery('.gspwp-title-options').show();
        }
    });
    /* Title Option Hide-Show END */

    /* Content Option Hide-Show START */
    if (jQuery("input[name='options[display_content]']:checked").val() == 1) {
        jQuery('.gspwp-content-options').hide();
    } else {
        jQuery('.gspwp-content-options').show();
    }
    jQuery("input[name='options[display_content]']").change(function () {
        if (jQuery(this).val() == 1) {
            jQuery('.gspwp-content-options').hide();
        } else {
            jQuery('.gspwp-content-options').show();
        }
    });
    /* Content Option Hide-Show END */

    /* Arrows START */
    jQuery(document).on('click', '#gspwp-arrow-style-dialog .gspwp-arrow-style-box .gspwp-arrow-cover', function (e) {
        e.preventDefault();
        jQuery('#gspwp-arrow-style-dialog .gspwp-arrow-style-box .gspwp-arrow-cover').removeClass('gspwp_selected');
        jQuery(this).addClass('gspwp_selected');
    });

    var gspwp_arrow_dialog = jQuery("#gspwp-arrow-style-dialog").dialog({
        resizable: false,
        draggable: false,
        autoOpen: false,
        modal: true,
        height: 400,
        width: 700,
        buttons: [{
            text: gspwp_script_translations.set_arrow_style,
            id: "btn_set_arrow_style",
            class: "button button-primary",
            click: function () {
                var $arrow_style = jQuery('#gspwp-arrow-style-dialog .gspwp-arrow-cover.gspwp_selected').attr('data-style');
                if (typeof $arrow_style === 'undefined' || $arrow_style === null) {
                    jQuery("#gspwp-arrow-style-dialog").dialog('close');
                    return;
                }
                jQuery('#gspwp-arrow-style').val($arrow_style);
                jQuery('.gspwp-arrow-options .gspwp-arrow-cover .gspwp-arrow').removeClass(function (index, className) {
                    return (className.match(/(^|\s)gspwp-arrow-style-\S+/g) || []).join(' ');
                });
                jQuery('.gspwp-arrow-options .gspwp-arrow-cover .gspwp-arrow').addClass('gs-arrow-' + $arrow_style);
                jQuery(this).dialog("close");
            }
        },
        {
            text: gspwp_script_translations.close,
            class: 'gspwp_arrow_close',
            click: function () {
                jQuery(this).dialog("close");
            }
        }
        ],
        open: function (event, ui) {
            jQuery('#gspwp-arrow-style-dialog .gspwp-arrow-style-box .gspwp-arrow-cover').removeClass('gspwp_selected');
            var $arrow_style = jQuery('#gspwp-arrow-style').val();
            jQuery('#gspwp-arrow-style-dialog .gspwp-arrow-style-box .gspwp-arrow-cover').each(function () {
                if (jQuery(this).attr('data-style') == $arrow_style) {
                    jQuery(this).addClass('gspwp_selected');
                }
            });
        }
    });

    jQuery(".gspwp-arrow-style").on("click", function () {
        gspwp_arrow_dialog.dialog("open");
    });
    /* Arrows END */

    /* Navigation START*/
    jQuery(document).on('click', '#gspwp-navigation-style-dialog .gspwp-navigation-style-box .gspwp-navigation-cover', function (e) {
        e.preventDefault();
        jQuery('#gspwp-navigation-style-dialog .gspwp-navigation-style-box .gspwp-navigation-cover').removeClass('gspwp_selected');
        jQuery(this).addClass('gspwp_selected');
    });

    jQuery(document).on('click', '.gspwp-navigation-cover .owl-pagination .owl-page', function (e) {
        e.preventDefault();
        var $cover = jQuery(this).closest('.gspwp-navigation-cover');
        $cover.find('.owl-pagination .owl-page').removeClass('active');
        jQuery(this).addClass('active');
    });

    var gspwp_navigation_dialog = jQuery("#gspwp-navigation-style-dialog").dialog({
        resizable: false,
        draggable: false,
        autoOpen: false,
        modal: true,
        height: 400,
        width: 700,
        buttons: [{
            text: gspwp_script_translations.set_navigation_style,
            id: "btn_set_navigation_style",
            class: "button button-primary",
            click: function () {
                var $navigation_style = jQuery('#gspwp-navigation-style-dialog .gspwp-navigation-cover.gspwp_selected').attr('data-style');
                if (typeof $navigation_style === 'undefined' || $navigation_style === null) {
                    jQuery("#gspwp-navigation-style-dialog").dialog('close');
                    return;
                }
                jQuery('#gspwp-navigation-style').val($navigation_style);
                jQuery('.gspwp-navigation-options .gspwp-navigation-cover .gspwp-navigation').removeClass(function (index, className) {
                    return (className.match(/(^|\s)gspwp-navigation-style-\S+/g) || []).join(' ');
                });
                jQuery('.gspwp-navigation-options .gspwp-navigation-cover .gspwp-navigation').addClass('gs-navigation-' + $navigation_style);
                jQuery(this).dialog("close");
            }
        },
        {
            text: gspwp_script_translations.close,
            class: 'gs_navigation_close',
            click: function () {
                jQuery(this).dialog("close");
            }
        }
        ],
        open: function (event, ui) {
            jQuery('#gspwp-navigation-style-dialog .gspwp-navigation-style-box .gspwp-navigation-cover').removeClass('gspwp_selected');
            var $navigation_style = jQuery('#gspwp-navigation-style').val();
            jQuery('#gspwp-navigation-style-dialog .gspwp-navigation-style-box .gspwp-navigation-cover').each(function () {
                if (jQuery(this).attr('data-style') == $navigation_style) {
                    jQuery(this).addClass('gspwp_selected');
                }
            });

        }
    });
    jQuery(".gspwp-navigation-style").on("click", function () {
        gspwp_navigation_dialog.dialog("open");
    });
    /* Navigation END*/

    /* Gallery Source START */
    if (jQuery("#gallery_source").val() == 'posts') {
        jQuery('.gspwp_gallery_option_input').hide();
        jQuery('.gspwp_gallery_posts_option').show();
    } else {
        jQuery('.gspwp_gallery_option_input').show();
        jQuery('.gspwp_gallery_posts_option').hide();
    }
    jQuery("#gallery_source").change(function () {
        if (jQuery(this).val() == 'posts') {
            jQuery('.gspwp_gallery_option_input').hide();
            jQuery('.gspwp_gallery_posts_option').show();
        } else {
            jQuery('.gspwp_gallery_option_input').show();
            jQuery('.gspwp_gallery_posts_option').hide();
        }
    });
    /* Gallery Source END */

    /* Dynamic Post content Hide-Show START */
    if (jQuery("input[name='options[dynamic_post_content]']:checked").val() == 1) {
        jQuery('.gspwp-dynamic-post-data').hide();

        // Show Gallery Options Meta 
        jQuery('#advanced-sortables').show();
        jQuery('#gs_gallery_image ').removeClass('hide-if-js');

    } else {
        jQuery('.gspwp-dynamic-post-data').show();

        // Hide Gallery Options Meta 
        jQuery('#advanced-sortables').hide();
        jQuery('#gs_gallery_image ').addClass('hide-if-js');
    }

    jQuery("input[name='options[dynamic_post_content]']").change(function () {
        if (jQuery(this).val() == 1) {
            jQuery('.gspwp-dynamic-post-data').hide();

            // Show Gallery Options Meta 
            jQuery('#advanced-sortables').show();
            jQuery('#gs_gallery_image ').removeClass('hide-if-js');
            
        } else {
            jQuery('.gspwp-dynamic-post-data').show();

            // Hide Gallery Options Meta 
            jQuery('#advanced-sortables').hide();
            jQuery('#gs_gallery_image ').addClass('hide-if-js');
        }
    });
    /* Dynamic Post content Hide-Show END */

    /* GS number input js START */
    jQuery('<div class="number-nav"><div class="number-button number-up">+</div><div class="number-button number-down">-</div></div>').insertAfter('.gspwp-number-style input');
    jQuery('.gspwp-number-style').each(function () {
        var spinner = jQuery(this),
            input = spinner.find('input[type="number"]'),
            btnUp = spinner.find('.number-up'),
            btnDown = spinner.find('.number-down'),
            min = input.attr('min'),
            max = input.attr('max');

        btnUp.on('click', function () {
            var oldValue = parseFloat(input.val());
            if (oldValue >= max) {
                var newVal = oldValue;
            } else {
                var newVal = oldValue + 1;
            }
            spinner.find("input").val(newVal);
            spinner.find("input").trigger("change");
        });

        btnDown.on('click', function () {
            var oldValue = parseFloat(input.val());
            if (oldValue <= min) {
                var newVal = oldValue;
            } else {
                var newVal = oldValue - 1;
            }
            spinner.find("input").val(newVal);
            spinner.find("input").trigger("change");
        });
    });
    /* GS number input js END*/

    /* Want Category Filter Hide-Show START*/
    if (jQuery("input[name='options[want_category_filter]']:checked").val() == 1) {
        jQuery('.gspwp-category-filter').hide();
    } else {
        jQuery('.gspwp-category-filter').show();
    }

    jQuery("input[name='options[want_category_filter]']").change(function () {
        if (jQuery(this).val() == 1) {
            jQuery('.gspwp-category-filter').hide();
        } else {
            jQuery('.gspwp-category-filter').show();
        }
    });
    /* Want Category Filter Hide-Show END*/

    /* Custom post type taxonomies Hide-Show START */
    if (jQuery("input[name='options[want_category_filter]']:checked").val() == 0 && jQuery("input[name='options[dynamic_post_content]']:checked").val() == 0) {
        jQuery('.custom-post-type-taxonomies').show();
    } else {
        jQuery('.custom-post-type-taxonomies').hide();
    }

    jQuery("input[name='options[want_category_filter]']").change(function () {
        if (jQuery(this).val() == 0 && jQuery("input[name='options[dynamic_post_content]']:checked").val() == 0) {
            jQuery('.custom-post-type-taxonomies').show();
        } else {
            jQuery('.custom-post-type-taxonomies').hide();
        }
    });

    jQuery("input[name='options[dynamic_post_content]']").change(function () {
        if (jQuery(this).val() == 0 && jQuery("input[name='options[want_category_filter]']:checked").val() == 0) {
            jQuery('.custom-post-type-taxonomies').show();
        } else {
            jQuery('.custom-post-type-taxonomies').hide();
        }
    });
    /* Custom post type taxonomies Hide-Show END */

    /* Depended Taxonomy Dropdown Hide-Show START */
    jQuery("#custom_post_type").change(function () {
        var custom_post_type = jQuery("#custom_post_type").val();
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'gspwp_cpt_related_taxonomy',
                custom_post_type: custom_post_type,
            },
            success: function (response) {
                if (response) {
                    jQuery('#custom_post_type_taxonomies').html(response);
                } else {
                    jQuery('#custom_post_type_taxonomies').html('<option value="" >No taxonomy found.</option>');
                }
            }
        });
    });
    /* Depended Taxonomy Dropdown Hide-Show END */

});

/* Hiding Functions */
function gsSetOptionVisibility(effect) {
    var $layout_border_color = ["bubba", "chico", "dexter", "layla", "jazz", "romeo", "roxy", "ming", "oscar"];
    var $title_bg_color = ["steve", "zoe"];
    var $content_bg_color = ["julia", "steve"];

    var $effect = jQuery.trim(effect);
    if ($effect == '') {
        return;
    }
    if (jQuery.inArray($effect, $title_bg_color) !== -1) {
        jQuery('.gspwp-title-bg-color-div').show();
    } else {
        jQuery('.gspwp-title-bg-color-div').hide();
    }
    if (jQuery.inArray($effect, $layout_border_color) !== -1) {
        jQuery('.gspwp-layout-border-color-div').show();
    } else {
        jQuery('.gspwp-layout-border-color-div').hide();
    }

    if (jQuery.inArray($effect, $content_bg_color) !== -1) {
        jQuery('.gspwp-content-bg-color-div').show();
    } else {
        jQuery('.gspwp-content-bg-color-div').hide();
    }
}

/* Defult Values */
function gsSetDefaultValue(effect) {
    var $effect = jQuery.trim(effect);
    if ($effect == '') {
        return;
    }
    if ($effect == 'bubba') {
        jQuery('#gs-layout-bg-color').iris('color', '#9e5406');
        jQuery('#gs-layout-border-color').iris('color', '#ffffff');
        jQuery('#gs-title-font-color').iris('color', '#ffffff');
        jQuery('#gs-content-font-color').iris('color', '#ffffff');
    }
    if ($effect == 'chico') {
        jQuery('#gs-layout-bg-color').iris('color', '#3085a3');
        jQuery('#gs-layout-border-color').iris('color', '#ffffff');
        jQuery('#gs-title-font-color').iris('color', '#ffffff');
        jQuery('#gs-content-font-color').iris('color', '#ffffff');
    }
    if ($effect == 'dexter') {
        jQuery('#gs-layout-bg-color').iris('color', 'rgba(104,60,19,1)');
        jQuery('#gs-layout-border-color').iris('color', '#ffffff');
        jQuery('#gs-title-font-color').iris('color', '#ffffff');
        jQuery('#gs-content-font-color').iris('color', '#ffffff');
    }
    if ($effect == 'duke') {
        jQuery('#gs-layout-bg-color').iris('color', '#cc6055');
        jQuery('#gs-title-font-color').iris('color', '#ffffff');
        jQuery('#gs-content-font-color').iris('color', '#ffffff');
    }
    if ($effect == 'jazz') {
        jQuery('#gs-layout-bg-color').iris('color', '#f33f58');
        jQuery('#gs-layout-border-color').iris('color', '#ffffff');
        jQuery('#gs-title-font-color').iris('color', '#ffffff');
        jQuery('#gs-content-font-color').iris('color', '#ffffff');
    }
    if ($effect == 'julia') {
        jQuery('#gs-layout-bg-color').iris('color', '#2f3238');
        jQuery('#gs-title-font-color').iris('color', '#ffffff');
        jQuery('#gs-content-font-color').iris('color', '#2f3238');
        jQuery('#gs-content-bg-color').iris('color', 'rgba(255,255,255,0.9)');
    }
    if ($effect == 'goliath') {
        jQuery('#gs-layout-bg-color').iris('color', '#df4e4e');
        jQuery('#gs-title-font-color').iris('color', '#ffffff');
        jQuery('#gs-content-font-color').iris('color', '#ffffff');
    }
    if ($effect == 'layla') {
        jQuery('#gs-layout-bg-color').iris('color', '#18a367');
        jQuery('#gs-layout-border-color').iris('color', '#ffffff');
        jQuery('#gs-title-font-color').iris('color', '#ffffff');
        jQuery('#gs-content-font-color').iris('color', '#ffffff');
    }
    if ($effect == 'lily') {
        jQuery('#gs-layout-bg-color').iris('color', '#3085a3');
        jQuery('#gs-title-font-color').iris('color', '#ffffff');
        jQuery('#gs-content-font-color').iris('color', '#ffffff');
    }
    if ($effect == 'marley') {
        jQuery('#gs-layout-bg-color').iris('color', '#3085a3');
        jQuery('#gs-title-font-color').iris('color', '#ffffff');
        jQuery('#gs-content-font-color').iris('color', '#ffffff');
    }
    if ($effect == 'milo') {
        jQuery('#gs-layout-bg-color').iris('color', '#2e5d5a');
        jQuery('#gs-title-font-color').iris('color', '#ffffff');
        jQuery('#gs-content-font-color').iris('color', '#ffffff');
    }
    if ($effect == 'ming') {
        jQuery('#gs-layout-bg-color').iris('color', '#030c17');
        jQuery('#gs-layout-border-color').iris('color', '#ffffff');
        jQuery('#gs-title-font-color').iris('color', '#ffffff');
        jQuery('#gs-content-font-color').iris('color', '#ffffff');
    }
    if ($effect == 'moses') {
        jQuery('#gs-layout-bg-color').iris('color', '#05E0D8');
        jQuery('#gs-layout-border-color').iris('color', '#ffffff');
        jQuery('#gs-title-font-color').iris('color', '#ffffff');
        jQuery('#gs-content-font-color').iris('color', '#ffffff');
    }
    if ($effect == 'oscar') {
        jQuery('#gs-layout-bg-color').iris('color', '#05E0D8');
        jQuery('#gs-layout-border-color').iris('color', '#ffffff');
        jQuery('#gs-title-font-color').iris('color', '#ffffff');
        jQuery('#gs-content-font-color').iris('color', '#ffffff');
    }
    if ($effect == 'ruby') {
        jQuery('#gs-layout-bg-color').iris('color', '#17819c');
        jQuery('#gs-layout-border-color').iris('color', '#ffffff');
        jQuery('#gs-title-font-color').iris('color', '#ffffff');
        jQuery('#gs-content-font-color').iris('color', '#ffffff');
    }
    if ($effect == 'romeo') {
        jQuery('#gs-layout-bg-color').iris('color', '#3085a3 ');
        jQuery('#gs-layout-border-color').iris('color', '#ffffff');
        jQuery('#gs-title-font-color').iris('color', '#ffffff');
        jQuery('#gs-content-font-color').iris('color', '#ffffff');
    }
    if ($effect == 'roxy') {
        jQuery('#gs-layout-bg-color').iris('color', '#05abe0 ');
        jQuery('#gs-layout-border-color').iris('color', '#ffffff');
        jQuery('#gs-title-font-color').iris('color', '#ffffff');
        jQuery('#gs-content-font-color').iris('color', '#ffffff');
    }
    if ($effect == 'sadie') {
        jQuery('#gs-layout-bg-color').iris('color', 'rgba(72,76,97,0.8)');
        jQuery('#gs-title-font-color').iris('color', '#ffffff');
        jQuery('#gs-content-font-color').iris('color', '#ffffff');
    }
    if ($effect == 'sarah') {
        jQuery('#gs-layout-bg-color').iris('color', '#42b078');
        jQuery('#gs-title-font-color').iris('color', '#ffffff');
        jQuery('#gs-content-font-color').iris('color', '#ffffff');
    }
    if ($effect == 'selena') {
        jQuery('#gs-layout-bg-color').iris('color', '#ffffff');
        jQuery('#gs-title-font-color').iris('color', '#ffffff');
        jQuery('#gs-content-font-color').iris('color', '#ffffff');
    }
    if ($effect == 'steve') {
        jQuery('#gs-layout-bg-color').iris('color', '#000000');
        jQuery('#gs-title-font-color').iris('color', '#2d434e');
        jQuery('#gs-title-bg-color').iris('color', '#ffffff');
        jQuery('#gs-content-font-color').iris('color', '#2d434e');
        jQuery('#gs-content-bg-color').iris('color', '#ffffff');
    }
    if ($effect == 'zoe') {
        jQuery('#gs-layout-bg-color').iris('color', '#ffffff');
        jQuery('#gs-title-font-color').iris('color', '#3c4a50');
        jQuery('#gs-title-bg-color').iris('color', '#ffffff');
        jQuery('#gs-content-font-color').iris('color', '#ffffff');
    }
}