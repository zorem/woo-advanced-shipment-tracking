<?php
/**
 * Bulk Upload (CSV Import) tab — re-skinned for the new ZUI shell.
 *
 * Markup mirrors the AST PRO CSV Import wizard (3-step stepper, drag-and-drop
 * picker, ZUI rows + toggles + radio cards, progress / done console). Every
 * input name/id, AJAX action, nonce, jQuery selector and data-* hook used by
 * shipping_row.js / ast-settings.js is preserved verbatim:
 *
 *   #wc_ast_upload_csv_form, #trcking_csv_file, #replace_tracking_info,
 *   name="date_format_for_csv_import" radios, #nonce_csv_import,
 *   .upload_csv_div, .bulk_upload_status_div(.csv_import_done),
 *   ol.wc-progress-steps > .progress_step1/2/3,
 *   .progress-moved .progress-bar2, .progress_number,
 *   .csv_upload_status, .bulk_upload_status_action,
 *   .bulk_upload_status_heading_tr, .view_csv_error_details,
 *   .csv_upload_again, .bulk_upload_status_overview_td.csv_success_msg /
 *   .csv_fail_msg, .bulk_upload_status_detail_error_tr, .csv_error_details_ul,
 *   action=wc_ast_upload_csv_form_update.
 *
 * Free version exposes only the Manual CSV wizard. The SFTP/FTP automation
 * panel is shipped as its own locked PRO section (settings/partials/locked-ftp.php)
 * registered in section-map.php, so it is intentionally NOT duplicated here.
 *
 * @package wc_advanced_shipment_tracking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ast_free_settings_icon' ) ) {
	require_once SHIPMENT_TRACKING_PATH . '/includes/settings/partials/icons.php';
}

$ast_csv_date   = get_ast_settings( 'ast_general_settings', 'date_format_for_csv_import', 'd-m-Y' );
$ast_orders_url = admin_url( 'edit.php?post_type=shop_order' );
$ast_sample_url = wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/tracking.csv';
?>
<section class="tab_section" id="csv-import-tab">
	<div class="ast-set-csv" id="ast-set-csv">

		<?php /* Header banner + Manual / Automated sub-tab toggle */ ?>
		<div class="ast-set-csv-banner">
			<div class="ast-set-csv-banner__lead">
				<span class="ast-set-csv-banner__icon"><?php ast_free_settings_icon( 'sliders-horizontal' ); ?></span>
				<div>
					<h2 class="ast-set-csv-banner__title"><?php esc_html_e( 'CSV Search & Tracking Import', 'woo-advanced-shipment-tracking' ); ?></h2>
					<p class="ast-set-csv-banner__text"><?php esc_html_e( 'Manually import bulk order tracking datasheets, or schedule background cron polling jobs over secure remote file links.', 'woo-advanced-shipment-tracking' ); ?></p>
				</div>
			</div>
			<div class="zui-segmented" role="tablist">
				<button type="button" class="zui-segmented__item is-active" data-csvtab="manual" role="tab" aria-selected="true"><?php esc_html_e( 'Manual CSV Form', 'woo-advanced-shipment-tracking' ); ?></button>
				<button type="button" class="zui-segmented__item" data-csvtab="automated" role="tab" aria-selected="false"><?php esc_html_e( 'SFTP/FTP Automation', 'woo-advanced-shipment-tracking' ); ?></button>
			</div>
		</div>

		<?php /* ============================ MANUAL CSV FORM ============================ */ ?>
		<div class="ast-set-csv-sub" data-csvsub="manual">

			<?php /* Stepper (classes driven by shipping_row.js) */ ?>
			<div class="zui-card ast-set-csv-stepcard">
				<ol class="wc-progress-steps ast-set-csv-steps">
					<li class="progress_step1 active"><span class="ast-set-csv-steplabel"><?php esc_html_e( 'Upload CSV file', 'woo-advanced-shipment-tracking' ); ?></span></li>
					<li class="progress_step2"><span class="ast-set-csv-steplabel"><?php esc_html_e( 'Import', 'woo-advanced-shipment-tracking' ); ?></span></li>
					<li class="progress_step3"><span class="ast-set-csv-steplabel"><?php esc_html_e( 'Done!', 'woo-advanced-shipment-tracking' ); ?></span></li>
				</ol>
			</div>

			<?php /* Wizard card */ ?>
			<div class="ast-set-csv-wizard">
				<form method="post" id="wc_ast_upload_csv_form" action="" enctype="multipart/form-data">

					<?php /* ---- UPLOAD VIEW ---- */ ?>
					<div class="upload_csv_div ast-set-csv-upload">

						<div class="ast-set-csv-row">
							<div class="min-w-0">
								<span class="ast-set-csv-row__title"><?php esc_html_e( 'Upload a CSV file from your computer:', 'woo-advanced-shipment-tracking' ); ?></span>
								<p class="ast-set-csv-row__sub"><?php esc_html_e( 'Select log spreadsheets containing order IDs and carrier tracking numbers.', 'woo-advanced-shipment-tracking' ); ?></p>
							</div>
							<div class="ast-set-csv-choose">
								<input type="file" name="trcking_csv_file" id="trcking_csv_file" accept=".csv" class="ast-set-csv-fileinput">
								<button type="button" class="ast-set-csv-choosebtn" id="ast-csv-choose"><?php ast_free_settings_icon( 'upload' ); ?><span><?php esc_html_e( 'Choose file', 'woo-advanced-shipment-tracking' ); ?></span></button>
								<span class="ast-set-csv-filename" id="ast-csv-filename"><?php esc_html_e( 'No file chosen', 'woo-advanced-shipment-tracking' ); ?></span>
							</div>
						</div>

						<div class="ast-set-csv-drop" id="ast-csv-drop">
							<div class="ast-set-csv-drop__empty">
								<span class="ast-set-csv-drop__icon"><?php ast_free_settings_icon( 'upload' ); ?></span>
								<span class="ast-set-csv-drop__title"><?php esc_html_e( 'Drag & drop your CSV file here', 'woo-advanced-shipment-tracking' ); ?></span>
								<p class="ast-set-csv-drop__sub"><?php esc_html_e( 'Supported formats: Standard comma-delimited columns matching your warehousing logs.', 'woo-advanced-shipment-tracking' ); ?></p>
							</div>
							<div class="ast-set-csv-drop__selected" id="ast-csv-selected" hidden>
								<span class="ast-set-csv-drop__check"><?php ast_free_settings_icon( 'check-circle' ); ?></span>
								<span class="ast-set-csv-drop__fname" id="ast-csv-selected-name"></span>
								<span class="ast-set-csv-drop__meta" id="ast-csv-selected-meta"></span>
								<div class="ast-set-csv-drop__actions">
									<button type="button" class="ast-set-csv-drop__btn ast-set-csv-drop__btn--primary" id="ast-csv-change"><?php esc_html_e( 'Change file', 'woo-advanced-shipment-tracking' ); ?></button>
									<button type="button" class="ast-set-csv-drop__btn" id="ast-csv-remove"><?php esc_html_e( 'Remove', 'woo-advanced-shipment-tracking' ); ?></button>
								</div>
							</div>
						</div>

						<div class="ast-set-csv-row">
							<div class="min-w-0">
								<span class="ast-set-csv-row__title"><?php esc_html_e( 'Choose the Shipped Date format:', 'woo-advanced-shipment-tracking' ); ?></span>
								<p class="ast-set-csv-row__sub"><?php esc_html_e( 'Standardize how dates are extracted from sheet rows.', 'woo-advanced-shipment-tracking' ); ?></p>
							</div>
							<div class="zui-radio-cards">
								<label class="zui-radio-card<?php echo 'd-m-Y' === $ast_csv_date ? ' is-selected' : ''; ?>" for="date_format_ddmmyy">
									<input type="radio" id="date_format_ddmmyy" name="date_format_for_csv_import" value="d-m-Y" class="zui-radio-card__input" <?php checked( 'd-m-Y', $ast_csv_date ); ?>>
									<span class="zui-radio-card__body">
										<span class="zui-radio-card__label">dd/mm/YYYY</span>
									</span>
								</label>
								<label class="zui-radio-card<?php echo 'm-d-Y' === $ast_csv_date ? ' is-selected' : ''; ?>" for="date_format_mmddyy">
									<input type="radio" id="date_format_mmddyy" name="date_format_for_csv_import" value="m-d-Y" class="zui-radio-card__input" <?php checked( 'm-d-Y', $ast_csv_date ); ?>>
									<span class="zui-radio-card__body">
										<span class="zui-radio-card__label">mm/dd/YYYY</span>
									</span>
								</label>
							</div>
						</div>

						<div class="ast-set-csv-row ast-set-csv-row--last">
							<div class="min-w-0">
								<span class="ast-set-csv-row__title">
									<?php esc_html_e( 'Replace tracking information?', 'woo-advanced-shipment-tracking' ); ?>
									<span class="zui-tooltip" tabindex="0" role="img" aria-label="<?php esc_attr_e( 'Keep unchecked for the tracking info to be added to any existing tracking info on the orders.', 'woo-advanced-shipment-tracking' ); ?>"><?php ast_free_settings_icon( 'help-circle' ); ?><span class="zui-tooltip__bubble"><?php esc_html_e( 'Keep unchecked for the tracking info to be added to any existing tracking info on the orders.', 'woo-advanced-shipment-tracking' ); ?><span class="zui-tooltip__arrow"></span></span></span>
								</span>
								<p class="ast-set-csv-row__sub"><?php esc_html_e( 'Toggle whether you overwrite existing track tags or skip already appended orders.', 'woo-advanced-shipment-tracking' ); ?></p>
							</div>
							<label class="zui-toggle">
								<input type="checkbox" id="replace_tracking_info" name="replace_tracking_info" value="1" class="zui-toggle__input">
								<span class="zui-toggle__track"><span class="zui-toggle__thumb"></span></span>
							</label>
						</div>

						<div class="ast-set-csv-submit-row">
							<div class="submit">
								<button name="save" type="submit" value="Save" class="zui-btn-primary ast-set-csv-runbtn">
									<span><?php esc_html_e( 'Import Tracking Numbers', 'woo-advanced-shipment-tracking' ); ?></span>
									<?php ast_free_settings_icon( 'arrow-right' ); ?>
								</button>
								<div class="spinner" style="float:none"></div>
								<div class="success_msg" style="display:none;"><?php esc_html_e( 'Settings Saved.', 'woo-advanced-shipment-tracking' ); ?></div>
								<div class="error_msg" style="display:none;"></div>
							</div>
						</div>
					</div>

					<?php /* ---- STATUS VIEW (progress + done) ---- */ ?>
					<div class="bulk_upload_status_div ast-set-csv-status" style="display:none;">

						<div class="completed_icon"></div>

						<div class="bulk_upload_status_heading_tr ast-set-csv-status__head">
							<h2><?php esc_html_e( 'Import in Progress', 'woo-advanced-shipment-tracking' ); ?><span class="spinner is-active"></span></h2>
						</div>

						<div class="bulk_upload_status_overview_tr ast-set-csv-status__overview">
							<div class="bulk_upload_status_overview_td csv_success_msg" style="display:none;">
								<span></span>
							</div>
							<div class="bulk_upload_status_overview_td csv_fail_msg" style="display:none;">
								<span></span>
								<a href="javascript:void(0);" class="view_csv_error_details"><?php esc_html_e( 'view details', 'woo-advanced-shipment-tracking' ); ?></a>
							</div>
						</div>

						<div class="bulk_upload_status_detail_error_tr" style="display:none;">
							<ul class="csv_error_details_ul"></ul>
						</div>

						<div class="bulk_upload_status_tr ast-set-csv-progress">
							<div class="ast-set-csv-progress__row">
								<span><?php esc_html_e( 'PROGRESS STATUS', 'woo-advanced-shipment-tracking' ); ?></span>
								<span class="progress_number"></span>
							</div>
							<div id="p1" class="mdl-progress mdl-js-progress" style="display:none;"></div>
							<div class="progress2 progress-moved ast-set-csv-bar">
								<div class="progress-bar2"></div>
							</div>
						</div>

						<div class="ast-set-csv-done">
							<div class="ast-set-csv-stats">
								<div class="ast-set-csv-stat ast-set-csv-stat--ok">
									<span><?php esc_html_e( 'Ingested Success', 'woo-advanced-shipment-tracking' ); ?></span>
									<strong class="ast-csv-stat-success">0</strong>
								</div>
								<div class="ast-set-csv-stat ast-set-csv-stat--fail">
									<span><?php esc_html_e( 'Failed Checks', 'woo-advanced-shipment-tracking' ); ?></span>
									<strong class="ast-csv-stat-failed">0</strong>
								</div>
							</div>
						</div>

						<div class="ast-set-csv-console">
							<span class="ast-set-csv-console__label"><?php esc_html_e( 'Ingestion Activity Console Logs', 'woo-advanced-shipment-tracking' ); ?></span>
							<ul class="csv_upload_status ast-set-csv-log"></ul>
						</div>

						<div class="bulk_upload_status_action ast-set-csv-actions" style="display:none;">
							<a class="zui-btn-ghost" href="<?php echo esc_url( $ast_orders_url ); ?>"><?php esc_html_e( 'View Orders', 'woo-advanced-shipment-tracking' ); ?></a>
							<a href="javascript:void(0)" class="csv_upload_again zui-btn-primary"><?php esc_html_e( 'Upload again', 'woo-advanced-shipment-tracking' ); ?></a>
						</div>
					</div>

					<input type="hidden" id="nonce_csv_import" value="<?php echo esc_attr( wp_create_nonce( 'nonce_csv_import' ) ); ?>">
					<input type="hidden" name="action" value="wc_ast_upload_csv_form_update">
				</form>

				<?php /* Footer links */ ?>
				<div class="ast-set-csv-footer">
					<a class="ast-set-csv-footer__link" href="<?php echo esc_url( $ast_sample_url ); ?>">
						<?php ast_free_settings_icon( 'download' ); ?><span><?php esc_html_e( 'Download sample csv', 'woo-advanced-shipment-tracking' ); ?></span>
					</a>
					<a class="ast-set-csv-footer__link" href="https://docs.zorem.com/docs/ast-pro/advanced-shipment-tracking-free/csv-import/" target="_blank" rel="noopener noreferrer">
						<?php ast_free_settings_icon( 'book' ); ?><span><?php esc_html_e( 'How to import tracking numbers from CSV files?', 'woo-advanced-shipment-tracking' ); ?></span>
					</a>
				</div>
			</div>

		</div><?php /* /manual sub */ ?>

		<?php /* ========================= SFTP/FTP AUTOMATION (PRO-locked) ========================= */ ?>
		<div class="ast-set-csv-sub" data-csvsub="automated" hidden>
			<?php require SHIPMENT_TRACKING_PATH . '/includes/settings/partials/locked-ftp.php'; ?>
		</div>

	</div>
</section>
