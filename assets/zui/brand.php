<?php
/**
 * Zorem UI — plugin brand registry.
 *
 * Single source of truth for the header brand cluster (emblem icon + name +
 * "PRO" badge + tagline) for every Zorem plugin that consumes this library.
 * A consumer plugin renders its own brand by calling
 * `zui_get_plugin_brand( plugin_basename( $main_file ) )` and feeding the
 * returned array into the canonical `.zui-brand` markup.
 *
 * Registering a new plugin: add an entry below keyed by its main-file
 * basename (e.g. `'my-plugin/my-plugin.php'`). No consumer-side code
 * changes required after that.
 *
 * @package Zorem_UI
 * @since   1.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'zui_get_plugin_brand' ) ) {
	/**
	 * Return the chrome brand info for a given plugin slug.
	 *
	 * Returns NULL if the slug is unknown so the caller can fall back to a
	 * hardcoded brand (preserving pixel-identical chrome during migration).
	 *
	 * Returned keys:
	 *  - name         (string) Three-letter plugin acronym shown in the header.
	 *  - badge        (string) Short uppercase pill (typically "PRO").
	 *  - tagline      (string) One-line description shown after the badge.
	 *  - icon         (string) `zui_icon()` key rendered inside the emblem.
	 *  - emblem_bg    (string) Hex color piped into `--zui-brand-emblem-bg`.
	 *  - emblem_color (string) Hex color piped into `--zui-brand-emblem-color`.
	 *
	 * @param string $slug Plugin main-file basename, e.g. 'ast-pro/ast-pro.php'.
	 * @return array|null
	 */
	function zui_get_plugin_brand( $slug ) {
		$brands = array(
			'ast-pro/ast-pro.php' => array(
				'name'         => 'AST',
				'badge'        => 'PRO',
				'tagline'      => 'Fulfillment Manager',
				'icon'         => 'package',
				'emblem_bg'    => '#DBEAFE',
				'emblem_color' => '#2563EB',
			),
			'woo-advanced-shipment-tracking/woocommerce-advanced-shipment-tracking.php' => array(
				'name'         => 'AST',
				'badge'        => 'FREE',
				'tagline'      => 'Shipment Tracking',
				'icon'         => 'package',
				'emblem_bg'    => '#DBEAFE',
				'emblem_color' => '#2563EB',
			),
			'advanced-local-pickup-pro/advanced-local-pickup-pro.php' => array(
				'name'         => 'ALP',
				'badge'        => 'PRO',
				'tagline'      => 'Local Pickup Manager',
				'icon'         => 'store',
				'emblem_bg'    => '#DCFCE7',
				'emblem_color' => '#16A34A',
			),
			'country-base-restrictions-pro-addon/country-base-restrictions-pro-addon.php' => array(
				'name'         => 'CBR',
				'badge'        => 'PRO',
				'tagline'      => 'Country Restrictions',
				'icon'         => 'globe',
				'emblem_bg'    => '#FFEDD5',
				'emblem_color' => '#EA580C',
			),
			'customer-email-verification-pro/customer-email-verification-pro.php' => array(
				'name'         => 'CEV',
				'badge'        => 'PRO',
				'tagline'      => 'Email Verification',
				'icon'         => 'shield-check',
				'emblem_bg'    => '#FEE2E2',
				'emblem_color' => '#DC2626',
			),
			'sales-report-email-pro/sales-report-email-pro.php' => array(
				'name'         => 'SRE',
				'badge'        => 'PRO',
				'tagline'      => 'Sales Report Email',
				'icon'         => 'mail',
				'emblem_bg'    => '#F3E8FF',
				'emblem_color' => '#9333EA',
			),
			'sms-for-woocommerce/sms-for-woocommerce.php' => array(
				'name'         => 'SMS',
				'badge'        => 'PRO',
				'tagline'      => 'SMS Notifications',
				'icon'         => 'phone',
				'emblem_bg'    => '#DBEAFE',
				'emblem_color' => '#2563EB',
			),
		);

		return isset( $brands[ $slug ] ) ? $brands[ $slug ] : null;
	}
}
