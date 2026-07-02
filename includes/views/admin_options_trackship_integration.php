<?php
/**
 * TrackShip integration tab — re-skinned to match the AST PRO TrackShip hero.
 *
 * Pure marketing landing: no AJAX, no form submit, no nonce. The only live
 * contracts are the `trackship_script` enqueue and the wordpress.org install
 * URL, both preserved verbatim. Markup mirrors the AST PRO dark hero design
 * (glow orbs + gradient TrackShip wordmark + 2×2 feature grid + testimonial
 * card) so the free and pro plugins read as siblings.
 *
 * @package wc_advanced_shipment_tracking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_script( 'trackship_script' );

if ( ! function_exists( 'zui_icon' ) ) {
	require_once dirname( __DIR__, 2 ) . '/assets/zui/icons.php';
}

$ast_ts_logo        = wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/images/trackship.png';
$ast_ts_install_url = 'https://wordpress.org/plugins/trackship-for-woocommerce/';
$ast_ts_learn_url   = 'https://www.trackship.com/';

$ast_ts_features = array(
	__( 'Branded tracking experience in your store', 'woo-advanced-shipment-tracking' ),
	__( 'Automate your post-shipping workflow', 'woo-advanced-shipment-tracking' ),
	__( 'Amazon-style post-purchase experience', 'woo-advanced-shipment-tracking' ),
	__( 'Reduce time spent on customer service', 'woo-advanced-shipment-tracking' ),
);

$ast_ts_cards = array(
	array(
		'icon'   => 'globe',
		'tint'   => '#eff6ff',
		'accent' => '#2563eb',
		'title'  => __( 'Real-time Tracking', 'woo-advanced-shipment-tracking' ),
		'desc'   => __( 'Automatically track shipments across 1010+ carriers worldwide with instant status updates.', 'woo-advanced-shipment-tracking' ),
	),
	array(
		'icon'   => 'mail',
		'tint'   => '#ede9fe',
		'accent' => '#7c3aed',
		'title'  => __( 'Automated Emails', 'woo-advanced-shipment-tracking' ),
		'desc'   => __( 'Trigger custom email notifications based on delivery status like Out for Delivery or Delivered.', 'woo-advanced-shipment-tracking' ),
	),
	array(
		'icon'   => 'clipboard-list',
		'tint'   => '#ecfdf5',
		'accent' => '#16a34a',
		'title'  => __( 'Tracking Page', 'woo-advanced-shipment-tracking' ),
		'desc'   => __( 'A professional, branded tracking page on your store to keep customers coming back.', 'woo-advanced-shipment-tracking' ),
	),
);
?>
<section class="tab_section" id="trackship-tab">
	<div class="ast-set-ts" id="ast-set-ts">

		<?php /* ───── Dark hero ───── */ ?>
		<div class="ast-set-ts-hero">
			<span class="ast-set-ts-hero__glow ast-set-ts-hero__glow--1" aria-hidden="true"></span>
			<span class="ast-set-ts-hero__glow ast-set-ts-hero__glow--2" aria-hidden="true"></span>

			<div class="ast-set-ts-hero__inner">

				<div class="ast-set-ts-hero__left">
					<span class="ast-set-ts-badge">
						<?php zui_icon( 'sparkles' ); ?><span><?php esc_html_e( 'Drive Repeat Business', 'woo-advanced-shipment-tracking' ); ?></span>
					</span>

					<h1 class="ast-set-ts-title ts_landing_header">
						<?php esc_html_e( 'Your Post-Shipping Autopilot with', 'woo-advanced-shipment-tracking' ); ?> <span class="ast-set-ts-title__grad">TrackShip</span>
					</h1>

					<p class="ast-set-ts-lead">
						<?php esc_html_e( 'Automate post-purchase shipment tracking across 1010+ carriers and deliver an Amazon-style branded experience right inside your store — without outsourcing your retention to plain carrier websites.', 'woo-advanced-shipment-tracking' ); ?>
					</p>

					<div class="ast-set-ts-features">
						<?php foreach ( $ast_ts_features as $ast_feat ) : ?>
							<div class="ast-set-ts-feature">
								<?php zui_icon( 'check-circle' ); ?><span><?php echo esc_html( $ast_feat ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>

					<div class="ast-set-ts-actions">
						<a class="ast-set-ts-btn ast-set-ts-btn--primary ts_install_btn" href="<?php echo esc_url( $ast_ts_install_url ); ?>" target="_blank" rel="noopener noreferrer">
							<?php zui_icon( 'download' ); ?>
							<span><?php esc_html_e( 'Install TrackShip for WooCommerce', 'woo-advanced-shipment-tracking' ); ?></span>
						</a>
						<a class="ast-set-ts-btn ast-set-ts-btn--ghost" href="<?php echo esc_url( $ast_ts_learn_url ); ?>" target="_blank" rel="noopener noreferrer">
							<span><?php esc_html_e( 'Learn more about TrackShip', 'woo-advanced-shipment-tracking' ); ?></span><?php zui_icon( 'arrow-right' ); ?>
						</a>
					</div>
				</div>

				<div class="ast-set-ts-hero__right">
					<div class="ast-set-ts-card">
						<div class="ast-set-ts-card__top">
							<div class="ast-set-ts-card__brand">
								<span class="ast-set-ts-card__logo"><img class="ts_landing_logo" src="<?php echo esc_url( $ast_ts_logo ); ?>" alt="TrackShip"></span>
								<div>
									<h4 class="ast-set-ts-card__name"><?php esc_html_e( 'TrackShip Engine', 'woo-advanced-shipment-tracking' ); ?></h4>
									<p class="ast-set-ts-card__sub"><?php esc_html_e( 'INTEGRATED SOLUTION', 'woo-advanced-shipment-tracking' ); ?></p>
								</div>
							</div>
							<span class="ast-set-ts-rating"><?php zui_icon( 'star' ); ?><span>4.9 / 5</span></span>
						</div>

						<p class="ast-set-ts-card__quote ts_landing_h3">
							<?php esc_html_e( '"Start for Free — 50 trackers every month, no credit card required. Branded tracking page, automated delivery emails and a dashboard your team will love."', 'woo-advanced-shipment-tracking' ); ?>
						</p>

						<div class="ast-set-ts-card__foot">
							<span class="ast-set-ts-card__nocard"><?php zui_icon( 'shield-check' ); ?><span><?php esc_html_e( 'No credit card required', 'woo-advanced-shipment-tracking' ); ?></span></span>
							<a class="ast-set-ts-card__more" href="<?php echo esc_url( $ast_ts_learn_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Visit trackship.com', 'woo-advanced-shipment-tracking' ); ?> <?php zui_icon( 'arrow-right' ); ?></a>
						</div>
					</div>
				</div>

			</div>
		</div>

		<?php /* ───── Feature cards row ───── */ ?>
		<div class="ast-set-ts-cards">
			<?php foreach ( $ast_ts_cards as $ast_card ) : ?>
				<div class="ast-set-ts-fcard">
					<span class="ast-set-ts-fcard__icon" style="background: <?php echo esc_attr( $ast_card['tint'] ); ?>; color: <?php echo esc_attr( $ast_card['accent'] ); ?>;">
						<?php zui_icon( $ast_card['icon'] ); ?>
					</span>
					<h4 class="ast-set-ts-fcard__title"><?php echo esc_html( $ast_card['title'] ); ?></h4>
					<p class="ast-set-ts-fcard__desc"><?php echo esc_html( $ast_card['desc'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
