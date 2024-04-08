jQuery(document).on("submit", "#wc_ast_upload_csv_form", function(){
	
	jQuery('.csv_upload_status li').remove();	
	jQuery('.bulk_upload_status_tr').hide();
	jQuery('.progress_title').hide();	
	var form = jQuery('#wc_ast_upload_csv_form');	
	var error;
	var trcking_csv_file = form.find("#trcking_csv_file");
	var replace_tracking_info = jQuery("#replace_tracking_info").prop("checked");
	var date_format_for_csv_import = jQuery('input[name="date_format_for_csv_import"]:checked').val();
	
	if(replace_tracking_info == true){
		replace_tracking_info = 1;	
	} else{
		replace_tracking_info = 0;
	}		
	
	var ext = jQuery('#trcking_csv_file').val().split('.').pop().toLowerCase();	
	
	if( trcking_csv_file.val() === '' ){		
		showerror( trcking_csv_file );
		error = true;
	} else{
		if(ext != 'csv'){
			alert(shipment_tracking_table_rows.i18n.upload_only_csv_file);	
			showerror( trcking_csv_file );
			error = true;
		} else{
			hideerror(trcking_csv_file);
		}
	}
	
	if(error == true){
		return false;
	}
	
	var regex = /([a-zA-Z0-9\s_\\.\-\(\):])+(.csv|.txt)$/;
	if (regex.test(jQuery("#trcking_csv_file").val().toLowerCase())) {
		if (typeof (FileReader) != "undefined") {
			var reader = new FileReader();
			reader.onload = function (e) {
				
				var trackings = new Array();
				var rows = e.target.result.split("\n");						 
				
				if(rows.length <= 1){
					 alert('There are some issue with CSV file.');
					 return false;
				}		
				
				for (var i = 1; i < rows.length; i++) {
					var cells = rows[i].split(",");
					if (cells.length > 1) {
						var tracking = {};
						tracking.order_id = cells[0];								 
						tracking.tracking_provider = cells[1];
						tracking.tracking_number = cells[2];
						 tracking.date_shipped = cells[3];
						 tracking.status_shipped = cells[4];
						 if(cells[5]){
							tracking.sku = cells[5]; 
						 }
						 if(cells[6]){
							tracking.qty = cells[6]; 
						 }
						 if(tracking.order_id){
							trackings.push(tracking);	
						 }						
					}
				}  				
 			
				var csv_length = trackings.length;
				var run_data = 0; 
				
				jQuery("#wc_ast_upload_csv_form")[0].reset();												
				jQuery(".progress-moved .progress-bar2").css('width',0+'%');
				jQuery(".progress_step1").removeClass("active");
				jQuery(".progress_step1").addClass("done");
				jQuery(".progress_step2").addClass("active");
				jQuery(".upload_csv_div").hide();
				jQuery(".bulk_upload_status_div").show();
				
				var tracking_import = jQuery(trackings).each(function(index, element) {
					var sku = '';
					var qty = '';
					var order_id = trackings[index]['order_id'];
					var tracking_provider = trackings[index]['tracking_provider'];
					var tracking_number = trackings[index]['tracking_number'];
					var date_shipped = trackings[index]['date_shipped'];
					var status_shipped = trackings[index]['status_shipped'];
					var success_class = 0;
					var error_class = 0;
					var error_message = '';
					var success_message = '';
					if(trackings[index]['sku']){
						var sku = trackings[index]['sku'];	
					}					
					if(trackings[index]['qty']){
						var qty = trackings[index]['qty'];
					}						
					
					var nonce = jQuery( '#nonce_csv_import' ).val();
					
					var data = {
						action: 'wc_ast_upload_csv_form_update',
						order_id: order_id,
						date_format_for_csv_import: date_format_for_csv_import,
						tracking_provider: tracking_provider,
						tracking_number: tracking_number,
						date_shipped: date_shipped,
						status_shipped: status_shipped,
						sku: sku,
						qty: qty,
						replace_tracking_info: replace_tracking_info,
						trackings: trackings,
						security: nonce,	
					};
				
					var option = {
						url: ajaxurl,
						data: data,
						type: 'POST',
						success:function(data){								
							jQuery('.progress_number').html((index+1)+'/'+csv_length);
							
							jQuery('.csv_upload_status').append(data);
							var progress = (index+1)*100/csv_length;
							jQuery('.bulk_upload_status_tr').show();
							jQuery('.progress_title').show();	
							
							jQuery(".progress-moved .progress-bar2").css('width',progress+'%');
							
							var shipping_provider_error_class = 0;
							var tracking_number_error_class = 0;
							var empty_date_shipped_error_class = 0;
							var invalid_date_shipped_error_class = 0;
							var invalid_order_id_error_class = 0;
							var invalid_tracking_data_error_class = 0;
							
							if(progress == 100){
								jQuery( ".csv_upload_status li" ).each(function( index ) {
									if( this.className == 'shipping_provider_error' || this.className == 'tracking_number_error' || this.className == 'empty_date_shipped_error' || this.className == 'invalid_date_shipped_error' || this.className == 'invalid_order_id_error' || this.className == 'invalid_tracking_data_error' ){
										error_class++;
									}
									if(this.className == 'success'){										
										success_class++;
									}
									if( this.className == 'shipping_provider_error' )shipping_provider_error_class++;
									if( this.className == 'tracking_number_error' )tracking_number_error_class++;
									if( this.className == 'empty_date_shipped_error' )empty_date_shipped_error_class++;		
									if( this.className == 'invalid_date_shipped_error' )invalid_date_shipped_error_class++;
									if( this.className == 'invalid_order_id_error' )invalid_order_id_error_class++;
									if( this.className == 'invalid_tracking_data_error' )invalid_tracking_data_error_class++;
								});									
								
								jQuery('.progress_title').hide();
								jQuery(".progress_step2").removeClass("active");
								jQuery(".progress_step2").addClass("done");								
								jQuery(".progress_step3").addClass("active");
								jQuery(".bulk_upload_status_div").addClass("csv_import_done");
								jQuery(".bulk_upload_status_action ").show();
								
								if(error_class > 0){
									error_message = error_class+' tracking numbers import failed';
									jQuery(".bulk_upload_status_overview_td.csv_fail_msg").show();									
									jQuery(".bulk_upload_status_overview_td.csv_fail_msg span").html(error_message);
								} else{
									jQuery(".bulk_upload_status_overview_td.csv_fail_msg").hide();	
								}
								
								if(success_class > 0){
									jQuery(".bulk_upload_status_overview_td.csv_success_msg").show();								
									success_message = success_class+' tracking numbers imported successfully';
									jQuery(".bulk_upload_status_overview_td.csv_success_msg span").html(success_message);
								} else{
									jQuery(".bulk_upload_status_overview_td.csv_success_msg").hide();	
								}
	
								if(invalid_order_id_error_class > 0){
									jQuery(".csv_error_details_ul").append('<li>'+invalid_order_id_error_class+' tracking numbers import failed due to invalid order id</li>');	
								}
								if(shipping_provider_error_class > 0){
									jQuery(".csv_error_details_ul").append('<li>'+shipping_provider_error_class+' tracking numbers import failed due to invalid shipping provider</li>');	
								}
								if(tracking_number_error_class > 0){
									jQuery(".csv_error_details_ul").append('<li>'+tracking_number_error_class+' tracking numbers import failed due to empty tracking number</li>');	
								}
								if(empty_date_shipped_error_class > 0){
									jQuery(".csv_error_details_ul").append('<li>'+empty_date_shipped_error_class+' tracking numbers import failed due to empty date shipped</li>');	
								}
								if(invalid_date_shipped_error_class > 0){
									jQuery(".csv_error_details_ul").append('<li>'+invalid_date_shipped_error_class+' tracking numbers import failed due to invalid date shipped</li>');	
								}
								if(invalid_tracking_data_error_class > 0){
									jQuery(".csv_error_details_ul").append('<li>'+invalid_tracking_data_error_class+' tracking numbers import failed due to invalid tracking data</li>');	
								}	
																
								jQuery(".bulk_upload_status_heading_tr h2").html("Import Completed!");								
																
								jQuery(".bulk_upload_status_heading_tr p").hide();								
								jQuery(".csv_upload_status").hide();	
								jQuery('.bulk_upload_status_tr').hide();
							}												
						},
				
					};
				
					jQuery.ajaxQueue.addRequest(option);
				
					jQuery.ajaxQueue.run();					
					run_data++;					
				});											
			
			}				
			reader.readAsText(jQuery("#trcking_csv_file")[0].files[0]);						
		} else {
			alert(shipment_tracking_table_rows.i18n.browser_not_html);
		}
	} else {
		alert(shipment_tracking_table_rows.i18n.upload_valid_csv_file);
	}
	return false;
});

