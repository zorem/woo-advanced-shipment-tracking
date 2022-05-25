<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * View Order: Tracking information
 *
 * Shows tracking numbers view order page
 * 
 */

if ( $tracking_items ) :
	$settings = new wcast_initialise_customizer_settings();						
	$ast = new WC_Advanced_Shipment_Tracking_Actions();
		
	$select_tracking_template = $ast->get_option_value_from_array( 'tracking_info_settings', 'select_tracking_template', $settings->defaults['select_tracking_template'] );
	
	$show_provider_th = 1;
	$colspan = 1;
	
	$display_thumbnail = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'display_shipment_provider_image', $settings->defaults['display_shipment_provider_image'] );
	$display_shipping_provider_name = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'display_shipment_provider_name', $settings->defaults['display_shipment_provider_name'] );
	$tracking_number_link = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'tracking_number_link', '' );
		
	if ( 1 == $display_shipping_provider_name && 1 == $display_thumbnail ) {
		$show_provider_th = 1;
		$colspan = 2;
	} else if ( 1 != $display_shipping_provider_name && 1 == $display_thumbnail ) {
		$show_provider_th = 1;
		$colspan = 1;
	} else if ( 1 == $display_shipping_provider_name && 1 != $display_thumbnail ) {
		$show_provider_th = 1;
		$colspan = 1;
	} else if ( 1 != $display_shipping_provider_name && 1 != $display_thumbnail ) {
		$show_provider_th = 0;
		$colspan = 1;
	} else {
		$show_provider_th = 0;		
	} 
		
	$email_border_color = $ast->get_option_value_from_array( 'tracking_info_settings', 'table_border_color', $settings->defaults['table_border_color'] );
	$email_border_size = $ast->get_option_value_from_array( 'tracking_info_settings', 'table_border_size', $settings->defaults['table_border_size'] );
	$hide_trackig_header = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'hide_trackig_header', '' );
	$shipment_tracking_header = $ast->get_option_value_from_array( 'tracking_info_settings', 'header_text_change', 'Tracking Information' );
	$shipment_tracking_header_text = $ast->get_option_value_from_array( 'tracking_info_settings', 'additional_header_text', '' );
	$email_table_backgroud_color = $ast->get_option_value_from_array( 'tracking_info_settings', 'table_bg_color', $settings->defaults['table_bg_color'] );
	$table_content_line_height = $ast->get_option_value_from_array( 'tracking_info_settings', 'table_content_line_height', $settings->defaults['table_content_line_height'] );
	$table_content_font_weight = $ast->get_option_value_from_array( 'tracking_info_settings', 'table_content_font_weight', $settings->defaults['table_content_font_weight'] );
	$table_header_bg_color = $ast->get_option_value_from_array( 'tracking_info_settings', 'table_header_bg_color', $settings->defaults['table_header_bg_color'] );
	$table_header_font_size = $ast->get_option_value_from_array( 'tracking_info_settings', 'table_header_font_size', $settings->defaults['table_header_font_size'] );
	$table_header_font_weight = $ast->get_option_value_from_array( 'tracking_info_settings', 'table_header_font_weight', $settings->defaults['table_header_font_weight'] );	
	$table_header_font_color = $ast->get_option_value_from_array( 'tracking_info_settings', 'table_header_font_color', $settings->defaults['table_header_font_color'] );
	$table_content_font_size = $ast->get_option_value_from_array( 'tracking_info_settings', 'table_content_font_size', $settings->defaults['table_content_font_size'] );
	$table_content_font_color = $ast->get_option_value_from_array( 'tracking_info_settings', 'table_content_font_color', $settings->defaults['table_content_font_color'] );
	$tracking_link_font_color = $ast->get_option_value_from_array( 'tracking_info_settings', 'tracking_link_font_color', $settings->defaults['tracking_link_font_color'] );
	$tracking_link_bg_color = $ast->get_option_value_from_array( 'tracking_info_settings', 'tracking_link_bg_color', $settings->defaults['tracking_link_bg_color'] );
	$tracking_link_style = 'color: ' . $tracking_link_font_color . ' ;background:' . $tracking_link_bg_color . ';margin-bottom: 0;';
	$hide_table_header = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'hide_table_header', '' );
	$remove_date_from_tracking_info = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'remove_date_from_tracking', $settings->defaults['remove_date_from_tracking'] );
	$show_track_label = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'show_track_label', $settings->defaults['show_track_label'] );
	$provider_header_text = $ast->get_option_value_from_array( 'tracking_info_settings', 'provider_header_text', $settings->defaults['provider_header_text'] );
	$tracking_number_header_text = $ast->get_option_value_from_array( 'tracking_info_settings', 'tracking_number_header_text', $settings->defaults['tracking_number_header_text'] );
	$shipped_date_header_text = $ast->get_option_value_from_array( 'tracking_info_settings', 'shipped_date_header_text', $settings->defaults['shipped_date_header_text'] );
	$track_header_text = $ast->get_option_value_from_array( 'tracking_info_settings', 'track_header_text', $settings->defaults['track_header_text'] );
	$simple_layout_content = $ast->get_option_value_from_array( 'tracking_info_settings', 'simple_layout_content', $settings->defaults['simple_layout_content'] );
	$simple_provider_font_size = $ast->get_option_value_from_array( 'tracking_info_settings', 'simple_provider_font_size', $settings->defaults['simple_provider_font_size'] );
	$simple_provider_font_color = $ast->get_option_value_from_array( 'tracking_info_settings', 'simple_provider_font_color', $settings->defaults['simple_provider_font_color'] );
	$show_provider_border = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'show_provider_border', $settings->defaults['show_provider_border'] );
	$provider_border_color = $ast->get_option_value_from_array( 'tracking_info_settings', 'provider_border_color', $settings->defaults['provider_border_color'] );

	if ( is_rtl() ) {
		$header_content_text_align = 'right';
	} else {
		$header_content_text_align = $ast->get_option_value_from_array( 'tracking_info_settings', 'header_content_text_align', $settings->defaults['header_content_text_align'] );
	}
	
	$th_column_style = 'background:' . $table_header_bg_color . ';text-align: ' . $header_content_text_align . '; font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;font-size:' . $table_header_font_size . 'px; color: ' . $table_header_font_color . ' ; border: ' . $email_border_size . 'px solid ' . $email_border_color . '; padding: 12px;font-weight:' . $table_header_font_weight . ';';
	
	$td_column_style = 'text-align: ' . $header_content_text_align . '; font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif; font-size:' . $table_content_font_size . 'px;font-weight:' . $table_content_font_weight . '; color: ' . $table_content_font_color . ' ; border: ' . $email_border_size . 'px solid ' . $email_border_color . '; padding: 12px;line-height: ' . $table_content_line_height . 'px;';	
	
	if ( 1 != $hide_trackig_header ) { 
		?>
		<h2><?php esc_html_e( apply_filters( 'woocommerce_shipment_tracking_my_orders_title', __( $shipment_tracking_header, 'woo-advanced-shipment-tracking' ) ) ); ?></h2>
	<?php } ?>
	
	<p><?php esc_html_e( $shipment_tracking_header_text ); ?></p>
	
	<?php if ( 'simple_list' == $select_tracking_template ) { ?>
		<div class="tracking_info">
			<ul class="tracking_list">
				<?php 
				foreach ( $tracking_items as $key => $tracking_item ) {
				
					$shipment_status = get_post_meta( $order_id, 'shipment_status', true);
					$ts_status = '';
					
					if ( isset( $shipment_status[$key] ) ) {
						if ( isset( $shipment_status[$key]['status'] ) ) {
							$ts_status = $shipment_status[$key]['status'];	
						}			
					}
					
					$ts_tracking_page = $ast->check_ts_tracking_page_for_tracking_item( $order_id, $tracking_item, $ts_status );
						
					$date_shipped = ( isset( $tracking_item['date_shipped'] ) ) ? $tracking_item['date_shipped'] : gmdate( 'Y-m-d' );
					
					$simple_layout_content_updated = ''; 
					?>
					
					<li class="tracking_list_li">
						<div class="tracking_list_div" style="font-size:<?php esc_html_e( $simple_provider_font_size ); ?>px;color:<?php esc_html_e( $simple_provider_font_color ); ?>;border-bottom:<?php esc_html_e( $show_provider_border ); ?>px solid <?php esc_html_e( $provider_border_color ); ?>">
							<?php 
							
							$formatted_tracking_provider = apply_filters( 'ast_provider_title', esc_html( $tracking_item[ 'formatted_tracking_provider' ] ));
							
							$simple_layout_content_updated = str_replace( '{ship_date}', date_i18n( get_option( 'date_format' ), $date_shipped ), $simple_layout_content );
							
							$simple_layout_content_updated = str_replace( '{shipping_provider}', $formatted_tracking_provider, $simple_layout_content_updated );
							
							if ( $ts_tracking_page ) {
								$tracking_number_link = '<a href="javascript:void(0)" class="open_tracking_lightbox" data-order="' . $order_id . '" data-tracking="' . $tracking_item['tracking_number'] . '" wp_nonce="' . wp_create_nonce( 'open_tracking_lightbox' ) . '">' . $tracking_item['tracking_number'] . '</a>';
							} else {
								$tracking_number_link = '<a target="_blank" href="' . esc_url( $tracking_item['ast_tracking_link'] ) . '">' . $tracking_item['tracking_number'] . '</a>';	
							} 
							
							$simple_layout_content_updated = str_replace( '{tracking_number_link}', $tracking_number_link, $simple_layout_content_updated );
							
							echo wp_kses_post( $simple_layout_content_updated );
							?>
						</div>
						<?php do_action( 'ast_tracking_simple_list_email_body', $order_id, $tracking_item ); ?>
					</li>
				<?php } ?>			
			</ul>
		</div>
	<?php } else if ( 'default_table' == $select_tracking_template ) { ?>
		<table class="shop_table shop_table_responsive my_account_tracking" style="width: 100%;border-collapse: collapse;background:<?php esc_html_e( $email_table_backgroud_color ); ?>">
			<?php if ( 1 != $hide_table_header ) { ?>
			<thead>
				<tr>
					<?php if ( $show_provider_th ) { ?>
						<th class="tracking-provider" colspan="<?php esc_html_e( $colspan ); ?>" style="<?php esc_html_e( $th_column_style ); ?>">
							<?php esc_html_e( $provider_header_text, 'woo-advanced-shipment-tracking' ); ?>
						</th>
					<?php 
					}
					
					do_action( 'ast_tracking_my_acoount_header', $order_id, $th_column_style ); 
					?>
					
					<th class="" style="<?php esc_html_e( $th_column_style ); ?>"><?php esc_html_e( $tracking_number_header_text, 'woo-advanced-shipment-tracking' ); ?></th>
					
					<?php if ( 1 != $remove_date_from_tracking_info ) { ?>
						<th class="date-shipped" style="<?php esc_html_e( $th_column_style ); ?>"><?php esc_html_e( $shipped_date_header_text, 'woo-advanced-shipment-tracking' ); ?></th>
					<?php 
					}
					
					if ( !$tracking_number_link ) {							
						?>
						<th class="order-actions" style="<?php esc_html_e( $th_column_style ); ?>">
							<?php 
							if ( 1 == $show_track_label ) { 
								esc_html_e( $track_header_text, 'woo-advanced-shipment-tracking' ); 
							}
							?>
						</th>
					<?php } ?>
				</tr>
			</thead>
			<?php } ?>
			<tbody>
			<?php
			foreach ( $tracking_items as $key => $tracking_item ) {
		
				$shipment_status = get_post_meta( $order_id, 'shipment_status', true);
				$ts_status = '';
					
				if ( isset( $shipment_status[$key] ) ) {
					if ( isset( $shipment_status[$key]['status'] ) ) {
						$ts_status = $shipment_status[$key]['status'];	
					}			
				}
					$date_shipped = ( isset( $tracking_item['date_shipped'] ) ) ? $tracking_item['date_shipped'] : gmdate('Y-m-d');
					
					$ts_tracking_page = $ast->check_ts_tracking_page_for_tracking_item( $order_id, $tracking_item, $ts_status );
					
				?>
					
					<tr class="tracking">
						<?php if ( 1 == $display_thumbnail ) { ?>
							<td class="tracking-provider" style="<?php esc_html_e( $td_column_style ); ?>" data-title="<?php esc_html_e( 'Provider', 'woo-advanced-shipment-tracking' ); ?>">
								<img style="width: 50px;margin-right: 5px;vertical-align: middle;" src="<?php echo esc_url( $tracking_item['tracking_provider_image'] ); ?>">
							</td>
						<?php } ?>
						
						<?php if ( 1 == $display_shipping_provider_name ) { ?>
							<td class="tracking-provider" style="<?php esc_html_e( $td_column_style ); ?>" data-title="<?php esc_html_e( 'Provider Name', 'woo-advanced-shipment-tracking' ); ?>">
								<?php 
								if ( '' != $tracking_item[ 'formatted_tracking_provider' ] ) {
									esc_html_e( apply_filters( 'ast_provider_title', esc_html( $tracking_item[ 'formatted_tracking_provider' ] ) ) );
								} else {
									esc_html_e( apply_filters( 'ast_provider_title', esc_html( $tracking_item[ 'tracking_provider' ] ) ) );
								}
								?>
							</td>
						<?php 
						}
						
						do_action( 'ast_tracking_my_account_body', $order_id, $tracking_item, $td_column_style ); 
						?>
						
						<td class="tracking-number" style="<?php esc_html_e( $td_column_style ); ?>" data-title="<?php esc_html_e( 'Tracking Number', 'woo-advanced-shipment-tracking' ); ?>">
							<?php 
							if ( '' !== $tracking_item['ast_tracking_link'] && $tracking_number_link ) {
								if ( $ts_tracking_page ) { 
									?>
									<a href="javascript:void(0)" class="open_tracking_lightbox" data-order="<?php esc_html_e( $order_id ); ?>" data-tracking="<?php echo esc_html( $tracking_item['tracking_number'] ); ?>" wp_nonce="<?php esc_html_e( wp_create_nonce( 'open_tracking_lightbox' ) ); ?>" style="<?php esc_html_e( $tracking_link_style ); ?>"><?php echo esc_html( $tracking_item['tracking_number'] ); ?></a>
								<?php } else { ?>
									<a href="<?php echo esc_url( $tracking_item['ast_tracking_link'] ); ?>" target="_blank" style="<?php esc_html_e( $tracking_link_style ); ?>"><?php echo esc_html( $tracking_item['tracking_number'] ); ?></a>
								<?php 
								}
							} else {
								echo esc_html( $tracking_item['tracking_number'] );	
							}
							?>
						</td>
						
						<?php if ( 1 != $remove_date_from_tracking_info ) { ?>
							<td class="date-shipped" style="<?php esc_html_e( $td_column_style ); ?>" data-title="<?php esc_html_e( 'Date', 'woocommerce' ); ?>" style="text-align:left; white-space:nowrap;">
								<time datetime="<?php esc_html_e( gmdate( 'Y-m-d', $date_shipped ) ); ?>" title="<?php esc_html_e( gmdate( 'Y-m-d', $date_shipped ) ); ?>"><?php esc_html_e( date_i18n( get_option( 'date_format' ), $date_shipped ) ); ?></time>
							</td>
						<?php 
						}
						
						if ( !$tracking_number_link ) { 
							?>
							<td class="order-actions" style="<?php esc_html_e( $td_column_style ); ?>;text-align:center;">
							<?php 
							if ( '' !== $tracking_item['ast_tracking_link'] ) { 
									
								if ( $ts_tracking_page ) {
									?>
										<a href="javascript:void(0)" class="button open_tracking_lightbox" data-order="<?php esc_html_e( $order_id ); ?>" data-tracking="<?php echo esc_html( $tracking_item['tracking_number'] ); ?>" wp_nonce="<?php esc_html_e( wp_create_nonce( 'open_tracking_lightbox' ) ); ?>" style="<?php esc_html_e( $tracking_link_style ); ?>"><?php esc_html_e( 'Track', 'woo-advanced-shipment-tracking' ); ?></a>
									<?php } else { ?>
										<a href="<?php echo esc_url( $tracking_item['ast_tracking_link'] ); ?>" target="_blank" class="button" style="<?php esc_html_e( $tracking_link_style ); ?>"><?php esc_html_e( 'Track', 'woo-advanced-shipment-tracking' ); ?></a>
									<?php } } ?>
							</td>
						<?php } ?>
					</tr>
		<?php } ?>
			</tbody>
		</table>
<?php 
	} 
	
	if ( !isset( $show_shipment_status ) ) {
		$show_shipment_status = false;
	}	
	
	do_action( 'my_account_tracking_info_template' , $order_id, $tracking_items, $show_shipment_status ); 
	?>
	
	<div id="" class="popupwrapper ts_tracking_popup" style="display:none;">
		<div class="popuprow">
			
		</div>	
		<div class="popupclose"></div>
	</div>  
<?php	
endif;
