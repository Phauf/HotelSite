<?php

// Webba Booking service schedule management class
// check if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
require_once 'class_wbk_business_hours.php';
require_once 'class_wbk_appointment_deprecated.php';
require_once 'class_wbk_service_deprecated.php';
require_once 'class_wbk_time_slot.php';
require_once 'class_wbk_date_time_utils.php';
class WBK_Service_Schedule
{
    // service id
    protected  $service_id ;
    // service
    protected  $service ;
    // result time slots for day
    protected  $timeslots ;
    // time slots locked manualy
    protected  $locked_ts ;
    // days locked manualy
    protected  $locked_days ;
    // days unlocked manualy
    protected  $unlocked_days ;
    // locked timeslots
    protected  $locked_timeslots ;
    // list of appointments for day
    protected  $appointments ;
    // business hours global option
    protected  $busines_hours ;
    // breakers (appointments, day break)
    protected  $breakers ;
    // Google calendar breakers (event imported from google)
    protected  $gg_breakers ;
    // load custom locks / unlocks
    public function load()
    {
        // load locked and unlocked days
        $this->loadLockedDays();
        $this->loadUnlockedDays();
        // load locked timeslots
        $this->loadLockedTimeSlots();
        // initalize service object
        $this->service = new WBK_Service_deprecated();
        
        if ( $this->service->setId( $this->service_id ) ) {
            if ( !$this->service->load() ) {
                return false;
            }
        } else {
            return false;
        }
        
        $this->busines_hours = new WBK_Business_Hours();
        $this->busines_hours->load( $this->service->getBusinessHours() );
        return true;
    }
    
    // set service id
    public function setServiceId( $value )
    {
        
        if ( WBK_Validator::checkInteger( $value, 1, 99999 ) ) {
            $this->service_id = $value;
            return true;
        } else {
            return false;
        }
    
    }
    
    // full schedule for day
    public function buildSchedule(
        $day,
        $ignore_optimization = false,
        $skip_gg_calendar = false,
        $ignore_preparation = false,
        $calculate_availability = false
    )
    {
        $sp = new WBK_Schedule_Processor();
        $this->day = $day;
        if ( !is_null( $this->gg_breakers ) ) {
            $sp->set_gg_breakers( $this->gg_breakers );
        }
        $this->timeslots = $sp->get_time_slots_by_day(
            $day,
            $this->service_id,
            $skip_gg_calendar,
            $ignore_preparation,
            $calculate_availability
        );
        return;
    }
    
    // render select options for free appointments including given appointment_id
    // -1 means appointment not provided
    // REMOVE IN FUTURE RELEASE
    public function renderSelectOptionsFreeTimslot( $appointment_id )
    {
        $time_format = WBK_Date_Time_Utils::getTimeFormat();
        $html = '';
        foreach ( $this->timeslots as $timeslot ) {
            $time = wp_date( $time_format, $timeslot->getStart(), new DateTimeZone( date_default_timezone_get() ) );
            // group booking
            
            if ( is_array( $timeslot->getStatus() ) || $timeslot->getStatus() == 0 && $this->service->getQuantity( $timeslot->getStart() ) > 1 ) {
                $available = $this->getAvailableCount( $timeslot->getStart() );
                
                if ( $available > 0 || in_array( $appointment_id, $timeslot->getStatus() ) ) {
                    $selected = '';
                    
                    if ( is_array( $timeslot->getStatus() ) && in_array( $appointment_id, $timeslot->getStatus() ) ) {
                        $selected = 'selected';
                        $appointment = new WBK_Appointment_deprecated();
                        if ( !$appointment->setId( $appointment_id ) ) {
                            continue;
                        }
                        if ( !$appointment->load() ) {
                            continue;
                        }
                        $available = $available . '+' . $appointment->getQuantity();
                    }
                    
                    $available_lablel = get_option( 'wbk_time_slot_available_text', __( 'available', 'wbk' ) );
                    
                    if ( $available_lablel == '' ) {
                        global  $wbk_wording ;
                        $available_lablel = $wbk_wording['wbk_time_slot_available_text'];
                    }
                    
                    $html .= '<option ' . $selected . ' value="' . $timeslot->getStart() . '">' . $time . ' (' . $available . ' ' . $available_lablel . ' ' . ')</option>';
                }
            
            }
            
            
            if ( $timeslot->getStatus() == $appointment_id || $timeslot->getStatus() == 0 && $this->service->getQuantity( $timeslot->getStart() ) == 1 ) {
                $selected = '';
                if ( $timeslot->getStatus() == $appointment_id ) {
                    $selected = 'selected';
                }
                $html .= '<option ' . $selected . '  value="' . $timeslot->getStart() . '">' . $time . '</option>';
            }
        
        }
        return $html;
    }
    
    // get array of free time slots
    public function getFreeTimeslotsPlusGivenAppointment( $appointment_id, $ignore_parial = false )
    {
        $time_format = WBK_Date_Time_Utils::getTimeFormat();
        $result = array();
        $result[] = array( __( 'Select time slot', 'wbk' ), 0 );
        foreach ( $this->timeslots as $timeslot ) {
            
            if ( get_option( 'wbk_date_format_time_slot_schedule', 'start' ) == 'start' ) {
                $time = date( $time_format, $timeslot->getStart() );
            } else {
                $time = date( $time_format, $timeslot->getStart() ) . ' - ' . date( $time_format, $timeslot->getStart() + $this->service->getDuration() * 60 );
            }
            
            // group booking
            
            if ( is_array( $timeslot->getStatus() ) || $timeslot->getStatus() == 0 && $this->service->getQuantity( $timeslot->getStart() ) > 1 ) {
                $available = $this->getAvailableCount( $timeslot->getStart(), $ignore_parial );
                
                if ( $available > 0 || in_array( $appointment_id, $timeslot->getStatus() ) ) {
                    if ( is_array( $timeslot->getStatus() ) ) {
                        
                        if ( in_array( $appointment_id, $timeslot->getStatus() ) ) {
                            $appointment = new WBK_Appointment_deprecated();
                            if ( !$appointment->setId( $appointment_id ) ) {
                                continue;
                            }
                            if ( !$appointment->load() ) {
                                continue;
                            }
                            $available = $available + $appointment->getQuantity();
                        }
                    
                    }
                    $available_lablel = get_option( 'wbk_time_slot_available_text', __( 'available', 'wbk' ) );
                    
                    if ( $available_lablel == '' ) {
                        global  $wbk_wording ;
                        $available_lablel = $wbk_wording['wbk_time_slot_available_text'];
                    }
                    
                    $opt_name = $time . ' (' . $available . ' ' . $available_lablel . ')';
                    $result[$timeslot->getStart()] = array( $opt_name, $available );
                }
            
            }
            
            if ( $timeslot->getStatus() == $appointment_id || $timeslot->getStatus() == 0 && $this->service->getQuantity( $timeslot->getStart() ) == 1 ) {
                $result[$timeslot->getStart()] = array( $time, 1 );
            }
        }
        return $result;
    }
    