jQuery(document).on("click", ".view_csv_error_details", function(){
	jQuery('.bulk_upload_status_detail_error_tr').toggle();
	var tr_visible = jQuery('.bulk_upload_status_detail_error_tr').is(":visible");
	if(tr_visible == true){
		jQuery('.view_csv_error_details').text('hide details');
	} else{
		jQuery('.view_csv_error_details').text('view details');
	}
});
	
jQuery(document).on("click", ".csv_upload_again", function(){
	jQuery('.csv_upload_status li').remove();	
	jQuery('.csv_upload_status').show();	
	jQuery('.bulk_upload_status_tr').hide();
	jQuery('.bulk_upload_status_overview_td').hide();	
	jQuery('.progress_title').hide();
	jQuery(".bulk_upload_status_heading_tr h2").html('Importing'+'<span class="spinner is-active"></span>');
	jQuery(".bulk_upload_status_heading_tr p").show();
	jQuery(".progress_step2").removeClass("active");
	jQuery(".progress_step2").removeClass("done");								
	jQuery(".progress_step3").removeClass("done");								
	jQuery(".progress_step3").removeClass("active");
	jQuery(".progress_step1").removeClass("done");
	jQuery(".progress_step1").addClass("active");
	jQuery(".bulk_upload_status_div ").removeClass("csv_import_done");
	jQuery(".bulk_upload_status_action ").hide();
	jQuery('.bulk_upload_status_div').hide();
	jQuery('.upload_csv_div').show();
	jQuery('.bulk_upload_status_detail_error_tr').hide();
	jQuery('.csv_error_details_ul li').remove();
});

