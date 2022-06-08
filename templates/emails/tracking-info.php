<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shipment Tracking
 *
 * Shows tracking information in the HTML order email
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
	
	if ( is_rtl() ) {
		$header_content_text_align = 'right';
	} else {
		$header_content_text_align = $ast->get_option_value_from_array( 'tracking_info_settings', 'header_content_text_align', $settings->defaults['header_content_text_align'] );
	}
	
	$table_padding = 10;	
	
	$email_border_color = $ast->get_option_value_from_array( 'tracking_info_settings', 'table_border_color', $settings->defaults['table_border_color'] );
	$email_border_size = $ast->get_option_value_from_array( 'tracking_info_settings', 'table_border_size', $settings->defaults['table_border_size'] );
	$hide_trackig_header = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'hide_trackig_header', '');
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
	
	$email_preview = ( isset( $_REQUEST['wcast-tracking-preview'] ) && '1' === $_REQUEST['wcast-tracking-preview'] ) ? true : false;	
	
	$text_align = is_rtl() ? 'right' : 'left'; 
	
	$th_column_style = 'background:' . $table_header_bg_color . ';text-align: ' . $header_content_text_align . '; font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;font-size:' . $table_header_font_size . 'px;font-weight:' . $table_header_font_weight . '; color: ' . $table_header_font_color . ' ; border: ' . $email_border_size . 'px solid ' . $email_border_color . '; padding: ' . $table_padding . 'px;';
	$td_column_style = 'text-align: ' . $header_content_text_align . '; font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif; font-size:' . $table_content_font_size . 'px;font-weight:' . $table_content_font_weight . '; color: ' . $table_content_font_color . ' ; border: ' . $email_border_size . 'px solid ' . $email_border_color . '; padding: ' . $table_padding . 'px;min-width: auto;';
	$tracking_link_style = 'color: ' . $tracking_link_font_color . ' ;background:' . $tracking_link_bg_color . ';padding: 10px;text-decoration: none;';
	$tracking_link_style2 = 'color: ' . $tracking_link_font_color . ';padding: 10px;text-decoration: none;';
	
	$shipment_status = get_post_meta( $order_id, 'shipment_status', true);
	
	if ( $email_preview ) {
		?>
		<h2 class="header_text <?php esc_html_e( ( $hide_trackig_header ) ? 'hide' : '' ); ?>" style="text-align:<?php esc_html_e( $text_align ); ?>;">
			<?php esc_html_e( apply_filters( 'woocommerce_shipment_tracking_my_orders_title', __( $shipment_tracking_header, 'woo-advanced-shipment-tracking' ) ) ); ?>
		</h2>
	<?php } else { ?>
		<h2 class="header_text" style="text-align:<?php esc_html_e( $text_align ); ?>;<?php esc_html_e( ( $hide_trackig_header ) ? 'display:none' : '' ); ?>">
			<?php esc_html_e( apply_filters( 'woocommerce_shipment_tracking_my_orders_title', __( $shipment_tracking_header, 'woo-advanced-shipment-tracking' ) ) ); ?>
		</h2>
	<?php } ?>
	
	<p class="addition_header"><?php esc_html_e( $shipment_tracking_header_text ); ?></p>
	
	<?php if ( 'simple_list' == $select_tracking_template ) { ?>
		<div class="tracking_info">
			<ul class="tracking_list">
				<?php 
				foreach ( $tracking_items as $tracking_item ) {
						
					$date_shipped = ( isset( $tracking_item['date_shipped'] ) ) ? $tracking_item['date_shipped'] : gmdate( 'Y-m-d' );
					
					$simple_layout_content_updated = '';
					?>
					
					<li class="tracking_list_li">
						<div class="tracking_list_div" style="font-size:<?php esc_html_e( $simple_provider_font_size ); ?>px;color:<?php esc_html_e( $simple_provider_font_color ); ?>;border-bottom:<?php esc_html_e( $show_provider_border ); ?>px solid <?php esc_html_e( $provider_border_color ); ?>">
							<?php 
							$formatted_tracking_provider = apply_filters( 'ast_provider_title', esc_html( $tracking_item[ 'formatted_tracking_provider' ] ) );
							$simple_layout_content_updated = str_replace( '{ship_date}', date_i18n( get_option( 'date_format' ), $date_shipped ), $simple_layout_content );
							$simple_layout_content_updated = str_replace( '{shipping_provider}', $formatted_tracking_provider, $simple_layout_content_updated );
							$tracking_number_link = '<a target="_blank" href="' . esc_url( $tracking_item['ast_tracking_link'] ) . '">' . $tracking_item['tracking_number'] . '</a>';
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
	<table class="td tracking_table" cellspacing="0" cellpadding="6" style="width: 100%;border-collapse: collapse;background:<?php esc_html_e( $email_table_backgroud_color ); ?>" border="1">
		<?php if ( $email_preview ) { ?>
			<thead class="<?php esc_html_e( ( $hide_table_header ) ? 'hide' : '' ); ?>">
				<tr>
					<?php if ( $show_provider_th ) { ?>
						<th class="tracking-provider"  colspan="<?php esc_html_e( $colspan ); ?>" scope="col" class="td" style="<?php esc_html_e( $th_column_style ); ?>">
							<?php esc_html_e( $provider_header_text, 'woo-advanced-shipment-tracking' ); ?>
						</th>
					<?php 
					} 
					
					do_action( 'ast_tracking_email_header', $order_id, $th_column_style );
					?>
					
					<th class="tracking-number" scope="col" class="td" style="<?php esc_html_e( $th_column_style ); ?>"><?php esc_html_e( $tracking_number_header_text, 'woo-advanced-shipment-tracking' ); ?></th>												
					<?php if ( $email_preview ) { ?>
						<th class="date-shipped <?php esc_html_e( ( 1 == $remove_date_from_tracking_info ) ? 'hide' : '' ); ?>" scope="col" class="td" style="<?php esc_html_e( $th_column_style ); ?>"><?php esc_html_e( $shipped_date_header_text, 'woo-advanced-shipment-tracking' ); ?></th>
					<?php 
					} else {
						if ( 1 != $remove_date_from_tracking_info ) {
							?>
							<th class="date-shipped" style="<?php esc_html_e( $th_column_style ); ?>"><span class="nobr"><?php esc_html_e( $shipped_date_header_text, 'woo-advanced-shipment-tracking' ); ?></span></th>
						<?php 
						}
					}
						
					if ( !$tracking_number_link ) {
						if ( $email_preview ) { 
							?>
							<th class="order-actions" scope="col" class="td" style="<?php esc_html_e( $th_column_style ); ?>"><span class="track_label <?php esc_html_e( ( 1 != $show_track_label ) ? 'hide' : '' ); ?>"><?php esc_html_e( $track_header_text, 'woo-advanced-shipment-tracking' ); ?></span></th>
						<?php } else { ?>
							<th class="order-actions" scope="col" class="td" style="<?php esc_html_e( $th_column_style ); ?>"><?php esc_html_e( ( 1 == $show_track_label ) ? __( $track_header_text, 'woo-advanced-shipment-tracking' ) : '' ); ?></th>
						<?php 
						} 
					} 
					?>
				</tr>
			</thead>
		<?php } else { ?>
			<thead style="<?php esc_html_e( ( $hide_table_header ) ? 'display:none' : '' ); ?>">
				<tr>
					<?php if ( $show_provider_th ) { ?>
						<th class="tracking-provider" colspan="<?php esc_html_e( $colspan ); ?>"  scope="col" class="td" style="<?php esc_html_e( $th_column_style ); ?>">
							<?php esc_html_e( $provider_header_text, 'woo-advanced-shipment-tracking' ); ?>
						</th>
					<?php 
					}
					
					do_action( 'ast_tracking_email_header', $order_id, $th_column_style); 
					?>
					
					<th class="tracking-number" scope="col" class="td" style="<?php esc_html_e( $th_column_style ); ?>"><?php esc_html_e( $tracking_number_header_text, 'woo-advanced-shipment-tracking' ); ?></th>				
					<?php if ( $email_preview ) { ?>
						<th class="date-shipped <?php esc_html_e( ( 1 != $remove_date_from_tracking_info ) ? 'hide' : '' ); ?>" scope="col" class="td" style="<?php esc_html_e( $th_column_style ); ?>"><?php esc_html_e( $shipped_date_header_text, 'woo-advanced-shipment-tracking' ); ?></th>
					<?php 
					} else {
						if ( 1 != $remove_date_from_tracking_info ) {
							?>
							<th class="date-shipped" style="<?php esc_html_e( $th_column_style ); ?>"><span class="nobr"><?php esc_html_e( $shipped_date_header_text, 'woo-advanced-shipment-tracking' ); ?></span></th>
						<?php 
						}
					}
						
					if ( !$tracking_number_link ) {
						if ( $email_preview ) { 
							?>
							<th class="order-actions" scope="col" class="td" style="<?php esc_html_e( $th_column_style ); ?>"><span class="track_label <?php esc_html_e( ( 1 != $show_track_label ) ? 'hide' : '' ); ?>"><?php esc_html_e( $track_header_text, 'woo-advanced-shipment-tracking' ); ?></span></th>
						<?php } else { ?>
							<th class="order-actions" scope="col" class="td" style="<?php esc_html_e( $th_column_style ); ?>"><?php esc_html_e( ( 1 == $show_track_label ) ? __( $track_header_text, 'woo-advanced-shipment-tracking' ) : '' ); ?></th>
						<?php 
						} 
					} 
					?>
				</tr>
			</thead>	
		<?php } ?>

		<tbody style="line-height:<?php esc_html_e( $table_content_line_height ); ?>px;">
		<?php
		foreach ( $tracking_items as $key => $tracking_item ) {
				
			$date_shipped = ( isset( $tracking_item['date_shipped'] ) ) ? $tracking_item['date_shipped'] : gmdate('Y-m-d'); 				
			?>
				<tr class="tracking" style="background-color:<?php esc_html_e( $email_table_backgroud_color ); ?>">
					
					<?php if ( 1 == $display_thumbnail ) { ?>
					<td class="tracking-provider" data-title="<?php esc_html_e( 'Provider', 'woo-advanced-shipment-tracking' ); ?>" style="<?php esc_html_e( $td_column_style ); ?>;width: 50px;">
						<img style="width: 50px;vertical-align: middle;" src="<?php echo esc_url( $tracking_item['tracking_provider_image'] ); ?>">
					</td>
					<?php } ?>
					
					<?php if ( 1 == $display_shipping_provider_name ) { ?>
					<td class="tracking-provider" data-title="<?php esc_html_e( 'Provider Name', 'woo-advanced-shipment-tracking' ); ?>" style="<?php esc_html_e( $td_column_style ); ?>">
						<?php 
						if ( '' != $tracking_item[ 'formatted_tracking_provider' ] ) {
							esc_html_e( apply_filters( 'ast_provider_title', esc_html( $tracking_item[ 'formatted_tracking_provider' ] ) ) );
						} else {
							esc_html_e( apply_filters( 'ast_provider_title', esc_html( $tracking_item[ 'tracking_provider' ] ) ) );
						}
						?>
					</td>
					<?php } ?>

					<?php do_action( 'ast_tracking_email_body', $order_id, $tracking_item, $td_column_style ); ?>

					<td class="tracking-number" data-title="<?php esc_html_e( 'Tracking Number', 'woo-advanced-shipment-tracking' ); ?>" style="<?php esc_html_e( $td_column_style ); ?>">
						<?php if ( $tracking_item['ast_tracking_link'] && $tracking_number_link ) { ?>	
							<a href="<?php echo esc_url( $tracking_item['ast_tracking_link'] ); ?>" style="text-decoration: none;" target="_blank"><?php echo esc_html( $tracking_item['tracking_number'] ); ?></a>
						<?php 
						} else {
							echo esc_html( $tracking_item['tracking_number'] );
						}
						?>
					</td>
					
					<?php if ( $email_preview ) { ?>
						<td class="date-shipped <?php esc_html_e( ( 1 == $remove_date_from_tracking_info ) ? 'hide' : '' ); ?>" data-title="<?php esc_html_e( 'Status', 'woocommerce' ); ?>" style="<?php esc_html_e(  $td_column_style ); ?>">
							<time datetime="<?php esc_html_e( gmdate( 'Y-m-d', $date_shipped ) ); ?>" title="<?php esc_html_e( gmdate( 'Y-m-d', $date_shipped ) ); ?>"><?php esc_html_e( date_i18n( get_option( 'date_format' ), $date_shipped ) ); ?></time>
						</td>						
					<?php 
					} else { 
						if ( 1 != $remove_date_from_tracking_info ) {
							?>
							<td class="date-shipped" style="<?php esc_html_e( $td_column_style ); ?>" data-title="<?php esc_html_e( 'Date', 'woocommerce' ); ?>" style="text-align:left; white-space:nowrap;">
								<time datetime="<?php esc_html_e( gmdate( 'Y-m-d', $date_shipped ) ); ?>" title="<?php esc_html_e( gmdate( 'Y-m-d', $date_shipped ) ); ?>"><?php esc_html_e( date_i18n( get_option( 'date_format' ), $date_shipped ) ); ?></time>
							</td>
						<?php 
						} 
					}
					
					if ( !$tracking_number_link ) {
						?>
						<td class="order-actions" style="<?php esc_html_e( $td_column_style ); ?>">
							<?php if ( $tracking_item['ast_tracking_link'] ) { ?>
								<a href="<?php echo esc_url( $tracking_item['ast_tracking_link'] ); ?>" style="<?php esc_html_e( $tracking_link_style ); ?>" target="_blank"><?php esc_html_e( 'Track', 'woo-advanced-shipment-tracking' ); ?></a>
							<?php } ?>
						</td>
					<?php } ?>
				</tr>
				<?php
		} 
		?>
		</tbody>
	</table><br/>
	<?php 
	}
	if ( !isset( $show_shipment_status ) ) {
		$show_shipment_status = false;
	}
	do_action( 'tracking_info_template' , $order_id, $tracking_items, $show_shipment_status ); 
	?>
	
	<style>
	ul.tracking_list{
		padding: 0;
		list-style: none;
	}
	ul.tracking_list .tracking_list_li{
		margin-bottom: 5px;
	}
	ul.tracking_list .tracking_list_li .product_list_ul{
		padding-left: 10px;
	}
	ul.tracking_list .tracking_list_li .tracking_list_div{
		border-bottom:1px solid #e0e0e0;
	} 
	</style>
<?php
endif;
