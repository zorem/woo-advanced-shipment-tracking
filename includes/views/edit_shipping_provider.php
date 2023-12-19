<div class="slidout_header">	
	<?php
	
	$WC_Countries = new WC_Countries();
	$countries = $WC_Countries->get_countries();
	$upload_dir   = wp_upload_dir();	
	$ast_directory = $upload_dir['baseurl'] . '/ast-shipping-providers/';
	
	if ( 0 != $shippment_provider->custom_thumb_id ) {
		$image = wp_get_attachment_url( $shippment_provider->custom_thumb_id );	
	} else {
		$image = null;
	}
	
	if ( isset( $shippment_provider->custom_tracking_url ) && '' != $shippment_provider->custom_tracking_url ) {
		$tracking_url = $shippment_provider->custom_tracking_url;	
	} else {
		$tracking_url = $shippment_provider->provider_url;
	}

	$default_provider = 0;	
	if ( get_option( 'wc_ast_default_provider', '' ) == $id ) {
		$default_provider = 1;	
	}
	$checked = ( 1 == $default_provider ) ? 'checked' : '';

	if ( 1 == $shippment_provider->shipping_default ) {
		$provider_type = 'default_provider';
	} else {
		$provider_type = 'custom_provider';
	}
	
	if ( '' != $shippment_provider->api_provider_name ) {
		$api_provider_array = json_decode( $shippment_provider->api_provider_name );
	} else {
		$api_provider_array = array();
	}

	?>
	<div class="slidout_header_title">
		<h3 class="slidout_title"><?php esc_html_e( 'Edit Shipping Carrier', 'woo-advanced-shipment-tracking'); ?></h3>
		<div class="grid-top">        
			<div class="grid-provider-img">
				<?php
				$custom_thumb_id = $shippment_provider->custom_thumb_id;				
				if ( 1 == $shippment_provider->shipping_default ) {
					if ( 0 != $custom_thumb_id ) {
						$image_attributes = wp_get_attachment_image_src( $custom_thumb_id , array( '60', '60' ) );
						$provider_image = $image_attributes[0];
					} else {
						$provider_image = $ast_directory . '' . sanitize_title( $shippment_provider->provider_name ) . '.png?v=' . wc_advanced_shipment_tracking()->version;
					}
					echo '<img class="provider-thumb" src="' . esc_url( $provider_image ) . '">';
				} else { 
					$image_attributes = wp_get_attachment_image_src( $custom_thumb_id , array( '60', '60' ) );
				
					if ( 0 != $custom_thumb_id ) { 
						echo '<img class="provider-thumb" src="' . esc_url( $image_attributes[0] ) . '">';
					} else { 
						echo '<img class="provider-thumb" src="' . esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ) . 'assets/images/icon-default.png">';
					}  
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

	<?php
	/* <div class="form-field set_default_container">
		<label><input type="checkbox" name="make_provider_default" class="make_provider_default" <?php echo esc_html( $checked ); ?> value="1" disabled>&nbsp;&nbsp;<span><?php esc_html_e( 'Set default', 'woo-advanced-shipment-tracking' ); ?></span></label>							
	</div>
	
	<?php if ( 1 != $shippment_provider->shipping_default ) { ?>
		<div class="form-field">
			<label><?php esc_html_e( 'Carrier Name', 'woo-advanced-shipment-tracking' ); ?></label>
			<input type="text" name="shipping_provider" class="shipping_provider" value="<?php echo esc_html( $shippment_provider->provider_name ); ?>" placeholder="<?php esc_html_e( 'Shipping Carrier', 'woo-advanced-shipment-tracking' ); ?>" disabled>
		</div>
	<?php } ?>													
	
	<div class="form-field margin-0">
		<label><?php esc_html_e( 'Custom display name', 'woo-advanced-shipment-tracking' ); ?> 
			<span class="woocommerce-help-tip tipTip" data-tip="<?php esc_html_e( "The custom display name will show in the tracking info section on the customer order emails, my-account, and TrackShip's tracking page and email notifications", 'woo-advanced-shipment-tracking' ); ?>"></span>
		</label>
		<input type="text" name="shipping_display_name" class="shipping_display_name" value="<?php echo esc_html( $shippment_provider->custom_provider_name ); ?>" placeholder="<?php esc_html_e( 'White Label Carrier Name', 'woo-advanced-shipment-tracking' ); ?>" disabled>
	</div>						
	
	<div class="form-field api_provider_name_container">
		<label><?php esc_html_e( 'Custom API name', 'woo-advanced-shipment-tracking' ); ?> 
			<span class="woocommerce-help-tip tipTip" data-tip="<?php esc_html_e( 'Add API name aliases to map Shipping carriers names with the carrier names that are updated in the shipment tracking API by external shipping services', 'woo-advanced-shipment-tracking' ); ?>"></span>
		</label>
		<div class="api_provider_div">									
			<input type="text" name="api_provider_name[]" class="api_provider_name" value="" placeholder="<?php esc_html_e( 'API Name', 'woo-advanced-shipment-tracking' ); ?>" disabled>				
			<span class="dashicons dashicons-insert"></span>								
		</div>
	</div>
	
	<div class="form-field margin-0">
		<label><?php esc_html_e( 'Logo image', 'woo-advanced-shipment-tracking' ); ?></label>
		<input type='text' placeholder='Image' name='thumb_url' class='image_path thumb_url' value="<?php echo esc_html( $image ); ?>" disabled>
		<input type='hidden' name='thumb_id' class='image_id thumb_id' placeholder="Image" value="<?php echo esc_html( $shippment_provider->custom_thumb_id ); ?>">
		<input type="button" class="button upload_image_button" value="<?php esc_html_e( 'Upload' , 'woo-advanced-shipment-tracking' ); ?>" disabled />
	</div>
	<div class="form-field">
		<label><?php esc_html_e( 'Tracking URL', 'woo-advanced-shipment-tracking' ); ?></label>
		<input type="text" name="tracking_url" class="tracking_url" value="<?php echo esc_html( $tracking_url ); ?>" placeholder="<?php esc_html_e( 'Tracking URL', 'woo-advanced-shipment-tracking' ); ?>" disabled>
	</div>	*/
	?>
</div>
