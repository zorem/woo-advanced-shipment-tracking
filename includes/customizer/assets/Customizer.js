/*** Default JS ***/

/* efc_snackbar jquery */
(function( $ ){
	$.fn.efc_snackbar = function(msg) {
		if ( jQuery('.snackbar-logs').length === 0 ){
			$("body").append("<section class=snackbar-logs></section>");
		}
		var efc_snackbar = $("<article></article>").addClass('snackbar-log snackbar-log-success snackbar-log-show').text( msg );
		$(".snackbar-logs").append(efc_snackbar);
		setTimeout(function(){ efc_snackbar.remove(); }, 3000);
		return this;
	}; 
})( jQuery );

/* efc_snackbar_warning jquery */
(function( $ ){
	$.fn.efc_snackbar_warning = function(msg) {
		if ( jQuery('.snackbar-logs').length === 0 ){
			$("body").append("<section class=snackbar-logs></section>");
		}
		var efc_snackbar_warning = $("<article></article>").addClass( 'snackbar-log snackbar-log-error snackbar-log-show' ).html( msg );
		$(".snackbar-logs").append(efc_snackbar_warning);
		setTimeout(function(){ efc_snackbar_warning.remove(); }, 3000);
		return this;
	}; 
})( jQuery );
/*header script end*/ 

/*
on change alert box open
*/
jQuery(document).on("click", ".back_to_notice", function(){
	var r = confirm( 'The changes you made will be lost if you navigate away from this page.' );
	if (r === true ) {
	} else {	
		return false;
	}
});

function setting_change_trigger() {	
	jQuery('.woocommerce-save-button').removeAttr("disabled");
	jQuery('.zoremmail-layout-content-header .button-trackship').html('Save Changes');
	jQuery('.zoremmail-back-wordpress-link').addClass('back_to_notice');
}

function save_customizer_email_setting(){
	
	jQuery('.zoremmail-layout-content-preview').addClass('customizer-unloading');
	setting_change_trigger();
	
	var form = jQuery('#zoremmail_email_options');
	jQuery.ajax({
		url: ajaxurl,
		data: form.serialize(),
		type: 'POST',
		dataType:"json",		
		success: function(response) {			
			if( response.success === "true" ){
				jQuery('iframe').attr('src', jQuery('iframe').attr('src'));
				jQuery('.zoremmail-back-wordpress-link').removeClass('back_to_notice');
			}
		},
		error: function(response) {
			console.log(response);			
		}
	});
}


var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return typeof sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
    return false;
};

jQuery(document).on("click", ".zoremmail-sub-panel-title", function(){
	
	var label = jQuery(this).data('label');
	var id = jQuery(this).attr('id');
	var Status = jQuery('#orderStatus').val();

	//jQuery('.zoremmail-sub-panels').hide();
	jQuery('.customize-action-default').hide(); // hide default back heading
	jQuery('.customize-action-changed').show(); // Show back chanegd heading
	jQuery('.zoremmail-sub-panel-title').hide();
	jQuery( ".customize-section-back" ).removeClass('panels').addClass('sub-panels').show();
	
	var type = jQuery(this).data('type');
	var parent_label = jQuery('.zoremmail-panels #'+type).data('label');
	jQuery( '.zoremmail-sub-panel-heading.'+ type +' .customize-action-changed' ).html( 'Customizing <span class="dashicons dashicons-arrow-right-alt2"></span> '+parent_label );
	jQuery( '.zoremmail-sub-panel-heading.'+ type +' .sub_heading' ).html( label );
	
	
	jQuery('.zoremmail-menu-submenu-title').each(function(index, element) {
		if ( jQuery(this).data('id') ===  id ) {
			jQuery(this).addClass('open');
			jQuery(this).next('.zoremmail-menu-contain').addClass('active');
		} else {
			jQuery(this).removeClass('open');
			jQuery(this).next('.zoremmail-menu-contain').removeClass('active');
		}
	});		
	
	change_submenu_item();
	
	jQuery( ".tgl-btn-parent span" ).hide();
	
	var type = getUrlParameter('type');
	if ( type == 'email_content' ) {
		jQuery( ".tgl-btn-parent .tgl_"+Status ).show();
	}
	
	
});

