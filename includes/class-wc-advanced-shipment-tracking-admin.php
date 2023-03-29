<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Advanced_Shipment_Tracking_Admin {
		
	/**
	 * Initialize the main plugin function
	*/
	public function __construct() {								
		
		global $wpdb;
		if ( is_multisite() ) {			
			
			if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			}
			
			if ( is_plugin_active_for_network( 'woo-advanced-shipment-tracking/woocommerce-advanced-shipment-tracking.php' ) ) {
				$main_blog_prefix = $wpdb->get_blog_prefix( BLOG_ID_CURRENT_SITE );			
				$this->table = $main_blog_prefix . 'woo_shippment_provider';	
			} else {
				$this->table = $wpdb->prefix . 'woo_shippment_provider';
			}
			
		} else {
			$this->table = $wpdb->prefix . 'woo_shippment_provider';	
		}
			
	}
	
	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	 */
	private static $instance;
	
	/**
	 * Get the class instance
	 *
	 * @return WC_Advanced_Shipment_Tracking_Admin
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
		
		add_action( 'admin_head', array( $this, 'hide_admin_notices_from_settings' ) );
		
		// add bulk order tracking number filter for exported / non-exported orders			
		add_filter( 'woocommerce_shop_order_search_fields', array( $this, 'filter_orders_by_tracking_number_query' ) );			
		
		// add bulk order filter for exported / non-exported orders
		add_action( 'restrict_manage_posts', array( $this, 'filter_orders_by_shipping_provider'), 20 );	
		add_filter( 'request', array( $this, 'filter_orders_by_shipping_provider_query' ) );					
		
		add_filter( 'woocommerce_email_title', array( $this, 'change_completed_woocommerce_email_title'), 10, 2 );
		
		add_action( 'wp_ajax_wc_ast_upload_csv_form_update', array( $this, 'upload_tracking_csv_fun') );		

		add_action( 'admin_footer', array( $this, 'footer_function'), 1 );									
		
		add_filter( 'woocommerce_admin_order_actions', array( $this, 'add_delivered_order_status_actions_button'), 100, 2 );		
		
		//Shipping Provider Action
		add_action( 'wp_ajax_paginate_shipping_provider_list', array( $this, 'paginate_shipping_provider_list') );
		
		add_action( 'wp_ajax_filter_shipping_provider_list', array( $this, 'filter_shipping_provider_list') );

		add_action( 'wp_ajax_get_provider_details', array( $this, 'get_provider_details_fun') );
		
		add_action( 'wp_ajax_update_custom_shipment_provider', array( $this, 'update_custom_shipment_provider_fun') );
		
		add_action( 'wp_ajax_reset_default_provider', array( $this, 'reset_default_provider_fun') );
		
		add_action( 'wp_ajax_woocommerce_shipping_provider_delete', array( $this, 'woocommerce_shipping_provider_delete' ) );				
		
		add_action( 'wp_ajax_update_provider_status', array( $this, 'update_provider_status_fun') );				
		
		add_action( 'wp_ajax_reset_shipping_providers_database', array( $this, 'reset_shipping_providers_database_fun') );
		
		add_action( 'wp_ajax_update_default_provider', array( $this, 'update_default_provider_fun') );
		
		add_action( 'wp_ajax_update_shipment_status', array( $this, 'update_shipment_status_fun') );				

		add_action( 'update_order_status_after_adding_tracking', array( $this, 'update_order_status_after_adding_tracking'), 10, 2 );	

		add_action( 'add_more_api_provider', array( $this, 'add_more_api_provider' ) );
	}		
	
	/*
	* Get shipped orders
	*/
	public function get_shipped_orders() {
		$range = get_option( 'wc_ast_api_date_range', 30 );
		$args = array(
			'status'	=> 'wc-completed',
			'limit'		=> -1,
		);
		
		if ( 0 != $range ) {
			$start = strtotime( gmdate( 'Y-m-d 00:00:00', strtotime( '-' . $range . ' days' ) ) );
			$end = strtotime( gmdate( 'Y-m-d 23:59:59', strtotime( '-1 days' ) ) );
			$args['date_completed'] = $start . ' ... ' . $end;
		}
		
		return wc_get_orders( $args );
	}	
	
	/**
	* Load admin styles.
	*/
	public function admin_styles( $hook ) {						
		
		if ( !isset( $_GET['page'] ) ) {
			return;
		}	
		
		if ( 'woocommerce-advanced-shipment-tracking' != $_GET['page'] ) {
			return;		
		}	
		
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';				

		wp_register_script( 'select2', WC()->plugin_url() . '/assets/js/select2/select2.full' . $suffix . '.js', array( 'jquery' ), '4.0.3' );
		wp_enqueue_script( 'select2');
		
		wp_enqueue_style( 'ast_styles', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/admin.css', array(), wc_advanced_shipment_tracking()->version );		
		
		wp_enqueue_script( 'woocommerce-advanced-shipment-tracking-js', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/admin.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version, true );				
		
		wp_register_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full' . $suffix . '.js', array( 'jquery' ), '1.0.4' );
		wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'selectWoo' ), WC_VERSION );
		wp_register_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
		
		wp_enqueue_script( 'selectWoo' );
		wp_enqueue_script( 'wc-enhanced-select' );
		
		wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
		wp_enqueue_style( 'woocommerce_admin_styles' );
		wp_enqueue_style( 'wp-color-picker' );
		
		wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), WC_VERSION, true );
		
		wp_enqueue_script( 'jquery-tiptip' );
		wp_enqueue_script( 'jquery-blockui' );
		wp_enqueue_script( 'wp-color-picker' );		
		
		wp_enqueue_script( 'ajax-queue', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/jquery.ajax.queue.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version );
				
		wp_enqueue_script( 'ast_settings', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/settings.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version );	
		
		wp_register_script( 'shipment_tracking_table_rows', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/shipping_row.js' , array( 'jquery', 'wp-util' ), wc_advanced_shipment_tracking()->version );
		
		wp_localize_script( 'shipment_tracking_table_rows', 'shipment_tracking_table_rows', array(
			'i18n' => array(				
				'data_saved'	=> __( 'Data saved successfully.', 'woo-advanced-shipment-tracking' ),
				'delete_provider' => __( 'Really delete this entry? This will not be undo.', 'woo-advanced-shipment-tracking' ),
				'upload_only_csv_file' => __( 'You can upload only csv file.', 'woo-advanced-shipment-tracking' ),
				'browser_not_html' => __( 'This browser does not support HTML5.', 'woo-advanced-shipment-tracking' ),
				'upload_valid_csv_file' => __( 'Please upload a valid CSV file.', 'woo-advanced-shipment-tracking' ),
			),
			'delete_rates_nonce' => wp_create_nonce( 'delete-rate' ),
		) );
		wp_enqueue_media();	
	}
	
	/*
	* Admin Menu add function
	* WC sub menu
	*/
	public function register_woocommerce_menu() {
		add_submenu_page( 'woocommerce', 'Shipment Tracking', __( 'Shipment Tracking', 'woo-advanced-shipment-tracking' ), 'manage_woocommerce', 'woocommerce-advanced-shipment-tracking', array( $this, 'woocommerce_advanced_shipment_tracking_page_callback' ) ); 
	}		
	
	public function hide_admin_notices_from_settings() {
		$screen = get_current_screen();
		if ( 'woocommerce_page_woocommerce-advanced-shipment-tracking' === $screen->id && null == get_option( 'ast_usage_data_selector' ) ) {
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'network_admin_notices' );
			remove_all_actions( 'all_admin_notices' );
			remove_all_actions( 'user_admin_notices' );
		}
	}
	/*
	* callback for Shipment Tracking page
	*/
	public function woocommerce_advanced_shipment_tracking_page_callback() {		  
		
		global $order, $wpdb;
		$WC_Countries = new WC_Countries();
		$countries = $WC_Countries->get_countries();				
		
		$default_shippment_providers = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s ORDER BY shipping_default ASC, display_in_order DESC, trackship_supported DESC, id ASC', $this->table ) );
		
		foreach ( $default_shippment_providers as $key => $value ) {			
			$search = array('(US)', '(UK)');
			$replace = array('', '');
			
			if ( $value->shipping_country && 'Global' != $value->shipping_country ) {
				$country = str_replace( $search, $replace, $WC_Countries->countries[ $value->shipping_country ] );
				$default_shippment_providers[ $key ]->country = $country;			
			} elseif ( $value->shipping_country && 'Global' == $value->shipping_country ) {
				$default_shippment_providers[ $key ]->country = 'Global';
			}
		}				
		
		wp_enqueue_script( 'shipment_tracking_table_rows' );
		
		?>		
		
		<div class="zorem-layout">
			<?php 
			if ( null == get_option( 'ast_usage_data_selector' ) ) {
				do_action( 'before_ast_settings' );				
			} else {
				?>
			<div class="zorem-layout__header">
				<h1 class="page_heading">
					<a href="javascript:void(0)"><?php esc_html_e( 'Shipment Tracking', 'woo-advanced-shipment-tracking' ); ?></a> <span class="dashicons dashicons-arrow-right-alt2"></span> <span class="breadcums_page_heading"><?php esc_html_e( 'Settings', 'woo-advanced-shipment-tracking' ); ?></span>
				</h1>				
				<img class="zorem-layout__header-logo" src="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ); ?>assets/images/ast-logo.png">								
			</div>
			
			<div class="woocommerce zorem_admin_layout">
				<div class="ast_admin_content zorem_admin_settings">
					
					<?php include 'views/activity_panel.php'; ?>					
					<div class="ast_nav_div">											
						<?php
						$this->get_html_menu_tab( $this->get_ast_tab_settings_data() );
						?>
						<div class="menu_devider"></div>
						<?php
						require_once( 'views/admin_options_shipping_provider.php' );
						require_once( 'views/admin_options_settings.php' );
						require_once( 'views/admin_options_bulk_upload.php' );
						require_once( 'views/admin_options_integrations.php' );																		
						require_once( 'views/admin_options_addons.php' ); 
						include 'views/admin_options_trackship_integration.php';
						?>
					</div>                   					
				</div>				
			</div>
			<?php } ?>						
		</div>
		<?php include 'views/admin_upgrade_to_pro_popup.php'; ?>		   
	<?php 
	}
	
	/*
	* callback for Shipment Tracking menu array
	*/
	public function get_ast_tab_settings_data() {	
		
		$go_pro_label = class_exists( 'ast_pro' ) ? __( 'License', 'woo-advanced-shipment-tracking' ) : __( 'Go Pro', 'woo-advanced-shipment-tracking' ) ;
		
		$ts4wc_installed = ( function_exists( 'trackship_for_woocommerce' ) ) ? true : false;
		$trackship_type = ( $ts4wc_installed ) ? 'link' : '' ;
		$trackship_link = ( $ts4wc_installed ) ? admin_url( 'admin.php?page=trackship-dashboard' ) : '' ;
		
		$setting_data = array(
			'tab2' => array(					
				'title'		=> __( 'Settings', 'woo-advanced-shipment-tracking' ),
				'show'      => true,
				'class'     => 'tab_label first_label',
				'data-tab'  => 'settings',
				'data-label' => __( 'Settings', 'woo-advanced-shipment-tracking' ),
				'name'  => 'tabs',
			),					
			'tab1' => array(					
				'title'		=> __( 'Shipping Providers', 'woo-advanced-shipment-tracking' ),
				'show'      => true,
				'class'     => 'tab_label',
				'data-tab'  => 'shipping-providers',
				'data-label' => __( 'Shipping Providers', 'woo-advanced-shipment-tracking' ),
				'name'  => 'tabs',				
			),
			'tab4' => array(					
				'title'		=> __( 'CSV Import', 'woo-advanced-shipment-tracking' ),
				'show'      => true,
				'class'     => 'tab_label',
				'data-tab'  => 'bulk-upload',
				'data-label' => __( 'CSV Import', 'woo-advanced-shipment-tracking' ),
				'name'  => 'tabs',				
			),
			'integrations_tab' => array(					
				'title'		=> __( 'Integrations', 'woo-advanced-shipment-tracking' ),
				'show'      => true,
				'data-tab'  => 'integrations',
				'data-label' => __( 'Integrations', 'woo-advanced-shipment-tracking' ),
				'class'     => 'tab_label ast_premium_menu',
				'name'  => 'tabs',				
			),			
			/*'tab6' => array(					
				'title'		=> $go_pro_label,
				'show'      => true,
				'class'     => 'tab_label',
				'data-tab'  => 'addons',
				'data-label' => $go_pro_label,
				'name'  => 'tabs',				
			),*/
			'trackship' => array(					
				'title'		=> 'TrackShip',
				'show'      => true,
				'type'		=> $trackship_type,
				'link'		=> $trackship_link,
				'class'     => 'tab_label',
				'data-tab'  => 'trackship',
				'data-label' => 'TrackShip',
				'name'  => 'tabs',				
			),	
		);
		return apply_filters( 'ast_menu_tab_options', $setting_data );		
	}
	
	/*
	* callback for Shipment Tracking general settings data
	*/
	public function get_ast_tab_general_settings_data() {	
		$setting_data = array(
			'tab_general_settings' => array(					
				'title'		=> __( 'General Settings', 'woo-advanced-shipment-tracking' ),
				'show'      => true,
				'class'     => 'inner_tab_label',
				'data-tab'  => 'general-settings',
				'data-label' => __( 'General Settings', 'woo-advanced-shipment-tracking' ),
				'name'  => 'ast_generatral_settings_tabs',
				'position'  => 1,	
			),
			'tab_order_status' => array(					
				'title'		=> __( 'Order Statuses', 'woo-advanced-shipment-tracking' ),
				'show'      => true,
				'class'     => 'inner_tab_label',
				'data-tab'  => 'order-status',
				'data-label' => __( 'Order Statuses', 'woo-advanced-shipment-tracking' ),
				'name'  => 'ast_generatral_settings_tabs',
				'position'  => 1,	
			),				
		);
		return apply_filters( 'ast_general_settings_tab_options', $setting_data );
	}
	
	/*
	* callback for HTML function for Shipment Tracking menu
	*/
	public function get_html_menu_tab( $arrays, $tab_class = 'tab_input' ) { 
		
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'settings';
		$settings = isset( $_GET['settings'] ) ? sanitize_text_field( $_GET['settings'] ) : 'general-settings';		
		
		foreach ( (array) $arrays as $id => $array ) {					
			if ( $array['show'] ) {	
				if ( isset( $array['type'] ) && 'link' == $array['type'] ) {
					?>
					<a class="menu_link <?php esc_html_e( $array['class'] ); ?>" href="<?php esc_html_e( esc_url( $array['link'] ) ); ?>"><?php esc_html_e( $array['title'] ); ?></a>
				<?php 
				} else { 
					$checked = ( $tab == $array['data-tab'] || $settings == $array['data-tab'] ) ? 'checked' : '';
					?>
					<input class="<?php esc_html_e( $tab_class ); ?>" id="<?php esc_html_e( $id ); ?>" name="<?php esc_html_e( $array['name'] ); ?>" type="radio"  data-tab="<?php esc_html_e( $array['data-tab'] ); ?>" data-label="<?php esc_html_e( $array['data-label'] ); ?>"  <?php esc_html_e( $checked ); ?>/>
					<label class="<?php esc_html_e( $array['class'] ); ?>" for="<?php esc_html_e( $id ); ?>"><?php esc_html_e( $array['title'] ); ?></label>
				<?php 
				} 
			}
		}
	}			

	/*
	* get UL html of fields
	*/
	public function get_html_ul( $arrays ) { 
		?>
		<ul class="settings_ul">		
		<?php 
		foreach ( (array) $arrays as $id => $array ) {
				
			if ( $array['show'] ) { 
				
				if ( 'checkbox' == $array['type'] ) {
					$default = isset( $array['default'] ) ? $array['default'] : '';
					$checked = ( get_option( $id, $default ) ) ? 'checked' : '' ;					
					?>
					<li>
						<input type="hidden" name="<?php esc_html_e( $id ); ?>" value="0"/>
						<input class="" id="<?php esc_html_e( $id ); ?>" name="<?php esc_html_e( $id ); ?>" type="checkbox" <?php esc_html_e( $checked ); ?> value="1"/>
											
						<label class="setting_ul_checkbox_label"><?php esc_html_e( $array['title'] ); ?>
						<?php if ( isset( $array['tooltip'] ) ) { ?>
							<span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( $array['tooltip'] ); ?>"></span>
						<?php } ?>
						</label>						
					</li>	
				<?php 
				} else if ( 'tgl_checkbox' == $array['type'] ) {
					$default = isset( $array['default'] ) ? $array['default'] : '';
					$checked = ( get_option( $id, $default ) ) ? 'checked' : '' ;
					$tgl_class = isset( $array['tgl_color'] ) ? 'ast-tgl-btn-green' : '';
					$disabled = isset( $array['disabled'] ) && true == $array['disabled'] ? 'disabled' : '';
					?>
					<li>
						<span class="ast-tgl-btn-parent">
							<input type="hidden" name="<?php esc_html_e( $id ); ?>" value="0"/>
							<input class="ast-tgl ast-tgl-flat ast-settings-toggle" id="<?php esc_html_e( $id ); ?>" name="<?php esc_html_e( $id ); ?>" type="checkbox" <?php esc_html_e( $checked ); ?> value="1" <?php esc_html_e( $disabled ); ?>/>
							<label class="ast-tgl-btn <?php esc_html_e( $tgl_class ); ?>" for="<?php esc_html_e( $id ); ?>"></label>
						</span>
											
						<div class="setting_ul_tgl_checkbox_label"><label><?php esc_html_e( $array['title'] ); ?></label>
							<?php if ( isset( $array['tooltip'] ) ) { ?>
								<span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( $array['tooltip'] ); ?>"></span>
							<?php } ?>
							<?php if ( isset( $array['desc'] ) ) { ?>
								<div class="tgl_checkbox_desc"><?php esc_html_e( $array['desc'] ); ?></div>
							<?php } ?>	
						</div>						
						
						<?php if ( isset( $array['customize_link'] ) ) { ?>
							<a href="<?php esc_html_e( $array['customize_link'] ); ?>" class="button-primary btn_ts_transparent btn_large ts_customizer_btn">
								<?php esc_html_e( 'Customize', 'woo-advanced-shipment-tracking' ); ?>
							</a>	
						<?php } ?>
					</li>	
				<?php 
				} else if ( 'radio' == $array['type'] ) { 
					?>
					<li class="settings_radio_li">
						<label><strong><?php esc_html_e( $array['title'] ); ?></strong>
							<?php if ( isset( $array['tooltip'] ) ) { ?>
								<span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( $array['tooltip'] ); ?>"></span>
							<?php } ?>
						</label>	
						
						<?php 
						
						foreach ( (array) $array['options'] as $key => $val ) {
							$selected = ( get_option( $id, $array['default'] ) == (string) $key ) ? 'checked' : '' ; 
							?>
							<span class="radio_section">
								<label class="" for="<?php esc_html_e( $id ); ?>_<?php esc_html_e( $key ); ?>">												
									<input type="radio" id="<?php esc_html_e( $id ); ?>_<?php esc_html_e( $key ); ?>" name="<?php esc_html_e( $id ); ?>" class="<?php esc_html_e( $id ); ?>"  value="<?php esc_html_e( $key ); ?>" <?php esc_html_e( $selected ); ?> />
									<span class=""><?php esc_html_e( $val ); ?></span></br>
								</label>																		
							</span>
						<?php } ?>
					</li>					
				<?php 
				} else if ( 'multiple_select' == $array['type'] ) { 
					?>
					<li class="multiple_select_li">
						<label><?php esc_html_e( $array['title'] ); ?>
							<?php if ( isset( $array['tooltip'] ) ) { ?>
								<span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( $array['tooltip'] ); ?>"></span>
							<?php } ?>
						</label>
						<div class="multiple_select_container <?php esc_html_e( $id ); ?>">	
							<select multiple class="wc-enhanced-select" name="<?php esc_html_e( $id ); ?>[]" id="<?php esc_html_e( $id ); ?>">
							<?php
							foreach ( (array) $array['options'] as $key => $val ) { 
								$multi_checkbox_data = get_option( $id );
								$checked = isset( $multi_checkbox_data[ $key ] ) && 1 == $multi_checkbox_data[ $key ] ? 'selected' : '' ;
								?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php esc_html_e( $checked ); ?>><?php esc_html_e( $val['status'] ); ?></option>
							<?php 
							} 
							?>
							</select>	
						</div>
					</li>	
				<?php 
				} else if ( 'multiple_checkbox' == $array['type'] ) { 
					?>
					<li>
						<div class="multiple_checkbox_label">
							<label for=""><strong><?php esc_html_e( $array['title'] ); ?></strong></label>
							<span class="multiple_checkbox_description"><?php esc_html_e( $array['desc'] ); ?></span>
						</div >
						<div class="multiple_checkbox_parent">
							<?php 
							$op = 1;	
							foreach ( (array) $array['options'] as $key => $val ) {
								$multi_checkbox_data = get_option($id);
								$checked = isset( $multi_checkbox_data[ $key ] ) && 1 == $multi_checkbox_data[ $key ] ? 'checked' : '' ;
								?>
								<span class="multiple_checkbox">
									<label class="" for="">
										<input type="hidden" name="<?php esc_html_e( $id ); ?>[<?php esc_html_e( $key ); ?>]" value="0"/>
										<input type="checkbox" name="<?php esc_html_e( $id ); ?>[<?php esc_html_e( $key ); ?>]" class=""  <?php esc_html_e( $checked ); ?> value="1"/>
										<span class="multiple_label"><?php esc_html_e( $val['status'] ); ?></span>
										</br>
									</label>																		
								</span>												
							<?php } ?>
						</div>						
					</li>	
				<?php 
				} else if ( 'dropdown_tpage' == $array['type'] ) { 
					?>
					<li>
						<label class="left_label"><?php esc_html_e( $array['title'] ); ?>
							<?php if ( isset( $array['tooltip'] ) ) { ?>
								<span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( $array['tooltip'] ); ?>"></span>
							<?php } ?>
						</label>						
						
						<select class="select select2 tracking_page_select" id="<?php esc_html_e( $id ); ?>" name="<?php esc_html_e( $id ); ?>">
							<?php
							foreach ( (array) $array['options'] as $page_id => $page_name ) { 
								$selected = ( get_option( $id ) == $page_id ) ? 'selected' : '' ;
								?>
								<option value="<?php esc_html_e( $page_id ); ?>" <?php esc_html_e( $selected ); ?>><?php esc_html_e( $page_name ); ?></option>
							<?php 
							}
							$selected = ( 'other' == get_option( $id ) ) ? 'selected' : '';	
							?>
							<option <?php esc_html_e( $selected ); ?> value="other"><?php esc_html_e( 'Other', 'woo-advanced-shipment-tracking' ); ?></option>	
						</select>
						<?php $style = ( 'other' != get_option( $id ) ) ? 'display:none;' : ''; ?>
						<fieldset style="<?php esc_html_e( $style ); ?>" class="trackship_other_page_fieldset">
							<input type="text" name="wc_ast_trackship_other_page" id="wc_ast_trackship_other_page" value="<?php esc_html_e( get_option('wc_ast_trackship_other_page') ); ?>">
						</fieldset>
						
						<p class="tracking_page_desc"><?php esc_html_e( 'add the [wcast-track-order] shortcode in the selected page.', 'woo-advanced-shipment-tracking' ); ?> 
							<a href="https://docs.zorem.com/docs/ast-pro/advanced-shipment-tracking-free/trackship-integration/" target="blank"><?php esc_html_e( 'more info', 'woo-advanced-shipment-tracking' ); ?></a>
						</p>	
						
					</li>	
				<?php 
				} else if ( 'button' == $array['type'] ) { 
					?>
					<li>
						<label class="left_label"><?php esc_html_e( $array['title'] ); ?>
							<?php if ( isset( $array['tooltip'] ) ) { ?>
								<span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( $array['tooltip'] ); ?>"></span>
							<?php } ?>
						</label>	
						<?php 
						if ( isset( $array['customize_link'] ) ) {
							?>
							<a href="<?php esc_html_e( $array['customize_link'] ); ?>" class="button-primary btn_ts_transparent btn_large ts_customizer_btn"><?php esc_html_e( 'Customize', 'woo-advanced-shipment-tracking' ); ?></a>	
						<?php } ?>	
					</li>	
				<?php 
				} 
			}
		}
		?>
		</ul>	
	<?php 
	}	

	public function get_add_tracking_options() {
		
		$wc_ast_status_shipped = get_option( 'wc_ast_status_shipped', 0 );
		
		if ( 1 == $wc_ast_status_shipped ) {
			$completed_order_label = __( 'Shipped', 'woo-advanced-shipment-tracking' );				
		} else {
			$completed_order_label = __( 'Completed', 'woocommerce' );		
		}
		
		$all_order_status = wc_get_order_statuses();
		
		$default_order_status = array(
			'wc-pending' => 'Pending payment',
			'wc-processing' => 'Processing',
			'wc-on-hold' => 'On hold',
			'wc-completed' => 'Completed',
			'wc-delivered' => 'Delivered',			
			'wc-cancelled' => 'Cancelled',
			'wc-refunded' => 'Refunded',
			'wc-failed' => 'Failed',
			'wc-ready-pickup' => 'Ready for Pickup',		
			'wc-pickup' => 'Picked up',	
			'wc-partial-shipped' => 'Partially Shipped',		
			'wc-updated-tracking' => 'Updated Tracking',				
		);
		
		foreach ( $default_order_status as $key => $value ) {
			unset($all_order_status[$key]);
		}
		$custom_order_status = $all_order_status;
		
		foreach ( $custom_order_status as $key => $value ) {
			unset($custom_order_status[$key]);			
			$key = str_replace( 'wc-', '', $key);		
			$custom_order_status[$key] = array(
				'status' => __( $value, '' ),
				'type' => 'custom',
			);
		}
		
		$actions_order_status = array( 
			'processing' => array(
				'status' => __( 'Processing', 'woocommerce' ),
				'type' => 'default',
			),
			'completed' => array(
				'status' => $completed_order_label,
				'type' => 'default',
			),
			'partial-shipped' => array(
				'status' => __( 'Partially Shipped', '' ),
				'type' => 'default',
				'class' => 'partially_shipped_checkbox',
			),
			'updated-tracking' => array(
				'status' => __( 'Updated Tracking', '' ),
				'type' => 'default',
				'class' => 'updated_tracking_checkbox',
			),	
			'on-hold' => array(
				'status' => __( 'On Hold', 'woocommerce' ),
				'type' => 'default',
			),
			'cancelled' => array(
				'status' => __( 'Cancelled', 'woocommerce' ),
				'type' => 'default',
			),		
			'refunded' => array(
				'status' => __( 'Refunded', 'woocommerce' ),
				'type' => 'default',
			),	
			'failed' => array(
				'status' => __( 'Failed', 'woocommerce' ),
				'type' => 'default',
			),					
		);
		
		$action_order_status_array = array_merge( $actions_order_status, $custom_order_status );
		
		$form_data = array(		
			'wc_ast_default_mark_shipped' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'Set the "mark as shipped" option checked  when adding tracking info to orders', 'woo-advanced-shipment-tracking' ),				
				'show'		=> true,
				'class'     => '',
			),
			'wc_ast_show_orders_actions' => array(
				'type'		=> 'multiple_select',
				'title'		=> __( 'Add Tracking Order action', 'woo-advanced-shipment-tracking' ),				
				'options'   => $action_order_status_array,					
				'show'		=> true,
				'class'     => '',
			),	
		);
		return $form_data;
	}

	public function get_customer_view_options() {
		
		$wc_ast_status_shipped = get_option( 'wc_ast_status_shipped', 0 );
		$completed_order_label = ( 1 == $wc_ast_status_shipped ) ? __( 'Shipped', 'woo-advanced-shipment-tracking' ) : __( 'Completed', 'woocommerce' );
		
		$all_order_status = wc_get_order_statuses();
		
		$default_order_status = array(
			'wc-pending' => 'Pending payment',
			'wc-processing' => 'Processing',
			'wc-on-hold' => 'On hold',
			'wc-completed' => 'Completed',
			'wc-delivered' => 'Delivered',			
			'wc-cancelled' => 'Cancelled',
			'wc-refunded' => 'Refunded',
			'wc-failed' => 'Failed',
			'wc-ready-pickup' => 'Ready for Pickup',		
			'wc-pickup' => 'Picked up',	
			'wc-partial-shipped' => 'Partially Shipped',		
			'wc-updated-tracking' => 'Updated Tracking',				
		);
		
		foreach ( $default_order_status as $key => $value ) {
			unset($all_order_status[$key]);
		}
		
		$custom_order_status = $all_order_status;
		
		foreach ( $custom_order_status as $key => $value ) {
			unset($custom_order_status[$key]);			
			$key = str_replace( 'wc-', '', $key);		
			$custom_order_status[$key] = array(
				'status' => __( $value, '' ),
				'type' => 'custom',
			);
		}
		
		$order_status = array( 
			'processing' => array(
				'status' => __( 'Processing', 'woocommerce' ),
				'type' => 'default',
			),
			'completed' => array(
				'status' => $completed_order_label,
				'type' => 'default',
			),
			'partial-shipped' => array(
				'status' => __( 'Partially Shipped', '' ),
				'type' => 'default',
				'class' => 'partially_shipped_checkbox',
			),
			'updated-tracking' => array(
				'status' => __( 'Updated Tracking', '' ),
				'type' => 'default',
				'class' => 'updated_tracking_checkbox',
			),	
			'cancelled' => array(
				'status' => __( 'Cancelled', 'woocommerce' ),
				'type' => 'default',
			),
			'on-hold' => array(
				'status' => __( 'On Hold', 'woocommerce' ),
				'type' => 'default',
			),			
			'refunded' => array(
				'status' => __( 'Refunded', 'woocommerce' ),
				'type' => 'default',
			),
			
			'failed' => array(
				'status' => __( 'Failed', 'woocommerce' ),
				'type' => 'default',
			),
			'show_in_customer_invoice' => array(
				'status' => __( 'Customer Invoice', 'woocommerce' ),
				'type' => 'default',
			),
			'show_in_customer_note' => array(
				'status' => __( 'Customer note', 'woocommerce' ),
				'type' => 'default',
			),			
		);
		
		$order_status_array = array_merge( $order_status, $custom_order_status );	
		
		$form_data = array(
			'wc_ast_unclude_tracking_info' => array(
				'type'		=> 'multiple_select',
				'title'		=> __( 'Additional Order Emails Display', 'woo-advanced-shipment-tracking' ),				
				'options'   => $order_status_array,					
				'show'		=> true,
				'class'     => '',
			),
			'display_track_in_my_account' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'Enable Track button in orders history (actions)', 'woo-advanced-shipment-tracking' ),				
				'show'		=> true,
				'class'     => '',
			),
			'open_track_in_new_tab' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'Open the Track Button link in a new tab', 'woo-advanced-shipment-tracking' ),			
				'show'		=> true,
				'class'     => '',
			),
		);
		return $form_data;
	}	
	
	public function get_shipment_tracking_api_options() {				
		$form_data = array(
			'wc_ast_api_date_format' => array(
				'type'		=> 'radio',
				'title'		=> __( 'API Date Format', 'woo-advanced-shipment-tracking' ),				
				'desc'		=> __( 'Choose for which Order status to display', 'woo-advanced-shipment-tracking' ),			
				'options'   => array(
									'd-m-Y' => 'DD/MM/YYYY',
									'm-d-Y' => 'MM/DD/YYYY',
							),
				'default'   => 'd-m-Y',				
				'show'		=> true,
				'class'     => '',
			),
		);
		return $form_data;
	}
	
	public function get_usage_tracking_options() {				
		$form_data = array(			
			'ast_optin_email_notification' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'Opt in to get email notifications for security & feature updates', 'woo-advanced-shipment-tracking' ),				
				'show'		=> true,
				'class'     => '',
			),
			'ast_enable_usage_data' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'Opt in to share some basic WordPress environment info', 'woo-advanced-shipment-tracking' ),			
				'show'		=> true,
				'class'     => '',
			),
		);
		return $form_data;
	}
	
	/*
	* get updated tracking status settings array data
	* return array
	*/
	public function get_updated_tracking_data() {		
		$form_data = array(			
			'wc_ast_status_updated_tracking' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Enable custom order status “Updated Tracking"', '' ),				
				'show'		=> true,
				'class'     => '',
			),			
			'wc_ast_status_updated_tracking_label_color' => array(
				'type'		=> 'color',
				'title'		=> __( 'Updated Tracking Label color', '' ),				
				'class'		=> 'updated_tracking_status_label_color_th',
				'show'		=> true,
			),
			'wc_ast_status_updated_tracking_label_font_color' => array(
				'type'		=> 'dropdown',
				'title'		=> __( 'Updated Tracking Label font color', '' ),
				'options'   => array( 
									'' =>__( 'Select', 'woocommerce' ),
									'#fff' =>__( 'Light', '' ),
									'#000' =>__( 'Dark', '' ),
								),			
				'class'		=> 'updated_tracking_status_label_color_th',
				'show'		=> true,
			),			
			'wcast_enable_updated_tracking_email' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Enable the Updated Tracking order status email', '' ),
				'title_link'=> '',
				'class'		=> 'updated_tracking_status_label_color_th',
				'show'		=> true,
			),			
		);
		return $form_data;
	}

	/*
	* get Partially Shipped array data
	* return array
	*/
	public function get_partial_shipped_data() {		
		$form_data = array(			
			'wc_ast_status_partial_shipped' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Enable custom order status “Partially Shipped"', '' ),				
				'show'		=> true,
				'class'     => '',
			),			
			'wc_ast_status_partial_shipped_label_color' => array(
				'type'		=> 'color',
				'title'		=> __( 'Partially Shipped Label color', '' ),				
				'class'		=> 'partial_shipped_status_label_color_th',
				'show'		=> true,
			),
			'wc_ast_status_partial_shipped_label_font_color' => array(
				'type'		=> 'dropdown',
				'title'		=> __( 'Partially Shipped Label font color', '' ),
				'options'   => array( 
									'' =>__( 'Select', 'woocommerce' ),
									'#fff' =>__( 'Light', '' ),
									'#000' =>__( 'Dark', '' ),
								),			
				'class'		=> 'partial_shipped_status_label_color_th',
				'show'		=> true,
			),			
			'wcast_enable_partial_shipped_email' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Enable the Partially Shipped order status email', '' ),
				'title_link'=> '',
				'class'		=> 'partial_shipped_status_label_color_th',
				'show'		=> true,
			),			
		);
		return $form_data;
	}

	/*
	* get settings tab array data
	* return array
	*/
	public function get_delivered_data() {		
		$form_data = array(			
			'wc_ast_status_delivered' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Enable custom order status “Delivered"', '' ),				
				'show'		=> true,
				'class'     => '',
			),			
			'wc_ast_status_label_color' => array(
				'type'		=> 'color',
				'title'		=> __( 'Delivered Label color', '' ),				
				'class'		=> 'status_label_color_th',
				'show'		=> true,
			),
			'wc_ast_status_label_font_color' => array(
				'type'		=> 'dropdown',
				'title'		=> __( 'Delivered Label font color', '' ),
				'options'   => array( 
					'' =>__( 'Select', 'woocommerce' ),
					'#fff' =>__( 'Light', '' ),
					'#000' =>__( 'Dark', '' ),
				),			
				'class'		=> 'status_label_color_th',
				'show'		=> true,
			),							
		);
		return $form_data;
	}	
	
	/*
	* get Order Status data
	* return array
	*/
	public function get_osm_data() {
		
		$osm_data = array(			
			'partial_shipped' => array(
				'id'		=> 'wc_ast_status_partial_shipped',
				'slug'   	=> 'partial-shipped',
				'label'		=> __( 'Partially Shipped', 'woo-advanced-shipment-tracking' ),				
				'label_class' => 'wc-partially-shipped',
				'option_id'	=> 'woocommerce_customer_partial_shipped_order_settings',				
				'edit_email'=> admin_url( 'admin.php?page=ast_customizer&email_type=partial_shipped' ),
				'label_color_field' => 'wc_ast_status_partial_shipped_label_color',	
				'font_color_field' => 'wc_ast_status_partial_shipped_label_font_color',	
				'email_field' => 'wcast_enable_partial_shipped_email',					
			),	
			'delivered' => array(
				'id'		=> 'wc_ast_status_delivered',
				'slug'   	=> 'delivered',
				'label'		=> __( 'Delivered', 'woo-advanced-shipment-tracking' ),				
				'label_class' => 'wc-delivered',
				'option_id'	=> 'woocommerce_customer_delivered_order_settings',				
				'edit_email'=> '',
				'label_color_field' => 'wc_ast_status_label_color',	
				'font_color_field' => 'wc_ast_status_label_font_color',	
				'email_field' => '',					
			),		
		);
		
		$updated_tracking_status = get_option( 'wc_ast_status_updated_tracking', 0);
		
		if ( true == $updated_tracking_status ) {	
			$updated_tracking_data = array(			
				'updated_tracking' => array(
					'id'		=> 'wc_ast_status_updated_tracking',
					'slug'   	=> 'updated-tracking',
					'label'		=> __( 'Updated Tracking', 'woo-advanced-shipment-tracking' ),				
					'label_class' => 'wc-updated-tracking',
					'option_id'	=> 'woocommerce_customer_updated_tracking_order_settings',				
					'edit_email'=> '',
					'label_color_field' => 'wc_ast_status_updated_tracking_label_color',	
					'font_color_field' => 'wc_ast_status_updated_tracking_label_font_color',	
					'email_field' => 'wcast_enable_updated_tracking_email',					
				),		
			);
			$osm_data = array_merge( $osm_data, $updated_tracking_data );
		}
		return apply_filters( 'ast_osm_data', $osm_data );		
	}
	
	/*
	* settings form save
	*/
	public function wc_ast_settings_form_update_callback() {
		
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			exit( 'You are not allowed' );
		}
		
		if ( ! empty( $_POST ) && check_admin_referer( 'wc_ast_settings_form', 'wc_ast_settings_form_nonce' ) ) {
			
			$data = $this->get_add_tracking_options();						
			
			foreach ( $data as $key => $val ) {				
				
				if ( isset( $val['type'] ) && 'multiple_select' == $val['type'] ) {					
					
					foreach ( $val['options'] as $op_status => $op_data ) {
						$_POST[ $key ][$op_status] = 0;
					}
					
					if ( isset( $_POST[ $key ] ) ) {
						foreach ( wc_clean( $_POST[ $key ] ) as $key1 => $status) {
							$_POST[ $key ][$status] = 1;						
						}
					}
					
					if ( isset( $_POST[ $key ] ) ) {
						update_option( $key, wc_clean( $_POST[ $key ] ) );
					}
					
					
				} else {
					
					if ( isset( $_POST[ $key ] ) ) {						
						update_option( $key, wc_clean( $_POST[ $key ] ) );
					}	
				}
				
				if ( isset( $val['type'] ) && 'inline_checkbox' == $val['type'] ) {
					foreach ( (array) $val['checkbox_array'] as $key1 => $val1 ) {
						if ( isset( $_POST[ $key1 ] ) ) {						
							update_option( $key1, wc_clean( $_POST[ $key1 ] ) );
						}
					}					
				}
			}

			$data1 = $this->get_customer_view_options();						
			
			foreach ( $data1 as $key => $val ) {				
				
				if ( isset( $val['type'] ) && 'multiple_select' == $val['type'] ) {					
					
					foreach ( $val['options'] as $op_status => $op_data ) {
						$_POST[ $key ][$op_status] = 0;
					}
					
					foreach ( wc_clean( $_POST[ $key ] ) as $key1 => $status) {
						$_POST[ $key ][$status] = 1;						
					}
					
					update_option( $key, wc_clean( $_POST[ $key ] ) );					
					
				} else {
					
					if ( isset( $_POST[ $key ] ) ) {						
						update_option( $key, wc_clean( $_POST[ $key ] ) );
					}	
				}
			}										

			$data2 = $this->get_shipment_tracking_api_options();						
			
			foreach ( $data2 as $key => $val ) {				
				
				if ( isset( $_POST[ $key ] ) ) {						
					update_option( $key, wc_clean( $_POST[ $key ] ) );
				}
			}

			$data3 = $this->get_usage_tracking_options();						
			
			foreach ( $data3 as $key => $val ) {				
				if ( isset( $_POST[ $key ] ) ) {						
					update_option( $key, wc_clean( $_POST[ $key ] ) );
				}				
			}		
			
			$ast_tracker = WC_AST_Tracker::get_instance();
			$ast_tracker->set_unset_usage_data_cron();

			$wc_ast_status_shipped = isset( $_POST[ 'wc_ast_status_shipped' ] ) ? wc_clean( $_POST[ 'wc_ast_status_shipped' ] ) : '';
			update_option( 'wc_ast_status_shipped', $wc_ast_status_shipped );
			
			
			$data = $this->get_delivered_data();						
			foreach ( $data as $key => $val ) {				
				if ( isset( $_POST[ $key ] ) ) {						
					update_option( $key, wc_clean( $_POST[ $key ] ) );
				}
			}
			
			
			$data = $this->get_partial_shipped_data();						
			
			foreach ( $data as $key => $val ) {				
				
				if ( 'wcast_enable_partial_shipped_email' == $key ) {						
					if ( isset( $_POST['wcast_enable_partial_shipped_email'] ) ) {						
						
						if ( 1 == $_POST['wcast_enable_partial_shipped_email'] ) {
							update_option( 'customizer_partial_shipped_order_settings_enabled', wc_clean( $_POST['wcast_enable_partial_shipped_email'] ) );
							$enabled = 'yes';
						} else {
							update_option( 'customizer_partial_shipped_order_settings_enabled', '' );
							$enabled = 'no';
						}						
						
						$wcast_enable_partial_shipped_email = get_option( 'woocommerce_customer_partial_shipped_order_settings' );
						$wcast_enable_partial_shipped_email['enabled'] = $enabled;
						update_option( 'woocommerce_customer_partial_shipped_order_settings', $wcast_enable_partial_shipped_email );	
					}	
				}										
				
				if ( isset( $_POST[ $key ] ) ) {						
					update_option( $key, wc_clean( $_POST[ $key ] ) );
				}
			}
			
			$data = $this->get_updated_tracking_data();						
			
			foreach ( $data as $key => $val ) {				
				
				if ( 'wcast_enable_updated_tracking_email' == $key ) {						
					if ( isset( $_POST['wcast_enable_updated_tracking_email'] ) ) {						
						if ( 1 == $_POST['wcast_enable_updated_tracking_email'] ) {
							update_option( 'customizer_updated_tracking_order_settings_enabled', wc_clean( $_POST['wcast_enable_updated_tracking_email'] ) );
							$enabled = 'yes';
						} else {
							update_option( 'customizer_updated_tracking_order_settings_enabled', '' );
							$enabled = 'no';
						}																		
						
						$wcast_enable_updated_tracking_email = get_option( 'woocommerce_customer_updated_tracking_order_settings' );
						$wcast_enable_updated_tracking_email['enabled'] = $enabled;
						update_option( 'woocommerce_customer_updated_tracking_order_settings', $wcast_enable_updated_tracking_email );	
					}	
				}										
				
				if ( isset( $_POST[ $key ] ) ) {						
					update_option( $key, wc_clean( $_POST[ $key ] ) );
				}
			}						
		}
	}	
		
	/*
	* Change style of delivered order label
	*/	
	public function footer_function() {
		if ( !is_plugin_active( 'woocommerce-order-status-manager/woocommerce-order-status-manager.php' ) ) {
			$bg_color = get_option( 'wc_ast_status_label_color', '#59c889' );
			$color = get_option( 'wc_ast_status_label_font_color', '#fff' );						
			
			$ps_bg_color = get_option( 'wc_ast_status_partial_shipped_label_color', '#1e73be' );
			$ps_color = get_option( 'wc_ast_status_partial_shipped_label_font_color', '#fff' );
			
			$ut_bg_color = get_option( 'wc_ast_status_updated_tracking_label_color', '#23a2dd' );
			$ut_color = get_option( 'wc_ast_status_updated_tracking_label_font_color', '#fff' );
			?>
			<style>
			.order-status.status-delivered,.order-status-table .order-label.wc-delivered{
				background: <?php esc_html_e( $bg_color ); ?>;
				color: <?php esc_html_e( $color ); ?>;
			}					
			.order-status.status-partial-shipped,.order-status-table .order-label.wc-partially-shipped{
				background: <?php esc_html_e( $ps_bg_color ); ?>;
				color: <?php esc_html_e( $ps_color ); ?>;
			}
			.order-status.status-updated-tracking,.order-status-table .order-label.wc-updated-tracking{
				background: <?php esc_html_e( $ut_bg_color ); ?>;
				color: <?php esc_html_e( $ut_color ); ?>;
			}		
			</style>
			<?php
		}
	}		
	
	/*
	* Ajax call for upload tracking details into order from bulk upload
	*/
	public function upload_tracking_csv_fun() {				
		
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			exit( 'You are not allowed' );
		}
		
		check_ajax_referer( 'nonce_csv_import', 'security' );
		
		$replace_tracking_info = isset( $_POST['replace_tracking_info'] ) ? wc_clean( $_POST['replace_tracking_info'] ) : '';
		$date_format_for_csv_import = isset( $_POST['date_format_for_csv_import'] ) ? wc_clean( $_POST['date_format_for_csv_import'] ) : '';
		update_option( 'date_format_for_csv_import', $date_format_for_csv_import );
		$order_number = isset( $_POST['order_id'] ) ? wc_clean( $_POST['order_id'] ) : '';				
		
		$wast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
		$order_id = $wast->get_formated_order_id( $order_number );

		$tracking_provider = isset( $_POST['tracking_provider'] ) ? wc_clean( $_POST['tracking_provider'] ) : '';
		$tracking_number = isset( $_POST['tracking_number'] ) ? wc_clean( $_POST['tracking_number'] ) : '';
		$status_shipped = ( isset( $_POST['status_shipped'] ) ? wc_clean( $_POST['status_shipped'] ) : '' );
		$date_shipped = ( isset( $_POST['date_shipped'] ) ? wc_clean( $_POST['date_shipped'] ) : '' );
		$date_shipped = str_replace( '/', '-', $date_shipped );
		$trackings = ( isset( $_POST['trackings'] ) ? wc_clean( $_POST['trackings'] ) : '' );		
		
		$sku = isset( $_POST['sku'] ) ? wc_clean( $_POST['sku'] ) : '';
		$qty = isset( $_POST['qty'] ) ? wc_clean( $_POST['qty'] ) : '';	
		$date_shipped = empty( $date_shipped ) ? gmdate('d-m-Y') : $date_shipped ;	

		global $wpdb;					
		
		$shippment_provider = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %1s WHERE api_provider_name = %s', $this->table, $tracking_provider ) );
		
		if ( 0 == $shippment_provider ) {
			$shippment_provider = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM %1s WHERE JSON_CONTAINS(api_provider_name, '[" . '"' . $tracking_provider . '"' . "]')", $this->table ) );			
		}	
		
		if ( 0 == $shippment_provider ) {
			$shippment_provider = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %1s WHERE provider_name = %s', $this->table, $tracking_provider ) );
		}
		
		if ( 0 == $shippment_provider ) {
			$shippment_provider = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %1s WHERE ts_slug = %s', $this->table, $tracking_provider ) );
		}
		
		$order = wc_get_order($order_id);		
		
		if ( false === $order ) {
			echo '<li class="invalid_order_id_error">Failed - Invalid Order Id - Order ' . esc_html( $order_number ) . '</li>';
			exit;
		}
		
		if ( 0 == $shippment_provider ) {
			echo '<li class="shipping_provider_error">Failed - Invalid Shipping Provider - Order ' . esc_html( $order_number ) . '</li>';
			exit;
		}
		
		if ( empty( $tracking_number ) ) {
			echo '<li class="tracking_number_error">Failed - Empty Tracking Number - Order ' . esc_html( $order_number ) . '</li>';
			exit;
		}

		if ( preg_match( '/^[+-]?[0-9]+(\.[0-9]+)?E[+-][0-9]+$/', $tracking_number ) ) {
			echo '<li class="tracking_number_error">Failed - Invalid Tracking Number - Order ' . esc_html( $order_number ) . '</li>';
			exit;
		}
		
		if ( empty( $date_shipped ) ) {
			echo '<li class="empty_date_shipped_error">Failed - Empty Date Shipped - Order ' . esc_html( $order_number ) . '</li>';
			exit;
		}			
		
		if ( !$this->isDate( $date_shipped, $date_format_for_csv_import ) ) {
			echo '<li class="invalid_date_shipped_error">Failed - Invalid Date Shipped - Order ' . esc_html( $order_number ) . '</li>';
			exit;
		}	
		
		if ( 'm-d-Y' == $date_format_for_csv_import ) {
			$date_array = explode( '-', $date_shipped );
			$date_shipped = $date_array[1] . '-' . $date_array[0] . '-' . $date_array[2];			
		}
		
		$tracking_items = ast_get_tracking_items( $order_id );	
		
		if ( 1 == $replace_tracking_info ) {
			
			$order = wc_get_order($order_id);
			
			if ( $order ) {	
						
				if ( count( $tracking_items ) > 0 ) {
					
					foreach ( $tracking_items as $key => $item ) {								
						do_action( 'delete_tracking_number_from_trackship', $tracking_items, $item['tracking_id'], $order_id );
						unset( $tracking_items[ $key ] );												
					}
					$wast->save_tracking_items( $order_id, $tracking_items );
				}
			}
		}
		
		if ( $shippment_provider && $tracking_number && $date_shipped ) {
					
			$tracking_provider = $this->get_provider_slug_from_name( $tracking_provider );
				
			$args = array(
				'tracking_provider' => $tracking_provider,					
				'tracking_number'   => $tracking_number,
				'date_shipped'      => $date_shipped,
				'status_shipped'	=> $status_shipped,
			);
				
			if ( '' != $sku ) {				
				
				$products_list = array();
				
				if ( $qty > 0 ) {
					
					$product_id = ast_get_product_id_by_sku( $sku );
					
					if ( $product_id ) {
						
						$product_data =  (object) array (							
							'product' => $product_id,
							'qty' => $qty,
						);
						
						array_push( $products_list, $product_data );
						
						$product_data_array = array();
						$product_data_array[ $product_id ] = $qty;
						
						$status_shipped = ( isset( $_POST['status_shipped'] ) ? wc_clean( $_POST['status_shipped'] ) : '' );
						
						$autocomplete_order_tpi = get_option( 'autocomplete_order_tpi', 0 );
						if ( 1 == $autocomplete_order_tpi ) {
							$status_shipped = $this->autocomplete_order_after_adding_all_products( $order_id, $status_shipped, $products_list );
							$args['status_shipped'] = $status_shipped;
						}						
						
						if ( count( $tracking_items ) > 0 ) {								
							foreach ( $tracking_items as $key => $item ) {						
								if ( $item['tracking_number'] == $tracking_number ) {
									
									if ( isset( $item['products_list'] ) && !empty( $item['products_list'] ) ) {
										
										$product_list_array = array();
										foreach ( $item['products_list'] as $item_product_list ) {														
											$product_list_array[ $item_product_list->product ] = $item_product_list->qty;
										}																							
										
										$mearge_array = array();										
										foreach ( array_keys( $product_data_array + $product_list_array ) as $product) {										
											$mearge_array[ $product ] = (int) ( isset( $product_data_array[ $product ] ) ? $product_data_array[ $product ] : 0 ) + (int) ( isset( $product_list_array[$product] ) ? $product_list_array[ $product ] : 0 );
										}																								
										
										foreach ( $mearge_array as $productid => $product_qty ) {
											$merge_product_data[] =  (object) array (							
												'product' => $productid,
												'qty' => $product_qty,
											);
										}
											
										if ( !empty( $merge_product_data ) ) {
											$tracking_items[ $key ]['products_list'] = $merge_product_data;	
											$wast->save_tracking_items( $order_id, $tracking_items );

											$order = new WC_Order( $order_id );
											
											do_action( 'update_order_status_after_adding_tracking', $status_shipped, $order );
		
											echo '<li class="success">Success - added tracking info to Order ' . esc_html( $order_number ) . '</li>';
											exit;
										}		
									}											
								}	 
							}																		
						} 
						
						$product_args = array(
							'products_list' => $products_list,				
						);							
					}
				}																																	
				$args = array_merge( $args, $product_args );				
			}																												
			 
			$wast->add_tracking_item( $order_id, $args );
			
			echo '<li class="success">Success - added tracking info to Order ' . esc_html( $order_number ) . '</li>';
			exit;				
		} else {
			echo '<li class="invalid_tracking_data_error">Failed - Invalid Tracking Data</li>';
			exit;
		}		
	}
	
	/*
	* Function for autocompleted order after adding all product through TPI 
	*/
	public function autocomplete_order_after_adding_all_products( $order_id, $status_shipped, $products_list ) {
	
		$order = wc_get_order( $order_id );
		$items = $order->get_items();
		$items_count = count( $items );
		
		$added_products = $this->get_all_added_product_list_with_qty( $order_id );
		
		$new_products = array();
			
		foreach ( $products_list as $in_list ) {
			
			if ( isset( $new_products[ $in_list->product ] ) ) {
				$new_products[ $in_list->product ] = (int) $new_products[ $in_list->product ] + (int) $in_list->qty;							
			} else {
				$new_products[ $in_list->product ] = $in_list->qty;	
			}			
		}
		
		$total_products_data = array();
	
		foreach ( array_keys( $new_products + $added_products ) as $products ) {
			$total_products_data[ $products ] = ( isset( $new_products[ $products ] ) ? $new_products[ $products ] : 0 ) + ( isset( $added_products[ $products ] ) ? $added_products[ $products ] : 0 );
		}			
		
		$orders_products_data = array();
		foreach ( $items as $item ) {																
			$checked = 0;
			$qty = $item->get_quantity();
			
			$variation_id = $item->get_variation_id();
			$product_id = $item->get_product_id();					
			
			if ( 0 != $variation_id ) {
				$product_id = $variation_id;
			}
			
			$orders_products_data[ $product_id ] = $qty;
		}				
		
		$change_status = 0;
		$autocomplete_order = true;				
		
		foreach ( $orders_products_data as $product_id => $qty ) {		
			if (isset( $total_products_data[ $product_id ] ) ) {
				if ( $qty > $total_products_data[ $product_id ] ) {
					$autocomplete_order = false;
					$change_status = 1;
				} else {
					$change_status = 1;
				}
			} else {
				$autocomplete_order = false;
			}
		}
		
		if ( $autocomplete_order && 1 == $change_status ) {
			$status_shipped = 1;
		}
		return $status_shipped;
	}
	
	/*
	* Function for get already added product in TPI
	*/
	public function get_all_added_product_list_with_qty( $order_id ) {
		
		$ast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
		$tracking_items = ast_get_tracking_items( $order_id );
		
		$product_list = array();			
		
		foreach ( $tracking_items as $tracking_item ) {			
			if ( isset( $tracking_item[ 'products_list' ] ) ) {
				$product_list[] = $tracking_item[ 'products_list' ];				
			}
		}
		
		$all_list = array();
		foreach ( $product_list as $list ) {			
			foreach ( $list as $in_list ) {
				if ( isset( $all_list[ $in_list->product ] ) ) {
					$all_list[ $in_list->product ] = (int) $all_list[ $in_list->product ] + (int) $in_list->qty;
				} else {
					$all_list[ $in_list->product ] = $in_list->qty;	
				}
			}				
		}
		
		return $all_list;
	}
	
	/*
	* Updated order status to Shipped(Completed), Partially Shipped, Updated Tracking
	*/
	public function update_order_status_after_adding_tracking( $status_shipped, $order ) {
		
		$order_id = $order->get_id();
		
		if ( 1 == $status_shipped ) {

			$custom_shipped = apply_filters( 'check_for_custom_shipped', false, $status_shipped, $order );
			
			if ( !$custom_shipped ) {
				if ( 'completed' == $order->get_status() ) {								
					do_action( 'send_order_to_trackship', $order_id );	
				} else {
					$order->update_status( 'completed' );
				}			
			}
		}
		
		if ( 2 == $status_shipped ) {
			
			$wc_ast_status_partial_shipped = get_option( 'wc_ast_status_partial_shipped' );
			
			if ( $wc_ast_status_partial_shipped ) {			
				
				$previous_order_status = $order->get_status();
				
				if ( 'partial-shipped' == $previous_order_status ) {								
					WC()->mailer()->emails['WC_Email_Customer_Partial_Shipped_Order']->trigger( $order_id, $order );	
				}
				
				$order->update_status('partial-shipped');
				do_action( 'send_order_to_trackship', $order_id );
			}
		}
		
		if ( 3 == $status_shipped ) {
			
			$wc_ast_status_updated_tracking = get_option( 'wc_ast_status_updated_tracking' );
			
			if ( $wc_ast_status_updated_tracking ) {			
				
				$previous_order_status = $order->get_status();
				
				if ( 'updated-tracking' == $previous_order_status ) {								
					WC()->mailer()->emails['WC_Email_Customer_Updated_Tracking_Order']->trigger( $order_id, $order );	
				}
				
				$order->update_status( 'updated-tracking' );
				do_action( 'send_order_to_trackship', $order_id );
			}
		}	
	}
	
	/**
	* Check if the value is a valid date
	*
	* @param mixed $value
	*
	* @return boolean
	*/
	public function isDate( $date, $format = 'd-m-Y' ) {
		if ( !$date ) {
			return false;
		}
			
		$d = DateTime::createFromFormat( $format, $date );
		// The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
		return $d && $d->format( $format ) === $date;
	}
	
	/*
	* Change completed order email title to Shipped Order
	*/
	public function change_completed_woocommerce_email_title( $email_title, $email ) {
		$wc_ast_status_shipped = get_option( 'wc_ast_status_shipped', 0 );		
		// Only on backend Woocommerce Settings "Emails" tab
		if ( 1 == $wc_ast_status_shipped ) {
			if ( isset( $_GET['page'] ) && 'wc-settings' == $_GET['page'] && isset( $_GET['tab'] ) && 'email' == $_GET['tab'] ) {
				switch ( $email->id ) {
					case 'customer_completed_order':
						$email_title = __( 'Shipped Order', 'woo-advanced-shipment-tracking' );
						break;
				}
			}
		}
		return $email_title;
	}
	
	/*
	* Add action button in order list to change order status from completed to delivered
	*/
	public function add_delivered_order_status_actions_button( $actions, $order ) {
		
		wp_enqueue_style( 'ast_styles', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/admin.css', array(), wc_advanced_shipment_tracking()->version );	
		wp_enqueue_script( 'woocommerce-advanced-shipment-tracking-js', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/admin.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version);
		wp_localize_script(
			'woocommerce-advanced-shipment-tracking-js',
			'ast_orders_params',
			array(
				'order_nonce' => wp_create_nonce( 'ast-order-list' ),
			)
		);			
		
		$wc_ast_show_orders_actions = get_option( 'wc_ast_show_orders_actions' );
		$order_array = array();
		
		foreach ( (array) $wc_ast_show_orders_actions as $order_status => $value ) {
			if ( 1 == $value ) {
				array_push($order_array, $order_status);			
			}	
		}
		
		if ( $order->get_shipping_method() != 'Local pickup' && $order->get_shipping_method() != 'Local Pickup' ) {		
			if ( $order->has_status( $order_array ) ) {			
				$actions['add_tracking'] = array(
					'url'       => '#' . $order->get_id(),
					'name'      => __( 'Add Tracking', 'woo-advanced-shipment-tracking' ),
					'icon' => '<i class="fa fa-map-marker">&nbsp;</i>',
					'action'    => 'add_inline_tracking', // keep "view" class for a clean button CSS
				);		
			}
		}
		
		$wc_ast_status_shipped = get_option( 'wc_ast_status_shipped' );
		if ( $wc_ast_status_shipped ) {
			$actions['complete']['name'] = __( 'Mark as Shipped', 'woo-advanced-shipment-tracking' );
		}
		
		return $actions;
	}	
	
	/*
	* Get providers list html
	*/
	public function get_provider_html( $page = 1, $search_term = null ) {
		
		$upload_dir   = wp_upload_dir();	
		$ast_directory = $upload_dir['baseurl'] . '/ast-shipping-providers/'; 

		global $wpdb;
		$WC_Countries = new WC_Countries();
		$countries = $WC_Countries->get_countries();
		
		// items per page
		$items_per_page = 50;
		
		// offset
		$offset = ( $page - 1 ) * $items_per_page;

		if ( null != $search_term ) {
			$totla_shipping_provider = $wpdb->get_row( $wpdb->prepare( 'SELECT COUNT(*) as total_providers FROM %1s WHERE provider_name LIKE "%%%2$s%%"', $this->table, $search_term ) );
			$shippment_providers = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s WHERE provider_name LIKE "%%%2$s%%" ORDER BY shipping_default ASC, display_in_order DESC, trackship_supported DESC, id ASC LIMIT %3$d, %4$d', $this->table, $search_term, $offset, $items_per_page ) );			
		} else {
			$totla_shipping_provider = $wpdb->get_row( $wpdb->prepare( 'SELECT COUNT(*) as total_providers FROM %1s', $this->table ) );			
			$shippment_providers = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s ORDER BY shipping_default ASC, display_in_order DESC, trackship_supported DESC, id ASC LIMIT %d, %d', $this->table, $offset, $items_per_page ) );
		}	
		
		$total_provders = $totla_shipping_provider->total_providers;			

		foreach ( $shippment_providers as $key => $value ) {			
			$search = array('(US)', '(UK)');
			$replace = array('', '');

			if ( $value->shipping_country && 'Global' != $value->shipping_country ) {
				$country = str_replace( $search, $replace, $WC_Countries->countries[ $value->shipping_country ] );
				$shippment_providers[ $key ]->country = $country;			
			} elseif ( $value->shipping_country && 'Global' == $value->shipping_country ) {
				$shippment_providers[ $key ]->country = 'Global';
			}
		}

		?>
		<div class="provider_list">
			<?php 
			if ( $shippment_providers ) {
				?>
			<div class="provider-grid-row grid-row">
				<?php 
				foreach ( $shippment_providers as $d_s_p ) {
				$provider_type = ( 1 == $d_s_p->shipping_default ) ? 'default_provider' : 'custom_provider';
					?>
				<div class="grid-item">					
					<div class="grid-top">
						<div class="grid-provider-img">
							<?php  
							$custom_thumb_id = $d_s_p->custom_thumb_id;
							if ( 1 == $d_s_p->shipping_default ) {
								if ( 0 != $custom_thumb_id ) {
									$image_attributes = wp_get_attachment_image_src( $custom_thumb_id , array( '60', '60' ) );
									$provider_image = $image_attributes[0];
								} else {
									$provider_image = $ast_directory . '' . sanitize_title( $d_s_p->provider_name ) . '.png?v=' . wc_advanced_shipment_tracking()->version;
								}
								echo '<img class="provider-thumb" src="' . esc_url( $provider_image ) . '">';
							} else { 
								$image_attributes = wp_get_attachment_image_src( $custom_thumb_id , array( '60', '60' ) );
								
								if ( 0 != $custom_thumb_id ) { 
									echo '<img class="provider-thumb" src="' . esc_url( $image_attributes[0] ) . '">';
								} else { 
									echo '<img class="provider-thumb" src="' . esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ) . 'assets/images/icon-default.png">';
								}  
							}
							?>
						</div>
						<div class="grid-provider-name">
							<span class="provider_name">
								<?php 
								esc_html_e( $d_s_p->provider_name );
								$enable_edit = false;
								
								if ( isset( $d_s_p->custom_provider_name ) && '' != $d_s_p->custom_provider_name ) { 
									esc_html_e( ' (' . $d_s_p->custom_provider_name . ')' ); 
									$enable_edit = true;
								} 
								
								if ( isset( $d_s_p->api_provider_name ) && '' != $d_s_p->api_provider_name ) {
									$enable_edit = true;
									if ( $this->isJSON( $d_s_p->api_provider_name ) && class_exists( 'ast_pro' ) ) {
										$api_count = count( json_decode( $d_s_p->api_provider_name ) );
									} else {
										$api_count = 1;
									}
									$api_text = __( 'API aliases', 'woo-advanced-shipment-tracking' );
									esc_html_e( ' (' . $api_count . ' ' . $api_text . ')' );
								}
								?>
							</span>																		
							<span class="provider_country">
								<?php
								$search  = array('(US)', '(UK)');
								$replace = array('', '');
								
								if ( $d_s_p->shipping_country && 'Global' != $d_s_p->shipping_country ) {
									esc_html_e( str_replace( $search, $replace, $WC_Countries->countries[ $d_s_p->shipping_country ] ) );
								} elseif ( $d_s_p->shipping_country && 'Global' == $d_s_p->shipping_country ) {
									esc_html_e( 'Global' );
								} 
								?>
							</span>
						</div>
						<div class="grid-provider-settings">
							<?php
							
							$edit_provider_class = ( $enable_edit ) ? apply_filters( 'edit_provider_class', 'edit_provider' ) : 'upgrade_to_ast_pro';
							
							if ( 0 == $d_s_p->shipping_default ) { 
								echo '<span class="dashicons dashicons-trash remove provider_actions_btn" data-pid="' . esc_html( $d_s_p->id ) . '"></span>';
							} 
							?>
							<span class="dashicons dashicons-admin-generic <?php esc_html_e( $edit_provider_class ); ?> provider_actions_btn" data-provider="<?php esc_html_e( $provider_type ); ?>" data-pid="<?php esc_html_e( $d_s_p->id ); ?>"></span>
						</div>
					</div>
					<div class="grid-bottom">
						<div class="grid-provider-ts">
							<?php 
							if ( 1 == $d_s_p->trackship_supported ) { 
								echo '<span class="dashicons dashicons-yes-alt"></span>'; 
							} else { 
								echo '<span class="dashicons dashicons-dismiss"></span>'; 
							} 
							?>
							<span>TrackShip</span>
						</div>
						<div class="grid-provider-enable">
							<?php $checked = ( 1 == $d_s_p->display_in_order ) ? 'checked' : ''; ?>
							<input class="ast-tgl ast-tgl-flat status_slide" id="list-switch-<?php esc_html_e( $d_s_p->id ); ?>" name="select_custom_provider[]" type="checkbox" <?php esc_html_e( $checked ); ?> value="<?php esc_html_e( $d_s_p->id ); ?>"/>
							<label class="ast-tgl-btn" for="list-switch-<?php esc_html_e( $d_s_p->id ); ?>"></label>
						</div>
					</div>						
				</div>
				<?php } ?>
								
			</div>			
			<?php 
			} else {
				?>
				<p class="provider_message">
					<?php
					/* translators: %s: replace with status */	
					printf( esc_html_e( "You don't have any %s shipping providers.", 'woo-advanced-shipment-tracking' ), esc_html( $status ) ); 
					?>
				</p>
				<?php 
			}
			$total_pages = ceil($total_provders / $items_per_page);	
			?>
			<div class="hip-pagination">
				<?php 
				for ( $i=1; $i <= $total_pages; $i++ ) {
					if ( $i == $page ) {
						echo '<a class="active">' . esc_html( $i ) . '</a>';
					} else {
						echo '<a class="pagination_link" id="' . esc_html( $i ) . '">' . esc_html( $i ) . '</a>';
					}
				}
				?>
			</div>
		</div>
		<?php 
	}
	
	public function paginate_shipping_provider_list() {
		
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			exit( 'You are not allowed' );
		}

		check_ajax_referer( 'nonce_shipping_provider', 'security' );
		
		$page = isset( $_POST['page'] ) ? wc_clean( $_POST['page'] ) : '';
		$html = $this->get_provider_html( $page );		
		exit;
	}
	
	public function filter_shipping_provider_list() {
		
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			exit( 'You are not allowed' );
		}

		check_ajax_referer( 'nonce_shipping_provider', 'security' );

		$search_term = isset( $_POST['search_term'] ) ? wc_clean( $_POST['search_term'] ) : '';
		$html = $this->get_provider_html( 1, $search_term );		
		exit;
	}

	/*
	* Check if valid json
	*/
	public function isJSON( $string ) {
		return is_string( $string ) && is_array( json_decode( $string, true ) ) && ( json_last_error() == JSON_ERROR_NONE ) ? true : false;
	}
			
	/*
	* Update shipment provider status
	*/
	public function update_shipment_status_fun() {
		
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			exit( 'You are not allowed' );
		}
		
		check_ajax_referer( 'nonce_shipping_provider', 'security' );		
		
		$checked = isset( $_POST['checked'] ) ? wc_clean( $_POST['checked'] ) : '';
		$id = isset( $_POST['id'] ) ? wc_clean( $_POST['id'] ) : '';
		
		global $wpdb;
		$success = $wpdb->update( $this->table, 
			array(
				'display_in_order' => $checked,
			),	
			array( 'id' => $id )
		);
		exit;	
	}
	
	/**
	* Update default provider function 
	*/
	public function update_default_provider_fun() {
		
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			exit( 'You are not allowed' );
		}
		
		check_ajax_referer( 'nonce_shipping_provider', 'security' );
		
		$default_provider = isset( $_POST['default_provider'] ) ? wc_clean( $_POST['default_provider'] ) : '';
		$checked = isset( $_POST['checked'] ) ? wc_clean( $_POST['checked'] ) : '';
		
		if ( 1 == $checked ) {
			update_option( 'wc_ast_default_provider', $default_provider );
		} else {
			update_option( 'wc_ast_default_provider', '' );
		}
		exit;
	}
	
	/*
	* Delet provide by ajax
	*/
	public function woocommerce_shipping_provider_delete() {

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			exit( 'You are not allowed' );
		}
		
		check_ajax_referer( 'nonce_shipping_provider', 'security' );
		
		$provider_id = isset( $_POST['provider_id'] ) ? wc_clean( $_POST['provider_id'] ) : '';
		
		if ( ! empty( $provider_id ) ) {
			global $wpdb;
			$where = array(
				'id' => $provider_id,
				'shipping_default' => 0
			);
			$wpdb->delete( $this->table, $where );
		}
		$html = $this->get_provider_html( 1 );		
		exit;
	}
	
	/**
	* Get shipping provider details fun 
	*/
	public function get_provider_details_fun() {
		
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			exit( 'You are not allowed' );
		}
		
		check_ajax_referer( 'nonce_shipping_provider', 'security' );
		
		$id = isset( $_POST['provider_id'] ) ? wc_clean( $_POST['provider_id'] ) : '';		
		global $wpdb;
		
		$shippment_provider = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s WHERE id=%d', $this->table, $id ) );
		
		if ( 0 != $shippment_provider[0]->custom_thumb_id ) {
			$image = wp_get_attachment_url( $shippment_provider[0]->custom_thumb_id );	
		} else {
			$image = null;
		}
		
		$provider_name = $shippment_provider[0]->provider_name;			
		$custom_provider_name = $shippment_provider[0]->custom_provider_name;
		$api_provider_name = $shippment_provider[0]->api_provider_name;		
		
		echo json_encode( array('id' => $shippment_provider[0]->id,'provider_name' => $provider_name,'custom_provider_name' => $custom_provider_name,'api_provider_name' => $api_provider_name,'provider_url' => $shippment_provider[0]->provider_url,'shipping_country' => $shippment_provider[0]->shipping_country,'custom_thumb_id' => $shippment_provider[0]->custom_thumb_id,'image' => $image) );
		exit;			
	}
	
	/**
	* Update custom shipping provider and returen html of it
	*/
	public function update_custom_shipment_provider_fun() {
		
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			exit( 'You are not allowed' );
		}
		
		check_ajax_referer( 'nonce_edit_shipping_provider', 'nonce_edit_shipping_provider' );
		
		global $wpdb;		
		
		$provider_id = isset( $_POST['provider_id'] ) ? wc_clean( $_POST['provider_id'] ) : '';
		$tracking_url = isset( $_POST['tracking_url'] ) ? wc_clean( $_POST['tracking_url'] ) : '';
		$thumb_id = isset( $_POST['thumb_id'] ) ? wc_clean( $_POST['thumb_id'] ) : '';
		$shipping_provider = isset( $_POST['shipping_provider'] ) ? wc_clean( $_POST['shipping_provider'] ) : '';
		$shipping_display_name = isset( $_POST['shipping_display_name'] ) ? wc_clean( $_POST['shipping_display_name'] ) : '';
		$shipping_country = isset( $_POST['shipping_country'] ) ? wc_clean( $_POST['shipping_country'] ) : '';
		$api_provider_name = isset( $_POST['api_provider_name'] ) ? wc_clean( $_POST['api_provider_name'] ) : '';
		$provider_type = isset( $_POST['provider_type'] ) ? wc_clean( $_POST['provider_type'] ) : '';
		
		if ( [] == array_filter( $api_provider_name ) ) {
			$api_provider_name = null;			
		} else {
			$api_provider_name = wc_clean( json_encode( $api_provider_name ) );
		}	
				
		if ( 'default_provider' == $provider_type ) {
			$data_array = array(				
				'custom_provider_name' => $shipping_display_name,
				'api_provider_name' => $api_provider_name,				
				'custom_thumb_id' => $thumb_id,				
			);				
		} else {
			$data_array = array(
				'shipping_country' => $shipping_country,
				'provider_name' => $shipping_provider,
				'custom_provider_name' => $shipping_display_name,
				'ts_slug' => $shipping_provider,
				'custom_thumb_id' => $thumb_id,
				'provider_url' => $tracking_url		
			);	
		}
		
		$where_array = array(
			'id' => $provider_id,			
		);
		$wpdb->update( $this->table, $data_array, $where_array );
		$html = $this->get_provider_html( 1 );		
		exit;
	}

	/**
	* Reset default provider
	*/
	public function reset_default_provider_fun() {
		
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			exit( 'You are not allowed' );
		}
		
		check_ajax_referer( 'nonce_shipping_provider', 'security' );
		
		global $wpdb;		
		
		$provider_id = isset( $_POST['provider_id'] ) ? wc_clean( $_POST['provider_id'] ) : '';
		
		$data_array = array(				
			'custom_provider_name' => null,				
			'custom_thumb_id' => null,
			'api_provider_name' => null,			
		);	
		
		$where_array = array(
			'id' => $provider_id,			
		);
		
		$wpdb->update( $this->table, $data_array, $where_array );
		$html = $this->get_provider_html( 1 );
		exit;
	}	
	
	/**
	* Update bulk status of providers to active
	*/
	public function update_provider_status_fun() {
		
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			exit( 'You are not allowed' );
		}
		
		check_ajax_referer( 'nonce_shipping_provider', 'security' );
		
		global $wpdb;
		
		$status = isset( $_POST['status'] ) ? wc_clean( $_POST['status'] ) : '';
		
		$data_array = array(
			'display_in_order' => $status,			
		);
		
		$display_in_order = ( 1 == $status ) ? 0 : 1;
		
		$where_array = array(
			'display_in_order' => $display_in_order,			
		);
		
		$wpdb->update( $this->table, $data_array, $where_array );
		$html = $this->get_provider_html( 1 );
		exit;
	}	

	/**
	 * Add bulk filter for Shipping provider in orders list
	 *
	 * @since 2.4
	 */
	public function filter_orders_by_shipping_provider() {
		global $typenow, $wpdb;
		$default_shippment_providers = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s ORDER BY shipping_default ASC, display_in_order DESC, trackship_supported DESC, id ASC', $this->table ) );
		
		if ( 'shop_order' === $typenow ) {
			?>
			<select name="_shop_order_shipping_provider" id="dropdown_shop_order_shipping_provider">
				<option value=""><?php esc_html_e( 'Filter by shipping provider', 'woo-advanced-shipment-tracking' ); ?></option>
				<?php foreach ( $default_shippment_providers as $provider ) : ?>
					<option value="<?php echo esc_attr( $provider->ts_slug ); ?>" <?php echo esc_attr( isset( $_GET['_shop_order_shipping_provider'] ) ? selected( $provider->ts_slug, wc_clean( $_GET['_shop_order_shipping_provider'] ), false ) : '' ); ?>>
						<?php printf( '%1$s', esc_html( $provider->provider_name ) ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		<?php
		}
	}	
	
	/**
	 * Process bulk filter action for shipment status orders
	 *
	 * @since 3.0.0
	 * @param array $vars query vars without filtering
	 * @return array $vars query vars with (maybe) filtering
	 */
	public function filter_orders_by_shipping_provider_query( $vars ) {
		global $typenow;		
		if ( 'shop_order' === $typenow && isset( $_GET['_shop_order_shipping_provider'] ) && '' != $_GET['_shop_order_shipping_provider'] ) {
			$vars['meta_query'][] = array(
				'key'       => '_wc_shipment_tracking_items',
				'value'     => wc_clean( $_GET['_shop_order_shipping_provider'] ),
				'compare'   => 'LIKE'
			);						
		}

		return $vars;
	}			
	
	/**
	 * Process bulk filter action for shipment status orders
	 *
	 * @since 2.7.4
	 * @param array $vars query vars without filtering
	 * @return array $vars query vars with (maybe) filtering
	 */
	public function filter_orders_by_tracking_number_query( $search_fields ) {
		$search_fields[] = '_wc_shipment_tracking_items';
		return $search_fields;
	}	
	
	/*
	* get tracking provider slug (ts_slug) from database
	* 
	* return provider slug
	*/
	public function get_provider_slug_from_name( $tracking_provider_name ) {
		
		global $wpdb;
		
		$tracking_provider = $wpdb->get_var( $wpdb->prepare( 'SELECT ts_slug FROM %1s WHERE api_provider_name = %s', $this->table, $tracking_provider_name ) );				
		
		if ( !$tracking_provider ) {			
			$tracking_provider = $wpdb->get_var(  $wpdb->prepare( "SELECT ts_slug FROM %1s WHERE JSON_CONTAINS(LOWER(api_provider_name), LOWER('[" . '"' . $tracking_provider_name . '"' . "]') )", $this->table ) );
		}
		
		if ( !$tracking_provider ) {
			$tracking_provider = $wpdb->get_var( $wpdb->prepare( 'SELECT ts_slug FROM %1s WHERE provider_name = %s', $this->table, $tracking_provider_name ) );
		}		
		
		if ( !$tracking_provider ) {
			$tracking_provider =  $tracking_provider_name ;
		}
		
		return $tracking_provider;
	}
	
	/*
	* function for add more provider btn
	*/
	public function add_more_api_provider() { 
		$tooltip_text = class_exists( 'ast_pro' ) ? __( 'Add API Name alias', 'woo-advanced-shipment-tracking' ) : __( 'Multiple API names mapping is a pro features', 'woo-advanced-shipment-tracking' ) ;
		?>
		<span class="dashicons dashicons-insert woocommerce-help-tip tipTip add_more_api_provider" title="<?php esc_html_e( $tooltip_text ); ?>"></span>	
		<?php 
	}
}