jQuery(document).click(function(event){
	var $trigger = jQuery(".provider_settings");
	if($trigger !== event.target && !$trigger.has(event.target).length){
		jQuery(".provider-settings-ul").hide();
	}   
});
jQuery(document).on("click", ".enable_carriers, .provider_list div.add-provider-container", function(){
	jQuery('#search_default_provider').val('');
	jQuery(".search-carrier-icon").click();
	jQuery(".provider-settings-ul").hide();	
	jQuery('.add_provider_popup').slideOutForm();	
});

jQuery(document).on("click", "#provider-settings", function(){
	var data_remove_selected = jQuery('.provider-grid-row.grid-row').attr('data-shippment-providers');

	if ( data_remove_selected == 'false' ) {
		jQuery('.reset_providers').hide();
	} else {
		jQuery('.reset_providers').show();
	}

	jQuery('.provider-settings-ul').show();
});

jQuery(document).on("click", ".status_slide", function(){
	var id = jQuery(this).val();
	if(jQuery(this).prop("checked") == true){
	   var checked = 1;
	   jQuery(this).closest('.provider').addClass('active_provider');
	   jQuery('#make_default_'+id).prop('disabled', false);
	   jQuery('#default_label_'+id).removeClass('disable_label');
	} else{
		var checked = 0;
		jQuery(this).closest('.provider').removeClass('active_provider');
		jQuery('#make_default_'+id).prop('disabled', true);
		jQuery('#make_default_'+id).prop('checked', false);
		jQuery('#default_label_'+id).addClass('disable_label');
	}
	
	var nonce = jQuery( '#nonce_shipping_provider' ).val();

	var error;	
	var ajax_data = {
		action: 'update_shipment_status',
		id: id,
		checked: checked,
		security: nonce,	
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',
		success: function(response) {	
			jQuery(document).ast_snackbar( shipment_tracking_table_rows.i18n.data_saved );					
		},
		error: function(response) {
			console.log(response);			
		}
	});
});

jQuery(document).on("click", ".pagination_link", function(){
	var page = jQuery(this).attr('id');
	var nonce = jQuery( '#nonce_shipping_provider' ).val();
	var ajax_data = {
		action: 'paginate_shipping_provider_list',
		page: page,
		security: nonce,	
	};

	jQuery(".provider_list ").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
	});

	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,
		type: 'POST',
		success: function(response) {	
			jQuery(".provider_list").replaceWith(response);
			jQuery(".provider_list").unblock();							
		},
		error: function(response) {	
			console.log(response);				
		}
	});	
});

jQuery(document).on( "click", ".search-icon", function(){	
	var search_term = jQuery('#search_provider').val();
	var nonce = jQuery( '#nonce_shipping_provider' ).val();
	var ajax_data = {
		action: 'filter_shipping_provider_list',
		search_term: search_term,
		security: nonce,	
	};

	jQuery(".provider_list ").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
	});

	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,
		type: 'POST',
		success: function(response) {	
			jQuery(".provider_list").replaceWith(response);
			jQuery(".provider_list").unblock();							
		},
		error: function(response) {	
			console.log(response);				
		}
	});	
});

jQuery(document).on("change", ".make_provider_default", function(){	
	jQuery("#content1 ").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
	});
	if(jQuery(this).prop("checked") == true){
	   jQuery('.make_provider_default').removeAttr('checked');
	   var checked = 1;	   
	   jQuery(this).prop('checked',true);	   
	} else{
		var checked = 0;		
	}
	var id = jQuery(this).data('id');
	var nonce = jQuery( '#nonce_shipping_provider' ).val();
	var error;	
	var default_provider = jQuery(this).val();
	var ajax_data = {
		action: 'update_default_provider',
		default_provider: default_provider,	
		id: id,
		checked: checked,	
		security: nonce,	
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',
		success: function(response) {
			jQuery("#content1 ").unblock();			
		},
		error: function(response) {
			console.log(response);			
		}
	});
});

jQuery(document).on("click", ".popupclose", function(){			
	jQuery('.ts_video_popup').hide();	
	jQuery('.upgrade_to_pro_popup').hide();	
});
jQuery(document).on("click", ".popup_close_icon", function(){		
	jQuery('.upgrade_to_pro_popup').hide();
});
jQuery(document).on("click", ".popupclose_btn", function(){		
	jQuery('.ts_video_popup').hide();	
});

