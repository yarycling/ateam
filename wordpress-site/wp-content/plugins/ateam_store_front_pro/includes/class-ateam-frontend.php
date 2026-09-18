<?php
/**
 * Frontend Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ATeam_Frontend {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		
		// Register Shortcodes
		add_shortcode( 'ateam_storefront', array( $this, 'render_full_storefront' ) );
		add_shortcode( 'ateam_bestsellers', array( $this, 'render_hero' ) );
		add_shortcode( 'ateam_hero', array( $this, 'render_hero' ) );
		add_shortcode( 'ateam_featured_products', array( $this, 'render_product_grid' ) );
		add_shortcode( 'ateam_all_products', array( $this, 'render_all_products' ) );
		add_shortcode( 'ateam_promo_banner', array( $this, 'render_promo_banner' ) );
	}

	public function enqueue_assets() {
		wp_enqueue_style( 'google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Outfit:wght@400;700;900&display=swap', array(), null );
		wp_enqueue_style( 'ateam-frontend-css', ATEAM_STOREFRONT_URL . 'assets/css/frontend.css', array(), ATEAM_STOREFRONT_VERSION );
		wp_enqueue_script( 'ateam-frontend-js', ATEAM_STOREFRONT_URL . 'assets/js/frontend.js', array( 'jquery' ), ATEAM_STOREFRONT_VERSION, true );
	}

	/**
	 * Render Full Storefront
	 */
	public function render_full_storefront() {
		ob_start();
		$this->render_hero();
		$this->render_product_grid();
		$this->render_promo_banner();
		$this->render_all_products();
		return ob_get_clean();
	}

	/**
	 * Render Hero Section
	 */
	public function render_hero( $atts = array() ) {
		$options = get_option( 'ateam_storefront_settings' );
		if ( empty( $options['hero_enabled'] ) ) return '';

		$data = array(
			'image'    => wp_get_attachment_url( $options['hero_image'] ),
			'title'    => ! empty( $options['hero_title'] ) ? $options['hero_title'] : 'BESTSELLERS',
			'subtitle' => ! empty( $options['hero_subtitle'] ) ? $options['hero_subtitle'] : '',
			'cta'      => ! empty( $options['hero_cta'] ) ? $options['hero_cta'] : 'SHOP NOW',
			'link'     => ! empty( $options['hero_link'] ) ? $options['hero_link'] : '#',
		);

		include ATEAM_STOREFRONT_PATH . 'templates/hero.php';
	}

	/**
	 * Render Product Grid
	 */
	public function render_product_grid( $atts = array() ) {
		$options = get_option( 'ateam_storefront_settings' );
		if ( empty( $options['products_enabled'] ) ) return '';
		
		$atts = shortcode_atts( array(
			'count'    => ! empty( $options['products_count'] ) ? $options['products_count'] : 8,
			'columns'  => ! empty( $options['products_columns'] ) ? $options['products_columns'] : 4,
			'source'   => ! empty( $options['products_source'] ) ? $options['products_source'] : 'featured',
			'category' => '', // From shortcode
		), $atts );

		$products = ATeam_Query::get_products( array(
			'posts_per_page' => $atts['count'],
			'source'         => $atts['source'],
			'category'       => $atts['category'],
		) );

		$title = ! empty( $options['products_title'] ) ? $options['products_title'] : 'FEATURED PRODUCTS';
		
		// Get categories for tabs from settings if defined
		$selected_cats = ! empty( $options['products_categories'] ) ? $options['products_categories'] : array();
		$categories = ATeam_Query::get_categories( $selected_cats );

		include ATEAM_STOREFRONT_PATH . 'templates/product-grid.php';
	}

	/**
	 * Render All Products
	 */
	public function render_all_products( $atts = array() ) {
		$options = get_option( 'ateam_storefront_settings' );
		if ( empty( $options['all_enabled'] ) ) return '';

		$atts = shortcode_atts( array(
			'count'    => ! empty( $options['all_count'] ) ? $options['all_count'] : 8,
			'columns'  => ! empty( $options['all_columns'] ) ? $options['all_columns'] : 4,
			'category' => '',
		), $atts );

		$products = ATeam_Query::get_products( array(
			'posts_per_page' => $atts['count'],
			'category'       => $atts['category'],
			// source defaults to 'date' DESC (All)
		) );

		$title = ! empty( $options['all_title'] ) ? $options['all_title'] : 'CUSTOMER FAVORITES';
		$categories = array(); // No tabs for this section to keep it clean, or we can add them if needed
		
		$bg_image = ! empty( $options['all_bg_image'] ) ? wp_get_attachment_url( $options['all_bg_image'] ) : '';
		$extra_class = $bg_image ? 'ateam-has-bg' : 'ateam-transparent-bg';

		include ATEAM_STOREFRONT_PATH . 'templates/product-grid.php';
	}

	/**
	 * Render Promo Banner
	 */
	public function render_promo_banner( $atts = array() ) {
		$options = get_option( 'ateam_storefront_settings' );
		if ( empty( $options['promo_enabled'] ) ) return '';

		$data = array(
			'image' => wp_get_attachment_url( $options['promo_image'] ),
			'title' => $options['promo_title'],
			'cta'   => $options['promo_cta'],
			'link'  => $options['promo_link'],
		);

		include ATEAM_STOREFRONT_PATH . 'templates/promo-banner.php';
	}
}
