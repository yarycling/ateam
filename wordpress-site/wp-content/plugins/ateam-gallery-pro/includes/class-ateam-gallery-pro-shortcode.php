<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ATeam_Gallery_Pro_Shortcode {

	public function render_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'posts_per_page' => 12,
			'category'       => '',
		), $atts, 'ateam_gallery' );

		ob_start();

		// $this->render_hero_section();
		$this->render_category_navigation();
		
		echo '<div id="ateam-gallery-container" class="ateam-gallery-container" data-per-page="' . esc_attr( $atts['posts_per_page'] ) . '">';
		$this->render_gallery_grid( $atts );
		echo '</div>';

		$this->render_load_more_button();
		$this->render_lightbox_modal();

		return ob_get_clean();
	}

	private function render_hero_section() {
		?>
		<section class="ateam-hero">
			<div class="ateam-hero-overlay">
				<h1 class="ateam-hero-title">A-Team Live in Action</h1>
			</div>
		</section>
		<?php
	}

	private function render_category_navigation() {
		$terms = get_terms( array(
			'taxonomy'   => 'gallery_category',
			'hide_empty' => true,
			'parent'     => 0, // Top level
		) );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return;
		}

		?>
		<nav class="ateam-gallery-nav">
			<ul class="ateam-nav-tabs">
				<li class="ateam-nav-item active" data-id="all">
					<a href="#" class="ateam-nav-link">All</a>
				</li>
				<?php foreach ( $terms as $term ) : ?>
					<li class="ateam-nav-item has-children" data-id="<?php echo esc_attr( $term->term_id ); ?>">
						<a href="#" class="ateam-nav-link"><?php echo esc_html( $term->name ); ?></a>
						<?php
						$children = get_terms( array(
							'taxonomy'   => 'gallery_category',
							'hide_empty' => true,
							'parent'     => $term->term_id,
						) );
						if ( ! empty( $children ) && ! is_wp_error( $children ) ) :
							?>
							<ul class="ateam-dropdown">
								<?php foreach ( $children as $child ) : ?>
									<li class="ateam-dropdown-item" data-id="<?php echo esc_attr( $child->term_id ); ?>">
										<a href="#"><?php echo esc_html( $child->name ); ?></a>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<?php
	}

	public function render_gallery_grid( $atts, $paged = 1, $ajax = false ) {
		$args = array(
			'post_type'      => 'gallery_item',
			'posts_per_page' => $atts['posts_per_page'],
			'paged'          => $paged,
		);

		if ( ! empty( $atts['category'] ) && $atts['category'] !== 'all' ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'gallery_category',
					'field'    => 'term_id',
					'terms'    => $atts['category'],
				),
			);
		}

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			if ( ! $ajax ) {
				echo '<div class="ateam-grid">';
			}
			while ( $query->have_posts() ) {
				$query->the_post();
				$this->render_gallery_item( get_the_ID() );
			}
			if ( ! $ajax ) {
				echo '</div>';
			}
			wp_reset_postdata();
		} else {
			if ( ! $ajax ) {
				echo '<p class="ateam-no-posts">No items found.</p>';
			}
		}

		return $query->max_num_pages;
	}

	private function render_gallery_item( $post_id ) {
		$thumbnail_id = get_post_thumbnail_id( $post_id );
		$full_url = wp_get_attachment_image_url( $thumbnail_id, 'large' );
		$location = get_post_meta( $post_id, '_gallery_item_location', true );
		$date = get_post_meta( $post_id, '_gallery_item_date', true );
		$terms = wp_get_post_terms( $post_id, 'gallery_category' );
		$cat_id = ! empty( $terms ) && ! is_wp_error( $terms ) ? $terms[0]->term_id : 0;
		?>
		<div class="ateam-card" data-id="<?php echo esc_attr( $post_id ); ?>" data-category="<?php echo esc_attr( $cat_id ); ?>" data-image="<?php echo esc_url( $full_url ); ?>" data-title="<?php echo esc_attr( get_the_title() ); ?>" data-desc="<?php echo esc_attr( get_the_content() ); ?>" data-location="<?php echo esc_attr( $location ); ?>" data-date="<?php echo esc_attr( $date ); ?>">
			<div class="ateam-card-inner">
				<?php echo wp_get_attachment_image( $thumbnail_id, 'ateam-gallery-thumb', false, array( 'class' => 'ateam-card-img', 'loading' => 'lazy' ) ); ?>
				<div class="ateam-card-overlay">
					<div class="ateam-card-content">
						<h3 class="ateam-card-title"><?php the_title(); ?></h3>
						<?php if ( $location ) : ?>
							<span class="ateam-card-location"><i class="dashicons dashicons-location"></i> <?php echo esc_html( $location ); ?></span>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	private function render_load_more_button() {
		echo '<div class="ateam-load-more-wrapper">';
		echo '<button id="ateam-load-more" class="ateam-btn-primary">Load More</button>';
		echo '</div>';
	}

	private function render_lightbox_modal() {
		?>
		<div id="ateam-lightbox" class="ateam-lightbox">
			<span class="ateam-close">&times;</span>
			<div class="ateam-lightbox-content">
				<div class="ateam-lightbox-media">
					<img id="ateam-lightbox-img" src="" alt="">
					<button class="ateam-prev"><</button>
					<button class="ateam-next">></button>
				</div>
				<div class="ateam-lightbox-info">
					<h2 id="ateam-lightbox-title"></h2>
					<p id="ateam-lightbox-location"></p>
					
					<div class="ateam-lightbox-related-wrapper">
						<h3>In this Category</h3>
						<div id="ateam-lightbox-related" class="ateam-lightbox-related"></div>
					</div>

					<div id="ateam-lightbox-desc"></div>
				</div>
			</div>
		</div>
		<?php
	}
}