    // render shcedule for day for backend
    public function renderDayBackend()
    {
        $html = '';
        $time_format = WBK_Date_Time_Utils::getTimeFormat();
        foreach ( $this->timeslots as $timeslot ) {
            if ( get_option( 'wbk_appointments_auto_lock', 'disabled' ) == 'enabled' ) {
                
                if ( get_option( 'wbk_appointments_auto_lock_allow_unlock', 'allow' ) == 'disallow' ) {
                    $connected_quantity = WBK_Db_Utils::getQuantityFromConnectedServices2( $this->service->getId(), $timeslot->getStart(), true );
                    if ( $connected_quantity > 0 ) {
                        
                        if ( $this->service->getQuantity( $timeslot->getStart() ) == 1 ) {
                            $timeslot->setStatus( -2 );
                        } else {
                            if ( get_option( 'wbk_appointments_auto_lock_group', 'lock' ) == 'lock' ) {
                                $timeslot->setStatus( -2 );
                            }
                        }
                    
                    }
                }
            
            }
            $time = $timeslot->get_formated_time_backend();
            $status_class = '';
            $time_controls = '<a id="time_lock_' . $this->service_id . '_' . $timeslot->getStart() . '"><span class="dashicons dashicons-unlock"></span></a>';
            $time_controls = '<a id="app_add_' . $this->service_id . '_' . $timeslot->getStart() . '"><span class="dashicons dashicons-welcome-add-page"></span></a>' . $time_controls;
            
            if ( is_array( $timeslot->getStatus() ) ) {
                $time_controls = '';
                $items_booked = 0;
                foreach ( $timeslot->getStatus() as $app_id ) {
                    $appointment = new WBK_Appointment_deprecated();
                    if ( !$appointment->setId( $app_id ) ) {
                        continue;
                    }
                    if ( !$appointment->load() ) {
                        continue;
                    }
                    $items_booked += $appointment->getQuantity();
                    $time_controls .= '<a class="wbk-appointment-backend" id="wbk_appointment_' . $app_id . '_' . $this->service_id . '_1" >' . $appointment->getName() . ' (' . $appointment->getQuantity() . ')' . '</a> ';
                }
                if ( $items_booked < $this->service->getQuantity( $timeslot->getStart() ) ) {
                    $time_controls .= '<a id="app_add_' . $this->service_id . '_' . $timeslot->getStart() . '"><span class="dashicons dashicons-welcome-add-page"></span></a>';
                }
                
                if ( in_array( $timeslot->getStart(), $this->locked_timeslots ) ) {
                    $status_class = 'red_font';
                    $time_controls .= '<a class="red_font" id="time_unlock_' . $this->service_id . '_' . $timeslot->getStart() . '"><span class="dashicons dashicons-lock"></span></a></a>';
                } else {
                    $time_controls .= '<a id="time_lock_' . $this->service_id . '_' . $timeslot->getStart() . '"><span class="dashicons dashicons-unlock"></span></a>';
                }
            
            }
            
            
            if ( $timeslot->getStatus() == -2 || in_array( $timeslot->getStart(), $this->locked_timeslots ) && !is_array( $timeslot->getStatus() ) ) {
                $status_class = 'red_font';
                $time_controls = '<a class="red_font" id="time_unlock_' . $this->service_id . '_' . $timeslot->getStart() . '"><span class="dashicons dashicons-lock"></span></a></a>';
            }
            
            
            if ( $timeslot->getStatus() > 0 && !is_array( $timeslot->getStatus() ) ) {
                $app_ids = WBK_Db_Utils::getAppointmentsByServiceAndTime( $this->service_id, $timeslot->getStart() );
                $time_controls = '';
                foreach ( $app_ids as $app_id ) {
                    $appointment = new WBK_Appointment_deprecated();
                    if ( !$appointment->setId( $app_id ) ) {
                        continue;
                    }
                    if ( !$appointment->load() ) {
                        continue;
                    }
                    $name = $appointment->getName();
                    $name = WBK_Db_Utils::backend_customer_name_processing( $appointment->getId(), $name );
                    $time_controls .= '<a class="wbk-appointment-backend" id="wbk_appointment_' . $appointment->getId() . '_' . $this->service_id . '_1" >' . $name . '</a>';
                }
            }
            
            $html .= '<div class="timeslot_container">
						<div class="timeslot_time ' . $status_class . '">' . $time . '</div>
						<div class="timeslot_controls">' . $time_controls . '
						</div>
						<div class="cb"></div>
					  </div>';
        }
        return $html;
    }
    
