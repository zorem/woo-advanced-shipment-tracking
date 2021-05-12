<?php
/**
 * html code for tools tab
 */
$more_plugins = array(
	0 => array(
		'title' => 'Country Based Restrictions Pro',
		'description' => 'The country-based restrictions plugin by zorem works by the WooCommerce Geolocation or the shipping country added by the customer and allows you to restrict products on your store to sell or not to sell to specific countries.',
		'image' => 'cbr-icon.png',
		'url' => 'https://www.zorem.com/product/country-based-restriction-pro/',
		'file' => 'country-base-restrictions-pro-addon/country-base-restrictions-pro-addon.php',
		'price' => 79,
	),
	1 => array(
		'title' => 'Advanced Local Pickup Pro',
		'description' => 'The Advanced Local Pickup (ALP) helps you manage the local pickup orders workflow more conveniently by extending the WooCommerce Local Pickup shipping method. The Pro you set up multiple pickup locations, , split the business hours, apply discounts by pickup location, display local pickup message on the products pages, allow customers to choose pickup location per product, force products to be local pickup only and more…',
		'image' => 'alp-icon.png',
		'url' => 'https://www.zorem.com/product/advanced-local-pickup-for-woocommerce/',
		'file' => 'advanced-local-pickup-pro/advanced-local-pickup-pro.php',
		'price' => 79,
	),
	2 => array(
		'title' => 'SMS for WooCommerce',
		'description' => 'Keep your customers informed by sending them automated SMS text messages with order & delivery updates. You can send SMS notifications to customers when the order status is updated or when the shipment is out for delivery and more…',
		'image' => 'smswoo-icon.png',
		'url' => 'https://www.zorem.com/product/sms-for-woocommerce/',
		'file' => 'sms-for-woocommerce/sms-for-woocommerce.php',
		'price' => 79,
	),
	3 => array(
		'title' => 'Customer Email Verification Pro',
		'description' => 'The Customer Email Verification helps WooCommerce store owners to reduce registration and spam orders by requiring customers to verify their email address when they register an account or before they can place an order on your store.',
		'image' => 'cev-icon.png',
		'url' => 'https://www.zorem.com/product/customer-verification-for-woocommerce/',
		'file' => 'customer-email-verification-pro/customer-email-verification-pro.php',
		'price' => 79,
	),	
	4 => array(
		'title' => 'Advanced Order Status Manager',
		'description' => 'The Advanced Order Status Manager allows store owners to manage the WooCommerce orders statuses, create, edit, and delete custom Custom Order Statuses and integrate them into the WooCommerce orders flow.',
		'image' => 'AOSM-addons-icon.jpg',
		'url' => 'https://www.zorem.com/product/advanced-order-status-manager/',
		'file' => 'advanced-order-status-manager/advanced-order-status-manager.php',
		'price' => 49,
	),
	5 => array(
		'title' => 'Sales Report Email Pro',
		'description' => 'The Sales Report Email Pro will help know how well your store is performing and how your products are selling by sending you a daily, weekly, or monthly sales report by email, directly from your WooCommerce store.',
		'image' => 'sre-icon.png',
		'url' => 'https://www.zorem.com/product/sales-report-email-for-woocommerce/',
		'file' => 'sales-report-email-pro-addon/sales-report-email-pro-addon.php',
		'price' => 59,
	),	
); 

$ast_paid_addons = array(
	0 => array(
		'title' => '',
		'description' => '',
		'url' => 'https://www.zorem.com/product/tracking-per-item-ast-add-on/',
		'image' => 'trackship-logo.png',
		'file' => '',
		'price' => '',
	),	
	1 => array(
		'title' => 'PayPal Tracking Add-on',
		'description' => 'This add-on extends the Advanced shipment tracking plugin and will automatically send tracking numbers and associated information from WooCommerce to PayPal using the PayPal API.',
		'url' => 'https://www.zorem.com/product/paypal-tracking-add-on/',
		'image' => 'paypal-addon-banner.png',
		'file' => 'paypal-tracking-add-on-for-ast/paypal-tracking-add-on-for-ast.php',
		'price' => 49,
	),		
);
 
