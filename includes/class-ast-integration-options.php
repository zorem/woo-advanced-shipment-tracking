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
		$text_domin = 'woo-advanced-shipment-tracking';
		 // Assuming this returns a callable function or object with __invoke()
		$fun = function( $title, $img, $doc_url) {
			return $this->astfree_integration_settings( $title, $img, $doc_url );
		};
		$form_data = array(
			'ordoro'					=> $fun( __( 'Ordoro', $text_domin ), 'ordoro-icon.png', 'ordoro/'),
			'cartrover'					=> $fun( __( 'CartRover', $text_domin ), 'cart-rover-icon.png', 'cartrover/' ),
			'parcelforce'				=> $fun( __( 'ParcelForce', $text_domin ), 'parcelfoce-icon.png', 'parcelforce/' ),
			'zenventory'				=> $fun( __( 'Zenventory', $text_domin ), 'zenventory-icon.png', 'zenventory/' ),
			'jtl'						=> $fun( __( 'JTL-Connector', $text_domin ), 'jtl-icon.png', 'jtl-connector/' ),
			'shipstation'				=> $fun( __( 'ShipStation', $text_domin ), 'shipstation-icon.png', 'shipstation/' ),
			'wc_shipping'				=> $fun( __( 'WC Shipping', $text_domin ), 'woo-shipping-icon.png', 'woocommerce-shipping-tracking-add-on/' ),
			'ups_shipping'				=> $fun( __( 'WooCommerce UPS Shipping', $text_domin ), 'woo-ups-shipping-icon.png', 'woocommerce-ups-shipping/' ),
			'canada_post'				=> $fun( __( 'WooCommerce Canada Post Shipping', $text_domin ), 'woo-ups-shipping-icon.png', 'woocommerce-canada-post-shipping/' ),
			'wc_shipping_PluginHive'	=> $fun( __( 'WooCommerce Shipping Services by PluginHive', $text_domin ), 'wc_pluginhive-icon.png', 'wc_pluginhive/' ),
			'dhl_for_woocommerce'		=> $fun( __( 'DHL Shipping Germany for WooCommerce', $text_domin ), 'dhl-for-wc.png', 'dhl-shipping-germany-for-woocommerce/'),
			'quickbooks_commerce'		=> $fun( __( 'QuickBooks Commerce (formerly TradeGecko)', $text_domin ), 'quickbooks-icon.png', 'quickbooks-commerce-tracking/' ),
			'readytoship'				=> $fun( __( 'ReadyToShip', $text_domin ), 'readytoship-icon.png', 'readytoship/' ),
			'royalmail'					=> $fun( __( 'Royal Mail Click & Drop', $text_domin ), 'royal-mail-icon.png', 'royal-mail-click-drop/' ),
			'customcat'					=> $fun( __( 'CustomCat', $text_domin ), 'customcat-icon.png', 'customcat/' ),
			'dear_inventory'			=> $fun( __( 'Dear Systems', $text_domin ), 'dear-system-icon.png', 'dear-systems/' ),
			'picqer'					=> $fun( __( 'Picqer', $text_domin ), 'picqer-icon.png', 'picqer/' ),
			'3plwinner'					=> $fun( __( '3plwinner', $text_domin ), '3plwinner-icon.png', '3plwinner/' ),
			'dianxiaomi'				=> $fun( __( 'Dianxiaomi', $text_domin ), 'dianxiaomi-icon.png', 'dianxiaomi/' ),
			'eiz'						=> $fun( __( 'EIZ', $text_domin ), 'eiz-icon.png', 'ebiz/' ),
			'shippypro'					=> $fun( __( 'Shippypro', $text_domin ), 'shippypro-icon.png', 'shippypro/' ),
			'ali2woo'					=> $fun( __( 'AliExpress Dropshipping', $text_domin ), 'aliexpress-icon.png', 'ali2woo/' ),
			'pirateship'				=> $fun( __( 'Pirate Ship', $text_domin ), 'pirateship-icon.png', 'pirate-ship/' ),
			'sendcloud'					=> $fun( __( 'Sendcloud', $text_domin ), 'sendcloud-icon.png', 'sendcloud/' ),
			'shiptheory'				=> $fun( __( 'Shiptheory', $text_domin ), 'shiptheory-icon.png', 'shiptheory/' ),
			'stamps_com'				=> $fun( __( 'Stamps.com', $text_domin ), 'stamps-com-icon.png', 'stamps-com/' ),
			'shippo'					=> $fun( __( 'Shippo', $text_domin ), 'shippo-icon.png', 'shippo/' ),
			'inventory_source'			=> $fun( __( 'Inventory source', $text_domin ), 'inventory-source-icon.png', 'inventory-source/' ),
			'gls_sell_send_italy'		=> $fun( __( 'GLS Sell & Send Italy', $text_domin ), 'gls.png', 'gls-sell-send-italy/' ),
			'gls_deliveryfrom'			=> $fun( __( 'Print Label and Tracking Code for GLS', $text_domin ), 'gls.png', 'print-label-and-tracking-code-for-gls/' ),
			'printful'					=> $fun( __( 'Printful', $text_domin ), 'printful-icon.png', 'printful/' ),
			'byrd'						=> $fun( __( 'Byrd Fulfillment', $text_domin ), 'byrd-icon.png', 'byrd/' ),
			'shirtee_cloud'				=> $fun( __( 'Shirtee Cloud', $text_domin ), 'shirtee-cloud-icon.png', 'shirtee-cloud/' ),
			'qapla'						=> $fun( __( 'Qapla', $text_domin ), 'qapla-icon.png', 'qapla/' ),
			'shiptime'					=> $fun( __( 'Shiptime', $text_domin ), 'shiptime-icon.png', 'shiptime/' ),
			'eshipper'					=> $fun( __( 'eShipper', $text_domin ), 'eshipper-icon.png', 'eshipper/' ),
			'linnworks'					=> $fun( __( 'Linnworks', $text_domin ), 'linnworks-icon.png', 'linnworks/' ),
			'simplesell'				=> $fun( __( 'SimpleSell', $text_domin ), 'simplesell-icon.png', 'simplesell/' ),
			'easypost'					=> $fun( __( 'EasyPost', $text_domin ), 'easypost.png', 'easypost/' ),
			'interparcel'				=> $fun( __( 'Interparcel', $text_domin ), 'interparcel.png', 'interparcel/' ),
			'sendle'					=> $fun( __( 'Sendle', $text_domin ), 'sendle.png', 'sendle/' ),
			'tehster'					=> $fun( __( 'WooCommerce GLS plugin by Tehster', $text_domin ), 'tehster.png', 'woocommerce-gls-plugin-by-tehster/' ),
			'germanized_vendidero'		=> $fun( __( 'WooCommerce Germanized plugin by Vendidero', $text_domin ), 'woocommerce-germanized.png', 'woocommerce-germanized-plugin-by-vendidero/' ),
			'netsuite'					=> $fun( __( 'NetSuite Connector', $text_domin ), 'netsuite.png', 'netsuite/' ),
			'zappy'						=> $fun( __( 'Zappy', $text_domin ), 'zappy.png', 'zappy/' ),
			'shiphero'					=> $fun( __( 'ShipHero', $text_domin ), 'shiphero.png', 'shiphero/' ),
			'csg'						=> $fun( __( 'CSG 3PL', $text_domin ), 'csg-icon.png', 'csg-3pl/' ),
			'parcel2go'					=> $fun( __( 'Parcel2go', $text_domin ), 'parcel2go-icon.png', 'parcel2go/' ),
			'canadian_machool'			=> $fun( __( 'Canadian Machool', $text_domin ), 'canadian-machool-icon.png', 'canadian-machool/' ),
			'extenda_retail'			=> $fun( __( 'Extenda Retail', $text_domin ), 'extenda-retail-icon.jpg', 'extenda-retail/' ),
			'bigseller'					=> $fun( __( 'BigSeller', $text_domin ), 'bigseller-icon.png', 'bigseller/' ),
			'monta'						=> $fun( __( 'Monta', $text_domin ), 'monta-icon.png', 'monta/' ),
			'despach_cloud'				=> $fun( __( 'Despach Cloud', $text_domin ), 'despach-cloud-icon.png', 'despach-cloud/' ),
			'starshipit'				=> $fun( __( 'Starshipit', $text_domin ), 'starshipit-icon.png', 'starshipit/' ),
			'shiprush'					=> $fun( __( 'Shiprush', $text_domin ), 'shiprush-icon.png', 'shiprush/' ),
			'easyship'					=> $fun( __( 'Easyship', $text_domin ), 'easyship-icon.png', 'easyship/' ),
			'fedex'						=> $fun( __( 'FedEx', $text_domin ), 'fedex-icon.png', 'fedex/' ),
			'jj_global'					=> $fun( __( 'J&J Global Fulfillment', $text_domin ), 'jj_global-icon.png', 'jj_global/' ),
			'shipping_easy'				=> $fun( __( 'ShippingEasy', $text_domin ), 'shipping_easy-icon.png', 'shipping-easy/' ),
			'podpartner'				=> $fun( __( 'PODpartner', $text_domin ), 'podpartner-icon.png', 'podpartner/' ),
			'ups_ecommerce_dashboard'	=> $fun( __( 'UPS Ecommerce Dashboard', $text_domin ), 'ups-ecommerce-dashboard-icon.png', 'ups-ecommerce-dashboard/' ),
			'shippit'					=> $fun( __( 'shippit', $text_domin ), 'shippit-icon.png', 'shippit/' ),
			'boostmyshop'				=> $fun( __( 'BoostMyShop', $text_domin ), 'boostmyshop-icon.png', 'boostmyshop/' ),
		);
		
		return $form_data;
	}
}
