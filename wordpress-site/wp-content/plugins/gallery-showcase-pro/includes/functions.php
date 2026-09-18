<?php
/**
 * Dynamic Style
 * @param $gspwp_options, $layout
 * @return Dynamic Style
 * @since Gallery Showcase Pro 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if (!function_exists('gspwp_add_dynamic_style')) {

    function gspwp_add_dynamic_style($gspwp_options, $layout = '') {

        if ($layout != '') {
            if (!class_exists('gspwp_lessc') && !class_exists('gspwp_lessc') && !class_exists('gspwp_lessc') && !class_exists('gspwp_lessc') && !class_exists('gspwp_lessc')) {
                include_once GSPWP_PLUGIN_DIR . 'assets/less/lessc.inc.php';
            }
            $data['layout'] = $layout;
            $data['layout_effect_group'] = (isset($gspwp_options['layout_effect_group']) && $gspwp_options['layout_effect_group'] != '') ? $gspwp_options['layout_effect_group'] : '';
            $data['layout_effect'] = (isset($gspwp_options['layout_effect']) && $gspwp_options['layout_effect'] != '') ? $gspwp_options['layout_effect'] : '';
            $data['padding_top'] = (isset($gspwp_options['padding_top']) && $gspwp_options['padding_top'] != '') ? $gspwp_options['padding_top'] . 'px' : '0';
            $data['padding_right'] = (isset($gspwp_options['padding_right']) && $gspwp_options['padding_right'] != '') ? $gspwp_options['padding_right'] . 'px' : '0';
            $data['padding_bottom'] = (isset($gspwp_options['padding_bottom']) && $gspwp_options['padding_bottom'] != '') ? $gspwp_options['padding_bottom'] . 'px' : '0';
            $data['padding_left'] = (isset($gspwp_options['padding_left']) && $gspwp_options['padding_left'] != '') ? $gspwp_options['padding_left'] . 'px' : '0';
            $data['image_height'] = (isset($gspwp_options['image_height']) && $gspwp_options['image_height'] != '') ? $gspwp_options['image_height'] . 'px' : 'auto';

            $data['layout_bg_color'] = (isset($gspwp_options['gs-layout-bg-color']) && $gspwp_options['gs-layout-bg-color'] != '') ? $gspwp_options['gs-layout-bg-color'] : 'rgba(72,76,97,0.8)';
            $data['layout_border_color'] = (isset($gspwp_options['gs-layout-border-color']) && $gspwp_options['gs-layout-border-color'] != '') ? $gspwp_options['gs-layout-border-color'] : '#ffffff';

            $data['title_font'] = (isset($gspwp_options['title_font']) && $gspwp_options['title_font'] != '') ? $gspwp_options['title_font'] : 'inherit';
            $data['title_font_size'] = (isset($gspwp_options['title-font-size']) && $gspwp_options['title-font-size'] > 0) ? $gspwp_options['title-font-size'] : 0;
            $data['title_font_color'] = (isset($gspwp_options['gs-title-font-color']) && $gspwp_options['gs-title-font-color'] != '') ? $gspwp_options['gs-title-font-color'] : 'inherit';
            $data['title_font_italic'] = (isset($gspwp_options['gs-title-font-italic']) && $gspwp_options['gs-title-font-italic'] == 1) ? 'italic' : 'normal';
            $data['title_font_weight'] = (isset($gspwp_options['gs-title-font-weight']) && $gspwp_options['gs-title-font-weight'] != '') ? $gspwp_options['gs-title-font-weight'] : 'normal';
            $data['title_text_transform'] = (isset($gspwp_options['gs-title-text-transform']) && $gspwp_options['gs-title-text-transform'] != '') ? $gspwp_options['gs-title-text-transform'] : 'none';
            $data['title_text_decoration'] = (isset($gspwp_options['gs-title-text-decoration']) && $gspwp_options['gs-title-text-decoration'] != '') ? $gspwp_options['gs-title-text-decoration'] : 'none';
            $data['title_line_height'] = (isset($gspwp_options['gs-title-line-height']) && $gspwp_options['gs-title-line-height'] != '') ? $gspwp_options['gs-title-line-height'] : '1.2';
            $data['title_letter_spacing'] = (isset($gspwp_options['gs-title-letter-spacing']) && $gspwp_options['gs-title-letter-spacing'] != '') ? $gspwp_options['gs-title-letter-spacing'] . 'px' : '0';
            $data['title_bg_color'] = (isset($gspwp_options['gs-title-bg-color']) && $gspwp_options['gs-title-bg-color'] != '') ? $gspwp_options['gs-title-bg-color'] : 'transparent';

            $data['content_font'] = (isset($gspwp_options['content_font']) && $gspwp_options['content_font'] != '') ? $gspwp_options['content_font'] : 'inherit';
            $data['content_font_size'] = (isset($gspwp_options['content-font-size']) && $gspwp_options['content-font-size'] > 0) ? $gspwp_options['content-font-size'] : 0;
            $data['content_font_color'] = (isset($gspwp_options['gs-content-font-color']) && $gspwp_options['gs-content-font-color'] != '') ? $gspwp_options['gs-content-font-color'] : 'inherit';
            $data['content_font_italic'] = (isset($gspwp_options['gs-content-font-italic']) && $gspwp_options['gs-content-font-italic'] == 1) ? 'italic' : 'normal';
            $data['content_font_weight'] = (isset($gspwp_options['gs-content-font-weight']) && $gspwp_options['gs-content-font-weight'] != '') ? $gspwp_options['gs-content-font-weight'] : 'normal';
            $data['content_text_transform'] = (isset($gspwp_options['gs-content-text-transform']) && $gspwp_options['gs-content-text-transform'] != '') ? $gspwp_options['gs-content-text-transform'] : 'none';
            $data['content_text_decoration'] = (isset($gspwp_options['gs-content-text-decoration']) && $gspwp_options['gs-content-text-decoration'] != '') ? $gspwp_options['gs-content-text-decoration'] : 'none';
            $data['content_line_height'] = (isset($gspwp_options['gs-content-line-height']) && $gspwp_options['gs-content-line-height'] != '') ? $gspwp_options['gs-content-line-height'] : '1.2';
            $data['content_letter_spacing'] = (isset($gspwp_options['gs-content-letter-spacing']) && $gspwp_options['gs-content-letter-spacing'] != '') ? $gspwp_options['gs-content-letter-spacing'] . 'px' : '0';
            $data['content_bg_color'] = (isset($gspwp_options['gs-content-bg-color']) && $gspwp_options['gs-content-bg-color'] != '') ? $gspwp_options['gs-content-bg-color'] : 'transparent';

            $data['display_arrow'] = (isset($gspwp_options['display_arrow']) && $gspwp_options['display_arrow'] == 1) ? 'none' : 'block';
            $data['gs_arrow_size'] = (isset($gspwp_options['gs_arrow_size']) && $gspwp_options['gs_arrow_size'] > 0) ? $gspwp_options['gs_arrow_size'] . 'px' : '40px';
            $data['left_arrow_hori_pos'] = (isset($gspwp_options['left_arrow_hori_pos']) && $gspwp_options['left_arrow_hori_pos'] != '') ? $gspwp_options['left_arrow_hori_pos'] : '0';
            $data['left_arrow_vert_pos'] = (isset($gspwp_options['left_arrow_vert_pos']) && $gspwp_options['left_arrow_vert_pos'] != '') ? $gspwp_options['left_arrow_vert_pos'] : '1';
            $data['left_arrow_hori_off'] = (isset($gspwp_options['left_arrow_hori_off']) && $gspwp_options['left_arrow_hori_off'] != '') ? $gspwp_options['left_arrow_hori_off'] . 'px' : '20px';
            $data['left_arrow_vert_off'] = (isset($gspwp_options['left_arrow_vert_off']) && $gspwp_options['left_arrow_vert_off'] != '') ? $gspwp_options['left_arrow_vert_off'] . 'px' : '0';
            $data['right_arrow_hori_pos'] = (isset($gspwp_options['right_arrow_hori_pos']) && $gspwp_options['right_arrow_hori_pos'] != '') ? $gspwp_options['right_arrow_hori_pos'] : '2';
            $data['right_arrow_vert_pos'] = (isset($gspwp_options['right_arrow_vert_pos']) && $gspwp_options['right_arrow_vert_pos'] != '') ? $gspwp_options['right_arrow_vert_pos'] : '1';
            $data['right_arrow_hori_off'] = (isset($gspwp_options['right_arrow_hori_off']) && $gspwp_options['right_arrow_hori_off'] != '') ? $gspwp_options['right_arrow_hori_off'] . 'px' : '-20px';
            $data['right_arrow_vert_off'] = (isset($gspwp_options['right_arrow_vert_off']) && $gspwp_options['right_arrow_vert_off'] != '') ? $gspwp_options['right_arrow_vert_off'] . 'px' : '0';

            $data['display_navigation'] = (isset($gspwp_options['display_navigation']) && $gspwp_options['display_navigation'] == 1) ? 'none' : 'inline-block';
            $data['navigation_size'] = (isset($gspwp_options['gs_navigation_size']) && $gspwp_options['gs_navigation_size'] > 0) ? $gspwp_options['gs_navigation_size'] . 'px' : '16px';
            $data['nav_hori_pos'] = (isset($gspwp_options['nav_hori_pos']) && $gspwp_options['nav_hori_pos'] != '') ? $gspwp_options['nav_hori_pos'] : 1;
            $data['nav_vert_pos'] = (isset($gspwp_options['nav_vert_pos']) && $gspwp_options['nav_vert_pos'] != '') ? $gspwp_options['nav_vert_pos'] : 2;
            $data['nav_hori_off'] = (isset($gspwp_options['nav_hori_off']) && $gspwp_options['nav_hori_off'] != '') ? $gspwp_options['nav_hori_off'] . 'px' : 0;
            $data['nav_vert_off'] = (isset($gspwp_options['nav_vert_off']) && $gspwp_options['nav_vert_off'] != '') ? $gspwp_options['nav_vert_off'] . 'px' : '-20px';

            // filter design options
            $data['filter_title_font'] = (isset($gspwp_options['filter_title_font']) && $gspwp_options['filter_title_font'] != '') ? $gspwp_options['filter_title_font'] : 'inherit';
            $data['filter_title_font_size'] = (isset($gspwp_options['filter-title-font-size']) && $gspwp_options['filter-title-font-size'] > 0) ? $gspwp_options['filter-title-font-size'] : 0;
            $data['filter_title_font_color'] = (isset($gspwp_options['gs-filter-title-font-color']) && $gspwp_options['gs-filter-title-font-color'] != '') ? $gspwp_options['gs-filter-title-font-color'] : 'inherit';
            $data['filter_title_font_color_hover'] = (isset($gspwp_options['gs-filter-title-font-color-hover']) && $gspwp_options['gs-filter-title-font-color-hover'] != '') ? $gspwp_options['gs-filter-title-font-color-hover'] : 'inherit';
            $data['filter_title_font_italic'] = (isset($gspwp_options['gs-filter-title-font-italic']) && $gspwp_options['gs-filter-title-font-italic'] == 1) ? 'italic' : 'normal';
            $data['filter_title_font_weight'] = (isset($gspwp_options['gs-filter-title-font-weight']) && $gspwp_options['gs-filter-title-font-weight'] != '') ? $gspwp_options['gs-filter-title-font-weight'] : 'normal';
            $data['filter_title_text_transform'] = (isset($gspwp_options['gs-filter-title-text-transform']) && $gspwp_options['gs-filter-title-text-transform'] != '') ? $gspwp_options['gs-filter-title-text-transform'] : 'none';
            $data['filter_title_text_decoration'] = (isset($gspwp_options['gs-filter-title-text-decoration']) && $gspwp_options['gs-filter-title-text-decoration'] != '') ? $gspwp_options['gs-filter-title-text-decoration'] : 'none';
            $data['filter_title_line_height'] = (isset($gspwp_options['gs-filter-title-line-height']) && $gspwp_options['gs-filter-title-line-height'] != '') ? $gspwp_options['gs-filter-title-line-height'] : '1.2';
            $data['filter_title_letter_spacing'] = (isset($gspwp_options['gs-filter-title-letter-spacing']) && $gspwp_options['gs-filter-title-letter-spacing'] != '') ? $gspwp_options['gs-filter-title-letter-spacing'] . 'px' : '0';
            $data['filter_title_bg_color'] = (isset($gspwp_options['gs-filter-title-bg-color']) && $gspwp_options['gs-filter-title-bg-color'] != '') ? $gspwp_options['gs-filter-title-bg-color'] : 'transparent';
            $data['filter_title_bg_color_hover'] = (isset($gspwp_options['gs-filter-title-bg-color-hover']) && $gspwp_options['gs-filter-title-bg-color-hover'] != '') ? $gspwp_options['gs-filter-title-bg-color-hover'] : 'transparent';
            $data['filter_title_gap'] = (isset($gspwp_options['gs-filter-gap']) && $gspwp_options['gs-filter-gap'] != '') ? $gspwp_options['gs-filter-gap'] . 'px' : '10';
            $data['filter_padding_top'] = (isset($gspwp_options['filter_padding_top']) && $gspwp_options['filter_padding_top'] != '') ? $gspwp_options['filter_padding_top'] . 'px' : '0';
            $data['filter_padding_right'] = (isset($gspwp_options['filter_padding_right']) && $gspwp_options['filter_padding_right'] != '') ? $gspwp_options['filter_padding_right'] . 'px' : '0';
            $data['filter_padding_bottom'] = (isset($gspwp_options['filter_padding_bottom']) && $gspwp_options['filter_padding_bottom'] != '') ? $gspwp_options['filter_padding_bottom'] . 'px' : '0';
            $data['filter_padding_left'] = (isset($gspwp_options['filter_padding_left']) && $gspwp_options['filter_padding_left'] != '') ? $gspwp_options['filter_padding_left'] . 'px' : '0';


            $gspwp_less = new gspwp_lessc();
            $gspwp_less->setVariables($data);
            $style_file = GSPWP_PLUGIN_DIR . 'assets/less/style.less';

            echo '<style type="text/css" id="gspwp_dynamic_style">';

            echo esc_html($gspwp_less->compileFile($style_file) , 'gallery-showcase-pro');

            try {
                echo esc_html($gspwp_less->compile($gspwp_options['custom_css']), 'gallery-showcase-pro');
            } catch (exception $e) {
                echo esc_html($e , 'gallery-showcase-pro');
            }

            echo '</style>';
        }
    }

}

/**
 * Image Resize
 * @param $img_url, $width, $height, $crop
 * @return Return Image HTML
 * @since Gallery Showcase Pro 1.0
 */
