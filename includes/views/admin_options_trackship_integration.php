<?php
/**
 * html code for trackship tab
 */
wp_enqueue_script( 'trackship_script' );
?>
<div class="section-content trackship_section">
	<div class="">
		<div class="ts_col_inner">
			<h1 class="ts_landing_header">Your Post-Shipping &amp; Delivery Autopilot</h1>
			<p class="ts_landing_description">Trackship is a Multi-Carrier Shipment Tracking API that seamlessly integrates into your WooCommerce store and auto-tracks your shipments, automates your orders workflow, reduces the time spent on customer service and lets you provide a superior post-purchase experience to your customers.</p>	
			<h3>Start for Free. 50 Free trackers for new accounts!</h3>	
		</div>
	</div>
	<a href="https://trackship.info/?utm_source=wpadmin&utm_campaign=tspage" target="_blank" class="button-primary btn_green2 btn_large"><span><?php _e('Connect your store', 'woo-advanced-shipment-tracking'); ?></span><span class="dashicons dashicons-arrow-right-alt2"></span></a>
	<div class="">
		<div class="ts_col_inner ts_landing_banner">
			<img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/ts-header-banner.png">
			<span class="dashicons dashicons-video-alt3 open_ts_video"></span>
		</div>		
	</div>				
</div>
<div id="" class="popupwrapper ts_video_popup" style="display:none;">
	<div class="popuprow">
		<div class="videoWrapper">
		<iframe id="ts_video" src="https://www.youtube.com/embed/PhnqDorKN_c" frameborder="0"  allowfullscreen></iframe>
		</div>
	</div>
	<div class="popupclose"></div>
</div>