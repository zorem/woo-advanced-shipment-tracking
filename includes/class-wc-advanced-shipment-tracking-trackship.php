<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Advanced_Shipment_Tracking_Trackship {
	
	/**
	 * Initialize the main plugin function
	*/
    public function __construct() {								
		
		global $wpdb;
		$this->table = $wpdb->prefix . "woo_shippment_provider";
		
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
	 * @since  1.0
	 * @return smswoo_license
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	/*
	 * init function
	 *
	 * @since  1.0
	*/
	public function init() {	
			
		add_action( 'admin_enqueue_scripts', array( $this, 'trackship_styles' ), 4 );
		add_action('admin_menu', array( $this, 'register_woocommerce_trackship_menu' ), 99 );		
		
		$wc_ast_api_key = get_option( 'wc_ast_api_key' ); 
		if ( $wc_ast_api_key ) {
		
			add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ) );											
			
			//ajax save admin trackship settings
			add_action( 'wp_ajax_wc_ast_trackship_form_update', array( $this, 'wc_ast_trackship_form_update_callback' ) );
			add_action( 'wp_ajax_trackship_tracking_page_form_update', array( $this, 'trackship_tracking_page_form_update_callback' ) );
			add_action( 'wp_ajax_ts_late_shipments_email_form_update', array( $this, 'ts_late_shipments_email_form_update_callback' ) );
					
			//add Shipment status column after tracking
			add_filter( 'manage_edit-shop_order_columns', array( $this, 'wc_add_order_shipment_status_column_header' ), 20 );
			add_action( 'manage_shop_order_posts_custom_column', array( $this, 'wc_add_order_shipment_status_column_content' ) );
			
			//add bulk action - get shipment status
			add_filter( 'bulk_actions-edit-shop_order', array( $this, 'add_bulk_actions_get_shipment_status' ), 10, 1 );
			
			// Make the action from selected orders to get shipment status
			add_filter( 'handle_bulk_actions-edit-shop_order', array( $this, 'get_shipment_status_handle_bulk_action_edit_shop_order' ), 10, 3 );
			
			// Bulk shipment status sync ajax call from settings
			add_action( 'wp_ajax_bulk_shipment_status_from_settings', array( $this, 'bulk_shipment_status_from_settings_fun' ) );
			
			// Bulk shipment status sync for empty balance ajax call from settings
			add_action( 'wp_ajax_bulk_shipment_status_for_empty_balance_from_settings', array( $this, 'bulk_shipment_status_for_empty_balance_from_settings_fun' ) );
			
			// Bulk shipment status sync for please do connection status ajax call from settings
			add_action( 'wp_ajax_bulk_shipment_status_for_do_connection_from_settings', array( $this, 'bulk_shipment_status_for_do_connection_from_settings_fun' ) );
			
			// The results notice from bulk action on orders
			add_action( 'admin_notices', array( $this, 'shipment_status_bulk_action_admin_notice' ) );
			
			// add 'get_shipment_status' order meta box order action
			add_action( 'woocommerce_order_actions', array( $this, 'add_order_meta_box_get_shipment_status_actions' ) );
			add_action( 'woocommerce_order_action_get_shipment_status_edit_order', array( $this, 'process_order_meta_box_actions_get_shipment_status' ) );
			
			// add bulk order filter for exported / non-exported orders
			$wc_ast_show_shipment_status_filter = get_option( 'wc_ast_show_shipment_status_filter', 0 );
			if ( 1 == $wc_ast_show_shipment_status_filter ) {
				add_action( 'restrict_manage_posts', array( $this, 'filter_orders_by_shipment_status' ) , 20 );
				add_filter( 'request', array( $this, 'filter_orders_by_shipment_status_query' ) );
			}		
			
			// trigger when order status changed to shipped or completed
			add_action( 'woocommerce_order_status_completed', array( $this, 'trigger_woocommerce_order_status_completed' ), 10, 1 );			
			add_action( 'woocommerce_order_status_shipped', array( $this, 'trigger_woocommerce_order_status_completed' ), 10, 1 );
			
			add_action( 'send_order_to_trackship', array( $this, 'trigger_woocommerce_order_status_completed' ), 10, 1 );
			
			add_action( 'woocommerce_order_status_updated-tracking', array( $this, 'trigger_woocommerce_order_status_completed' ), 10, 1 );
			
			// filter for shipment status
			add_filter( 'trackship_status_filter', array( $this, 'trackship_status_filter_func' ), 10 , 1 );
			
			// filter for shipment status icon
			add_filter( 'trackship_status_icon_filter', array( $this, 'trackship_status_icon_filter_func' ), 10 , 2 );				
			
			add_action( 'wcast_retry_trackship_apicall', array( $this, 'wcast_retry_trackship_apicall_func' ) );
			
			add_action( 'wp_ajax_update_shipment_status_email_status', array( $this, 'update_shipment_status_email_status_fun' ) );
	
			add_action( 'wp_ajax_update_enable_late_shipments_email', array( $this, 'update_enable_late_shipments_email_fun' ) );
		
			add_action( 'ast_shipment_tracking_end', array( $this, 'display_shipment_tracking_info' ), 10, 2 );
			
			add_action( 'delete_tracking_number_from_trackship', array( $this, 'delete_tracking_number_from_trackship' ), 10, 3 );
			
			//fix shipment tracking for deleted tracking
			add_action( 'fix_shipment_tracking_for_deleted_tracking', array( $this, 'func_fix_shipment_tracking_for_deleted_tracking' ), 10, 3 );
			
			add_action( 'wp_dashboard_setup', array( $this, 'ast_add_dashboard_widgets' ) );			
			
			//filter in shipped orders
			add_filter( 'is_order_shipped', array( $this, 'check_tracking_exist' ), 10, 2 );
			add_filter( 'is_order_shipped', array( $this, 'check_order_status' ), 5, 2 );					
			
			add_action( 'wp_ajax_wc_ast_trackship_automation_form_update', array( $this, 'wc_ast_trackship_automation_form_update' ) );
		}
	}
	
	/**
	* Load trackship styles.
	*/
	public function trackship_styles( $hook ) {
		
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';	
		
		wp_register_style( 'trackship_styles',  wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/trackship.css', array(), wc_advanced_shipment_tracking()->version );
		wp_register_script( 'trackship_script',  wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/trackship.js', array( 'jquery', 'wp-util' ), wc_advanced_shipment_tracking()->version, true );
		wp_localize_script( 'trackship_script', 'trackship_script', array(
			'i18n' => array(				
				'data_saved' => __( 'Data saved successfully.', 'woo-advanced-shipment-tracking' ),				
			),			
		) );				
		
		if ( !isset( $_GET['page'] ) ) {
			return;
		}	
		
		if ( 'woocommerce-advanced-shipment-tracking' != $_GET['page'] && 'trackship-for-woocommerce' != $_GET['page'] ) {
			return;
		}	
								
		wp_enqueue_style( 'trackship_styles' );								
		wp_enqueue_script( 'trackship_script' );				
	}
	
	/**
	* TrackShip Menu Array.
	*/
	public function ast_menu_trackship_options() {
		$trackship_tab = array(
			'ts_dashboard' => array(					
				'title'		=> __( 'Dashboard', 'woo-advanced-shipment-tracking' ),
				'show'      => true,
				'class'     => 'tab_label first_label',
				'data-tab'  => 'ts_dashboard',
				'data-label' => __( 'Dashboard', 'woo-advanced-shipment-tracking' ),
				'name'  => 'tabs',
			),
			'ts_notifications' => array(					
				'title'		=> __( 'Notifications', 'woo-advanced-shipment-tracking' ),
				'show'      => true,
				'class'     => 'tab_label',
				'data-tab'  => 'ts_notifications',
				'data-label' => __( 'Notifications', 'woo-advanced-shipment-tracking' ),
				'name'  => 'tabs',
			),		
		);
		return apply_filters( 'trackship_menu_tab_options', $trackship_tab );		
	}	
	
	/**
	* TrackShip Shipment status notification data.
	*/
	public function trackship_shipment_status_notifications_data() {
		
		$intransit_customizer = new wcast_intransit_customizer_email();		

		$notifications_data = array(
			'in_transit' => array(					
				'title'			=> __( 'In Transit', 'woo-advanced-shipment-tracking' ),
				'slug' => 'in-transit',
				'option_name'	=> 'wcast_intransit_email_settings',
				'enable_status_name' => 'wcast_enable_intransit_email',		
				'customizer_url' => $intransit_customizer->get_customizer_url( 'trackship_shipment_status_email', 'in_transit', 'ts_dashboard' ),	
			),
			'on_hold' => array(					
				'title'	=> __( 'On Hold', 'woo-advanced-shipment-tracking' ),
				'slug'  => 'on-hold',
				'option_name'	=> 'wcast_onhold_email_settings',
				'enable_status_name' => 'wcast_enable_onhold_email',		
				'customizer_url' => $intransit_customizer->get_customizer_url( 'trackship_shipment_status_email', 'on_hold', 'ts_dashboard' ),	
			),
			'return_to_sender' => array(					
				'title'	=> __( 'Return To Sender', 'woo-advanced-shipment-tracking' ),
				'slug'  => 'return-to-sender',
				'option_name'	=> 'wcast_returntosender_email_settings',
				'enable_status_name' => 'wcast_enable_returntosender_email',		
				'customizer_url' => $intransit_customizer->get_customizer_url( 'trackship_shipment_status_email', 'return_to_sender', 'ts_dashboard' ),	
			),
			'available_for_pickup' => array(					
				'title'	=> __( 'Available For Pickup', 'woo-advanced-shipment-tracking' ),
				'slug'  => 'available-for-pickup',
				'option_name'	=> 'wcast_availableforpickup_email_settings',
				'enable_status_name' => 'wcast_enable_availableforpickup_email',		
				'customizer_url' => $intransit_customizer->get_customizer_url( 'trackship_shipment_status_email', 'available_for_pickup', 'ts_dashboard' ),	
			),
			'out_for_delivery' => array(					
				'title'	=> __( 'Out For Delivery', 'woo-advanced-shipment-tracking' ),
				'slug'  => 'out-for-delivery',
				'option_name'	=> 'wcast_outfordelivery_email_settings',
				'enable_status_name' => 'wcast_enable_outfordelivery_email',		
				'customizer_url' => $intransit_customizer->get_customizer_url( 'trackship_shipment_status_email', 'out_for_delivery', 'ts_dashboard' ),	
			),
			'delivered' => array(					
				'title'	=> __( 'Delivered', 'woo-advanced-shipment-tracking' ),
				'slug'  => 'delivered-status',
				'option_name'	=> 'wcast_delivered_email_settings',
				'enable_status_name' => 'wcast_enable_delivered_status_email',		
				'customizer_url' => $intransit_customizer->get_customizer_url( 'trackship_shipment_status_email', 'delivered', 'ts_dashboard' ),	
			),
			'failure' => array(					
				'title'	=> __( 'Failed Attempt', 'woo-advanced-shipment-tracking' ),
				'slug'  => 'failed-attempt',
				'option_name'	=> 'wcast_failure_email_settings',
				'enable_status_name' => 'wcast_enable_failure_email',		
				'customizer_url' => $intransit_customizer->get_customizer_url( 'trackship_shipment_status_email', 'failure', 'ts_dashboard' ),	
			),
			'exception' => array(					
				'title'	=> __( 'Exception', 'woo-advanced-shipment-tracking' ),
				'slug'  => 'exception',
				'option_name'	=> 'wcast_exception_email_settings',
				'enable_status_name' => 'wcast_enable_exception_email',		
				'customizer_url' => $intransit_customizer->get_customizer_url( 'trackship_shipment_status_email', 'exception', 'ts_dashboard' ),	
			),	
		);
		return $notifications_data;
	}
	
	/*
	* Admin Menu add function
	* WC sub menu
	*/
	public function register_woocommerce_trackship_menu() {
		add_submenu_page( 'woocommerce', 'TrackShip', 'TrackShip', 'manage_woocommerce', 'trackship-for-woocommerce', array( $this, 'trackship_page_callback' ) ); 
	}
	
	/*
	* callback for Shipment Tracking page
	*/
	public function trackship_page_callback() { 
		$wc_ast_api_key = get_option('wc_ast_api_key'); 
		?>
		<div class="zorem-layout">
			<div class="zorem-layout__header">
				<h1 class="zorem-layout__header-breadcrumbs">TrackShip</h1>		

				<div class="woocommerce-layout__activity-panel">
					<div class="woocommerce-layout__activity-panel-tabs">
						<button type="button" id="activity-panel-tab-help" class="components-button woocommerce-layout__activity-panel-tab">
							<span class="dashicons dashicons-editor-help"></span>
							Help 
						</button>
					</div>
					<div class="woocommerce-layout__activity-panel-wrapper">
						<div class="woocommerce-layout__activity-panel-content" id="activity-panel-true">
							<div class="woocommerce-layout__activity-panel-header">
								<div class="woocommerce-layout__inbox-title">
									<p class="css-activity-panel-Text">Documentation</p>            
								</div>								
							</div>
							<div>
								<ul class="woocommerce-list woocommerce-quick-links__list">
									<li class="woocommerce-list__item has-action">
										<a href="<?php echo esc_url( 'https://trackship.info/support/' ); ?>" class="woocommerce-list__item-inner" target="_blank" >
											<div class="woocommerce-list__item-before">
												<span class="dashicons dashicons-media-document"></span>	
											</div>
											<div class="woocommerce-list__item-text">
												<span class="woocommerce-list__item-title">
													<div class="woocommerce-list-Text">Get Support</div>
												</span>
											</div>
											<div class="woocommerce-list__item-after">
												<span class="dashicons dashicons-arrow-right-alt2"></span>
											</div>
										</a>
									</li>            
									<li class="woocommerce-list__item has-action">
										<a href="https://trackship.info/documentation/" class="woocommerce-list__item-inner" target="_blank">
											<div class="woocommerce-list__item-before">
												<span class="dashicons dashicons-media-document"></span>
											</div>
											<div class="woocommerce-list__item-text">
												<span class="woocommerce-list__item-title">
													<div class="woocommerce-list-Text">Documentation</div>
												</span>
											</div>
											<div class="woocommerce-list__item-after">
												<span class="dashicons dashicons-arrow-right-alt2"></span>
											</div>
										</a>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>		
			</div>		
			<?php 
			if ( $wc_ast_api_key ) {
			$ast_admin = new WC_Advanced_Shipment_Tracking_Admin();	
			
			do_action( 'ast_settings_admin_notice' );
			
			$trackship = WC_Advanced_Shipment_Tracking_Trackship::get_instance();			
			$completed_order_with_tracking = $trackship->completed_order_with_tracking();		
			$completed_order_with_zero_balance = $trackship->completed_order_with_zero_balance();							
			$completed_order_with_do_connection = $trackship->completed_order_with_do_connection();
			
			$url = 'https://my.trackship.info/wp-json/tracking/get_user_plan';								
			$args['body'] = array(
				'user_key' => $wc_ast_api_key,				
			);
			$response = wp_remote_post( $url, $args );
			if ( !is_wp_error( $response ) ) {
				$plan_data = json_decode($response['body']);
			}	
			?>		
            <div class="woocommerce zorem_admin_layout">
                <div class="ast_admin_content" >
					<div class="ts_nav_div">											
						<?php 
						$this->get_html_menu_tab( $this->ast_menu_trackship_options() ); 
						require_once( 'views/admin_trackship_dashboard.php' );	
						require_once( 'views/admin_status_notifications.php' );
						?>						
					</div>                   					
                </div>				
            </div>            			
			<div id="trackship_settings_snackbar" class="ast_snackbar"><?php esc_html_e( 'Data saved successfully.', 'woo-advanced-shipment-tracking' ); ?></div>	
		</div>		
	<?php 
		} else {
			include 'views/admin_options_trackship_integration.php'; 	
		}
	}	
	
	/*
	* function for create tab menu html
	*/
	public function get_html_menu_tab( $arrays ) { 
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'ts_dashboard' ;
		foreach( (array)$arrays as $id => $array ) { 
			$checked = ( $tab == $array['data-tab'] ) ? 'checked' : '' ;
			?>
			<input class="tab_input" id="<?php esc_html_e( $id ); ?>" name="<?php esc_html_e( $array['name'] ); ?>" type="radio"  data-tab="<?php esc_html_e( $array['data-tab'] ); ?>" data-label="<?php esc_html_e( $array['data-label'] ); ?>" <?php esc_html_e( $checked ); ?> />
			<label class="<?php esc_html_e( $array['class'] ); ?>" for="<?php esc_html_e( $id ); ?>"><?php esc_html_e( $array['title'] ); ?></label>
			<?php  
		}
	}
	
	/*
	* include file on plugin load
	*/
	public function on_plugins_loaded() {					
		require_once wc_advanced_shipment_tracking()->get_plugin_path() . '/includes/customizer/class-trackship-customizer.php';
		require_once wc_advanced_shipment_tracking()->get_plugin_path() . '/includes/customizer/class-wc-tracking-page-customizer.php';
		require_once wc_advanced_shipment_tracking()->get_plugin_path() . '/includes/customizer/class-wc-intransit-email-customizer.php';
		require_once wc_advanced_shipment_tracking()->get_plugin_path() . '/includes/customizer/class-wc-failure-email-customizer.php';
		require_once wc_advanced_shipment_tracking()->get_plugin_path() . '/includes/customizer/class-wc-exception-email-customizer.php';
		require_once wc_advanced_shipment_tracking()->get_plugin_path() . '/includes/customizer/class-wc-outfordelivery-email-customizer.php';
		require_once wc_advanced_shipment_tracking()->get_plugin_path() . '/includes/customizer/class-wc-delivered-email-customizer.php';
		require_once wc_advanced_shipment_tracking()->get_plugin_path() . '/includes/customizer/class-wc-returntosender-email-customizer.php';
		require_once wc_advanced_shipment_tracking()->get_plugin_path() . '/includes/customizer/class-wc-availableforpickup-email-customizer.php';
		require_once wc_advanced_shipment_tracking()->get_plugin_path() . '/includes/customizer/class-wc-onhold-email-customizer.php';
		require_once wc_advanced_shipment_tracking()->get_plugin_path() . '/includes/customizer/class-wc-late-shipments-email-customizer.php';
		require_once wc_advanced_shipment_tracking()->get_plugin_path() . '/includes/trackship-email-manager.php';
	}
	
	/*
	* settings form save
	*/
	public function wc_ast_trackship_form_update_callback() {
		
		if ( ! empty( $_POST ) && check_admin_referer( 'wc_ast_trackship_form', 'wc_ast_trackship_form_nonce' ) ) {
			
			$data2 = $this->get_trackship_general_data();		
			
			foreach ( $data2 as $key2 => $val2 ) {
				if ( isset( $_POST[ $key2 ] ) ) {	
					update_option( $key2, sanitize_text_field( $_POST[ $key2 ] ) );
				}
			}				
			
			echo json_encode( array( 'success' => 'true' ) );
			die();
		}
	}
	
	/*
	* tracking page form save
	*/
	public function trackship_tracking_page_form_update_callback() {
		
		if ( ! empty( $_POST ) && check_admin_referer( 'trackship_tracking_page_form', 'trackship_tracking_page_form_nonce' ) ) {
			
			$data1 = $this->get_tracking_page_data();
			
			foreach ( $data1 as $key1 => $val1 ) {
				if ( isset( $_POST[ $key1 ] ) ) {
					update_option( $key1, sanitize_text_field( $_POST[ $key1 ] ) );
				}
			}
			
			if ( isset( $_POST[ 'wc_ast_trackship_other_page' ] ) ) {
				update_option( 'wc_ast_trackship_other_page', sanitize_text_field( $_POST[ 'wc_ast_trackship_other_page' ] ) );
			}
			
			echo json_encode( array('success' => 'true') );
			die();
		}
	}
	
	/*
	* late shipmenta form save
	*/
	public function ts_late_shipments_email_form_update_callback() {
		
		if ( ! empty( $_POST ) && check_admin_referer( 'ts_late_shipments_email_form', 'ts_late_shipments_email_form_nonce' ) ) {
			
			$wcast_late_shipments_days = isset( $_POST['wcast_late_shipments_days'] ) ? wc_clean( $_POST['wcast_late_shipments_days'] ) : '';
			$wcast_late_shipments_email_to = isset( $_POST['wcast_late_shipments_email_to'] ) ? wc_clean( $_POST['wcast_late_shipments_email_to'] ) : '';			
			$wcast_late_shipments_email_subject = isset( $_POST['wcast_late_shipments_email_subject'] ) ? wc_clean( $_POST['wcast_late_shipments_email_subject'] ) : '';			
			$wcast_late_shipments_email_content = isset( $_POST['wcast_late_shipments_email_content'] ) ? wc_clean( $_POST['wcast_late_shipments_email_content'] ) : '';
			$wcast_late_shipments_trigger_alert = isset( $_POST['wcast_late_shipments_trigger_alert'] ) ? wc_clean( $_POST['wcast_late_shipments_trigger_alert'] ) : '';			
			$wcast_late_shipments_daily_digest_time = isset( $_POST['wcast_late_shipments_daily_digest_time'] ) ? wc_clean( $_POST['wcast_late_shipments_daily_digest_time'] ) : '';
			$wcast_enable_late_shipments_admin_email = isset( $_POST['wcast_enable_late_shipments_admin_email'] ) ? wc_clean( $_POST['wcast_enable_late_shipments_admin_email'] ) : '';

			$late_shipments_email_settings = array(
				'wcast_enable_late_shipments_admin_email' => $wcast_enable_late_shipments_admin_email,
				'wcast_late_shipments_days' => $wcast_late_shipments_days,
				'wcast_late_shipments_email_to' => $wcast_late_shipments_email_to,
				'wcast_late_shipments_email_subject' => $wcast_late_shipments_email_subject,
				'wcast_late_shipments_email_content' => $wcast_late_shipments_email_content,
				'wcast_late_shipments_trigger_alert' => $wcast_late_shipments_trigger_alert,
				'wcast_late_shipments_daily_digest_time' => $wcast_late_shipments_daily_digest_time,
			);
			
			update_option( 'late_shipments_email_settings', $late_shipments_email_settings );
			
			$Late_Shipments = new WC_Advanced_Shipment_Tracking_Late_Shipments();
			$Late_Shipments->remove_cron();
			$Late_Shipments->setup_cron();
		}
	}		
	
	/*
	* get settings tab array data
	* return array
	*/
	public function get_trackship_general_data(){		
		
		$wc_ast_api_key = get_option( 'wc_ast_api_key' );
		$show_trackship_field = ( $wc_ast_api_key ) ? true : false;		
								
		$form_data = array(												
			'wc_ast_show_shipment_status_filter' => array(
				'type'		=> 'tgl_checkbox',
				'tgl_color' => 'green',
				'title'		=> __( 'Display shipment status filter on orders admin', 'woo-advanced-shipment-tracking' ),				
				'show' => $show_trackship_field,
				'class'     => '',				
			),			
		);
		return $form_data;
	}

	/*
	* get settings tab array data
	* return array
	*/
	public function get_tracking_page_data(){		
		
		$wc_ast_api_key = get_option( 'wc_ast_api_key' );
		$show_trackship_field = ( $wc_ast_api_key ) ? true : false;	

		$slug = '';
		$page_list = wp_list_pluck( get_pages(), 'post_title', 'ID' );
		$wc_ast_trackship_page_id = get_option( 'wc_ast_trackship_page_id' );
		$post = get_post( $wc_ast_trackship_page_id ); 
		
		if ( $post ) {
			$slug = $post->post_name;
		}	
		
		$page_desc = ( 'ts-shipment-tracking' != $slug ) ? __( 'You must add the shortcode [wcast-track-order] to the selected page in order for the tracking page to work.', 'woo-advanced-shipment-tracking' ) : '';
		
		$ts_tracking_page_customizer = new ts_tracking_page_customizer();						
		$form_data = array(												
			'wc_ast_use_tracking_page' => array(
				'type'		=> 'tgl_checkbox',
				'tgl_color' => 'green',
				'title'		=> __( 'Enable a tracking page', 'woo-advanced-shipment-tracking' ),				
				'show' 		=> $show_trackship_field,
				'class'     => '',				
			),
			'wc_ast_trackship_page_id' => array(
				'type'		=> 'dropdown_tpage',
				'title'		=> __( 'Select tracking page', 'woo-advanced-shipment-tracking' ),
				'options'   => $page_list,				
				'show' 		=> $show_trackship_field,
				'desc' 		=> $page_desc,
				'class'     => '',
			),
			'wc_ast_trackship_other_page' => array(
				'type'		=> 'text',
				'title'		=> __( 'Other', '' ),						
				'show' 		=> $show_trackship_field,				
				'class'     => '',
			),
			'wc_ast_tracking_page_customize_btn' => array(
				'type'		=> 'button',
				'title'		=> __( 'Tracking Widget Customizer', 'woo-advanced-shipment-tracking' ),						
				'show' 		=> $show_trackship_field,				
				'class'     => '',
				'customize_link'     => $ts_tracking_page_customizer->get_customizer_url( 'ast_tracking_page_section', 'ts_dashboard' ),
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
					"" =>__( 'Select', 'woocommerce' ),
					"#fff" =>__( 'Light', '' ),
					"#000" =>__( 'Dark', '' ),
				),			
				'class'		=> 'status_label_color_th',
				'show'		=> true,
			),							
		);
		return $form_data;
	}
	
	/*
	* Trackship Automation form save
	*/	
	public function wc_ast_trackship_automation_form_update() {		
		$data = $this->get_delivered_data();						
		foreach ( $data as $key => $val ) {																							
			if ( isset( $_POST[ $key ] ) ) {						
				update_option( $key, wc_clean( $_POST[ $key ] ) );
			}
		}
	}
	
	/**
	 * Adds 'shipment_status' column header to 'Orders' page immediately after 'woocommerce-advanced-shipment-tracking' column.
	 *
	 * @param string[] $columns
	 * @return string[] $new_columns
	 */
	public function wc_add_order_shipment_status_column_header( $columns ) {
		wp_enqueue_style( 'trackship_styles' );
		$new_columns = array();
	
		foreach ( $columns as $column_name => $column_info ) {
	
			$new_columns[ $column_name ] = $column_info;				
			
			if ( 'woocommerce-advanced-shipment-tracking' === $column_name ) {			
				$new_columns['shipment_status'] = __( 'Shipment Status', 'woo-advanced-shipment-tracking' );
			}
		}
		return $new_columns;
	}
	
	/**
	 * Adds 'shipment_status' column content to 'Orders' page.
	 *
	 * @param string[] $column name of column being displayed
	 */
	public function wc_add_order_shipment_status_column_content( $column ) {
		
		global $post;
		if ( 'shipment_status' === $column ) {
			
			$ast = new WC_Advanced_Shipment_Tracking_Actions;
			$tracking_items = $ast->get_tracking_items( $post->ID );
			$shipment_status = get_post_meta( $post->ID, 'shipment_status', true );				
			$wp_date_format = get_option( 'date_format' );
			$date_format = ( $wp_date_format == 'd/m/Y' ) ? 'd/m' : 'm/d' ;	
			
			if ( count( $tracking_items ) > 0 ) { 
				?>
                <ul class="wcast-shipment-status-list">
                	<?php 
					foreach ( $tracking_items as $key => $tracking_item ) { 
						
						if ( !isset( $shipment_status[$key] ) ) {
							echo '<li class="tracking-item-' . $tracking_item['tracking_id'] . '"></li>';continue;
						}
						
						$status = isset( $shipment_status[$key]['pending_status'] ) ? $shipment_status[$key]['pending_status'] : $shipment_status[$key]['status'];	
						$status_date = $shipment_status[$key]['status_date'];
						$est_delivery_date = isset( $shipment_status[$key]['est_delivery_date'] ) ? $shipment_status[$key]['est_delivery_date'] : '';							
						$has_est_delivery = ( 'delivered' != $status && 'return_to_sender' != $status && !empty( $est_delivery_date ) ) ? true : false ;
                        ?>
                        <li id="shipment-item-<?php esc_html_e( $tracking_item['tracking_id'] ); ?>" class="tracking-item-<?php esc_html_e( $tracking_item['tracking_id'] ); ?> open_tracking_details" data-orderid="<?php esc_html_e( $post->ID ); ?>" data-tracking_id="<?php esc_html_e( $tracking_item['tracking_id'] ); ?>">                            	
                            <div class="ast-shipment-status shipment-<?php esc_html_e( $status ); ?> has_est_delivery_<?php echo ( $has_est_delivery ? 1 : 0 ) ?>">
								<?php echo apply_filters( 'trackship_status_icon_filter', "", $status ); ?>
								<span class="ast-shipment-tracking-status"><?php echo apply_filters( 'trackship_status_filter', $status ); ?></span>
								<?php if ( '' != $status_date ) { ?>
									<span class="showif_has_est_delivery_0 ft11">on <?php esc_html_e( date( $date_format, strtotime( $status_date ) ) ); ?></span>
								<?php } ?>
                                <?php if ( $has_est_delivery ) { ?>
                                	<span class="wcast-shipment-est-delivery ft11">Est. Delivery(<?php esc_html_e( date( $date_format, strtotime( $est_delivery_date ) ) ); ?>)</span>
								<?php } ?>
                            </div>
                        </li>
				<?php } ?>
                </ul>
				<?php
			} else {
				echo '–';
			}
		}
	}
	
	/*
	* add bulk action
	* Change order status to delivered
	*/
	public function add_bulk_actions_get_shipment_status( $bulk_actions ) {
		$bulk_actions['get_shipment_status'] = 'Get Shipment Status';
		return $bulk_actions;
	}
	
	/*
	* order bulk action for get shipment status
	*/
	public function get_shipment_status_handle_bulk_action_edit_shop_order( $redirect_to, $action, $post_ids ) {
		
		if ( 'get_shipment_status' !== $action ) {
			return $redirect_to;
		}	
	
		$processed_ids = array();
		
		$order_count = count($post_ids);
		
		foreach ( $post_ids as $post_id ) {
			wp_schedule_single_event( time() + 1, 'wcast_retry_trackship_apicall', array( $post_id ) );			
			$processed_ids[] = $post_id;			
		}
	
		return $redirect_to = add_query_arg( array(
			'get_shipment_status' => '1',
			'processed_count' => count( $processed_ids ),
			'processed_ids' => implode( ',', $processed_ids ),
		), $redirect_to );
	}
	
	/*
	* bulk shipment status action for completed order with tracking details and without shipment status
	*/
	public static function bulk_shipment_status_from_settings_fun() {
		
		$args = array(
			'status' => 'wc-completed',
			'limit'	 => 100,	
			'date_created' => '>' . ( time() - 2592000 ),
		);		
		
		$orders = wc_get_orders( $args );		
		
		foreach ( $orders as $order ) {
			
			$order_id = $order->get_id();
			
			$ast = new WC_Advanced_Shipment_Tracking_Actions;
			$tracking_items = $ast->get_tracking_items( $order_id, true );
			
			if ( $tracking_items ) {
				$shipment_status = get_post_meta( $order_id, 'shipment_status', true);				
				foreach ( $tracking_items as $key => $tracking_item ) { 
					
					if ( !isset( $shipment_status[ $key ] ) ) {						
						wp_schedule_single_event( time() + 1, 'wcast_retry_trackship_apicall', array( $order_id ) );					
					}
					
					if ( isset( $shipment_status[ $key ][ 'pending_status' ] ) && 'TrackShip balance is 0' == $shipment_status[ $key ][ 'pending_status' ] ) {						
						wp_schedule_single_event( time() + 1, 'wcast_retry_trackship_apicall', array( $order_id ) );		
					}
					
					if( isset( $shipment_status[ $key ][ 'pending_status' ] ) && 'TrackShip connection issue' == $shipment_status[$key]['pending_status'] ) {	
						wp_schedule_single_event( time() + 1, 'wcast_retry_trackship_apicall', array( $order_id ) );		
					}
				}									
			}			
		}
		$url = admin_url( '/edit.php?post_type=shop_order' );
		echo $url;
		die();	
	}		

	/*
	* The results notice from bulk action on orders
	*/
	public function shipment_status_bulk_action_admin_notice() {
		
		if ( empty( $_REQUEST['get_shipment_status'] ) ) {
			return; // Exit
		}	
	
		$count = intval( $_REQUEST['processed_count'] );
	
		printf( '<div id="message" class="updated fade"><p>' .
			_n( 'The shipment status updates will run in the background, please refresh the page in a few minutes.',
			'The shipment status updates will run in the background, please refresh the page in a few minutes.',
			$count,
			'get_shipment_status'
		) . '</p></div>', $count );
	}

	/**
	 * Add 'get_shipment_status' link to order actions select box on edit order page
	 *
	 * @since 1.0
	 * @param array $actions order actions array to display
	 * @return array
	 */
	public function add_order_meta_box_get_shipment_status_actions( $actions ) {
		// add download to CSV action
		$actions['get_shipment_status_edit_order'] = __( 'Get Shipment Status', 'woo-advanced-shipment-tracking' );
		return $actions;
	}

	/*
	* order details meta box action
	*/
	public function process_order_meta_box_actions_get_shipment_status( $order ) {
		$this->trigger_woocommerce_order_status_completed( $order->get_id() );
	}	
	
	/**
	 * Add bulk filter for Shipment status in orders list
	 *
	 * @since 2.4
	 */
	public function filter_orders_by_shipment_status() {
		global $typenow;

		if ( 'shop_order' === $typenow ) {			

			$terms = array(
				'pending_trackship' => (object) array( 'term' => __( 'Pending TrackShip', 'woo-advanced-shipment-tracking' ) ),
				'unknown' => (object) array( 'term' => __( 'Unknown', 'woo-advanced-shipment-tracking' ) ),
				'pre_transit' => (object) array( 'term' => __( 'Pre Transit', 'woo-advanced-shipment-tracking' ) ),
				'in_transit' => (object) array( 'term' => __( 'In Transit', 'woo-advanced-shipment-tracking' ) ),
				'available_for_pickup' => (object) array( 'term' => __( 'Available For Pickup', 'woo-advanced-shipment-tracking' ) ),
				'out_for_delivery' => (object) array( 'term' => __( 'Out For Delivery', 'woo-advanced-shipment-tracking' ) ),
				'delivered' => (object) array( 'term' => __( 'Delivered', 'woo-advanced-shipment-tracking' ) ),
				'failure' => (object) array( 'term' => __( 'Failed Attempt', 'woo-advanced-shipment-tracking' ) ),
				'cancelled' => (object) array( 'term' => __( 'Cancelled', 'woocommerce' ) ),
				'carrier_unsupported' => (object) array( 'term' => __( 'Carrier Unsupported', 'woo-advanced-shipment-tracking' ) ),
				'return_to_sender' => (object) array( 'term' => __( 'Return To Sender', 'woo-advanced-shipment-tracking' ) ),				
				'INVALID_TRACKING_NUM' => (object) array( 'term' => __( 'Invalid Tracking Number', 'woo-advanced-shipment-tracking' ) ),
			);

			?>
			<select name="_shop_order_shipment_status" id="dropdown_shop_order_shipment_status">
				<option value=""><?php _e( 'Filter by shipment status', 'woo-advanced-shipment-tracking' ); ?></option>
				<?php foreach ( $terms as $value => $term ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php echo esc_attr( isset( $_GET['_shop_order_shipment_status'] ) ? selected( $value, $_GET['_shop_order_shipment_status'], false ) : '' ); ?>>
					<?php printf( '%1$s', esc_html( $term->term ) ); ?>
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
	public function filter_orders_by_shipment_status_query( $vars ) {
		global $typenow;		
		if ( 'shop_order' === $typenow && isset( $_GET['_shop_order_shipment_status'] ) && $_GET['_shop_order_shipment_status'] != '' ) {
			$vars['meta_query'][] = array(
				'key'       => 'ts_shipment_status',
				'value'     => $_GET['_shop_order_shipment_status'],
				'compare'   => 'LIKE'
			);		
		}
		return $vars;
	}	
	
	/*
	* trigger when order status changed to shipped or completed or update tracking
	* param $order_id
	*/	
	public function trigger_woocommerce_order_status_completed( $order_id ) {
				
		$order = wc_get_order( $order_id );
		$order_shipped = apply_filters( 'is_order_shipped', true, $order );
				
		if( $order_shipped ) {
			$api = new WC_Advanced_Shipment_Tracking_Api_Call;
			$array = $api->get_trackship_apicall( $order_id );							
		}
	}
	
	/*
	* filter for shipment status
	*/
	public function trackship_status_filter_func( $status ) {
		switch ( $status ) {
			case "in_transit":
				$status = __( 'In Transit', 'woo-advanced-shipment-tracking' );
				break;
			case "on_hold":
				$status = __( 'On Hold', 'woo-advanced-shipment-tracking' );
				break;
			case "pre_transit":
				$status = __( 'Pre Transit', 'woo-advanced-shipment-tracking' );
				break;
			case "delivered":
				$status = __( 'Delivered', 'woo-advanced-shipment-tracking' );
				break;
			case "out_for_delivery":
				$status = __( 'Out For Delivery', 'woo-advanced-shipment-tracking' );
				break;
			case "available_for_pickup":
				$status = __( 'Available For Pickup', 'woo-advanced-shipment-tracking' );
				break;
			case "return_to_sender":
				$status = __( 'Return To Sender', 'woo-advanced-shipment-tracking' );
				break;
			case "failure":
				$status = __( 'Failed Attempt', 'woo-advanced-shipment-tracking' );
				break;
			case "exception":
				$status = __( 'Exception', 'woo-advanced-shipment-tracking' );
				break;	
			case "unknown":
				$status = __( 'Unknown', 'woo-advanced-shipment-tracking' );
				break;
			case "pending_trackship":
				$status = __( 'Pending TrackShip', 'woo-advanced-shipment-tracking' );
				break;
			case "INVALID_TRACKING_NUM":
				$status = __( 'Invalid Tracking Number', 'woo-advanced-shipment-tracking' );
				break;
			case "carrier_unsupported":
				$status = __( 'Carrier Unsupported', 'woo-advanced-shipment-tracking' );
				break;
			case "invalid_user_key":
				$status = __( 'Invalid User Key', 'woo-advanced-shipment-tracking' );
				break;
			case "wrong_shipping_provider":
				$status = __( 'Wrong Shipping Provider', 'woo-advanced-shipment-tracking' );
				break;	
			case "deleted":
				$status = __( 'Deleted', 'woocommerce' );
				break;	
			case "balance_zero":
				$status = __( 'TrackShip balance is 0', 'woocommerce' );
				break;		
		}
		return $status;
	}
	
	/*
	* filter for shipment status icon
	*/
	public function trackship_status_icon_filter_func( $html, $status ) {
		switch ( $status ) {
			case "in_transit":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;
			case "on_hold":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;	
			case "pre_transit":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;
			case "delivered":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;
			case "out_for_delivery":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;
			case "available_for_pickup":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;
			case "return_to_sender":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;
			case "failure":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;
			case "exception":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;	
			case "unknown":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;
			case "pending_trackship":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;
			case "INVALID_TRACKING_NUM":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;
			case "wrong_shipping_provider":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;	
			case "invalid_user_key":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;
			case "carrier_unsupported":
				$html = '<span class="shipment-icon icon-'.$status.'">';
				break;				
			default:
				$html = '<span class="shipment-icon icon-default">';
				break;
		}
		return $html;
	}

	/*
	* retry trackship api call
	*/
	public function wcast_retry_trackship_apicall_func( $order_id ) {
		$logger = wc_get_logger();
		$context = array( 'source' => 'retry_trackship_apicall' );
		$logger->info( "Retry trackship api call for Order id : " . $order_id, $context );
		$this->trigger_woocommerce_order_status_completed( $order_id );
	}

	/*
	* update all shipment status email status
	*/
	public function update_shipment_status_email_status_fun() {	
		$status_settings = get_option( $_POST['settings_data'] ); 
		$status_settings[ $_POST['id'] ] = wc_clean( $_POST[ 'wcast_enable_status_email' ] );
		update_option( $_POST['settings_data'], $status_settings );		
		exit;
	}
	
	/*
	* update late shipment email status
	*/
	public function update_enable_late_shipments_email_fun() {		
		$status_settings[ $_POST['id'] ] = wc_clean( $_POST[ 'wcast_enable_late_shipments_email' ] );
		update_option( $_POST['settings_data'], $status_settings );			
		exit;
	}

	/*
	* get trackship bulk actions tab array data
	* return array
	*/
	public function get_trackship_bulk_actions_data() {			
		
		$completed_order_with_tracking = $this->completed_order_with_tracking();
		$completed_order_with_zero_balance = $this->completed_order_with_zero_balance();							
		$completed_order_with_do_connection = $this->completed_order_with_do_connection();
		
		$disable_bulk_sync = ( $completed_order_with_tracking > 0 ) ? false : true;	
		$disable_bulk_sync_zero_balance = ( $completed_order_with_zero_balance > 0 ) ? false : true;
		$disable_bulk_sync_do_connection = ( $completed_order_with_do_connection > 0 ) ? false : true;

		$wc_ast_status_shipped = get_option('wc_ast_status_shipped');
		
		if ( 1 == $wc_ast_status_shipped ) {
			$completed_order_label = '<span class="shipped_label">shipped</span>';			
		} else{
			$completed_order_label = '<span class="shipped_label">completed</span>';			
		}
		
		$form_data = array(						
			'wc_ast_bulk_shipment_status' => array(
				'type'			=> 'button',
				'title'			=> sprintf( __( 'You got %s %s orders with tracking info that were not sent to track on TrackShip', 'woo-advanced-shipment-tracking' ), $completed_order_with_tracking , $completed_order_label ),
				'label' 		=> __( 'Get Shipment Status', 'woo-advanced-shipment-tracking' ),
				'show' 			=> true,
				'disable' 		=> $disable_bulk_sync,
				'button_class'	=> 'bulk_shipment_status_button',
				'class'			=> '',
			),
			'wc_ast_bulk_shipment_status_for_zero_tracker_balace' => array(
				'type'			=> 'button',
				'title'			=> sprintf( __( 'You got %s %s orders with shipment status “TrackShip balance is 0”', 'woo-advanced-shipment-tracking' ), $completed_order_with_zero_balance , $completed_order_label ),
				'label' 		=> __( 'Get Shipment Status', 'woo-advanced-shipment-tracking' ),
				'show' 			=> true,
				'disable'		=> $disable_bulk_sync_zero_balance,
				'button_class'	=> 'bulk_shipment_status_button_for_empty_balance',
				'class'			=> '',
			),
			'wc_ast_bulk_shipment_status_for_trackship_connection_issue' => array(
				'type'			=> 'button',
				'title'			=> sprintf( __( 'You got %s %s orders with shipment status  “TrackShip connection issue”', 'woo-advanced-shipment-tracking' ), $completed_order_with_do_connection , $completed_order_label ),
				'label'			=> __( 'Get Shipment Status', 'woo-advanced-shipment-tracking' ),
				'show' => true,
				'disable'		=> $disable_bulk_sync_do_connection,
				'button_class'	=> 'bulk_shipment_status_button_for_connection_issue',
				'class'			=> '',
			),
		);
		return $form_data;
	}
	
	/*
	* get completed order with tracking that not sent to TrackShip
	* return number
	*/
	public function completed_order_with_tracking() {
		
		// Get orders completed.
		$args = array(
			'status' => 'wc-completed',
			'limit'	 => 100,	
			'date_created' => '>' . ( time() - 2592000 ),
		);
		
		$orders = wc_get_orders( $args );
		
		$completed_order_with_tracking = 0;
		
		foreach ( $orders as $order ) {
			$order_id = $order->get_id();
			
			$ast = new WC_Advanced_Shipment_Tracking_Actions;
			$tracking_items = $ast->get_tracking_items( $order_id, true );
			
			if ( $tracking_items ) {
				$shipment_status = get_post_meta( $order_id, 'shipment_status', true);
				foreach ( $tracking_items as $key => $tracking_item ) { 				
					if( !isset( $shipment_status[ $key ] ) ) {						
						$completed_order_with_tracking++;		
					}
				}									
			}			
		}
		return $completed_order_with_tracking;
	}
	
	/*
	* get completed order with Trackship Balance 0 status
	* return number
	*/
	public function completed_order_with_zero_balance() {
		
		// Get orders completed.
		$args = array(
			'status' => 'wc-completed',
			'limit'	 => 100,	
			'date_created' => '>' . ( time() - 2592000 ),
		);		
		
		$orders = wc_get_orders( $args );
		
		$completed_order_with_zero_balance = 0;
		
		foreach ( $orders as $order ) {
			$order_id = $order->get_id();
			
			$ast = new WC_Advanced_Shipment_Tracking_Actions;
			$tracking_items = $ast->get_tracking_items( $order_id, true );
			
			if ( $tracking_items ) {				
				$shipment_status = get_post_meta( $order_id, 'shipment_status', true);		
				foreach ( $tracking_items as $key => $tracking_item ) {			
					if ( isset( $shipment_status[ $key ][ 'pending_status' ] ) && 'TrackShip balance is 0' == $shipment_status[ $key ][ 'pending_status' ] ) {
						$completed_order_with_zero_balance++;
					}
				}									
			}			
		}				
		return $completed_order_with_zero_balance;
	}
	
	/*
	* get completed order with Trackship connection issue status
	* return number
	*/
	public function completed_order_with_do_connection() {
		
		// Get orders completed.
		$args = array(
			'status' => 'wc-completed',
			'limit'	 => 100,	
			'date_created' => '>' . ( time() - 2592000 ),
		);		
		
		$orders = wc_get_orders( $args );
		
		$completed_order_with_do_connection = 0;
		
		foreach ( $orders as $order ) {
			$order_id = $order->get_id();
			
			$ast = new WC_Advanced_Shipment_Tracking_Actions;
			$tracking_items = $ast->get_tracking_items( $order_id, true );
			
			if ( $tracking_items ) {				
				$shipment_status = get_post_meta( $order_id, 'shipment_status', true);
				foreach ( $tracking_items as $key => $tracking_item ) { 					
					if ( isset( $shipment_status[ $key ][ 'pending_status' ] ) && 'TrackShip connection issue' == $shipment_status[ $key ][ 'pending_status' ] ) {
						$completed_order_with_do_connection++;		
					}
				}									
			}			
		}				
		return $completed_order_with_do_connection;
	}
	
	/**
	 * Shipment tracking info html in orders details page
	 */
	public function display_shipment_tracking_info( $order_id, $item ) {
		
		$shipment_status = get_post_meta( $order_id, 'shipment_status', true );
		$ts_shipment_status = get_post_meta( $order_id, 'ts_shipment_status', true );
		
		$tracking_id = $item['tracking_id'];
		
		$ast = new WC_Advanced_Shipment_Tracking_Actions;
		$tracking_items = $ast->get_tracking_items( $order_id );
		
		$wp_date_format = get_option( 'date_format' );
		$date_format = ( 'd/m/Y' == $wp_date_format ) ? 'd/m' : 'm/d' ;	
		
		if ( count( $tracking_items ) > 0 ) {
			foreach ( $tracking_items as $key => $tracking_item ) {
				if( $tracking_id == $tracking_item['tracking_id'] ) {
					if ( isset( $shipment_status[$key] ) ) {
						$has_est_delivery = false;																		
						
						$status = isset( $shipment_status[$key]['pending_status'] ) ? $shipment_status[$key]['pending_status'] : $shipment_status[$key]['status'];	
						$status_date = $shipment_status[$key]['status_date'];
						$est_delivery_date = isset( $shipment_status[$key]['est_delivery_date'] ) ? $shipment_status[$key]['est_delivery_date'] : '';							
						
						if ( $status != 'delivered' && $status != 'return_to_sender' && !empty( $est_delivery_date ) ) {
							$has_est_delivery = true;
						}	
						?>	
						<div class="ast-shipment-status-div">	
							<span class="open_tracking_details ast-shipment-status shipment-<?php echo sanitize_title( $status ); ?>" data-orderid="<?php echo $order_id; ?>" data-tracking_id="<?php echo $tracking_id; ?>">
								<?php echo apply_filters( 'trackship_status_icon_filter', "", $status ); ?> <strong><?php echo apply_filters( 'trackship_status_filter', $status ); ?></strong>
							</span>
							<?php if ( '' != $status_date ) { ?>
								<span class="">on <?php echo date( $date_format, strtotime( $status_date ) ); ?></span>
							<?php } ?>	
							<br>
							<?php if ( $has_est_delivery ) { ?>
								<span class="wcast-shipment-est-delivery ft11">Est. Delivery(<?php echo date( $date_format, strtotime( $est_delivery_date ) ); ?>)</span>
							<?php } ?>
						</div>	
                        <?php
					}
				}
			}
		}
	}

	/**
	 * Delete tracking information from TrackShip when tracking deleted from AST
	 */
	public function delete_tracking_number_from_trackship( $tracking_items, $tracking_id, $order_id ) {
		
		foreach ( $tracking_items as $tracking_item ) {				
			if ( $tracking_item['tracking_id'] == $_POST['tracking_id'] ) {					
				$tracking_number = $tracking_item['tracking_number'];
				$tracking_provider = $tracking_item['tracking_provider'];					
				$api = new WC_Advanced_Shipment_Tracking_Api_Call;
				$array = $api->delete_tracking_number_from_trackship( $order_id, $tracking_number, $tracking_provider );
			}				
		}									
	}
	
	/*
	* fix shipment tracking for deleted tracking
	*/
	public function func_fix_shipment_tracking_for_deleted_tracking( $order_id, $key, $item ){
		$shipment_status = get_post_meta( $order_id, 'shipment_status', true);
		if ( isset( $shipment_status[ $key ] ) ) {
			unset($shipment_status[$key]);
			update_post_meta( $order_id, 'shipment_status', $shipment_status );			
		}
		
		$ts_shipment_status = get_post_meta( $order_id, 'ts_shipment_status', true );
		if( isset( $ts_shipment_status[ $key ] ) ) {
			unset( $ts_shipment_status[$key] );	
			update_post_meta( $order_id, 'ts_shipment_status', $ts_shipment_status );
		}
	}

	/**
	 * code for check if tracking number in order is delivered or not
	*/
	public function check_tracking_delivered( $order_id ) {
		
		$delivered = true;
		$shipment_status = get_post_meta( $order_id, 'shipment_status', true );
		$wc_ast_status_delivered = get_option( 'wc_ast_status_delivered' );
		
		foreach( (array) $shipment_status as $shipment ) {
			$status = $shipment['status'];
			if( 'delivered' != $status ) {
				$delivered = false;
			}
		}
		
		if ( count( $shipment_status ) > 0 && $delivered == true && $wc_ast_status_delivered ) {
			
			$order = wc_get_order( $order_id );
			$order_status  = $order->get_status();
			
			$change_to_delivered = apply_filters( 'ts_change_order_to_delivered', false, $order_status );
			
			if ( $order_status == 'completed' || $order_status == 'updated-tracking' || $order_status == 'shipped' ) {
				$change_to_delivered = true;
			}
				
			if ( $change_to_delivered ) {
				$order->update_status('delivered');
			}			
		}
	}

	/**
	 * code for trigger shipment status email
	*/
	public function trigger_tracking_email( $order_id, $old_status, $new_status, $tracking_item, $shipment_status ) {
		$order = wc_get_order( $order_id );		
		require_once( 'email-manager.php' );		
		
		if ( $old_status != $new_status ) {			
			if ( $new_status == 'delivered' ) {
				wc_trackship_email_manager()->delivered_shippment_status_email_trigger( $order_id, $order, $old_status, $new_status, $tracking_item, $shipment_status );
			} elseif( $new_status == 'exception' || $new_status == 'failure' || $new_status == 'in_transit' || $new_status == 'on_hold' || $new_status == 'out_for_delivery' || $new_status == 'available_for_pickup' || $new_status == 'return_to_sender' ) {
				wc_trackship_email_manager()->shippment_status_email_trigger( $order_id, $order, $old_status, $new_status, $tracking_item, $shipment_status );
			}	
			do_action( 'ast_trigger_ts_status_change',$order_id, $old_status, $new_status, $tracking_item, $shipment_status );				
		}
	}	
	
	/**
	* Add a new dashboard widget.
	*/
	public function ast_add_dashboard_widgets() {
		wp_enqueue_style( 'trackship_styles',  wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/trackship.css', array(), wc_advanced_shipment_tracking()->version );		
		wp_add_dashboard_widget( 'trackship_dashboard_widget', 'TrackShip Analytics <small>(last 30 days)</small>', array( $this, 'dashboard_widget_function' ) );
	}
	
	/**
	* Output the contents of the dashboard widget
	*/
	public function dashboard_widget_function( $post, $callback_args ) {				
		$tracking_analytics = $this->get_tracking_analytics_overview(); 
		?>
		<div class="ts-widget-content ">
			<div class="ts-widget-row">
				<div class="ts-widget__section ts-widget-rborder ts-widget-bborder">
					<h3><?php esc_html_e( 'Total Shipments', 'woo-advanced-shipment-tracking' ); ?></h3>	
					<span class="ts-widget-analytics-number"><?php esc_html_e( $tracking_analytics['total_shipments'] ); ?></span>
					<span>(<?php esc_html_e( $tracking_analytics['total_orders'] ); ?> <?php esc_html_e( 'Orders', 'woocommerce' ); ?>)</span>
				</div>
				<div class="ts-widget__section ts-widget-bborder">
					<h3><?php esc_html_e( 'Avg Shipment Length', 'woo-advanced-shipment-tracking' ); ?></h3>
					<span class="ts-widget-analytics-number"><?php esc_html_e( round( (int)$tracking_analytics['avg_shipment_length'] ) ); ?></span>
					<span><?php esc_html_e( 'days', 'woo-advanced-shipment-tracking' ); ?></span>
				</div>
			</div>
			<div class="ts-widget-row">
				<div class="ts-widget__section ts-widget-rborder ts-widget-bborder">
					<h3><?php esc_html_e( 'Active Shipments', 'woo-advanced-shipment-tracking' ); ?></h3>	
					<span class="ts-widget-analytics-number"><?php esc_html_e( $tracking_analytics['active_shipments'] ); ?></span>
				</div>
				<div class="ts-widget__section ts-widget-bborder">
					<h3><?php esc_html_e( 'Delivered', 'woo-advanced-shipment-tracking' ); ?></h3>
					<span class="ts-widget-analytics-number"><?php esc_html_e( $tracking_analytics['delivered_shipments'] ); ?></span>
				</div>
			</div>
			<div class="ts-widget-footer">
				<a class="" href="https://trackship.info/my-account/analytics/" target="blank"><?php esc_html_e( 'View Tracking Analytics','woo-advanced-shipment-tracking' ); ?></a>
				<a class="ts_link" href="https://trackship.info" title="TrackShip" target="blank"><img src="<?php echo wc_advanced_shipment_tracking()->plugin_dir_url()?>assets/images/trackship-logo.png"></a>
			</div>
		</div>
		<?php
	}
	
	/*
	* TrackShip Analytics Overview
	*/
	public function get_tracking_analytics_overview() {
		global $wpdb;		
		$paid_order_statuses =  array( 'completed', 'delivered', 'shipped' );
		
		$end_date = date( 'Y-m-d', strtotime( 'today - 30 days' ) );
		$start_date = date('Y-m-d');				
		
		global $wpdb;		
		$paid_order_statuses = array('completed','delivered','shipped');				

		$order_query = "
			SELECT 				
				posts.post_status as ordr_status,  								
				shipment_tracking_items.meta_value as shipment_tracking_items,
				shipment_status.meta_value as shipment_status,				
				posts.ID AS ID
				
				FROM    {$wpdb->posts} AS posts								
				LEFT JOIN {$wpdb->postmeta} AS shipment_tracking_items ON(posts.ID = shipment_tracking_items.post_id)
				LEFT JOIN {$wpdb->postmeta} AS shipment_status ON(posts.ID = shipment_status.post_id)				
			WHERE 
				posts.post_status IN ( 'wc-" . implode( "','wc-", $paid_order_statuses ) . "' )
				AND posts.post_type IN ( 'shop_order' )											
				AND shipment_tracking_items.meta_key IN ( '_wc_shipment_tracking_items')
				AND shipment_tracking_items.meta_key IS NOT NULL	
				AND shipment_status.meta_key IN ( 'shipment_status')	
				AND post_date < '".$start_date."'
				AND post_date > '".$end_date."'	
				
			ORDER BY
				posts.ID DESC
		";
		
		$shipment_status_results = $wpdb->get_results( $order_query );
		$shipment_status = array();
		$shipment_status_merge = array();
		$tracking_item_merge = array();
		
		foreach ( $shipment_status_results as $order ) {
			$order_id = $order->ID;														
			$shipment_status = unserialize( $order->shipment_status );
						
			if ( is_array( $shipment_status ) ) {
				$shipment_status_merge = array_merge( $shipment_status_merge, $shipment_status );				
			}					
		}
		
				
		$tracking_issues = 0;
		$active_shipments = 0;
		$delivered_shipments = 0;
		$avg_shipment_days_array = array();
		$avg_shipment_length = '';
		
		foreach ( $shipment_status_merge as $key => $val ) {					
			
			$first = ( isset($val['tracking_events']) ) ? reset($val['tracking_events']) : '';
			$first_date = ( isset($first->datetime) ) ? $first->datetime : '';
			
			if ( isset( $val['tracking_destination_events'] ) && count( $val['tracking_destination_events'] ) > 0 ) {
				$last = end($val['tracking_destination_events']);
			} elseif ( isset( $val['tracking_events'] ) ) {
				$last = end($val['tracking_events']);
			} else {
				$last = '';
			}
			
			$last_date = ( isset($last->datetime) ) ? $last->datetime : '';
			
			$status = isset( $val['status'] ) ? $val['status'] : '';
			
			if ( 'delivered' != $status ) {
				$last_date = date("Y-m-d H:i:s");
			}
			
			$days = NULL;
			$days = $this->get_num_of_days( $first_date, $last_date );	
			
			$avg_shipment_days_array[] = $days;
			
			$avg_shipment_length = $this->get_average( $avg_shipment_days_array );						
			
			if ( 'carrier_unsupported' == $status || 'INVALID_TRACKING_NUM' == $status || 'unknown' == $status || 'wrong_shipping_provider' == $status ) {
				$tracking_issues ++;
			}
			
			if ( 'delivered' == $status ) {
				$delivered_shipments ++;
			}
			
			if ( 'delivered' != $status ) {
				$active_shipments ++;
			}	
		}		
			
		$result = array();
		
		$result['total_shipments'] = count($shipment_status_merge);
		$result['tracking_issues'] = $tracking_issues;
		$result['active_shipments'] = $active_shipments;
		$result['delivered_shipments'] = $delivered_shipments;
		$result['avg_shipment_length'] = $avg_shipment_length;
		$result['total_orders'] = count($shipment_status_results);
		return $result;
	}
	
	/*
	*
	*/
	public function get_num_of_days( $first_date, $last_date ) {
		$date1 = strtotime($first_date);
		$date2 = strtotime($last_date);
		$diff = abs($date2 - $date1);
		return date( "d", $diff );
	}
	
	public function get_average( $array ) {
		return round( array_sum($array) / count($array), 2 );
	}
	
	/**
	* Create tracking page after store is connected
	*/
	public function create_tracking_page() {
		
		if ( version_compare( get_option( 'wc_advanced_shipment_tracking_ts_page' ), '1.0', '<' ) ) {
			$new_page_title = 'Shipment Tracking';
			$new_page_slug = 'ts-shipment-tracking';		
			$new_page_content = '[wcast-track-order]';       
			//don't change the code below, unless you know what you're doing
			$page_check = get_page_by_title( $new_page_title );		
	
			if ( !isset( $page_check->ID ) ) {
				$new_page = array(
					'post_type' => 'page',
					'post_title' => $new_page_title,
					'post_name' => $new_page_slug,
					'post_content' => $new_page_content,
					'post_status' => 'publish',
					'post_author' => 1,
				);
				$new_page_id = wp_insert_post( $new_page );	
				update_option( 'wc_ast_trackship_page_id', $new_page_id );	
			}
			update_option( 'wc_advanced_shipment_tracking_ts_page', '1.0' );					
		}	
	}
	
	/*
	* tracking number filter
	* if number not found. return false
	* if number found. return true
	*/
	public function check_tracking_exist( $value, $order ) {
		
		if ( true == $value ) {
				
			$tracking_items = $order->get_meta( '_wc_shipment_tracking_items', true );
			
			if ( $tracking_items ) {
				return true;
			} else {
				return false;
			}
		}
		
		return $value;
	}		
	
	/*
	* If order status is "Updated Tracking" or "Completed" than retrn true else return false
	*/
	public function check_order_status( $value, $order ) {
		
		$order_status  = $order->get_status(); 
		$all_order_status = wc_get_order_statuses();
		
		$default_order_status = array(
			'wc-pending' => 'Pending payment',
			'wc-processing' => 'Processing',
			'wc-on-hold' => 'On hold',
			'wc-completed' => 'Completed',
			'wc-delivered' => 'Delivered',
			'wc-cancelled' => 'Cancelled',
			'wc-refunded' => 'Refunded',
			'wc-failed' => 'Failed'			
		);
		
		foreach ( $default_order_status as $key => $value ) {
			unset( $all_order_status[ $key ] );
		}
		
		$custom_order_status = $all_order_status;
		
		foreach ( $custom_order_status as $key => $value ) {
			unset( $custom_order_status[ $key ] );
			$key = str_replace( "wc-", "", $key );		
			$custom_order_status[] = $key;
		}				
		
		if ( 'updated-tracking' == $order_status || 'completed' == $order_status || 'shipped' == $order_status || in_array( $order_status, $custom_order_status )){
			return true;			
		} else {
			return false;
		}
		return $value;				
	}
}	