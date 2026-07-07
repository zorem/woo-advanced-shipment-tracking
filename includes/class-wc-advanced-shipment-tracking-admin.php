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

		add_action( 'wp_ajax_woocommerce_shipping_provider_delete', array( $this, 'woocommerce_shipping_provider_delete' ) );

		add_action( 'wp_ajax_update_provider_status', array( $this, 'update_provider_status_fun') );

		add_action( 'wp_ajax_update_shipment_status', array( $this, 'update_shipment_status_fun') );

		add_action( 'update_order_status_after_adding_tracking', array( $this, 'update_order_status_after_adding_tracking'), 10, 2 );

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

		if ( ! wp_script_is( 'select2', 'registered' ) ) {
			wp_register_script( 'select2', WC()->plugin_url() . '/assets/js/select2/select2.full' . $suffix . '.js', array( 'jquery' ), '4.0.3' );
		}
		wp_enqueue_script( 'select2' );
		
		wp_enqueue_style( 'ast_styles', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/admin.css', array(), time() );

		wp_enqueue_style( 'ast_go_pro_styles', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/go-pro.css', array(), wc_advanced_shipment_tracking()->version );
		
		wp_enqueue_style( 'ast_slideout_styles', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/slideout.css', array(), wc_advanced_shipment_tracking()->version );
		wp_enqueue_script( 'woocommerce-advanced-shipment-tracking-js', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/admin.js', array(), wc_advanced_shipment_tracking()->version );
		wp_enqueue_script('jquery-ui-datepicker');
		
		if ( ! wp_script_is( 'selectWoo', 'registered' ) ) {
			wp_register_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full' . $suffix . '.js', array( 'jquery' ), '1.0.4' );
		}
		if ( ! wp_script_is( 'wc-enhanced-select', 'registered' ) ) {
			wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'selectWoo' ), WC_VERSION );
		}
		if ( ! wp_script_is( 'wc-jquery-blockui', 'registered' ) ) {
			wp_register_script( 'wc-jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
		}

		wp_enqueue_script( 'selectWoo' );
		wp_enqueue_script( 'wc-enhanced-select' );

		if ( ! wp_style_is( 'woocommerce_admin_styles', 'registered' ) ) {
			wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
		}
		wp_enqueue_style( 'woocommerce_admin_styles' );
		wp_enqueue_style( 'wp-color-picker' );

		if ( ! wp_script_is( 'wc-jquery-tiptip', 'registered' ) ) {
			wp_register_script( 'wc-jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), WC_VERSION, true );
		}
		wp_enqueue_script( 'wc-jquery-tiptip' );

		wp_enqueue_script( 'wc-jquery-blockui' );
		wp_enqueue_script( 'wp-color-picker' );
		
		wp_enqueue_script( 'ajax-queue', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/jquery.ajax.queue.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version );
				
		wp_enqueue_script( 'ast_settings', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/settings.js', array( 'jquery', 'wc-jquery-tiptip' ), wc_advanced_shipment_tracking()->version, true );

		wp_register_script( 'shipment_tracking_table_rows', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/shipping_row.js', array( 'jquery', 'wp-util', 'wc-jquery-tiptip' ), wc_advanced_shipment_tracking()->version, true );
		
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

		// New Settings UI — ZUI component library + plugin chrome.
		// VERSION file lets every consumer plugin (AST/ALP/CBR/CEV/SMS/SRE) cache-bust on
		// the same library bump without touching plugin code.
		$ast_zui_dir  = wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/zui/';
		$ast_zui_path = wc_advanced_shipment_tracking()->get_plugin_path() . '/assets/zui/';
		$ast_zui_ver  = file_exists( $ast_zui_path . 'VERSION' ) ? trim( file_get_contents( $ast_zui_path . 'VERSION' ) ) : wc_advanced_shipment_tracking()->version;
		wp_enqueue_style( 'zui', $ast_zui_dir . 'css/zui.css', array(), $ast_zui_ver );
		wp_enqueue_script( 'zui', $ast_zui_dir . 'js/zui.js', array(), $ast_zui_ver, true );
		wp_enqueue_style( 'ast-settings', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/ast-settings.css', array( 'zui' ), wc_advanced_shipment_tracking()->version );
		wp_enqueue_script( 'ast-settings', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/ast-settings.js', array( 'zui', 'jquery' ), wc_advanced_shipment_tracking()->version, true );
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

		// Preserve legacy data prep — the existing view fragments (carriers, CSV,
		// integrations, trackship) still read $default_shippment_providers, $countries,
		// $WC_Countries via $this scope when they're required from the new shell.
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

		// New Settings UI — single-page shell with sidebar + top tabs. Functionality
		// (forms, save handlers, AJAX endpoints) is identical to the legacy UI; only
		// the chrome (header, sidebar, sections) is new.
		require SHIPMENT_TRACKING_PATH . '/includes/settings/layout-app.php';
		include SHIPMENT_TRACKING_PATH . '/includes/views/admin_upgrade_to_pro_popup.php';
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
			'integrations_tab' => array(
				'title'		=> __( 'Integrations', 'woo-advanced-shipment-tracking' ),
				'show'      => true,
				'data-tab'  => 'integrations',
				'data-label' => __( 'Integrations', 'woo-advanced-shipment-tracking' ),
				// 'class'     => 'tab_label ast_premium_menu',
				'class'     => 'tab_label',
				'badge'     => 'PRO',
				'name'  => 'tabs',
			),
			'tab4' => array(
				'title'		=> __( 'CSV Import', 'woo-advanced-shipment-tracking' ),
				'show'      => true,
				'class'     => 'tab_label',
				'data-tab'  => 'csv-import',
				'data-label' => __( 'CSV Import', 'woo-advanced-shipment-tracking' ),
				'name'  => 'tabs',
			),
			'tab_bulk_paste' => array(
				'title'		=> __( 'Bulk Paste', 'woo-advanced-shipment-tracking' ),
				'show'      => true,
				'class'     => 'tab_label',
				'data-tab'  => 'bulk-paste',
				'data-label' => __( 'Bulk Paste', 'woo-advanced-shipment-tracking' ),
				'badge'     => 'PRO',
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
	/*
	* get UL html of fields
	*/
	public function get_html_ul( $arrays ) {
		// New Settings UI output: ZUI rows inside .zui-card wrappers (driven by
		// settings-body.php / the new shell). Input name+id contracts are preserved
		// so existing save handlers (wc_ast_settings_form_update) keep working
		// untouched. Legacy CSS classes (`.settings_ul`, `.ast-tgl-btn`, …) are
		// retained only where they still drive existing JS behaviour.
		foreach ( (array) $arrays as $id => $array ) {
			if ( empty( $array['show'] ) ) {
				continue;
			}

			$type    = isset( $array['type'] ) ? $array['type'] : 'text';
			$title   = isset( $array['title'] ) ? $array['title'] : '';
			$tooltip = isset( $array['tooltip'] ) ? $array['tooltip'] : '';
			$default = isset( $array['default'] ) ? $array['default'] : '';
			$opt     = isset( $array['option_name'] ) ? $array['option_name'] : 'ast_general_settings';

			if ( 'checkbox' === $type ) {
				$checked = (bool) get_ast_settings( $opt, $id, $default );
				?>
				<div class="zui-row zui-row--inline">
					<div class="zui-row__head">
						<span class="zui-row__label"><?php echo esc_html( $title ); ?></span>
						<?php if ( $tooltip ) : ?><p class="zui-row__desc"><?php echo esc_html( $tooltip ); ?></p><?php endif; ?>
					</div>
					<div class="zui-row__control">
						<label class="zui-checkbox">
							<input type="hidden" name="<?php echo esc_attr( $id ); ?>" value="0">
							<input id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" type="checkbox" class="zui-checkbox__input" value="1" <?php checked( $checked, true ); ?>>
							<span class="zui-checkbox__box"></span>
						</label>
					</div>
				</div>
				<?php

			} elseif ( 'tgl_checkbox' === $type ) {
				// 'wc_ast_enable_log' reads from ast_general_settings (new), all others use get_option (legacy stand-alone keys).
				if ( 'wc_ast_enable_log' === $id ) {
					$checked = (bool) get_ast_settings( $opt, $id, $default );
				} else {
					$checked = (bool) get_option( $id, $default );
				}
				$disabled = ! empty( $array['disabled'] );
				?>
				<div class="zui-row zui-row--inline">
					<div class="zui-row__head">
						<span class="zui-row__label"><?php echo esc_html( $title ); ?></span>
						<?php if ( ! empty( $array['desc'] ) ) : ?>
							<p class="zui-row__desc"><?php echo esc_html( $array['desc'] ); ?></p>
						<?php elseif ( $tooltip ) : ?>
							<p class="zui-row__desc"><?php echo esc_html( $tooltip ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $array['input_desc'] ) ) : ?>
							<p class="zui-row__hint">
								<?php echo esc_html( $array['input_desc'] ); ?>
								<?php if ( ! empty( $array['desc_url'] ) ) : ?>
									<a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( $array['desc_url'] ); ?>"><?php esc_html_e( 'View Logs', 'woo-advanced-shipment-tracking' ); ?></a>
								<?php endif; ?>
							</p>
						<?php endif; ?>
					</div>
					<div class="zui-row__control">
						<label class="zui-toggle">
							<input type="hidden" name="<?php echo esc_attr( $id ); ?>" value="0">
							<input id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" type="checkbox" class="zui-toggle__input ast-settings-toggle" value="1" <?php checked( $checked, true ); ?> <?php disabled( $disabled, true ); ?>>
							<span class="zui-toggle__track"><span class="zui-toggle__thumb"></span></span>
						</label>
						<?php if ( ! empty( $array['customize_link'] ) ) : ?>
							<a href="<?php echo esc_url( $array['customize_link'] ); ?>" class="zui-btn-secondary ts_customizer_btn">
								<?php esc_html_e( 'Customize', 'woo-advanced-shipment-tracking' ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
				<?php

			} elseif ( 'radio' === $type ) {
				$current = get_ast_settings( $opt, $id, $default );
				?>
				<div class="zui-row">
					<div class="zui-row__head">
						<span class="zui-row__label"><?php echo esc_html( $title ); ?></span>
						<?php if ( $tooltip ) : ?><p class="zui-row__desc"><?php echo esc_html( $tooltip ); ?></p><?php endif; ?>
					</div>
					<div class="zui-row__control zui-radio-cards">
						<?php foreach ( (array) $array['options'] as $key => $val ) : ?>
							<label class="zui-radio-card <?php echo esc_attr( $id ); ?><?php echo ( (string) $current === (string) $key ) ? ' is-selected' : ''; ?>" for="<?php echo esc_attr( $id . '_' . $key ); ?>">
								<input type="radio" id="<?php echo esc_attr( $id . '_' . $key ); ?>" name="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $id ); ?> zui-radio-card__input" value="<?php echo esc_attr( $key ); ?>" <?php checked( (string) $current, (string) $key ); ?>>
								<span class="zui-radio-card__body">
									<span class="zui-radio-card__label"><?php echo esc_html( $val ); ?></span>
								</span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
				<?php

			} elseif ( 'multiple_select' === $type ) {
				$stored      = get_ast_settings( $opt, $id, '' );
				$stored      = is_array( $stored ) ? $stored : array();
				$placeholder = __( 'Select one or more…', 'woo-advanced-shipment-tracking' );

				// Pre-compute selected state per option for SSR chips + native select.
				$ms_options = array();
				foreach ( (array) $array['options'] as $key => $val ) {
					$label = is_array( $val ) && isset( $val['status'] ) ? $val['status'] : (string) $val;
					$ms_options[] = array(
						'key'   => (string) $key,
						'label' => (string) $label,
						'sel'   => isset( $stored[ $key ] ) && 1 == $stored[ $key ], // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					);
				}
				?>
				<div class="zui-row">
					<div class="zui-row__head">
						<span class="zui-row__label"><?php echo esc_html( $title ); ?></span>
						<?php if ( $tooltip ) : ?><p class="zui-row__desc"><?php echo esc_html( $tooltip ); ?></p><?php endif; ?>
					</div>
					<div class="zui-row__control">
						<div class="zui-ms" data-zui-multiselect data-placeholder="<?php echo esc_attr( $placeholder ); ?>">
							<select multiple id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>[]" class="zui-ms__native" hidden>
								<?php foreach ( $ms_options as $o ) : ?>
									<option value="<?php echo esc_attr( $o['key'] ); ?>" <?php selected( $o['sel'], true ); ?>><?php echo esc_html( $o['label'] ); ?></option>
								<?php endforeach; ?>
							</select>

							<div class="zui-ms__control" tabindex="0" role="combobox" aria-haspopup="listbox" aria-expanded="false">
								<div class="zui-ms__chips">
									<?php
									$has_selected = false;
									foreach ( $ms_options as $o ) :
										if ( ! $o['sel'] ) {
											continue;
										}
										$has_selected = true;
										?>
										<span class="zui-ms__chip" data-value="<?php echo esc_attr( $o['key'] ); ?>">
											<span class="zui-ms__chip-label"><?php echo esc_html( $o['label'] ); ?></span>
											<button type="button" class="zui-ms__chip-remove" aria-label="<?php echo esc_attr( $o['label'] ); ?>">&times;</button>
										</span>
									<?php endforeach; ?>
									<?php if ( ! $has_selected ) : ?>
										<span class="zui-ms__placeholder"><?php echo esc_html( $placeholder ); ?></span>
									<?php endif; ?>
								</div>
								<span class="zui-ms__chevron">
									<?php ast_free_settings_icon( 'chevron-down' ); ?>
								</span>
							</div>

							<div class="zui-ms__dropdown" role="listbox" hidden></div>
						</div>
					</div>
				</div>
				<?php

			} elseif ( 'multiple_checkbox' === $type ) {
				$stored = get_ast_settings( $opt, $id, '' );
				?>
				<div class="zui-row">
					<div class="zui-row__head">
						<span class="zui-row__label"><?php echo esc_html( $title ); ?></span>
						<?php if ( ! empty( $array['desc'] ) ) : ?>
							<p class="zui-row__desc"><?php echo esc_html( $array['desc'] ); ?></p>
						<?php elseif ( $tooltip ) : ?>
							<p class="zui-row__desc"><?php echo esc_html( $tooltip ); ?></p>
						<?php endif; ?>
					</div>
					<div class="zui-row__control">
						<div class="multiple_checkbox_parent">
							<?php foreach ( (array) $array['options'] as $key => $val ) :
								$checked = isset( $stored[ $key ] ) && 1 == $stored[ $key ]; ?>
								<label class="zui-checkbox multiple_checkbox">
									<input type="hidden" name="<?php echo esc_attr( $id ); ?>[<?php echo esc_attr( $key ); ?>]" value="0">
									<input type="checkbox" name="<?php echo esc_attr( $id ); ?>[<?php echo esc_attr( $key ); ?>]" class="zui-checkbox__input" value="1" <?php checked( $checked, true ); ?>>
									<span class="zui-checkbox__box"></span>
									<span class="multiple_label"><?php echo esc_html( $val['status'] ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
				<?php

			} elseif ( 'button' === $type ) {
				?>
				<div class="zui-row zui-row--inline">
					<div class="zui-row__head">
						<span class="zui-row__label"><?php echo esc_html( $title ); ?></span>
						<?php if ( $tooltip ) : ?><p class="zui-row__desc"><?php echo esc_html( $tooltip ); ?></p><?php endif; ?>
					</div>
					<div class="zui-row__control">
						<?php if ( ! empty( $array['customize_link'] ) ) : ?>
							<a href="<?php echo esc_url( $array['customize_link'] ); ?>" class="zui-btn-secondary ts_customizer_btn"><?php esc_html_e( 'Customize', 'woo-advanced-shipment-tracking' ); ?></a>
						<?php endif; ?>
					</div>
				</div>
				<?php

			} elseif ( 'pro_feature' === $type ) {
				?>
				<div class="zui-row zui-row--inline ast-pro-feature-row">
					<div class="zui-row__head">
						<span class="zui-row__label"><?php echo esc_html( $title ); ?></span>
						<?php if ( $tooltip ) : ?><p class="zui-row__desc"><?php echo esc_html( $tooltip ); ?></p><?php endif; ?>
					</div>
					<div class="zui-row__control">
						<label class="zui-toggle" aria-disabled="true">
							<input type="checkbox" class="zui-toggle__input" disabled>
							<span class="zui-toggle__track"><span class="zui-toggle__thumb"></span></span>
						</label>
						<span class="zui-pro-feature">
							<span class="zui-pro-feature__badge">PRO</span>
							<span class="zui-pro-feature__lock" aria-hidden="true"><?php zui_icon( 'lock' ); ?></span>
						</span>
					</div>
				</div>
				<?php
			}
		}
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
		
		$shippment_provider = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %1s WHERE provider_name = %s', $this->table, $tracking_provider ) );
		
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

		if ( ! function_exists( 'zui_icon' ) ) {
			require_once SHIPMENT_TRACKING_PATH . '/assets/zui/icons.php';
		}

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
			$ast_has_providers = ! empty( $shippment_providers );
			?>
			<div class="ast-set-carriers-grid provider-grid-row grid-row" data-shippment-providers="<?php echo $ast_has_providers ? 'true' : 'false'; ?>">

				<?php /* Enable Carriers tile — PRO-styled, legacy click hooks (.add_custom_provider, .add-provider-container) preserved so shipping_row.js still opens the slideout. */ ?>
				<div class="ast-set-carrier-add-tile grid-item hip-item add-provider-container add_custom_provider">
					<span class="ast-set-carrier-add-tile__plus add-provider-box">
						<span class="dashicons dashicons-plus-alt add_custom_provider"></span>
					</span>
					<span class="add-provider-label add_custom_provider"><?php esc_html_e( 'Enable Carriers', 'woo-advanced-shipment-tracking' ); ?></span>
				</div>

				<?php
				if ( $ast_has_providers ) {
					foreach ( $shippment_providers as $index => $d_s_p ) {
						$provider_type = ( 1 == $d_s_p->shipping_default ) ? 'default_provider' : 'custom_provider';

						// Logo URL (logic preserved). Default carriers have real PNGs; custom
						// carriers fall back to a 2-letter chip rendered from the name so the
						// card never shows a broken-image icon.
						$ast_logo_url   = '';
						$ast_logo_letters = strtoupper( mb_substr( (string) $d_s_p->provider_name, 0, 2 ) );
						if ( 1 == $d_s_p->shipping_default ) {
							$ast_logo_url = $ast_directory . esc_html( $d_s_p->ts_slug ) . '.png?v=' . wc_advanced_shipment_tracking()->version;
						}

						// Name + suffix (custom or api alias count).
						$ast_name_suffix = '';
						if ( isset( $d_s_p->custom_provider_name ) && '' != $d_s_p->custom_provider_name ) {
							$ast_name_suffix .= ' (' . $d_s_p->custom_provider_name . ')';
						}
						$ast_alias_count = 0;
						if ( isset( $d_s_p->api_provider_name ) && '' != $d_s_p->api_provider_name ) {
							if ( $this->isJSON( $d_s_p->api_provider_name ) && class_exists( 'ast_pro' ) ) {
								$ast_alias_count = count( json_decode( $d_s_p->api_provider_name ) );
							} else {
								$ast_alias_count = 1;
							}
						}

						// Country label.
						$search  = array( '(US)', '(UK)' );
						$replace = array( '', '' );
						if ( $d_s_p->shipping_country && 'Global' != $d_s_p->shipping_country ) {
							$ast_country = str_replace( $search, $replace, $WC_Countries->countries[ $d_s_p->shipping_country ] );
						} elseif ( $d_s_p->shipping_country && 'Global' == $d_s_p->shipping_country ) {
							$ast_country = __( 'Global', 'woo-advanced-shipment-tracking' );
						} else {
							$ast_country = '';
						}
						?>
						<div class="zui-card ast-set-carrier-card grid-item" data-pid="<?php echo esc_attr( $d_s_p->id ); ?>" data-type="<?php echo esc_attr( $provider_type ); ?>">

							<?php /* Floating select checkbox — keeps .bulk_select_provider class + name so shipping_row.js change handler still fires. */ ?>
							<label class="ast-set-carrier-check" title="<?php esc_attr_e( 'Select carrier', 'woo-advanced-shipment-tracking' ); ?>">
								<input type="checkbox" name="bulk_select_provider[]" class="bulk_select_provider" value="<?php echo esc_attr( $d_s_p->id ); ?>">
								<span class="ast-set-carrier-check__box"><?php zui_icon( 'check' ); ?></span>
							</label>

							<div class="ast-set-carrier-main grid-top">
								<span class="ast-set-carrier-logo grid-provider-img">
									<?php if ( $ast_logo_url ) : ?>
										<img class="provider-thumb" src="<?php echo esc_url( $ast_logo_url ); ?>" alt="<?php echo esc_attr( $d_s_p->provider_name ); ?>" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex';">
										<span class="ast-set-carrier-logo__fallback" style="display:none;"><?php echo esc_html( $ast_logo_letters ); ?></span>
									<?php else : ?>
										<span class="ast-set-carrier-logo__fallback"><?php echo esc_html( $ast_logo_letters ); ?></span>
									<?php endif; ?>
								</span>
								<span class="ast-set-carrier-info grid-provider-name">
									<span class="ast-set-carrier-name provider_name"><?php echo esc_html( $d_s_p->provider_name . $ast_name_suffix ); ?></span>
									<span class="ast-set-carrier-meta">
										<span class="ast-set-carrier-country provider_country">
											<?php zui_icon( 'globe' ); ?><?php echo esc_html( $ast_country ); ?>
										</span>
										<?php if ( $ast_alias_count > 0 ) : ?>
											<span class="ast-set-carrier-aliases">
												<?php
												/* translators: %d: number of API alias names mapped on this carrier */
												echo esc_html( sprintf( _n( '%d API alias', '%d API aliases', $ast_alias_count, 'woo-advanced-shipment-tracking' ), $ast_alias_count ) );
												?>
											</span>
										<?php endif; ?>
									</span>
								</span>
							</div>

							<?php /* Right action — toggle (off = delete via direct AJAX) + edit pencil.
							     The toggle handler calls the delete endpoint directly; no hidden
							     legacy .remove link needed. */ ?>
							<div class="ast-set-carrier-actions grid-provider-settings">
								<label class="zui-toggle ast-set-carrier-toggle" title="<?php esc_attr_e( 'Toggle carrier (off = delete)', 'woo-advanced-shipment-tracking' ); ?>">
									<input type="checkbox" class="zui-toggle__input ast-carrier-toggle" data-pid="<?php echo esc_attr( $d_s_p->id ); ?>" checked>
									<span class="zui-toggle__track"><span class="zui-toggle__thumb"></span></span>
								</label>
								<a href="javaScript:void(0);" class="ast-set-carrier-edit edit_provider" data-provider="<?php echo esc_attr( $provider_type ); ?>" data-pid="<?php echo esc_attr( $d_s_p->id ); ?>" aria-label="<?php esc_attr_e( 'Edit carrier', 'woo-advanced-shipment-tracking' ); ?>">
									<?php zui_icon( 'edit' ); ?>
								</a>
							</div>
						</div>
						<?php
					}
				}

				if ( ! $ast_has_providers ) {
					echo '<div class="ast-set-carriers-empty">' . esc_html__( 'No matching active carriers. Use "Enable Carriers" to activate some.', 'woo-advanced-shipment-tracking' ) . '</div>';
				}
				?>
			</div>
			<?php
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
				<?php
			}
			?>
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

		if ( ! function_exists( 'zui_icon' ) ) {
			require_once SHIPMENT_TRACKING_PATH . '/assets/zui/icons.php';
		}

		$upload_dir   = wp_upload_dir();
		$ast_directory = $upload_dir['baseurl'] . '/ast-shipping-providers/';
		$items_per_page = 6;
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
							<button class="button add_default_provider" type="button" data-id="<?php echo esc_html( $provider->id ); ?>"><?php esc_html_e( 'Enable', 'woo-advanced-shipment-tracking' ); ?></button>
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
				<input type="hidden" id="nonce_shipping_pagination_provider" value="<?php echo esc_attr( wp_create_nonce( 'nonce_shipping_pagination_provider' ) ); ?>">
				<span class="ast-set-enable-pageinfo">
					<?php esc_html_e( 'Page', 'woo-advanced-shipment-tracking' ); ?> <strong><?php echo esc_html( $page ); ?></strong> <?php esc_html_e( 'of', 'woo-advanced-shipment-tracking' ); ?> <span><?php echo esc_html( $total_pages ); ?></span>
				</span>
				<button data-number="<?php echo esc_html( $page - 1 ); ?>" data-side="left" class="dashicons dashicons-arrow-left-alt arrow_pagination" <?php esc_html_e( $prev_disabled ); ?>></button>
				<button data-number="<?php echo esc_html( $page + 1 ); ?>" data-side="right" class="dashicons dashicons-arrow-right-alt arrow_pagination" <?php esc_html_e( $next_disabled ); ?>></button>
			</div>
			<?php } ?>
		</div>
		<?php
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
		
		$tracking_provider = $wpdb->get_var( $wpdb->prepare( 'SELECT ts_slug FROM %1s WHERE provider_name = %s', $this->table, $tracking_provider_name ) );				
		
		if ( !$tracking_provider ) {
			$tracking_provider =  $tracking_provider_name ;
		}
		
		return $tracking_provider;
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
