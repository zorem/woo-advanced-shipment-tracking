<?php
/**
 * Html code for bulk upload tab
 */
?>
<section id="content4" class="tab_section">
	<div class="ast_container_with_sidebar">
		<div class="csv_import_container">				
			<h2 class="tab_page_heading clear_spacing"><?php esc_html_e( 'CSV Import', 'woo-advanced-shipment-tracking' ); ?></h2>
			<p><?php esc_html_e('Use our CSV Import Tool  to bulk Import tracking info to orders from a CSV file', 'woo-advanced-shipment-tracking'); ?></p>
			<!-- progressbar -->						
			<section class="woocommerce-progress-form-wrapper" style="display:block;">
				<ol class="wc-progress-steps">
					<li class="progress_step1 active"><?php esc_html_e('Upload CSV file', 'woocommerce'); ?></li>					
					<li class="progress_step2"><?php esc_html_e('Import', 'woocommerce'); ?></li>
					<li class="progress_step3"><?php esc_html_e('Done!', 'woocommerce'); ?></li>
				</ol>	
				<form method="post" id="wc_ast_upload_csv_form" action="" enctype="multipart/form-data" style="text-align:left;">
					<div class="upload_csv_div">
						<div class="outer_form_table">							
							<table class="form-table upload_csv_table">
								<tbody>								
									<tr valign="top" class="border-bottom-1">
										<th scope="row" class="">
											<label for=""><?php esc_html_e('Upload a CSV file from your computer:', 'woo-advanced-shipment-tracking'); ?></label>
										</th>
										<td scope="row" class="input_file_cl">
											<input type="file" name="trcking_csv_file" id="trcking_csv_file">
										</td>
									</tr> 
									<tr valign="top" class="border-bottom-1">
										<th scope="row" class="">
											<label for=""><?php esc_html_e('Choose the Shipped Date format', 'woo-advanced-shipment-tracking'); ?></label>
										</th>
										<td scope="row" class="">
											<?php $date_format = get_option( 'date_format_for_csv_import', 'd-m-Y' ); ?>
											<label class="ast_radio_label" for="date_format_ddmmyy">
												<input type="radio" <?php esc_html_e( ( 'd-m-Y' == $date_format ) ? 'checked' : '' ); ?> id="date_format_ddmmyy" name="date_format_for_csv_import" class="" value="d-m-Y"/> 	dd/mm/YYYY
											</label>
											<label class="ast_radio_label" for="date_format_mmddyy">
												<input type="radio" <?php esc_html_e( ( 'm-d-Y' == $date_format ) ? 'checked' : '' ); ?> id="date_format_mmddyy" name="date_format_for_csv_import" class="" value="m-d-Y"/> 	mm/dd/YYYY
											</label>
										</td>
									</tr>
									<tr valign="top" class="">
										<th scope="row" class="">
											<label for=""><?php esc_html_e('Replace tracking information?', 'woo-advanced-shipment-tracking'); ?><span class="woocommerce-help-tip tipTip" data-tip="<?php esc_html_e('Keep 	unchecked for the tracking info to be added to any existing tracking info added to the orders.', 'woo-advanced-shipment-tracking'); ?>"></span></label>
										</th>
										<td scope="row" class="">
											<input type="checkbox" id="replace_tracking_info" name="replace_tracking_info" class="" value="1"/>
										</td>
									</tr>
									<tr valign="top" class="">
										<td scope="row" class="button-column" colspan="2">
											<div class="submit">
												<button name="save" class="button-primary btn_ast2" type="submit" value="Save"><?php esc_html_e('Continue', 'woo-advanced-shipment-tracking'); ?></button>
												<div class="spinner" style="float:none"></div>
												<div class="success_msg" style="display:none;"><?php esc_html_e('Settings Saved.', 'woo-advanced-shipment-tracking'); ?></div>
												<div class="error_msg" style="display:none;"></div>									
												<input type="hidden" name="action" value="wc_ast_upload_csv_form_update">
											</div>	
										</td>									
									</tr>								
								</tbody>				
							</table>
						</div>										
					</div>				
					<div class="bulk_upload_status_div" style="display:none;">
						<div class="outer_form_table">							
							<div class="completed_icon"></div>
							<table class="form-table upload_csv_table">
								<tbody>								
									<tr valign="top" class="bulk_upload_status_heading_tr">
										<td scope="row" class="input_file_cl bulk_upload_status_td" colspan="2">
											<h2><?php esc_html_e('Import in Progress', 'woo-advanced-shipment-tracking'); ?><span class="spinner is-active"></span></h2>	
										</td>																
									</tr>
									<tr valign="top" class="bulk_upload_status_overview_tr">
										<td scope="row" class="bulk_upload_status_overview_td csv_success_msg" colspan="2">
											<span></span>
										</td>																
									</tr>
									<tr valign="top" class="bulk_upload_status_overview_tr">
										<td scope="row" class="bulk_upload_status_overview_td csv_fail_msg" colspan="2">
											<span></span>
											<a href="javascript:void(0);" class="view_csv_error_details"><?php esc_html_e('view details', 'woo-advanced-shipment-tracking'); ?></a>
										</td>																
									</tr>	
									<tr valign="top" class="bulk_upload_status_detail_error_tr">
										<td scope="row" colspan="2">
											<ul class="csv_error_details_ul">											
											</ul>
										</td>
									</tr>
									<tr class="bulk_upload_status_tr">
										<td scope="row" colspan="2">
											<div id="p1" class="mdl-progress mdl-js-progress" style=""></div>
											<div class="progress2 progress-moved">
												<div class="progress-bar2" >
												</div>                       
											</div> 	
											<ul class="csv_upload_status"></ul>
										</td>
									</tr>
									<tr valign="top" class="bulk_upload_status_action" style="display:none;">
										<td>
											<a class="button-primary btn_ast2" href="<?php echo esc_url( admin_url( 'edit.php?post_type=shop_order' ) ); ?>"><?php esc_html_e('View Orders', 'woo-advanced-shipment-tracking'); ?></a>
											<a href="javascript:void(0)" class="csv_upload_again button-primary btn_ast2"><?php esc_html_e('Upload again', 'woo-advanced-shipment-tracking'); ?></a>
										</td>	
									</tr>
								</tbody>				
							</table>
						</div>					
					</div>				
					<input type="hidden" id="nonce_csv_import" value="<?php esc_html_e( wp_create_nonce( 'nonce_csv_import' ) ); ?>">
				</form>	
				<ul class="bulk_upload_documentation_ul">
					<li><span class="dashicons dashicons-download"></span><a class="" href="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ); ?>/assets/tracking.csv"><?php esc_html_e('Download 	sample csv', 'woo-advanced-shipment-tracking'); ?></a></li>
					<li><span class="dashicons dashicons-media-document"></span><a target="blank" href="https://docs.zorem.com/docs/ast-pro/advanced-shipment-tracking-free/csv-import/"><?php esc_html_e('How to import 	tracking number from CSV files?', 'woo-advanced-shipment-tracking'); ?></a></li>	
				</ul>	
			</section>			
		</div>
		<?php include 'admin_sidebar.php'; ?>
	</div>
	<?php include 'admin_footer_promotion.php'; ?>
</section>
