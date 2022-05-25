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
		add_action( 'update_ts_shipment_status_order_mete', array( $this, 'update_ts_shipment_status_order_mete' ) );
		add_action( 'wp_ajax_update_ts_shipment_status_order_mete', array( $this, 'update_ts_shipment_status_order_mete' ) );
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
		
		$this->create_shippment_tracking_table();
		$this->update_shipping_providers();					
		
		$wc_ast_default_mark_shipped = get_option( 'wc_ast_default_mark_shipped' );
		if ( '' == $wc_ast_default_mark_shipped ) {
			update_option( 'wc_ast_default_mark_shipped', 1 );
		}
		
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
		
		if ( !$wpdb->query( $wpdb->prepare( 'show tables like %s', $this->table ) ) ) {
			$charset_collate = $wpdb->get_charset_collate();			
			$sql = "CREATE TABLE $this->table (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				provider_name varchar(500) DEFAULT '' NOT NULL,
				api_provider_name text NULL DEFAULT NULL,
				custom_provider_name text NULL DEFAULT NULL,
				ts_slug text NULL DEFAULT NULL,
				provider_url varchar(500) DEFAULT '' NULL,
				shipping_country varchar(45) DEFAULT '' NULL,
				shipping_default tinyint(4) NULL DEFAULT '0',
				custom_thumb_id int(11) NOT NULL DEFAULT '0',
				display_in_order tinyint(4) NOT NULL DEFAULT '1',
				trackship_supported int(11) NOT NULL DEFAULT '0',
				sort_order int(11) NOT NULL DEFAULT '0',				
				PRIMARY KEY  (id)
			) $charset_collate;";			
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
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
		$row = $wpdb->get_row( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$this->table}' AND COLUMN_NAME = 'provider_name' ", ARRAY_A );
		if ( ! $row ) {
			$wpdb->query( "ALTER TABLE $this->table ADD provider_name varchar(500) DEFAULT '' NOT NULL AFTER id" );
			$db_update_need = true;
		}

		$row = $wpdb->get_row( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$this->table}' AND COLUMN_NAME = 'api_provider_name' ", ARRAY_A );
		if ( ! $row ) {
			$wpdb->query( "ALTER TABLE $this->table ADD api_provider_name text NULL DEFAULT NULL AFTER provider_name" );
			$db_update_need = true;
		}

		$row = $wpdb->get_row( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$this->table}' AND COLUMN_NAME = 'custom_provider_name' ", ARRAY_A );
		if ( ! $row ) {
			$wpdb->query( "ALTER TABLE $this->table ADD custom_provider_name text NULL DEFAULT NULL AFTER api_provider_name" );
			$db_update_need = true;
		}

		$row = $wpdb->get_row( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$this->table}' AND COLUMN_NAME = 'ts_slug' ", ARRAY_A );
		if ( ! $row ) {
			$wpdb->query( "ALTER TABLE $this->table ADD ts_slug text NULL DEFAULT NULL AFTER custom_provider_name" );
			$db_update_need = true;
		}

		$row = $wpdb->get_row( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$this->table}' AND COLUMN_NAME = 'provider_url' ", ARRAY_A );
		if ( ! $row ) {
			$wpdb->query( "ALTER TABLE $this->table ADD provider_url varchar(500) DEFAULT '' NULL AFTER ts_slug" );
			$db_update_need = true;
		}

		$row = $wpdb->get_row( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$this->table}' AND COLUMN_NAME = 'shipping_country' ", ARRAY_A );
		if ( ! $row ) {
			$wpdb->query( "ALTER TABLE $this->table ADD shipping_country varchar(45) DEFAULT '' NULL AFTER provider_url" );
			$db_update_need = true;
		}

		$row = $wpdb->get_row( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$this->table}' AND COLUMN_NAME = 'shipping_default' ", ARRAY_A );
		if ( ! $row ) {
			$wpdb->query( "ALTER TABLE $this->table ADD shipping_default tinyint(4) NULL DEFAULT '0' AFTER shipping_country" );
			$db_update_need = true;
		}

		$row = $wpdb->get_row( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$this->table}' AND COLUMN_NAME = 'custom_thumb_id' ", ARRAY_A );
		if ( ! $row ) {
			$wpdb->query( "ALTER TABLE $this->table ADD custom_thumb_id int(11) NOT NULL DEFAULT '0' AFTER shipping_default" );
			$db_update_need = true;
		}

		$row = $wpdb->get_row( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$this->table}' AND COLUMN_NAME = 'display_in_order' ", ARRAY_A );
		if ( ! $row ) {
			$wpdb->query( "ALTER TABLE $this->table ADD display_in_order tinyint(4) NOT NULL DEFAULT '1' AFTER custom_thumb_id" );
			$db_update_need = true;
		}

		$row = $wpdb->get_row( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$this->table}' AND COLUMN_NAME = 'trackship_supported' ", ARRAY_A );
		if ( ! $row ) {
			$wpdb->query( "ALTER TABLE $this->table ADD trackship_supported int(11) NOT NULL DEFAULT '0' AFTER display_in_order" );
			$db_update_need = true;
		}

		$row = $wpdb->get_row( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$this->table}' AND COLUMN_NAME = 'sort_order' ", ARRAY_A );
		if ( ! $row ) {
			$wpdb->query( "ALTER TABLE $this->table ADD sort_order int(11) NOT NULL DEFAULT '0' AFTER trackship_supported" );
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
			
			if ( version_compare( get_option( 'wc_advanced_shipment_tracking' ), '3.14', '<' ) ) {				
				$this->add_provider_image_in_upload_directory();							
				update_option( 'wc_advanced_shipment_tracking', '3.14');		
			}
			
			if ( version_compare( get_option( 'wc_advanced_shipment_tracking' ), '3.20', '<' ) ) {				
				as_schedule_single_action( time(), 'update_ts_shipment_status_order_mete' , array( 'order_page' => 1 ), '' );
				as_schedule_single_action( time(), 'update_ts_shipment_status_order_mete' , array( 'order_page' => 2 ), '' );
				as_schedule_single_action( time(), 'update_ts_shipment_status_order_mete' , array( 'order_page' => 3 ), '' );
				as_schedule_single_action( time(), 'update_ts_shipment_status_order_mete' , array( 'order_page' => 4 ), '' );
				as_schedule_single_action( time(), 'update_ts_shipment_status_order_mete' , array( 'order_page' => 5 ), '' );
				as_schedule_single_action( time(), 'update_ts_shipment_status_order_mete' , array( 'order_page' => 6 ), '' );
				as_schedule_single_action( time(), 'update_ts_shipment_status_order_mete' , array( 'order_page' => 7 ), '' );
				as_schedule_single_action( time(), 'update_ts_shipment_status_order_mete' , array( 'order_page' => 8 ), '' );
				as_schedule_single_action( time(), 'update_ts_shipment_status_order_mete' , array( 'order_page' => 9 ), '' );
				as_schedule_single_action( time(), 'update_ts_shipment_status_order_mete' , array( 'order_page' => 10 ), '' );
				update_option( 'wc_advanced_shipment_tracking', '3.20');				
			}	
			
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
					$data_array = array_merge( $multi_checkbox_data,$data_array );
				}		
				update_option( 'wc_ast_unclude_tracking_info', $data_array );
				update_option( 'wc_advanced_shipment_tracking', '3.23' );
			}
		}
	}
	
	/*
	* function for update order meta from shipment_status to ts_shipment_status for filter order by shipment status
	*/
	public function update_ts_shipment_status_order_mete( $page ) {
		
		$wc_ast_api_key = get_option( 'wc_ast_api_key' ); 
		if ( !$wc_ast_api_key ) {
			return;
		}	
		
		$args = array(			
			'limit' => 100,
			'paged' => $page,
			'return' => 'ids',
		);
		
		$orders = wc_get_orders( $args );
		
		foreach ( $orders as $order_id ) {
			$shipment_status = get_post_meta( $order_id, 'shipment_status', true );
			if ( !empty( $shipment_status ) ) {
				foreach ( $shipment_status as $key => $shipment ) {
					$ts_shipment_status[ $key ][ 'status' ] = $shipment[ 'status' ];			
					update_post_meta( $order_id, 'ts_shipment_status', $ts_shipment_status );						
				}
			}			
		}		
	}
	
	/**
	 * Function for add provider image in uploads directory under wp-content/uploads/ast-shipping-providers
	*/
	public function add_provider_image_in_upload_directory() {		
		$upload_dir   = wp_upload_dir();	
		$ast_directory = $upload_dir['basedir'] . '/ast-shipping-providers';
		
		if ( !is_dir( $ast_directory ) ) {
			wp_mkdir_p( $ast_directory );	
		}
				
		$url = 'https://trackship.info/wp-json/WCAST/v1/Provider';		
		$resp = wp_remote_get( $url );							
		
		if ( is_array( $resp ) && ! is_wp_error( $resp ) ) {
			$providers = json_decode( $resp['body'], true );
			foreach ( $providers as $provider ) {
				$provider_name = $provider['shipping_provider'];
				$img_url = $provider['img_url'];
				$img_slug = sanitize_title($provider_name);
				$img = $ast_directory . '/' . $img_slug . '.png';
				$ch = curl_init(); 
		
				curl_setopt($ch, CURLOPT_HEADER, 0); 
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
				curl_setopt($ch, CURLOPT_URL, $img_url); 
				
				$data = curl_exec($ch); 
				curl_close($ch); 							
				file_put_contents($img, $data);
			}
		}	
	}
	
	/**
	 * Get providers list from trackship and update providers in database
	*/
	public function update_shipping_providers() {
		global $wpdb;

		//create log
		$logger = wc_get_logger();
		$time = time();
		$logger->info( $time . ': shipping provider update started', array( 'source' => 'ast' ) );

		// shipping provider image path
		$upload_dir   = wp_upload_dir();
		$ast_directory = $upload_dir['basedir'] . '/ast-shipping-providers';
		if ( !is_dir( $ast_directory ) ) {
			wp_mkdir_p( $ast_directory );
		}

		//get shipping provider json and convert to array
		$body = file_get_contents( wc_advanced_shipment_tracking()->get_plugin_path() . '/assets/providers.json');
		$providers = json_decode( $body, true );
		
		$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table WHERE shipping_default = 1" );
		foreach ( $default_shippment_providers as $key => $val ) {
			$shippment_providers[ $val->ts_slug ] = $val;
		}

		$providers_name = array();
		foreach ( $providers as $key => $val ) {
			$providers_name[ $val['shipping_provider_slug'] ] = $val;
		}
		
		$n = 0;
		foreach ( $providers as $provider ) {
			
			$provider_name = $provider['shipping_provider'];
			$provider_url = $provider['provider_url'];
			$shipping_country = $provider['shipping_country'];
			$ts_slug = $provider['shipping_provider_slug'];
			$trackship_supported = $provider['trackship_supported'];
			
			if ( isset( $shippment_providers[ $ts_slug ] ) ) {
				
				$db_provider_name = $shippment_providers[ $ts_slug ]->provider_name;
				$db_provider_url = $shippment_providers[$ts_slug]->provider_url;
				$db_shipping_country = $shippment_providers[$ts_slug]->shipping_country;
				$db_ts_slug = $shippment_providers[$ts_slug]->ts_slug;
				$db_trackship_supported = $shippment_providers[$ts_slug]->trackship_supported;
				
				if ( ( $db_provider_name != $provider_name ) || ( $db_provider_url != $provider_url ) || ( $db_shipping_country != $shipping_country ) || ( $db_ts_slug != $ts_slug ) || ( $db_trackship_supported != $trackship_supported ) ) {
					$data_array = array(
						'provider_name' => $provider_name,
						'ts_slug' => $ts_slug,
						'provider_url' => $provider_url,
						'shipping_country' => $shipping_country,
						'trackship_supported' => $trackship_supported,
					);
					$where_array = array(
						'ts_slug' => $ts_slug,
					);
					$wpdb->update( $this->table, $data_array, $where_array);
				}
			} else {
				$display_in_order = 1; 	
				if ( $n > 14 ) {
					$display_in_order = 0; 	
				}	
				
				$data_array = array(
					'shipping_country' => sanitize_text_field($shipping_country),
					'provider_name' => sanitize_text_field($provider_name),
					'ts_slug' => $ts_slug,
					'provider_url' => sanitize_text_field($provider_url),			
					'display_in_order' => $display_in_order,
					'shipping_default' => 1,
					'trackship_supported' => $provider['trackship_supported'],
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

		$this->extract_zip_file();

		$logger->info( $time . ': shipping provider update finished', array( 'source' => 'ast' ) );
	}	
	
	public function extract_zip_file() {
		if ( class_exists( 'ZipArchive' ) ) {
			$upload_dir   = wp_upload_dir();	
			$ast_directory = $upload_dir['basedir'] . '/ast-shipping-providers';
			
			if ( !is_dir( $ast_directory ) ) {
				wp_mkdir_p( $ast_directory );	
			}

			$zip = new ZipArchive;
			wc_advanced_shipment_tracking()->get_plugin_path() . '/assets/providers.zip';
			$res = $zip->open( wc_advanced_shipment_tracking()->get_plugin_path() . '/assets/providers.zip' );
			if ($res === TRUE) {
				$zip->extractTo($ast_directory);
				$zip->close();
			}
		}
	}
}
