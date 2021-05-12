/*
 * Customizer Scripts
 * Need to rewrite and clean up this file.
 */

jQuery(document).ready(function() {

    /**
     * Change description
     */	 	
	jQuery('#customize-theme-controls #accordion-section-themes').hide();			
	jQuery( '#sub-accordion-section-trackship_shipment_status_email .customize-section-title > h3 .customize-action' ).append( '<span class="dashicons dashicons-arrow-right" style="padding-top:4px;"></span> '+wcast_customizer.customizer_title );
	jQuery( '#sub-accordion-section-ast_tracking_page_section .customize-section-title > h3 .customize-action' ).append( '<span class="dashicons dashicons-arrow-right" style="padding-top:4px;"></span> '+wcast_customizer.customizer_title );
	jQuery( '#sub-accordion-section-ast_tracking_general_section .customize-section-title > h3 .customize-action' ).append( '<span class="dashicons dashicons-arrow-right" style="padding-top:4px;"></span> '+wcast_customizer.customizer_title );
	jQuery( '#sub-accordion-section-custom_order_status_email .customize-section-title > h3 .customize-action' ).append( '<span class="dashicons dashicons-arrow-right" style="padding-top:4px;"></span> '+wcast_customizer.customizer_title );
	jQuery( '.accordion-section .panel-title' ).html(wcast_customizer.customizer_title);
});	

// Handle mobile button click
function custom_size_mobile() {
	// get email width.
	var email_width = '684';
	var ratio = email_width/304;
	var framescale = 100/ratio;
	var framescale = framescale/100;
	jQuery('#customize-preview iframe').width(email_width+'px');
	jQuery('#customize-preview iframe').css({
			'-webkit-transform' : 'scale(' + framescale + ')',
			'-moz-transform'    : 'scale(' + framescale + ')',
			'-ms-transform'     : 'scale(' + framescale + ')',
			'-o-transform'      : 'scale(' + framescale + ')',
			'transform'         : 'scale(' + framescale + ')'
	});
}
jQuery('#customize-footer-actions .preview-mobile').click(function(e) {
	custom_size_mobile();
});
	jQuery('#customize-footer-actions .preview-desktop').click(function(e) {
	jQuery('#customize-preview iframe').width('100%');
	jQuery('#customize-preview iframe').css({
			'-webkit-transform' : 'scale(1)',
			'-moz-transform'    : 'scale(1)',
			'-ms-transform'     : 'scale(1)',
			'-o-transform'      : 'scale(1)',
			'transform'         : 'scale(1)'
	});
});
jQuery('#customize-footer-actions .preview-tablet').click(function(e) {
	jQuery('#customize-preview iframe').width('100%');
	jQuery('#customize-preview iframe').css({
			'-webkit-transform' : 'scale(1)',
			'-moz-transform'    : 'scale(1)',
			'-ms-transform'     : 'scale(1)',
			'-o-transform'      : 'scale(1)',
			'transform'         : 'scale(1)'
	});
});
	
(function ( api ) {
    api.section( 'custom_order_status_email', function( section ) {		
        section.expanded.bind( function( isExpanded ) {	
			var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
				var order_status = jQuery(".preview_email_type option:selected").val();				
				
				if(order_status == 'partially_shipped'){					
					url = wcast_customizer.partial_shipped_email_preview_url;
					api.previewer.previewUrl.set( url );	
				} else if(order_status == 'updated_tracking'){
					url = wcast_customizer.updated_tracking_email_preview_url;
					api.previewer.previewUrl.set( url );
				} else if(order_status == 'shipped'){
					url = wcast_customizer.shipped_email_preview_url;
					api.previewer.previewUrl.set( url );
				}					
            }
        } );
    } );
} ( wp.customize ) );

(function ( api ) {
    api.section( 'trackship_shipment_status_email', function( section ) {		
        section.expanded.bind( function( isExpanded ) {	
			var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
				var shipment_status = jQuery(".preview_shipment_status_type option:selected").val();				
				
				if(shipment_status == 'in_transit'){					
					url = wcast_customizer.customer_intransit_preview_url;
					api.previewer.previewUrl.set( url );
				} else if(shipment_status == 'on_hold'){					
					url = wcast_customizer.customer_onhold_preview_url;
					api.previewer.previewUrl.set( url );	
				} else if(shipment_status == 'return_to_sender'){
					url = wcast_customizer.customer_returntosender_preview_url;
					api.previewer.previewUrl.set( url );
				} else if(shipment_status == 'available_for_pickup'){
					url = wcast_customizer.customer_availableforpickup_preview_url;
					api.previewer.previewUrl.set( url );
				} else if(shipment_status == 'out_for_delivery'){
					url = wcast_customizer.customer_outfordelivery_preview_url;
					api.previewer.previewUrl.set( url );
				} else if(shipment_status == 'delivered'){
					url = wcast_customizer.customer_delivered_preview_url;
					api.previewer.previewUrl.set( url );
				} else if(shipment_status == 'failure'){
					url = wcast_customizer.customer_failure_preview_url;
					api.previewer.previewUrl.set( url );
				} else if(shipment_status == 'exception'){
					url = wcast_customizer.customer_exception_preview_url;
					api.previewer.previewUrl.set( url );
				}				
            }
        } );
    } );
} ( wp.customize ) );

