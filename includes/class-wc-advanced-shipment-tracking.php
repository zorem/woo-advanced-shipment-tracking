<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

class WC_Advanced_Shipment_Tracking_Actions {

	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	 */
	private static $instance;
	
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
	 * Get the class instance
	 *
	 * @return WC_Advanced_Shipment_Tracking_Actions
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	/**
	 * Get shipping providers from database
	 */
	public function get_providers() {
		
		if ( empty( $this->providers ) ) {
			$this->providers = array();

			global $wpdb;
			$wpdb->hide_errors();
			$results = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s', $this->table ) );
			
			if ( ! empty( $results ) ) {
				
				foreach ( $results as $row ) {										
					$provider_name = !empty( $row->custom_provider_name ) ? $row->custom_provider_name : $row->provider_name;
					$shippment_providers[ $row->ts_slug ] = array(
						'provider_name'=> $provider_name,
						'provider_url' => $row->provider_url,
						'trackship_supported' => $row->trackship_supported,						
					);
				}

				$this->providers = $shippment_providers;
			}
		}
		return $this->providers;
		
	}
	
	/**
	 * Get shipping providers from database for WooCommerce App
	 */
	public function get_providers_for_app() {
		
		if ( empty( $this->providers_for_app ) ) {
			$this->providers_for_app = array();

			global $wpdb;
			$WC_Countries = new WC_Countries();
			$wpdb->hide_errors();
			
			$shippment_countries = $wpdb->get_results( $wpdb->prepare( 'SELECT shipping_country FROM %1s WHERE display_in_order = 1 GROUP BY shipping_country', $this->table ) );
			
			$results = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s GROUP BY shipping_country', $this->table ) );
			
			
			foreach ( $shippment_countries as $s_c ) {
				
				$country_name = ( 'Global' != $s_c->shipping_country ) ? esc_attr( $WC_Countries->countries[ $s_c->shipping_country ] ) : 'Global';
				$country = $s_c->shipping_country;
				$shippment_providers_by_country = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s WHERE shipping_country = %s AND display_in_order = 1', $this->table, $country ) );				
								
				$providers_array = array();
				$new_provider = array();
				
				foreach ( $shippment_providers_by_country as $providers ) {	
					$new_provider = array(
						$providers->provider_name => $providers->provider_url,	
					);	
					$providers_array = array_merge( $providers_array, $new_provider );
				}
				
				$shippment_providers[ $country_name ] = $providers_array;				
					
				$this->providers_for_app = $shippment_providers;				
			}						
		}
		return $this->providers_for_app;
		
	}

	/**
	 * Load admin styles.
	 */
	public function admin_styles() {
		$plugin_url  = wc_shipment_tracking()->plugin_url;
		wp_enqueue_style( 'ast_styles', $plugin_url . '/assets/css/admin.css', array(), wc_shipment_tracking()->version );				
	}

	/**
	 * Define shipment tracking column in admin orders list.
	 *
	 * @since 1.6.1
	 *
	 * @param array $columns Existing columns
	 *
	 * @return array Altered columns
	 */
	public function shop_order_columns( $columns ) {
		$columns['woocommerce-advanced-shipment-tracking'] = __( 'Shipment Tracking', 'woo-advanced-shipment-tracking' );
		return $columns;
	}

	/**
	 * Render shipment tracking in custom column.
	 *
	 * @since 1.6.1
	 *
	 * @param string $column Current column
	 */
	public function render_shop_order_columns( $column ) {
		global $post;
		if ( 'woocommerce-advanced-shipment-tracking' === $column ) {
			echo wp_kses_post( $this->get_shipment_tracking_column( $post->ID ) );
		}
	}

	public function render_woocommerce_page_orders_columns( $column_name, $order ) {
		if ( 'woocommerce-advanced-shipment-tracking' === $column_name ) {
			echo wp_kses_post( $this->get_shipment_tracking_column( $order->get_id() ) );
		}
	}

	/**
	 * Get content for shipment tracking column.
	 *
	 * @since 1.6.1
	 *
	 * @param int $order_id Order ID
	 *
	 * @return string Column content to render
	 */
	public function get_shipment_tracking_column( $order_id ) {
		ob_start();

		$tracking_items = $this->get_tracking_items( $order_id );

		if ( count( $tracking_items ) > 0 ) {
			echo '<ul class="wcast-tracking-number-list">';

			foreach ( $tracking_items as $tracking_item ) {
				global $wpdb;
				
				$tracking_provider = isset( $tracking_item['tracking_provider'] ) ? $tracking_item['tracking_provider'] : $tracking_item['custom_tracking_provider'];
				$tracking_provider = apply_filters( 'convert_provider_name_to_slug', $tracking_provider );

				$results = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM %1s WHERE ts_slug = %s', $this->table, $tracking_provider ) );
				
				$provider_name = apply_filters('get_ast_provider_name', $tracking_provider, $results);
				
				$formatted = $this->get_formatted_tracking_item( $order_id, $tracking_item );
				
				if ( $formatted['ast_tracking_link'] ) {
					printf(
						'<li id="tracking-item-%s" class="tracking-item-%s"><div><b>%s</b></div><a href="%s" target="_blank" class=ft11>%s</a><a class="inline_tracking_delete" rel="%s" data-order="%s" data-nonce="' . esc_html( wp_create_nonce( 'delete-tracking-item' ) ) . '"><span class="dashicons dashicons-trash"></span></a></li>',
						esc_attr( $tracking_item['tracking_id'] ),
						esc_attr( $tracking_item['tracking_id'] ),
						esc_html( $provider_name ),
						esc_url( $formatted['ast_tracking_link'] ),
						esc_html( $tracking_item['tracking_number'] ),
						esc_attr( $tracking_item['tracking_id'] ),
						esc_attr( $order_id )
					);
				} else {
					printf(
						'<li id="tracking-item-%s" class="tracking-item-%s"><div><b>%s</b></div>%s<a class="inline_tracking_delete" rel="%s" data-order="%s" data-nonce="' . esc_html( wp_create_nonce( 'delete-tracking-item' ) ) . '"><span class="dashicons dashicons-trash"></span></a></li>',
						esc_attr( $tracking_item['tracking_id'] ),
						esc_attr( $tracking_item['tracking_id'] ),
						esc_html( $provider_name ),
						esc_html( $tracking_item['tracking_number'] ),
						esc_attr( $tracking_item['tracking_id'] ),
						esc_attr( $order_id )
					);
				}
			}			
			echo '</ul>';
		} else {
			echo '–';			
		}		
		return apply_filters( 'woocommerce_shipment_tracking_get_shipment_tracking_column', ob_get_clean(), $order_id, $tracking_items );
	}	