jQuery(document).on("click", ".remove", function(){	
	jQuery("#content1 ").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
	});
	var r = confirm( shipment_tracking_table_rows.i18n.delete_provider );
	if (r === true) {		
	} else {
		jQuery("#content1").unblock();	
		return;
	}
	var id = jQuery(this).data('pid');
	var nonce = jQuery( '#nonce_shipping_provider' ).val();
	var error;	
	var default_provider = jQuery(this).val();
	var ajax_data = {
		action: 'woocommerce_shipping_provider_delete',		
		provider_id: id,
		security: nonce,
	};
	
	jQuery('#search_provider').removeAttr('value');
	
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',
		success: function(response) {
			jQuery(".provider_list").replaceWith(response);						
			jQuery("#content1").unblock();						
		},
		error: function(response) {
			console.log(response);			
		}
	});
});

jQuery(document).on("click", ".edit_provider", function(){		
	var id = jQuery(this).data('pid');
	var provider = jQuery(this).data('provider');
	var nonce = jQuery( '#nonce_shipping_provider' ).val();
	
	jQuery("#content1").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
	});

	var ajax_data = {
		action: 'get_provider_details',
		provder_type: provider,	
		provider_id: id,
		security: nonce,
	};
	
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',
		//dataType: "json",
		success: function(response) {
			jQuery(".edit_provider_popup").html(response);
			jQuery( '.tipTip' ).tipTip( {
				'attribute': 'data-tip'		
			} );		
			jQuery('.edit_provider_popup').slideOutForm();	
			jQuery("#content1").unblock();							
		},
		error: function(response) {
			console.log(response);			
		}
	});
});

function IsValidJSONString(str) {
	try {
		JSON.parse(str);
	} catch (e) {
		return false;
	}
	return true;
}

jQuery(document).on("click", ".reset_default_provider", function(){
	var form = jQuery('#edit_provider_form');
	
	jQuery(".edit_provider_popup").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
	});
	
	jQuery('#search_provider').removeAttr('value');
	var provider_id = jQuery(form).find('#provider_id').val();
	var nonce = jQuery( '#nonce_shipping_provider' ).val();
	var ajax_data = {
		action: 'reset_default_provider',		
		provider_id: provider_id,
		security: nonce,	
	};
	
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,
		type: 'POST',		
		success: function(response) {					
			jQuery(".provider_list").replaceWith(response);	
			form[0].reset();												
			jQuery('.edit_provider_popup').hide();			
			jQuery(".edit_provider_popup").unblock();		
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
});