if (!function_exists('gspwp_image_resize')) {

    function gspwp_image_resize($img_url = null, $width = "", $height = "", $crop = false) {
        if ($img_url) {
            $file_path = parse_url($img_url);
            $file_path = sanitize_text_field(wp_unslash($_SERVER['DOCUMENT_ROOT'])) . $file_path['path'];
            // Look for Multisite Path
            if (is_multisite()) {
                $img_info = pathinfo($img_url);
                $uploads_dir = wp_upload_dir();
                $file_path = $uploads_dir['path'] . '/' . $img_info['basename'];
            }
            if (!file_exists($file_path)) {
                return;
            }
            $orig_size = getimagesize($file_path);
            $image_src[0] = $img_url;
            $image_src[1] = $orig_size[0];
            $image_src[2] = $orig_size[1];
        }
        $file_info = pathinfo($file_path);
        // check if file exists
        $base_file = $file_info['dirname'] . '/' . $file_info['filename'] . '.' . $file_info['extension'];
        if (!file_exists($base_file)) {
            return;
        }

        $extension = '.' . $file_info['extension'];
        // the image path without the extension
        $no_ext_path = $file_info['dirname'] . '/' . $file_info['filename'];
        $cropped_img_path = $no_ext_path . '-' . $width . 'x' . $height . $extension;
        // checking if the file size is larger than the target size
        // if it is smaller or the same size, stop right here and return
        if ($image_src[1] > $width) {
            // the file is larger, check if the resized version already exists (for $crop = true but will also work for $crop = false if the sizes match)
            if (file_exists($cropped_img_path)) {
                $cropped_img_url = str_replace(basename($image_src[0]), basename($cropped_img_path), $image_src[0]);
                $gspwp_images = array(
                    'url' => $cropped_img_url,
                    'width' => $width,
                    'height' => $height
                );
                return $gspwp_images;
            }
            // $crop = false or no height set
            if ($crop == false OR ! $height) {
                // calculate the size proportionaly
                $proportional_size = wp_constrain_dimensions($image_src[1], $image_src[2], $width, $height);
                $resized_img_path = $no_ext_path . '-' . $proportional_size[0] . 'x' . $proportional_size[1] . $extension;
                // checking if the file already exists
                if (file_exists($resized_img_path)) {
                    $resized_img_url = str_replace(basename($image_src[0]), basename($resized_img_path), $image_src[0]);
                    $gspwp_images = array(
                        'url' => $resized_img_url,
                        'width' => $proportional_size[0],
                        'height' => $proportional_size[1]
                    );
                    return $gspwp_images;
                }
            }
            // check if image width is smaller than set width
            $img_size = getimagesize($file_path);
            if ($img_size[0] <= $width)
                $width = $img_size[0];
            // Check if GD Library installed
            if (!function_exists('imagecreatetruecolor')) {
                esc_html_e('GD Library Error: imagecreatetruecolor does not exist - please contact your webhost and ask them to install the GD library', 'gallery-showcase-pro');
                return;
            }
            // no cache files - let's finally resize it
            $image = wp_get_image_editor($file_path);

            if (!is_wp_error($image)) {
                $new_file_name = $file_info['filename'] . "-" . $width . "x" . $height . '.' . $file_info['extension'];
                $image->resize($width, $height, $crop);
                $image->save($file_info['dirname'] . '/' . $new_file_name);
            }
            $new_img_path = $file_info['dirname'] . '/' . $new_file_name;
            $new_img_size = getimagesize($new_img_path);
            $new_img = str_replace(basename($image_src[0]), basename($new_img_path), $image_src[0]);
            // resized output
            $gspwp_images = array(
                'url' => $new_img,
                'width' => $new_img_size[0],
                'height' => $new_img_size[1]
            );
            return $gspwp_images;
        }
        // default output - without resizing
        $gspwp_images = array(
            'url' => $image_src[0],
            'width' => $width,
            'height' => $height
        );
        return $gspwp_images;
    }

}

