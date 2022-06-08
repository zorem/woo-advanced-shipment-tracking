<?php
/**
 * Html code for trackship tab
 */
wp_enqueue_script( 'trackship_script' );
?>
<section id="trackship_landing" class="tab_section">
	<div class="tab_inner_container" style="width: 100%;">	
		<div class="section-content trackship_section">
			<div class="">
				<div class="ts_col_inner">
					<img class="ts_landing_logo" src="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ); ?>assets/images/trackship-logo.png">
					<h1 class="ts_landing_header">Your Post-Shipping &amp; Delivery Autopilot</h1>
					<p class="ts_landing_description">Trackship is a Multi-Carrier Shipment Tracking API that seamlessly integrates into your WooCommerce store and auto-tracks your shipments, automates your orders workflow, reduces the time spent on customer service and lets you provide a superior post-purchase experience to your customers.</p>	
					<h3>Start for Free. 50 Free trackers for new accounts!</h3>	
				</div>
			</div>
			<a href="<?php echo esc_url( admin_url( 'plugin-install.php?tab=search&s=TrackShip+For+WooCommerce&plugin-search-input=Search+Plugins' ) ); ?>" target="_blank" class="button-primary btn_green2 btn_large"><span><?php esc_html_e('Install TrackShip for WooCommerce', 'woo-advanced-shipment-tracking'); ?></span><span class="dashicons dashicons-arrow-right-alt2"></span></a>
			<div class="">
				<div class="ts_col_inner ts_landing_banner">
					<img src="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ); ?>assets/images/ts-header-banner.png">
					
					<a href="https://www.youtube.com/watch?v=PhnqDorKN_c" target="_blank" class="open_ts_video"><span class="dashicons dashicons-video-alt3"></span></a>	
				</div>		
			</div>				
		</div>
	</div>
</section>
