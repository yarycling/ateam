<?php
/*
 * Plugin Name: Gallery Showcase Pro For WordPress
 * Plugin URI: https://www.techeshta.com/product/gallery-showcase-pro-for-wordpress/
 * Description: Gallery Showcase plugin allows you to manage, edit, design and create new galleries showcases or teasers.
 * Tags: Gallery Showcase Plugin, Gallery, Gallery Showcase, Image Gallery Showcase, Gallery Plugin, Gallery Shortcode, WordPress Plugin
 * Version: 1.0.4
 * Author: Techeshta
 * Author URI: https://profiles.wordpress.org/alkesh7/
 * Text Domain: gallery-showcase-pro
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 */
if (!defined('ABSPATH')) {
    exit;
}
define('GSPWP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GSPWP_PLUGIN_URL', plugins_url('/', __FILE__));
define('GSPWP_TEXTDOMAIN', 'gallery-showcase-pro');

class GalleryShowcasePro {

    /**
     * Initialize the plugin
     */
    function __construct() {

        /* Add Functions Page */
        require_once GSPWP_PLUGIN_DIR . 'includes/functions.php';

        /* Shortcode Page */
        require_once GSPWP_PLUGIN_DIR . 'includes/shortcode.php';

        /* Create Posttypes */
        add_action('init', array($this, 'gspwp_create_posttypes'), 1);

        /* Add Image Size */
        add_action('init', array($this, 'gspwp_add_image_size'), 1);

        /* Save Layout Massage */
        add_action('post_updated_messages', array($this, 'gspwp_updated_messages'), 1);

        /* Add Metaboxes */
        add_action('add_meta_boxes', array($this, 'gspwp_add_metaboxes'), 2);

        /* Save Metaboxes */
        add_action('save_post', array($this, 'gspwp_save_metadata'), 3, 2);

        /* Load Admin Scripts And Styles For Admin */
        add_action('admin_enqueue_scripts', array($this, 'gspwp_enqueue_admin_scripts'), 4);

        /* Load Admin Scripts And Styles For Front */
        add_action('wp_enqueue_scripts', array($this, 'gspwp_enqueue_front_scripts'), 5);

        /* Admin Menu Functions */
        add_action('admin_menu', array($this, 'gspwp_admin_menu_function'), 6);

        /* Admin Notices Functions */
        add_action('admin_notices', array($this, 'gspwp_admin_notices'), 7);

        /* Manage Posts Columns Functions */
        add_filter('manage_posts_columns', array($this, 'gspwp_manage_posts_columns'), 8, 1);

        /* Manage Posts Custom Column Functions */
        add_action('manage_posts_custom_column', array($this, 'gspwp_manage_posts_custom_column'), 9, 2);

        /* Gallery Showcase Images Ajax Handler Functions */
        add_action('wp_ajax_nopriv_gspwp_gallery_images_ajax', array($this, 'gspwp_gallery_images_ajax'), 10);
        add_action('wp_ajax_gspwp_gallery_images_ajax', array($this, 'gspwp_gallery_images_ajax'), 10);

        /* Gallery Showcase CPT Related Taxonomy Ajax Handler Functions */
        add_action('wp_ajax_nopriv_gspwp_cpt_related_taxonomy', array($this, 'gspwp_cpt_related_taxonomy'), 10);
        add_action('wp_ajax_gspwp_cpt_related_taxonomy', array($this, 'gspwp_cpt_related_taxonomy'), 10);

        /* Add Media Buttons */
        add_filter('media_buttons', array($this, 'gspwp_media_buttons_context'), 12);

        /* Add Media Buttons HTML */
        add_action('admin_footer', array($this, 'gspwp_admin_footer'), 13);

        /* Plugin Active Register Activation */
        register_activation_hook(__FILE__, array($this, 'gallery_showcase_pro_activation'));
    }

    /**
     * Add action on plugin activation
     *
     * @version 1.0.0
     * @since   1.0.0
     */
    function gallery_showcase_pro_activation() {

        // Deactivate Gallery Showcase For WordPress
        deactivate_plugins('gallery-showcase/gallery-showcase.php');    
        
    }

    /**
     * Create Post Types
     *
     * @version 1.0.0
     * @since   1.0.0
     */
    function gspwp_create_posttypes() {
        include_once GSPWP_PLUGIN_DIR . 'includes/register_posttypes.php';
    }

