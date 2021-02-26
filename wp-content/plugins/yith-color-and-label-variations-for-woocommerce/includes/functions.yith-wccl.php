<?php
/**
 * Functions
 *
 * @author  YITH
 * @package YITH WooCommerce Colors and Labels Variations
 * @version 1.1.0
 */

if ( ! function_exists( 'ywccl_get_term_meta' ) ) {
	/**
	 * Get term meta. If WooCommerce version is >= 2.6 use get_term_meta else use get_woocommerce_term_meta
	 *
	 * @author Francesco Licandro*
	 * @param      $term_id
	 * @param      $key
	 * @param bool $single
	 * @return mixed
	 */
	function ywccl_get_term_meta( $term_id, $key, $single = true ) {
		return function_exists( 'get_term_meta' ) ? get_term_meta( $term_id, $key, $single ) : get_metadata( 'woocommerce_term', $term_id, $key, $single );
	}
}

if ( ! function_exists( 'ywccl_update_term_meta' ) ) {
	/**
	 * Get term meta. If WooCommerce version is >= 2.6 use update_term_meta else use update_woocommerce_term_meta
	 *
	 * @author Francesco Licandro
	 * @param string     $meta_key
	 * @param mixed      $meta_value
	 * @param mixed      $prev_value
	 *
	 * @param string|int $term_id
	 * @return bool
	 */
	function ywccl_update_term_meta( $term_id, $meta_key, $meta_value, $prev_value = '' ) {
		return function_exists( 'update_term_meta' ) ? update_term_meta( $term_id, $meta_key, $meta_value, $prev_value ) : update_metadata( 'woocommerce_term', $term_id, $meta_key, $meta_value, $prev_value );
	}
}

if ( ! function_exists( 'ywccl_check_wc_version' ) ) {
	/**
	 * Check installed WooCommerce version
	 *
	 * @since  1.3.0
	 * @author Francesco Licandro
	 * @param string $version
	 * @param string $operator
	 * @return boolean
	 * @deprecated
	 */
	function ywccl_check_wc_version( $version, $operator ) {
		return version_compare( WC()->version, $version, $operator );
	}
}

if ( ! function_exists( 'ywccl_get_custom_tax_types' ) ) {
	/**
	 * Return custom product's attributes type
	 *
	 * @since  1.2.0
	 * @author Francesco Licandro
	 * @return array
	 */
	function ywccl_get_custom_tax_types() {
		return apply_filters(
			'yith_wccl_get_custom_tax_types',
			array(
				'colorpicker' => __( 'Colorpicker', 'yith-color-and-label-variations-for-woocommerce' ),
				'image'       => __( 'Image', 'yith-color-and-label-variations-for-woocommerce' ),
				'label'       => __( 'Label', 'yith-color-and-label-variations-for-woocommerce' ),
			)
		);
	}
}