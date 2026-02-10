<?php
/**
 * Tab-specific admin notice content.
 */
$ast_tab_notices = array(
	'settings'           => array(
		'heading'     => '🚀 Upgrade to AST PRO! 🎉',
		'line1'       => 'Upgrade to AST PRO to unlock tracking per item, auto-detect shipping carriers, a custom &ldquo;Shipped&rdquo; order status, and prevent duplicate tracking numbers via API.',
		'line2'       => '',
	),
	'shipping-providers' => array(
		'heading'     => '🚀 Upgrade to AST PRO! 🎉',
		'line1'       => 'Upgrade to AST PRO to white-label shipping carriers, create custom carriers, and map carrier names from external shipping services.',
		'line2'       => '',
	),
	'bulk-upload'        => array(
		'heading'     => '🚀 Upgrade to AST PRO! 🎉',
		'line1'       => 'Upgrade to AST PRO to automate CSV tracking imports via FTP and SFTP&mdash;no more manual uploads.',
		'line2'       => '',
	),
	'integrations'       => array(
		'heading'     => '🚀 Upgrade to AST PRO! 🎉',
		'line1'       => 'Upgrade to AST PRO to enable built-in integrations with shipping services that automatically populate tracking info into the shipment tracking panel and complete orders when labels are generated&mdash;no manual copy-paste required.',
		'line2'       => '',
	),
	'addons'             => array(
		'heading'     => '🚀 Upgrade to Automate Your Shipping Workflow! 🎉',
		'line1'       => 'Streamline your fulfillment process with the <strong>Advanced Shipment Tracking Pro</strong> and save time on daily shipping tasks.',
		'line2'       => 'Automate the order fulfillment with integration with <strong>20+ shipping services</strong>, and manage all shipments in Woo from a centralized dashboard.',
	),
	'trackship'          => array(
		'heading'     => '🚀 Upgrade to Automate Your Shipping Workflow! 🎉',
		'line1'       => 'Streamline your fulfillment process with the <strong>Advanced Shipment Tracking Pro</strong> and save time on daily shipping tasks.',
		'line2'       => 'Automate the order fulfillment with integration with <strong>20+ shipping services</strong>, and manage all shipments in Woo from a centralized dashboard.',
	),
);

$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'settings';
$notice      = isset( $ast_tab_notices[ $current_tab ] ) ? $ast_tab_notices[ $current_tab ] : $ast_tab_notices['settings'];
?>
<div class="admin-message-panel">
	<div class="admin-message-row">
		<div class="admin_message_box_main">
			<div class="admin_message_box_left">
				<h1 class="admin_message_header"><?php echo wp_kses_post( $notice['heading'] ); ?></h1>
				<p class="ast-notice-line1"><?php echo wp_kses_post( $notice['line1'] ); ?></p>
				<p class="ast-notice-line2"><?php echo wp_kses_post( $notice['line2'] ); ?></p>
				<p><strong>Get 20% Off*!</strong> Use code <strong>ASTPRO20</strong> at checkout.</p>
				<a href="https://www.zorem.com/ast-pro/?utm_source=wp-admin&utm_medium=ast-pro-update-notice&utm_campaign=upgrad-now" class="button-primary btn_ast2" target="_blank">UPGRADE NOW</a>
				<p><strong>★</strong> for new customers only</p>
			</div>
			<div class="admin_message_box_right">
				<img src="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ); ?>assets/images/ast-admin-notice.png"></img>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
jQuery( function( $ ) {
	var astTabNotices = <?php echo wp_json_encode( $ast_tab_notices ); ?>;

	$( document ).on( 'click', '.tab_input', function() {
		var tab    = $( this ).data( 'tab' );
		var notice = astTabNotices[ tab ] || astTabNotices['settings'];

		if ( notice ) {
			$( '.admin_message_header' ).html( notice.heading );
			$( '.ast-notice-line1' ).html( notice.line1 );
			$( '.ast-notice-line2' ).html( notice.line2 );
		}
	});
});
</script>
