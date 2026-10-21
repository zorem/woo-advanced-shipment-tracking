<?php
/**
 * AST - Settings screen: AST PRO upgrade offer (countdown card).
 *
 * Sits directly under the top tab bar, so it shows on every tab of the AST settings
 * screen. There is no dismiss control by design: the card retires itself when the
 * offer window closes, and nothing else hides it.
 *
 * Three things end it:
 *   - the offer end date passes (checked here, so the markup is never printed),
 *   - the countdown reaches zero while the screen is open (ast-settings.js removes it),
 *   - AST PRO is active (nothing left to upsell).
 *
 * The end date matches the one used by the Agent OS launch card in
 * class-wc-advanced-shipment-tracking-admin-notice.php, and the link goes through
 * ast_pro_upgrade_url() so this placement is separable in GA4 (utm_content).
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

// Tabs that never carry the offer card: Go Pro is already a full upgrade pitch, and
// TrackShip is a different product. Every other tab (Settings, Shipping Carriers,
// Integrations, CSV Import, Bulk Paste) shows it.
$ast_promo_skip_tabs = array( 'addons', 'trackship' );

// Campaign settings. Editing these two lines retires or re-runs the offer; nothing
// else in the plugin needs to change.
//
// The date is the LAST DAY the coupon can be used, inclusive: the card stays up for
// the whole of that day in the store's timezone and disappears the moment it ends.
$ast_promo_last_day = '2026-11-15';
$ast_promo_coupon   = 'ASTPRO30';

// PRO is installed - there is nothing to sell.
if ( class_exists( 'ast_pro' ) ) {
	return;
}

// Last second of the final day (what the copy prints), and the cut-off one second
// later (when the card goes) - so 23:59:59 on the final day still shows the offer.
$ast_promo_ends   = new DateTime( $ast_promo_last_day . ' 23:59:59', wp_timezone() );
$ast_promo_cutoff = $ast_promo_ends->getTimestamp() + 1;
$ast_promo_left   = $ast_promo_cutoff - time();

// Offer over: print nothing, ever again.
if ( $ast_promo_left <= 0 ) {
	return;
}

$ast_promo_days  = (int) floor( $ast_promo_left / DAY_IN_SECONDS );
$ast_promo_hours = (int) floor( ( $ast_promo_left % DAY_IN_SECONDS ) / HOUR_IN_SECONDS );
$ast_promo_mins  = (int) floor( ( $ast_promo_left % HOUR_IN_SECONDS ) / MINUTE_IN_SECONDS );
$ast_promo_secs  = (int) ( $ast_promo_left % MINUTE_IN_SECONDS );

$ast_promo_url = function_exists( 'ast_pro_upgrade_url' )
	? ast_pro_upgrade_url( 'settings-offer-30' )
	: 'https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/';

// The card is printed once, above all the tab panels, and tabs swap client-side
// without a reload - so PHP sets the state for the tab the page loaded on, and
// ast-settings.js keeps it in sync from there (same skip list, read off the card).
$ast_promo_tab = isset( $ast_active_tab ) ? $ast_active_tab : 'settings';
?>
<div class="ast-promo" role="status" data-ast-promo-skip-tabs="<?php echo esc_attr( implode( ' ', $ast_promo_skip_tabs ) ); ?>" <?php echo in_array( $ast_promo_tab, $ast_promo_skip_tabs, true ) ? 'hidden' : ''; ?>>

	<div class="ast-promo__panel" aria-hidden="true">
		<span class="ast-promo__panel-label"><?php esc_html_e( 'Save', 'woo-advanced-shipment-tracking' ); ?></span>
		<span class="ast-promo__panel-num">30<small>%</small></span>
		<span class="ast-promo__panel-sub"><?php esc_html_e( 'on AST PRO', 'woo-advanced-shipment-tracking' ); ?></span>
	</div>

	<div class="ast-promo__body">

		<strong class="ast-promo__title"><?php esc_html_e( 'Automate your fulfillment with AST PRO', 'woo-advanced-shipment-tracking' ); ?></strong>

		<p class="ast-promo__text">
			<?php esc_html_e( 'Fulfill every order from one dashboard, import tracking numbers automatically by CSV, FTP or API, sync tracking to PayPal and Stripe, and send branded shipment and partial shipment emails.', 'woo-advanced-shipment-tracking' ); ?>
			<?php
			printf(
				/* translators: 1: coupon code, 2: date the offer ends. */
				wp_kses_post( __( 'Apply coupon %1$s at checkout &mdash; valid until %2$s.', 'woo-advanced-shipment-tracking' ) ),
				'<code class="ast-promo__coupon">' . esc_html( $ast_promo_coupon ) . '</code>',
				esc_html( wp_date( 'F j, Y', $ast_promo_ends->getTimestamp() ) )
			);
			?>
		</p>

		<div class="ast-promo__row">

			<span class="ast-promo__clock-label">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
				<?php esc_html_e( 'Offer ends in', 'woo-advanced-shipment-tracking' ); ?>
			</span>

			<span class="ast-promo__clock" data-ast-promo-countdown data-ends="<?php echo esc_attr( $ast_promo_cutoff * 1000 ); ?>">
				<span class="ast-promo__unit">
					<b data-ast-promo-days><?php echo esc_html( sprintf( '%02d', $ast_promo_days ) ); ?></b>
					<i><?php esc_html_e( 'days', 'woo-advanced-shipment-tracking' ); ?></i>
				</span>
				<span class="ast-promo__unit">
					<b data-ast-promo-hours><?php echo esc_html( sprintf( '%02d', $ast_promo_hours ) ); ?></b>
					<i><?php esc_html_e( 'hrs', 'woo-advanced-shipment-tracking' ); ?></i>
				</span>
				<span class="ast-promo__unit">
					<b data-ast-promo-mins><?php echo esc_html( sprintf( '%02d', $ast_promo_mins ) ); ?></b>
					<i><?php esc_html_e( 'min', 'woo-advanced-shipment-tracking' ); ?></i>
				</span>
				<span class="ast-promo__unit">
					<b data-ast-promo-secs><?php echo esc_html( sprintf( '%02d', $ast_promo_secs ) ); ?></b>
					<i><?php esc_html_e( 'sec', 'woo-advanced-shipment-tracking' ); ?></i>
				</span>
			</span>

			<a class="ast-promo__cta" href="<?php echo esc_url( $ast_promo_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Upgrade now', 'woo-advanced-shipment-tracking' ); ?></a>

		</div>

	</div>

</div>
