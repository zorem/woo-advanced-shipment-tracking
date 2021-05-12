<div class="tabs_inner_section" id="content_osm_settings">
	<form method="post" id="wc_ast_order_status_form" action="" enctype="multipart/form-data">
		<div class="custom_order_status_section">							
			<table class="form-table order-status-table">
				<tbody>					
					<tr valign="top">
						<td class="forminp">
							<input type="hidden" name="wc_ast_status_shipped" value="0"/>
							<input class="ast-tgl ast-tgl-flat" id="wc_ast_status_shipped" name="wc_ast_status_shipped" type="checkbox" <?php if(get_option('wc_ast_status_shipped')){echo 'checked'; } ?> value="1"/>
							<label class="ast-tgl-btn" for="wc_ast_status_shipped"></label>						
						</td>
						<td colspan="2" class="status_shipped_label">
							<?php _e( 'Rename the “Completed” Order status label to “Shipped”', 'woo-advanced-shipment-tracking' ); ?>
						</td>	
					</tr>
					
					<?php $osm_data = $this->get_osm_data();
					foreach( $osm_data as $status => $data ){
						$checked = ( get_option( $data['id'] ) ) ? 'checked' : '';
						$disable_row = ( !get_option( $data['id'] ) ) ? 'disable_row' : '';
						$wc_ast_status_shipped = get_option( 'wc_ast_status_shipped' );
						if( $wc_ast_status_shipped && $status == 'shipped' ) {
							$checked = '';	
							$disable_row = 'disable_row';	
						}	
						?>
						<tr valign="top" class="<?php echo $status;?>_row <?php echo $disable_row; ?>">	
							<td class="forminp">
								<input type="hidden" name="<?php echo $data['id'];?>" value="0"/>
								<input class="ast-tgl ast-tgl-flat order_status_toggle" id="<?php echo $data['id'];?>" name="<?php echo $data['id'];?>" type="checkbox"  value="1" <?php echo $checked; ?> />
								<label class="ast-tgl-btn" for="<?php echo $data['id'];?>"></label>	
							</td>
							<td class="forminp status-label-column">
								<span class="order-label <?php echo $data['label_class'];?>">
									<?php 
									if ( get_option( $data['id'] ) ) {
										_e( wc_get_order_status_name( $data['slug'] ), 'woo-advanced-shipment-tracking' );	
									} else{
										echo $data['label'];
									} ?>								
								</span>
							</td>												
							<td class="forminp">								
								<?php
								$ast_enable_email = get_option($data['option_id']);
								
								$checked = '';	
								
								if(isset( $ast_enable_email['enabled'] )){
									if( $ast_enable_email['enabled'] == 'yes' || $ast_enable_email['enabled'] == 1 ){
										$checked = 'checked';
									}
								}
								
								?>
								<fieldset>
									<input class="input-text regular-input color_input" type="text" name="<?php echo $data['label_color_field']; ?>" id="<?php echo $data['label_color_field']; ?>" style="" value="<?php echo get_option($data['label_color_field'],'#1e73be')?>" placeholder="">
									<select class="select custom_order_color_select" id="<?php echo $data['font_color_field']; ?>" name="<?php echo $data['font_color_field']; ?>">		
										<option value="#fff" <?php if(get_option($data['font_color_field'],'#fff') == '#fff'){ echo 'selected'; }?>><?php _e( 'Light Font', 'woo-advanced-shipment-tracking' ); ?></option>
										<option value="#000" <?php if(get_option($data['font_color_field'],'#fff') == '#000'){ echo 'selected'; }?>><?php _e( 'Dark Font', 'woo-advanced-shipment-tracking' ); ?></option>
									</select>
									<label class="send_email_label">
										<input type="hidden" name="<?php echo $data['email_field']; ?>" value="0"/>
										<input type="checkbox" name="<?php echo $data['email_field']; ?>" id="<?php echo $data['email_field']; ?>"class="enable_order_status_email_input"  <?php echo $checked; ?> value="1"><?php _e( 'Send Email', 'woo-advanced-shipment-tracking' ); ?></label>
										<a class='settings_edit' href="<?php echo $data['edit_email']; ?>"><?php _e( 'edit email', 'woocommerce' ) ?></a>
								</fieldset>
							</td>
						</tr>	
					<?php } 
					do_action("ast_orders_status_column_end"); ?>	
				</tbody>
			</table>	
			<?php wp_nonce_field( 'wc_ast_order_status_form', 'wc_ast_order_status_form_nonce' );?>	
			<input type="hidden" name="action" value="wc_ast_custom_order_status_form_update">									
		</div>	
	</form>			
</div>