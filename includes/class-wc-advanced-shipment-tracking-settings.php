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
		add_filter( 'bulk_actions-woocommerce_page_wc-orders', array( $this, 'modify_bulk_actions' ), 50, 1 );

		add_action( 'woocommerce_update_options_email_customer_partial_shipped_order', array( $this, 'save_partial_shipped_email' ), 100, 1); 
		add_action( 'wp_ajax_sync_providers', array( $this, 'sync_providers_fun' ) );
		
		$wc_ast_status_delivered = get_option( 'wc_ast_status_delivered', 0);
		if ( true == $wc_ast_status_delivered ) {
			//register order status 
			add_action( 'init', array( $this, 'register_order_status') );
			//add status after completed
			add_filter( 'wc_order_statuses', array( $this, 'add_delivered_to_order_statuses') );
			//Custom Statuses in admin reports
			add_filter( 'woocommerce_reports_order_statuses', array( $this, 'include_custom_order_status_to_reports'), 20, 1 );
			// for automate woo to check order is paid
			add_filter( 'woocommerce_order_is_paid_statuses', array( $this, 'delivered_woocommerce_order_is_paid_statuses' ) );
			//add bulk action
			add_filter( 'bulk_actions-edit-shop_order', array( $this, 'add_bulk_actions'), 50, 1 );
			add_filter( 'bulk_actions-woocommerce_page_wc-orders', array( $this, 'add_bulk_actions' ), 50, 1 );
			//add reorder button
			add_filter( 'woocommerce_valid_order_statuses_for_order_again', array( $this, 'add_reorder_button_delivered'), 50, 1 );
			//add button in preview
			add_filter( 'woocommerce_admin_order_preview_actions', array( $this, 'additional_admin_order_preview_buttons_actions'), 5, 2 );
			//add actions in column
			add_filter( 'woocommerce_admin_order_actions', array( $this, 'add_delivered_order_status_actions_button'), 100, 2 );
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
			add_filter('woocommerce_order_is_download_permitted', array( $this, 'add_updated_tracking_to_download_permission' ), 10, 2);
			//add bulk action
			add_filter( 'bulk_actions-edit-shop_order', array( $this, 'add_bulk_actions_updated_tracking' ), 50, 1 );
			add_filter( 'bulk_actions-woocommerce_page_wc-orders', array( $this, 'add_bulk_actions_updated_tracking' ), 50, 1 );
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
			add_filter('woocommerce_order_is_download_permitted', array( $this, 'add_partial_shipped_to_download_permission' ), 10, 2);
			//add bulk action
			add_filter( 'bulk_actions-edit-shop_order', array( $this, 'add_bulk_actions_partial_shipped' ), 50, 1 );
			add_filter( 'bulk_actions-woocommerce_page_wc-orders', array( $this, 'add_bulk_actions_partial_shipped' ), 50, 1 );
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
			/* translators: %s: search number of order */
			'label_count'               => _n_noop( 'Delivered <span class="count">(%s)</span>', 'Delivered <span class="count">(%s)</span>', 'woo-advanced-shipment-tracking' )
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
	* Adding the custom order status to the default woocommerce order statuses
	*/
	public function include_custom_order_status_to_reports( $statuses ) {
		if ( $statuses ) {
			$statuses[] = 'delivered';
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
	* add bulk action
	* Change order status to delivered
	*/
	public function add_bulk_actions( $bulk_actions ) {
		$lable = wc_get_order_status_name( 'delivered' );	
		/* translators: %s: search order status label */
		$bulk_actions['mark_delivered'] = sprintf( __( 'Change status to %s', 'woo-advanced-shipment-tracking' ), $lable );	
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
			foreach ( $custom_statuses as $status_slug => $values ) {
				if ( $order->has_status( $values['allowed'] ) ) {
					$actions[ 'status' ][ 'group' ] = __( 'Change status: ', 'woocommerce' );
					$actions[ 'status' ][ 'actions' ][ $status_slug ] = array(
						'url'    => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=' . $status_slug . '&order_id=' . $order->get_id() ), 'woocommerce-mark-order-status' ),
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
	* Add action button in order list to change order status from completed to delivered
	*/
	public function add_delivered_order_status_actions_button( $actions, $order ) {
		
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
					'action'    => 'delivered_icon', // keep "view" class for a clean button CSS
				);
			}	
		}
		
		return $actions;
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
											/* translators: %s: replace with Updated Tracking Count */
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
											/* translators: %s: replace with Partially Shipped Count */
			'label_count'               => _n_noop( 'Partially Shipped <span class="count">(%s)</span>', 'Partially Shipped <span class="count">(%s)</span>', 'woo-advanced-shipment-tracking' )
		) );		
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
	* Adding the updated-tracking order status to the default woocommerce order statuses
	*/
	public function include_updated_tracking_order_status_to_reports( $statuses ) {
		if ( $statuses ) {
			$statuses[] = 'updated-tracking';
		}	
		return $statuses;
	}

	/*
	* Adding the partial-shipped order status to the default woocommerce order statuses
	*/
	public function include_partial_shipped_order_status_to_reports( $statuses ) {
		if ( $statuses ) {
			$statuses[] = 'partial-shipped';
		}	
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
	* Give download permission to updated tracking order status
	*/
	public function add_updated_tracking_to_download_permission( $data, $order ) {
		if ( $order->has_status( 'updated-tracking' ) ) { 
			return true; 
		}
		return $data;
	}

	/*
	* mark status as a paid.
	*/
	public function partial_shipped_woocommerce_order_is_paid_statuses( $statuses ) { 
		$statuses[] = 'partial-shipped';		
		return $statuses; 
	}

	/*
	* Give download permission to partial shipped order status
	*/
	public function add_partial_shipped_to_download_permission( $data, $order ) {
		if ( $order->has_status( 'partial-shipped' ) ) { 
			return true; 
		}
		return $data;
	}	
	
	/*
	* add bulk action
	* Change order status to Updated Tracking
	*/
	public function add_bulk_actions_updated_tracking( $bulk_actions ) {
		$lable = wc_get_order_status_name( 'updated-tracking' );	
		/* translators: %s: search order status label */	
		$bulk_actions['mark_updated-tracking'] = sprintf( __( 'Change status to %s', 'woo-advanced-shipment-tracking' ), $lable );
		return $bulk_actions;		
	}

	/*
	* add bulk action
	* Change order status to Partially Shipped
	*/
	public function add_bulk_actions_partial_shipped( $bulk_actions ) {
		$lable = wc_get_order_status_name( 'partial-shipped' );
		/* translators: %s: search order status label */
		$bulk_actions['mark_partial-shipped'] = sprintf( __( 'Change status to %s', 'woo-advanced-shipment-tracking' ), $lable );
		return $bulk_actions;		
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
				$order_statuses['wc-completed'] = esc_html__( 'Shipped', 'woo-advanced-shipment-tracking' );
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
			/* translators: %s: replace with shipped order count */
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
		$page = ( isset( $_REQUEST['page'] ) ? wc_clean( $_REQUEST['page'] ) : '' );
		if ( 'woocommerce-advanced-shipment-tracking' == $page ) {
			$classes .= ' shipment_tracking_admin_settings';
		}	
		return $classes;
	}
	
	public function ast_open_inline_tracking_form_fun() {
		
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			exit( 'You are not allowed' );
		}

		check_ajax_referer( 'ast-order-list', 'security' );
		
		$order_id =  isset( $_POST['order_id'] ) ? wc_clean( $_POST['order_id'] ) :'';
		$order = wc_get_order( $order_id );
		$order_number = $order->get_order_number();
		
		global $wpdb;
		$WC_Countries = new WC_Countries();
		$countries = $WC_Countries->get_countries();
		
		$shippment_countries = $wpdb->get_results( $wpdb->prepare( 'SELECT shipping_country FROM %1s WHERE display_in_order = 1 GROUP BY shipping_country', $this->table ) );			
		
		$default_provider = get_option( 'wc_ast_default_provider' );
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
									echo '<optgroup label="' . esc_html( $country_name ) . '">';
									$country = $s_c->shipping_country;				
									$shippment_providers_by_country = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s WHERE shipping_country = %s AND display_in_order = 1', $this->table, $country ) );			foreach ( $shippment_providers_by_country as $providers ) {											
										$selected = ( esc_attr( $providers->provider_name ) == $default_provider ) ? 'selected' : '';
										echo '<option value="' . esc_attr( $providers->ts_slug ) . '" ' . esc_html( $selected ) . '>' . esc_html( $providers->provider_name ) . '</option>';
									}
									echo '</optgroup>';	
								}
								?>
							</select>
						</p>					
						<p class="form-field tracking_product_code_field form-50">
							<label for="tracking_product_code"><?php esc_html_e( 'Product Code:', 'woo-advanced-shipment-tracking'); ?></label>
							<input type="text" class="short" name="tracking_product_code" id="tracking_product_code" value=""> 
						</p>
						<p class="form-field date_shipped_field form-50">
							<label for="date_shipped"><?php esc_html_e( 'Date shipped:', 'woo-advanced-shipment-tracking'); ?></label>
							<input type="text" class="ast-date-picker-field" name="date_shipped" id="date_shipped" value="<?php echo esc_html( date_i18n( __( 'Y-m-d', 'woo-advanced-shipment-tracking' ), current_time( 'timestamp' ) ) ); ?>" placeholder="<?php echo esc_html( date_i18n( esc_html_e( 'Y-m-d', 'woo-advanced-shipment-tracking' ), time() ) ); ?>">						
						</p>								
						<?php do_action( 'ast_after_tracking_field', $order_id ); ?>
						<hr>
						<?php wc_advanced_shipment_tracking()->actions->mark_order_as_fields_html(); ?>
						<hr>
						<p>		
							<?php wp_nonce_field( 'wc_ast_inline_tracking_form', 'wc_ast_inline_tracking_form_nonce' ); ?>
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
		$json['html'] = $html;
		wp_send_json_success( $json );	
	}	
	
	/**
	* Update Partially Shipped order email enable/disable in customizer
	*/
	public function save_partial_shipped_email( $data ) {
		$woocommerce_customer_partial_shipped_order_enabled = ( isset( $_REQUEST['woocommerce_customer_partial_shipped_order_enabled'] ) ? wc_clean( $_REQUEST['woocommerce_customer_partial_shipped_order_enabled'] ) : '' );
		update_option( 'customizer_partial_shipped_order_settings_enabled', $woocommerce_customer_partial_shipped_order_enabled );
	}
	
	/**
	* Synch provider function 
	*/
	public function sync_providers_fun() {
		
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			exit( 'You are not allowed' );
		}
		
		check_ajax_referer( 'nonce_shipping_provider', 'security' );
		
		$reset_checked = isset( $_POST[ 'reset_checked' ] ) ? wc_clean( $_POST[ 'reset_checked' ] ) : '';
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
				
				$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %1s', $this->table ) );
				
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

					$response = wp_remote_get( $img_url );
					$data = wp_remote_retrieve_body( $response );					
					
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
				
				
				ob_start();
				$admin = new WC_Advanced_Shipment_Tracking_Admin();
				$html = $admin->get_provider_html( 1 );
				$html = ob_get_clean();	
				
				echo json_encode( array( 'html' => $html ) );
				exit;
			} else {
			
				$default_shippment_providers = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s WHERE shipping_default = 1', $this->table ) );
				
				foreach ( $default_shippment_providers as $key => $val ) {
					$shippment_providers[ $val->ts_slug ] = $val;						
				}
		
				foreach ( $providers as $key => $val ) {
					$providers_name[ $val['shipping_provider_slug'] ] = $val;						
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
					
					if ( isset( $shippment_providers[ $ts_slug ] ) ) {				
						
						$db_provider_name = $shippment_providers[ $ts_slug ]->provider_name;
						$db_provider_url = $shippment_providers[ $ts_slug ]->provider_url;
						$db_shipping_country = $shippment_providers[ $ts_slug ]->shipping_country;
						$db_ts_slug = $shippment_providers[ $ts_slug ]->ts_slug;
						$db_trackship_supported = $shippment_providers[ $ts_slug ]->trackship_supported;
						
						$update_needed = apply_filters( 'ast_sync_provider_update', false, $provider, $shippment_providers );
						
						if ( $db_provider_name != $provider_name ) {
							$update_needed = true;
						} elseif ( $db_provider_url != $provider_url ) {
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
								'provider_name' => $provider_name,
								'ts_slug' => $ts_slug,
								'provider_url' => $provider_url,
								'shipping_country' => $shipping_country,
								'trackship_supported' => $trackship_supported,								
							);
							
							$data_array = apply_filters( 'ast_sync_provider_data_array', $data_array, $provider );
							
							$where_array = array(
								'ts_slug' => $ts_slug,			
							);					
							$wpdb->update( $this->table, $data_array, $where_array );
							$updated_data[ $updated ] = array( 'provider_name' => $provider_name );
							$updated++;
						}
					} else {
						$img_url = $provider['img_url'];					
						$img_slug = sanitize_title( $provider_name );
						$img = $ast_directory . '/' . $img_slug . '.png';
						
						$response = wp_remote_get( $img_url );
						$data = wp_remote_retrieve_body( $response );
						
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
					if ( !isset( $providers_name[ $db_provider->ts_slug ] ) ) {			
						$where = array(
							'ts_slug' => $db_provider->ts_slug,
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
				
				ob_start();
				$admin = new WC_Advanced_Shipment_Tracking_Admin();
				$html = $admin->get_provider_html( 1 );
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
