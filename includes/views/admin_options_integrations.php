<section id="integrations_content" class="tab_section">
	<div class="integration_container">
		<div class="integration_list">
			<div class="provider-grid-row grid-row">
				<?php
				$ast_integration = AST_Integration::get_instance();
				$integrations = $ast_integration->integrations_settings_options();
				foreach ( $integrations as $integrations_id => $array ) {
				?>
				<div class="grid-item integration-popup-content">
					<div class="grid-top">
						<div class="grid-provider-img">
							<img class="provider-thumb" src="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/images/' . $array['img'] ); ?>">
						</div>
						<div class="grid-provider-name">
							<span class="provider_name"><?php echo esc_html( $array['title'] ); ?></span>
						</div>
						<div class="grid-provider-settings">
							<span class="dashicons dashicons-admin-generic integration_settings"></span>
						</div>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>

		<!-- Modal Overlay -->
		<div class="integration_modal_overlay">
			<div class="integration_modal_popup">
				<span class="close_modal">&times;</span>
				<div class="integration_modal_content"></div>
			</div>
		</div>

		<script>
			jQuery(function($){
				const staticContent = `
					<h2 style="color:#005b9a;font-size:20px;font-weight:700;margin-top:0">🚀 Unlock Powerful Shipping Integrations with AST PRO! 🎉</h2>
					<p><strong>Take the hassle out of order fulfillment!</strong> With AST PRO, you can seamlessly integrate with leading shipping services to <strong>automatically import tracking info</strong>, mark orders as shipped, and notify customers—<strong>no manual work required.</strong></p>
					<h3>✅ Benefits of Integrations in AST PRO:</h3>
					<ul>
					<li><strong>Auto-fetch tracking numbers</strong> from shipping services like <strong>ShipStation, WooCommerce Shipping, AliExpress Dropshipping, GLS</strong>, and more</li>
					<li><strong>Auto-update orders</strong> with tracking info & mark as shipped</li>
					<li><strong>Send tracking notifications</strong> to customers instantly</li>
					<li><strong>Save time</strong> and eliminate manual data entry</li>
					<li><strong>Reduce errors</strong> and improve order processing speed</li>
					</ul>
					<p>🎁 <strong>Special Offer:</strong> Use coupon code <strong>ASTPRO20</strong> to get <strong>20% OFF</strong> your upgrade!</p>
					<a href="https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/?utm_source=wp-admin&utm_medium=integration&utm_campaign=upgrad-now" class="button-primary btn_ast2" target="_blank">UPGRADE NOW</a>
				`;

				$('#integrations_content .integration-popup-content').click(function(e){
					e.stopPropagation();
					$('.integration_modal_content').html(staticContent);
					$('.integration_modal_overlay').fadeIn();
				});

				$('.integration_modal_overlay, .close_modal').click(function(e){
					if ($(e.target).is('.integration_modal_overlay, .close_modal')) {
					$('.integration_modal_overlay').fadeOut();
					}
				});
			});
		</script>
	</div>
</section>

