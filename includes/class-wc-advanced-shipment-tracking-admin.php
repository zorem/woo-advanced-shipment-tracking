<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Advanced_Shipment_Tracking_Admin {
		
	var $zorem_pluginlist;
	
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
			} else{
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
		
		//cron_schedules
		add_filter( 'cron_schedules', array( $this, 'add_cron_interval') );							
		
		// add bulk order tracking number filter for exported / non-exported orders			
		add_filter( 'woocommerce_shop_order_search_fields', array( $this, 'filter_orders_by_tracking_number_query' ) );			
		
		// add bulk order filter for exported / non-exported orders
		add_action( 'restrict_manage_posts', array( $this, 'filter_orders_by_shipping_provider'), 20 );	
		add_filter( 'request', array( $this, 'filter_orders_by_shipping_provider_query' ) );					
		
		add_filter( 'woocommerce_email_title', array( $this, 'change_completed_woocommerce_email_title'), 10, 2 );
		
		add_action( 'wp_ajax_wc_ast_upload_csv_form_update', array( $this, 'upload_tracking_csv_fun') );		

		add_action( 'admin_footer', array( $this, 'footer_function'), 1 );									
		
		add_action( 'wp_ajax_update_email_preview_order', array( $this, 'update_email_preview_order_fun') );
		
		add_filter( 'woocommerce_admin_order_actions', array( $this, 'add_delivered_order_status_actions_button'), 100, 2 );		
		add_filter( 'woocommerce_admin_order_preview_actions', array( $this, 'additional_admin_order_preview_buttons_actions'), 5, 2 );
		
		//Shipping Provider Action
		add_action( 'wp_ajax_filter_shipiing_provider_by_status', array( $this, 'filter_shipiing_provider_by_status_fun') );				

		add_action( 'wp_ajax_add_custom_shipment_provider', array( $this, 'add_custom_shipment_provider_fun') );
		
		add_action( 'wp_ajax_get_provider_details', array( $this, 'get_provider_details_fun') );
		
		add_action( 'wp_ajax_update_custom_shipment_provider', array( $this, 'update_custom_shipment_provider_fun') );
		
		add_action( 'wp_ajax_reset_default_provider', array( $this, 'reset_default_provider_fun') );
		
		add_action( 'wp_ajax_woocommerce_shipping_provider_delete', array( $this, 'woocommerce_shipping_provider_delete' ) );				
		
		add_action( 'wp_ajax_update_provider_status', array( $this, 'update_provider_status_fun') );				
		
		add_action( 'wp_ajax_reset_shipping_providers_database', array( $this, 'reset_shipping_providers_database_fun') );
		
		add_action( 'wp_ajax_update_default_provider', array( $this, 'update_default_provider_fun') );
		
		add_action( 'wp_ajax_update_shipment_status', array( $this, 'update_shipment_status_fun') );
		
		add_action( 'wp_ajax_update_custom_order_status_email_display', array( $this, 'update_custom_order_status_email_display_fun') );

		add_action( 'update_order_status_after_adding_tracking', array( $this, 'update_order_status_after_adding_tracking'), 10, 2 );	

		add_action( 'add_more_api_provider', array( $this, 'add_more_api_provider' ) );
	}					
	
	/*
	* add_cron_interval
	*/
	public function add_cron_interval( $schedules ) {
		
		$schedules['wc_ast_1hr'] = array(
			'interval' => 60*60,//1 hour
			'display'  => esc_html__( 'Every one hour' ),
		);
		
		$schedules['wc_ast_6hr'] = array(
			'interval' => 60*60*6,//6 hour
			'display'  => esc_html__( 'Every six hour' ),
		);
		
		$schedules['wc_ast_12hr'] = array(
			'interval' => 60*60*12,//6 hour
			'display'  => esc_html__( 'Every twelve hour' ),
		);
		
		$schedules['wc_ast_1day'] = array(
			'interval' => 60*60*24*1,//1 days
			'display'  => esc_html__( 'Every one day' ),
		);
		
		$schedules['wc_ast_2day'] = array(
			'interval' => 60*60*24*2,//2 days
			'display'  => esc_html__( 'Every two day' ),
		);
		
		$schedules['wc_ast_7day'] = array(
			'interval' => 60*60*24*7,//7 days
			'display'  => esc_html__( 'Every Seven day' ),
		);
		
		//every 5 sec for batch proccessing
		$schedules['wc_ast_2min'] = array(
			'interval' => 2*60,//1 hour
			'display'  => esc_html__( 'Every two min' ),
		);
		
		return $schedules;
	}
	
	/*
	* get shipped orders
	*/
	function get_shipped_orders() {
		$range = get_option( 'wc_ast_api_date_range', 30 );
		$args = array(
			'status'	=> 'wc-completed',
			'limit'		=> -1,
		);
		
		if ( 0 != $range ) {
			$start = strtotime( date( 'Y-m-d 00:00:00', strtotime( '-'.$range.' days' ) ) );
			$end = strtotime( date( 'Y-m-d 23:59:59', strtotime( '-1 days' ) ) );
			$args['date_completed'] = $start.'...'.$end;
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
		
		if ( 'woocommerce-advanced-shipment-tracking' != $_GET['page'] && 'trackship-for-woocommerce' != $_GET['page'] ) {
			return;		
		}	
		
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';				

		wp_register_script( 'select2', WC()->plugin_url() . '/assets/js/select2/select2.full' . $suffix . '.js', array( 'jquery' ), '4.0.3' );
		wp_enqueue_script( 'select2');
		
		wp_enqueue_style( 'ast_styles',  wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/admin.css', array(), wc_advanced_shipment_tracking()->version );
		wp_register_style( 'trackship_styles',  wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/trackship.css', array(), wc_advanced_shipment_tracking()->version );
		
		wp_enqueue_style( 'front_style',  wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/front.css', array(), wc_advanced_shipment_tracking()->version );	
		
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
		wp_enqueue_script( 'jquery-ui-sortable' );		
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );		
		wp_enqueue_style( 'thickbox' );	
		wp_enqueue_style( 'trackship_styles' );			
		
		wp_enqueue_script( 'ajax-queue', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/jquery.ajax.queue.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version );
				
		wp_enqueue_script( 'ast_settings', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/settings.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version );

		wp_enqueue_script( 'ast_datatable', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/datatable.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version );

		wp_enqueue_script( 'ast_datatable_jquery', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/datatable.jquery.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version );
		
		wp_enqueue_script( 'front-js', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/front.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version );
		
		wp_register_script( 'shipment_tracking_table_rows', wc_advanced_shipment_tracking()->plugin_dir_url().'assets/js/shipping_row.js' , array( 'jquery', 'wp-util' ), wc_advanced_shipment_tracking()->version );
		
		wp_localize_script( 'shipment_tracking_table_rows', 'shipment_tracking_table_rows', array(
			'i18n' => array(				
				'data_saved'	=> __( 'Data saved successfully.', 'woo-advanced-shipment-tracking' ),
				'delete_provider' => __( 'Really delete this entry? This will not be undo.', 'woo-advanced-shipment-tracking' ),
				'upload_only_csv_file' => __( 'You can upload only csv file.', 'woo-advanced-shipment-tracking' ),
				'browser_not_html' => __( 'This browser does not support HTML5.', 'woo-advanced-shipment-tracking' ),
				'upload_valid_csv_file' => __( 'Please upload a valid CSV file.', 'woo-advanced-shipment-tracking' ),
			),
			'delete_rates_nonce' => wp_create_nonce( "delete-rate" ),
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
	
	/*
	* callback for Shipment Tracking page
	*/
	public function woocommerce_advanced_shipment_tracking_page_callback() {		  
		
		global $order, $wpdb;
		$WC_Countries = new WC_Countries();
		$countries = $WC_Countries->get_countries();				
		
		$default_shippment_providers = $wpdb->get_results( "SELECT * FROM {$this->table} ORDER BY shipping_default ASC, display_in_order DESC, trackship_supported DESC, id ASC" );		
		
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
			<div class="zorem-layout__header">
				<h1 class="zorem-layout__header-breadcrumbs">Advanced Shipment Tracking</h1>
				
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
										<?php
										$support_link = class_exists( 'ast_pro' ) ? 'https://www.zorem.com/?support=1' : 'https://wordpress.org/support/plugin/woo-advanced-shipment-tracking/#new-topic-0' ;
										?>
										<a href="<?php echo esc_url( $support_link ); ?>" class="woocommerce-list__item-inner" target="_blank" >
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
										<a href="https://www.zorem.com/docs/woocommerce-advanced-shipment-tracking/" class="woocommerce-list__item-inner" target="_blank">
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
									<?php if ( !class_exists( 'ast_pro' ) ) { ?>
										<li class="woocommerce-list__item has-action">
											<a href="https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/" class="woocommerce-list__item-inner" target="_blank">
												<div class="woocommerce-list__item-before">
													<span class="dashicons dashicons-media-document"></span>
												</div>
												<div class="woocommerce-list__item-text">
													<span class="woocommerce-list__item-title">
														<div class="woocommerce-list-Text">Upgrade To Pro</div>
													</span>
												</div>
												<div class="woocommerce-list__item-after">
													<span class="dashicons dashicons-arrow-right-alt2"></span>
												</div>
											</a>
										</li>
									<?php } ?>
								</ul>
							</div>
						</div>
					</div>
				</div>	
			</div>
			<?php do_action( 'ast_settings_admin_notice' ); ?>		
			<div class="woocommerce zorem_admin_layout">
				<div class="ast_admin_content" >
					<div class="ast_nav_div">											
						<?php $this->get_html_menu_tab( $this->get_ast_tab_settings_data() ); 
						require_once( 'views/admin_options_shipping_provider.php' );
						require_once( 'views/admin_options_settings.php' );
						require_once( 'views/admin_options_bulk_upload.php' );								
						do_action( 'ast_paypal_settings_panel' );
						require_once( 'views/admin_options_addons.php' ); 
						?>															
					</div>                   					
				</div>				
			</div>            			
		
			<div id="ast_settings_snackbar" class="ast_snackbar"><?php _e( 'Data saved successfully.', 'woo-advanced-shipment-tracking' )?></div>						
		</div>		
	<?php }
	
	/*
	* callback for Shipment Tracking menu array
	*/
	public function get_ast_tab_settings_data() {	
		
		$ast_customizer_settings = new wcast_initialise_customizer_settings();	
		$go_pro_label = class_exists( 'ast_pro' ) ? __( 'License', 'woo-advanced-shipment-tracking' ) : __( 'Go Pro', 'woo-advanced-shipment-tracking' ) ;
		$setting_data = array(
			'tab2' => array(					
				'title'		=> __( 'Settings', 'woo-advanced-shipment-tracking' ),
				'show'      => true,
				'class'     => 'tab_label first_label',
				'data-tab'  => 'settings',
				'data-label' => __( 'Settings', 'woo-advanced-shipment-tracking' ),
				'name'  => 'tabs',
				'position'  => 1,	
			),			
			'tab1' => array(					
				'title'		=> __( 'Shipping Providers', 'woo-advanced-shipment-tracking' ),
				'show'      => true,
				'class'     => 'tab_label',
				'data-tab'  => 'shipping-providers',
				'data-label' => __( 'Shipping Providers', 'woo-advanced-shipment-tracking' ),
				'name'  => 'tabs',
				'position'  => 2,
			),
			'customize' => array(					
				'title'		=> __( 'Customize', 'woo-advanced-shipment-tracking' ),
				'type'		=> 'link',
				'link'		=> $ast_customizer_settings->get_customizer_url( 'ast_tracking_general_section', 'settings' ),
				'show'      => true,
				'class'     => 'tab_label',
				'data-tab'  => 'trackship',
				'data-label' => __( 'Customize', 'woo-advanced-shipment-tracking' ),
				'name'  => 'tabs',
				'position'  => 3,				
			),			
			'tab4' => array(					
				'title'		=> __( 'CSV Import', 'woo-advanced-shipment-tracking' ),
				'show'      => true,
				'class'     => 'tab_label',
				'data-tab'  => 'bulk-upload',
				'data-label' => __( 'CSV Import', 'woo-advanced-shipment-tracking' ),
				'name'  => 'tabs',
				'position'  => 4,
			),
			'tab6' => array(					
				'title'		=> $go_pro_label,
				'show'      => true,
				'class'     => 'tab_label',
				'data-tab'  => 'addons',
				'data-label' => $go_pro_label,
				'name'  => 'tabs',
				'position'  => 5,
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
	public function get_html_menu_tab( $arrays, $tab_class = "tab_input" ) { 
		
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'settings';
		$settings = isset( $_GET['settings'] ) ? sanitize_text_field( $_GET['settings'] ) : 'general-settings';		
		
		foreach ( (array) $arrays as $id => $array ) {
			$checked = ( $tab == $array['data-tab'] || $settings == $array['data-tab'] ) ? 'checked' : '';
			if( isset( $array['type'] ) && 'link' == $array['type'] ) {	
			?>				
				<a class="menu_trackship_link" href="<?php esc_html_e( esc_url( $array['link'] ) ); ?>"><?php esc_html_e( $array['title'] ); ?></a>
			<?php 
			} else { 
			?>
				<input class="<?php esc_html_e( $tab_class ); ?>" id="<?php esc_html_e( $id ); ?>" name="<?php esc_html_e( $array['name'] ); ?>" type="radio"  data-tab="<?php esc_html_e( $array['data-tab'] ); ?>" data-label="<?php esc_html_e( $array['data-label'] ); ?>"  <?php esc_html_e( $checked ); ?>/>
				<label class="<?php esc_html_e( $array['class'] ); ?>" for="<?php esc_html_e( $id ); ?>"><?php esc_html_e( $array['title'] ); ?></label>
			<?php 
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
		foreach( (array)$arrays as $id => $array ) {
				
			if( $array['show'] ) { 
				
				if( 'checkbox' == $array['type'] ) {
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
							<input class="ast-tgl ast-tgl-flat" id="<?php esc_html_e( $id ); ?>" name="<?php esc_html_e( $id ); ?>" type="checkbox" <?php esc_html_e( $checked ); ?> value="1" <?php esc_html_e( $disabled ); ?>/>
							<label class="ast-tgl-btn <?php esc_html_e( $tgl_class ); ?>" for="<?php esc_html_e( $id ); ?>"></label>
						</span>
											
						<div class="setting_ul_tgl_checkbox_label"><strong><?php esc_html_e( $array['title'] ); ?></strong>
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
						
						foreach( (array) $array['options'] as $key => $val ) {
							$selected = ( get_option( $id, $array['default'] ) == (string)$key ) ? 'checked' : '' ; 
							?>
							<span class="radio_section">
								<label class="" for="<?php esc_html_e( $id ); ?>_<?php esc_html_e( $key ); ?>">												
									<input type="radio" id="<?php esc_html_e( $id ); ?>_<?php esc_html_e( $key ); ?>" name="<?php esc_html_e( $id ); ?>" class="<?php esc_html_e( $id ); ?>"  value="<?php esc_html_e( $key ); ?>" <?php esc_html_e( $selected ); ?> />
									<span class=""><?php esc_html_e( $val ); ?></span></br>
								</label>																		
							</span>
                        <?php 
						} 
						?>
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
						<div class="multiple_select_container">	
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
							?>
							<option <?php if ( 'other' == get_option( $id ) ) { esc_html_e( 'selected' ); } ?> value="other"><?php esc_html_e( 'Other', 'woo-advanced-shipment-tracking' ); ?></option>	
						</select>
						
						<fieldset style="<?php if ( 'other' != get_option( $id ) ) { esc_html_e( 'display:none;' ); } ?>" class="trackship_other_page_fieldset">
							<input type="text" name="wc_ast_trackship_other_page" id="wc_ast_trackship_other_page" value="<?php esc_html_e( get_option('wc_ast_trackship_other_page') ); ?>">
						</fieldset>
						
						<p class="tracking_page_desc"><?php esc_html_e( 'add the [wcast-track-order] shortcode in the selected page.', 'woo-advanced-shipment-tracking' ); ?> 
							<a href="https://www.zorem.com/docs/woocommerce-advanced-shipment-tracking/integration/" target="blank"><?php esc_html_e( 'more info', 'woo-advanced-shipment-tracking' ); ?></a>
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
	
	/*
	* get settings tab array data
	* return array
	*/
	public function get_settings_data(){
		
		$wc_ast_status_shipped = get_option('wc_ast_status_shipped',0);
		
		if( 1 == $wc_ast_status_shipped ) {
			$completed_order_label = __( 'Shipped', 'woo-advanced-shipment-tracking' );	
			$mark_as_shipped_label = __( 'Default "mark as <span class="shipped_label">shipped</span>"', 'woo-advanced-shipment-tracking' );	
			$mark_as_shipped_tooltip = __( "This means that the 'mark as <span class='shipped_label'>shipped</span>' will be selected by default when adding tracking info to orders.", 'woo-advanced-shipment-tracking' );
		} else {
			$completed_order_label = __( 'Completed', 'woocommerce' );
			$mark_as_shipped_label = __( 'Default "mark as <span class="shipped_label">completed</span>"', 'woo-advanced-shipment-tracking' );
			$mark_as_shipped_tooltip = __( "This means that the 'mark as <span class='shipped_label'>completed</span>' will be selected by default when adding tracking info to orders.", 'woo-advanced-shipment-tracking' );	
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
			$key = str_replace("wc-", "", $key);		
			$custom_order_status[$key] = array(
				'status' => __( $value, '' ),
				'type' => 'custom',
			);
		}
		
		$order_status = array( 
			"processing" => array(
				'status' => __( 'Processing', 'woocommerce' ),
				'type' => 'default',
			),
			"completed" => array(
				'status' => $completed_order_label,
				'type' => 'default',
			),
			"partial-shipped" => array(
				'status' => __( 'Partially Shipped', '' ),
				'type' => 'default',
				'class' => 'partially_shipped_checkbox',
			),
			"updated-tracking" => array(
				'status' => __( 'Updated Tracking', '' ),
				'type' => 'default',
				'class' => 'updated_tracking_checkbox',
			),	
			"cancelled" => array(
				'status' => __( 'Cancelled', 'woocommerce' ),
				'type' => 'default',
			),
			"on-hold" => array(
				'status' => __( 'On Hold', 'woocommerce' ),
				'type' => 'default',
			),			
			"refunded" => array(
				'status' => __( 'Refunded', 'woocommerce' ),
				'type' => 'default',
			),
			
			"failed" => array(
				'status' => __( 'Failed', 'woocommerce' ),
				'type' => 'default',
			),
			"show_in_customer_invoice" => array(
				'status' => __( 'Customer Invoice', 'woocommerce' ),
				'type' => 'default',
			),
			"show_in_customer_note" => array(
				'status' => __( 'Customer note', 'woocommerce' ),
				'type' => 'default',
			),			
		);
		
		$actions_order_status = array( 
			"processing" => array(
				'status' => __( 'Processing', 'woocommerce' ),
				'type' => 'default',
			),
			"completed" => array(
				'status' => $completed_order_label,
				'type' => 'default',
			),
			"partial-shipped" => array(
				'status' => __( 'Partially Shipped', '' ),
				'type' => 'default',
				'class' => 'partially_shipped_checkbox',
			),
			"updated-tracking" => array(
				'status' => __( 'Updated Tracking', '' ),
				'type' => 'default',
				'class' => 'updated_tracking_checkbox',
			),	
			"on-hold" => array(
				'status' => __( 'On Hold', 'woocommerce' ),
				'type' => 'default',
			),
			"cancelled" => array(
				'status' => __( 'Cancelled', 'woocommerce' ),
				'type' => 'default',
			),		
			"refunded" => array(
				'status' => __( 'Refunded', 'woocommerce' ),
				'type' => 'default',
			),	
			"failed" => array(
				'status' => __( 'Failed', 'woocommerce' ),
				'type' => 'default',
			),					
		);
		
		$order_status_array = array_merge( $order_status, $custom_order_status );			
		$action_order_status_array = array_merge( $actions_order_status, $custom_order_status );											
									
		$form_data = array(					
			'wc_ast_default_mark_shipped' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Set the "mark as shipped" option checked  when adding tracking info to orders', 'woo-advanced-shipment-tracking' ),				
				'show'		=> true,
				'class'     => '',
			),				
			'wc_ast_unclude_tracking_info' => array(
				'type'		=> 'multiple_select',
				'title'		=> __( 'Order Emails Display', 'woo-advanced-shipment-tracking' ),
				'tooltip'	=> __( 'Choose on which order emails to include the shipment tracking info', 'woo-advanced-shipment-tracking' ),
				'options'   => $order_status_array,					
				'show'		=> true,
				'class'     => '',
			),
			'wc_ast_show_orders_actions' => array(
				'type'		=> 'multiple_select',
				'title'		=> __( 'Add Tracking Order action', 'woo-advanced-shipment-tracking' ),
				'tooltip'	=> __( 'Choose for which Order status to display Add Tracking action button', 'woo-advanced-shipment-tracking' ),
				'options'   => $action_order_status_array,					
				'show'		=> true,
				'class'     => '',
			),	
			'display_track_in_my_account' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Display Track button on the Orders history list in customer accounts', 'woo-advanced-shipment-tracking' ),				
				'show'		=> true,
				'class'     => '',
			),	
			'open_track_in_new_tab' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Open the track link in a new tab', 'woo-advanced-shipment-tracking' ),			
				'show'		=> true,
				'class'     => '',
			),							
			'wc_ast_api_date_format' => array(
				'type'		=> 'radio',
				'title'		=> __( 'API Date Format', 'woo-advanced-shipment-tracking' ),				
				'desc'		=> __( 'Choose for which Order status to display', 'woo-advanced-shipment-tracking' ),
				'tooltip'   => __( 'The date format which your external service update the API', 'woo-advanced-shipment-tracking' ),
				'options'   => array(
									"d-m-Y" => 'DD/MM/YYYY',
									"m-d-Y" => 'MM/DD/YYYY',
							   ),
				'default'   => 'd-m-Y',				
				'show'		=> true,
				'class'     => '',
			),	
		);
		
		return apply_filters( 'ast_general_settings_options', $form_data );				 
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
				'title_link'=> "<a class='settings_edit' href='" . ps_customizer()->get_customizer_url( 'custom_order_status_email' , 'updated_tracking' ) . "'>" . __( 'Edit', 'woocommerce' ) . "</a>",
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
	public function get_partial_shipped_data(){		
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
				'title_link'=> "<a class='settings_edit' href='" . ps_customizer()->get_customizer_url( 'custom_order_status_email', 'partially_shipped' ) . "'>" . __( 'Edit', 'woocommerce' ) . "</a>",
				'class'		=> 'partial_shipped_status_label_color_th',
				'show'		=> true,
			),			
		);
		return $form_data;

	}	
	
	/*
	* get Order Status data
	* return array
	*/
	public function get_osm_data(){
		$osm_data = array(			
			'partial_shipped' => array(
				'id'		=> 'wc_ast_status_partial_shipped',
				'slug'   	=> 'partial-shipped',
				'label'		=> __( 'Partially Shipped', 'woo-advanced-shipment-tracking' ),				
				'label_class' => 'wc-partially-shipped',
				'option_id'	=> 'woocommerce_customer_partial_shipped_order_settings',				
				'edit_email'=> ps_customizer()->get_customizer_url('custom_order_status_email','partially_shipped'),
				'label_color_field' => 'wc_ast_status_partial_shipped_label_color',	
				'font_color_field' => 'wc_ast_status_partial_shipped_label_font_color',	
				'email_field' => 'wcast_enable_partial_shipped_email',					
			),					
		);
		
		$updated_tracking_status = get_option( "wc_ast_status_updated_tracking", 0);
		
		if ( true == $updated_tracking_status ) {	
			$updated_tracking_data = array(			
				'updated_tracking' => array(
					'id'		=> 'wc_ast_status_updated_tracking',
					'slug'   	=> 'updated-tracking',
					'label'		=> __( 'Updated Tracking', 'woo-advanced-shipment-tracking' ),				
					'label_class' => 'wc-updated-tracking',
					'option_id'	=> 'woocommerce_customer_updated_tracking_order_settings',				
					'edit_email'=> ut_customizer()->get_customizer_url('custom_order_status_email','updated_tracking'),	
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

		if ( ! empty( $_POST ) && check_admin_referer( 'wc_ast_settings_form', 'wc_ast_settings_form_nonce' ) ) {
			
			$data = $this->get_settings_data();						
			
			foreach ( $data as $key => $val ) {				
				
				if ( isset( $val['type'] ) && 'multiple_select' == $val['type'] ) {					
					
					foreach ( $val['options'] as $op_status => $op_data ) {
						$_POST[ $key ][$op_status] = 0;
					}
					
					foreach ( $_POST[ $key ] as $key1 => $status) {
						$_POST[ $key ][$status] = 1;						
					}
					
					update_option( $key, wc_clean( $_POST[ $key ] ) );					
					
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
		}
	}

	/**
	* Save custom order status - eanble/disable,color,font,email
	*/
	public function wc_ast_custom_order_status_form_update() {		
		
		if ( ! empty( $_POST ) && check_admin_referer( 'wc_ast_order_status_form', 'wc_ast_order_status_form_nonce' ) ) {
						
			update_option( 'wc_ast_status_shipped', wc_clean( $_POST[ 'wc_ast_status_shipped' ] ) );
			
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
			
			do_action( 'ast_custom_order_status_save', $_POST );	
			echo json_encode( array('success' => 'true') );
			die();
		}
	}	
		
	/*
	* change style of delivered order label
	*/	
	public function footer_function(){
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
				background: <?php echo $bg_color; ?>;
				color: <?php echo $color; ?>;
			}					
			.order-status.status-partial-shipped,.order-status-table .order-label.wc-partially-shipped{
				background: <?php echo $ps_bg_color; ?>;
				color: <?php echo $ps_color; ?>;
			}
			.order-status.status-updated-tracking,.order-status-table .order-label.wc-updated-tracking{
				background: <?php echo $ut_bg_color; ?>;
				color: <?php echo $ut_color; ?>;
			}		
			</style>
			<?php
		}
	}		
	
	/*
	* Ajax call for upload tracking details into order from bulk upload
	*/
	public function upload_tracking_csv_fun(){				
		
		$replace_tracking_info = wc_clean( $_POST['replace_tracking_info'] );
		$date_format_for_csv_import = wc_clean( $_POST['date_format_for_csv_import'] );
		update_option( 'date_format_for_csv_import', $date_format_for_csv_import );
		$order_id = wc_clean( $_POST['order_id'] );			
		
		$wast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
		$order_id = $wast->get_formated_order_id( $order_id );
		
		$tracking_provider = wc_clean( $_POST['tracking_provider'] );
		$tracking_number = wc_clean( $_POST['tracking_number'] );
		$date_shipped = str_replace( "/", "-", wc_clean( $_POST['date_shipped']) );
		
		$sku = isset( $_POST['sku'] ) ? wc_clean( $_POST['sku'] ) : '';
		$qty = isset( $_POST['qty'] ) ? wc_clean( $_POST['qty'] ) : '';	
		$date_shipped = empty( $date_shipped ) ? date("d-m-Y") : $date_shipped ;									

		global $wpdb;					
		
		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$this->table} WHERE api_provider_name = %s", $tracking_provider );
		$shippment_provider = $wpdb->get_var( $sql );
		
		if( $shippment_provider == 0 ){			
			$sql = "SELECT COUNT(*) FROM {$this->table} WHERE JSON_CONTAINS(api_provider_name, '[".'"'.$tracking_provider.'"'."]')";
			$shippment_provider = $wpdb->get_var( $sql );			
		}	
		
		if( $shippment_provider == 0 ){
			$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$this->table} WHERE provider_name = %s", $tracking_provider );
			$shippment_provider = $wpdb->get_var( $sql );
		}	 		
		
		$order = wc_get_order($order_id);		
		
		if ( $order === false ) {
			echo '<li class="invalid_order_id_error">Failed - Invalid Order Id - Order '.$_POST['order_id'].'</li>';exit;
		}
		
		if ( $shippment_provider == 0 ) {
			echo '<li class="shipping_provider_error">Failed - Invalid Shipping Provider - Order '.$_POST['order_id'].'</li>';exit;
		}
		
		if ( empty( $tracking_number ) ){
			echo '<li class="tracking_number_error">Failed - Empty Tracking Number - Order '.$_POST['order_id'].'</li>';exit;
		}
		
		if ( empty( $date_shipped ) ) {
			echo '<li class="empty_date_shipped_error">Failed - Empty Date Shipped - Order '.$_POST['order_id'].'</li>';exit;
		}			
		
		if ( !$this->isDate( $date_shipped, $date_format_for_csv_import ) ) {
			echo '<li class="invalid_date_shipped_error">Failed - Invalid Date Shipped - Order '.$_POST['order_id'].'</li>';exit;
		}	
		
		if ( 'm-d-Y' == $date_format_for_csv_import ) {
			$date_array = explode("-",$date_shipped);
			$date_shipped = $date_array[1].'-'.$date_array[0].'-'.$date_array[2];			
		}
		
		$tracking_items = $wast->get_tracking_items( $order_id );	
		
		if ( 1 == $replace_tracking_info ) {
			
			$order = wc_get_order($order_id);
			
			if ( $order ) {	
						
				if ( count( $tracking_items ) > 0 ) {
					foreach ( $tracking_items as $key => $item ) {								
						
						$tracking_exist = false;
						
						if ( class_exists( 'ast_woo_advanced_shipment_tracking_by_products' ) ) {
							$item_tracking_number = $item['tracking_number'];
							$tracking_exist = in_array( $item_tracking_number, array_column( $_POST['trackings'], 'tracking_number' ) );
						}
						
						if( false == $tracking_exist ) {
							unset( $tracking_items[ $key ] );		
						}
					}
					$wast->save_tracking_items( $order_id, $tracking_items );
				}
			}
		}
		
		if ( $shippment_provider && $tracking_number && $date_shipped ) {
					
			$tracking_provider = $this->get_provider_slug_from_name( $tracking_provider );
				
			$args = array(
				'tracking_provider' => wc_clean( $tracking_provider ),					
				'tracking_number'   => wc_clean( $_POST['tracking_number'] ),
				'date_shipped'      => wc_clean( $date_shipped ),
				'status_shipped'	=> wc_clean( $_POST['status_shipped'] ),
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
						
						$status_shipped = ( isset( $_POST["status_shipped"] ) ? $_POST["status_shipped"] : "" );
						
						$autocomplete_order_tpi = get_option('autocomplete_order_tpi',0);
						if ( 1 == $autocomplete_order_tpi ) {
							$status_shipped = $this->autocomplete_order_after_adding_all_products( $order_id, $status_shipped, $products_list );
							$args['status_shipped'] = $status_shipped;
						}						
						
						if ( count( $tracking_items ) > 0 ) {								
							foreach ( $tracking_items as $key => $item ) {						
								if ( $item['tracking_number'] == $_POST['tracking_number'] ) {
									
									if ( isset( $item['products_list'] ) && !empty( $item['products_list'] ) ) {
										
										$product_list_array = array();
										foreach ( $item['products_list'] as $item_product_list ) {														
											$product_list_array[ $item_product_list->product ] = $item_product_list->qty;
										}																							
										
										$mearge_array = array();										
										foreach ( array_keys( $product_data_array + $product_list_array ) as $product) {										
											$mearge_array[ $product ] = (int)( isset( $product_data_array[ $product ] ) ? $product_data_array[ $product ] : 0 ) + (int)( isset( $product_list_array[$product] ) ? $product_list_array[ $product ] : 0);
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
		
											echo '<li class="success">Success - added tracking info to Order '.$_POST['order_id'].'</li>';
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
			
			echo '<li class="success">Success - added tracking info to Order '.$_POST['order_id'].'</li>';exit;				
		} else{
			echo '<li class="invalid_tracking_data_error">Failed - Invalid Tracking Data</li>';exit;
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
				$new_products[ $in_list->product ] = (int)$new_products[ $in_list->product ] + (int)$in_list->qty;							
			} else{
				$new_products[ $in_list->product ] = $in_list->qty;	
			}			
		}
		
		$total_products_data = array();
	
		foreach ( array_keys( $new_products + $added_products ) as $products ) {
			$total_products_data[ $products ] = ( isset( $new_products[ $products ] ) ? $new_products[ $products ] : 0 ) + ( isset( $added_products[ $products ] ) ? $added_products[ $products ] : 0);
		}			
		
		$orders_products_data = array();
		foreach ( $items as $item ) {																
			$checked = 0;
			$qty = $item->get_quantity();
			
			if ( 1 == $items_count && 1 == $qty ) {
				return $status_shipped;
			}	
			
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
		$tracking_items = $ast->get_tracking_items( $order_id, true );
		
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
					$all_list[ $in_list->product ] = (int)$all_list[ $in_list->product ] + (int)$in_list->qty;							
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
				} else{
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
				do_action("send_order_to_trackship", $order_id);
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
	* update preview order id in customizer
	*/
	public function update_email_preview_order_fun() {
		set_theme_mod( 'wcast_availableforpickup_email_preview_order_id', wc_clean( $_POST['wcast_preview_order_id'] ) );
		set_theme_mod( 'wcast_returntosender_email_preview_order_id', wc_clean( $_POST['wcast_preview_order_id'] ) );
		set_theme_mod( 'wcast_delivered_status_email_preview_order_id', wc_clean( $_POST['wcast_preview_order_id'] ) );
		set_theme_mod( 'wcast_outfordelivery_email_preview_order_id', wc_clean( $_POST['wcast_preview_order_id'] ) );
		set_theme_mod( 'wcast_intransit_email_preview_order_id', wc_clean( $_POST['wcast_preview_order_id'] ) );
		set_theme_mod( 'wcast_onhold_email_preview_order_id', wc_clean( $_POST['wcast_preview_order_id'] ) );
		set_theme_mod( 'wcast_pretransit_email_preview_order_id', wc_clean( $_POST['wcast_preview_order_id'] ) );
		set_theme_mod( 'wcast_email_preview_order_id', wc_clean( $_POST['wcast_preview_order_id'] ) );
		set_theme_mod( 'wcast_preview_order_id', wc_clean( $_POST['wcast_preview_order_id'] ) );		
		exit;
	}
	
	/*
	* Change completed order email title to Shipped Order
	*/
	public function change_completed_woocommerce_email_title( $email_title, $email ) {
		$wc_ast_status_shipped = get_option( 'wc_ast_status_shipped', 0 );		
		// Only on backend Woocommerce Settings "Emails" tab
		if ( 1 == $wc_ast_status_shipped ) {
			if ( isset( $_GET['page'] ) && $_GET['page'] == 'wc-settings' && isset( $_GET['tab'] )  && $_GET['tab'] == 'email' ) {
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
		
		wp_enqueue_style( 'ast_styles',  wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/admin.css', array(), wc_advanced_shipment_tracking()->version );	
		wp_enqueue_script( 'woocommerce-advanced-shipment-tracking-js', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/admin.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version);
		
		$wc_ast_status_delivered = get_option( 'wc_ast_status_delivered' );
		
		if ( $wc_ast_status_delivered ) {
			if ( $order->has_status( array( 'completed' ) ) || $order->has_status( array( 'shipped' ) ) ) {
				
				// Get Order ID (compatibility all WC versions)
				$order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
				
				// Set the action button
				$actions['delivered'] = array(
					'url'       => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=delivered&order_id=' . $order_id ), 'woocommerce-mark-order-status' ),
					'name'      => __( 'Mark order as delivered', 'woo-advanced-shipment-tracking' ),
					'icon' => '<i class="fa fa-truck">&nbsp;</i>',
					'action'    => "delivered_icon", // keep "view" class for a clean button CSS
				);
			}	
		}			
		
		$wc_ast_show_orders_actions = get_option( 'wc_ast_show_orders_actions' );
		$order_array = array();
		
		foreach ( $wc_ast_show_orders_actions as $order_status => $value ) {
			if ( 1 == $value ) {
				array_push($order_array, $order_status);			
			}	
		}
		
		if ( $order->get_shipping_method() != 'Local pickup' && $order->get_shipping_method() != 'Local Pickup' ) {		
			if ( $order->has_status( $order_array ) ) {			
				$actions['add_tracking'] = array(
					'url'       => "#" . $order->get_id(),
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
	* Add delivered action button in preview order list to change order status from completed to delivered
	*/
	public function additional_admin_order_preview_buttons_actions( $actions, $order ) {
		
		$wc_ast_status_delivered = get_option( 'wc_ast_status_delivered' );
		if ( $wc_ast_status_delivered ) {
			// Below set your custom order statuses (key / label / allowed statuses) that needs a button
			$custom_statuses = array(
				'delivered' => array( // The key (slug without "wc-")
					'label'     => __( 'Delivered', 'woo-advanced-shipment-tracking' ), // Label name
					'allowed'   => array( 'completed'), // Button displayed for this statuses (slugs without "wc-")
				),
			);
		
			// Loop through your custom orders Statuses
			foreach ( $custom_statuses as $status_slug => $values ){
				if ( $order->has_status( $values['allowed'] ) ) {
					$actions[ 'status' ][ 'group' ] = __( 'Change status: ', 'woocommerce' );
					$actions[ 'status' ][ 'actions' ][ $status_slug ] = array(
						'url'    => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=' . $status_slug.'&order_id=' . $order->get_id() ), 'woocommerce-mark-order-status' ),
						'name'   => $values['label'],
						'title'  => __( 'Change order status to', 'woo-advanced-shipment-tracking' ) . ' ' . strtolower( $values['label'] ),
						'action' => $status_slug,
					);
				}
			}
		}		
		return $actions;
	}
	
	/*
	* filter shipping providers by stats
	*/
	public function filter_shipiing_provider_by_status_fun() {		
		
		$status = wc_clean( $_POST['status'] );
		
		global $wpdb;		
		
		if( 'active' == $status ){				
			$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table WHERE display_in_order = 1" );	
		}
		
		if ( 'inactive' == $status ) {			
			$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table WHERE display_in_order = 0" );	
		}
		
		if ( 'custom' == $status ) {			
			$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table WHERE shipping_default = 0" );	
		}
		
		if( 'all' == $status ) {
			$status = '';
			$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table ORDER BY shipping_default ASC, display_in_order DESC, trackship_supported DESC, id ASC" );	
		}
		
		$html = $this->get_provider_html( $default_shippment_providers, $status );
		echo $html;
		exit;		
	}	
	
	/*
	* Get providers list html
	*/
	public function get_provider_html( $default_shippment_providers, $status ) {
		$WC_Countries = new WC_Countries();
		$upload_dir   = wp_upload_dir();	
		$ast_directory = $upload_dir['baseurl'] . '/ast-shipping-providers/'; ?>
		<div class="provider_list">
			<?php if($default_shippment_providers){ 
			if($status == 'custom'){
				?>					
				</br><a href="javaScript:void(0);" class="button-primary btn_ast2 btn_large add_custom_provider" id="add-custom"><span class="dashicons dashicons-plus-alt"></span><?php _e( 'Add Custom Provider', 'woo-advanced-shipment-tracking' ); ?></a>	
			<?php } ?>
			<div class="provider_table_hc">
				<div class="shipping_provider_counter counter"></div>				
			</div>			
			<table class="wp-list-table widefat posts provder_table" id="shipping-provider-table">
				<thead>
					<tr>						
						<th><?php esc_html_e( 'Shipping Providers', 'woo-advanced-shipment-tracking'); ?></th>
						<th><?php esc_html_e( 'Display Name', 'woo-advanced-shipment-tracking'); ?></th>						
						<?php do_action('ast_shipping_provider_column_after_api_name'); ?>						
						<th><?php esc_html_e( 'TrackShip', 'woo-advanced-shipment-tracking'); ?></th>
						<th><?php esc_html_e( 'Actions', 'woo-advanced-shipment-tracking'); ?></th>												
					</tr>
				</thead>
				<tbody>		
					<?php 					
					foreach ( $default_shippment_providers as $d_s_p ) { 
					$class = ( 1 == $d_s_p->display_in_order ) ? 'enable' : 'disable' ;
					?>
						<tr class="<?php esc_html_e( $class ); ?>">							
							<td>
								<?php  
								$custom_thumb_id = $d_s_p->custom_thumb_id;
								if ( 1 == $d_s_p->shipping_default ) {
									if ( 0 != $custom_thumb_id ) {
										$image_attributes = wp_get_attachment_image_src( $custom_thumb_id , array( '60', '60' ) );
										$provider_image = $image_attributes[0];
									} else {
										$provider_image = $ast_directory . '' . sanitize_title( $d_s_p->provider_name ) . '.png?v=' . wc_advanced_shipment_tracking()->version;
									}
									echo '<img class="provider-thumb" src="' . $provider_image . '">';
								} else { 
									$image_attributes = wp_get_attachment_image_src( $custom_thumb_id , array( '60', '60' ) );
									
									if ( 0 != $custom_thumb_id ) { 
										echo '<img class="provider-thumb" src="' . $image_attributes[0] . '">';
									} else { 
										echo '<img class="provider-thumb" src="' . wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/images/icon-default.png">';
									}  
								} ?>																					
																
							</td>
							<td>
								<span class="provider_name">
									<?php 
									esc_html_e( $d_s_p->provider_name );
									
									if ( isset( $d_s_p->custom_provider_name ) && '' != $d_s_p->custom_provider_name ) { 
										esc_html_e( ' (' . $d_s_p->custom_provider_name . ')' ); 
									} 
									
									if ( isset( $d_s_p->api_provider_name ) && '' != $d_s_p->api_provider_name ) {
										
										if ( $this->isJSON( $d_s_p->api_provider_name ) && class_exists( 'ast_pro' ) ) {
											$api_count = count( json_decode( $d_s_p->api_provider_name ) );
										} else {
											$api_count = 1;
										}
										$api_text = __('API aliases','woo-advanced-shipment-tracking');
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
								
								<?php 
								
								if ( 0 == $d_s_p->shipping_default ) { 
									echo '<span class="dashicons dashicons-trash remove provider_actions_btn" data-pid="' . $d_s_p->id . '"></span>';
								} 
								
								$edit_provider_class = apply_filters( 'edit_provider_class', 'edit_provider' );
								
								$provider_type = ( 1 == $d_s_p->shipping_default ) ? 'default_provider' : 'custom_provider';
								
								echo '<span class="' . $edit_provider_class . ' provider_actions_btn" data-provider="' . $provider_type . '" data-pid="' . $d_s_p->id . '">' . esc_html( 'edit', 'woo-advanced-shipment-tracking' ) . '</span>';
									
								$default_provider = get_option( 'wc_ast_default_provider' );
								
								$label_class = ( 1 != $d_s_p->display_in_order ) ? 'disable_label' : '';
								$make_default_checked = ( $default_provider == $d_s_p->provider_name ) ? 'checked' : '';
								$make_default_disabled = ( 1 != $d_s_p->display_in_order ) ? 'disabled' : '';
								?>
								
								<label for="make_default_<?php esc_html_e( $d_s_p->id ); ?>" id="default_label_<?php esc_html_e( $d_s_p->id ); ?>" class="<?php esc_html_e( $label_class ); ?>">
									<input type="checkbox" id="make_default_<?php esc_html_e( $d_s_p->id ); ?>" name="make_provider_default" data-id="<?php esc_html_e( $d_s_p->id ); ?>" class="make_provider_default" value="<?php esc_html_e( $d_s_p->provider_name ); ?>" <?php esc_html_e( $make_default_checked ); ?> <?php esc_html_e( $make_default_disabled ); ?>>
									<span class="default_label"><?php esc_html_e( 'default', 'woo-advanced-shipment-tracking' ); ?></span>
								</label>
							</td>								
							
							<?php do_action('ast_shipping_provider_column_content_after_api_name', $d_s_p->provider_name); ?>																			
							
							<td class="provider_trackship_td">
								<?php 
								if ( 1 == $d_s_p->trackship_supported ) { 
									echo '<span class="dashicons dashicons-yes-alt"></span>'; 
								} else { 
									echo '<span class="dashicons dashicons-dismiss"></span>'; 
								} ?>
								<span>TrackShip</span>
							</td>																
							
							<td>
								<input class="ast-tgl ast-tgl-flat status_slide" id="list-switch-<?php esc_html_e( $d_s_p->id ); ?>" name="select_custom_provider[]" type="checkbox" <?php if( 1 == $d_s_p->display_in_order ) { esc_html_e( 'checked' ); } ?> value="<?php esc_html_e( $d_s_p->id ); ?>"/>
								<label class="ast-tgl-btn" for="list-switch-<?php esc_html_e( $d_s_p->id ); ?>"></label>
							</td>								
						</tr>
					<?php } ?>
				</tbody>				
			</table>			
			<div class="provider_table_hc_footer">
				<div class="shipping_provider_counter counter"></div>	
				<div class="paging shipping_provider_paging"></div>	
			</div>
			<?php } else { 
				if ( 'custom' == $status ) { ?>					
				<p class="provider_message"><?php printf( esc_html_e( 'You did not create any %s shipping providers yet.', 'woo-advanced-shipment-tracking' ), $status ); ?></p>
				<a href="javaScript:void(0);" class="button-primary btn_ast2 btn_large add_custom_provider" id="add-custom">
					<span class="dashicons dashicons-plus-alt"></span>
					<?php esc_html_e( 'Add Custom Provider', 'woo-advanced-shipment-tracking' ); ?>
				</a>	
			<?php } else { ?>
				<p class="provider_message"><?php printf( esc_html_e( "You don't have any %s shipping providers.", 'woo-advanced-shipment-tracking' ), $status ); ?></p>
			<?php 
				} 
			}	
			?>		
		</div>	
		<?php 
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
		global $wpdb;		
		$success = $wpdb->update( $this->table, 
			array(
				"display_in_order" => wc_clean( $_POST['checked'] ),
			),	
			array( 'id' => wc_clean( $_POST['id'] ) )
		);
		exit;	
	}
	
	/**
	* update default provider function 
	*/
	public function update_default_provider_fun() {
		if ( 1 == $_POST['checked'] ) {
			update_option( 'wc_ast_default_provider', wc_clean( $_POST['default_provider'] ) );
		} else {
			update_option( 'wc_ast_default_provider', '' );
		}
		exit;
	}
	
	/**
	* Create slug from title
	*/
	public static function create_slug( $text ) {
		// replace non letter or digits by -
		$text = preg_replace('~[^\pL\d]+~u', '-', $text);
		
		// transliterate
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
		
		// remove unwanted characters
		$text = preg_replace('~[^-\w]+~', '', $text);
		
		// trim
		$text = trim($text, '-');
		
		// remove duplicate -
		$text = preg_replace('~-+~', '-', $text);
		
		// lowercase
		$text = strtolower($text);
		
		$text = 'cp-'.$text;
		
		if ( empty( $text ) ) {
			return '';
		}
		
		return $text;
	}

	/**
	* Add custom shipping provider function 
	*/
	public function add_custom_shipment_provider_fun() {
		
		global $wpdb;
		$provider_slug = $this->create_slug( wc_clean($_POST['shipping_provider'] ) );		
		
		if ( '' == $provider_slug ) {
			$provider_slug = sanitize_text_field( $_POST['shipping_provider'] );
		}
		
		$data_array = array(
			'shipping_country' => sanitize_text_field( $_POST['shipping_country'] ),
			'provider_name' => sanitize_text_field( $_POST['shipping_provider'] ),
			'custom_provider_name' => sanitize_text_field( $_POST['shipping_display_name'] ),
			'ts_slug' => $provider_slug,
			'provider_url' => sanitize_text_field( $_POST['tracking_url'] ),
			'custom_thumb_id' => sanitize_text_field( $_POST['thumb_id'] ),			
			'display_in_order' => 1,
			'shipping_default' => 0,
		);
		
		$result = $wpdb->insert( $this->table, $data_array );
		
		$status = 'all';
		$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table ORDER BY shipping_default ASC, display_in_order DESC, trackship_supported DESC, id ASC" );		
		$html = $this->get_provider_html( $default_shippment_providers, $status );
		echo $html;
		exit;		
	}
	
	/*
	* delet provide by ajax
	*/
	public function woocommerce_shipping_provider_delete(){				

		$provider_id = wc_clean( $_POST['provider_id'] );
		
		if ( ! empty( $provider_id ) ) {
			global $wpdb;
			$where = array(
				'id' => $provider_id,
				'shipping_default' => 0
			);
			$wpdb->delete( $this->table, $where );
		}
		$status = 'all';
		
		$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table ORDER BY shipping_default ASC, display_in_order DESC, trackship_supported DESC, id ASC" );
		$html = $this->get_provider_html( $default_shippment_providers, $status );
		echo $html;
		exit;
	}
	
	/**
	* Get shipping provider details fun 
	*/
	public function get_provider_details_fun(){
		$id = wc_clean( $_POST['provider_id'] );
		global $wpdb;
		
		$shippment_provider = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->table WHERE id=%d", $id ) );
		
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
	public function update_custom_shipment_provider_fun(){
		
		global $wpdb;		
		
		if ( [] == array_filter( $_POST['api_provider_name'] ) ) {
			$api_provider_name = null;			
		} else {
			$api_provider_name = wc_clean( json_encode( $_POST['api_provider_name'] ) );
		}	
		
		$provider_type = $_POST['provider_type'];
		if ( 'default_provider' == $provider_type ) {
			$data_array = array(				
				'custom_provider_name' => sanitize_text_field( $_POST['shipping_display_name'] ),
				'api_provider_name' => $api_provider_name,				
				'custom_thumb_id' => sanitize_text_field( $_POST['thumb_id'] ),				
			);				
		} else{
			$data_array = array(
				'shipping_country' => sanitize_text_field( $_POST['shipping_country'] ),
				'provider_name' => sanitize_text_field( $_POST['shipping_provider'] ),
				'custom_provider_name' => sanitize_text_field( $_POST['shipping_display_name'] ),
				'ts_slug' => sanitize_title( $_POST['shipping_provider'] ),
				'custom_thumb_id' => sanitize_text_field( $_POST['thumb_id'] ),
				'provider_url' => sanitize_text_field( $_POST['tracking_url'] )		
			);	
		}
		
		$where_array = array(
			'id' => $_POST['provider_id'],			
		);
		$wpdb->update( $this->table, $data_array, $where_array );
		$status = 'active';
		$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table ORDER BY shipping_default ASC, display_in_order DESC, trackship_supported DESC, id ASC" );	
		$html = $this->get_provider_html( $default_shippment_providers, $status );
		echo $html;
		exit;
	}

	/**
	* Reset default provider
	*/
	public function reset_default_provider_fun(){
		global $wpdb;		
				
		$data_array = array(				
			'custom_provider_name' => NULL,				
			'custom_thumb_id' => NULL,
			'api_provider_name' => NULL,			
		);	
		$where_array = array(
			'id' => $_POST['provider_id'],			
		);
		$wpdb->update( $this->table, $data_array, $where_array );
		$status = 'active';
		$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table ORDER BY shipping_default ASC, display_in_order DESC, trackship_supported DESC, id ASC" );	
		$html = $this->get_provider_html( $default_shippment_providers, $status );
		echo $html;
		exit;
	}	
	
	/**
	* Update bulk status of providers to active
	*/
	public function update_provider_status_fun(){
		global $wpdb;
		
		$data_array = array(
			'display_in_order' => $_POST['status'],			
		);
		
		$display_in_order = ( 1 == $_POST['status'] ) ? 0 : 1;
		
		$where_array = array(
			'display_in_order' => $display_in_order,			
		);
		
		$wpdb->update( $this->table, $data_array, $where_array );
		$status = 'all';
		$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table ORDER BY shipping_default ASC, display_in_order DESC, trackship_supported DESC, id ASC" );	
		$html = $this->get_provider_html( $default_shippment_providers, $status );
		exit;
	}	

	/**
	 * Add bulk filter for Shipping provider in orders list
	 *
	 * @since 2.4
	 */
	public function filter_orders_by_shipping_provider(){
		global $typenow, $wpdb;
		$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table ORDER BY shipping_default ASC, display_in_order DESC, trackship_supported DESC, id ASC" );
		
		if ( 'shop_order' === $typenow ) {
		?>
			<select name="_shop_order_shipping_provider" id="dropdown_shop_order_shipping_provider">
				<option value=""><?php esc_html_e( 'Filter by shipping provider', 'woo-advanced-shipment-tracking' ); ?></option>
				<?php foreach ( $default_shippment_providers as $provider ) : ?>
					<option value="<?php echo esc_attr( $provider->ts_slug ); ?>" <?php echo esc_attr( isset( $_GET['_shop_order_shipping_provider'] ) ? selected( $provider->ts_slug, $_GET['_shop_order_shipping_provider'], false ) : '' ); ?>>
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
				'value'     => $_GET['_shop_order_shipping_provider'],
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
     * get_zorem_pluginlist
     * 
     * return array
    */
    public function get_zorem_pluginlist() {
        
        if ( !empty( $this->zorem_pluginlist ) ) return $this->zorem_pluginlist;
        
        if ( false === ( $plugin_list = get_transient( 'zorem_pluginlist' ) ) ) {
            
            $response = wp_remote_get( 'https://www.zorem.com/wp-json/pluginlist/v1/' );
            
            if ( is_array( $response ) && ! is_wp_error( $response ) ) {
                $body    = $response['body']; // use the content
                $plugin_list = json_decode( $body );
                set_transient( 'zorem_pluginlist', $plugin_list, 60*60*24 );
            } else {
                $plugin_list = array();
            }
        }
        return $this->zorem_pluginlist = $plugin_list;
    }	
	
	public function update_custom_order_status_email_display_fun() {
		
		$status = wc_clean( $_POST['status'] );
		
		$wc_ast_show_orders_actions = get_option( 'wc_ast_show_orders_actions' );		
		$wc_ast_show_orders_actions[$status] = 1;
		update_option( 'wc_ast_show_orders_actions', $wc_ast_show_orders_actions );			
		
		$wc_ast_unclude_tracking_info = get_option( 'wc_ast_unclude_tracking_info' );
		$wc_ast_unclude_tracking_info[$status] = 1;		
		update_option( 'wc_ast_unclude_tracking_info', $wc_ast_unclude_tracking_info );		
	}	
	
	/*
     * get tracking provider slug (ts_slug) from database
     * 
     * return provider slug
    */
	public function get_provider_slug_from_name( $tracking_provider_name ) {
		
		global $wpdb;
		
		$tracking_provider = $wpdb->get_var( $wpdb->prepare( "SELECT ts_slug FROM $this->table WHERE api_provider_name = '%s'", $tracking_provider_name ) );		
		
		if ( !$tracking_provider ) {			
			$query = "SELECT ts_slug FROM $this->table WHERE JSON_CONTAINS(api_provider_name, '[".'"'.$tracking_provider_name.'"'."]')";
			$tracking_provider = $wpdb->get_var( $query );			
		}		
		
		if ( !$tracking_provider ) {
			$tracking_provider = $wpdb->get_var( $wpdb->prepare( "SELECT ts_slug FROM $this->table WHERE provider_name = '%s'", $tracking_provider_name ) );
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
		$tooltip_text = class_exists( 'ast_pro' ) ? __( "Add API Name alias", 'ast-pro' ) : __( "Multiple API names mapping is a pro features", 'woo-advanced-shipment-tracking' ) ;
		?>
		<span class="dashicons dashicons-insert woocommerce-help-tip tipTip add_more_api_provider" title="<?php esc_html_e( $tooltip_text ); ?>"></span>	
		<?php 
	}
}