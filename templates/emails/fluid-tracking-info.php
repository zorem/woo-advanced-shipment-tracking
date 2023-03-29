<?php
/**
 * Tracking Widget template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/fluid-tracking-info.php.
 *
 */

if ( $tracking_items ) : 

$ast = new WC_Advanced_Shipment_Tracking_Actions();
$ast_customizer = Ast_Customizer::get_instance();

//Widget header option
$hide_trackig_header = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'hide_trackig_header', '' );
$shipment_tracking_header = $ast->get_option_value_from_array( 'tracking_info_settings', 'header_text_change', 'Tracking Information' );
$shipment_tracking_header_text = $ast->get_option_value_from_array( 'tracking_info_settings', 'additional_header_text', '' );

// Tracking widget background/border color and radius option
$border_color = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_table_border_color', $ast_customizer->defaults['fluid_table_border_color'] );
$border_radius = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_table_border_radius', $ast_customizer->defaults['fluid_table_border_radius'] );
$background_color = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_table_background_color', $ast_customizer->defaults['fluid_table_background_color'] );

//Hide Shipped/Tracker type header option
$fluid_display_shipped_header = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'fluid_display_shipped_header', $ast_customizer->defaults['fluid_display_shipped_header'] );

//Hide shipping provider image
$fluid_hide_provider_image = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'fluid_hide_provider_image', $ast_customizer->defaults['fluid_hide_provider_image'] );

	if ( $fluid_hide_provider_image ) {
		$colspan = '2';
	} else {
		$colspan = '3';
	}
$fluid_provider_img_class = ( $fluid_hide_provider_image ) ? 'hide' : '' ;

// Button option
$button_background_color = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_button_background_color', $ast_customizer->defaults['fluid_button_background_color'] );
$button_font_color = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_button_font_color', $ast_customizer->defaults['fluid_button_font_color'] );
$button_radius = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_button_radius', $ast_customizer->defaults['fluid_button_radius'] );
$fluid_button_text = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_button_text', $ast_customizer->defaults['fluid_button_text'] );
$fluid_button_size = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'fluid_button_size', $ast_customizer->defaults['fluid_button_size'] );
$fluid_tracker_type = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_tracker_type', $ast_customizer->defaults['fluid_tracker_type'] );
$button_font_size = ( 'large' == $fluid_button_size ) ? 16 : 14 ;
$button_padding = ( 'large' == $fluid_button_size ) ? '12px 25px' : '10px 15px' ;

$order_details = wc_get_order( $order_id );

$ast_preview = ( isset( $_REQUEST['action'] ) && 'ast_email_preview' === $_REQUEST['action'] ) ? true : false;
$text_align = is_rtl() ? 'right' : 'left'; 

	if ( !empty( $order_details ) ) {
		$order_status = $order_details->get_status();
	} else {
		$order_status = 'completed';
	}

	if ( $ast_preview ) {
		$hide_header_class = ( $hide_trackig_header ) ? 'hide' : '' ;
		?>
		<h2 class="header_text <?php esc_html_e( $hide_header_class ); ?>" style="margin: 0;text-align:<?php esc_html_e( $text_align ); ?>;">
			<?php esc_html_e( apply_filters( 'woocommerce_shipment_tracking_my_orders_title', $shipment_tracking_header ) ); ?>
		</h2>
		<?php 
	} else { 
		$hide_header = ( $hide_trackig_header ) ? 'display:none' : '' ;
		?>
		<h2 class="header_text" style="margin: 0;text-align:<?php esc_html_e( $text_align ); ?>;<?php esc_html_e( $hide_header ); ?>">
			<?php esc_html_e( apply_filters( 'woocommerce_shipment_tracking_my_orders_title', $shipment_tracking_header ) ); ?>
		</h2>
	<?php } ?>
	
<p style="margin: 0;" class="addition_header"><?php echo wp_kses_post( $shipment_tracking_header_text ); ?></p>