$wc_ast_api_key = get_option('wc_ast_api_key'); 
?>
<section id="content6" class="tab_section">
	<div class="addons_page_dtable" style="">

		<?php
		$show_addons_tab = apply_filters( 'ast_show_addons_tab', false );
		
		if ( class_exists( 'ast_pro' ) ) {
			$show_addons_tab = true;
		} elseif ( class_exists( 'ast_compatibility_with_wc_shipstation' ) ) {
			$show_addons_tab = true;
		} elseif ( class_exists( 'ast_compatibility_with_wc_services' ) ) {
			$show_addons_tab = true;
		} elseif ( class_exists( 'ast_compatibility_with_readytoship' ) ) {
			$show_addons_tab = true;
		} elseif ( class_exists( 'paypal_tracking_add_on' ) ) {
			$show_addons_tab = true;
		}
		$addons = isset( $_GET['addons'] ) ? sanitize_text_field($_GET['addons']) : 'addons'; ?>
		
		<?php do_action('ast_addon_license_form'); ?>	
		<?php if(!class_exists('ast_pro')){ ?>
		<div class="section-content trackship_addon_section">
			<div class="ast-row">
				<div class="as-col-6">
					<div class="ts_col_inner">
						<h1 class="ast_pro_landing_header">Advanced Shipment Tracking Pro</h1>
						<ul class="ast_pro_features_list">
							<li>Premium Support</li>
							<li>Tracking per item</li>
							<li>Fluid Responsive tracking widget</li>
							<li>Custom order status "Shipped"</li>
							<li>Custom email templates</li>							
							<li>Unfulfilled orders filter</li>
							<li>Auto-detect shipping providers</li>							
							<li>Integrations with ShipStation, WooCommerce Shipping, Royal Mail Click & Drop and more..</li>
						</ul>
						<a href="https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/" class="button-primary btn_ast2 btn_large" target="_blank">UPGRADE NOW</a>							
					</div>
				</div>									
				<div class="as-col-6">
					<div class="ts_col_inner ast_ts_landing_banner">
						<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/ast-pro-banner.png">
					</div>
				</div>
			</div>
		</div>	
		<?php } ?>		
		
		<div class="plugins_section free_plugin_section">
			<div class="single_plugin as-col-6">
				<div class="free_plugin_inner">
					<div class="ast_paid_plugin_image">
						<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/trackship-logo.png">
					</div>
					<div class="paid_plugin_description">
						<h3>Your Post-Shipping & Delivery Autopilot</h3>	
						<p>Trackship is a Multi-Carrier Shipment Tracking API that seamlessly integrates into your WooCommerce store and auto-tracks your shipments.</p>
						
						<?php 
						$wc_ast_api_key = get_option('wc_ast_api_key'); 
						if ( $wc_ast_api_key ) { 
						?>
							<a href="https://trackship.info/my-account/" class="button button-primary btn_green2" target="blank"><?php _e('Connected', 'woo-advanced-shipment-tracking'); ?></a>
						<?php } else { ?>
							<a href="https://trackship.info/?utm_source=wpadmin&utm_campaign=tspage" class="button button-primary btn_ast2" target="blank"><?php _e('Connect your store', 'woo-advanced-shipment-tracking'); ?></a>
						<?php } ?>
						
					</div>
				</div>
			</div>
			<div class="single_plugin as-col-6">
				<div class="free_plugin_inner">
					<div class="ast_paid_plugin_image">
						<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/paypal-addon-banner.png">
					</div>
					<div class="paid_plugin_description">
						<h3 class="plugin_title">PayPal Tracking for WooCommerce</h3>
						<p>This add-on extends the Advanced shipment tracking plugin and will automatically send tracking numbers and associated information from WooCommerce to PayPal using the PayPal API.</p>
						<?php 
						if ( is_plugin_active( 'paypal-tracking-add-on-for-ast/paypal-tracking-add-on-for-ast.php' ) ) { ?>
							<button type="button" class="button button button-primary btn_green2">Active</button>
						<?php } else { ?>
							<a href="https://www.zorem.com/product/paypal-tracking-add-on/" class="button button-primary btn_ast2" target="blank"><?php _e('More Info', 'woo-advanced-shipment-tracking'); ?></a>
						<?php } ?>	
					</div>
				</div>
			</div>	
		</div>	
		
		<div class="plugins_section free_plugin_section">
			<?php foreach($more_plugins as $plugin){ ?>
				<div class="single_plugin as-col-4">
					<div class="free_plugin_inner">
						<div class="paid_plugin_image">
							<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/<?php echo $plugin['image']; ?>">
						</div>
						<div class="paid_plugin_description">
							<h3 class="plugin_title"><?php echo $plugin['title']; ?></h3>
							<p><?php echo $plugin['description']; ?></p>
							<?php 
							if ( is_plugin_active( $plugin['file'] ) ) { ?>
								<button type="button" class="button button button-primary btn_green2">Active</button>
							<?php } else{ ?>
								<a href="<?php echo $plugin['url']; ?>" class="button button-primary btn_ast2" target="blank"><?php _e('More Info', 'woo-advanced-shipment-tracking'); ?></a>
							<?php } ?>	
						</div>
					</div>
				</div>	
			<?php } ?>				
		</div>																																						
	</div>
</section>