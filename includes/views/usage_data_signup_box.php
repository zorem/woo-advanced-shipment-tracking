
<div id="ast_usagedata_connect" class="ud-box-container">
	<div class="ud-content">
	   <h2>Thank you for installing the Advanced Shipment Tracking (AST) plugin</h2>
	   <p>Thank you for updating to Advanced Shipment Tracking (AST)! We recently introduced an opt-in feature that allows us to collect basic usage data from your website.</p>
	   <p>We collect this data to help us improve the plugin and make the plugin more compatible with other plugins and different WooCommerce configurations. The data we collect is used for statistical analysis purposes only and will be kept confidential. We respect your privacy and handle your information securely.</p>
	   <p>The data we collect includes basic WordPress environment information and WooCommerce data. This includes information about your site's WordPress version, theme, active plugins, WooCommerce version, and related settings. The collected data does not include any personal information, customer data, or order information.</p>
	   <p style="margin-bottom: 0;">For more information about the data we collect and how we use it, please visit our <a href="https://www.zorem.com/plugins-usage-tracking/" target="_blank">website</a>.</p>		   
	</div>
	<form id="ast_usage_data_form" action="" method="POST">		
		<ul class="ud-checkbox">
			<h4 style="margin: 0 0 10px;">To opt in, please check the boxes below:</h4>
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
			<button id="ast_ud_skip_activation" class="button button-secondary skip_usage_data" tabindex="2" type="button">Skip</button>
	   </div>	   
	</form>
	<form id="ast_skip_usage_data_form" action="" method="POST">
		<input type="hidden" name="action" value="ast_skip_usage_data">
		<?php wp_nonce_field( 'ast_usage_skip_form', 'ast_usage_skip_form_nonce' ); ?>			
	</form>				   
</div> 
