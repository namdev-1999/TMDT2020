<?php
/**
 * Admin Class
 *
 * Handles the admin functionality of plugin
 *
 * @package Product Slider and Carousel with Category for WooCommerce
 * @since 1.0
 */

if( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Wcpscwc_Admin {

	function __construct() {

		// Action to add admin menu
		add_action( 'admin_menu', array($this, 'wpnw_register_menu'), 12 );
	}

	/**
	 * Function to add menu
	 * 
	 * @package Product Slider and Carousel with Category for WooCommerce
	 * @since 1.0.0
	 */
	function wpnw_register_menu() {

		// Getting Started page
		add_menu_page( __('Woo - Product Slider', 'woo-product-slider-and-carousel-with-category'), __('Woo - Product Slider', 'woo-product-slider-and-carousel-with-category'), 'manage_options', 'wcpscwc-about', array($this, 'wcpscwc_designs_page'), 'dashicons-slides', 56 );

		// Register plugin premium page
		add_submenu_page( 'wcpscwc-about', __('Upgrade to PRO - Woo Product Slider', 'woo-product-slider-and-carousel-with-category'), '<span style="color:#2ECC71">'.__('Upgrade to PRO', 'woo-product-slider-and-carousel-with-category').'</span>', 'edit_posts', 'wcpscwc-premium', array($this, 'wcpscwc_premium_page') );
	}

	/**
	 * How it work Page Html
	 * 
	 * @package Product Slider and Carousel with Category for WooCommerce
	 * @since 1.0.0
	 */
	function wcpscwc_designs_page() {
		include_once( WCPSCWC_DIR . '/includes/admin/wcpscwc-how-it-work.php' );
	}

	/**
	 * Premium Page Html
	 * 
	 * @package Product Slider and Carousel with Category for WooCommerce
	 * @since 1.0.0
	 */
	function wcpscwc_premium_page() {
		include_once( WCPSCWC_DIR . '/includes/admin/settings/premium.php' );
	}
}

$wcpscwc_Admin = new Wcpscwc_Admin();