(function ( api ) {
    api.section( 'ast_tracking_general_section', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.tracking_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );

(function ( api ) {
    api.section( 'ast_tracking_page_section', function( section ) {		
        section.expanded.bind( function( isExpanded ) {				
            var url;
            if ( isExpanded ) {
				jQuery('#save').trigger('click');
                url = wcast_customizer.tracking_page_preview_url;
                api.previewer.previewUrl.set( url );
            }
        } );
    } );
} ( wp.customize ) );


jQuery(document).on("change", ".preview_order_select", function(){
	var wcast_preview_order_id = jQuery(this).val();
	var data = {
		action: 'update_email_preview_order',
		wcast_preview_order_id: wcast_preview_order_id,	
	};
	jQuery.ajax({
		url: ajaxurl,		
		data: data,
		type: 'POST',
		success: function(response) {			
			jQuery(".preview_order_select option[value="+wcast_preview_order_id+"]").attr('selected', 'selected');			
		},
		error: function(response) {
			console.log(response);			
		}
	});	
});

wp.customize( 'wcast_order_status_email_type', function( value ) {		
	value.bind( function( wcast_order_status_email_type ) {		
		if(wcast_order_status_email_type == 'partially_shipped'){
			wp.customize.previewer.previewUrl(wcast_customizer.partial_shipped_email_preview_url);
			wp.customize.previewer.refresh();	
		} else if(wcast_order_status_email_type == 'updated_tracking'){
			wp.customize.previewer.previewUrl(wcast_customizer.updated_tracking_email_preview_url);
			wp.customize.previewer.refresh();
		} else if(wcast_order_status_email_type == 'shipped'){
			wp.customize.previewer.previewUrl(wcast_customizer.shipped_email_preview_url);
			wp.customize.previewer.refresh();
		}					
	});
});
jQuery(document).ready(function() {
	var email_type = wcast_customizer.email_type;
	jQuery(".preview_email_type").val(email_type);	
	
	var shipment_status = wcast_customizer.shipment_status;
	jQuery(".preview_shipment_status_type").val(shipment_status);
});

wp.customize( 'wcast_shipment_status_type', function( value ) {		
	value.bind( function( wcast_shipment_status_type ) {
		
		if(wcast_shipment_status_type == 'in_transit'){
			wp.customize.previewer.previewUrl(wcast_customizer.customer_intransit_preview_url);
			wp.customize.previewer.refresh();	
		} else if(wcast_shipment_status_type == 'on_hold'){
			wp.customize.previewer.previewUrl(wcast_customizer.customer_onhold_preview_url);
			wp.customize.previewer.refresh();	
		} else if(wcast_shipment_status_type == 'return_to_sender'){
			wp.customize.previewer.previewUrl(wcast_customizer.customer_returntosender_preview_url);
			wp.customize.previewer.refresh();	
		} else if(wcast_shipment_status_type == 'available_for_pickup'){
			wp.customize.previewer.previewUrl(wcast_customizer.customer_availableforpickup_preview_url);
			wp.customize.previewer.refresh();	
		} else if(wcast_shipment_status_type == 'out_for_delivery'){
			wp.customize.previewer.previewUrl(wcast_customizer.customer_outfordelivery_preview_url);
			wp.customize.previewer.refresh();	
		} else if(wcast_shipment_status_type == 'delivered'){			
			wp.customize.previewer.previewUrl(wcast_customizer.customer_delivered_preview_url);
			wp.customize.previewer.refresh();	
		} else if(wcast_shipment_status_type == 'failure'){
			wp.customize.previewer.previewUrl(wcast_customizer.customer_failure_preview_url);
			wp.customize.previewer.refresh();	
		} else if(wcast_shipment_status_type == 'exception'){
			wp.customize.previewer.previewUrl(wcast_customizer.customer_exception_preview_url);
			wp.customize.previewer.refresh();	
		} 				
	});
});