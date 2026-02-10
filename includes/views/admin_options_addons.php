<?php

$more_plugins = array(
	0 => array(
		'title' => 'TrackShip for WooCommerce',
		'description' => __( 'Take control of your post-shipping workflows, reduce time spent on customer service and provide a superior post-purchase experience to your customers.Beyond automatic shipment tracking, TrackShip brings a branded tracking experience into your store, integrates into your workflow, and takes care of all the touch points with your customers after shipping.', 'woo-advanced-shipment-tracking' ),
		'url' => 'https://wordpress.org/plugins/trackship-for-woocommerce/#utm_source=wp-admin&utm_medium=ast-addons&utm_campaign=add-ons',
		'image' => 'trackship.png',
		'file' => 'trackship-for-woocommerce/trackship-for-woocommerce.php'
	),
	1 => array(
		'title' => 'SMS for WooCommerce',
		'description' => __( 'Keep your customers informed by sending them automated SMS text messages with order & delivery updates. You can send SMS notifications to customers when the order status is updated or when the shipment is out for delivery and more…', 'woo-advanced-shipment-tracking' ),
		'url' => 'https://www.zorem.com/products/sms-for-woocommerce/#utm_source=wp-admin&utm_medium=ast-addons&utm_campaign=add-ons',
		'image' => 'smswoo.png',
		'file' => 'sms-for-woocommerce/sms-for-woocommerce.php'
	),
	2 => array(
		'title' => 'Zorem Local Pickup Pro',
		'description' => __( 'The Advanced Local Pickup (ALP) helps you manage the local pickup orders workflow more conveniently by extending the WooCommerce Local Pickup shipping method. The Pro you set up multiple pickup locations, , split the business hours, apply discounts by pickup location, display local pickup message on the products pages, allow customers to choose pickup location per product, force products to be local pickup only and more…', 'woo-advanced-shipment-tracking' ),
		'url' => 'https://www.zorem.com/product/zorem-local-pickup-pro/#utm_source=wp-admin&utm_medium=ast-addons&utm_campaign=add-ons',
		'image' => 'alp.png',
		'file' => 'advanced-local-pickup-pro/advanced-local-pickup-pro.php'
	),
	3 => array(
		'title' => 'Country Based Restriction for WooCommerce',
		'description' => __( 'The country-based restrictions plugin by zorem works by the WooCommerce Geolocation or the shipping country added by the customer and allows you to restrict products on your store to sell or not to sell to specific countries.', 'woo-advanced-shipment-tracking' ),
		'url' => 'https://www.zorem.com/product/country-based-restriction-pro/#utm_source=wp-admin&utm_medium=ast-addons&utm_campaign=add-ons',
		'image' => 'cbr.png',
		'file' => 'country-based-restriction-pro-addon/country-based-restriction-pro-addon.php'
	),
	4 => array(
		'title' => 'Customer Email Verification',
		'description' => __( 'The Customer Email Verification helps WooCommerce store owners to reduce registration and spam orders by requiring customers to verify their email address when they register an account or before they can place an order on your store.', 'woo-advanced-shipment-tracking' ),
		'url' => 'https://www.zorem.com/product/customer-email-verification/?utm_source=wp-admin&utm_medium=ast-addons&utm_campaign=more-info',
		'image' => 'cev.png',
		'file' => 'customer-email-verification/customer-email-verification.php'
	),
	5 => array(
		'title' => 'Email Reports for WooCommerce',
		'description' => __( 'The Sales Report Email Pro will help know how well your store is performing and how your products are selling by sending you a daily, weekly, or monthly sales report by email, directly from your WooCommerce store.', 'woo-advanced-shipment-tracking' ),
		'url' => 'https://www.zorem.com/product/email-reports-for-woocommerce/#utm_source=wp-admin&utm_medium=ast-addons&utm_campaign=add-ons',
		'image' => 'sre.png',
		'file' => 'sales-report-email-pro/sales-report-email-pro.php'
	),
);

$plugin_url = wc_advanced_shipment_tracking()->plugin_dir_url();

