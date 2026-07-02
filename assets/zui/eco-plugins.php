<?php
/**
 * Zorem UI — ecosystem plugin registry.
 *
 * Single source of truth for the "Fulfillment Workflow Platform Ecosystem"
 * card grid rendered on every Zorem plugin's License tab. A consumer plugin
 * gets the array by calling
 * `zui_get_ecosystem_plugins( plugin_basename( $main_file ) )`; if the
 * current plugin's slug matches a registered entry it is auto-hidden so
 * a plugin never advertises itself in its own ecosystem grid.
 *
 * @package Zorem_UI
 * @since   1.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'zui_get_ecosystem_plugins' ) ) {
	/**
	 * Return the ordered ecosystem-plugin list for the License tab grid.
	 *
	 * The `logo` key resolves to an absolute URL under the library's
	 * `images/eco/` folder so consumer plugins don't need to construct
	 * the path themselves.
	 *
	 * Each entry keys: name, slug, icon, logo, accent, tint, desc, url,
	 * badge, stat. Identical contract to AST PRO's pre-1.6.0 inline array.
	 *
	 * @param string $current_slug Optional. Plugin basename of the caller;
	 *                             when matched, its entry is removed so the
	 *                             plugin doesn't advertise itself.
	 * @return array Numerically-indexed list of plugin entries.
	 */
	function zui_get_ecosystem_plugins( $current_slug = '' ) {
		$images_url = plugin_dir_url( __FILE__ ) . 'images/eco/';
		$utm_source = '' !== $current_slug ? strstr( $current_slug, '/', true ) : 'zorem';
		$utm_suffix = '?utm_source=' . rawurlencode( $utm_source ) . '&utm_medium=license-page&utm_campaign=ecosystem';

		$plugins = array(
			'trackship-for-woocommerce/trackship-for-woocommerce.php' => array(
				'name'   => 'TrackShip for WooCommerce',
				'slug'   => 'trackship-for-woocommerce/trackship-for-woocommerce.php',
				'icon'   => 'truck',
				'logo'   => $images_url . 'trackship.png',
				'accent' => '#0d9488',
				'tint'   => '#CCFBF1',
				'desc'   => __( 'Take control of your post-shipping workflows, reduce customer service overhead and provide a premium post-purchase tracking experience directly inside WooCommerce. Keeps your tracking data up-to-date and sends automated status changes automatically.', 'zorem-ui' ),
				'url'    => 'https://wordpress.org/plugins/trackship-for-woocommerce/',
				'badge'  => __( 'Recommended', 'zorem-ui' ),
				'stat'   => '6,000+ active stores',
			),
			'sms-for-woocommerce/sms-for-woocommerce.php' => array(
				'name'   => 'SMS For WooCommerce',
				'slug'   => 'sms-for-woocommerce/sms-for-woocommerce.php',
				'icon'   => 'phone',
				'logo'   => '',
				'accent' => '#2563EB',
				'tint'   => '#DBEAFE',
				'desc'   => __( 'Keep your shoppers perfectly aligned and updated with lightning fast automated SMS notifications for status updates, dispatched parcels, custom notes, and imminent local drop-offs. Integrates seamlessly with domestic and international gateways.', 'zorem-ui' ),
				'url'    => 'https://www.zorem.com/product/sms-for-woocommerce/',
				'badge'  => '',
				'stat'   => '6k+ stores',
			),
			'advanced-local-pickup-pro/advanced-local-pickup-pro.php' => array(
				'name'   => 'Advanced Local Pickup Pro',
				'slug'   => 'advanced-local-pickup-pro/advanced-local-pickup-pro.php',
				'icon'   => 'store',
				'logo'   => '',
				'accent' => '#16A34A',
				'tint'   => '#DCFCE7',
				'desc'   => __( 'Supercharge pickup schedules, offer precise contact-free local pickup times, assign inventory reserves across dynamic multiple regional coordinates, configure localized discounts and split operational hours effortlessly.', 'zorem-ui' ),
				'url'    => 'https://www.zorem.com/product/zorem-local-pickup-pro/',
				'badge'  => '',
				'stat'   => '4k+ stores',
			),
			'country-base-restrictions-pro-addon/country-base-restrictions-pro-addon.php' => array(
				'name'   => 'Country Based Restrictions Pro',
				'slug'   => 'country-base-restrictions-pro-addon/country-base-restrictions-pro-addon.php',
				'icon'   => 'globe',
				'logo'   => '',
				'accent' => '#EA580C',
				'tint'   => '#FFEDD5',
				'desc'   => __( 'Control catalog visibility dynamically. Use safe IP geolocation heuristics to easily allow, restrict, or filter selected product availability across specific global geo environments and border codes.', 'zorem-ui' ),
				'url'    => 'https://www.zorem.com/product/country-based-restriction-pro/',
				'badge'  => '',
				'stat'   => '3k+ active',
			),
			'customer-email-verification-pro/customer-email-verification-pro.php' => array(
				'name'   => 'Customer Email Verification',
				'slug'   => 'customer-email-verification-pro/customer-email-verification-pro.php',
				'icon'   => 'shield-check',
				'logo'   => '',
				'accent' => '#DC2626',
				'tint'   => '#FEE2E2',
				'desc'   => __( 'Block dummy checkouts, malicious registration scripts and spam sign-up queues by requiring multi-step verification code validation before customers complete purchases or establish accounts.', 'zorem-ui' ),
				'url'    => 'https://www.zorem.com/product/customer-email-verification/',
				'badge'  => __( 'Highly Rated', 'zorem-ui' ),
				'stat'   => '11k+ stores',
			),
			'sales-report-email-pro/sales-report-email-pro.php' => array(
				'name'   => 'Sales Report Email Pro',
				'slug'   => 'sales-report-email-pro/sales-report-email-pro.php',
				'icon'   => 'mail',
				'logo'   => '',
				'accent' => '#9333EA',
				'tint'   => '#F3E8FF',
				'desc'   => __( 'Receive high-fidelity operational digests directly in your inbox. Deliver elegant daily, weekly or custom periodic sales charts, average cart tracking metrics and order analytics on auto-pilot.', 'zorem-ui' ),
				'url'    => 'https://www.zorem.com/product/woocommerce-sales-report-email-pro/',
				'badge'  => '',
				'stat'   => '2k+ stores',
			),
		);

		// Make sure is_plugin_active() is available — eco grid renders on the
		// License tab which is always admin-side, but plugin.php isn't always
		// loaded that early.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( $plugins as $key => $p ) {
			$plugins[ $key ]['url'] .= $utm_suffix;
			// Dynamic stat: when the plugin really IS installed + active on
			// this site, replace the marketing stat with the "Active in this
			// store" badge. The caller's own plugin is unset just below so we
			// never mark the host plugin as active in its own grid.
			if ( is_plugin_active( $key ) ) {
				$plugins[ $key ]['stat'] = __( 'Active in this store', 'zorem-ui' );
			}
		}

		if ( '' !== $current_slug && isset( $plugins[ $current_slug ] ) ) {
			unset( $plugins[ $current_slug ] );
		}

		return array_values( $plugins );
	}
}
