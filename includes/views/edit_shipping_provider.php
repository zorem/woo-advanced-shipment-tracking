<div class="slidout_header">	
	<?php
	
	$upload_dir   = wp_upload_dir();	
	$ast_directory = $upload_dir['baseurl'] . '/ast-shipping-providers/';	

	?>
	<div class="slidout_header_title">
		<h3 class="slidout_title"><?php esc_html_e( 'Edit Shipping Carrier', 'woo-advanced-shipment-tracking'); ?></h3>
		<div class="grid-top">        
			<div class="grid-provider-img">
				<?php
						
				if ( 1 == $shippment_provider->shipping_default ) {
					$provider_image = $ast_directory . '' . esc_html( $shippment_provider->ts_slug ) . '.png?v=' . wc_advanced_shipment_tracking()->version;
					echo '<img class="provider-thumb" src="' . esc_url( $provider_image ) . '">';
				} else { 
					echo '<img class="provider-thumb" src="' . esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ) . 'assets/images/icon-default.png">';					
				}
				?>
			</div>
			<div class="grid-provider-name">
				<span class="provider_name"><?php echo esc_html( $shippment_provider->provider_name ); ?></span>																		
				<span class="provider_country"><?php echo esc_html( $shippment_provider->shipping_country_name ); ?></span>
			</div>
		</div>        
	</div>
	<div class="slidout_header_action">
		<span class="dashicons dashicons-no-alt edit_slidout_close slidout_close"></span>
	</div>
</div>

<div class="slidout_body">
	<div class="menu_devider"></div>
	<div class="get_feature_container">
		<a href="https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/" target="_blank"><span class="get_feature_span"><span class="dashicons dashicons-arrow-up-alt"></span>Get Feature</span></a>
		<div>Upgrade to Shipment Tracking Pro and gain the power to Add Custom Shipping Carriers. Ditch limitations, offer more shipping choices, enhance customer satisfaction, and streamline operations. Elevate your e-commerce game today!</div>
	</div>	
</div>
