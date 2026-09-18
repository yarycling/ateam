<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ATeam_Gallery_Pro_AJAX {

	public function filter_gallery() {
		check_ajax_referer( 'ateam_gallery_nonce', 'nonce' );

		$category = isset( $_POST['category'] ) ? sanitize_text_field( $_POST['category'] ) : '';
		$paged = isset( $_POST['paged'] ) ? intval( $_POST['paged'] ) : 1;
		$per_page = isset( $_POST['per_page'] ) ? intval( $_POST['per_page'] ) : 12;

		$shortcode = new ATeam_Gallery_Pro_Shortcode();
		
		ob_start();
		$max_pages = $shortcode->render_gallery_grid( array(
			'posts_per_page' => $per_page,
			'category'       => $category,
		), $paged, true );
		$html = ob_get_clean();

		wp_send_json_success( array(
			'html'      => $html,
			'max_pages' => $max_pages,
			'current'   => $paged
		) );
	}

	public function bulk_add_items() {
		check_ajax_referer( 'ateam_bulk_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permission denied' );
		}

		$category_id = isset( $_POST['category_id'] ) ? intval( $_POST['category_id'] ) : 0;
		$image_ids = isset( $_POST['image_ids'] ) ? array_map( 'intval', $_POST['image_ids'] ) : array();

		if ( ! $category_id || empty( $image_ids ) ) {
			wp_send_json_error( 'Missing data' );
		}

		$category = get_term( $category_id, 'gallery_category' );
		if ( ! $category || is_wp_error( $category ) ) {
			wp_send_json_error( 'Invalid category' );
		}

		$count = 1;
		$created_ids = array();

		foreach ( $image_ids as $image_id ) {
			$post_title = $category->name . ' - ' . $count;
			
			$post_params = array(
				'post_title'   => $post_title,
				'post_status'  => 'publish',
				'post_type'    => 'gallery_item',
			);

			$post_id = wp_insert_post( $post_params );

			if ( ! is_wp_error( $post_id ) ) {
				set_post_thumbnail( $post_id, $image_id );
				wp_set_post_terms( $post_id, array( $category_id ), 'gallery_category' );
				$created_ids[] = $post_id;
				$count++;
			}
		}

		wp_send_json_success( array(
			'message' => sprintf( '%d items created successfully.', count( $created_ids ) )
		) );
	}
}