    /**
     * Add Image Size
     *
     * @version 1.0.0
     * @since   1.0.0
     */
    function gspwp_add_image_size() {
        add_image_size('gspwp_thumbnail_size', '200', '200', false);
        add_image_size('gspwp_feature_image_size', '800', '400', false);
    }

    /**
     * Sava and update admin notice
     *
     * @version 1.0.0
     * @since   1.0.0
     */
    function gspwp_updated_messages($messages) {
        if (get_post_type() == 'gs_layouts') {
            $messages['post'][1] = esc_html__('Showcase Updated.', 'gallery-showcase-pro');
            $messages['post'][4] = esc_html__('Showcase Updated.', 'gallery-showcase-pro');
            $messages['post'][6] = esc_html__('Showcase Added.', 'gallery-showcase-pro');
            $messages['post'][7] = esc_html__('Showcase Saved.', 'gallery-showcase-pro');
            $messages['post'][8] = esc_html__('Showcase Submitted.', 'gallery-showcase-pro');
            $messages['post'][10] = esc_html__('Showcase Draft Updated.', 'gallery-showcase-pro');
        }
        return $messages;
    }

    /**
     * Add Metaboxes
     *
     * @version 1.0.0
     * @since   1.0.0
     */
    function gspwp_add_metaboxes() {
        include_once GSPWP_PLUGIN_DIR . 'includes/add_metaboxes.php';
    }

    /**
     * Save Metadata
     *
     * @version 1.0.0
     * @since   1.0.0
     */
    function gspwp_save_metadata($post_id, $post) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        $post_type = get_post_type($post_id);

