<?php
/**
 * Admin Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ATeam_Admin {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	public function add_settings_page() {
		add_menu_page(
			__( 'A-Team Storefront', 'ateam-storefront' ),
			__( 'A-Team Storefront', 'ateam-storefront' ),
			'manage_options',
			'ateam-storefront',
			array( $this, 'render_settings_page' ),
			'dashicons-store',
			58
		);
	}

	public function register_settings() {
		register_setting( 'ateam_storefront_options', 'ateam_storefront_settings' );

		// Hero Section
		add_settings_section( 'ateam_hero_section', __( 'Hero Banner Settings', 'ateam-storefront' ), null, 'ateam-storefront' );
		$this->add_field( 'hero_enabled', __( 'Enable Hero Banner', 'ateam-storefront' ), 'checkbox', 'ateam_hero_section' );
		$this->add_field( 'hero_image', __( 'Hero Image', 'ateam-storefront' ), 'media', 'ateam_hero_section' );
		$this->add_field( 'hero_title', __( 'Hero Title', 'ateam-storefront' ), 'text', 'ateam_hero_section', 'BESTSELLERS' );
		$this->add_field( 'hero_subtitle', __( 'Hero Subtitle', 'ateam-storefront' ), 'text', 'ateam_hero_section', 'Shop our most popular streetwear styles!' );
		$this->add_field( 'hero_cta', __( 'CTA Text', 'ateam-storefront' ), 'text', 'ateam_hero_section', 'SHOP NOW' );
		$this->add_field( 'hero_link', __( 'CTA Link', 'ateam-storefront' ), 'text', 'ateam_hero_section', '#' );

		// Featured Products
		add_settings_section( 'ateam_products_section', __( 'Featured Products Settings', 'ateam-storefront' ), null, 'ateam-storefront' );
		$this->add_field( 'products_enabled', __( 'Enable Products Section', 'ateam-storefront' ), 'checkbox', 'ateam_products_section', 1 );
		$this->add_field( 'products_title', __( 'Section Title', 'ateam-storefront' ), 'text', 'ateam_products_section', 'FEATURED PRODUCTS' );
		$this->add_field( 'products_source', __( 'Product Source', 'ateam-storefront' ), 'select', 'ateam_products_section', 'featured', array(
			'options' => array(
				'featured'    => 'Featured Products',
				'bestselling' => 'Bestselling Products',
				'onsale'      => 'On Sale Products',
			)
		) );
		$this->add_field( 'products_categories', __( 'Filter by Categories', 'ateam-storefront' ), 'multiselect', 'ateam_products_section', '', array(
			'options' => $this->get_category_options()
		) );
		$this->add_field( 'products_count', __( 'Number of Products', 'ateam-storefront' ), 'number', 'ateam_products_section', 8 );
		$this->add_field( 'products_columns', __( 'Columns', 'ateam-storefront' ), 'number', 'ateam_products_section', 4 );
		$this->add_field( 'show_badges', __( 'Show Badges (Sale/Bestseller)', 'ateam-storefront' ), 'checkbox', 'ateam_products_section', 1 );

		// All Products
		add_settings_section( 'ateam_all_section', __( 'All Products Settings', 'ateam-storefront' ), null, 'ateam-storefront' );
		$this->add_field( 'all_enabled', __( 'Enable All Products Section', 'ateam-storefront' ), 'checkbox', 'ateam_all_section', 1 );
		$this->add_field( 'all_title', __( 'Section Title', 'ateam-storefront' ), 'text', 'ateam_all_section', 'CUSTOMER FAVORITES' );
		$this->add_field( 'all_bg_image', __( 'Background Image', 'ateam-storefront' ), 'media', 'ateam_all_section' );
		$this->add_field( 'all_count', __( 'Number of Products', 'ateam-storefront' ), 'number', 'ateam_all_section', 8 );
		$this->add_field( 'all_columns', __( 'Columns', 'ateam-storefront' ), 'number', 'ateam_all_section', 4 );

		// Promo Banner
		add_settings_section( 'ateam_promo_section', __( 'Promo Banner Settings', 'ateam-storefront' ), null, 'ateam-storefront' );
		$this->add_field( 'promo_enabled', __( 'Enable Promo Banner', 'ateam-storefront' ), 'checkbox', 'ateam_promo_section' );
		$this->add_field( 'promo_image', __( 'Promo Image', 'ateam-storefront' ), 'media', 'ateam_promo_section' );
		$this->add_field( 'promo_title', __( 'Promo Title', 'ateam-storefront' ), 'text', 'ateam_promo_section', '20% OFF ALL HOODIES' );
		$this->add_field( 'promo_cta', __( 'Promo CTA', 'ateam-storefront' ), 'text', 'ateam_promo_section', 'SHOP HOODIES' );
		$this->add_field( 'promo_link', __( 'Promo Link', 'ateam-storefront' ), 'text', 'ateam_promo_section', '#' );
	}

	private function add_field( $id, $title, $type, $section, $default = '', $extra = array() ) {
		add_settings_field(
			$id,
			$title,
			array( $this, 'render_field' ),
			'ateam-storefront',
			$section,
			array_merge( array( 'id' => $id, 'type' => $type, 'default' => $default ), $extra )
		) ;
	}

	private function get_category_options() {
		$categories = get_terms( array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
		) );
		$options = array();
		if ( ! is_wp_error( $categories ) ) {
			foreach ( $categories as $cat ) {
				$options[$cat->slug] = $cat->name;
			}
		}
		return $options;
	}

	public function render_field( $args ) {
		$options = get_option( 'ateam_storefront_settings' );
		$id = $args['id'];
		$value = isset( $options[$id] ) ? $options[$id] : $args['default'];

		switch ( $args['type'] ) {
			case 'text':
				echo '<input type="text" name="ateam_storefront_settings[' . esc_attr( $id ) . ']" value="' . esc_attr( $value ) . '" class="regular-text">';
				break;
			case 'number':
				echo '<input type="number" name="ateam_storefront_settings[' . esc_attr( $id ) . ']" value="' . esc_attr( $value ) . '" class="small-text">';
				break;
			case 'checkbox':
				echo '<input type="checkbox" name="ateam_storefront_settings[' . esc_attr( $id ) . ']" value="1" ' . checked( 1, $value, false ) . '>';
				break;
			case 'select':
				echo '<select name="ateam_storefront_settings[' . esc_attr( $id ) . ']">';
				foreach ( $args['options'] as $opt_val => $opt_label ) {
					echo '<option value="' . esc_attr( $opt_val ) . '" ' . selected( $value, $opt_val, false ) . '>' . esc_html( $opt_label ) . '</option>';
				}
				echo '</select>';
				break;
			case 'multiselect':
				$value = is_array( $value ) ? $value : array();
				echo '<select name="ateam_storefront_settings[' . esc_attr( $id ) . '][]" multiple style="height: 100px;">';
				foreach ( $args['options'] as $opt_val => $opt_label ) {
					echo '<option value="' . esc_attr( $opt_val ) . '" ' . ( in_array( $opt_val, $value ) ? 'selected' : '' ) . '>' . esc_html( $opt_label ) . '</option>';
				}
				echo '</select>';
				echo '<p class="description">' . __( 'Hold Ctrl (Windows) or Command (Mac) to select multiple.', 'ateam-storefront' ) . '</p>';
				break;
			case 'media':
				$image_url = $value ? wp_get_attachment_url( $value ) : '';
				echo '<div class="ateam-media-uploader">';
				echo '<input type="hidden" name="ateam_storefront_settings[' . esc_attr( $id ) . ']" value="' . esc_attr( $value ) . '" class="ateam-media-id">';
				echo '<div class="ateam-media-preview" style="margin-bottom:10px;">';
				if ( $image_url ) {
					echo '<img src="' . esc_url( $image_url ) . '" style="max-width:200px; height:auto; display:block; border:1px solid #ccc; padding:5px;">';
				}
				echo '</div>';
				echo '<button class="button ateam-upload-button">' . __( 'Select Image', 'ateam-storefront' ) . '</button>';
				echo '<button class="button ateam-remove-button" style="' . ( $value ? '' : 'display:none;' ) . '">' . __( 'Remove', 'ateam-storefront' ) . '</button>';
				echo '</div>';
				break;
		}
	}

	public function render_settings_page() {
		?>
		<div class="wrap ateam-settings-wrap">
			<h1><?php _e( 'A-Team Storefront Designer', 'ateam-storefront' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'ateam_storefront_options' );
				do_settings_sections( 'ateam-storefront' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public function enqueue_admin_assets( $hook ) {
		if ( 'toplevel_page_ateam-storefront' !== $hook ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'ateam-admin-css', ATEAM_STOREFRONT_URL . 'assets/css/admin.css', array(), ATEAM_STOREFRONT_VERSION );
		wp_enqueue_script( 'ateam-admin-js', ATEAM_STOREFRONT_URL . 'assets/js/admin.js', array( 'jquery' ), ATEAM_STOREFRONT_VERSION, true );
	}
}
