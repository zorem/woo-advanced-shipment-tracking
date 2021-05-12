<?php
/**
 * Customizer Setup and Custom Controls 
 */

/**
 * Adds the individual sections, settings, and controls to the theme customizer
 */
class wcast_availableforpickup_customizer_email {
	// Get our default values
	public function __construct() {
		
		// Get our Customizer defaults
		$this->defaults = $this->wcast_generate_defaults();
		
		$wc_ast_api_key = get_option('wc_ast_api_key');
		if(!$wc_ast_api_key)return;
		
		// Register our sample default controls
		add_action( 'customize_register', array( $this, 'wcast_register_sample_default_controls' ) );
		
		// Only proceed if this is own request.
		if ( ! $this->is_own_customizer_request() && ! $this->is_own_preview_request() )return;
							
		// Register our sections
		add_action( 'customize_register', array( ts_customizer(), 'wcast_add_customizer_sections' ) );	
		
		// Remove unrelated components.
		add_filter( 'customize_loaded_components', array( ts_customizer(), 'remove_unrelated_components' ), 99, 2 );

		// Remove unrelated sections.
		add_filter( 'customize_section_active', array( ts_customizer(), 'remove_unrelated_sections' ), 10, 2 );	
		
		// Unhook divi front end.
		add_action( 'woomail_footer', array( ts_customizer(), 'unhook_divi' ), 10 );

		// Unhook Flatsome js
		add_action( 'customize_preview_init', array( ts_customizer(), 'unhook_flatsome' ), 50  );
		
		add_filter( 'customize_controls_enqueue_scripts', array( ts_customizer(), 'enqueue_customizer_scripts' ) );				
		
		add_action( 'parse_request', array( $this, 'set_up_preview' ) );	
		
		add_action( 'customize_preview_init', array( ts_customizer(), 'enqueue_preview_scripts' ) );	

	}		
	
	/**
	 * Checks to see if we are opening our custom customizer preview
	 *
	 * @access public
	 * @return bool
	 */
	public function is_own_preview_request() {
		return isset( $_REQUEST['wcast-availableforpickup-email-customizer-preview'] ) && '1' === $_REQUEST['wcast-availableforpickup-email-customizer-preview'];
	}
	
	/**
	 * Checks to see if we are opening our custom customizer controls
	 *
	 * @access public
	 * @return bool
	 */
	public function is_own_customizer_request() {
		return isset( $_REQUEST['email'] ) && $_REQUEST['email'] === 'trackship_shipment_status_email';
	}
	
	/**
	 * code for initialize default value for customizer
	*/
	public function wcast_generate_defaults() {		
		$customizer_defaults = array(			
			'wcast_availableforpickup_email_subject' => __( 'Your order #{order_number} is available for pickup', 'woo-advanced-shipment-tracking' ),
			'wcast_availableforpickup_email_heading' => __( 'Available For Pickup', 'woo-advanced-shipment-tracking' ),
			'wcast_availableforpickup_email_content' => __( "Hi there. we thought you'd like to know that your recent order from {site_title} is available for pickup.", 'woo-advanced-shipment-tracking' ),				
			'wcast_enable_availableforpickup_email'  => '',
			'wcast_availableforpickup_email_to'  => 	'{customer_email}',
			'wcast_availableforpickup_show_tracking_details' => '',
			'wcast_availableforpickup_show_order_details' => 1,	
			'wcast_availableforpickup_hide_shipping_item_price' => 1,				
			'wcast_availableforpickup_show_shipping_address' => 1,
			'wcast_availableforpickup_email_code_block' => '',
		);

		return apply_filters( 'ast_customizer_defaults', $customizer_defaults );
	}
	
