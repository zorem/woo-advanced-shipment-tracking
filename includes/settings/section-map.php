<?php
/**
 * AST - New Settings UI: Settings-tab section map (Free version).
 *
 * Single source of truth for the Settings sidebar. Consumed by sidebar.php and
 * settings-body.php. Each entry declares navigation metadata + a render source.
 *
 * FREE VERSION SECTIONS (match the legacy admin_options_settings.php):
 *   1. general          (active)   — legacy: $this->get_add_tracking_options()
 *   2. order-statuses   (active)   — legacy: views/admin_options_osm.php
 *   3. tracking-api     (active)   — legacy: $this->get_shipment_tracking_api_options()
 *   4. paypal           (PRO lock) — upgrade CTA
 *   5. stripe           (PRO lock) — upgrade CTA
 *   6. ftp-import       (PRO lock) — upgrade CTA
 *
 * All three active sections submit through the same #wc_ast_settings_form
 * (action: wc_ast_settings_form_update) — identical contract to the legacy UI.
 *
 * @package wc_advanced_shipment_tracking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ast_free_settings_section_map' ) ) {
	/**
	 * Return the ordered, filterable list of Settings sections for the free plugin.
	 *
	 * Entry shape:
	 *   id            : sidebar slug + panel id suffix.
	 *   label / sub   : sidebar + section-header title / subtitle.
	 *   icon          : icon key (see partials/icons.php).
	 *   badge         : optional badge label ('PRO' for locked sections).
	 *   render_mode   : 'legacy'  → require the file in `source`
	 *                   'locked'  → render an upgrade-to-PRO card
	 *   source        : (legacy)  absolute path to the legacy view fragment.
	 *   save_context  : form id + nonce action (only used by active sections).
	 *
	 * @return array
	 */
	function ast_free_settings_section_map() {

		// Shared save context for every active settings section (the live wc_ast_settings_form).
		$save_context = array(
			'form_id'      => 'wc_ast_settings_form',
			'nonce_action' => 'wc_ast_settings_form',
			'nonce_field'  => 'wc_ast_settings_form_nonce',
			'action'       => 'wc_ast_settings_form_update',
		);

		$sections = array(
			'general' => array(
				'id'           => 'general',
				'label'        => __( 'General Settings', 'woo-advanced-shipment-tracking' ),
				'sub'          => __( 'Core tracking and display options.', 'woo-advanced-shipment-tracking' ),
				'icon'         => 'sliders-horizontal',
				'render_mode'  => 'legacy',
				'source'       => 'general',
				'save_context' => $save_context,
			),
			'order-statuses' => array(
				'id'           => 'order-statuses',
				'label'        => __( 'Order Statuses & Notifications', 'woo-advanced-shipment-tracking' ),
				'sub'          => __( 'Configure shipment statuses and notification emails.', 'woo-advanced-shipment-tracking' ),
				'icon'         => 'mail',
				'render_mode'  => 'legacy',
				'source'       => SHIPMENT_TRACKING_PATH . '/includes/views/admin_options_osm.php',
				'save_context' => $save_context,
			),
			'tracking-api' => array(
				'id'           => 'tracking-api',
				'label'        => __( 'Shipment Tracking API', 'woo-advanced-shipment-tracking' ),
				'sub'          => __( 'Settings for the REST endpoints other apps use to push tracking.', 'woo-advanced-shipment-tracking' ),
				'icon'         => 'plug',
				'render_mode'  => 'legacy',
				'source'       => 'tracking-api',
				'save_context' => $save_context,
			),
			'paypal' => array(
				'id'           => 'paypal',
				'label'        => __( 'PayPal Tracking', 'woo-advanced-shipment-tracking' ),
				'sub'          => __( 'Automated synchronization of tracking numbers and logistics milestones to PayPal transaction receipts.', 'woo-advanced-shipment-tracking' ),
				'icon'         => 'credit-card',
				'badge'        => 'PRO',
				'render_mode'  => 'locked',
				'source'       => SHIPMENT_TRACKING_PATH . '/includes/settings/partials/locked-paypal.php',
			),
			'stripe' => array(
				'id'           => 'stripe',
				'label'        => __( 'Stripe Tracking', 'woo-advanced-shipment-tracking' ),
				'sub'          => __( 'Automated synchronization of tracking numbers and logistics milestones to Stripe transaction receipts.', 'woo-advanced-shipment-tracking' ),
				'icon'         => 'credit-card',
				'badge'        => 'PRO',
				'render_mode'  => 'locked',
				'source'       => SHIPMENT_TRACKING_PATH . '/includes/settings/partials/locked-stripe.php',
			),
		);

		/**
		 * Filter the Settings sidebar sections.
		 *
		 * @param array $sections Ordered section definitions.
		 */
		return apply_filters( 'ast_free_settings_sections', $sections );
	}
}
