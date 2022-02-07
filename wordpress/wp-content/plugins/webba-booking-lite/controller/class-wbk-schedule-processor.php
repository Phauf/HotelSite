<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class WBK_Schedule_Processor
{
    protected  $locked_days ;
    protected  $unlocked_days ;
    protected  $locked_timeslots ;
    protected  $gg_breakers ;
    protected  $ext_breakers ;
    protected  $breakers ;
    protected  $appointments ;
    protected  $timeslots ;
    public function load_locked_days()
    {
        global  $wpdb ;
        if ( !is_null( $this->locked_days ) ) {
            return;
        }
        $result = $wpdb->get_results( "\n\t\t\t\t\t\tSELECT day, service_id\n\t\t\t\t\t\tFROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_days_on_off\n\t\t\t\t\t\twhere status = 0  and day > " . time() );
        foreach ( $result as $item ) {
            $this->locked_days[$item->service_id][] = $item->day;
        }
    }
    
    public function load_unlocked_days()
    {
        global  $wpdb ;
        $result = $wpdb->get_results( "\n                        SELECT day, service_id\n                        FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_days_on_off\n                        where status = 1 and day > " . time() );
        foreach ( $result as $item ) {
            $this->unlocked_days[$item->service_id][] = $item->day;
        }
    }
    
    public function load_locked_timeslots()
    {
        global  $wpdb ;
        $result = $wpdb->get_results( "\n\t\t\t\t\t\tSELECT time, service_id\n\t\t\t\t\t\tFROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_locked_time_slots" );
        foreach ( $result as $item ) {
            $this->locked_time_slots[$item->service_id][] = $item->time;
        }
    }
    
    public function get_time_slots_by_day(
        $day,
        $service_id,
        $skip_gg_calendar = false,
        $ignore_preparation = false,
        $calculate_availability = false
    )
    {
        $this->load_locked_days();
        $this->load_unlocked_days();
        $this->load_locked_timeslots();
        $working = $this->is_working_day( $day, $service_id );
        $service = new WBK_Service( $service_id );
        $time_format = WBK_Date_Time_Utils::getTimeFormat();
        $date_format = WBK_Date_Time_Utils::getDateFormat();
        
        if ( isset( $_POST['offset'] ) ) {
            $offset = $_POST['offset'];
        } else {
            $offset = '';
        }
        
        if ( !is_numeric( $offset ) ) {
            $offset = 0;
        }
        $time_zone_client = '';
        if ( isset( $_POST['time_zone_client'] ) ) {
            $time_zone_client = $_POST['time_zone_client'];
        }
        
        if ( $time_zone_client != '' ) {
            $this_tz = new DateTimeZone( $time_zone_client );
            $date = ( new DateTime( '@' . $day ) )->setTimezone( new DateTimeZone( $time_zone_client ) );
            $offset = $this_tz->getOffset( $date );
            $offset = $offset * -1 / 60;
        }
        
        $this->day = $day;
        $this->breakers = [];
        if ( !isset( $this->gg_breakers ) ) {
            $this->gg_breakers = [];
        }
        if ( !isset( $this->ext_breakers ) ) {
            $this->ext_breakers = [];
        }
        // load appointments
        
        if ( get_option( 'wbk_allow_cross_midnight', '' ) == 'true' ) {
            $start_all = WBK_Time_Math_Utils::adjust_times( $day, $service->get_duration() * -60, get_option( 'wbk_timezone', 'UTC' ) );
            $end_all = WBK_Time_Math_Utils::adjust_times( $day, 86400 + $service->get_duration() * 60, get_option( 'wbk_timezone', 'UTC' ) );
        } else {
            $start_all = $day;
            $end_all = WBK_Time_Math_Utils::adjust_times( $day, 86400, get_option( 'wbk_timezone', 'UTC' ) );
        }
        
        $day_end = WBK_Time_Math_Utils::adjust_times( $day, 86400, get_option( 'wbk_timezone', 'UTC' ) );
        $this->load_appointments_in_range( $start_all, $end_all, $service_id );
        // load data from google caelndar
        if ( !$skip_gg_calendar ) {
        }
        $betw_interval = $service->get_interval_between() * 60;
        $duration = $service->get_duration() * 60;
        $step = $service->get_step() * 60;
        $total_duration_optim = $betw_interval + $duration;
        
        if ( $ignore_preparation ) {
            $preparation_time = 0;
        } else {
            $preparation_time = $service->get_prepare_time();
        }
        
        $intervals = $this->get_business_hours_intervals_by_dow( date( 'N', $day ), $service_id );
        // special business houts
        $data = trim( get_option( 'wbk_appointments_special_hours', '' ) );
        $intervals_overriden = array();
        
        if ( $data != '' ) {
            $data = explode( PHP_EOL, $data );
            foreach ( $data as $line ) {
                $parts = explode( ' ', $line );
                if ( count( $parts ) != 2 && count( $parts ) != 3 ) {
                    continue;
                }
                
                if ( count( $parts ) == 3 ) {
                    if ( $service_id != $parts[0] ) {
                        continue;
                    }
                } else {
                    array_unshift( $parts, 'x' );
                }
                
                $date = strtotime( $parts[1] );
                
                if ( $date == $day ) {
                    $intervals_this = explode( ',', $parts[2] );
                    foreach ( $intervals_this as $interval ) {
                        $times = explode( '-', $interval );
                        $time = $times[0];
                        $splitted_time = explode( ':', $time );
                        $seconds = $splitted_time[0] * 60 * 60 + $splitted_time[1] * 60;
                        $start = $seconds;
                        $time = $times[1];
                        $splitted_time = explode( ':', $time );
                        $seconds = $splitted_time[0] * 60 * 60 + $splitted_time[1] * 60;
                        $end = $seconds;
                        $interval_data = new stdClass();
                        $interval_data->start = $start;
                        $interval_data->end = $end;
                        $interval_data->day_of_week = date( 'N', $day );
                        $interval_data->status = 'active';
                        $intervals_overriden[] = $interval_data;
                    }
                }
            
            }
        }
        
        if ( count( $intervals_overriden ) > 0 ) {
            $intervals = $intervals_overriden;
        }
        // special business hours end
        $timeslots = [];
        foreach ( $intervals as $interval ) {
            if ( $working && $interval->status != 'active' ) {
                continue;
            }
            $start = WBK_Time_Math_Utils::adjust_times( $day, $interval->start, get_option( 'wbk_timezone', 'UTC' ) );
            $end = WBK_Time_Math_Utils::adjust_times( $day, $interval->end, get_option( 'wbk_timezone', 'UTC' ) );
            //for ($time = $start; $time < $end; $time += $step  ) {
            for ( $time = $start ;  $time < $end ;  $time = WBK_Time_Math_Utils::adjust_times( $time, $step, get_option( 'wbk_timezone', 'UTC' ) ) ) {
                if ( get_option( 'wbk_allow_ongoing_time_slot', 'disallow' ) == 'disallow' ) {
                    if ( $time < time() + $preparation_time * 60 ) {
                        if ( !$ignore_preparation ) {
                            continue;
                        }
                    }
                }
                $total_duration = $duration + $betw_interval;
                $temp = WBK_Time_Math_Utils::adjust_times( $time, $total_duration, get_option( 'wbk_timezone', 'UTC' ) );
                $allow_cross = false;
                if ( get_option( 'wbk_allow_cross_midnight', '' ) == 'true' ) {
                    if ( date( 'N', $time ) != date( 'N', $temp ) ) {
                        $allow_cross = true;
                    }
                }
                if ( $temp > $end && !$allow_cross ) {
                    continue;
                }
                $status = $this->get_time_slot_status( $time, $total_duration, $service );
                if ( $status == -1 ) {
                    continue;
                }
                $slot = new WBK_Time_Slot( $time, $temp );
                $slot->setStatus( $status );
                $timeslots[] = $slot;
            }
        }
        // check for not attached appointments
        $need_sort = false;
        foreach ( $this->appointments as $appointment ) {
            $appointment_found = false;
            foreach ( $timeslots as $timeslot ) {
                if ( $appointment->getTime() == $timeslot->getStart() ) {
                    $appointment_found = true;
                }
            }
            
            if ( !$appointment_found ) {
                if ( $appointment->getTime() < $day || $appointment->getTime() > $day_end ) {
                    continue;
                }
                $temp = $appointment->getTime() + $duration + $betw_interval;
                $slot = new WBK_Time_Slot( $appointment->getTime(), $temp );
                $slot->setStatus( $appointment->getId() );
                array_push( $timeslots, $slot );
                $need_sort = true;
            }
        
        }
        
        if ( $need_sort ) {
            $arr_temp = [];
            foreach ( $timeslots as $timeslot ) {
                array_push( $arr_temp, $timeslot->getStart() );
            }
            array_multisort( $timeslots, $arr_temp );
        }
        
        $connected_service_ids = array();
        if ( get_option( 'wbk_appointments_auto_lock', 'disabled' ) == 'enabled' ) {
            
            if ( get_option( 'wbk_appointments_auto_lock_mode', 'all' ) == 'all' ) {
                foreach ( WBK_Model_Utils::get_service_ids() as $id ) {
                    if ( $id != $service_id ) {
                        $connected_service_ids[] = $id;
                    }
                }
            } else {
                $connected_service_ids = WBK_Model_Utils::get_services_with_same_category( $service_id );
            }
        
        }
        $connected_service_ids = apply_filters( 'webba_connected_services', $connected_service_ids, $service_id );
        // clarifying count of available places
        // and updating status if needed
        for ( $i = 0 ;  $i < count( $timeslots ) ;  $i++ ) {
            if ( $calculate_availability ) {
                
                if ( $timeslots[$i]->getStatus() == 0 || is_array( $timeslots[$i]->getStatus() ) ) {
                    $booked_count = 0;
                    // places in current booking
                    if ( is_array( $timeslots[$i]->getStatus() ) ) {
                        foreach ( $timeslots[$i]->getStatus() as $booking_id ) {
                            $booking = new WBK_Booking( $booking_id );
                            $booked_count += $booking->get_quantity();
                        }
                    }
                    $partial_check = true;
                    
                    if ( is_array( $timeslots[$i]->getStatus() ) ) {
                        $parital_mode = get_option( 'wbk_appointments_lock_timeslot_if_parital_booked', '' );
                        if ( $parital_mode == '' ) {
                            $parital_mode = array();
                        }
                        if ( in_array( $service_id, $parital_mode ) ) {
                            $partial_check = false;
                        }
                    }
                    
                    // places in connected services
                    $connected_quantity = 0;
                    
                    if ( count( $connected_service_ids ) > 0 ) {
                        $booking_ids = array();
                        foreach ( $connected_service_ids as $connected_service_id ) {
                            $temp = WBK_Model_Utils::get_booking_ids_by_range_service( $start_all, $end_all, $connected_service_id );
                            $booking_ids = array_merge( $booking_ids, $temp );
                        }
                        $booking_ids = array_unique( $booking_ids );
                        foreach ( $booking_ids as $booking_id ) {
                            $booking = new WBK_Booking( $booking_id );
                            if ( WBK_Time_Math_Utils::check_range_intersect(
                                $timeslots[$i]->getStart(),
                                $timeslots[$i]->getEnd(),
                                $booking->get_start(),
                                $booking->get_end()
                            ) ) {
                                $connected_quantity += $booking->get_quantity();
                            }
                        }
                    }
                    
                    // check intersection with time slots of the same service
                    $same_service_quantity = 0;
                    
                    if ( get_option( 'wbk_mode_overlapping_availabiliy', 'true' ) == 'true' ) {
                        $booking_ids = WBK_Model_Utils::get_booking_ids_by_day_service( $day, $service_id );
                        foreach ( $booking_ids as $booking_id ) {
                            $booking = new WBK_Booking( $booking_id );
                            if ( $timeslots[$i]->getStart() != $booking->get_start() ) {
                                if ( WBK_Time_Math_Utils::check_range_intersect(
                                    $timeslots[$i]->getStart(),
                                    $timeslots[$i]->getEnd(),
                                    $booking->get_start(),
                                    $booking->get_end()
                                ) ) {
                                    $same_service_quantity += $booking->get_quantity();
                                }
                            }
                        }
                    }
                    
                    $gg_count = 0;
                    $ext_count = 0;
                    $available = $service->get_quantity( $timeslots[$i]->getStart() ) - $booked_count - $connected_quantity - $same_service_quantity - $gg_count - $ext_count;
                    
                    if ( $available <= 0 ) {
                        $timeslots[$i]->set_free_places( 0 );
                        if ( get_option( 'wbk_allow_cross_midnight', '' ) == 'true' ) {
                            $timeslots[$i]->setStatus( -2 );
                        }
                    } else {
                        
                        if ( $partial_check ) {
                            $timeslots[$i]->set_free_places( $available );
                        } else {
                            $timeslots[$i]->set_free_places( 0 );
                        }
                    
                    }
                
                }
            
            }
            // set formated time strings
            //if ($i == 0) {
            $timezone_to_use = new DateTimeZone( date_default_timezone_get() );
            $this_tz = new DateTimeZone( date_default_timezone_get() );
            $date = ( new DateTime( '@' . $timeslots[$i]->getStart() ) )->setTimezone( new DateTimeZone( date_default_timezone_get() ) );
            $date_end = ( new DateTime( '@' . $timeslots[$i]->getEnd() ) )->setTimezone( new DateTimeZone( date_default_timezone_get() ) );
            $now = new DateTime( 'now', $this_tz );
            $offset_sign = $this_tz->getOffset( $date );
            
            if ( $offset_sign > 0 ) {
                $sign = '+';
            } else {
                $sign = '-';
            }
            
            $offset_rounded = abs( $offset_sign / 3600 );
            $offset_int = floor( $offset_rounded );
            
            if ( $offset_rounded - $offset_int == 0.5 ) {
                $offset_fractional = ':30';
            } else {
                $offset_fractional = '';
            }
            
            $timezone_utc_string = $sign . $offset_int . $offset_fractional;
            $offset_sign = $this_tz->getOffset( $date_end );
            
            if ( $offset_sign > 0 ) {
                $sign = '+';
            } else {
                $sign = '-';
            }
            
            $offset_rounded = abs( $offset_sign / 3600 );
            $offset_int = floor( $offset_rounded );
            
            if ( $offset_rounded - $offset_int == 0.5 ) {
                $offset_fractional = ':30';
            } else {
                $offset_fractional = '';
            }
            
            $timezone_utc_string_end = $sign . $offset_int . $offset_fractional;
            $timezone_to_use = new DateTimeZone( $timezone_utc_string );
            $timezone_to_use_end = new DateTimeZone( $timezone_utc_string_end );
            $timeslot_time_string = get_option( 'wbk_timeslot_time_string', 'start' );
            
            if ( $timeslot_time_string == 'start' ) {
                $time = wp_date( $time_format, $timeslots[$i]->getStart(), $timezone_to_use );
                
                if ( get_option( 'wbk_show_local_time', 'disabled' ) == 'enabled' || get_option( 'wbk_show_local_time', 'disabled' ) == 'enabled_only' ) {
                    $timezone = $timezone_to_use;
                    $current_offset = $offset * -60 - $timezone->getOffset( new DateTime() );
                    $local_start = $timeslots[$i]->getStart() + $current_offset;
                    $local_start = wp_date( $time_format, $local_start, $timezone_to_use );
                    $local_start_date = $timeslots[$i]->getStart() + $current_offset;
                    $local_start_date = wp_date( $date_format, $local_start_date, $timezone_to_use );
                    $local_time_str = get_option( 'wbk_local_time_format', 'Your local time:<br>#ds<br>#ts' );
                    $local_time_str = str_replace( '#ts', $local_start, $local_time_str );
                    $local_time_str = str_replace( '#ds', $local_start_date, $local_time_str );
                } else {
                    $local_time_str = '';
                }
            
            }
            
            $end_minus_gap = $timeslots[$i]->getEnd() - $service->get_interval_between() * 60;
            
            if ( $timeslot_time_string == 'start_end' ) {
                $time = wp_date( $time_format, $timeslots[$i]->getStart(), $timezone_to_use ) . ' - ' . wp_date( $time_format, $end_minus_gap, $timezone_to_use_end );
                
                if ( get_option( 'wbk_show_local_time', 'disabled' ) == 'enabled' || get_option( 'wbk_show_local_time', 'disabled' ) == 'enabled_only' ) {
                    $timezone = new DateTimeZone( get_option( 'wbk_timezone', 'UTC' ) );
                    $current_offset = $offset * -60 - $timezone->getOffset( new DateTime() );
                    $local_start = $timeslots[$i]->getStart() + $current_offset;
                    $local_start = wp_date( $time_format, $local_start, $timezone_to_use );
                    $local_end = $timeslots[$i]->getStart() + $service->get_duration() * 60 + $current_offset;
                    $local_end = wp_date( $time_format, $local_end, $timezone_to_use_end );
                    $local_start_date = $timeslots[$i]->getStart() + $current_offset;
                    $local_start_date = wp_date( $date_format, $local_start_date, $timezone_to_use );
                    $local_time_str = get_option( 'wbk_local_time_format', 'Your local time:<br>#ds<br>#ts - #te' );
                    $local_time_str = str_replace( '#ts', $local_start, $local_time_str );
                    $local_time_str = str_replace( '#te', $local_end, $local_time_str );
                    $local_time_str = str_replace( '#ds', $local_start_date, $local_time_str );
                } else {
                    $local_time_str = '';
                }
            
            }
            
            $timeslots[$i]->set_formated_time( $time );
            $timeslots[$i]->set_formated_time_local( $local_time_str );
            $timeslot_time_string_backend = get_option( 'wbk_date_format_time_slot_schedule', 'start' );
            
            if ( $timeslot_time_string_backend == 'start' ) {
                $time = wp_date( $time_format, $timeslots[$i]->getStart(), $timezone_to_use );
            } else {
                $time = wp_date( $time_format, $timeslots[$i]->getStart(), $timezone_to_use ) . ' - ' . wp_date( $time_format, $end_minus_gap, $timezone_to_use );
            }
            
            $timeslots[$i]->set_formated_time_backend( $time );
        }
        $this->timeslots = $timeslots;
        return $timeslots;
    }
    
    // get day status working / weekend
    // 1 - working, 0 - weekend, 2 - limit reached
    public function get_day_status( $day, $service_id )
    {
        // check Lock day if at least one time slot is booked
        $whole_day_checkin = get_option( 'wbk_appointments_lock_day_if_timeslot_booked', '' );
        if ( is_array( $whole_day_checkin ) ) {
            
            if ( in_array( $service_id, $whole_day_checkin, true ) ) {
                $this_day_bookings = WBK_Model_Utils::get_booking_ids_by_day_service( $day, $this->service_id );
                if ( count( $this_day_bookings ) > 0 ) {
                    return 0;
                }
            }
        
        }
        // check overal daily limit
        $day_limit = trim( get_option( 'wbk_appointments_limit_by_day', '' ) );
        if ( $day_limit != '' ) {
            if ( WBK_Db_Utils::getCountOfAppointmentsByDay( $day ) >= $day_limit ) {
                return 2;
            }
        }
        // check locked / unlocked arrays
        
        if ( isset( $this->locked_days[$service_id] ) && is_array( $this->locked_days[$service_id] ) ) {
            $locked_days = $this->locked_days[$service_id];
        } else {
            $locked_days = [];
        }
        
        if ( in_array( $day, $locked_days, true ) === true ) {
            return 0;
        }
        
        if ( isset( $this->unlocked_days[$service_id] ) && is_array( $this->unlocked_days[$service_id] ) ) {
            $unlocked_days = $this->unlocked_days[$service_id];
        } else {
            $unlocked_days = [];
        }
        
        if ( in_array( $day, $unlocked_days, true ) === true ) {
            return 1;
        }
        if ( $this->is_holyday( $day ) === true ) {
            return 0;
        }
        // check service business hours
        //
        
        if ( $this->is_working_day( $day, $service_id ) === true ) {
            return 1;
        } else {
            return 0;
        }
    
    }
    
    public function is_holyday( $day )
    {
        $holydays = get_option( 'wbk_holydays' );
        $arr = explode( ',', $holydays );
        foreach ( $arr as $item ) {
            $holyday = strtotime( $item );
            if ( $holyday == $day ) {
                return apply_filters( 'wbk_check_holiday', true );
            }
            
            if ( get_option( 'wbk_recurring_holidays', '' ) == 'true' ) {
                $holyday_m = date( 'm', $holyday );
                $holyday_d = date( 'd', $holyday );
                $holyday_y = date( 'y', $holyday );
                $current_y = date( 'y', $day );
                $holyday = strtotime( $holyday_m . '/' . $holyday_d . '/' . $current_y );
                if ( $holyday == $day ) {
                    return apply_filters( 'wbk_check_holiday', true );
                }
            }
        
        }
        return apply_filters( 'wbk_check_holiday', false );
    }
    
    public function load_gg_events_in_range( $start, $end, $service )
    {
        $event_data_arr = [];
        return $event_data_arr;
    }
    
    public function load_appointments_in_range( $start, $end, $service_id )
    {
        global  $wpdb ;
        $db_arr = $wpdb->get_results( $wpdb->prepare(
            "\n\t\t\t\t\t\t\t\t\t\t\t\t\tSELECT *\n\t\t\t\t\t\t\t\t\t\t\t\t\tFROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments\n\t\t\t\t\t\t\t\t\t\t\t\t\twhere service_id = %d AND time >= %d  AND time < %d\n\t\t\t\t\t\t\t\t\t\t\t\t\t",
            $service_id,
            $start,
            $end
        ) );
        $this->appointments = [];
        if ( count( $db_arr ) == 0 ) {
            return 0;
        }
        foreach ( $db_arr as $item ) {
            $appointment = new WBK_Appointment_deprecated();
            
            if ( $appointment->set(
                $item->id,
                $item->name,
                $item->description,
                $item->email,
                $item->duration,
                $item->time,
                $item->day,
                $item->phone,
                $item->extra,
                $item->attachment,
                $item->quantity
            ) ) {
                array_push( $this->appointments, $appointment );
                // create breaker
                $service = new WBK_Service_deprecated();
                if ( !$service->setId( $service_id ) ) {
                    continue;
                }
                if ( !$service->load() ) {
                    continue;
                }
                
                if ( $service->getQuantity() == 1 ) {
                    $betw_interval = $service->getInterval();
                    $app_end = $item->time + $item->duration * 60 + $betw_interval * 60;
                    $breaker = new WBK_Time_Slot( $item->time, $app_end );
                    array_push( $this->breakers, $breaker );
                }
            
            }
        
        }
        return;
    }
    
    public function is_working_day( $day, $service_id )
    {
        $service = new WBK_Service( $service_id );
        $busines_hours = json_decode( $service->get_business_hours() );
        if ( !is_object( $busines_hours ) ) {
            return false;
        }
        foreach ( $busines_hours->dow_availability as $item ) {
            
            if ( $day >= 1 && $day <= 7 ) {
                if ( $day == $item->day_of_week && $item->status == 'active' ) {
                    return true;
                }
            } else {
                if ( date( 'N', $day ) == $item->day_of_week && $item->status == 'active' ) {
                    return true;
                }
            }
        
        }
        return false;
    }
    
    public function is_unlockced_has_dow( $day, $service_id )
    {
        if ( isset( $this->unlocked_days[$service_id] ) ) {
            foreach ( $this->unlocked_days[$service_id] as $day_current ) {
                if ( date( 'N', $day_current ) == $day ) {
                    return true;
                }
            }
        }
        return false;
    }
    
    public function get_business_hours_intervals_by_dow( $dow, $service_id )
    {
        $service = new WBK_Service( $service_id );
        $busines_hours = json_decode( $service->get_business_hours() );
        if ( !is_object( $busines_hours ) ) {
            return [];
        }
        $slots = [];
        $sort_array = [];
        foreach ( $busines_hours->dow_availability as $item ) {
            if ( $dow == $item->day_of_week ) {
                $slots[] = $item;
            }
        }
        usort( $slots, function ( $a, $b ) {
            return (int) ($a->start > $b->start);
        } );
        return $slots;
    }
    
    // get timeslot status. 0 - free timeslot
    public function get_time_slot_status( $time, $duration, $service )
    {
        $start = $time;
        $end = $time + $duration;
        // check breakers
        foreach ( $this->breakers as $breaker ) {
            if ( $start > $breaker->getStart() && $start < $breaker->getEnd() ) {
                return -1;
            }
            if ( $end > $breaker->getStart() && $end < $breaker->getEnd() ) {
                return -1;
            }
        }
        // check locked timeslots
        
        if ( isset( $this->locked_time_slots[$service->get_id()] ) && is_array( $this->locked_time_slots[$service->get_id()] ) ) {
            $locked_timeslots = $this->locked_time_slots[$service->get_id()];
        } else {
            $locked_timeslots = [];
        }
        
        if ( in_array( $start, $locked_timeslots ) ) {
            return -2;
        }
        // check appointments
        
        if ( $service->get_quantity() == 1 ) {
            foreach ( $this->appointments as $appointment ) {
                if ( $time == $appointment->getTime() ) {
                    return $appointment->getId();
                }
            }
        } else {
            $booking_ids = [];
            foreach ( $this->appointments as $appointment ) {
                if ( $time == $appointment->getTime() ) {
                    array_push( $booking_ids, $appointment->getId() );
                }
            }
            if ( count( $booking_ids ) > 0 ) {
                return $booking_ids;
            }
        }
        
        return 0;
    }
    
    public function get_unlocked_days()
    {
        return $this->unlocked_days;
    }
    
    public function set_gg_breakers( $input )
    {
        $this->gg_breakers = $input;
    }
    
    public function get_available_count( $time )
    {
        foreach ( $this->timeslots as $timeslot ) {
            if ( $timeslot->getStart() == $time ) {
                return $timeslot->get_free_places();
            }
        }
        return 0;
    }

}