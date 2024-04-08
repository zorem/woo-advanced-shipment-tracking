<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AST_Integration {
	
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
		$this->init();	
	}
	
	/**
	 * Get the class instance
	 *
	 * @return AST_Pro_Admin
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
		add_action( 'wp_ajax_integration_settings_slideout', array( $this, 'integration_settings_slideout' ) );
	}

	public function integration_settings_slideout() {
		check_ajax_referer( 'integrations_settings_form', 'security' );		
		$integration_id = isset( $_POST['integration_id'] ) ? wc_clean( $_POST['integration_id'] ) : '';
		$integration_array = $this->integrations_settings_options();
		$integration_data = $integration_array[$integration_id];		
		$documentation = isset( $integration_data['documentation'] ) ? $integration_data['documentation'] : null ;				
		?>		
		<div class="slidout_header">				
			<div class="slidout_header_title">
				<div class="grid-top">        
					<div class="grid-provider-img">
						<img class="provider-thumb" src="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ); ?>assets/images/<?php esc_html_e( $integration_data['img'] ); ?>">
					</div>
					<div class="integration-name">
						<div><?php esc_html_e( $integration_data['title'] ); ?></div>
						<a href="<?php echo esc_url( $documentation ); ?>" class="slideout-doc-link" target="_blank"><?php esc_html_e( 'More Info', 'woo-advanced-shipment-tracking' ); ?></a>
					</div>
				</div>
			</div>	
			<div class="slidout_header_action">
				<span class="dashicons dashicons-no-alt integration_slidout_close slidout_close"></span>
			</div>
		</div>
		<div class="slidout_body">
			<form id="integration_settings_popup_form" method="POST" class="">
				
				<div class="integration-enable">
					<input type="hidden" name="ast-integration-toggle" value="0">
					<input class="ast-toggle" id="ast-integration-toggle" name="ast-integration-toggle" type="checkbox" value="1" disabled/>						
					<label class="slideout-integration-tgl-lbl" for="ast-integration-toggle"><span><?php esc_html_e( 'Enable Integration', 'woo-advanced-shipment-tracking' ); ?></span></label>
				</div>
				<div class="get_feature_container">
					<a href="https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/?utm_source=wp-admin&utm_medium=plugin-setting&utm_campaign=upgrad-to-pro" target="_blank"><span class="get_feature_span">Get Feature</span></a>
					<div><a href="https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/?utm_source=wp-admin&utm_medium=plugin-setting&utm_campaign=upgrad-to-pro" target="_blank">The Advanced Shipment Tracking Pro</a> streamlines fulfillment by integrating with shipping services. Typically, these services use WooCommerce REST API or a plugin to import processing orders to their dashboards. When you generate a shipping label, they update tracking information via the order notes API endpoint, but this doesn't add it to the shipment tracking panel or display it in the shipping confirmation email. With our integrations, tracking info is automatically added to the shipment tracking order meta when labels are generated, and orders are completed, eliminating manual effort.</div>
				</div>				
			</form>
		</div>		
		<?php
		exit;
	}
	/*
	* functions for add integrations options in AST settings
	*/
	public function integrations_settings_options() {
		$form_data = array(		
			'enable_ordoro_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'Ordoro', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'ordoro-icon.png',				
				'class'     => '',
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/ast-pro/integrations/ordoro/',
			),
			'enable_cartrover_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'CartRover', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'cart-rover-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/ast-pro/integrations/cartrover/',
			),
			'enable_parcelforce_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'ParcelForce', 'woo-advanced-shipment-tracking' ),		
				'img'		=> 'parcelfoce-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/integrations/parcelforce/',
			),			
			'enable_zenventory_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'Zenventory', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'zenventory-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/integrations/zenventory/',
			),
			'enable_jtl_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'JTL-Connector', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'jtl-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/integrations/jtl-connector/',
			),
			'enable_shipstation_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'ShipStation', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'shipstation-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/ast-pro/integrations/shipstation/',
			),
			'enable_wc_shipping_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'WC Shipping', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'woo-shipping-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/ast-pro/integrations/woocommerce-shipping-tracking-add-on/',
			),
			'enable_ups_shipping_label_pluginhive' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'WooCommerce UPS Shipping', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'woo-ups-shipping-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/ast-pro/integrations/woocommerce-ups-shipping/',
			),
			'enable_canada_post_shipping_label_pluginhive' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'WooCommerce Canada Post Shipping', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'woo-ups-shipping-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/integrations/woocommerce-canada-post-shipping/',
			),	
			'enable_dhl_for_woocommerce_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'DHL Shipping Germany for WooCommerce', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'dhl-for-wc.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/integrations/dhl-shipping-germany-for-woocommerce/',
			),		
			'enable_quickbooks_commerce_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'QuickBooks Commerce (formerly TradeGecko)', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'quickbooks-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/ast-pro/integrations/quickbooks-commerce-tracking/',
			),
			'enable_readytoship_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'ReadyToShip', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'readytoship-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/ast-pro/integrations/readytoship/',
			),
			'enable_royalmail_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'Royal Mail Click & Drop', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'royal-mail-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/ast-pro/integrations/royal-mail-click-drop/',
			),	
			'enable_customcat_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'CustomCat', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'customcat-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/ast-pro/integrations/customcat/',
			),
			'enable_dear_inventory_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'Dear Systems', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'dear-system-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/ast-pro/integrations/dear-systems/',
			),
			'enable_picqer_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'Picqer', 'woo-advanced-shipment-tracking' ),			
				'img'		=> 'picqer-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/ast-pro/integrations/picqer/',
			),
			'enable_3plwinner_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( '3plwinner', 'woo-advanced-shipment-tracking' ),				
				'img'		=> '3plwinner-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/ast-pro/integrations/3plwinner/',
			),			
			'enable_dianxiaomi_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'Dianxiaomi', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'dianxiaomi-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/ast-pro/integrations/dianxiaomi/',
			),
			'enable_eiz_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'EIZ', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'eiz-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/ast-pro/integrations/ebiz/',
			),
			'enable_shippypro_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'Shippypro', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'shippypro-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/ast-pro/integrations/shippypro/',
			),
			'enable_ali2woo_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'AliExpress Dropshipping', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'aliexpress-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/ast-pro/integrations/ali2woo/',
			),
			'enable_pirateship_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'Pirate Ship', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'pirateship-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/ast-pro/integrations/pirate-ship/',
			),	
			'enable_sendcloud_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'Sendcloud', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'sendcloud-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/ast-pro/integrations/sendcloud/',
			),
			'enable_shiptheory_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'Shiptheory', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'shiptheory-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/ast-pro/integrations/shiptheory/',
			),
			'enable_stamps_com_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'Stamps.com', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'stamps-com-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/integrations/stamps-com/',
			),
			'enable_shippo_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'Shippo', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'shippo-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/integrations/shippo/',
			),
			'enable_inventory_source_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'Inventory source', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'inventory-source-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/ast-pro/integrations/inventory-source/',
			),
			'enable_gls_sell_send_italy_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'GLS Sell & Send Italy', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'gls.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/integrations/gls-sell-send-italy/',
			),
			'enable_gls_deliveryfrom_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'Print Label and Tracking Code for GLS', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'gls.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/integrations/print-label-and-tracking-code-for-gls/',
			),
			'enable_printful_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'Printful', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'printful-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/integrations/printful/',
			),
			'enable_byrd_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'Byrd Fulfillment', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'byrd-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/integrations/byrd/',
			),
			'enable_shirtee_cloud_integration' => array(
				'type'		=> 'tgl_checkbox',
				'title'		=> __( 'Shirtee Cloud', 'woo-advanced-shipment-tracking' ),				
				'img'		=> 'shirtee-cloud-icon.png',				
				'documentation' => 'https://docs.zorem.com/docs/ast-pro/integrations/shirtee-cloud/',
			),
		);
		
		return $form_data;
	}
}
