<?php
/**
 * html code for tools tab
 */
$wc_ast_api_key = get_option('wc_ast_api_key'); 
?>
<section id="content6" class="tab_section">
	<div class="tab_inner_container" style="">

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
		
		<h1 class="tab_section_heading clear_spacing" style="margin: 20px 0 0;"><?php _e('Other Products by zorem', 'ast-pro'); ?></h1>				
		
		<div class="plugins_section free_plugin_section">
			<div class="single_plugin as-col-4">
				<div class="free_plugin_inner">
					<div class="paid_plugin_image">
						<img style="width: 150px;height: auto;" src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/trackship-logo.png">
					</div>
					<div class="paid_plugin_description">
						<h3 class="plugin_title">TrackShip for WooCommerce</h3>
						<p>Auto-Track all your shipments and provide a superior Post-Purchase Experience to your Customers</p>
						<?php 
						if ( is_plugin_active('trackship-for-woocommerce/trackship-for-woocommerce.php' ) ) { ?>
							<button type="button" class="button button button-primary btn_green2">Active</button>
						<?php } else{ ?>
							<a href="https://wordpress.org/plugins/trackship-for-woocommerce/" class="button button-primary btn_ast2" target="blank"><?php _e('More Info', 'ast-pro'); ?></a>
						<?php } ?>	
					</div>
				</div>
			</div>	
			<div class="single_plugin as-col-4">
				<div class="free_plugin_inner">
					<div class="paid_plugin_image">
						<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/paypal-addon-banner.png">
					</div>
					<div class="paid_plugin_description">
						<h3 class="plugin_title">PayPal Tracking for WooCommerce</h3>
						<p>This add-on extends the Advanced shipment tracking plugin and will automatically send tracking numbers and associated information from WooCommerce to PayPal using the PayPal API.</p>
						<?php 
						if ( is_plugin_active('paypal-tracking-add-on-for-ast/paypal-tracking-add-on-for-ast.php' ) ) { ?>
							<button type="button" class="button button button-primary btn_green2">Active</button>
						<?php } else{ ?>
							<a href="https://www.zorem.com/product/paypal-tracking-for-woocommerce/" class="button button-primary btn_ast2" target="blank"><?php _e('More Info', 'ast-pro'); ?></a>
						<?php } ?>	
					</div>
				</div>
			</div>
			<div class="single_plugin as-col-4">
				<div class="free_plugin_inner">
					<div class="paid_plugin_image">
						<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/smswoo-icon.png">
					</div>
					<div class="paid_plugin_description">
						<h3 class="plugin_title">SMSWOO - SMS for WooCommerce</h3>
						<p>Keep your customers informed by sending them automated SMS text messages with order & delivery updates. You can send SMS notifications to customers when the order status is updated or when the shipment is out for delivery and more…</p>
						<?php 
						if ( is_plugin_active('sms-for-woocommerce/sms-for-woocommerce.php' ) ) { ?>
							<button type="button" class="button button button-primary btn_green2">Active</button>
						<?php } else{ ?>
							<a href="https://www.zorem.com/product/sms-for-woocommerce/" class="button button-primary btn_ast2" target="blank"><?php _e('More Info', 'ast-pro'); ?></a>
						<?php } ?>	
					</div>
				</div>
			</div>
			<div class="single_plugin as-col-4">
				<div class="free_plugin_inner">
					<div class="paid_plugin_image">
						<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/alp-icon.png">
					</div>
					<div class="paid_plugin_description">
						<h3 class="plugin_title">Advanced Local Pickup Pro</h3>
						<p>The Advanced Local Pickup (ALP) helps you manage the local pickup orders workflow more conveniently by extending the WooCommerce Local Pickup shipping method. The Pro you set up multiple pickup locations, , split the business hours, apply discounts by pickup location, display local pickup message on the products pages, allow customers to choose pickup location per product, force products to be local pickup only and more…</p>
						<?php 
						if ( is_plugin_active('advanced-local-pickup-pro/advanced-local-pickup-pro.php' ) ) { ?>
							<button type="button" class="button button button-primary btn_green2">Active</button>
						<?php } else{ ?>
							<a href="https://www.zorem.com/product/advanced-local-pickup-for-woocommerce/" class="button button-primary btn_ast2" target="blank"><?php _e('More Info', 'ast-pro'); ?></a>
						<?php } ?>	
					</div>
				</div>
			</div>	
			<div class="single_plugin as-col-4">
				<div class="free_plugin_inner">
					<div class="paid_plugin_image">
						<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/cbr-icon.png">
					</div>
					<div class="paid_plugin_description">
						<h3 class="plugin_title">Country Based Restrictions Pro</h3>
						<p>The country-based restrictions plugin by zorem works by the WooCommerce Geolocation or the shipping country added by the customer and allows you to restrict products on your store to sell or not to sell to specific countries.</p>
						<?php 
						if ( is_plugin_active('country-base-restrictions-pro-addon/country-base-restrictions-pro-addon.php' ) ) { ?>
							<button type="button" class="button button button-primary btn_green2">Active</button>
						<?php } else{ ?>
							<a href="https://www.zorem.com/product/country-based-restriction-pro/" class="button button-primary btn_ast2" target="blank"><?php _e('More Info', 'ast-pro'); ?></a>
						<?php } ?>	
					</div>
				</div>
			</div>	
		</div>																																					
	</div>
</section>