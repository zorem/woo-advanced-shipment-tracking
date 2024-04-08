<?php
/**
 * Html code for shipping providers tab
 */

$total_enable_providers = $wpdb->get_row(
	$wpdb->prepare(
		'SELECT COUNT(*) as total_providers FROM %1s WHERE display_in_order = 1',
		$this->table
	)
);

if ( isset( $_GET['open'] ) && 'synch_providers' == $_GET['open'] ) {
	?>
	<script>
		jQuery( document ).ready(function() {	
			jQuery('.sync_provider_popup').show();
		});
	</script>
<?php } ?>
<section id="content1" class="tab_section">	
	<div class="shipping_provider_container">
		<?php do_action( 'before_shipping_provider_list' ); ?>
		<div class="provider_top">																					
			<div class="search_section">				
				<h2 class="shipping_carrier_heading"><?php esc_html_e( 'Shipping Carriers', 'woo-advanced-shipment-tracking' ); ?></h2>
				<!--span class="dashicons dashicons-search search-icon"></span>
				<input class="provider_search_bar" type="text" name="search_provider" id="search_provider" placeholder="<?php //esc_html_e( 'Search by carrier / country', 'woo-advanced-shipment-tracking' ); ?>"-->		
			</div>			
			
			<button class="button button-primary" style="display:none;" id="delete_provider_bulk" data-remove="selected-page"><?php esc_html_e( 'Remove Selected', 'woo-advanced-shipment-tracking' ); ?></button>

			<div class="provider_settings">
				<a href="javaScript:void(0);" class="provider_settings_icon" id="provider-settings"><span class="dashicons dashicons-ellipsis"></span></a>				
				<ul class="provider-settings-ul">
					<li><a href="javaScript:void(0);" class="enable_carriers"><?php esc_html_e('Enable Carriers', 'woo-advanced-shipment-tracking'); ?></a></li>
					<li><a href="javaScript:void(0);" class="add_custom_carriers"><?php esc_html_e('Add Custom Carrier', 'woo-advanced-shipment-tracking'); ?></a></li>
					<li><a href="javaScript:void(0);" class="sync_providers"><?php esc_html_e('Sync Carriers', 'woo-advanced-shipment-tracking'); ?></a></li>
					<li><a href="javaScript:void(0);" class="reset_providers" data-reset="1"><?php esc_html_e('Select All', 'woo-advanced-shipment-tracking'); ?></a></li>
					<li><a href="javaScript:void(0);" class="reset_providers deselect" data-reset="0"><?php esc_html_e('Deselect All', 'woo-advanced-shipment-tracking'); ?></a></li>
				</ul>			
			</div>			
		</div>	
		<div class="shipping_carriers_menu_devider"></div>
		
		<?php if ( $total_enable_providers->total_providers > 0 ) { ?>
			<div class="shipping-carriers-selected-provider-message">
				<p class="selected_provider_show_notice"><span id="selected_provider_total">0</span> <?php esc_html_e('carriers selected.', 'woo-advanced-shipment-tracking'); ?> <a class="remove_all_shipping_carrier"><?php esc_html_e('Click Here', 'woo-advanced-shipment-tracking'); ?></a> <?php esc_html_e('if you want to select all carriers.', 'woo-advanced-shipment-tracking'); ?></p>
			</div>
			<div class="all-shipping-carriers-selected">
				<p class="all_carriers_selected"><?php esc_html_e('All Carriers Selected.', 'woo-advanced-shipment-tracking'); ?> <a class="remove_selected_shipping_carrier"><?php esc_html_e('Undo', 'woo-advanced-shipment-tracking'); ?></a></p>
			</div>
		<?php } ?>

		<div class="provider_list">			
			<?php echo wp_kses_post( $this->get_provider_html( 1 ) ); ?>
		</div>
		
		<input type="hidden" id="nonce_shipping_provider" value="<?php esc_html_e( wp_create_nonce( 'nonce_shipping_provider' ) ); ?>">
		
		<div id="" class="slidout_container add_provider_popup">			
			<div class="slidout_header">
				<div class="slidout_header_title">
					<h3 class="slidout_title"><?php esc_html_e( 'Enable Shipping Carriers', 'woo-advanced-shipment-tracking'); ?></h3>
				</div>	
				<div class="slidout_header_action">
					<span class="dashicons dashicons-no-alt add_slidout_close slidout_close"></span>
				</div>	
			</div>
			<div class="slidout_body padding_zero">
				<div class="menu_devider"></div>
				<section id="add_default_carrier_section">
					<div class="top_carrier_section">
						<div class="top_search_section">
							<div class="search_section">
								<span class="dashicons dashicons-search search-carrier-icon"></span>
								<input class="provider_search_bar" type="text" name="search_default_provider" id="search_default_provider" placeholder="<?php esc_html_e( 'Search by carrier / country', 'woo-advanced-shipment-tracking' ); ?>">		
							</div>
							<?php echo wp_kses_post( $this->shipping_pagination_fun( 1 ) ); ?>
						</div>
					</div>
					<div class="top_search_section">
						<div class="powered_by_section">
							<span>Powered by</span>
							<a href="https://trackship.com/" target="blank"><img src="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ); ?>assets/images/trackship-logo.png"></a>
						</div>
						<div class="get_feature_container" style="margin-top: 10px;">Discover a wide range of shipping carriers curated from TrackShip.com <a target="blank" href="https://trackship.com/shipping-providers/">right here</a>. If you don't find your preferred shipping provider on our list, you can suggest a shipping carrier on TrackShip. We're here to make your WooCommerce shipping experience hassle-free and efficient. Thank you for choosing Advanced Shipment Tracking.</div>
					</div>
				</section>
			</div>			
		</div>
		<div id="" class="slidout_container add_custom_carriers_popup">
			<div class="slidout_header">
				<div class="slidout_header_title">
					<h3 class="slidout_title"><?php esc_html_e( 'Add Custom Carrier', 'woo-advanced-shipment-tracking'); ?></h3>
				</div>	
				<div class="slidout_header_action">
					<span class="dashicons dashicons-no-alt add_slidout_custom_carriers_close slidout_close"></span>
				</div>	
			</div>
			<div class="slidout_body">
				<div class="menu_devider"></div>
				<section id="add_customer_carrier_section">	
					<div class="get_feature_container">
						<a href="https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/?utm_source=wp-admin&utm_medium=provider-popup&utm_campaign=upgrad-to-pro" target="_blank"><span class="get_feature_span"><span class="dashicons dashicons-arrow-up-alt"></span>Get Feature</span></a>
						<div>Upgrade to Advanced Shipment Tracking Pro and gain the power to Add Custom Shipping Carriers. Ditch limitations, offer more shipping choices, enhance customer satisfaction, and streamline operations. Elevate your e-commerce game today!</div>
					</div>

					<?php
					/* <div class="form-field form-50">
						<label><?php esc_html_e( 'Carrier Name', 'woo-advanced-shipment-tracking' ); ?></label>
						<input type="text" class="shipping_provider" placeholder="<?php esc_html_e( 'Custom Carrier', 'woo-advanced-shipment-tracking' ); ?>" disabled>
					</div>
					
					<div class="form-field form-50 margin-0">
						<label><?php esc_html_e( 'Custom Display Name', 'woo-advanced-shipment-tracking' ); ?> <span class="woocommerce-help-tip tipTip" data-tip="<?php esc_html_e( "The custom display name will show in the tracking info section on the customer order emails, my-account, and TrackShip's tracking page and email notifications", 'woo-advanced-shipment-tracking' ); ?>"></span> </label>
						<input type="text" class="shipping_display_name" value="" placeholder="<?php esc_html_e( 'White Label Carrier Name', 'woo-advanced-shipment-tracking' ); ?>" disabled>
					</div>
					
					<div class="form-field form-50">
						<label><?php esc_html_e( 'Country', 'woo-advanced-shipment-tracking' ); ?></label>
						<select class="select wcast_shipping_country shipping_country" name="shipping_country" disabled>
							<option value=""><?php esc_html_e( 'Shipping Country', 'woo-advanced-shipment-tracking' ); ?></option>
							<option value="Global"><?php esc_html_e( 'Global', 'woo-advanced-shipment-tracking' ); ?></option>
							<?php 
							foreach ( $countries as $key=>$val ) { 
								?>
								<option value="<?php esc_html_e( $key ); ?>" ><?php esc_html_e( $val, 'woo-advanced-shipment-tracking' ); ?></option>
							<?php } ?>
						</select>
					</div>
					
					<div class="form-field form-50 margin-0">
						<label><?php esc_html_e( 'Logo image', 'woo-advanced-shipment-tracking' ); ?></label>
						<input type='text' placeholder='Image' name='thumb_url' class='image_path thumb_url' value='' disabled>						
						<input type="button" class="button upload_image_button" value="<?php esc_html_e( 'Upload' , 'woo-advanced-shipment-tracking' ); ?>" disabled />
					</div>
					
					<div class="form-field">
						<label><?php esc_html_e( 'Custom URL', 'woo-advanced-shipment-tracking' ); ?></label>
						<input type="text" name="tracking_url" class="tracking_url" placeholder="<?php esc_html_e( 'My White Label Carrier URL', 'woo-advanced-shipment-tracking' ); ?>" disabled>
					</div>*/
					?>
				</section>
			</div>
		</div>

		<div id="" class="slidout_container edit_provider_popup"></div>
		<div id="" class="slidout_container sync_provider_popup">
			<div class="slidout_header">
				<div class="slidout_header_title">
					<h3 class="slidout_title"><?php esc_html_e( 'Sync Shipping Carriers', 'woo-advanced-shipment-tracking'); ?></h3>
				</div>	
				<div class="slidout_header_action">
					<span class="dashicons dashicons-no-alt synch_slidout_close slidout_close"></span>
				</div>				
			</div>
			<div class="slidout_body">
				<div class="menu_devider"></div>	
				<p class="sync_message"><?php esc_html_e( 'Syncing the shipping carriers list add or updates the pre-set shipping carriers and will not effect custom shipping carriers.', 'woo-advanced-shipment-tracking' ); ?></p>
				<ul class="synch_result">
					<li class="providers_added"><?php esc_html_e( 'Carriers Added', 'woo-advanced-shipment-tracking' ); ?> - <span></span></li>
					<li class="providers_updated"><?php esc_html_e( 'Carriers Updated', 'woo-advanced-shipment-tracking' ); ?> - <span></span></li>
					<li class="providers_deleted"><?php esc_html_e( 'Carriers Deleted', 'woo-advanced-shipment-tracking' ); ?> - <span></span></li>
				</ul>
				<p class="reset_db_message" style="display:none;"><?php esc_html_e( 'Shipping carriers database reset successfully.', 'woo-advanced-shipment-tracking' ); ?></p>
				<fieldset class="reset_db_fieldset">						
					<label><input type="checkbox" id="reset_tracking_providers" name="reset_tracking_providers" value="1"><?php esc_html_e( 'Reset carriers database, it will reset all your shipping provider database', 'woo-advanced-shipment-tracking' ); ?></label>	
				</fieldset>
				<button class="sync_providers_btn button-primary btn_ast2"><span class="dashicons dashicons-update"></span><?php esc_html_e( 'Sync Shipping Carriers', 'woo-advanced-shipment-tracking' ); ?></button>
				<button class="close_synch_popup button-primary btn_ast2"><?php esc_html_e( 'Close', 'woocommerce' ); ?></button>
				<div class="spinner" style=""></div>
			</div>			
		</div>
	</div>
</section>
