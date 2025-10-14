<div class="menu-container">
	<button class="menu-button">
		<span class="menu-icon">
			<span class="dashicons dashicons-menu-alt"></span>
		</span>
	</button>
	<div class="popup-menu">
		<?php
		$support_link = class_exists( 'ast_pro' ) ? 'https://www.zorem.com/?support=1' : 'https://wordpress.org/support/plugin/woo-advanced-shipment-tracking/#new-topic-0' ;
		// Plugin directory URL
		$plugin_url = esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() );
		?>
		<a href="<?php echo esc_url( $support_link ); ?>" class="menu-item" target="_blank" >
			<span class="menu-icon">
				<img src="<?php echo esc_attr( $plugin_url ); ?>assets/images/get-support-icon.svg" alt="Get Support">
			</span>
			Get Support
		</a>
		<a href="https://docs.zorem.com/docs/ast-free/" class="menu-item" target="_blank">
			<span class="menu-icon">
				<img src="<?php echo esc_attr( $plugin_url ); ?>assets/images/documentation-icon.svg" alt="Documentation">
			</span>
			Documentation
		</a>
	</div>
</div>
