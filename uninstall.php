<?php
/**
 * Uninstall Advanced Shipment Tracking for WooCommerce.
 *
 * @package woo-advanced-shipment-tracking
 */

// Exit if the file is not called by WordPress during uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

wp_clear_scheduled_hook( 'zorem_usage_tracker_send' );
