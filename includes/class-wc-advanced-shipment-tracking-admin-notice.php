<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Advanced_Shipment_Tracking_Admin_notice {

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
	 * @return WC_Advanced_Shipment_Tracking_Admin_notice
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
		add_action( 'admin_notices', array( $this, 'ast_pro_admin_notice' ) );	
		add_action( 'admin_init', array( $this, 'ast_pro_admin_notice_ignore' ) );	
	}		
	
	/*
	* Display admin notice on plugin install or update
	*/
	public function ast_pro_admin_notice() { 		
		
		if( class_exists( 'ast_pro' ) ) {
			return;
		}
		
		if ( get_option('ast_pro_admin_notice_ignore') ) {
			return;
		}	
		
		$dismissable_url = esc_url(  add_query_arg( 'ast-pro-ignore-notice', 'true' ) );
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
		</style>	
		<div class="notice updated notice-success ast-dismissable-notice">			
			<a href="<?php esc_html_e( $dismissable_url ); ?>" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></a>			
			<h3>Advanced Shipment Tracking Pro</h3>
			<p>We just released the Advanced Shipment Tracking Pro! Upgrade now and enjoy a 20% off early bird discount.</p>
			<p>To redeem your discount, use coupon code <strong>ASTPRO20</strong> (valid until March 31st) </p>
			<a class="button-primary ast_notice_btn" target="blank" href="https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/">Upgrade to AST Pro</a>
			<a class="button-primary ast_notice_btn" href="<?php esc_html_e( $dismissable_url ); ?>">No Thanks</a>				
		</div>
	<?php 		
	}	
	
	/*
	* Dismiss admin notice for trackship
	*/
	public function ast_pro_admin_notice_ignore() {
		if ( isset( $_GET['ast-pro-ignore-notice'] ) ) {
			update_option( 'ast_pro_admin_notice_ignore', 'true' );
		}
	}					
}