?>
<section id="content6" class="tab_section">
	<div class="tab_container_without_bg">
		<?php do_action('ast_addon_license_form'); ?>

		<!-- New Go Pro v2 layout -->
		<div class="ast-go-pro-v2">
			<!-- Hero -->
			<div class="gopro-hero">
				<h1><?php esc_html_e( 'Take Your Fulfillment to the Next Level', 'woo-advanced-shipment-tracking' ); ?></h1>
				<p><?php echo wp_kses_post(
					__( 'Stop wasting hours on manual tracking updates. Switch from a <strong>tedious manual workflow</strong> to a <a href="https://www.zorem.com/ast-pro/?utm_source=wp-admin&utm_medium=ast-go-pro&utm_campaign=hero" target="_blank">fully automated fulfillment powerhouse</a>.', 'woo-advanced-shipment-tracking' )
				); ?></p>
			</div>

			<!-- Feature comparison -->
			<div class="gopro-comparison">
				<!-- Table header -->
				<div class="gopro-comp-header">
					<div class="gopro-comp-header-label"><?php esc_html_e( 'Feature Comparison', 'woo-advanced-shipment-tracking' ); ?></div>
					<div class="gopro-comp-header-col">
						<span class="comp-header-badge badge-current"><?php esc_html_e( 'Current', 'woo-advanced-shipment-tracking' ); ?></span>
						<span class="comp-header-title"><?php esc_html_e( 'AST FREE', 'woo-advanced-shipment-tracking' ); ?></span>
					</div>
					<div class="gopro-comp-header-col is-pro">
						<span class="comp-header-badge badge-recommended"><?php esc_html_e( 'Recommended', 'woo-advanced-shipment-tracking' ); ?></span>
						<span class="comp-header-title"><?php esc_html_e( 'AST PRO', 'woo-advanced-shipment-tracking' ); ?></span>
					</div>
				</div>
				<?php
				$comp_features = array(
					array(
						'title'     => __( 'Tracking Per Item', 'woo-advanced-shipment-tracking' ),
						'desc'      => __( 'Add multiple tracking numbers to specific line items in an order.', 'woo-advanced-shipment-tracking' ),
						'free'      => __( 'Not Available', 'woo-advanced-shipment-tracking' ),
						'pro'       => __( 'Full Item Tracking', 'woo-advanced-shipment-tracking' ),
					),
					array(
						'title'     => __( 'Auto-detect shipping Carriers', 'woo-advanced-shipment-tracking' ),
						'desc'      => __( 'Automatically identify carriers based on tracking number format.', 'woo-advanced-shipment-tracking' ),
						'free'      => __( 'Not Available', 'woo-advanced-shipment-tracking' ),
						'pro'       => __( 'Instant Detection', 'woo-advanced-shipment-tracking' ),
					),
					array(
						'title'     => __( 'Fulfillment Dashboard', 'woo-advanced-shipment-tracking' ),
						'desc'      => __( 'Centralized view of all unfulfilled and pending shipments.', 'woo-advanced-shipment-tracking' ),
						'free'      => __( 'Not Available', 'woo-advanced-shipment-tracking' ),
						'pro'       => __( 'Full Overview', 'woo-advanced-shipment-tracking' ),
					),
					array(
						'title'     => __( 'White Label Shipping Carriers', 'woo-advanced-shipment-tracking' ),
						'desc'      => __( 'Customize provider names and logos for a branded experience.', 'woo-advanced-shipment-tracking' ),
						'free'      => __( 'Not Available', 'woo-advanced-shipment-tracking' ),
						'pro'       => __( 'Custom Branding', 'woo-advanced-shipment-tracking' ),
					),
					array(
						'title'     => __( 'Create Custom Shipping Carriers', 'woo-advanced-shipment-tracking' ),
						'desc'      => __( 'Add your own local or specialized carrier tracking links.', 'woo-advanced-shipment-tracking' ),
						'free'      => __( 'Not Available', 'woo-advanced-shipment-tracking' ),
						'pro'       => __( 'Unlimited Custom', 'woo-advanced-shipment-tracking' ),
					),
					array(
						'title'     => __( 'Shipping Provider API Name Mapping', 'woo-advanced-shipment-tracking' ),
						'desc'      => __( 'Map carrier names from external APIs to AST providers.', 'woo-advanced-shipment-tracking' ),
						'free'      => __( 'Not Available', 'woo-advanced-shipment-tracking' ),
						'pro'       => __( 'Smart API Mapping', 'woo-advanced-shipment-tracking' ),
					),
					array(
						'title'     => __( 'Integrates with Your Favorite Shipping Service', 'woo-advanced-shipment-tracking' ),
						'desc'      => __( 'Advanced Shipment Tracking Pro automatically syncs tracking from shipping services.', 'woo-advanced-shipment-tracking' ),
						'free'      => __( 'Not Available', 'woo-advanced-shipment-tracking' ),
						'pro'       => __( 'Shipping Service Integrations', 'woo-advanced-shipment-tracking' ),
					),
					array(
						'title'     => __( 'Export Tracking to PayPal and Stripe', 'woo-advanced-shipment-tracking' ),
						'desc'      => __( 'Sync tracking info to PayPal and Stripe transactions automatically.', 'woo-advanced-shipment-tracking' ),
						'free'      => __( 'Not Available', 'woo-advanced-shipment-tracking' ),
						'pro'       => __( 'Auto-Sync Enabled', 'woo-advanced-shipment-tracking' ),
					),
					array(
						'title'     => __( 'Compatible with Popular Plugins', 'woo-advanced-shipment-tracking' ),
						'desc'      => __( 'Built-in support for PDF Invoices, Email Customizers, and more.', 'woo-advanced-shipment-tracking' ),
						'free'      => __( 'Limited Support', 'woo-advanced-shipment-tracking' ),
						'pro'       => __( 'Full Integration', 'woo-advanced-shipment-tracking' ),
					),
					array(
						'title'     => __( 'Premium Support', 'woo-advanced-shipment-tracking' ),
						'desc'      => __( 'Priority ticket handling and dedicated help center access.', 'woo-advanced-shipment-tracking' ),
						'free'      => __( 'Standard Only', 'woo-advanced-shipment-tracking' ),
						'pro'       => __( 'Priority Support', 'woo-advanced-shipment-tracking' ),
					),
				);
				foreach ( $comp_features as $feat ) :
				?>
				<div class="gopro-comp-row">
					<div class="gopro-comp-feature">
						<strong><?php echo esc_html( $feat['title'] ); ?></strong>
						<span><?php echo esc_html( $feat['desc'] ); ?></span>
					</div>
					<div class="gopro-comp-cell">
						<span class="comp-icon icon-x">
							<svg fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2.5"><line x1="16" y1="8" x2="8" y2="16"/><line x1="8" y1="8" x2="16" y2="16"/></svg>
						</span>
						<span class="comp-status"><?php echo esc_html( $feat['free'] ); ?></span>
					</div>
					<div class="gopro-comp-cell is-pro">
						<span class="comp-icon icon-check">
							<svg fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
						</span>
						<span class="comp-status"><?php echo esc_html( $feat['pro'] ); ?></span>
					</div>
				</div>
				<?php endforeach; ?>
			</div>

			<!-- CTA -->
			<div class="gopro-cta">
				<a href="https://www.zorem.com/ast-pro/?utm_source=wp-admin&utm_medium=ast-go-pro&utm_campaign=get-started" class="gopro-cta-btn button-primary btn_ast2" target="_blank"><?php esc_html_e( 'GET STARTED WITH PRO', 'woo-advanced-shipment-tracking' ); ?></a>
				<p class="gopro-cta-sub"><?php esc_html_e( 'Join 60,000+ stores optimizing their shipping workflow', 'woo-advanced-shipment-tracking' ); ?></p>
			</div>

			<!-- Benefit cards -->
			<div class="gopro-benefits">
				<div class="gopro-benefits-grid">

					<!-- Huge Time Savings -->
					<div class="gopro-benefit-card">
						<div class="gopro-benefit-icon icon-blue">
							<svg fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
						</div>
						<h3><?php esc_html_e( 'Huge Time Savings', 'woo-advanced-shipment-tracking' ); ?></h3>
						<div class="gopro-benefit-row">
							<span class="gopro-benefit-label label-before"><?php esc_html_e( 'BEFORE:', 'woo-advanced-shipment-tracking' ); ?></span>
							<span class="gopro-benefit-text"><?php esc_html_e( 'Spending 2-3 hours daily manually copy-pasting tracking numbers from carrier sites to WooCommerce orders.', 'woo-advanced-shipment-tracking' ); ?></span>
						</div>
						<div class="gopro-benefit-row">
							<span class="gopro-benefit-label label-after"><?php esc_html_e( 'AFTER:', 'woo-advanced-shipment-tracking' ); ?></span>
							<span class="gopro-benefit-text"><?php esc_html_e( 'Tracking numbers sync automatically. Fulfillment time reduced to seconds per order.', 'woo-advanced-shipment-tracking' ); ?></span>
						</div>
					</div>

					<!-- Reduce Support Tickets -->
					<div class="gopro-benefit-card">
						<div class="gopro-benefit-icon icon-purple">
							<svg fill="none" viewBox="0 0 24 24" stroke="#7c3aed" stroke-width="2"><path d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
						</div>
						<h3><?php esc_html_e( 'Reduce Support Tickets', 'woo-advanced-shipment-tracking' ); ?></h3>
						<div class="gopro-benefit-row">
							<span class="gopro-benefit-label label-before"><?php esc_html_e( 'BEFORE:', 'woo-advanced-shipment-tracking' ); ?></span>
							<span class="gopro-benefit-text"><?php esc_html_e( '"Where is my order?" emails flooding your inbox because of missing or delayed tracking info.', 'woo-advanced-shipment-tracking' ); ?></span>
						</div>
						<div class="gopro-benefit-row">
							<span class="gopro-benefit-label label-after"><?php esc_html_e( 'AFTER:', 'woo-advanced-shipment-tracking' ); ?></span>
							<span class="gopro-benefit-text"><?php esc_html_e( 'Customers get instant, branded notifications. 65% fewer shipping-related support inquiries.', 'woo-advanced-shipment-tracking' ); ?></span>
						</div>
					</div>

				</div>
			</div>

		</div>
		<!-- End .ast-go-pro-v2 hero -->

		<!-- Powerful Add-ons -->
		<div class="ast-go-pro-v2">
			<div class="gopro-addons">
				<div class="gopro-addons-header">
					<div>
						<h2><?php esc_html_e( 'Powerful Add-ons', 'woo-advanced-shipment-tracking' ); ?></h2>
						<p><?php esc_html_e( 'Extend your store\'s capabilities with our ecosystem', 'woo-advanced-shipment-tracking' ); ?></p>
					</div>
				</div>
				<div class="gopro-addons-track">
						<?php
						$icon_colors = array( '#ecfdf5', '#ede9fe', '#eff6ff', '#fef3c7', '#fce7f3', '#e0f2fe' );
						foreach ( $more_plugins as $index => $addon ) :
							$icon_bg = isset( $icon_colors[ $index ] ) ? $icon_colors[ $index ] : '#f3f4f6';
						?>
							<div class="gopro-addon-card">
								<div class="gopro-addon-card-top">
									<div class="gopro-addon-card-icon" style="background:<?php echo esc_attr( $icon_bg ); ?>">
										<img src="<?php echo esc_url( $plugin_url ); ?>assets/images/<?php echo esc_attr( $addon['image'] ); ?>" alt="<?php echo esc_attr( $addon['title'] ); ?>">
									</div>
									<h3><?php echo esc_html( $addon['title'] ); ?></h3>
								</div>
								<div class="gopro-addon-card-body">
									<p><?php echo esc_html( $addon['description'] ); ?></p>
								</div>
								<div class="gopro-addon-card-footer">
									<?php if ( is_plugin_active( $addon['file'] ) ) : ?>
										<span class="gopro-addon-card-btn is-active"><?php esc_html_e( 'Active', 'woo-advanced-shipment-tracking' ); ?></span>
									<?php else : ?>
										<a href="<?php echo esc_url( $addon['url'] ); ?>" class="gopro-addon-card-btn" target="_blank"><?php esc_html_e( 'Learn More', 'woo-advanced-shipment-tracking' ); ?></a>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
				</div>
			</div>
		</div>
		<!-- Usage Tracking (unchanged) -->
		<form method="post" id="wc_usage_tracking_form" action="" enctype="multipart/form-data">
			<div class="usage-tracking-accordion heading add-tracking-option">
				<label>
					<?php esc_html_e( 'Usage Tracking', 'woo-advanced-shipment-tracking' ); ?>
					<span class="ast-accordion-btn">
						<div class="spinner workflow_spinner" style="float:none"></div>
						<button name="save" class="button-primary usage-tracking-save btn_ast2" type="submit" value="Save changes"><?php esc_html_e( 'Save', 'woo-advanced-shipment-tracking' ); ?></button>
					</span>
				</label>
			</div>
			<div class="usage-tracking-panel options usage-data-option">
				<?php wc_advanced_shipment_tracking()->admin->get_html_ul( wc_advanced_shipment_tracking()->admin->get_usage_tracking_options() ); ?>
			</div>
			<?php wp_nonce_field( 'wc_usage_tracking_form', 'wc_usage_tracking_form_nonce' ); ?>
			<input type="hidden" name="action" value="wc_usage_tracking_form_update">
		</form>
	</div>
</section>