jQuery(document).on("click", "#zoremmail_email_options .button-trackship", function( e ){
	"use strict";
	var form = jQuery('#zoremmail_email_options');
	var btn = jQuery('#zoremmail_email_options .button-trackship');
	jQuery.ajax({
		url: ajaxurl,//csv_workflow_update,		
		data: form.serialize(),
		type: 'POST',
		dataType:"json",
		beforeSend: function(){
			btn.prop('disabled', true).html('Please wait..');
		},		
		success: function(response) {
			if( response.success === "true" ){
				btn.prop('disabled', true).html('Saved');
				jQuery(document).efc_snackbar( "Settings Successfully Saved." );
				jQuery('.zoremmail-back-wordpress-link').removeClass('back_to_notice');
			} else {
				if( response.permission === "false" ){
					btn.prop('disabled', false).html('Save Changes');
					jQuery(document).efc_snackbar_warning( "you don't have permission to save settings." );
				}
			}
		},
		error: function(response) {
			console.log(response);			
		}
	});
	e.preventDefault();
});

function change_submenu_item() {
	var Status = jQuery('#orderStatus,#order_status').val();
	jQuery( '.all_status_submenu' ).hide();
	jQuery( '.all_status_submenu.' + Status + '_sub_menu' ).show();
}

jQuery('iframe').load(function(){
	jQuery('.zoremmail-layout-content-preview').removeClass('customizer-unloading');
	jQuery("#email_preview").contents().find( 'div#query-monitor-main' ).css( 'display', 'none');
	jQuery( '.zoremmail-layout-content-media .last-checked .dashicons' ).trigger('click');	

	document.getElementById("email_preview").contentWindow.document.body.onclick = 
	function() {	
		jQuery('#orderStatus').select2('close');
		jQuery('#order_preview').select2('close');
	}
});

jQuery(document).on("click", ".customize-section-back", function(){
	if ( jQuery(this).hasClass('panels') ) {		
		jQuery( '.header_mockup_order' ).hide();
		jQuery('.sub_options_panel').hide();
		jQuery( ".customize-section-back, .customize-action-changed" ).hide();
		jQuery( ".zoremmail-panel-title, .customize-action-default" ).show();
		jQuery( ".zoremmail-panels" ).show();
		jQuery('.zoremmail-sub-panel-heading').removeClass('open');
		jQuery('.zoremmail-sub-panel-heading').removeClass('active');
	}
	if ( jQuery(this).hasClass('sub-panels') ) {
		
		jQuery( '.breadcrumb-sub-panel-title' ).remove();
		jQuery( ".customize-section-back" ).removeClass('sub-panels').addClass('panels');
		jQuery( ".zoremmail-sub-panels" ).show();
		
		/*if ( jQuery('.zoremmail-sub-panel-title:visible').length == 1 ) {
			jQuery(this).trigger('click');
		}*/

		var parent = jQuery(this).parents('.zoremmail-sub-panel-heading'); 
		
		if ( parent.hasClass( 'email_content' ) ) {
			jQuery(this).trigger('click');
		} else {
			var id = parent.data('id');
			jQuery('.customize-action-changed').hide();
			jQuery('.customize-action-default').show();
			var parent_label = jQuery('.zoremmail-panels #'+id).data('label');
			jQuery( '.zoremmail-sub-panel-heading .sub_heading' ).html( parent_label );
			jQuery('.zoremmail-sub-panel-title.'+id).show();
		}

		jQuery('.zoremmail-menu-contain').removeClass('active');
		jQuery('.zoremmail-menu-submenu-title').removeClass('open');
		jQuery('.zoremmail-menu-submenu-title').removeClass('active');
	}
});

jQuery(document).on("click", ".send-to-email", function(){	
	"use strict";
	jQuery("#send_to_email_option_html").show();
});

