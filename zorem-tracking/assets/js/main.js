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

/**
 * Busy state for the opt-in box while a request is in flight.
 * Plain jQuery only (no BlockUI): fade the box, block pointer events and
 * disable both action buttons so the request can't be fired twice.
 */
function zorem_tracking_set_busy( busy ) {
	var box = jQuery(".ud-box-container");
	box.css({
		opacity: busy ? 0.6 : '',
		pointerEvents: busy ? 'none' : ''
	}).attr("aria-busy", busy ? "true" : "false");
	box.find(".submit_usage_data, .skip_usage_data").prop("disabled", busy);
}

function zorem_tracking_send( form ) {
	zorem_tracking_set_busy( true );

	jQuery.ajax({
		url: ajaxurl,
		data: form.serialize(),
		type: 'POST',
		success: function(response) {
			// Keep the box disabled until the page reloads.
			location.reload(true);
		},
		error: function(response) {
			console.log(response);
			zorem_tracking_set_busy( false );
		}
	});
}

jQuery(document).on("click", ".submit_usage_data", function(e){
	zorem_tracking_send( jQuery('#usage_data_form') );
	return false;
});

jQuery(document).on("click", ".skip_usage_data", function(e){
	zorem_tracking_send( jQuery('#skip_usage_data_form') );
	return false;
});
