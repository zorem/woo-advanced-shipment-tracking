<?php
/**
 * html code for settings tab
 */
?>
<section id="content2" class="tab_section">
	<form method="post" id="wc_ast_settings_form" action="" enctype="multipart/form-data">
	
		<h1 class="tab_page_heading"><?php _e( 'Settings', 'woo-advanced-shipment-tracking'); ?></h1>
		
		<h2 class="tab_section_heading botton_border"><?php _e( 'Add Tracking Options', 'woo-advanced-shipment-tracking'); ?></h2>
		<?php $this->get_html_ul( $this->get_add_tracking_options() );?>
		
		<div class="tabs_inner_section">
			<h1 class="tab_section_heading botton_border"><?php _e( 'Customer View', 'woo-advanced-shipment-tracking'); ?></h1>
			<?php $this->get_html_ul( $this->get_customer_view_options() );?>
		</div>
		
		<div class="tabs_inner_section">
			<h2 class="tab_section_heading botton_border"><?php _e( 'Shipment Tracking API', 'woo-advanced-shipment-tracking'); ?></h2>
			<?php $this->get_html_ul( $this->get_shipment_tracking_api_options() );?>
		</div>
		
		<div class="tabs_inner_section">
			<h2 class="tab_section_heading botton_border"><?php _e( 'Custom Order Statuses', 'woo-advanced-shipment-tracking'); ?></h2>
			<?php require_once( 'admin_options_osm.php' ); ?>
		</div>						
		
		<?php wp_nonce_field( 'wc_ast_settings_form', 'wc_ast_settings_form_nonce' );?>
		<input type="hidden" name="action" value="wc_ast_settings_form_update">							
	</form>	
</section>