<?php

// check if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Webba Booking common ajax controller class
require_once 'class_wbk_business_hours.php';
// define main frontend class
class WBK_Ajax_Controller
{
    public function __construct()
    {
        // add action render service hours on frontend
        add_action( 'wp_ajax_wbk-render-days', array( $this, 'ajaxRenderDays' ) );
        add_action( 'wp_ajax_nopriv_wbk-render-days', array( $this, 'ajaxRenderDays' ) );
        // add action search time slots on fronted
        add_action( 'wp_ajax_wbk_search_time', array( $this, 'ajaxSearchTime' ) );
        add_action( 'wp_ajax_nopriv_wbk_search_time', array( $this, 'ajaxSearchTime' ) );
        // add action render time form
        add_action( 'wp_ajax_wbk_render_booking_form', array( $this, 'ajaxRenderBookingForm' ) );
        add_action( 'wp_ajax_nopriv_wbk_render_booking_form', array( $this, 'ajaxRenderBookingForm' ) );
        // add action for booking
        add_action( 'wp_ajax_wbk_book', array( $this, 'ajaxBook' ) );
        add_action( 'wp_ajax_nopriv_wbk_book', array( $this, 'ajaxBook' ) );
        // add action for payment prepare
        add_action( 'wp_ajax_wbk_prepare_payment', array( $this, 'ajaxPreparePayment' ) );
        add_action( 'wp_ajax_nopriv_wbk_prepare_payment', array( $this, 'ajaxPreparePayment' ) );
        // add action for appointment delete
        add_action( 'wp_ajax_wbk_cancel_appointment', array( $this, 'ajaxCancelAppointment' ) );
        add_action( 'wp_ajax_nopriv_wbk_cancel_appointment', array( $this, 'ajaxCancelAppointment' ) );
        add_action( 'wp_ajax_wbk_prepare_service_data', array( $this, 'ajaxPrepareServiceData' ) );
        add_action( 'wp_ajax_nopriv_wbk_prepare_service_data', array( $this, 'ajaxPrepareServiceData' ) );
    }
    
    // callback render service hours on frontend
    public function ajaxRenderDays()
    {
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $total_steps = $_POST['step'];
        $service_id = $_POST['service'];
        
        if ( !WBK_Validator::checkInteger( $service_id, 1, 999999 ) ) {
            echo  -1 ;
            die;
            return;
        }
        
        $service = new WBK_Service( $service_id );
        global  $wbk_wording ;
        $time_format = WBK_Date_Time_Utils::getTimeFormat();
        $select_hours_label = get_option( 'wbk_hours_label', '' );
        if ( $select_hours_label == '' ) {
            $select_hours_label = sanitize_text_field( $wbk_wording['hours_label'] );
        }
        $show_hours = get_option( 'wbk_show_suitable_hours', 'yes' );
        
        if ( $show_hours == 'yes' ) {
            $row_class = 'wbk-frontend-row';
        } else {
            $row_class = 'wbk_hidden';
        }
        
        $html = '<div class="wbk-col-12-12">';
        
        if ( $show_hours == 'yes' ) {
            $html .= '<label class="wbk-input-label">' . $select_hours_label . ' </label>';
            $html .= '<hr class="wbk-hours-separator">';
        }
        
        $hours_step = $service->get_step() * 60;
        $sp = new WBK_Schedule_Processor();
        $sp->load_unlocked_days();
        for ( $i = 1 ;  $i <= 7 ;  $i++ ) {
            
            if ( $sp->is_working_day( $i, $service_id ) || $sp->is_unlockced_has_dow( $i, $service_id ) ) {
                $html .= '<div class="' . $row_class . '" >';
                switch ( $i ) {
                    case 1:
                        $day_name_translated = __( 'Monday', 'wbk' );
                        $day_name = 'monday';
                        break;
                    case 2:
                        $day_name_translated = __( 'Tuesday', 'wbk' );
                        $day_name = 'tuesday';
                        break;
                    case 3:
                        $day_name_translated = __( 'Wednesday', 'wbk' );
                        $day_name = 'wednesday';
                        break;
                    case 4:
                        $day_name_translated = __( 'Thursday', 'wbk' );
                        $day_name = 'thursday';
                        break;
                    case 5:
                        $day_name_translated = __( 'Friday', 'wbk' );
                        $day_name = 'friday';
                        break;
                    case 6:
                        $day_name_translated = __( 'Saturday', 'wbk' );
                        $day_name = 'saturday';
                        break;
                    case 7:
                        $day_name_translated = __( 'Sunday', 'wbk' );
                        $day_name = 'sunday';
                        break;
                }
                $select = '<select id="wbk-time_' . $day_name . '" class="wbk-input wbk-time_after">';
                $intervals = $sp->get_business_hours_intervals_by_dow( $i, $service_id );
                $timeslots = [];
                foreach ( $intervals as $interval ) {
                    $start = $interval->start;
                    $end = $interval->end;
                    for ( $time = $start ;  $time <= $end - $service->get_duration() * 60 ;  $time += $hours_step ) {
                        $select .= '<option value="' . $time . '" >' . __( 'from', 'wbk' ) . ' ' . wp_date( $time_format, $time, new DateTimeZone( 'UTC' ) ) . '</option>';
                    }
                }
                $select .= '</select>';
                $html .= '<div class="wbk-col-3-12 wbk-table-cell">
							<input type="checkbox" value="' . $day_name . '" class="wbk-checkbox" id="wbk-day_' . $day_name . '" checked="checked"/>
							<label for="wbk-day_' . $day_name . '" class="wbk-checkbox-label">' . $day_name_translated . '</label>
						  </div>';
                $html .= '<div class="wbk-col-9-12">' . $select . '</div>';
                $html .= '</div>';
                $html .= '<div class="wbk-clear"></div>';
            }
        
        }
        $html .= '<input type="button" class="wbk-button wbk-searchtime-btn"  id="wbk-search_time_btn" value="' . __( 'Search time slots', 'wbk' ) . '"  />';
        echo  '<hr class="wbk-separator"/>' . $html ;
        date_default_timezone_set( 'UTC' );
        die;
        return;
    }
    
