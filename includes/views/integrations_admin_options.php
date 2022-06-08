<section id="integrations_content" class="tab_section">	
	<div class="tab_container_without_bg">		
		<div class="accordion_container">
			<?php
			//echo '<pre>';print_r($this->integrations_settings_options());echo '</pre>';
			foreach ( $this->integrations_settings_options() as $integrations_id => $array ) {
			$tgl_class = isset( $array['tgl_color'] ) ? 'ast-tgl-btn-green' : '';
			$disabled = isset( $array['disabled'] ) && true == $array['disabled'] ? 'disabled' : '';
			$checked = ( 'enable_parcelforce_integration' == $integrations_id ) ? 'checked' : '' ;
			$upgrade_class = ( 'enable_parcelforce_integration' == $integrations_id ) ? '' : 'upgrade_to_ast_pro' ;
			$documentation = isset( $array['documentation'] ) ? $array['documentation'] : null ;
			?>
			<div class="integration_accordion_set">
				<div class="integration_accordion heading">
					
					<img class="integration-img" src="<?php echo esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ); ?>assets/images/<?php esc_html_e( $array['img'] ); ?>">	
				
					<label class="integration-label"><?php esc_html_e( $array['title'] ); ?></label>
					
					<span class="ast-tgl-btn-parent integration-tgl">
						<input type="hidden" name="<?php esc_html_e( $integrations_id ); ?>" value="0"/>
						<input class="ast-tgl ast-tgl-flat ast-settings-toggle" id="<?php esc_html_e( $integrations_id ); ?>" name="<?php esc_html_e( $integrations_id ); ?>" type="checkbox" <?php esc_html_e( 	$checked ); ?> value="1" readonly <?php esc_html_e( $disabled ); ?>/>
						<label class="ast-tgl-btn <?php esc_html_e( $tgl_class ); ?> <?php esc_html_e( $upgrade_class ); ?>" for="<?php esc_html_e( $integrations_id ); ?>"></label>
					</span>

					<?php if ( null != $documentation ) { ?>
						<a href="<?php echo esc_url( $documentation ); ?>" class="doc-link" target="_blank"><?php esc_html_e( 'more info', 'ast-pro' ); ?></a>
					<?php } ?>
				</div>								
			</div>			
			<?php } ?>
		</div>
	</div>	
</section>
