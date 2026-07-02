<?php
/**
 * Free version — Stripe Tracking section: PRO-locked preview.
 *
 * Single upgrade banner at top + read-only preview of the PRO field layout
 * (rows visible but dimmed and non-interactive via .zui-lock-section__preview).
 *
 * @package wc_advanced_shipment_tracking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ast_upgrade_url = 'https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/?utm_source=wp-admin&utm_medium=plugin-setting&utm_campaign=upgrade-to-pro';
?>

<div class="zui-lock-section">
	<div class="zui-lock-section__icon"><?php zui_icon( 'lock' ); ?></div>
	<h3 class="zui-lock-section__title">
		<?php esc_html_e( 'Unlock Stripe Tracking', 'woo-advanced-shipment-tracking' ); ?>
		<span class="zui-lock-section__badge">PRO</span>
	</h3>
	<p class="zui-lock-section__desc">
		<?php esc_html_e( 'Automatically sync tracking information from your store to Stripe transactions. Reduce disputes, build buyer trust, and meet Stripe seller requirements. Upgrade to AST Pro to unlock these options.', 'woo-advanced-shipment-tracking' ); ?>
	</p>
	<a class="zui-btn-primary zui-lock-section__cta" href="<?php echo esc_url( $ast_upgrade_url ); ?>" target="_blank">
		<?php esc_html_e( 'Upgrade to PRO', 'woo-advanced-shipment-tracking' ); ?>
	</a>
</div>

<div class="zui-lock-section__preview zui-card zui-card--locked">

	<div class="zui-row zui-row--inline ast-locked-row">
		<div class="zui-row__head">
			<span class="zui-row__label"><?php esc_html_e( 'Enable Stripe Tracking', 'woo-advanced-shipment-tracking' ); ?></span>
			<p class="zui-row__desc"><?php esc_html_e( 'Enable this option to synchronize tracking information between your store and Stripe transactions. When you fulfill an order and add tracking information, the tracking number, shipping provider, and date will be sent to the corresponding Stripe transaction.', 'woo-advanced-shipment-tracking' ); ?></p>
		</div>
		<div class="zui-row__control">
			<label class="zui-toggle" aria-disabled="true">
				<input type="checkbox" class="zui-toggle__input" disabled>
				<span class="zui-toggle__track"><span class="zui-toggle__thumb"></span></span>
			</label>
		</div>
	</div>

	<div class="zui-row ast-locked-row">
		<div class="zui-row__head">
			<span class="zui-row__label"><?php esc_html_e( 'Stripe API Client Secret', 'woo-advanced-shipment-tracking' ); ?></span>
			<p class="zui-row__desc"><?php esc_html_e( 'Enter your Stripe REST API Client Secret.', 'woo-advanced-shipment-tracking' ); ?></p>
		</div>
		<div class="zui-row__control">
			<input type="password" class="zui-input" placeholder="" disabled>
		</div>
	</div>

	<div class="zui-row ast-locked-row">
		<div class="zui-row__head">
			<span class="zui-row__label"><?php esc_html_e( 'Stripe Payment Methods', 'woo-advanced-shipment-tracking' ); ?></span>
			<p class="zui-row__desc"><?php esc_html_e( 'Choose for which Payment method you want to sync the tracking to Stripe.', 'woo-advanced-shipment-tracking' ); ?></p>
		</div>
		<div class="zui-row__control">
			<div class="zui-ms is-disabled" aria-disabled="true">
				<div class="zui-ms__control" tabindex="-1" role="combobox" aria-disabled="true" aria-expanded="false">
					<div class="zui-ms__chips">
						<span class="zui-ms__placeholder">&nbsp;</span>
					</div>
					<span class="zui-ms__chevron"><?php ast_free_settings_icon( 'chevron-down' ); ?></span>
				</div>
			</div>
		</div>
	</div>

	<div class="zui-row zui-row--inline ast-locked-row">
		<div class="zui-row__head">
			<span class="zui-row__label"><?php esc_html_e( 'Enable log', 'woo-advanced-shipment-tracking' ); ?></span>
			<p class="zui-row__desc"><?php esc_html_e( 'Enabling this option will save logs of any errors that may occur when syncing tracking information with the Stripe Tracking API.', 'woo-advanced-shipment-tracking' ); ?></p>
		</div>
		<div class="zui-row__control">
			<label class="zui-toggle" aria-disabled="true">
				<input type="checkbox" class="zui-toggle__input" disabled>
				<span class="zui-toggle__track"><span class="zui-toggle__thumb"></span></span>
			</label>
		</div>
	</div>

</div>
