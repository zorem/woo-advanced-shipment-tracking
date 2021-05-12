<?php
/**
 * html code for settings tab
 */
?>
<section id="content2" class="tab_section">
	<div class="tab_inner_container center">
		
		<div class="tabs_outer_form_table">
			
			<?php $this->get_html_menu_tab( $this->get_ast_tab_general_settings_data(), 'inner_tab_input'); ?>
			
			<div class="tabs_inner_section" id="content_general_settings">
				<form method="post" id="wc_ast_settings_form" action="" enctype="multipart/form-data">
					<?php $this->get_html_ul( $this->get_settings_data() );?>
					<div class="tabs_submit">					
						<div class="spinner"></div>
						<button name="save" class="button-primary woocommerce-save-button btn_ast2" type="submit" value="Save changes"><?php _e( 'Save Changes', 'woo-advanced-shipment-tracking' ); ?></button>															
						<?php wp_nonce_field( 'wc_ast_settings_form', 'wc_ast_settings_form_nonce' );?>
						<input type="hidden" name="action" value="wc_ast_settings_form_update">
					</div>								
				</form>	
			</div>
			<?php require_once( 'admin_options_osm.php' );
			do_action('ast_general_settings_panel');
			?>		
		</div>	
		
		<?php do_action('ast_generat_settings_end'); ?>						
	</div>		
</section>