jQuery( ".ud-checkbox li" ).on("click", function (e) { 
    // alert(zorem_tracking_data.plugin_slug_with_hyphens);
    var slug = zorem_tracking_data.plugin_slug_with_hyphens
	var ast_optin_email_notification = jQuery("#" + slug + "_optin_email_notification").prop("checked");
    var ast_enable_usage_data = jQuery("#" + slug + "_enable_usage_data").prop("checked");
	if ( false == ast_optin_email_notification && false == ast_enable_usage_data ) {
		jQuery('.submit_usage_data').prop("disabled", true);
	} else {
		jQuery('.submit_usage_data').prop("disabled", false);
	}
});

jQuery(document).on("click", ".submit_usage_data", function(e){	
	
	var form = jQuery('#usage_data_form');
	jQuery(".ud-box-container").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });

	jQuery.ajax({
		url: ajaxurl,		
		data: form.serialize(),		
		type: 'POST',		
		success: function(response) {	
			jQuery(".ud-box-container").unblock();	
			location.reload(true);
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
});

jQuery(document).on("click", ".skip_usage_data", function(e){	
	
	var form = jQuery('#skip_usage_data_form');
	jQuery(".ud-box-container").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });
	
	jQuery.ajax({
		url: ajaxurl,		
		data: form.serialize(),		
		type: 'POST',		
		success: function(response) {	
			jQuery(".ud-box-container").unblock();
			location.reload(true);
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
});