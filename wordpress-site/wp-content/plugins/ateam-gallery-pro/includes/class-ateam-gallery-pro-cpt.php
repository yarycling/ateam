<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ATeam_Gallery_Pro_CPT {

	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Gallery Items', 'Post Type General Name', 'ateam-gallery-pro' ),
			'singular_name'      => _x( 'Gallery Item', 'Post Type Singular Name', 'ateam-gallery-pro' ),
			'menu_name'          => __( 'ATeam Gallery', 'ateam-gallery-pro' ),
			'all_items'          => __( 'All Items', 'ateam-gallery-pro' ),
			'add_new_item'       => __( 'Add New Gallery Item', 'ateam-gallery-pro' ),
			'add_new'            => __( 'Add New', 'ateam-gallery-pro' ),
			'edit_item'          => __( 'Edit Item', 'ateam-gallery-pro' ),
			'update_item'        => __( 'Update Item', 'ateam-gallery-pro' ),
			'view_item'          => __( 'View Item', 'ateam-gallery-pro' ),
			'search_items'       => __( 'Search Items', 'ateam-gallery-pro' ),
		);

		$args = array(
			'label'               => __( 'gallery_item', 'ateam-gallery-pro' ),
			'description'         => __( 'Gallery items for ATeam', 'ateam-gallery-pro' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'thumbnail' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-format-gallery',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
		);
		register_post_type( 'gallery_item', $args );

		add_action( 'admin_menu', array( $this, 'add_bulk_add_page' ) );
	}

	public function add_bulk_add_page() {
		add_submenu_page(
			'edit.php?post_type=gallery_item',
			__( 'Bulk Add Items', 'ateam-gallery-pro' ),
			__( 'Bulk Add', 'ateam-gallery-pro' ),
			'manage_options',
			'ateam-bulk-add',
			array( $this, 'render_bulk_add_page' )
		);
	}

	public function render_bulk_add_page() {
		$categories = get_terms( array(
			'taxonomy'   => 'gallery_category',
			'hide_empty' => false,
		) );
		?>
		<div class="wrap ateam-bulk-wrap">
			<h1><?php _e( 'Bulk Add Gallery Items', 'ateam-gallery-pro' ); ?></h1>
			<p><?php _e( 'Select a category and multiple images to quickly populate your gallery.', 'ateam-gallery-pro' ); ?></p>
			
			<div class="card">
				<form id="ateam-bulk-add-form">
					<p>
						<label for="bulk_category"><strong><?php _e( 'Select Category:', 'ateam-gallery-pro' ); ?></strong></label><br>
						<select id="bulk_category" name="bulk_category" required>
							<option value=""><?php _e( 'Choose a Category...', 'ateam-gallery-pro' ); ?></option>
							<?php foreach ( $categories as $cat ) : ?>
								<option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					
					<p>
						<button type="button" id="ateam-select-images" class="button button-secondary"><?php _e( 'Select Images from Media Library', 'ateam-gallery-pro' ); ?></button>
					</p>
					
					<div id="ateam-selected-preview" class="ateam-bulk-preview"></div>
					
					<p>
						<button type="submit" id="ateam-process-bulk" class="button button-primary" disabled><?php _e( 'Create Gallery Items', 'ateam-gallery-pro' ); ?></button>
					</p>
				</form>
				<div id="ateam-bulk-status"></div>
			</div>
		</div>
		<?php
	}

	public function register_taxonomy() {
		$labels = array(
			'name'              => _x( 'Gallery Categories', 'taxonomy general name', 'ateam-gallery-pro' ),
			'singular_name'     => _x( 'Gallery Category', 'taxonomy singular name', 'ateam-gallery-pro' ),
			'search_items'      => __( 'Search Categories', 'ateam-gallery-pro' ),
			'all_items'         => __( 'All Categories', 'ateam-gallery-pro' ),
			'parent_item'       => __( 'Parent Category', 'ateam-gallery-pro' ),
			'parent_item_colon' => __( 'Parent Category:', 'ateam-gallery-pro' ),
			'edit_item'         => __( 'Edit Category', 'ateam-gallery-pro' ),
			'update_item'       => __( 'Update Category', 'ateam-gallery-pro' ),
			'add_new_item'      => __( 'Add New Category', 'ateam-gallery-pro' ),
			'new_item_name'     => __( 'New Category Name', 'ateam-gallery-pro' ),
			'menu_name'         => __( 'Categories', 'ateam-gallery-pro' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'gallery-category' ),
		);

		register_taxonomy( 'gallery_category', array( 'gallery_item' ), $args );
	}

	public function add_gallery_meta_boxes() {
		add_meta_box(
			'gallery_item_details',
			__( 'Gallery Item Details', 'ateam-gallery-pro' ),
			array( $this, 'render_meta_box' ),
			'gallery_item',
			'normal',
			'high'
		);
	}

	public function render_meta_box( $post ) {
		wp_nonce_field( 'gallery_meta_nonce', 'gallery_meta_nonce_field' );

		$location = get_post_meta( $post->ID, '_gallery_item_location', true );
		$event_date = get_post_meta( $post->ID, '_gallery_item_date', true );

		echo '<p><label for="gallery_location">' . __( 'Location', 'ateam-gallery-pro' ) . '</label>';
		echo '<input type="text" id="gallery_location" name="gallery_location" value="' . esc_attr( $location ) . '" class="widefat" /></p>';

		echo '<p><label for="gallery_date">' . __( 'Event Date', 'ateam-gallery-pro' ) . '</label>';
		echo '<input type="date" id="gallery_date" name="gallery_date" value="' . esc_attr( $event_date ) . '" class="widefat" /></p>';
	}

	public function save_gallery_meta( $post_id ) {
		if ( ! isset( $_POST['gallery_meta_nonce_field'] ) || ! wp_verify_nonce( $_POST['gallery_meta_nonce_field'], 'gallery_meta_nonce' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( isset( $_POST['gallery_location'] ) ) {
			update_post_meta( $post_id, '_gallery_item_location', sanitize_text_field( $_POST['gallery_location'] ) );
		}
		if ( isset( $_POST['gallery_date'] ) ) {
			update_post_meta( $post_id, '_gallery_item_date', sanitize_text_field( $_POST['gallery_date'] ) );
		}
	}

	public function add_custom_columns( $columns ) {
		$new_columns = array();
		if ( isset( $columns['cb'] ) ) {
			$new_columns['cb'] = $columns['cb'];
		}
		$new_columns['image'] = __( 'Image', 'ateam-gallery-pro' );
		$new_columns['title'] = $columns['title'];
		$new_columns['taxonomy-gallery_category'] = __( 'Categories', 'ateam-gallery-pro' );
		$new_columns['location'] = __( 'Location', 'ateam-gallery-pro' );
		$new_columns['date'] = __( 'Date', 'ateam-gallery-pro' );
		return $new_columns;
	}

	public function render_custom_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'image':
				if ( has_post_thumbnail( $post_id ) ) {
					echo get_the_post_thumbnail( $post_id, array( 50, 50 ) );
				} else {
					echo __( 'No Image', 'ateam-gallery-pro' );
				}
				break;
			case 'location':
				echo get_post_meta( $post_id, '_gallery_item_location', true );
				break;
			case 'date':
				echo get_post_meta( $post_id, '_gallery_item_date', true );
				break;
		}
	}
}

