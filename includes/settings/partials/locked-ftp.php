<?php
/**
 * Free version — Automated CSV Import (FTP/SFTP) section: PRO-locked preview.
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
		<?php esc_html_e( 'Unlock SFTP/FTP Automation', 'woo-advanced-shipment-tracking' ); ?>
		<span class="zui-lock-section__badge">PRO</span>
	</h3>
	<p class="zui-lock-section__desc">
		<?php esc_html_e( 'Schedule automatic CSV imports from your FTP or SFTP server. Set polling frequency, run diagnostic connection probes, and let AST sync tracking data in the background — no manual uploads. Upgrade to AST Pro to unlock these options.', 'woo-advanced-shipment-tracking' ); ?>
	</p>
	<a class="zui-btn-primary zui-lock-section__cta" href="<?php echo esc_url( $ast_upgrade_url ); ?>" target="_blank" rel="noopener noreferrer">
		<?php esc_html_e( 'Upgrade to PRO', 'woo-advanced-shipment-tracking' ); ?>
	</a>
</div>

<div class="zui-lock-section__preview ast-set-csv ast-locked-csv-ftp" aria-disabled="true">

	<form class="ast-set-csv-ftp" onsubmit="return false;">

		<div class="zui-card zui-card--csv-ftp">

			<?php /* Enable CSV Auto-Import — toggle row */ ?>
			<div class="zui-row zui-row--inline ast-locked-row">
				<div class="zui-row__head">
					<span class="zui-row__label"><?php esc_html_e( 'Enable CSV Auto-Import', 'woo-advanced-shipment-tracking' ); ?></span>
					<p class="zui-row__desc"><?php esc_html_e( 'Scans, decrypts, parses, and merges remote data sheets onto your WooCommerce active catalog behind the scenes.', 'woo-advanced-shipment-tracking' ); ?></p>
				</div>
				<div class="zui-row__control">
					<label class="zui-toggle" aria-disabled="true">
						<input type="checkbox" class="zui-toggle__input" disabled>
						<span class="zui-toggle__track"><span class="zui-toggle__thumb"></span></span>
					</label>
				</div>
			</div>

			<?php /* Import Polling Frequency */ ?>
			<div class="zui-row ast-locked-row">
				<div class="zui-row__head">
					<span class="zui-row__label"><?php esc_html_e( 'Import Polling Frequency', 'woo-advanced-shipment-tracking' ); ?></span>
					<p class="zui-row__desc"><?php esc_html_e( 'Define the background polling interval schedule to trigger the SFTP search parameters automatically.', 'woo-advanced-shipment-tracking' ); ?></p>
				</div>
				<div class="zui-row__control">
					<div class="zui-select-wrap">
						<select class="zui-select" disabled>
							<option><?php esc_html_e( 'Daily Cron Jobs', 'woo-advanced-shipment-tracking' ); ?></option>
						</select>
						<span class="zui-select-chevron"><?php ast_free_settings_icon( 'chevron-down' ); ?></span>
					</div>
				</div>
			</div>

			<?php /* Import Time */ ?>
			<div class="zui-row ast-locked-row">
				<div class="zui-row__head">
					<span class="zui-row__label"><?php esc_html_e( 'Import Time', 'woo-advanced-shipment-tracking' ); ?></span>
					<p class="zui-row__desc"><?php esc_html_e( 'Set the daily time for the import to run.', 'woo-advanced-shipment-tracking' ); ?></p>
				</div>
				<div class="zui-row__control">
					<div class="zui-select-wrap">
						<select class="zui-select" disabled>
							<option>8:30am</option>
						</select>
						<span class="zui-select-chevron"><?php ast_free_settings_icon( 'chevron-down' ); ?></span>
					</div>
				</div>
			</div>

			<?php /* Protocol Method — radio cards */ ?>
			<div class="zui-row ast-locked-row">
				<div class="zui-row__head">
					<span class="zui-row__label"><?php esc_html_e( 'Protocol Method', 'woo-advanced-shipment-tracking' ); ?></span>
					<p class="zui-row__desc"><?php esc_html_e( 'FTP is legacy and transmits plaintext credentials. We highly recommend using SFTP for remote connection security.', 'woo-advanced-shipment-tracking' ); ?></p>
				</div>
				<div class="zui-row__control zui-row__control--stack">
					<div class="zui-radio-cards">
						<label class="zui-radio-card is-selected" aria-disabled="true">
							<input type="radio" name="ast_locked_protocol" value="ftp" class="zui-radio-card__input" checked disabled>
							<span class="zui-radio-card__body">
								<span class="zui-radio-card__label"><?php esc_html_e( 'FTP', 'woo-advanced-shipment-tracking' ); ?></span>
								<span class="zui-radio-card__desc"><?php esc_html_e( 'Classic unencrypted connection channel (Port 21).', 'woo-advanced-shipment-tracking' ); ?></span>
							</span>
						</label>
						<label class="zui-radio-card" aria-disabled="true">
							<input type="radio" name="ast_locked_protocol" value="sftp" class="zui-radio-card__input" disabled>
							<span class="zui-radio-card__body">
								<span class="zui-radio-card__label"><?php esc_html_e( 'SFTP (Recommended)', 'woo-advanced-shipment-tracking' ); ?></span>
								<span class="zui-radio-card__desc"><?php esc_html_e( 'Secure SSH communication channel (Port 22).', 'woo-advanced-shipment-tracking' ); ?></span>
							</span>
						</label>
					</div>
				</div>
			</div>

			<?php /* FTP/SFTP Server Host URL */ ?>
			<div class="zui-row ast-locked-row">
				<div class="zui-row__head">
					<span class="zui-row__label"><?php esc_html_e( 'FTP/SFTP Server Host URL', 'woo-advanced-shipment-tracking' ); ?></span>
					<p class="zui-row__desc"><?php esc_html_e( 'The remote host URL address where logistics reports are saved.', 'woo-advanced-shipment-tracking' ); ?></p>
				</div>
				<div class="zui-row__control">
					<input type="text" class="zui-input" placeholder="" disabled>
				</div>
			</div>

			<?php /* FTP/SFTP Server Port */ ?>
			<div class="zui-row ast-locked-row">
				<div class="zui-row__head">
					<span class="zui-row__label"><?php esc_html_e( 'FTP/SFTP Server Port', 'woo-advanced-shipment-tracking' ); ?></span>
					<p class="zui-row__desc"><?php esc_html_e( 'Default ports: 21 for classic FTP, 22 for secure SFTP.', 'woo-advanced-shipment-tracking' ); ?></p>
				</div>
				<div class="zui-row__control">
					<input type="text" class="zui-input" value="21" disabled>
				</div>
			</div>

			<?php /* FTP/SFTP Username */ ?>
			<div class="zui-row ast-locked-row">
				<div class="zui-row__head">
					<span class="zui-row__label"><?php esc_html_e( 'FTP/SFTP Username', 'woo-advanced-shipment-tracking' ); ?></span>
					<p class="zui-row__desc"><?php esc_html_e( 'Your FTP/SFTP server login username.', 'woo-advanced-shipment-tracking' ); ?></p>
				</div>
				<div class="zui-row__control">
					<input type="text" class="zui-input" placeholder="" disabled>
				</div>
			</div>

			<?php /* FTP/SFTP Password */ ?>
			<div class="zui-row ast-locked-row">
				<div class="zui-row__head">
					<span class="zui-row__label"><?php esc_html_e( 'FTP/SFTP Password', 'woo-advanced-shipment-tracking' ); ?></span>
					<p class="zui-row__desc"><?php esc_html_e( 'The password for your FTP/SFTP server account.', 'woo-advanced-shipment-tracking' ); ?></p>
				</div>
				<div class="zui-row__control">
					<input type="password" class="zui-input" placeholder="" disabled>
				</div>
			</div>

			<?php /* CSV Remote Folder Path */ ?>
			<div class="zui-row ast-locked-row">
				<div class="zui-row__head">
					<span class="zui-row__label"><?php esc_html_e( 'CSV Remote Folder Path', 'woo-advanced-shipment-tracking' ); ?></span>
					<p class="zui-row__desc"><?php esc_html_e( 'Directory path search pattern limits. Enter the remote folder path (not a file name); all .csv files in it are downloaded and merged during import.', 'woo-advanced-shipment-tracking' ); ?></p>
				</div>
				<div class="zui-row__control">
					<input type="text" class="zui-input" placeholder="" disabled>
				</div>
			</div>

			<?php /* Date Format for FTP Import — radio cards */ ?>
			<div class="zui-row ast-locked-row">
				<div class="zui-row__head">
					<span class="zui-row__label"><?php esc_html_e( 'Date Format for FTP Import', 'woo-advanced-shipment-tracking' ); ?></span>
					<p class="zui-row__desc"><?php esc_html_e( 'Select the date format used in your imported CSV files. Both slash (/) and dash (-) separators will be accepted.', 'woo-advanced-shipment-tracking' ); ?></p>
				</div>
				<div class="zui-row__control zui-row__control--stack">
					<div class="zui-radio-cards">
						<label class="zui-radio-card is-selected" aria-disabled="true">
							<input type="radio" name="ast_locked_ftp_date" value="d-m-Y" class="zui-radio-card__input" checked disabled>
							<span class="zui-radio-card__body">
								<span class="zui-radio-card__label">DD-MM-YYYY</span>
								<span class="zui-radio-card__desc"><?php esc_html_e( 'E.g., 02-06-2026 (Standard Day, Month, Year)', 'woo-advanced-shipment-tracking' ); ?></span>
							</span>
						</label>
						<label class="zui-radio-card" aria-disabled="true">
							<input type="radio" name="ast_locked_ftp_date" value="m-d-Y" class="zui-radio-card__input" disabled>
							<span class="zui-radio-card__body">
								<span class="zui-radio-card__label">MM-DD-YYYY</span>
								<span class="zui-radio-card__desc"><?php esc_html_e( 'E.g., 06-02-2026 (Month, Day, Year)', 'woo-advanced-shipment-tracking' ); ?></span>
							</span>
						</label>
					</div>
				</div>
			</div>

			<?php /* Test Connected Poller URL */ ?>
			<div class="zui-row zui-row--inline ast-locked-row">
				<div class="zui-row__head">
					<span class="zui-row__label"><?php esc_html_e( 'Test Connected Poller URL', 'woo-advanced-shipment-tracking' ); ?></span>
					<p class="zui-row__desc"><?php esc_html_e( 'Validate host and port details before initiating automatic background cron checks.', 'woo-advanced-shipment-tracking' ); ?></p>
				</div>
				<div class="zui-row__control">
					<button type="button" class="zui-btn-ghost" disabled><?php esc_html_e( 'Run Diagnostic Connection Probe', 'woo-advanced-shipment-tracking' ); ?></button>
				</div>
			</div>

			<?php /* Delete After Import — toggle */ ?>
			<div class="zui-row zui-row--inline ast-locked-row">
				<div class="zui-row__head">
					<span class="zui-row__label"><?php esc_html_e( 'Delete After Import', 'woo-advanced-shipment-tracking' ); ?></span>
					<p class="zui-row__desc"><?php esc_html_e( 'Permanently wipes CSV files from the remote server directory once parsed and ingested successfully.', 'woo-advanced-shipment-tracking' ); ?></p>
				</div>
				<div class="zui-row__control">
					<label class="zui-toggle" aria-disabled="true">
						<input type="checkbox" class="zui-toggle__input" disabled>
						<span class="zui-toggle__track"><span class="zui-toggle__thumb"></span></span>
					</label>
				</div>
			</div>

			<?php /* Enable cron import logs — toggle */ ?>
			<div class="zui-row zui-row--inline ast-locked-row">
				<div class="zui-row__head">
					<span class="zui-row__label"><?php esc_html_e( 'Enable cron import logs', 'woo-advanced-shipment-tracking' ); ?></span>
					<p class="zui-row__desc"><?php esc_html_e( 'Recommended for troubleshooting background cron actions.', 'woo-advanced-shipment-tracking' ); ?></p>
				</div>
				<div class="zui-row__control">
					<label class="zui-toggle" aria-disabled="true">
						<input type="checkbox" class="zui-toggle__input" disabled>
						<span class="zui-toggle__track"><span class="zui-toggle__thumb"></span></span>
					</label>
				</div>
			</div>

		</div>
	</form>
</div>
