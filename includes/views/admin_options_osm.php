<div class="custom_order_status_section">							
	<table class="form-table order-status-table">
		<tbody>					
			<tr valign="top">
				<td class="forminp">
					<input type="hidden" name="wc_ast_status_shipped" value="0"/>
					<input class="ast-tgl ast-tgl-flat" id="wc_ast_status_shipped" name="wc_ast_status_shipped" type="checkbox" <?php ( get_option( 'wc_ast_status_shipped', 1 ) ) ? esc_html_e( 'checked' ) : ''; ?> value="1"/>
					<label class="ast-tgl-btn" for="wc_ast_status_shipped"></label>						
				</td>
				<td colspan="2" class="status_shipped_label">
					<?php esc_html_e( 'Rename the “Completed” Order status label to “Shipped”', 'woo-advanced-shipment-tracking' ); ?>
				</td>	
				<td style="text-align:right;">
					<a class='settings_edit' href="<?php echo esc_url( admin_url( 'admin.php?page=ast_customizer&email_type=completed' ) ); ?>"><span class="dashicons dashicons-admin-generic"></span></a>
				</td>
			</tr>
			
			<?php
			$osm_data = $this->get_osm_data();
			foreach ( $osm_data as $o_status => $data ) {
				$checked = ( get_option( $data['id'] ) ) ? 'checked' : '';
				$disable_row = ( !get_option( $data['id'] ) ) ? 'disable_row' : '';
				$wc_ast_status_shipped = get_option( 'wc_ast_status_shipped' );
				if ( $wc_ast_status_shipped && 'shipped' == $o_status ) {
					$checked = '';	
					$disable_row = 'disable_row';	
				}	
				?>
				<tr valign="top" class="<?php echo esc_html( $o_status ); ?>_row <?php echo esc_html( $disable_row ); ?>">	
					<td class="forminp">
						<input type="hidden" name="<?php echo esc_html( $data['id'] ); ?>" value="0"/>
						<input class="ast-tgl ast-tgl-flat order_status_toggle" id="<?php echo esc_html( $data['id'] ); ?>" name="<?php echo esc_html( $data['id'] ); ?>" type="checkbox"  value="1" <?php echo esc_html( $checked ); ?> />
						<label class="ast-tgl-btn" for="<?php echo esc_html( $data['id'] ); ?>"></label>	
					</td>
					<td class="forminp status-label-column">
						<span class="order-label <?php echo esc_html( $data['label_class'] ); ?>">
							<?php 
							if ( get_option( $data['id'] ) ) {
								esc_html_e( wc_get_order_status_name( $data['slug'] ) );	
							} else {
								echo esc_html( $data['label'] );
							}
							?>
						</span>
					</td>												
					<td class="forminp">								
						<?php
						$ast_enable_email = get_option($data['option_id']);
						
						$checked = '';	
						
						if ( isset( $ast_enable_email['enabled'] ) ) {
							if ( 'yes' == $ast_enable_email['enabled'] || 1 == $ast_enable_email['enabled'] ) {
								$checked = 'checked';
							}
						}
						
						?>
						<fieldset>
							<input class="input-text regular-input color_input" type="text" name="<?php echo esc_html( $data['label_color_field'] ); ?>" id="<?php echo esc_html( $data['label_color_field'] ); ?>" style="" value="<?php esc_html_e( get_option( $data['label_color_field'], '#1e73be' ) ); ?>" placeholder="">
							<select class="select custom_order_color_select" id="<?php echo esc_html( $data['font_color_field'] ); ?>" name="<?php echo esc_html( $data['font_color_field'] ); ?>">		
								<option value="#fff" <?php ( '#fff' == get_option( $data['font_color_field'], '#fff' ) ) ? esc_html_e( 'selected' ) : ''; ?>><?php esc_html_e( 'Light Font', 'woo-advanced-shipment-tracking' ); ?></option>
								<option value="#000" <?php ( '#000' == get_option( $data['font_color_field'], '#fff' ) ) ? esc_html_e( 'selected' ) : ''; ?>><?php esc_html_e( 'Dark Font', 'woo-advanced-shipment-tracking' ); ?></option>
							</select>							
						</fieldset>
					</td>
					<td class="forminp" style="text-align:right;">
						<?php if ( 'delivered' != $o_status ) { ?>
						<fieldset>
							<label class="send_email_label">
								<input type="hidden" name="<?php esc_html_e( $data['email_field'] ); ?>" value="0"/>
								<input type="checkbox" name="<?php esc_html_e( $data['email_field'] ); ?>" id="<?php esc_html_e( $data['email_field'] ); ?>"class="enable_order_status_email_input"  <?php esc_html_e( $checked ); ?> value="1"><?php esc_html_e( 'Send Email', 'woo-advanced-shipment-tracking' ); ?>
							</label>
							<?php if ( 'updated_tracking' != $o_status ) { ?>
								<a class='settings_edit' href="<?php echo esc_url( $data['edit_email'] ); ?>"><span class="dashicons dashicons-admin-generic"></span></a>
							<?php } ?>
						</fieldset>
						<?php } ?>
					</td>
				</tr>	
			<?php
			} 
			do_action('ast_orders_status_column_end');
			?>
		</tbody>
	</table>	
	<?php wp_nonce_field( 'wc_ast_order_status_form', 'wc_ast_order_status_form_nonce' ); ?>	
	<input type="hidden" name="action" value="wc_ast_custom_order_status_form_update">									
</div>
