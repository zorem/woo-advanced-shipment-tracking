<?php
/**
 * Html code for trackship tab
 */
wp_enqueue_script( 'trackship_script' );
?>
<section id="trackship_landing" class="tab_section">
	<div class="tab_container_without_bg">
		<!-- Main Hero Section -->
		<div class="ts_hero_card">
			<div class="ts_hero_row">
				<div class="ts_hero_left">
					<div class="ts_hero_logo">
						<img class="ts_landing_logo" src="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ); ?>assets/images/trackship-logo.png">
					</div>
					<h1 class="ts_landing_header"><?php esc_html_e( 'Your Post-Shipping & Delivery Autopilot', 'woo-advanced-shipment-tracking' ); ?></h1>
					<ul class="ast_pro_features_list">
						<li><?php esc_html_e( 'Branded tracking experience in your store', 'woo-advanced-shipment-tracking' ); ?></li>
						<li><?php esc_html_e( 'Automate your post-shipping workflow', 'woo-advanced-shipment-tracking' ); ?></li>
						<li><?php esc_html_e( 'Provide Amazon Style post-purchase customer experience', 'woo-advanced-shipment-tracking' ); ?></li>
						<li><?php esc_html_e( 'Reduce time spent on customer service', 'woo-advanced-shipment-tracking' ); ?></li>
						<li><?php esc_html_e( 'Create Relationships by engaging your customers after shipping', 'woo-advanced-shipment-tracking' ); ?></li>
						<li><?php esc_html_e( 'Increase customer satisfaction and repeat purchases', 'woo-advanced-shipment-tracking' ); ?></li>
					</ul>
					<h3 class="ts_landing_h3">
						<?php esc_html_e( 'Start for Free. 50 Free trackers / monthly', 'woo-advanced-shipment-tracking' ); ?>
					</h3>
					<a href="https://wordpress.org/plugins/trackship-for-woocommerce/"
					   class="ts_install_btn"
					   target="_blank">
						<?php esc_html_e( 'Install TrackShip for WooCommerce', 'woo-advanced-shipment-tracking' ); ?>
						<span class="dashicons dashicons-download"></span>
					</a>
				</div>
				<div class="ts_hero_right">
					<div class="ts_illustration">
						<div class="ts_illustration_top">
							<div class="ts_illus_card">
								<span class="dashicons dashicons-store"></span>
								<span class="ts_illus_label"><?php esc_html_e( 'YOUR STORE', 'woo-advanced-shipment-tracking' ); ?></span>
								
							</div>
							<div class="ts_illus_card">
								<span class="dashicons dashicons-admin-site-alt3"></span>
								<span class="ts_illus_label"><?php esc_html_e( '950+ CARRIERS', 'woo-advanced-shipment-tracking' ); ?></span>
							</div>
						</div>
						<div class="ts_illustration_mid">
							<div class="ts_sync_circle">
								<span class="dashicons dashicons-update"></span>
							</div>
						</div>
						<div class="ts_illustration_bottom">
							<div class="ts_tracking_card">
								<div class="ts_tracking_card_header">
									<span class="ts_skeleton_line ts_skeleton_short"></span>
									<span class="ts_status_badge"><?php esc_html_e( 'IN TRANSIT', 'woo-advanced-shipment-tracking' ); ?></span>
								</div>
								<div class="ts_tracking_step ts_step_active">
									<span class="ts_step_dot"></span>
									<div class="ts_step_lines">
										<span class="ts_skeleton_line ts_skeleton_wide ts_skeleton_green"></span>
									</div>
								</div>
								<div class="ts_tracking_step">
									<span class="ts_step_dot ts_dot_gray"></span>
									<div class="ts_step_lines">
										<span class="ts_skeleton_line ts_skeleton_wide"></span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Feature Cards Section -->
		<div class="ts_features_row">
			<div class="ts_feature_card">
				<div class="ts_feature_icon ts_icon_blue">
					<span class="dashicons dashicons-location"></span>
				</div>
				<h4 class="ts_feature_title"><?php esc_html_e( 'Real-time Tracking', 'woo-advanced-shipment-tracking' ); ?></h4>
				<p class="ts_feature_desc"><?php esc_html_e( 'Automatically track shipments across 950+ carriers worldwide with instant status updates.', 'woo-advanced-shipment-tracking' ); ?></p>
			</div>
			<div class="ts_feature_card">
				<div class="ts_feature_icon ts_icon_blue">
					<span class="dashicons dashicons-email-alt"></span>
				</div>
				<h4 class="ts_feature_title"><?php esc_html_e( 'Automated Emails', 'woo-advanced-shipment-tracking' ); ?></h4>
				<p class="ts_feature_desc"><?php esc_html_e( 'Trigger custom email notifications based on delivery status like Out for Delivery or Delivered.', 'woo-advanced-shipment-tracking' ); ?></p>
			</div>
			<div class="ts_feature_card">
				<div class="ts_feature_icon ts_icon_blue">
					<span class="dashicons dashicons-media-text"></span>
				</div>
				<h4 class="ts_feature_title"><?php esc_html_e( 'Tracking Page', 'woo-advanced-shipment-tracking' ); ?></h4>
				<p class="ts_feature_desc"><?php esc_html_e( 'A professional, branded tracking page on your store to keep customers coming back.', 'woo-advanced-shipment-tracking' ); ?></p>
			</div>
		</div>
	</div>
</section>
