<?php
	$nonce = wp_create_nonce('ast_dismiss_notice');
	$dismissable_url = esc_url(add_query_arg(['ast-pro-settings-ignore-notice' => 'true', 'nonce' => $nonce]));
?>
<div class="admin-message-panel">
	<div class="admin-message-row">
		<div class="admin_message_box_main">
			<div class="admin_message_box_left">
				<h1 class="admin_message_header">Upgrade to AST PRO!</h1>
				<p>Upgrade to Advanced Shipment Tracking Pro and automate your fulfillment workflow, save time on your daily tasks and keep your customers happy and informed on their shipped orders.</p>
				<p>Get <strong>20% Off</strong> on your 1st year license. Use code <strong>ASTPRO20</strong> to redeem your discount</p>
				<a href="https://www.zorem.com/ast-pro/?utm_source=wp-admin&utm_medium=ast-notice-dismiss&utm_campaign=upgrad-now" class="button-primary btn_ast2" target="_blank">UPGRADE NOW</a>
				<a href="<?php esc_html_e( $dismissable_url ); ?>" class="ast-notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></a>		
			</div>
			<div class="admin_message_box_right">
				<img src="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ); ?>assets/images/ast-admin-notice.png"></img>
			</div>
		</div>
	</div>
</div>