jQuery(document).on("click", ".popupclose, .popup_close_icon", function(){
	"use strict";
	jQuery('#send_to_email_option_html').hide();
});

/*** Default JS end***/

/*** Custom JS ***/

jQuery(document).ready(function(){
	jQuery( '#orderStatus' ).select2({
		width: '250px',
		templateSelection: text_contain,
		minimumResultsForSearch: Infinity,		
	});	
	
	jQuery( '#order_preview' ).select2({		
		minimumResultsForSearch: Infinity,
		width: '250px'
	});	
});

jQuery(window).on("load",function(){
	
	jQuery("#email_preview").contents().find('.hide').hide();

	var display_shipped_header = jQuery('#fluid_display_shipped_header').is(':checked');	
	if ( display_shipped_header ){
		jQuery(".fluid_tracker_type").show();	
	} else {
		jQuery(".fluid_tracker_type").hide();		
	}
});


jQuery('#hide_trackig_header').on("change", function() {
	setting_change_trigger();
	var isChecked = jQuery(this).is(':checked');
	if ( isChecked ) {
		jQuery('.header_text_change').hide();
		jQuery("#email_preview").contents().find('.header_text').hide();
	} else {
		jQuery('.header_text_change').show();
		jQuery("#email_preview").contents().find('.header_text').show();
	}	
});

jQuery('#header_text_change').on("keyup", function() {
	setting_change_trigger();
	var header_text = jQuery(this).val();
	jQuery("#email_preview").contents().find('.header_text').text(header_text);
});	

jQuery('#additional_header_text').on("keyup", function() {
	setting_change_trigger();
	var addition_header = jQuery(this).val();
	jQuery("#email_preview").contents().find('.addition_header').text(addition_header);
});	

jQuery('#fluid_display_shipped_header').on("click", function(){
	setting_change_trigger();
	var display_shipped_header = jQuery(this).is(':checked');	
	if ( display_shipped_header ){
		jQuery(".fluid_tracker_type").show();
		jQuery("#email_preview").contents().find('.fluid_header_tr').show();
	} else {
		jQuery(".fluid_tracker_type").hide();
		jQuery("#email_preview").contents().find('.fluid_header_tr').hide();
	}	
});

jQuery('#fluid_tracker_type').on("change", function(){
	setting_change_trigger();
	var tracker_type = jQuery(this).val();
	if ( 'hide' == tracker_type ) {
		jQuery("#email_preview").contents().find('.tracker_tr').hide();
		jQuery("#email_preview").contents().find('.mobile_tracker_image').hide();		
	} else{
		jQuery("#email_preview").contents().find('.tracker_tr').show();
		jQuery("#email_preview").contents().find('.mobile_tracker_image').show();
		var tracker_img = ast_customizer.plugin_dir_url+'assets/images/'+tracker_type+'.png';
		var mobile_tracker_img = ast_customizer.plugin_dir_url+'assets/images/mobile_'+tracker_type+'.png';
		jQuery("#email_preview").contents().find('.tracker_image').attr('src', tracker_img);
		jQuery("#email_preview").contents().find('.mobile_tracker_image').attr('src', mobile_tracker_img);
	}		
});

jQuery('#fluid_table_background_color').wpColorPicker({
	change: function(e, ui) {	
		setting_change_trigger();
		var color = ui.color.toString();				
		jQuery("#email_preview").contents().find('.fluid_table').css('background',color);				
	}, 	
});	

jQuery('#fluid_table_border_color').wpColorPicker({
	change: function(e, ui) {	
		setting_change_trigger();
		var color = ui.color.toString();				
		jQuery("#email_preview").contents().find('.fluid_table').css('border-color',color);
		jQuery("#email_preview").contents().find('.tracker_tr td').css('border-color',color);				
	}, 	
});	

jQuery('#fluid_table_border_radius').on("change", function() {
	setting_change_trigger();
	var border_radius = jQuery(this).val();
	jQuery("#email_preview").contents().find('.fluid_table').css( 'border-radius', border_radius+'px' );	
});

