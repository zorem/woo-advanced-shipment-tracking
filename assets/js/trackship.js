( function( $, data, wp, ajaxurl ) {				
	var $wc_ast_trackship_form = $("#wc_ast_trackship_form");	
		
	var trackship_js = {
		
		init: function() {						
							
			$("#wc_ast_trackship_form").on( 'click', '.woocommerce-save-button', this.save_wc_ast_trackship_form );			
			$("#trackship_tracking_page_form").on( 'click', '.woocommerce-save-button', this.save_trackship_tracking_page_form );
			$("#trackship_late_shipments_form").on( 'click', '.woocommerce-save-button', this.save_trackship_late_shipments_form );
			$(".tipTip").tipTip();

		},				
		
		save_wc_ast_trackship_form: function( event ) {
			event.preventDefault();
			
			$("#wc_ast_trackship_form").find(".spinner").addClass("active");
			//$wc_ast_settings_form.find(".success_msg").hide();
			var ajax_data = $("#wc_ast_trackship_form").serialize();
			
			$.post( ajaxurl, ajax_data, function(response) {
				$("#wc_ast_trackship_form").find(".spinner").removeClass("active");
				
				jQuery("#ast_settings_snackbar").addClass('show_snackbar');	
				jQuery("#ast_settings_snackbar").text(trackship_script.i18n.data_saved);			
				setTimeout(function(){ jQuery("#ast_settings_snackbar").removeClass('show_snackbar'); }, 3000);										
			});
			
		},
		save_trackship_tracking_page_form: function( event ) {			
			event.preventDefault();
			
			$("#trackship_tracking_page_form").find(".spinner").addClass("active");			
			var ajax_data = $("#trackship_tracking_page_form").serialize();
			
			$.post( ajaxurl, ajax_data, function(response) {
				$("#trackship_tracking_page_form").find(".spinner").removeClass("active");
				
				jQuery("#ast_settings_snackbar").addClass('show_snackbar');	
				jQuery("#ast_settings_snackbar").text(trackship_script.i18n.data_saved);			
				setTimeout(function(){ jQuery("#ast_settings_snackbar").removeClass('show_snackbar'); }, 3000);					
			});			
		},
		save_trackship_late_shipments_form: function( event ) {			
			event.preventDefault();
			
			$("#trackship_late_shipments_form").find(".spinner").addClass("active");			
			var ajax_data = $("#trackship_late_shipments_form").serialize();
			
			$.post( ajaxurl, ajax_data, function(response) {
				$("#trackship_late_shipments_form").find(".spinner").removeClass("active");
				
				jQuery("#ast_settings_snackbar").addClass('show_snackbar');	
				jQuery("#ast_settings_snackbar").text(trackship_script.i18n.data_saved);			
				setTimeout(function(){ jQuery("#ast_settings_snackbar").removeClass('show_snackbar'); }, 3000);								
			});			
		},	
	};
	$(window).on('load',function () {
		trackship_js.init();	
	});	
})( jQuery, trackship_script, wp, ajaxurl );


jQuery(document).on("click", ".tab_input", function(){
	var tab = jQuery(this).data('tab');
	var label = jQuery(this).data('label');	
	var url = window.location.protocol + "//" + window.location.host + window.location.pathname+"?page=trackship-for-woocommerce&tab="+tab;
	window.history.pushState({path:url},'',url);	
});

jQuery(document).on("click", ".bulk_shipment_status_button", function(){
	jQuery(".trackship-notice").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });	
	var ajax_data = {
		action: 'bulk_shipment_status_from_settings',		
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',		
		success: function(response) {
			jQuery(".trackship-notice").unblock();
			jQuery('.bulk_shipment_status_button').closest(".trackship-notice").hide();
			jQuery( '.bulk_shipment_status_success' ).show();
			jQuery( '.bulk_shipment_status_button' ).attr("disabled", true)
					
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
});

jQuery(document).on("change", "#wc_ast_trackship_page_id", function(){
	var wc_ast_trackship_page_id = jQuery(this).val();
	if(wc_ast_trackship_page_id == 'other'){
		jQuery('.trackship_other_page_fieldset').show();
	} else{
		jQuery('.trackship_other_page_fieldset').hide();
	}
});

