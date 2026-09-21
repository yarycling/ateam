<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ATV_Admin {
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu_pages' ) );
		add_action( 'wp_ajax_atv_bulk_add_videos', array( $this, 'bulk_add_videos' ) );
		add_action( 'add_meta_boxes_atv_video', array( $this, 'add_video_metabox' ) );
		add_action( 'save_post_atv_video', array( $this, 'save_video_metabox' ) );
		add_action( 'wp_ajax_atv_save_captured_thumb', array( $this, 'save_captured_thumb' ) );
	}

	public function save_captured_thumb() {
		check_ajax_referer( 'atv_vg_nonce', 'nonce' );

		$post_id = intval( $_POST['post_id'] );
		$image_data = $_POST['image']; // base64

		if ( ! $post_id || ! $image_data ) {
			wp_send_json_error( 'Invalid data' );
		}

		// Remove header of dataurl
		$image_data = str_replace( 'data:image/jpeg;base64,', '', $image_data );
		$image_data = str_replace( ' ', '+', $image_data );
		$image_binary = base64_decode( $image_data );

		$filename = 'video-thumb-' . $post_id . '.jpg';
		$upload = wp_upload_bits( $filename, null, $image_binary );

		if ( $upload['error'] ) {
			wp_send_json_error( $upload['error'] );
		}

		$wp_filetype = wp_check_filetype( $upload['file'], null );
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title'     => sanitize_file_name( $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);

		$attach_id = wp_insert_attachment( $attachment, $upload['file'], $post_id );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		set_post_thumbnail( $post_id, $attach_id );

		wp_send_json_success( array(
			'url' => $upload['url'],
			'thumb_id' => $attach_id
		) );
	}

	public function add_menu_pages() {
		add_submenu_page(
			'edit.php?post_type=atv_video',
			'Bulk Add',
			'Bulk Add',
			'manage_options',
			'atv-bulk-add',
			array( $this, 'render_bulk_add' )
		);
	}

	public function render_bulk_add() {
		$categories = get_terms( array(
			'taxonomy' => 'atv_category',
			'hide_empty' => false,
		) );
		?>
		<div class="wrap atv-admin-wrap">
			<h1>Bulk Add Videos to Category</h1>
			<div class="atv-bulk-form">
				<div class="form-group">
					<label for="atv-target-category">Select Category:</label>
					<select id="atv-target-category">
						<option value="">-- Choose Category --</option>
						<?php foreach ( $categories as $cat ) : ?>
							<option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="form-group">
					<button type="button" id="atv-select-videos" class="button button-primary">Select Videos from Media Library</button>
				</div>
				<div id="atv-selected-preview" class="atv-grid-preview"></div>
				<div class="form-actions" style="display:none;">
					<button type="button" id="atv-save-bulk" class="button button-hero button-primary">Save Videos to Category</button>
					<span id="atv-bulk-progress" style="margin-left:15px; font-weight:600; color:#2271b1;"></span>
				</div>
			</div>
			<!-- Hidden helper elements for capture -->
			<video id="atv-helper-video" style="display:none;" crossOrigin="anonymous"></video>
			<canvas id="atv-helper-canvas" style="display:none;"></canvas>
		</div>
		<?php
	}

	public function bulk_add_videos() {
		check_ajax_referer( 'atv_vg_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permission denied' );
		}

		$category_id = intval( $_POST['category_id'] );
		$attachment_ids = array_map( 'intval', $_POST['attachment_ids'] );

		if ( ! $category_id || empty( $attachment_ids ) ) {
			wp_send_json_error( 'Missing data' );
		}

		$created_posts = array();
		foreach ( $attachment_ids as $attachment_id ) {
			$title = get_the_title( $attachment_id );
			$attachment = get_post( $attachment_id );
			$description = $attachment ? $attachment->post_content : '';
			
			$post_id = wp_insert_post( array(
				'post_title'   => $title,
				'post_content' => $description,
				'post_status'  => 'publish',
				'post_type'    => 'atv_video',
			) );

			if ( $post_id ) {
				update_post_meta( $post_id, '_atv_attachment_id', $attachment_id );
				wp_set_post_terms( $post_id, array( $category_id ), 'atv_category' );
				
				// Try to get existing generated thumbnail
				$thumb_id = get_post_meta( $attachment_id, '_thumbnail_id', true );
				if ( $thumb_id ) {
					set_post_thumbnail( $post_id, $thumb_id );
				}

				$created_posts[] = array(
					'post_id' => $post_id,
					'video_url' => wp_get_attachment_url( $attachment_id )
				);
			}
		}

		wp_send_json_success( array(
			'message' => 'Videos added! Now capturing thumbnails...',
			'posts'   => $created_posts
		) );
	}

	public function add_video_metabox() {
		add_meta_box(
			'atv_video_details',
			'Video Attachment',
			array( $this, 'render_video_metabox' ),
			'atv_video',
			'normal',
			'high'
		);
	}

	public function render_video_metabox( $post ) {
		$attachment_id = get_post_meta( $post->ID, '_atv_attachment_id', true );
		$video_url = $attachment_id ? wp_get_attachment_url( $attachment_id ) : '';
		wp_nonce_field( 'atv_video_details', 'atv_video_details_nonce' );
		?>
		<div class="atv-metabox-field">
			<label for="atv-video-description"><strong>Video Description:</strong></label>
			<textarea id="atv-video-description" name="atv_video_description" class="widefat" rows="5" style="margin-top:8px;"><?php echo esc_textarea( $post->post_content ); ?></textarea>
			<p class="description">This description appears in the video lightbox/player details.</p>
		</div>
		<div class="atv-metabox-field">
			<label>Current Video URL:</label>
			<input type="text" class="widefat" readonly value="<?php echo esc_url( $video_url ); ?>">
			<p class="description">This video is linked to Media Library item #<?php echo esc_html( $attachment_id ); ?>.</p>
		</div>
		<div class="atv-metabox-field" style="margin-top:20px;">
			<label>Thumbnail Preview:</label>
			<div style="margin-top:10px;">
				<?php if ( has_post_thumbnail( $post ) ) : ?>
					<?php echo get_the_post_thumbnail( $post, 'medium', array( 'style' => 'max-width:300px; height:auto; border-radius:8px;' ) ); ?>
				<?php else : ?>
					<div style="background:#eee; width:300px; height:168px; display:flex; align-items:center; justify-content:center; color:#666; border-radius:8px;">No Thumbnail Set</div>
				<?php endif; ?>
			</div>
			<p class="description">To change the thumbnail, use the <strong>Featured Image</strong> section on the right side of this page.</p>
		</div>
		<div class="atv-metabox-field" style="margin-top:20px; border-top:1px solid #eee; padding-top:20px;">
			<label>Automatic Capture:</label>
			<div style="margin-top:10px;">
				<button type="button" id="atv-capture-frame" class="button button-secondary" data-video-url="<?php echo esc_url( $video_url ); ?>" data-post-id="<?php echo $post->ID; ?>">Capture Frame at 10s</button>
				<span id="atv-capture-status" style="margin-left:10px; color:#666;"></span>
			</div>
			<p class="description">This will try to grab a snapshot of the actual video at the 10-second mark using your browser.</p>
			<!-- Hidden helper elements -->
			<video id="atv-helper-video" style="display:none;" crossOrigin="anonymous"></video>
			<canvas id="atv-helper-canvas" style="display:none;"></canvas>
		</div>
		<?php
	}

	public function save_video_metabox( $post_id ) {
		if ( ! isset( $_POST['atv_video_details_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['atv_video_details_nonce'] ) ), 'atv_video_details' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST['atv_video_description'] ) ) {
			return;
		}

		$description = wp_kses_post( wp_unslash( $_POST['atv_video_description'] ) );

		remove_action( 'save_post_atv_video', array( $this, 'save_video_metabox' ) );
		wp_update_post( array(
			'ID' => $post_id,
			'post_content' => $description,
		) );
		add_action( 'save_post_atv_video', array( $this, 'save_video_metabox' ) );
	}
}
