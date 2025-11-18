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
		
		add_action( 'admin_init', array( $this, 'ast_afws_notice_ignore_cb' ) );
		add_action( 'admin_init', array( $this, 'ast_review_notice_ignore_388' ) );

		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';

		if ( 'woocommerce-advanced-shipment-tracking' != $page && is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
			// Analytics for WooCommerce Subscriptions Notice
			add_action( 'admin_notices', array( $this, 'zorem_afws_admin_notice_387' ) );
		}

		add_action( 'admin_notices', array( $this, 'ast_review_admin_notice_388' ) );

		// AST PRO Notice
		add_shortcode( 'ast_settings_admin_notice', array( $this, 'ast_settings_admin_notice' ) );
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
	public function ast_afws_notice_ignore_cb() {
		if ( isset( $_GET['zorem-afws-update-notice-387'] ) ) {
			if (isset($_GET['nonce'])) {
				$nonce = sanitize_text_field($_GET['nonce']);
				if (wp_verify_nonce($nonce, 'zorem_afws_dismiss_notice_387')) {
					update_option('zorem_afws_update_ignore_387', 'true');
				}
			}
		}
	}

	/*
	* Display admin notice on plugin install or update
	*/
	public function zorem_afws_admin_notice_387() {
		
		if ( get_option('zorem_afws_update_ignore_387') ) {
			return;
		}
		
		$nonce = wp_create_nonce('zorem_afws_dismiss_notice_387');
		$dismissable_url = esc_url(add_query_arg(['zorem-afws-update-notice-387' => 'true', 'nonce' => $nonce]));

		?>
		<style>		
		.wp-core-ui .notice.zorem-afws-dismissable-notice{
			position: relative;
			padding-right: 38px;
			border-left-color: #3b64d3;
		}
		.wp-core-ui .notice.zorem-afws-dismissable-notice h3{
			margin-bottom: 5px;
		} 
		.wp-core-ui .notice.zorem-afws-dismissable-notice a.notice-dismiss{
			padding: 9px;
			text-decoration: none;
		} 
		.wp-core-ui .button-primary.zorem_afws_notice_btn_387 {
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
		.zorem-afws-dismissable-notice strong{
			font-weight:bold;
		}
		</style>
		<div class="notice updated notice-success zorem-afws-dismissable-notice">
			<a href="<?php esc_html_e( $dismissable_url ); ?>" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></a>
			<h2>🚀 Introducing Analytics for WooCommerce Subscriptions</h2>
			<p>Get powerful insights with <a href="https://woocommerce.com/products/analytics-for-woocommerce-subscriptions/">Analytics for WooCommerce Subscriptions</a> — the all-in-one dashboard to track signups, renewals, cancellations, and recurring revenue.</p>
			
			<p>Discover which products and customers drive the most value, reduce churn, and grow your subscription income with data-driven decisions.</p>
			<a class="button-primary zorem_afws_notice_btn_387" target="blank" href="https://woocommerce.com/products/analytics-for-woocommerce-subscriptions/">👉 Learn More on WooCommerce.com</a>
			<a class="button-primary zorem_afws_notice_btn_387" href="<?php esc_html_e( $dismissable_url ); ?>">Dismiss</a>
		</div>
		<?php
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
}
