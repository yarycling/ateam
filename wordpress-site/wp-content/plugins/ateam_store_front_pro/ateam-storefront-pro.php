<?php
/**
 * Plugin Name: A-Team Storefront Designer
 * Plugin URI: https://ateam.com/
 * Description: A premium, dark-themed WooCommerce storefront designer with hero banners, product grids, and promotional sections.
 * Version: 1.0.0
 * Author: A-Team
 * Author URI: https://ateam.com/
 * Text Domain: ateam-storefront
 * WC exceeds: 3.0
 * WC requires at least: 3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define Constants
define( 'ATEAM_STOREFRONT_VERSION', '1.0.0' );
define( 'ATEAM_STOREFRONT_PATH', plugin_dir_path( __FILE__ ) );
define( 'ATEAM_STOREFRONT_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main Class for A-Team Storefront Designer
 */
class ATeam_Storefront {

	/**
	 * Instance of this class.
	 * @var ATeam_Storefront
	 */
	protected static $instance = null;

	/**
	 * Get instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		// Check for WooCommerce dependency
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		
		// Activation notice if WC is missing
		add_action( 'admin_notices', array( $this, 'wc_missing_notice' ) );
	}

	/**
	 * Initialize the plugin
	 */
	public function init() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		$this->includes();
		$this->setup();
	}

	/**
	 * Include required files
	 */
	private function includes() {
		require_once ATEAM_STOREFRONT_PATH . 'includes/class-ateam-query.php';
		require_once ATEAM_STOREFRONT_PATH . 'includes/class-ateam-admin.php';
		require_once ATEAM_STOREFRONT_PATH . 'includes/class-ateam-frontend.php';
	}

	/**
	 * Setup hooks and classes
	 */
	private function setup() {
		new ATeam_Admin();
		new ATeam_Frontend();
	}

	/**
	 * WooCommerce missing notice
	 */
	public function wc_missing_notice() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			?>
			<div class="notice notice-error">
				<p><?php esc_html_e( 'A-Team Storefront Designer requires WooCommerce to be installed and active.', 'ateam-storefront' ); ?></p>
			</div>
			<?php
		}
	}
}

// Global instance
function ATeam_Storefront() {
	return ATeam_Storefront::get_instance();
}

ATeam_Storefront();