/*jQuery('#fluid_table_padding').on("change", function() {
	setting_change_trigger();
	var padding = jQuery(this).val();
	jQuery("#email_preview").contents().find('.fluid_table tr td').css( 'padding', padding+'px' );		
});*/

jQuery('#fluid_hide_provider_image').on("click", function(){
	setting_change_trigger();
	var hide_img = jQuery(this).is(':checked');	
	if ( hide_img ){
		jQuery("#email_preview").contents().find('.fluid_provider_img').hide();
	} else {
		jQuery("#email_preview").contents().find('.fluid_provider_img').show();
	}	
});

/*jQuery('#fluid_hide_shipping_date').on("click", function(){
	setting_change_trigger();
	var hide_date = jQuery(this).is(':checked');	
	if ( hide_date ){
		jQuery("#email_preview").contents().find('.shipped_on').hide();
	} else {
		jQuery("#email_preview").contents().find('.shipped_on').show();
	}	
});*/

jQuery('#fluid_button_text').on("keyup", function(){
	setting_change_trigger();
	var fluid_button_text = jQuery(this).val();
	jQuery("#email_preview").contents().find('.track-button').text(fluid_button_text);

});	

jQuery('#fluid_button_background_color').wpColorPicker({
	change: function(e, ui) {	
		setting_change_trigger();
		var color = ui.color.toString();				
		jQuery("#email_preview").contents().find('.track-button').css('background',color);				
	}, 	
});	

jQuery('#fluid_button_font_color').wpColorPicker({
	change: function(e, ui) {	
		setting_change_trigger();
		var color = ui.color.toString();				
		jQuery("#email_preview").contents().find('.track-button').css('color',color);				
	}, 	
});

jQuery('#fluid_button_radius').on("change", function() {
	setting_change_trigger();
	var fluid_button_radius = jQuery(this).val();
	jQuery("#email_preview").contents().find('.track-button').css( 'border-radius', fluid_button_radius+'px' );	
});

jQuery('.fluid_button_size input').on("change", function() {
	setting_change_trigger();
	var fluid_button_size = jQuery(this).val();
	if ( 'large' == fluid_button_size ) {
		jQuery("#email_preview").contents().find('.track-button').css( 'padding', '12px 25px' );
		jQuery("#email_preview").contents().find('.track-button').css( 'font-size', '16px' );	
	} else {
		jQuery("#email_preview").contents().find('.track-button').css( 'padding', '10px 15px' );
		jQuery("#email_preview").contents().find('.track-button').css( 'font-size', '14px' );	
	}
	
});

jQuery('#completed_heading,#partial_shipped_heading,#shipped_heading').on("keyup", function(){
	setting_change_trigger();
	var heading = jQuery(this).val();
	jQuery("#email_preview").contents().find('#header_wrapper h1').text(heading);

});	

jQuery('#completed_email_content,#partial_shipped_email_content,#shipped_email_content').on("blur", function(){
	setting_change_trigger();
	//var email_content = jQuery(this).val();
	//jQuery("#email_preview").contents().find('.email_content p').text(email_content);
	save_customizer_email_setting();
});	

jQuery('#display_tracking_info_at,#fluid_table_layout,#fluid_tracker_type,#fluid_table_border_radius,#fluid_table_padding,#fluid_button_radius,.fluid_button_size input').on("change", function(){
	setting_change_trigger();
});

jQuery('#order_preview').on("change", function(){
	save_customizer_email_setting();
});

jQuery('.zoremmail-checkbox,.ast-toggle').on("click", function(){
	setting_change_trigger();
});

jQuery('.zoremmail-input.text,.zoremmail-input.textarea').on("keyup", function(){
	setting_change_trigger();
});	