jQuery(document).on("submit", "#edit_provider_form", function(){
	
	var form = jQuery('#edit_provider_form');
	var error;
	var shipping_provider = jQuery("#edit_provider_form .shipping_provider");
	var shipping_country = jQuery("#edit_provider_form .shipping_country");
	var api_provider_name = jQuery(".api_provider_new .api_provider_name");
	var thumb_url = jQuery("#edit_provider_form .thumb_url");
	var tracking_url = jQuery("#edit_provider_form .tracking_url");	
	var provider_type = jQuery("#edit_provider_form #provider_type");	
	
	if(provider_type.val() == 'custom_provider'){
		if( shipping_provider.val() === '' ){				
			showerror(shipping_provider);
			error = true;
		} else{		
			hideerror(shipping_provider);
		}	
		
		if( shipping_country.val() === '' ){				
			showerror(shipping_country);
			error = true;
		} else{		
			hideerror(shipping_country);
		}		
	}	

	if(provider_type.val() == 'default_provider'){				
		for(var i=0; i<api_provider_name.length; i++) {					
			if(validate(api_provider_name[i]) == false){
				showerror(jQuery(api_provider_name[i]));
				error = true;
			} else{
				hideerror(jQuery(api_provider_name[i]));
			}			
		}
	}
	
	if(error == true){
		return false;
	}	
	jQuery(".edit_provider_popup").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
	});
	
	jQuery('#search_provider').removeAttr('value');
	
	jQuery.ajax({
		url: ajaxurl,		
		data: form.serialize(),
		type: 'POST',		
		success: function(response) {					
			jQuery(".provider_list").replaceWith(response);	
			form[0].reset();												
			jQuery('.edit_provider_popup').hide();			
			jQuery(".edit_provider_popup").unblock();	
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
});

jQuery( ".thumb_url" ).blur(function() {
  var url = jQuery(this).val();
  if(url == ''){
	  jQuery('.thumb_id').val('');
  }
});

jQuery(document).on("click", ".bulk_select_provider", function(){
	jQuery('#delete_provider_bulk').attr('data-remove', 'selected-page');
	jQuery('div.all-shipping-carriers-selected').hide();
	var length_get = jQuery('input[name="bulk_select_provider[]"]:checked').length;
	jQuery('#selected_provider_total').text(length_get);

	if (jQuery('input[name="bulk_select_provider[]"]:checked').length > 0) {
		jQuery('#delete_provider_bulk').show();
		jQuery('div.shipping-carriers-selected-provider-message').show();
	} else {
		jQuery('#delete_provider_bulk').hide();
		jQuery('div.shipping-carriers-selected-provider-message').hide();
	}
});

jQuery(document).on("click", ".remove_all_shipping_carrier", function(){	
	jQuery('div.all-shipping-carriers-selected').show();
	jQuery('div.shipping-carriers-selected-provider-message').hide();
	jQuery('.bulk_select_provider').prop('checked', true);
	jQuery('#delete_provider_bulk').attr('data-remove', 'all');
});

jQuery(document).on("click", ".reset_providers", function(){	
	
	jQuery('#delete_provider_bulk').attr('data-remove', 'selected-page');
	var reset_checked = jQuery(this).data('reset');	
	var counter = 0;

	if ( 1 == reset_checked ) {
		jQuery('.bulk_select_provider').prop('checked', true);
		jQuery('#delete_provider_bulk').show();
		counter = jQuery('.bulk_select_provider:checked').length;
	} else {
		jQuery('.bulk_select_provider').prop('checked', false);
		jQuery('#delete_provider_bulk').hide();
	}
	jQuery('#selected_provider_total').text(counter);

	jQuery('.provider-settings-ul').hide();
	jQuery('div.shipping-carriers-selected-provider-message').show();	
});

jQuery(document).on("click", ".reset_providers.deselect", function(){	
	jQuery('.provider-settings-ul').hide();
	jQuery('div.shipping-carriers-selected-provider-message, div.all-shipping-carriers-selected').hide();
	jQuery('#delete_provider_bulk').attr('data-remove', 'selected-page');
});

jQuery(document).on("click", ".remove_selected_shipping_carrier", function(){
    jQuery('#delete_provider_bulk').attr('data-remove', 'selected-page');
	jQuery('div.all-shipping-carriers-selected').hide();
	jQuery('div.shipping-carriers-selected-provider-message').show();
	var length_get = jQuery('input[name="bulk_select_provider[]"]:checked').length;
	jQuery('#selected_provider_total').text(length_get);
});

jQuery(document).on("click", "#delete_provider_bulk", function(){

	jQuery('div.all-shipping-carriers-selected').hide();
	jQuery('div.shipping-carriers-selected-provider-message').hide();
	
	var data_remove_selected = jQuery(this).attr('data-remove');
	var selectedProviderValues = jQuery('input[name="bulk_select_provider[]"]:checked').map(function() {
		return jQuery(this).val();
	}).get();

	jQuery("#content1 ").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });
	var r = confirm( shipment_tracking_table_rows.i18n.delete_provider );
	if (r === true) {		
	} else {
		jQuery("#content1").unblock();	
		return;
	}
	
	jQuery('#search_provider').removeAttr('value');	
	jQuery(".provider-settings-ul").hide();
	
	var nonce = jQuery( '#nonce_shipping_provider' ).val();		
	
	var ajax_data = {
		action: 'update_provider_status',
		providers_id: selectedProviderValues,
		data_remove_selected : data_remove_selected,
		security: nonce,	
	};
	
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',
		success: function(response) {
			jQuery(".provider_list").replaceWith(response);
			jQuery('#delete_provider_bulk').hide();
			jQuery("#content1").unblock();					
		},
		error: function(response) {
			console.log(response);			
		}
	});
});

jQuery(document).on("click", ".provider_actions_btn", function(){	
	jQuery(this).siblings('.provider-action-ul').show();
});

jQuery(document).click(function(event){
	var $trigger = jQuery(".grid-provider-settings");
	if($trigger !== event.target && !$trigger.has(event.target).length){
		jQuery(".provider-action-ul").hide();
	}   
});

jQuery(document).on("click", ".upgrade_to_ast_pro", function(){		
	jQuery('.upgrade_to_pro_popup').show();	
});

jQuery(document).on("click", ".sync_providers", function(){		
	jQuery(".provider-settings-ul").hide();
	jQuery('.sync_provider_popup').slideOutForm();
	jQuery("#reset_tracking_providers").prop("checked", false);	
});

