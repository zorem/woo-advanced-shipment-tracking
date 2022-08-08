<?php
/**
 * Handles email sending
 */
class WC_Advanced_Shipment_Tracking_Email_Manager {

	private static $instance;
	
	/**
	 * Constructor sets up actions
	 */
	public function __construct() {
		// template path	
		if (!defined( 'AST_TEMPLATE_PATH' ) ) {
			define( 'AST_TEMPLATE_PATH', SHIPMENT_TRACKING_PATH . '/templates/' );
		}	
		// hook for when order status is changed	
		add_filter( 'woocommerce_email_classes', array( $this, 'custom_init_emails' ) );	
		
		// Use our templates instead of woocommerce.
		add_filter( 'woocommerce_locate_template', array( $this, 'filter_locate_template' ), 100, 3 );

		add_filter( 'woocommerce_email_heading_customer_completed_order', array( $this, 'completed_email_heading' ), 10, 2 );
		add_filter( 'woocommerce_email_subject_customer_completed_order', array( $this, 'completed_email_subject' ), 10, 2 );
	}		    
	
	/**
	 * Code for include delivered email class
	 */
	public function custom_init_emails( $emails ) {
				
		// Include the email class file if it's not included already		
		$partial_shipped_status = get_option( 'wc_ast_status_partial_shipped', 0 );
		if ( true == $partial_shipped_status ) {
			if ( ! isset( $emails[ 'WC_Email_Customer_Partial_Shipped_Order' ] ) ) {
				$emails[ 'WC_Email_Customer_Partial_Shipped_Order' ] = include_once( 'emails/class-shipment-partial-shipped-email.php' );
			}
		}
		
		$updated_tracking_status = get_option( 'wc_ast_status_updated_tracking', 0 );
		if ( true == $updated_tracking_status ) {
			if ( ! isset( $emails[ 'WC_Email_Customer_Updated_Tracking_Order' ] ) ) {
				$emails[ 'WC_Email_Customer_Updated_Tracking_Order' ] = include_once( 'emails/class-shipment-updated-tracking-email.php' );
			}				
		}
		return $emails;
	}
	
	/**
	 * Code for format email content
	 */
	public function email_content( $email_content, $order_id, $order ) {	
	
		$order_number = $order->get_order_number();
		
		$customer_email = $order->get_billing_email();
		$first_name = $order->get_billing_first_name();
		$last_name = $order->get_billing_last_name();
		$company_name = $order->get_billing_company();
		$user = $order->get_user();
		
		if ( $user ) {
			$username = $user->user_login;
		}	
		
		$email_content = str_replace( '{customer_email}', $customer_email, $email_content );
		$email_content = str_replace( '{site_title}', $this->get_blogname(), $email_content );
		$email_content = str_replace( '{customer_first_name}', $first_name, $email_content );
		$email_content = str_replace( '{customer_last_name}', $last_name, $email_content );
		
		if ( isset( $company_name ) ) {
			$email_content = str_replace( '{customer_company_name}', $company_name, $email_content );	
		} else {
			$email_content = str_replace( '{customer_company_name}', '', $email_content );	
		}	 
		
		if ( isset( $username ) ) {
			$email_content = str_replace( '{customer_username}', $username, $email_content );
		} else {
			$email_content = str_replace( '{customer_username}', '', $email_content );
		}
		
		$email_content = str_replace( '{order_number}', $order_number, $email_content );		
		
		return $email_content;
	}

	/**
	 * Get blog name formatted for emails.
	 *
	 * @return string
	 */
	private function get_blogname() {
		return wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}
	
	/**
	 * Filter in custom email templates with priority to child themes
	 *
	 * @param string $template the email template file.
	 * @param string $template_name name of email template.
	 * @param string $template_path path to email template.	 
	 * @return string
	 */
	public function filter_locate_template( $template, $template_name, $template_path ) {

		// Make sure we are working with an email template.
		if ( ! in_array( 'emails', explode( '/', $template_name ) ) ) {
			return $template;
		}

		// clone template.
		$_template = $template;

		// Get the woocommerce template path if empty.
		if ( ! $template_path ) {
			global $woocommerce;
			$template_path = $woocommerce->template_url;
		}

		// Get our template path.
		$plugin_path = SHIPMENT_TRACKING_PATH . '/templates/';
		

		// Look within passed path within the theme - this is priority.
		$template = locate_template( array( $template_path . $template_name, $template_name ) );

		// If theme isn't trying to override get the template from this plugin, if it exists.
		if ( ! $template && file_exists( $plugin_path . $template_name ) ) {
			$template = $plugin_path . $template_name;
		}

		// else if we still don't have a template use default.
		if ( ! $template ) {
			$template = $_template;
		}					
		
		// Return template.
		return $template;
	}	

	public function completed_email_heading( $email_heading, $order ) {
		$first_name = $order->get_billing_first_name();
		$last_name = $order->get_billing_last_name();

		$email_heading = str_replace( '{customer_first_name}', $first_name, $email_heading );
		$email_heading = str_replace( '{customer_last_name}', $last_name, $email_heading );

		return $email_heading;
	}

	public function completed_email_subject( $email_subject, $order ) {
		$first_name = $order->get_billing_first_name();
		$last_name = $order->get_billing_last_name();

		$email_subject = str_replace( '{customer_first_name}', $first_name, $email_subject );
		$email_subject = str_replace( '{customer_last_name}', $last_name, $email_subject );

		return $email_subject;
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
function wc_advanced_shipment_tracking_email_class() {
	static $instance;

	if ( ! isset( $instance ) ) {
		$instance = new WC_Advanced_Shipment_Tracking_Email_Manager();
	}

	return $instance;
}

/**
 * Register this class globally.
 *
 * Backward compatibility.
*/
wc_advanced_shipment_tracking_email_class();