    // render for past day for backend
    public function renderPastDayBackend()
    {
        $html = '';
        $time_format = WBK_Date_Time_Utils::getTimeFormat();
        foreach ( $this->timeslots as $timeslot ) {
            if ( $timeslot->getStatus() == 0 && !is_array( $timeslot->getStatus() ) ) {
                continue;
            }
            $time = $timeslot->get_formated_time_backend();
            if ( get_option( 'wbk_appointments_auto_lock', 'disabled' ) == 'enabled' ) {
                
                if ( get_option( 'wbk_appointments_auto_lock_allow_unlock', 'allow' ) == 'disallow' ) {
                    $connected_quantity = WBK_Db_Utils::getQuantityFromConnectedServices2( $this->service->getId(), $timeslot->getStart(), true );
                    if ( $connected_quantity > 0 ) {
                        
                        if ( $this->service->getQuantity( $timeslot->getStart() ) == 1 ) {
                            $timeslot->setStatus( -2 );
                        } else {
                            if ( get_option( 'wbk_appointments_auto_lock_group', 'lock' ) == 'lock' ) {
                                $timeslot->setStatus( -2 );
                            }
                        }
                    
                    }
                }
            
            }
            
            if ( get_option( 'wbk_date_format_time_slot_schedule', 'start' ) == 'start' ) {
                $time = date( $time_format, $timeslot->getStart() );
            } else {
                $time = date( $time_format, $timeslot->getStart() ) . ' - ' . date( $time_format, $timeslot->getStart() + $this->service->getDuration() * 60 );
            }
            
            $status_class = '';
            $time_controls = '';
            
            if ( is_array( $timeslot->getStatus() ) ) {
                $items_booked = 0;
                foreach ( $timeslot->getStatus() as $app_id ) {
                    $appointment = new WBK_Appointment_deprecated();
                    if ( !$appointment->setId( $app_id ) ) {
                        continue;
                    }
                    if ( !$appointment->load() ) {
                        continue;
                    }
                    $items_booked += $appointment->getQuantity();
                    $time_controls .= '<a class="wbk-appointment-backend" id="wbk_appointment_' . $app_id . '_' . $this->service_id . '_1" >' . $appointment->getName() . ' (' . $appointment->getQuantity() . ')' . '</a> ';
                }
            }
            
            if ( $timeslot->getStatus() == -2 ) {
                $status_class = 'red_font';
            }
            
            if ( !is_array( $timeslot->getStatus() ) && $timeslot->getStatus() > 0 ) {
                $app_ids = WBK_Db_Utils::getAppointmentsByServiceAndTime( $this->service_id, $timeslot->getStart() );
                $time_controls = '';
                foreach ( $app_ids as $app_id ) {
                    $appointment = new WBK_Appointment_deprecated();
                    if ( !$appointment->setId( $app_id ) ) {
                        continue;
                    }
                    if ( !$appointment->load() ) {
                        continue;
                    }
                    $name = $appointment->getName();
                    $name = WBK_Db_Utils::backend_customer_name_processing( $appointment->getId(), $name );
                    $time_controls .= '<a class="wbk-appointment-backend" id="wbk_appointment_' . $appointment->getId() . '_' . $this->service_id . '_1" >' . $name . '</a>';
                }
            }
            
            if ( !isset( $time_controls ) ) {
                $time_controls = '';
            }
            $html .= '<div class="timeslot_container">
						<div class="timeslot_time ' . $status_class . '">' . $time . '</div>
						<div class="timeslot_controls">' . $time_controls . '
						</div>
						<div class="cb"></div>
					  </div>';
        }
        return $html;
    }
    
    // get timeslot status. 0 - free timeslot
    public function timeSlotStatus( $time, $duration )
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
        if ( in_array( $start, $this->locked_timeslots ) ) {
            return -2;
        }
        // check appointments
        
