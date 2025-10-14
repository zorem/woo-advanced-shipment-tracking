<?php

$more_plugins = array(
	0 => array(
		'title' => 'TrackShip for WooCommerce',
		'description' => 'Take control of your post-shipping workflows, reduce time spent on customer service and provide a superior post-purchase experience to your customers.Beyond automatic shipment tracking, TrackShip brings a branded tracking experience into your store, integrates into your workflow, and takes care of all the touch points with your customers after shipping.',
		'url' => 'https://wordpress.org/plugins/trackship-for-woocommerce/#utm_source=wp-admin&utm_medium=ast-addons&utm_campaign=add-ons',
		'image' => 'trackship.png',
		'width' => '90px',
		'file' => 'trackship-for-woocommerce/trackship-for-woocommerce.php'
	),
	1 => array(
		'title' => 'SMS for WooCommerce',
		'description' => 'Keep your customers informed by sending them automated SMS text messages with order & delivery updates. You can send SMS notifications to customers when the order status is updated or when the shipment is out for delivery and more…',
		'url' => 'https://www.zorem.com/products/sms-for-woocommerce/#utm_source=wp-admin&utm_medium=ast-addons&utm_campaign=add-ons',
		'image' => 'smswoo.png',
		'width' => '90px',
		'file' => 'sms-for-woocommerce/sms-for-woocommerce.php'
	),
	2 => array(
		'title' => 'Zorem Local Pickup Pro',
		'description' => 'The Advanced Local Pickup (ALP) helps you manage the local pickup orders workflow more conveniently by extending the WooCommerce Local Pickup shipping method. The Pro you set up multiple pickup locations, , split the business hours, apply discounts by pickup location, display local pickup message on the products pages, allow customers to choose pickup location per product, force products to be local pickup only and more…',
		'url' => 'https://www.zorem.com/product/zorem-local-pickup-pro/#utm_source=wp-admin&utm_medium=ast-addons&utm_campaign=add-ons',
		'image' => 'alp.png',
		'width' => '60px',
		'file' => 'advanced-local-pickup-pro/advanced-local-pickup-pro.php'
	),
	3 => array(
		'title' => 'Country Based Restriction for WooCommerce',
		'description' => 'The country-based restrictions plugin by zorem works by the WooCommerce Geolocation or the shipping country added by the customer and allows you to restrict products on your store to sell or not to sell to specific countries.',
		'url' => 'https://www.zorem.com/product/country-based-restriction-pro/#utm_source=wp-admin&utm_medium=ast-addons&utm_campaign=add-ons',
		'image' => 'cbr.png',
		'width' => '70px',
		'file' => 'country-based-restriction-pro-addon/country-based-restriction-pro-addon.php'
	),
	4 => array(
		'title' => 'Customer Email Verification',
		'description' => 'The Customer Email Verification helps WooCommerce store owners to reduce registration and spam orders by requiring customers to verify their email address when they register an account or before they can place an order on your store.',
		'url' => 'https://www.zorem.com/product/customer-email-verification/?utm_source=wp-admin&utm_medium=ast-addons&utm_campaign=more-info',
		'image' => 'cev.png',
		'width' => '70px',
		'file' => 'country-based-restriction-pro-addon/country-based-restriction-pro-addon.php'
	),	
	5 => array(
		'title' => 'Email Reports for WooCommerce',
		'description' => 'The Sales Report Email Pro will help know how well your store is performing and how your products are selling by sending you a daily, weekly, or monthly sales report by email, directly from your WooCommerce store.',
		'url' => 'https://www.zorem.com/product/email-reports-for-woocommerce/#utm_source=wp-admin&utm_medium=ast-addons&utm_campaign=add-ons',
		'image' => 'sre.png',
		'width' => '60px',
		'file' => 'sales-report-email-pro/sales-report-email-pro.php'
	),
);

?>
<section id="content6" class="tab_section">
	<div class="tab_container_without_bg">
		<?php do_action('ast_addon_license_form'); ?>	
		
		<div class="section-content trackship_addon_section ast_pro_addon_section">
			<h1 class="ast_pro_landing_header">Upgrade to AST PRO</h1>
			<div class="ast_features_container">
				<div class="ast_features_inner">
					<ul class="ast_pro_features_list">
						<li>Add tracking per item</li>
						<li>Auto-detect shipping providers</li>
						<li>Fulfillment dashboard</li>
						<li>Responsive Tracking info widget</li>
						<li>Custom Order Status "Shipped"</li>			
						<li>AutoComplete API orders</li>				       
					</ul>
				</div>
				<div class="ast_features_inner">
					<ul class="ast_pro_features_list">
						<li>25+ Built-in <a href="https://docs.zorem.com/docs/ast-pro/integrations/" target="_blank">tracking Integrations</a> with shipping services</li>
						<li>Export tracking information to PayPal</li>							
						<li>Add Custom Shipping Providers</li>
						<li>White Label Shipping Providers</li>
						<li>Shipping Provider API Name Mapping</li>
						<li>Premium Support</li>				        
					</ul>
				</div>
			</div>
			<a href="https://www.zorem.com/ast-pro/?utm_source=wp-admin&utm_medium=ast-addons&utm_campaign=more-info" class="button-primary btn_ast2 btn_large upgrade_to_pro_btn" target="_blank">UPGRADE TO AST PRO <span class="dashicons dashicons-arrow-right-alt2"></span></a>	
		</div>

		<form method="post" id="wc_usage_tracking_form" action="" enctype="multipart/form-data">
			<div class="usage-tracking-accordion heading add-tracking-option">
				<label>
					<?php esc_html_e( 'Usage Tracking', 'ast-pro' ); ?>
					<span class="ast-accordion-btn">
						<div class="spinner workflow_spinner" style="float:none"></div>
						<button name="save" class="button-primary usage-tracking-save btn_ast2" type="submit" value="Save changes"><?php esc_html_e( 'Save & Close', 'ast-pro' ); ?></button>
					</span>
				</label>
			</div>
			<div class="usage-tracking-panel options usage-data-option">
				<?php wc_advanced_shipment_tracking()->admin->get_html_ul( wc_advanced_shipment_tracking()->admin->get_usage_tracking_options() ); ?>
			</div>
			<?php wp_nonce_field( 'wc_usage_tracking_form', 'wc_usage_tracking_form_nonce' ); ?>
			<input type="hidden" name="action" value="wc_usage_tracking_form_update">	
		</form>
	
		<div class="plugins_section zorem_plugin_section">				
			<div class="zorem_plugin_container">
				<?php foreach ( $more_plugins as $mplugin ) { ?>
					<div class="zorem_single_plugin">
						<div class="free_plugin_inner">
							<div class="plugin_image">
								<img src="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ); ?>assets/images/<?php esc_html_e( $mplugin['image'] ); ?>?<?php echo esc_html_e( time() ); ?>">
								<h3 class="plugin_title"><?php esc_html_e( $mplugin['title'] ); ?></h3>
							</div>
							<div class="plugin_description">
								<p><?php esc_html_e( $mplugin['description'] ); ?></p>
								<?php 
								if ( is_plugin_active( $mplugin['file'] ) ) {
									?>
								<button type="button" class="button button button-primary btn_green2">Active</button>
							<?php } else { ?>
								<a href="<?php esc_html_e( $mplugin['url'] ); ?>" class="button button-primary btn_ast2" target="blank">Buy Now</a>
							<?php } ?>								
							</div>
						</div>	
					</div>	
				<?php } ?>						
			</div>
		</div>
	</div>
</section>
