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
	public function init() {
		//add_action( 'admin_init', array( $this, 'admin_notices_for_ast_pro' ) );		
	}

	public function admin_notices_for_ast_pro() {

		if ( ! class_exists( 'Notes' ) ) {
			return;
		}

		if ( class_exists( 'ast_pro' ) ) {
			return;
		}	
		
		$date_now = gmdate( 'Y-m-d' );
		
		$already_set = get_transient( 'ast_pro_1_year_wc_admin' );			
		
		set_transient( 'ast_pro_1_year_wc_admin', 'yes' );				
		
		$note_name = 'ast_pro_1_year_wc_admin_notice';
		//$data_store = WC_Data_Store::load( 'admin-note' );		
		
		// Otherwise, add the note
		$activated_time = current_time( 'timestamp', 0 );
		$activated_time_formatted = gmdate( 'F jS', $activated_time );
		// Instantiate a new Note object
		$note = new Note();
		$note->set_title( 'AST PRO is celebrating 1 Year!' );
		$note->set_content( 'Advanced Shipment Tracking Pro allows you to streamline & automate your fulfillment workflow, save time on your daily tasks and keep your customers happy and informed on their shipped orders. Use code ASTPRO20 to redeem your discount (valid by March 31th 2022)' );
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
			'settings', 'Upgrade Now', 'https://www.zorem.com/ast-pro/'
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