        if ($post_type == 'gs_layouts') {
            if (!isset($_POST['gspwp_layout_options_nonce']) || !isset($_POST['gspwp_gallery_image_information'])) {
                return;
            }

            if (isset($_POST['gs_gallery_images'])) {
                $portdesign_gallery_images = sanitize_text_field(wp_unslash($_POST['gs_gallery_images']));
                update_post_meta($post_id, 'gs_gallery_images', $portdesign_gallery_images);
            }

            if (isset($_POST['gs_gallery_details'])) {
                $gspwp_gallery_details = wp_unslash($_POST['gs_gallery_details']);
                // Sanitizing here:
                array_walk($gspwp_gallery_details, function($value, $key) {
                    $gspwp_gallery_details['title'] = sanitize_text_field($value['title']);
                    $gspwp_gallery_details['desc'] = sanitize_text_field($value['desc']);
                });
                update_post_meta($post_id, 'gs_gallery_details', $gspwp_gallery_details);
            }
            if (isset($_POST['options'])) {
                $gspwp_options = $_POST['options'];
                // Sanitizing here:
                array_walk($gspwp_options, function($value, $key) {
                    $gspwp_options[$key] = sanitize_text_field($value);
                });
                update_post_meta($post_id, 'gs_optoins', $gspwp_options);
            }
        }
    }

    /**
     * Enqueue Admin Scripts
     *
     * @version 1.0.0
     * @since   1.0.0
     */
    function gspwp_enqueue_admin_scripts() {
        if (get_post_type() == 'gs_gallery' || get_post_type() == 'gs_layouts') {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_style(array('wp-jquery-ui', 'wp-jquery-ui-dialog', 'wp-jquery-ui-core'));
            wp_enqueue_style('gspwp-custom-admin-style', GSPWP_PLUGIN_URL . 'includes/css/gspwp-admin-custom-style.css', array(), '1.0', false);
            wp_enqueue_style('gspwp-admin-style', GSPWP_PLUGIN_URL . 'includes/css/admin_style.css', array(), '1.0', false);

            wp_enqueue_media();
            wp_enqueue_script('gspwp-admin-custom-script', GSPWP_PLUGIN_URL . 'includes/js/admin_custom_script.js', array('jquery', 'wp-color-picker'), 1.0, false);
            wp_enqueue_script('gspwp-admin-script', GSPWP_PLUGIN_URL . 'includes/js/admin_script.js', array('jquery', 'wp-color-picker', 'jquery-ui-core', 'jquery-ui-dialog'), 1.0, true);

            $gspwp_script_translations = array(
                'select_gallery' => esc_html__('Select Images for Gallery', 'gallery-showcase-pro'),
                'custom_field_remove' => esc_html__('Require atleast one field', 'gallery-showcase-pro'),
                'confirmation' => esc_html__('Are you sure you want to remove image?', 'gallery-showcase-pro'),
                'remove_image' => esc_html__('Remove Image', 'gallery-showcase-pro'),
                'upload_image' => esc_html__('Upload Image', 'gallery-showcase-pro'),
                'pagination_type' => esc_html__('Pagination Type', 'gallery-showcase-pro'),
                'set_pagination_type' => esc_html__("Set Pgination Type", 'gallery-showcase-pro'),
                'set_arrow_style' => esc_html__("Select Arrow Style", 'gallery-showcase-pro'),
                'set_navigation_style' => esc_html__("Select Navigation Style", 'gallery-showcase-pro'),
                'close' => esc_html__("Close", 'gallery-showcase-pro'),
            );

            wp_localize_script('gspwp-admin-script', 'gspwp_script_translations', $gspwp_script_translations);
        }
    }

    /**
     * Enqueue Front Scripts
     *
     * @version 1.0.0
     * @since   1.0.0
     */
    function gspwp_enqueue_front_scripts() {
        
        wp_register_style('gspwp-inspirational-style', GSPWP_PLUGIN_URL . 'assets/css/inspirational.css', array(), '1.0', false);
        wp_enqueue_style('gspwp-inspirational-style');
        wp_enqueue_style('gspwp-custom-style', GSPWP_PLUGIN_URL . 'assets/css/gspwp-custom-style.css', array(), '1.0', false);
        wp_enqueue_style('gspwp-front-style', GSPWP_PLUGIN_URL . 'assets/css/style.css', array(), '1.0', false);

        wp_enqueue_script('jquery');
        wp_enqueue_script('gspwp-less-script', GSPWP_PLUGIN_URL . 'assets/less/less.min.js', array('jquery'), 1.0, false);

        wp_enqueue_script('gspwp-owl-script', GSPWP_PLUGIN_URL . 'assets/js/owl.carousel.min.js', array('jquery'), 1.0, false);
        wp_enqueue_script('gspwp-fancybox-script', GSPWP_PLUGIN_URL . 'assets/js/jquery.fancybox.min.js', array('jquery'), 1.0, false);

        wp_enqueue_script('jquery-masonry', array('jquery'));
        wp_enqueue_script('gspwp-script', GSPWP_PLUGIN_URL . 'assets/js/script.js', array('jquery', 'jquery-masonry'), 1.0, false);

    }

    /**
     * Add Admin Menu
     *
     * @version 1.0.0
     * @since   1.0.0
     */
    function gspwp_admin_menu_function() {
        include_once GSPWP_PLUGIN_DIR . 'includes/admin_menu_function.php';
    }

    /**
     * Admin Notices
     *
     * @version 1.0.0
     * @since   1.0.0
     */
    function gspwp_admin_notices() {
        include_once GSPWP_PLUGIN_DIR . 'includes/admin_notices.php';
    }

    /**
     * Manage Posts Columns
     *
     * @version 1.0.0
     * @since   1.0.0
     * @return  string
     */
    function gspwp_manage_posts_columns($columns) {
        if (get_post_type() == 'gs_layouts') {
            foreach ($columns as $key => $title) {
                if ($key == 'date') {
                    $newColumnOrder['shortcode'] = esc_html__('Layout Shortcode', 'gallery-showcase-pro');
                }
                $newColumnOrder[$key] = $title;
            }
        } else {
            $newColumnOrder = $columns;
        }
        return $newColumnOrder;
    }

    /**
     * Manage Posts Custom Column
     *
     * @version 1.0.0
     * @since   1.0.0
     */
    function gspwp_manage_posts_custom_column($column, $post_id) {
        if (get_post_type() == 'gs_layouts') {
            if ($column == 'shortcode') {
                $post = get_post($post_id);
                ?><input type="text" value="<?php echo esc_attr('[gallery_showcase layout=\'' . sanitize_text_field($post->post_name) . '\']'); ?>" onclick="this.select();" readonly="readonly" /><?php
            }
        }
    }

    /**
     * CPT Related Taxonomy
     *
     * @version 1.0.0
     * @since   1.0.0
     * @return  string
     */
    function gspwp_cpt_related_taxonomy(){
        $custom_post_type = (!empty($_POST['custom_post_type'])) ? sanitize_text_field($_POST['custom_post_type']) : '';
        
        $taxonomies_array = get_object_taxonomies( $custom_post_type );
        $html = "";
        if(!empty($taxonomies_array)) {

            foreach ($taxonomies_array as $taxonomy) {
                $taxonomy_details = get_taxonomy( $taxonomy );
                
                if($taxonomy != "post_format"){

                    $html .= '<option value="' . esc_attr($taxonomy) . '" >' .  esc_html( $taxonomy_details->label ) . '</option>';
                    
                }
            }   
        }

        echo $html;
        exit;
    }

    /**
     * Gallery Images Ajax
     *
     * @version 1.0.0
     * @since   1.0.0
     * @return  HTML
     */
    function gspwp_gallery_images_ajax() {
        $postid = sanitize_text_field(wp_unslash($_POST['postid']));
        $mediadata = sanitize_text_field(wp_unslash($_POST['mediadata']));

        $gallery_images = explode(',', $mediadata);

        $gallery_meta_data = get_post_custom($postid);
        $gspwp_gallery_details = '';
        if (isset($gallery_meta_data['gs_gallery_details'])) {
            $gspwp_gallery_details = unserialize($gallery_meta_data['gs_gallery_details'][0]);
        }

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
                        <textarea id="gspwp_gallery_desc_<?php echo esc_attr($value); ?>" name="gs_gallery_details[<?php echo esc_attr($value); ?>][desc]"><?php echo esc_textarea($desc); ?></textarea>
                    </p>
                </div>
            </div>
            <?php
        }
        exit();
    }

    /**
     * Media Buttons Context
     *
     * @version 1.0.0
     * @since   1.0.0
     * @return  string
     */
    function gspwp_media_buttons_context($context) {
        global $pagenow;
        if (in_array($pagenow, array('post.php', 'page.php', 'post-new.php', 'post-edit.php'))) {
            $context .= '<a href="#TB_inline?width=650&height=300&inlineId=choose-gs-shortcode" class="thickbox button" title="' .
                    esc_attr('Select Gallery Showcase Shortcode' , 'gallery-showcase-pro') .
                    '"><span class="wp-media-buttons-icon dashicons dashicons-before dashicons-format-gallery" ></span> ' .
                    esc_html__('Gallery Showcase', 'gallery-showcase-pro') . '</a>';
        }
        return $context;
    }

    /**
     * Admin Footer add HTML
     *
     * @version 1.0.0
     * @since   1.0.0
     */
    function gspwp_admin_footer() {
        global $pagenow;
        if (in_array($pagenow, array('post.php', 'page.php', 'post-new.php', 'post-edit.php'))) {
            $argc = array(
                'post_type' => 'gs_layouts',    
                'post_status' => 'publish',
                'posts_per_page' => -1
            );
            $posts = get_posts($argc);
            ?>
            <div id="choose-gs-shortcode" style="display: none;">
                <div class="wrap">
                    <?php
                    if (!empty($posts)) {
                        echo "<h3>" . esc_html__("Insert Gallery Showcase Shortcode", 'gallery-showcase-pro') . "</h3>";
                        echo "<select id='gspwp-select'>";
                        echo "<option disabled=disabled>" . esc_html__("Choose shortcode", 'gallery-showcase-pro') . "</option>";
                        foreach ($posts as $post) {
                            if ($post->post_title != '') {
                                $name = $post->post_title;
                                echo '<option value="'. esc_attr($post->post_name) .'" >' . esc_html( $name ) . '</option>';
                            }
                        }
                        echo "</select>";
                        echo "<button class='button primary' id='insertGalleryDesignerShortcode'>" . esc_html__("Insert Shortcode", 'gallery-showcase-pro') . "</button>";
                    } else {
                        echo esc_html__("No shortcodes found", 'gallery-showcase-pro');
                    }
                    ?>
                </div>
            </div>
            <script type="text/javascript">
                jQuery(document).ready(function () {
                    jQuery('#insertGalleryDesignerShortcode').on('click', function () {
                        var id = jQuery('#gspwp-select option:selected').val();
                        window.send_to_editor('[gallery_showcase layout="' + id + '"]');
                        tb_remove();
                    });
                });
            </script>
            <?php
        }
    }
}

new GalleryShowcasePro();