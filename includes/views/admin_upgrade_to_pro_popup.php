<div id="" class="popupwrapper upgrade_to_pro_popup" style="display:none;">
	<div class="popuprow">
        <div class="popup_header">
            <h2 class="upgrade_title"><?php esc_html_e( 'Upgrade to', 'woo-advanced-shipment-tracking' ); ?> AST PRO</h2>
			<span class="dashicons dashicons-no-alt popup_close_icon"></span>
		</div>
		<div class="popup_body">	
			<p class="ast_description">
				<?php esc_html_e(
					'AST PRO provides powerful features to easily add tracking info to WooCommerce orders, automate the fulfillment workflows and keep your customers happy and informed.',
					'woo-advanced-shipment-tracking'
				); ?>
			</p>
            <div class="ast_features_container">
                <div class="ast_features_inner">
                    <ul class="ast_pro_features_list">
				       	<li><?php esc_html_e( 'Add tracking per item', 'woo-advanced-shipment-tracking' ); ?></li>
						<li><?php esc_html_e( 'Auto-detect shipping carriers', 'woo-advanced-shipment-tracking' ); ?></li>
						<li><?php esc_html_e( 'Fulfillment dashboard', 'woo-advanced-shipment-tracking' ); ?></li>
						<li><?php esc_html_e( 'Responsive tracking info widget', 'woo-advanced-shipment-tracking' ); ?></li>
						<li><?php esc_html_e( 'Custom Order Status "Shipped"', 'woo-advanced-shipment-tracking' ); ?></li>
						<li><?php esc_html_e( 'AutoComplete API orders', 'woo-advanced-shipment-tracking' ); ?></li>				       
			        </ul>
                </div>
                <div class="ast_features_inner">
                    <ul class="ast_pro_features_list">
                       <li>
							<?php
							echo wp_kses_post(
								sprintf(
									/* translators: %s: link to integrations documentation */
									__( '25+ Built-in %s tracking integrations with shipping services', 'woo-advanced-shipment-tracking' ),
									'<a href="https://docs.zorem.com/docs/ast-pro/integrations/" target="_blank">' .
										esc_html__( 'tracking integrations', 'woo-advanced-shipment-tracking' ) .
									'</a>'
								)
							);
							?>
						</li>
				        <li><?php esc_html_e( 'Export tracking information to PayPal', 'woo-advanced-shipment-tracking' ); ?></li>
						<li><?php esc_html_e( 'Add Custom Shipping Carriers', 'woo-advanced-shipment-tracking' ); ?></li>
						<li><?php esc_html_e( 'White Label Shipping Carriers', 'woo-advanced-shipment-tracking' ); ?></li>
						<li><?php esc_html_e( 'Shipping Provider API Name Mapping', 'woo-advanced-shipment-tracking' ); ?></li>
						<li><?php esc_html_e( 'Premium Support', 'woo-advanced-shipment-tracking' ); ?></li>			        
			        </ul>
                </div>
            </div>
            <div class="call_to_action_section">			
			    <a href="https://www.zorem.com/ast-pro/?utm_source=wp-admin&utm_medium=pro-popup&utm_campaign=upgrad-to-pro" class="button-primary btn_ast2 btn_large" target="_blank"><?php esc_html_e( 'Upgrade To AST PRO', 'woo-advanced-shipment-tracking' ); ?> <span class="dashicons dashicons-arrow-right-alt2"></span></a>
            </div>
		</div>
	</div>	
	<div class="popupclose"></div>
</div>