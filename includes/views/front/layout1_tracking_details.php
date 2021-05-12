<?php 
if($hide_tracking_events == 1)return;
?>
<div class="tracking-details" style="">	
	<?php 
	if($hide_tracking_events == 2){
	if(!empty($tracking_details_by_date)){ ?>
		
		<div class="shipment_progress_heading_div">	               				
			<h4 class="h4-heading text-uppercase"><?php _e( 'Tracking Details', 'woo-advanced-shipment-tracking' ); ?></h4>					
		</div>	
		
		<?php if(!empty($trackind_destination_detail_by_status_rev)){ ?>
		
		<div class="tracking_destination_details_by_date">
			
			<h4 style=""><?php _e( 'Destination Details', 'woo-advanced-shipment-tracking' ); ?></h4>
			<ul class="timeline new-details">	
				<?php $a = 1; 
					foreach( $trackind_destination_detail_by_status_rev as $key => $value ){ 
					if( $a > 2)break;	
					$date = date('Y-m-d', strtotime($value->datetime)); ?>
					<li>
						<strong><?php echo date_i18n( get_option( 'date_format' ), strtotime($date) ); ?> <?php echo date_i18n( get_option( 'time_format' ), strtotime($value->datetime) )?></strong>
						<p><?php echo apply_filters( 'trackship_tracking_event_description', $value->message ); if($value->tracking_location->city != NULL)echo ' - '; ?><span><?php echo apply_filters( 'trackship_tracking_event_location', $value->tracking_location->city ); ?></span></p>					
					</li>						
				<?php $a++; } ?>
			</ul>	
			
			<ul class="timeline old-details" style="display:none;">	
				<?php 
					$a = 1;	
					foreach($trackind_destination_detail_by_status_rev as $key => $value){
					if( $a <= 2 ){  $a++; continue; }
					$date = date('Y-m-d', strtotime($value->datetime)); ?>
					<li>
						<strong><?php echo date_i18n( get_option( 'date_format' ), strtotime($date) ); ?> <?php echo date_i18n( get_option( 'time_format' ), strtotime($value->datetime) )?></strong>
						<p><?php echo apply_filters( 'trackship_tracking_event_description', $value->message ); if($value->tracking_location->city != NULL)echo ' - '; ?><span><?php echo apply_filters( 'trackship_tracking_event_location', $value->tracking_location->city ); ?></span></p>					
					</li>						
				<?php $a++; } ?>
			</ul>	
		</div>
		
		<?php } ?>
		
		<div class="tracking_details_by_date">
			
			<?php if(!empty($trackind_destination_detail_by_status_rev)){ ?>
				<h4 class="" style=""><?php _e( 'Origin Details', 'woo-advanced-shipment-tracking' ); ?></h4>
			<?php } ?> 
			
			<ul class="timeline new-details">	
				<?php $a = 1; 
					foreach( $trackind_detail_by_status_rev as $key => $value ){ 
					if( $a > 2)break;	
					$date = date('Y-m-d', strtotime($value->datetime)); ?>
					<li>
						<strong><?php echo date_i18n( get_option( 'date_format' ), strtotime($date) ); ?> <?php echo date_i18n( get_option( 'time_format' ), strtotime($value->datetime) )?></strong>
						<p><?php echo apply_filters( 'trackship_tracking_event_description', $value->message ); if($value->tracking_location->city != NULL)echo ' - '; ?><span><?php echo apply_filters( 'trackship_tracking_event_location', $value->tracking_location->city ); ?></span></p>					
					</li>						
				<?php $a++; } ?>
			</ul>	
			
			<ul class="timeline old-details" style="display:none;">	
				<?php 
					$a = 1;	
					foreach($trackind_detail_by_status_rev as $key => $value){
					if( $a <= 2 ){  $a++; continue; }
					$date = date('Y-m-d', strtotime($value->datetime)); ?>
					<li>
						<strong><?php echo date_i18n( get_option( 'date_format' ), strtotime($date) ); ?> <?php echo date_i18n( get_option( 'time_format' ), strtotime($value->datetime) )?></strong>
						<p><?php echo apply_filters( 'trackship_tracking_event_description', $value->message ); if($value->tracking_location->city != NULL)echo ' - '; ?><span><?php echo apply_filters( 'trackship_tracking_event_location', $value->tracking_location->city ); ?></span></p>					
					</li>						
				<?php $a++; } ?>
			</ul>	
			
		</div>	
		
		<a class="view_old_details" href="javaScript:void(0);" style="display: inline;"><?php _e( 'view more', 	'woo-advanced-shipment-tracking' ); ?></a>
		<a class="hide_old_details" href="javaScript:void(0);" style="display: none;"><?php _e( 'view less', 		'woo-advanced-shipment-tracking' ); ?></a>	
	
	<?php } } else{
	
	if(!empty($tracking_details_by_date)){ ?>
	
	<div class="shipment_progress_heading_div">	               				
		<h4 class="h4-heading text-uppercase"><?php _e( 'Tracking Details', 'woo-advanced-shipment-tracking' ); ?></h4>					
	</div>	
	
	<?php if(!empty($trackind_destination_detail_by_status_rev)){ ?>
		
		<div class="tracking_destination_details_by_date">
			
			<h4 style=""><?php _e( 'Destination Details', 'woo-advanced-shipment-tracking' ); ?></h4>
			<ul class="timeline">	
			
			<?php foreach($trackind_destination_detail_by_status_rev as $key => $value){
				$date = date('Y-m-d', strtotime($value->datetime)); ?>
				<li>
					<strong><?php echo date_i18n( get_option( 'date_format' ), strtotime($date) ); ?> <?php echo date_i18n( get_option( 'time_format' ), strtotime($value->datetime) )?></strong>
					<p><?php echo apply_filters( 'trackship_tracking_event_description', $value->message ); if($value->tracking_location->city != NULL)echo ' - '; ?><span><?php echo apply_filters( 'trackship_tracking_event_location', $value->tracking_location->city ); ?></span></p>					
				</li>					
			<?php }  ?>								
			
			</ul>	
		</div>
		
		<?php } ?>
		
		<div class="tracking_details_by_date">
			
			<?php if(!empty($trackind_destination_detail_by_status_rev)){ ?>
				<h4 class="" style=""><?php _e( 'Origin Details', 'woo-advanced-shipment-tracking' ); ?></h4>
			<?php } ?> 
			
			<ul class="timeline">	
				<?php foreach($trackind_detail_by_status_rev as $key => $value){ 
					$date = date('Y-m-d', strtotime($value->datetime)); ?>
					
					<li>
						<strong><?php echo date_i18n( get_option( 'date_format' ), strtotime($date) ); ?> <?php echo date_i18n( get_option( 'time_format' ), strtotime($value->datetime) )?></strong>
						<p><?php echo apply_filters( 'trackship_tracking_event_description', $value->message ); if($value->tracking_location->city != NULL)echo ' - '; ?><span><?php echo apply_filters( 'trackship_tracking_event_location', $value->tracking_location->city ); ?></span></p>					
					</li>						
				<?php } ?>
			</ul>	
		</div>		
	<?php }
	} ?>
</div>