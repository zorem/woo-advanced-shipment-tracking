<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $tracking_items ) {

	$wcast_customizer_settings = new wcast_initialise_customizer_settings();
	$ast = new WC_Advanced_Shipment_Tracking_Actions();
	$tracking_info_settings = get_option('tracking_info_settings');
	
	if ( $tracking_info_settings['header_text_change'] ) {
		$shipment_tracking_header = $tracking_info_settings['header_text_change']; 
	} else {
		$shipment_tracking_header = 'Tracking Information';
	}
	
	if ( $tracking_info_settings['additional_header_text'] ) {
		$shipment_tracking_header_text = $tracking_info_settings['additional_header_text']; 
	}

	$provider_header_text = $ast->get_option_value_from_array( 'tracking_info_settings', 'provider_header_text', $wcast_customizer_settings->defaults['provider_header_text'] );
	
	$tracking_number_header_text = $ast->get_option_value_from_array( 'tracking_info_settings', 'tracking_number_header_text', $wcast_customizer_settings->defaults['tracking_number_header_text'] );
	
	$track_header_text = $ast->get_option_value_from_array( 'tracking_info_settings', 'track_header_text', $wcast_customizer_settings->defaults['track_header_text'] );

	echo esc_html( strtoupper( apply_filters( 'woocommerce_shipment_tracking_my_orders_title', __( $shipment_tracking_header, 'woo-advanced-shipment-tracking' ) ) ) ) . "\n\n";
	
	if ( isset( $shipment_tracking_header_text ) ) {
		echo esc_html( $shipment_tracking_header_text ) . "\n\n";
	}
	
	if ( isset( $new_status ) ) {
		/* translators: %s: replace with status */
		echo sprintf( esc_html__( 'Shipment status changed to %s', 'woo-advanced-shipment-tracking' ), esc_html( apply_filters( 'trackship_status_filter', $new_status ) ) ) . "\n\n";
	}

	foreach ( $tracking_items as $tracking_item ) {
		echo esc_html__( $provider_header_text, 'woo-advanced-shipment-tracking' ) . ': ' . esc_html( apply_filters( 'ast_provider_title', esc_html( $tracking_item['formatted_tracking_provider'] ) ) ) . "\n";
		echo esc_html__( $tracking_number_header_text, 'woo-advanced-shipment-tracking' ) . ': ' . esc_html( $tracking_item['tracking_number'] ) . "\n";
		echo esc_html__( $track_header_text, 'woo-advanced-shipment-tracking' ) . ': ' . esc_url( $tracking_item['ast_tracking_link'] ) . "\n\n";
	}

	echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= \n\n";
}
