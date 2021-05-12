<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Advanced_Shipment_Tracking_Settings {		
	
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
	 * @return WC_Advanced_Shipment_Tracking_Settings
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
		
		//rename order status +  rename bulk action + rename filter
		add_filter( 'wc_order_statuses', array( $this, 'wc_renaming_order_status' ) );		
		add_filter( 'woocommerce_register_shop_order_post_statuses', array( $this, 'filter_woocommerce_register_shop_order_post_statuses' ), 10, 1 );
		add_filter( 'bulk_actions-edit-shop_order', array( $this, 'modify_bulk_actions' ), 50, 1 );
				
		add_action( 'woocommerce_update_options_email_customer_partial_shipped_order', array( $this, 'save_partial_shipped_email' ) ,100, 1); 
		add_action( 'wp_ajax_sync_providers', array( $this, 'sync_providers_fun' ) );
		
		//new order status
		$newstatus = get_option( 'wc_ast_status_delivered', 0 );
		if ( true == $newstatus ) {
			//register order status 
			add_action( 'init', array( $this, 'register_order_status' ) );
			//add status after completed
			add_filter( 'wc_order_statuses', array( $this, 'add_delivered_to_order_statuses' ) );
			//Custom Statuses in admin reports
			add_filter( 'woocommerce_reports_order_statuses', array( $this, 'include_custom_order_status_to_reports' ), 20, 1 );
			// for automate woo to check order is paid
			add_filter( 'woocommerce_order_is_paid_statuses', array( $this, 'delivered_woocommerce_order_is_paid_statuses' ) );
			//add bulk action
			add_filter( 'bulk_actions-edit-shop_order', array( $this, 'add_bulk_actions' ), 50, 1 );
			//add reorder button
			add_filter( 'woocommerce_valid_order_statuses_for_order_again', array( $this, 'add_reorder_button_delivered' ), 50, 1 );
		}						
		
		//new order status
		$updated_tracking_status = get_option( 'wc_ast_status_updated_tracking', 0 );
		if ( true == $updated_tracking_status ) {			
			//register order status 
			add_action( 'init', array( $this, 'register_updated_tracking_order_status' ) );
			//add status after completed
			add_filter( 'wc_order_statuses', array( $this, 'add_updated_tracking_to_order_statuses' ) );
			//Custom Statuses in admin reports
			add_filter( 'woocommerce_reports_order_statuses', array( $this, 'include_updated_tracking_order_status_to_reports' ), 20, 1 );
			// for automate woo to check order is paid
			add_filter( 'woocommerce_order_is_paid_statuses', array( $this, 'updated_tracking_woocommerce_order_is_paid_statuses' ) );
			//add bulk action
			add_filter( 'bulk_actions-edit-shop_order', array( $this, 'add_bulk_actions_updated_tracking' ), 50, 1 );
			//add reorder button
			add_filter( 'woocommerce_valid_order_statuses_for_order_again', array( $this, 'add_reorder_button_updated_tracking' ), 50, 1 );
			add_filter( 'wcast_order_status_email_type', array( $this, 'wcast_order_status_email_type' ), 50, 1 );
		}
		
		//new order status
		$partial_shipped_status = get_option( 'wc_ast_status_partial_shipped', 0 );
		if ( true == $partial_shipped_status ) {
			//register order status 
			add_action( 'init', array( $this, 'register_partial_shipped_order_status' ) );
			//add status after completed
			add_filter( 'wc_order_statuses', array( $this, 'add_partial_shipped_to_order_statuses' ) );
			//Custom Statuses in admin reports
			add_filter( 'woocommerce_reports_order_statuses', array( $this, 'include_partial_shipped_order_status_to_reports' ), 20, 1 );
			// for automate woo to check order is paid
			add_filter( 'woocommerce_order_is_paid_statuses', array( $this, 'partial_shipped_woocommerce_order_is_paid_statuses' ) );
			//add bulk action
			add_filter( 'bulk_actions-edit-shop_order', array( $this, 'add_bulk_actions_partial_shipped' ), 50, 1 );
			//add reorder button
			add_filter( 'woocommerce_valid_order_statuses_for_order_again', array( $this, 'add_reorder_button_partial_shipped' ), 50, 1 );
		}				
		
		// Hook for add admin body class in settings page
		add_filter( 'admin_body_class', array( $this, 'ahipment_tracking_admin_body_class' ) );
		
		// Ajax hook for open inline tracking form
		add_action( 'wp_ajax_ast_open_inline_tracking_form', array( $this, 'ast_open_inline_tracking_form_fun' ) );							
	}

	/** 
	 * Register new status : Delivered
	**/
	public function register_order_status() {						
		register_post_status( 'wc-delivered', array(
			'label'                     => __( 'Delivered', 'woo-advanced-shipment-tracking' ),
			'public'                    => true,
			'show_in_admin_status_list' => true,
			'show_in_admin_all_list'    => true,
			'exclude_from_search'       => false,
			'label_count'               => _n_noop( 'Delivered <span class="count">(%s)</span>', 'Delivered <span class="count">(%s)</span>', 'woo-advanced-shipment-tracking' )
		) );		
	}			
	
	/** 
	 * Register new status : Updated Tracking
	**/
	public function register_updated_tracking_order_status() {
		register_post_status( 'wc-updated-tracking', array(
			'label'                     => __( 'Updated Tracking', 'woo-advanced-shipment-tracking' ),
			'public'                    => true,
			'show_in_admin_status_list' => true,
			'show_in_admin_all_list'    => true,
			'exclude_from_search'       => false,
			'label_count'               => _n_noop( 'Updated Tracking <span class="count">(%s)</span>', 'Updated Tracking <span class="count">(%s)</span>', 'woo-advanced-shipment-tracking' )
		) );		
	}
	
	/** 
	 * Register new status : Partially Shipped
	**/
	public function register_partial_shipped_order_status() {
		register_post_status( 'wc-partial-shipped', array(
			'label'                     => __( 'Partially Shipped', 'woo-advanced-shipment-tracking' ),
			'public'                    => true,
			'show_in_admin_status_list' => true,
			'show_in_admin_all_list'    => true,
			'exclude_from_search'       => false,
			'label_count'               => _n_noop( 'Partially Shipped <span class="count">(%s)</span>', 'Partially Shipped <span class="count">(%s)</span>', 'woo-advanced-shipment-tracking' )
		) );		
	}
	
	/*
	* add status after completed
	*/
	public function add_delivered_to_order_statuses( $order_statuses ) {							
		$new_order_statuses = array();
		foreach ( $order_statuses as $key => $status ) {
			$new_order_statuses[ $key ] = $status;
			if ( 'wc-completed' === $key ) {
				$new_order_statuses['wc-delivered'] = __( 'Delivered', 'woo-advanced-shipment-tracking' );				
			}
		}
		
		return $new_order_statuses;
	}			
	
	/*
	* add status after completed
	*/
	public function add_updated_tracking_to_order_statuses( $order_statuses ) {		
		$new_order_statuses = array();
		foreach ( $order_statuses as $key => $status ) {
			$new_order_statuses[ $key ] = $status;
			if ( 'wc-completed' === $key ) {
				$new_order_statuses['wc-updated-tracking'] = __( 'Updated Tracking', 'woo-advanced-shipment-tracking' );				
			}
		}		
		return $new_order_statuses;
	}
	
	/*
	* add status after completed
	*/
	public function add_partial_shipped_to_order_statuses( $order_statuses ) {		
		$new_order_statuses = array();
		foreach ( $order_statuses as $key => $status ) {
			$new_order_statuses[ $key ] = $status;
			if ( 'wc-completed' === $key ) {
				$new_order_statuses['wc-partial-shipped'] = __( 'Partially Shipped', 'woo-advanced-shipment-tracking' );				
			}
		}		
		return $new_order_statuses;
	}
	
	/*
	* Adding the custom order status to the default woocommerce order statuses
	*/
	public function include_custom_order_status_to_reports( $statuses ){
		if ( $statuses ) {
			$statuses[] = 'delivered';		
		}	
		return $statuses;
	}	
	
	/*
	* Adding the updated-tracking order status to the default woocommerce order statuses
	*/
	public function include_updated_tracking_order_status_to_reports( $statuses ){
		if ( $statuses ) {
			$statuses[] = 'updated-tracking';
		}	
		return $statuses;
	}

	/*
	* Adding the partial-shipped order status to the default woocommerce order statuses
	*/
	public function include_partial_shipped_order_status_to_reports( $statuses ){
		if ( $statuses ) {
			$statuses[] = 'partial-shipped';
		}	
		return $statuses;
	}		
	
	/*
	* mark status as a paid.
	*/
	public function delivered_woocommerce_order_is_paid_statuses( $statuses ) { 
		$statuses[] = 'delivered';		
		return $statuses; 
	}	
	
	/*
	* mark status as a paid.
	*/
	public function updated_tracking_woocommerce_order_is_paid_statuses( $statuses ) { 
		$statuses[] = 'updated-tracking';		
		return $statuses; 
	}

	/*
	* mark status as a paid.
	*/
	public function partial_shipped_woocommerce_order_is_paid_statuses( $statuses ) { 
		$statuses[] = 'partial-shipped';		
		return $statuses; 
	}		
	
	/*
	* add bulk action
	* Change order status to delivered
	*/
	public function add_bulk_actions( $bulk_actions ) {
		$lable = wc_get_order_status_name( 'delivered' );
		$bulk_actions['mark_delivered'] = __( 'Change status to '.$lable.'', 'woo-advanced-shipment-tracking' );	
		return $bulk_actions;		
	}		
	
	/*
	* add bulk action
	* Change order status to Updated Tracking
	*/
	public function add_bulk_actions_updated_tracking( $bulk_actions ) {
		$lable = wc_get_order_status_name( 'updated-tracking' );
		$bulk_actions['mark_updated-tracking'] = __( 'Change status to '.$lable.'', 'woo-advanced-shipment-tracking' );
		return $bulk_actions;		
	}

	/*
	* add bulk action
	* Change order status to Partially Shipped
	*/
	public function add_bulk_actions_partial_shipped( $bulk_actions ) {
		$lable = wc_get_order_status_name( 'partial-shipped' );
		$bulk_actions['mark_partial-shipped'] = __( 'Change status to '.$lable.'', 'woo-advanced-shipment-tracking' );
		return $bulk_actions;		
	}

	/*
	* add order again button for delivered order status	
	*/
	public function add_reorder_button_delivered( $statuses ) {
		$statuses[] = 'delivered';
		return $statuses;	
	}

	/*
	* add order again button for delivered order status	
	*/
	public function add_reorder_button_partial_shipped( $statuses ) {
		$statuses[] = 'partial-shipped';
		return $statuses;	
	}

	/*
	* add order again button for delivered order status	
	*/
	public function add_reorder_button_updated_tracking( $statuses ) {
		$statuses[] = 'updated-tracking';
		return $statuses;	
	}
	
	/*
	* add Updated Tracking in order status email customizer
	*/
	public function wcast_order_status_email_type( $order_status ) {
		$updated_tracking_status = array(
			'updated_tracking' => __( 'Updated Tracking', 'woo-advanced-shipment-tracking' ),
		);
		$order_status = array_merge( $order_status, $updated_tracking_status );
		return $order_status;
	}	
	
	/*
	* Rename WooCommerce Order Status
	*/
	public function wc_renaming_order_status( $order_statuses ) {
		
		$enable = get_option( 'wc_ast_status_shipped', 0);
		if ( false == $enable ) {
			return $order_statuses;
		}	
		
		foreach ( $order_statuses as $key => $status ) {
			$new_order_statuses[ $key ] = $status;
			if ( 'wc-completed' === $key ) {
				$order_statuses['wc-completed'] = esc_html__( 'Shipped','woo-advanced-shipment-tracking' );
			}
		}		
		return $order_statuses;
	}			
	
	/*
	* define the woocommerce_register_shop_order_post_statuses callback 
	* rename filter 
	* rename from completed to shipped
	*/
	public function filter_woocommerce_register_shop_order_post_statuses( $array ) {
		
		$enable = get_option( 'wc_ast_status_shipped', 0);
		if ( false == $enable ) {
			return $array;
		}	
		
		if ( isset( $array[ 'wc-completed' ] ) ) {
			$array[ 'wc-completed' ]['label_count'] = _n_noop( 'Shipped <span class="count">(%s)</span>', 'Shipped <span class="count">(%s)</span>', 'woo-advanced-shipment-tracking' );
		}
		return $array; 
	}
	
	/*
	* rename bulk action
	*/
	public function modify_bulk_actions( $bulk_actions ) {
		
		$enable = get_option( 'wc_ast_status_shipped', 0);
		if ( false == $enable ) {
			return $bulk_actions;
		}	
		
		if ( isset( $bulk_actions['mark_completed'] ) ) {
			$bulk_actions['mark_completed'] = __( 'Change status to shipped', 'woo-advanced-shipment-tracking' );
		}
		return $bulk_actions;
	}		
	
	/*
	* Add class in admin settings page
	*/
	public function ahipment_tracking_admin_body_class( $classes ) {
		$page = ( isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '' );
		if ( 'woocommerce-advanced-shipment-tracking' == $page ) {
			$classes .= ' shipment_tracking_admin_settings';
		}
		if ( 'trackship-for-woocommerce' == $page ) {
			$classes .= ' trackship_admin_settings';
		}
        return $classes;
	}
	
	public function ast_open_inline_tracking_form_fun() {
		
		$order_id =  wc_clean( $_POST['order_id'] );
		$order = wc_get_order( $order_id );
		$order_number = $order->get_order_number();
		
		global $wpdb;
		$WC_Countries = new WC_Countries();
		$countries = $WC_Countries->get_countries();
		
		$shippment_countries = $wpdb->get_results( "SELECT shipping_country FROM $this->table WHERE display_in_order = 1 GROUP BY shipping_country" );				
		
		$default_provider = get_option("wc_ast_default_provider" );		
		ob_start();
		?>
		<div id="" class="trackingpopup_wrapper add_tracking_popup" style="display:none;">
			<div class="trackingpopup_row">
				<div class="popup_header">
					<h3 class="popup_title"><?php esc_html_e( 'Add Tracking - order	', 'woo-advanced-shipment-tracking'); ?> - #<?php esc_html_e( $order_number ); ?></h2>					
					<span class="dashicons dashicons-no-alt popup_close_icon"></span>
				</div>
				<div class="popup_body">
					<form id="add_tracking_number_form" method="POST" class="add_tracking_number_form">	
						<?php do_action( 'ast_tracking_form_between_form', $order_id, 'inline' ); ?>
						<p class="form-field tracking_number_field form-50">
							<label for="tracking_number"><?php esc_html_e( 'Tracking number:', 'woo-advanced-shipment-tracking'); ?></label>
							<input type="text" class="short" name="tracking_number" id="tracking_number" value="" autocomplete="off"> 
						</p>
						<p class="form-field form-50">
							<label for="tracking_number"><?php esc_html_e( 'Shipping Provider:', 'woo-advanced-shipment-tracking'); ?></label>
							<select class="chosen_select tracking_provider_dropdown" id="tracking_provider" name="tracking_provider">
								<option value=""><?php esc_html_e( 'Shipping Provider:', 'woo-advanced-shipment-tracking' ); ?></option>
								<?php 
									foreach ( $shippment_countries as $s_c ) {
										if ( 'Global' != $s_c->shipping_country ) {
											$country_name = esc_attr( $WC_Countries->countries[ $s_c->shipping_country ] );
										} else {
											$country_name = 'Global';
										}
										echo '<optgroup label="' . $country_name . '">';
											$country = $s_c->shipping_country;				
											$shippment_providers_by_country = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->table WHERE shipping_country = %s AND display_in_order = 1",$country ) );
											foreach ( $shippment_providers_by_country as $providers ) {											
												$selected = ( $default_provider == esc_attr( $providers->provider_name ) ) ? 'selected' : '';
												echo '<option value="' . esc_attr( $providers->ts_slug ) . '" '.$selected. '>' . esc_html( $providers->provider_name ) . '</option>';
											}
										echo '</optgroup>';	
									} ?>
							</select>
						</p>					
						<p class="form-field tracking_product_code_field form-50">
							<label for="tracking_product_code"><?php esc_html_e( 'Product Code:', 'woo-advanced-shipment-tracking'); ?></label>
							<input type="text" class="short" name="tracking_product_code" id="tracking_product_code" value=""> 
						</p>
						<p class="form-field date_shipped_field form-50">
							<label for="date_shipped"><?php esc_html_e( 'Date shipped:', 'woo-advanced-shipment-tracking'); ?></label>
							<input type="text" class="ast-date-picker-field" name="date_shipped" id="date_shipped" value="<?php echo date_i18n( __( 'Y-m-d', 'woo-advanced-shipment-tracking' ), current_time( 'timestamp' ) ); ?>" placeholder="<?php echo date_i18n( esc_html_e( 'Y-m-d', 'woo-advanced-shipment-tracking' ), time() ); ?>">						
						</p>								
						<?php do_action( 'ast_after_tracking_field', $order_id ); ?>
						<hr>
						<?php wc_advanced_shipment_tracking()->actions->mark_order_as_fields_html(); ?>
						<hr>
						<p>		
							<input type="hidden" name="action" value="add_inline_tracking_number">
							<input type="hidden" name="order_id" id="order_id" value="<?php esc_html_e( $order_id ); ?>">
							<input type="submit" name="Submit" value="<?php esc_html_e( 'Fulfill Order', 'woo-advanced-shipment-tracking' ); ?>" class="button-primary btn_green">        
						</p>			
					</form>
				</div>								
			</div>
			<div class="popupclose"></div>
		</div>
		<?php		
		$html = ob_get_clean();
		echo $html;exit;	
	}	
	
	/**
	* Update Partially Shipped order email enable/disable in customizer
	*/
	public function save_partial_shipped_email( $data ) {
		$woocommerce_customer_partial_shipped_order_enabled = ( isset( $_POST['woocommerce_customer_partial_shipped_order_enabled'] ) ? wc_clean( $_REQUEST['woocommerce_customer_partial_shipped_order_enabled'] ) : "" );
		update_option( 'customizer_partial_shipped_order_settings_enabled', $woocommerce_customer_partial_shipped_order_enabled );
	}
	
	/**
	* Synch provider function 
	*/
	public function sync_providers_fun() {
		$reset_checked = sanitize_text_field( $_POST[ 'reset_checked' ] );		
		global $wpdb;		
		
		$url =	apply_filters( 'ast_sync_provider_url', 'https://trackship.info/wp-json/WCAST/v1/Provider' );
		$resp = wp_remote_get( $url );

		$upload_dir   = wp_upload_dir();	
		$ast_directory = $upload_dir['basedir'] . '/ast-shipping-providers';		
		
		if ( !is_dir( $ast_directory ) ) {
			wp_mkdir_p( $ast_directory );	
		}
		
		if ( is_array( $resp ) && ! is_wp_error( $resp ) ) {
			$providers = json_decode( $resp['body'], true );
			
			if ( 1 == $reset_checked ) {
				
				$wpdb->query( "DROP TABLE IF EXISTS {$this->table}" );
				
				$install = WC_Advanced_Shipment_Tracking_Install::get_instance();
				$install->create_shippment_tracking_table();
				
				foreach ( $providers as $provider ) {
					$provider_name = $provider['shipping_provider'];
					$provider_url = $provider['provider_url'];
					$shipping_country = $provider['shipping_country'];
					$ts_slug = $provider['shipping_provider_slug'];	
					$img_url = $provider['img_url'];			
					$trackship_supported = $provider['trackship_supported'];							
					$img_slug = sanitize_title( $provider_name );
									
					$img = $ast_directory . '/' . $img_slug . '.png';
					
					$ch = curl_init(); 
			
					curl_setopt( $ch, CURLOPT_HEADER, 0 ); 
					curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 ); 
					curl_setopt( $ch, CURLOPT_URL, $img_url ); 
					
					$data = curl_exec( $ch );
					curl_close( $ch );
					
					file_put_contents( $img, $data );
								
					$data_array = array(
						'shipping_country' => sanitize_text_field( $shipping_country ),
						'provider_name' => sanitize_text_field( $provider_name ),
						'ts_slug' => $ts_slug,
						'provider_url' => sanitize_text_field( $provider_url ),			
						'display_in_order' => 1,
						'shipping_default' => 1,
						'trackship_supported' => sanitize_text_field( $trackship_supported ),
					);
					
					$data_array = apply_filters( 'ast_sync_provider_data_array', $data_array, $provider );
					
					$result = $wpdb->insert( $this->table, $data_array );
				}
				
				$status = 'active';
				$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table ORDER BY shipping_default ASC, display_in_order DESC, trackship_supported DESC, id ASC" );	
				ob_start();
				$admin = new WC_Advanced_Shipment_Tracking_Admin;
				$html = $admin->get_provider_html( $default_shippment_providers, $status );
				$html = ob_get_clean();	
				
				echo json_encode( array( 'html' => $html ) );
				exit;
			} else {
			
				$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table ORDER BY shipping_default ASC, display_in_order DESC, trackship_supported DESC, id ASC" );
				
				foreach ( $default_shippment_providers as $key => $val ) {
					$shippment_providers[ $val->provider_name ] = $val;						
				}
		
				foreach ( $providers as $key => $val ) {
					$providers_name[ $val['provider_name'] ] = $val;						
				}		
					
				$added = 0;
				$updated = 0;
				$deleted = 0;
				$added_html = '';
				$updated_html = '';
				$deleted_html = '';
				
				foreach ( $providers as $provider ) {
					
					$provider_name = $provider['shipping_provider'];
					$provider_url = $provider['provider_url'];
					$shipping_country = $provider['shipping_country'];
					$ts_slug = $provider['shipping_provider_slug'];
					$trackship_supported = $provider['trackship_supported'];
					
					if ( isset( $shippment_providers[ $provider_name ] ) ) {				
						$db_provider_url = $shippment_providers[ $provider_name ]->provider_url;
						$db_shipping_country = $shippment_providers[ $provider_name ]->shipping_country;
						$db_ts_slug = $shippment_providers[ $provider_name ]->ts_slug;
						$db_trackship_supported = $shippment_providers[ $provider_name ]->trackship_supported;
						
						$update_needed = apply_filters( 'ast_sync_provider_update', false, $provider, $shippment_providers );
						
						if ( $db_provider_url != $provider_url ) {
							$update_needed = true;
						} elseif ( $db_shipping_country != $shipping_country ) {
							$update_needed = true;
						} elseif ( $db_ts_slug != $ts_slug ) {
							$update_needed = true;
						} elseif ( $db_trackship_supported != $trackship_supported ) {
							$update_needed = true;
						}
						
						if ( $update_needed ) {
							
							$data_array = array(
								'ts_slug' => $ts_slug,
								'provider_url' => $provider_url,
								'shipping_country' => $shipping_country,
								'trackship_supported' => $trackship_supported,								
							);
							
							$data_array = apply_filters( 'ast_sync_provider_data_array', $data_array, $provider );
							
							$where_array = array(
								'provider_name' => $provider_name,			
							);					
							$wpdb->update( $this->table, $data_array, $where_array );
							$updated_data[ $updated ] = array( 'provider_name' => $provider_name );
							$updated++;
						}
					} else {
						$img_url = $provider['img_url'];					
						$img_slug = sanitize_title( $provider_name );
						$img = $ast_directory . '/' . $img_slug . '.png';
						
						$ch = curl_init(); 
		
						curl_setopt( $ch, CURLOPT_HEADER, 0 ); 
						curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
						curl_setopt( $ch, CURLOPT_URL, $img_url );
					
						$data = curl_exec( $ch ); 
						curl_close( $ch );
						
						file_put_contents( $img, $data );
																		
						$data_array = array(
							'shipping_country' => sanitize_text_field( $shipping_country ),
							'provider_name' => sanitize_text_field( $provider_name ),
							'ts_slug' => $ts_slug,
							'provider_url' => sanitize_text_field( $provider_url ),
							'display_in_order' => 0,
							'shipping_default' => 1,
							'trackship_supported' => sanitize_text_field( $trackship_supported ),
						);
						
						$data_array = apply_filters( 'ast_sync_provider_data_array', $data_array, $provider );
						
						$result = $wpdb->insert( $this->table, $data_array );
						$added_data[ $added ] = array( 'provider_name' => $provider_name );
						$added++;
					}		
				}
				
				foreach ( $default_shippment_providers as $db_provider ) {
					if ( !isset( $providers_name[ $db_provider->provider_name ] ) ) {			
						$where = array(
							'provider_name' => $db_provider->provider_name,
							'shipping_default' => 1
						);
						$wpdb->delete( $this->table, $where );
						$deleted_data[ $deleted ] = array( 'provider_name' => $db_provider->provider_name );
						$deleted++;		
					}
				}

				if ( $added > 0 ) {
					ob_start();
					$added_html = $this->added_html( $added_data );
					$added_html = ob_get_clean();	
				}
				
				if ( $updated > 0 ) {
					ob_start();
					$updated_html = $this->updated_html( $updated_data );
					$updated_html = ob_get_clean();	
				}
				
				if ( $deleted > 0 ) {
					ob_start();
					$deleted_html = $this->deleted_html( $deleted_data );
					$deleted_html = ob_get_clean();	
				}
				
				$status = 'active';
				$default_shippment_providers = $wpdb->get_results( "SELECT * FROM $this->table ORDER BY shipping_default ASC, display_in_order DESC, trackship_supported DESC, id ASC" );	
				ob_start();
				$admin = new WC_Advanced_Shipment_Tracking_Admin;
				$html = $admin->get_provider_html( $default_shippment_providers, $status );
				$html = ob_get_clean();										
				
				echo json_encode( array( 'added' => $added, 'added_html' => $added_html, 'updated' => $updated, 'updated_html' => $updated_html, 'deleted' => $deleted, 'deleted_html' => $deleted_html,'html' => $html ) );
				exit;
			}
		} else {
			echo json_encode( array( 'sync_error' => 1, 'message' => __( 'There are some issue with sync, Please Retry.', 'woo-advanced-shipment-tracking') ) );
			exit;
		}	
	}
	
	/**
	* Output html of added provider from sync providers
	*/
	public function added_html( $added_data ) { 
		?>
		<ul class="updated_details" id="added_providers">
			<?php 
			foreach ( $added_data as $added ) { 
				?>
				<li><?php esc_html_e( $added['provider_name'] ); ?></li>	
			<?php } ?>
		</ul>
		<a class="view_synch_details" id="view_added_details" href="javaScript:void(0);" style="display: block;"><?php esc_html_e( 'view details', 'woo-advanced-shipment-tracking' ); ?></a>
		<a class="view_synch_details" id="hide_added_details" href="javaScript:void(0);" style="display: none;"><?php esc_html_e( 'hide details', 'woo-advanced-shipment-tracking' ); ?></a>
	<?php 
	}

	/**
	* Output html of updated provider from sync providers
	*/
	public function updated_html( $updated_data ) { 
		?>
		<ul class="updated_details" id="updated_providers">
			<?php 
			foreach ( $updated_data as $updated ) { 
				?>
				<li><?php esc_html_e( $updated['provider_name'] ); ?></li>	
			<?php } ?>
		</ul>
		<a class="view_synch_details" id="view_updated_details" href="javaScript:void(0);" style="display: block;"><?php esc_html_e( 'view details', 'woo-advanced-shipment-tracking' ); ?></a>
		<a class="view_synch_details" id="hide_updated_details" href="javaScript:void(0);" style="display: none;"><?php esc_html_e( 'hide details', 'woo-advanced-shipment-tracking' ); ?></a>
	<?php 
	}
	
	/**
	* Output html of deleted provider from sync providers
	*/
	public function deleted_html( $deleted_data ) { 
		?>
		<ul class="updated_details" id="deleted_providers">
			<?php 
			foreach ( $deleted_data as $deleted ) { 
				?>
				<li><?php esc_html_e( $deleted['provider_name'] ); ?></li>	
			<?php } ?>
		</ul>
		<a class="view_synch_details" id="view_deleted_details" href="javaScript:void(0);" style="display: block;"><?php esc_html_e( 'view details', 'woo-advanced-shipment-tracking'); ?></a>
		<a class="view_synch_details" id="hide_deleted_details" href="javaScript:void(0);" style="display: none;"><?php esc_html_e( 'hide details', 'woo-advanced-shipment-tracking'); ?></a>
	<?php 
	}	
}