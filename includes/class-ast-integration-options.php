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

	public function astfree_integration_settings( $title, $img, $doc_url) {
		return [
			'title'	=> $title,
			'img'	=> $img,
			'documentation'	=> 'https://docs.zorem.com/docs/ast-pro/integrations/' . $doc_url,
		];
	}
	
	/*
	* functions for add integrations options in AST settings
	*/
	public function integrations_settings_options() {
		 // Assuming this returns a callable function or object with __invoke()
		$fun = function( $title, $img, $doc_url) {
			return $this->astfree_integration_settings( $title, $img, $doc_url );
		};
		$form_data = array(
			'ordoro'					=> $fun( __( 'Ordoro', 'woo-advanced-shipment-tracking' ), 'ordoro-icon.png', 'ordoro/'),
			'extensiv'					=> $fun( __( 'Extensiv', 'woo-advanced-shipment-tracking' ), 'extensiv-icon.png', 'extensiv/' ),
			'cartrover'					=> $fun( __( 'CartRover', 'woo-advanced-shipment-tracking' ), 'cart-rover-icon.png', 'cartrover/' ),
			'parcelforce'				=> $fun( __( 'ParcelForce', 'woo-advanced-shipment-tracking' ), 'parcelfoce-icon.png', 'parcelforce/' ),
			'zenventory'				=> $fun( __( 'Zenventory', 'woo-advanced-shipment-tracking' ), 'zenventory-icon.png', 'zenventory/' ),
			'jtl'						=> $fun( __( 'JTL-Connector', 'woo-advanced-shipment-tracking' ), 'jtl-icon.png', 'jtl-connector/' ),
			'shipstation'				=> $fun( __( 'ShipStation', 'woo-advanced-shipment-tracking' ), 'shipstation-icon.png', 'shipstation/' ),
			'wc_shipping'				=> $fun( __( 'WC Shipping', 'woo-advanced-shipment-tracking' ), 'woo-shipping-icon.png', 'woocommerce-shipping-tracking-add-on/' ),
			'ups_shipping'				=> $fun( __( 'WooCommerce UPS Shipping', 'woo-advanced-shipment-tracking' ), 'woo-ups-shipping-icon.png', 'woocommerce-ups-shipping/' ),
			'canada_post'				=> $fun( __( 'WooCommerce Canada Post Shipping', 'woo-advanced-shipment-tracking' ), 'woo-ups-shipping-icon.png', 'woocommerce-canada-post-shipping/' ),
			'wc_shipping_PluginHive'	=> $fun( __( 'WooCommerce Shipping Services by PluginHive', 'woo-advanced-shipment-tracking' ), 'wc_pluginhive-icon.png', 'wc_pluginhive/' ),
			'dhl_for_woocommerce'		=> $fun( __( 'DHL Shipping Germany for WooCommerce', 'woo-advanced-shipment-tracking' ), 'dhl-for-wc.png', 'dhl-shipping-germany-for-woocommerce/'),
			'quickbooks_commerce'		=> $fun( __( 'QuickBooks Commerce (formerly TradeGecko)', 'woo-advanced-shipment-tracking' ), 'quickbooks-icon.png', 'quickbooks-commerce-tracking/' ),
			'readytoship'				=> $fun( __( 'ReadyToShip', 'woo-advanced-shipment-tracking' ), 'readytoship-icon.png', 'readytoship/' ),
			'royalmail'					=> $fun( __( 'Royal Mail Click & Drop', 'woo-advanced-shipment-tracking' ), 'royal-mail-icon.png', 'royal-mail-click-drop/' ),
			'customcat'					=> $fun( __( 'CustomCat', 'woo-advanced-shipment-tracking' ), 'customcat-icon.png', 'customcat/' ),
			'dear_inventory'			=> $fun( __( 'Dear Systems', 'woo-advanced-shipment-tracking' ), 'dear-system-icon.png', 'dear-systems/' ),
			'picqer'					=> $fun( __( 'Picqer', 'woo-advanced-shipment-tracking' ), 'picqer-icon.png', 'picqer/' ),
			'3plwinner'					=> $fun( __( '3plwinner', 'woo-advanced-shipment-tracking' ), '3plwinner-icon.png', '3plwinner/' ),
			'dianxiaomi'				=> $fun( __( 'Dianxiaomi', 'woo-advanced-shipment-tracking' ), 'dianxiaomi-icon.png', 'dianxiaomi/' ),
			'eiz'						=> $fun( __( 'EIZ', 'woo-advanced-shipment-tracking' ), 'eiz-icon.png', 'ebiz/' ),
			'shippypro'					=> $fun( __( 'Shippypro', 'woo-advanced-shipment-tracking' ), 'shippypro-icon.png', 'shippypro/' ),
			'ali2woo'					=> $fun( __( 'AliExpress Dropshipping', 'woo-advanced-shipment-tracking' ), 'aliexpress-icon.png', 'ali2woo/' ),
			'pirateship'				=> $fun( __( 'Pirate Ship', 'woo-advanced-shipment-tracking' ), 'pirateship-icon.png', 'pirate-ship/' ),
			'sendcloud'					=> $fun( __( 'Sendcloud', 'woo-advanced-shipment-tracking' ), 'sendcloud-icon.png', 'sendcloud/' ),
			'shiptheory'				=> $fun( __( 'Shiptheory', 'woo-advanced-shipment-tracking' ), 'shiptheory-icon.png', 'shiptheory/' ),
			'stamps_com'				=> $fun( __( 'Stamps.com', 'woo-advanced-shipment-tracking' ), 'stamps-com-icon.png', 'stamps-com/' ),
			'chitchats'					=> $fun( __( 'Chit Chats', 'woo-advanced-shipment-tracking' ), 'chitchats-icon.png', 'chit-chats/' ),
			'dripshipper'				=> $fun( __( 'Dripshipper', 'woo-advanced-shipment-tracking' ), 'dripshipper-icon.png', 'dripshipper/' ),
			'shippo'					=> $fun( __( 'Shippo', 'woo-advanced-shipment-tracking' ), 'shippo-icon.png', 'shippo/' ),
			'inventory_source'			=> $fun( __( 'Inventory source', 'woo-advanced-shipment-tracking' ), 'inventory-source-icon.png', 'inventory-source/' ),
			'gls_sell_send_italy'		=> $fun( __( 'GLS Sell & Send Italy', 'woo-advanced-shipment-tracking' ), 'gls.png', 'gls-sell-send-italy/' ),
			'gls_deliveryfrom'			=> $fun( __( 'Print Label and Tracking Code for GLS', 'woo-advanced-shipment-tracking' ), 'gls.png', 'print-label-and-tracking-code-for-gls/' ),
			'printful'					=> $fun( __( 'Printful', 'woo-advanced-shipment-tracking' ), 'printful-icon.png', 'printful/' ),
			'byrd'						=> $fun( __( 'Byrd Fulfillment', 'woo-advanced-shipment-tracking' ), 'byrd-icon.png', 'byrd/' ),
			'shirtee_cloud'				=> $fun( __( 'Shirtee Cloud', 'woo-advanced-shipment-tracking' ), 'shirtee-cloud-icon.png', 'shirtee-cloud/' ),
			'qapla'						=> $fun( __( 'Qapla', 'woo-advanced-shipment-tracking' ), 'qapla-icon.png', 'qapla/' ),
			'shiptime'					=> $fun( __( 'Shiptime', 'woo-advanced-shipment-tracking' ), 'shiptime-icon.png', 'shiptime/' ),
			'eshipper'					=> $fun( __( 'eShipper', 'woo-advanced-shipment-tracking' ), 'eshipper-icon.png', 'eshipper/' ),
			'linnworks'					=> $fun( __( 'Linnworks', 'woo-advanced-shipment-tracking' ), 'linnworks-icon.png', 'linnworks/' ),
			'simplesell'				=> $fun( __( 'SimpleSell', 'woo-advanced-shipment-tracking' ), 'simplesell-icon.png', 'simplesell/' ),
			'easypost'					=> $fun( __( 'EasyPost', 'woo-advanced-shipment-tracking' ), 'easypost.png', 'easypost/' ),
			'interparcel'				=> $fun( __( 'Interparcel', 'woo-advanced-shipment-tracking' ), 'interparcel.png', 'interparcel/' ),
			'sendle'					=> $fun( __( 'Sendle', 'woo-advanced-shipment-tracking' ), 'sendle.png', 'sendle/' ),
			'tehster'					=> $fun( __( 'WooCommerce GLS plugin by Tehster', 'woo-advanced-shipment-tracking' ), 'tehster.png', 'woocommerce-gls-plugin-by-tehster/' ),
			'germanized_vendidero'		=> $fun( __( 'WooCommerce Germanized plugin by Vendidero', 'woo-advanced-shipment-tracking' ), 'woocommerce-germanized.png', 'woocommerce-germanized-plugin-by-vendidero/' ),
			'netsuite'					=> $fun( __( 'NetSuite Connector', 'woo-advanced-shipment-tracking' ), 'netsuite.png', 'netsuite/' ),
			'zappy'						=> $fun( __( 'Zappy', 'woo-advanced-shipment-tracking' ), 'zappy.png', 'zappy/' ),
			'shiphero'					=> $fun( __( 'ShipHero', 'woo-advanced-shipment-tracking' ), 'shiphero.png', 'shiphero/' ),
			'csg'						=> $fun( __( 'CSG 3PL', 'woo-advanced-shipment-tracking' ), 'csg-icon.png', 'csg-3pl/' ),
			'parcel2go'					=> $fun( __( 'Parcel2go', 'woo-advanced-shipment-tracking' ), 'parcel2go-icon.png', 'parcel2go/' ),
			'canadian_machool'			=> $fun( __( 'Canadian Machool', 'woo-advanced-shipment-tracking' ), 'canadian-machool-icon.png', 'canadian-machool/' ),
			'extenda_retail'			=> $fun( __( 'Extenda Retail', 'woo-advanced-shipment-tracking' ), 'extenda-retail-icon.jpg', 'extenda-retail/' ),
			'bigseller'					=> $fun( __( 'BigSeller', 'woo-advanced-shipment-tracking' ), 'bigseller-icon.png', 'bigseller/' ),
			'monta'						=> $fun( __( 'Monta', 'woo-advanced-shipment-tracking' ), 'monta-icon.png', 'monta/' ),
			'despach_cloud'				=> $fun( __( 'Despach Cloud', 'woo-advanced-shipment-tracking' ), 'despach-cloud-icon.png', 'despach-cloud/' ),
			'starshipit'				=> $fun( __( 'Starshipit', 'woo-advanced-shipment-tracking' ), 'starshipit-icon.png', 'starshipit/' ),
			'shiprush'					=> $fun( __( 'Shiprush', 'woo-advanced-shipment-tracking' ), 'shiprush-icon.png', 'shiprush/' ),
			'easyship'					=> $fun( __( 'Easyship', 'woo-advanced-shipment-tracking' ), 'easyship-icon.png', 'easyship/' ),
			'fedex'						=> $fun( __( 'FedEx', 'woo-advanced-shipment-tracking' ), 'fedex-icon.png', 'fedex/' ),
			'jj_global'					=> $fun( __( 'J&J Global Fulfillment', 'woo-advanced-shipment-tracking' ), 'jj_global-icon.png', 'jj_global/' ),
			'shipping_easy'				=> $fun( __( 'ShippingEasy', 'woo-advanced-shipment-tracking' ), 'shipping_easy-icon.png', 'shipping-easy/' ),
			'podpartner'				=> $fun( __( 'PODpartner', 'woo-advanced-shipment-tracking' ), 'podpartner-icon.png', 'podpartner/' ),
			'ups_ecommerce_dashboard'	=> $fun( __( 'UPS Ecommerce Dashboard', 'woo-advanced-shipment-tracking' ), 'ups-ecommerce-dashboard-icon.png', 'ups-ecommerce-dashboard/' ),
			'shippit'					=> $fun( __( 'shippit', 'woo-advanced-shipment-tracking' ), 'shippit-icon.png', 'shippit/' ),
			'boostmyshop'				=> $fun( __( 'BoostMyShop', 'woo-advanced-shipment-tracking' ), 'boostmyshop-icon.png', 'boostmyshop/' ),
			'dpd_shipping_label'		=> $fun( __( 'DPD Shipping Label', 'woo-advanced-shipment-tracking' ), 'dpd-sl-icon.png', 'dpd-shipping-label/' ),
			'flagship'					=> $fun( __( 'FlagShip', 'woo-advanced-shipment-tracking' ), 'flagship-icon.png', 'flagship/' ),
			'flxpoint'					=> $fun( __( 'Flxpoint', 'woo-advanced-shipment-tracking' ), 'flxpoint-icon.png', 'flxpoint/' ),
			'bosta'						=> $fun( __( 'Bosta', 'woo-advanced-shipment-tracking' ), 'bosta-icon.png', 'bosta/' ),
			'the_courier_guy'			=> $fun( __( 'The Courier Guy', 'woo-advanced-shipment-tracking' ), 'the-courier-guy-icon.png', 'the-courier-guy/' ),
			'colissimo'					=> $fun( __( 'Colissimo', 'woo-advanced-shipment-tracking' ), 'colissimo.png', 'colissimo-shipping-methods-for-woocommerce/' ),
			'mondial_relay'				=> $fun( __( 'Mondial Relay (InPost)', 'woo-advanced-shipment-tracking' ), 'mondial-relay-icon.png', 'mondial-relay/' ),
			'transglobal_express'		=> $fun( __( 'Transglobal Express', 'woo-advanced-shipment-tracking' ), 'transglobal-express.png', 'transglobal-express/' ),
			'delhivery'					=> $fun( __( 'Delhivery', 'woo-advanced-shipment-tracking' ), 'delhivery.png', 'delhivery/' ),
			'shiprocket'				=> $fun( __( 'Shiprocket', 'woo-advanced-shipment-tracking' ), 'shiprocket.png', 'shiprocket/' ),
			'veeqo'						=> $fun( __( 'Veeqo', 'woo-advanced-shipment-tracking' ), 'veeqo-icon.png', 'veeqo/' ),
			'swisspost'					=> $fun( __( 'Swiss Post', 'woo-advanced-shipment-tracking' ), 'swisspost-icon.png', 'swisspost/' ),
		);
		
		return $form_data;
	}
}
