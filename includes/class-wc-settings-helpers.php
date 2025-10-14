<?php
/**
 * AST - Settings Helpers
 *
 * These functions help get, update, and delete settings and integration settings.
 *
 * @package wc_advanced_shipment_tracking
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get a value from the AST settings option.
 *
 * @param string $name          The option name.
 * @param string $key           The setting key.
 * @param mixed  $default_value The default value to return if key not found.
 * @return mixed
 */
if (!function_exists('get_ast_settings')) {
	function get_ast_settings( $name, $key, $default_value = '' ) {
		$data_array = get_option( $name, array() );
		// return $data_array[$key] ?? $default_value;
		return isset($data_array[$key]) ? $data_array[$key] : $default_value;
	}
}

/**
 * Update a value in the AST settings option.
 *
 * @param string $name The option name.
 * @param string $key  The setting key.
 * @param mixed  $value The value to save.
 */
if (!function_exists('update_ast_settings')) {
	function update_ast_settings( $name, $key, $value ) {
		$data_array = get_option( $name, array() );
		$data_array[ $key ] = $value;
		update_option( $name, $data_array );
	}
}

/**
 * Delete a setting key from the AST settings option.
 *
 * @param string $name The option name.
 * @param string $key  The setting key to remove.
 */
if (!function_exists('delete_ast_settings')) {
	function delete_ast_settings( $name, $key ) {
		$data_array = get_option( $name, array() );
		unset($data_array[$key]);
		update_option( $name, $data_array );
	}
}

/**
 * Initialize and return Zorem Tracking instance for AST PRO.
 *
 * This function loads the Zorem tracking utility, which helps collect
 * plugin usage data (if allowed) and assists in improving the plugin.
 * 
 * It is only initialized once using a singleton pattern.
 */
if ( ! function_exists( 'zorem_ast_tracking' ) ) {
	function zorem_ast_tracking() {
		require_once dirname(__FILE__) . '/../zorem-tracking/zorem-tracking.php';
		$plugin_name = 'Advanced Shipment Tracking for WooCommerce';
		$plugin_slug = 'ast';
		$user_id = '1';
		$setting_page_type = 'top-level';
		$setting_page_location =  'A custom top-level admin menu (admin.php)';
		$parent_menu_type = '';
		$menu_slug = 'woocommerce-advanced-shipment-tracking';
		$plugin_id = '1';
		$zorem_tracking = WC_Trackers::get_instance( $plugin_name, $plugin_slug, $user_id, $setting_page_type, $setting_page_location, $parent_menu_type, $menu_slug, $plugin_id );
		return $zorem_tracking;
	}
	zorem_ast_tracking();
}
