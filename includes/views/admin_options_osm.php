<div class="custom_order_status_section">							
	<table class="form-table order-status-table">
		<tbody>					
			<tr valign="top">
				<td>
					<input type="hidden" name="wc_ast_status_shipped" value="0"/>
					<input class="ast-tgl ast-tgl-flat" id="wc_ast_status_shipped" name="wc_ast_status_shipped" type="checkbox" <?php ( get_ast_settings( 'ast_general_settings', 'wc_ast_status_shipped', 1 ) ) ? esc_html_e( 'checked' ) : ''; ?> value="1"/>
					<label class="ast-tgl-btn" for="wc_ast_status_shipped"></label>						
				</td>
				<td colspan="2" class="status_shipped_label">
					<?php esc_html_e( 'Rename the "Completed" Order status label to "Shipped"', 'woo-advanced-shipment-tracking' ); ?>
				</td>	
				<td style="text-align:right;">
					<a class='settings_edit' href="<?php echo esc_url( admin_url( 'admin.php?page=ast_customizer&email_type=completed' ) ); ?>"><span class="dashicons dashicons-admin-generic"></span></a>
				</td>
			</tr>
			
			<?php
			$osm_data = $this->get_osm_data();
			foreach ( $osm_data as $o_status => $data ) {
				$is_pro = isset( $data['pro'] ) && $data['pro'];

				$checked = ( get_ast_settings( 'ast_general_settings', $data['id'] ) ) ? 'checked' : '';
				$disable_row = ( !get_ast_settings( 'ast_general_settings', $data['id'] ) ) ? 'disable_row' : '';
				$wc_ast_status_shipped = get_ast_settings( 'ast_general_settings', 'wc_ast_status_shipped', '' );
				if ( $wc_ast_status_shipped && 'shipped' == $o_status ) {
					$checked = '';
					$disable_row = 'disable_row';
				}

				// Add PRO row class if it's a PRO feature
				$row_class = $o_status . '_row ' . $disable_row;
				if ( $is_pro ) {
					$row_class .= ' ast-pro-status-row';
				}
				?>
				<tr valign="top" class="<?php echo esc_html( $row_class ); ?>">
					<td>
						<?php if ( $is_pro ) { ?>
							<input type="checkbox" class="ast-tgl ast-tgl-flat" disabled/>
							<label class="ast-tgl-btn"></label>
						<?php } else { ?>
							<input type="hidden" name="<?php echo esc_html( $data['id'] ); ?>" value="0"/>
							<input class="ast-tgl ast-tgl-flat order_status_toggle" id="<?php echo esc_html( $data['id'] ); ?>" name="<?php echo esc_html( $data['id'] ); ?>" type="checkbox"  value="1" <?php echo esc_html( $checked ); ?> />
							<label class="ast-tgl-btn" for="<?php echo esc_html( $data['id'] ); ?>"></label>
						<?php } ?>
					</td>
					<td class="status-label-column">
						<span class="order-label <?php echo esc_html( $data['label_class'] ); ?>">
							<?php
							if ( $is_pro ) {
								echo esc_html( $data['label'] );
							} else if ( get_ast_settings( 'ast_general_settings', $data['id'], '' ) ) {
								esc_html_e( wc_get_order_status_name( $data['slug'] ) );
							} else {
								echo esc_html( $data['label'] );
							}
							?>
						</span>
					</td>
					<td>
						<?php
						$checked_email = '';
						if ( ! $is_pro ) {
							$ast_enable_email = get_ast_settings( 'ast_general_settings', $data['email_field'], '' );
							if ( 'yes' == $ast_enable_email || 1 == $ast_enable_email ) {
								$checked_email = 'checked';
							}
						}
						?>
						<fieldset>
							<input class="input-text regular-input color_input" type="text" name="<?php echo esc_html( $data['label_color_field'] ); ?>" id="<?php echo esc_html( $data['label_color_field'] ); ?>" style="width: 80px;" value="<?php echo $is_pro ? '#1e73be' : esc_attr( get_ast_settings( 'ast_general_settings', $data['label_color_field'], '#1e73be' ) ); ?>" placeholder="" <?php if ( $is_pro ) echo 'disabled'; ?>>
							<select class="select custom_order_color_select" id="<?php echo esc_html( $data['font_color_field'] ); ?>" name="<?php echo esc_html( $data['font_color_field'] ); ?>" <?php if ( $is_pro ) echo 'disabled'; ?>>
								<option value="#fff"><?php esc_html_e( 'Light Font', 'woo-advanced-shipment-tracking' ); ?></option>
								<?php if ( ! $is_pro ) { ?>
								<option value="#000" <?php ( '#000' == get_ast_settings( 'ast_general_settings', $data['font_color_field'], '#fff' ) ) ? esc_attr( 'selected' ) : ''; ?>><?php esc_html_e( 'Dark Font', 'woo-advanced-shipment-tracking' ); ?></option>
								<?php } ?>
							</select>
						</fieldset>
					</td>
					<td style="text-align:right;">
						<?php if ( 'delivered' != $o_status ) { ?>
						<fieldset>
							<label class="send_email_label">
								<?php if ( ! $is_pro ) { ?>
									<input type="hidden" name="<?php esc_html_e( $data['email_field'] ); ?>" value="0"/>
								<?php } ?>
								<input type="checkbox" name="<?php esc_html_e( $data['email_field'] ); ?>" id="<?php esc_html_e( $data['email_field'] ); ?>" class="enable_order_status_email_input" <?php echo esc_html( $checked_email ); ?> value="1" <?php if ( $is_pro ) echo 'disabled'; ?>><?php esc_html_e( 'Send Email', 'woo-advanced-shipment-tracking' ); ?>
							</label>
							<?php if ( $is_pro ) { ?>
								<span class="ast-pro-badge">PRO</span>
								<span class="dashicons dashicons-lock"></span>
							<?php } else if ( 'updated_tracking' != $o_status && ! empty( $data['edit_email'] ) ) { ?>
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
