<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handles plugin uninstall popup and reassigning order statuses.
 *
 * @since 1.0.0
 */
class AST_Uninstall_Handler {

    /**
	 * The single instance of the class.
	 *
	 * @var AST_Uninstall_Handler
	 */
	protected static $instance = null;

	/**
	 * Main instance.
	 *
	 * @return AST_Uninstall_Handler
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Show uninstall popup notice on plugins page if custom statuses are in use.
	 */
	public function uninstall_notice() {
		$screen = get_current_screen();

		if ( 'plugins.php' !== $screen->parent_file ) {
			return;
		}

		// Enqueue admin styles and blockUI script
        wp_enqueue_style( 'ast_styles', wc_advanced_shipment_tracking()->plugin_dir_url() . 'assets/css/admin.css', array(), wc_advanced_shipment_tracking()->version );
        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
        wp_enqueue_script( 'wc-jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );

		$ps_count       = array_key_exists( 'wc-partial-shipped', wc_get_order_statuses() ) ? wc_orders_count( 'partial-shipped' ) : 0;
		$delivered_count = array_key_exists( 'wc-delivered', wc_get_order_statuses() ) ? wc_orders_count( 'delivered' ) : 0;

		$order_statuses = wc_get_order_statuses();

		unset( $order_statuses['wc-partial-shipped'] );

		if ( $ps_count > 0 || $delivered_count > 0 ) {
			?>
		<script>

		jQuery(document).on("click","[data-slug='woo-advanced-shipment-tracking'] .deactivate a",function(e){
			e.preventDefault();
			jQuery('.uninstall_popup').show();
			var theHREF = jQuery(this).attr("href");
			jQuery(document).on("click",".uninstall_plugin",function(e){
				jQuery("body").block({
					message: null,
					overlayCSS: {
						background: "#fff",
						opacity: .6
					}
				});
				var form = jQuery('#order_reassign_form');
				jQuery.ajax({
					url: ajaxurl,
					data: form.serialize(),
					type: 'POST',
					success: function(response) {
						jQuery("body").unblock();
						window.location.href = theHREF;
					},
					error: function(response) {
						console.log(response);
					}
				});
			});
		});

		jQuery(document).on("click",".popupclose",function(e){
			jQuery('.uninstall_popup').hide();
		});

		jQuery(document).on("click",".uninstall_close",function(e){
			jQuery('.uninstall_popup').hide();
		});

		jQuery(document).on("click",".popup_close_icon",function(e){
			jQuery('.uninstall_popup').hide();
		});
		</script>
		<div id="" class="popupwrapper uninstall_popup" style="display:none;">
			<div class="popuprow">
				<div class="popup_header">
					<h3 class="popup_title">Advanced Shipment Tracking for WooCommerce</h3>
					<span class="dashicons dashicons-no-alt popup_close_icon"></span>
				</div>
				<div class="popup_body">
					<form method="post" id="order_reassign_form">
						<?php 
						if ( $ps_count > 0 ) {
							?>
							<p>
							<?php 
								/* translators: %s: replace with Partially Shipped order count */
								printf( esc_html__('We detected %s orders that use the Partially Shipped order status, You can reassign these orders to a different status', 'woo-advanced-shipment-tracking'), esc_html( $ps_count ) );
							?>
							</p>

							<select id="reassign_ps_order" name="reassign_ps_order" class="reassign_select">
								<option value=""><?php esc_html_e('Select', 'woocommerce'); ?></option>
								<?php foreach ( $order_statuses as $key => $status ) { ?>
									<option value="<?php esc_html_e( $key ); ?>"><?php esc_html_e( $status ); ?></option>
								<?php } ?>
							</select>

						<?php
						}
						if ( $delivered_count > 0 ) { 
							?>
							<p>
							<?php 
								/* translators: %s: replace with Partially Shipped order count */
								printf( esc_html__('We detected %s orders that use the Delivered order status, You can reassign these orders to a different status', 'woo-advanced-shipment-tracking'), esc_html__( $delivered_count ) ); 
							?>
							</p>
							
							<select id="reassign_delivered_order" name="reassign_delivered_order" class="reassign_select">
								<option value=""><?php esc_html_e('Select', 'woocommerce'); ?></option>
								<?php foreach ( $order_statuses as $key => $status ) { ?>
									<option value="<?php esc_html_e( $key ); ?>"><?php esc_html_e( $status ); ?></option>
								<?php } ?>
							</select>
						
						<?php } ?>
						<p>	
							<?php wp_nonce_field( 'ast_reassign_order_status', 'ast_reassign_order_status_nonce' ); ?>
							<input type="hidden" name="action" value="reassign_order_status">
							<input type="button" value="<?php esc_html_e( 'Deactivate' ); ?>" class="uninstall_plugin button-primary btn_ast2">
							<input type="button" value="<?php esc_html_e( 'Close', 'woocommerce' ); ?>" class="uninstall_close button-primary btn_red">
						</p>
					</form>	
				</div>	
			</div>
			<div class="popupclose"></div>
		</div>
		<?php 
		}
	}

	/**
	 * Reassign orders from custom AST statuses to selected status.
	 *
	 * @action wp_ajax_ast_pro_reassign_order_status
	 */
	public function reassign_order_status() {
		check_ajax_referer( 'ast_reassign_order_status', 'ast_reassign_order_status_nonce' );
		
		$reassign_ps_order = isset(	$_POST['reassign_ps_order']	) ? wc_clean( $_POST['reassign_ps_order'] ) : '';
		$reassign_delivered_order = isset(	$_POST['reassign_delivered_order']	) ? wc_clean( $_POST['reassign_delivered_order'] ) : '';
		
		if ( '' != $reassign_ps_order ) {
			
			$args = array(
				'status' => 'partial-shipped',
				'limit' => '-1',
			);
			
			$ps_orders = wc_get_orders( $args );
			
			foreach ( $ps_orders as $order ) {
				$order_id = $order->get_id();
				$order = new WC_Order( $order_id );
				$order->update_status( $reassign_ps_order );
			}
		}
		if ( '' != $reassign_delivered_order ) {
			
			$args = array(
				'status' => 'delivered',
				'limit' => '-1',
			);
			
			$delivered_orders = wc_get_orders( $args );

			foreach ( $delivered_orders as $order ) {
				$order_id = $order->get_id();
				$order = new WC_Order( $order_id );
				$order->update_status( $reassign_delivered_order );
			}
		}
		echo 1;
		die();
	}
}
