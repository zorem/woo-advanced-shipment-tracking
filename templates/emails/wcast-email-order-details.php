<?php
/**
 * Order details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-order-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates/Emails
 * @version 3.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$text_align = is_rtl() ? 'right' : 'left'; 
$responsive_check = false;
if (class_exists('Kadence_Woomail_Customizer')) {
	$responsive_check = Kadence_Woomail_Customizer::opt( 'responsive_mode' );
}

do_action( 'wcast_email_before_order_table', $order, $sent_to_admin, $plain_text, $email );

$table_font_size = '';
$kt_woomail = get_option( 'kt_woomail' );
if( !empty($kt_woomail) && isset( $kt_woomail['font_size'] ) )$table_font_size = 'font-size:'.$kt_woomail['font_size'].'px';	
?>
<br>
<h2 style="text-align:<?php echo $text_align; ?>">
	<?php
	echo __( 'Order details', 'woocommerce' );
	?>
</h2>
<table class="order-info-split-table" cellspacing="0" cellpadding="0" width="100%" border="0">
	<tr>
		<td align="left" valign="middle" style="padding: 12px 0;">
			<h3 style="text-align: left;">
			<?php 
				if ( $sent_to_admin ) {
					$before = '<a class="link" href="' . esc_url( $order->get_edit_order_url() ) . '">';
					$after  = '</a>';
				} else {
					$before = '';
					$after  = '';
				}
				/* translators: %s: Order ID. */
				echo wp_kses_post( $before . sprintf( __( 'Order number: %s', 'woo-advanced-shipment-tracking' ) . $after, $order->get_order_number() ) );
				?>
			</h3>
		</td>
		<td align="right" valign="middle" style="padding: 12px 0;">
			<h3 style="text-align: right;">
			<?php 
				echo wp_kses_post( sprintf(  __( 'Order Date', 'woocommerce' ) . ': <time datetime="%s">%s</time>', $order->get_date_created()->format( 'c' ), wc_format_datetime( $order->get_date_created() ) ) );
				?>
			</h3>
		</td>
	</tr>
</table>
<?php 
if ( true == $responsive_check ) { ?>
	<div style="margin-bottom: 40px;">
		<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;<?php echo $table_font_size; ?>" border="1">
			<thead>
				<tr>
					<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
					<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
					<?php if(!$hide_shipping_item_price){ ?>
						<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Price', 'woocommerce' ); ?></th>
					<?php } ?>
				</tr>
			</thead>
			<tbody>
				<?php
				$args = array(
					'show_sku'      => $sent_to_admin,
					'show_image'    => true,
					'image_size'    => array( 64, 64 ),
					'plain_text'    => $plain_text,
					'sent_to_admin' => $sent_to_admin,
				);
				$args = apply_filters( 'ast_email_order_items_args', $args);
				//echo wc_get_email_order_items( $order, $args );
									
				echo wc_get_template(
					'emails/wcast-email-order-items.php', array(
						'order'               => $order,
						'items'               => $order->get_items(),
						'show_download_links' => $order->is_download_permitted() && ! $args['sent_to_admin'],
						'show_sku'            => $args['show_sku'],
						'show_purchase_note'  => $order->is_paid() && ! $args['sent_to_admin'],
						'show_image'          => $args['show_image'],
						'image_size'          => $args['image_size'],
						'plain_text'          => $args['plain_text'],
						'sent_to_admin'       => $args['sent_to_admin'],
						'hide_shipping_item_price' => $hide_shipping_item_price
					),
					'woocommerce-advanced-shipment-tracking/', 
					wc_advanced_shipment_tracking()->get_plugin_path() . '/templates/'
				);	
				?>
			</tbody>			
		</table>
	</div>
<?php } else{ ?>
	<div style="margin-bottom: 40px;">
		<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;<?php echo $table_font_size; ?>" border="1">
			<thead>
				<tr>
					<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
					<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
					<?php if(!$hide_shipping_item_price){ ?>
						<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Price', 'woocommerce' ); ?></th>
					<?php } ?>
				</tr>
			</thead>
			<tbody>
				<?php
				$args = array(
							'show_sku'      => $sent_to_admin,
							'show_image'    => false,
							'image_size'    => array( 32, 32 ),
							'plain_text'    => $plain_text,
							'sent_to_admin' => $sent_to_admin,
						);
				$args = apply_filters( 'ast_email_order_items_args', $args);
				//echo wc_get_email_order_items( $order, $args );
				echo wc_get_template(
					'emails/wcast-email-order-items.php', array(
						'order'               => $order,
						'items'               => $order->get_items(),
						'show_download_links' => $order->is_download_permitted() && ! $args['sent_to_admin'],
						'show_sku'            => $args['show_sku'],
						'show_purchase_note'  => $order->is_paid() && ! $args['sent_to_admin'],
						'show_image'          => $args['show_image'],
						'image_size'          => $args['image_size'],
						'plain_text'          => $args['plain_text'],
						'sent_to_admin'       => $args['sent_to_admin'],
						'hide_shipping_item_price' => $hide_shipping_item_price
					),
					'woocommerce-advanced-shipment-tracking/', 
					wc_advanced_shipment_tracking()->get_plugin_path() . '/templates/'
				);	
				?>
			</tbody>			
		</table>
	</div>
<?php } ?>
<?php do_action( 'wcast_email_after_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>