	/**
	 * Register our sample default controls
	 */
	public function wcast_register_sample_default_controls( $wp_customize ) {		
		/**
		* Load all our Customizer Custom Controls
		*/
		require_once trailingslashit( dirname(__FILE__) ) . 'custom-controls.php';
				
				
		// Display Shipment Provider image/thumbnail
		$wp_customize->add_setting( 'wcast_availableforpickup_email_settings[wcast_enable_availableforpickup_email]',
			array(
				'default' => $this->defaults['wcast_enable_availableforpickup_email'],
				'transport' => 'postMessage',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'wcast_availableforpickup_email_settings[wcast_enable_availableforpickup_email]',
			array(
				'label' => __( 'Enable Available For Pickup email', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'trackship_shipment_status_email',
				'type' => 'checkbox',
				'active_callback' => array( $this, 'active_callback' ),
			)
		);			
			
		// Header Text		
		$wp_customize->add_setting( 'wcast_availableforpickup_email_settings[wcast_availableforpickup_email_to]',
			array(
				'default' => $this->defaults['wcast_availableforpickup_email_to'],
				'transport' => 'postMessage',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'wcast_availableforpickup_email_settings[wcast_availableforpickup_email_to]',
			array(
				'label' => __( 'Recipient(s)', 'woocommerce' ),
				'description' => esc_html__( 'Use the {customer_email} placeholder, you can add comma separated email addresses.', 'woocommerce' ),
				'section' => 'trackship_shipment_status_email',
				'type' => 'text',
				'input_attrs' => array(
					'class' => '',
					'style' => '',
					'placeholder' => __( 'E.g. {customer.email}, admin@example.org', 'woo-advanced-shipment-tracking' ),
				),
				'active_callback' => array( $this, 'active_callback' ),
			)
		);		
		
		// Header Text		
		$wp_customize->add_setting( 'wcast_availableforpickup_email_settings[wcast_availableforpickup_email_subject]',
			array(
				'default' => $this->defaults['wcast_availableforpickup_email_subject'],
				'transport' => 'postMessage',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'wcast_availableforpickup_email_settings[wcast_availableforpickup_email_subject]',
			array(
				'label' => __( 'Email Subject', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( 'Available variables:', 'woo-advanced-shipment-tracking' ) . ' {site_title}, {order_number}',
				'section' => 'trackship_shipment_status_email',
				'type' => 'text',
				'input_attrs' => array(
					'class' => '',
					'style' => '',
					'placeholder' => __( $this->defaults['wcast_availableforpickup_email_subject'], 'woo-advanced-shipment-tracking' ),
				),
				'active_callback' => array( $this, 'active_callback' ),
			)
		);
		
		// Header Text		
		$wp_customize->add_setting( 'wcast_availableforpickup_email_settings[wcast_availableforpickup_email_heading]',
			array(
				'default' => $this->defaults['wcast_availableforpickup_email_heading'],
				'transport' => 'refresh',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'wcast_availableforpickup_email_settings[wcast_availableforpickup_email_heading]',
			array(
				'label' => __( 'Email heading', 'woocommerce' ),
				'description' => esc_html__( 'Available variables:', 'woo-advanced-shipment-tracking' ) . ' {site_title}, {order_number}',
				'section' => 'trackship_shipment_status_email',
				'type' => 'text',
				'input_attrs' => array(
					'class' => '',
					'style' => '',
					'placeholder' => __( $this->defaults['wcast_availableforpickup_email_heading'], 'woo-advanced-shipment-tracking' ),
				),
				'active_callback' => array( $this, 'active_callback' ),
			)
		);
		
		// Test of TinyMCE control
		$wp_customize->add_setting( 'wcast_availableforpickup_email_settings[wcast_availableforpickup_email_content]',
			array(
				'default' => $this->defaults['wcast_availableforpickup_email_content'],
				'transport' => 'refresh',
				'type'  => 'option',
				'sanitize_callback' => 'wp_kses_post'
			)
		);
		$wp_customize->add_control( new AST_TinyMCE_Custom_control( $wp_customize, 'wcast_availableforpickup_email_settings[wcast_availableforpickup_email_content]',
			array(
				'label' => __( 'Email content', 'woo-advanced-shipment-tracking' ),
				'description' => __( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'trackship_shipment_status_email',
				'input_attrs' => array(
					'toolbar1' => 'bold italic bullist numlist alignleft aligncenter alignright link',
					'mediaButtons' => true,
					'placeholder' => __( $this->defaults['wcast_availableforpickup_email_content'], 'woo-advanced-shipment-tracking' ),
				),
				'active_callback' => array( $this, 'active_callback' ),
			)
		) );				
		
		$wp_customize->add_setting( 'wcast_availableforpickup_email_settings[wcast_availableforpickup_email_code_block]',
			array(
				'default' => $this->defaults['wcast_availableforpickup_email_code_block'],
				'transport' => 'postMessage',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( new WP_Customize_codeinfoblock_Control( $wp_customize, 'wcast_availableforpickup_email_settings[wcast_availableforpickup_email_code_block]',
			array(
				'label' => __( 'Available variables:', 'woo-advanced-shipment-tracking' ),
				'description' => '<code>{site_title}<br>{customer_email}<br>{customer_first_name}<br>{customer_last_name}<br>{customer_company_name}<br>{customer_username}<br>{order_number}<br>{est_delivery_date}</code>',
				'section' => 'trackship_shipment_status_email',	
				'active_callback' => array( $this, 'active_callback' ),					
			)
		) );
		
		// Display Shipment Provider image/thumbnail
		$wp_customize->add_setting( 'wcast_availableforpickup_email_settings[wcast_availableforpickup_show_order_details]',
			array(
				'default' => $this->defaults['wcast_availableforpickup_show_order_details'],
				'transport' => 'refresh',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'wcast_availableforpickup_email_settings[wcast_availableforpickup_show_order_details]',
			array(
				'label' => __( 'Display the Shipping items', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'trackship_shipment_status_email',
				'type' => 'checkbox',
				'active_callback' => array( $this, 'active_callback' ),
			)
		);	

		// Display Shipment Provider image/thumbnail
		$wp_customize->add_setting( 'wcast_availableforpickup_email_settings[wcast_availableforpickup_hide_shipping_item_price]',
			array(
				'default' => $this->defaults['wcast_availableforpickup_hide_shipping_item_price'],
				'transport' => 'refresh',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'wcast_availableforpickup_email_settings[wcast_availableforpickup_hide_shipping_item_price]',
			array(
				'label' => __( 'Hide Shipping Items Price', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'trackship_shipment_status_email',
				'type' => 'checkbox',
				'active_callback' => array( $this, 'active_callback_only_show_order_details' ),	
			)
		);		
		
		// Display Shipment Provider image/thumbnail
		$wp_customize->add_setting( 'wcast_availableforpickup_email_settings[wcast_availableforpickup_show_shipping_address]',
			array(
				'default' => $this->defaults['wcast_availableforpickup_show_shipping_address'],
				'transport' => 'refresh',
				'type'  => 'option',
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'wcast_availableforpickup_email_settings[wcast_availableforpickup_show_shipping_address]',
			array(
				'label' => __( 'Display the shipping address', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( '', 'woo-advanced-shipment-tracking' ),
				'section' => 'trackship_shipment_status_email',
				'type' => 'checkbox',
				'active_callback' => array( $this, 'active_callback' ),
			)
		);
		
		$wp_customize->add_setting( 'wcast_availableforpickup_email_settings[wcast_availableforpickup_analytics_link]',
			array(
				'default' => '',
				'transport' => 'refresh',
				'type'  => 'option',				
				'sanitize_callback' => ''
			)
		);
		$wp_customize->add_control( 'wcast_availableforpickup_email_settings[wcast_availableforpickup_analytics_link]',
			array(
				'label' => __( 'Google Analytics link tracking', 'woo-advanced-shipment-tracking' ),
				'description' => esc_html__( 'This will be appended to URL in the email content', 'woo-advanced-shipment-tracking' ),
				'section' => 'trackship_shipment_status_email',
				'type' => 'text',
				'input_attrs' => array(
					'class' => '',
					'style' => '',
					'placeholder' => __( '', 'woo-advanced-shipment-tracking' ),
				),
				'active_callback' => array( $this, 'active_callback' ),
			)
		);				
	}	
	
	public function active_callback(){
		return ( $this->is_own_preview_request() ) ? true : false ;	
	}	
	
	public function active_callback_only_show_order_details(){
		$ast = new WC_Advanced_Shipment_Tracking_Actions;	
		$show_order_details = $ast->get_checkbox_option_value_from_array('wcast_availableforpickup_email_settings','wcast_availableforpickup_show_order_details',$this->defaults['wcast_availableforpickup_show_order_details']);		
		return ( $this->is_own_preview_request() && $show_order_details ) ? true : false ;		
	}
	
	/**
	 * Set up preview
	 *
	 * @access public
	 * @return void
	 */
	public function set_up_preview() {
		// Make sure this is own preview request.
		if ( ! $this->is_own_preview_request() )return;
		include wc_advanced_shipment_tracking()->get_plugin_path() . '/includes/customizer/preview/availableforpickup_preview.php';exit;			
	}
	
	/**
	 * code for preview of available for pickup email
	*/
	public function preview_availableforpickup_email(){
		
		$preview_id     = get_theme_mod('wcast_intransit_email_preview_order_id');
		$ast = new WC_Advanced_Shipment_Tracking_Actions;
		
		$email_heading = $ast->get_option_value_from_array('wcast_availableforpickup_email_settings','wcast_availableforpickup_email_heading',$this->defaults['wcast_availableforpickup_email_heading']);			
		$email_content = $ast->get_option_value_from_array('wcast_availableforpickup_email_settings','wcast_availableforpickup_email_content',$this->defaults['wcast_availableforpickup_email_content']);
		$wcast_show_order_details = $ast->get_checkbox_option_value_from_array('wcast_availableforpickup_email_settings','wcast_availableforpickup_show_order_details',$this->defaults['wcast_availableforpickup_show_order_details']);	
		$hide_shipping_item_price = $ast->get_checkbox_option_value_from_array('wcast_availableforpickup_email_settings','wcast_availableforpickup_hide_shipping_item_price',$this->defaults['wcast_availableforpickup_hide_shipping_item_price']);	
		$wcast_show_shipping_address = $ast->get_checkbox_option_value_from_array('wcast_availableforpickup_email_settings','wcast_availableforpickup_show_shipping_address',$this->defaults['wcast_availableforpickup_show_shipping_address']);
		
		if($preview_id == '' || $preview_id == 'mockup') {
			$content = '<div style="padding: 35px 40px; background-color: white;">' . __( 'Please select order to preview.', 'woo-advanced-shipment-tracking' ) . '</div>';							
			echo $content;
			return;
		}		
		
		$order = wc_get_order( $preview_id );
		
		if( !$order ){
			$content = '<div style="padding: 35px 40px; background-color: white;">' . __( 'Please select order to preview.', 'woo-advanced-shipment-tracking' ) . '</div>';							
			echo $content;
			return;
		}
		
		// get the preview email subject
		$email_heading = wc_trackship_email_manager()->email_heading( $email_heading, $preview_id, $order );		
		$message = wc_trackship_email_manager()->email_content( $email_content, $preview_id, $order );
		
		$wcast_availableforpickup_analytics_link = $ast->get_option_value_from_array('wcast_availableforpickup_email_settings','wcast_availableforpickup_analytics_link','');	
		if($wcast_availableforpickup_analytics_link){	
			$regex = '#(<a href=")([^"]*)("[^>]*?>)#i';
			$message = preg_replace_callback($regex, array( $this, '_appendCampaignToString'), $message);	
		}
		
		$tracking_items = $ast->get_tracking_items( $preview_id, true );
		$message .= $ast->tracking_info_template( $preview_id, $tracking_items, 'available_for_pickup' );					
		if( $wcast_show_order_details )$message .= $ast->order_details_template( $order, $hide_shipping_item_price );	
		if( $wcast_show_shipping_address )$message .= $ast->order_shipping_details_template( $order );	
		
		$mailer = WC()->mailer();
		// create a new email
		$email = new WC_Email();		
		
		// wrap the content with the email template and then add styles
		echo apply_filters( 'woocommerce_mail_content', $email->style_inline( $mailer->wrap_message( $email_heading, $message ) ) );					
	}

	/**
	 * code for append analytics link in email content
	*/
	public function _appendCampaignToString($match){
		$ast = new WC_Advanced_Shipment_Tracking_Actions;
		$wcast_availableforpickup_analytics_link = $ast->get_option_value_from_array('wcast_availableforpickup_email_settings','wcast_availableforpickup_analytics_link','');
		
		$url = $match[2];
		if (strpos($url, '?') === false) {
			$url .= '?';
		}
		$url .= $wcast_availableforpickup_analytics_link;
		return $match[1].$url.$match[3];
	}	
}
/**
 * Initialise our Customizer settings
 */
new wcast_availableforpickup_customizer_email();