<?php 

if ( $tracking_items ) : 

$ast = new WC_Advanced_Shipment_Tracking_Actions();
$ast_customizer = Ast_Customizer::get_instance();

$hide_trackig_header = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'hide_trackig_header', '' );
$shipment_tracking_header = $ast->get_option_value_from_array( 'tracking_info_settings', 'header_text_change', 'Tracking Information' );
$shipment_tracking_header_text = $ast->get_option_value_from_array( 'tracking_info_settings', 'additional_header_text', '' );
$fluid_table_layout = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_table_layout', $ast_customizer->defaults['fluid_table_layout'] );
$border_color = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_table_border_color', $ast_customizer->defaults['fluid_table_border_color'] );
$border_radius = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_table_border_radius', $ast_customizer->defaults['fluid_table_border_radius'] );
$background_color = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_table_background_color', $ast_customizer->defaults['fluid_table_background_color'] );
$table_padding = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_table_padding', $ast_customizer->defaults['fluid_table_padding'] );
$button_background_color = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_button_background_color', $ast_customizer->defaults['fluid_button_background_color'] );
$button_font_color = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_button_font_color', $ast_customizer->defaults['fluid_button_font_color'] );
$button_radius = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_button_radius', $ast_customizer->defaults['fluid_button_radius'] );
$button_expand = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'fluid_button_expand', $ast_customizer->defaults['fluid_button_expand'] );
$fluid_button_text = $ast->get_option_value_from_array( 'tracking_info_settings', 'fluid_button_text', $ast_customizer->defaults['fluid_button_text'] );
$fluid_hide_provider_image = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'fluid_hide_provider_image', $ast_customizer->defaults['fluid_hide_provider_image'] );

$fluid_button_size = $ast->get_checkbox_option_value_from_array( 'tracking_info_settings', 'fluid_button_size', $ast_customizer->defaults['fluid_button_size'] );
$button_font_size = ( 'large' == $fluid_button_size ) ? 16 : 14 ;
$button_padding = ( 'large' == $fluid_button_size ) ? '12px 20px' : '10px 15px' ;

$order_data = wc_get_order( $order_id );

	if ( !empty( $order_data ) ) {
	$order_status = $order_data->get_status();
	} else {
	$order_status = 'completed';
	}

	if ( 1 != $hide_trackig_header ) {
		?>
	<h2><?php esc_html_e( apply_filters( 'woocommerce_shipment_tracking_my_orders_title', $shipment_tracking_header ) ); ?></h2>
<?php 
	}

	if ( '' != $hide_trackig_header) {
		?>
	<p><?php esc_html_e( $shipment_tracking_header_text ); ?></p>
<?php } ?>

<div class="fluid_section">
	<?php 
	foreach ( $tracking_items as $key => $tracking_item ) { 
	
		if ( '' != $tracking_item[ 'formatted_tracking_provider' ] ) {
			$ast_provider_title = apply_filters( 'ast_provider_title', esc_html( $tracking_item[ 'formatted_tracking_provider' ] )); 
		} else {
			$ast_provider_title = apply_filters( 'ast_provider_title', esc_html( $tracking_item[ 'tracking_provider' ] ));
		}
		?>
		<div class="fluid_container">
			<div class="fluid_cl fluid_left_cl">
				<?php if ( !$fluid_hide_provider_image ) { ?>
						<div class="fluid_provider_img">
							<img src="<?php echo esc_url( $tracking_item['tracking_provider_image'] ); ?>"></img>
						</div>
					<?php } ?>
					<div class="provider_name">
						<div>
							<strong class="tracking_provider"><?php esc_html_e( $ast_provider_title ); ?></strong>
							<a class="tracking_number" href="<?php echo esc_url( $tracking_item['ast_tracking_link'] ); ?>" target="_blank"><?php esc_html_e( $tracking_item['tracking_number'] ); ?></a>
						</div>
						<div class="order_status <?php esc_html_e( $order_status ); ?>">
						<?php 
							esc_html_e( 'Shipped on:', 'woo-advanced-shipment-tracking' ); 
							echo '<strong> ' . esc_html( date_i18n( get_option( 'date_format' ), $tracking_item['date_shipped'] ) ) . '</strong>'; 
						?>
						</div>	
					</div>									
					
				<?php do_action( 'ast_fluid_left_cl_end', $tracking_item, $order_id ); ?>	
			</div>
			<div class="fluid_cl fluid_right_cl">
				<div>
					<a target="blank" href="<?php echo esc_url( $tracking_item['ast_tracking_link'] ); ?>" class="button track-button" data-order="<?php esc_html_e( $order_id ); ?>" data-tracking="<?php echo esc_html( $tracking_item['tracking_number'] ); ?>" target="_blank"><?php esc_html_e( $fluid_button_text ); ?></a>
				</div>
			</div>
		</div>
	<?php } ?>
</div>

<style>
.fluid_section{
	margin-bottom: 10px;
}
.fluid_container {
	background: <?php esc_html_e( $background_color ); ?>;
	border: 1px solid <?php esc_html_e( $border_color ); ?>;
	border-radius: <?php esc_html_e( $border_radius ); ?>px;
	margin-bottom: 10px;
	display: inline-block;
	width: 49%;
	vertical-align: top;
}
.fluid_container:after{
	content: "";
	clear: both;
	display: block;
}
.fluid_cl {	
	padding: <?php esc_html_e( $table_padding ); ?>px;
	vertical-align: middle;
}
.fluid_right_cl{
	padding-top: 0;
}
.fluid_cl ul li{
	margin-left: 0;
	margin-bottom: 0;
	font-size: 14px;
}
.fluid_provider_img{
	margin-right: 10px;
	width: 40px;
	display: inline-block;
	vertical-align: text-bottom;
}
.fluid_provider_img img{
	border-radius: 5px;
	width: 100%;
}
.provider_name {
	display: inline-block;
	vertical-align: middle;
	width: calc(100% - 100px);
}
<?php if ( 2 == $fluid_table_layout ) { ?>
	.fluid_container{
		width: 100%;		
		display: table;		
	}
	.fluid_cl{
		width: 70%;
		display: table-cell;
	}	
	.fluid_right_cl{
		padding-top: <?php esc_html_e( $table_padding ); ?>px;
		text-align: right;
		vertical-align: top;
	}
<?php } ?>
a.button.track-button {
	background: <?php esc_html_e( $button_background_color ); ?>;
	color: <?php esc_html_e( $button_font_color ); ?>;
	padding: <?php esc_html_e( $button_padding ); ?>;
	text-decoration: none;
	display: inline-block;
	border-radius: <?php esc_html_e( $button_radius ); ?>px;
	margin-top: 0;
	font-size: <?php esc_html_e( $button_font_size ); ?>px;
	text-align: center;
	margin-bottom: 0;   
	margin-right: 0; 
	line-height: 20px;
	text-transform: none;
}
<?php if ( $button_expand && 1 == $fluid_table_layout ) { ?>
a.button.track-button {
	display: block;
}
<?php } ?>
.tracking_provider{
	word-break: break-word;
	display: block;
	margin-right: 5px;
}
<?php
	if ( $fluid_hide_provider_image && 2 == $fluid_table_layout && !$fluid_hide_shipping_date ) { 
		?>
	.tracking_provider,.tracking_number{
		display: inline-block;
	}
<?php } ?>
.tracking_number{
	color: #03a9f4;
	text-decoration: none;
}
.order_status{
	font-size: 13px;    
	margin: 0;
}
</style>
<?php
endif;
