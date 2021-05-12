<div class="tracking-header">
	<?php echo do_action("trackship_tracking_header_before",$order_id, $tracker, $item['formatted_tracking_provider'], $item['tracking_number']);?>
	<div class="provider_image_div" style="<?php if($hide_tracking_provider_image == 1) { echo 'display:none'; };  ?>">
		<img class="provider_image" src="<?php echo $item['tracking_provider_image']; ?>">
	</div>
	<div class="tracking_number_div">
		<ul>			
			<li>
				<?php echo apply_filters( 'ast_provider_title', esc_html( $item['formatted_tracking_provider'] )); ?>:</span> 
				<?php if ( $wc_ast_link_to_shipping_provider == 1 ) { ?>
					<a href="<?php echo $item['formatted_tracking_link']; ?>" target="blank"><strong><?php echo $item['tracking_number']; ?></strong></a>	
				<?php } else{ ?>
					<strong><?php echo $item['tracking_number']; ?></strong>	
				<?php } ?>
			</li>
		</ul>
	</div>					
	<h1 class="shipment_status_heading <?php echo $tracker->ep_status; ?>">
		<?php echo apply_filters("trackship_status_filter",$tracker->ep_status);?>
	</h1>	
	<span class="est_delivery_date">
		<?php _e( 'Est. Delivery Date', 'woo-advanced-shipment-tracking' ); ?>: <strong>
		<?php 
		if($tracker->est_delivery_date){
			echo date_i18n( 'l, M d', strtotime( $tracker->est_delivery_date ) );
		} else{
			echo 'N/A';
		} ?></strong>				
	</span>		
</div>