        if ( $this->service->getQuantity( $time ) == 1 ) {
            foreach ( $this->appointments as $appointment ) {
                if ( $time == $appointment->getTime() ) {
                    return $appointment->getId();
                }
            }
        } else {
            $booking_ids = array();
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
    
    // load locked days for service
    public function loadLockedDays()
    {
        global  $wpdb ;
        $days = $wpdb->get_col( $wpdb->prepare( "\n\t\t\t\t\t\tSELECT day\n\t\t\t\t\t\tFROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_days_on_off\n\t\t\t\t\t\twhere service_id = %d AND status = 0\n\t\t\t\t\t\t", $this->service_id ) );
        $this->locked_days = array();
        $this->locked_days = array_merge( $this->locked_days, $days );
    }
    
    // load unlocked days for service
    public function loadUnlockedDays()
    {
        global  $wpdb ;
        $days = $wpdb->get_col( $wpdb->prepare( "\n\t\t\t\t\t\tSELECT day\n\t\t\t\t\t\tFROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_days_on_off\n\t\t\t\t\t\twhere service_id = %d AND status = 1\n\t\t\t\t\t\t", $this->service_id ) );
        $this->unlocked_days = array();
        $this->unlocked_days = array_merge( $this->unlocked_days, $days );
    }
    
    // load unlocked days for service
    public function loadLockedTimeSlots()
    {
        global  $wpdb ;
        $timeslots = $wpdb->get_col( $wpdb->prepare( "\n\t\t\t\t\t\tSELECT time\n\t\t\t\t\t\tFROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_locked_time_slots\n\t\t\t\t\t\twhere service_id = %d", $this->service_id ) );
        $this->locked_timeslots = array();
        $this->locked_timeslots = array_merge( $this->locked_timeslots, $timeslots );
    }
    
    // get day status working / weekend
    // 1 - working, 0 - weekend, 2 - limit reached
    public function getDayStatus( $day )
    {
        $whole_day_checkin = get_option( 'wbk_appointments_lock_day_if_timeslot_booked', '' );
        if ( is_array( $whole_day_checkin ) ) {
            
            if ( in_array( $this->service_id, $whole_day_checkin, true ) ) {
                $this_day_bookings = WBK_Model_Utils::get_booking_ids_by_day_service( $day, $this->service_id );
                if ( count( $this_day_bookings ) > 0 ) {
                    return 0;
                }
            }
        
        }
        $day_limit = trim( get_option( 'wbk_appointments_limit_by_day', '' ) );
        if ( $day_limit != '' ) {
            if ( WBK_Db_Utils::getCountOfAppointmentsByDay( $day ) >= $day_limit ) {
                return 2;
            }
        }
        // check manual arrays
        if ( in_array( $day, $this->locked_days ) === true ) {
            return 0;
        }
        // check manual arrays
        if ( in_array( $day, $this->unlocked_days ) === true ) {
            return 1;
        }
        // check global holyday option
        if ( $this->busines_hours->checkIfHolyday( $day ) === true ) {
            return 0;
        }
        // check special hours option
        $data = trim( get_option( 'wbk_appointments_special_hours', '' ) );
        
        if ( $data != '' ) {
            $data = explode( PHP_EOL, $data );
            foreach ( $data as $line ) {
                $parts = explode( ' ', $line );
                if ( count( $parts ) != 2 && count( $parts ) != 3 ) {
                    continue;
                }
                
                if ( count( $parts ) == 3 ) {
                    if ( $this->service_id != $parts[0] ) {
                        continue;
                    }
                    $date = strtotime( $parts[1] );
                } else {
                    $date = strtotime( $parts[0] );
                }
                
                if ( $date == $day ) {
                    return 1;
                }
            }
        }
        
        // check global weekly options
        
        if ( $this->is_working_day( $this->service_id, $day ) === true ) {
            return 1;
        } else {
            return 0;
        }
    
    }
    
    public function is_working_day( $service_id, $day )
    {
        $sp = new WBK_Schedule_Processor();
        return $sp->is_working_day( $day, $service_id );
    }
    
    // load all appoitments from db for given day
    public function loadAppointmentsDay( $day )
    {
        global  $wpdb ;
        $db_arr = $wpdb->get_results( $wpdb->prepare( "\n\t\t\t\t\t\t\t\t\t\t\t\t\tSELECT *\n\t\t\t\t\t\t\t\t\t\t\t\t\tFROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments\n\t\t\t\t\t\t\t\t\t\t\t\t\twhere service_id = %d AND day = %d\n\t\t\t\t\t\t\t\t\t\t\t\t\t", $this->service_id, $day ) );
        $this->appointments = array();
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
                if ( !$service->setId( $this->service_id ) ) {
                    continue;
                }
                if ( !$service->load() ) {
                    continue;
                }
                
                if ( $service->getQuantity() == 1 ) {
                    $betw_interval = $this->service->getInterval();
                    $app_end = $item->time + $item->duration * 60 + $betw_interval * 60;
                    $breaker = new WBK_Time_Slot( $item->time, $app_end );
                    array_push( $this->breakers, $breaker );
                }
            
            }
        
        }
        return;
    }
    
    // add break for day
    public function addBusinessHoursBreak( $day )
    {
        $arr = $this->busines_hours->getBusinessHours( $day );
        
        if ( count( $arr ) == 4 ) {
            $start = $day + $arr[1];
            $end = $day + $arr[2];
            $breaker = new WBK_Time_Interval( $start, $end );
            array_push( $this->breakers, $breaker );
        }
    
    }
    
    public function getFirstAvailableTime()
    {
        foreach ( $this->timeslots as $timeslot ) {
            if ( $timeslot->getStatus() == 0 ) {
                return $timeslot->getStart();
            }
            if ( is_array( $timeslot->getStatus() ) ) {
                if ( $this->service->getQuantity( $timeslot->getStart() ) > 1 ) {
                    if ( $this->getAvailableCount( $timeslot->getStart() ) > 0 ) {
                        return $timeslot->getStart();
                    }
                }
            }
        }
        return 0;
    }
    
    // frontend render
    public function renderDayFrontend( $time_after, $offset )
    {
        global  $wbk_wording ;
        $html = '';
        $time_format = WBK_Date_Time_Utils::getTimeFormat();
        $date_format = WBK_Date_Time_Utils::getDateFormat();
        $time_slots = '';
        $timeslot_format = get_option( 'wbk_timeslot_format', 'detailed' );
        $night_houts_addon = get_option( 'wbk_night_hours', '0' ) * 60 * 60;
        $add_before_after = get_option( 'wbk_appointments_lock_one_before_and_one_after', '' );
        
        if ( !WBK_Validator::checkInteger( $night_houts_addon, 1, 64800 ) ) {
            $night_houts_addon = 0;
        } else {
            $service_schedule_tomorrow = new WBK_Service_Schedule();
            $service_schedule_tomorrow->setServiceId( $this->service_id );
            $service_schedule_tomorrow->load();
            $tomorrow = strtotime( '+1 day', $this->day );
            $service_schedule_tomorrow->buildSchedule(
                $tomorrow,
                false,
                false,
                true,
                true
            );
            $tomorrows_time_slots = $service_schedule_tomorrow->getTimeSlots();
            for ( $i = 0 ;  $i < count( $tomorrows_time_slots ) ;  $i++ ) {
                $timeslot = $tomorrows_time_slots[$i];
                if ( $timeslot->getStart() < $tomorrow + $night_houts_addon && $timeslot->getEnd() <= $tomorrow + $night_houts_addon ) {
                    $this->timeslots[] = $timeslot;
                }
            }
        }
        
        for ( $i = 0 ;  $i < count( $this->timeslots ) ;  $i++ ) {
            $timeslot = $this->timeslots[$i];
            
            if ( get_option( 'wbk_disallow_after', '0' ) != '0' ) {
                $timeslot = $this->timeslots[$i];
                $limit2 = time() + get_option( 'wbk_disallow_after' ) * 60 * 60;
                if ( $timeslot->getStart() > $limit2 ) {
                    continue;
                }
            }
            
            // ** check before and after appointments
            if ( is_array( $add_before_after ) ) {
                if ( in_array( $this->service_id, $add_before_after ) ) {
                    
                    if ( $timeslot->getStatus() == 0 || is_array( $timeslot->getStatus() ) ) {
                        
                        if ( $i > 0 ) {
                            $prev_slot = $this->timeslots[$i - 1];
                            if ( $prev_slot->getStatus() > 0 || is_array( $prev_slot->getStatus() ) ) {
                                $timeslot->setStatus( -2 );
                            }
                        }
                        
                        
                        if ( $i < count( $this->timeslots ) - 1 ) {
                            $next_slot = $this->timeslots[$i + 1];
                            if ( $next_slot->getStatus() > 0 || is_array( $next_slot->getStatus() ) ) {
                                $timeslot->setStatus( -2 );
                            }
                        }
                    
                    }
                
                }
            }
            // night hours
            
            if ( $night_houts_addon != 0 ) {
                $comparation_value = WBK_Time_Math_Utils::adjust_times( $this->day, $night_houts_addon, get_option( 'wbk_timezone', 'UTC' ) );
                if ( $timeslot->getStart() < $comparation_value && $timeslot->getEnd() <= $comparation_value ) {
                    if ( strtotime( 'today midnight' ) != $this->day ) {
                        continue;
                    }
                }
            }
            
            $max_per_time = trim( get_option( 'wbk_appointments_autolock_avail_limit', '' ) );
            
            if ( $max_per_time != '' && is_numeric( $max_per_time ) ) {
                $all_quantity = WBK_Db_Utils::getQuantityFromAllSerivces( $timeslot->getStart(), $timeslot->getEnd() );
                if ( $all_quantity >= $max_per_time ) {
                    continue;
                }
            }
            
            if ( get_option( 'wbk_appointments_auto_lock', 'disabled' ) == 'enabled' ) {
                
                if ( get_option( 'wbk_appointments_auto_lock_allow_unlock', 'allow' ) == 'disallow' ) {
                    $connected_quantity = WBK_Db_Utils::getQuantityFromConnectedServices2( $this->service->getId(), $timeslot->getStart(), true );
                    if ( $connected_quantity > 0 ) {
                        
                        if ( $this->service->getQuantity() == 1 ) {
                            $timeslot->setStatus( -2 );
                        } else {
                            if ( get_option( 'wbk_appointments_auto_lock_group', 'lock' ) == 'lock' ) {
                                $timeslot->setStatus( -2 );
                            }
                        }
                    
                    }
                }
            
            }
            $time = $timeslot->get_formated_time();
            $local_time_str = $timeslot->get_formated_time_local();
            if ( $timeslot->getStatus() == 0 || is_array( $timeslot->getStatus() ) ) {
                
                if ( $timeslot->getStart() >= $time_after ) {
                    $ongoing_valid = false;
                    
                    if ( get_option( 'wbk_allow_ongoing_time_slot', 'disallow' ) == 'disallow' ) {
                        if ( $timeslot->getStart() > time() + $this->service->getPrepareTime() * 60 ) {
                            $ongoing_valid = true;
                        }
                    } else {
                        if ( $timeslot->getStart() > time() || $timeslot->getStart() < time() && $timeslot->getEnd() > time() ) {
                            $ongoing_valid = true;
                        }
                    }
                    
                    
                    if ( $ongoing_valid ) {
                        $slot_html = '';
                        
                        if ( $timeslot_format == 'detailed' ) {
                            $available_html = '';
                            $available_count = '';
                            
                            if ( $this->service->getQuantity( $timeslot->getStart() ) > 1 ) {
                                $available_count = $this->getAvailableCount( $timeslot->getStart() );
                                if ( in_array( $timeslot->getStart(), $this->locked_timeslots ) ) {
                                    $available_count = 0;
                                }
                                if ( $available_count == 0 ) {
                                    if ( get_option( 'wbk_show_booked_slots', 'disabled' ) == 'disabled' ) {
                                        continue;
                                    }
                                }
                                if ( $this->service->getMinQuantity() > 1 ) {
                                    if ( $available_count < $this->service->getMinQuantity() ) {
                                        continue;
                                    }
                                }
                                $available_lablel = get_option( 'wbk_time_slot_available_text', __( 'available', 'wbk' ) );
                                
                                if ( $available_lablel == '' ) {
                                    global  $wbk_wording ;
                                    $available_lablel = $wbk_wording['wbk_time_slot_available_text'];
                                }
                                
                                $available_count = $timeslot->get_free_places();
                                $available_html = '<div class="wbk-slot-available"><span class="wbk-abailable-container">' . $available_count . '</span> ' . $available_lablel . '</div>';
                                
                                if ( get_option( 'wbk_show_details_prev_booking', 'disabled' ) == 'enabled' ) {
                                    $app_ids_avail = WBK_Db_Utils::getAppointmentsByServiceAndTime( $this->service_id, $timeslot->getStart() );
                                    foreach ( $app_ids_avail as $app_id_this ) {
                                        $appointment = new WBK_Appointment_deprecated();
                                        if ( !$appointment->setId( $app_id_this ) ) {
                                            continue;
                                        }
                                        if ( !$appointment->load() ) {
                                            continue;
                                        }
                                        $customer_name = $appointment->getName();
                                        $slot_button = get_option( 'wbk_booked_text', '' );
                                        $slot_button = str_replace( '#username', $customer_name, $slot_button );
                                        $slot_button = WBK_Db_Utils::subject_placeholder_processing( $slot_button, $appointment, FALSE );
                                        $available_html .= '<div class="wbk-slot-available">' . $slot_button . '</div>';
                                    }
                                }
                            
                            }
                            
                            $book_text = get_option( 'wbk_book_text_timeslot', '' );
                            if ( $book_text == '' ) {
                                $book_text = sanitize_text_field( $wbk_wording['book_text'] );
                            }
                            
                            if ( $available_count > 0 || $this->service->getQuantity() == 1 ) {
                                $book_button = '<input type="button" data-end="' . $timeslot->getEnd() . '"  data-start="' . $timeslot->getStart() . '"  value="' . $book_text . '" id="wbk-timeslot-btn_' . $timeslot->getStart() . '" data-available="' . $available_count . '"   data-service="' . $this->service->getId() . '"  class="wbk-slot-button" />';
                            } else {
                                $slot_button = get_option( 'wbk_booked_text', '' );
                                if ( $slot_button == '' ) {
                                    $slot_button = sanitize_text_field( $wbk_wording['booked_text'] );
                                }
                                
                                if ( get_option( 'wbk_show_details_prev_booking', 'disabled' ) == 'disabled' ) {
                                    $book_button = '<input type="button"  data-start="' . $timeslot->getStart() . '" data-service="' . $this->service->getId() . '" value="' . $slot_button . '" class="wbk-slot-button wbk-slot-booked" />';
                                } else {
                                    $book_button = '';
                                }
                            
                            }
                            
                            $pre_time = get_option( 'wbk_server_time_format', '' );
                            if ( $pre_time != '' ) {
                                $time = $pre_time . ' ' . $time;
                            }
                            $post_time = get_option( 'wbk_server_time_format2', '' );
                            if ( $post_time != '' ) {
                                $time = $time . ' ' . $post_time;
                            }
                            
                            if ( get_option( 'wbk_show_local_time', 'disabled' ) == 'enabled_only' ) {
                                $time = '';
                            } else {
                                $time .= '<br>';
                            }
                            
                            $slot_html = '<div class="wbk-slot-time">' . $time . $local_time_str . '</div>' . $available_html . $book_button;
                        } else {
                            
                            if ( $this->service->getQuantity( $timeslot->getStart() ) > 1 ) {
                                $available_count = $this->getAvailableCount( $timeslot->getStart() );
                                if ( $available_count == 0 ) {
                                    if ( get_option( 'wbk_show_booked_slots', 'disabled' ) == 'disabled' ) {
                                        continue;
                                    }
                                }
                            }
                            
                            $slot_html = '<input type="button"  data-service="' . $this->service->getId() . '"  data-end="' . $timeslot->getEnd() . '"  data-start="' . $timeslot->getStart() . '" value="' . $time . '" id="wbk-timeslot-btn_' . $timeslot->getStart() . '" data-available="' . $available_count . '"   class="wbk-slot-button" />';
                        }
                        
                        $availability = '';
                        $time_slots .= '<li class="wbk-col-4-6-12">
								<div class="wbk-slot-inner">' . $slot_html . '</div>
							</li>';
                    }
                
                }
            
            }
            if ( get_option( 'wbk_show_locked_as_booked', 'no' ) == 'yes' ) {
                if ( $timeslot->getStatus() == -2 ) {
                    
                    if ( get_option( 'wbk_show_booked_slots', 'disabled' ) == 'enabled' ) {
                        $slot_button = get_option( 'wbk_booked_text', '' );
                        if ( $slot_button == '' ) {
                            $slot_button = sanitize_text_field( $wbk_wording['booked_text'] );
                        }
                        
                        if ( $timeslot_format == 'detailed' ) {
                            $slot_html = '<div class="wbk-slot-time">' . $time . '</div>
											<input data-start="' . $timeslot->getStart() . '" data-service="' . $this->service->getId() . '" type="button" value="' . $slot_button . '" class="wbk-slot-button wbk-slot-booked" />';
                        } else {
                            $slot_html = '<input  data-start="' . $timeslot->getStart() . '" data-service="' . $this->service->getId() . '"  type="button" value="' . $slot_button . '" class="wbk-slot-button wbk-slot-booked" />';
                        }
                        
                        $time_slots .= '<li class="wbk-col-4-6-12">
									<div class="wbk-slot-inner">' . $slot_html . '</div>
								</li>';
                    }
                
                }
            }
            
            if ( $timeslot->getStatus() > 0 && !is_array( $timeslot->getStatus() ) ) {
                $show_booked_slots = get_option( 'wbk_show_booked_slots', 'disabled' );
                
                if ( $show_booked_slots == 'enabled' ) {
                    $slot_button = get_option( 'wbk_booked_text', '' );
                    if ( $slot_button == '' ) {
                        $slot_button = sanitize_text_field( $wbk_wording['booked_text'] );
                    }
                    // replace placeholders
                    // name
                    $appointment = new WBK_Appointment_deprecated();
                    if ( !$appointment->setId( $timeslot->getStatus() ) ) {
                        continue;
                    }
                    if ( !$appointment->load() ) {
                        continue;
                    }
                    $customer_name = $appointment->getName();
                    $slot_button = str_replace( '#username', $customer_name, $slot_button );
                    $slot_button = str_replace( '#time', $time, $slot_button );
                    $slot_button = WBK_Db_Utils::subject_placeholder_processing( $slot_button, $appointment, FALSE );
                    // end replace placeholders
                    
                    if ( $timeslot_format == 'detailed' ) {
                        $pre_time = get_option( 'wbk_server_time_format', '' );
                        if ( $pre_time != '' ) {
                            $time = $pre_time . ' ' . $time;
                        }
                        $post_time = get_option( 'wbk_server_time_format2', '' );
                        if ( $post_time != '' ) {
                            $time = $time . ' ' . $post_time;
                        }
                        
                        if ( get_option( 'wbk_show_local_time', 'disabled' ) == 'enabled_only' ) {
                            $time = '';
                        } else {
                            $time .= '<br>';
                        }
                        
                        $slot_html = '
										<div class="wbk-slot-time">' . $time . $local_time_str . '</div>
										<input data-start="' . $timeslot->getStart() . '"  type="button" value="' . $slot_button . '" class="wbk-slot-button wbk-slot-booked" />';
                    } else {
                        $slot_html = '
										<input data-start="' . $timeslot->getStart() . '" type="button" value="' . $slot_button . '" class="wbk-slot-button wbk-slot-booked" />';
                    }
                    
                    $time_slots .= '<li class="wbk-col-4-6-12">
								<div class="wbk-slot-inner">' . $slot_html . '</div>
							</li>';
                }
            
            }
        
        }
        
        if ( $time_slots != '' ) {
            $html = '<ul class="wbk-timeslot-list">';
            $html .= $time_slots;
            $html .= '</ul>';
        }
        
        return $html;
    }
    
    public function fableCount( $time )
    {
        global  $wpdb ;
        $service = new WBK_Service_deprecated();
        if ( !$service->setId( $this->service_id ) ) {
            return 0;
        }
        if ( !$service->load() ) {
            return 0;
        }
        $total_duration = $service->getDuration() * 60 + $service->getInterval() * 60;
        $booked = $wpdb->get_var( $wpdb->prepare( "SELECT sum(quantity) FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE service_id = %d AND  time = %d", $this->service_id, $time ) );
        if ( $booked === NULL ) {
            $booked = 0;
        }
        $booked2 = $wpdb->get_var( $wpdb->prepare(
            "SELECT sum(quantity) FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE service_id = %d AND  ( time < %d AND ( time + %d ) > %d)",
            $this->service_id,
            $time,
            $total_duration,
            $time
        ) );
        if ( $booked2 === NULL ) {
            $booked2 = 0;
        }
        $booked3 = $wpdb->get_var( $wpdb->prepare(
            "SELECT sum(quantity) FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE service_id = %d AND  ( time > %d AND time < ( %d + %d ) )",
            $this->service_id,
            $time,
            $time,
            $total_duration
        ) );
        if ( $booked3 === NULL ) {
            $booked3 = 0;
        }
        $booked = $booked + $booked2 + $booked3;
        $available = $service->getQuantity( $time ) - $booked;
        $end = $time + $service->getDuration() * 60 + $service->getInterval() * 60;
        $connected_quantity = WBK_Db_Utils::getQuantityFromConnectedServices2( $this->service_id, $time );
        $available = $available - $connected_quantity;
        if ( $available < 0 ) {
            $available = 0;
        }
        return $available;
    }
    
    public function getAvailableCount( $time, $ignore_parital = false )
    {
        global  $wpdb ;
        
        if ( get_option( 'wbk_availability_calculation_method', 'sum' ) == 'highest' ) {
            $service = new WBK_Service_deprecated();
            if ( !$service->setId( $this->service_id ) ) {
                return 0;
            }
            if ( !$service->load() ) {
                return 0;
            }
            $total_duration = $service->getDuration() * 60 + $service->getInterval() * 60;
            $booked = $wpdb->get_var( $wpdb->prepare( "SELECT max(quantity) FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE service_id = %d AND  time = %d", $this->service_id, $time ) );
            if ( $booked === NULL ) {
                $booked = 0;
            }
            $booked2 = $wpdb->get_var( $wpdb->prepare(
                "SELECT max(quantity) FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE service_id = %d AND  ( time < %d AND ( time + %d ) > %d)",
                $this->service_id,
                $time,
                $total_duration,
                $time
            ) );
            if ( $booked2 === NULL ) {
                $booked2 = 0;
            }
            $booked3 = $wpdb->get_var( $wpdb->prepare(
                "SELECT max(quantity) FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE service_id = %d AND  ( time > %d AND time < ( %d + %d ) )",
                $this->service_id,
                $time,
                $time,
                $total_duration
            ) );
            if ( $booked3 === NULL ) {
                $booked3 = 0;
            }
            $avail_array = [ $booked, $booked2, $booked3 ];
            return $service->getQuantity( $time ) - max( $avail_array );
        }
        
        // *****
        $service = new WBK_Service_deprecated();
        if ( !$service->setId( $this->service_id ) ) {
            return 0;
        }
        if ( !$service->load() ) {
            return 0;
        }
        $total_duration = $service->getDuration() * 60 + $service->getInterval() * 60;
        
        if ( get_option( 'wbk_mode_overlapping_availabiliy', 'true' ) == 'true' ) {
            $booked = $wpdb->get_var( $wpdb->prepare(
                "SELECT sum(quantity) FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE ( service_id = %d AND  time = %d ) ||\n\t\t\t\t( service_id = %d AND  ( time < %d AND ( time + %d ) > %d)  ) ||\n\t\t\t\t( service_id = %d AND  ( time > %d AND time < ( %d + %d ) ) )",
                $this->service_id,
                $time,
                $this->service_id,
                $time,
                $total_duration,
                $time,
                $this->service_id,
                $time,
                $time,
                $total_duration
            ) );
        } else {
            $booked = $wpdb->get_var( $wpdb->prepare( "SELECT sum(quantity) FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE ( service_id = %d AND  time = %d )", $this->service_id, $time ) );
        }
        
        if ( $booked === NULL ) {
            $booked = 0;
        }
        $service_new = new WBK_Service( $this->service_id );
        $available = $service_new->get_quantity( $time ) - $booked;
        $end = $time + $service->getDuration() * 60 + $service->getInterval() * 60;
        $connected_quantity = WBK_Db_Utils::getQuantityFromConnectedServices2( $this->service_id, $time );
        $booked += $connected_quantity;
        $available = $available - $connected_quantity;
        
        if ( $service_new->get_quantity( $time ) > 1 && get_option( 'wbk_gg_2way_group', 'lock' ) == 'reduce' ) {
            $gg_intersect = $this->intersectWithGoogleEvents( $time, $time + $total_duration );
            $available -= $gg_intersect;
            $booked += $gg_intersect;
        }
        
        if ( $available < 0 ) {
            $available = 0;
        }
        
        if ( !$ignore_parital ) {
            $parital_mode = get_option( 'wbk_appointments_lock_timeslot_if_parital_booked', '' );
            if ( $parital_mode == '' ) {
                $parital_mode = array();
            }
            if ( in_array( $service->getId(), $parital_mode ) && $booked > 0 ) {
                return 0;
            }
        }
        
        return $available;
    }
    
    public function getAvailableCountSingle( $time )
    {
        global  $wpdb ;
        $service = new WBK_Service_deprecated();
        if ( !$service->setId( $this->service_id ) ) {
            return 0;
        }
        if ( !$service->load() ) {
            return 0;
        }
        $total_duration = $service->getDuration() * 60 + $service->getInterval() * 60;
        $booked = $wpdb->get_var( $wpdb->prepare( "SELECT sum(quantity) FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE service_id = %d AND  time = %d", $this->service_id, $time ) );
        if ( $booked === NULL ) {
            $booked = 0;
        }
        $booked2 = $wpdb->get_var( $wpdb->prepare(
            "SELECT sum(quantity) FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE service_id = %d AND  ( time < %d AND ( time + %d ) > %d)",
            $this->service_id,
            $time,
            $total_duration,
            $time
        ) );
        if ( $booked2 === NULL ) {
            $booked2 = 0;
        }
        $booked3 = $wpdb->get_var( $wpdb->prepare(
            "SELECT sum(quantity) FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE service_id = %d AND  ( time > %d AND time < ( %d + %d ) )",
            $this->service_id,
            $time,
            $time,
            $total_duration
        ) );
        if ( $booked3 === NULL ) {
            $booked3 = 0;
        }
        $booked = $booked + $booked2 + $booked3;
        return $booked;
    }
    
    public function getAvailableCountSingleRange( $start, $end )
    {
        global  $wpdb ;
        $service = new WBK_Service_deprecated();
        if ( !$service->setId( $this->service_id ) ) {
            return 0;
        }
        if ( !$service->load() ) {
            return 0;
        }
        $total_duration = $service->getDuration() * 60 + $service->getInterval() * 60;
        $booked = $wpdb->get_var( $wpdb->prepare(
            "SELECT sum(quantity) FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE service_id = %d AND  ( time >= %d AND ( time + %d ) <= %d    )",
            $this->service_id,
            $start,
            $total_duration,
            $end
        ) );
        if ( $booked === NULL ) {
            $booked = 0;
        }
        return $booked;
    }
    
    public function getTimeSlotStartForParticularTime( $time )
    {
        foreach ( $this->timeslots as $timeslot ) {
            if ( $timeslot->isTimeIn( $time ) || $time == $timeslot->getStart() ) {
                return $timeslot->getStart();
            }
        }
        return FALSE;
    }
    
    // get free time slots in range
    public function getNotBookedTimeSlotsInRange( $start, $end )
    {
        $result = array();
        foreach ( $this->timeslots as $timeslot ) {
            if ( $timeslot->getStatus() == 0 ) {
                if ( $timeslot->getStart() >= $start && $timeslot->getStart() < $end ) {
                    $result[] = $timeslot->getStart();
                }
            }
        }
        return $result;
    }
    
    public function getLockedTimeSlotsInRange( $start, $end )
    {
        $result = array();
        foreach ( $this->timeslots as $timeslot ) {
            if ( $timeslot->getStatus() == -2 ) {
                if ( $timeslot->getStart() >= $start && $timeslot->getStart() <= $end ) {
                    $result[] = $timeslot->getStart();
                }
            }
        }
        return $result;
    }
    
    // get free time slots
    public function getNotBookedTimeSlots()
    {
        $result = array();
        foreach ( $this->timeslots as $timeslot ) {
            if ( $timeslot->getStatus() == 0 ) {
                $result[] = $timeslot->getStart();
            }
        }
        return $result;
    }
    
    public function hasFreeTimeSlots()
    {
        foreach ( $this->timeslots as $timeslot ) {
            if ( $timeslot->getStatus() == 0 ) {
                
                if ( get_option( 'wbk_appointments_auto_lock', 'disabled' ) == 'disabled' ) {
                    return true;
                } else {
                    if ( $this->getAvailableCount( $timeslot->getStart() ) > 0 ) {
                        return true;
                    }
                }
            
            }
            
            if ( is_array( $timeslot->getStatus() ) ) {
                $available = $this->getAvailableCount( $timeslot->getStart() );
                if ( $available > 0 ) {
                    return true;
                }
            }
        
        }
        return false;
    }
    
    public function getTimeSlots()
    {
        return $this->timeslots;
    }
    
    public function getService()
    {
        return $this->service;
    }
    
    // loadAppointmentsDay( $day );
    public function loadFromGGCalendar( $day )
    {
        $event_data_arr = array();
        return $event_data_arr;
    }
    
    public function getAppointment()
    {
        return $this->appointments;
    }
    
    public function parital_load1()
    {
        $this->breakers = array();
        $this->service = new WBK_Service_deprecated();
        
        if ( $this->service->setId( $this->service_id ) ) {
            if ( !$this->service->load() ) {
                return false;
            }
        } else {
            return false;
        }
    
    }
    
    // get diabilities depended on week_starts_on
    public function getWeekDisabilities()
    {
        $sp = new WBK_Schedule_Processor();
        $sp->load_unlocked_days();
        $result = array();
        for ( $i = 1 ;  $i <= 7 ;  $i++ ) {
            if ( !$sp->is_working_day( $i, $this->service_id ) && !$sp->is_unlockced_has_dow( $i, $this->service_id ) ) {
                
                if ( get_option( 'wbk_start_of_week', 'monday' ) == 'monday' ) {
                    $result[] = $i;
                } else {
                    $term = $i + 1;
                    if ( $term == 8 ) {
                        $term = 1;
                    }
                    $result[] = $term;
                }
            
            }
        }
        return $result;
    }
    
    function intersectWithGoogleEvents( $start, $end )
    {
        return 0;
    }
    
    public function loadEventsInRange( $day, $number_of_days )
    {
        $event_data_arr = array();
        return $event_data_arr;
    }
    
    public function setGoogleEventsManualy( $events )
    {
        $this->gg_breakers = $events;
    }

}
add_filter(
    'wbk_business_hours_for_service',
    'webba_native_wbk_business_hours_for_service',
    10,
    3
);
function webba_native_wbk_business_hours_for_service( $value, $day, $service_id )
{
    $data = trim( get_option( 'wbk_appointments_special_hours', '' ) );
    if ( $data == '' ) {
        return $value;
    }
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
        $result = array();
        
        if ( $date == $day ) {
            $intervals = explode( ',', $parts[2] );
            foreach ( $intervals as $interval ) {
                $times = explode( '-', $interval );
                $time = $times[0];
                $splitted_time = explode( ':', $time );
                $seconds = $splitted_time[0] * 60 * 60 + $splitted_time[1] * 60 + 2;
                $result[] = $seconds;
                $times = explode( '-', $interval );
                $time = $times[1];
                $splitted_time = explode( ':', $time );
                $seconds = $splitted_time[0] * 60 * 60 + $splitted_time[1] * 60 + 2;
                $result[] = $seconds;
            }
            return $result;
        }
    
    }
    return $value;
}
