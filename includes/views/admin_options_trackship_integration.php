<?php
/**
 * Html code for trackship tab
 */
wp_enqueue_script( 'trackship_script' );
?>
<section id="trackship_landing" class="tab_section">
	<div class="tab_container_without_bg">
		<div class="section-content trackship_addon_section">
			<div class="ast-row">
				<div class="as-col-6">
					<div class="ts_col_inner">
						<img class="ts_landing_logo" src="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ); ?>assets/images/trackship-logo.png">
						<h1 class="ts_landing_header">Your Post-Shipping & Delivery Autopilot</h1>
						<ul class="ast_pro_features_list">
							<li>Branded tracking experience in your store</li>
							<li>Automate your Post-Shipping workflow</li>
							<li>Provide Amazon Style post-purchase customer experience</li>
							<li>Reduce time spent on customer service</li>
							<li>Create Relationships by engaging your customers after shipping</li>			
							<li>Increase customer satisfaction and repeat purchases</li>							
						</ul>
						<h3 class="ts_landing_h3">Start for Free. 50 Free trackers / monthly</h3>
						<a href="https://wordpress.org/plugins/trackship-for-woocommerce/" class="button-primary btn_green2 btn_large" target="_blank">Install TrackShip for WooCommerce</a>							
					</div>
				</div>									
				<div class="as-col-6">
					<div class="ts_col_inner ts_landing_banner">
						<img src="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ); ?>assets/images/trackship-banner.png">
						<a href="https://www.youtube.com/watch?v=PhnqDorKN_c" target="_blank" class="open_ts_video"><span class="dashicons dashicons-video-alt3"></span></a>
					</div>
				</div>
			</div>
		</div>		
	</div>
</section>