jQuery(document).on("click", ".sync_providers_btn", function(){
	var btn = jQuery('.right_side_db_btn .sync_providers_btn');
	btn.html('<div class="dot-carousel"></div>');	
	
	// jQuery('.sync_providers_btn').attr("disabled", true);	
	jQuery('#reset_tracking_providers').val;
	
	var reset_checked = 0;
	if(jQuery('#reset_tracking_providers').prop("checked") == true){
		reset_checked = 1;
	}
	
	jQuery('.sync_message').hide();
	jQuery('#search_provider').removeAttr('value');
	var nonce = jQuery( '#nonce_shipping_provider' ).val();
	
	jQuery(".sync_provider_popup").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
	});

	var ajax_data = {
		action: 'sync_providers',
		reset_checked: reset_checked,
		security: nonce,		
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',
		dataType: "json",
		success: function(response) {	
			jQuery(".shipping_provider_msg").hide();
			jQuery(".shipping_provider_msg_updated").css('display','block');
			jQuery(".sync_provider_popup").unblock();	
			jQuery(".provider_list").replaceWith(response.html);			
			
			if(response.sync_error == 1 ){
				jQuery( ".sync_message" ).text( response.message );
				jQuery( ".sync_providers_btn" ).text( 'Retry' );				
			} else{
				if(reset_checked == 1){
					jQuery('.reset_db_message').show();
				} else{
					jQuery(".providers_added span").text(response.added);
					if(response.added > 0 ){
						jQuery( ".providers_added" ).append( response.added_html );
					}
					
					jQuery(".providers_updated span").text(response.updated);
					if(response.updated > 0 ){
						jQuery( ".providers_updated" ).append( response.updated_html );
					}
					
					jQuery(".providers_deleted span").text(response.deleted);
					if(response.deleted > 0 ){
						jQuery( ".providers_deleted" ).append( response.deleted_html );
					}	
					jQuery(".synch_result").show();
				}								
			}
			
			jQuery(".reset_db_fieldset").hide();
			jQuery(".sync_providers_btn").attr("disabled", false);
			jQuery(".sync_providers_btn").hide();
			jQuery(".close_synch_popup").show();										
			jQuery( '.tipTip' ).tipTip( {
				'attribute': 'data-tip'		
			} );
		},
		error: function(response) {
			console.log(response);			
		}
	});
});

jQuery(document).on("click", ".add_custom_carriers", function(){
	jQuery(".provider-settings-ul").hide();
	jQuery('.add_custom_carriers_popup').slideOutForm();
	// jQuery('.add_provider_popup').slideInForm();
	jQuery('.custom_provider_instruction').show();
});

jQuery("#search_default_provider").keyup(function(event) {
	if (event.which === 13) {
		jQuery(".search-carrier-icon").click();
	}
});

jQuery(document).on("click", ".arrow_pagination", function () {
	
	var number = jQuery(this).data('number');
	var ajax_data = {
		action: 'shipping_pagination',
		paged: number,
		search:jQuery('#search_default_provider').val(),
		security: jQuery('#nonce_shipping_pagination_provider').val(),
	};

	jQuery("#add_default_carrier_section ").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });

	jQuery.ajax({
		url: ajaxurl,
		data: ajax_data,
		type: 'POST',
		success: function (response) {
			jQuery(".default_privder_list").html(response);
			jQuery("#add_default_carrier_section").unblock();
			jQuery('.add_provider_popup').animate({ scrollTop: 0 }, 'slow');
		},
		error: function (response) {
			console.log(response);
		}
	});
});

jQuery(document).on( "click", ".search-carrier-icon", function(){	
	
	var search_term = jQuery('#search_default_provider').val();
	var nonce = jQuery( '#nonce_shipping_provider' ).val();
	var ajax_data = {
		action: 'search_disabled_default_carrier',
		search_term: search_term,
		security: nonce,	
	};

	jQuery("#add_default_carrier_section ").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });

	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,
		type: 'POST',
		success: function(response) {	
			jQuery(".default_privder_list").html(response);
			jQuery("#add_default_carrier_section").unblock();			
		},
		error: function(response) {	
			console.log(response);				
		}
	});	
});

jQuery(document).on("click", ".add_default_provider", function(){
	var button = jQuery(this);
	var id = jQuery(this).data('id');
	var checked = 1;	
	var nonce = jQuery( '#nonce_shipping_provider' ).val();

	var error;	
	var ajax_data = {
		action: 'update_shipment_status',
		id: id,
		checked: checked,
		security: nonce,	
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,		
		type: 'POST',
		success: function(response) {	
			refresh_shipping_carriers_list();						
			button.html('Added');
			button.prop('disabled', true);			
		},
		error: function(response) {
			console.log(response);			
		}
	});
});

function refresh_shipping_carriers_list() {
	var search_term = '';
	var nonce = jQuery( '#nonce_shipping_provider' ).val();
	var ajax_data = {
		action: 'filter_shipping_provider_list',
		search_term: search_term,
		security: nonce,	
	};

	jQuery.ajax({
		url: ajaxurl,		
		data: ajax_data,
		type: 'POST',
		success: function(response) {	
			jQuery(".provider_list").replaceWith(response);								
		},
		error: function(response) {	
			console.log(response);				
		}
	});	
}

jQuery(document).on("click", ".add_slidout_close", function(){
	jQuery('.add_provider_popup').slideInForm();
	jQuery('.add_custom_carriers_popup').slideInForm();
});
jQuery(document).on("click", ".add_slidout_custom_carriers_close", function(){
	jQuery('.add_custom_carriers_popup').slideInForm();
	jQuery('.add_provider_popup').slideInForm();
});
jQuery(document).on("click", ".right_side_db_btn button", function(){
	// jQuery('.add_provider_popup').slideInForm();
});