<?php 
	foreach ( $tracking_items as $key => $tracking_item ) { 	

		if ( '' != $tracking_item[ 'formatted_tracking_provider' ] ) {
			$ast_provider_title = apply_filters( 'ast_provider_title', esc_html( $tracking_item[ 'formatted_tracking_provider' ] )); 
		} else {
			$ast_provider_title = apply_filters( 'ast_provider_title', esc_html( $tracking_item[ 'tracking_provider' ] ));
		} 
		?>
	<table class="fluid_table fluid_table_2cl">
		<tbody class="fluid_tbody_2cl">
			
			<?php 
			if ( $ast_preview ) { 
				$fluid_display_shipped_header = ( 0 == $fluid_display_shipped_header ) ? 'hide' : '' ;
				?>
				<tr class="fluid_header_tr <?php esc_html_e( $fluid_display_shipped_header ); ?>">
					<td style="padding-bottom:0 !important;" colspan="<?php esc_html_e( $colspan ); ?>">
						<h2 class="shipped_label"><?php esc_html_e( 'Shipped', 'woo-advanced-shipment-tracking' ); ?></h2>
					</td>
				</tr>
				<tr class="fluid_header_tr <?php esc_html_e( $fluid_display_shipped_header ); ?>">
					<td style="padding-top:0 !important;" colspan="<?php esc_html_e( $colspan ); ?>">
						<?php
							echo '<span class="shipped_on">';
							esc_html_e( 'Shipped on', 'woo-advanced-shipment-tracking' );
							echo ': <b>';
							echo esc_html( date_i18n( get_option( 'date_format' ), $tracking_item['date_shipped'] ) );
							echo '</b>';
							echo '</span>';
						?>
					</td>
				</tr>
				<tr class="fluid_header_tr tracker_tr <?php esc_html_e( $fluid_display_shipped_header ); ?>">
					<td class="fluid_2cl_td_image" style="padding-top:5px !important;" colspan="<?php esc_html_e( $colspan ); ?>">
						<img class="tracker_image" style="width:100%;" src="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ); ?>assets/images/<?php esc_html_e( $fluid_tracker_type ); ?>.png"></img>
					</td>	
				</tr>			
			<?php } else if ( $fluid_display_shipped_header ) { ?>
				<tr class="<?php esc_html_e( $fluid_display_shipped_header ); ?>">
					<td style="padding-bottom:0 !important;" colspan="<?php esc_html_e( $colspan ); ?>">
						<h2 class="shipped_label"><?php esc_html_e( 'Shipped', 'woo-advanced-shipment-tracking' ); ?></h2>
					</td>
				</tr>
				<tr class="<?php esc_html_e( $fluid_display_shipped_header ); ?>">
					<td style="padding-top:0 !important;" colspan="<?php esc_html_e( $colspan ); ?>">
						<?php
							echo '<span class="shipped_on">';
							esc_html_e( 'Shipped on', 'woo-advanced-shipment-tracking' );
							echo ': <b>';
							echo esc_html( date_i18n( get_option( 'date_format' ), $tracking_item['date_shipped'] ) );
							echo '</b>';
							echo '</span>';
						?>
					</td>
				</tr>
				<tr class="tracker_tr <?php esc_html_e( $fluid_display_shipped_header ); ?>">
					<td class="" style="padding-top:5px !important;" colspan="<?php esc_html_e( $colspan ); ?>">
						<img class="tracker_image" style="width:100%;" src="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ); ?>assets/images/<?php esc_html_e( $fluid_tracker_type ); ?>.png"></img>
					</td>	
				</tr>
			<?php } ?>
			
			
			<tr class="fluid_2cl_tr">
				<?php if ( $ast_preview ) { ?>
					<td class="fluid_provider_img <?php esc_html_e( $fluid_provider_img_class ); ?>" style="padding-right:0 !important;">
						<img src="<?php echo esc_url( $tracking_item['tracking_provider_image'] ); ?>"></img>
					</td>
				<?php } else if ( !$fluid_hide_provider_image ) { ?>
					<td class="fluid_provider_img" style="padding-right:0 !important;">
						<img src="<?php echo esc_url( $tracking_item['tracking_provider_image'] ); ?>"></img>
					</td>	
				<?php } ?>				
				<td class="fluid_2cl_td_provider">
					<span class="tracking_provider"><?php esc_html_e( $ast_provider_title ); ?></span></br>
					<a class="tracking_number" href="<?php echo esc_url( $tracking_item['ast_tracking_link'] ); ?>" target="_blank"><?php esc_html_e( $tracking_item['tracking_number'] ); ?></a>	
				</td>
				<td class="fluid_2cl_td_button" style="text-align: right;">
					<a href="<?php echo esc_url( $tracking_item['ast_tracking_link'] ); ?>" class="track-button" target="_blank"><?php esc_html_e( $fluid_button_text ); ?></a>	
				</td>
			</tr>
		</tbody>
	</table>
<?php } ?>

