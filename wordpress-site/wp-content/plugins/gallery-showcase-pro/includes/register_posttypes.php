<?php

/**
 * Create Posttypes
 *
 * @version 1.0.0
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$layouts_labels = array(
    'name' => esc_html__('Gallery Showcase', 'gallery-showcase-pro'),
    'singular_name' => esc_html__('Gallery Showcase', 'gallery-showcase-pro'),
    'menu_name' => esc_html__('Gallery Showcase', 'gallery-showcase-pro'),
    'name_admin_bar' => esc_html__('Gallery Showcase', 'gallery-showcase-pro'),
    'add_new' => esc_html__('Add New', 'gallery-showcase-pro'),
    'add_new_item' => esc_html__('Add New Showcase', 'gallery-showcase-pro'),
    'new_item' => esc_html__('New Showcase', 'gallery-showcase-pro'),
    'edit_item' => esc_html__('Edit Showcase', 'gallery-showcase-pro'),
    'view_item' => esc_html__('View Showcases', 'gallery-showcase-pro'),
    'all_items' => esc_html__('All Showcases', 'gallery-showcase-pro'),
    'search_items' => esc_html__('Search Showcase', 'gallery-showcase-pro'),
    'parent_item_colon' => esc_html__('Parent Showcase', 'gallery-showcase-pro'),
    'not_found' => esc_html__('No Gallery Showcase found', 'gallery-showcase-pro'),
    'not_found_in_trash' => esc_html__('No Gallery Showcase found in Trash', 'gallery-showcase-pro'),
);

$layouts_args = array(
    'labels' => apply_filters('gs_layouts_post_labels', $layouts_labels),
    'public' => false,
    'publicly_queryable' => false,
    'show_ui' => true,
    'query_var' => false,
    'rewrite' => false,
    'menu_icon' => apply_filters('gs_gallery_menu_icon', 'dashicons-format-gallery'),
    'capability_type' => 'post',
    'has_archive' => false,
    'hierarchical' => false,
    'supports' => apply_filters('gs_layouts_post_supports', array('title')),
);

register_post_type('gs_layouts', apply_filters('gs_layouts_args', $layouts_args));