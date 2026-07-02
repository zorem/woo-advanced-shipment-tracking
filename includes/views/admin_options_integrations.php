<?php
/**
 * Integrations tab — PRO-locked preview (free version).
 *
 * Top header banner stays as-is. A single .zui-lock-section upgrade card sits
 * below it, and the search + card grid render dimmed/non-interactive.
 *
 * @package wc_advanced_shipment_tracking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ast_free_settings_icon' ) ) {
	require_once SHIPMENT_TRACKING_PATH . '/includes/settings/partials/icons.php';
}

$ast_integration = AST_Integration::get_instance();
$integrations    = $ast_integration->integrations_settings_options();
$plugin_url      = wc_advanced_shipment_tracking()->plugin_dir_url();
$ast_upgrade_url = 'https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/?utm_source=wp-admin&utm_medium=integration&utm_campaign=upgrad-now';
?>
<section class="tab_section" id="integrations-tab">

	<div class="ast-set-integrations ast-locked-intg" id="ast-set-integrations" aria-disabled="true">

		<?php /* Top header banner (kept as-is) */ ?>
		<div class="ast-set-intg-banner">
			<span class="ast-set-intg-banner__icon"><?php ast_free_settings_icon( 'app-window' ); ?></span>
			<div>
				<h4 class="ast-set-intg-banner__title"><?php esc_html_e( 'Fulfillment Integrations & Live Automation', 'woo-advanced-shipment-tracking' ); ?></h4>
				<p class="ast-set-intg-banner__text"><?php esc_html_e( 'Automatically import tracking info from leading shipping services like ShipStation, WooCommerce Shipping, AliExpress Dropshipping, GLS and more. Available in AST PRO.', 'woo-advanced-shipment-tracking' ); ?></p>
			</div>
		</div>

		<?php /* Lock section — single upgrade CTA below the header */ ?>
		<div class="zui-lock-section">
			<div class="zui-lock-section__icon"><?php zui_icon( 'lock' ); ?></div>
			<h3 class="zui-lock-section__title">
				<?php esc_html_e( 'Unlock Integrations', 'woo-advanced-shipment-tracking' ); ?>
				<span class="zui-locked__badge">PRO</span>
			</h3>
			<p class="zui-lock-section__desc">
				<?php esc_html_e( 'Connect with leading shipping services to automatically import tracking info, mark orders as shipped, and notify customers—no manual work required. Upgrade to AST Pro to use Integrations.', 'woo-advanced-shipment-tracking' ); ?>
			</p>
			<a class="zui-btn-primary zui-lock-section__cta" href="<?php echo esc_url( $ast_upgrade_url ); ?>" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Upgrade to PRO', 'woo-advanced-shipment-tracking' ); ?>
			</a>
		</div>

		<?php /* Preview: search + card grid rendered but dimmed + non-interactive */ ?>
		<div class="zui-lock-section__preview">

			<div class="ast-set-intg-search">
				<span class="ast-set-intg-search__icon"><?php ast_free_settings_icon( 'search' ); ?></span>
				<input type="text" id="ast-intg-search" class="zui-input" placeholder="<?php esc_attr_e( 'Search by integration…', 'woo-advanced-shipment-tracking' ); ?>" autocomplete="off" disabled>
			</div>

			<div class="ast-set-intg-grid" id="ast-intg-grid">
				<?php foreach ( $integrations as $integrations_id => $array ) : ?>
					<?php $logo = $plugin_url . 'assets/images/' . $array['img']; ?>
					<div class="zui-card ast-set-intg-card" data-iid="<?php echo esc_attr( $integrations_id ); ?>" data-name="<?php echo esc_attr( $array['title'] ); ?>">
						<div class="ast-set-intg-main">
							<span class="ast-set-intg-logo">
								<img class="provider-thumb" src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $array['title'] ); ?>" loading="lazy">
							</span>
							<span class="ast-set-intg-info">
								<span class="ast-set-intg-name">
									<?php echo esc_html( $array['title'] ); ?>
									<span class="ast-set-intg-dot" aria-hidden="true"></span>
								</span>
								<span class="ast-set-intg-meta">
									<?php esc_html_e( 'Fulfillment', 'woo-advanced-shipment-tracking' ); ?> &bull;
									<span class="ast-set-intg-status"><?php esc_html_e( 'Locked', 'woo-advanced-shipment-tracking' ); ?></span>
								</span>
							</span>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="ast-set-intg-empty" id="ast-intg-empty" hidden><?php esc_html_e( 'No matching integrations found.', 'woo-advanced-shipment-tracking' ); ?></div>

		</div>

	</div>

</section>
