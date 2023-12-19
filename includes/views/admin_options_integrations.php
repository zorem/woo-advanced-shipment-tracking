<?php
/**
 * Html code for Integrations tab
 */
?>
<section id="integrations_content" class="tab_section">
<div class="integration_container">
		<?php $ast_integration = AST_Integration::get_instance(); ?>
		<form method="post" id="integrations_settings_form" action="" enctype="multipart/form-data">
			<div class="integration_list">
				<div class="provider-grid-row grid-row">
				<?php
				foreach ( $ast_integration->integrations_settings_options() as $integrations_id => $array ) {
					$default = isset( $array['default'] ) ? $array['default'] : '';					
					$tgl_class = isset( $array['tgl_color'] ) ? 'ast-tgl-btn-green' : '';
					$disabled = isset( $array['disabled'] ) && true == $array['disabled'] ? 'disabled' : '';
					$settings = isset( $array['settings'] ) ? $array['settings'] : false ;	
					$checked = ( get_option( $integrations_id, $default ) ) || false == $settings ? 'checked' : '' ;
					$documentation = isset( $array['documentation'] ) ? $array['documentation'] : null ;
				
					if ( $settings ) { 
						$settings_key_array = array_keys( $array['settings_fields'] );
						$settings_option = $settings_key_array['0'];
						$settings_option_value = get_option( $settings_option, $array['settings_fields'][$settings_option]['default'] );						
					}
					
					?>
					<div class="grid-item">					
						<div class="grid-top">
							<div class="grid-provider-img">
								<img class="provider-thumb" src="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ); ?>assets/images/<?php esc_html_e( $array['img'] ); ?>">
							</div>
							<div class="grid-provider-name">
								<span class="provider_name">
									<?php esc_html_e( $array['title'] ); ?>
								</span>	
							</div>	
							<!-- <div class="grid-provider-settings">
								<span class="dashicons dashicons-admin-generic integration_settings" data-iid="<?php //esc_html_e( $integrations_id ); ?>"></span>								
							</div>		 -->
						</div>						
					</div>	
					<?php
				}
				?>
				</div>
			</div>
			<?php wp_nonce_field( 'integrations_settings_form', 'integrations_settings_form_nonce' ); ?>
			<input type="hidden" name="action" value="integrations_settings_form_update">
		</form>
		
		<div id="" class="slidout_container integration_settings_popup">
		</div>	
	</div>
</section>