jQuery(document).on("click", ".edit_slidout_close", function(){
	jQuery('.edit_provider_popup').slideInForm();
});

jQuery(document).on("click", ".close_synch_popup, .synch_slidout_close", function(){		
	jQuery('.sync_provider_popup').slideInForm();
	jQuery(".sync_message").show();
	jQuery(".reset_db_fieldset").show();
	jQuery(".synch_result").hide();
	jQuery(".reset_db_message").hide();
	jQuery(".view_synch_details").remove();
	jQuery(".updated_details").remove();
	jQuery(".sync_providers_btn").show();
	jQuery(".close_synch_popup").hide();
});

jQuery(document).on("click", "#view_added_details", function(){	
	jQuery('#added_providers').show();
	jQuery(this).hide();
	jQuery('#hide_added_details').show();
});
jQuery(document).on("click", "#hide_added_details", function(){	
	jQuery('#added_providers').hide();
	jQuery(this).hide();
	jQuery('#view_added_details').show();
});

jQuery(document).on("click", "#view_updated_details", function(){	
	jQuery('#updated_providers').show();
	jQuery(this).hide();
	jQuery('#hide_updated_details').show();
});
jQuery(document).on("click", "#hide_updated_details", function(){	
	jQuery('#updated_providers').hide();
	jQuery(this).hide();
	jQuery('#view_updated_details').show();
});

jQuery(document).on("click", "#view_deleted_details", function(){	
	jQuery('#deleted_providers').show();
	jQuery(this).hide();
	jQuery('#hide_deleted_details').show();
});
jQuery(document).on("click", "#hide_deleted_details", function(){	
	jQuery('#deleted_providers').hide();
	jQuery(this).hide();
	jQuery('#view_deleted_details').show();
});

function validate (input) {
	if(jQuery(input).val().trim() == '' || jQuery(input).val().trim() == 0){
		return false;
	}
}

function showerror(element){
	element.css("border","1px solid red");
}
function hideerror(element){
	element.css("border","1px solid #ddd");
}
jQuery(document).on("change", "#wc_ast_status_shipped", function(){
	if(jQuery(this).prop("checked") == true){
		jQuery("[for=show_in_completed] .multiple_label").text('Shipped');
		jQuery("label .shipped_label").text('shipped');
	} else{
		jQuery("[for=show_in_completed] .multiple_label").text('Completed');
		jQuery("label .shipped_label").text('completed');
	}
});

jQuery(document).on("click", ".tab_input", function(){
	var tab = jQuery(this).data('tab');
	var label = jQuery(this).data('label');
	jQuery('.zorem-layout__header .breadcums_page_heading').text(label);
	var url = window.location.protocol + "//" + window.location.host + window.location.pathname+"?page=woocommerce-advanced-shipment-tracking&tab="+tab;
	window.history.pushState({path:url},'',url);	
});

jQuery(document).on("click", ".accordion", function(){
	if ( jQuery(this).hasClass( 'active' ) ) {
		jQuery(this).removeClass( 'active' );
		jQuery(this).siblings( '.panel' ).slideUp( 'slow' );
		jQuery( '.accordion' ).find('span.dashicons').addClass('dashicons-arrow-right-alt2');
		jQuery( '.accordion' ).find('span.ast-accordion-btn').hide();	  
	} else {
		jQuery( '.accordion' ).removeClass( 'active' );
		jQuery(".accordion").find('span.ast-accordion-btn').hide();
		jQuery(".accordion").find('span.dashicons').addClass('dashicons-arrow-right-alt2');	
		jQuery( '.panel' ).slideUp('slow');
		jQuery(this).addClass( 'active' );
		jQuery(this).find('span.dashicons').removeClass('dashicons-arrow-right-alt2');
		jQuery(this).find('span.ast-accordion-btn').show();
		jQuery(this).find('span.ast-accordion-btn button').prop("disabled", true);
		jQuery(this).siblings( '.panel' ).slideDown( 'slow' );	  
	}
});

