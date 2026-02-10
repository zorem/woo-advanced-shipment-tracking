<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Advanced_Shipment_Tracking_Admin {

	public $table;
		
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
		add_action( 'woocommerce_order_list_table_restrict_manage_orders', array( $this, 'filter_listtable_orders_by_shipping_provider'), 10, 2 );

		add_filter( 'request', array( $this, 'filter_orders_by_shipping_provider_query' ) );
		add_filter( 'woocommerce_shop_order_list_table_prepare_items_query_args', array( $this, 'filter_listtable_orders_by_shipping_provider_query' ) );
		
		add_filter( 'woocommerce_email_title', array( $this, 'change_completed_woocommerce_email_title'), 10, 2 );
		
		add_action( 'wp_ajax_wc_ast_upload_csv_form_update', array( $this, 'upload_tracking_csv_fun') );

		add_action( 'admin_footer', array( $this, 'footer_function'), 1 );
		
		add_filter( 'woocommerce_admin_order_actions', array( $this, 'add_delivered_order_status_actions_button'), 100, 2 );
		
		//Shipping Provider Action
		add_action( 'wp_ajax_paginate_shipping_provider_list', array( $this, 'paginate_shipping_provider_list') );
		
		add_action( 'wp_ajax_filter_shipping_provider_list', array( $this, 'filter_shipping_provider_list') );

		add_action( 'wp_ajax_get_provider_details', array( $this, 'get_provider_details_fun') );

		add_action( 'wp_ajax_shipping_pagination', array( $this, 'shipping_pagination_fun_callback') );
		
		// add_action( 'wp_ajax_update_custom_shipment_provider', array( $this, 'update_custom_shipment_provider_fun') );
		
		add_action( 'wp_ajax_reset_default_provider', array( $this, 'reset_default_provider_fun') );
		
		add_action( 'wp_ajax_woocommerce_shipping_provider_delete', array( $this, 'woocommerce_shipping_provider_delete' ) );
		
		add_action( 'wp_ajax_update_provider_status', array( $this, 'update_provider_status_fun') );
		
		add_action( 'wp_ajax_reset_shipping_providers_database', array( $this, 'reset_shipping_providers_database_fun') );
		
		add_action( 'wp_ajax_update_default_provider', array( $this, 'update_default_provider_fun') );
		
		add_action( 'wp_ajax_update_shipment_status', array( $this, 'update_shipment_status_fun') );

		add_action( 'update_order_status_after_adding_tracking', array( $this, 'update_order_status_after_adding_tracking'), 10, 2 );

		add_action( 'add_more_api_provider', array( $this, 'add_more_api_provider' ) );

		add_action( 'wp_ajax_search_disabled_default_carrier', array( $this, 'search_disabled_default_carrier' ) );
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
		
		wp_enqueue_style( 'ast_styles', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/admin.css', array(), time() );

		wp_enqueue_style( 'ast_go_pro_styles', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/go-pro.css', array(), wc_advanced_shipment_tracking()->version );
		
		wp_enqueue_style( 'ast_slideout_styles', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/slideout.css', array(), wc_advanced_shipment_tracking()->version );
		wp_enqueue_script( 'woocommerce-advanced-shipment-tracking-js', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/admin.js', array(), wc_advanced_shipment_tracking()->version );
		wp_enqueue_script('jquery-ui-datepicker');
		
		wp_register_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full' . $suffix . '.js', array( 'jquery' ), '1.0.4' );
		wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'selectWoo' ), WC_VERSION );
		wp_register_script( 'wc-jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
		
		wp_enqueue_script( 'selectWoo' );
		wp_enqueue_script( 'wc-enhanced-select' );
		
		wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
		wp_enqueue_style( 'woocommerce_admin_styles' );
		wp_enqueue_style( 'wp-color-picker' );
		
		wp_register_script( 'wc-jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery', 'dompurify' ), WC_VERSION, true );
		wp_enqueue_script( 'wc-jquery-tiptip' );
		
		wp_enqueue_script( 'wc-jquery-blockui' );
		wp_enqueue_script( 'wp-color-picker' );
		
		wp_enqueue_script( 'ajax-queue', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/jquery.ajax.queue.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version );
				
		wp_enqueue_script( 'ast_settings', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/settings.js', array( 'jquery', 'wc-jquery-tiptip' ), wc_advanced_shipment_tracking()->version );	
		
		wp_register_script( 'shipment_tracking_table_rows', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/shipping_row.js' , array( 'jquery', 'wp-util', 'wc-jquery-tiptip' ), wc_advanced_shipment_tracking()->version );
		
		wp_localize_script( 'shipment_tracking_table_rows', 'shipment_tracking_table_rows', array(
			'i18n' => array(				
				'data_saved'	=> __( 'Data saved successfully.', 'woo-advanced-shipment-tracking' ),
				'delete_provider' => __( 'Are you sure you want to delete this shipping carrier?', 'woo-advanced-shipment-tracking' ),
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
			<div class="zorem-layout__header">
				<h1 class="page_heading">
					<a href="javascript:void(0)"><?php esc_html_e( 'Shipment Tracking', 'woo-advanced-shipment-tracking' ); ?></a> <span class="dashicons dashicons-arrow-right-alt2"></span> <span class="breadcums_page_heading"><?php esc_html_e( 'Settings', 'woo-advanced-shipment-tracking' ); ?></span>
				</h1>
				<a href="https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/?utm_source=wp-admin&utm_medium=plugin-setting&utm_campaign=upgrad-to-pro" target="_blank"><span class="button-primary btn_ast2">UPGRADE TO PRO</span></a>
				<img class="zorem-layout__header-logo" src="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ); ?>assets/images/zorem-logo.png">
			</div>
			
			<div class="woocommerce zorem_admin_layout">
				<div class="ast_admin_content zorem_admin_settings" >
					<div class="ast_nav_div">
						<?php echo do_shortcode('[ast_settings_admin_notice]'); ?>
						<?php include 'views/activity_panel.php'; ?>
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
		</div>
		<?php include 'views/admin_upgrade_to_pro_popup.php'; ?>
	<?php
	}
	
	/*
	* callback for Shipment Tracking menu array
	*/
	public function get_ast_tab_settings_data() {
		
		$go_pro_label = __( 'Go Pro ✨', 'woo-advanced-shipment-tracking' );
		
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
				'title'		=> __( 'Shipping Carriers', 'woo-advanced-shipment-tracking' ),
				'show'      => true,
				'class'     => 'tab_label',
				'data-tab'  => 'shipping-providers',
				'data-label' => __( 'Shipping Carriers', 'woo-advanced-shipment-tracking' ),
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
				// 'class'     => 'tab_label ast_premium_menu',
				'class'     => 'tab_label',
				'name'  => 'tabs',
			),			
			'tab6' => array(
				'title'		=> $go_pro_label,
				'show'      => true,
				'class'     => 'tab_label go_pro_tab',
				'data-tab'  => 'addons',
				'data-label' => $go_pro_label,
				'name'  => 'tabs',
			),
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
					<a class="menu_link <?php echo esc_attr( $array['class'] ); ?>" href="<?php echo esc_url( $array['link'] ); ?>"><?php echo esc_html( $array['title'] ); ?></a>
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
					$checked = ( get_ast_settings( $array['option_name'], $id, $default ) ) ? 'checked' : '' ;	
					?>
					<li>
						<input type="hidden" name="<?php esc_html_e( $id ); ?>" value="0"/>
						<input class="" id="<?php esc_html_e( $id ); ?>" name="<?php esc_html_e( $id ); ?>" type="checkbox" <?php esc_html_e( $checked ); ?> value="1"/>
											
						<label class="setting_ul_checkbox_label"><?php esc_html_e( $array['title'] ); ?>
						<?php if ( isset( $array['tooltip'] ) ) { ?>
							<span class="woocommerce-help-tip tipTip" data-tip="<?php esc_html_e( $array['tooltip'] ); ?>"></span>
						<?php } ?>
						</label>
					</li>
				<?php
				} else if ( 'tgl_checkbox' == $array['type'] ) {
					$default = isset( $array['default'] ) ? $array['default'] : '';
					$checked = get_option( $id, $default ) ? 'checked' : '' ;
					if ( 'wc_ast_enable_log' == $id ) {
						$checked = ( get_ast_settings( $array['option_name'], $id, $default ) ) ? 'checked' : '' ;
					} 
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
								<span class="woocommerce-help-tip tipTip" data-tip="<?php esc_html_e( $array['tooltip'] ); ?>"></span>
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
						<?php						
						if ( isset( $array['input_desc'] ) ) {
							if ( isset( $array['desc_url'] ) ) { 
								?>
								<span class="ast_log_setting"><?php esc_html_e( $array['input_desc'] ); ?>
								<a target="_blank" class='' href="<?php esc_html_e( $array['desc_url'] ); ?>">Logs</a></span>
							<?php 
							} else {
								?>
								<span><?php esc_html_e( $array['input_desc'] ); ?></span>
							<?php 
							}
						}
						?>
					</li>
				<?php
				} else if ( 'radio' == $array['type'] ) {
					?>
					<li class="settings_radio_li">
						<label><strong><?php esc_html_e( $array['title'] ); ?></strong>
							<?php if ( isset( $array['tooltip'] ) ) { ?>
								<span class="woocommerce-help-tip tipTip" data-tip="<?php esc_html_e( $array['tooltip'] ); ?>"></span>
							<?php } ?>
						</label>

						<?php

						foreach ( (array) $array['options'] as $key => $val ) {
							$selected = ( get_ast_settings( $array['option_name'], $id, $array['default'] ) == (string) $key ) ? 'checked' : '' ;
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
								<span class="woocommerce-help-tip tipTip" data-tip="<?php esc_html_e( $array['tooltip'] ); ?>"></span>
							<?php } ?>
						</label>
						<div class="multiple_select_container <?php esc_html_e( $id ); ?>">	
							<select multiple class="wc-enhanced-select" name="<?php esc_html_e( $id ); ?>[]" id="<?php esc_html_e( $id ); ?>">
							<?php
							foreach ( (array) $array['options'] as $key => $val ) {
								$multi_checkbox_data = get_ast_settings( $array['option_name'], $id, '' );
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
							<label><strong><?php esc_html_e( $array['title'] ); ?></strong></label>
							<span class="multiple_checkbox_description"><?php esc_html_e( $array['desc'] ); ?></span>
						</div >
						<div class="multiple_checkbox_parent">
							<?php 
							$op = 1;
							foreach ( (array) $array['options'] as $key => $val ) {
								$multi_checkbox_data = get_ast_settings( $array['option_name'], $id, '' );
								$checked = isset( $multi_checkbox_data[ $key ] ) && 1 == $multi_checkbox_data[ $key ] ? 'checked' : '' ;
								?>
								<span class="multiple_checkbox">
									<label class="">
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
				} else if ( 'button' == $array['type'] ) {
					?>
					<li>
						<label class="left_label"><?php esc_html_e( $array['title'] ); ?>
							<?php if ( isset( $array['tooltip'] ) ) { ?>
								<span class="woocommerce-help-tip tipTip" data-tip="<?php esc_html_e( $array['tooltip'] ); ?>"></span>
							<?php } ?>
						</label>
						<?php
						if ( isset( $array['customize_link'] ) ) {
							?>
							<a href="<?php esc_html_e( $array['customize_link'] ); ?>" class="button-primary btn_ts_transparent btn_large ts_customizer_btn"><?php esc_html_e( 'Customize', 'woo-advanced-shipment-tracking' ); ?></a>
						<?php } ?>
					</li>
				<?php
				} else if ( 'pro_feature' == $array['type'] ) {
					$title = isset( $array['title'] ) ? $array['title'] : '';
					$tooltip = isset( $array['tooltip'] ) ? $array['tooltip'] : '';
					$upgrade_url = isset( $array['upgrade_url'] ) ? $array['upgrade_url'] : 'https://www.zorem.com/ast-pro/?utm_source=wp-admin&utm_medium=plugin-setting&utm_campaign=pro-feature';
					?>
					<li class="ast-pro-feature-row">
						<div class="ast-pro-feature-content">
							<div class="ast-pro-toggle-wrapper">
								<div class="ast-pro-toggle-disabled"></div>
							</div>
							<div class="ast-pro-feature-text">
								<label class="ast-pro-feature-label"><?php echo esc_html( $title ); ?></label>
								<?php if ( ! empty( $tooltip ) ) { ?>
									<span class="ast-pro-info-icon" data-tip="<?php echo esc_attr( $tooltip ); ?>">?</span>
								<?php } ?>
							</div>
							<div class="ast-pro-feature-badges">
								<span class="ast-pro-badge">PRO</span>
								<span class="ast-pro-lock-icon"></span>
							</div>
						</div>
					</li>
				<?php
				}
			}
		}
		?>
		</ul>
	<?php
	}

	/**
	 * Render a PRO feature row (locked state)
	 *
	 * @param string $title Feature title
	 * @param string $description Feature description
	 * @param string $upgrade_url Optional upgrade URL
	 */
	public function render_pro_feature_row( $title, $description, $upgrade_url = '' ) {
		if ( empty( $upgrade_url ) ) {
			$upgrade_url = 'https://www.zorem.com/ast-pro/?utm_source=wp-admin&utm_medium=plugin-setting&utm_campaign=pro-feature';
		}
		?>
		<li class="ast-pro-feature-row">
			<div class="ast-pro-feature-content">
				<div class="ast-pro-feature-left">
					<span class="ast-pro-lock-icon"></span>
					<div class="ast-pro-feature-text">
						<div class="ast-pro-feature-title">
							<?php echo esc_html( $title ); ?>
						</div>
						<div class="ast-pro-feature-desc">
							<?php echo esc_html( $description ); ?>
						</div>
					</div>
				</div>
				<div class="ast-pro-feature-right">
					<div class="ast-pro-toggle-disabled"></div>
					<span class="ast-pro-badge">PRO</span>
				</div>
			</div>
		</li>
		<?php
	}

	/**
	 * Render multiple PRO feature rows
	 *
	 * @param array $features Array of features with 'title' and 'description'
	 */
	public function render_pro_features_section( $features ) {
		if ( empty( $features ) || ! is_array( $features ) ) {
			return;
		}
		?>
		<ul class="settings_ul">
		<?php
		foreach ( $features as $feature ) {
			$title = isset( $feature['title'] ) ? $feature['title'] : '';
			$description = isset( $feature['description'] ) ? $feature['description'] : '';
			$upgrade_url = isset( $feature['upgrade_url'] ) ? $feature['upgrade_url'] : '';

			if ( ! empty( $title ) ) {
				$this->render_pro_feature_row( $title, $description, $upgrade_url );
			}
		}
		?>
		</ul>
		<?php
	}

	public function get_add_tracking_options() {
		
		$wc_ast_status_shipped = get_ast_settings( 'ast_general_settings', 'wc_ast_status_shipped', 0 );
		
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
			/*'wc_ast_default_mark_shipped' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'Set the "mark as shipped" option checked  when adding tracking info to orders', 'woo-advanced-shipment-tracking' ),
				'show'		=> true,
				'class'     => '',
			),*/
			'wc_ast_show_orders_actions' => array(
				'type'		=> 'multiple_select',
				'title'		=> __( 'Add Tracking Order action', 'woo-advanced-shipment-tracking' ),
				'tooltip'		=> __( 'Choose which Order Status in your store you would like to display Add the Tracking icon in the Order Actions menu.', 'woo-advanced-shipment-tracking' ),
				'options'   => $action_order_status_array,
				'option_name' => 'ast_general_settings',
				'show'		=> true,
				'class'     => '',
			),
			'wc_ast_unclude_tracking_info' => array(
				'type'		=> 'multiple_select',
				'title'		=> __( 'Order Emails Display', 'woo-advanced-shipment-tracking' ),
				'tooltip'		=> __( 'This option allows you to choose on which order status email you would like to display the tracking information', 'woo-advanced-shipment-tracking' ),
				'options'   => $order_status_array,
				'option_name' => 'ast_general_settings',
				'show'		=> true,
				'class'     => '',
			),
			'ast_pro_multiple_tracking' => array(
				'type'		=> 'pro_feature',
				'title'		=> __( 'Enable multiple tracking numbers per order item', 'woo-advanced-shipment-tracking' ),
				'tooltip'	=> __( 'Allow adding multiple tracking numbers for each order item', 'woo-advanced-shipment-tracking' ),
				'show'		=> true,
			),
			'ast_pro_shipment_email' => array(
				'type'		=> 'pro_feature',
				'title'		=> __( 'Shipment Email Tracking Content', 'woo-advanced-shipment-tracking' ),
				'tooltip'	=> __( 'Customize email content for shipment tracking notifications', 'woo-advanced-shipment-tracking' ),
				'show'		=> true,
			),
		);
		return $form_data;
	}
	
	public function get_shipment_tracking_api_options() {				
		$form_data = array(
			'ast_pro_api_auto_complete' => array(
				'type'		=> 'pro_feature',
				'title'		=> __( 'Auto-complete orders that come from the API', 'woo-advanced-shipment-tracking' ),
				'tooltip'	=> __( 'Automatically mark orders as completed when tracking information is added via API', 'woo-advanced-shipment-tracking' ),
				'show'		=> true,
			),
			'ast_pro_api_restrict_duplicate' => array(
				'type'		=> 'pro_feature',
				'title'		=> __( 'Restrict adding the same tracking number', 'woo-advanced-shipment-tracking' ),
				'tooltip'	=> __( 'Prevent duplicate tracking numbers from being added to orders', 'woo-advanced-shipment-tracking' ),
				'show'		=> true,
			),
			'wc_ast_api_date_format' => array(
				'type'		=> 'radio',
				'title'		=> __( 'API Date Format', 'woo-advanced-shipment-tracking' ),
				'tooltip'		=> __( 'Choose the date format that you use when updating the shipment tracking API endpoint from external sources', 'woo-advanced-shipment-tracking' ),
				'desc'		=> __( 'Choose for which Order status to display', 'woo-advanced-shipment-tracking' ),
				'options'   => array(
									'd-m-Y' => 'DD-MM-YYYY',
									'm-d-Y' => 'MM-DD-YYYY',
							),
				'default'   => 'd-m-Y',
				'show'		=> true,
				'option_name' => 'ast_general_settings',
				'class'     => '',
			),
			'wc_ast_enable_log' => array(
				'title'		=> __( 'Enable log', 'woo-advanced-shipment-tracking' ),
				'tooltip'   => __( 'Enable this to log all incoming API requests and responses for the Shipment Tracking API in WooCommerce logs. Logs can be found under WooCommerce > Status > Logs.', 'woo-advanced-shipment-tracking' ),
				'type'		=> 'tgl_checkbox',
				'default'	=> 0,
				'show'		=> true,
				'option_name' => 'ast_general_settings',
				'class'		=> '',
				'input_desc' => __( 'Log will be added to WooCommerce > Status > ', 'woo-advanced-shipment-tracking' ),
				'desc_url'	=>  admin_url( 'admin.php?page=wc-status&tab=logs', 'https' ),
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
				'option_name' => 'ast_general_settings',
			),			
			'wc_ast_status_updated_tracking_label_color' => array(
				'type'		=> 'color',
				'title'		=> __( 'Updated Tracking Label color', '' ),				
				'class'		=> 'updated_tracking_status_label_color_th',
				'show'		=> true,
				'option_name' => 'ast_general_settings',
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
				'option_name' => 'ast_general_settings',
			),			
			'wcast_enable_updated_tracking_email' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Enable the Updated Tracking order status email', '' ),
				'title_link'=> '',
				'class'		=> 'updated_tracking_status_label_color_th',
				'show'		=> true,
				'option_name' => 'ast_general_settings',
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
				'option_name' => 'ast_general_settings',
				'class'     => '',
			),			
			'wc_ast_status_partial_shipped_label_color' => array(
				'type'		=> 'color',
				'title'		=> __( 'Partially Shipped Label color', '' ),				
				'class'		=> 'partial_shipped_status_label_color_th',
				'show'		=> true,
				'option_name' => 'ast_general_settings',
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
				'option_name' => 'ast_general_settings',
			),			
			'wcast_enable_partial_shipped_email' => array(
				'type'		=> 'checkbox',
				'title'		=> __( 'Enable the Partially Shipped order status email', '' ),
				'title_link'=> '',
				'class'		=> 'partial_shipped_status_label_color_th',
				'show'		=> true,
				'option_name' => 'ast_general_settings',
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
				'option_name' => 'ast_general_settings',
				'class'     => '',
			),			
			'wc_ast_status_label_color' => array(
				'type'		=> 'color',
				'title'		=> __( 'Delivered Label color', '' ),				
				'class'		=> 'status_label_color_th',
				'show'		=> true,
				'option_name' => 'ast_general_settings',
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
				'option_name' => 'ast_general_settings',
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
				'option_name' => 'ast_general_settings',
			),
			'shipped' => array(
				'id'		=> 'wc_ast_status_shipped_pro',
				'slug'   	=> 'shipped',
				'label'		=> __( 'Shipped', 'woo-advanced-shipment-tracking' ),
				'label_class' => 'wc-shipped',
				'option_id'	=> '',
				'edit_email'=> '',
				'label_color_field' => 'wc_ast_status_shipped_label_color',
				'font_color_field' => 'wc_ast_status_shipped_label_font_color',
				'email_field' => 'wcast_enable_shipped_email',
				'option_name' => 'ast_general_settings',
				'pro'		=> true,
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
				'option_name' => 'ast_general_settings',
			),
		);
		
		$updated_tracking_status = get_ast_settings( 'ast_general_settings', 'wc_ast_status_updated_tracking', 0 );

		if ( true == $updated_tracking_status) {	
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
					'option_name' => 'ast_general_settings',			
				),		
			);
			$osm_data = array_merge( $osm_data, $updated_tracking_data );
		}
		return apply_filters( 'ast_osm_data', $osm_data );		
	}

	/*
	* Usage Tracking form save
	*/
	public function wc_usage_tracking_form_update_callback() {
		if ( ! current_user_can( AST_FREE_PLUGIN_ACCESS ) ) {
			exit( 'You are not allowed' );
		}
		
		if ( ! empty( $_POST ) && check_admin_referer( 'wc_usage_tracking_form', 'wc_usage_tracking_form_nonce' ) ) {
			$data3 = $this->get_usage_tracking_options();						
			
			foreach ( $data3 as $key => $val ) {				
				if ( isset( $_POST[ $key ] ) ) {						
					update_option( $key, wc_clean( $_POST[ $key ] ) );
					// update_ast_settings( $val['option_name'], $key, wc_clean( $_POST[ $key ] ) );
				}				
			}
		}
		wp_send_json(true);
	}
	
	/*
	* settings form save
	*/
	public function wc_ast_settings_form_update_callback() {
		
		if ( ! current_user_can( AST_FREE_PLUGIN_ACCESS ) ) {
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
						// update_option( $key, wc_clean( $_POST[ $key ] ) );
						update_ast_settings( $val['option_name'], $key, wc_clean( $_POST[ $key ] ) );
					}
					
					
				} else {
					
					if ( isset( $_POST[ $key ] ) ) {						
						// update_option( $key, wc_clean( $_POST[ $key ] ) );
						update_ast_settings( $val['option_name'], $key, wc_clean( $_POST[ $key ] ) );
					}	
				}
				
				if ( isset( $val['type'] ) && 'inline_checkbox' == $val['type'] ) {
					foreach ( (array) $val['checkbox_array'] as $key1 => $val1 ) {
						if ( isset( $_POST[ $key1 ] ) ) {						
							// update_option( $key1, wc_clean( $_POST[ $key1 ] ) );
							update_ast_settings( $val['option_name'], $key, wc_clean( $_POST[ $key1 ] ) );
						}
					}					
				}
			}

			$data2 = $this->get_shipment_tracking_api_options();						
			
			foreach ( $data2 as $key => $val ) {				
				
				if ( isset( $_POST[ $key ] ) ) {						
					// update_option( $key, wc_clean( $_POST[ $key ] ) );
					update_ast_settings( $val['option_name'], $key, wc_clean( $_POST[ $key ] ) );
				}
			}

			$wc_ast_status_shipped = isset( $_POST[ 'wc_ast_status_shipped' ] ) ? wc_clean( $_POST[ 'wc_ast_status_shipped' ] ) : '';
			update_ast_settings( 'ast_general_settings', 'wc_ast_status_shipped', $wc_ast_status_shipped );
			
			
			$data = $this->get_delivered_data();						
			foreach ( $data as $key => $val ) {				
				if ( isset( $_POST[ $key ] ) ) {						
					// update_option( $key, wc_clean( $_POST[ $key ] ) );
					update_ast_settings( $val['option_name'], $key, wc_clean( $_POST[ $key ] ) );
				}
			}
			
			
			$data = $this->get_partial_shipped_data();						
			
			foreach ( $data as $key => $val ) {
				if ( 'wcast_enable_partial_shipped_email' == $key ) {					
					if ( isset($_POST['wcast_enable_partial_shipped_email']) && 1 == $_POST['wcast_enable_partial_shipped_email'] ) {
						update_ast_settings( $val['option_name'], $key, wc_clean( $_POST[ $key ] ) );
						update_option( 'customizer_partial_shipped_order_settings_enabled', wc_clean( $_POST['wcast_enable_partial_shipped_email'] ) );
						$enabled = 'yes';
					} else {
						update_ast_settings( $val['option_name'], $key, '' );
						update_option( 'customizer_partial_shipped_order_settings_enabled', '' );
						$enabled = 'no';
					}

					// Get the option and ensure it's an array
					$wcast_enable_partial_shipped_email = (array) get_option( 'woocommerce_customer_partial_shipped_order_settings', array() );
					$wcast_enable_partial_shipped_email['enabled'] = $enabled;
					update_option( 'woocommerce_customer_partial_shipped_order_settings', $wcast_enable_partial_shipped_email );

				}										
				
				if ( isset( $_POST[ $key ] ) ) {						
					update_ast_settings( $val['option_name'], $key, wc_clean( $_POST[ $key ] ) );
				}
			}
			
			$data = $this->get_updated_tracking_data();						
			
			foreach ( $data as $key => $val ) {		
				
				if ( 'wcast_enable_updated_tracking_email' == $key ) {						
					if ( isset( $_POST['wcast_enable_updated_tracking_email'] ) ) {						
						if ( isset($_POST['wcast_enable_updated_tracking_email']) && 1 == $_POST['wcast_enable_updated_tracking_email'] ) {
							update_ast_settings( $val['option_name'], $key, wc_clean( $_POST[ $key ] ) );
							$enabled = 'yes';
						} else {
							update_ast_settings( $val['option_name'], $key, '' );
							$enabled = 'no';
						}
						update_option( 'woocommerce_customer_updated_tracking_order_settings', 'enabled', $enabled );
					}
					update_option( 'woocommerce_customer_updated_tracking_order_settings', 'enabled', $enabled );	
				}										
				
				if ( isset( $_POST[ $key ] ) ) {						
					update_ast_settings( $val['option_name'], $key, wc_clean( $_POST[ $key ] ) );
				}
			}						
		}
	}	
		
	/*
	* Change style of delivered order label
	*/	
	public function footer_function() {
		if ( !is_plugin_active( 'woocommerce-order-status-manager/woocommerce-order-status-manager.php' ) ) {
			$bg_color = get_ast_settings( 'ast_general_settings', 'wc_ast_status_label_color', '#59c889' );
			$color = get_ast_settings( 'ast_general_settings', 'wc_ast_status_label_font_color', '#fff' );						
			
			$ps_bg_color = get_ast_settings( 'ast_general_settings', 'wc_ast_status_partial_shipped_label_color', '#1e73be' );
			$ps_color = get_ast_settings( 'ast_general_settings', 'wc_ast_status_partial_shipped_label_font_color', '#fff' );
			
			$ut_bg_color = get_ast_settings( 'ast_general_settings', 'wc_ast_status_updated_tracking_label_color', '#23a2dd' );
			$ut_color = get_ast_settings( 'ast_general_settings', 'wc_ast_status_updated_tracking_label_font_color', '#fff' );

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
		
		if ( ! current_user_can( AST_FREE_PLUGIN_ACCESS ) ) {
			exit( 'You are not allowed' );
		}
		
		check_ajax_referer( 'nonce_csv_import', 'security' );
		
		$replace_tracking_info = isset( $_POST['replace_tracking_info'] ) ? wc_clean( $_POST['replace_tracking_info'] ) : '';
		$date_format_for_csv_import = isset( $_POST['date_format_for_csv_import'] ) ? wc_clean( $_POST['date_format_for_csv_import'] ) : '';
		update_ast_settings( 'ast_general_settings', 'date_format_for_csv_import', $date_format_for_csv_import );
		$order_number = isset( $_POST['order_id'] ) ? wc_clean( $_POST['order_id'] ) : '';				
		
		$wast = WC_Advanced_Shipment_Tracking_Actions::get_instance();
		$order_id = $wast->get_formated_order_id( $order_number );

		$tracking_provider = isset( $_POST['tracking_provider'] ) ? wc_clean( $_POST['tracking_provider'] ) : '';
		$tracking_number = isset( $_POST['tracking_number'] ) ? wc_clean( $_POST['tracking_number'] ) : '';
		$status_shipped = ( isset( $_POST['status_shipped'] ) ? wc_clean( $_POST['status_shipped'] ) : '' );
		$date_shipped = ( isset( $_POST['date_shipped'] ) ? wc_clean( $_POST['date_shipped'] ) : '' );
		$date_shipped = str_replace( '/', '-', $date_shipped );
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
			
			$args['source'] = __( ' csv ', 'woo-advanced-shipment-tracking' );

			$tracking_item = $wast->add_tracking_item( $order_id, $args );

			$wc_ast_enable_log = get_ast_settings( 'ast_general_settings', 'wc_ast_enable_log', 0 );
			 
			if ( 1 == $wc_ast_enable_log ) {
				$log_content = array(
					'url'		=> isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : 'unknown',
					'request'	=> $_POST,
					'response'	=> array( 'tracking_item' => $tracking_item ),
				);

				$ast = WC_AST_Logger::get_instance();
				$ast->log_event( 'ast_create_csv_import_log', $log_content );
			}

			echo '<li class="success">Success - added tracking info to Order ' . esc_html( $order_number ) . '</li>';
			exit;				
		} else {
			echo '<li class="invalid_tracking_data_error">Failed - Invalid Tracking Data</li>';
			exit;
		}		
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
			
			$wc_ast_status_partial_shipped = get_ast_settings( 'ast_general_settings', 'wc_ast_status_partial_shipped', '' );
			
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
			
			// $wc_ast_status_updated_tracking = get_option( 'wc_ast_status_updated_tracking' );
			$wc_ast_status_updated_tracking = get_ast_settings( 'ast_general_settings', 'wc_ast_status_updated_tracking', 0 );
			
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
		$wc_ast_status_shipped = get_ast_settings( 'ast_general_settings', 'wc_ast_status_shipped', 0 );		
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
		wp_enqueue_style( 'ast_slideout_styles', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/slideout.css', array(), wc_advanced_shipment_tracking()->version );	
		wp_enqueue_script( 'woocommerce-advanced-shipment-tracking-js', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/admin.js', array(), wc_advanced_shipment_tracking()->version );
		wp_localize_script(
			'woocommerce-advanced-shipment-tracking-js',
			'ast_orders_params',
			array(
				'order_nonce' => wp_create_nonce( 'ast-order-list' ),
			)
		);			
		
		$wc_ast_show_orders_actions = get_ast_settings( 'ast_general_settings', 'wc_ast_show_orders_actions', '' );
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
		
		$wc_ast_status_shipped = get_ast_settings( 'ast_general_settings', 'wc_ast_status_shipped', '' );
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
		$items_per_page = 99;
		
		// offset
		$offset = ( $page - 1 ) * $items_per_page;

		if ( null != $search_term ) {
			$totla_shipping_provider = $wpdb->get_row( $wpdb->prepare( 'SELECT COUNT(*) as total_providers FROM %1s WHERE ( provider_name LIKE %s OR shipping_country_name LIKE %s) AND ( display_in_order = 1 )', $this->table, '%%' . $search_term . '%%', '%' . $search_term . '%' ) );			
			$shippment_providers = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s WHERE ( provider_name LIKE %s OR shipping_country_name LIKE %s) AND ( display_in_order = 1 ) ORDER BY shipping_default ASC, display_in_order DESC, trackship_supported DESC, id ASC LIMIT %4$d, %5$d', $this->table, '%%' . $search_term . '%%', '%' . $search_term . '%', $offset, $items_per_page ) );
		} else {
			$totla_shipping_provider = $wpdb->get_row( $wpdb->prepare( 'SELECT COUNT(*) as total_providers FROM %1s WHERE display_in_order = 1', $this->table ) );			
			$shippment_providers = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s WHERE display_in_order = 1 ORDER BY shipping_default ASC, display_in_order DESC, trackship_supported DESC, id ASC LIMIT %d, %d', $this->table, $offset, $items_per_page ) );
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
			<div class="provider-grid-row grid-row" data-shippment-providers="true">
				<div class="grid-item hip-item add-provider-container">
					<div class="add-provider-box">						
						<div class="add_custom_provider add-provider-label"><span class="dashicons dashicons-plus-alt add_custom_provider"></span><?php esc_html_e('Enable Carriers', 'woo-advanced-shipment-tracking'); ?></div>
					</div>
				</div>
				<?php 
				foreach ( $shippment_providers as $index => $d_s_p ) {
				$provider_type = ( 1 == $d_s_p->shipping_default ) ? 'default_provider' : 'custom_provider';
					?>
				<div class="grid-item">					
					<div class="grid-top">
						<div class="grid-provider-img">
							<?php  
							if ( 1 == $d_s_p->shipping_default ) {
								$provider_image = $ast_directory . '' . esc_html( $d_s_p->ts_slug ) . '.png?v=' . wc_advanced_shipment_tracking()->version;
								echo '<img class="provider-thumb" src="' . esc_url( $provider_image ) . '">';
							} else { 
								echo '<img class="provider-thumb" src="' . esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ) . 'assets/images/icon-default.png">';								
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
							<span class="dashicons dashicons-ellipsis provider_actions_btn"></span>
							<ul class="provider-action-ul">
								<li><a href="javaScript:void(0);" class="edit_provider" data-provider="<?php esc_html_e( $provider_type ); ?>" data-pid="<?php esc_html_e( $d_s_p->id ); ?>"><?php esc_html_e('Edit', 'woo-advanced-shipment-tracking'); ?></a></li>
								<li><a style="color:#f44336" href="javaScript:void(0);" class="remove" data-pid="<?php esc_html_e( $d_s_p->id ); ?>"><?php esc_html_e('Delete', 'woo-advanced-shipment-tracking'); ?></a></li>
							</ul>	
							<input type="checkbox" name="bulk_select_provider[]" class="bulk_select_provider" value="<?php echo esc_html( $d_s_p->id ); ?>">
						</div>
					</div>					
				</div>
				<?php } ?>
								
			</div>			
			<?php 
			} else {
				?>
				<div class="provider-grid-row grid-row" data-shippment-providers="false">
					<div class="grid-item hip-item add-provider-container">
						<div class="add-provider-box">						
							<div class="add_custom_provider add-provider-label"><span class="dashicons dashicons-plus-alt add_custom_provider"></span><?php esc_html_e('Enable Carriers', 'woo-advanced-shipment-tracking'); ?></div>
						</div>
					</div>
				</div>				
				<?php 
			}
			$total_pages = ceil($total_provders / $items_per_page);	
			if ( $total_pages > 1 ) {
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
			<?php } ?>
		</div>
		<?php 
	}
	
	public function paginate_shipping_provider_list() {
		
		if ( ! current_user_can( AST_FREE_PLUGIN_ACCESS ) ) {
			exit( 'You are not allowed' );
		}

		check_ajax_referer( 'nonce_shipping_provider', 'security' );
		
		$page = isset( $_POST['page'] ) ? wc_clean( $_POST['page'] ) : '';
		$html = $this->get_provider_html( $page );		
		exit;
	}
	
	public function filter_shipping_provider_list() {
		
		if ( ! current_user_can( AST_FREE_PLUGIN_ACCESS ) ) {
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
		
		if ( ! current_user_can( AST_FREE_PLUGIN_ACCESS ) ) {
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
		
		if ( ! current_user_can( AST_FREE_PLUGIN_ACCESS ) ) {
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

		if ( ! current_user_can( AST_FREE_PLUGIN_ACCESS ) ) {
			exit( 'You are not allowed' );
		}
		
		check_ajax_referer( 'nonce_shipping_provider', 'security' );
		
		$provider_id = isset( $_POST['provider_id'] ) ? wc_clean( $_POST['provider_id'] ) : '';
		
		if ( ! empty( $provider_id ) ) {
			global $wpdb;
			$data_array = array(				
				'display_in_order' => 0,
			);
			
			$where_array = array(
				'id' => $provider_id,			
			);

			$wpdb->update( $this->table, $data_array, $where_array );
		}
		$html = $this->get_provider_html( 1 );		
		exit;
	}
	
	/**
	* Get shipping provider details fun 
	*/
	public function get_provider_details_fun() {
		
		if ( ! current_user_can( AST_FREE_PLUGIN_ACCESS ) ) {
			exit( 'You are not allowed' );
		}
		
		check_ajax_referer( 'nonce_shipping_provider', 'security' );
		
		$id = isset( $_POST['provider_id'] ) ? wc_clean( $_POST['provider_id'] ) : '';		
		global $wpdb;
		
		$shippment_provider = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM %1s WHERE id=%d', $this->table, $id ) );		
		include 'views/edit_shipping_provider.php';
		exit;			
	}

	/**
	* Get shipping provider details fun 
	*/
	public function shipping_pagination_fun_callback() {
		global $wpdb;
		
		if ( ! current_user_can( AST_FREE_PLUGIN_ACCESS ) ) {
			exit( 'You are not allowed' );
		}
		
		check_ajax_referer( 'nonce_shipping_pagination_provider', 'security' );

		$page = isset( $_POST['paged'] ) ? wc_clean( $_POST['paged'] ) : 1;	
		$search = isset( $_POST['search'] ) ? wc_clean( $_POST['search'] ) : '';		
		$html = $this->shipping_pagination_fun( $page, $search );
		exit;
	}
	
	public function shipping_pagination_fun( $page = 1, $search = '' ) {
		global $wpdb;
		$upload_dir   = wp_upload_dir();
		$ast_directory = $upload_dir['baseurl'] . '/ast-shipping-providers/';
		$items_per_page = 10;
		$start = ( $page - 1 ) * $items_per_page;

		$shippment_provider_pagination = $wpdb->get_results( 
			$wpdb->prepare("SELECT * FROM {$wpdb->prefix}woo_shippment_provider WHERE display_in_order = 0 AND ( provider_name LIKE %s OR shipping_country_name LIKE %s) ORDER BY id ASC LIMIT %d,%d",
			'%%' . $search . '%%', '%' . $search . '%' , $start, $items_per_page )
		);
		$total_shipping_providers = $wpdb->get_row( 
			$wpdb->prepare("SELECT COUNT(*) as total_providers FROM {$wpdb->prefix}woo_shippment_provider WHERE display_in_order = 0 AND ( provider_name LIKE %s OR shipping_country_name LIKE %s) ORDER BY id ASC",
			'%%' . $search . '%%', '%' . $search . '%' )
		);

		$added_provider = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woo_shippment_provider WHERE provider_name LIKE %s AND ( display_in_order = 1 ) AND ( shipping_default = 1 ) ORDER BY shipping_default ASC, trackship_supported DESC, id ASC", '%%' . $search . '%%' ) );

		$total_provders = $total_shipping_providers->total_providers;
		$total_pages = ceil($total_provders / $items_per_page);
		?>
		<div class="default_privder_list">
		<?php
		if ( $shippment_provider_pagination ) {
			?>
			<div class="provider-grid-row grid-row">
				<?php
				foreach ($shippment_provider_pagination as $key => $provider) {
					?>
					<div class="grid-item hip-item">
						<div class="grid-left">				
							<div class="grid-top">
								<div class="grid-provider-img">
									<?php 
									$provider_image = $ast_directory . '' . esc_html( $provider->ts_slug ) . '.png?v=' . wc_advanced_shipment_tracking()->version;									
									echo '<img class="provider-thumb" src="' . esc_url( $provider_image ) . '">';
									?>
								</div>
								<div class="grid-provider-name">
									<span class="provider_name"><?php esc_html_e( $provider->provider_name ); ?></span>		
									<span class="provider_country"><?php esc_html_e( $provider->shipping_country_name ); ?></span>
								</div>							
							</div>						
						</div>
						<div class="grid-right">
							<button class="button add_default_provider" type="button" data-id="<?php echo esc_html( $provider->id ); ?>"><?php esc_html_e( 'Add', 'woo-advanced-shipment-tracking' ); ?></button>
						</div>				
					</div>
				<?php } ?>
			</div>
		<?php
		} else if ( !empty( $added_provider ) ) {
			?>
			<div class="provider_msg"><?php esc_html_e( 'Shipping Carrier Already Added', 'woo-advanced-shipment-tracking' ); ?></div>
			<?php
		} else {
			?>
			<div class="provider_msg"><?php esc_html_e( 'Shipping Carrier Not Found!', 'woo-advanced-shipment-tracking' ); ?></div>
			<div class="provider_note">
				<span><?php esc_html_e( 'Try syncing your shipping carriers to get the latest list.', 'woo-advanced-shipment-tracking' ); ?></span>
				<button type="button" class="button button-primary button-small sync_providers">
    				<?php esc_html_e('Sync Carriers', 'woo-advanced-shipment-tracking'); ?>
				</button>
			</div>
			<?php
		}
		if ( $total_pages > 1 ) {
			$prev_disabled = ( 1 == $page ) ? 'disabled' : '';
			$next_disabled = ( $page >= $total_pages ) ? 'disabled' : '';
			?>
			<div class="shipping_carriers_arrow_pagination">
				<input type="hidden" id="nonce_shipping_pagination_provider" value="<?php esc_html_e( wp_create_nonce( 'nonce_shipping_pagination_provider' ) ); ?>">
				<button data-number="<?php echo esc_html( $page - 1 ); ?>" data-side="left" class="dashicons dashicons-arrow-left-alt arrow_pagination" <?php esc_html_e( $prev_disabled ); ?>></button>
				<button data-number="<?php echo esc_html( $page + 1 ); ?>" data-side="right" class="dashicons dashicons-arrow-right-alt arrow_pagination" <?php esc_html_e( $next_disabled ); ?>></button>
			</div>
			<?php } ?>			
		</div>		
		<?php
	}

	/**
	* Update custom shipping provider and returen html of it
	*/
	public function update_custom_shipment_provider_fun() {
		
		if ( ! current_user_can( AST_FREE_PLUGIN_ACCESS ) ) {
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
		
		if ( ! current_user_can( AST_FREE_PLUGIN_ACCESS ) ) {
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
		
		if ( ! current_user_can( AST_FREE_PLUGIN_ACCESS ) ) {
			exit( 'You are not allowed' );
		}
		
		check_ajax_referer( 'nonce_shipping_provider', 'security' );
		
		global $wpdb;
		
		$providers_id = isset( $_POST['providers_id'] ) ? wc_clean( $_POST['providers_id'] ) : '';

		$data_remove_selected = isset( $_POST['data_remove_selected'] ) ? wc_clean( $_POST['data_remove_selected'] ) : '';

		if ( 'all' == $data_remove_selected ) {
			$wpdb->query("UPDATE {$this->table} SET display_in_order = 0");			
		}

		if ( 'selected-page' == $data_remove_selected ) {
			foreach ( $providers_id as $id ) {
				$data_array = array(				
					'display_in_order' => 0,
				);
				
				$where_array = array(
					'id' => $id,			
				);
				
				$wpdb->update( $this->table, $data_array, $where_array );
			}		
		}

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
		$default_shippment_providers = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s WHERE display_in_order = 1 ORDER BY shipping_default ASC, display_in_order DESC, trackship_supported DESC, id ASC', $this->table ) );
		
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

	public function filter_listtable_orders_by_shipping_provider( $order_type, $which ) {
		global $wpdb;		
		$default_shippment_providers = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s WHERE display_in_order = 1 ORDER BY shipping_default ASC, display_in_order DESC, trackship_supported DESC, id ASC', $this->table ) );		
		if ( 'shop_order' === $order_type ) {
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
	
	public function filter_listtable_orders_by_shipping_provider_query( $args ) {
		if ( isset( $_GET['_shop_order_shipping_provider'] ) && '' != $_GET['_shop_order_shipping_provider'] ) {
			$args['meta_query'][] = array(
				'key'       => '_wc_shipment_tracking_items',
				'value'     => wc_clean( $_GET['_shop_order_shipping_provider'] ),
				'compare'   => 'LIKE'
			);						
		}
		return $args;
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
			/*$tracking_provider = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT ts_slug FROM %s WHERE JSON_CONTAINS(LOWER(api_provider_name), LOWER('[%s]'))",
					$this->table,
					$tracking_provider_name
				)
			);*/
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

	public function search_disabled_default_carrier() {
		
		if ( ! current_user_can( AST_FREE_PLUGIN_ACCESS ) ) {
			exit( 'You are not allowed' );
		}

		check_ajax_referer( 'nonce_shipping_provider', 'security' );
		
		$search_term = isset( $_POST['search_term'] ) ? wc_clean( $_POST['search_term'] ) : '';
		echo wp_kses_post( $this->shipping_pagination_fun( 1, $search_term ) );
		exit;
	}
}
