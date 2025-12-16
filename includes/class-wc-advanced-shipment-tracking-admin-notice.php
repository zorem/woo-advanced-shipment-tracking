<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Advanced_Shipment_Tracking_Admin_Notice {

	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	 */
	private static $instance;
	
	/**
	 * Initialize the main plugin function
	*/
	public function __construct() {
		$this->init();
	}
	
	/**
	 * Get the class instance
	 *
	 * @return WC_Advanced_Shipment_Tracking_Admin_Notice
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	/*
	* init from parent mail class
	*/
	public function init() {

		add_action( 'admin_init', array( $this, 'ast_review_notice_ignore_388' ) );
		add_action( 'admin_notices', array( $this, 'ast_review_admin_notice_388' ) );

		// AST PRO Notice
		add_shortcode( 'ast_settings_admin_notice', array( $this, 'ast_settings_admin_notice' ) );

		add_action( 'admin_notices', array( $this, 'ast_free_trackship_notice_389' ) );	
		add_action( 'admin_init', array( $this, 'ast_free_trackship_notice_ignore_389' ) );
	}

	/**
	 * Display admin notice for missing shipping integration
	 */
	public function ast_settings_admin_notice() {
		ob_start();

		include 'views/admin_message_panel.php';
		
		return ob_get_clean();
	}

	/*
	* Dismiss admin notice for trackship
	*/
	public function ast_review_notice_ignore_388() {
		if ( isset( $_GET['ast-review-update-notice-388'] ) ) {
			if (isset($_GET['nonce'])) {
				$nonce = sanitize_text_field($_GET['nonce']);
				if (wp_verify_nonce($nonce, 'ast_review_dismiss_notice_388')) {
					update_option('ast_review_update_ignore_388', 'true');
				}
			}
		}
	}

	/*
	* Display admin notice on plugin install or update
	*/
	public function ast_review_admin_notice_388() {
		
		if ( get_option('ast_review_update_ignore_388') ) {
			return;
		}
		
		$nonce = wp_create_nonce('ast_review_dismiss_notice_388');
		$dismissable_url = esc_url(add_query_arg(['ast-review-update-notice-388' => 'true', 'nonce' => $nonce]));

		?>
		<style>		
		.wp-core-ui .notice.ast-review-dismissable-notice{
			position: relative;
			padding-right: 38px;
			border-left-color: #3b64d3;
		}
		.wp-core-ui .notice.ast-review-dismissable-notice h3{
			margin-bottom: 5px;
		} 
		.wp-core-ui .notice.ast-review-dismissable-notice a.notice-dismiss{
			padding: 9px;
			text-decoration: none;
		} 
		.wp-core-ui .button-primary.ast_review_notice_btn_388 {
			background: #3b64d3;
			color: #fff;
			border-color: #3b64d3;
			text-transform: uppercase;
			padding: 0 11px;
			font-size: 12px;
			height: 30px;
			line-height: 28px;
			margin: 5px 0 10px;
		}
		.ast-review-dismissable-notice strong{
			font-weight:bold;
		}
		</style>
		<div class="notice updated notice-success ast-review-dismissable-notice">
			<a href="<?php esc_html_e( $dismissable_url ); ?>" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></a>
			<h2>⭐ Enjoying AST? Leave Us a Review!</h2>
			
			<p>We hope <strong>Advanced Shipment Tracking</strong> has improved your order fulfillment workflow! Your feedback helps us grow and continue improving the plugin.</p>
			<p>If you love using AST, we’d really appreciate it if you could take a moment to leave us a 5-star review. It helps us keep improving and providing the best experience for you!</p>
			<p>👍 Support AST & Share Your Experience!</p>
			<a class="button-primary ast_review_notice_btn_388" target="blank" href="https://wordpress.org/support/plugin/woo-advanced-shipment-tracking/reviews/#new-post">LEAVE A REVIEW</a>
			<a class="button-primary ast_review_notice_btn_388" href="<?php esc_html_e( $dismissable_url ); ?>">Dismiss</a>
		</div>
		<?php
	}

	/*
	* Display admin notice on plugin install or update
	*/
	public function ast_free_trackship_notice_389() { 		
		
		$ts4wc_installed = ( function_exists( 'trackship_for_woocommerce' ) ) ? true : false;
		if ( $ts4wc_installed ) {
			return;
		}
		
		if ( get_option('ast_trackship_notice_ignore_389') ) {
			return;
		}	
		
		$nonce = wp_create_nonce('ast_pro_dismiss_notice_389');
		$dismissable_url = esc_url(add_query_arg(['ast-trackship-notice-389' => 'true', 'nonce' => $nonce]));
		?>		
		<style>		
		.wp-core-ui .notice.ast-dismissable-notice{
			position: relative;
			padding-right: 38px;
			border-left-color: #005B9A;
		}
		.wp-core-ui .notice.ast-dismissable-notice h3{
			margin-bottom: 5px;
		} 
		.wp-core-ui .notice.ast-dismissable-notice a.notice-dismiss{
			padding: 9px;
			text-decoration: none;
		} 
		.wp-core-ui .button-primary.ast_notice_btn {
			background: #005B9A;
			color: #fff;
			border-color: #005B9A;
			text-transform: uppercase;
			padding: 0 11px;
			font-size: 12px;
			height: 30px;
			line-height: 28px;
			margin: 5px 0 15px;
		}
		.ast-dismissable-notice strong{
			font-weight: bold;
		}
		.ast-dismissable-notice .ts-updated-notice{
			margin:1em 0 !important;
		}
		</style>
		<div class="notice updated notice-success ast-dismissable-notice">			
			<a href="<?php esc_html_e( $dismissable_url ); ?>" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></a>			
			<h3 class="ts-updated-notice">🚀 Provide seamless experience from Shipping to Delivery! 🎉</h3>
			<p><strong><a href="https://wordpress.org/plugins/trackship-for-woocommerce/" target="blank">TrackShip for WooCommerce</a></strong> automates tracking from shipping to delivery, saving you time and reducing customer service costs:</p>		
			<ul>
				<li>🔹 Automatically track all shipments and keep customers informed.</li>
				<li>🔹 Create a seamless branded tracking experience to boost engagement.</li>
				<li>🔹 Reduce "Where is my order?" support tickets with real-time updates.</li>
			</ul>
			<p>🎁 <strong>Special Offer:</strong> Get <strong>50% OFF</strong> your first three months with coupon code <strong>TRACKSHIP503M</strong>(Valid until december 31)</p>
			<a class="button-primary ast_notice_btn" target="blank" href="https://trackship.com/pricing/">👉 Upgrade Now & Save 50%</a>
			<a class="button-primary ast_notice_btn" href="<?php esc_html_e( $dismissable_url ); ?>">Dismiss</a>				
		</div>	
		<?php 				
	}

	/*
	* Dismiss admin notice for trackship
	*/
	public function ast_free_trackship_notice_ignore_389() {
		if ( isset( $_GET['ast-trackship-notice-389'] ) ) {
			if (isset($_GET['nonce']) && wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'ast_pro_dismiss_notice_389')) {
				update_option( 'ast_trackship_notice_ignore_389', 'true' );
			}	
		}
	}
}
