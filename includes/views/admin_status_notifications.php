<section id="content_status_notifications" class="tab_section">
	<div class="tab_inner_container">
		<div class="outer_form_table ts_notifications_outer_table">			
			<?php 
				$ast = new WC_Advanced_Shipment_Tracking_Actions;												
				
				$wcast_enable_late_shipments_admin_email = $ast->get_option_value_from_array('late_shipments_email_settings','wcast_enable_late_shipments_admin_email','');
				
				$type = isset( $_GET['type'] ) ? sanitize_text_field($_GET['type']) : 'email';
				
				$ts_notifications = $this->trackship_shipment_status_notifications_data();				
			?>				
			
			<input id="tab_email_notifications" type="radio" name="ts_notification_tabs" class="inner_tab_input" data-type="email" <?php if($type == 'email'){ echo 'checked'; } ?>>
			<label for="tab_email_notifications" class="inner_tab_label ts_tabs_label"><?php _e( 'Email Notifications', 'woo-advanced-shipment-tracking' ); ?></label>				
			
			<input id="tab_sms_notifications" type="radio" name="ts_notification_tabs" class="inner_tab_input" data-type="sms" <?php if($type == 'sms'){ echo 'checked'; } ?>>
			<label for="tab_sms_notifications" class="inner_tab_label ts_tabs_label"><?php _e( 'SMS Notifications', 'woo-advanced-shipment-tracking' ); ?></label>
			
			<section class="inner_tab_section shipment-status-email-section">
				<div class="custom_order_status_section">
					<table class="form-table shipment-status-email-table">
						<tbody>
							<?php foreach( $ts_notifications as $key => $val ){ 
								$ast_enable_email = $ast->get_option_value_from_array( $val['option_name'],$val['enable_status_name'],''); ?>
								<tr class="<?php if($ast_enable_email == 1){ echo 'enable'; } else{ echo 'disable'; }?>">						
									<td class="forminp status-label-column">
										<span class="shipment_status_toggle">								
											<input type="hidden" name="<?php echo $val['enable_status_name']; ?>" value="0"/>
											<input class="ast-tgl ast-tgl-flat" id="<?php echo $val['enable_status_name']; ?>" name="<?php echo $val['enable_status_name']; ?>" data-settings="<?php echo $val['option_name']; ?>" type="checkbox" <?php if($ast_enable_email == 1) { echo 'checked'; } ?> value="yes"/>
											<label class="ast-tgl-btn ast-tgl-btn-green" for="<?php echo $val['enable_status_name']; ?>"></label>	
										</span>
										<button class="button button-primary shipment-status-label <?php echo $val['slug']; ?>"><?php echo $val['title']; ?></button>
									</td>
									<td class="forminp">
										<a class="button-primary btn_ts_transparent edit_customizer_a" href="<?php echo $val['customizer_url']; ?>"><?php _e('Customize', 'woo-advanced-shipment-tracking'); ?></a>
									</td>
								</tr>
							<?php } ?>										
						</tbody>
					</table>	
				</div>	
				
				<?php do_action( 'after_shipment_status_email_notifications' ); ?>
				
				<form method="post" id="trackship_late_shipments_form" action="" enctype="multipart/form-data">
					<div class="custom_order_status_section">
						<table class="form-table heading-table">
							<tbody>
								<tr valign="top">
									<td>
										<h3 style=""><?php _e( 'Admin Notifications', 'woo-advanced-shipment-tracking' ); ?></h3>
									</td>						
								</tr>
							</tbody>
						</table>	
						<table class="form-table shipment-status-email-table">
							<tbody>
								<tr class="<?php if($wcast_enable_late_shipments_admin_email == 1){ echo 'enable'; } else{ echo 'disable'; }?>">
									<td class="forminp status-label-column">
										<span class="shipment_status_toggle">								
											<input type="hidden" name="wcast_enable_late_shipments_admin_email" value="0"/>
											<input class="ast-tgl ast-tgl-flat" id="wcast_enable_late_shipments_admin_email" name="wcast_enable_late_shipments_admin_email" data-settings="late_shipments_email_settings" type="checkbox" <?php if($wcast_enable_late_shipments_admin_email == 1) { echo 'checked'; } ?> value="1"/>
											<label class="ast-tgl-btn ast-tgl-btn-green" for="wcast_enable_late_shipments_admin_email"></label>	
										</span>
										<button type="button" class="button button-primary shipment-status-label late-shipments"><?php _e('Late Shipments', 'woo-advanced-shipment-tracking'); ?></button>
									</td>
									<td class="forminp">
										<a class="edit_customizer_a late_shipments_a button-primary btn_ts_transparent" href="javascript:void(0);"><?php _e('Customize', 'woo-advanced-shipment-tracking'); ?></a>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<?php 
					$late_shipments_email_settings = get_option('late_shipments_email_settings');
					$wcast_late_shipments_days = isset( $late_shipments_email_settings['wcast_late_shipments_days'] ) ? $late_shipments_email_settings['wcast_late_shipments_days'] : '';
					$wcast_late_shipments_email_to = isset( $late_shipments_email_settings['wcast_late_shipments_email_to'] ) ? $late_shipments_email_settings['wcast_late_shipments_email_to'] : '';			
					$wcast_late_shipments_email_subject = isset( $late_shipments_email_settings['wcast_late_shipments_email_subject'] ) ? $late_shipments_email_settings['wcast_late_shipments_email_subject'] : '';			
					$wcast_late_shipments_email_content = isset( $late_shipments_email_settings['wcast_late_shipments_email_content'] ) ? $late_shipments_email_settings['wcast_late_shipments_email_content'] : '';
					$wcast_late_shipments_trigger_alert = isset( $late_shipments_email_settings['wcast_late_shipments_trigger_alert'] ) ? $late_shipments_email_settings['wcast_late_shipments_trigger_alert'] : '';			
					$wcast_late_shipments_daily_digest_time = isset( $late_shipments_email_settings['wcast_late_shipments_daily_digest_time'] ) ? $late_shipments_email_settings['wcast_late_shipments_daily_digest_time'] : ''; ?>
					<div class="custom_order_status_section">
						<table class="form-table late-shipments-email-content-table hide_table">
							<tr class="">
								<th scope="row" class="titledesc">
									<label for=""><?php _e('Late Shipment Days', 'woo-advanced-shipment-tracking'); ?></label>	
								</th>	
								<td class="forminp">
									<fieldset>
										<input class="input-text" type="number" name="wcast_late_shipments_days" id="wcast_late_shipments_days" min="1" value="<?php echo $wcast_late_shipments_days; ?>">
									</fieldset>
								</td>
							</tr>
							<tr class="">
								<th scope="row" class="titledesc">
									<label for=""><?php _e('Recipient(s)', 'woocommerce'); ?></label>	
								</th>	
								<td class="forminp">
									<fieldset>
										<input class="input-text regular-input " type="text" name="wcast_late_shipments_email_to" id="wcast_late_shipments_email_to" placeholder="<?php _e('E.g. {admin_email}, admin@example.org', 'woo-advanced-shipment-tracking'); ?>" value="<?php echo $wcast_late_shipments_email_to; ?>">
									</fieldset>
								</td>
							</tr>
							<tr class="">
								<th scope="row" class="titledesc">
									<label for=""><?php _e('Subject', 'woocommerce'); ?></label>	
								</th>	
								<td class="forminp">
									<fieldset>
										<input class="input-text regular-input " type="text" name="wcast_late_shipments_email_subject" id="wcast_late_shipments_email_subject" placeholder="<?php _e('Late shipment for order #{order_number}', 'woo-advanced-shipment-tracking'); ?>" value="<?php echo $wcast_late_shipments_email_subject; ?>">
									</fieldset>
								</td>
							</tr>
							<tr class="">
								<th scope="row" class="titledesc">
									<label for=""><?php _e('Email content', 'woo-advanced-shipment-tracking'); ?></label>	
								</th>	
								<td class="forminp">
									<fieldset>
										<textarea name="wcast_late_shipments_email_content" id="wcast_late_shipments_email_content" placeholder="<?php _e('This order was shipped {shipment_length} days ago, the shipment status is {shipment_status} and its est. delivery date is {est_delivery_date}.', 'woo-advanced-shipment-tracking'); ?>"><?php echo $wcast_late_shipments_email_content; ?></textarea>
									</fieldset>						
									<span><?php _e('Available variables:', 'woo-advanced-shipment-tracking'); ?> {site_title} {admin_email} {customer_first_name} {customer_last_name} {customer_company_name} {customer_username} {order_number} {shipment_length} {shipment_status} {est_delivery_date}</span>
								</td>
							</tr>
							<?php 
							$send_time_array = array();										
							for ( $hour = 0; $hour < 24; $hour++ ) {
								for ( $min = 0; $min < 60; $min = $min + 30 ) {
									$this_time = date( 'H:i', strtotime( "$hour:$min" ) );
									$send_time_array[ $this_time ] = $this_time;
								}	
							} ?>
							<tr class="">
								<th scope="row" class="titledesc">
									<label for=""><?php _e('Trigger Alert', 'woo-advanced-shipment-tracking'); ?></label>	
								</th>	
								<td class="forminp">
									<label class="" for="trigger_alert_as_it_happens">												
										<input type="radio" id="trigger_alert_as_it_happens" name="wcast_late_shipments_trigger_alert" value="as_it_happens" <?php if($wcast_late_shipments_trigger_alert == 'as_it_happens')echo 'checked'; ?>>
										<span class=""><?php _e('As it Happens', 'woo-advanced-shipment-tracking'); ?></span>	
									</label>
									<label class="" for="trigger_alert_daily_digest_on">												
										<input type="radio" id="trigger_alert_daily_digest_on" name="wcast_late_shipments_trigger_alert" value="daily_digest_on" <?php if($wcast_late_shipments_trigger_alert == 'daily_digest_on')echo 'checked'; ?>>
										<span class=""><?php _e('Daily Digest on', 'woo-advanced-shipment-tracking'); ?></span>								
									</label>
									<select class="select daily_digest_time" name="wcast_late_shipments_daily_digest_time"> 
										<?php foreach((array)$send_time_array as $key1 => $val1 ){ ?>
											<option <?php if($wcast_late_shipments_daily_digest_time == $key1)echo 'selected'; ?> value="<?php echo $key1?>" ><?php echo $val1; ?></option>
										<?php } ?>
									</select>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<button name="save" class="button-primary woocommerce-save-button btn_green2 btn_large" type="submit" value="Save changes"><?php _e( 'Save Changes', 'woo-advanced-shipment-tracking' ); ?></button>
									<div class="spinner"></div>								
									<?php wp_nonce_field( 'ts_late_shipments_email_form', 'ts_late_shipments_email_form_nonce' );?>
									<input type="hidden" name="action" value="ts_late_shipments_email_form_update">
								</td>
							</tr>
						</table>	
					</div>	
				</form>
			</section>	
			<section class="inner_tab_section shipment-status-sms-section">
				<?php 
				if(!class_exists('SMS_for_WooCommerce')){ ?>
					<div class="smswoo_notification_section">
						<div class="ast-row">
							<div class="ast-col as-col-4">
								<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/smswoo-addons-icon.jpg">
							</div>
							<div class="ast-col as-col-8">
								<h3>SMSWOO – SMS for WooCommerce</h3>
								<p>The SMSWOO plugin connects your WooCommerce store with SMS API service providers and lets you send your customers automated SMS notifications about order & delivery updates. You can also send SMS messages to store admins about new orders, refunds, low stock, and you can even manually send custom SMS text messages to customers from the order admin.</p>
								<a href="https://www.zorem.com/product/sms-for-woocommerce/" class="button button-primary btn_ast2" target="blank">From $79</a>
							</div>							
						</div>
					</div>
				<?php }
				do_action( 'shipment_status_sms_section' );	
				?>
			</section>	
		</div>				
	</div>
</section>