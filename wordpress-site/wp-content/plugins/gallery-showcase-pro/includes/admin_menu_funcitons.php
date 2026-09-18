<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Remove Metaboxes submitdiv & gs_layouts & core
 *
 * @version 1.0.0
 * @since   1.0.0
 */
remove_meta_box('submitdiv', 'gs_layouts', 'core');

/**
 * Add Metaboxes submitdiv
 *
 * @version 1.0.0
 * @since   1.0.0
 */
add_meta_box('submitdiv', sprintf(__('Publish', 'gallery-showcase-pro')), 'gs_layouts_submit_meta_box', 'gs_layouts', 'side', 'high');

if (!function_exists('gs_layouts_submit_meta_box')) {

    function gs_layouts_submit_meta_box() {
        global $action, $post;
        $post_type = $post->post_type;
        $post_type_object = get_post_type_object($post_type);
        $can_publish = current_user_can($post_type_object->cap->publish_posts);
        ?>
        <div class="submitbox" id="submitpost">
            <?php
            if (in_array($post->post_status, array('publish', 'future', 'private'))) {
                ?>
                <div class="minor-publishing">
                    <div class="gallery-showcase-shortcode">
                        <input type="text" value="[gallery_showcase layout='<?php echo esc_attr($post->post_name); ?>']" onclick="this.select();" readonly="readonly"/>
                    </div>
                </div>
                <?php
            }
            ?>
            <div id="major-publishing-actions">
                <?php do_action('post_submitbox_start'); ?>
                <div id="delete-action">
                    <?php
                    if (!EMPTY_TRASH_DAYS) {
                        $delete_text = __('Delete Permanently', 'gallery-showcase-pro');
                    } else {
                        $delete_text = __('Move to Trash', 'gallery-showcase-pro');
                    }
                    ?>
                    <a class="submitdelete deletion" href="<?php echo get_delete_post_link($post->ID); ?>"><?php echo esc_html($delete_text); ?></a>
                </div>
                <div id="publishing-action">
                    <span class="spinner"></span>
                    <?php
                    if (!in_array($post->post_status, array('publish', 'future', 'private')) || 0 == $post->ID) {
                        if ($can_publish) :
                            ?><input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Add Tab', 'gallery-showcase-pro') ?>" /><?php
                            submit_button(sprintf(__('Add', 'gallery-showcase-pro')), 'primary button-large', 'publish', false, array('accesskey' => 'p'));
                        endif;
                    } else {
                        ?>
                        <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Update', 'gallery-showcase-pro') ?>" />
                        <input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e('Update', 'gallery-showcase-pro'); ?>" />
                        <?php
                    }
                    ?>
                </div>
                <div class="clear"></div>
            </div>
        </div>
        <?php
    }

}