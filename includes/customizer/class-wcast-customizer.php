<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Advanced_Shipment_Tracking_Customizer {

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
    }
	
	/**
	 * Register the Customizer sections
	 */
	public function wcast_add_customizer_sections( $wp_customize ) {	
		
		$wp_customize->add_section( 'ast_tracking_general_section',
			array(
				'title' => __( 'Tracking info display', 'woo-advanced-shipment-tracking' ),
				'description' => '',				
			)
		);				
		
		$wp_customize->add_section( 'custom_order_status_email',
			array(
				'title' => __( 'Custom order status email', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking'  ),				
			)
		);				
	}
	
	/**
	 * add css and js for preview
	*/
	public function enqueue_preview_scripts() {		 
		
		wp_enqueue_script('wcast-email-preview-scripts', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/preview-scripts.js', array('jquery', 'customize-preview'), wc_advanced_shipment_tracking()->version, true);
		wp_enqueue_style('wcast-preview-styles', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/preview-styles.css', array(), wc_advanced_shipment_tracking()->version  );
		wp_localize_script('wcast-email-preview-scripts', 'wcast_preview', array(
			'site_title'   => $this->get_blogname(),
			'order_number' => get_theme_mod('wcast_email_preview_order_id'),			
		));
	}
	
	/**
	 * Get blog name formatted for emails.
	 *
	 * @return string
	 */
	public function get_blogname() {
		return wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}
	
	/**
	 * add css and js for customizer
	*/
	public function enqueue_customizer_scripts(){
		
		if(isset( $_REQUEST['wcast-customizer'] ) && '1' === $_REQUEST['wcast-customizer']){
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style('wcast-customizer-styles', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/customizer-styles.css', array(), wc_advanced_shipment_tracking()->version  );			
			wp_enqueue_script('wcast-customizer-scripts', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/customizer-scripts.js', array('jquery', 'customize-controls','wp-color-picker'), wc_advanced_shipment_tracking()->version, true);
			
			$email_type = ( isset($_REQUEST['order_status']) )  ? $_REQUEST['order_status'] : 'partially_shipped';
			$shipment_status = ( isset($_REQUEST['shipment_status']) )  ? $_REQUEST['shipment_status'] : 'in_transit';
		
			// Send variables to Javascript
			wp_localize_script('wcast-customizer-scripts', 'wcast_customizer', array(
				'ajax_url'                                => admin_url('admin-ajax.php'),
				'email_preview_url'        				  => $this->get_email_preview_url(),
				'partial_shipped_email_preview_url'       => $this->get_partial_shipped_email_preview_url(),
				'shipped_email_preview_url' 			  => $this->get_shipped_email_preview_url(),				
				'updated_tracking_email_preview_url' 	  => $this->get_updated_tracking_email_preview_url(),
				'email_type' 							  => $email_type,
				'shipment_status' 						  => $shipment_status,
				'tracking_preview_url'        			  => $this->get_tracking_preview_url(),
				'tracking_page_preview_url'        		  => $this->get_tracking_page_preview_url(),				
				'customer_failure_preview_url'  		  => $this->get_customer_failure_preview_url(),
				'customer_exception_preview_url'  		  => $this->get_customer_exception_preview_url(),
				'customer_intransit_preview_url'  		  => $this->get_customer_intransit_preview_url(),
				'customer_onhold_preview_url'  			  => $this->get_customer_onhold_preview_url(),
				'customer_outfordelivery_preview_url' 	  => $this->get_customer_outfordelivery_preview_url(),
				'customer_delivered_preview_url' 		  => $this->get_customer_delivered_preview_url(),
				'customer_returntosender_preview_url' 	  => $this->get_customer_returntosender_preview_url(),
				'customer_availableforpickup_preview_url' => $this->get_customer_availableforpickup_preview_url(),				
				'trigger_click'        					  => '#accordion-section-'.$_REQUEST['email'].' h3',	
				'customizer_title'        				  => 'Shipment Tracking',	
			));	

			wp_localize_script('wp-color-picker', 'wpColorPickerL10n', array(
				'clear'            => __( 'Clear' ),
				'clearAriaLabel'   => __( 'Clear color' ),
				'defaultString'    => __( 'Default' ),
				'defaultAriaLabel' => __( 'Select default color' ),
				'pick'             => __( 'Select Color' ),
				'defaultLabel'     => __( 'Color value' ),
			));	
		}
	}
	
	/**
	 * Get Customizer URL
	 *
	 */
	public function get_email_preview_url() {		
		return add_query_arg( array(
			'wcast-email-customizer-preview' => '1',
		), home_url( '' ) );
	}
	
	/**
	 * Get Customizer URL
	 *
	 */
	public function get_partial_shipped_email_preview_url() {		
		return add_query_arg( array(
			'wcast-partial-shipped-email-customizer-preview' => '1',
		), home_url( '' ) );
	}
	
	/**
	 * Get Customizer URL
	 *
	 */
	public function get_shipped_email_preview_url(){
		return add_query_arg( array(
			'wcast-shipped-email-customizer-preview' => '1',
		), home_url( '' ) );
	}
	
	/**
	 * Get Customizer URL
	 *
	 */
	public function get_custom_completed_email_preview_url() {		
		return add_query_arg( array(
			'wcast-custom-completed-email-customizer-preview' => '1',
		), home_url( '' ) );
	}
	
	/**
	 * Get Customizer URL
	 *
	 */
	public function get_updated_tracking_email_preview_url() {		
		return add_query_arg( array(
			'wcast-updated-tracking-email-customizer-preview' => '1',
		), home_url( '' ) );				 
	}
	
	/**
	 * Get Customizer URL
	 *
	 */
	public function get_tracking_preview_url() {		
		return add_query_arg( array(
			'wcast-tracking-preview' => '1',
		), home_url( '' ) );
	}	
	
	/**
	 * Get Tracking Page Preview URL
	 *
	 */
	public function get_tracking_page_preview_url() {		
		return add_query_arg( array(
			'action' => 'preview_tracking_page',
		), home_url( '' ) );				 
	}		
	
	/**
	 * Get Failuere Shipment status preview URL
	 *
	 */
	public function get_customer_failure_preview_url() {		
		return add_query_arg( array(
			'wcast-failure-email-customizer-preview' => '1',
		), home_url( '' ) );
	}
	
	/**
	 * Get Exception Shipment status preview URL
	 *
	 */
	public function get_customer_exception_preview_url(){
		return add_query_arg( array(
			'wcast-exception-email-customizer-preview' => '1',
		), home_url( '' ) );
	}
	
	/**
	 * Get Tracking page preview URL
	 *
	 */
	public function get_customer_intransit_preview_url() {		
		return add_query_arg( array(
			'wcast-intransit-email-customizer-preview' => '1',
		), home_url( '' ) );
	}
	
	/**
	 * Get Tracking page preview URL
	 *
	 */
	public function get_customer_onhold_preview_url() {		
		return add_query_arg( array(
			'wcast-onhold-email-customizer-preview' => '1',
		), home_url( '' ) );
	}
	
	/**
	 * Get Tracking page preview URL
	 *
	 */
	public function get_customer_outfordelivery_preview_url() {		
		return add_query_arg( array(
			'wcast-outfordelivery-email-customizer-preview' => '1',
		), home_url( '' ) );
	}
	
	/**
	 * Get Tracking page preview URL
	 *
	 */
	public function get_customer_delivered_preview_url() {		
		return add_query_arg( array(
			'wcast-delivered-email-customizer-preview' => '1',
		), home_url( '' ) );
	}
	
	/**
	 * Get Tracking page preview URL
	 *
	 */
	public function get_customer_returntosender_preview_url() {		
		return add_query_arg( array(
			'wcast-returntosender-email-customizer-preview' => '1',
		), home_url( '' ) );
	}
	
	/**
	 * Get Tracking page preview URL
	 *
	 */
	public function get_customer_availableforpickup_preview_url() {		
		return add_query_arg( array(
			'wcast-availableforpickup-email-customizer-preview' => '1',
		), home_url( '' ) );
	}		
	
	/**
     * Remove unrelated components
     *
     * @access public
     * @param array $components
     * @param object $wp_customize
     * @return array
     */
    public function remove_unrelated_components($components, $wp_customize)	{		
        // Iterate over components
        foreach ($components as $component_key => $component) {			
            // Check if current component is own component
            if ( ! $this->is_own_component( $component ) ) {
                unset($components[$component_key]);
            }
        }
				
        // Return remaining components
        return $components;
    }

    /**
     * Remove unrelated sections
     *
     * @access public
     * @param bool $active
     * @param object $section
     * @return bool
     */
    public function remove_unrelated_sections( $active, $section ) {
        // Check if current section is own section
        if ( ! $this->is_own_section( $section->id ) ) {
            return false;
        }

        // We can override $active completely since this runs only on own Customizer requests
        return true;
    }

	/**
	* Check if current component is own component
	*
	* @access public
	* @param string $component
	* @return bool
	*/
	public function is_own_component( $component ) {
		return false;
	}

	/**
	* Check if current section is own section
	*
	* @access public
	* @param string $key
	* @return bool
	*/
	public function is_own_section( $key ) {
				
		if ( $key === 'ast_tracking_general_section' || $key === 'custom_order_status_email' ) {
			return true;
		}

		// Section not found
		return false;
	}
	
	/*
	 * Unhook flatsome front end.
	 */
	public function unhook_flatsome() {
		// Unhook flatsome issue.
		wp_dequeue_style( 'flatsome-customizer-preview' );
		wp_dequeue_script( 'flatsome-customizer-frontend-js' );
	}
	
	/*
	 * Unhook Divi front end.
	 */
	public function unhook_divi() {
		// Divi Theme issue.
		remove_action( 'wp_footer', 'et_builder_get_modules_js_data' );
		remove_action( 'et_customizer_footer_preview', 'et_load_social_icons' );
	}
	
	/**
	 * Get Order Ids
	 *
	 * @access public
	 * @return array
	 */
	public function get_order_ids() {		
		$order_array = array();
		$order_array['mockup'] = __( 'Mockup order', 'woo-advanced-shipment-tracking' );
		
		$orders = wc_get_orders( array(
			'limit'        => 20,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'meta_key'     => '_wc_shipment_tracking_items', // The postmeta key field
			'meta_compare' => 'EXISTS', // The comparison argument
		));	
			
		foreach ( $orders as $order ) {				
				$ast = new WC_Advanced_Shipment_Tracking_Actions;
				$tracking_items = $ast->get_tracking_items( $order->get_id(), true );
				if($tracking_items){
					$order_array[ $order->get_id() ] = $order->get_id() . ' - ' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();					
				}				
			}
		return $order_array;
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
function wcast_customizer() {
	static $instance;

	if ( ! isset( $instance ) ) {		
		$instance = new wc_advanced_shipment_tracking_customizer();
	}

	return $instance;
}

/**
 * Register this class globally.
 *
 * Backward compatibility.
*/
wcast_customizer();