	/**
	 * Add the meta box for shipment info on the order page
	 */
	public function add_meta_box() {
		
		if ( class_exists( 'Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) ) {
			$screen = wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled() ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order';	
		} else {
			$screen = 'shop_order';
		}
		
		add_meta_box( 'woocommerce-advanced-shipment-tracking', __( 'Shipment Tracking', 'woo-advanced-shipment-tracking' ), array( $this, 'meta_box' ), $screen, 'side', 'high' );
	}

	/**
	 * Returns a HTML node for a tracking item for the admin meta box
	 */
	public function display_html_tracking_item_for_meta_box( $order_id, $item ) {
			
		global $wpdb;
		
		$formatted = $this->get_formatted_tracking_item( $order_id, $item );			
		$tracking_provider = isset( $item['tracking_provider'] ) ? $item['tracking_provider'] : $item['custom_tracking_provider'];
		$tracking_provider = apply_filters( 'convert_provider_name_to_slug', $tracking_provider );
		$results = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM %1s WHERE ts_slug = %s', $this->table, $tracking_provider ) );
		$provider_name = apply_filters( 'get_ast_provider_name', $tracking_provider, $results );
		?>
		<div class="tracking-item" id="tracking-item-<?php echo esc_attr( $item['tracking_id'] ); ?>">
			<div class="tracking-content">
				<div class="tracking-content-div">
					<strong><?php echo esc_html( $provider_name ); ?></strong>						
					<?php if ( strlen( $formatted['ast_tracking_link'] ) > 0 ) { ?>
						- 
						<?php 							
						echo sprintf( '<a href="%s" target="_blank" title="' . esc_attr( __( 'Track Shipment', 'woo-advanced-shipment-tracking' ) ) . '">' . esc_html( $item['tracking_number'] ) . '</a>', esc_url( $formatted['ast_tracking_link'] ) ); 
						?>
					<?php } else { ?>
						<span> - <?php echo esc_html( $item['tracking_number'] ); ?></span>
					<?php } ?>
				</div>					
				<?php 
				do_action(	'ast_after_tracking_number', $order_id, $item['tracking_id'] );
				do_action(	'ast_shipment_tracking_end', $order_id, $item ); 
				?>
			</div>
			<p class="meta">
				<?php /* translators: 1: shipping date */ ?>
				<?php echo esc_html( sprintf( __( 'Shipped on %s', 'woo-advanced-shipment-tracking' ), date_i18n( get_option( 'date_format' ), $item['date_shipped'] ) ) ); ?>
				<a href="#" class="delete-tracking" rel="<?php echo esc_attr( $item['tracking_id'] ); ?>"><?php esc_html_e( 'Delete', 'woocommerce' ); ?></a>                    
			</p>
		</div>
		<?php
	}		

	/**
	 * Show the meta box for shipment info on the order page
	 */
	public function meta_box( $post_or_order_object ) {
		global $wpdb;			
		
		$order = ( $post_or_order_object instanceof WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;
		$order_id = $order->get_id();

		$order_status = $order->get_status();
		
		$WC_Countries = new WC_Countries();
		$countries = $WC_Countries->get_countries();
		
		$tracking_items = $this->get_tracking_items( $order_id );
		
		$shippment_countries = $wpdb->get_results( $wpdb->prepare( 'SELECT shipping_country FROM %1s WHERE display_in_order = 1 GROUP BY shipping_country', $this->table ) );
		
		$shippment_providers = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s', $this->table ) );
		
		$default_provider = get_option( 'wc_ast_default_provider' );
		$wc_ast_default_mark_shipped = 	get_option( 'wc_ast_default_mark_shipped' );
		$wc_ast_status_partial_shipped = get_option( 'wc_ast_status_partial_shipped' );
		$value = 1;
		$cbvalue = '';
		
		if ( 1 == $wc_ast_default_mark_shipped ) {
			if ( $wc_ast_status_partial_shipped ) {
				$cbvalue = 'change_order_to_shipped';
			} else {
				$cbvalue = 1;	
			}			
		}		
		
		$wc_ast_status_shipped = get_option( 'wc_ast_status_shipped' );
		
		if ( 1 == $wc_ast_status_shipped ) {
			$change_order_status_label = __( 'Mark as Shipped?', 'woo-advanced-shipment-tracking' );
			$shipped_label = 'Shipped';
		} else {
			$change_order_status_label = __( 'Mark as Completed?', 'woo-advanced-shipment-tracking' );
			$shipped_label = 'Completed';
		}				
						
		echo '<div id="tracking-items">';
		if ( count( $tracking_items ) > 0 ) {
			foreach ( $tracking_items as $tracking_item ) {				
				$this->display_html_tracking_item_for_meta_box( $order_id, $tracking_item );
			}
		}
		echo '</div>';
		
		do_action( 'ast_add_tracking_btn' );
		
		echo '<div id="advanced-shipment-tracking-form">'; 
		?>
			<p class="form-field tracking_number_field ">
				<label for="tracking_number"><?php esc_html_e( 'Tracking number:', 'woo-advanced-shipment-tracking' ); ?></label>
				<input type="text" class="short" style="" name="tracking_number" id="tracking_number" value="" autocomplete="off"> 
			</p>
			<?php
			echo '<p class="form-field tracking_provider_field"><label for="tracking_provider">' . esc_html__( 'Shipping Provider:', 'woo-advanced-shipment-tracking' ) . '</label><br/><select id="tracking_provider" name="tracking_provider" class="chosen_select tracking_provider_dropdown" style="width:100%;">';	
			
			echo '<option value="">' . esc_html__( 'Select Provider', 'woo-advanced-shipment-tracking' ) . '</option>';
			
			foreach ( $shippment_countries as $s_c ) {
				if ( 'Global' != $s_c->shipping_country ) {
					$country_name = esc_attr( $WC_Countries->countries[$s_c->shipping_country] );
				} else {
					$country_name = 'Global';
				}
				echo '<optgroup label="' . esc_html( $country_name ) . '">';
					$country = $s_c->shipping_country;				
					$shippment_providers_by_country = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s WHERE shipping_country = %s AND display_in_order = 1', $this->table, $country ) );
				foreach ( $shippment_providers_by_country as $providers ) {		
					$providers->ts_slug;	
					$selected = ( esc_attr( $providers->provider_name ) == $default_provider ) ? 'selected' : '';
					echo '<option value="' . esc_attr( $providers->ts_slug ) . '" ' . esc_html( $selected ) . '>' . esc_html( $providers->provider_name ) . '</option>';
				}
				echo '</optgroup>';	
			}
	
			echo '</select> ';
		
		woocommerce_wp_hidden_input( array(
			'id'    => 'wc_shipment_tracking_get_nonce',
			'value' => wp_create_nonce( 'get-tracking-item' ),
		) );

		woocommerce_wp_hidden_input( array(
			'id'    => 'wc_shipment_tracking_delete_nonce',
			'value' => wp_create_nonce( 'delete-tracking-item' ),
		) );

		woocommerce_wp_hidden_input( array(
			'id'    => 'wc_shipment_tracking_create_nonce',
			'value' => wp_create_nonce( 'create-tracking-item' ),
		) );		
		
		woocommerce_wp_text_input( array(
			'id'          => 'tracking_product_code',
			'label'       => __( 'Product Code:', 'woo-advanced-shipment-tracking' ),
			'placeholder' => '',
			'description' => '',
			'value'       => '',
		) );

		woocommerce_wp_text_input( array(
			'id'          => 'date_shipped',
			'label'       => __( 'Date shipped:', 'woo-advanced-shipment-tracking' ),
			'placeholder' => date_i18n( __( 'Y-m-d', 'woo-advanced-shipment-tracking' ), time() ),
			'description' => '',
			'class'       => 'date-picker-field',
			'value'       => date_i18n( __( 'Y-m-d', 'woo-advanced-shipment-tracking' ), current_time( 'timestamp' ) ),
		) );	
		
		do_action( 'ast_after_tracking_field', $order_id );	
		do_action( 'ast_tracking_form_between_form', $order_id, 'single_order' );
		
			if ( 'auto-draft' != $order_status ) {
				wc_advanced_shipment_tracking()->actions->mark_order_as_fields_html(); 
			}
		
			if ( 'auto-draft' != $order_status ) {
				echo '<button class="button button-primary btn_ast2 button-save-form">' . esc_html__( 'Save Tracking', 'woo-advanced-shipment-tracking' ) . '</button>';
			}
		
		echo '<p class="preview_tracking_link">' . esc_html__( 'Preview:', 'woo-advanced-shipment-tracking' ) . ' <a href="" target="_blank">' . esc_html__( 'Track Shipment', 'woo-advanced-shipment-tracking' ) . '</a></p>';
		
		echo '</div>';
		
		$provider_array = array();

			foreach ( $shippment_providers as $provider ) {
				$provider_array[ sanitize_title( $provider->provider_name ) ] = urlencode( $provider->provider_url );
			}
		
		$js = "
			jQuery( 'p.custom_tracking_link_field, p.custom_tracking_provider_field ').hide();

			jQuery( 'input#tracking_number, #tracking_provider' ).change( function() {

				var tracking  = jQuery( 'input#tracking_number' ).val();
				var provider  = jQuery( '#tracking_provider' ).val();
				var providers = jQuery.parseJSON( '" . json_encode( $provider_array ) . "' );

				var postcode = jQuery( '#_shipping_postcode' ).val();

				if ( ! postcode.length ) {
					postcode = jQuery( '#_billing_postcode' ).val();
				}

				postcode = encodeURIComponent( postcode );

				var link = '';

				if ( providers[ provider ] ) {
					link = providers[provider];
					link = link.replace( '%25number%25', tracking );
					link = link.replace( '%252%24s', postcode );
					link = decodeURIComponent( link );

					jQuery( 'p.custom_tracking_link_field, p.custom_tracking_provider_field' ).hide();
				} else {
					jQuery( 'p.custom_tracking_link_field, p.custom_tracking_provider_field' ).show();

					link = jQuery( 'input#custom_tracking_link' ).val();
				}

				if ( link ) {
					jQuery( 'p.preview_tracking_link a' ).attr( 'href', link );
					jQuery( 'p.preview_tracking_link' ).show();
				} else {
					jQuery( 'p.preview_tracking_link' ).hide();
				}

			} ).change();";

			if ( function_exists( 'wc_enqueue_js' ) ) {
				wc_enqueue_js( $js );
			} else {
				WC()->add_inline_js( $js );
			}
		
		wp_enqueue_style( 'ast_styles', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/admin.css', array(), wc_advanced_shipment_tracking()->version );				
		wp_enqueue_script( 'woocommerce-advanced-shipment-tracking-js', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/js/admin.js', array( 'jquery' ), wc_advanced_shipment_tracking()->version, true );
			?>
		<script>
			jQuery(document).on("change", "#tracking_provider", function(){	
				var selected_provider = jQuery(this).val();			
				if(selected_provider == 'nz-couriers' || selected_provider == 'post-haste' || selected_provider == 'castle-parcels' || selected_provider == 'dx-mail' || selected_provider == 'now-couriers'){
					jQuery('.tracking_product_code_field').show();
				} else{
					jQuery('.tracking_product_code_field').hide();
				}			
			});
		</script>
		<?php
		do_action( 'ast_tracking_form_end_meta_box' );
	}
	
	/*
	* Function for mark order as html
	*/
	public function mark_order_as_fields_html() {
		
		$wc_ast_status_shipped = get_option( 'wc_ast_status_shipped', 0 );
		$shipped_label = ( 1 == $wc_ast_status_shipped ) ? __( 'Shipped', 'woo-advanced-shipment-tracking' ) : __( 'Completed', 'woo-advanced-shipment-tracking' );
		
		$wc_ast_default_mark_shipped	= get_option( 'wc_ast_default_mark_shipped' );
		$wc_ast_status_partial_shipped	= get_option( 'wc_ast_status_partial_shipped' );
		
		$order_status_array = apply_filters( 'mark_order_as_fields_data' , array(
				'change_order_to_shipped' => array(					
					'name'		=> 'change_order_to_shipped',
					'class'		=> 'mark_shipped_checkbox',
					'label'		=> $shipped_label,
					'checked'	=> ( 1 == $wc_ast_default_mark_shipped ) ? true : false,
					'show'		=> true,
				),
				'change_order_to_partial_shipped' => array(					
					'name'		=> 'change_order_to_shipped',
					'class'		=> 'mark_shipped_checkbox',
					'label'		=> __( 'Partial Shipped', 'woo-advanced-shipment-tracking'),
					'checked'	=> false,
					'show'		=> ( 1 == $wc_ast_status_partial_shipped ) ? true : false		
				),
			)	
		);
		?>
		
		<fieldset class="form-field change_order_to_shipped_field">
			<span><?php esc_html_e( 'Mark order as:', 'woo-advanced-shipment-tracking'); ?></span>
			<ul class="wc-radios">
				<?php 
				foreach ( $order_status_array as $value => $data ) {
					if ( isset( $data['show'] ) && true == $data['show'] ) {
						$checked = $data['checked'] ? 'checked' : '';	
						?>
						<li>
							<label>
								<input name="<?php esc_html_e( $data['name'] ); ?>" value="<?php esc_html_e( $value ); ?>" type="checkbox" class="select short mark_shipped_checkbox" <?php esc_html_e( $checked ); ?>><?php esc_html_e( $data['label'] ); ?>
							</label>
						</li>
					<?php 
					} 
				}
				?>
			</ul>
		</fieldset>	
		<?php		
	}

	/*
	* Function for add tracking button in order details page
	*/
	public function ast_add_tracking_btn() {
		echo '<button class="button button-primary btn_ast2 btn_full button-show-tracking-form" type="button">' . esc_html__( 'Add Tracking Info', 'woo-advanced-shipment-tracking' ) . '</button>';
	}	

	/**
	 * Order Tracking Get All Order Items AJAX
	 *
	 * Function for getting all tracking items associated with the order
	 */
	public function get_meta_box_items_ajax() {
		
		check_ajax_referer( 'get-tracking-item', 'security', true );

		$order_id = isset( $_POST['order_id'] ) ? wc_clean( $_POST['order_id'] ) : '';
		$tracking_items = $this->get_tracking_items( $order_id );

		foreach ( $tracking_items as $tracking_item ) {
			$this->display_html_tracking_item_for_meta_box( $order_id, $tracking_item );
		}
		die();
	}
	
	/**
	 * Get shipping provider custom name or name	 
	 */
	public function get_ast_provider_name_callback( $provider_name, $results ) {
		
		if ( !empty( $results ) ) {
			$provider_name = ( null != $results->custom_provider_name ) ? $results->custom_provider_name : $results->provider_name;			
		}
		
		return $provider_name;
	}
	
	/**
	 * Get shipping provider image src 
	 */
	public function get_shipping_provdider_src_callback( $results ) {
		
		if ( !empty( $results ) ) {
			
			$upload_dir   = wp_upload_dir();	
			$ast_directory = $upload_dir['baseurl'] . '/ast-shipping-providers/';
			$ast_base_directory = $upload_dir['basedir'] . '/ast-shipping-providers/';
			
			$custom_thumb_id = $results->custom_thumb_id;			
			
			if ( 0 == (int) $custom_thumb_id && 1 == (int) $results->shipping_default ) {
				$src = $ast_directory . '' . sanitize_title( $results->provider_name ) . '.png?v=' . wc_advanced_shipment_tracking()->version;
			} else if ( 0 != (int) $custom_thumb_id ) {
				$image_attributes = wp_get_attachment_image_src( (int) $custom_thumb_id , array( '60', '60' ) );
				if ( $image_attributes[0] ) {
					$src = $image_attributes[0];
				}
			} else {
				$src = wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/images/icon-default.png';
			}
		} else {
			$src = wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/images/icon-default.png';
		}
		return $src;
	}
	
	/**
	 * Order Tracking Save
	 *
	 * Function for saving tracking items
	 */
	public function save_meta_box( $post_id, $post ) {
		
		// Check the nonce.
		if ( empty( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( wc_clean( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return;
		}
		
		$tracking_provider = isset( $_POST['tracking_provider'] ) ? wc_clean( $_POST['tracking_provider'] ) : '';
		$tracking_number = isset( $_POST['tracking_number'] ) ? wc_clean( $_POST['tracking_number'] ) : '';
		
		if ( strlen( $tracking_number ) > 0 && '' != $tracking_provider ) {	
			
			$tracking_product_code = isset( $_POST['tracking_product_code'] ) ? wc_clean( $_POST['tracking_product_code'] ) : '';
			$date_shipped = isset( $_POST['date_shipped'] ) ? wc_clean( $_POST['date_shipped'] ) : '';
			$tracking_number = isset( $_POST['tracking_number'] ) ? wc_clean( $_POST['tracking_number'] ) : '';
			$tracking_provider = isset( $_POST['tracking_provider'] ) ? wc_clean( $_POST['tracking_provider'] ) : '';
			$order = new WC_Order($post_id);
			
			$args = array(
				'tracking_provider'        => $tracking_provider,
				'tracking_number'          => $tracking_number,
				'tracking_product_code'    => $tracking_product_code,
				'date_shipped'             => $date_shipped,
			);
			
			$args = apply_filters( 'tracking_info_args', $args, $_POST, $post_id );
			
			if ( isset( $_POST['change_order_to_shipped'] ) ) {
				if ( 'change_order_to_shipped' == $_POST['change_order_to_shipped'] ) {
					$args['status_shipped'] = 1;														
				} elseif ( 'change_order_to_partial_shipped' == $_POST['change_order_to_shipped'] ) {
					$args['status_shipped'] = 2;	
				}
			} else {
				$args['status_shipped'] = 0;
			}
			
			$this->add_tracking_item( $post_id, $args );							
		}
	}
	
	/**
	 * Order Tracking Save AJAX
	 *
	 * Function for saving tracking items via AJAX
	 */
	public function save_meta_box_ajax() {
		
		check_ajax_referer( 'create-tracking-item', 'security', true );
		
		$tracking_provider = isset( $_POST['tracking_provider'] ) ? wc_clean( $_POST['tracking_provider'] ) : '';
		$tracking_number = isset( $_POST['tracking_number'] ) ? wc_clean( $_POST['tracking_number'] ) : '';
		//$tracking_number = str_replace( ' ', '', $tracking_number );				
		
		if ( strlen( $tracking_number ) > 0 && '' != $tracking_provider ) {	
	
			$order_id = isset( $_POST['order_id'] ) ? wc_clean( $_POST['order_id'] ) : '';
			$order = new WC_Order( $order_id );
			$tracking_product_code = isset( $_POST['tracking_product_code'] ) ? wc_clean( $_POST['tracking_product_code'] ) : '';
			$date_shipped = isset( $_POST['date_shipped'] ) ? wc_clean( $_POST['date_shipped'] ) : '';
			
			$args = array(
				'tracking_provider'        => $tracking_provider,
				'tracking_number'          => $tracking_number,
				'tracking_product_code'    => $tracking_product_code,	
				'date_shipped'             => $date_shipped,
			);
			
			$args = apply_filters( 'tracking_info_args', $args, $_POST, $order_id );
			
			$change_order_to_shipped = isset( $_POST[ 'change_order_to_shipped' ] ) ? wc_clean( $_POST[ 'change_order_to_shipped' ] ) : '';
			
			if ( 'change_order_to_shipped' == $change_order_to_shipped ) {  						
				$args['status_shipped'] = 1;																
			} elseif ( 'change_order_to_partial_shipped' == $change_order_to_shipped ) {	
				$args['status_shipped'] = 2;	
			}
			
			$tracking_item = $this->add_tracking_item( $order_id, $args );	
			
			do_action( 'ast_save_tracking_details_end', $order_id, $_POST );
			
			if ( isset( $_POST['productlist'] ) && !empty( $_POST['productlist'] ) && '[]' != $_POST['productlist'] ) {
				echo 'reload';
				die();
			}	
			
			$this->display_html_tracking_item_for_meta_box( $order_id, $tracking_item );
		}

		die();
	}
	
	/**
	 * Order Tracking Save AJAX
	 *
	 * Function for saving tracking items via AJAX
	 */
	public function save_inline_tracking_number() {		
		
		check_ajax_referer( 'wc_ast_inline_tracking_form', 'wc_ast_inline_tracking_form_nonce' );
		
		$tracking_provider = isset( $_POST['tracking_provider'] ) ? wc_clean( $_POST['tracking_provider'] ) : '';
		$tracking_number = isset( $_POST['tracking_number'] ) ? wc_clean( $_POST['tracking_number'] ) : '';
		
		if ( strlen( $tracking_number ) > 0 && '' != $tracking_provider ) {	
			
			$order_id = isset( $_POST['order_id'] ) ? wc_clean( $_POST['order_id'] ) : '';
			$tracking_product_code = isset( $_POST['tracking_product_code'] ) ? wc_clean( $_POST['tracking_product_code'] ) : '';
			$date_shipped = isset( $_POST['date_shipped'] ) ? wc_clean( $_POST['date_shipped'] ) : '';
			
			$args = array(
				'tracking_provider'        => $tracking_provider,
				'tracking_number'          => $tracking_number,
				'tracking_product_code'    => $tracking_product_code,	
				'date_shipped'             => $date_shipped,
			);
			
			$args = apply_filters( 'tracking_info_args', $args, $_POST, $order_id );
			
			$change_order_to_shipped = isset( $_POST[ 'change_order_to_shipped' ] ) ? wc_clean( $_POST[ 'change_order_to_shipped' ] ) : '';
			
			if ( 'change_order_to_shipped' == $change_order_to_shipped || 'yes' == $change_order_to_shipped ) {
				$args['status_shipped'] = 1;																
			} elseif ( 'change_order_to_partial_shipped' == $change_order_to_shipped ) {
				$args['status_shipped'] = 2;	
			}	
			
			$tracking_item = $this->add_tracking_item( $order_id, $args );		
			do_action( 'ast_save_tracking_details_end', $order_id, $_POST );	
		}
	}

	/**
	 * Order Tracking Delete
	 *
	 * Function to delete a tracking item
	 */
	public function meta_box_delete_tracking() {
		
		check_ajax_referer( 'delete-tracking-item', 'security' );
		
		$order_id    = isset( $_POST['order_id'] ) ? wc_clean( $_POST['order_id'] ) : '';
		$tracking_id = isset( $_POST['tracking_id'] ) ? wc_clean( $_POST['tracking_id'] ) : '';
		$tracking_items = $this->get_tracking_items( $order_id, true );
		
		do_action( 'delete_tracking_number_from_trackship', $tracking_items, $tracking_id, $order_id );				
		
		foreach ( $tracking_items as $tracking_item ) {
			if ( $tracking_item['tracking_id'] == $tracking_id ) {
				
				$formated_tracking_item = $this->get_formatted_tracking_item( $order_id, $tracking_item );
				$tracking_number = $tracking_item['tracking_number'];
				$tracking_provider = $formated_tracking_item['formatted_tracking_provider'];
				$order = wc_get_order(  $order_id );
				
				/* translators: %s: Reaplce with tracking provider, %s: Reaplce with tracking number */
				$note = sprintf( __( 'Tracking info was deleted for tracking provider %s with tracking number %s', 'woo-advanced-shipment-tracking' ), $tracking_provider, $tracking_number );
				
				// Add the note
				$order->add_order_note( $note );
			}
		}
		
		$this->delete_tracking_item( $order_id, $tracking_id );				
	}

	/**
	 * Display Shipment info in the frontend (order view/tracking page).
	 */
	public function show_tracking_info_order( $order_id ) {	
		
		wp_enqueue_style( 'front_style' );
		wp_enqueue_script( 'jquery-blockui' );
		wp_enqueue_script( 'front-js' );
		
		$local_template	= get_stylesheet_directory() . '/woocommerce/myaccount/tracking-info.php';

		if ( file_exists( $local_template ) && is_writable( $local_template ) ) {	
			wc_get_template( 'myaccount/tracking-info.php', array( 'tracking_items' => $this->get_tracking_items( $order_id, true ), 'order_id' => $order_id ), 'woocommerce-advanced-shipment-tracking/', get_stylesheet_directory() . '/woocommerce/' );
		} else {
			wc_get_template( 'myaccount/tracking-info.php', array( 'tracking_items' => $this->get_tracking_items( $order_id, true ), 'order_id' => $order_id ), 'woocommerce-advanced-shipment-tracking/', wc_advanced_shipment_tracking()->get_plugin_path() . '/templates/' );	
		}
	}	
	
	/**
	* Adds a new column Track to the "My Orders" table in the account.
	*
	* @param string[] $columns the columns in the orders table
	* @return string[] updated columns
	*/
	public function add_column_my_account_orders( $columns ) {
		
		$new_columns = array();
		foreach ( $columns as $key => $name ) {
	
			$new_columns[ $key ] = $name;
	
			// add ship-to after order status column
			if ( 'order-total' === $key ) {
				$new_columns['order-ast-track'] = __( 'Track', 'woo-advanced-shipment-tracking' );
			}
		}

		return $new_columns;	
	}	
	
	/**
	* Adds data to the custom "Track" column in "My Account > Orders".
	*
	* @param \WC_Order $order the order object for the row
	*/
	public function add_column_my_account_orders_ast_track_column( $actions, $order ) {
	
		$order_id = $order->get_id();
		$tracking_items = $this->get_tracking_items( $order_id, true );			
		$display_track_in_my_account = get_option( 'display_track_in_my_account', 0 );
		$open_track_in_new_tab = get_option( 'open_track_in_new_tab', 0 );
		
		if ( 1 != $display_track_in_my_account ) {
			return $actions;
		}
		
		if ( 0 == count( $tracking_items ) ) {
			return $actions;
		}
		
		if ( count( $tracking_items ) > 1 ) {
			$actions['ast_multi_track'] = array(
				// adjust URL as needed
				'url'  => $order->get_view_order_url(),
				'name' => __( 'Track', 'woo-advanced-shipment-tracking' ),
			);
			return $actions;
		}
		
		if ( 1 == $open_track_in_new_tab ) { 
			?>
			<script>
				jQuery( document ).ready(function() {
					jQuery('.ast_track').attr("target","_blank");
				});
			</script>
		<?php 
		}
		$tracking_items = reset($tracking_items);
		$actions['ast_track'] = array(
			// adjust URL as needed
			'url'  => $tracking_items[ 'ast_tracking_link' ],
			'name' => __( 'Track', 'woo-advanced-shipment-tracking' ),
		);		
		return $actions;
	}

	/**
	 * Display shipment info in customer emails.
	 *
	 * @version 1.6.8
	 *
	 * @param WC_Order $order         Order object.
	 * @param bool     $sent_to_admin Whether the email is being sent to admin or not.
	 * @param bool     $plain_text    Whether email is in plain text or not.
	 * @param WC_Email $email         Email object.
	 */
	public function email_display( $order, $sent_to_admin, $plain_text = null, $email = null ) {

		$wc_ast_unclude_tracking_info = get_option( 'wc_ast_unclude_tracking_info' );
		
		$order_id = is_callable( array( $order, 'get_id' ) ) ? $order->get_id() : $order->id;
		
		$ast_preview = ( isset( $_REQUEST['action'] ) && 'ast_email_preview' === $_REQUEST['action'] ) ? true : false;
		
		$local_template	= get_stylesheet_directory() . '/woocommerce/emails/fluid-tracking-info.php';
		
		$order = wc_get_order( $order_id );
		
		if ( $ast_preview && 1 == $order_id ) {
			
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
						
			if ( file_exists( $local_template ) && is_writable( $local_template ) ) {	
				wc_get_template( 'emails/fluid-tracking-info.php', array( 'tracking_items' => $tracking_items, 'order_id'=> 1 ), 'woocommerce-advanced-shipment-tracking/', get_stylesheet_directory() . '/woocommerce/' );
			} else {
				wc_get_template( 'emails/fluid-tracking-info.php', array( 'tracking_items' => $tracking_items, 'order_id'=> 1 ), 'woocommerce-advanced-shipment-tracking/', wc_advanced_shipment_tracking()->get_plugin_path() . '/templates/' );	
			}	
			
		} else if ( $order ) {
			$order_status = $order->get_status();			
			
			if ( is_a( $email, 'WC_Email_Customer_Invoice' ) && isset( $wc_ast_unclude_tracking_info['show_in_customer_invoice'] ) && 0 == $wc_ast_unclude_tracking_info['show_in_customer_invoice'] ) {
				return;
			}	
			
			if ( is_a( $email, 'WC_Email_Customer_Note' ) && isset( $wc_ast_unclude_tracking_info['show_in_customer_note'] ) && 0 == $wc_ast_unclude_tracking_info['show_in_customer_note'] ) {
				return;
			}	
				
			if ( isset( $wc_ast_unclude_tracking_info[ $order_status ] ) && 0 == $wc_ast_unclude_tracking_info[ $order_status ] && !is_a( $email, 'WC_Email_Customer_Invoice' ) && !is_a( $email, 'WC_Email_Customer_Note' ) ) {
				return;
			}	
			
			if ( is_a( $email, 'WC_Email_Customer_Refunded_Order' ) && isset( $wc_ast_unclude_tracking_info[ 'refunded' ] ) && 0 == $wc_ast_unclude_tracking_info[ 'refunded' ] ) {
				return;
			}
	
			$tracking_items = $this->get_tracking_items( $order_id, true );
			
			if ( true === $plain_text ) {
				
				if ( file_exists( $local_template ) && is_writable( $local_template ) ) {
					wc_get_template( 
						'emails/plain/fluid-tracking-info.php', 
						array( 
							'tracking_items' => $this->get_tracking_items( $order_id, true ), 
							'order_id'=> $order_id
						)
					);
				} else {
					wc_get_template( 
						'emails/plain/fluid-tracking-info.php',
						array( 
							'tracking_items' => $this->get_tracking_items( $order_id, true ), 
							'order_id'=> $order_id
						), 
						'woocommerce-advanced-shipment-tracking/', 
						wc_advanced_shipment_tracking()->get_plugin_path() . '/templates/' 
					);
				}					
			} else {
				
				if ( file_exists( $local_template ) && is_writable( $local_template ) ) {
					wc_get_template( 
						'emails/fluid-tracking-info.php', 
						array( 
							'tracking_items' => $this->get_tracking_items( $order_id, true ), 
							'order_id'=> $order_id
						)
					);
				} else {
					wc_get_template( 
						'emails/fluid-tracking-info.php', 
						array( 
							'tracking_items' => $this->get_tracking_items( $order_id, true ), 
							'order_id'=> $order_id
						), 
						'woocommerce-advanced-shipment-tracking/', 
						wc_advanced_shipment_tracking()->get_plugin_path() . '/templates/' 
					);	
				}				
			}
		}	
	}		
	
	/**
	 * Prevents data being copied to subscription renewals
	 */
	public function woocommerce_subscriptions_renewal_order_meta_query( $order_meta_query, $original_order_id, $renewal_order_id, $new_order_role ) {
		$order_meta_query .= " AND `meta_key` NOT IN ( '_wc_shipment_tracking_items' )";
		return $order_meta_query;
	}

	/*
	 * Works out the final tracking provider and tracking link and appends then to the returned tracking item
	 *
	*/
	public function get_formatted_tracking_item( $order_id, $tracking_item ) {
		
		$formatted = array();
		$tracking_items = $this->get_tracking_items( $order_id );
		$trackship_supported = '';	
		
		foreach ( $tracking_items as $key => $item ) {
			if ( $item['tracking_id'] == $tracking_item['tracking_id'] ) {
				$shipmet_key = $key;
			}		
		}

		$order = wc_get_order( $order_id );		
		$postcode = $order->get_shipping_postcode();

		$formatted['formatted_tracking_provider'] = '';
		$formatted['formatted_tracking_link']     = '';				
		
		$tracking_provider = isset( $tracking_item['tracking_provider'] ) ? $tracking_item['tracking_provider'] : $tracking_item['custom_tracking_provider'];
		$tracking_provider = apply_filters( 'convert_provider_name_to_slug', $tracking_provider );
		$tracking_item['tracking_provider'] = $tracking_provider;
		
		$link_format = '';
					
		foreach ( $this->get_providers() as $provider => $format ) {
			if ( $provider  === $tracking_item['tracking_provider'] || $format['provider_name']  === $tracking_item['tracking_provider'] ) {
				$link_format = $format['provider_url'];
				$trackship_supported = $format['trackship_supported'];				
				$formatted['formatted_tracking_provider'] = $format['provider_name'];
				break;
			}

			if ( $link_format ) {
				break;
			}
		}
			
		if ( $link_format ) {
			$searchVal = array( '%number%', str_replace( ' ', '', '%2 $ s' ) );
			$tracking_number = $tracking_item['tracking_number'];
			$replaceVal = array( $tracking_number, urlencode( $postcode ) );
			$link_format = str_replace( $searchVal, $replaceVal, $link_format );
			
			if ( isset( $tracking_item[ 'tracking_product_code' ] ) ) {
				$searchnumber2 = array( '%number2%', str_replace(' ', '', '%2 $ s') );
				$tracking_product_code = str_replace(' ', '', $tracking_item['tracking_product_code']);					
				$link_format = str_replace( $searchnumber2, $tracking_product_code, $link_format );
			}
			
			if ( null != $order->get_shipping_country() ) {
				$shipping_country = $order->get_shipping_country();	
			} else {
				$shipping_country = $order->get_billing_country();	
			}								
			
			if ( $shipping_country ) {
				
				if ( 'jp-post' == $tracking_item['tracking_provider'] && 'JP' != $shipping_country ) {
					$local_en = '&locale=en';
					$link_format = $link_format . $local_en;
				}						
				
				if ( 'dhl-ecommerce' == $tracking_item['tracking_provider'] ) {
					$link_format = str_replace('us-en', strtolower($shipping_country) . '-en', $link_format); 	
				}
				
				if ( 'dhl-freight' == $tracking_item['tracking_provider'] ) {
					$link_format = str_replace('global-en', strtolower($shipping_country) . '-en', $link_format);
				}
			}
			
			if ( null != $order->get_shipping_postcode() ) {
				$shipping_postal_code = $order->get_shipping_postcode();	
			} else {
				$shipping_postal_code = $order->get_billing_postcode();
			}							
													
			$shipping_country = str_replace( ' ', '', $shipping_country );					
			$link_format = str_replace( '%country_code%', $shipping_country, $link_format );
													
			if ( 'apc-overnight' == $tracking_item['tracking_provider'] ) {
				$shipping_postal_code = str_replace( ' ', '+', $shipping_postal_code );
			} else {
				$shipping_postal_code = str_replace( ' ', '', $shipping_postal_code );
			}
			
			$link_format = str_replace( '%postal_code%', $shipping_postal_code, $link_format );
								
			$formatted_tracking_link = $link_format;
			$formatted['formatted_tracking_link'] = $link_format;
		} else {
			$formatted_tracking_link = isset( $tracking_item['custom_tracking_link'] ) ? $tracking_item['custom_tracking_link'] : '' ;
			$formatted['formatted_tracking_link'] = $formatted_tracking_link;
		}
		
		$trackship_supported = $this->check_provider_trackship_supported( $tracking_provider );
		$formatted['ast_tracking_link'] = apply_filters( 'ast_tracking_link', $formatted_tracking_link, $tracking_number, $order_id, $trackship_supported );
		
		global $wpdb;
		$results = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM %1s WHERE ts_slug = %s', $this->table, $tracking_item['tracking_provider'] ) );
		$formatted[ 'tracking_provider_image' ] = apply_filters( 'get_shipping_provdider_src', $results ); 	
		
		return $formatted;
	}
	
	public function check_provider_trackship_supported( $tracking_provider ) {
		global $wpdb;		
		$trackship_supported = $wpdb->get_var( $wpdb->prepare( 'SELECT trackship_supported FROM %1s WHERE ts_slug = %s', $this->table, $tracking_provider ) );
		return $trackship_supported;
	}

	/**
	 * Deletes a tracking item from post_meta array
	 *
	 * @param int    $order_id    Order ID
	 * @param string $tracking_id Tracking ID
	 *
	 * @return bool True if tracking item is deleted successfully
	 */
	public function delete_tracking_item( $order_id, $tracking_id ) {
		$tracking_items = $this->get_tracking_items( $order_id );
		$order = wc_get_order( $order_id );
		$is_deleted = false;

		if ( count( $tracking_items ) > 0 ) {
			foreach ( $tracking_items as $key => $item ) {
				if ( $item['tracking_id'] == $tracking_id ) {
					unset( $tracking_items[ $key ] );
					$is_deleted = true;
					do_action( 'fix_shipment_tracking_for_deleted_tracking', $order_id, $key, $item );
					break;
				}
			}
			$this->save_tracking_items( $order_id, $tracking_items );
		}
		
		$tracking_items = $this->get_tracking_items( $order_id );
		
		if ( !$tracking_items ) {
			$order->delete_meta_data( '_wc_shipment_tracking_items' );
			$order->save();
		}	
		
		return $is_deleted;
	}

	/*
	 * Adds a tracking item to the post_meta array
	 *
	 * @param int   $order_id    Order ID
	 * @param array $tracking_items List of tracking item
	 *
	 * @return array Tracking item
	 */
	public function add_tracking_item( $order_id, $args ) {
		$tracking_item = array();
		
		$tracking_item['tracking_provider']			= isset( $args['tracking_provider'] ) ? wc_clean( $args['tracking_provider'] ) : '';
		$tracking_item['custom_tracking_provider']	= isset( $args['custom_tracking_provider'] ) ? wc_clean( $args['custom_tracking_provider'] ) : '';
		$tracking_item['custom_tracking_link']		= isset( $args['custom_tracking_link'] ) ? wc_clean( $args['custom_tracking_link'] ) : '';
		$tracking_item['tracking_number']			= isset( $args['tracking_number'] ) ? wc_clean( $args['tracking_number'] ) : '';
		$tracking_item['tracking_product_code']		= isset( $args['tracking_product_code'] ) ? wc_clean( $args['tracking_product_code'] ) : '';				
		
		if ( strtotime( $args['date_shipped'] ) ) {
			if ( isset( $args['date_shipped'] ) ) {
				
				if ( isset($args['source']) && 'REST_API' == $args['source'] ) {
					$wc_ast_api_date_format = get_option( 'wc_ast_api_date_format', 'd-m-Y' );					
					$date = date_i18n( $wc_ast_api_date_format, strtotime( $args['date_shipped'] ) );				
				} else {									
					$date = date_i18n( 'd-m-Y', strtotime( $args['date_shipped'] ) );
				} 						
			
				$tracking_item['date_shipped'] = wc_clean( strtotime( $date ) );
			}
		} else {
			$tracking_item['date_shipped']   = time();
		}
		
		$tracking_item['products_list']	 = isset( $args['products_list'] ) ? wc_clean( $args['products_list'] ) : '';
		$tracking_item['status_shipped'] = isset( $args['status_shipped'] ) ? wc_clean( $args['status_shipped'] ) : '';		
		
		if ( 0 == (int) $tracking_item['date_shipped'] ) {
			 $tracking_item['date_shipped'] = time();
		}		

		if ( isset($tracking_item['custom_tracking_provider'] )) {
			$tracking_item['tracking_id'] = md5( "{$tracking_item['custom_tracking_provider']}-{$tracking_item['tracking_number']}" . microtime() );
		} else {
			$tracking_item['tracking_id'] = md5( "{$tracking_item['tracking_provider']}-{$tracking_item['tracking_number']}" . microtime() );
		}
		
		$tracking_item = apply_filters( 'tracking_item_args', $tracking_item, $args, $order_id );
		
		$tracking_items = $this->get_tracking_items( $order_id );					

		$tracking_items[] = $tracking_item;													
		
		$status_shipped = ( isset( $tracking_item[ 'status_shipped' ] ) ? $tracking_item[ 'status_shipped' ] : '' );
		
		$this->save_tracking_items( $order_id, $tracking_items );					
		
		$order = new WC_Order( $order_id );
		
		do_action( 'update_order_status_after_adding_tracking', $status_shipped, $order );
		
		$formated_tracking_item = $this->get_formatted_tracking_item( $order_id, $tracking_item );
		$tracking_provider = $formated_tracking_item['formatted_tracking_provider'];								
		
		/* translators: %s: Reaplce with tracking provider, %s: Reaplce with tracking number */
		$note = sprintf( __( 'Order was shipped with %s and tracking number is: %s', 'woo-advanced-shipment-tracking' ), $tracking_provider, $tracking_item['tracking_number'] );
		
		// Add the note
		$order->add_order_note( $note );
		
		return $tracking_item;
	}
	
	public function seach_tracking_number_in_items( $tracking_number, $tracking_items ) {
		foreach ( $tracking_items as $key => $val ) {
			if ( $val['tracking_number'] === $tracking_number ) {
				return $key;
			}
		}
		return null;
	}
	
	/*
	 * Adds a tracking item to the post_meta array from external system programatticaly
	 *
	 * @param int   $order_id    Order ID
	 * @param array $tracking_items List of tracking item
	 *
	 * @return array Tracking item
	 */
	public function insert_tracking_item( $order_id, $args ) {
		$tracking_item = array();
		$tracking_provider = $args['tracking_provider'];				
		
		$ast_admin = WC_Advanced_Shipment_Tracking_Admin::get_instance();
		$shippment_provider = $ast_admin->get_provider_slug_from_name( $tracking_provider );	
		
		if ( $args['tracking_provider'] && !empty( $shippment_provider ) ) {
			$tracking_item['tracking_provider'] = wc_clean ( $shippment_provider );
		} else if ( $args['tracking_provider'] ) {
			$tracking_item['tracking_provider'] = $args['tracking_provider'];
		} 
		
		if ( $args['tracking_number'] ) {
			$tracking_item['tracking_number'] = wc_clean( $args['tracking_number'] );
		}
		
		if ( $args['date_shipped'] ) {
			$date = str_replace( '/', '-', $args['date_shipped'] );
			$date = date_create($date);
			$date = date_format( $date, 'd-m-Y' );
		
			$tracking_item['date_shipped'] = wc_clean( strtotime( $date ) );
		}
		
		if ( $args['status_shipped'] ) {
			$tracking_item['status_shipped'] = wc_clean( $args['status_shipped'] );
		}
		
		if ( 0 == (int) $tracking_item['date_shipped'] ) {
			 $tracking_item['date_shipped'] = time();
		}

		$tracking_item['tracking_id'] = md5( "{$tracking_item['tracking_provider']}-{$tracking_item['tracking_number']}" . microtime() );

		$tracking_items   = $this->get_tracking_items( $order_id );
		$tracking_items[] = $tracking_item;
		
		if ( $tracking_item['tracking_provider'] ) {
			$this->save_tracking_items( $order_id, $tracking_items );
			
			$status_shipped = ( isset( $tracking_item['status_shipped'] ) ? $tracking_item['status_shipped'] : '' );				
		
			$order = new WC_Order( $order_id );
			
			do_action( 'update_order_status_after_adding_tracking', $status_shipped, $order );
			
			$formated_tracking_item = $this->get_formatted_tracking_item( $order_id, $tracking_item );
			$tracking_provider = $formated_tracking_item['formatted_tracking_provider'];			
			
			/* translators: %1$s: Reaplce with tracking provider, %2$s: Reaplce with tracking number */
			$note = sprintf( __( 'Order was shipped with %1$s and tracking number is: %2$s', 'woo-advanced-shipment-tracking' ), $tracking_provider, $tracking_item['tracking_number'] );
			
			// Add the note
			$order->add_order_note( $note );	
			
			return $tracking_item;
		}				
	}
	
	

	/**
	 * Saves the tracking items array to post_meta.
	 *
	 * @param int   $order_id       Order ID
	 * @param array $tracking_items List of tracking item
	 */
	public function save_tracking_items( $order_id, $tracking_items ) {
		$order = wc_get_order( $order_id );
		// Always re-index the array
		$tracking_items = array_values( $tracking_items );			
		$order->update_meta_data( '_wc_shipment_tracking_items', $tracking_items );
		$order->save();
	}

	/**
	 * Gets a single tracking item from the post_meta array for an order.
	 *
	 * @param int    $order_id    Order ID
	 * @param string $tracking_id Tracking ID
	 * @param bool   $formatted   Wether or not to reslove the final tracking
	 *                            link and provider in the returned tracking item.
	 *                            Default to false.
	 *
	 * @return null|array Null if not found, otherwise array of tracking item will be returned
	 */
	public function get_tracking_item( $order_id, $tracking_id, $formatted = false ) {
		$tracking_items = $this->get_tracking_items( $order_id, $formatted );

		if ( count( $tracking_items ) ) {
			foreach ( $tracking_items as $item ) {
				if ( $item['tracking_id'] === $tracking_id ) {
					return $item;
				}
			}
		}
		return null;
	}

	/*
	 * Gets all tracking itesm fron the post meta array for an order
	 *
	 * @param int  $order_id  Order ID
	 * @param bool $formatted Wether or not to reslove the final tracking link
	 *                        and provider in the returned tracking item.
	 *                        Default to false.
	 *
	 * @return array List of tracking items
	 */
	public function get_tracking_items( $order_id, $formatted = false ) {
		
		global $wpdb;
		$order = wc_get_order( $order_id );			
		if ( $order ) {	
			
			$tracking_items = $order->get_meta( '_wc_shipment_tracking_items', true );

			if ( is_array( $tracking_items ) ) {
				if ( $formatted ) {
					foreach ( $tracking_items as &$item ) {
						$formatted_item = $this->get_formatted_tracking_item( $order_id, $item );
						$item           = array_merge( $item, $formatted_item );
					}
				}
				return $tracking_items;
			} else {
				return array();
			}
		} else {
			return array();
		}
	}

	/**
	* Gets the absolute plugin path without a trailing slash, e.g.
	* /path/to/wp-content/plugins/plugin-directory
	*
	* @return string plugin path
	*/
	public function get_plugin_path() {
		$this->plugin_path = untrailingslashit( plugin_dir_path( dirname( __FILE__ ) ) );
		return $this->plugin_path;
	}	
	
	/**
	 * Validation code add tracking info form
	*/
	public function custom_validation_js() {
		?>
		<script>
		jQuery(document).on("click",".button-save-form",function(e){			
			var error;
			var tracking_provider = jQuery("#tracking_provider");	
			var tracking_number = jQuery("#tracking_number");				
			
			if(tracking_provider.val() == '' ){				
				jQuery( "#select2-tracking_provider-container" ).closest( ".select2-selection" ).css( "border-color", "red" );
				error = true;
			} else {
				jQuery( "#select2-tracking_provider-container" ).closest( ".select2-selection" ).css( "border-color", "" );
			}
			if(tracking_number.val() == '' ){				
				tracking_number.css( "border-color", "red" );
				error = true;
			} else {
				var pattern = /^[0-9a-zA-Z- \b]+$/;	
				if(!pattern.test(tracking_number.val())){			
					tracking_number.css( "border-color", "red" );
					error = true;
				} else{
					tracking_number.css( "border-color", "" );
				}								
			}
						
			if(error == true){
				return false;
			}
		});		
		</script>
	<?php 
	}
	
	/*
	* Get formated order id
	*/
	public function get_formated_order_id( $order_id ) {
		
		if ( is_plugin_active( 'custom-order-numbers-for-woocommerce/custom-order-numbers-for-woocommerce.php' ) ) {
			$alg_wc_custom_order_numbers_enabled = get_option( 'alg_wc_custom_order_numbers_enabled' );
			$alg_wc_custom_order_numbers_prefix  = get_option( 'alg_wc_custom_order_numbers_prefix' );
			$new_order_id = str_replace( $alg_wc_custom_order_numbers_prefix, '', $order_id );
						
			if ( 'yes' == $alg_wc_custom_order_numbers_enabled ) {
				$args = array(
					'post_type'		=>	'shop_order',			
					'posts_per_page'    => '1',
					'meta_query'        => array(
						'relation' => 'AND', 
						array(
						'key'       => '_alg_wc_custom_order_number',
						'value'     => $new_order_id,
						),
					),
					'post_status' => array_keys( wc_get_order_statuses() ) , 	
				);
				$posts = get_posts( $args );
				$my_query = new WP_Query( $args );				
				
				if ( $my_query->have_posts() ) {
					while ( $my_query->have_posts()) {
						$my_query->the_post();
						if ( get_the_ID() ) {
							$order_id = get_the_ID();
						}									
					} // end while
				} // end if
				$order_id;
				wp_reset_postdata();	
			}			
		}		
		
		if ( is_plugin_active( 'woocommerce-sequential-order-numbers/woocommerce-sequential-order-numbers.php' ) ) {
						
			$s_order_id = wc_sequential_order_numbers()->find_order_by_order_number( $order_id );			
			if ( $s_order_id ) {
				$order_id = $s_order_id;
			}
		}
		
		if ( is_plugin_active( 'woocommerce-sequential-order-numbers-pro/woocommerce-sequential-order-numbers-pro.php' ) ) {
			
			// search for the order by custom order number
			$query_args = array(
				'numberposts' => 1,
				'meta_key'    => '_order_number_formatted',
				'meta_value'  => $order_id,
				'post_type'   => 'shop_order',
				'post_status' => 'any',
				'fields'      => 'ids',
			);
			
			$posts = get_posts( $query_args );			
			if ( !empty( $posts ) ) {
				list( $order_id ) = $posts;			
			}			
		}
		
		if ( is_plugin_active( 'woocommerce-jetpack/woocommerce-jetpack.php' ) ) {
			
			$wcj_order_numbers_enabled = get_option( 'wcj_order_numbers_enabled' );
			// Get prefix and suffix options
			$prefix = do_shortcode( get_option( 'wcj_order_number_prefix', '' ) );
			$prefix .= date_i18n( get_option( 'wcj_order_number_date_prefix', '' ) );
			$suffix = do_shortcode( get_option( 'wcj_order_number_suffix', '' ) );
			$suffix .= date_i18n( get_option( 'wcj_order_number_date_suffix', '' ) );
	
			// Ignore suffix and prefix from search input
			$search_no_suffix            = preg_replace( "/\A{$prefix}/i", '', $order_id );
			$search_no_suffix_and_prefix = preg_replace( "/{$suffix}\z/i", '', $search_no_suffix );
			$final_search                = empty( $search_no_suffix_and_prefix ) ? $search : $search_no_suffix_and_prefix;	
			
			if ( 'yes' == $wcj_order_numbers_enabled ) {
				$query_args = array(
					'numberposts' => 1,
					'meta_key'    => '_wcj_order_number',
					'meta_value'  => $final_search,
					'post_type'   => 'shop_order',
					'post_status' => 'any',
					'fields'      => 'ids',
				);
				
				$posts = get_posts( $query_args );
				if ( !empty( $posts ) ) {	
					list( $order_id ) = $posts;			
				}			
			}
		}
		
		if ( is_plugin_active( 'wp-lister-amazon/wp-lister-amazon.php' ) ) {
			$wpla_use_amazon_order_number = get_option( 'wpla_use_amazon_order_number' );
			if ( 1 == $wpla_use_amazon_order_number ) {
				$query_args = array(
					'numberposts' => 1,
					'meta_key'    => '_wpla_amazon_order_id',
					'meta_value'  => $order_id,
					'post_type'   => 'shop_order',
					'post_status' => 'any',
					'fields'      => 'ids',
				);
				
				$posts = get_posts( $query_args );			
				if ( !empty( $posts ) ) {	
					list( $order_id ) = $posts;			
				}	
			}			
		}	
		
		if ( is_plugin_active( 'wp-lister/wp-lister.php' ) || is_plugin_active( 'wp-lister-for-ebay/wp-lister.php' ) ) {
			$args = array(
				'post_type'		=>	'shop_order',			
				'posts_per_page'    => '1',
				'meta_query'        => array(
					'relation' => 'OR', 
					array(
						'key'       => '_ebay_extended_order_id',
						'value'     => $order_id
					),
					array(
						'key'       => '_ebay_order_id',
						'value'     => $order_id
					),					
				),	
				'post_status' => 'any',	
			);
			
			$posts = get_posts( $args );
			$my_query = new WP_Query( $args );				
			
			if ( $my_query->have_posts() ) {
				while ( $my_query->have_posts() ) {
					$my_query->the_post();
					if ( get_the_ID() ) {
						$order_id = get_the_ID();
					}									
				} // end while
			} // end if
			wp_reset_postdata();
		}
		
		if ( is_plugin_active( 'yith-woocommerce-sequential-order-number-premium/init.php' ) ) {
			$query_args = array(
				'numberposts' => 1,
				'meta_key'    => '_ywson_custom_number_order_complete',
				'meta_value'  => $order_id,
				'post_type'   => 'shop_order',
				'post_status' => 'any',
				'fields'      => 'ids',
			);
			
			$posts = get_posts( $query_args );			
			if ( !empty( $posts ) ) {	
				list( $order_id ) = $posts;			
			}	
		}
		
		if ( is_plugin_active( 'wt-woocommerce-sequential-order-numbers/wt-advanced-order-number.php' ) ) {						
			$query_args = array(
				'numberposts' => 1,
				'meta_key'    => '_order_number',
				'meta_value'  => $order_id,
				'post_type'   => 'shop_order',
				'post_status' => 'any',
				'fields'      => 'ids',
			);
			
			$posts = get_posts( $query_args );			
			if ( !empty( $posts ) ) {
				list( $order_id ) = $posts;			
			}			
		}
		
		return apply_filters( 'ast_formated_order_id', $order_id );
	}
	
	/*
	* Return option value for customizer
	*/
	public function get_option_value_from_array( $array, $key, $default_value ) {
		
		$array_data = get_option( $array );	
		$value = '';
		
		if ( isset( $array_data[ $key ] ) ) {
			$value = $array_data[ $key ];		
			if ( '' != $value ) {
				return $value;
			}	
		}							
		
		if ( '' == $value ) {
			$value = $default_value;
		}	
				
		return $value;
	}
	
	/*
	* Return checkbox option value for customizer
	*/
	public function get_checkbox_option_value_from_array( $array, $key, $default_value ) {		
		
		$array_data = get_option( $array );	
		$value = '';
		
		if ( isset( $array_data[ $key ] ) ) {
			$value = $array_data[ $key ];
			return $value;
		}							
		
		if ( '' == $value ) {
			$value = $default_value;
		}	
				
		return $value;
	}
			
}
