<?php

/**
 * Admin notice for featured image
 *
 * @version 1.0.0
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if (get_transient("has_post_thumbnail") == "no") {
    echo "<div id='message' class='error'><p><strong>" . esc_html__('You must select Featured Image. Your Post is saved but it can not be published.', 'gallery-showcase-pro') . "</strong></p></div>";
    delete_transient("has_post_thumbnail");
}