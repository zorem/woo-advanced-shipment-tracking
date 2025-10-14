<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Advanced_Shipment_Tracking_Install {

	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	 */
	private static $instance;
	public $table;
	
	/**
	 * Initialize the main plugin function
	*/
	public function __construct() {
		
		global $wpdb;
		$this->table = $wpdb->prefix . 'woo_shippment_provider';
		
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
		
		$this->init();	
	}
	
	/**
	 * Get the class instance
	 *
	 * @return WC_Advanced_Shipment_Tracking_Install
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
		add_action( 'init', array( $this, 'update_database_check' ) );	
		add_action( 'ast_insert_shipping_provider', array( $this, 'ast_insert_shipping_provider' ), 10, 1 );
	}	

	/**
	 * Define plugin activation function
	 *
	 * Create Table
	 *
	 * Insert data 
	 *
	 * 
	*/	
	public function woo_shippment_tracking_install() {
		
		// Add transient to trigger redirect.
		set_transient( '_ast_activation_redirect', 1, 30 );		
		
		$this->ast_insert_shipping_providers();		
		
		$wc_ast_unclude_tracking_info = get_option( 'wc_ast_unclude_tracking_info' );
		if ( empty( $wc_ast_unclude_tracking_info ) ) {	
			$data_array = array( 'completed' => 1, 'partial-shipped' => 1, 'updated-tracking' => 1 );
			update_option( 'wc_ast_unclude_tracking_info', $data_array );	
		}

		$wc_ast_show_orders_actions = get_option( 'wc_ast_show_orders_actions' );
		if ( empty( $wc_ast_show_orders_actions ) ) {	
			$data_array = array( 'processing' => 1, 'completed' => 1, 'partial-shipped' => 1, 'updated-tracking' => 1 );
			update_option( 'wc_ast_show_orders_actions', $data_array );	
		}		

		update_option( 'wc_advanced_shipment_tracking', '3.21' );
	}
	
	/*
	* function for create shipping provider table
	*/
	public function create_shippment_tracking_table() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();			
		$sql = "CREATE TABLE $this->table (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			provider_name varchar(500) DEFAULT '' NOT NULL,
			api_provider_name text NULL DEFAULT NULL,
			custom_provider_name text NULL DEFAULT NULL,
			paypal_slug text NULL DEFAULT NULL,
			ts_slug text NULL DEFAULT NULL,
			provider_url varchar(500) DEFAULT '' NULL,
			shipping_country varchar(45) DEFAULT '' NULL,
			shipping_country_name varchar(45) DEFAULT '' NULL,
			shipping_default tinyint(4) NULL DEFAULT '0',
			custom_thumb_id int(11) NOT NULL DEFAULT '0',
			display_in_order tinyint(4) NOT NULL DEFAULT '1',
			trackship_supported int(11) NOT NULL DEFAULT '0',
			sort_order int(11) NOT NULL DEFAULT '0',				
			PRIMARY KEY  (id)
		) $charset_collate;";			
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );		
	}
	
	public function ast_insert_shipping_providers() {
		global $wpdb;		
		if ( !$wpdb->query( $wpdb->prepare( 'show tables like %s', $this->table ) ) ) {			
			$this->create_shippment_tracking_table();	
			$this->update_shipping_providers();	
		} else {
			$this->check_all_column_exist();
		}
	}
	
	/*
	* check if all column exist in shipping provider database
	*/
	public function check_all_column_exist() {
		
		$db_update_need = false;
		global $wpdb;
		
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = 'provider_name' ", $this->table ), ARRAY_A );
		if ( ! $row ) {
			$wpdb->query( $wpdb->prepare( "ALTER TABLE %1s ADD provider_name varchar(500) DEFAULT '' NOT NULL AFTER id", $this->table ) );
			$db_update_need = true;
		}

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = 'api_provider_name' ", $this->table ), ARRAY_A );
		if ( ! $row ) {
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s ADD api_provider_name text NULL DEFAULT NULL AFTER provider_name', $this->table ) );
			$db_update_need = true;
		}

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = 'custom_provider_name' ", $this->table ), ARRAY_A );
		if ( ! $row ) {
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s ADD custom_provider_name text NULL DEFAULT NULL AFTER api_provider_name', $this->table ) );
			$db_update_need = true;
		}

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = 'paypal_slug' ", $this->table ), ARRAY_A );
		if ( ! $row ) {
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s ADD paypal_slug text NULL DEFAULT NULL AFTER custom_provider_name', $this->table ) );
			$db_update_need = true;
		}

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = 'ts_slug' ", $this->table ), ARRAY_A );
		if ( ! $row ) {
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s ADD ts_slug text NULL DEFAULT NULL AFTER paypal_slug', $this->table ) );
			$db_update_need = true;
		}

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = 'provider_url' ", $this->table ), ARRAY_A );
		if ( ! $row ) {
			$wpdb->query( $wpdb->prepare( "ALTER TABLE %1s ADD provider_url varchar(500) DEFAULT '' NULL AFTER ts_slug", $this->table ) );
			$db_update_need = true;
		}

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = 'shipping_country' ", $this->table ), ARRAY_A );
		if ( ! $row ) {			
			$wpdb->query( $wpdb->prepare( "ALTER TABLE %1s ADD shipping_country varchar(45) DEFAULT '' NULL AFTER provider_url", $this->table ) );
			$db_update_need = true;
		}

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = 'shipping_country_name' ", $this->table ), ARRAY_A );
		if ( ! $row ) {			
			$wpdb->query( $wpdb->prepare( "ALTER TABLE %1s ADD shipping_country_name varchar(45) DEFAULT '' NULL AFTER shipping_country", $this->table ) );
			$db_update_need = true;
		}

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = 'shipping_default' ", $this->table ), ARRAY_A );
		if ( ! $row ) {
			$wpdb->query( $wpdb->prepare( "ALTER TABLE %1s ADD shipping_default tinyint(4) NOT NULL DEFAULT '0' AFTER shipping_country_name", $this->table ) );
			$db_update_need = true;
		}

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = 'custom_thumb_id' ", $this->table ), ARRAY_A );
		if ( ! $row ) {			
			$wpdb->query( $wpdb->prepare( "ALTER TABLE %1s ADD custom_thumb_id int(11) NOT NULL DEFAULT '0' AFTER shipping_default", $this->table ) );
			$db_update_need = true;
		}
		
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = 'display_in_order' ", $this->table ), ARRAY_A );
		if ( ! $row ) {
			$wpdb->query( $wpdb->prepare( "ALTER TABLE %1s ADD display_in_order tinyint(4) NOT NULL DEFAULT '1' AFTER custom_thumb_id", $this->table ) );
			$db_update_need = true;
		}
		
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = 'trackship_supported' ", $this->table ), ARRAY_A );
		if ( ! $row ) {
			$wpdb->query( $wpdb->prepare( "ALTER TABLE %1s ADD trackship_supported int(11) NOT NULL DEFAULT '0' AFTER display_in_order", $this->table ) );
			$db_update_need = true;
		}

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = 'sort_order' ", $this->table ), ARRAY_A );		
		if ( ! $row ) {
			$wpdb->query( $wpdb->prepare( "ALTER TABLE %1s ADD sort_order int(11) NOT NULL DEFAULT '0' AFTER trackship_supported", $this->table ) );
			$db_update_need = true;
		}

		if ( $db_update_need ) {
			$this->update_shipping_providers();
		}	
	}
	
	/*
	* database update
	*/
	public function update_database_check() {					
		if ( is_admin() ) {																
			
			if ( version_compare( get_option( 'wc_advanced_shipment_tracking' ), '3.21', '<') ) {	
				$this->check_all_column_exist();
				update_option( 'wc_advanced_shipment_tracking', '3.21');				
			}
			if ( version_compare( get_option( 'wc_advanced_shipment_tracking' ), '3.22', '<' ) ) {
				
				$upload_dir   = wp_upload_dir();	
				$ast_directory = $upload_dir['baseurl'] . '/ast-shipping-providers/';
			
				$tracking_items[]  = array(
					'tracking_provider'       		=> 'usps',
					'custom_tracking_provider'		=> '',				
					'formatted_tracking_provider'	=> 'USPS',
					'tracking_provider_image' 		=> $ast_directory . 'usps.png',
					'formatted_tracking_link'		=> 'https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1=112123113',
					'ast_tracking_link'				=> 'https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1=112123113',
					'tracking_number'          		=> '112123113',				
					'date_shipped'             		=> strtotime( gmdate( 'Y-m-d' ) ),
				);
				
				update_post_meta( 1, '_wc_shipment_tracking_items', $tracking_items );
				update_option( 'wc_advanced_shipment_tracking', '3.22');		
			}
			if ( version_compare( get_option( 'wc_advanced_shipment_tracking', '1.0' ), '3.23', '<' ) ) {
				$multi_checkbox_data = get_option( 'wc_ast_unclude_tracking_info' );
				$data_array = array( 'partial-shipped' => 1, 'completed' => 1 );
				if ( $multi_checkbox_data ) {	
					$data_array = array_merge( $multi_checkbox_data, $data_array );
				}		
				update_option( 'wc_ast_unclude_tracking_info', $data_array );
				update_option( 'wc_advanced_shipment_tracking', '3.23' );
			}

			if ( version_compare( get_option( 'wc_advanced_shipment_tracking', '1.0' ), '3.24', '<' ) ) {
				$tracking_info_settings = get_option( 'tracking_info_settings', array() );
				$fluid_tracker_type = !empty($tracking_info_settings['fluid_tracker_type']) ? $tracking_info_settings['fluid_tracker_type'] : 'progress_bar';
				
				if ( 'hide' == $fluid_tracker_type ) {
					$tracking_info_settings['fluid_display_shipped_header'] = 0;
					$tracking_info_settings['fluid_tracker_type'] = 'progress_bar';					
					update_option( 'tracking_info_settings', $tracking_info_settings );					
				}				
				update_option( 'wc_advanced_shipment_tracking', '3.24' );
			}
			
			if ( version_compare( get_option( 'wc_advanced_shipment_tracking', '1.0' ), '4.0', '<' ) ) {				
				$this->ast_insert_shipping_providers();				
				update_option( 'wc_advanced_shipment_tracking', '4.0' );
			}

			if ( version_compare( get_option( 'wc_advanced_shipment_tracking', '1.0' ), '4.1', '<' ) ) {
				$this->insert_shipping_carrier_image();
				update_option( 'wc_advanced_shipment_tracking', '4.1' );
			}

			if ( version_compare( get_option( 'wc_advanced_shipment_tracking', '1.0' ), '4.2', '<' ) ) {
				if ( get_option('ast_option_migrated') == false ) {

					//get old general options 
					$wc_ast_show_orders_actions = get_option( 'wc_ast_show_orders_actions' );
					$wc_ast_unclude_tracking_info = get_option( 'wc_ast_unclude_tracking_info' );
					$wc_ast_status_shipped = get_option( 'wc_ast_status_shipped' );
					$wc_ast_status_partial_shipped = get_option( 'wc_ast_status_partial_shipped' );
					$wc_ast_status_partial_shipped_label_color = get_option( 'wc_ast_status_partial_shipped_label_color' );
					$wc_ast_status_partial_shipped_label_font_color = get_option( 'wc_ast_status_partial_shipped_label_font_color' );
					$wc_ast_status_delivered = get_option( 'wc_ast_status_delivered' );
					$wc_ast_api_date_format = get_option( 'wc_ast_api_date_format' );
					$wcast_enable_partial_shipped_email = get_option( 'wcast_enable_partial_shipped_email' );
					$wc_ast_status_label_color = get_option( 'wc_ast_status_label_color' );
					$wc_ast_status_label_font_color = get_option( 'wc_ast_status_label_font_color' );
					$autocomplete_order_tpi = get_option( 'autocomplete_order_tpi' );

					//update new general options
					update_ast_settings( 'ast_general_settings', 'wc_ast_show_orders_actions', $wc_ast_show_orders_actions );
					update_ast_settings( 'ast_general_settings', 'wc_ast_unclude_tracking_info', $wc_ast_unclude_tracking_info );
					update_ast_settings( 'ast_general_settings', 'wc_ast_status_shipped', $wc_ast_status_shipped );
					update_ast_settings( 'ast_general_settings', 'wc_ast_status_partial_shipped', $wc_ast_status_partial_shipped );
					update_ast_settings( 'ast_general_settings', 'wc_ast_status_partial_shipped_label_color', $wc_ast_status_partial_shipped_label_color );
					update_ast_settings( 'ast_general_settings', 'wc_ast_status_partial_shipped_label_font_color', $wc_ast_status_partial_shipped_label_font_color );
					update_ast_settings( 'ast_general_settings', 'wc_ast_status_delivered', $wc_ast_status_delivered );
					update_ast_settings( 'ast_general_settings', 'wc_ast_api_date_format', $wc_ast_api_date_format );
					update_ast_settings( 'ast_general_settings', 'wcast_enable_partial_shipped_email', $wcast_enable_partial_shipped_email );
					update_ast_settings( 'ast_general_settings', 'wc_ast_status_label_color', $wc_ast_status_label_color );
					update_ast_settings( 'ast_general_settings', 'wc_ast_status_label_font_color', $wc_ast_status_label_font_color );
					update_ast_settings( 'ast_general_settings', 'autocomplete_order_tpi', $autocomplete_order_tpi );

					//delete old general options
					delete_option( 'wc_ast_show_orders_actions' );
					delete_option( 'wc_ast_unclude_tracking_info' );
					delete_option( 'wc_ast_status_shipped' );
					delete_option( 'wc_ast_status_partial_shipped' );
					delete_option( 'wc_ast_status_partial_shipped_label_color' );
					delete_option( 'wc_ast_status_partial_shipped_label_font_color' );
					delete_option( 'wc_ast_status_delivered' );
					delete_option( 'wc_ast_api_date_format' );
					delete_option( 'wcast_enable_partial_shipped_email' );
					delete_option( 'wc_ast_status_label_color' );
					delete_option( 'wc_ast_status_label_font_color' );
					delete_option( 'autocomplete_order_tpi' );

					update_option('ast_option_migrated', true);
					update_option( 'wc_advanced_shipment_tracking', '4.2' );
				} else {
					update_option( 'wc_advanced_shipment_tracking', '4.2' );
				}
			}

			if ( version_compare( get_option( 'wc_advanced_shipment_tracking', '4.2' ), '4.3', '<' ) ) {

				//get old general options 
				$wc_ast_status_updated_tracking = get_option( 'wc_ast_status_updated_tracking', 0 );
				$wc_ast_status_updated_tracking_label_color = get_option( 'wc_ast_status_updated_tracking_label_color', '#23a2dd' );
				$wc_ast_status_updated_tracking_label_font_color = get_option( 'wc_ast_status_updated_tracking_label_font_color', '#fff' );
				$wcast_enable_updated_tracking_email = get_option( 'wcast_enable_updated_tracking_email', 0 );
				
				//update new general options
				update_ast_settings( 'ast_general_settings', 'wc_ast_status_updated_tracking', $wc_ast_status_updated_tracking );
				update_ast_settings( 'ast_general_settings', 'wc_ast_status_updated_tracking_label_color', $wc_ast_status_updated_tracking_label_color );
				update_ast_settings( 'ast_general_settings', 'wc_ast_status_updated_tracking_label_font_color', $wc_ast_status_updated_tracking_label_font_color );
				update_ast_settings( 'ast_general_settings', 'wcast_enable_updated_tracking_email', $wcast_enable_updated_tracking_email );

				//delete old general options
				delete_option( 'wc_ast_status_updated_tracking' );
				delete_option( 'wc_ast_status_updated_tracking_label_color' );
				delete_option( 'wc_ast_status_updated_tracking_label_font_color' );
				delete_option( 'wcast_enable_updated_tracking_email' );

				update_option( 'wc_advanced_shipment_tracking', '4.3' );
			}

			if ( version_compare( get_option( 'wc_advanced_shipment_tracking', '4.3' ), '4.4', '<' ) ) {
				delete_option( 'ast_trackship_notice_ignore' );
				delete_option( 'ast_pro_shipping_integration_notice_ignore' );

				update_option( 'wc_advanced_shipment_tracking', '4.4' );
			}

			if ( version_compare( get_option( 'wc_advanced_shipment_tracking', '4.4' ), '4.5', '<' ) ) {
				delete_option( 'zorem_return_update_ignore_385' );
				update_option( 'wc_advanced_shipment_tracking', '4.5' );
			}

		}
	}		
	
	/**
	 * Get providers list from trackship and update providers in database
	*/
	public function update_shipping_providers() {
		as_schedule_single_action( time(), 'ast_insert_shipping_provider', array() );			
	}
	
	/**
	 * Get providers list from trackship and update providers in database
	*/
	public function ast_insert_shipping_provider() {
		global $wpdb;		
		$url = 'https://api.trackship.com/v1/shipping_carriers/all';		
		$resp = wp_remote_get( $url );
		
		$WC_Countries = new WC_Countries();
		$countries = $WC_Countries->get_countries();

		$this->insert_shipping_carrier_image();
		
		if ( is_array( $resp ) && ! is_wp_error( $resp ) ) {
			
			$response = json_decode( $resp['body'], true );
			$providers = $response['data'];

			$default_shippment_providers = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s WHERE shipping_default = 1', $this->table ) );
			foreach ( $default_shippment_providers as $key => $val ) {
				$shippment_providers[ $val->ts_slug ] = $val;
			}

			$providers_name = array();
			foreach ( $providers as $key => $val ) {
				$providers_name[ $val['slug'] ] = $val;
			}

			$n = 0;
			foreach ( $providers as $provider ) {

				$provider_name = $provider['shipping_provider'];
				$provider_url = $provider['tracking_url'];
				$shipping_country = $provider['shipping_country'];
				
				if ( 'Global' == $provider['shipping_country'] ) {
					$shipping_country_name = $provider['shipping_country'];
				} else {
					$shipping_country_name = $countries[ $provider['shipping_country'] ];
				}
				
				$ts_slug = $provider['slug'];
				$trackship_supported = $provider['trackship_supported'];
				$paypal_slug = $provider['paypal_slug'];
				

				if ( isset( $shippment_providers[ $ts_slug ] ) ) {

					$db_provider_name = $shippment_providers[ $ts_slug ]->provider_name;
					$db_provider_url = $shippment_providers[$ts_slug]->provider_url;
					$db_shipping_country = $shippment_providers[$ts_slug]->shipping_country;
					$db_shipping_country_name = $shippment_providers[$ts_slug]->shipping_country_name;
					$db_ts_slug = $shippment_providers[$ts_slug]->ts_slug;
					$db_trackship_supported = $shippment_providers[$ts_slug]->trackship_supported;
					$db_paypal_slug = $shippment_providers[$ts_slug]->paypal_slug;
					
					if ( ( $db_provider_name != $provider_name ) || ( $db_provider_url != $provider_url ) || ( $db_shipping_country != $shipping_country ) || ( $db_shipping_country_name != $shipping_country_name ) || ( $db_ts_slug != $ts_slug ) || ( $db_trackship_supported != 	$trackship_supported ) || ( $db_paypal_slug != 	$paypal_slug ) ) {
						
						if ( 'Global' == $shipping_country ) {
							$shipping_country_name = $shipping_country;
						} else {
							$shipping_country_name = $countries[ $shipping_country ];
						}
						
						$data_array = array(
							'provider_name' => $provider_name,
							'ts_slug' => $ts_slug,
							'provider_url' => $provider_url,
							'shipping_country' => $shipping_country,
							'shipping_country_name' => $shipping_country_name,
							'trackship_supported' => $trackship_supported,
							'paypal_slug' => $paypal_slug,
						);
						$where_array = array(
							'ts_slug' => $ts_slug,
						);
						$wpdb->update( $this->table, $data_array, $where_array);
					}
				} else {
					
					$display_in_order = 0; 
					
					if ( 'Global' == $shipping_country ) {
						$shipping_country_name = $shipping_country;
					} else {
						$shipping_country_name = $countries[ $shipping_country ];
					}

					$data_array = array(
						'shipping_country' => sanitize_text_field($shipping_country),
						'shipping_country_name' => $shipping_country_name,
						'provider_name' => sanitize_text_field($provider_name),
						'ts_slug' => $ts_slug,
						'provider_url' => sanitize_text_field($provider_url),			
						'display_in_order' => $display_in_order,
						'shipping_default' => 1,
						'trackship_supported' => sanitize_text_field( $trackship_supported ),
						'paypal_slug' => sanitize_text_field( $paypal_slug ),
					);
					$wpdb->insert( $this->table, $data_array );
					$n++;
				}
			}

			foreach ( $default_shippment_providers as $db_provider ) {

				if ( !isset( $providers_name[ $db_provider->ts_slug ] ) ) {
					$where = array(
						'ts_slug' => $db_provider->ts_slug,
						'shipping_default' => 1
					);
					$wpdb->delete( $this->table, $where );
				}
			}
		}
	}

	public function insert_shipping_carrier_image() {
		
		// The URL of the zip file
		$url = 'https://api.trackship.com/images/shipping-carriers/60x60.zip';

		$version = date('YmdHis'); // Current date and time as version
		$url_with_version = $url . '?v=' . $version;

		$upload_dir   = wp_upload_dir();	
		$ast_directory = $upload_dir['basedir'] . '/ast-shipping-providers';
		$zipFilePath = $upload_dir['basedir'] . '/shipping-carriers.zip';	
		
		if ( !is_dir( $ast_directory ) ) {
			wp_mkdir_p( $ast_directory );	
		}
		// Download the zip file
		$zipContent = file_get_contents($url_with_version);
		// Save the zip file to the server
		file_put_contents($zipFilePath, $zipContent);
		if (class_exists('ZipArchive')) {
			// Initialize ZipArchive
			$zip = new ZipArchive();
			// Open and extract the zip file
			if ( $zip->open( $zipFilePath ) === true ) {
				// Extract to the specified directory
				$zip->extractTo($ast_directory);
				$zip->close();
				unlink($zipFilePath); // Delete the zip file after extraction				
			}
		} else {
			// ZipArchive isn't available, use PclZip
			require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
			$archive = new PclZip($zipFilePath);
			if ($archive->extract(PCLZIP_OPT_PATH, $ast_directory) != 0) {
				unlink($zipFilePath); // Delete the zip file after extraction
			}						
		}
	}

}
