<?php

/**
 * Add Metaboxes
 *
 * @version 1.0.0
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

add_meta_box('gs_gallery_image', esc_html__('Gallery Options', 'gallery-showcase-pro'), 'gspwp_gallery_image', 'gs_layouts', 'normal');

if (!function_exists('gspwp_gallery_image')) {

    function gspwp_gallery_image($post) {
        $gallery_meta_data = get_post_custom($post->ID);

        $gspwp_gallery_image = '';
        if (isset($gallery_meta_data['gs_gallery_images'])) {
            $gspwp_gallery_image = $gallery_meta_data['gs_gallery_images'][0];
        }
        $button = '<button></button>';
        $selectText = esc_html__('Select Images', 'gallery-showcase-pro');
        $visible = 'hidden';
        if ($gspwp_gallery_image) {
            $selectText = esc_html__('Edit', 'gallery-showcase-pro');
            $visible = 'visible';
        }
        $gspwp_gallery_details = '';
        if (isset($gallery_meta_data['gs_gallery_details'])) {
            $gspwp_gallery_details = unserialize($gallery_meta_data['gs_gallery_details'][0]);
        }
        wp_nonce_field(plugin_basename(__FILE__), 'gspwp_gallery_image_information');
        $gspwp_options = get_post_custom($post->ID, 'gs_optoins', true);
        if (is_array($gspwp_options) && !empty($gspwp_options)) {
            $gspwp_options = unserialize($gspwp_options['gs_optoins'][0]);
        } else {
            $gspwp_options = array();
        }
        $default_value = gspwp_default_values();

        $gspwp_options = array_merge($default_value, $gspwp_options);
        ?>
        <div class="gspwp_gallery_option_wrap">

            <div class="gspwp_gallery_option_input">
                <div class="gspwp_gallery_cover">
                    <?php
                        if ($gspwp_gallery_image) {
                            $gallery_images = explode(',', $gspwp_gallery_image);
                            foreach ($gallery_images as $value) {
                                $post_data = get_post($value);

                                $title = isset($gspwp_gallery_details[$value]['title']) ? $gspwp_gallery_details[$value]['title'] : $post_data->post_title;
                                $desc = isset($gspwp_gallery_details[$value]['desc']) ? $gspwp_gallery_details[$value]['desc'] : $post_data->post_content;
                                ?>
                                <div class="gspwp_gallery_single_cover">
                                    <div class="gspwp_gallery_image_cover">
                                        <img src="<?php echo esc_url(wp_get_attachment_url($value)); ?>" id="<?php echo 'gs_gallery_' . esc_attr($value); ?>" >
                                    </div>
                                    <div class="gspwp_gallery_content_cover">
                                        <p>
                                            <label for="gspwp_gallery_title_<?php echo esc_attr($value); ?>"><?php esc_html_e('Title', 'gallery-showcase-pro'); ?></label>
                                            <input id="gspwp_gallery_title_<?php echo esc_attr($value); ?>" name="gs_gallery_details[<?php echo esc_attr($value); ?>][title]" type="text" value="<?php echo esc_attr($title); ?>">
                                        </p>
                                        <p>
                                            <label for="gspwp_gallery_desc_<?php echo esc_attr($value); ?>"><?php esc_html_e('Description', 'gallery-showcase-pro'); ?></label>
                                            <textarea id="gspwp_gallery_desc_<?php echo esc_attr($value); ?>" name="gs_gallery_details[<?php echo esc_attr($value); ?>][desc]"><?php echo esc_attr($desc); ?></textarea>
                                        </p>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                    ?>
                </div>
                <input type="hidden" name="gs_gallery_post_id" id="gspwp_gallery_post_id" value="<?php echo esc_attr($post->ID); ?>" />
                <input type="hidden" name="gs_gallery_images" id="gspwp_gallery_images" value="<?php echo esc_attr($gspwp_gallery_image); ?>" />
                <button class="button" id="gspwp_gallery_image_select"><?php echo esc_html( $selectText , 'gallery-showcase-pro'); ?></button>
                <button class="button <?php echo esc_attr($visible); ?>" id="gspwp_gallery_image_removeall"><?php esc_html_e('Remove All', 'gallery-showcase-pro'); ?></button>
            </div>

        </div>
        <?php
    }

}

/**
 * Add Metaboxes For Layout Options
 *
 * @version 1.0.0
 * @since   1.0.0
 */
add_meta_box('gs_layout_options', esc_html__('Layout Options', 'gallery-showcase-pro'), 'gspwp_layout_options', 'gs_layouts', 'normal', 'high');