<div class="clearfix"></div>

<style>
.clearfix{
display: block;
content: '';
clear: both;
}
.fluid_container{
width: 100%;
display: block;
}
.fluid_table_2cl{
width: 100%;	
margin: 10px 0 !important;
border: 1px solid <?php esc_html_e( $border_color ); ?> !important;
border-radius: <?php esc_html_e( $border_radius ); ?>px !important;    
background: <?php esc_html_e( $background_color ); ?> !important;	
border-spacing: 0 !important;	
}
.tracker_tr td{	
border-bottom: 1px solid <?php esc_html_e( $border_color ); ?>;
}
.fluid_table_2cl .fluid_2cl_tr td.fluid_2cl_td_action{	
text-align: right;
vertical-align: middle !important;
}

.fluid_table td{
padding: 15px !important;
}

.fluid_provider_img {    
display: inline-block;
vertical-align: middle;
}
.fluid_provider_img img{
width: 40px;
border-radius: 5px;
margin-right: 10px !important;
}
.provider_name{
display: inline-block;
vertical-align: middle;
}
.tracking_provider{
word-break: break-word;
margin-right: 5px;	
font-size: 14px;
display: block;
}
.tracking_number{
color: #03a9f4;
text-decoration: none;    
font-size: 14px;
line-height: 19px;
display: block;
margin-top: 4px;
}
.order_status{
font-size: 12px;    
margin: 0;	
}
.shipped_label{
font-size: 24px !important;
margin: 0 0 10px !important;	
display: inline-block;
color: #333;
vertical-align: middle;
font-weight:500;
line-height: 100%;
}
span.shipped_on{
margin-top: 5px;
display: inline-block;
font-size: 14px;
}
.order_status span{
vertical-align: middle;
}
a.track-button {
background: <?php esc_html_e( $button_background_color ); ?>;
color: <?php esc_html_e( $button_font_color ); ?> !important;
padding: <?php esc_html_e( $button_padding ); ?>;
text-decoration: none;
display: inline-block;
border-radius: <?php esc_html_e( $button_radius ); ?>px;
margin-top: 2px;
font-size: <?php esc_html_e( $button_font_size ); ?>px !important;
text-align: center;
min-height: 10px;
white-space: nowrap;
}
.track-button-div{
float: right;
}

@media screen and (max-width: 720px) {
	.fluid_2cl_tr{
		display: block;
	}
	.fluid_2cl_td_provider{
		display: inline-block;
		padding-right: 0 !important;
	}
	.fluid_2cl_td_button{
		display: block;
	}
}	
@media screen and (max-width: 460px) {
	.track-button-div{
		float: none !important;
		margin-top: 15px !important;
	}
	.track-button{
		display: block !important;
	}
}

</style>

<?php
endif;
