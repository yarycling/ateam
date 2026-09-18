<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ATV_Frontend {
	public function __construct() {
		add_shortcode( 'atv_video_gallery', array( $this, 'render_gallery' ) );
		add_shortcode( 'av_video_gallery', array( $this, 'render_gallery' ) );
		add_action( 'wp_ajax_atv_get_category_videos', array( $this, 'get_category_videos' ) );
		add_action( 'wp_ajax_nopriv_atv_get_category_videos', array( $this, 'get_category_videos' ) );
		add_action( 'wp_ajax_atv_track_view', array( $this, 'track_view' ) );
		add_action( 'wp_ajax_nopriv_atv_track_view', array( $this, 'track_view' ) );
	}

	public function track_view() {
		$post_id = intval( $_POST['post_id'] );
		if ( $post_id ) {
			$views = get_post_meta( $post_id, '_atv_views', true );
			$views = $views ? intval( $views ) + 1 : 1;
			update_post_meta( $post_id, '_atv_views', $views );
			wp_send_json_success( $views );
		}
		wp_send_json_error();
	}

	public function format_views( $views ) {
		if ( $views >= 1000000 ) return round( $views / 1000000, 1 ) . 'M';
		if ( $views >= 1000 ) return round( $views / 1000, 1 ) . 'k';
		return $views;
	}

	public function get_category_videos() {
		$cat_id = intval( $_POST['cat_id'] );
		$videos = new WP_Query( array(
			'post_type' => 'atv_video',
			'posts_per_page' => -1,
			'tax_query' => array(
				array(
					'taxonomy' => 'atv_category',
					'field' => 'term_id',
					'terms' => $cat_id,
				),
			),
		) );

		$result = array();
		if ( $videos->have_posts() ) {
			while ( $videos->have_posts() ) {
				$videos->the_post();
				$views = get_post_meta( get_the_ID(), '_atv_views', true ) ?: 0;
				$result[] = array(
					'id' => get_the_ID(),
					'title' => get_the_title(),
					'desc' => get_the_content(),
					'video_url' => wp_get_attachment_url( $attachment_id ),
					'thumbnail' => get_the_post_thumbnail_url( get_the_ID(), 'medium' ) ?: ATV_VG_URL . 'assets/images/video-placeholder.jpg',
					'cat_id' => $cat_id,
					'views' => $this->format_views( $views ),
					'date' => human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . ' ago'
				);
			}
			wp_reset_postdata();
		}

		wp_send_json_success( $result );
	}

	public function render_gallery( $atts ) {
		$categories = get_terms( array(
			'taxonomy' => 'atv_category',
			'hide_empty' => true,
		) );

		ob_start();
		?>
		<div class="atv-gallery-container dark-theme" id="atv-gallery-root">
			<?php if ( empty( $categories ) ) : ?>
				<div class="atv-no-content">
					<p>No videos found. Please add categories and assign videos in the admin panel.</p>
				</div>
			<?php else : ?>
				<!-- Category Tabs (Chips) -->
				<nav class="atv-tabs-container">
					<div class="atv-tabs-scroll">
						<button class="atv-tab active" data-filter="all">All</button>
						<?php foreach ( $categories as $cat ) : ?>
							<button class="atv-tab" data-filter="<?php echo esc_attr( $cat->term_id ); ?>">
								<?php echo esc_html( $cat->name ); ?>
							</button>
						<?php endforeach; ?>
					</div>
				</nav>

				<!-- Gallery Layout Wrapper -->
				<div class="atv-gallery-layout">
					
					<!-- Main Content Area -->
					<div class="atv-main-content">
						<div class="atv-video-grid-unified" id="atv-main-grid">
							<?php
							$all_videos = new WP_Query( array(
								'post_type' => 'atv_video',
								'posts_per_page' => -1,
								'orderby' => 'date',
								'order' => 'DESC'
							) );

							$favicon = get_site_icon_url( 32 ) ?: ATV_VG_URL . 'assets/images/video-placeholder.jpg';

							if ( $all_videos->have_posts() ) :
								while ( $all_videos->have_posts() ) : $all_videos->the_post();
									$attachment_id = get_post_meta( get_the_ID(), '_atv_attachment_id', true );
									$video_url = wp_get_attachment_url( $attachment_id );
									$thumbnail = get_the_post_thumbnail_url( get_the_ID(), 'medium' ) ?: ATV_VG_URL . 'assets/images/video-placeholder.jpg';
									$views = get_post_meta( get_the_ID(), '_atv_views', true ) ?: 0;
									$time_ago = human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . ' ago';
									
									$video_cats = wp_get_post_terms( get_the_ID(), 'atv_category', array( 'fields' => 'ids' ) );
									$cat_data = ! is_wp_error( $video_cats ) ? implode( ',', $video_cats ) : '';
									$cat_names = wp_get_post_terms( get_the_ID(), 'atv_category', array( 'fields' => 'names' ) );
									$cat_display = ! is_wp_error( $cat_names ) && ! empty( $cat_names ) ? $cat_names[0] : '';
									?>
									<article class="atv-video-card" 
											 data-id="<?php the_ID(); ?>" 
											 data-video-url="<?php echo esc_url( $video_url ); ?>"
											 data-title="<?php echo esc_attr( get_the_title() ); ?>"
											 data-desc="<?php echo esc_attr( get_the_content() ); ?>"
											 data-categories="<?php echo esc_attr( $cat_data ); ?>">
									<div class="atv-video-thumb-wrapper">
										<img src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php the_title(); ?>">
										<div class="atv-play-overlay">
											<div class="play-icon-container">
												<svg viewBox="0 0 68 48" width="68" height="48">
													<path class="ytp-large-play-button-bg" d="M66.52,7.74c-0.78-2.93-2.49-5.41-5.42-6.19C55.79,.13,34,0,34,0S12.21,.13,6.9,1.55 C3.97,2.33,2.27,4.81,1.48,7.74C0.06,13.05,0,24,0,24s0.06,10.95,1.48,16.26c0.78,2.93,2.49,5.41,5.42,6.19 C12.21,47.87,34,48,34,48s21.79-0.13,27.1-1.55c2.93-0.78,4.64-3.26,5.42-6.19C67.94,34.95,68,24,68,24S67.94,13.05,66.52,7.74z" fill="#f00"></path>
													<path d="M 45,24 27,14 27,34" fill="#fff"></path>
												</svg>
											</div>
										</div>
									</div>
										<div class="atv-video-info-new">
											<div class="atv-channel-icon">
												<img src="<?php echo esc_url( $favicon ); ?>" alt="Site Icon">
											</div>
											<div class="atv-meta-content">
												<h3 class="atv-video-title"><?php the_title(); ?></h3>
												<p class="atv-channel-name"><?php echo esc_html( $cat_display ); ?></p>
												<p class="atv-video-meta">
													<?php echo $this->format_views( $views ); ?> views • <?php echo $time_ago; ?>
												</p>
											</div>
										</div>
									</article>
									<?php
								endwhile;
								wp_reset_postdata();
							endif;
							?>
						</div>
					</div>

					<!-- Sidebar Area -->
					<aside class="atv-sidebar">
						<h2 class="atv-sidebar-title">Most Viewed</h2>
						<div class="atv-sidebar-list">
							<?php
							// Query for top viewed
							$top_videos = new WP_Query( array(
								'post_type' => 'atv_video',
								'posts_per_page' => 10,
								'meta_key' => '_atv_views',
								'orderby' => 'meta_value_num date', // Fallback to date if views are zero/missing
								'order' => 'DESC'
							) );

							if ( $top_videos->have_posts() ) :
								while ( $top_videos->have_posts() ) : $top_videos->the_post();
									$attachment_id = get_post_meta( get_the_ID(), '_atv_attachment_id', true );
									$video_url = wp_get_attachment_url( $attachment_id );
									$thumbnail = get_the_post_thumbnail_url( get_the_ID(), 'medium' ) ?: ATV_VG_URL . 'assets/images/video-placeholder.jpg';
									$views = get_post_meta( get_the_ID(), '_atv_views', true ) ?: 0;
									?>
									<div class="atv-sidebar-item atv-video-card" 
										 data-id="<?php the_ID(); ?>" 
										 data-video-url="<?php echo esc_url( $video_url ); ?>"
										 data-title="<?php echo esc_attr( get_the_title() ); ?>"
										 data-desc="<?php echo esc_attr( get_the_content() ); ?>">
										<div class="atv-sidebar-thumb">
											<img src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php the_title(); ?>">
											<div class="atv-sidebar-play-overlay">
												<svg viewBox="0 0 68 48" width="40" height="30">
													<path class="ytp-large-play-button-bg" d="M66.52,7.74c-0.78-2.93-2.49-5.41-5.42-6.19C55.79,.13,34,0,34,0S12.21,.13,6.9,1.55 C3.97,2.33,2.27,4.81,1.48,7.74C0.06,13.05,0,24,0,24s0.06,10.95,1.48,16.26c0.78,2.93,2.49,5.41,5.42,6.19 C12.21,47.87,34,48,34,48s21.79-0.13,27.1-1.55c2.93-0.78,4.64-3.26,5.42-6.19C67.94,34.95,68,24,68,24S67.94,13.05,66.52,7.74z" fill="#f00"></path>
													<path d="M 45,24 27,14 27,34" fill="#fff"></path>
												</svg>
											</div>
										</div>
										<div class="atv-sidebar-info">
											<h4><?php the_title(); ?></h4>
											<p><?php echo $this->format_views( $views ); ?> views</p>
										</div>
									</div>
									<?php
								endwhile;
								wp_reset_postdata();
							endif;
							?>
						</div>
					</aside>

				</div>
			<?php endif; ?>
		</div>

		<!-- Lightbox Template (Existing) -->
		<div id="atv-lightbox" class="atv-modal">
			<div class="atv-modal-content">
				<span class="atv-close">&times;</span>
				<div class="atv-player-container">
					<video id="atv-main-player" controls autoplay></video>
				</div>
				<div class="atv-video-details">
					<h1 id="atv-player-title"></h1>
					<p id="atv-player-desc"></p>
				</div>
				<div class="atv-related-slider-container">
					<h3>Up Next</h3>
					<div class="atv-related-slider" id="atv-related-slider">
						<!-- Dynamically populated from sibling categories -->
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