jQuery(document).on("click", ".woocommerce-save-button", function(e){	
	
	var form = jQuery('#wc_ast_settings_form');
	form.find(".spinner").addClass("active");
	
	jQuery.ajax({
		url: ajaxurl,		
		data: form.serialize(),		
		type: 'POST',		
		success: function(response) {	
			form.find(".spinner").removeClass("active");
			jQuery(document).ast_snackbar( shipment_tracking_table_rows.i18n.data_saved );			
			jQuery( '.accordion' ).removeClass( 'active' );
			jQuery( '.accordion' ).find( 'span.ast-accordion-btn' ).hide();
			jQuery( '.accordion' ).find( 'span.dashicons' ).addClass( 'dashicons-arrow-right-alt2' );
			jQuery( '.panel' ).slideUp( 'slow' );
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
});

jQuery(document).on("change", "#wc_ast_settings_form .ast-settings-toggle,.order_status_toggle,.enable_order_status_email_input,.custom_order_color_select, #wc_ast_status_shipped", function(){	
	jQuery('span.ast-accordion-btn button').prop("disabled", false);
});
		
jQuery('#wc_ast_status_partial_shipped_label_color').wpColorPicker({
	change: function(e, ui) {
		var color = ui.color.toString();			
		jQuery('.order-status-table .order-label.wc-partially-shipped').css('background',color);
	},
});

jQuery('#wc_ast_status_label_color').wpColorPicker({
	change: function(e, ui) {
		var color = ui.color.toString();			
		jQuery('.order-status-table .order-label.wc-delivered').css('background',color);
	},
});

jQuery('#wc_ast_status_updated_tracking_label_color').wpColorPicker({
	change: function(e, ui) {
		var color = ui.color.toString();			
		jQuery('.order-status-table .order-label.wc-updated-tracking').css('background',color);
	},
});

jQuery('body').click( function(){	
	if ( jQuery('.order-status-table button.button.wp-color-result').hasClass( 'wp-picker-open' ) ) { 
		jQuery('span.ast-accordion-btn button').prop("disabled", false);
	}
});

jQuery('.order-status-table button.button.wp-color-result').click( function(){	
	if ( jQuery(this).hasClass( 'wp-picker-open' ) ) {}else{jQuery('span.ast-accordion-btn button').prop("disabled", false);}
});
jQuery(".wc_ast_api_date_format").on("click", function (e) { 
	jQuery('span.ast-accordion-btn button').prop("disabled", false);
});
jQuery( "#wc_ast_show_orders_actions,#wc_ast_unclude_tracking_info" ).on("change", function (e) { 
	jQuery('span.ast-accordion-btn button').prop("disabled", false); 
});

jQuery('#wc_ast_unclude_tracking_info').on('select2:unselecting', function(e){
	if ( jQuery(e.params.args.data.element).val() == 'partial-shipped' || jQuery(e.params.args.data.element).val() == 'shipped' || jQuery(e.params.args.data.element).val() == 'completed' ) {
		e.preventDefault();
	}
});

jQuery( ".ud-checkbox li" ).on("click", function (e) { 
	var ast_optin_email_notification = jQuery("#ast_optin_email_notification").prop("checked");
	var ast_enable_usage_data = jQuery("#ast_enable_usage_data").prop("checked");
	if ( false == ast_optin_email_notification && false == ast_enable_usage_data ) {
		jQuery('.submit_usage_data').prop("disabled", true);
	} else {
		jQuery('.submit_usage_data').prop("disabled", false);
	}
});

jQuery(document).on("click", ".integration_settings", function(){
	var nonce = jQuery( '#integrations_settings_form_nonce' ).val();
	var integration_id = jQuery(this).data('iid');
	var ajax_data = {
		action: 'integration_settings_slideout',
		security: nonce,
		integration_id: integration_id,	
	};

	jQuery("#integrations_content").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
	});

	jQuery.ajax({
		url: ajaxurl,
		data: ajax_data,
		type: 'POST',
		//dataType:"json",	
		success: function(response) {
			jQuery("#integrations_content").unblock();
			jQuery(".integration_settings_popup").html(response);
			jQuery('.integration_settings_popup').slideOutForm();
		},
		error: function(response) {
			console.log(response);
		}
	});	
});

jQuery(document).on("click", ".integration_slidout_close", function(){
	jQuery('.integration_settings_popup').slideInForm();	
});

/* zorem_snackbar jquery */
(function( $ ){
	$.fn.ast_snackbar = function(msg) {
		if ( jQuery('.snackbar-logs').length === 0 ){
			$("body").append("<section class=snackbar-logs></section>");
		}
		var ast_snackbar = $("<article></article>").addClass('snackbar-log snackbar-log-success snackbar-log-show').text( msg );
		$(".snackbar-logs").append(ast_snackbar);
		setTimeout(function(){ ast_snackbar.remove(); }, 3000);
		return this;
	}; 
})( jQuery );

/* zorem_snackbar_warning jquery */
(function( $ ){
	$.fn.ast_snackbar_warning = function(msg) {
		if ( jQuery('.snackbar-logs').length === 0 ){
			$("body").append("<section class=snackbar-logs></section>");
		}
		var ast_snackbar_warning = $("<article></article>").addClass( 'snackbar-log snackbar-log-error snackbar-log-show' ).html( msg );
		$(".snackbar-logs").append(ast_snackbar_warning);
		setTimeout(function(){ ast_snackbar_warning.remove(); }, 3000);
		return this;
	}; 
})( jQuery );

(function($) {
	$.fn.slideOutForm = function() {
		var $formContainer = $(this);
		$formContainer.addClass('slideout');
		var htmlContent = '<div class="append_slideout"></div>';
		$('body').append(htmlContent);
		$('body').css('overflow', 'hidden');
	};
	$.fn.slideInForm = function() {
		var $formContainer = $(this);   
		$formContainer.removeClass('slideout');
		$('.append_slideout').remove();
		$('body').css('overflow', '');
	};
  })(jQuery);
