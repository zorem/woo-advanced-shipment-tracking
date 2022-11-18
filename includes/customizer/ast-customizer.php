<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ast_Customizer {

	// WooCommerce email classes.
	public static $email_types_class_names  = array(		
		'completed'          => 'WC_Email_Customer_Completed_Order',		
		
		//AST custom status
		'partial_shipped'					=> 'WC_Email_Customer_Partial_Shipped_Order',				
	);
	
	public static $email_types_order_status = array(
		
		'completed'          => 'completed',		
		
		//AST custom status
		'partial_shipped'					=> 'partial-shipped',		
	);

	/**
	 * Get the class instance
	 *
	 * @since  1.0
	 * @return Ast_Customizer
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	*/
	private static $instance;
	
	/**
	 * Initialize the main plugin function
	 * 
	 * @since  1.0
	*/
	public function __construct() {

		// Get our Customizer defaults
		$this->defaults = $this->ast_generate_defaults();	

		$this->init();
	}
	
	/*
	 * init function
	 *
	 * @since  1.0
	*/
	public function init() {

		//adding hooks
		add_action( 'admin_menu', array( $this, 'register_woocommerce_menu' ), 99 );
		
		//save of settings hook
		add_action( 'wp_ajax_save_ast_email_settings', array( $this, 'customizer_save_email_settings' ) );
		
		add_action( 'wp_ajax_ast_email_preview', array( $this, 'get_preview_email' ) );
		
		//load javascript in admin
		add_action('admin_enqueue_scripts', array( $this, 'customizer_enqueue_scripts' ) );
		
		//load CSS/javascript in admin
		add_action('admin_footer', array( $this, 'admin_footer_enqueue_scripts' ) );
		
	}
	
	/*
	 * Admin Menu add function
	 *
	 * @since  2.4
	 * WC sub menu 
	*/
	public function register_woocommerce_menu() {
		add_menu_page( __( 'AST Customizer', 'woo-advanced-shipment-tracking' ), __( 'AST Customizer', 'woo-advanced-shipment-tracking' ), 'manage_woocommerce', 'ast_customizer', array( $this, 'settingsPage' ) );
	}
	
	/*
	 * Add admin javascript
	 *
	 * @since  2.4
	 * WC sub menu 
	*/
	public function admin_footer_enqueue_scripts() {
		?>
		<style type="text/css">
			#toplevel_page_ast_customizer { display: none !important; }
		</style>
		<?php
	}
	
	/*
	 * callback for settingsPage
	 *
	 * @since  2.4
	*/
	public function settingsPage() {

		$page = isset( $_GET['page'] ) ? wc_clean( $_GET['page'] ) : '' ;
		
		// Add condition for css & js include for admin page  
		if ( 'ast_customizer' != $page ) {
			return;
		}
	
		$email_type = !empty( $_GET['email_type'] ) ? sanitize_text_field($_GET['email_type']) : 'completed';
		$iframe_url = $this->get_email_preview_url( $email_type ) ;
		
		$rename_shipped_status = get_option( 'wc_ast_status_shipped', 1 );				
		
		$completed_label = ( $rename_shipped_status ) ? esc_html__( 'Shipped', 'woo-advanced-shipment-tracking' ) : esc_html__( 'Completed', 'woocommerce' );		
		
		$email_types = array(
			'completed'		  => $completed_label,
			'partial_shipped' => esc_html__( 'Partially Shipped', 'woo-advanced-shipment-tracking' ),		
		);

		// When load this page will not show adminba
		?>
		
		<style type="text/css">
			#wpcontent, #wpbody-content, .wp-toolbar {margin: 0 !important;padding: 0 !important;}
			#adminmenuback, #adminmenuwrap, #wpadminbar, #wpfooter, .notice, div.error, div.updated { display: none !important; }
		</style>
		
		<script type="text/javascript" id="zoremmail-onload">
			jQuery(document).ready( function() {
				jQuery('#adminmenuback, #adminmenuwrap, #wpadminbar, #wpfooter').remove();
			});
		</script>

		<section class="zoremmail-layout zoremmail-layout-has-sider">
			<form method="post" id="zoremmail_email_options" class="zoremmail_email_options" style="display: contents;">
				<section class="zoremmail-layout zoremmail-layout-has-content zoremmail-layout-sider">
					
					<aside class="zoremmail-layout-slider-header">
						<button type="button" class="wordpress-to-back" tabindex="0">
							<a class="zoremmail-back-wordpress-link" href="<?php echo esc_url( admin_url() . 'admin.php?page=woocommerce-advanced-shipment-tracking' ); ?>"><span class="zoremmail-back-wordpress-title dashicons dashicons-no-alt"></span></a>
						</button>
						<span class="wcts-save-content" style="float: right;">
							<button name="save" class="button-primary button-trackship btn_large woocommerce-save-button" type="submit" value="Saved" disabled><?php esc_html_e( 'Saved', 'woo-advanced-shipment-tracking' ); ?></button>
							<?php wp_nonce_field( 'customizer_email_options_actions', 'customizer_email_options_nonce_field' ); ?>
							<input type="hidden" name="action" value="save_ast_email_settings">
						</span>
						
					</aside>
					
					<aside class="zoremmail-layout-slider-content">
						<div class="zoremmail-layout-sider-container">
							<?php $this->get_html( $this->customize_setting_options_func() ); ?>
						</div>
					</aside>
					
					<aside class="zoremmail-layout-content-collapse">
						<div class="zoremmail-layout-content-media">
							<a data-width="600px" data-iframe-width="100%" class="last-checked"><span class="dashicons dashicons-desktop"></span></a>
							<a data-width="600px" data-iframe-width="610px"><span class="dashicons dashicons-tablet"></span></a>
							<a data-width="400px" data-iframe-width="410px"><span class="dashicons dashicons-smartphone"></span></a>
						</div>
					</aside>
					
				</section>

				<section class="zoremmail-layout zoremmail-layout-has-content">
					<div class="zoremmail-layout-content-header">
						<div class="header-panel options-content">
							<span class="header_select_box header_orderStatus" style="display:none;">	
								<select name="orderStatus" id="orderStatus" class="select" >
									<?php foreach ( $email_types as $key => $status ) { ?>
											<option value="<?php echo esc_html($key); ?>" <?php echo $email_type == $key ? 'selected' : ''; ?>><?php echo esc_html(wc_get_order_status_name($status)); ?></option>
									<?php } ?>
								</select>
							</span>
							<?php $preview_id = get_option( 'order_preview', 'mockup' ); ?>
							<span class="header_select_box header_order_preview">	
								<select name="order_preview" id="order_preview" class="select" >
									<?php foreach ( $this->get_order_ids() as $key => $label ) { ?>
											<option value="<?php echo esc_html( $key ); ?>" <?php echo $preview_id == $key ? 'selected' : ''; ?>><?php echo esc_html( $label ); ?></option>
									<?php } ?>
								</select>
							</span>
														
						</div>
					</div>
					<div class="zoremmail-layout-content-container">
						<section class="zoremmail-layout-content-preview customize-preview">
							<div id="overlay"></div>
							<iframe id="email_preview" src="<?php esc_attr_e( $iframe_url ); ?>" style="width: 100%;height: 100%;display: block;margin: 0 auto;"></iframe>
						</section>						
					</div>
				</section>
			</form>
		</section>
		<?php
	}
	
	/*
	* Add admin javascript
	*
	* @since 1.0
	*/	
	public function customizer_enqueue_scripts() {
		
		$page = isset( $_GET['page'] ) ? wc_clean( $_GET['page'] ) : '' ;
		
		// Add condition for css & js include for admin page  
		if ( 'ast_customizer' != $page ) {
			return;
		}
		
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
		wp_enqueue_style( 'woocommerce_admin_styles');
		wp_register_script( 'select2', WC()->plugin_url() . '/assets/js/select2/select2.full' . $suffix . '.js', array( 'jquery' ), '4.0.3' );
		wp_enqueue_script( 'select2');
		
		// Add tiptip js and css file		
		wp_enqueue_style( 'ast-customizer', plugin_dir_url(__FILE__) . 'assets/Customizer.css', array(), wc_advanced_shipment_tracking()->version );
		wp_enqueue_script( 'ast-customizer', plugin_dir_url(__FILE__) . 'assets/Customizer.js', array( 'jquery', 'wp-util', 'wp-color-picker','jquery-tiptip' ), wc_advanced_shipment_tracking()->version, true );
		
		wp_localize_script('ast-customizer', 'ast_customizer', array(
			'plugin_dir_url'		=> wc_advanced_shipment_tracking()->plugin_dir_url(),
			'site_title'			=> get_bloginfo( 'name' ),
			'order_number'			=> 1,
			'customer_first_name'	=> 'Sherlock',
			'customer_last_name'	=> 'Holmes',
			'customer_company_name' => 'Detectives Ltd.',
			'customer_username'		=> 'sher_lock',
			'customer_email'		=> 'sherlock@holmes.co.uk',
			'est_delivery_date'		=> '2021-07-30 15:28:02',
			'email_iframe_url'		=> add_query_arg( array( 'action'	=> 'ast_email_preview' ), admin_url( 'admin-ajax.php' ) ),			
		));
		
	}
	
	/*
	* save settings function
	*/
	public function customizer_save_email_settings() {			
		
		if ( !current_user_can( 'manage_options' ) ) {
			echo json_encode( array('permission' => 'false') );
			die();
		}

		if ( ! empty( $_POST ) && check_admin_referer( 'customizer_email_options_actions', 'customizer_email_options_nonce_field' ) ) {

			//data to be saved			
			
			$settings = $this->customize_setting_options_func();						
			
			foreach ( $settings as $key => $val ) {
				if ( isset( $_POST[ $key ] ) ) {
					if ( isset( $val['type'] ) && 'textarea' == $val['type'] && !isset( $val['option_key'] ) ) {
						$option_data = get_option( $val['option_name'], array() );
						$option_data[$key] = wp_kses_post( wp_unslash( $_POST[$key] ) );
						update_option( $val['option_name'], $option_data );
					} elseif ( isset( $val['option_type'] ) && 'key' == $val['option_type'] ) {
						update_option( $key, wc_clean( $_POST[$key] ) );					
					} elseif ( isset( $val['option_type'] ) && 'array' == $val['option_type'] ) {					
						if ( isset( $val['option_key'] ) ) {
							if ( isset( $val['type'] ) && 'textarea' == $val['type'] ) {
								$option_data = get_option( $val['option_name'], array() );
								$option_data[$val['option_key']] = wp_kses_post( wp_unslash( $_POST[ $key ] ) );	
								update_option( $val['option_name'], $option_data );
							} else {	
								$option_data = get_option( $val['option_name'], array() );
								$option_data[$val['option_key']] = wc_clean( wp_unslash( $_POST[ $key ] ) );	
								update_option( $val['option_name'], $option_data );								
							}						
						} else {
							$option_data = get_option( $val['option_name'], array() );
							$option_data[$key] = wc_clean( wp_unslash( $_POST[ $key ] ) );
							update_option( $val['option_name'], $option_data );						
						}
					}
				}
			}
			
			echo json_encode( array('success' => 'true') );
			die();
	
		}
	}		
	
	/**
	 * Code for initialize default value for customizer
	*/	
	public function ast_generate_defaults() {

		$customizer_defaults = array(
			'wcast_preview_order_id' => 'mockup',
			'display_tracking_info_at' => 'before_order',
			'header_text_change' => '',
			'additional_header_text' => '',
			'fluid_table_layout' => 2,
			'fluid_display_shipped_header' => 1,
			'fluid_tracker_type' => 'progress_bar',
			'fluid_table_border_color' => '#e0e0e0',
			'fluid_table_border_radius' => 3,
			'fluid_table_background_color' => '#fafafa',
			'fluid_table_padding' => '15',
			'fluid_hide_provider_image' => 0,
			'fluid_hide_shipping_date'	=> 0,
			'fluid_button_text' => __( 'Track Your Order', 'woo-advanced-shipment-tracking' ),
			'fluid_button_background_color' => '#005b9a',
			'fluid_button_font_color' => '#fff',
			'fluid_button_size' => 'normal',
			'fluid_button_padding' => '10',
			'fluid_button_radius' => '3',
			'fluid_button_expand' => 1,
			'completed_subject' => __( 'Your {site_title} order is now complete', 'woocommerce' ),
			'completed_heading' => __( 'Your order is Complete!', 'woocommerce' ),
			'completed_email_content' => __( 'We have finished processing your order.', 'woocommerce' ),						
			'partial_shipped_subject' => __( 'Your {site_title} order is now partially shipped', 'woo-advanced-shipment-tracking' ),
			'partial_shipped_heading' => __( 'Your Order is Partially Shipped', 'woo-advanced-shipment-tracking' ),
			'partial_shipped_email_content' => __( "Hi there. we thought you'd like to know that your recent order from {site_title} has been partially shipped.", 'woo-advanced-shipment-tracking' ),			
		);

		return apply_filters( 'ast_customizer_defaults', $customizer_defaults );
	}

	public function customize_setting_options_func() {
		
		$email_type = isset( $_GET['email_type'] ) ? sanitize_text_field($_GET['email_type']) : get_option( 'orderStatus', 'completed' );	
		
		$email_settings = get_option('woocommerce_customer_' . $email_type . '_order_settings', array());		
		
		$email_iframe_url = $this->get_email_preview_url( $email_type );

		$tracking_info_settings = get_option( 'tracking_info_settings', array() );
		
		$iframe_url = $this->get_email_preview_url( $email_type ) ;
		
		$rename_shipped_status = get_option( 'wc_ast_status_shipped', 1 );		
		
		$completed_label = ( $rename_shipped_status ) ? esc_html__( 'Shipped', 'woo-advanced-shipment-tracking' ) : esc_html__( 'Completed', 'woocommerce' );	
		
		$fluid_display_shipped_header_default = ( ( isset( $tracking_info_settings['fluid_display_shipped_header'] ) )  && ( !empty($tracking_info_settings['fluid_display_shipped_header']) || 0 == $tracking_info_settings['fluid_display_shipped_header'] ) ) ? $tracking_info_settings['fluid_display_shipped_header'] : $this->defaults['fluid_display_shipped_header'];
		
		$email_types = array(
			'completed'		  => $completed_label,					
		);
		
		$wc_ast_status_partial_shipped = get_option( 'wc_ast_status_partial_shipped', 0 );
		if ( $wc_ast_status_partial_shipped ) {
			$email_types['partial_shipped'] = esc_html__( 'Partially Shipped', 'woo-advanced-shipment-tracking' );
		}

		$settings = array(
			
			//order_preivew
			'order_preview'	=> array(
				'id'	=> 'order_preview',
				'class' => '',
				'label' => '',
				'title'	=> '',
				'type'	=> 'text',
				'option_type' => 'key',
				'show'	=> false,
			),
			
			//panels
			'email_design'	=> array(
				'id'	=> 'email_design',
				'class' => 'options_panel',
				'label' => esc_html__( 'Tracking Widget', 'woo-advanced-shipment-tracking' ),
				'title'	=> esc_html__( 'Tracking Widget', 'woo-advanced-shipment-tracking' ),
				'type'	=> 'panel',
				'iframe_url' => $email_iframe_url,
				'show'	=> true,
			),
			'email_content'	=> array(
				'id'	=> 'email_content',
				'class' => 'options_panel',
				'label'	=> esc_html__( 'Email Content', 'woo-advanced-shipment-tracking' ),
				'title'	=> esc_html__( 'Email Content', 'woo-advanced-shipment-tracking' ),
				'type'	=> 'panel',
				'iframe_url' => $email_iframe_url,
				'show'	=> true,
			),

			//sub-panels
			'back_section1' => array(
				'id'     => 'email_content',
				'title'       => esc_html__( 'Email Content', 'woo-advanced-shipment-tracking' ),
				'type'     => 'sub-panel-heading',
				'parent'	=> 'email_content',
				'show'     => true,
				'class' => 'sub_options_panel',
			),
			'back_section2' => array(
				'id'     => 'email_design',
				'title'       => esc_html__( 'Tracking Widget', 'woo-advanced-shipment-tracking' ),
				'type'     => 'sub-panel-heading',
				'parent'	=> 'email_design',
				'show'     => true,
				'class' => 'sub_options_panel',
			),

			'widget_header' => array(
				'id'	=> 'widget_header',
				'title'	=> esc_html__( 'Widget Header', 'woo-advanced-shipment-tracking' ),
				'parent_label' => esc_html__( 'Tracking Widget', 'woo-advanced-shipment-tracking' ),
				'type'	=> 'sub-panel',
				'parent'=> 'email_design',
				'show'	=> true,
				'class' => 'sub_options_panel',
			),
			'widget_style' => array(
				'id'	=> 'widget_style',
				'title'	=> esc_html__( 'Widget Style', 'woo-advanced-shipment-tracking' ),
				'parent_label' => esc_html__( 'Tracking Widget', 'woo-advanced-shipment-tracking' ),
				'type'	=> 'sub-panel',
				'parent'=> 'email_design',
				'show'	=> true,
				'class' => 'sub_options_panel',
			),
			'tracking_button' => array(
				'id'	=> 'tracking_button',
				'title'	=> esc_html__( 'Tracking Button', 'woo-advanced-shipment-tracking' ),
				'parent_label' => esc_html__( 'Tracking Widget', 'woo-advanced-shipment-tracking' ),
				'type'	=> 'sub-panel',
				'parent'=> 'email_design',
				'show'	=> true,
				'class' => 'sub_options_panel',
			),	
			'email_settings' => array(
				'id'	=> 'email_settings',
				'title'	=> esc_html__( 'Email Content', 'woo-advanced-shipment-tracking' ),
				'parent_label' => esc_html__( 'Email Content', 'woo-advanced-shipment-tracking' ),
				'type'	=> 'sub-panel',
				'parent'=> 'email_content',
				'show'	=> true,
				'class' => 'sub_options_panel',
			),	
								
			//section
			'heading1' => array(
				'id'     => 'widget_header',
				'title'       => esc_html__( 'Widget Header', 'woo-advanced-shipment-tracking' ),
				'type'     => 'section',
				'parent'	=> 'widget_header',
				'show'     => true,
				'class' => 'email_design_first_section ',
			),
			
			'hide_trackig_header' => array(
				'title'    => esc_html__( 'Hide tracking header', 'woo-advanced-shipment-tracking' ),
				'default'  => !empty($tracking_info_settings['hide_trackig_header']) ? $tracking_info_settings['hide_trackig_header'] : 0,
				'type'     => 'checkbox',
				'show'     => true,
				'class'	   => 'hide_trackig_header',
				'option_name' => 'tracking_info_settings',
				'option_type' => 'array',
			),
			'header_text_change' => array(
				'title'    => esc_html__( 'Tracking header text', 'woo-advanced-shipment-tracking' ),
				'default'  => !empty($tracking_info_settings['header_text_change']) ? $tracking_info_settings['header_text_change'] : $this->defaults['header_text_change'],
				'placeholder' => '',
				'type'     => 'text',
				'show'     => true,
				'class'	   => 'header_text_change',
				'option_name' => 'tracking_info_settings',
				'option_type' => 'array',
			),
			'additional_header_text' => array(
				'title'    => esc_html__( 'Additional text after header', 'woo-advanced-shipment-tracking' ),
				'default'  => !empty($tracking_info_settings['additional_header_text']) ? $tracking_info_settings['additional_header_text'] : $this->defaults['additional_header_text'],
				'placeholder' => '',
				'type'     => 'textarea',
				'show'     => true,
				'class'	   => 'additional_header_text',
				'option_name' => 'tracking_info_settings',
				'option_type' => 'array',
			),	
			'heading3' => array(
				'id'     => 'widget_style',
				'title'       => esc_html__( 'Widget Style', 'woo-advanced-shipment-tracking' ),
				'type'     => 'section',
				'parent'	=> 'widget_style',
				'show'     => true,				
			),
			'fluid_display_shipped_header' => array(
				'title'    => esc_html__( 'Display shipment status section', 'woo-advanced-shipment-tracking' ),
				'default'  => $fluid_display_shipped_header_default,
				'type'     => 'checkbox',
				'show'     => true,
				'class'	   => 'fluid_display_shipped_header',
				'option_name' => 'tracking_info_settings',
				'option_type' => 'array',
			),
			'fluid_tracker_type' => array(
				'title'    => esc_html__( 'Tracker type', 'woo-advanced-shipment-tracking' ),
				'type'     => 'select',
				'default'  => !empty($tracking_info_settings['fluid_tracker_type']) ? $tracking_info_settings['fluid_tracker_type'] : $this->defaults['fluid_tracker_type'],
				'show'     => true,
				'options'  => array(								
					'progress_bar'		=> 'Progress bar',
					'icons'				=> 'Icons',
					'single_icons'		=> 'Single icon',	
				),
				'class'		  => 'fluid_tracker_type',	
				'option_name' => 'tracking_info_settings',
				'option_type' => 'array',
			),				
			'fluid_table_background_color' => array(
				'title'    => esc_html__( 'Background color', 'woo-advanced-shipment-tracking' ),
				'type'     => 'color',
				'default'  => !empty($tracking_info_settings['fluid_table_background_color']) ? $tracking_info_settings['fluid_table_background_color'] : $this->defaults['fluid_table_background_color'],
				'show'     => true,
				'option_name' => 'tracking_info_settings',
				'option_type' => 'array',
			),
			'fluid_table_border_color' => array(
				'title'    => esc_html__( 'Border color', 'woo-advanced-shipment-tracking' ),
				'type'     => 'color',
				'default'  => !empty($tracking_info_settings['fluid_table_border_color']) ? $tracking_info_settings['fluid_table_border_color'] : $this->defaults['fluid_table_border_color'],
				'show'     => true,
				'option_name' => 'tracking_info_settings',
				'option_type' => 'array',
			),
			'fluid_table_border_radius' => array(
				'title'    => esc_html__( 'Border radius', 'woo-advanced-shipment-tracking' ),
				'type'     => 'range',
				'min'	   => 0,	
				'max'	   => 20,
				'default'  => !empty($tracking_info_settings['fluid_table_border_radius']) ? $tracking_info_settings['fluid_table_border_radius'] : $this->defaults['fluid_table_border_radius'],
				'show'     => true,
				'option_name' => 'tracking_info_settings',
				'option_type' => 'array',
			),
			'fluid_hide_provider_image' => array(
				'title'    => esc_html__( 'Hide shipping provider image', 'woo-advanced-shipment-tracking' ),
				'default'  => !empty($tracking_info_settings['fluid_hide_provider_image']) ? $tracking_info_settings['fluid_hide_provider_image'] : $this->defaults['fluid_hide_provider_image'],
				'type'     => 'checkbox',
				'show'     => true,
				'class'	   => 'fluid_hide_provider_image',
				'option_name' => 'tracking_info_settings',
				'option_type' => 'array',
			),
			'heading4' => array(
				'id'     => 'tracking_button',
				'title'       => esc_html__( 'Tracking Button', 'woo-advanced-shipment-tracking' ),
				'type'     => 'section',
				'parent'	=> 'tracking_button',
				'show'     => true,				
			),
			'fluid_button_text' => array(
				'title'    => esc_html__( 'Button text', 'woo-advanced-shipment-tracking' ),
				'default'  => !empty($tracking_info_settings['fluid_button_text']) ? $tracking_info_settings['fluid_button_text'] : $this->defaults['fluid_button_text'],
				'placeholder' => '',
				'type'     => 'text',
				'show'     => true,
				'class'	   => 'fluid_button_text',
				'option_name' => 'tracking_info_settings',
				'option_type' => 'array',
			),
			'fluid_button_size' => array(
				'title'    => esc_html__( 'Button size', 'woo-advanced-shipment-tracking' ),
				'type'     => 'radio_butoon',
				'default'  => !empty($tracking_info_settings['fluid_button_size']) ? $tracking_info_settings['fluid_button_size'] : $this->defaults['fluid_button_size'],
				'show'     => true,
				'choices'  => array(
					'normal' => __( 'Normal', 'woo-advanced-shipment-tracking' ),
					'large' => __( 'Large', 'woo-advanced-shipment-tracking'  )	
				),
				'option_name' => 'tracking_info_settings',
				'option_type' => 'array',
			),
			'fluid_button_background_color' => array(
				'title'    => esc_html__( 'Button color', 'woo-advanced-shipment-tracking' ),
				'type'     => 'color',
				'default'  => !empty($tracking_info_settings['fluid_button_background_color']) ? $tracking_info_settings['fluid_button_background_color'] : $this->defaults['fluid_button_background_color'],
				'show'     => true,
				'option_name' => 'tracking_info_settings',
				'option_type' => 'array',
			),	
			'fluid_button_font_color' => array(
				'title'    => esc_html__( 'Button font color', 'woo-advanced-shipment-tracking' ),
				'type'     => 'color',
				'default'  => !empty($tracking_info_settings['fluid_button_font_color']) ? $tracking_info_settings['fluid_button_font_color'] : $this->defaults['fluid_button_font_color'],
				'show'     => true,
				'option_name' => 'tracking_info_settings',
				'option_type' => 'array',
			),
			'fluid_button_radius' => array(
				'title'    => esc_html__( 'Button radius', 'woo-advanced-shipment-tracking' ),
				'type'     => 'range',
				'min'	   => 1,	
				'max'	   => 20,
				'default'  => !empty($tracking_info_settings['fluid_button_radius']) ? $tracking_info_settings['fluid_button_radius'] : $this->defaults['fluid_button_radius'],
				'show'     => true,
				'option_name' => 'tracking_info_settings',
				'option_type' => 'array',
			),	
		);
		
		//sections			
		$all_statuses = array(
			'completed'		=> esc_html__( 'Completed', 'woocommerce' ),
			'partial_shipped'			=> esc_html__( 'Partially Shipped', 'woo-advanced-shipment-tracking' ),			
		);
		
		$settings[ 'heading5' ] = array(
			'id'	=> 'email_settings',
			'class' => 'email_content_first_section ',
			'title'	=> esc_html__( 'Email Content', 'woo-advanced-shipment-tracking' ),
			'type'	=> 'section',
			'parent'=> 'email_settings',
			'show'	=> true,
		);

		$settings[ 'order_status' ] = array(
			'title'    => esc_html__( 'Email type', 'woo-advanced-shipment-tracking' ),
			'type'     => 'select',
			'default'  => $email_type,
			'show'     => true,
			'options'  => $email_types,
			'option_name' => 'order_status',
			'option_type' => 'array',
		);

		foreach ( $all_statuses as $key => $value ) {
			
			$email_settings = get_option('woocommerce_customer_' . $key . '_order_settings', array());			
			
			$settings[ $key . '_subject' ] = array(
				'title'    => esc_html__( 'Email Subject', 'woo-advanced-shipment-tracking' ),
				'default'  => !empty( $email_settings['subject'] ) ? stripslashes( $email_settings['subject'] ) : $this->defaults[ $key . '_subject' ],
				'placeholder' => $this->defaults[$key . '_subject'],
				'type'     => 'text',
				'show'     => true,
				'option_name' => 'woocommerce_customer_' . $key . '_order_settings',
				'option_key'=> 'subject',
				'option_type' => 'array',
				'class'		=> $key . '_sub_menu all_status_submenu subject',
			);		
			$settings[ $key . '_heading' ] = array(
				'title'    => esc_html__( 'Email heading', 'woo-advanced-shipment-tracking' ),
				'default'  => !empty( $email_settings['heading'] ) ? stripslashes( $email_settings['heading'] ) : $this->defaults[ $key . '_heading' ],
				'placeholder' => $this->defaults[$key . '_heading'],
				'type'     => 'text',
				'show'     => true,
				'class'	=> 'heading',
				'option_name' => 'woocommerce_customer_' . $key . '_order_settings',
				'option_key'=> 'heading',
				'option_type' => 'array',
				'class'		=> $key . '_sub_menu all_status_submenu heading',
			);
			$settings[ $key . '_email_content' ] = array(
				'title'    => esc_html__( 'Email content', 'woo-advanced-shipment-tracking' ),
				'default'  => !empty( $email_settings['wcast_' . $key . '_email_content']) ? stripslashes( $email_settings['wcast_' . $key . '_email_content'] ) : $this->defaults[$key . '_email_content'],
				'placeholder' => $this->defaults[$key . '_email_content'],
				'type'     => 'textarea',
				'show'     => true,
				'class'	=> 'heading',
				'option_name' => 'woocommerce_customer_' . $key . '_order_settings',
				'option_key'=> 'wcast_' . $key . '_email_content',
				'option_type' => 'array',
				'class'		=> $key . '_sub_menu all_status_submenu heading',
			);
			$settings[ $key . '_codeinfoblock' ] = array(
				'title'    => esc_html__( 'Available Placeholders:', 'woo-advanced-shipment-tracking' ),
				'default'  => '<code>{customer_first_name}<br>{customer_last_name}<br>{site_title}<br>{order_number}</code>',
				'type'     => 'codeinfo',
				'show'     => true,
				'class'		=> $key . '_sub_menu all_status_submenu',
			);					
		}

		$settings = apply_filters( 'customizer_email_options_array' , $settings );
		
		return $settings; 

	}
	
	/*
	* Get html of fields
	*/
	public function get_html( $arrays ) {
		
		echo '<ul class="zoremmail-panels">';
		?>
		<div class="customize-section-title">
			<h3>
				<span class="customize-action-default">
					<?php esc_html_e( 'You are customizing', 'woo-advanced-shipment-tracking' ); ?>
				</span>
				<?php esc_html_e( 'Email Customizer', 'woo-advanced-shipment-tracking' ); ?>
			</h3>
		</div>
		<?php
		foreach ( (array) $arrays as $id => $array ) {
			
			if ( isset($array['show']) && true != $array['show'] ) {
				continue; 
			}

			if ( isset($array['type']) && 'panel' == $array['type'] ) {
				?>
				<li id="<?php isset($array['id']) ? esc_attr_e($array['id']) : ''; ?>" data-label="<?php isset($array['label']) ? esc_attr_e($array['label']) : ''; ?>" data-iframe_url="<?php isset($array['iframe_url']) ? esc_attr_e($array['iframe_url']) : ''; ?>" class="zoremmail-panel-title <?php isset($array['class']) ? esc_attr_e($array['class']) : ''; ?>">
					<span><?php isset($array['title']) ? esc_html_e($array['title']) : ''; ?></span>
					<span class="dashicons dashicons-arrow-right-alt2"></span>
				</li>
				<?php
			}
		}
		echo '</ul>';

		echo '<ul class="zoremmail-sub-panels" style="display:none;">';

		foreach ( (array) $arrays as $id => $array ) {
			
			if ( isset($array['show']) && true != $array['show'] ) {
				continue; 
			}
				
			if ( isset($array['type']) && 'sub-panel-heading' == $array['type'] ) {
				?>
				<li data-id="<?php isset($array['parent']) ? esc_attr_e($array['parent']) : ''; ?>" class="zoremmail-sub-panel-heading <?php isset($array['class']) ? esc_attr_e($array['class']) : ''; ?> <?php isset($array['parent']) ? esc_attr_e($array['parent']) : ''; ?>">
					<div class="customize-section-title">
						<button type="button" class="customize-section-back" tabindex="0">
							<span class="screen-reader-text">Back</span>
						</button>
						<h3>
							<span class="customize-action-default">
								<?php esc_html_e( 'You are customizing', 'wooflow-email-customizer' ); ?>
							</span>
							<span class="customize-action-changed"></span>
							<span class="sub_heading"><?php esc_html_e( $array['title'] ); ?></span>
						</h3>
					</div>
				</li>
				<?php
			}

			if ( isset($array['type']) && 'sub-panel' == $array['type'] ) {
				?>
				<li id="<?php isset($array['id']) ? esc_attr_e($array['id']) : ''; ?>"  data-type="<?php isset($array['parent']) ? esc_html_e($array['parent']) : ''; ?>" data-label="<?php isset($array['title']) ? esc_html_e($array['title']) : ''; ?>" class="zoremmail-sub-panel-title <?php isset($array['class']) ? esc_attr_e($array['class']) : ''; ?> <?php isset($array['parent']) ? esc_attr_e($array['parent']) : ''; ?>">
					<span><?php isset($array['title']) ? esc_html_e($array['title']) : ''; ?></span>
					<span class="dashicons dashicons-arrow-right-alt2"></span>
				</li>
				<?php
			}
		}
		echo '</ul>';

		foreach ( (array) $arrays as $id => $array ) {

			if ( isset($array['show']) && true != $array['show'] ) {
				continue; 
			}

			if ( isset($array['type']) && 'panel' == $array['type'] ) {
				continue; 
			}
			
			if ( isset($array['type']) && 'sub-panel-heading' == $array['type'] ) {
				continue; 
			}

			if ( isset($array['type']) && 'sub-panel' == $array['type'] ) {
				continue; 
			}
			
			if ( isset($array['type']) && 'section' == $array['type'] ) {
				echo 'heading' != $id ? '</div>' : '';
				?>
				<div data-id="<?php isset($array['parent']) ? esc_attr_e($array['parent']) : ''; ?>" class="zoremmail-menu-submenu-title <?php isset($array['class']) ? esc_attr_e($array['class']) : ''; ?>">
					<span><?php esc_html_e( $array['title'] ); ?></span>
					<span class="dashicons dashicons-arrow-right-alt2"></span>
				</div>
				<div class="zoremmail-menu-contain">
				<?php
			} else {
				$array_default = isset( $array['default'] ) ? $array['default'] : '';
				?>
				<div class="zoremmail-menu zoremmail-menu-inline zoremmail-menu-sub <?php isset($array['class']) ? esc_attr_e($array['class']) : ''; ?>">
					<div class="zoremmail-menu-item">
						<div class="<?php esc_attr_e( $id ); ?> <?php esc_attr_e( $array['type'] ); ?>">
							<?php if ( isset($array['title']) && 'checkbox' != $array['type'] ) { ?>
								<div class="menu-sub-title"><?php esc_html_e( $array['title'] ); ?></div>
							<?php } ?>
							<?php if ( isset($array['type']) && 'text' == $array['type'] ) { ?>
								<?php //echo '<pre>';print_r($array);echo '</pre>'; ?>
								<?php $field_name = isset( $array['option_type'] ) && 'key' == $array['option_type'] ? $array['option_name'] : $id; ?>
								<div class="menu-sub-field">
									<input type="text" name="<?php esc_attr_e( $field_name ); ?>" id="<?php esc_attr_e( $field_name ); ?>" placeholder="<?php isset($array['placeholder']) ? esc_attr_e($array['placeholder']) : ''; ?>" value="<?php echo esc_html( $array_default ); ?>" class="zoremmail-input <?php esc_html_e($array['type']); ?> <?php isset($array['class']) ? esc_attr_e($array['class']) : ''; ?>">
									<br>
									<span class="menu-sub-tooltip"><?php isset($array['desc']) ? esc_html_e($array['desc']) : ''; ?></span>
								</div>
							<?php } else if ( isset($array['type']) && 'textarea' == $array['type'] ) { ?>
								<div class="menu-sub-field">
									<textarea id="<?php esc_attr_e( $id ); ?>" rows="4" name="<?php esc_attr_e( $id ); ?>" placeholder="<?php isset($array['placeholder']) ? esc_attr_e($array['placeholder']) : ''; ?>" class="zoremmail-input <?php esc_html_e($array['type']); ?> <?php isset($array['class']) ? esc_attr_e($array['class']) : ''; ?>"><?php echo esc_html( $array_default ); ?></textarea>
									<br>
									<span class="menu-sub-tooltip"><?php isset($array['desc']) ? esc_html_e($array['desc']) : ''; ?></span>
								</div>
							<?php } else if ( isset($array['type']) && 'codeinfo' == $array['type'] ) { ?>
								<div class="menu-sub-field">
									<span class="menu-sub-codeinfo <?php esc_html_e($array['type']); ?>"><?php echo isset($array['default']) ? wp_kses_post($array['default']) : ''; ?></span>
								</div>
							<?php } else if ( isset($array['type']) && 'select' == $array['type'] ) { ?>
								<div class="menu-sub-field">
									<select name="<?php esc_attr_e( $id ); ?>" id="<?php esc_attr_e( $id ); ?>" class="zoremmail-input <?php esc_html_e($array['type']); ?> <?php isset($array['class']) ? esc_attr_e($array['class']) : ''; ?>">
										<?php foreach ( (array) $array['options'] as $key => $val ) { ?>
											<option value="<?php echo esc_html($key); ?>" <?php echo $array_default == $key ? 'selected' : ''; ?>><?php echo esc_html($val); ?></option>
										<?php } ?>
									</select>
									<br>
									<span class="menu-sub-tooltip"><?php isset($array['desc']) ? esc_html_e($array['desc']) : ''; ?></span>
								</div>
							<?php } else if ( isset($array['type']) && 'color' == $array['type'] ) { ?>
								<div class="menu-sub-field">
									<input type="text" name="<?php esc_attr_e( $id ); ?>" id="<?php esc_attr_e( $id ); ?>" class="input-text regular-input zoremmail-input <?php esc_html_e($array['type']); ?> <?php isset($array['class']) ? esc_attr_e($array['class']) : ''; ?>" value="<?php echo esc_html( $array_default ); ?>" placeholder="<?php isset($array['placeholder']) ? esc_attr_e($array['placeholder']) : ''; ?>">
									<br>
									<span class="menu-sub-tooltip"><?php isset($array['desc']) ? esc_html_e($array['desc']) : ''; ?></span>
								</div>
							<?php } else if ( isset($array['type']) && 'checkbox' == $array['type'] ) { ?>
								<?php //echo '<pre>';print_r($array['default']);echo '</pre>'; ?>
								<div class="menu-sub-field">
									<label class="menu-sub-title">
										<input type="hidden" name="<?php esc_attr_e( $id ); ?>" value="0"/>
										<input type="checkbox" id="<?php esc_attr_e( $id ); ?>" name="<?php esc_attr_e( $id ); ?>" class="zoremmail-checkbox <?php isset($array['class']) ? esc_attr_e($array['class']) : ''; ?>" value="1" <?php echo $array_default ? 'checked' : ''; ?>/>
										<?php esc_html_e( $array['title'] ); ?>
										<?php if ( isset($array['tip-tip'] ) ) { ?>
											<span class="woocommerce-help-tip tipTip" title="<?php echo esc_html( $array['tip-tip'] ); ?>"></span>
										<?php } ?>
									</label>
								</div>
							<?php } else if ( isset($array['type']) && 'radio_butoon' == $array['type'] ) { ?>
								<div class="menu-sub-field">
									<label class="menu-sub-title">
										<?php foreach ( $array['choices'] as $key => $value ) { ?>
											<label class="radio-button-label">
												<input type="radio" name="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php echo $array_default == $key ? 'checked' : ''; ?>/>
												<span><?php echo esc_html( $value ); ?></span>
											</label>
										<?php } ?>
									</label>
								</div>
							<?php } else if ( isset($array['type']) && 'tgl-btn' == $array['type'] ) { ?>
								<div class="menu-sub-field">
									<?php //echo $array_default; ?>
									<label class="menu-sub-title">
										<span class="tgl-btn-parent">
											<input type="hidden" name="<?php esc_attr_e( $id ); ?>" value="0">
											<input type="checkbox" id="<?php esc_attr_e( $id ); ?>" name="<?php esc_attr_e( $id ); ?>" class="tgl tgl-flat" <?php echo $array_default ? 'checked' : ''; ?> value="1">
											<label class="tgl-btn" for="<?php esc_attr_e( $id ); ?>"></label>
										</span>
										<label for="<?php esc_attr_e( $id ); ?>" class="shipment_email_label"><?php esc_html_e( 'Enable email', 'woo-advanced-shipment-tracking' ); ?></label>
									</label>
								</div>
							<?php } else if ( isset($array['type']) && 'range' == $array['type'] ) { ?>
								<div class="menu-sub-field">
									<label class="menu-sub-title">
										<input type="range" class="zoremmail-range" id="<?php esc_attr_e( $id ); ?>" name="<?php esc_attr_e( $id ); ?>" value="<?php echo esc_html( $array_default ); ?>" min="<?php esc_html_e( $array['min'] ); ?>" max="<?php esc_html_e( $array['max'] ); ?>" oninput="this.nextElementSibling.value = this.value">
										<input style="width:50px;" class="slider__value" type="number" min="<?php esc_attr_e( $array['min'] ); ?>" max="<?php esc_attr_e( $array['max'] ); ?>" value="<?php echo esc_html( $array_default ); ?>">
									</label>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
				<?php
			}
		}
	}
	
	/**
	 * Get the email order status
	 *
	 * @param string $email_template the template string name.
	 */
	public function get_email_order_status( $email_template ) {
		
		$order_status = apply_filters( 'customizer_email_type_order_status_array', self::$email_types_order_status );
		
		$order_status = self::$email_types_order_status;
		
		if ( isset( $order_status[ $email_template ] ) ) {
			return $order_status[ $email_template ];
		} else {
			return 'completed';
		}
	}
	
	/**
	 * Get the email class name
	 *
	 * @param string $email_template the email template slug.
	 */
	public function get_email_class_name( $email_template ) {
		
		$class_names = apply_filters( 'customizer_email_type_class_name_array', self::$email_types_class_names );

		$class_names = self::$email_types_class_names;
		if ( isset( $class_names[ $email_template ] ) ) {
			return $class_names[ $email_template ];
		} else {
			return false;
		}
	}
	
	/**
	 * Get the email content
	 *
	 */
	public function get_preview_email( $send_email = false, $email_addresses = null ) { 
		
		// Load WooCommerce emails.
		$wc_emails      = WC_Emails::instance();
		$emails         = $wc_emails->get_emails();		
		
		$email_template = isset( $_GET['email_type'] ) ? sanitize_text_field($_GET['email_type']) : get_option( 'orderStatus', 'completed' );
		$preview_id = get_option( 'order_preview', 'mockup' );

		$email_type = self::get_email_class_name( $email_template );

		if ( false === $email_type ) {
			return false;
		}		 				
		
		// Reference email.
		if ( isset( $emails[ $email_type ] ) && is_object( $emails[ $email_type ] ) ) {
			$email = $emails[ $email_type ];
		
		}
		$order_status = self::get_email_order_status( $email_template );
		
		// Get an order
		$order = self::get_wc_order_for_preview( $order_status, $preview_id );				

		if ( is_object( $order ) ) {
			// Get user ID from order, if guest get current user ID.
			$user_id = $order->get_meta( '_customer_user', true );
			if ( 0 === ( $user_id ) ) {
				$user_id = get_current_user_id();
			}
		} else {
			$user_id = get_current_user_id();
		}
		// Get user object
		$user = get_user_by( 'id', $user_id );
		
		if ( isset( $email ) ) {
			// Make sure gateways are running in case the email needs to input content from them.
			WC()->payment_gateways();
			// Make sure shipping is running in case the email needs to input content from it.
			WC()->shipping();
			
			$email->object               = $order;
			$user_id = $order->get_meta( '_customer_user', true );
			if ( is_object( $order ) ) {
				$email->find['order-date']   = '{order_date}';
				$email->find['order-number'] = '{order_number}';
				$email->find['customer-first-name'] = '{customer_first_name}';
				$email->find['customer-last-name'] = '{customer_last_name}';
				$email->replace['order-date']   = wc_format_datetime( $email->object->get_date_created() );
				$email->replace['order-number'] = $email->object->get_order_number();
				$email->replace['customer-first-name'] = get_user_meta( $user_id, 'shipping_first_name', true );
				$email->replace['customer-last-name'] = get_user_meta( $user_id, 'shipping_last_name', true );
				// Other properties
				$email->recipient = $email->object->get_billing_email();
			}

			if ( ! empty( $email ) ) {

				$content = $email->get_content();		
				$content = $email->style_inline( $content );
				$content = apply_filters( 'woocommerce_mail_content', $content );	
				
			} else {
				if ( false == $email->object ) {
					$content = '<div style="padding: 35px 40px; background-color: white;">' . __( 'This email type can not be previewed please try a different order or email type.', 'advanced-email-customizer' ) . '</div>';
				}
			}
		} else {
			$content = false;
		}
		
		add_filter( 'wp_kses_allowed_html', array( $this, 'allowed_css_tags' ) );
		add_filter( 'safe_style_css', array( $this, 'safe_style_css' ), 10, 1 );

		echo wp_kses_post( $content );
		die();
	}
	
	public function allowed_css_tags( $tags ) {
		$tags['style'] = array( 'type' => true, );
		return $tags;
	}
	
	public function safe_style_css( $styles ) {
		 $styles[] = 'display';
		return $styles;
	}

	/**
	 * Get WooCommerce order for preview
	 *
	 * @param string $order_status
	 * @return object
	 */
	public static function get_wc_order_for_preview( $order_status = null, $order_id = null ) {
		if ( ! empty( $order_id ) && 'mockup' != $order_id ) { 
			return wc_get_order( $order_id );
		} else {
			// Use mockup order

			// Instantiate order object
			$order = new WC_Order();

			// Other order properties
			$order->set_props( array(
				'id'                 => 1,
				'status'             => ( null === $order_status ? 'processing' : $order_status ),
				'shipping_first_name' => 'Sherlock',
				'shipping_last_name'  => 'Holmes',
				'shipping_company'    => 'Detectives Ltd.',
				'shipping_address_1'  => '221B Baker Street',
				'shipping_city'       => 'London',
				'shipping_postcode'   => 'NW1 6XE',
				'shipping_country'    => 'GB',
				'billing_first_name' => 'Sherlock',
				'billing_last_name'  => 'Holmes',
				'billing_company'    => 'Detectives Ltd.',
				'billing_address_1'  => '221B Baker Street',
				'billing_city'       => 'London',
				'billing_postcode'   => 'NW1 6XE',
				'billing_country'    => 'GB',
				'billing_email'      => 'sherlock@holmes.co.uk',
				'billing_phone'      => '02079304832',
				'date_created'       => gmdate( 'Y-m-d H:i:s' ),
				'total'              => 24.90,
			) );

			// Item #1
			$order_item = new WC_Order_Item_Product();
			$order_item->set_props( array(
				'name'     => 'A Study in Scarlet',
				'subtotal' => '9.95',
			) );
			$order->add_item( $order_item );

			// Item #2
			$order_item = new WC_Order_Item_Product();
			$order_item->set_props( array(
				'name'     => 'The Hound of the Baskervilles',
				'subtotal' => '14.95',
			) );
			$order->add_item( $order_item );

			// Return mockup order
			return $order;
		}

	}
	
	/**
	 * Get Order Ids
	 *
	 * @return array
	 */
	public static function get_order_ids() {		
		$order_array = array();
		$order_array['mockup'] = __( 'Mockup Order', 'woo-advanced-shipment-tracking' );
		
		$orders = wc_get_orders( array(
			'limit'        => 20,
			'status' => array( 'wc-processing', 'wc-completed', 'wc-shipped', 'wc-partial-shipped' ),
			'orderby'      => 'date',
			'order'        => 'DESC',
			'meta_key'     => '_wc_shipment_tracking_items', // The postmeta key field
			'meta_compare' => 'EXISTS', // The comparison argument
		));	
			
		foreach ( $orders as $order ) {				
			$order_array[ $order->get_id() ] = $order->get_id() . ' - ' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();			
		}
		return $order_array;
	}
	
	/**
	 * Get preview URL(admin load url)
	 *
	 */
	public function get_email_preview_url( $status ) {
		return add_query_arg( array(
			'action'	=> 'ast_email_preview',
			'email_type'	=> $status
		), admin_url( 'admin-ajax.php' ) );
	}
	
	/**
	 * Get preview URL(front load url)
	 *
	 */
	public function get_custom_preview_url( $status ) {
		return add_query_arg( array(
			'email-customizer-preview' => '1',
			'email_type'	=> $status
		), home_url( '' ) );
	}
}
