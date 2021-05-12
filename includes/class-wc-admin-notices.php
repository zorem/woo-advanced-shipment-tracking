<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_AST_Admin_Notices_Under_WC_Admin {

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
	public function init(){										
		add_action('init', array( $this, 'admin_notices_for_ast_pro' ) );		
	}

	public function admin_notices_for_ast_pro() {

		if( class_exists( 'ast_pro' ) ) {
			return;
		}	
		
		if ( ! class_exists( 'Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes' ) ) {
			return;
		}
		
		$already_set = get_transient( 'ast_pro_wc_admin' );
		
		if( 'yes' == $already_set ){
			return;
		}	
		
		set_transient( 'ast_pro_wc_admin', 'yes' );				
		
		$note_name = 'ast_pro_wc_admin_notice';
		$data_store = WC_Data_Store::load( 'admin-note' );		
		
		// Otherwise, add the note
		$activated_time = current_time( 'timestamp', 0 );
		$activated_time_formatted = date( 'F jS', $activated_time );
		$note = new Automattic\WooCommerce\Admin\Notes\WC_Admin_Note();
		$note->set_title( 'Advanced Shipment Tracking PRO' );
		$note->set_content( 'We just released the Advanced Shipment Tracking Pro! Upgrade now and enjoy a 20% off early bird discount. To redeem your discount, use coupon code ASTPRO20 (valid until March 31st)' );
		$note->set_content_data( (object) array(
			'getting_started'     => true,
			'activated'           => $activated_time,
			'activated_formatted' => $activated_time_formatted,
		) );
		$note->set_type( 'info' );		
		$note->set_image('');
		$note->set_name( $note_name );
		$note->set_source( 'AST Pro' );		
		$note->set_image('');
		// This example has two actions. A note can have 0 or 1 as well.
		$note->add_action(
			'settings', 'Upgrade to AST Pro', 'https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/'
		);		
		$note->save();
	}				
}

/**
 * Returns an instance of zorem_woocommerce_advanced_shipment_tracking.
 *
 * @since 1.6.5
 * @version 1.6.5
 *
 * @return zorem_woocommerce_advanced_shipment_tracking
*/
function WC_AST_Admin_Notices_Under_WC_Admin() {
	static $instance;

	if ( ! isset( $instance ) ) {		
		$instance = new WC_AST_Admin_Notices_Under_WC_Admin();
	}

	return $instance;
}

/**
 * Register this class globally.
 *
 * Backward compatibility.
*/
WC_AST_Admin_Notices_Under_WC_Admin();