    // callback search time slots
    // timizone conversion enbaled
    public function ajaxSearchTime()
    {
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $service_id = $_POST['service'];
        $date = $_POST['date'];
        $days = $_POST['days'];
        $times = $_POST['times'];
        $offset = $_POST['offset'];
        $time_zone_client = $_POST['time_zone_client'];
        $search_allowed = apply_filters(
            'wbk_time_slots_search_allowed',
            true,
            $service_id,
            $date
        );
        
        if ( $search_allowed !== true ) {
            $result = array(
                'dest' => 'slot',
                'data' => $search_allowed,
            );
            echo  json_encode( $result ) ;
            date_default_timezone_set( 'UTC' );
            die;
            return;
        }
        
        if ( !is_numeric( $offset ) ) {
            $offset = 0;
        }
        // check date variable: string date or int timestamp
        
        if ( !is_numeric( $date ) ) {
            $day_to_render = strtotime( $date );
        } else {
            $day_to_render = $date;
        }
        
        if ( !is_numeric( $offset ) ) {
            $offset = 0;
        }
        
        if ( $time_zone_client != '' ) {
            $this_tz = new DateTimeZone( $time_zone_client );
            $date_this = ( new DateTime( '@' . $day_to_render ) )->setTimezone( new DateTimeZone( $time_zone_client ) );
            $offset = $this_tz->getOffset( $date_this );
            $offset = $offset * -1 / 60;
        }
        
        // validation
        
        if ( get_option( 'wbk_mode', 'extended' ) == 'extended' && !is_array( $service_id ) ) {
            
            if ( !is_array( $days ) || !is_array( $times ) ) {
                echo  -1 ;
                die;
                return;
            }
            
            foreach ( $days as $day ) {
                
                if ( !WBK_Validator::checkDayofweek( $day ) ) {
                    date_default_timezone_set( 'UTC' );
                    echo  -3 ;
                    die;
                    return;
                }
            
            }
            foreach ( $times as $time ) {
                
                if ( !WBK_Validator::checkInteger( $time, 0, 1758537351 ) ) {
                    date_default_timezone_set( 'UTC' );
                    echo  -4 ;
                    die;
                    return;
                }
            
            }
        }
        
        
        if ( !WBK_Validator::checkInteger( $day_to_render, 0, 1758537351 ) ) {
            date_default_timezone_set( 'UTC' );
            echo  -5 ;
            die;
            return;
        }
        
        // end validation
        
        if ( !is_array( $service_id ) ) {
            $service_schedule = new WBK_Service_Schedule();
            $service_schedule->setServiceId( $service_id );
            
            if ( !$service_schedule->load() ) {
                date_default_timezone_set( 'UTC' );
                echo  -6 ;
                die;
                return;
            }
            
            $limit_end = 0;
            if ( $service_schedule->getService()->getDateRange() != '' ) {
                $limit_end = $service_schedule->getService()->getDateRangeEnd();
            }
            $date_format = WBK_Date_Time_Utils::getDateFormat();
            $i = 0;
            
            if ( get_option( 'wbk_mode', 'extended' ) == 'extended' ) {
                $days_count_opt = get_option( 'wbk_days_in_extended_mode', 'default' );
                $days_count_opt = apply_filters( 'wbk_days_extended_mode', $days_count_opt, $service_id );
                
                if ( $days_count_opt == 'default' ) {
                    $output_count = 2;
                } else {
                    
                    if ( $days_count_opt == 'lowlimit' || $days_count_opt == 'uplimit' ) {
                        $low_limit = $service_schedule->getService()->getMultipleLowLimit();
                        $up_limit = $service_schedule->getService()->getMultipleLimit();
                        $output_count = 2;
                        if ( $days_count_opt == 'lowlimit' && $low_limit != '' ) {
                            $output_count = $low_limit - 1;
                        }
                        if ( $days_count_opt == 'uplimit' && $up_limit != '' ) {
                            $output_count = $up_limit - 1;
                        }
                    } else {
                        $output_count = $days_count_opt - 1;
                    }
                
                }
            
            } else {
                $output_count = 0;
            }
            
            $html = '';
            $limit_year = 0;
            while ( $i <= $output_count ) {
                $limit_year++;
                
                if ( $limit_year > 360 ) {
                    $i = $output_count + 1;
                    continue;
                }
                
                if ( $limit_end != 0 && get_option( 'wbk_mode', 'extended' ) == 'extended' ) {
                    
                    if ( $day_to_render > $limit_end ) {
                        $i = $output_count + 1;
                        continue;
                    }
                
                }
                $day_status = $service_schedule->getDayStatus( $day_to_render );
                
                if ( $day_status == 1 ) {
                    
                    if ( get_option( 'wbk_mode', 'extended' ) == 'extended' ) {
                        $day_name = strtolower( date( 'l', $day_to_render ) );
                        $key = array_search( $day_name, $days );
                        
                        if ( $key === FALSE ) {
                            $day_to_render = strtotime( 'tomorrow', $day_to_render );
                            continue;
                        } else {
                            $time_after = $times[$key] + $day_to_render;
                            $day = $day_to_render;
                            $tz = new DateTimeZone( get_option( 'wbk_timezone', 'UTC' ) );
                            $transition = $tz->getTransitions( $day, $day );
                            $offset1 = $transition[0]['offset'];
                            $next_day = strtotime( '+1 day', $day );
                            $transition = $tz->getTransitions( $next_day, $next_day );
                            $offset2 = $transition[0]['offset'];
                            $difference = $offset1 - $offset2;
                            if ( $difference != 0 ) {
                                $time_after += $difference;
                            }
                        }
                    
                    } else {
                        $time_after = $day_to_render;
                    }
                    
                    $service_schedule->buildSchedule(
                        $day_to_render,
                        false,
                        false,
                        true,
                        true
                    );
                    $date_regular = wp_date( $date_format, $day_to_render, new DateTimeZone( date_default_timezone_get() ) );
                    $timezone = new DateTimeZone( get_option( 'wbk_timezone', 'UTC' ) );
                    $current_offset = $offset * -60 - $timezone->getOffset( new DateTime() );
                    $date_local = wp_date( $date_format, $day_to_render + $current_offset, new DateTimeZone( date_default_timezone_get() ) );
                    $day_title = get_option( 'wbk_day_label', '#date' );
                    $day_title = str_replace( '#date', $date_regular, $day_title );
                    $day_title = str_replace( '#local_date', $date_local, $day_title );
                    $day_slots = $service_schedule->renderDayFrontend( $time_after, $offset );
                    $skip_value = apply_filters( 'wbk_skip_timeslots', get_option( 'wbk_skip_timeslot_select', 'disabled' ), $service_id );
                    // CHECK FOR 1 ONLY TIME SLOTS AND SKIP VALUE
                    
                    if ( substr_count( $day_slots, 'wbk-timeslot-btn' ) == 1 && $skip_value == 'enabled' ) {
                        $first_time = $service_schedule->getFirstAvailableTime();
                        $form_html = $this->render_booking_form( $service_id, $first_time );
                        $form_html = apply_filters(
                            'wbk_form_html',
                            $form_html,
                            $service_id,
                            $first_time
                        );
                        $result = array(
                            'dest' => 'form',
                            'data' => $form_html,
                            'time' => $first_time,
                        );
                        date_default_timezone_set( 'UTC' );
                        echo  json_encode( $result ) ;
                        die;
                        return;
                    }
                    
                    // END CHECK FOR 1 ONLY TIME SLOTS AND SKIP VALUE
                    // CHECK FOR 1 ONLY TIME SLOTS
                    $only_one_slot = false;
                    $center_class = '';
                    
                    if ( substr_count( $day_slots, 'wbk-timeslot-btn' ) == 1 && get_option( 'wbk_mode', 'basic' ) == 'extended' ) {
                        $day_slots = str_replace( 'wbk-col-4-6-12', '', $day_slots );
                        $only_one_slot = true;
                        $center_class = ' wbk-text-center ';
                    }
                    
                    // END CHECK FOR 1 ONLY TIME SLOTS
                    
                    if ( $day_slots != '' ) {
                        if ( $only_one_slot && get_option( 'wbk_mode', 'basic' ) == 'extended' ) {
                            $html .= '<div class="wbk-col-4-6-12">';
                        }
                        $html .= '<div class="wbk-col-12-12">
									<div class="wbk-day-title  ' . $center_class . ' .">
										' . $day_title . '
									</div>
									<hr class="wbk-day-separator">
		  						  </div>';
                        $html .= '<div class="wbk-col-12-12 wbk-text-center" >' . $day_slots . '</div>';
                        if ( $only_one_slot && get_option( 'wbk_mode', 'basic' ) == 'extended' ) {
                            $html .= '</div>';
                        }
                    }
                    
                    $i++;
                }
                
                
                if ( get_option( 'wbk_mode', 'extended' ) == 'extended' ) {
                    $day_to_render = strtotime( 'tomorrow', $day_to_render );
                } else {
                    $i++;
                }
            
            }
        } else {
            // multi service boooking
            $service_ids = $service_id;
            $date_format = WBK_Date_Time_Utils::getDateFormat();
            
            if ( !is_numeric( $date ) ) {
                $day_to_render = strtotime( $date );
            } else {
                $day_to_render = $date;
            }
            
            if ( !is_numeric( $offset ) ) {
                $offset = 0;
            }
            $html = '';
            $i = 0;
            $multi_serv_date_limit = get_option( 'wbk_multi_serv_date_limit', '360' );
            $up_limit = strtotime( 'today midnight' ) + 86400 * $multi_serv_date_limit;
            $output_counts = array();
            foreach ( $service_ids as $service_id ) {
                $service_schedule = new WBK_Service_Schedule();
                $service_schedule->setServiceId( $service_id );
                if ( !$service_schedule->load() ) {
                    continue;
                }
                
                if ( get_option( 'wbk_mode', 'extended' ) == 'extended' ) {
                    $days_count_opt = get_option( 'wbk_days_in_extended_mode', 'default' );
                    $days_count_opt = apply_filters( 'wbk_days_extended_mode', $days_count_opt, $service_id );
                    
                    if ( $days_count_opt == 'default' ) {
                        $output_count = 2;
                    } else {
                        
                        if ( $days_count_opt == 'lowlimit' || $days_count_opt == 'uplimit' ) {
                            $low_limit_x = $service_schedule->getService()->getMultipleLowLimit();
                            $up_limit_x = $service_schedule->getService()->getMultipleLimit();
                            $output_count = 2;
                            if ( $days_count_opt == 'lowlimit' && $low_limit_x != '' ) {
                                $output_count = $low_limit_x - 1;
                            }
                            if ( $days_count_opt == 'uplimit' && $up_limit_x != '' ) {
                                $output_count = $up_limit_x - 1;
                            }
                        } else {
                            $output_count = $days_count_opt - 1;
                        }
                    
                    }
                
                } else {
                    $output_count = 0;
                }
                
                $output_counts[] = $output_count;
            }
            $output_count = min( $output_counts );
            $limit_end = 0;
            while ( $i <= $output_count ) {
                
                if ( $day_to_render > $up_limit ) {
                    $limit_end = $day_to_render + 1;
                    $i = $output_count + 1;
                }
                
                $day_slots_all_services = '';
                foreach ( $service_ids as $service_id ) {
                    $service_schedule = new WBK_Service_Schedule();
                    $service_schedule->setServiceId( $service_id );
                    if ( !$service_schedule->load() ) {
                        continue;
                    }
                    
                    if ( $service_schedule->getService()->getDateRange() != '' ) {
                        $limit_end_current_service = $service_schedule->getService()->getDateRangeEnd();
                        $limit_start_current_service = $service_schedule->getService()->getDateRangeStart();
                    } else {
                        $limit_end_current_service = 0;
                        $limit_start_current_service = 0;
                    }
                    
                    if ( $day_to_render > $limit_end_current_service && $limit_end_current_service != 0 || $day_to_render < $limit_start_current_service && $limit_start_current_service != 0 ) {
                        continue;
                    }
                    $day_status = $service_schedule->getDayStatus( $day_to_render );
                    
                    if ( $day_status == 1 ) {
                        $time_after = $day_to_render;
                        $service_schedule->buildSchedule(
                            $day_to_render,
                            false,
                            false,
                            true,
                            true
                        );
                        $day_slots = $service_schedule->renderDayFrontend( $time_after, $offset );
                    } else {
                        $day_slots = '';
                    }
                    
                    if ( $day_slots != '' ) {
                        $day_slots_all_services .= '<label class="wbk-multiple-service-title">' . $service_schedule->getService()->getName() . '</label>' . $day_slots;
                    }
                }
                $date_regular = wp_date( $date_format, $day_to_render, new DateTimeZone( date_default_timezone_get() ) );
                $timezone = new DateTimeZone( get_option( 'wbk_timezone', 'UTC' ) );
                $current_offset = $offset * -60 - $timezone->getOffset( new DateTime() );
                $date_local = wp_date( $date_format, $day_to_render + $current_offset, new DateTimeZone( date_default_timezone_get() ) );
                $day_title = get_option( 'wbk_day_label', '#date' );
                $day_title = str_replace( '#date', $date_regular, $day_title );
                $day_title = str_replace( '#local_date', $date_local, $day_title );
                
                if ( get_option( 'wbk_mode', 'extended' ) == 'extended' ) {
                    $day_to_render = strtotime( 'tomorrow', $day_to_render );
                } else {
                    $i++;
                }
                
                
                if ( $day_slots_all_services != '' ) {
                    $html .= '<div class="wbk-col-12-12">
								<div class="wbk-day-title">
									' . $day_title . '
								</div>
								<hr class="wbk-day-separator">
	  						  </div>';
                    $html .= '<div class="wbk-col-12-12 wbk-text-center" >' . $day_slots_all_services . '</div>';
                    $i++;
                }
            
            }
        }
        
        
        if ( get_option( 'wbk_mode', 'extended' ) == 'extended' ) {
            
            if ( $html != '' ) {
                
                if ( $limit_end != 0 ) {
                    if ( $day_to_render <= $limit_end ) {
                        $html .= '<div class="wbk-frontend-row" id="wbk-show_more_container">
									<input type="button" class="wbk-button"  id="wbk-show_more_btn" value="' . __( 'Show more', 'wbk' ) . '"  />
									<input type="hidden" id="wbk-show-more-start" value="' . $day_to_render . '">
								  </div>';
                    }
                    $html .= '<div class="wbk-more-container"></div>';
                } else {
                    $html .= '<div class="wbk-frontend-row" id="wbk-show_more_container">
								<input type="button" class="wbk-button"  id="wbk-show_more_btn" value="' . __( 'Show more', 'wbk' ) . '"  />
								<input type="hidden" id="wbk-show-more-start" value="' . $day_to_render . '">
							  </div>';
                    $html .= '<div class="wbk-more-container"></div>';
                }
            
            } else {
                $html = get_option( 'wbk_book_not_found_message', 'Unfortunately we were unable to meet your search criteria. Please change the criteria and try again.' );
            }
        
        } else {
            if ( $html == '' ) {
                $html = get_option( 'wbk_book_not_found_message', 'Unfortunately we were unable to meet your search criteria. Please change the criteria and try again.' );
            }
        }
        
        
        if ( get_option( 'wbk_show_cancel_button', 'disabled' ) == 'enabled' ) {
            global  $wbk_wording ;
            $cancel_label = get_option( 'wbk_cancel_button_text', '' );
            if ( $cancel_label == '' ) {
                $cancel_label = sanitize_text_field( $wbk_wording['cancel_label_form'] );
            }
            $html .= '<input class="wbk-button wbk-width-100 wbk-cancel-button"  value="' . $cancel_label . '" type="button">';
        }
        
        $result = array(
            'dest' => 'slot',
            'data' => $html,
        );
        echo  json_encode( $result ) ;
        date_default_timezone_set( 'UTC' );
        die;
        return;
    }
    
    // time zone converted
    public function ajaxRenderBookingForm()
    {
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $time = $_POST['time'];
        
        if ( isset( $_POST['service'] ) ) {
            $service_id = $_POST['service'];
            $multi_service = false;
        } else {
            $multi_service = true;
            $services = $_POST['services'];
            foreach ( $services as $service_this ) {
                
                if ( !WBK_Validator::checkInteger( $service_this, 1, 2758537351 ) ) {
                    echo  -1 ;
                    date_default_timezone_set( 'UTC' );
                    die;
                    return;
                }
            
            }
        }
        
        $offset = $_POST['time_offset'];
        if ( !is_numeric( $offset ) ) {
            $offset = 0;
        }
        $time_zone_client = $_POST['time_zone_client'];
        
        if ( is_array( $time ) && count( $time ) > 0 ) {
            $time_for_offset = $time[0];
        } else {
            $time_for_offset = $time;
        }
        
        
        if ( $time_zone_client != '' ) {
            $this_tz = new DateTimeZone( $time_zone_client );
            $date_this = ( new DateTime( '@' . $time_for_offset ) )->setTimezone( new DateTimeZone( $time_zone_client ) );
            $offset = $this_tz->getOffset( $date_this );
            $offset = $offset * -1 / 60;
        }
        
        
        if ( is_array( $time ) ) {
            foreach ( $time as $time_this ) {
                
                if ( !WBK_Validator::checkInteger( $time_this, 0, 2758537351 ) ) {
                    echo  -1 ;
                    date_default_timezone_set( 'UTC' );
                    die;
                    return;
                }
            
            }
        } else {
            
            if ( !WBK_Validator::checkInteger( $time, 0, 2758537351 ) ) {
                echo  -1 ;
                date_default_timezone_set( 'UTC' );
                die;
                return;
            }
        
        }
        
        
        if ( $multi_service ) {
            $result = $this->render_booking_form_multiple( $services, $time );
        } else {
            $result = $this->render_booking_form( $service_id, $time, $offset );
        }
        
        
        if ( $result === FALSE ) {
            echo  -1 ;
            date_default_timezone_set( 'UTC' );
            die;
            return;
        }
        
        echo  $result ;
        date_default_timezone_set( 'UTC' );
        die;
        return;
    }
    
