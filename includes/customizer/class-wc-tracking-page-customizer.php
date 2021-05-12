<?php
/**
 * Customizer Setup and Custom Controls
 *
 */

/**
 * Adds the individual sections, settings, and controls to the theme customizer
 */
class ts_tracking_page_customizer {
	// Get our default values	
	private static $order_ids  = null;
	
	public function __construct() {
		// Get our Customizer defaults
		$this->defaults = $this->wcast_generate_defaults();		
		
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
		
		add_action( 'customize_preview_init', array( $this, 'enqueue_preview_scripts' ) );			
	}
	
	
	/**
	 * add css and js for preview
	*/	
	public function enqueue_preview_scripts() {
		 wp_enqueue_script('wcast-preview-scripts', wc_advanced_shipment_tracking()->plugin_dir_url() . '/assets/js/preview-scripts.js', array('jquery', 'customize-preview'), wc_advanced_shipment_tracking()->version, true);
		 wp_enqueue_style('wcast-preview-styles', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/preview-styles.css', array(), wc_advanced_shipment_tracking()->version  );
		 $preview_id     = get_theme_mod('wcast_email_preview_order_id');
		 wp_localize_script('wcast-preview-scripts', 'wcast_preview', array(
			'site_title'   => $this->get_blogname(),
			'order_number' => $preview_id,			
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
	 * Checks to see if we are opening our custom customizer preview
	 *
	 * @access public
	 * @return bool
	 */
	public function is_own_preview_request() {
		return isset( $_REQUEST['action'] ) && 'preview_tracking_page' === $_REQUEST['action'];
	}
	
	/**
	 * Checks to see if we are opening our custom customizer controls
	 *
	 * @access public
	 * @return bool
	 */
	public function is_own_customizer_request() {
		return isset( $_REQUEST['email'] ) && $_REQUEST['email'] === 'ast_tracking_page_section';
	}
	
	/**
	 * Get Customizer URL
	 *
	 */
	public function get_customizer_url( $email, $return_tab ) {	
		return add_query_arg( array(
			'trackship-customizer' => '1',
			'email' => $email,						
			'autofocus[section]' => 'ast_tracking_page_section',
			'url'                  => urlencode( add_query_arg( array( 'action' => 'preview_tracking_page' ), home_url( '/' ) ) ),
			'return'               => urlencode( $this->get_email_settings_page_url( $return_tab ) ),								
		), admin_url( 'customize.php' ) );
	}
	
	/**
	 * Get WooCommerce email settings page URL
	 *
	 * @access public
	 * @return string
	 */
	public function get_email_settings_page_url( $return_tab ) {
		return admin_url( 'admin.php?page=trackship-for-woocommerce&tab='.$return_tab );
	}
	
	/**
	 * code for initialize default value for customizer
	*/	
	public function wcast_generate_defaults() {
		$customizer_defaults = array(
			'wc_ast_select_tracking_page_layout' => 't_layout_1',			
			'wc_ast_select_border_color' => '#cccccc',			
			'wc_ast_select_bg_color' => '#fafafa',
			'wc_ast_hide_tracking_provider_image' => 0,
			'wc_ast_link_to_shipping_provider' => 1,
			'wc_ast_remove_trackship_branding' => 0,
			'wc_ast_hide_tracking_events' => 2, 
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
		
		$font_size_array[ '' ] = __( 'Select', 'woocommerce' );
		for ( $i = 10; $i <= 30; $i++ ) {
			$font_size_array[ $i ] = $i."px";
		}		
		
		$wp_customize->add_setting( 'wc_ast_select_tracking_page_layout',
			array(
				'default' => $this->defaults['wc_ast_select_tracking_page_layout'],
				'transport' => 'refresh',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( new AST_Dropdown_Select_Custom_Control( $wp_customize, 'wc_ast_select_tracking_page_layout',
			array(
				'label' => __( 'Widget Tracker Type', 'woo-advanced-shipment-tracking' ),						
				'section' => 'ast_tracking_page_section',
				'input_attrs' => array(
					'placeholder' => __( 'Widget Tracker Type', 'woo-advanced-shipment-tracking' ),
					'class' => '',
				),
				'choices' => array(
					't_layout_2' => __( 'Progress Bar', 'woo-advanced-shipment-tracking' ),
					't_layout_1' => __( 'Tracking Icons', 'woo-advanced-shipment-tracking' ),
				),
			)
		) );
				
		$wp_customize->add_setting( 'wc_ast_select_border_color',
			array(
				'default' => $this->defaults['wc_ast_select_border_color'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'wc_ast_select_border_color',
			array(
				'label' => __( 'Widget border color', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_page_section',
				'type' => 'color',				
			)
		);	
		
		$wp_customize->add_setting( 'wc_ast_select_bg_color',
			array(
				'default' => $this->defaults['wc_ast_select_bg_color'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'wc_ast_select_bg_color',
			array(
				'label' => __( 'Widget background color', 'woo-advanced-shipment-tracking' ),
				'section' => 'ast_tracking_page_section',
				'type' => 'color',				
			)
		);	
		
		$wp_customize->add_setting( 'wc_ast_hide_tracking_events',
			array(
				'default' => $this->defaults['wc_ast_hide_tracking_events'],
				'transport' => 'refresh',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( new AST_Dropdown_Select_Custom_Control( $wp_customize, 'wc_ast_hide_tracking_events',
			array(
				'label' => __( 'Events Display Type', 'woo-advanced-shipment-tracking' ),						
				'section' => 'ast_tracking_page_section',
				'input_attrs' => array(
					'placeholder' => __( 'Events Display Type', 'woo-advanced-shipment-tracking' ),
					'class' => '',
				),
				'choices' => array(
					0 => __( 'Show all Events', 'woo-advanced-shipment-tracking' ),
					1 => __( 'Hide tracking events', 'woo-advanced-shipment-tracking' ),					
					2 => __( 'Show last event & expand', 'woo-advanced-shipment-tracking' ),					
				),
			)
		) );
		
		$wp_customize->add_setting( 'wc_ast_hide_tracking_provider_image',
			array(
				'default' => $this->defaults['wc_ast_hide_tracking_provider_image'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'wc_ast_hide_tracking_provider_image',
			array(
				'label' => __( 'Hide the Shipping Provider logo', 'woo-advanced-shipment-tracking' ),				
				'section' => 'ast_tracking_page_section',
				'type' => 'checkbox',				
			)
		);
		
		$wp_customize->add_setting( 'wc_ast_link_to_shipping_provider',
			array(
				'default' => $this->defaults['wc_ast_link_to_shipping_provider'],
				'transport' => 'postMessage',
				'sanitize_callback' => '',
				'type' => 'option',
			)
		);
		$wp_customize->add_control( 'wc_ast_link_to_shipping_provider',
			array(
				'label' => __( 'Enable Tracking # link to Carrier', 'woo-advanced-shipment-tracking' ),				
				'section' => 'ast_tracking_page_section',
				'type' => 'checkbox',				
			)
		);				
	}	
		
}
/**
 * Initialise our Customizer settings
 */

$ts_tracking_page_customizer = new ts_tracking_page_customizer();