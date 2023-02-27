
	<div id="ast_usagedata_connect" class="ud-box-container">
		<div class="ud-content">
		   <h2>Thank you for updating to Advanced Shipment Tracking for WooCommerce v<?php echo esc_html( wc_advanced_shipment_tracking()->version ); ?>!</h2>
		   <p>We have introduced this opt-in so you never miss an important update and help us make the plugin more compatible with your site and better at doing what you need it to.<br><br>Opt in to get email 	notifications for security &amp; feature updates, and to share some basic WordPress environment info. If you skip this, that's okay! <b>Advanced Shipment Tracking for WooCommerce</b> will still work just fine.</p>
		</div>
		<form id="ast_usage_data_form" action="" method="POST">
		<ul class="ud-checkbox">
			<li class="">
				<label class="" for="ast_optin_email_notification">
					<input type="hidden" name="ast_optin_email_notification" value="0">
					<input type="checkbox" id="ast_optin_email_notification" name="ast_optin_email_notification" checked="true" value="1">
					<span class="label">Opt in to get email notifications for security & feature updates</span>	
				</label>	
			</li>
			<li class="">
				<label class="" for="ast_enable_usage_data">
					<input type="hidden" name="ast_enable_usage_data" value="0">
					<input type="checkbox" id="ast_enable_usage_data" name="ast_enable_usage_data" checked="true" value="1">
					<span class="label">Opt in to share some basic WordPress environment info</span>	
				</label>	
			</li>
		</ul>
		<div class="ud-actions">
			
				<input type="hidden" name="action" value="ast_activate_usage_data">
				<?php wp_nonce_field( 'ast_usage_data_form', 'ast_usage_data_form_nonce' ); ?>         
				<button class="button button-primary submit_usage_data" tabindex="1" type="submit">Allow &amp; Continue</button>
			</form>	
			<form id="ast_skip_usage_data_form" action="" method="POST">
				<input type="hidden" name="action" value="ast_skip_usage_data">
				<?php wp_nonce_field( 'ast_usage_skip_form', 'ast_usage_skip_form_nonce' ); ?>         
				<button id="ast_ud_skip_activation" class="button button-secondary skip_usage_data" tabindex="2" type="button">Skip</button>
			</form>	
			<div class="clear"></div>
		</div>      
	</div> 
