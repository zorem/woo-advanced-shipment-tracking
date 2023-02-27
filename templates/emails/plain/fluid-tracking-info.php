<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $tracking_items ) {

	$tracking_info_settings = get_option('tracking_info_settings');
	
	if ( $tracking_info_settings['header_text_change'] ) {
		$shipment_tracking_header = $tracking_info_settings['header_text_change']; 
	} else {
		$shipment_tracking_header = 'Tracking Information';
	}
	
	if ( $tracking_info_settings['additional_header_text'] ) {
		$shipment_tracking_header_text = $tracking_info_settings['additional_header_text']; 
	}

	echo esc_html( strtoupper( apply_filters( 'woocommerce_shipment_tracking_my_orders_title', $shipment_tracking_header ) ) ) . "\n\n";
	
	if ( isset( $shipment_tracking_header_text ) ) {
		echo esc_html( $shipment_tracking_header_text ) . "\n\n";
	}
	
	if ( isset( $new_status ) ) {
		/* translators: %s: replace with status */
		echo sprintf( esc_html__( 'Shipment status changed to %s', 'woo-advanced-shipment-tracking' ), esc_html( apply_filters( 'trackship_status_filter', $new_status ) ) ) . "\n\n";
	}

	foreach ( $tracking_items as $tracking_item ) {
		echo esc_html__( 'Provider', 'woo-advanced-shipment-tracking' ) . ': ' . esc_html( apply_filters( 'ast_provider_title', esc_html( $tracking_item['formatted_tracking_provider'] ) ) ) . "\n";
		echo esc_html__( 'Tracking Number', 'woo-advanced-shipment-tracking' ) . ': ' . esc_html( $tracking_item['tracking_number'] ) . "\n";
		echo esc_html__( 'Track', 'woo-advanced-shipment-tracking' ) . ': ' . esc_url( $tracking_item['ast_tracking_link'] ) . "\n\n";
	}

	echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= \n\n";
}
