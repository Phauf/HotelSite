<!-- Webba Booking backend schedule page template -->
<?php
	// check if accessed directly
	if ( ! defined( 'ABSPATH' ) ) exit;
	date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
	require_once  dirname(__FILE__).'/../../common/class_wbk_db_utils.php';
	require_once  dirname(__FILE__).'/../../common/class_wbk_service_deprecated.php';
?>
<div id="dialog-appointment" height="500px" >
   	<div id="appointment_dialog_content">
   		<div id="appointment_dialog_left">
	   		<label for="wbk-appointment-time"><?php echo __( 'Time', 'wbk') ?> <span class="input-error" id="error-name"></span></label><br/>
            <input id="wbk-appointment-time" class="wbk-long-input" type="text" value="" /><br/>
            <input id="wbk-appointment-timestamp" type="hidden" value="" />
			<label for="wbk-appointment-name"><?php echo __( 'Name', 'wbk') ?> <span class="input-error" id="error-name"></span></label><br/>
            <input id="wbk-appointment-name" class="wbk-long-input" type="text" value="" /><br/>
            <label for="wbk-appointment-email"><?php echo __( 'Email', 'wbk') ?></label><br/>
            <input id="wbk-appointment-email" class="wbk-long-input" type="text" value="" /><br/>
            <label for="wbk-appointment-phone"><?php echo __( 'Phone', 'wbk') ?></label><br/>
            <input id="wbk-appointment-phone" class="wbk-long-input" type="text" value="" /><br/>
            <label id="wbk-appointment-quantity-label" for="wbk-appointment-quantity"><?php echo __( 'Items count', 'wbk') ?></label><br/>
            <input id="wbk-appointment-quantity" class="wbk-long-input" type="text" value="1" /><br/>
            <input id="wbk-appointment-quantity-max"  type="hidden" value="" />

            	<?php
            		$ids = get_option( 'wbk_custom_fields_columns', '' );
			        if( $ids != ''  ){
			            $ids = explode( ',', $ids );
			            $html = '';
			            foreach( $ids as $id ){
							$col_title = '';
							preg_match("/\[[^\]]*\]/", $id, $matches);
							if( is_array( $matches ) && count( $matches ) > 0 ){
								$col_title = rtrim( ltrim( $matches[0], '[' ), ']' );

							}
							$id = explode( '[', $id );
							$id = $id[0];
							if( $col_title == '' ){
								$col_title =  $id;
							}
			            	$html .= '<label for="' . $id  . '" class="slf_table_component_label" > ' . $col_title . '</label><br>';
							$html .= '<input type="text"  data-id="' . $id . '"  data-label="' . $id . '" class="wbk-long-input wbk_table_custom_field_part"  value=""  /><br>';
			            }
			            echo $html;
			        }

            	?>
        </div>
        <div id="appointment_dialog_right">
			<label style="display:none" for="wbk-appointment-extra"><?php echo __( 'Custom data', 'wbk') ?></label>
        	<textarea  style="display:none" class="wbk-full-width-control" id="wbk-appointment-extra" rows="7" class="wbk-long-input" readonly="readonly"></textarea>
			<label id="wbk-quantity-label" for="wbk-appointment-desc"><?php echo __( 'Comment', 'wbk') ?></label><br/>
            <textarea  class="wbk-full-width-control" id="wbk-appointment-desc" rows="5" class="wbk-long-input">
            </textarea>
        </div>
   	</div>
</div>
<div class="wrap">
	<h2 class="wbk_panel_title"><?php  echo __( 'Schedule', 'wbk' ); ?>
    <a style="text-decoration:none;" href="https://webba-booking.com/documentation/schedules/" rel="noopener"  target="_blank"><span class="dashicons dashicons-editor-help"></span></a>
	</h2>
	<?php
		$html = '<div class="wbk-schedule-row">';
	 		$arrIds = WBK_Model_Utils::get_service_ids();
	 		if ( count( $arrIds ) < 1 ) {
	 			$html .= __( 'Create at least one service. ', 'wbk' );
	 		} else {
				$html .= '<p class="wbk-section-title">' . __( 'Click to display the service schedule:', 'wbk' ) . '</p>';
		 		foreach ( $arrIds as $id ) {
					// check access
					if ( !current_user_can('manage_options') ) {
						if ( !WBK_Validator::checkAccessToService( $id ) ) {
 							continue;
						}
					}
		 			$service = new WBK_Service( $id );
					$service_label = $service->get_name();
					if( get_option( 'wbk_backend_show_category_name', 'disabled' ) == 'enabled' ){
						$category_names =  WBK_Db_Utils::getCategoryNamesByService( $id );
						if( $category_names != '' ){
							$service_label .= ' (' . $category_names . ')';
						}
					}

		 			$html .= '<a class="button ml5" id="load_schedule_'. $id .'" >' . $service_label . '</a>';
		 		}
		 	}
		$html .= '</div>';
		$html .= '<div class="wbk-schedule-row">';
	 		$arrIds = WBK_Db_Utils::getServices();
	 		if ( count( $arrIds ) < 1 ) {
	 			$html .= __( 'Create at least one service. ', 'wbk' );
	 		} else {
				$html .= '<p class="wbk-section-title">' . __( 'Schedule Tools:', 'wbk' ) . '</p>';
				$html .= '<a class="button ml5 wbk-shedule-tools-btn" id="auto_lock">' .  __( 'Date auto lock', 'wbk' )   . '</a>';
		 		$html .= '<a class="button ml5 wbk-shedule-tools-btn" id="auto_unlock" >' .  __( 'Date auto unlock', 'wbk' )   . '</a>';
				$html .= '<a class="button ml5 wbk-shedule-tools-btn" id="auto_lock_timeslot">' .  __( 'Time slot auto lock', 'wbk' )   . '</a>';
		 		$html .= '<a class="button ml5 wbk-shedule-tools-btn" id="auto_unlock_timeslot" >' .  __( 'Time slot auto unlock', 'wbk' )   . '</a>';
				$html .= '<a class="button ml5 wbk-shedule-tools-btn" id="create_multiple_bookings" >' .  __( 'Create multiple bookings', 'wbk' )   . '</a>';
		 	}
		$html .= '</div>';
		echo $html;
	?>
	<div id="days_container">
	</div>
	<div id="control_container">
	</div>
</div>
<?php
date_default_timezone_set( 'UTC' );
?>
