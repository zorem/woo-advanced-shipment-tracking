<?php
/**
 * Bulk Paste tab — PRO-locked preview (free version).
 *
 * Top header banner stays as-is. A single .zui-lock-section upgrade card sits
 * below it, and the stepper + wizard render dimmed/non-interactive (no
 * per-row PRO badges).
 *
 * @package wc_advanced_shipment_tracking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ast_free_settings_icon' ) ) {
	require_once SHIPMENT_TRACKING_PATH . '/includes/settings/partials/icons.php';
}

$ast_upgrade_url = 'https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/?utm_source=wp-admin&utm_medium=plugin-setting&utm_campaign=upgrade-to-pro';
?>
<section class="tab_section" id="bulk-paste-tab">
	<div class="ast-set-csv ast-set-bp ast-locked-bp" aria-disabled="true">

		<?php /* Top header banner (kept as-is) */ ?>
		<div class="ast-set-csv-banner">
			<div class="ast-set-csv-banner__lead">
				<span class="ast-set-csv-banner__icon"><?php ast_free_settings_icon( 'clipboard-list' ); ?></span>
				<div>
					<h2 class="ast-set-csv-banner__title">
						<?php esc_html_e( 'Smart Bulk Paste Order Dispatcher', 'woo-advanced-shipment-tracking' ); ?>
						<span class="zui-locked__badge">PRO</span>
					</h2>
					<p class="ast-set-csv-banner__text"><?php esc_html_e( 'Instantly paste columns of order data directly from Excel, CSV, or text. Automatically align couriers, map headers, and synchronize shipping keys.', 'woo-advanced-shipment-tracking' ); ?></p>
				</div>
			</div>
		</div>

		<?php /* Lock section — single upgrade CTA below the header */ ?>
		<div class="zui-lock-section">
			<div class="zui-lock-section__icon"><?php zui_icon( 'lock' ); ?></div>
			<h3 class="zui-lock-section__title">
				<?php esc_html_e( 'Unlock Bulk Paste', 'woo-advanced-shipment-tracking' ); ?>
				<span class="zui-locked__badge">PRO</span>
			</h3>
			<p class="zui-lock-section__desc">
				<?php esc_html_e( 'Paste hundreds of orders from a spreadsheet, auto-map columns, validate against your couriers, and dispatch in seconds. Upgrade to AST Pro to use Bulk Paste.', 'woo-advanced-shipment-tracking' ); ?>
			</p>
			<a class="zui-btn-primary zui-lock-section__cta" href="<?php echo esc_url( $ast_upgrade_url ); ?>" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Upgrade to PRO', 'woo-advanced-shipment-tracking' ); ?>
			</a>
		</div>

		<?php /* Preview: stepper + wizard rendered but dimmed + non-interactive */ ?>
		<div class="zui-lock-section__preview">

			<?php /* Stepper (locked) */ ?>
			<div class="zui-card ast-set-csv-stepcard">
				<ol class="wc-progress-steps ast-set-csv-steps">
					<li class="progress_step1 active"><span class="ast-set-csv-steplabel"><?php esc_html_e( 'Paste Raw Records', 'woo-advanced-shipment-tracking' ); ?></span></li>
					<li class="progress_step2"><span class="ast-set-csv-steplabel"><?php esc_html_e( 'Align & Map Columns', 'woo-advanced-shipment-tracking' ); ?></span></li>
					<li class="progress_step3"><span class="ast-set-csv-steplabel"><?php esc_html_e( 'Fulfillment Status', 'woo-advanced-shipment-tracking' ); ?></span></li>
				</ol>
			</div>

			<?php /* Wizard card (paste view only — locked) */ ?>
			<div class="ast-set-csv-wizard ast-locked-bp__wizard">

				<div class="ast-set-bp-step" data-bpstep="paste">

					<div class="ast-set-csv-row">
						<div class="min-w-0">
							<span class="ast-set-csv-row__title"><?php esc_html_e( 'Paste raw columns here:', 'woo-advanced-shipment-tracking' ); ?></span>
							<p class="ast-set-csv-row__sub"><?php esc_html_e( 'Use newline-separated rows copied directly from your desktop sheets.', 'woo-advanced-shipment-tracking' ); ?></p>
						</div>
						<div class="ast-set-bp-sample">
							<button type="button" class="zui-btn-secondary" disabled>
								<?php ast_free_settings_icon( 'clipboard-list' ); ?>
								<span><?php esc_html_e( 'Load Sample Data', 'woo-advanced-shipment-tracking' ); ?></span>
							</button>
						</div>
					</div>

					<div class="ast-set-bp-textarea-wrap">
						<textarea class="ast-set-bp-textarea" rows="7" placeholder="order_id,tracking_provider,tracking_number,date_shipped,status_shipped,shipping_note&#10;#18402,UPS,1Z999AA10123456784,02-06-2026,Completed,Fragile packages&#10;#18401,FedEx,48123098234,02-06-2026,Completed,Handled carefully" disabled></textarea>
						<div class="ast-set-bp-textmeta">
							<span><?php esc_html_e( 'Supports comma-separated values (CSV) for each order line.', 'woo-advanced-shipment-tracking' ); ?></span>
							<span class="ast-set-bp-rows"><?php esc_html_e( 'Rows:', 'woo-advanced-shipment-tracking' ); ?> <strong>0</strong></span>
						</div>
					</div>

					<div class="ast-set-bp-controls">
						<div class="ast-set-bp-sep">
							<span class="ast-set-csv-row__title"><?php esc_html_e( 'Columns Separator Status:', 'woo-advanced-shipment-tracking' ); ?></span>
							<div class="ast-set-bp-sep__box">
								<?php ast_free_settings_icon( 'check' ); ?>
								<span><?php esc_html_e( 'Strict Comma-Separation (CSV Format)', 'woo-advanced-shipment-tracking' ); ?></span>
							</div>
						</div>
						<div class="ast-set-bp-switches">
							<div class="ast-set-csv-row ast-set-csv-row--last ast-locked-row">
								<div class="min-w-0">
									<span class="ast-set-csv-row__title"><?php esc_html_e( 'First line contains column labels?', 'woo-advanced-shipment-tracking' ); ?></span>
									<p class="ast-set-csv-row__sub"><?php esc_html_e( 'Enable if the first pasted line is the headers row.', 'woo-advanced-shipment-tracking' ); ?></p>
								</div>
								<div class="zui-row__control">
									<label class="zui-toggle" aria-disabled="true">
										<input type="checkbox" class="zui-toggle__input" disabled checked>
										<span class="zui-toggle__track"><span class="zui-toggle__thumb"></span></span>
									</label>
								</div>
							</div>
							<div class="ast-set-csv-row ast-set-csv-row--last ast-locked-row">
								<div class="min-w-0">
									<span class="ast-set-csv-row__title"><?php esc_html_e( 'Replace existing tracking details?', 'woo-advanced-shipment-tracking' ); ?></span>
									<p class="ast-set-csv-row__sub"><?php esc_html_e( 'Overwrite shipping info on previously fulfilled orders if matched.', 'woo-advanced-shipment-tracking' ); ?></p>
								</div>
								<div class="zui-row__control">
									<label class="zui-toggle" aria-disabled="true">
										<input type="checkbox" class="zui-toggle__input" disabled>
										<span class="zui-toggle__track"><span class="zui-toggle__thumb"></span></span>
									</label>
								</div>
							</div>
						</div>
					</div>

					<div class="ast-set-csv-submit-row">
						<button type="button" class="zui-btn-ghost" disabled><?php esc_html_e( 'Clear Content', 'woo-advanced-shipment-tracking' ); ?></button>
						<button type="button" class="zui-btn-primary" disabled>
							<span><?php esc_html_e( 'Process Records', 'woo-advanced-shipment-tracking' ); ?></span>
							<?php ast_free_settings_icon( 'arrow-right' ); ?>
						</button>
					</div>
				</div>

				<?php /* Footer */ ?>
				<div class="ast-set-csv-footer">
					<span class="ast-set-bp-footnote"><?php ast_free_settings_icon( 'sparkles' ); ?><span><?php esc_html_e( 'Pasted values are validated against your registered couriers as they import.', 'woo-advanced-shipment-tracking' ); ?></span></span>
					<a class="ast-set-csv-footer__link" href="https://docs.zorem.com/docs/ast-pro/advanced-shipment-tracking-pro/csv-import/" target="_blank" rel="noopener noreferrer">
						<?php ast_free_settings_icon( 'book' ); ?><span><?php esc_html_e( 'Format requirements & instructions', 'woo-advanced-shipment-tracking' ); ?></span>
					</a>
				</div>
			</div>

		</div>

	</div>
</section>
