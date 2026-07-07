<?php
/**
 * Go Pro / Add-ons tab — bespoke marketing layout.
 *
 * Bespoke chrome (not the License-page template): a gradient hero, an animated
 * stat strip, a 6-card power-feature grid, an outcomes showcase and a finale
 * CTA banner — followed by the shared zui-lic-* telemetry/help/ecosystem
 * sections so the bottom of the page still matches the PRO License tab.
 *
 * Legacy contracts preserved verbatim:
 *   - `wc_usage_tracking_form` form id + nonce + action + get_usage_tracking_options()
 *   - is_plugin_active() checks for add-on cards
 *   - utm_* URLs to zorem.com / wordpress.org
 *   - ast_addon_license_form action (no-op in free, kept for hook parity)
 *
 * @package wc_advanced_shipment_tracking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'zui_icon' ) ) {
	require_once dirname( __DIR__, 2 ) . '/assets/zui/icons.php';
}

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

do_action( 'ast_addon_license_form' );

$plugin_url      = wc_advanced_shipment_tracking()->plugin_dir_url();
$ast_upgrade_url = 'https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/?utm_source=wp-admin&utm_medium=plugin-setting&utm_campaign=upgrade-to-pro';

$ast_opt_email_key = 'ast_optin_email_notification';
$ast_opt_env_key   = 'ast_enable_usage_data';
$ast_opt_email_val = (bool) get_option( $ast_opt_email_key, 1 );
$ast_opt_env_val   = (bool) get_option( $ast_opt_env_key, 1 );

$ast_stats = array(
	array(
		'value' => '60K+',
		'label' => __( 'Stores Powered', 'woo-advanced-shipment-tracking' ),
		'sub'   => __( 'Active installations worldwide', 'woo-advanced-shipment-tracking' ),
		'icon'  => 'store',
		'tint'  => '#eff6ff',
		'accent'=> '#2563eb',
	),
	array(
		'value' => '65%',
		'label' => __( 'Fewer Support Tickets', 'woo-advanced-shipment-tracking' ),
		'sub'   => __( 'Less "where is my order?"', 'woo-advanced-shipment-tracking' ),
		'icon'  => 'trending-down',
		'tint'  => '#ecfdf5',
		'accent'=> '#059669',
	),
	array(
		'value' => '95%',
		'label' => __( 'Time Saved on Fulfillment', 'woo-advanced-shipment-tracking' ),
		'sub'   => __( 'From hours to seconds', 'woo-advanced-shipment-tracking' ),
		'icon'  => 'clock',
		'tint'  => '#f5f3ff',
		'accent'=> '#7c3aed',
	),
	array(
		'value' => '4.8',
		'label' => __( 'Average Rating', 'woo-advanced-shipment-tracking' ),
		'sub'   => __( 'WordPress.org reviews', 'woo-advanced-shipment-tracking' ),
		'icon'  => 'star',
		'tint'  => '#fef3c7',
		'accent'=> '#d97706',
	),
);

$ast_powers = array(
	array(
		'icon'  => 'package-2',
		'tint'  => '#eff6ff',
		'accent'=> '#2563eb',
		'title' => __( 'Tracking Per Line Item', 'woo-advanced-shipment-tracking' ),
		'desc'  => __( 'Attach multiple tracking numbers to individual products in a single order — perfect for split shipments.', 'woo-advanced-shipment-tracking' ),
	),
	array(
		'icon'  => 'wand-2',
		'tint'  => '#f5f3ff',
		'accent'=> '#7c3aed',
		'title' => __( 'Smart Auto-Detect Carriers', 'woo-advanced-shipment-tracking' ),
		'desc'  => __( 'Paste any tracking number and the carrier is recognised instantly from its format — no manual selection.', 'woo-advanced-shipment-tracking' ),
	),
	array(
		'icon'  => 'layout-dashboard',
		'tint'  => '#ecfdf5',
		'accent'=> '#059669',
		'title' => __( 'Fulfillment Dashboard', 'woo-advanced-shipment-tracking' ),
		'desc'  => __( 'One screen for every unfulfilled order with filters, search and bulk actions to clear your backlog faster.', 'woo-advanced-shipment-tracking' ),
	),
	array(
		'icon'  => 'paint-bucket',
		'tint'  => '#fce7f3',
		'accent'=> '#db2777',
		'title' => __( 'White-Label Carriers', 'woo-advanced-shipment-tracking' ),
		'desc'  => __( 'Rename providers and swap their logos so customer-facing tracking emails stay perfectly on-brand.', 'woo-advanced-shipment-tracking' ),
	),
	array(
		'icon'  => 'plug',
		'tint'  => '#fef3c7',
		'accent'=> '#d97706',
		'title' => __( 'PayPal & Stripe Sync', 'woo-advanced-shipment-tracking' ),
		'desc'  => __( 'Tracking numbers are pushed to PayPal & Stripe transactions automatically — fewer payment disputes.', 'woo-advanced-shipment-tracking' ),
	),
	array(
		'icon'  => 'cloud-upload',
		'tint'  => '#e0f2fe',
		'accent'=> '#0284c7',
		'title' => __( 'Automated FTP/SFTP Import', 'woo-advanced-shipment-tracking' ),
		'desc'  => __( 'Drop a CSV on a remote server and AST PRO imports tracking on a cron — fully hands-off fulfillment.', 'woo-advanced-shipment-tracking' ),
	),
);

$ast_outcomes = array(
	array(
		'side'      => 'before',
		'eyebrow'   => __( 'Manual workflow', 'woo-advanced-shipment-tracking' ),
		'title'     => __( 'The old way', 'woo-advanced-shipment-tracking' ),
		'icon'      => 'frown',
		'items'     => array(
			__( 'Copy-paste tracking numbers from 5+ carrier sites every day', 'woo-advanced-shipment-tracking' ),
			__( 'Customers email you "where is my order?" constantly', 'woo-advanced-shipment-tracking' ),
			__( 'Tracking links break the moment a carrier name is misspelled', 'woo-advanced-shipment-tracking' ),
			__( 'No visibility into how many orders are stuck unfulfilled', 'woo-advanced-shipment-tracking' ),
		),
	),
	array(
		'side'      => 'after',
		'eyebrow'   => __( 'Automated with AST PRO', 'woo-advanced-shipment-tracking' ),
		'title'     => __( 'The new way', 'woo-advanced-shipment-tracking' ),
		'icon'      => 'smile',
		'items'     => array(
			__( 'Tracking syncs from your shipping app the moment a label is printed', 'woo-advanced-shipment-tracking' ),
			__( 'Branded delivery notifications keep customers in the loop automatically', 'woo-advanced-shipment-tracking' ),
			__( 'Carriers auto-detect from tracking format — links never break again', 'woo-advanced-shipment-tracking' ),
			__( 'Real-time dashboard shows every pending shipment at a glance', 'woo-advanced-shipment-tracking' ),
		),
	),
);

$ast_addons = array(
	array(
		'name'   => 'TrackShip for WooCommerce',
		'desc'   => __( 'Take control of your post-shipping workflows, reduce time spent on customer service and provide a superior post-purchase experience to your customers.', 'woo-advanced-shipment-tracking' ),
		'url'    => 'https://wordpress.org/plugins/trackship-for-woocommerce/#utm_source=wp-admin&utm_medium=ast-addons&utm_campaign=add-ons',
		'image'  => 'trackship.png',
		'file'   => 'trackship-for-woocommerce/trackship-for-woocommerce.php',
		'tint'   => '#CCFBF1',
		'accent' => '#0d9488',
	),
	array(
		'name'   => 'SMS for WooCommerce',
		'desc'   => __( 'Keep your customers informed by sending them automated SMS text messages with order & delivery updates.', 'woo-advanced-shipment-tracking' ),
		'url'    => 'https://www.zorem.com/products/sms-for-woocommerce/#utm_source=wp-admin&utm_medium=ast-addons&utm_campaign=add-ons',
		'icon'   => 'phone',
		'file'   => 'sms-for-woocommerce/sms-for-woocommerce.php',
		'tint'   => '#DBEAFE',
		'accent' => '#2563EB',
	),
	array(
		'name'   => 'Zorem Local Pickup Pro',
		'desc'   => __( 'The Advanced Local Pickup (ALP) helps you manage the local pickup orders workflow more conveniently by extending the WooCommerce Local Pickup shipping method.', 'woo-advanced-shipment-tracking' ),
		'url'    => 'https://www.zorem.com/product/zorem-local-pickup-pro/#utm_source=wp-admin&utm_medium=ast-addons&utm_campaign=add-ons',
		'icon'   => 'store',
		'file'   => 'advanced-local-pickup-pro/advanced-local-pickup-pro.php',
		'tint'   => '#DCFCE7',
		'accent' => '#16A34A',
	),
	array(
		'name'   => 'Country Based Restriction for WooCommerce',
		'desc'   => __( 'Restrict products on your store to sell or not to sell to specific countries using WooCommerce Geolocation or shipping country.', 'woo-advanced-shipment-tracking' ),
		'url'    => 'https://www.zorem.com/product/country-based-restriction-pro/#utm_source=wp-admin&utm_medium=ast-addons&utm_campaign=add-ons',
		'icon'   => 'globe',
		'file'   => 'country-based-restriction-pro-addon/country-based-restriction-pro-addon.php',
		'tint'   => '#FFEDD5',
		'accent' => '#EA580C',
	),
	array(
		'name'   => 'Customer Email Verification',
		'desc'   => __( 'Reduce registration and spam orders by requiring customers to verify their email address before they can place an order on your store.', 'woo-advanced-shipment-tracking' ),
		'url'    => 'https://www.zorem.com/product/customer-email-verification/?utm_source=wp-admin&utm_medium=ast-addons&utm_campaign=more-info',
		'icon'   => 'shield-check',
		'file'   => 'customer-email-verification/customer-email-verification.php',
		'tint'   => '#FEE2E2',
		'accent' => '#DC2626',
	),
	array(
		'name'   => 'Email Reports for WooCommerce',
		'desc'   => __( 'Know how well your store is performing by receiving daily, weekly, or monthly sales reports by email, directly from your WooCommerce store.', 'woo-advanced-shipment-tracking' ),
		'url'    => 'https://www.zorem.com/product/email-reports-for-woocommerce/#utm_source=wp-admin&utm_medium=ast-addons&utm_campaign=add-ons',
		'icon'   => 'mail',
		'file'   => 'sales-report-email-pro/sales-report-email-pro.php',
		'tint'   => '#F3E8FF',
		'accent' => '#9333EA',
	),
);
?>
<section class="tab_section" id="addons-tab">
	<div class="ast-gp" id="ast-gp">

		<?php /* ─────────────────────────────────────────────────────────
		   1) HERO — gradient banner with eyebrow, headline and CTAs
		   ───────────────────────────────────────────────────────── */ ?>
		<div class="ast-gp-hero">
			<div class="ast-gp-hero__orb ast-gp-hero__orb--a"></div>
			<div class="ast-gp-hero__orb ast-gp-hero__orb--b"></div>
			<div class="ast-gp-hero__orb ast-gp-hero__orb--c"></div>

			<div class="ast-gp-hero__inner">
				<span class="ast-gp-hero__eyebrow">
					<?php zui_icon( 'sparkles' ); ?>
					<span><?php esc_html_e( 'Upgrade to AST PRO', 'woo-advanced-shipment-tracking' ); ?></span>
				</span>
				<h1 class="ast-gp-hero__title"><?php esc_html_e( 'Ship Faster. Support Less. Sell More.', 'woo-advanced-shipment-tracking' ); ?></h1>
				<p class="ast-gp-hero__lead">
					<?php esc_html_e( 'Turn manual tracking into a fully automated post-purchase engine that brands your store, calms your inbox and reclaims your evenings.', 'woo-advanced-shipment-tracking' ); ?>
				</p>

				<div class="ast-gp-hero__actions">
					<a class="ast-gp-btn ast-gp-btn--primary" href="<?php echo esc_url( $ast_upgrade_url ); ?>" target="_blank" rel="noopener noreferrer">
						<?php zui_icon( 'zap' ); ?>
						<span><?php esc_html_e( 'Get AST PRO', 'woo-advanced-shipment-tracking' ); ?></span>
					</a>
				</div>

				<div class="ast-gp-hero__trust">
					<span class="ast-gp-hero__trust-item"><?php zui_icon( 'shield-check' ); ?><?php esc_html_e( '30-day money back', 'woo-advanced-shipment-tracking' ); ?></span>
					<span class="ast-gp-hero__trust-item"><?php zui_icon( 'lifebuoy' ); ?><?php esc_html_e( 'Priority email support', 'woo-advanced-shipment-tracking' ); ?></span>
					<span class="ast-gp-hero__trust-item"><?php zui_icon( 'rocket' ); ?><?php esc_html_e( 'Instant activation', 'woo-advanced-shipment-tracking' ); ?></span>
				</div>
			</div>

			<div class="ast-gp-hero__badge">
				<span class="ast-gp-hero__badge-dot"></span>
				<div>
					<div class="ast-gp-hero__badge-eyebrow"><?php esc_html_e( 'Plan Status', 'woo-advanced-shipment-tracking' ); ?></div>
					<div class="ast-gp-hero__badge-val"><?php esc_html_e( 'Free Edition', 'woo-advanced-shipment-tracking' ); ?></div>
				</div>
			</div>
		</div>

		<?php /* ─────────────────────────────────────────────────────────
		   2) STATS STRIP — 4 large numbers
		   ───────────────────────────────────────────────────────── */ ?>
		<div class="ast-gp-stats">
			<?php foreach ( $ast_stats as $ast_s ) : ?>
				<div class="ast-gp-stat">
					<span class="ast-gp-stat__icon" style="background: <?php echo esc_attr( $ast_s['tint'] ); ?>; color: <?php echo esc_attr( $ast_s['accent'] ); ?>;">
						<?php zui_icon( $ast_s['icon'] ); ?>
					</span>
					<div class="ast-gp-stat__num"><?php echo esc_html( $ast_s['value'] ); ?></div>
					<div class="ast-gp-stat__label"><?php echo esc_html( $ast_s['label'] ); ?></div>
					<div class="ast-gp-stat__sub"><?php echo esc_html( $ast_s['sub'] ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php /* ─────────────────────────────────────────────────────────
		   3) POWER FEATURES — 6 visual cards in a 3-col grid
		   ───────────────────────────────────────────────────────── */ ?>
		<div class="ast-gp-section">
			<div class="ast-gp-section__head">
				<span class="ast-gp-section__eyebrow"><?php esc_html_e( 'Why AST PRO', 'woo-advanced-shipment-tracking' ); ?></span>
				<h2 class="ast-gp-section__title"><?php esc_html_e( 'Six power-ups your fulfillment is missing', 'woo-advanced-shipment-tracking' ); ?></h2>
				<p class="ast-gp-section__sub"><?php esc_html_e( 'Built for stores that ship more than a handful of orders a day. Each feature replaces hours of manual work with a few seconds of automation.', 'woo-advanced-shipment-tracking' ); ?></p>
			</div>

			<div class="ast-gp-powers">
				<?php foreach ( $ast_powers as $ast_p ) : ?>
					<div class="ast-gp-power">
						<span class="ast-gp-power__icon" style="background: <?php echo esc_attr( $ast_p['tint'] ); ?>; color: <?php echo esc_attr( $ast_p['accent'] ); ?>;">
							<?php zui_icon( $ast_p['icon'] ); ?>
						</span>
						<h3 class="ast-gp-power__title"><?php echo esc_html( $ast_p['title'] ); ?></h3>
						<p class="ast-gp-power__desc"><?php echo esc_html( $ast_p['desc'] ); ?></p>
						<span class="ast-gp-power__chip"><?php zui_icon( 'check' ); ?><?php esc_html_e( 'Included in PRO', 'woo-advanced-shipment-tracking' ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<?php /* ─────────────────────────────────────────────────────────
		   4) OUTCOMES — side-by-side "old way / new way"
		   ───────────────────────────────────────────────────────── */ ?>
		<div class="ast-gp-section">
			<div class="ast-gp-section__head">
				<span class="ast-gp-section__eyebrow"><?php esc_html_e( 'Before / After', 'woo-advanced-shipment-tracking' ); ?></span>
				<h2 class="ast-gp-section__title"><?php esc_html_e( 'What changes the day you upgrade', 'woo-advanced-shipment-tracking' ); ?></h2>
			</div>

			<div class="ast-gp-outcomes">
				<?php foreach ( $ast_outcomes as $ast_o ) : ?>
					<div class="ast-gp-outcome ast-gp-outcome--<?php echo esc_attr( $ast_o['side'] ); ?>">
						<div class="ast-gp-outcome__head">
							<span class="ast-gp-outcome__icon"><?php zui_icon( $ast_o['icon'] ); ?></span>
							<div>
								<span class="ast-gp-outcome__eyebrow"><?php echo esc_html( $ast_o['eyebrow'] ); ?></span>
								<h3 class="ast-gp-outcome__title"><?php echo esc_html( $ast_o['title'] ); ?></h3>
							</div>
						</div>
						<ul class="ast-gp-outcome__list">
							<?php foreach ( $ast_o['items'] as $ast_item ) : ?>
								<li>
									<span class="ast-gp-outcome__bullet"><?php zui_icon( 'before' === $ast_o['side'] ? 'x' : 'check' ); ?></span>
									<span><?php echo esc_html( $ast_item ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<?php /* ─────────────────────────────────────────────────────────
		   5) FINALE CTA — bold gradient banner
		   ───────────────────────────────────────────────────────── */ ?>
		<div class="ast-gp-cta">
			<div class="ast-gp-cta__orb"></div>
			<div class="ast-gp-cta__content">
				<span class="ast-gp-cta__eyebrow"><?php zui_icon( 'rocket' ); ?><?php esc_html_e( 'Ready when you are', 'woo-advanced-shipment-tracking' ); ?></span>
				<h2 class="ast-gp-cta__title"><?php esc_html_e( 'Unlock every feature for your store today', 'woo-advanced-shipment-tracking' ); ?></h2>
				<p class="ast-gp-cta__sub"><?php esc_html_e( 'Activate AST PRO in under two minutes. If it doesn\'t cut your fulfillment time in half, we refund every penny — no questions asked.', 'woo-advanced-shipment-tracking' ); ?></p>
			</div>
			<div class="ast-gp-cta__action">
				<a class="ast-gp-btn ast-gp-btn--primary ast-gp-btn--xl" href="<?php echo esc_url( $ast_upgrade_url ); ?>" target="_blank" rel="noopener noreferrer">
					<?php zui_icon( 'zap' ); ?>
					<span><?php esc_html_e( 'Get Started With PRO', 'woo-advanced-shipment-tracking' ); ?></span>
				</a>
				<span class="ast-gp-cta__note"><?php esc_html_e( '60,000+ stores already shipping faster', 'woo-advanced-shipment-tracking' ); ?></span>
			</div>
		</div>

		<?php /* ─────────────────────────────────────────────────────────
		   6) BOTTOM SHARED CHROME — telemetry + ecosystem + docs
		   ───────────────────────────────────────────────────────── */ ?>
		<div class="zui-lic">
			<div class="zui-lic-grid">
				<div class="zui-lic-col-main">
					<?php /* Telemetry preferences card (legacy wc_usage_tracking_form preserved) */ ?>
					<div class="zui-card zui-lic-card">
						<div class="zui-lic-card__head zui-lic-card__head--row">
							<span class="zui-lic-card__title">
								<?php zui_icon( 'info' ); ?>
								<?php esc_html_e( 'Telemetry & Communication Preferences', 'woo-advanced-shipment-tracking' ); ?>
							</span>
							<div class="zui-lic-save">
								<div class="spinner workflow_spinner" style="float:none"></div>
								<button name="save" form="wc_usage_tracking_form" type="submit" value="Save changes" class="zui-lic-savebtn usage-tracking-save zui-btn-primary"
									data-label-saving="<?php esc_attr_e( 'Saving…', 'woo-advanced-shipment-tracking' ); ?>"
									data-label-saved="<?php esc_attr_e( 'Saved', 'woo-advanced-shipment-tracking' ); ?>"><?php esc_html_e( 'Save', 'woo-advanced-shipment-tracking' ); ?></button>
							</div>
						</div>
						<form method="post" id="wc_usage_tracking_form" class="zui-lic-telemetry-form" action="" enctype="multipart/form-data">
							<div class="zui-lic-pref">
								<label class="zui-toggle">
									<input type="hidden" name="<?php echo esc_attr( $ast_opt_email_key ); ?>" value="0">
									<input type="checkbox" name="<?php echo esc_attr( $ast_opt_email_key ); ?>" value="1" class="zui-toggle__input" <?php checked( $ast_opt_email_val, true ); ?>>
									<span class="zui-toggle__track"><span class="zui-toggle__thumb"></span></span>
								</label>
								<div>
									<span class="zui-lic-pref__title"><?php esc_html_e( 'Opt in to get email notifications for security & feature updates', 'woo-advanced-shipment-tracking' ); ?></span>
									<p class="zui-lic-pref__sub"><?php esc_html_e( 'Subscribes your registered contact to security bulletins, local carrier format enhancements, and diagnostic updates.', 'woo-advanced-shipment-tracking' ); ?></p>
								</div>
							</div>
							<div class="zui-lic-pref">
								<label class="zui-toggle">
									<input type="hidden" name="<?php echo esc_attr( $ast_opt_env_key ); ?>" value="0">
									<input type="checkbox" name="<?php echo esc_attr( $ast_opt_env_key ); ?>" value="1" class="zui-toggle__input" <?php checked( $ast_opt_env_val, true ); ?>>
									<span class="zui-toggle__track"><span class="zui-toggle__thumb"></span></span>
								</label>
								<div>
									<span class="zui-lic-pref__title"><?php esc_html_e( 'Opt in to share some basic WordPress environment info', 'woo-advanced-shipment-tracking' ); ?></span>
									<p class="zui-lic-pref__sub"><?php esc_html_e( 'Shares secure versioning data (PHP version, active plugins list, theme template names) to help our support team proactively identify store errors.', 'woo-advanced-shipment-tracking' ); ?></p>
								</div>
							</div>
							<?php wp_nonce_field( 'wc_usage_tracking_form', 'wc_usage_tracking_form_nonce' ); ?>
							<input type="hidden" name="action" value="wc_usage_tracking_form_update">
						</form>
					</div>
				</div>

				<div class="zui-lic-col-side">
					<div class="zui-lic-help">
						<h3 class="zui-lic-help__title"><?php esc_html_e( 'Documentation & Help', 'woo-advanced-shipment-tracking' ); ?></h3>
						<ul class="zui-lic-help__list">
							<li><a href="https://docs.zorem.com/" target="_blank" rel="noopener noreferrer"><span><?php esc_html_e( 'Browse Zorem Knowledge Base', 'woo-advanced-shipment-tracking' ); ?></span><?php zui_icon( 'external-link' ); ?></a></li>
							<li><a href="https://wordpress.org/support/plugin/woo-advanced-shipment-tracking/#new-topic-0" target="_blank" rel="noopener noreferrer"><span><?php esc_html_e( 'Submit Helpdesk Support Ticket', 'woo-advanced-shipment-tracking' ); ?></span><?php zui_icon( 'external-link' ); ?></a></li>
							<li><a href="https://www.trackship.com/" target="_blank" rel="noopener noreferrer"><span><?php esc_html_e( 'TrackShip Fulfillment Engine Portal', 'woo-advanced-shipment-tracking' ); ?></span><?php zui_icon( 'external-link' ); ?></a></li>
						</ul>
					</div>
				</div>
			</div>

			<?php /* Ecosystem / add-ons grid (same chrome as PRO License tab) */ ?>
			<div class="zui-lic-eco">
				<div class="zui-lic-eco__head">
					<div class="zui-lic-eco__heading">
						<span class="zui-lic-eco__icon"><?php zui_icon( 'layers' ); ?></span>
						<div>
							<h3><?php esc_html_e( 'Powerful Add-ons', 'woo-advanced-shipment-tracking' ); ?></h3>
							<p><?php esc_html_e( "Extend your store's capabilities with our ecosystem of complementary plugins.", 'woo-advanced-shipment-tracking' ); ?></p>
						</div>
					</div>
					<div class="zui-lic-eco__filters">
						<button type="button" class="zui-lic-eco__filter is-active" data-filter="all"><?php esc_html_e( 'All', 'woo-advanced-shipment-tracking' ); ?></button>
						<button type="button" class="zui-lic-eco__filter" data-filter="active"><?php esc_html_e( 'Active', 'woo-advanced-shipment-tracking' ); ?></button>
						<button type="button" class="zui-lic-eco__filter" data-filter="addons"><?php esc_html_e( 'Add-ons', 'woo-advanced-shipment-tracking' ); ?></button>
					</div>
				</div>

				<div class="zui-lic-eco__search">
					<span class="zui-lic-eco__search-icon"><?php zui_icon( 'search' ); ?></span>
					<input type="text" id="zui-lic-search" class="zui-input" placeholder="<?php esc_attr_e( 'Search add-ons…', 'woo-advanced-shipment-tracking' ); ?>" autocomplete="off">
				</div>

				<div class="zui-lic-eco__grid" id="zui-lic-grid">
					<?php
					foreach ( $ast_addons as $ast_addon ) :
						$ast_addon_active = is_plugin_active( $ast_addon['file'] );
						?>
						<div class="zui-card zui-lic-plugin" data-name="<?php echo esc_attr( $ast_addon['name'] ); ?>" data-active="<?php echo $ast_addon_active ? '1' : '0'; ?>">
							<div class="zui-lic-plugin__head">
								<span class="zui-lic-plugin__logo" style="background: <?php echo esc_attr( $ast_addon['tint'] ); ?>; color: <?php echo esc_attr( $ast_addon['accent'] ); ?>;">
									<?php if ( ! empty( $ast_addon['image'] ) ) : ?>
										<img src="<?php echo esc_url( $plugin_url ); ?>assets/images/<?php echo esc_attr( $ast_addon['image'] ); ?>" alt="<?php echo esc_attr( $ast_addon['name'] ); ?>">
									<?php else : ?>
										<?php zui_icon( $ast_addon['icon'] ); ?>
									<?php endif; ?>
								</span>
								<div class="zui-lic-plugin__id">
									<h4 class="zui-lic-plugin__name"><?php echo esc_html( $ast_addon['name'] ); ?></h4>
									<span class="zui-lic-plugin__type"><?php esc_html_e( 'WOO EXTENSION', 'woo-advanced-shipment-tracking' ); ?></span>
								</div>
							</div>
							<div class="zui-lic-plugin__body">
								<p class="zui-lic-plugin__desc"><?php echo esc_html( $ast_addon['desc'] ); ?></p>
								<div class="zui-lic-plugin__foot">
									<?php if ( $ast_addon_active ) : ?>
										<span class="zui-lic-plugin__active"><span class="zui-lic-plugin__dot"></span><?php esc_html_e( 'Active', 'woo-advanced-shipment-tracking' ); ?></span>
										<span class="zui-lic-plugin__stat"><?php esc_html_e( 'Active in this store', 'woo-advanced-shipment-tracking' ); ?></span>
									<?php else : ?>
										<a class="zui-lic-plugin__get" href="<?php echo esc_url( $ast_addon['url'] ); ?>" target="_blank" rel="noopener noreferrer"><span><?php esc_html_e( 'Learn More', 'woo-advanced-shipment-tracking' ); ?></span><?php zui_icon( 'external-link' ); ?></a>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
					<div class="zui-lic-eco__empty" id="zui-lic-empty" hidden><?php esc_html_e( 'No add-ons matched your search.', 'woo-advanced-shipment-tracking' ); ?></div>
				</div>
			</div>
		</div>

	</div>
</section>