    public function ajaxBook()
    {
        global  $wpdb ;
        global  $wbk_wording ;
        $arr_uploaded_urls = array();
        if ( get_option( 'wbk_allow_attachemnt', 'no' ) == 'yes' ) {
            foreach ( $_FILES as $file ) {
                $uploaded_file = wp_handle_upload( $file, array(
                    'test_form' => false,
                ) );
                if ( $uploaded_file && !isset( $uploaded_file['error'] ) ) {
                    $arr_uploaded_urls[] = $uploaded_file['file'];
                }
            }
        }
        
        if ( count( $arr_uploaded_urls ) > 0 ) {
            $attachments = json_encode( $arr_uploaded_urls );
        } else {
            $attachments = '';
        }
        
        $wbk_external_validation = true;
        $wbk_external_validation = apply_filters( 'wbk_booking_form_validation', $wbk_external_validation, $_POST );
        
        if ( $wbk_external_validation == false ) {
            echo  -1 ;
            date_default_timezone_set( 'UTC' );
            die;
            return;
        }
        
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $name = apply_filters( 'wbk_field_before_book', sanitize_text_field( $_POST['custname'] ), 'name' );
        $email = apply_filters( 'wbk_field_before_book', sanitize_text_field( $_POST['email'] ), 'email' );
        $phone = sanitize_text_field( $_POST['phone'] );
        $times = explode( ',', $_POST['time'] );
        if ( isset( $_POST['services'] ) ) {
            $services = explode( ',', $_POST['services'] );
        }
        $desc = sanitize_text_field( $_POST['desc'] );
        $extra = stripcslashes( $_POST['extra'] );
        $quantity = sanitize_text_field( $_POST['quantity'] );
        
        if ( isset( $_POST['current_category'] ) ) {
            $current_category = sanitize_text_field( $_POST['current_category'] );
        } else {
            $current_category = 0;
        }
        
        $time_offset = sanitize_text_field( $_POST['time_offset'] );
        
        if ( isset( $_POST['time_zone_client'] ) ) {
            $time_zone_client = $_POST['time_zone_client'];
        } else {
            $time_zone_client = '';
        }
        
        $per_serv_quantity_result = array();
        if ( !is_numeric( $time_offset ) ) {
            $time_offset = 0;
        }
        
        if ( isset( $_POST['secondary_data'] ) ) {
            $scondary_data = stripslashes( $_POST['secondary_data'] );
            $secondary_data = json_decode( $scondary_data );
        }
        
        
        if ( isset( $_POST['service'] ) && $_POST['service'] != 'undefined' ) {
            $service_id = $_POST['service'];
            $multi_service = false;
        } else {
            $multi_service = true;
            
            if ( $_POST['per_serv_quantity'] != '' ) {
                $per_serv_quantity = explode( ',', $_POST['per_serv_quantity'] );
                foreach ( $per_serv_quantity as $cur_quantity ) {
                    $cur_quantity = explode( ';', $cur_quantity );
                    
                    if ( count( $cur_quantity ) != 2 ) {
                        echo  -1 ;
                        date_default_timezone_set( 'UTC' );
                        die;
                        return;
                    }
                    
                    
                    if ( !WBK_Validator::checkInteger( $cur_quantity[0], 1, 2758537351 ) ) {
                        echo  -9 ;
                        date_default_timezone_set( 'UTC' );
                        die;
                        return;
                    }
                    
                    
                    if ( !WBK_Validator::checkInteger( $cur_quantity[1], 1, 1000000 ) ) {
                        echo  -9 ;
                        date_default_timezone_set( 'UTC' );
                        die;
                        return;
                    }
                    
                    $per_serv_quantity_result['service-' . $cur_quantity[0]] = $cur_quantity[1];
                }
            }
            
            foreach ( $services as $service_this ) {
                
                if ( !WBK_Validator::checkInteger( $service_this, 1, 2758537351 ) ) {
                    echo  -1 ;
                    date_default_timezone_set( 'UTC' );
                    die;
                    return;
                }
            
            }
        }
        
        $appointment_ids = array();
        $i = -1;
        $skipped_count = 0;
        $serices_used = array();
        $notification_appointment_ids = array();
        $not_booked_due_limit = false;
        foreach ( $times as $time ) {
            $i++;
            
            if ( !is_numeric( $time ) ) {
                echo  -9 ;
                date_default_timezone_set( 'UTC' );
                die;
                return;
            }
            
            
            if ( !WBK_Validator::checkInteger( $quantity, 1, 1000000 ) ) {
                echo  -9 ;
                date_default_timezone_set( 'UTC' );
                die;
                return;
            }
            
            $service = new WBK_Service_deprecated();
            if ( $multi_service ) {
                $service_id = $services[$i];
            }
            
            if ( !$service->setId( $service_id ) ) {
                echo  -6 ;
                date_default_timezone_set( 'UTC' );
                die;
                return;
            }
            
            
            if ( !$service->load() ) {
                echo  -6 ;
                date_default_timezone_set( 'UTC' );
                die;
                return;
            }
            
            $ongoing_valid = false;
            
            if ( get_option( 'wbk_allow_ongoing_time_slot', 'disallow' ) == 'disallow' ) {
                if ( $time > time() ) {
                    $ongoing_valid = true;
                }
            } else {
                $end_time_current = $time + $service->getDuration() * 60;
                if ( $time > time() || $time < time() && $end_time_current > time() ) {
                    $ongoing_valid = true;
                }
            }
            
            
            if ( !$ongoing_valid ) {
                echo  -9 ;
                date_default_timezone_set( 'UTC' );
                die;
                return;
            }
            
            
            if ( !WBK_Validator::checkInteger( $quantity, 1, 1000000 ) ) {
                echo  -9 ;
                date_default_timezone_set( 'UTC' );
                die;
                return;
            }
            
            $day = strtotime( date( 'Y-m-d', $time ) . ' 00:00:00' );
            
            if ( $time_zone_client != '' ) {
                $this_tz = new DateTimeZone( $time_zone_client );
                $date_this = ( new DateTime( '@' . $day ) )->setTimezone( new DateTimeZone( $time_zone_client ) );
                $time_offset = $this_tz->getOffset( $date_this );
                $time_offset = $time_offset * -1 / 60;
            }
            
            
            if ( isset( $per_serv_quantity_result['service-' . $service_id] ) ) {
                $quantity_this = $per_serv_quantity_result['service-' . $service_id];
            } else {
                $quantity_this = $quantity;
            }
            
            // ** double check for closed days
            $service_schedule = new WBK_Service_Schedule();
            $service_schedule->setServiceId( $service->getId() );
            $service_schedule->load();
            $service_schedule->buildSchedule( $day, false, true );
            $day_status = $service_schedule->getDayStatus( $day );
            
            if ( $day_status != 1 ) {
                $skipped_count++;
                continue;
            }
            
            $time_slot_valid = false;
            $timeslots = $service_schedule->getTimeSlots();
            foreach ( $timeslots as $timeslot ) {
                if ( $timeslot->getStart() == $time ) {
                    if ( is_array( $timeslot->getStatus() ) || $timeslot->getStatus() == 0 ) {
                        $time_slot_valid = true;
                    }
                }
            }
            
            if ( !$time_slot_valid ) {
                $skipped_count++;
                continue;
            }
            
            
            if ( $service->getQuantity( $time ) == 1 ) {
                $service_schedule = new WBK_Service_Schedule();
                $service_schedule->setServiceId( $service->getId() );
                $avail_count = $service_schedule->getAvailableCount( $time );
                
                if ( $avail_count < 1 ) {
                    $skipped_count++;
                    continue;
                }
            
            } else {
                $service_schedule = new WBK_Service_Schedule();
                $service_schedule->setServiceId( $service->getId() );
                $avail_count = $service_schedule->getAvailableCount( $time );
                
                if ( $quantity_this > $avail_count ) {
                    $skipped_count++;
                    continue;
                }
            
            }
            
            // strong validation for multiple service mode
            
            if ( $multi_service ) {
                $service_schedule = new WBK_Service_Schedule();
                $service_schedule->setServiceId( $service->getId() );
                $avail_count = $service_schedule->getAvailableCount( $time );
                
                if ( $quantity_this > $avail_count ) {
                    $skipped_count++;
                    continue;
                }
            
            }
            
            $duration = $service->getDuration();
            $appointment = new WBK_Appointment_deprecated();
            
            if ( !$appointment->setName( $name ) ) {
                echo  -1 ;
                date_default_timezone_set( 'UTC' );
                die;
                return;
            }
            
            
            if ( !$appointment->setEmail( $email ) ) {
                echo  -2 ;
                date_default_timezone_set( 'UTC' );
                die;
                return;
            }
            
            
            if ( !$appointment->setPhone( $phone ) ) {
                echo  -3 ;
                date_default_timezone_set( 'UTC' );
                die;
                return;
            }
            
            
            if ( !$appointment->setTime( $time ) ) {
                echo  -4 ;
                date_default_timezone_set( 'UTC' );
                die;
                return;
            }
            
            
            if ( !$appointment->setDay( $day ) ) {
                echo  -5 ;
                date_default_timezone_set( 'UTC' );
                die;
                return;
            }
            
            
            if ( !$appointment->setService( $service_id ) ) {
                echo  -6 ;
                date_default_timezone_set( 'UTC' );
                die;
                return;
            }
            
            
            if ( !$appointment->setDuration( $duration ) ) {
                echo  -7 ;
                date_default_timezone_set( 'UTC' );
                die;
                return;
            }
            
            
            if ( !$appointment->setDescription( $desc ) ) {
                echo  -9 ;
                date_default_timezone_set( 'UTC' );
                die;
                return;
            }
            
            $extra = apply_filters( 'wbk_external_custom_field', $extra, '' );
            
            if ( !$appointment->setExtra( $extra ) ) {
                echo  -9 ;
                date_default_timezone_set( 'UTC' );
                die;
                return;
            }
            
            
            if ( !$appointment->setQuantity( $quantity_this ) ) {
                echo  -9 ;
                date_default_timezone_set( 'UTC' );
                die;
                return;
            }
            
            
            if ( !$appointment->setTimeOffset( $time_offset ) ) {
                echo  -9 ;
                date_default_timezone_set( 'UTC' );
                die;
                return;
            }
            
            
            if ( !$appointment->setAttachment( $attachments ) ) {
                echo  -9 ;
                date_default_timezone_set( 'UTC' );
                die;
                return;
            }
            
            
            if ( !is_numeric( $time ) ) {
                echo  -9 ;
                date_default_timezone_set( 'UTC' );
                die;
                return;
            }
            
            // LIMIT validation
            // check time slot limits
            
            if ( get_option( 'wbk_appointments_only_one_per_slot', 'disabled' ) == 'enabled' ) {
                $count = WBK_Db_Utils::getCountOfAppointmentsByEmailTimeService( $email, $time, $service_id );
                
                if ( $count > 0 ) {
                    $not_booked_due_limit = true;
                    continue;
                }
            
            }
            
            
            if ( get_option( 'wbk_appointments_only_one_per_service', 'disabled' ) == 'enabled' ) {
                $count = WBK_Db_Utils::getCountOfAppointmentsByEmailService( $email, $service_id );
                
                if ( $count > 0 ) {
                    $not_booked_due_limit = true;
                    continue;
                }
            
            }
            
            $limit_email_service_date = get_option( 'wbk_appointments_limit_email_service_date', 'disabled' );
            if ( is_numeric( $limit_email_service_date ) && $limit_email_service_date > 0 ) {
                
                if ( count( WBK_Model_Utils::get_booking_ids_by_day_service_email( $day, $service_id, $email ) ) >= $limit_email_service_date ) {
                    $not_booked_due_limit = true;
                    continue;
                }
            
            }
            
            if ( get_option( 'wbk_appointments_only_one_per_day', 'disabled' ) == 'enabled' ) {
                $count = WBK_Db_Utils::getCountOfAppointmentsByEmailServiceDay( $email, $service_id, $day );
                
                if ( $count > 0 ) {
                    $not_booked_due_limit = true;
                    continue;
                }
            
            }
            
            // END LIMIT VALIDATION
            $appointment_id = $appointment->add();
            
            if ( !$appointment_id ) {
                echo  -8 ;
                date_default_timezone_set( 'UTC' );
                die;
                return;
            }
            
            do_action( 'wbk_table_after_add', [ $appointment_id, get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments' ] );
            WBK_Db_Utils::setCreatedOnToAppointment( $appointment_id );
            WBK_Db_Utils::setActualDurationToAppointment( $appointment_id, 0 );
            WBK_Db_Utils::setServiceCategoryToAppointment( $appointment_id, $current_category );
            WBK_Db_Utils::setIPToAppointment( $appointment_id );
            WBK_Model_Utils::set_end_for_booking( $appointment_id );
            $serices_used[] = $service_id;
            
            if ( get_option( 'wbk_appointments_default_status', 'approved' ) == 'approved' ) {
                WBK_Db_Utils::updateAppointmentStatus( $appointment_id, 'approved' );
                Plugion()->set_value(
                    get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
                    'appointment_prev_status',
                    $appointment_id,
                    'approved'
                );
            } else {
                WBK_Db_Utils::updateAppointmentStatus( $appointment_id, 'pending' );
                Plugion()->set_value(
                    get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
                    'appointment_prev_status',
                    $appointment_id,
                    'pending'
                );
            }
            
            $auto_lock = get_option( 'wbk_appointments_auto_lock', 'disabled' );
            if ( $auto_lock == 'enabled' ) {
                WBK_Db_Utils::lockTimeSlotsOfOthersServices( $service_id, $appointment_id );
            }
            $appointment_ids[] = $appointment_id;
            $notification_appointment_ids[] = $appointment_id;
            $expiration_mode = get_option( 'wbk_appointments_delete_not_paid_mode', 'disabled' );
            if ( $expiration_mode == 'on_booking' ) {
                WBK_Db_Utils::setAppointmentsExpiration( $appointment_id );
            }
            WBK_Db_Utils::setLangToAppointmentId( $appointment_id );
            $wbk_action_data = array(
                'appointment_id' => $appointment_id,
                'customer'       => $name,
                'email'          => $email,
                'phone'          => $phone,
                'time'           => $time,
                'serice id'      => $service_id,
                'duration'       => $duration,
                'comment'        => $desc,
                'quantity'       => $quantity,
            );
            do_action( 'wbk_add_appointment', $wbk_action_data );
            
            if ( get_option( 'wbk_woo_prefil_fields', '' ) == 'true' ) {
                if ( !session_id() ) {
                    session_start();
                }
                $booking = new WBK_Booking( $appointment_id );
                $last_name = $booking->get_custom_field_value( 'last_name' );
                if ( is_null( $last_name ) ) {
                    $last_name = '';
                }
                $_SESSION['wbk_name'] = $name;
                $_SESSION['wbk_email'] = $email;
                $_SESSION['wbk_phone'] = $phone;
                $_SESSION['wbk_last_name'] = $last_name;
            }
        
        }
        foreach ( $appointment_ids as $booking_id ) {
            $amount = WBK_Price_Processor::calculate_single_booking_price( $booking_id, $appointment_ids );
            WBK_Model_Utils::set_amount_for_booking( $booking_id, $amount['price'], json_encode( $amount['price_details'] ) );
            // *** GG ADD
            if ( get_option( 'wbk_gg_when_add', 'onbooking' ) == 'onbooking' ) {
            }
        }
        do_action( 'wbebba_after_bookings_added', $appointment_ids );
        
        if ( count( $appointment_ids ) == 0 && $not_booked_due_limit == true ) {
            echo  '-14' ;
            wp_die();
            return;
        }
        
        
        if ( count( $appointment_ids ) == 0 ) {
            echo  '-13' ;
            wp_die();
            return;
        }
        
        $sort_array = array();
        foreach ( $notification_appointment_ids as $notification_app_id ) {
            $app = Wbk_Db_Utils::initAppointmentById( $notification_app_id );
            $sort_array[] = $app->getTime();
        }
        array_multisort(
            $sort_array,
            SORT_ASC,
            SORT_NUMERIC,
            $notification_appointment_ids
        );
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        foreach ( $notification_appointment_ids as $notification_app_id ) {
            $notification_service_id = WBK_Db_Utils::getServiceIdByAppointmentId( $notification_app_id );
            $noifications = new WBK_Email_Notifications( $notification_service_id, $notification_app_id, $current_category );
            $noifications->send( 'book' );
            // sending invoice
            if ( get_option( 'wbk_email_customer_send_invoice', 'disabled' ) == 'onbooking' ) {
                if ( get_option( 'wbk_multi_booking', 'disabled' ) != 'disabled' && get_option( 'wbk_email_customer_book_multiple_mode', 'foreach' ) == 'foreach' || get_option( 'wbk_multi_booking', 'disabled' ) == 'disabled' ) {
                    $noifications->sendSingleInvoice();
                }
            }
            // secondary names notifications
            if ( get_option( 'wbk_multi_booking', 'disabled' ) != 'disabled' && get_option( 'wbk_email_customer_book_multiple_mode', 'foreach' ) == 'foreach' || get_option( 'wbk_multi_booking', 'disabled' ) == 'disabled' ) {
                if ( isset( $secondary_data ) ) {
                    if ( is_array( $secondary_data ) ) {
                        $noifications->sendToSecondary( $secondary_data );
                    }
                }
            }
        }
        $sort_array = array();
        foreach ( $appointment_ids as $appointment_id ) {
            $app = Wbk_Db_Utils::initAppointmentById( $appointment_id );
            $sort_array[] = $app->getTime();
        }
        array_multisort(
            $sort_array,
            SORT_ASC,
            SORT_NUMERIC,
            $appointment_ids
        );
        if ( get_option( 'wbk_multi_booking', 'disabled' ) != 'disabled' && get_option( 'wbk_email_customer_book_multiple_mode', 'foreach' ) == 'one' ) {
            if ( isset( $secondary_data ) ) {
                if ( is_array( $secondary_data ) ) {
                    $noifications->sendMultipleToSecondary( $appointment_ids, $secondary_data );
                }
            }
        }
        
        if ( get_option( 'wbk_multi_booking', 'disabled' ) != 'disabled' && get_option( 'wbk_email_customer_book_multiple_mode', 'one' ) == 'one' ) {
            $noifications = new WBK_Email_Notifications( $service_id, $appointment_id, $current_category );
            $noifications->sendMultipleCustomerNotification( $appointment_ids );
            if ( get_option( 'wbk_email_customer_send_invoice', 'disabled' ) == 'onbooking' ) {
                $noifications->sendMultipleCustomerInvoice( $appointment_ids );
            }
        }
        
        
        if ( get_option( 'wbk_multi_booking', 'disabled' ) != 'disabled' && get_option( 'wbk_email_admin_book_multiple_mode', 'one' ) == 'one' ) {
            $noifications = new WBK_Email_Notifications( $service_id, $appointment_id, $current_category );
            $noifications->sendMultipleAdminNotification( $appointment_ids );
        }
        
        $thanks_message = get_option( 'wbk_book_thanks_message', '' );
        if ( $thanks_message == '' ) {
            $thanks_message = $wbk_wording['thanks_for_booking'];
        }
        $thanks_message = WBK_Db_Utils::prepareThankYouMessage(
            $appointment_ids,
            $service_id,
            $thanks_message,
            $skipped_count
        );
        $payment_methods = json_decode( $service->getPayementMethods() );
        
        if ( !is_null( $payment_methods ) ) {
            $payment_methods_html = '';
            
            if ( $multi_service ) {
                $payment_methods_html .= WBK_PayPal::renderPaymentMethods( $serices_used, $appointment_ids, ' wbk_payment_button_afterform' );
                $payment_methods_html .= WBK_Stripe::renderPaymentMethods( $serices_used, $appointment_ids, ' wbk_payment_button_afterform' );
                $payment_methods_html .= WBK_WooCommerce::renderPaymentMethods( $serices_used, $appointment_ids );
            } else {
                $payment_methods_html .= WBK_PayPal::renderPaymentMethods( $service_id, $appointment_ids, ' wbk_payment_button_afterform' );
                $payment_methods_html .= WBK_Stripe::renderPaymentMethods( $service_id, $appointment_ids, ' wbk_payment_button_afterform' );
                $payment_methods_html .= WBK_WooCommerce::renderPaymentMethods( $service_id, $appointment_ids );
            }
            
            if ( $payment_methods_html != '' ) {
                if ( get_option( 'wbk_allow_coupons', 'disabled' ) == 'enabled' ) {
                    $payment_methods_html = '<input class="wbk-input" id="wbk-coupon" placeholder="' . __( 'coupon code', 'wbk' ) . '" >' . $payment_methods_html;
                }
            }
            
            if ( in_array( 'arrival', $payment_methods ) ) {
                $button_text = get_option( 'wbk_pay_on_arrival_button_text', __( 'Pay on arrival', 'wbk' ) );
                if ( $button_text == '' ) {
                    $button_text = __( 'Pay on arrival', 'wbk' );
                }
                $payment_methods_html .= '<input class="wbk-button wbk-width-100 wbk-mt-10-mb-10 wbk-payment-init wbk-payment-on-booking-init" data-method="arrival" data-app-id="' . implode( ',', $appointment_ids ) . '"  value="' . $button_text . '  " type="button">';
            }
            
            
            if ( in_array( 'bank', $payment_methods ) ) {
                $button_text = get_option( 'wbk_bank_transfer_button_text', __( 'Pay by bank transfer', 'wbk' ) );
                $payment_methods_html .= '<input class="wbk-button wbk-width-100 wbk-mt-10-mb-10 wbk-payment-init wbk-payment-on-booking-init" data-method="bank" data-app-id="' . implode( ',', $appointment_ids ) . '"  value="' . $button_text . '  " type="button">';
            }
            
            $thanks_message .= $payment_methods_html;
        }
        
        
        if ( count( $appointment_ids ) > 0 ) {
            $booked_slot_text = WBK_Db_Utils::booked_slot_placeholder_processing( $appointment_ids[0] );
        } else {
            $booked_slot_text = '';
        }
        
        $result = array(
            'thanks_message'   => $thanks_message,
            'booked_slot_text' => $booked_slot_text,
        );
        echo  json_encode( $result ) ;
        date_default_timezone_set( 'UTC' );
        die;
        return;
    }
    
    public function ajaxPreparePayment()
    {
        global  $wbk_wording ;
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $method = sanitize_text_field( $_POST['method'] );
        $app_ids = explode( ',', sanitize_text_field( $_POST['app_id'] ) );
        $referer = explode( '?', wp_get_referer() );
        $coupon = sanitize_text_field( trim( $_POST['coupon'] ) );
        
        if ( $method == 'arrival' ) {
            foreach ( $app_ids as $app_id ) {
                WBK_Db_Utils::setPaymentMethodToAppointment( $app_id, 'Pay on arrival' );
            }
            $html = get_option( 'wbk_pay_on_arrival_message', '' );
            if ( $html == '' ) {
                $html = __( 'Your booking should be paid on arrival', 'wbk' );
            }
            echo  $html ;
            wp_die();
            return;
        }
        
        
        if ( $method == 'bank' ) {
            foreach ( $app_ids as $app_id ) {
                WBK_Db_Utils::setPaymentMethodToAppointment( $app_id, 'Bank transfer' );
            }
            $html = get_option( 'wbk_bank_transfer_message', '' );
            $html = apply_filters( 'wbk_bank_transfer_message', $html, $app_ids );
            echo  $html ;
            wp_die();
            return;
        }
        
        $pay_not_approved = get_option( 'wbk_appointments_allow_payments', 'disabled' );
        $appointment_ids = array();
        $sub_total = 0;
        foreach ( $app_ids as $appointment_id ) {
            $status = WBK_Db_Utils::getStatusByAppointmentId( $appointment_id );
            $service = WBK_Db_Utils::initServiceById( WBK_Db_Utils::getServiceIdByAppointmentId( $appointment_id ) );
            $appointment = WBK_Db_Utils::initAppointmentById( $appointment_id );
            $price = $service->getPrice( $appointment->getTime() ) * $appointment->getQuantity();
            if ( $status == 'woocommerce' || $status == 'paid' || $status == 'paid_approved' || $status == 'pending' && $pay_not_approved == 'enabled' || is_null( $status ) ) {
                continue;
            }
            $sub_total += $price;
            $appointment_ids[] = $appointment_id;
        }
        
        if ( count( $appointment_ids ) == 0 ) {
            global  $wbk_wording ;
            $html = get_option( 'wbk_nothing_to_pay_message', '' );
            if ( $html == '' ) {
                $html = $wbk_wording['nothing_to_pay'];
            }
            
            if ( $method == 'woocommerce' ) {
                echo  json_encode( array(
                    'status'  => 0,
                    'details' => $html,
                ) ) ;
            } else {
                echo  $html ;
            }
            
            date_default_timezone_set( 'UTC' );
            wp_die();
            return;
        }
        
        
        if ( get_option( 'wbk_allow_coupons', 'disabled' ) == 'enabled' ) {
            
            if ( $coupon != '' ) {
                $service_id = WBK_Db_Utils::getServiceIdByAppointmentId( $appointment_ids[0] );
                $coupon_result = WBK_Validator::checkCoupon( $coupon, $service_id );
            } else {
                $coupon_result = FALSE;
            }
        
        } else {
            $coupon_result = FALSE;
        }
        
        $coupon_status_html = '';
        
        if ( get_option( 'wbk_allow_coupons', 'disabled' ) == 'enabled' && $coupon != '' ) {
            global  $wbk_wording ;
            
            if ( is_array( $coupon_result ) ) {
                $coupon_status_html = get_option( 'wbk_coupon_applied', __( 'Coupon applied', 'wbk' ) );
                if ( $coupon_status_html == '' ) {
                    $coupon_status_html = $wbk_wording['wbk_coupon_applied'];
                }
                foreach ( $appointment_ids as $appointment_id ) {
                    WBK_Db_Utils::setCouponToAppointment( $appointment_id, $coupon_result[0] );
                }
            } else {
                $coupon_status_html = get_option( 'wbk_coupon_not_applied', __( 'Coupon not applied', 'wbk' ) );
                if ( $coupon_status_html == '' ) {
                    $wbk_coupon_not_applied = $wbk_wording['wbk_coupon_not_applied'];
                }
                foreach ( $appointment_ids as $appointment_id ) {
                    WBK_Db_Utils::setCouponToAppointment( $appointment_id, 0 );
                }
            }
        
        }
        
        
        if ( is_array( $coupon_result ) ) {
            if ( $coupon_result[2] == 100 ) {
                wbk_set_appointment_as_paid_with_coupon( $appointment_ids, $method );
            }
            if ( $coupon_result[1] >= $sub_total ) {
                wbk_set_appointment_as_paid_with_coupon( $appointment_ids, $method );
            }
        }
        
        if ( $method == 'paypal' ) {
        }
        if ( $method == 'stripe' ) {
        }
        
        if ( $method == 'woocommerce' ) {
            $result = WBK_WooCommerce::addToCart( $appointment_ids );
            echo  $result ;
            wp_die();
            return;
        }
        
        $html = '<div class="wbk-details-sub-title">' . $coupon_status_html . '</div>' . $html;
        echo  $html ;
        date_default_timezone_set( 'UTC' );
        wp_die();
        return;
    }
    
    public function ajaxCancelAppointment()
    {
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $email = $_POST['email'];
        $email = strtolower( $email );
        $app_token = $_POST['app_token'];
        $app_token = str_replace( '"', '', $app_token );
        $app_token = str_replace( '<', '', $app_token );
        $app_token = str_replace( '\'', '', $app_token );
        $app_token = str_replace( '>', '', $app_token );
        $app_token = str_replace( '/', '', $app_token );
        $app_token = str_replace( '\\', '', $app_token );
        
        if ( !WBK_Validator::checkEmail( $email ) ) {
            global  $wbk_wording ;
            $message = get_option( 'wbk_booking_cancel_error_message', '' );
            
            if ( $message == '' ) {
                global  $wbk_wording ;
                $message = sanitize_text_field( $wbk_wording['booking_cancel_error'] );
            }
            
            echo  '<span class="wbk-input-label">' . $message . '</span>' ;
            date_default_timezone_set( 'UTC' );
            wp_die();
            return;
        }
        
        $appointment_ids = WBK_Db_Utils::getAppointmentIdsByGroupToken( $app_token );
        $valid = true;
        $arr_tokens = explode( '-', $app_token );
        $i = 0;
        $multi_booking_valid = true;
        foreach ( $appointment_ids as $appointment_id ) {
            $appt = WBK_Db_Utils::initAppointmentById( $appointment_id );
            if ( $appt->getEmail() != $email ) {
                $multi_booking_valid = false;
            }
        }
        
        if ( $multi_booking_valid ) {
            $customer_notification_mode = get_option( 'wbk_email_customer_cancel_multiple_mode', 'foreach' );
            $admin_notification_mode = get_option( 'wbk_email_admin_cancel_multiple_mode', 'foreach' );
            if ( $customer_notification_mode == 'one' && get_option( 'wbk_email_customer_appointment_cancel_status', '' ) == 'true' ) {
                
                if ( count( $appointment_ids ) > 0 ) {
                    $appointment = new WBK_Appointment_deprecated();
                    if ( $appointment->setId( $appointment_ids[0] ) ) {
                        
                        if ( $appointment->load() ) {
                            $recipient = $appointment->getEmail();
                            $notifications = new WBK_Email_Notifications( null, null );
                            $subject = get_option( 'wbk_email_customer_appointment_cancel_subject', '' );
                            $message = get_option( 'wbk_email_customer_bycustomer_appointment_cancel_message', '' );
                            $notifications->sendMultipleNotification(
                                $appointment_ids,
                                $message,
                                $subject,
                                $recipient
                            );
                        }
                    
                    }
                }
            
            }
            if ( $admin_notification_mode == 'one' && get_option( 'wbk_email_adimn_appointment_cancel_status', '' ) == 'true' ) {
                
                if ( count( $appointment_ids ) > 0 ) {
                    $appointment = new WBK_Appointment_deprecated();
                    if ( $appointment->setId( $appointment_ids[0] ) ) {
                        
                        if ( $appointment->load() ) {
                            $service = WBK_Db_Utils::initServiceById( $appointment->getService() );
                            
                            if ( $service != FALSE ) {
                                $recipient = $service->getEmail();
                                $subject = get_option( 'wbk_email_adimn_appointment_cancel_subject', '' );
                                $message = get_option( 'wbk_email_adimn_appointment_cancel_message', '' );
                                $notifications = new WBK_Email_Notifications( null, null );
                                $notifications->sendMultipleNotification(
                                    $appointment_ids,
                                    $message,
                                    $subject,
                                    $recipient
                                );
                                $super_admin_email = get_option( 'wbk_super_admin_email', '' );
                                if ( $super_admin_email != '' ) {
                                    $notifications->sendMultipleNotification(
                                        $appointment_ids,
                                        $message,
                                        $subject,
                                        $super_admin_email
                                    );
                                }
                            }
                        
                        }
                    
                    }
                }
            
            }
        }
        
        foreach ( $appointment_ids as $appointment_id ) {
            $service_id = WBK_Db_Utils::getServiceIdByAppointmentId( $appointment_id );
            if ( $service_id == FALSE ) {
                continue;
            }
            $noifications = new WBK_Email_Notifications( $service_id, $appointment_id );
            if ( $admin_notification_mode == 'foreach' ) {
                $noifications->prepareOnCancel();
            }
            if ( $customer_notification_mode == 'foreach' ) {
                $noifications->prepareOnCancelCustomer( true );
            }
            do_action( 'webba_before_cancel_booking', $appointment_id );
            
            if ( WBK_Db_Utils::deleteAppointmentByEmailTokenPair( $email, $arr_tokens[$i] ) == true ) {
                if ( $admin_notification_mode == 'foreach' ) {
                    $noifications->sendOnCancel();
                }
                if ( $customer_notification_mode == 'foreach' ) {
                    $noifications->sendOnCancelCustomer();
                }
                WBK_Db_Utils::freeLockedTimeSlot( $appointment_id );
            } else {
                $valid = false;
            }
            
            $i++;
        }
        
        if ( $valid == true ) {
            $message = get_option( 'wbk_booking_canceled_message', '' );
            
            if ( $message == '' ) {
                global  $wbk_wording ;
                $message = sanitize_text_field( $wbk_wording['booking_canceled'] );
            }
            
            $message = '<span class="wbk-input-label">' . $message . '</span>';
            $result = array(
                'status'  => 1,
                'message' => $message,
            );
        } else {
            global  $wbk_wording ;
            $message = get_option( 'wbk_booking_cancel_error_message', '' );
            
            if ( $message == '' ) {
                global  $wbk_wording ;
                $message = $wbk_wording['booking_cancel_error'];
            }
            
            $message = '<span class="wbk-input-label">' . $message . '</span>';
            $result = array(
                'status'  => 0,
                'message' => $message,
            );
        }
        
        echo  json_encode( $result ) ;
        date_default_timezone_set( 'UTC' );
        wp_die();
        return;
    }
    
    public function ajaxChargeStripe()
    {
    }
    
    public function ajaxPrepareServiceData()
    {
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $service_id = $_POST['service'];
        $offset = $_POST['offset'];
        $result = array();
        
        if ( !is_array( $service_id ) ) {
            
            if ( !is_numeric( $service_id ) ) {
                $result['disabilities'] = '';
                $result['limits'] = '';
                $result['abilities'] = '';
                echo  json_encode( $result ) ;
                date_default_timezone_set( 'UTC' );
                wp_die();
                return;
            }
            
            
            if ( get_option( 'wbk_date_input', 'popup' ) == 'popup' || get_option( 'wbk_date_input', 'popup' ) == 'classic' ) {
                $disabilities = WBK_Date_Time_Utils::getServiceAbiliy( $service_id );
                $result['disabilities'] = $disabilities;
                $dates = explode( ';', $disabilities );
                $result['limits'] = WBK_Date_Time_Utils::getServiceLimits( $service_id );
                $result['abilities'] = '';
                $result['week_disabilities'] = WBK_Date_Time_Utils::getServicWeekDisabiliy( $service_id );
            } else {
                $result['disabilities'] = '';
                $result['limits'] = '';
                $result['abilities'] = WBK_Date_Time_Utils::getBHAbilities( $service_id );
                $result['week_disabilities'] = '';
            }
        
        } else {
            $service_ids = $service_id;
            $total_array = array();
            $use_limits = TRUE;
            $range_start = 7863319160;
            $range_end = 0;
            foreach ( $service_ids as $service_id ) {
                if ( !is_numeric( $service_id ) ) {
                    continue;
                }
                
                if ( get_option( 'wbk_date_input', 'popup' ) == 'popup' || get_option( 'wbk_date_input', 'popup' ) == 'classic' ) {
                    $current_data = explode( ';', WBK_Date_Time_Utils::getServiceAbiliy( $service_id ) );
                } else {
                    $current_data = explode( ';', WBK_Date_Time_Utils::getBHAbilities( $service_id ) );
                }
                
                
                if ( count( $total_array ) == 0 ) {
                    $total_array = $current_data;
                } else {
                    $total_array = array_merge( $total_array, $current_data );
                }
                
                $service = WBK_Db_Utils::initServiceById( $service_id );
                $current_start = $service->getDateRangeStart();
                $current_end = $service->getDateRangeEnd();
                
                if ( $service->getDateRangeStart() == FALSE || $service->getDateRangeEnd() == FALSE ) {
                    $use_limits = FALSE;
                } else {
                    if ( $current_start < $range_start ) {
                        $range_start = $current_start;
                    }
                    if ( $current_end > $range_end ) {
                        $range_end = $current_end;
                    }
                }
            
            }
            $multi_serv_date_limit = get_option( 'wbk_multi_serv_date_limit', '360' );
            
            if ( $use_limits ) {
                $result['limits'] = date( 'Y,n,j', $range_start ) . '-' . date( 'Y,n,j', $range_end );
            } else {
                $result['limits'] = date( 'Y,n,j', strtotime( 'today midnight' ) ) . '-' . date( 'Y,n,j', strtotime( 'today midnight' ) + 86400 * $multi_serv_date_limit );
            }
            
            if ( $range_start == $range_end ) {
                if ( $range_end != 0 ) {
                    if ( $use_limits ) {
                        $result['limits'] = $range_start;
                    }
                }
            }
            $result['week_disabilities'] = '';
            
            if ( get_option( 'wbk_date_input', 'popup' ) == 'popup' || get_option( 'wbk_date_input', 'popup' ) == 'classic' ) {
                $result['disabilities'] = implode( ';', $total_array );
                $result['abilities'] = '';
            } else {
                $result['disabilities'] = '';
                $result['abilities'] = implode( ';', $total_array );
            }
        
        }
        
        echo  json_encode( $result ) ;
        date_default_timezone_set( 'UTC' );
        wp_die();
        return;
    }
    
    protected function render_booking_form_multiple( $service_ids, $times, $offset = 0 )
    {
        global  $wbk_wording ;
        $time_format = WBK_Date_Time_Utils::getTimeFormat();
        $date_format = WBK_Date_Time_Utils::getDateFormat();
        $service_ids_unique = array_unique( $service_ids );
        $time_zone_client = $_POST['time_zone_client'];
        $form_label_initial = explode( '[split]', html_entity_decode( html_entity_decode( get_option( 'wbk_form_label', '' ) ) ) );
        
        if ( count( $form_label_initial ) == 2 ) {
            $html_all_services = '<div class="wbk-details-sub-title">' . $form_label_initial[0] . '</div>';
            $html_all_services = str_replace( '#total_amount', '<span class="wbk_form_label_total"></span>', $html_all_services );
            $repeatable_part = $form_label_initial[1];
        } else {
            $html_all_services = '';
            $repeatable_part = $form_label_initial[0];
        }
        
        $forms = array();
        foreach ( $service_ids_unique as $service_id ) {
            $service = new WBK_Service_deprecated();
            if ( !$service->setId( $service_id ) ) {
                return FALSE;
            }
            if ( !$service->load() ) {
                return FALSE;
            }
            $forms[] = $service->getForm();
            $form_label = $repeatable_part;
            $form_label = str_replace( '#service', $service->getName(), $form_label );
            $form_label = str_replace( '#description', $service->getDescription(), $form_label );
            $price_format = get_option( 'wbk_payment_price_format', '$#price' );
            $price = str_replace( '#price', number_format(
                $service->getPrice(),
                2,
                '.',
                ''
            ), $price_format );
            $form_label = str_replace( '#price', $price, $form_label );
            $time = array();
            $i = 0;
            foreach ( $times as $curent_time ) {
                if ( $service_ids[$i] == $service_id ) {
                    $time[] = $curent_time;
                }
                $i++;
            }
            
            if ( is_array( $time ) ) {
                $date_collect = array();
                $time_collect = array();
                $datetime_collect = array();
                $datetime_n_collect = array();
                $datetimerange_n_collect = array();
                $time_local_collect = array();
                $date_local_collect = array();
                foreach ( $time as $time_this ) {
                    $timezone = new DateTimeZone( get_option( 'wbk_timezone', 'UTC' ) );
                    $date_this = ( new DateTime( '@' . $time_this ) )->setTimezone( new DateTimeZone( $time_zone_client ) );
                    $current_offset = $offset * -60 - $timezone->getOffset( $date_this );
                    $end_this = $time_this + $service->getDuration() * 60;
                    $date_collect[] = wp_date( $date_format, $time_this, new DateTimeZone( date_default_timezone_get() ) );
                    $time_collect[] = wp_date( $time_format, $time_this, new DateTimeZone( date_default_timezone_get() ) );
                    $time_local_collect[] = wp_date( $time_format, $time_this + $current_offset, new DateTimeZone( date_default_timezone_get() ) );
                    $date_local_collect[] = wp_date( $date_format, $time_this + $current_offset, new DateTimeZone( date_default_timezone_get() ) );
                    $datetime_collect[] = wp_date( $date_format, $time_this, new DateTimeZone( date_default_timezone_get() ) ) . ' ' . wp_date( $time_format, $time_this, new DateTimeZone( date_default_timezone_get() ) );
                    $datetime_local_collect[] = wp_date( $date_format, $time_this + $current_offset, new DateTimeZone( date_default_timezone_get() ) ) . ' ' . wp_date( $time_format, $time_this + $current_offset, new DateTimeZone( date_default_timezone_get() ) );
                    $datetime_n_collect[] = '<br>' . wp_date( $date_format, $time_this, new DateTimeZone( date_default_timezone_get() ) ) . ' ' . wp_date( $time_format, $time_this, new DateTimeZone( date_default_timezone_get() ) );
                    $datetimerange_n_collect[] = '<br>' . wp_date( $date_format, $time_this, new DateTimeZone( date_default_timezone_get() ) ) . '   ' . wp_date( $time_format, $time_this, new DateTimeZone( date_default_timezone_get() ) ) . ' - ' . wp_date( $time_format, $end_this, new DateTimeZone( date_default_timezone_get() ) );
                }
                $form_label = str_replace( '#date', implode( ', ', $date_collect ), $form_label );
                $form_label = str_replace( '#time', implode( ', ', $time_collect ), $form_label );
                $form_label = str_replace( '#local', implode( ', ', $time_local_collect ), $form_label );
                $form_label = str_replace( '#dlocal', implode( ', ', $date_local_collect ), $form_label );
                $form_label = str_replace( '#dt', implode( ', ', $datetime_collect ), $form_label );
                $form_label = str_replace( '#drt', implode( '', $datetime_n_collect ), $form_label );
                $form_label = str_replace( '#dre', implode( '', $datetimerange_n_collect ), $form_label );
                $form_label = str_replace( '#dlt', implode( ', ', $datetime_local_collect ), $form_label );
            }
            
            $form_label = str_replace( '#total_amount', '', $form_label );
            $form_label = str_replace( '#selected_count', count( $time ), $form_label );
            $html = '<div class="wbk-details-sub-title">' . $form_label . ' </div>';
            $html .= '<hr class="wbk-form-separator">';
            $html_all_services .= $html;
        }
        foreach ( $service_ids_unique as $service_id ) {
            $html = '';
            $service = new WBK_Service_deprecated();
            if ( !$service->setId( $service_id ) ) {
                return FALSE;
            }
            if ( !$service->load() ) {
                return FALSE;
            }
            $time = array();
            $i = 0;
            foreach ( $times as $curent_time ) {
                if ( $service_ids[$i] == $service_id ) {
                    $time[] = $curent_time;
                }
                $i++;
            }
            
            if ( $service->getQuantity() > 1 ) {
                $sp = new WBK_Schedule_Processor();
                
                if ( is_array( $time ) ) {
                    $avail_count = 1000000;
                    foreach ( $time as $time_this ) {
                        $day = strtotime( 'today midnight', $time_this );
                        $sp->get_time_slots_by_day(
                            $day,
                            $service_id,
                            false,
                            true,
                            true
                        );
                        $current_avail = $sp->get_available_count( $time_this );
                        if ( $current_avail < $avail_count ) {
                            $avail_count = $current_avail;
                        }
                    }
                } else {
                    $day = strtotime( 'today midnight', $time );
                    $sp->get_time_slots_by_day(
                        $day,
                        $service_id,
                        false,
                        true,
                        true
                    );
                    $avail_count = $sp->get_available_count( $time );
                }
                
                $quantity_label = get_option( 'wbk_book_items_quantity_label', '' );
                if ( $quantity_label == '' ) {
                    $quantity_label = sanitize_text_field( $wbk_wording['quantity_label'] );
                }
                $quantity_label = str_replace( '#service', $service->getName(), $quantity_label );
                $selection_mode = get_option( 'wbk_places_selection_mode', 'normal' );
                
                if ( $selection_mode == 'normal' ) {
                    $html .= '<label class="wbk-input-label" for="wbk-quantity">' . $quantity_label . '</label>';
                    $html .= '<select autocomplete="disabled" type="text" data-service="' . $service_id . '" class="wbk-input wbk-width-100 wbk-mb-10 wbk-book-quantity">';
                    for ( $i = $service->getMinQuantity() ;  $i <= $avail_count ;  $i++ ) {
                        $html .= '<option value="' . $i . '" >' . $i . '</option>';
                    }
                } elseif ( $selection_mode == '1' ) {
                    $html .= '<select autocomplete="disabled" type="text" data-service="' . $service_id . '" class="wbk-input wbk_hidden wbk-width-100 wbk-mb-10 wbk-book-quantity">';
                    $html .= '<option value="1">1</option>';
                    $html .= '</select>';
                } elseif ( $selection_mode == 'max' ) {
                    $html .= '<select  autocomplete="disabled" type="text" data-service="' . $service_id . '" class="wbk-input wbk_hidden wbk-width-100 wbk-mb-10 wbk-book-quantity">';
                    $html .= '<option value="' . $service->getQuantity() . '">' . $service->getQuantity() . '</option>';
                    $html .= '</select>';
                }
                
                $html .= '</select>';
            }
            
            $html_all_services .= $html;
        }
        $html = $html_all_services;
        $forms = array_unique( $forms );
        
        if ( count( $forms ) == 1 ) {
            $form = $forms[0];
        } else {
            $form = 0;
        }
        
        
        if ( $form == 0 ) {
            $name_label = get_option( 'wbk_name_label', '' );
            $email_label = get_option( 'wbk_email_label', '' );
            $phone_label = get_option( 'wbk_phone_label', '' );
            $comment_label = get_option( 'wbk_comment_label', '' );
            if ( $name_label == '' ) {
                $name_label = sanitize_text_field( $wbk_wording['form_name'] );
            }
            if ( $email_label == '' ) {
                $email_label = sanitize_text_field( $wbk_wording['form_email'] );
            }
            if ( $phone_label == '' ) {
                $phone_label = sanitize_text_field( $wbk_wording['form_phone'] );
            }
            if ( $comment_label == '' ) {
                $comment_label = sanitize_text_field( $wbk_wording['form_comment'] );
            }
            $html .= '<label class="wbk-input-label" for="wbk-name">' . $name_label . '</label>';
            $html .= '<input name="wbk-name" autocomplete="disabled" type="text" class="wbk-input wbk-width-100 wbk-mb-10" id="wbk-name" />';
            $html .= '<label class="wbk-input-label" for="wbk-email">' . $email_label . '</label>';
            $html .= '<input name="wbk-email" autocomplete="disabled" type="text" class="wbk-input wbk-width-100 wbk-mb-10" id="wbk-email" />';
            $html .= '<label class="wbk-input-label" for="wbk-phone">' . $phone_label . '</label>';
            $html .= '<input name="wbk-phone" autocomplete="disabled" autocomplete="disabled" type="text" class="wbk-input wbk-width-100 wbk-mb-10" id="wbk-phone" />';
            $html .= '<label class="wbk-input-label" for="wbk-comment">' . $comment_label . '</label>';
            $html .= '<textarea name="wbk-comment" rows="3" class="wbk-input wbk-textarea wbk-width-100 wbk-mb-10" id="wbk-comment"></textarea> ';
        } else {
            
            if ( class_exists( 'Cf7_Polylang_Public' ) ) {
                $cf7_polylang = new Cf7_Polylang_Public( '1', '1' );
                if ( method_exists( $cf7_polylang, 'translate_form_id' ) ) {
                    $form = $cf7_polylang->translate_form_id( $form, null );
                }
            }
            
            $form = apply_filters(
                'wpml_object_id',
                $form,
                'wpcf7_contact_form',
                true
            );
            $cf7_form = do_shortcode( '[contact-form-7 id="' . $form . '"]' );
            $cf7_form = apply_filters( 'wbk_after_cf7_rendered', $cf7_form );
            $cf7_form = str_replace( '<p>', '', $cf7_form );
            $cf7_form = str_replace( '</p>', '', $cf7_form );
            $cf7_form = str_replace( '<label', '<label class="wbk-input-label" ', $cf7_form );
            $cf7_form = str_replace( 'type="checkbox"', 'type="checkbox" class="wbk-checkbox" ', $cf7_form );
            $cf7_form = str_replace( 'wbk-checkbox', ' wbk-checkbox wbk-checkbox-custom ', $cf7_form );
            $cf7_form = str_replace( 'wpcf7-list-item-label', 'wbk-checkbox-label', $cf7_form );
            $cf7_form = str_replace( 'wpcf7-list-item', 'wbk-checkbox-span-holder', $cf7_form );
            $cf7_form = str_replace( 'wpcf7-list-item-label', 'wbk-checkbox-label', $cf7_form );
            $cf7_form = str_replace( 'name="wbk-acceptance"', 'name="wbk-acceptance" value="1" id="wbk-acceptance" aria-invalid="false"><span class="wbk-checkbox-label"></span> <input type="hidden"', $cf7_form );
            $cf7_form = str_replace( 'type="file"', 'type="file" accept="application/pdf,image/png,image/jpeg,.doc, .docx"', $cf7_form );
            $html .= $cf7_form;
        }
        
        $book_text = get_option( 'wbk_book_text_form', '' );
        if ( $book_text == '' ) {
            $book_text = $wbk_wording['book_text'];
        }
        $html .= '<input type="button" class="wbk-button wbk-width-100 wbk-mt-10-mb-10" id="wbk-book_appointment" value="' . $book_text . '">';
        
        if ( get_option( 'wbk_show_cancel_button', 'disabled' ) == 'enabled' ) {
            global  $wbk_wording ;
            $cancel_label = get_option( 'wbk_cancel_button_text', '' );
            if ( $cancel_label == '' ) {
                $cancel_label = sanitize_text_field( $wbk_wording['cancel_label_form'] );
            }
            $html .= '<input class="wbk-button wbk-width-100 wbk-cancel-button"  value="' . $cancel_label . '" type="button">';
        }
        
        return '<hr class="wbk-separator"/>' . $html;
    }
    
    protected function render_booking_form( $service_id, $time, $offset = 0 )
    {
        global  $wbk_wording ;
        $time_format = WBK_Date_Time_Utils::getTimeFormat();
        $date_format = WBK_Date_Time_Utils::getDateFormat();
        $service = new WBK_Service_deprecated();
        $time_zone_client = $_POST['time_zone_client'];
        if ( !$service->setId( $service_id ) ) {
            return FALSE;
        }
        if ( !$service->load() ) {
            return FALSE;
        }
        $form = $service->getForm();
        $form_label = html_entity_decode( html_entity_decode( get_option( 'wbk_form_label', '' ) ) );
        if ( $form_label == '' ) {
            $form_label = sanitize_text_field( $wbk_wording['form_label'] );
        }
        $form_label = str_replace( '#service', $service->getName(), $form_label );
        $price_format = get_option( 'wbk_payment_price_format', '$#price' );
        $price = str_replace( '#price', number_format(
            $service->getPrice(),
            get_option( 'wbk_price_fractional', '2' ),
            get_option( 'wbk_price_separator', '.' ),
            ''
        ), $price_format );
        $form_label = str_replace( '#price', $price, $form_label );
        $form_label = str_replace( '#description', $service->getDescription(), $form_label );
        
        if ( is_array( $time ) ) {
            $date_collect = array();
            $time_collect = array();
            $datetime_collect = array();
            $start = 2554146984;
            $end = 0;
            foreach ( $time as $time_this ) {
                $timezone = new DateTimeZone( get_option( 'wbk_timezone', 'UTC' ) );
                $date_this = ( new DateTime( '@' . $time_this ) )->setTimezone( new DateTimeZone( $time_zone_client ) );
                $current_offset = $offset * -60 - $timezone->getOffset( $date_this );
                $cur_start = $time_this;
                $cur_end = $cur_start + $service->getDuration() * 60;
                if ( $cur_start < $start ) {
                    $start = $cur_start;
                }
                if ( $cur_end > $end ) {
                    $end = $cur_end;
                }
                $end_this = $time_this + $service->getDuration() * 60;
                $date_collect[] = wp_date( $date_format, $time_this, new DateTimeZone( date_default_timezone_get() ) );
                $time_collect[] = wp_date( $time_format, $time_this, new DateTimeZone( date_default_timezone_get() ) );
                $time_local_collect[] = wp_date( $time_format, $time_this + $current_offset, new DateTimeZone( date_default_timezone_get() ) );
                $date_local_collect[] = wp_date( $date_format, $time_this + $current_offset, new DateTimeZone( date_default_timezone_get() ) );
                $datetime_collect[] = wp_date( $date_format, $time_this, new DateTimeZone( date_default_timezone_get() ) ) . ' ' . wp_date( $time_format, $time_this, new DateTimeZone( date_default_timezone_get() ) );
                $datetime_n_collect[] = '<br>' . wp_date( $date_format, $time_this, new DateTimeZone( date_default_timezone_get() ) ) . ' ' . wp_date( $time_format, $time_this, new DateTimeZone( date_default_timezone_get() ) );
                $datetimerange_n_collect[] = '<br>' . wp_date( $date_format, $time_this, new DateTimeZone( date_default_timezone_get() ) ) . ' / ' . wp_date( $time_format, $time_this, new DateTimeZone( date_default_timezone_get() ) ) . ' - ' . wp_date( $time_format, $end_this, new DateTimeZone( date_default_timezone_get() ) );
                $datetime_local_collect[] = wp_date( $date_format, $time_this + $current_offset, new DateTimeZone( date_default_timezone_get() ) ) . ' ' . wp_date( $time_format, $time_this + $current_offset, new DateTimeZone( date_default_timezone_get() ) );
            }
            $time_range = wp_date( $time_format, $start, new DateTimeZone( date_default_timezone_get() ) ) . ' - ' . wp_date( $time_format, $end, new DateTimeZone( date_default_timezone_get() ) );
            $single_start_date = wp_date( $date_format, $start, new DateTimeZone( date_default_timezone_get() ) );
            $date_time_range = wp_date( $date_format, $start, new DateTimeZone( date_default_timezone_get() ) ) . ' ' . wp_date( $time_format, $start, new DateTimeZone( date_default_timezone_get() ) ) . ' - ' . wp_date( $date_format, $end, new DateTimeZone( date_default_timezone_get() ) ) . ' ' . wp_date( $time_format, $end, new DateTimeZone( date_default_timezone_get() ) );
            $form_label = str_replace( '#date', implode( ', ', $date_collect ), $form_label );
            $form_label = str_replace( '#time', implode( ', ', $time_collect ), $form_label );
            $form_label = str_replace( '#local', implode( ', ', $time_local_collect ), $form_label );
            $form_label = str_replace( '#dlocal', implode( ', ', $date_local_collect ), $form_label );
            $form_label = str_replace( '#dt', implode( ', ', $datetime_collect ), $form_label );
            $form_label = str_replace( '#drt', implode( '', $datetime_n_collect ), $form_label );
            $form_label = str_replace( '#dre', implode( '', $datetimerange_n_collect ), $form_label );
            $form_label = str_replace( '#selected_count', count( $time ), $form_label );
            $form_label = str_replace( '#range', $time_range, $form_label );
            $form_label = str_replace( '#sd', $single_start_date, $form_label );
            $form_label = str_replace( '#dlt', implode( ', ', $datetime_local_collect ), $form_label );
        } else {
            $timezone = new DateTimeZone( get_option( 'wbk_timezone', 'UTC' ) );
            $date = ( new DateTime( '@' . $time ) )->setTimezone( new DateTimeZone( $time_zone_client ) );
            $current_offset = $offset * -60 - $timezone->getOffset( $date );
            $form_label = str_replace( '#date', wp_date( $date_format, $time, new DateTimeZone( date_default_timezone_get() ) ), $form_label );
            $form_label = str_replace( '#time', wp_date( $time_format, $time, new DateTimeZone( date_default_timezone_get() ) ), $form_label );
            $form_label = str_replace( '#dt', wp_date( $date_format, $time, new DateTimeZone( date_default_timezone_get() ) ) . ' ' . wp_date( $time_format, $time, new DateTimeZone( date_default_timezone_get() ) ), $form_label );
            $local_time = $time + $current_offset;
            $form_label = str_replace( '#local', wp_date( $time_format, $local_time, new DateTimeZone( date_default_timezone_get() ) ), $form_label );
            $form_label = str_replace( '#dlocal', wp_date( $date_format, $local_time, new DateTimeZone( date_default_timezone_get() ) ), $form_label );
        }
        
        $form_label = str_replace( '#total_amount', '<span class="wbk_form_label_total"></span>', $form_label );
        $html = '<div class="wbk-details-sub-title">' . $form_label . ' </div>';
        $html .= '<hr class="wbk-form-separator">';
        
        if ( $service->getQuantity() > 1 ) {
            $service_schedule = new WBK_Service_Schedule();
            $service_schedule->setServiceId( $service->getId() );
            $sp = new WBK_Schedule_Processor();
            
            if ( is_array( $time ) ) {
                $avail_count = 1000000;
                foreach ( $time as $time_this ) {
                    $day = strtotime( 'today midnight', $time_this );
                    $sp->get_time_slots_by_day(
                        $day,
                        $service_id,
                        false,
                        true,
                        true
                    );
                    $current_avail = $sp->get_available_count( $time_this );
                    if ( $current_avail < $avail_count ) {
                        $avail_count = $current_avail;
                    }
                }
            } else {
                $day = strtotime( 'today midnight', $time );
                $sp->get_time_slots_by_day(
                    $day,
                    $service_id,
                    false,
                    true,
                    true
                );
                $avail_count = $sp->get_available_count( $time );
            }
            
            $quantity_label = get_option( 'wbk_book_items_quantity_label', '' );
            if ( $quantity_label == '' ) {
                $quantity_label = sanitize_text_field( $wbk_wording['quantity_label'] );
            }
            $quantity_label = str_replace( '#service', $service->getName(), $quantity_label );
            $selection_mode = get_option( 'wbk_places_selection_mode', 'normal' );
            
            if ( $selection_mode == 'normal' ) {
                $html .= '<label class="wbk-input-label" autocomplete="disabled" for="wbk-quantity">' . $quantity_label . '</label>';
                $html .= '<select name="wbk-book-quantity" data-service="' . $service->getId() . '" type="text" class="wbk-input wbk-width-100 wbk-mb-10 wbk-book-quantity" id="wbk-book-quantity">';
                for ( $i = $service->getMinQuantity() ;  $i <= $avail_count ;  $i++ ) {
                    $html .= '<option value="' . $i . '" >' . $i . '</option>';
                }
            } elseif ( $selection_mode == '1' ) {
                $html .= '<select name="wbk-book-quantity" data-service="' . $service->getId() . '" autocomplete="disabled" type="text" class="wbk-input wbk_hidden wbk-width-100 wbk-mb-10 wbk-book-quantity" id="wbk-book-quantity">';
                $html .= '<option value="1">1</option>';
                $html .= '</select>';
            } elseif ( $selection_mode == 'max' ) {
                $html .= '<select name="wbk-book-quantity" data-service="' . $service->getId() . '" autocomplete="disabled" type="text" class="wbk-input wbk_hidden wbk-width-100 wbk-mb-10 wbk-book-quantity" id="wbk-book-quantity">';
                $html .= '<option value="' . $service->getQuantity() . '">' . $service->getQuantity() . '</option>';
                $html .= '</select>';
            }
            
            $html .= '</select>';
        }
        
        
        if ( $form == 0 ) {
            $name_label = get_option( 'wbk_name_label', '' );
            $email_label = get_option( 'wbk_email_label', '' );
            $phone_label = get_option( 'wbk_phone_label', '' );
            $comment_label = get_option( 'wbk_comment_label', '' );
            if ( $name_label == '' ) {
                $name_label = sanitize_text_field( $wbk_wording['form_name'] );
            }
            if ( $email_label == '' ) {
                $email_label = sanitize_text_field( $wbk_wording['form_email'] );
            }
            if ( $phone_label == '' ) {
                $phone_label = sanitize_text_field( $wbk_wording['form_phone'] );
            }
            if ( $comment_label == '' ) {
                $comment_label = sanitize_text_field( $wbk_wording['form_comment'] );
            }
            $html .= '<label class="wbk-input-label" for="wbk-name">' . $name_label . '</label>';
            $html .= '<input name="wbk-name" type="text" autocomplete="disabled" class="wbk-input wbk-width-100 wbk-mb-10" id="wbk-name" />';
            $html .= '<label class="wbk-input-label" for="wbk-email">' . $email_label . '</label>';
            $html .= '<input name="wbk-email" autocomplete="disabled" type="text" class="wbk-input wbk-width-100 wbk-mb-10" id="wbk-email" />';
            $html .= '<label class="wbk-input-label" for="wbk-phone">' . $phone_label . '</label>';
            $html .= '<input name="wbk-phone" autocomplete="disabled" type="text" class="wbk-input wbk-width-100 wbk-mb-10" id="wbk-phone" />';
            $html .= '<label class="wbk-input-label" for="wbk-comment">' . $comment_label . '</label>';
            $html .= '<textarea name="wbk-comment" rows="3" class="wbk-input wbk-textarea wbk-width-100 wbk-mb-10" id="wbk-comment"></textarea> ';
        } else {
            
            if ( class_exists( 'Cf7_Polylang_Public' ) ) {
                $cf7_polylang = new Cf7_Polylang_Public( '1', '1' );
                if ( method_exists( $cf7_polylang, 'translate_form_id' ) ) {
                    $form = $cf7_polylang->translate_form_id( $form, null );
                }
            }
            
            $form = apply_filters(
                'wpml_object_id',
                $form,
                'wpcf7_contact_form',
                true
            );
            $cf7_form = do_shortcode( '[contact-form-7 id="' . $form . '"]' );
            $cf7_form = apply_filters( 'wbk_after_cf7_rendered', $cf7_form );
            $cf7_form = str_replace( '<p>', '', $cf7_form );
            $cf7_form = str_replace( '</p>', '', $cf7_form );
            $cf7_form = str_replace( '<label', '<label class="wbk-input-label" ', $cf7_form );
            $cf7_form = str_replace( 'type="checkbox"', 'type="checkbox" class="wbk-checkbox" ', $cf7_form );
            $cf7_form = str_replace( 'wbk-checkbox', ' wbk-checkbox wbk-checkbox-custom ', $cf7_form );
            $cf7_form = str_replace( 'wpcf7-list-item-label', 'wbk-checkbox-label', $cf7_form );
            $cf7_form = str_replace( 'wpcf7-list-item', 'wbk-checkbox-span-holder', $cf7_form );
            $cf7_form = str_replace( 'wpcf7-list-item-label', 'wbk-checkbox-label', $cf7_form );
            $cf7_form = str_replace( 'name="wbk-acceptance"', 'name="wbk-acceptance" value="1" id="wbk-acceptance" aria-invalid="false"><span class="wbk-checkbox-label"></span><input type="hidden"', $cf7_form );
            $cf7_form = str_replace( 'type="file"', 'type="file" accept="application/pdf,image/png,image/jpeg,.doc, .docx"', $cf7_form );
            $html .= $cf7_form;
        }
        
        $book_text = get_option( 'wbk_book_text_form', '' );
        if ( $book_text == '' ) {
            $book_text = $wbk_wording['book_text'];
        }
        $html .= '<input type="button" class="wbk-button wbk-width-100 wbk-mt-10-mb-10" id="wbk-book_appointment" value="' . $book_text . '">';
        
        if ( get_option( 'wbk_show_cancel_button', 'disabled' ) == 'enabled' ) {
            global  $wbk_wording ;
            $cancel_label = get_option( 'wbk_cancel_button_text', '' );
            if ( $cancel_label == '' ) {
                $cancel_label = sanitize_text_field( $wbk_wording['cancel_label_form'] );
            }
            $html .= '<input class="wbk-button wbk-width-100 wbk-cancel-button"  value="' . $cancel_label . '" type="button">';
        }
        
        return '<hr class="wbk-separator"/>' . $html;
    }
    
    public function searchMultiServiceTimeSlots( $service_ids, $date, $offset )
    {
        $date_format = WBK_Date_Time_Utils::getDateFormat();
        
        if ( !is_numeric( $date ) ) {
            $day_to_render = strtotime( $date );
        } else {
            $day_to_render = $date;
        }
        
        if ( !is_numeric( $offset ) ) {
            $offset = 0;
        }
        
        if ( get_option( 'wbk_mode', 'extended' ) == 'extended' ) {
            $output_count = 2;
        } else {
            $output_count = 0;
        }
        
        $html = '';
        $i = 0;
        while ( $i <= $output_count ) {
            $day_slots_all_services = '';
            foreach ( $service_ids as $service_id ) {
                $service_schedule = new WBK_Service_Schedule();
                $service_schedule->setServiceId( $service_id );
                if ( !$service_schedule->load() ) {
                    continue;
                }
                $day_status = $service_schedule->getDayStatus( $day_to_render );
                
                if ( $day_status == 1 ) {
                    $time_after = $day_to_render;
                    $service_schedule->buildSchedule( $day_to_render );
                    $day_slots = $service_schedule->renderDayFrontend( $time_after, $offset );
                }
                
                
                if ( get_option( 'wbk_mode', 'extended' ) == 'extended' ) {
                    $day_to_render = strtotime( 'tomorrow', $day_to_render );
                } else {
                    $i++;
                }
                
                $day_slots_all_services .= $day_slots;
            }
            $date_regular = wp_date( $date_format, $day_to_render, new DateTimeZone( date_default_timezone_get() ) );
            $timezone = new DateTimeZone( get_option( 'wbk_timezone', 'UTC' ) );
            $current_offset = $offset * -60 - $timezone->getOffset( new DateTime() );
            $date_local = wp_date( $date_format, $day_to_render + $current_offset, new DateTimeZone( date_default_timezone_get() ) );
            $day_title = get_option( 'wbk_day_label', '#date' );
            $day_title = str_replace( '#date', $date_regular, $day_title );
            $day_title = str_replace( '#local_date', $date_local, $day_title );
            
            if ( $day_slots_all_services != '' ) {
                $html .= '<div class="wbk-col-12-12">
							<div class="wbk-day-title">
								' . $day_title . '
							</div>
							<hr class="wbk-day-separator">
  						  </div>';
                $html .= '<div class="wbk-col-12-12 wbk-text-center" >' . $day_slots . '</div>';
            }
            
            $i++;
        }
        return $html;
    }

}
function wbk_set_appointment_as_paid_with_coupon( $app_ids, $method )
{
    WBK_Db_Utils::updatePaymentStatusByIds( $app_ids );
    foreach ( $app_ids as $app_id ) {
        WBK_Db_Utils::setPaymentMethodToAppointment( $app_id, 'paid by applying coupon' );
    }
    
    if ( $method == 'paypal' && get_option( 'wbk_paypal_redirect_url' ) != '' ) {
        echo  'redirect:' . get_option( 'wbk_paypal_redirect_url' ) ;
        wp_die();
        return;
    }
    
    
    if ( $method == 'stripe' && get_option( 'wbk_stripe_redirect_url' ) != '' ) {
        echo  'redirect:' . get_option( 'wbk_stripe_redirect_url' ) ;
        wp_die();
        return;
    }
    
    $html = '<div class="wbk-input-label wbk_payment_success">';
    global  $wbk_wording ;
    $payment_complete_label = get_option( 'wbk_payment_success_message', '' );
    if ( $payment_complete_label == '' ) {
        $payment_complete_label = sanitize_text_field( $wbk_wording['payment_complete'] );
    }
    $html .= $payment_complete_label;
    $html .= '</div>';
    echo  $html ;
    date_default_timezone_set( 'UTC' );
    wp_die();
    return;
}
