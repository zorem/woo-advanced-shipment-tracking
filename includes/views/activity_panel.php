<div class="woocommerce-layout__activity-panel">
	<div class="woocommerce-layout__activity-panel-tabs">
		<button type="button" id="activity-panel-tab-help" class="components-button woocommerce-layout__activity-panel-tab">
			<span class="dashicons dashicons-menu-alt"></span>
		</button>
	</div>
	<div class="woocommerce-layout__activity-panel-wrapper">
		<div class="woocommerce-layout__activity-panel-content" id="activity-panel-true">
			<div class="woocommerce-layout__activity-panel-header">
				<div class="woocommerce-layout__inbox-title">
					<p class="css-activity-panel-Text">Documentation</p>            
				</div>								
			</div>
			<div>
				<ul class="woocommerce-list woocommerce-quick-links__list">
					<li class="woocommerce-list__item has-action">
						<?php
						$support_link = class_exists( 'ast_pro' ) ? 'https://www.zorem.com/?support=1' : 'https://wordpress.org/support/plugin/woo-advanced-shipment-tracking/#new-topic-0' ;
						?>
						<a href="<?php echo esc_url( $support_link ); ?>" class="woocommerce-list__item-inner" target="_blank" >
							<div class="woocommerce-list__item-before">
								<span class="dashicons dashicons-media-document"></span>	
							</div>
							<div class="woocommerce-list__item-text">
								<span class="woocommerce-list__item-title">
									<div class="woocommerce-list-Text">Get Support</div>
								</span>
							</div>
							<div class="woocommerce-list__item-after">
								<span class="dashicons dashicons-arrow-right-alt2"></span>
							</div>
						</a>
					</li>            
					<li class="woocommerce-list__item has-action">
						<a href="https://docs.zorem.com/docs/ast-pro/advanced-shipment-tracking-free/" class="woocommerce-list__item-inner" target="_blank">
							<div class="woocommerce-list__item-before">
								<span class="dashicons dashicons-media-document"></span>
							</div>
							<div class="woocommerce-list__item-text">
								<span class="woocommerce-list__item-title">
									<div class="woocommerce-list-Text">Documentation</div>
								</span>
							</div>
							<div class="woocommerce-list__item-after">
								<span class="dashicons dashicons-arrow-right-alt2"></span>
							</div>
						</a>
					</li>
					<?php if ( !class_exists( 'ast_pro' ) ) { ?>
						<li class="woocommerce-list__item has-action">
							<a href="https://www.zorem.com/ast-pro/" class="woocommerce-list__item-inner" target="_blank">
								<div class="woocommerce-list__item-before">
									<span class="dashicons dashicons-media-document"></span>
								</div>
								<div class="woocommerce-list__item-text">
									<span class="woocommerce-list__item-title">
										<div class="woocommerce-list-Text">Upgrade To Pro</div>
									</span>
								</div>
								<div class="woocommerce-list__item-after">
									<span class="dashicons dashicons-arrow-right-alt2"></span>
								</div>
							</a>
						</li>
					<?php } ?>
				</ul>
			</div>
		</div>
	</div>
</div>