/**
 * Slider Arrow Style
 * @return Slider Arrow style options
 * @since Gallery Showcase Pro 1.0
 */
if (!function_exists('gspwp_default_values')) {

    function gspwp_default_values() {
        $default_value = array(
            'layout_type' => 'grid',
            'column_desktop' => 4,
            'column_small_desktop' => 3,
            'column_tablet' => 2,
            'column_mobile' => 1,
            'padding_top' => 0,
            'padding_right' => 0,
            'padding_bottom' => 0,
            'padding_left' => 0,
            'custom_css' => '',
            'image_size' => 'full',
            'image_width' => '500',
            'image_height' => '500',
            'image-hard-crop' => 0,
            'layout_effect' => 'apollo',
            'layout_effect_group' => 'inspirational',
            'layout_animation' => '',
            'gs-layout-bg-color' => 'rgba(75,75,75,0.8)',
            'gs-layout-border-color' => '#ffffff',
            'display_arrow' => 1,
            'gs_arrow_style' => 'style-1',
            'gs_arrow_size' => 40,
            'left_arrow_hori_pos' => 0,
            'left_arrow_vert_pos' => 1,
            'left_arrow_hori_off' => 20,
            'left_arrow_vert_off' => 0,
            'right_arrow_hori_pos' => 2,
            'right_arrow_vert_pos' => 1,
            'right_arrow_hori_off' => -20,
            'right_arrow_vert_off' => 0,
            'display_navigation' => 1,
            'gs_navigation_size' => 16,
            'nav_hori_pos' => 1,
            'nav_vert_pos' => 2,
        );
        return $default_value;
    }

}