if (!function_exists('gspwp_layout_options')) {

    function gspwp_layout_options($post) {
        wp_nonce_field(plugin_basename(__FILE__), 'gspwp_layout_options_nonce');
        $gspwp_options = get_post_custom($post->ID, 'gs_optoins', true);

        if (is_array($gspwp_options) && !empty($gspwp_options)) {
            $gspwp_options = unserialize($gspwp_options['gs_optoins'][0]);
        } else {
            $gspwp_options = array();
        }
        $default_value = gspwp_default_values();
        $gspwp_options = array_merge($default_value, $gspwp_options);
        ?>
        <div class="gspwp_panel_wrap">
            <ul class="gspwp-options-tabs">
                <li class="gspwp-options-tab layout-setting-data" data-id="#layout-setting-data">
                    <span> <i class="fa fa-gears"></i> <?php esc_html_e('General', 'gallery-showcase-pro') ?></span>
                </li>
                <li class="gspwp-options-tab thumbnail-setting-data" data-id="#thumbnail-setting-data">
                    <span> <i class="fa fa-image"></i> <?php esc_html_e('Thumbnail', 'gallery-showcase-pro') ?></span>
                </li>
                <li class="gspwp-options-tab slider-setting-data" data-id="#slider-setting-data">
                    <span> <i class="fa fa-map"></i> <?php esc_html_e('Slider Setting', 'gallery-showcase-pro') ?></span>
                </li>
                <li class="gspwp-options-tab title-setting-data" data-id="#title-setting-data">
                    <span> <i class="fa fa-window-maximize"></i> <?php esc_html_e('Title Setting', 'gallery-showcase-pro') ?></span>
                </li>
                <li class="gspwp-options-tab content-setting-data" data-id="#content-setting-data">
                    <span> <i class="fa fa-file-text-o"></i> <?php esc_html_e('Content Setting', 'gallery-showcase-pro') ?></span>
                </li>
                <li class="gspwp-options-tab filter-design" data-id="#filter-design">
                    <span> <i class="fa fa-delicious"></i> <?php esc_html_e('Filter Design', 'gallery-showcase-pro') ?></span>
                </li>
            </ul>

            <div id="layout-setting-data" class="panel gspwp-options-panel hidden">

                <div class="gspwp-options-group">
                    <div class="gspwp-form-field gspwp-buttonset-cover">
                        <label> <?php esc_html_e('Enable Dynamic Posts?', 'gallery-showcase-pro'); ?> </label>
                        <fieldset class="gspwp-buttonset">
                            <?php $dynamic_post_content = isset($gspwp_options['dynamic_post_content']) ? $gspwp_options['dynamic_post_content'] : 1; ?>
                            <input id="dynamic_post_content_0" name="options[dynamic_post_content]" type="radio" value="0" <?php echo checked(0, $dynamic_post_content); ?>/>
                            <label for="dynamic_post_content_0"><?php esc_html_e('Yes', 'gallery-showcase-pro'); ?></label>
                            <input id="dynamic_post_content_1" name="options[dynamic_post_content]" type="radio" value="1" <?php echo checked(1, $dynamic_post_content); ?> />
                            <label for="dynamic_post_content_1"><?php esc_html_e('No', 'gallery-showcase-pro'); ?></label>
                        </fieldset>
                    </div>
                </div>

                <div class="gspwp-options-group gspwp-dynamic-post-data">
                    <div class="gspwp-form-field gspwp-select-cover">
                        <label for="custom_post_type"><?php esc_html_e('Custom Post Type', 'gallery-showcase-pro') ?></label>
                        <select id="custom_post_type" name="options[custom_post_type]"  class="chosen-select" data-placeholder="<?php esc_attr_e('Custom Post Type', 'gallery-showcase-pro') ?>">
                        <?php
                            $args = array(
                                'public'   => true,
                            );

                            $output = 'objects'; // names or objects, note names is the default
                            $operator = 'and'; // 'and' or 'or'

                            $post_types = get_post_types( $args, $output, $operator );

                            foreach ($post_types as $post_type) {
                                if( $post_type->name != 'attachment' && $post_type->name != 'page') {
                                    ?>
                                        <option value="<?php echo esc_attr($post_type->name); ?>" <?php echo ( isset($gspwp_options['custom_post_type']) && $gspwp_options['custom_post_type'] == $post_type->name ) ? 'selected="selected"' : '' ?>><?php echo esc_html($post_type->label, 'gallery-showcase-pro') ?></option>
                                    <?php
                                }
                            }
                        ?>
                        </select>
                    </div>
                </div>

                <div class="gspwp-options-group gspwp-dynamic-post-data">
                    <div class="gspwp-form-field gspwp-number-cover gspwp-number-style">
                        <label for="set_post_limit"> <?php esc_html_e('Set Post Image Limit', 'gallery-showcase-pro') ?>
                            <div class="tooltip"> <i class="fa fa-info" aria-hidden="true"></i>
                                <span class="tooltiptext"><?php esc_html_e('If you set -1 / 0 it will take all Images.','gallery-showcase-pro'); ?></span>
                            </div>
                        </label>
                        <input type="number" class="gspwp-number" id="set_post_limit" name="options[set_post_limit]" min="-1" placeholder="<?php esc_attr_e('-1', 'gallery-showcase-pro'); ?>" value="<?php echo ( isset($gspwp_options['set_post_limit']) && $gspwp_options['set_post_limit'] != '') ? esc_attr($gspwp_options['set_post_limit']) : '-1' ?>">
                    </div>
                </div>

                <div class="gspwp-options-group gspwp-dynamic-post-data">
                    <div class="gspwp-form-field gspwp-buttonset-cover">
                        <label> <?php esc_html_e('Want Categories Filter', 'gallery-showcase-pro'); ?> </label>
                        <fieldset class="gspwp-buttonset">
                            <?php $want_category_filter = isset($gspwp_options['want_category_filter']) ? $gspwp_options['want_category_filter'] : 1; ?>
                            <input id="want_category_filter_0" name="options[want_category_filter]" type="radio" value="0" <?php echo checked(0, $want_category_filter); ?>/>
                            <label for="want_category_filter_0"><?php esc_html_e('Yes', 'gallery-showcase-pro'); ?></label>
                            <input id="want_category_filter_1" name="options[want_category_filter]" type="radio" value="1" <?php echo checked(1, $want_category_filter); ?> />
                            <label for="want_category_filter_1"><?php esc_html_e('No', 'gallery-showcase-pro'); ?></label>
                        </fieldset>
                    </div>
                </div>
                <!-- gspwp-category-filter  gspwp-dynamic-post-data-->
                <div class="gspwp-options-group  custom-post-type-taxonomies">
                    <div class="gspwp-form-field gspwp-select-cover">
                        <label for="custom_post_type_taxonomies"><?php esc_html_e('Select Filter Category', 'gallery-showcase-pro') ?></label>
                            <select id="custom_post_type_taxonomies" name="options[custom_post_type_taxonomies]"  class="chosen-select" data-placeholder="<?php esc_attr_e('No Category Found', 'gallery-showcase-pro') ?>">
                            <?php
                                $post_type = ( isset($gspwp_options['custom_post_type'])  ) ? $gspwp_options['custom_post_type'] : "post";
                                $taxonomies_array = get_object_taxonomies( $post_type );
                                foreach ($taxonomies_array as $taxonomy) {
                                    $taxonomy_details = get_taxonomy( $taxonomy );
                                    if($taxonomy != "post_format"){ ?>
                                        <option value="<?php echo esc_attr( $taxonomy ); ?>" <?php selected( $gspwp_options['custom_post_type_taxonomies'], $taxonomy); ?> ><?php echo esc_html( $taxonomy_details->label , 'gallery-showcase-pro') ?></option>
                                        <?php
                                    }
                                } ?>
                        </select>
                    </div>
                </div>

                <div class="gspwp-options-group">
                    <div class="gspwp-form-field gspwp-select-cover">
                        <label for="layout_type"><?php esc_html_e('Layout Type', 'gallery-showcase-pro') ?></label>
                        <select id="layout_type" name="options[layout_type]"  class="chosen-select" data-placeholder="<?php esc_attr_e('Layout Type', 'gallery-showcase-pro') ?>">
                            <option value="grid" <?php echo ($gspwp_options['layout_type'] == 'grid') ? 'selected="selected"' : '' ?>><?php esc_attr_e('Grid', 'gallery-showcase-pro') ?></option>
                            <option value="masonry" <?php echo ($gspwp_options['layout_type'] == 'masonry') ? 'selected="selected"' : '' ?> ><?php esc_attr_e('Masonry', 'gallery-showcase-pro') ?></option>
                            <option value="slider" <?php echo ($gspwp_options['layout_type'] == 'slider') ? 'selected="selected"' : '' ?> ><?php esc_attr_e('Slider', 'gallery-showcase-pro') ?></option>
                        </select>
                    </div>
                </div>

                <div class="gspwp-options-group">
                    <div class="gspwp-form-field gspwp-select-cover">
                        <label for="column_desktop"> <?php esc_attr_e('Column for Desktop', 'gallery-showcase-pro'); ?> </label>
                        <select id="column_desktop" name="options[column_desktop]">
                            <option value="1" <?php echo ($gspwp_options['column_desktop'] == '1') ? 'selected="selected"' : '' ?>><?php esc_attr_e('1 Column', 'gallery-showcase-pro') ?></option>
                            <option value="2" <?php echo ($gspwp_options['column_desktop'] == '2') ? 'selected="selected"' : '' ?>><?php esc_attr_e('2 Columns', 'gallery-showcase-pro') ?></option>
                            <option value="3" <?php echo ($gspwp_options['column_desktop'] == '3') ? 'selected="selected"' : '' ?>><?php esc_attr_e('3 Columns', 'gallery-showcase-pro') ?></option>
                            <option value="4" <?php echo ($gspwp_options['column_desktop'] == '4') ? 'selected="selected"' : '' ?>><?php esc_attr_e('4 Columns', 'gallery-showcase-pro') ?></option>
                            <option value="5" <?php echo ($gspwp_options['column_desktop'] == '5') ? 'selected="selected"' : '' ?>><?php esc_attr_e('5 Columns', 'gallery-showcase-pro') ?></option>
                            <option value="6" <?php echo ($gspwp_options['column_desktop'] == '6') ? 'selected="selected"' : '' ?>><?php esc_attr_e('6 Columns', 'gallery-showcase-pro') ?></option>
                        </select>
                    </div>

                    <div class="gspwp-form-field gspwp-select-cover">
                        <label for="column_small_desktop"> <?php esc_attr_e('Column for Small Desktop', 'gallery-showcase-pro'); ?> </label>
                        <select id="column_small_desktop" name="options[column_small_desktop]">
                            <option value="1" <?php echo ($gspwp_options['column_small_desktop'] == '1') ? 'selected="selected"' : '' ?>><?php esc_attr_e('1 Column', 'gallery-showcase-pro') ?></option>
                            <option value="2" <?php echo ($gspwp_options['column_small_desktop'] == '2') ? 'selected="selected"' : '' ?>><?php esc_attr_e('2 Columns', 'gallery-showcase-pro') ?></option>
                            <option value="3" <?php echo ($gspwp_options['column_small_desktop'] == '3') ? 'selected="selected"' : '' ?>><?php esc_attr_e('3 Columns', 'gallery-showcase-pro') ?></option>
                            <option value="4" <?php echo ($gspwp_options['column_small_desktop'] == '4') ? 'selected="selected"' : '' ?>><?php esc_attr_e('4 Columns', 'gallery-showcase-pro') ?></option>
                            <option value="5" <?php echo ($gspwp_options['column_small_desktop'] == '5') ? 'selected="selected"' : '' ?>><?php esc_attr_e('5 Columns', 'gallery-showcase-pro') ?></option>
                            <option value="6" <?php echo ($gspwp_options['column_small_desktop'] == '6') ? 'selected="selected"' : '' ?>><?php esc_attr_e('6 Columns', 'gallery-showcase-pro') ?></option>
                        </select>
                    </div>

                    <div class="gspwp-form-field gspwp-select-cover">
                        <label for="column_tablet"> <?php esc_attr_e('Column for Tablet', 'gallery-showcase-pro'); ?> </label>
                        <select id="column_tablet" name="options[column_tablet]">
                            <option value="1" <?php echo ($gspwp_options['column_tablet'] == '1') ? 'selected="selected"' : '' ?>><?php esc_attr_e('1 Column', 'gallery-showcase-pro') ?></option>
                            <option value="2" <?php echo ($gspwp_options['column_tablet'] == '2') ? 'selected="selected"' : '' ?>><?php esc_attr_e('2 Columns', 'gallery-showcase-pro') ?></option>
                            <option value="3" <?php echo ($gspwp_options['column_tablet'] == '3') ? 'selected="selected"' : '' ?>><?php esc_attr_e('3 Columns', 'gallery-showcase-pro') ?></option>
                            <option value="4" <?php echo ($gspwp_options['column_tablet'] == '4') ? 'selected="selected"' : '' ?>><?php esc_attr_e('4 Columns', 'gallery-showcase-pro') ?></option>
                            <option value="5" <?php echo ($gspwp_options['column_tablet'] == '5') ? 'selected="selected"' : '' ?>><?php esc_attr_e('5 Columns', 'gallery-showcase-pro') ?></option>
                            <option value="6" <?php echo ($gspwp_options['column_tablet'] == '6') ? 'selected="selected"' : '' ?>><?php esc_attr_e('6 Columns', 'gallery-showcase-pro') ?></option>
                        </select>
                    </div>

                    <div class="gspwp-form-field gspwp-select-cover">
                        <label for="column_mobile"> <?php esc_attr_e('Column for Mobile', 'gallery-showcase-pro'); ?> </label>
                        <select id="column_mobile" name="options[column_mobile]">
                            <option value="1" <?php echo ($gspwp_options['column_mobile'] == '1') ? 'selected="selected"' : '' ?>><?php esc_attr_e('1 Column', 'gallery-showcase-pro') ?></option>
                            <option value="2" <?php echo ($gspwp_options['column_mobile'] == '2') ? 'selected="selected"' : '' ?>><?php esc_attr_e('2 Columns', 'gallery-showcase-pro') ?></option>
                            <option value="3" <?php echo ($gspwp_options['column_mobile'] == '3') ? 'selected="selected"' : '' ?>><?php esc_attr_e('3 Columns', 'gallery-showcase-pro') ?></option>
                            <option value="4" <?php echo ($gspwp_options['column_mobile'] == '4') ? 'selected="selected"' : '' ?>><?php esc_attr_e('4 Columns', 'gallery-showcase-pro') ?></option>
                            <option value="5" <?php echo ($gspwp_options['column_mobile'] == '5') ? 'selected="selected"' : '' ?>><?php esc_attr_e('5 Columns', 'gallery-showcase-pro') ?></option>
                            <option value="6" <?php echo ($gspwp_options['column_mobile'] == '6') ? 'selected="selected"' : '' ?>><?php esc_attr_e('6 Columns', 'gallery-showcase-pro') ?></option>
                        </select>
                    </div>
                </div>

                <div class="gspwp-options-group">
                    <div class="gspwp-form-field gspwp-number-cover">
                        <label for="padding_top"> <?php esc_attr_e('Padding', 'gallery-showcase-pro') ?> </label>
                        <input type="number" class="gspwp-four-number" id="padding_top" name="options[padding_top]" min="0" placeholder="<?php esc_attr_e('Top', 'gallery-showcase-pro'); ?>" value="<?php echo ($gspwp_options['padding_top'] != '') ? esc_attr($gspwp_options['padding_top']) : '0' ?>">
                        <input type="number" class="gspwp-four-number" id="padding_right" name="options[padding_right]" min="0" placeholder="<?php esc_attr_e('Right', 'gallery-showcase-pro'); ?>" value="<?php echo ($gspwp_options['padding_right'] != '') ? esc_attr($gspwp_options['padding_right']) : '0' ?>">
                        <input type="number" class="gspwp-four-number" id="padding_bottom" name="options[padding_bottom]" min="0" placeholder="<?php esc_attr_e('Bottom', 'gallery-showcase-pro'); ?>" value="<?php echo ($gspwp_options['padding_bottom'] != '') ? esc_attr($gspwp_options['padding_bottom']) : '0' ?>">
                        <input type="number" class="gspwp-four-number" id="padding_left" name="options[padding_left]" min="0" placeholder="<?php esc_attr_e('Left', 'gallery-showcase-pro'); ?>" value="<?php echo ($gspwp_options['padding_left'] != '') ? esc_attr($gspwp_options['padding_left']) : '0' ?>">
                    </div>
                </div>

                <div class="gspwp-options-group">
                    <div class="gspwp-form-field gspwp-testarea-cover">
                        <label for="custom_css"> <?php esc_attr_e('Custom CSS', 'gallery-showcase-pro'); ?> </label>
                        <textarea id="custom_css" name="options[custom_css]" placeholder="<?php esc_attr_e('Write your Custom CSS here', 'gallery-showcase-pro'); ?>"><?php echo esc_textarea($gspwp_options['custom_css']); ?></textarea>
                    </div>
                </div>
            </div>

            <div id="thumbnail-setting-data" class="panel gspwp-options-panel hidden">
                <div class="gspwp-options-group">
                    <?php $thumb_sizes = array(); ?>
                    <div class="gspwp-form-field gspwp-select-cover">
                        <label for="image_size"> <?php esc_html_e('Select Image Size', 'gallery-showcase-pro'); ?> </label>
                        <select id="image_size" name="options[image_size]">
                            <option value="full" <?php echo (isset($gspwp_options['image_size']) && $gspwp_options['image_size'] == 'full') ? 'selected="selected"' : ''; ?>><?php esc_html_e('Original Resolution', 'gallery-showcase-pro'); ?></option>
                            <?php
                            foreach (get_intermediate_image_sizes() as $s) {
                                $thumb_sizes [$s] = array(0, 0);
                                if (in_array($s, array('thumbnail', 'medium', 'large'))) {
                                    ?> <option value="<?php echo esc_attr($s); ?>"  <?php echo (isset($gspwp_options['image_size']) && $gspwp_options['image_size'] == $s) ? 'selected="selected"' : ''; ?>> <?php echo esc_attr($s) . ' (' . esc_attr(get_option($s . '_size_w')) . 'x' . esc_attr(get_option($s . '_size_h')) . ')'; ?> </option> <?php
                                }
                            }
                            ?>
                            <option value="custom" <?php echo (isset($gspwp_options['image_size']) && $gspwp_options['image_size'] == 'custom') ? 'selected="selected"' : '' ?>><?php esc_html_e('Custom Size', 'gallery-showcase-pro'); ?></option>
                        </select>
                    </div>
                </div>

                <div class="gspwp-options-group gspwp-image-custom-size">
                    <div class="gspwp-form-field gspwp-number-cover">
                        <label for="image_width"> <?php esc_html_e('Add Image Size', 'gallery-showcase-pro'); ?> </label>
                        <input type="number" class="gspwp-two-number" id="image_width" name="options[image_width]" min="50" placeholder="<?php esc_attr_e('Width', 'gallery-showcase-pro'); ?>" value="<?php echo ($gspwp_options['image_width'] != '') ? esc_attr($gspwp_options['image_width']) : '500' ?>">
                        <input type="number" class="gspwp-two-number" id="image_height" name="options[image_height]" min="50" placeholder="<?php esc_attr_e('Height', 'gallery-showcase-pro'); ?>" value="<?php echo ($gspwp_options['image_height'] != '') ? esc_attr($gspwp_options['image_height']) : '500' ?>">
                    </div>
                </div>

                <div class="gspwp-options-group gspwp-image-custom-size">
                    <div class="gspwp-form-field gspwp-checkbox-cover">
                        <label for="gspwp-image-hard-crop"><?php esc_html_e('Image Hard Corp', 'gallery-showcase-pro') ?></label>
                        <input id="gspwp-image-hard-crop" name="options[image-hard-crop]" value="1" type="checkbox" <?php echo (isset($gspwp_options['image-hard-crop']) && $gspwp_options['image-hard-crop'] == 1) ? 'checked="checked"' : ''; ?>>
                    </div>
                </div>

                <div class="gspwp-options-group">
                    <div class="gspwp-form-field gspwp-select-cover">
                        <label for="layout_effect"> <?php esc_html_e('Layout Effects', 'gallery-showcase-pro'); ?> </label>
                        <select id="layout_effect" name="options[layout_effect]">
                            <optgroup class="inspirational" label="<?php esc_html_e('Inspirational', 'gallery-showcase-pro'); ?>">
                                <option value="apollo" <?php echo ($gspwp_options['layout_effect'] == 'apollo') ? 'selected="selected"' : '' ?>><?php esc_html_e('Apollo', 'gallery-showcase-pro') ?></option>
                                <option value="bubba" <?php echo ($gspwp_options['layout_effect'] == 'bubba') ? 'selected="selected"' : '' ?>><?php esc_html_e('Bubba', 'gallery-showcase-pro') ?></option>
                                <option value="chico" <?php echo ($gspwp_options['layout_effect'] == 'chico') ? 'selected="selected"' : '' ?>><?php esc_html_e('Chico', 'gallery-showcase-pro') ?></option>
                                <option value="dexter" <?php echo ($gspwp_options['layout_effect'] == 'dexter') ? 'selected="selected"' : '' ?>><?php esc_html_e('Dexter', 'gallery-showcase-pro') ?></option>
                                <option value="duke" <?php echo ($gspwp_options['layout_effect'] == 'duke') ? 'selected="selected"' : '' ?>><?php esc_html_e('Duke', 'gallery-showcase-pro') ?></option>
                                <option value="jazz" <?php echo ($gspwp_options['layout_effect'] == 'jazz') ? 'selected="selected"' : '' ?>><?php esc_html_e('Jazz', 'gallery-showcase-pro') ?></option>
                                <option value="julia" <?php echo ($gspwp_options['layout_effect'] == 'julia') ? 'selected="selected"' : '' ?>><?php esc_html_e('Julia', 'gallery-showcase-pro') ?></option>
                                <option value="goliath" <?php echo ($gspwp_options['layout_effect'] == 'goliath') ? 'selected="selected"' : '' ?>><?php esc_html_e('Goliath', 'gallery-showcase-pro') ?></option>
                                <option value="layla" <?php echo ($gspwp_options['layout_effect'] == 'layla') ? 'selected="selected"' : '' ?>><?php esc_html_e('Layla', 'gallery-showcase-pro') ?></option>
                                <option value="lily" <?php echo ($gspwp_options['layout_effect'] == 'lily') ? 'selected="selected"' : '' ?>><?php esc_html_e('Lily', 'gallery-showcase-pro') ?></option>
                                <option value="marley" <?php echo ($gspwp_options['layout_effect'] == 'marley') ? 'selected="selected"' : '' ?>><?php esc_html_e('Marley', 'gallery-showcase-pro') ?></option>
                                <option value="milo" <?php echo ($gspwp_options['layout_effect'] == 'milo') ? 'selected="selected"' : '' ?>><?php esc_html_e('Milo', 'gallery-showcase-pro') ?></option>
                                <option value="ming" <?php echo ($gspwp_options['layout_effect'] == 'ming') ? 'selected="selected"' : '' ?>><?php esc_html_e('Ming', 'gallery-showcase-pro') ?></option>
                                <option value="moses" <?php echo ($gspwp_options['layout_effect'] == 'moses') ? 'selected="selected"' : '' ?>><?php esc_html_e('Moses', 'gallery-showcase-pro') ?></option>
                                <option value="oscar" <?php echo ($gspwp_options['layout_effect'] == 'oscar') ? 'selected="selected"' : '' ?>><?php esc_html_e('Oscar', 'gallery-showcase-pro') ?></option>
                                <option value="ruby" <?php echo ($gspwp_options['layout_effect'] == 'ruby') ? 'selected="selected"' : '' ?>><?php esc_html_e('Ruby', 'gallery-showcase-pro') ?></option>
                                <option value="romeo" <?php echo ($gspwp_options['layout_effect'] == 'romeo') ? 'selected="selected"' : '' ?>><?php esc_html_e('Romeo', 'gallery-showcase-pro') ?></option>
                                <option value="roxy" <?php echo ($gspwp_options['layout_effect'] == 'roxy') ? 'selected="selected"' : '' ?>><?php esc_html_e('Roxy', 'gallery-showcase-pro') ?></option>
                                <option value="sadie" <?php echo ($gspwp_options['layout_effect'] == 'sadie') ? 'selected="selected"' : '' ?>><?php esc_html_e('Sadie', 'gallery-showcase-pro') ?></option>
                                <option value="sarah" <?php echo ($gspwp_options['layout_effect'] == 'sarah') ? 'selected="selected"' : '' ?>><?php esc_html_e('Sarah', 'gallery-showcase-pro') ?></option>
                                <option value="selena" <?php echo ($gspwp_options['layout_effect'] == 'selena') ? 'selected="selected"' : '' ?>><?php esc_html_e('Selena', 'gallery-showcase-pro') ?></option>
                                <option value="steve" <?php echo ($gspwp_options['layout_effect'] == 'steve') ? 'selected="selected"' : '' ?>><?php esc_html_e('Steve', 'gallery-showcase-pro') ?></option>
                                <option value="zoe" <?php echo ($gspwp_options['layout_effect'] == 'zoe') ? 'selected="selected"' : '' ?>><?php esc_html_e('Zoe', 'gallery-showcase-pro') ?></option>
                            </optgroup>
                        </select>
                        <input type="text" id="layout_effect_group" class="hidden" value="<?php echo ( $gspwp_options['layout_effect_group'] != '') ? esc_attr($gspwp_options['layout_effect_group']) : '' ?>" name="options[layout_effect_group]" />
                    </div>
                </div>

                <div class="gspwp-options-group">
                    <div class="gspwp-form-field gspwp-select-cover">
                        <label for="layout_animation"> <?php esc_html_e('Animation', 'gallery-showcase-pro'); ?> </label>
                        <select id="layout_animation" name="options[layout_animation]">
                            <option value="" <?php echo ($gspwp_options['layout_animation'] == '') ? 'selected="selected"' : '' ?>><?php esc_html_e('None', 'gallery-showcase-pro') ?></option>
                            <option value="bounce" <?php echo ($gspwp_options['layout_animation'] == 'bounce') ? 'selected="selected"' : '' ?>><?php esc_html_e('Bounce', 'gallery-showcase-pro') ?></option>
                            <option value="bounceIn" <?php echo ($gspwp_options['layout_animation'] == 'bounceIn') ? 'selected="selected"' : '' ?>><?php esc_html_e('Bounce In', 'gallery-showcase-pro') ?></option>
                            <option value="bounceInDown" <?php echo ($gspwp_options['layout_animation'] == 'bounceInDown') ? 'selected="selected"' : '' ?>><?php esc_html_e('Bounce In Down', 'gallery-showcase-pro') ?></option>
                            <option value="bounceInLeft" <?php echo ($gspwp_options['layout_animation'] == 'bounceInLeft') ? 'selected="selected"' : '' ?>><?php esc_html_e('Bounce In Left', 'gallery-showcase-pro') ?></option>
                            <option value="bounceInRight" <?php echo ($gspwp_options['layout_animation'] == 'bounceInRight') ? 'selected="selected"' : '' ?>><?php esc_html_e('Bounce In Right', 'gallery-showcase-pro') ?></option>
                            <option value="bounceInUp" <?php echo ($gspwp_options['layout_animation'] == 'bounceInUp') ? 'selected="selected"' : '' ?>><?php esc_html_e('Bounce In Up', 'gallery-showcase-pro') ?></option>
                            <option value="jackInTheBox" <?php echo ($gspwp_options['layout_animation'] == 'jackInTheBox') ? 'selected="selected"' : '' ?>><?php esc_html_e('Jack In The Box', 'gallery-showcase-pro') ?></option>
                            <option value="jello" <?php echo ($gspwp_options['layout_animation'] == 'jello') ? 'selected="selected"' : '' ?>><?php esc_html_e('Jello', 'gallery-showcase-pro') ?></option>
                            <option value="flash" <?php echo ($gspwp_options['layout_animation'] == 'flash') ? 'selected="selected"' : '' ?>><?php esc_html_e('Flash', 'gallery-showcase-pro') ?></option>
                            <option value="fadeIn" <?php echo ($gspwp_options['layout_animation'] == 'fadeIn') ? 'selected="selected"' : '' ?>><?php esc_html_e('Fade In', 'gallery-showcase-pro') ?></option>
                            <option value="fadeInDown" <?php echo ($gspwp_options['layout_animation'] == 'fadeInDown') ? 'selected="selected"' : '' ?>><?php esc_html_e('Fade In Down', 'gallery-showcase-pro') ?></option>
                            <option value="fadeInDownBig" <?php echo ($gspwp_options['layout_animation'] == 'fadeInDownBig') ? 'selected="selected"' : '' ?>><?php esc_html_e('Fade In Down Big', 'gallery-showcase-pro') ?></option>
                            <option value="fadeInLeft" <?php echo ($gspwp_options['layout_animation'] == 'fadeInLeft') ? 'selected="selected"' : '' ?>><?php esc_html_e('Fade In Left', 'gallery-showcase-pro') ?></option>
                            <option value="fadeInLeftBig" <?php echo ($gspwp_options['layout_animation'] == 'fadeInLeftBig') ? 'selected="selected"' : '' ?>><?php esc_html_e('Fade In Left Big', 'gallery-showcase-pro') ?></option>
                            <option value="fadeInRight" <?php echo ($gspwp_options['layout_animation'] == 'fadeInRight') ? 'selected="selected"' : '' ?>><?php esc_html_e('Fade In Right', 'gallery-showcase-pro') ?></option>
                            <option value="fadeInRightBig" <?php echo ($gspwp_options['layout_animation'] == 'fadeInRightBig') ? 'selected="selected"' : '' ?>><?php esc_html_e('Fade In Right Big', 'gallery-showcase-pro') ?></option>
                            <option value="fadeInUp" <?php echo ($gspwp_options['layout_animation'] == 'fadeInUp') ? 'selected="selected"' : '' ?>><?php esc_html_e('Fade In Up', 'gallery-showcase-pro') ?></option>
                            <option value="fadeInUpBig" <?php echo ($gspwp_options['layout_animation'] == 'fadeInUpBig') ? 'selected="selected"' : '' ?>><?php esc_html_e('Fade In Up Big', 'gallery-showcase-pro') ?></option>
                            <option value="flipInX" <?php echo ($gspwp_options['layout_animation'] == 'flipInX') ? 'selected="selected"' : '' ?>><?php esc_html_e('Flip In X', 'gallery-showcase-pro') ?></option>
                            <option value="flipInY" <?php echo ($gspwp_options['layout_animation'] == 'flipInY') ? 'selected="selected"' : '' ?>><?php esc_html_e('Flip In Y', 'gallery-showcase-pro') ?></option>
                            <option value="lightSpeedIn" <?php echo ($gspwp_options['layout_animation'] == 'lightSpeedIn') ? 'selected="selected"' : '' ?>><?php esc_html_e('Light Speed In', 'gallery-showcase-pro') ?></option>
                            <option value="pulse" <?php echo ($gspwp_options['layout_animation'] == 'pulse') ? 'selected="selected"' : '' ?>><?php esc_html_e('Pulse', 'gallery-showcase-pro') ?></option>
                            <option value="rollIn" <?php echo ($gspwp_options['layout_animation'] == 'rollIn') ? 'selected="selected"' : '' ?>><?php esc_html_e('Roll In', 'gallery-showcase-pro') ?></option>
                            <option value="rotateIn" <?php echo ($gspwp_options['layout_animation'] == 'rotateIn') ? 'selected="selected"' : '' ?>><?php esc_html_e('Rotate In', 'gallery-showcase-pro') ?></option>
                            <option value="rotateInDownLeft" <?php echo ($gspwp_options['layout_animation'] == 'rotateInDownLeft') ? 'selected="selected"' : '' ?>><?php esc_html_e('Rotate In Down Left', 'gallery-showcase-pro') ?></option>
                            <option value="rotateInDownRight" <?php echo ($gspwp_options['layout_animation'] == 'rotateInDownRight') ? 'selected="selected"' : '' ?>><?php esc_html_e('Rotate In Down Right', 'gallery-showcase-pro') ?></option>
                            <option value="rotateInUpLeft" <?php echo ($gspwp_options['layout_animation'] == 'rotateInUpLeft') ? 'selected="selected"' : '' ?>><?php esc_html_e('Rotate In Up Left', 'gallery-showcase-pro') ?></option>
                            <option value="rotateInUpRight" <?php echo ($gspwp_options['layout_animation'] == 'rotateInUpRight') ? 'selected="selected"' : '' ?>><?php esc_html_e('Rotate In Up Right', 'gallery-showcase-pro') ?></option>
                            <option value="rubberBand" <?php echo ($gspwp_options['layout_animation'] == 'rubberBand') ? 'selected="selected"' : '' ?>><?php esc_html_e('Rubber Band', 'gallery-showcase-pro') ?></option>
                            <option value="shake" <?php echo ($gspwp_options['layout_animation'] == 'shake') ? 'selected="selected"' : '' ?>><?php esc_html_e('Shake', 'gallery-showcase-pro') ?></option>
                            <option value="headShake" <?php echo ($gspwp_options['layout_animation'] == 'headShake') ? 'selected="selected"' : '' ?>><?php esc_html_e('Head Shake', 'gallery-showcase-pro') ?></option>
                            <option value="slideInDown" <?php echo ($gspwp_options['layout_animation'] == 'slideInDown') ? 'selected="selected"' : '' ?>><?php esc_html_e('Slide In Down', 'gallery-showcase-pro') ?></option>
                            <option value="slideInLeft" <?php echo ($gspwp_options['layout_animation'] == 'slideInLeft') ? 'selected="selected"' : '' ?>><?php esc_html_e('Slide In Left', 'gallery-showcase-pro') ?></option>
                            <option value="slideInRight" <?php echo ($gspwp_options['layout_animation'] == 'slideInRight') ? 'selected="selected"' : '' ?>><?php esc_html_e('Slide In Right', 'gallery-showcase-pro') ?></option>
                            <option value="slideInUp" <?php echo ($gspwp_options['layout_animation'] == 'slideInUp') ? 'selected="selected"' : '' ?>><?php esc_html_e('Slide In Up', 'gallery-showcase-pro') ?></option>
                            <option value="swing" <?php echo ($gspwp_options['layout_animation'] == 'swing') ? 'selected="selected"' : '' ?>><?php esc_html_e('Swing', 'gallery-showcase-pro') ?></option>
                            <option value="tada" <?php echo ($gspwp_options['layout_animation'] == 'tada') ? 'selected="selected"' : '' ?>><?php esc_html_e('Tada', 'gallery-showcase-pro') ?></option>
                            <option value="wobble" <?php echo ($gspwp_options['layout_animation'] == 'wobble') ? 'selected="selected"' : '' ?>><?php esc_html_e('Wobble', 'gallery-showcase-pro') ?></option>
                            <option value="zoomIn" <?php echo ($gspwp_options['layout_animation'] == 'zoomIn') ? 'selected="selected"' : '' ?>><?php esc_html_e('Zoom In', 'gallery-showcase-pro') ?></option>
                            <option value="zoomInDown" <?php echo ($gspwp_options['layout_animation'] == 'zoomInDown') ? 'selected="selected"' : '' ?>><?php esc_html_e('Zoom In Down', 'gallery-showcase-pro') ?></option>
                            <option value="zoomInLeft" <?php echo ($gspwp_options['layout_animation'] == 'zoomInLeft') ? 'selected="selected"' : '' ?>><?php esc_html_e('Zoom In Left', 'gallery-showcase-pro') ?></option>
                            <option value="zoomInRight" <?php echo ($gspwp_options['layout_animation'] == 'zoomInRight') ? 'selected="selected"' : '' ?>><?php esc_html_e('Zoom In Right', 'gallery-showcase-pro') ?></option>
                            <option value="zoomInUp" <?php echo ($gspwp_options['layout_animation'] == 'zoomInUp') ? 'selected="selected"' : '' ?>><?php esc_html_e('Zoom In Up', 'gallery-showcase-pro') ?></option>
                        </select>
                    </div>
                </div>

                <div class="gspwp-options-group gspwp-layout-bg-color-div">
                    <div class="gspwp-form-field gspwp-color-cover">
                        <label for="gspwp-layout-bg-color"><?php esc_html_e('Background Color', 'gallery-showcase-pro') ?></label>
                        <input id="gspwp-layout-bg-color" name="options[gs-layout-bg-color]" class="gspwp-color-picker" data-alpha="true" placeholder="<?php esc_attr_e('Color', 'gallery-showcase-pro'); ?>" value="<?php echo esc_attr($gspwp_options['gs-layout-bg-color']); ?>" type="text" />
                    </div>
                </div>
                <div class="gspwp-options-group gspwp-layout-border-color-div">
                    <div class="gspwp-form-field gspwp-color-cover">
                        <label for="gspwp-layout-border-color"><?php esc_html_e('Border Color', 'gallery-showcase-pro') ?></label>
                        <input id="gspwp-layout-border-color" name="options[gs-layout-border-color]" class="gspwp-color-picker" data-alpha="true" placeholder="<?php esc_attr_e('Color', 'gallery-showcase-pro'); ?>" value="<?php echo esc_attr($gspwp_options['gs-layout-border-color']); ?>" type="text" />
                    </div>
                </div>

            </div>

            <div id="slider-setting-data" class="panel gspwp-options-panel hidden">
                <ul class="gspwp-slider-menu">
                    <li class="gspwp-option-tab gspwp-slider-menu-item active" data-id="gspwp-slider-arrow"><?php esc_html_e('Arrow', 'gallery-showcase-pro') ?></li>
                    <li class="gspwp-option-tab gspwp-slider-menu-item" data-id="gspwp-slider-navigation"><?php esc_html_e('Navigation', 'gallery-showcase-pro') ?></li>
                </ul>

                <div id="gspwp-slider-arrow" class="gspwp-slider-arrow gspwp-slider-options">
                    <div class="gspwp-options-group">
                        <div class="gspwp-form-field gspwp-buttonset-cover">
                            <label><?php esc_html_e('Display Arrow', 'gallery-showcase-pro') ?></label>
                            <fieldset class="gspwp-buttonset">
                                <?php $display_arrow = $gspwp_options['display_arrow']; ?>
                                <input id="display_arrow_0" name="options[display_arrow]" type="radio" value="0" <?php echo checked(0, $display_arrow); ?> />
                                <label for="display_arrow_0"><?php esc_html_e('Yes', 'gallery-showcase-pro'); ?></label>
                                <input id="display_arrow_1" name="options[display_arrow]" type="radio" value="1" <?php echo checked(1, $display_arrow); ?>/>
                                <label for="display_arrow_1"><?php esc_html_e('No', 'gallery-showcase-pro'); ?></label>
                            </fieldset>
                        </div>
                    </div>
                    <div class="gspwp-options-group gspwp-arrow-options">
                        <div class="gspwp-form-field gspwp-button-cover">
                            <label><?php esc_html_e('Arrows Style', 'gallery-showcase-pro') ?></label>
                            <a class="gspwp-btn gspwp-arrow-style" href="javascript:void(0);"><?php esc_html_e('Chose Arrow Style', 'gallery-showcase-pro'); ?></a>
                            <?php $gspwp_arrow_style = $gspwp_options['gs_arrow_style']; ?>
                            <input type="text" hidden="hidden" class="hidden" id="gspwp-arrow-style" name="options[gs_arrow_style]" value="<?php echo esc_attr($gspwp_arrow_style); ?>" />
                            <div class="gspwp-arrow-cover">
                                <div class="gspwp-arrow gspwp-left-arrow gspwp-arrow-<?php echo esc_attr($gspwp_arrow_style); ?>"></div>
                                <div class="gspwp-arrow gspwp-right-arrow gspwp-arrow-<?php echo esc_attr($gspwp_arrow_style); ?>"></div>
                            </div>
                        </div>
                    </div>
                    <div class="gspwp-options-group gspwp-arrow-options">
                        <div class="gspwp-form-field gspwp-number-cover">
                            <label for="gspwp-arrow-size"> <?php esc_html_e('Arrow Size', 'gallery-showcase-pro'); ?> </label>
                            <input type="number" class="gspwp-one-number" id="gspwp-arrow-size" name="options[gs_arrow_size]" placeholder="<?php esc_attr_e('Arrow Size', 'gallery-showcase-pro'); ?>" value="<?php echo ($gspwp_options['gs_arrow_size'] != '') ? esc_attr($gspwp_options['gs_arrow_size']) : '40' ?>">
                        </div>
                    </div>
                    <div class="gspwp-options-group gspwp-arrow-options gspwp-padding-10">
                        <p class="gspwp-field-title"><?php esc_html_e('Left Arrow', 'gallery-showcase-pro') ?></p>
                        <div class="gspwp-form-field gspwp-buttonset-cover">
                            <label><?php esc_html_e('Horizontal Position', 'gallery-showcase-pro') ?></label>
                            <fieldset class="gspwp-buttonset">
                                <?php $left_arrow_hori_pos = $gspwp_options['left_arrow_hori_pos']; ?>
                                <input id="left_arrow_hori_pos_0" name="options[left_arrow_hori_pos]" type="radio" value="0" <?php echo checked(0, $left_arrow_hori_pos); ?> />
                                <label for="left_arrow_hori_pos_0"><?php esc_html_e('Left', 'gallery-showcase-pro'); ?></label>
                                <input id="left_arrow_hori_pos_1" name="options[left_arrow_hori_pos]" type="radio" value="1" <?php echo checked(1, $left_arrow_hori_pos); ?>/>
                                <label for="left_arrow_hori_pos_1"><?php esc_html_e('Center', 'gallery-showcase-pro'); ?></label>
                                <input id="left_arrow_hori_pos_2" name="options[left_arrow_hori_pos]" type="radio" value="2" <?php echo checked(2, $left_arrow_hori_pos); ?>/>
                                <label for="left_arrow_hori_pos_2"><?php esc_html_e('Right', 'gallery-showcase-pro'); ?></label>
                            </fieldset>
                        </div>
                        <div class="gspwp-form-field gspwp-buttonset-cover">
                            <label><?php esc_html_e('Vertical Position', 'gallery-showcase-pro') ?></label>
                            <fieldset class="gspwp-buttonset">
                                <?php $left_arrow_vert_pos = $gspwp_options['left_arrow_vert_pos']; ?>
                                <input id="left_arrow_vert_pos_0" name="options[left_arrow_vert_pos]" type="radio" value="0" <?php echo checked(0, $left_arrow_vert_pos); ?> />
                                <label for="left_arrow_vert_pos_0"><?php esc_html_e('Top', 'gallery-showcase-pro'); ?></label>
                                <input id="left_arrow_vert_pos_1" name="options[left_arrow_vert_pos]" type="radio" value="1" <?php echo checked(1, $left_arrow_vert_pos); ?>/>
                                <label for="left_arrow_vert_pos_1"><?php esc_html_e('Center', 'gallery-showcase-pro'); ?></label>
                                <input id="left_arrow_vert_pos_2" name="options[left_arrow_vert_pos]" type="radio" value="2" <?php echo checked(2, $left_arrow_vert_pos); ?>/>
                                <label for="left_arrow_vert_pos_2"><?php esc_html_e('Bottom', 'gallery-showcase-pro'); ?></label>
                            </fieldset>
                        </div>
                        <div class="gspwp-form-field gspwp-number-cover">
                            <label for="left_arrow_hori_off"> <?php esc_html_e('Horizontal Offset', 'gallery-showcase-pro'); ?> </label>
                            <input type="number" class="gspwp-one-number" id="left_arrow_hori_off" name="options[left_arrow_hori_off]" placeholder="<?php esc_attr_e('Horizontal Offset', 'gallery-showcase-pro'); ?>" value="<?php echo esc_attr($gspwp_options['left_arrow_hori_off']); ?>">
                        </div>
                        <div class="gspwp-form-field gspwp-number-cover">
                            <label for="left_arrow_vert_off"> <?php esc_html_e('Vertical Offset', 'gallery-showcase-pro'); ?> </label>
                            <input type="number" class="gspwp-one-number" id="left_arrow_vert_off" name="options[left_arrow_vert_off]" placeholder="<?php esc_attr_e('Vertical Offset', 'gallery-showcase-pro'); ?>" value="<?php echo esc_attr($gspwp_options['left_arrow_vert_off']); ?>">
                        </div>
                    </div>
                    <div class="gspwp-options-group gspwp-arrow-options gspwp-padding-10">
                        <p class="gspwp-field-title"><?php esc_html_e('Right Arrow', 'gallery-showcase-pro') ?></p>
                        <div class="gspwp-form-field gspwp-buttonset-cover">
                            <label><?php esc_html_e('Horizontal Position', 'gallery-showcase-pro') ?></label>
                            <fieldset class="gspwp-buttonset">
                                <?php $right_arrow_hori_pos = isset($gspwp_options['right_arrow_hori_pos']) ? $gspwp_options['right_arrow_hori_pos'] : 2; ?>
                                <input id="right_arrow_hori_pos_0" name="options[right_arrow_hori_pos]" type="radio" value="0" <?php echo checked(0, $right_arrow_hori_pos); ?> />
                                <label for="right_arrow_hori_pos_0"><?php esc_html_e('Left', 'gallery-showcase-pro'); ?></label>
                                <input id="right_arrow_hori_pos_1" name="options[right_arrow_hori_pos]" type="radio" value="1" <?php echo checked(1, $right_arrow_hori_pos); ?>/>
                                <label for="right_arrow_hori_pos_1"><?php esc_html_e('Center', 'gallery-showcase-pro'); ?></label>
                                <input id="right_arrow_hori_pos_2" name="options[right_arrow_hori_pos]" type="radio" value="2" <?php echo checked(2, $right_arrow_hori_pos); ?>/>
                                <label for="right_arrow_hori_pos_2"><?php esc_html_e('Right', 'gallery-showcase-pro'); ?></label>
                            </fieldset>
                        </div>
                        <div class="gspwp-form-field gspwp-buttonset-cover">
                            <label><?php esc_html_e('Vertical Position', 'gallery-showcase-pro') ?></label>
                            <fieldset class="gspwp-buttonset">
                                <?php $right_arrow_vert_pos = isset($gspwp_options['right_arrow_vert_pos']) ? $gspwp_options['right_arrow_vert_pos'] : 1; ?>
                                <input id="right_arrow_vert_pos_0" name="options[right_arrow_vert_pos]" type="radio" value="0" <?php echo checked(0, $right_arrow_vert_pos); ?> />
                                <label for="right_arrow_vert_pos_0"><?php esc_html_e('Top', 'gallery-showcase-pro'); ?></label>
                                <input id="right_arrow_vert_pos_1" name="options[right_arrow_vert_pos]" type="radio" value="1" <?php echo checked(1, $right_arrow_vert_pos); ?>/>
                                <label for="right_arrow_vert_pos_1"><?php esc_html_e('Center', 'gallery-showcase-pro'); ?></label>
                                <input id="right_arrow_vert_pos_2" name="options[right_arrow_vert_pos]" type="radio" value="2" <?php echo checked(2, $right_arrow_vert_pos); ?>/>
                                <label for="right_arrow_vert_pos_2"><?php esc_html_e('Bottom', 'gallery-showcase-pro'); ?></label>
                            </fieldset>
                        </div>
                        <div class="gspwp-form-field gspwp-number-cover">
                            <label for="right_arrow_hori_off"> <?php esc_html_e('Horizontal Offset', 'gallery-showcase-pro'); ?> </label>
                            <input type="number" class="gspwp-one-number" id="right_arrow_hori_off" name="options[right_arrow_hori_off]" placeholder="<?php esc_html_e('Horizontal Offset', 'gallery-showcase-pro'); ?>" value="<?php echo (isset($gspwp_options['right_arrow_hori_off']) && $gspwp_options['right_arrow_hori_off'] != '') ? esc_attr($gspwp_options['right_arrow_hori_off']) : '-20' ?>">
                        </div>
                        <div class="gspwp-form-field gspwp-number-cover">
                            <label for="right_arrow_vert_off"> <?php esc_html_e('Vertical Offset', 'gallery-showcase-pro'); ?> </label>
                            <input type="number" class="gspwp-one-number" id="right_arrow_vert_off" name="options[right_arrow_vert_off]" placeholder="<?php esc_html_e('Vertical Offset', 'gallery-showcase-pro'); ?>" value="<?php echo (isset($gspwp_options['left_arrow_vert_off']) && $gspwp_options['right_arrow_vert_off'] != '') ? esc_attr($gspwp_options['right_arrow_vert_off']) : '0' ?>">
                        </div>
                    </div>
                </div>

                <div id="gspwp-slider-navigation" class="gspwp-slider-navigation gspwp-slider-options">
                    <div class="gspwp-options-group">
                        <div class="gspwp-form-field gspwp-buttonset-cover">
                            <label><?php esc_html_e('Display Navigation', 'gallery-showcase-pro') ?></label>
                            <fieldset class="gspwp-buttonset">
                                <?php $display_navigation = isset($gspwp_options['display_navigation']) ? $gspwp_options['display_navigation'] : 1; ?>
                                <input id="display_navigation_0" name="options[display_navigation]" type="radio" value="0" <?php echo checked(0, $display_navigation); ?> />
                                <label for="display_navigation_0"><?php esc_html_e('Yes', 'gallery-showcase-pro'); ?></label>
                                <input id="display_navigation_1" name="options[display_navigation]" type="radio" value="1" <?php echo checked(1, $display_navigation); ?>/>
                                <label for="display_navigation_1"><?php esc_html_e('No', 'gallery-showcase-pro'); ?></label>
                            </fieldset>
                        </div>
                    </div>

                    <div class="gspwp-options-group gspwp-navigation-options">
                        <div class="gspwp-form-field gspwp-button-cover">
                            <label><?php esc_html_e('Navigation Style', 'gallery-showcase-pro') ?></label>
                            <a class="gspwp-btn gspwp-navigation-style" href="javascript:void(0);"><?php esc_html_e('Chose Navigation Style', 'gallery-showcase-pro'); ?></a>
                            <?php $gspwp_navigation_style = isset($gspwp_options['gs_navigation_style']) ? $gspwp_options['gs_navigation_style'] : 'style-1'; ?>
                            <input type="text" hidden="hidden" class="hidden" id="gspwp-navigation-style" name="options[gs_navigation_style]" value="<?php echo esc_attr($gspwp_navigation_style); ?>" />
                            <div class="gspwp-navigation-cover">
                                <div class="gspwp-navigation gspwp-navigation-<?php echo esc_attr($gspwp_navigation_style); ?>">
                                    <div class="owl-pagination">
                                        <div class="owl-page active"><span></span></div>
                                        <div class="owl-page"><span></span></div>
                                        <div class="owl-page"><span></span></div>
                                        <div class="owl-page"><span></span></div>
                                        <div class="owl-page"><span></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="gspwp-options-group gspwp-navigation-options">
                        <div class="gspwp-form-field gspwp-number-cover">
                            <label for="gspwp-navigation-size"> <?php esc_html_e('Navigation Size', 'gallery-showcase-pro'); ?> </label>
                            <input type="number" class="gspwp-one-number" id="gspwp-navigation-size" name="options[gs_navigation_size]" placeholder="<?php esc_attr_e('Navigation Size', 'gallery-showcase-pro'); ?>" value="<?php echo (isset($gspwp_options['gsp_navigation_size']) && $gspwp_options['gs_navigation_size'] != '') ? esc_attr($gspwp_options['gs_navigation_size']) : '16' ?>">
                        </div>
                    </div>
                    <div class="gspwp-options-group gspwp-navigation-options">
                        <div class="gspwp-form-field gspwp-buttonset-cover">
                            <label><?php esc_html_e('Horizontal Position', 'gallery-showcase-pro') ?></label>
                            <fieldset class="gspwp-buttonset">
                                <?php $nav_hori_pos = isset($gspwp_options['nav_hori_pos']) ? $gspwp_options['nav_hori_pos'] : 1; ?>
                                <input id="nav_hori_pos_0" name="options[nav_hori_pos]" type="radio" value="0" <?php echo checked(0, $nav_hori_pos); ?> />
                                <label for="nav_hori_pos_0"><?php esc_html_e('Left', 'gallery-showcase-pro'); ?></label>
                                <input id="nav_hori_pos_1" name="options[nav_hori_pos]" type="radio" value="1" <?php echo checked(1, $nav_hori_pos); ?>/>
                                <label for="nav_hori_pos_1"><?php esc_html_e('Center', 'gallery-showcase-pro'); ?></label>
                                <input id="nav_hori_pos_2" name="options[nav_hori_pos]" type="radio" value="2" <?php echo checked(2, $nav_hori_pos); ?>/>
                                <label for="nav_hori_pos_2"><?php esc_html_e('Right', 'gallery-showcase-pro'); ?></label>
                            </fieldset>
                        </div>
                        <div class="gspwp-form-field gspwp-buttonset-cover">
                            <label><?php esc_html_e('Vertical Position', 'gallery-showcase-pro') ?></label>
                            <fieldset class="gspwp-buttonset">
                                <?php $nav_vert_pos = isset($gspwp_options['nav_vert_pos']) ? $gspwp_options['nav_vert_pos'] : 2; ?>
                                <input id="nav_vert_pos_0" name="options[nav_vert_pos]" type="radio" value="0" <?php echo checked(0, $nav_vert_pos); ?> />
                                <label for="nav_vert_pos_0"><?php esc_html_e('Top', 'gallery-showcase-pro'); ?></label>
                                <input id="nav_vert_pos_1" name="options[nav_vert_pos]" type="radio" value="1" <?php echo checked(1, $nav_vert_pos); ?>/>
                                <label for="nav_vert_pos_1"><?php esc_html_e('Center', 'gallery-showcase-pro'); ?></label>
                                <input id="nav_vert_pos_2" name="options[nav_vert_pos]" type="radio" value="2" <?php echo checked(2, $nav_vert_pos); ?>/>
                                <label for="nav_vert_pos_2"><?php esc_html_e('Bottom', 'gallery-showcase-pro'); ?></label>
                            </fieldset>
                        </div>
                    </div>
                    <div class="gspwp-options-group gspwp-navigation-options">
                        <div class="gspwp-form-field gspwp-number-cover">
                            <label for="nav_hori_off"> <?php esc_html_e('Horizontal Offset', 'gallery-showcase-pro'); ?> </label>
                            <input type="number" class="gspwp-one-number" id="nav_hori_off" name="options[nav_hori_off]" placeholder="<?php esc_attr_e('Horizontal Offset', 'gallery-showcase-pro'); ?>" value="<?php echo (isset($gspwp_options['nav_hori_off']) && $gspwp_options['nav_hori_off'] != '') ? esc_attr($gspwp_options['nav_hori_off']) : '0' ?>">
                        </div>
                        <div class="gspwp-form-field gspwp-number-cover">
                            <label for="nav_vert_off"> <?php esc_html_e('Vertical Offset', 'gallery-showcase-pro'); ?> </label>
                            <input type="number" class="gspwp-one-number" id="nav_vert_off" name="options[nav_vert_off]" placeholder="<?php esc_attr_e('Vertical Offset', 'gallery-showcase-pro'); ?>" value="<?php echo (isset($gspwp_options['left_arrow_vert_off']) && isset( $gspwp_options['nav_vert_off'] ) && $gspwp_options['nav_vert_off'] != '') ? esc_attr($gspwp_options['nav_vert_off']) : '-20' ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div id="title-setting-data" class="panel gspwp-options-panel hidden">
                <div class="gspwp-options-group">
                    <div class="gspwp-form-field gspwp-buttonset-cover">
                        <label> <?php esc_html_e('Display Title', 'gallery-showcase-pro'); ?> </label>
                        <fieldset class="gspwp-buttonset">
                            <?php $display_title = isset($gspwp_options['display_title']) ? $gspwp_options['display_title'] : 0; ?>
                            <input id="display_title_0" name="options[display_title]" type="radio" value="0" <?php echo checked(0, $display_title); ?>/>
                            <label for="display_title_0"><?php esc_html_e('Yes', 'gallery-showcase-pro'); ?></label>
                            <input id="display_title_1" name="options[display_title]" type="radio" value="1" <?php echo checked(1, $display_title); ?> />
                            <label for="display_title_1"><?php esc_html_e('No', 'gallery-showcase-pro'); ?></label>
                        </fieldset>
                    </div>
                </div>

                <div class="gspwp-options-group gspwp-title-options">
                    <div class="gspwp-form-field gspwp-select-cover">
                        <label for="title_font"> <?php esc_html_e('Font Family', 'gallery-showcase-pro'); ?> </label>
                        <select id="title_font" name="options[title_font]">
                            <option value="" <?php echo (isset($gspwp_options['title_font']) && $gspwp_options['title_font'] == '') ? 'selected="selected"' : '' ?>><?php esc_html_e('Default', 'gallery-showcase-pro') ?></option>
                        </select>
                        <input type="text" hidden="hidden" value="<?php echo isset($gspwp_options['title_font']) ? esc_attr($gspwp_options['title_font']) : ''; ?>" class="title_font_selected" />
                    </div>
                </div>

                <div class="gspwp-options-group gspwp-title-options">
                    <div class="gspwp-form-field gspwp-number-cover">
                        <label for="gspwp-title-font-size"> <?php esc_html_e('Font Size', 'gallery-showcase-pro'); ?> </label>
                        <input type="number" class="gspwp-one-number" id="gspwp-title-font-size" name="options[title-font-size]" placeholder="<?php esc_attr_e('Font Size', 'gallery-showcase-pro'); ?>" value="<?php echo (isset($gspwp_options['title-font-size']) && $gspwp_options['title-font-size'] != '') ? esc_attr($gspwp_options['title-font-size']) : '0' ?>">
                    </div>
                </div>

                <div class="gspwp-options-group gspwp-title-options">
                    <div class="gspwp-form-field gspwp-color-cover">
                        <label for="gspwp-title-font-color"><?php esc_html_e('Font Color', 'gallery-showcase-pro') ?></label>
                        <input id="gspwp-title-font-color" name="options[gs-title-font-color]" class="gspwp-color-picker" data-alpha="true" placeholder="<?php esc_attr_e('Color', 'gallery-showcase-pro'); ?>" value="<?php echo (isset($gspwp_options['gs-title-font-color'])) ? esc_attr($gspwp_options['gs-title-font-color']) : '#ffffff'; ?>" type="text" />
                    </div>
                </div>

                <div class="gspwp-options-group gspwp-title-bg-color-div gspwp-title-options">
                    <div class="gspwp-form-field gspwp-color-cover">
                        <label for="gspwp-title-bg-color"><?php esc_html_e('Background Color', 'gallery-showcase-pro') ?></label>
                        <input id="gspwp-title-bg-color" name="options[gs-title-bg-color]" class="gspwp-color-picker" data-alpha="true" placeholder="<?php esc_attr_e('Color', 'gallery-showcase-pro'); ?>" value="<?php echo (isset($gspwp_options['gs-title-bg-color'])) ? esc_attr($gspwp_options['gs-title-bg-color']) : '#ffffff'; ?>" type="text" />
                    </div>
                </div>

                <div class="gspwp-options-group gspwp-title-options">
                    <div class="gspwp-form-field gspwp-checkbox-cover">
                        <label for="gspwp-title-font-italic"><?php esc_html_e('Italic Style', 'gallery-showcase-pro') ?></label>
                        <input id="gspwp-title-font-italic" name="options[gs-title-font-italic]" value="1" type="checkbox" <?php echo (isset($gspwp_options['gs-title-font-italic']) && $gspwp_options['gs-title-font-italic'] == 1) ? 'checked="checked"' : ''; ?>>
                    </div>
                    <div class="gspwp-form-field gspwp-select-cover">
                        <?php $title_font_weidth = (isset($gspwp_options['gs-title-font-weight']) && $gspwp_options['gs-title-font-weight'] != '') ? $gspwp_options['gs-title-font-weight'] : 'normal'; ?>
                        <label for="gspwp-title-font-weight"><?php esc_html_e('Font Weight', 'gallery-showcase-pro') ?></label>
                        <select id="gspwp-title-font-weight" name="options[gs-title-font-weight]">
                            <option value="100" <?php echo ($title_font_weidth == '100') ? 'selected="selected"' : ''; ?> >100</option>
                            <option value="200" <?php echo ($title_font_weidth == '200') ? 'selected="selected"' : ''; ?> >200</option>
                            <option value="300" <?php echo ($title_font_weidth == '300') ? 'selected="selected"' : ''; ?> >300</option>
                            <option value="400" <?php echo ($title_font_weidth == '400') ? 'selected="selected"' : ''; ?> >400</option>
                            <option value="500" <?php echo ($title_font_weidth == '500') ? 'selected="selected"' : ''; ?> >500</option>
                            <option value="600" <?php echo ($title_font_weidth == '600') ? 'selected="selected"' : ''; ?> >600</option>
                            <option value="700" <?php echo ($title_font_weidth == '800') ? 'selected="selected"' : ''; ?> >700</option>
                            <option value="800" <?php echo ($title_font_weidth == '800') ? 'selected="selected"' : ''; ?> >800</option>
                            <option value="900" <?php echo ($title_font_weidth == '900') ? 'selected="selected"' : ''; ?> >900</option>
                            <option value="bold" <?php echo ($title_font_weidth == 'bold') ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Bold', 'gallery-showcase-pro'); ?></option>
                            <option value="normal" <?php echo ($title_font_weidth == 'normal') ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Normal', 'gallery-showcase-pro'); ?></option>
                        </select>
                    </div>
                    <div class="gspwp-form-field gspwp-select-cover">
                        <label for="gspwp-title-text-transform"><?php esc_html_e('Text Transform', 'gallery-showcase-pro') ?></label>
                        <select id="gspwp-title-text-transform" name="options[gs-title-text-transform]">
                            <option value="none" <?php echo ((isset($gspwp_options['gs-title-text-transform']) && $gspwp_options['gs-title-text-transform'] == 'none')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('None', 'gallery-showcase-pro'); ?></option>
                            <option value="capitalize" <?php echo ((isset($gspwp_options['gs-title-text-transform']) && $gspwp_options['gs-title-text-transform'] == 'capitalize')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Capitalize', 'gallery-showcase-pro'); ?></option>
                            <option value="uppercase" <?php echo ((isset($gspwp_options['gs-title-text-transform']) && $gspwp_options['gs-title-text-transform'] == 'uppercase')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Uppercase', 'gallery-showcase-pro'); ?></option>
                            <option value="lowercase" <?php echo ((isset($gspwp_options['gs-title-text-transform']) && $gspwp_options['gs-title-text-transform'] == 'lowercase')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Lowercase', 'gallery-showcase-pro'); ?></option>
                            <option value="full-width" <?php echo ((isset($gspwp_options['gs-title-text-transform']) && $gspwp_options['gs-title-text-transform'] == 'full-width')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Full Width', 'gallery-showcase-pro'); ?></option>
                        </select>
                    </div>
                    <div class="gspwp-form-field gspwp-select-cover">
                        <label for="gspwp-title-text-decoration"><?php esc_html_e('Text Decoration', 'gallery-showcase-pro') ?></label>
                        <select id="gspwp-title-text-decoration" name="options[gs-title-text-decoration]">
                            <option value="none" <?php echo ((isset($gspwp_options['gs-title-text-decoration']) && $gspwp_options['gs-title-text-decoration'] == 'none')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('None', 'gallery-showcase-pro'); ?></option>
                            <option value="underline" <?php echo ((isset($gspwp_options['gs-title-text-decoration']) && $gspwp_options['gs-title-text-decoration'] == 'underline')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Underline', 'gallery-showcase-pro'); ?></option>
                            <option value="overline" <?php echo ((isset($gspwp_options['gs-title-text-decoration']) && $gspwp_options['gs-title-text-decoration'] == 'overline')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Overline', 'gallery-showcase-pro'); ?></option>
                            <option value="line-through" <?php echo ((isset($gspwp_options['gs-title-text-decoration']) && $gspwp_options['gs-title-text-decoration'] == 'line-through')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Line Through', 'gallery-showcase-pro'); ?></option>
                        </select>
                    </div>
                    <div class="gspwp-form-field gspwp-number-cover">
                        <label for="gspwp-title-line-height"><?php esc_html_e('Line Height', 'gallery-showcase-pro') ?></label>
                        <input type="number" class="gspwp-one-number" id="gspwp-title-line-height" name="options[gs-title-line-height]" min="0" step="0.1" placeholder="<?php esc_attr_e('Line Height', 'gallery-showcase-pro'); ?>" value="<?php echo (isset($gspwp_options['gs-title-line-height']) && $gspwp_options['gs-title-line-height'] != '') ? esc_attr($gspwp_options['gs-title-line-height']) : '1.5' ?>">
                    </div>
                    <div class="gspwp-form-field gspwp-number-cover">
                        <label for="gspwp-title-letter-spacing"><?php esc_html_e('Letter Spacing', 'gallery-showcase-pro') ?> (PX)</label>
                        <input type="number" class="gspwp-one-number" id="gspwp-title-letter-spacing" name="options[gs-title-letter-spacing]" min="0" placeholder="<?php esc_attr_e('Letter Spacing', 'gallery-showcase-pro'); ?>" value="<?php echo (isset($gspwp_options['gs-title-letter-spacing']) && $gspwp_options['gs-title-letter-spacing'] != '') ? esc_attr($gspwp_options['gs-title-letter-spacing']) : '0' ?>">
                    </div>
                </div>
            </div>

            <div id="content-setting-data" class="panel gspwp-options-panel hidden">
                <div class="gspwp-options-group">
                    <div class="gspwp-form-field gspwp-buttonset-cover">
                        <label> <?php esc_html_e('Display Content', 'gallery-showcase-pro'); ?> </label>
                        <fieldset class="gspwp-buttonset">
                            <?php $display_content = isset($gspwp_options['display_content']) ? $gspwp_options['display_content'] : 0; ?>
                            <input id="display_content_0" name="options[display_content]" type="radio" value="0" <?php echo checked(0, $display_content); ?>/>
                            <label for="display_content_0"><?php esc_html_e('Yes', 'gallery-showcase-pro'); ?></label>
                            <input id="display_content_1" name="options[display_content]" type="radio" value="1" <?php echo checked(1, $display_content); ?> />
                            <label for="display_content_1"><?php esc_html_e('No', 'gallery-showcase-pro'); ?></label>
                        </fieldset>
                    </div>
                </div>

                <div class="gspwp-options-group gspwp-content-options">
                    <div class="gspwp-form-field gspwp-select-cover">
                        <label for="content_font"> <?php esc_html_e('Font Family', 'gallery-showcase-pro'); ?> </label>
                        <select id="content_font" name="options[content_font]">
                            <option value="" <?php echo (isset($gspwp_options['content_font']) && $gspwp_options['content_font'] == '') ? 'selected="selected"' : '' ?>><?php esc_html_e('Default', 'gallery-showcase-pro') ?></option>
                        </select>
                        <input type="text" hidden="hidden" value="<?php echo isset($gspwp_options['content_font']) ? esc_attr($gspwp_options['content_font']) : '' ?>" class="content_font_selected" />
                    </div>
                </div>

                <div class="gspwp-options-group gspwp-content-options">
                    <div class="gspwp-form-field gspwp-number-cover">
                        <label for="gspwp-content-font-size"> <?php esc_html_e('Font Size', 'gallery-showcase-pro'); ?> </label>
                        <input type="number" class="gspwp-one-number" id="gspwp-content-font-size" name="options[content-font-size]" placeholder="<?php esc_attr_e('Font Size', 'gallery-showcase-pro'); ?>" value="<?php echo (isset($gspwp_options['content-font-size']) && $gspwp_options['content-font-size'] != '') ? esc_attr($gspwp_options['content-font-size']) : '0' ?>">
                    </div>
                </div>

                <div class="gspwp-options-group gspwp-content-options">
                    <div class="gspwp-form-field gspwp-color-cover">
                        <label for="gspwp-content-font-color"><?php esc_html_e('Font Color', 'gallery-showcase-pro') ?></label>
                        <input id="gspwp-content-font-color" name="options[gs-content-font-color]" class="gspwp-color-picker" data-alpha="true" placeholder="<?php esc_attr_e('Color', 'gallery-showcase-pro'); ?>" value="<?php echo (isset($gspwp_options['gs-content-font-color'])) ? esc_attr($gspwp_options['gs-content-font-color']) : '#ffffff'; ?>" type="text" />
                    </div>
                </div>

                <div class="gspwp-options-group gspwp-content-bg-color-div">
                    <div class="gspwp-form-field gspwp-color-cover">
                        <label for="gspwp-content-bg-color"><?php esc_html_e('Background Color', 'gallery-showcase-pro') ?></label>
                        <input id="gspwp-content-bg-color" name="options[gs-content-bg-color]" class="gspwp-color-picker" data-alpha="true" placeholder="<?php esc_attr_e('Color', 'gallery-showcase-pro'); ?>" value="<?php echo (isset($gspwp_options['gs-content-bg-color'])) ? esc_attr($gspwp_options['gs-content-bg-color']) : '#ffffff'; ?>" type="text" />
                    </div>
                </div>

                <div class="gspwp-options-group gspwp-content-options">
                    <div class="gspwp-form-field gspwp-checkbox-cover">
                        <label for="gspwp-content-font-italic"><?php esc_html_e('Italic Style', 'gallery-showcase-pro') ?></label>
                        <input id="gspwp-content-font-italic" name="options[gs-content-font-italic]" value="1" type="checkbox" <?php echo (isset($gspwp_options['gs-content-font-italic']) && $gspwp_options['gs-content-font-italic'] == 1) ? 'checked="checked"' : ''; ?>>
                    </div>
                    <div class="gspwp-form-field gspwp-select-cover">
                        <?php $content_font_weidth = (isset($gspwp_options['gs-content-font-weight']) && $gspwp_options['gs-content-font-weight'] != '') ? $gspwp_options['gs-content-font-weight'] : 'normal'; ?>
                        <label for="gspwp-content-font-weight"><?php esc_html_e('Font Weight', 'gallery-showcase-pro') ?></label>
                        <select id="gspwp-content-font-weight" name="options[gs-content-font-weight]">
                            <option value="100" <?php echo ($content_font_weidth == '100') ? 'selected="selected"' : ''; ?> >100</option>
                            <option value="200" <?php echo ($content_font_weidth == '200') ? 'selected="selected"' : ''; ?> >200</option>
                            <option value="300" <?php echo ($content_font_weidth == '300') ? 'selected="selected"' : ''; ?> >300</option>
                            <option value="400" <?php echo ($content_font_weidth == '400') ? 'selected="selected"' : ''; ?> >400</option>
                            <option value="500" <?php echo ($content_font_weidth == '500') ? 'selected="selected"' : ''; ?> >500</option>
                            <option value="600" <?php echo ($content_font_weidth == '600') ? 'selected="selected"' : ''; ?> >600</option>
                            <option value="700" <?php echo ($content_font_weidth == '800') ? 'selected="selected"' : ''; ?> >700</option>
                            <option value="800" <?php echo ($content_font_weidth == '800') ? 'selected="selected"' : ''; ?> >800</option>
                            <option value="900" <?php echo ($content_font_weidth == '900') ? 'selected="selected"' : ''; ?> >900</option>
                            <option value="bold" <?php echo ($content_font_weidth == 'bold') ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Bold', 'gallery-showcase-pro'); ?></option>
                            <option value="normal" <?php echo ($content_font_weidth == 'normal') ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Normal', 'gallery-showcase-pro'); ?></option>
                        </select>
                    </div>
                    <div class="gspwp-form-field gspwp-select-cover">
                        <label for="gspwp-content-text-transform"><?php esc_html_e('Text Transform', 'gallery-showcase-pro') ?></label>
                        <select id="gspwp-content-text-transform" name="options[gs-content-text-transform]">
                            <option value="none" <?php echo ((isset($gspwp_options['gs-content-text-transform']) && $gspwp_options['gs-content-text-transform'] == 'none')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('None', 'gallery-showcase-pro'); ?></option>
                            <option value="capitalize" <?php echo ((isset($gspwp_options['gs-content-text-transform']) && $gspwp_options['gs-content-text-transform'] == 'capitalize')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Capitalize', 'gallery-showcase-pro'); ?></option>
                            <option value="uppercase" <?php echo ((isset($gspwp_options['gs-content-text-transform']) && $gspwp_options['gs-content-text-transform'] == 'uppercase')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Uppercase', 'gallery-showcase-pro'); ?></option>
                            <option value="lowercase" <?php echo ((isset($gspwp_options['gs-content-text-transform']) && $gspwp_options['gs-content-text-transform'] == 'lowercase')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Lowercase', 'gallery-showcase-pro'); ?></option>
                            <option value="full-width" <?php echo ((isset($gspwp_options['gs-content-text-transform']) && $gspwp_options['gs-content-text-transform'] == 'full-width')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Full Width', 'gallery-showcase-pro'); ?></option>
                        </select>
                    </div>
                    <div class="gspwp-form-field gspwp-select-cover">
                        <label for="gspwp-content-text-decoration"><?php esc_html_e('Text Decoration', 'gallery-showcase-pro') ?></label>
                        <select id="gspwp-content-text-decoration" name="options[gs-content-text-decoration]">
                            <option value="none" <?php echo ((isset($gspwp_options['gs-content-text-decoration']) && $gspwp_options['gs-content-text-decoration'] == 'none')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('None', 'gallery-showcase-pro'); ?></option>
                            <option value="underline" <?php echo ((isset($gspwp_options['gs-content-text-decoration']) && $gspwp_options['gs-content-text-decoration'] == 'underline')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Underline', 'gallery-showcase-pro'); ?></option>
                            <option value="overline" <?php echo ((isset($gspwp_options['gs-content-text-decoration']) && $gspwp_options['gs-content-text-decoration'] == 'overline')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Overline', 'gallery-showcase-pro'); ?></option>
                            <option value="line-through" <?php echo ((isset($gspwp_options['gs-content-text-decoration']) && $gspwp_options['gs-content-text-decoration'] == 'line-through')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Line Through', 'gallery-showcase-pro'); ?></option>
                        </select>
                    </div>
                    <div class="gspwp-form-field gspwp-number-cover">
                        <label for="gspwp-content-line-height"><?php esc_html_e('Line Height', 'gallery-showcase-pro') ?></label>
                        <input type="number" class="gspwp-one-number" id="gspwp-content-line-height" name="options[gs-content-line-height]" min="0" step="0.1" placeholder="<?php esc_attr_e('Line Height', 'gallery-showcase-pro'); ?>" value="<?php echo (isset($gspwp_options['gs-content-line-height']) && $gspwp_options['gs-content-line-height'] != '') ? esc_attr($gspwp_options['gs-content-line-height']) : '1.5' ?>">
                    </div>
                    <div class="gspwp-form-field gspwp-number-cover">
                        <label for="gspwp-content-letter-spacing"><?php esc_html_e('Letter Spacing', 'gallery-showcase-pro') ?> (PX)</label>
                        <input type="number" class="gspwp-one-number" id="gspwp-content-letter-spacing" name="options[gs-content-letter-spacing]" min="0" placeholder="<?php esc_attr_e('Letter Spacing', 'gallery-showcase-pro'); ?>" value="<?php echo (isset($gspwp_options['gs-content-letter-spacing']) && $gspwp_options['gs-content-letter-spacing'] != '') ? esc_attr($gspwp_options['gs-content-letter-spacing']) : '0' ?>">
                    </div>
                </div>
            </div>

            <div id="filter-design" class="panel gspwp-options-panel hidden">

                <div class="gspwp-options-group">
                    <div class="gspwp-form-field gspwp-select-cover">
                        <label for="filter_title_font"> <?php esc_html_e('Font Family', 'gallery-showcase-pro'); ?> </label>
                        <select id="filter_title_font" name="options[filter_title_font]">
                            <option value="" <?php echo (isset($gspwp_options['filter_title_font']) && $gspwp_options['filter_title_font'] == '') ? 'selected="selected"' : '' ?>><?php esc_html_e('Default', 'gallery-showcase-pro') ?></option>
                        </select>
                        <input type="text" hidden="hidden" value="<?php echo isset($gspwp_options['filter_title_font']) ? esc_attr($gspwp_options['filter_title_font']) : ''; ?>" class="filter_title_font_selected" />
                    </div>
                </div>

                <div class="gspwp-options-group">
                    <div class="gspwp-form-field gspwp-number-cover">
                        <label for="gspwp-filter-title-font-size"> <?php esc_html_e('Font Size', 'gallery-showcase-pro'); ?> </label>
                        <input type="number" class="gspwp-one-number" id="gspwp-filter-title-font-size" name="options[filter-title-font-size]" placeholder="<?php esc_attr_e('Font Size', 'gallery-showcase-pro'); ?>" value="<?php echo (isset($gspwp_options['filter-title-font-size']) && $gspwp_options['filter-title-font-size'] != '') ? esc_attr($gspwp_options['filter-title-font-size']) : '0' ?>">
                    </div>
                </div>

                <div class="gspwp-options-group d-flex">
                    <div class="gspwp-form-field gspwp-color-cover">
                        <label for="gspwp-filter-title-font-color"><?php esc_html_e('Font Color', 'gallery-showcase-pro') ?></label>
                        <input id="gspwp-filter-title-font-color" name="options[gs-filter-title-font-color]" class="gspwp-color-picker" data-alpha="true" placeholder="<?php esc_attr_e('Color', 'gallery-showcase-pro'); ?>" value="<?php echo (isset($gspwp_options['gs-filter-title-font-color'])) ? esc_attr($gspwp_options['gs-filter-title-font-color']) : '#4f4f4f'; ?>" type="text" />
                    </div>
                    <div class="gspwp-form-field gspwp-color-cover">
                        <label for="gspwp-filter-title-font-color-hover"><?php esc_html_e('Hover Font Color', 'gallery-showcase-pro') ?></label>
                        <input id="gspwp-filter-title-font-color-hover" name="options[gs-filter-title-font-color-hover]" class="gspwp-color-picker" data-alpha="true" placeholder="<?php esc_attr_e('Color', 'gallery-showcase-pro'); ?>" value="<?php echo (isset($gspwp_options['gs-filter-title-font-color-hover'])) ? esc_attr($gspwp_options['gs-filter-title-font-color-hover']) : '#000000'; ?>" type="text" />
                    </div>
                </div>

                <div class="gspwp-options-group d-flex">
                    <div class="gspwp-form-field gspwp-color-cover">
                        <label for="gspwp-filter-title-bg-color"><?php esc_html_e('Background Color', 'gallery-showcase-pro') ?></label>
                        <input id="gspwp-filter-title-bg-color" name="options[gs-filter-title-bg-color]" class="gspwp-color-picker" data-alpha="true" placeholder="<?php esc_attr_e('Color', 'gallery-showcase-pro'); ?>" value="<?php echo (isset($gspwp_options['gs-filter-title-bg-color'])) ? esc_attr($gspwp_options['gs-filter-title-bg-color']) : '#ffffff'; ?>" type="text" />
                    </div>
                    <div class="gspwp-form-field gspwp-color-cover">
                        <label for="gspwp-filter-title-bg-color-hover"><?php esc_html_e('Hover Background Color', 'gallery-showcase-pro') ?></label>
                        <input id="gspwp-filter-title-bg-color-hover" name="options[gs-filter-title-bg-color-hover]" class="gspwp-color-picker" data-alpha="true" placeholder="<?php esc_attr_e('Color', 'gallery-showcase-pro'); ?>" value="<?php echo (isset($gspwp_options['gs-filter-title-bg-color-hover'])) ? esc_attr($gspwp_options['gs-filter-title-bg-color-hover']) : '#ffffff'; ?>" type="text" />
                    </div>
                </div>

                <div class="gspwp-options-group ">
                    <div class="gspwp-form-field gspwp-number-cover">
                        <label for="gspwp-filter-gap"><?php esc_html_e('Filter Tab Gap', 'gallery-showcase-pro') ?></label>
                        <input type="number" class="gspwp-one-number" id="gspwp-filter-gap" name="options[gs-filter-gap]" min="0"  placeholder="<?php esc_attr_e('Line Height', 'gallery-showcase-pro'); ?>" value="<?php echo (isset($gspwp_options['gs-filter-gap']) && $gspwp_options['gs-filter-gap'] != '') ? esc_attr($gspwp_options['gs-filter-gap']) : '10' ?>">
                    </div>
                </div>

                <div class="gspwp-options-group">
                    <div class="gspwp-form-field gspwp-number-cover">
                        <label for="filter_padding_top"> <?php esc_html_e('Padding', 'gallery-showcase-pro') ?> </label>
                        <input type="number" class="gspwp-four-number" id="filter_padding_top" name="options[filter_padding_top]" min="0" placeholder="<?php esc_attr_e('Top', 'gallery-showcase-pro'); ?>" value="<?php echo ($gspwp_options['filter_padding_top'] != '') ? esc_attr($gspwp_options['filter_padding_top']) : '0' ?>">
                        <input type="number" class="gspwp-four-number" id="filter_padding_right" name="options[filter_padding_right]" min="0" placeholder="<?php esc_attr_e('Right', 'gallery-showcase-pro'); ?>" value="<?php echo ($gspwp_options['filter_padding_right'] != '') ? esc_attr($gspwp_options['filter_padding_right']) : '0' ?>">
                        <input type="number" class="gspwp-four-number" id="filter_padding_bottom" name="options[filter_padding_bottom]" min="0" placeholder="<?php esc_attr_e('Bottom', 'gallery-showcase-pro'); ?>" value="<?php echo ($gspwp_options['filter_padding_bottom'] != '') ?esc_attr( $gspwp_options['filter_padding_bottom']) : '0' ?>">
                        <input type="number" class="gspwp-four-number" id="filter_padding_left" name="options[filter_padding_left]" min="0" placeholder="<?php esc_attr_e('Left', 'gallery-showcase-pro'); ?>" value="<?php echo ($gspwp_options['filter_padding_left'] != '') ? esc_attr($gspwp_options['filter_padding_left']) : '0' ?>">
                    </div>
                </div>

                <div class="gspwp-options-group">
                    <div class="gspwp-form-field gspwp-checkbox-cover">
                        <label for="gspwp-filter-title-font-italic"><?php esc_html_e('Italic Style', 'gallery-showcase-pro') ?></label>
                        <input id="gspwp-filter-title-font-italic" name="options[gs-filter-title-font-italic]" value="1" type="checkbox" <?php echo (isset($gspwp_options['gs-filter-title-font-italic']) && $gspwp_options['gs-filter-title-font-italic'] == 1) ? 'checked="checked"' : ''; ?>>
                    </div>
                    <div class="gspwp-form-field gspwp-select-cover">
                        <?php $title_font_weidth = (isset($gspwp_options['gs-filter-title-font-weight']) && $gspwp_options['gs-filter-title-font-weight'] != '') ? $gspwp_options['gs-filter-title-font-weight'] : 'normal'; ?>
                        <label for="gspwp-filter-title-font-weight"><?php esc_html_e('Font Weight', 'gallery-showcase-pro') ?></label>
                        <select id="gspwp-filter-title-font-weight" name="options[gs-filter-title-font-weight]">
                            <option value="100" <?php echo ($title_font_weidth == '100') ? 'selected="selected"' : ''; ?> >100</option>
                            <option value="200" <?php echo ($title_font_weidth == '200') ? 'selected="selected"' : ''; ?> >200</option>
                            <option value="300" <?php echo ($title_font_weidth == '300') ? 'selected="selected"' : ''; ?> >300</option>
                            <option value="400" <?php echo ($title_font_weidth == '400') ? 'selected="selected"' : ''; ?> >400</option>
                            <option value="500" <?php echo ($title_font_weidth == '500') ? 'selected="selected"' : ''; ?> >500</option>
                            <option value="600" <?php echo ($title_font_weidth == '600') ? 'selected="selected"' : ''; ?> >600</option>
                            <option value="700" <?php echo ($title_font_weidth == '800') ? 'selected="selected"' : ''; ?> >700</option>
                            <option value="800" <?php echo ($title_font_weidth == '800') ? 'selected="selected"' : ''; ?> >800</option>
                            <option value="900" <?php echo ($title_font_weidth == '900') ? 'selected="selected"' : ''; ?> >900</option>
                            <option value="bold" <?php echo ($title_font_weidth == 'bold') ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Bold', 'gallery-showcase-pro'); ?></option>
                            <option value="normal" <?php echo ($title_font_weidth == 'normal') ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Normal', 'gallery-showcase-pro'); ?></option>
                        </select>
                    </div>
                    <div class="gspwp-form-field gspwp-select-cover">
                        <label for="gspwp-filter-title-text-transform"><?php esc_html_e('Text Transform', 'gallery-showcase-pro') ?></label>
                        <select id="gspwp-filter-title-text-transform" name="options[gs-filter-title-text-transform]">
                            <option value="none" <?php echo ((isset($gspwp_options['gs-filter-title-text-transform']) && $gspwp_options['gs-filter-title-text-transform'] == 'none')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('None', 'gallery-showcase-pro'); ?></option>
                            <option value="capitalize" <?php echo ((isset($gspwp_options['gs-filter-title-text-transform']) && $gspwp_options['gs-filter-title-text-transform'] == 'capitalize')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Capitalize', 'gallery-showcase-pro'); ?></option>
                            <option value="uppercase" <?php echo ((isset($gspwp_options['gs-filter-title-text-transform']) && $gspwp_options['gs-filter-title-text-transform'] == 'uppercase')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Uppercase', 'gallery-showcase-pro'); ?></option>
                            <option value="lowercase" <?php echo ((isset($gspwp_options['gs-filter-title-text-transform']) && $gspwp_options['gs-filter-title-text-transform'] == 'lowercase')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Lowercase', 'gallery-showcase-pro'); ?></option>
                            <option value="full-width" <?php echo ((isset($gspwp_options['gs-filter-title-text-transform']) && $gspwp_options['gs-filter-title-text-transform'] == 'full-width')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Full Width', 'gallery-showcase-pro'); ?></option>
                        </select>
                    </div>
                    <div class="gspwp-form-field gspwp-select-cover">
                        <label for="gspwp-filter-title-text-decoration"><?php esc_html_e('Text Decoration', 'gallery-showcase-pro') ?></label>
                        <select id="gspwp-filter-title-text-decoration" name="options[gs-filter-title-text-decoration]">
                            <option value="none" <?php echo ((isset($gspwp_options['gs-filter-title-text-decoration']) && $gspwp_options['gs-filter-title-text-decoration'] == 'none')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('None', 'gallery-showcase-pro'); ?></option>
                            <option value="underline" <?php echo ((isset($gspwp_options['gs-filter-title-text-decoration']) && $gspwp_options['gs-filter-title-text-decoration'] == 'underline')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Underline', 'gallery-showcase-pro'); ?></option>
                            <option value="overline" <?php echo ((isset($gspwp_options['gs-filter-title-text-decoration']) && $gspwp_options['gs-filter-title-text-decoration'] == 'overline')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Overline', 'gallery-showcase-pro'); ?></option>
                            <option value="line-through" <?php echo ((isset($gspwp_options['gs-filter-title-text-decoration']) && $gspwp_options['gs-filter-title-text-decoration'] == 'line-through')) ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Line Through', 'gallery-showcase-pro'); ?></option>
                        </select>
                    </div>
                    <div class="gspwp-form-field gspwp-number-cover">
                        <label for="gspwp-filter-title-line-height"><?php esc_html_e('Line Height', 'gallery-showcase-pro') ?></label>
                        <input type="number" class="gspwp-one-number" id="gspwp-filter-title-line-height" name="options[gs-filter-title-line-height]" min="0" step="0.1" placeholder="<?php esc_attr_e('Line Height', 'gallery-showcase-pro'); ?>" value="<?php echo (isset($gspwp_options['gs-filter-title-line-height']) && $gspwp_options['gs-filter-title-line-height'] != '') ? esc_attr($gspwp_options['gs-filter-title-line-height']) : '1.5' ?>">
                    </div>
                    <div class="gspwp-form-field gspwp-number-cover">
                        <label for="gspwp-filter-title-letter-spacing"><?php esc_html_e('Letter Spacing', 'gallery-showcase-pro') ?> (PX)</label>
                        <input type="number" class="gspwp-one-number" id="gspwp-filter-title-letter-spacing" name="options[gs-filter-title-letter-spacing]" min="0" placeholder="<?php esc_attr_e('Letter Spacing', 'gallery-showcase-pro'); ?>" value="<?php echo (isset($gspwp_options['gs-filter-title-letter-spacing']) && $gspwp_options['gs-filter-title-letter-spacing'] != '') ? esc_attr($gspwp_options['gs-filter-title-letter-spacing']) : '0' ?>">
                    </div>
                </div>

            </div>

        </div>

        <div id="gspwp-arrow-style-dialog" class="gspwp-arrow-style-dialog" title="<?php esc_attr_e('Select Arrows', 'gallery-showcase-pro'); ?>" style="display: none;">
            <div class="gspwp-arrow-style-box">
                <?php
                for ($i = 1; $i <= 10; $i++) {
                    ?>
                    <div class="gspwp-arrow-cover" data-style="style-<?php echo esc_attr($i); ?>">
                        <div class="gspwp-arrow gspwp-left-arrow gspwp-arrow-style-<?php echo esc_attr($i); ?>"></div>
                        <div class="gspwp-arrow gspwp-right-arrow gspwp-arrow-style-<?php echo esc_attr($i); ?>"></div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>

        <div id="gspwp-navigation-style-dialog" class="gspwp-navigation-style-dialog" title="<?php esc_attr_e('Select Navigation', 'gallery-showcase-pro'); ?>" style="display: none;">
            <div class="gspwp-navigation-style-box">
                <?php
                for ($i = 1; $i <= 10; $i++) {
                    ?>
                    <div class="gspwp-navigation-cover" data-style="style-<?php echo esc_attr($i); ?>">
                        <div class="gspwp-navigation gspwp-navigation-style-<?php echo esc_attr($i); ?>">
                            <div class="owl-pagination">
                                <div class="owl-page active"><span></span></div>
                                <div class="owl-page"><span></span></div>
                                <div class="owl-page"><span></span></div>
                                <div class="owl-page"><span></span></div>
                                <div class="owl-page"><span></span></div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>

        </div>

        <?php
    }

}