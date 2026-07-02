<?php
/**
 * Edit Shipping Carrier modal body (free version).
 *
 * Injected via AJAX from get_provider_details_fun() into
 * #ast-modal-edit > .ast-modal-edit-content. Structure mirrors
 * ast-pro/.../modals.php — head (title + sub + close + preview
 * chip) + body. In free the body is a PRO upsell card.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'zui_icon' ) ) {
	require_once dirname( __DIR__, 2 ) . '/assets/zui/icons.php';
}

$upload_dir       = wp_upload_dir();
$ast_directory    = $upload_dir['baseurl'] . '/ast-shipping-providers/';
$ast_upgrade_url  = 'https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/?utm_source=wp-admin&utm_medium=edit-carrier&utm_campaign=upgrad-to-pro';
$ast_logo_letters = strtoupper( mb_substr( (string) $shippment_provider->provider_name, 0, 2 ) );
$ast_logo_url     = ( 1 == $shippment_provider->shipping_default )
	? $ast_directory . esc_html( $shippment_provider->ts_slug ) . '.png?v=' . wc_advanced_shipment_tracking()->version
	: '';
?>
<div class="zui-modal__head zui-modal__head--edit slidout_header">
	<div class="zui-modal__head-row">
		<div class="slidout_header_title">
			<h3 class="zui-modal__title slidout_title"><?php esc_html_e( 'Edit Shipping Carrier', 'woo-advanced-shipment-tracking' ); ?></h3>
			<p class="zui-modal__sub slidout_subtitle">
				<?php esc_html_e( 'Customize display preferences for', 'woo-advanced-shipment-tracking' ); ?>
				<span class="ast-edit-carrier-name"><?php echo esc_html( $shippment_provider->provider_name ); ?></span>
			</p>
		</div>
		<button type="button" class="zui-modal__close slidout_close edit_slidout_close" data-modal-close aria-label="<?php esc_attr_e( 'Close', 'woo-advanced-shipment-tracking' ); ?>"><?php zui_icon( 'x' ); ?></button>
	</div>

	<div class="ast-set-edit-preview">
		<span class="ast-set-carrier-logo ast-edit-carrier-logo">
			<?php if ( $ast_logo_url ) : ?>
				<img class="provider-thumb" src="<?php echo esc_url( $ast_logo_url ); ?>" alt="<?php echo esc_attr( $shippment_provider->provider_name ); ?>" onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex';">
				<span class="ast-set-carrier-logo__fallback" style="display:none;"><?php echo esc_html( $ast_logo_letters ); ?></span>
			<?php else : ?>
				<span class="ast-set-carrier-logo__fallback"><?php echo esc_html( $ast_logo_letters ); ?></span>
			<?php endif; ?>
		</span>
		<div class="min-w-0">
			<h4 class="ast-edit-carrier-name-2"><?php echo esc_html( $shippment_provider->provider_name ); ?></h4>
			<p class="ast-edit-carrier-country"><?php echo esc_html( $shippment_provider->shipping_country_name ); ?></p>
		</div>
	</div>
</div>

<div class="zui-modal__body slidout_body">
	<div class="zui-lock-section">
		<div class="zui-lock-section__icon"><?php zui_icon( 'lock' ); ?></div>
		<h3 class="zui-lock-section__title">
			<?php esc_html_e( 'Unlock Edit Shipping Carrier', 'woo-advanced-shipment-tracking' ); ?>
			<span class="zui-locked__badge">PRO</span>
		</h3>
		<p class="zui-lock-section__desc">
			<?php esc_html_e( 'Upgrade to Advanced Shipment Tracking Pro to set a default carrier, override display names, map API alias names, upload custom logos and customise the tracking URL pattern.', 'woo-advanced-shipment-tracking' ); ?>
		</p>
		<a class="zui-btn-primary zui-lock-section__cta get_feature_span" href="<?php echo esc_url( $ast_upgrade_url ); ?>" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'Upgrade to PRO', 'woo-advanced-shipment-tracking' ); ?>
		</a>
	</div>
</div>
