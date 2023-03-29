<?php
/**
 * Html code for tools tab
 */
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
			<a href="https://www.zorem.com/ast-pro/" class="button-primary btn_ast2 btn_large upgrade_to_pro_btn" target="_blank">UPGRADE TO AST PRO <span class="dashicons dashicons-arrow-right-alt2"></span></a>	
		</div>						
		
		<h1 class="tab_section_heading clear_spacing" style="margin: 20px 0 0;">Level up your fulfillment workflows</h1>				
		
		<div class="plugins_section free_plugin_section">
			<div class="single_plugin as-col-4">
				<div class="free_plugin_inner">
					<div class="paid_plugin_image">
						<img src="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ); ?>assets/images/trackship.png">
						<h3 class="plugin_title">TrackShip for WooCommerce</h3>
					</div>
					<div class="paid_plugin_description">						
						<p>Take control of your post-shipping workflows, reduce time spent on customer service and provide a superior post-purchase experience to your customers.Beyond automatic shipment tracking, TrackShip brings a branded tracking experience into your store, integrates into your workflow, and takes care of all the touch points with your customers after shipping.</p>
						<?php 
						if ( is_plugin_active('trackship-for-woocommerce/trackship-for-woocommerce.php' ) ) {
							?>
							<button type="button" class="button button button-primary btn_green2">Active</button>
						<?php } else { ?>
							<a href="https://wordpress.org/plugins/trackship-for-woocommerce/" class="button button-primary btn_ast2" target="blank"><?php esc_html_e('More Info', 'ast-pro'); ?></a>
						<?php } ?>	
					</div>
				</div>
			</div>				
			<div class="single_plugin as-col-4">
				<div class="free_plugin_inner">
					<div class="paid_plugin_image">
						<img src="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ); ?>assets/images/smswoo-icon.png">
						<h3 class="plugin_title">SMS for WooCommerce</h3>
					</div>
					<div class="paid_plugin_description">						
						<p>Keep your customers informed by sending them automated SMS text messages with order & delivery updates. You can send SMS notifications to customers when the order status is updated or when the shipment is out for delivery and more…</p>
						<?php 
						if ( is_plugin_active('sms-for-woocommerce/sms-for-woocommerce.php' ) ) {
							?>
							<button type="button" class="button button button-primary btn_green2">Active</button>
						<?php } else { ?>
							<a href="https://www.zorem.com/product/sms-for-woocommerce/" class="button button-primary btn_ast2" target="blank"><?php esc_html_e('More Info', 'ast-pro'); ?></a>
						<?php } ?>	
					</div>
				</div>
			</div>
			<div class="single_plugin as-col-4">
				<div class="free_plugin_inner">
					<div class="paid_plugin_image">
						<img src="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ); ?>assets/images/alp.png">
						<h3 class="plugin_title">Advanced Local Pickup Pro</h3>
					</div>
					<div class="paid_plugin_description">						
						<p>The Advanced Local Pickup (ALP) helps you manage the local pickup orders workflow more conveniently by extending the WooCommerce Local Pickup shipping method. The Pro you set up multiple pickup locations, , split the business hours, apply discounts by pickup location, display local pickup message on the products pages, allow customers to choose pickup location per product, force products to be local pickup only and more…</p>
						<?php 
						if ( is_plugin_active('advanced-local-pickup-pro/advanced-local-pickup-pro.php' ) ) {
							?>
							<button type="button" class="button button button-primary btn_green2">Active</button>
						<?php } else { ?>
							<a href="https://www.zorem.com/product/advanced-local-pickup-for-woocommerce/" class="button button-primary btn_ast2" target="blank"><?php esc_html_e('More Info', 'ast-pro'); ?></a>
						<?php } ?>	
					</div>
				</div>
			</div>	
			<div class="single_plugin as-col-4">
				<div class="free_plugin_inner">
					<div class="paid_plugin_image">
						<img src="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ); ?>assets/images/cbr.png">
						<h3 class="plugin_title">Country Based Restrictions Pro</h3>
					</div>
					<div class="paid_plugin_description">						
						<p>The country-based restrictions plugin by zorem works by the WooCommerce Geolocation or the shipping country added by the customer and allows you to restrict products on your store to sell or not to sell to specific countries.</p>
						<?php 
						if ( is_plugin_active('country-base-restrictions-pro-addon/country-base-restrictions-pro-addon.php' ) ) {
							?>
							<button type="button" class="button button button-primary btn_green2">Active</button>
						<?php } else { ?>
							<a href="https://www.zorem.com/product/country-based-restriction-pro/" class="button button-primary btn_ast2" target="blank"><?php esc_html_e('More Info', 'ast-pro'); ?></a>
						<?php } ?>	
					</div>
				</div>
			</div>
			<div class="single_plugin as-col-4">
				<div class="free_plugin_inner">
					<div class="paid_plugin_image">
						<img src="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ); ?>assets/images/aosm.png">
						<h3 class="plugin_title">Order Status Manager</h3>
					</div>
					<div class="paid_plugin_description">						
						<p>The Advanced Order Status Manager allows store owners to manage the WooCommerce orders statuses, create, edit, and delete custom Custom Order Statuses and integrate them into the WooCommerce orders flow.</p>
						<?php 
						if ( is_plugin_active('advanced-order-status-manager/advanced-order-status-manager.php' ) ) {
							?>
							<button type="button" class="button button button-primary btn_green2">Active</button>
						<?php } else { ?>
							<a href="https://www.zorem.com/product/advanced-order-status-manager/" class="button button-primary btn_ast2" target="blank"><?php esc_html_e('More Info', 'ast-pro'); ?></a>
						<?php } ?>	
					</div>
				</div>
			</div>			
			<div class="single_plugin as-col-4">
				<div class="free_plugin_inner">
					<div class="paid_plugin_image">
						<img src="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ); ?>assets/images/sre.png">
						<h3 class="plugin_title">Sales Report Email</h3>
					</div>
					<div class="paid_plugin_description">						
						<p>The Sales Report Email Pro will help know how well your store is performing and how your products are selling by sending you a daily, weekly, or monthly sales report by email, directly from your WooCommerce store.</p>
						<?php 
						if ( is_plugin_active('sales-report-email-pro/sales-report-email-pro.php' ) ) {
							?>
							<button type="button" class="button button button-primary btn_green2">Active</button>
						<?php } else { ?>
							<a href="https://www.zorem.com/product/sales-report-email-pro/" class="button button-primary btn_ast2" target="blank"><?php esc_html_e('More Info', 'ast-pro'); ?></a>
						<?php } ?>	
					</div>
				</div>
			</div>	
		</div>																																					
	</div>
</section>
