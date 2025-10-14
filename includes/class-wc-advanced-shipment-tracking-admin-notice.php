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
		
		add_action( 'admin_init', array( $this, 'ast_pro_notice_ignore_cb' ) );

		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';

		if ( 'woocommerce-advanced-shipment-tracking' != $page ) {
			// Zorem Return For WooCommerce PRO Notice
			add_action( 'admin_notices', array( $this, 'zorem_ast_pro_admin_notice_386' ) );

		}

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
	public function ast_pro_notice_ignore_cb() {
		if ( isset( $_GET['zorem-ast-pro-update-notice-386'] ) ) {
			if (isset($_GET['nonce'])) {
				$nonce = sanitize_text_field($_GET['nonce']);
				if (wp_verify_nonce($nonce, 'zorem_ast_pro_dismiss_notice_386')) {
					update_option('zorem_ast_pro_update_ignore_386', 'true');
				}
			}
		}
	}

	/*
	* Display admin notice on plugin install or update
	*/
	public function zorem_ast_pro_admin_notice_386() {
		
		if ( get_option('zorem_ast_pro_update_ignore_386') ) {
			return;
		}
		
		$nonce = wp_create_nonce('zorem_ast_pro_dismiss_notice_386');
		$dismissable_url = esc_url(add_query_arg(['zorem-ast-pro-update-notice-386' => 'true', 'nonce' => $nonce]));

		?>
		<style>		
		.wp-core-ui .notice.zorem-ast-pro-dismissable-notice{
			position: relative;
			padding-right: 38px;
			border-left-color: #3b64d3;
		}
		.wp-core-ui .notice.zorem-ast-pro-dismissable-notice h3{
			margin-bottom: 5px;
		} 
		.wp-core-ui .notice.zorem-ast-pro-dismissable-notice a.notice-dismiss{
			padding: 9px;
			text-decoration: none;
		} 
		.wp-core-ui .button-primary.zorem_ast_pro_notice_btn_386 {
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
		.zorem-ast-pro-dismissable-notice strong{
			font-weight:bold;
		}
		</style>
		<div class="notice updated notice-success zorem-ast-pro-dismissable-notice">
			<a href="<?php esc_html_e( $dismissable_url ); ?>" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></a>
			<h2>🚀 Ready to Take the Manual Work Out of Shipping? Meet AST PRO!</h2>
			<p>Say goodbye to copy-pasting tracking numbers. With Advanced Shipment Tracking PRO, you can:</p>
			<ul>
				<li>✅ Integrating with 50+ shipping solutions like ShipStation, WooCommerce Shipping, and more</li>
				<li>✅ Automatically importing tracking info from your shipping tools</li>
				<li>✅ Update orders and notify customers in a single click</li>
				<li>✅ Manage everything from a unified dashboard in WooCommerce</li>
			</ul>
			<p><strong>🎁 New customer offer:</strong> Use code <strong>ASTPRO20</strong> at checkout to get <strong>20% OFF</strong></p>
			<a class="button-primary zorem_ast_pro_notice_btn_386" target="blank" href="https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/">Upgrade to AST PRO</a>
			<a class="button-primary zorem_ast_pro_notice_btn_386" href="<?php esc_html_e( $dismissable_url ); ?>">Dismiss</a>
		</div>
		<?php
	}
}
