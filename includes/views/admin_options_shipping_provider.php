<?php
/**
 * html code for shipping providers tab
 */
?>
<?php $wc_ast_api_key = get_option('wc_ast_api_key'); 
$upload_dir   = wp_upload_dir();	
$ast_directory = $upload_dir['baseurl'] . '/ast-shipping-providers/';
if(isset($_GET['open']) && $_GET['open'] == 'synch_providers'){ ?>
	<script>
		jQuery( document ).ready(function() {	
			jQuery('.sync_provider_popup').show();
		});
	</script>
<?php }
?>
<section id="content1" class="tab_section">
	<div class="d_table" style="">
		<div class="tab_inner_container">
			<div class="provider_top">																					
				
				<div class="search_section">
					<span class="dashicons dashicons-search search-icon"></span>
					<input class="provider_search_bar " type="text" name="search_provider" id="search_provider" placeholder="<?php _e( 'Search by provider / country', 'woo-advanced-shipment-tracking'); ?>">		
				</div>
												
				<div class="provider_settings">
					<a href="javaScript:void(0);" class="add_custom_provider provider_settings_icon" id="add-custom"><span class="dashicons dashicons-plus-alt"></span></a>	
					<a href="javaScript:void(0);" class="sync_providers provider_settings_icon"><span class="dashicons dashicons-update"></span></a>
					<input class="ast-tgl ast-tgl-flat" id="reset_providers" name="reset_providers" type="checkbox" value="1"/>
					<label class="ast-tgl-btn" for="reset_providers"></label>
				</div>
			</div>		
			<div class="provider_list">	
			<?php if($default_shippment_providers){ 
				echo $this->get_provider_html( $default_shippment_providers,'all' );
			} ?>					
			</div>
			<div id="" class="popupwrapper add_provider_popup" style="display:none;">
				<div class="popuprow">
					<div class="popup_header">
						<h3 class="popup_title"><?php _e( 'Add Custom Shipping Provider', 'woo-advanced-shipment-tracking'); ?></h2>						
						<span class="dashicons dashicons-no-alt popup_close_icon"></span>
					</div>
					<div class="popup_body">
						<form id="add_provider_form" method="POST" class="add_provider_form">
							<div class="form-field form-50">
								<label><?php _e( 'Provider Name', 'woo-advanced-shipment-tracking' ); ?></label>
								<input type="text" name="shipping_provider" class="shipping_provider" placeholder="<?php _e( 'Custom Provider', 'woo-advanced-shipment-tracking' ); ?>">
							</div>
							<div class="form-field form-50 margin-0">
								<label><?php _e( 'Custom Display Name', 'woo-advanced-shipment-tracking' ); ?> <span class="woocommerce-help-tip tipTip" title="<?php _e( "The custom display name will show in the tracking info section on the customer order emails, my-account, and TrackShip's tracking page and email notifications", 'woo-advanced-shipment-tracking' ); ?>"></span> </label>
								<input type="text" name="shipping_display_name" class="shipping_display_name" value="" placeholder="<?php _e( 'White Label Provider Name', 'woo-advanced-shipment-tracking' ); ?>">
							</div>
							<div class="form-field form-50">
								<label><?php _e( 'Country', 'woo-advanced-shipment-tracking' ); ?></label>
								<select class="select wcast_shipping_country shipping_country" name="shipping_country">
									<option value=""><?php _e( 'Shipping Country', 'woo-advanced-shipment-tracking' ); ?></option>
									<option value="Global"><?php _e( 'Global', 'woo-advanced-shipment-tracking' ); ?></option>
									<?php 
										foreach($countries as $key=>$val){ ?>
											<option value="<?php echo $key; ?>" ><?php _e( $val, 'woo-advanced-shipment-tracking'); ?></option>
										<?php } ?>
								</select>
							</div>
							<div class="form-field form-50 margin-0">
								<label><?php _e( 'Logo image', 'woo-advanced-shipment-tracking' ); ?></label>
								<input type='text' placeholder='Image' name='thumb_url' class='image_path thumb_url' value=''>
								<input type='hidden' name='thumb_id' class='image_id thumb_id' placeholder="Image" value='' style="">
								<input type="button" class="button upload_image_button" value="<?php _e( 'Upload' , 'woo-advanced-shipment-tracking'); ?>" />
							</div>
							<div class="form-field">
								<label><?php _e( 'Custom URL', 'woo-advanced-shipment-tracking' ); ?></label>
								<input type="text" name="tracking_url" class="tracking_url" placeholder="<?php _e( 'My White Label Provider URL', 'woo-advanced-shipment-tracking' ); ?>	 ">
							</div>
							<div class="form-field custom_provider_instruction">
								<p><?php _e( 'You can use the variables %number%, %postal_code% and %country_code% in the URL, for more info, check our ', 'woo-advanced-shipment-tracking' ); ?><?php echo sprintf(__('<a href="%s" target="blank">documentation</a>', 'woo-advanced-shipment-tracking'), 'http://www.zorem.com/docs/woocommerce-advanced-shipment-tracking/setting-shipping-providers/#adding-custom-shipping-provider'); ?></p>
							</div>
							<input type="hidden" name="action" value="add_custom_shipment_provider">
							<input type="submit" name="Submit" value="<?php _e( 'Add Custom Provider', 'woo-advanced-shipment-tracking' ); ?>" class="button-primary btn_ast2">			
						</form>
					</div>						
				</div>
				<div class="popupclose"></div>
			</div>
			
			<div id="" class="popupwrapper edit_provider_popup" style="display:none;">			
				<div class="popuprow">
					<div class="popup_header">
						<h3 class="popup_title"><?php _e( 'Edit Shipping Provider', 'woo-advanced-shipment-tracking'); ?></h2> - <h3 class="popup_title edit_provider_title"></h2>
						<span class="dashicons dashicons-no-alt popup_close_icon"></span>
					</div>	
					<div class="popup_body">							
						<form id="edit_provider_form" method="POST" class="edit_provider_form">
							<div class="form-field form-50">
								<label><?php _e( 'Provider Name', 'woo-advanced-shipment-tracking' ); ?></label>
								<input type="text" name="shipping_provider" class="shipping_provider" value="" placeholder="<?php _e( 'Shipping Provider', 'woo-advanced-shipment-tracking' ); ?>">
							</div>
							<div class="form-field form-50 margin-0">
								<label><?php _e( 'Custom display name', 'woo-advanced-shipment-tracking' ); ?> <span class="woocommerce-help-tip tipTip" title="<?php _e( "The custom display name will show in the tracking info section on the customer order emails, my-account, and TrackShip's tracking page and email notifications", 'woo-advanced-shipment-tracking' ); ?>"></span> </label>
								<input type="text" name="shipping_display_name" class="shipping_display_name" value="" placeholder="<?php _e( 'White Label Provider Name', 'woo-advanced-shipment-tracking' ); ?>">
							</div>
							<div class="form-field api_provider_name_container">
								<label><?php _e( 'Custom API name', 'woo-advanced-shipment-tracking' ); ?> <span class="woocommerce-help-tip tipTip" title="<?php _e( "Add API name aliases to map Shipping providers names with the provider names that are updated in the shipment tracking API by external shipping services", 'woo-advanced-shipment-tracking' ); ?>"></span></label>
								<div class="api_provider_div">									
									<input type="text" name="api_provider_name[]" class="api_provider_name" value="" placeholder="<?php _e( 'API Name', 'woo-advanced-shipment-tracking' ); ?>">
									<?php do_action('add_more_api_provider'); ?>									
								</div>
							</div>	
							<div class="form-field form-50">
								<label><?php _e( 'Country', 'woo-advanced-shipment-tracking' ); ?></label>
								<select class="select wcast_shipping_country shipping_country" name="shipping_country">
									<option value=""><?php _e( 'Shipping Country', 'woo-advanced-shipment-tracking' ); ?></option>
									<option value="Global"><?php _e( 'Global', 'woo-advanced-shipment-tracking' ); ?></option>
									<?php foreach($countries as $key=>$val){ ?>
											<option value="<?php echo $key; ?>" ><?php _e( $val, 'woo-advanced-shipment-tracking'); ?></option>
										<?php } ?>
								</select>
							</div>
							<div class="form-field form-50 margin-0">
								<label><?php _e( 'Logo image', 'woo-advanced-shipment-tracking' ); ?></label>
								<input type='text' placeholder='Image' name='thumb_url' class='image_path thumb_url' value=''>
								<input type='hidden' name='thumb_id' class='image_id thumb_id' placeholder="Image" value=''>
								<input type="button" class="button upload_image_button" value="<?php _e( 'Upload' , 'woo-advanced-shipment-tracking'); ?>" />
							</div>
							<div class="form-field">
								<label><?php _e( 'Custom URL', 'woo-advanced-shipment-tracking' ); ?></label>
								<input type="text" name="tracking_url" class="tracking_url" placeholder="Tracking URL">
							</div>
							<div class="form-field custom_provider_instruction">
								<p><?php _e( 'You can use the variables %number%, %postal_code% and %country_code% in the URL, for more info, check our ', 'woo-advanced-shipment-tracking' ); ?><?php echo sprintf(__('<a href="%s" target="blank">documentation</a>', 'woo-advanced-shipment-tracking'), 'http://www.zorem.com/docs/woocommerce-advanced-shipment-tracking/setting-shipping-providers/#adding-custom-shipping-provider'); ?></p>
							</div>
							<input type="hidden" name="action" value="update_custom_shipment_provider">
							<input type="hidden" name="provider_type" id="provider_type" value="">
							<input type="hidden" name="provider_id" id="provider_id" value="">							
							<input type="submit" name="Submit" value="<?php _e( 'Update' , 'woo-advanced-shipment-tracking'); ?>" class="button-primary btn_ast2">
							<a href="javascript:void(0);" class="reset_default_provider"><?php _e( 'Reset' , 'woo-advanced-shipment-tracking'); ?></a>
						</form>
					</div>	
				</div>
				<div class="popupclose"></div>
			</div>
			
			<div id="" class="popupwrapper sync_provider_popup" style="display:none;">
				<div class="popuprow">
					<div class="popup_header">
						<h3 class="popup_title"><?php _e( 'Sync Shipping Providers', 'woo-advanced-shipment-tracking'); ?></h2>						
						<span class="dashicons dashicons-no-alt popup_close_icon"></span>
					</div>	
					<div class="popup_body">	
						<p class="sync_message"><?php _e( 'Syncing the shipping providers list add or updates the pre-set shipping providers and will not effect custom shipping providers.', 'woo-advanced-shipment-tracking'); ?></p>
						<ul class="synch_result">
							<li class="providers_added"><?php _e( 'Providers Added', 'woo-advanced-shipment-tracking'); ?> - <span></span></li>
							<li class="providers_updated"><?php _e( 'Providers Updated', 'woo-advanced-shipment-tracking'); ?> - <span></span></li>
							<li class="providers_deleted"><?php _e( 'Providers Deleted', 'woo-advanced-shipment-tracking'); ?> - <span></span></li>
						</ul>
						<p class="reset_db_message" style="display:none;"><?php _e( 'Shipping providers database reset successfully.', 'woo-advanced-shipment-tracking'); ?></p>
						<fieldset class="reset_db_fieldset">						
							<label><input type="checkbox" id="reset_tracking_providers" name="reset_tracking_providers" value="1"><?php _e( 'Reset providers database, it will reset all your shipping provider database', 'woo-advanced-shipment-tracking'); ?></label>	
						</fieldset>
						<button class="sync_providers_btn button-primary btn_ast2"><?php _e( 'Sync Shipping Providers', 'woo-advanced-shipment-tracking'); ?></button>
						<button class="close_synch_popup button-primary btn_ast2"><?php _e( 'Close', 'woocommerce'); ?></button>
						<div class="spinner" style=""></div>
					</div>
				</div>	
				<div class="popupclose"></div>
			</div>   	
		</div>		
	</div>	
</section>