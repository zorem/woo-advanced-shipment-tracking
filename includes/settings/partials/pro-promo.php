<?php
/**
 * AST - Settings screen: AST PRO sale banner (BFCM 2026).
 *
 * Sits directly under the top tab bar, so it shows on every tab of the AST settings
 * screen except the ones listed in $ast_promo_skip_tabs. There is no dismiss control
 * by design: the banner shows only inside the sale window and retires itself after.
 *
 * Two things hide it:
 *   - the current time is outside the sale window (checked here, so nothing is printed),
 *   - AST PRO is active (nothing left to upsell).
 *
 * The window matches the BFCM admin notice in
 * class-wc-advanced-shipment-tracking-admin-notice.php (BFCM_STARTS / BFCM_ENDS). The
 * link carries the bfcm-2026 campaign with its own placement, so the banner and the
 * notice can be told apart in GA4.
 *
 * @package WooCommerce_Advanced_Shipment_Tracking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * PHPCS / Plugin Check suppressions for this file.
 *
 * WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound: These are presentation variables
 * local to this template, not true globals.
 */
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- These are presentation variables local to this template, not true globals.

// Tabs that never carry the banner: Go Pro is already a full upgrade pitch, and
// TrackShip is a different product. Every other tab (Settings, Shipping Carriers,
// Integrations, CSV Import, Bulk Paste) shows it.
$ast_promo_skip_tabs = array( 'addons', 'trackship' );

// Campaign settings. Editing these lines retires or re-runs the sale; nothing else in
// the plugin needs to change. Both dates are inclusive, in the store's timezone.
$ast_promo_first_day = '2026-11-19';
$ast_promo_last_day  = '2026-12-01';
$ast_promo_coupon    = 'BFCM40';

// PRO is installed - there is nothing to sell.
if ( class_exists( 'ast_pro' ) ) {
	return;
}

$ast_promo_starts = new DateTime( $ast_promo_first_day . ' 00:00:00', wp_timezone() );
$ast_promo_ends   = new DateTime( $ast_promo_last_day . ' 23:59:59', wp_timezone() );

// Outside the sale window: print nothing.
if ( time() < $ast_promo_starts->getTimestamp() || time() > $ast_promo_ends->getTimestamp() ) {
	return;
}

$ast_promo_url = function_exists( 'ast_utm_url' )
	? ast_utm_url( 'https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/', 'settings-banner', 'bfcm-2026' )
	: 'https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/';

// The banner is printed once, above all the tab panels, and tabs swap client-side
// without a reload - so PHP sets the state for the tab the page loaded on, and
// ast-settings.js keeps it in sync from there (same skip list, read off the banner).
$ast_promo_tab = isset( $ast_active_tab ) ? $ast_active_tab : 'settings';
?>
<div class="ast-promo ast-promo--bfcm" role="status" data-ast-promo-skip-tabs="<?php echo esc_attr( implode( ' ', $ast_promo_skip_tabs ) ); ?>" <?php echo in_array( $ast_promo_tab, $ast_promo_skip_tabs, true ) ? 'hidden' : ''; ?>>

	<div class="ast-promo__panel" aria-hidden="true">
		<span class="ast-promo__panel-label"><?php esc_html_e( 'Black Friday', 'woo-advanced-shipment-tracking' ); ?></span>
		<span class="ast-promo__panel-num">40<small>%</small></span>
		<span class="ast-promo__panel-sub"><?php esc_html_e( 'off AST PRO', 'woo-advanced-shipment-tracking' ); ?></span>
	</div>

	<div class="ast-promo__body">

		<div class="ast-promo__copy">

			<strong class="ast-promo__title"><?php esc_html_e( '🛍️ Black Friday: 40% off AST PRO — only sale of the year', 'woo-advanced-shipment-tracking' ); ?></strong>

			<p class="ast-promo__text">
				<?php
				echo wp_kses_post( sprintf(
					/* translators: %s: coupon code. */
					esc_html__( 'Use code %s', 'woo-advanced-shipment-tracking' ),
					'<code class="ast-promo__coupon">' . esc_html( $ast_promo_coupon ) . '</code>'
				) );
				?>
				<span class="ast-promo__sep" aria-hidden="true">·</span><span class="ast-promo__old">$129</span> → <span class="ast-promo__new">$77</span>
				<span class="ast-promo__sep" aria-hidden="true">·</span><?php esc_html_e( 'New purchases only', 'woo-advanced-shipment-tracking' ); ?>
				<span class="ast-promo__sep" aria-hidden="true">·</span>
				<?php
				/* translators: %s: date the sale ends. */
				printf( esc_html__( 'Ends %s', 'woo-advanced-shipment-tracking' ), esc_html( wp_date( 'M j, Y', $ast_promo_ends->getTimestamp() ) ) );
				?>
			</p>

		</div>

		<a class="ast-promo__cta" href="<?php echo esc_url( $ast_promo_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Upgrade → $77', 'woo-advanced-shipment-tracking' ); ?></a>

	</div>

</div>