jQuery( ".zoremmail-layout-content-media .dashicons" ).on( "click", function() {
	jQuery(this).parent().siblings().removeClass('last-checked');
	var width = jQuery(this).parent().data('width');
	var iframeWidth = jQuery(this).parent().data('iframe-width');
	jQuery('#template_container, #template_body').css('width', width);
	jQuery( ".zoremmail-layout-content-media .dashicons" ).css('color', '#bdbdbd');
	jQuery(this).parent().addClass('last-checked');
	jQuery(this).css('color', '#005B9A');
	jQuery("#email_preview").css('width', iframeWidth);
	jQuery("#email_preview").contents().find('#template_container, #template_body, #template_footer').css('width', width);
});

function text_contain(state) {
	return 'Preview: ' + state.text;
}

jQuery(document).on("change", "#orderStatus,#order_status", function(){
	
	"use strict";
	jQuery('.zoremmail-layout-content-preview').addClass('customizer-unloading');
	var Status = jQuery(this).val();
	var sPageURL = window.location.href.split('&')[0];
	var id = getUrlParameter('type');
	
	if ( Status == 'pickup' ) {
		jQuery('.customize-section-back').trigger('click');
		jQuery('#email_content').trigger('click');		
	}
	
	window.history.pushState("object or string", sPageURL, sPageURL+'&type='+id+'&email_type='+Status);

	var iframe_url = ast_customizer.email_iframe_url+'&type='+id+'&email_type='+Status;
	jQuery('.options_panel').attr('data-iframe_url',iframe_url);
	jQuery('iframe').attr('src', iframe_url);

	jQuery("#email_preview").contents().find('.hide').hide();

	change_submenu_item();
	jQuery( ".tgl-btn-parent span" ).hide();
	var type = getUrlParameter('type');
	if ( type == 'email_content' ) {
		jQuery( ".tgl-btn-parent .tgl_"+Status ).show();
	}

});
	
jQuery(document).on("click", ".zoremmail-panel-title", function(){
	
	var lable = jQuery(this).data('label');
	var id = jQuery(this).attr('id');
	var Status = jQuery('#orderStatus').val();
	
	jQuery('.zoremmail-panels').hide();
	jQuery('.sub_options_panel').hide();
	jQuery('.sider-heading').show();
	jQuery( ".zoremmail-panel-title, .zoremmail-layout-sider-heading .main_logo" ).hide();
	jQuery( '.zoremmail-sub-panel-heading.sub_options_panel.'+id ).addClass('open'); //subpanels back			

	jQuery('.zoremmail-sub-panels, .zoremmail-sub-panels li.'+id).show();
	
	jQuery( ".customize-section-back" ).addClass('panels').show();
	jQuery('.tgl-btn-parent').show();
	jQuery( '.customizer_Breadcrumb' ).html( ' > ' + lable );
	
	var sPageURL = window.location.href.split('&')[0];
	window.history.pushState("object or string", sPageURL, sPageURL+'&type='+id+'&email_type='+Status);
	
	var iframe_url = ast_customizer.email_iframe_url+'&type='+id+'&email_type='+Status;	
	
	jQuery('.options_panel').attr('data-iframe_url',iframe_url);
	jQuery('iframe').attr('src', iframe_url);	

	jQuery('#email_preview').on("load", function() {
		jQuery("#email_preview").contents().find('.hide').hide();
	});	
	
	if ( jQuery('.zoremmail-sub-panel-title:visible').length == 1) {
		jQuery(".zoremmail-sub-panel-title:visible").trigger('click');
	}			
	
	change_submenu_item();
	jQuery( ".tgl-btn-parent span" ).hide();
	var type = getUrlParameter('type');
	if ( type == 'email_content' ) {		
		jQuery( ".tgl-btn-parent .tgl_"+Status ).show();
	}
	
	// chaneg back div sub heading
	jQuery( '.zoremmail-sub-panel-heading .sub_heading' ).html( lable );

});

jQuery(document).on("click", ".customize-section-back", function(){
	var Status = jQuery('#orderStatus').val();
	if ( Status == 'pickup' ) {
		jQuery('#email_design').hide();
	} else {
		jQuery('#email_design').show();
	}

});

/*** Custom JS end ***/