jQuery(document).on("change", ".shipment_status_toggle input", function(){
	jQuery("#content5 ").block({
    message: null,
    overlayCSS: {
        background: "#fff",
        opacity: .6
	}	
    });
	
	var settings_data = jQuery(this).data("settings");
	
	if(jQuery(this).prop("checked") == true){
		var wcast_enable_status_email = 1;
		jQuery(this).closest('tr').addClass('enable');
		jQuery(this).closest('tr').removeClass('disable');
	} else{
		var wcast_enable_status_email = 0;
		jQuery(this).closest('tr').addClass('disable');
		jQuery(this).closest('tr').removeClass('enable');
		if( settings_data == 'late_shipments_email_settings') jQuery('.late-shipments-email-content-table').hide();	
	}
	
	var id = jQuery(this).attr('id');
	
	var ajax_data = {
		action: 'update_shipment_status_email_status',
		id: id,
		wcast_enable_status_email: wcast_enable_status_email,
		settings_data: settings_data,		
	};
	
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,
		type: 'POST',
		success: function(response) {	
			jQuery("#content5 ").unblock();						
		},
		error: function(response) {					
		}
	});
});

jQuery(document).on("click", ".late_shipments_a", function(){
	jQuery('.late-shipments-email-content-table').toggle();
});

jQuery('#wc_ast_status_label_color').wpColorPicker({
	change: function(e, ui) {		
		var color = ui.color.toString();			
		jQuery('.order-status-table .order-label.wc-delivered').css('background',color);			
	}, 	
});

jQuery('body').click( function(){	
	if ( jQuery('.delivered_row button.button.wp-color-result').hasClass( 'wp-picker-open' ) ) { 
		save_automation_form(); 
	}
});

jQuery('.delivered_row button.button.wp-color-result').click( function(){	
	if ( jQuery(this).hasClass( 'wp-picker-open' ) ) {}else{save_automation_form();}
});

jQuery(document).on("change", ".ts_custom_order_color_select, #wc_ast_status_change_to_delivered, .ts_order_status_toggle", function(){
	save_automation_form();
});

jQuery(document).on("change", "#wc_ast_status_label_font_color", function(){
	var font_color = jQuery(this).val();
	jQuery('.order-status-table .order-label.wc-delivered').css('color',font_color);
});

function save_automation_form(){
	jQuery(".order-status-table").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });	
	var form = jQuery('#wc_ast_trackship_automation_form');
	jQuery.ajax({
		url: ajaxurl,		
		data: form.serialize(),		
		type: 'POST',		
		success: function(response) {
			jQuery(".order-status-table").unblock();			
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
}

jQuery(document).on("change", "#wc_ast_show_shipment_status_filter", function(){
	save_trackship_form();
});

jQuery(document).on("change", "#wc_ast_trackship_page_id", function(){
	save_tracking_page_form();
});

jQuery(document).on( "input", "#wc_ast_trackship_other_page", function(){	
	save_tracking_page_form();
});

jQuery(document).on("change", "#wc_ast_use_tracking_page", function(){
	if(jQuery(this).prop("checked") == true){
		jQuery('.ts_customizer_btn').removeClass('disable_ts_btn');
		jQuery(this).parent().parent('li').nextAll('li').fadeIn();
	} else{		
		jQuery('.ts_customizer_btn').addClass('disable_ts_btn');
		jQuery(this).parent().parent('li').nextAll('li').fadeOut();
	}
	save_tracking_page_form();
});

jQuery( document ).ready(function() {
	if(jQuery('#wc_ast_use_tracking_page').prop("checked") == true){
		jQuery('.ts_customizer_btn').removeClass('disable_ts_btn');
		jQuery('#wc_ast_use_tracking_page').parent().parent('li').nextAll('li').fadeIn();
	} else{
		jQuery('.ts_customizer_btn').addClass('disable_ts_btn');
		jQuery('#wc_ast_use_tracking_page').parent().parent('li').nextAll('li').fadeOut();
	}
});

function save_trackship_form(){
	jQuery("#wc_ast_trackship_form").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });	
	var form = jQuery('#wc_ast_trackship_form');
	jQuery.ajax({
		url: ajaxurl,		
		data: form.serialize(),		
		type: 'POST',		
		success: function(response) {
			jQuery("#wc_ast_trackship_form").unblock();			
			jQuery("#trackship_settings_snackbar").addClass('show_snackbar');	
			jQuery("#trackship_settings_snackbar").text(trackship_script.i18n.data_saved);			
			setTimeout(function(){ jQuery("#trackship_settings_snackbar").removeClass('show_snackbar'); }, 3000);
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
}

function save_tracking_page_form(){
	jQuery("#trackship_tracking_page_form").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });	
	var form = jQuery('#trackship_tracking_page_form');
	jQuery.ajax({
		url: ajaxurl,		
		data: form.serialize(),		
		type: 'POST',		
		success: function(response) {
			jQuery("#trackship_tracking_page_form").unblock();			
			jQuery("#trackship_settings_snackbar").addClass('show_snackbar');	
			jQuery("#trackship_settings_snackbar").text(trackship_script.i18n.data_saved);			
			setTimeout(function(){ jQuery("#trackship_settings_snackbar").removeClass('show_snackbar'); }, 3000);
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
}