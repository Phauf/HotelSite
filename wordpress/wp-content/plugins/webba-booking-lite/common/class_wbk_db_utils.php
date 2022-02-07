<?php

// check if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class WBK_Db_Utils
{
    static function createTables()
    {
        global  $wpdb ;
        $prefix = $wpdb->prefix;
        update_option( 'wbk_db_prefix', $prefix );
        // custom on/off days
        $wpdb->query( "CREATE TABLE IF NOT EXISTS " . get_option( 'wbk_db_prefix', '' ) . "wbk_days_on_off (\n\t            id int unsigned NOT NULL auto_increment PRIMARY KEY,\n\t            service_id int unsigned NOT NULL,\n\t            day int unsigned NOT NULL,\n\t            status int unsigned NOT NULL,\n\t            UNIQUE KEY id (id)\n\t        )\n\t        DEFAULT CHARACTER SET = utf8\n\t        COLLATE = utf8_general_ci" );
        // custom locked timeslots
        $wpdb->query( "CREATE TABLE IF NOT EXISTS " . get_option( 'wbk_db_prefix', '' ) . "wbk_locked_time_slots (\n\t            id int unsigned NOT NULL auto_increment PRIMARY KEY,\n\t            service_id int unsigned NOT NULL,\n\t            time int unsigned NOT NULL,\n\t            connected_id int unsigned NOT NULL default 0,\n\t            UNIQUE KEY id (id)\n\t        )\n\t        DEFAULT CHARACTER SET = utf8\n\t        COLLATE = utf8_general_ci" );
    }
    
    // drop tables
    static function dropTables()
    {
        global  $wpdb ;
        $wpdb->query( 'DROP TABLE IF EXISTS ' . get_option( 'wbk_db_prefix', '' ) . 'wbk_services' );
        $wpdb->query( 'DROP TABLE IF EXISTS ' . get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments' );
        $wpdb->query( 'DROP TABLE IF EXISTS ' . get_option( 'wbk_db_prefix', '' ) . 'wbk_locked_time_slots' );
        $wpdb->query( 'DROP TABLE IF EXISTS ' . get_option( 'wbk_db_prefix', '' ) . 'wbk_days_on_off' );
        $wpdb->query( 'DROP TABLE IF EXISTS ' . get_option( 'wbk_db_prefix', '' ) . 'wbk_email_templates' );
        $wpdb->query( 'DROP TABLE IF EXISTS ' . get_option( 'wbk_db_prefix', '' ) . 'wbk_gg_calendars' );
    }
    
    // get services with same category
    static function getServicesWithSameCategory( $service_id )
    {
        global  $wpdb ;
        $result = array();
        $categories = self::getServiceCategoryList();
        foreach ( $categories as $key => $value ) {
            $services = self::getServicesInCategory( $key );
            if ( in_array( $service_id, $services ) ) {
                foreach ( $services as $current_service ) {
                    if ( $current_service != $service_id ) {
                        $result[] = $current_service;
                    }
                }
            }
        }
        $result = array_unique( $result );
        return $result;
    }
    
    // get services
    static function getServices()
    {
        global  $wpdb ;
        $order_type = get_option( 'wbk_order_service_by', 'a-z' );
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( get_option( 'wbk_db_prefix', '' ) . 'wbk_services' ) );
        if ( !$wpdb->get_var( $query ) == get_option( 'wbk_db_prefix', '' ) . 'wbk_services' ) {
            return array();
        }
        
        if ( $order_type == 'a-z' ) {
            $service_sql = "SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_services ";
            $service_sql = apply_filters( 'wbk_get_services', $service_sql );
            $service_sql .= " order by name asc ";
            $result = $wpdb->get_col( $service_sql );
        }
        
        
        if ( $order_type == 'priority' ) {
            $service_sql = "SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_services ";
            $service_sql = apply_filters( 'wbk_get_services', $service_sql );
            $service_sql .= " order by priority desc";
            $result = $wpdb->get_col( $service_sql );
        }
        
        
        if ( $order_type == 'priority_a' ) {
            $service_sql = "SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_services ";
            $service_sql = apply_filters( 'wbk_get_services', $service_sql );
            $service_sql .= " order by priority asc";
            $result = $wpdb->get_col( $service_sql );
        }
        
        return $result;
    }
    
    // get service category list
    static function getServiceCategoryList()
    {
        global  $wpdb ;
        $sql = "SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_service_categories";
        $sql = apply_filters( 'wbk_get_categories', $sql );
        $categories = $wpdb->get_col( $sql );
        $result = array();
        foreach ( $categories as $category_id ) {
            $name = $wpdb->get_var( $wpdb->prepare( " SELECT name FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_service_categories WHERE id = %d", $category_id ) );
            $result[$category_id] = $name;
        }
        return $result;
    }
    
    // get service category list
    static function getServicesInCategory( $category_id )
    {
        global  $wpdb ;
        $list = $wpdb->get_var( $wpdb->prepare( " SELECT category_list FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_service_categories WHERE id = %d", $category_id ) );
        if ( $list == '' ) {
            return FALSE;
        }
        return json_decode( $list );
    }
    
    // get category names by service
    static function getCategoryNamesByService( $service_id )
    {
        $categories = self::getServiceCategoryList();
        $result = array();
        foreach ( $categories as $key => $value ) {
            $services = self::getServicesInCategory( $key );
            if ( is_array( $services ) ) {
                if ( in_array( $service_id, $services ) ) {
                    $result[] = $value;
                }
            }
        }
        
        if ( count( $result ) > 0 ) {
            return implode( ', ', $result );
        } else {
            return '';
        }
    
    }
    
    // get not-admin users
    static function getNotAdminUsers()
    {
        $arr_users = array();
        $arr_temp = get_users( array(
            'role__not_in' => array( 'administrator' ),
            'fields'       => 'user_login',
        ) );
        if ( count( $arr_temp ) > 0 ) {
            array_push( $arr_users, $arr_temp );
        }
        return $arr_users;
    }
    
    // get admin users
    static function getAdminUsers()
    {
        $arr_users = array();
        array_push( $arr_users, get_users( array(
            'role'   => 'administrator',
            'fields' => 'user_login',
        ) ) );
        return $arr_users;
    }
    
    // check if service name is free
    static function isServiceNameFree( $value )
    {
        global  $wpdb ;
        $count = $wpdb->get_var( $wpdb->prepare( " SELECT COUNT(*) FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_services WHERE name = %s ", $value ) );
        
        if ( $count > 0 ) {
            return false;
        } else {
            return true;
        }
    
    }
    
    // get CF7 forms
    static function getCF7Forms()
    {
        $args = array(
            'post_type'      => 'wpcf7_contact_form',
            'posts_per_page' => -1,
        );
        $result = array();
        if ( $cf7Forms = get_posts( $args ) ) {
            foreach ( $cf7Forms as $cf7Form ) {
                $form = new stdClass();
                $form->name = $cf7Form->post_title;
                $form->id = $cf7Form->ID;
                array_push( $result, $form );
            }
        }
        return $result;
    }
    
    // get service id by appointment id
    static function getServiceIdByAppointmentId( $appointment_id )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return false;
        }
        $service_id = $wpdb->get_var( $wpdb->prepare( " SELECT service_id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE id = %d ", $appointment_id ) );
        
        if ( $service_id == null ) {
            return false;
        } else {
            return $service_id;
        }
    
    }
    
    // get status by appointment id
    static function getStatusByAppointmentId( $appointment_id )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return false;
        }
        $value = $wpdb->get_var( $wpdb->prepare( " SELECT status FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE id = %d ", $appointment_id ) );
        
        if ( $value == null ) {
            return false;
        } else {
            return $value;
        }
    
    }
    
    // get appointment id by tokend
    static function getAppointmentIdByToken( $token )
    {
        global  $wpdb ;
        $appointment_id = $wpdb->get_var( $wpdb->prepare( " SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE token = %s ", $token ) );
        
        if ( $appointment_id == null ) {
            return false;
        } else {
            if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
                return false;
            }
            return $appointment_id;
        }
    
    }
    
    // get category name by category id
    static function getCategoryNameByCategoryId( $category_id )
    {
        global  $wpdb ;
        $category_name = $wpdb->get_var( $wpdb->prepare( " SELECT name FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_service_categories WHERE id = %d ", $category_id ) );
        
        if ( $category_name == null ) {
            return false;
        } else {
            return $category_name;
        }
    
    }
    
    // get appointment id by admin tokend
    static function getAppointmentIdByAdminToken( $token )
    {
        global  $wpdb ;
        $appointment_id = $wpdb->get_var( $wpdb->prepare( " SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE admin_token = %s ", $token ) );
        
        if ( $appointment_id == null ) {
            return false;
        } else {
            if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
                return false;
            }
            return $appointment_id;
        }
    
    }
    
    // get tokend by appointment id
    static function getTokenByAppointmentId( $appointment_id )
    {
        global  $wpdb ;
        $token = $wpdb->get_var( $wpdb->prepare( " SELECT token FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE id = %d ", $appointment_id ) );
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return '';
        }
        
        if ( $token == null ) {
            $token = uniqid();
            $result = $wpdb->update(
                get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
                array(
                'token' => $token,
            ),
                array(
                'id' => $appointment_id,
            ),
                array( '%s' ),
                array( '%d' )
            );
            return $token;
        } else {
            return $token;
        }
    
    }
    
    // get tokend by appointment id
    static function getAdminTokenByAppointmentId( $appointment_id )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return '';
        }
        $token = $wpdb->get_var( $wpdb->prepare( " SELECT admin_token FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE id = %d ", $appointment_id ) );
        
        if ( $token == null ) {
            $token = uniqid();
            $result = $wpdb->update(
                get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
                array(
                'admin_token' => $token,
            ),
                array(
                'id' => $appointment_id,
            ),
                array( '%s' ),
                array( '%d' )
            );
            return $token;
        } else {
            return $token;
        }
    
    }
    
    // get quantity by appointment id
    static function getQuantityByAppointmentId( $appointment_id )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return false;
        }
        $value = $wpdb->get_var( $wpdb->prepare( " SELECT quantity FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE id = %d ", $appointment_id ) );
        
        if ( $value == null ) {
            return false;
        } else {
            return $value;
        }
    
    }
    
    // get tomorrow appointments for the service
    static function getTomorrowAppointmentsForService( $service_id )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $service_id, get_option( 'wbk_db_prefix', '' ) . 'wbk_services' ) ) {
            return false;
        }
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $tomorrow = strtotime( 'tomorrow' );
        $result = $wpdb->get_col( $wpdb->prepare( " SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE service_id=%d AND day=%d  ORDER BY time ", $service_id, $tomorrow ) );
        date_default_timezone_set( 'UTC' );
        return $result;
    }
    
    // get future appointments for the service
    static function getFutureAppointmentsForService( $service_id, $days )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $service_id, get_option( 'wbk_db_prefix', '' ) . 'wbk_services' ) ) {
            return false;
        }
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $tomorrow = strtotime( 'today + ' . $days . ' days' );
        $result = $wpdb->get_col( $wpdb->prepare( " SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE service_id=%d AND day=%d  ORDER BY time ", $service_id, $tomorrow ) );
        date_default_timezone_set( 'UTC' );
        return $result;
    }
    
    // lock appointments of others services
    static function lockTimeSlotsOfOthersServices( $service_id, $appointment_id )
    {
        global  $wpdb ;
        // getting data about booked service
        $service = new WBK_Service_deprecated();
        if ( !$service->setId( $service_id ) ) {
            return FALSE;
        }
        if ( !$service->load() ) {
            return FALSE;
        }
        $appointment = new WBK_Appointment_deprecated();
        if ( !$appointment->setId( $appointment_id ) ) {
            return FALSE;
        }
        if ( !$appointment->load() ) {
            return FALSE;
        }
        $start = $appointment->getTime();
        $end = $start + $appointment->getDuration() * 60 + $service->getInterval() * 60;
        // iteration over others services
        $autolock_mode = get_option( 'wbk_appointments_auto_lock_mode', 'all' );
        
        if ( $autolock_mode == 'all' ) {
            $arrIds = WBK_Db_Utils::getServices();
        } elseif ( $autolock_mode == 'categories' ) {
            $arrIds = WBK_Db_Utils::getServicesWithSameCategory( $service_id );
        }
        
        if ( count( $arrIds ) < 1 ) {
            return TRUE;
        }
        foreach ( $arrIds as $service_id_this ) {
            if ( $service_id == $service_id_this ) {
                continue;
            }
            $service = new WBK_Service_deprecated();
            if ( !$service->setId( $service_id_this ) ) {
                continue;
            }
            if ( !$service->load() ) {
                continue;
            }
            if ( $service->getQuantity() > 1 && get_option( 'wbk_appointments_auto_lock_group', 'lock' ) == 'reduce' ) {
                continue;
            }
            if ( get_option( 'wbk_appointments_auto_lock_allow_unlock', 'allow' ) == 'disallow' ) {
                continue;
            }
            $service_schedule = new WBK_Service_Schedule();
            $service_schedule->setServiceId( $service_id_this );
            $service_schedule->load();
            $midnight = strtotime( 'today', $start );
            $service_schedule->buildSchedule( $midnight, true, true );
            $this_duration = $service->getDuration() * 60 + $service->getInterval() * 60;
            $timeslots_to_lock = $service_schedule->getNotBookedTimeSlots();
            foreach ( $timeslots_to_lock as $time_slot_start ) {
                $cur_start = $time_slot_start;
                $cur_end = $time_slot_start + $this_duration;
                $intersect = false;
                if ( $cur_start == $start ) {
                    $intersect = true;
                }
                if ( $cur_start > $start && $cur_start < $end ) {
                    $intersect = true;
                }
                if ( $cur_end > $start && $cur_end <= $end ) {
                    $intersect = true;
                }
                if ( $cur_start <= $start && $cur_end >= $end ) {
                    $intersect = true;
                }
                
                if ( $intersect == true ) {
                    
                    if ( $wpdb->query( $wpdb->prepare( "DELETE FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_locked_time_slots WHERE time = %d and service_id = %d", $time_slot_start, $service_id_this ) ) === false ) {
                        echo  -1 ;
                        die;
                        return;
                    }
                    
                    
                    if ( $wpdb->insert( get_option( 'wbk_db_prefix', '' ) . 'wbk_locked_time_slots', array(
                        'service_id'   => $service_id_this,
                        'time'         => $time_slot_start,
                        'connected_id' => $appointment_id,
                    ), array( '%d', '%d', '%d' ) ) === false ) {
                        echo  -1 ;
                        die;
                        return;
                    }
                
                }
            
            }
        }
    }
    
    // remove lock when appointment cancelled
    static function freeLockedTimeSlot( $appointment_id )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return;
        }
        $wpdb->query( $wpdb->prepare( "DELETE FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_locked_time_slots WHERE connected_id = %d", $appointment_id ) );
    }
    
    // set payment if for appointment()
    static function setPaymentId( $appointment_id, $payment_id )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return FALSE;
        }
        $result = $wpdb->update(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            array(
            'payment_id' => $payment_id,
        ),
            array(
            'id' => $appointment_id,
        ),
            array( '%s' ),
            array( '%d' )
        );
        
        if ( $result == false || $result == 0 ) {
            return FALSE;
        } else {
            return TRUE;
        }
    
    }
    
    // set payment id for appointment
    static function setPaymentCancelToken( $appointment_id, $cancel_token )
    {
        global  $wpdb ;
        if ( !is_numeric( $appointment_id ) ) {
            return FALSE;
        }
        $result = $wpdb->update(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            array(
            'payment_cancel_token' => $cancel_token,
        ),
            array(
            'id' => $appointment_id,
        ),
            array( '%s' ),
            array( '%d' )
        );
        
        if ( $result == false || $result == 0 ) {
            return FALSE;
        } else {
            return TRUE;
        }
    
    }
    
    // get google event data for appointment
    static function getGoogleEventsData( $appointment_id, $event_data )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return array();
        }
        $event_id_json = $wpdb->get_var( $wpdb->prepare( "SELECT gg_event_id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE id = %d", $appointment_id ) );
        if ( $event_id_json == '' ) {
            return array();
        }
        return json_decode( $event_id_json );
    }
    
    // check if google event id added
    static function idEventAddedToGoogle( $appointment_id )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return FALSE;
        }
        $event_id_json = $wpdb->get_var( $wpdb->prepare( "SELECT gg_event_id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE id = %d", $appointment_id ) );
        if ( $event_id_json == '' ) {
            return FALSE;
        }
        return TRUE;
    }
    
    // set google event data for appointment
    static function setGoogleEventsData( $appointment_id, $event_data )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return FALSE;
        }
        $result = $wpdb->update(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            array(
            'gg_event_id' => $event_data,
        ),
            array(
            'id' => $appointment_id,
        ),
            array( '%s' ),
            array( '%d' )
        );
        
        if ( $result == false || $result == 0 ) {
            return FALSE;
        } else {
            return TRUE;
        }
    
    }
    
    // get amount by payment id
    static function getAmountByPaymentId( $payment_id )
    {
        global  $wpdb ;
        if ( $payment_id == '' || !isset( $payment_id ) ) {
            return FALSE;
        }
        $quantity = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(quantity) FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE payment_id = %s", $payment_id ) );
        if ( $quantity == null ) {
            return FALSE;
        }
        $appointment_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE payment_id = %s", $payment_id ) );
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return FALSE;
        }
        if ( $appointment_id == null ) {
            return FALSE;
        }
        $service_id = WBK_Db_Utils::getServiceIdByAppointmentId( $appointment_id );
        $price = $wpdb->get_var( $wpdb->prepare( "SELECT price FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_services WHERE id = %d", $service_id ) );
        if ( $appointment_id == null ) {
            return FALSE;
        }
        return array( $price, $quantity );
    }
    
    // update payment status
    static function updatePaymentStatus( $payment_id, $amount )
    {
        global  $wpdb ;
        $result_pending = $wpdb->update(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            array(
            'status' => 'paid',
        ),
            array(
            'payment_id' => $payment_id,
            'status'     => 'pending',
        ),
            array( '%s' ),
            array( '%s', '%s' )
        );
        $result_approved = $wpdb->update(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            array(
            'status' => 'paid_approved',
        ),
            array(
            'payment_id' => $payment_id,
            'status'     => 'approved',
        ),
            array( '%s' ),
            array( '%s', '%s' )
        );
        
        if ( ($result_pending == false || $result_pending == 0) && ($result_approved == false || $result_approved == 0) ) {
            return FALSE;
        } else {
            $curent_invoice = get_option( 'wbk_email_current_invoice_number', '1' );
            $curent_invoice++;
            update_option( 'wbk_email_current_invoice_number', $curent_invoice );
            $app_ids = self::getAppointmentIdsByPaymentId( $payment_id );
            
            if ( count( $app_ids ) > 0 ) {
                wbk_email_processing_send_on_payment( $app_ids );
                WBK_Db_Utils::increeaseCouponUsage( $app_ids[0] );
            }
            
            return TRUE;
        }
    
    }
    
    // update payment status
    static function updatePaymentStatusByIds( $app_ids )
    {
        foreach ( $app_ids as $app_id ) {
            if ( !WBK_Validator::validateId( $app_id, 'wbk_appointments' ) ) {
                continue;
            }
            global  $wpdb ;
            $result_pending = $wpdb->update(
                get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
                array(
                'status' => 'paid',
            ),
                array(
                'id'     => $app_id,
                'status' => 'pending',
            ),
                array( '%s' ),
                array( '%d', '%s' )
            );
            $result_approved = $wpdb->update(
                get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
                array(
                'status' => 'paid_approved',
            ),
                array(
                'id'     => $app_id,
                'status' => 'approved',
            ),
                array( '%s' ),
                array( '%d', '%s' )
            );
            $result_pending = $wpdb->update(
                get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
                array(
                'prev_status' => 'paid',
            ),
                array(
                'id'          => $app_id,
                'prev_status' => 'pending',
            ),
                array( '%s' ),
                array( '%d', '%s' )
            );
            $result_approved = $wpdb->update(
                get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
                array(
                'prev_status' => 'paid_approved',
            ),
                array(
                'id'          => $app_id,
                'prev_status' => 'approved',
            ),
                array( '%s' ),
                array( '%d', '%s' )
            );
        }
        $curent_invoice = get_option( 'wbk_email_current_invoice_number', '1' );
        $curent_invoice++;
        update_option( 'wbk_email_current_invoice_number', $curent_invoice );
    }
    
    // update appointment status
    static function updateAppointmentStatus( $appointment_id, $status )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return;
        }
        $result = $wpdb->update(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            array(
            'status' => $status,
        ),
            array(
            'id' => $appointment_id,
        ),
            array( '%s' ),
            array( '%d' )
        );
        
        if ( $result == false || $result == 0 ) {
            return FALSE;
        } else {
            return TRUE;
        }
    
    }
    
    // get indexed names
    static function getIndexedNames( $table )
    {
        global  $wpdb ;
        $table = self::wbk_sanitize( $table );
        $sql = "SELECT id, name from {$table}";
        $sql = apply_filters( 'wbk_get_indexed_names', $sql );
        $result = $wpdb->get_results( $sql );
        return $result;
    }
    
    // get calenadrs related to user
    static function getGgCalendarsByUser( $user_id )
    {
        global  $wpdb ;
        $result = $wpdb->get_results( $wpdb->prepare( "SELECT id, name from " . get_option( 'wbk_db_prefix', '' ) . "wbk_gg_calendars WHERE user_id = %d ", $user_id ) );
        return $result;
    }
    
    static function getEmailTemplate( $id )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $id, 'wbk_email_templates' ) ) {
            return null;
        }
        $result = $wpdb->get_var( $wpdb->prepare( " SELECT template FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_email_templates WHERE id = %d ", $id ) );
        return $result;
    }
    
    // $appointment_id provided to get the date and include in free results
    static function getFreeTimeslotsArray( $appointment_id )
    {
        $result = false;
        if ( !is_numeric( $appointment_id ) ) {
            return $result;
        }
        $service_id = self::getServiceIdByAppointmentId( $appointment_id );
        $service_schedule = new WBK_Service_Schedule();
        if ( !$service_schedule->setServiceId( $service_id ) ) {
            return $result;
        }
        if ( !$service_schedule->load() ) {
            return $result;
        }
        $appointment = new WBK_Appointment_deprecated();
        if ( !$appointment->setId( $appointment_id ) ) {
            return $result;
        }
        if ( !$appointment->load() ) {
            return $result;
        }
        $midnight = $appointment->getDay();
        $day_status = $service_schedule->getDayStatus( $midnight );
        if ( $day_status == 0 ) {
            return $result;
        }
        $service_schedule->buildSchedule( $midnight );
        $result = $service_schedule->getFreeTimeslotsPlusGivenAppointment( $appointment_id, true );
        return $result;
    }
    
    static function getFreeTimeslotsArrayForTable( $appointment_id )
    {
        $result = false;
        if ( !is_numeric( $appointment_id ) ) {
            return $result;
        }
        $service_id = self::getServiceIdByAppointmentId( $appointment_id );
        $service_schedule = new WBK_Service_Schedule();
        if ( !$service_schedule->setServiceId( $service_id ) ) {
            return $result;
        }
        if ( !$service_schedule->load() ) {
            return $result;
        }
        $appointment = new WBK_Appointment_deprecated();
        if ( !$appointment->setId( $appointment_id ) ) {
            return $result;
        }
        if ( !$appointment->load() ) {
            return $result;
        }
        $midnight = $appointment->getDay();
        $day_status = $service_schedule->getDayStatus( $midnight );
        if ( $day_status == 0 ) {
            //	return $result;
        }
        $service_schedule->buildSchedule( $midnight );
        $result = $service_schedule->getFreeTimeslotsPlusGivenAppointment( $appointment_id, true );
        return $result;
    }
    
    // return blank array
    static function blankArray()
    {
        return array();
    }
    
    // create export file
    static function createHtFile()
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . 'export' . DIRECTORY_SEPARATOR . '.htaccess';
        $content = "RewriteEngine On" . "\r\n";
        $content .= "RewriteCond %{HTTP_REFERER} !^" . get_admin_url() . 'admin.php\\?page\\=wbk-appointments' . '.* [NC]' . "\r\n";
        $content .= "RewriteRule .* - [F]";
        if ( !file_exists( $path ) ) {
            file_put_contents( $path, $content );
        }
    }
    
    // appointment status list
    static function getAppointmentStatusList( $condition = null )
    {
        $result = array(
            'pending'       => array( __( 'Awaiting approval', 'wbk' ), '' ),
            'approved'      => array( __( 'Approved', 'wbk' ), '' ),
            'paid'          => array( __( 'Paid (awaiting approval)', 'wbk' ), '' ),
            'paid_approved' => array( __( 'Paid (approved)', 'wbk' ), '' ),
            'arrived'       => array( __( 'Arrived', 'wbk' ), '' ),
            'woocommerce'   => array( __( 'Managed by WooCommerce', 'wbk' ), '' ),
        );
        return $result;
    }
    
    // gg calendar mode list
    static function getGGCalendarModeList( $condition = null )
    {
        $result = array(
            'One-way'        => array( __( 'One-way (export)', 'wbk' ), '' ),
            'One-way-import' => array( __( 'One-way (import)', 'wbk' ), '' ),
            'Two-ways'       => array( __( 'Two-ways', 'wbk' ), '' ),
        );
        return $result;
    }
    
    // delete appointment by email - token pair
    static function deleteAppointmentByEmailTokenPair( $email, $token )
    {
        global  $wpdb ;
        $appointment_id = self::getAppointmentIdByToken( $token );
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return false;
        }
        $count = $wpdb->get_var( $wpdb->prepare( "SELECT count(*) as cnt FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE email = %s and token = %s", $email, $token ) );
        
        if ( $count > 0 ) {
            self::deleteAppointmentDataAtGGCelendar( $appointment_id );
            self::copyAppointmentToCancelled( $appointment_id, __( 'Customer', 'wbk' ) );
        }
        
        $deleted_count = $wpdb->delete( get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments', array(
            'email' => $email,
            'token' => $token,
        ), array( '%s', '%s' ) );
        
        if ( $deleted_count > 0 ) {
            return true;
        } else {
            return false;
        }
    
    }
    
    // clear payment id by token
    static function clearPaymentIdByToken( $token )
    {
        global  $wpdb ;
        $wpdb->update(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            array(
            'payment_id' => '',
        ),
            array(
            'payment_cancel_token' => $token,
        ),
            array( '%s' ),
            array( '%s' )
        );
    }
    
    // get app ids by payment_id
    static function getAppointmentIdsByPaymentId( $payment_id )
    {
        global  $wpdb ;
        $app_ids = $wpdb->get_col( $wpdb->prepare( 'select id from ' . get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments where payment_id = %s', $payment_id ) );
        return $app_ids;
    }
    
    static function setAppointmentsExpiration( $appointment_id )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return;
        }
        $expiration_time = get_option( 'wbk_appointments_expiration_time', '60' );
        if ( !is_numeric( $expiration_time ) ) {
            return;
        }
        if ( intval( $expiration_time ) < 5 ) {
            return;
        }
        $expiration_value = time() + $expiration_time * 60;
        $service_id = self::getServiceIdByAppointmentId( $appointment_id );
        $service = self::initServiceById( $service_id );
        if ( $service != FALSE ) {
            if ( $service->getPrice() == 0 ) {
                $expiration_value = 0;
            }
        }
        $wpdb->update(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            array(
            'expiration_time' => $expiration_value,
        ),
            array(
            'id' => $appointment_id,
        ),
            array( '%d' ),
            array( '%d' )
        );
    }
    
    static function deleteExpiredAppointments()
    {
        global  $wpdb ;
        $time = time();
        $date_format = WBK_Date_Time_Utils::getDateFormat();
        
        if ( get_option( 'wbk_appointments_delete_not_paid_mode', 'disabled' ) != 'disabled' ) {
            $delete_rule = get_option( 'wbk_appointments_delete_payment_started', 'skip' );
            
            if ( $delete_rule == 'skip' ) {
                $ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments where ( payment_id = '' or payment_id IS NULL ) and  ( status='pending' or status='approved'  ) and ( ( payment_method <> 'Pay on arrival' and payment_method <> 'Bank transfer' ) or payment_method IS NULL ) and  expiration_time <> 0 and expiration_time < %d", $time ) );
            } elseif ( $delete_rule == 'delete' ) {
                $ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments where ( status='pending' or status='approved'  ) and ( ( payment_method <> 'Pay on arrival' and payment_method <> 'Bank transfer' ) or payment_method IS NULL ) and expiration_time <> 0 and expiration_time < %d", $time ) );
            }
            
            $valid_ids = array();
            foreach ( $ids as $appointment_id ) {
                if ( WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
                    $valid_ids[] = $appointment_id;
                }
            }
            
            if ( $delete_rule == 'skip' ) {
                if ( count( $valid_ids ) > 0 ) {
                    foreach ( $valid_ids as $app_id ) {
                        $service_id = self::getServiceIdByAppointmentId( $app_id );
                        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
                        $noifications = new WBK_Email_Notifications( $service_id, $app_id );
                        $lang = get_option( 'WPLANG' );
                        $current_locale = get_locale();
                        if ( $lang != '' ) {
                            switch_to_locale( $lang );
                        }
                        $noifications->prepareOnCancelCustomer();
                        $noifications->prepareOnCancel();
                        self::deleteAppointmentDataAtGGCelendar( $app_id );
                        self::copyAppointmentToCancelled( $app_id, __( 'Auto', 'wbk' ) );
                        $wpdb->query( $wpdb->prepare( "DELETE FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments where ( payment_id = '' or payment_id IS NULL ) and  ( ( payment_method <> 'Pay on arrival' and payment_method <> 'Bank transfer' ) or payment_method IS NULL ) and ( status='pending' or status='approved'  ) and  expiration_time <> 0 and expiration_time < %d and id=" . $app_id, $time ) );
                        $noifications->sendOnCancelCustomer();
                        $noifications->sendOnCancel();
                        switch_to_locale( $current_locale );
                        date_default_timezone_set( 'UTC' );
                    }
                }
            } elseif ( $delete_rule == 'delete' ) {
                if ( count( $valid_ids ) > 0 ) {
                    foreach ( $valid_ids as $app_id ) {
                        $service_id = self::getServiceIdByAppointmentId( $app_id );
                        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
                        $noifications = new WBK_Email_Notifications( $service_id, $app_id );
                        $lang = get_option( 'WPLANG' );
                        $current_locale = get_locale();
                        if ( $lang != '' ) {
                            switch_to_locale( $lang );
                        }
                        $noifications->prepareOnCancelCustomer();
                        $noifications->prepareOnCancel();
                        self::deleteAppointmentDataAtGGCelendar( $app_id );
                        self::copyAppointmentToCancelled( $app_id, __( 'Auto', 'wbk' ) );
                        $wpdb->query( $wpdb->prepare( "DELETE FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments where  ( status='pending' or status='approved'  ) and ( ( payment_method <> 'Pay on arrival' and payment_method <> 'Bank transfer' ) or payment_method IS NULL ) and expiration_time <> 0 and expiration_time < %d and id=" . $app_id, $time ) );
                        $noifications->sendOnCancelCustomer();
                        $noifications->sendOnCancel();
                        switch_to_locale( $current_locale );
                        date_default_timezone_set( 'UTC' );
                    }
                }
            }
        
        }
        
        $pending_expiration = get_option( 'wbk_appointments_expiration_time_pending', 0 );
        
        if ( WBK_Validator::checkInteger( $pending_expiration, 5, 500000 ) ) {
            $old_point = time() - $pending_expiration * 60;
            $ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments where ( status='pending' ) and created_on  < %d", $old_point ) );
            $valid_ids = array();
            foreach ( $ids as $appointment_id ) {
                if ( WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
                    $valid_ids[] = $appointment_id;
                }
                self::deleteAppointmentDataAtGGCelendar( $appointment_id );
                self::copyAppointmentToCancelled( $appointment_id, __( 'Auto', 'wbk' ) );
            }
            if ( count( $valid_ids ) > 0 ) {
                foreach ( $valid_ids as $app_id ) {
                    $lang = get_option( 'WPLANG' );
                    $current_locale = get_locale();
                    if ( $lang != '' ) {
                        switch_to_locale( $lang );
                    }
                    $service_id = self::getServiceIdByAppointmentId( $app_id );
                    $noifications = new WBK_Email_Notifications( $service_id, $appointment_id );
                    date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
                    $noifications->prepareOnCancelCustomer();
                    $noifications->prepareOnCancel();
                    $wpdb->query( $wpdb->prepare( "DELETE FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments where  ( status='pending' )   and created_on  < %d and id  = " . $app_id, $old_point ) );
                    $noifications->sendOnCancelCustomer();
                    $noifications->sendOnCancel();
                    switch_to_locale( $current_locale );
                    date_default_timezone_set( 'UTC' );
                }
            }
        }
        
        
        if ( get_option( 'wbk_gdrp', 'disabled' ) == 'enabled' ) {
            $sql = "DELETE FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE end < %d";
            $sql = apply_filters( 'wbk_gdpr_query', $sql );
            $wpdb->query( $wpdb->prepare( $sql, $time ) );
            $sql = "DELETE FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_cancelled_appointments WHERE time < %d";
            $sql = apply_filters( 'wbk_gdpr_query', $sql );
            $wpdb->query( $wpdb->prepare( $sql, $time ) );
        }
    
    }
    
    static function getQuantityFromConnectedServices( $service_id, $start, $end )
    {
        if ( get_option( 'wbk_appointments_auto_lock', 'disabled' ) == 'disabled' ) {
            return 0;
        }
        $autolock_mode = get_option( 'wbk_appointments_auto_lock_mode', 'all' );
        $arrIds = array();
        
        if ( $autolock_mode == 'all' ) {
            $arrIds = WBK_Db_Utils::getServices();
        } elseif ( $autolock_mode == 'categories' ) {
            $arrIds = WBK_Db_Utils::getServicesWithSameCategory( $service_id );
        }
        
        $total_quantity = 0;
        foreach ( $arrIds as $service_id_this ) {
            if ( $service_id_this == $service_id ) {
                continue;
            }
            $service_this = new WBK_Service_deprecated();
            if ( !$service_this->setId( $service_id_this ) ) {
                continue;
            }
            if ( !$service_this->load() ) {
                continue;
            }
            $service_schedule = new WBK_Service_Schedule();
            if ( !$service_schedule->setServiceId( $service_id_this ) ) {
                continue;
            }
            if ( !$service_schedule->load() ) {
                continue;
            }
            $midnight = strtotime( 'today', $start );
            $service_schedule->buildSchedule( $midnight );
            $timeslots = $service_schedule->getTimeSlots();
            foreach ( $timeslots as $timeslot ) {
                $this_start = $timeslot->getStart();
                $this_end = $timeslot->getStart() + $service_this->getDuration() * 60 + $service_this->getInterval() * 60;
                $intersect = false;
                if ( $this_start == $start ) {
                    $intersect = true;
                }
                if ( $this_start > $start && $this_start < $end ) {
                    $intersect = true;
                }
                if ( $this_end > $start && $this_end <= $end ) {
                    $intersect = true;
                }
                if ( $intersect == true ) {
                    
                    if ( is_array( $timeslot->getStatus() ) ) {
                        foreach ( $timeslot->getStatus() as $this_app_id ) {
                            $total_quantity += intval( self::getQuantityByAppointmentId( $this_app_id ) );
                        }
                    } elseif ( $timeslot->getStatus() > 0 ) {
                        $total_quantity += intval( self::getQuantityByAppointmentId( $timeslot->getStatus() ) );
                    }
                
                }
            }
        }
        return $total_quantity;
    }
    
    static function getQuantityFromConnectedServices2( $service_id, $time, $use_beforeafter_rules = false )
    {
        if ( get_option( 'wbk_appointments_auto_lock', 'disabled' ) == 'disabled' ) {
            return 0;
        }
        $service = self::initServiceById( $service_id );
        $end = $time + $service->getDuration() * 60 + $service->getInterval() * 60;
        $autolock_mode = get_option( 'wbk_appointments_auto_lock_mode', 'all' );
        $arrIds = array();
        
        if ( $autolock_mode == 'all' ) {
            $arrIds = WBK_Db_Utils::getServices();
        } elseif ( $autolock_mode == 'categories' ) {
            $arrIds = WBK_Db_Utils::getServicesWithSameCategory( $service_id );
        }
        
        $total_quantity = 0;
        foreach ( $arrIds as $service_id_this ) {
            if ( $service_id_this == $service_id ) {
                continue;
            }
            $service_schedule = new WBK_Service_Schedule();
            if ( !$service_schedule->setServiceId( $service_id_this ) ) {
                continue;
            }
            $service_this = self::initServiceById( $service_id_this );
            $service_schedule->parital_load1();
            $day = strtotime( date( 'Y-m-d', $time ) . ' 00:00:00' );
            $service_schedule->loadAppointmentsDay( $day );
            $appointments = $service_schedule->getAppointment();
            $night_houts_addon = get_option( 'wbk_night_hours', '0' ) * 60 * 60;
            
            if ( $night_houts_addon > 0 ) {
                $day = strtotime( date( 'Y-m-d', $time ) . ' 00:00:00' );
                $service_schedule->loadAppointmentsDay( $day - 86400 );
                $appointments_before = $service_schedule->getAppointment();
                $appointments = array_merge( $appointments, $appointments_before );
                $day = strtotime( date( 'Y-m-d', $time ) . ' 00:00:00' );
                $service_schedule->loadAppointmentsDay( $day + 86400 );
                $appointments_after = $service_schedule->getAppointment();
                $appointments = array_merge( $appointments, $appointments_after );
            }
            
            foreach ( $appointments as $appointment ) {
                $start_cur = $appointment->getTime();
                $end_cur = $start_cur + $service_this->getDuration() * 60 + $service_this->getInterval() * 60;
                
                if ( $use_beforeafter_rules ) {
                    $add_before_after = get_option( 'wbk_appointments_lock_one_before_and_one_after', '' );
                    if ( is_array( $add_before_after ) ) {
                        
                        if ( in_array( $service_id_this, $add_before_after ) ) {
                            $start_cur -= $service_this->getDuration() * 60;
                            $end_cur += $service_this->getDuration() * 60;
                        }
                    
                    }
                }
                
                if ( WBK_Date_Time_Utils::chekRangeIntersect(
                    $time,
                    $end,
                    $start_cur,
                    $end_cur
                ) == TRUE ) {
                    $total_quantity += $appointment->getQuantity();
                }
            }
        }
        return $total_quantity;
    }
    
    static function getQuantityFromAllSerivces( $start, $end )
    {
        $arrIds = array();
        $arrIds = WBK_Db_Utils::getServices();
        $total_quantity = 0;
        $day = strtotime( date( 'Y-m-d', $start ) . ' 00:00:00' );
        foreach ( $arrIds as $service_id_this ) {
            $service_schedule = new WBK_Service_Schedule();
            if ( !$service_schedule->setServiceId( $service_id_this ) ) {
                continue;
            }
            $service_this = self::initServiceById( $service_id_this );
            $service_schedule->parital_load1();
            $service_schedule->loadAppointmentsDay( $day );
            $appointments = $service_schedule->getAppointment();
            foreach ( $appointments as $appointment ) {
                $start_cur = $appointment->getTime();
                $end_cur = $start_cur + $service_this->getDuration() * 60 + $service_this->getInterval() * 60;
                if ( WBK_Date_Time_Utils::chekRangeIntersect(
                    $start,
                    $end,
                    $start_cur,
                    $end_cur
                ) == TRUE ) {
                    $total_quantity += $appointment->getQuantity();
                }
            }
        }
        return $total_quantity;
    }
    
    static function getFeatureAppointmentsByService( $service_id )
    {
        global  $wpdb ;
        $time = time();
        $app_ids = $wpdb->get_col( $wpdb->prepare( "SELECT id from " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments where service_id = %d AND time > %d order by time asc", $service_id, $time ) );
        return $app_ids;
    }
    
    static function getFeatureAppointmentsByCategory( $category_id )
    {
        global  $wpdb ;
        $time = time();
        $result = array();
        $service_ids = self::getServicesInCategory( $category_id );
        foreach ( $service_ids as $service_id ) {
            $app_ids = $wpdb->get_col( $wpdb->prepare( "SELECT id from " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments where service_id = %d AND time > %d order by time asc", $service_id, $time ) );
            $result = array_merge( $result, $app_ids );
        }
        return $result;
    }
    
    public static function booked_slot_placeholder_processing( $appointment_id )
    {
        $text = get_option( 'wbk_booked_text', '' );
        $appointment = new WBK_Appointment_deprecated();
        if ( !$appointment->setId( $appointment_id ) ) {
            return '';
        }
        if ( !$appointment->load() ) {
            return '';
        }
        $customer_name = $appointment->getName();
        $text = str_replace( '#username', $customer_name, $text );
        $text = str_replace( '#time', '', $text );
        $text = WBK_Db_Utils::subject_placeholder_processing( $text, $appointment, FALSE );
        return $text;
    }
    
    public static function message_placeholder_processing_multi_service(
        $message,
        $appointment,
        $total_amount = null,
        $current_category = 0,
        $multi_token = null,
        $multi_token_admin = null,
        $app_total_price = null
    )
    {
        $service = self::initServiceById( self::getServiceIdByAppointmentId( $appointment->getId() ) );
        return WBK_Db_Utils::message_placeholder_processing(
            $message,
            $appointment,
            $service,
            $total_amount,
            $current_category,
            $multi_token,
            $multi_token_admin,
            $app_total_price
        );
    }
    
    public static function message_placeholder_processing(
        $message,
        $appointment,
        $service,
        $total_amount = null,
        $current_category = 0,
        $multi_token = null,
        $multi_token_admin = null,
        $app_total_price = null
    )
    {
        $current_category = self::getCurrentCateogryByAppointmentId( $appointment->getId() );
        $tax_amount = 0;
        $subtotal_amount = 0;
        $date_format = WBK_Date_Time_Utils::getDateFormat();
        $time_format = WBK_Date_Time_Utils::getTimeFormat();
        // begin landing for payment and cancelation
        $payment_link_url = get_option( 'wbk_email_landing', '' );
        $payment_link_text = get_option( 'wbk_email_landing_text', '' );
        if ( $payment_link_text == '' ) {
            $payment_link_text = sanitize_text_field( $wbk_wording['email_landing_anchor'] );
        }
        $cancel_link_text = get_option( 'wbk_email_landing_text_cancel', '' );
        if ( $cancel_link_text == '' ) {
            $cancel_link_text = sanitize_text_field( $wbk_wording['email_landing_anchor2'] );
        }
        $gg_add_link_text = get_option( 'wbk_email_landing_text_gg_event_add', __( 'Click here to add this event to your Google Calendar.', 'wbk' ) );
        if ( $gg_add_link_text == '' ) {
            $gg_add_link_text = sanitize_text_field( $wbk_wording['wbk_email_landing_text_gg_event_add'] );
        }
        $payment_link = '';
        $cancel_link = '';
        $gg_add_link = '';
        $payment_token = '';
        
        if ( $payment_link_url != '' ) {
            
            if ( $multi_token == null ) {
                $token = WBK_Db_Utils::getTokenByAppointmentId( $appointment->getId() );
                $status = self::getStatusByAppointmentId( $appointment->getId() );
                if ( $status == 'pending' || $status == 'approved' ) {
                    $payment_token = $token;
                }
            } else {
                $token = $multi_token;
                $payment_token = $token;
            }
            
            
            if ( $token != false ) {
                
                if ( $payment_token != '' ) {
                    $payment_link = '<a target="_blank" target="_blank" href="' . $payment_link_url . '?order_payment=' . $payment_token . '">' . trim( $payment_link_text ) . '</a>';
                } else {
                    $payment_link = '';
                }
                
                $cancel_link = '<a target="_blank" target="_blank" href="' . $payment_link_url . '?cancelation=' . $token . '">' . trim( $cancel_link_text ) . '</a>';
                $gg_add_link = '<a target="_blank" target="_blank" href="' . $payment_link_url . '?ggeventadd=' . $token . '">' . trim( $gg_add_link_text ) . '</a>';
            }
        
        }
        
        // end landing for payment
        // begin admin management links
        $admin_cancel_link = '';
        $admin_approve_link = '';
        $admin_cancel_link_text = get_option( 'wbk_email_landing_text_cancel_admin', __( 'Click here to cancel this booking.', 'wbk' ) );
        $admin_approve_link_text = get_option( 'wbk_email_landing_text_approve_admin', __( 'Click here to approve this booking.', 'wbk' ) );
        if ( get_option( 'wbk_allow_manage_by_link', 'no' ) == 'yes' ) {
            
            if ( $payment_link_url != '' ) {
                
                if ( $multi_token_admin == null ) {
                    $token = WBK_Db_Utils::getAdminTokenByAppointmentId( $appointment->getId() );
                } else {
                    $token = $multi_token_admin;
                }
                
                
                if ( $token != false ) {
                    $admin_cancel_link = '<a target="_blank" target="_blank" href="' . $payment_link_url . '?admin_cancel=' . $token . '">' . trim( $admin_cancel_link_text ) . '</a>';
                    $admin_approve_link = '<a target="_blank" target="_blank" href="' . $payment_link_url . '?admin_approve=' . $token . '">' . trim( $admin_approve_link_text ) . '</a>';
                }
            
            }
        
        }
        // end admin management links
        // begin total amount
        // processing discounts (coupons)
        $coupon_id = (int) WBK_Db_Utils::getCouponByAppointmentId( $appointment->getId() );
        $discount_data = null;
        
        if ( $coupon_id != 0 ) {
            $discount_data = WBK_Db_Utils::getCouponDiscount( $coupon_id );
            $coupon_name = WBK_Db_Utils::getCouponName( $coupon_id );
        } else {
            $coupon_name = '';
        }
        
        
        if ( is_null( $total_amount ) ) {
            $total_price = '';
            $payment_methods = json_decode( $service->getPayementMethods() );
            
            if ( is_array( $payment_methods ) && count( $payment_methods ) > 0 ) {
                $booking = new WBK_Booking( $appointment->getId() );
                $total = $booking->get_price() * $booking->get_quantity();
                $service_fee = WBK_Price_Processor::get_servcie_fees( array( $booking->get_id() ) );
                if ( get_option( 'wbk_do_not_tax_deposit', '' ) != 'true' ) {
                    $total += $service_fee[0];
                }
                if ( !is_null( $discount_data ) ) {
                    
                    if ( $discount_data[0] > 0 ) {
                        $total -= $discount_data[0];
                    } else {
                        $total -= $total * ($discount_data[1] / 100);
                    }
                
                }
                $subtotal_amount = $total;
                $price_format = get_option( 'wbk_payment_price_format', '$#price' );
                $tax_rule = get_option( 'wbk_tax_for_messages', 'paypal' );
                if ( $tax_rule == 'paypal' ) {
                    $tax = get_option( 'wbk_paypal_tax', 0 );
                }
                if ( $tax_rule == 'stripe' ) {
                    $tax = get_option( 'wbk_stripe_tax', 0 );
                }
                if ( $tax_rule == 'none' ) {
                    $tax = 0;
                }
                
                if ( is_numeric( $tax ) && $tax > 0 ) {
                    $tax_amount = $total / 100 * $tax;
                    $total = $total + $tax_amount;
                } else {
                    $tax_amount = 0;
                }
                
                if ( get_option( 'wbk_do_not_tax_deposit', '' ) == 'true' ) {
                    $total += $service_fee[0];
                }
                $total_price = str_replace( '#price', number_format(
                    $total,
                    get_option( 'wbk_price_fractional', '2' ),
                    get_option( 'wbk_price_separator', '.' ),
                    ''
                ), $price_format );
            }
        
        } else {
            
            if ( !is_null( $discount_data ) ) {
                $total_amount = self::priceToFloat( $total_amount );
                
                if ( $discount_data[0] > 0 ) {
                    $total_amount -= $discount_data[0];
                } else {
                    $total_amount -= $total_amount * ($discount_data[1] / 100);
                }
                
                $price_format = get_option( 'wbk_payment_price_format', '$#price' );
                $total_amount = str_replace( '#price', number_format(
                    $total_amount,
                    get_option( 'wbk_price_fractional', '2' ),
                    get_option( 'wbk_price_separator', '.' ),
                    ''
                ), $price_format );
            }
            
            $tax_rule = get_option( 'wbk_tax_for_messages', 'paypal' );
            if ( $tax_rule == 'paypal' ) {
                $tax = get_option( 'wbk_paypal_tax', 0 );
            }
            if ( $tax_rule == 'stripe' ) {
                $tax = get_option( 'wbk_stripe_tax', 0 );
            }
            if ( $tax_rule == 'none' ) {
                $tax = 0;
            }
            
            if ( is_numeric( $tax ) && $tax > 0 ) {
                $tax_amount = self::priceToFloat( $total_amount ) / (100 + $tax) * $tax;
                $subtotal_amount = self::priceToFloat( $total_amount ) - $tax_amount;
            } else {
                $tax_amount = 0;
                $subtotal_amount = self::priceToFloat( $total_amount );
            }
        
        }
        
        // end total amount
        $price_format = get_option( 'wbk_payment_price_format', '$#price' );
        $tax_amount = str_replace( '#price', number_format(
            $tax_amount,
            get_option( 'wbk_price_fractional', '2' ),
            get_option( 'wbk_price_separator', '.' ),
            ''
        ), $price_format );
        $subtotal_amount = str_replace( '#price', number_format(
            $subtotal_amount,
            get_option( 'wbk_price_fractional', '2' ),
            get_option( 'wbk_price_separator', '.' ),
            ''
        ), $price_format );
        // beging extra data
        $extra_data = trim( $appointment->getExtra() );
        
        if ( $extra_data != '' ) {
            $extra = json_decode( $extra_data );
            foreach ( $extra as $item ) {
                if ( count( $item ) != 3 ) {
                    continue;
                }
                $custom_placeholder = '#field_' . $item[0];
                $message = str_replace( $custom_placeholder, $item[2], $message );
            }
        }
        
        // end extra data
        
        if ( $current_category == 0 ) {
            $current_category_name = '';
        } else {
            $current_category_name = WBK_Db_Utils::getCategoryNameByCategoryId( $current_category );
            if ( $current_category_name == false ) {
                $current_category_name = '';
            }
        }
        
        $status = self::getStatusByAppointmentId( $appointment->getId() );
        $status_list = self::getAppointmentStatusList();
        
        if ( isset( $status_list[$status] ) ) {
            $status = $status_list[$status][0];
        } else {
            $status = '';
        }
        
        $paymnent_method = WBK_Db_Utils::getPaymentMethodByAppointmentId( $appointment->getId() );
        $short_token = WBK_Db_Utils::getTokenByAppointmentId( $appointment->getId() );
        
        if ( strlen( $short_token ) >= 10 ) {
            $short_token = strtoupper( substr( $short_token, 0, 10 ) );
        } else {
            $short_token = '';
        }
        
        $created_on = self::getAppointmentCreatedOn( $appointment->getId() );
        $attachment = '';
        
        if ( get_option( 'wbk_allow_attachemnt', 'no' ) == 'yes' ) {
            $attachment = $appointment->getAttachment();
            
            if ( $attachment !== '' ) {
                $attachment = json_decode( $attachment );
                
                if ( is_array( $attachment ) ) {
                    $attachment = $attachment[0];
                    $parts = explode( 'wp-content', $attachment );
                    $attachment = rtrim( site_url(), '/' ) . '/wp-content/' . ltrim( $parts[1], '/' );
                    $attachment = '<a rel="noopener" target="_blank" href="' . $attachment . '">' . $attachment . '</a>';
                }
            
            }
        
        }
        
        $message = str_replace( '#attachment', $attachment, $message );
        $message = str_replace( '#coupon', $coupon_name, $message );
        $message = str_replace( '#service_description', $service->getDescription(), $message );
        $message = str_replace( '#booked_on_date', wp_date( $date_format, $created_on, new DateTimeZone( date_default_timezone_get() ) ), $message );
        $message = str_replace( '#booked_on_time', wp_date( $time_format, $created_on, new DateTimeZone( date_default_timezone_get() ) ), $message );
        $message = str_replace( '#uniqueid', $short_token, $message );
        $message = str_replace( '#payment_method', $paymnent_method, $message );
        $user_ip = WBK_Db_Utils::getIPByAppointmentId( $appointment->getId() );
        $message = str_replace( '#user_ip', $user_ip, $message );
        $message = str_replace( '#status', $status, $message );
        $message = str_replace( '#cancel_link', $cancel_link, $message );
        $message = str_replace( '#payment_link', $payment_link, $message );
        $message = str_replace( '#add_event_link', $gg_add_link, $message );
        $message = str_replace( '#admin_cancel_link', $admin_cancel_link, $message );
        $message = str_replace( '#admin_approve_link', $admin_approve_link, $message );
        
        if ( is_null( $total_amount ) ) {
            $message = str_replace( '#total_amount', $total_price, $message );
        } else {
            $message = str_replace( '#total_amount', $total_amount, $message );
        }
        
        $message = str_replace( '#subtotal_amount', $subtotal_amount, $message );
        $message = str_replace( '#tax_amount', $tax_amount, $message );
        $category_names = WBK_Db_Utils::getCategoryNamesByService( $service->getId() );
        $message = str_replace( '#category_names', $category_names, $message );
        $message = str_replace( '#current_category_name', $current_category_name, $message );
        
        if ( function_exists( 'pll__' ) ) {
            $message = str_replace( '#service_name', pll__( $service->getName() ), $message );
        } else {
            $message = str_replace( '#service_name', $service->getName(), $message );
        }
        
        $message = str_replace( '#duration', $service->getDuration(), $message );
        $message = str_replace( '#customer_name', $appointment->getName(), $message );
        $message = str_replace( '#appointment_day', wp_date( $date_format, $appointment->getDay(), new DateTimeZone( date_default_timezone_get() ) ), $message );
        $message = str_replace( '#appointment_time', wp_date( $time_format, $appointment->getTime(), new DateTimeZone( date_default_timezone_get() ) ), $message );
        $message = str_replace( '#appointment_local_time', wp_date( $time_format, $appointment->getLocalTime(), new DateTimeZone( date_default_timezone_get() ) ), $message );
        $message = str_replace( '#appointment_local_date', wp_date( $date_format, $appointment->getLocalTime(), new DateTimeZone( date_default_timezone_get() ) ), $message );
        $message = str_replace( '#customer_phone', $appointment->getPhone(), $message );
        $message = str_replace( '#customer_email', $appointment->getEmail(), $message );
        $message = str_replace( '#customer_comment', $appointment->getDescription(), $message );
        $message = str_replace( '#items_count', $appointment->getQuantity(), $message );
        $message = str_replace( '#appointment_id', $appointment->getId(), $message );
        $message = str_replace( '#customer_custom', $appointment->getFormatedExtra(), $message );
        $time_range = wp_date( $time_format, $appointment->getTime(), new DateTimeZone( date_default_timezone_get() ) ) . ' - ' . wp_date( $time_format, $appointment->getTime() + $service->getDuration() * 60, new DateTimeZone( date_default_timezone_get() ) );
        $message = str_replace( '#time_range', $time_range, $message );
        $message = str_replace( '#invoice_number', get_option( 'wbk_email_current_invoice_number', '1' ), $message );
        $price_format = get_option( 'wbk_payment_price_format', '$#price' );
        $one_slot_price = str_replace( '#price', number_format(
            $service->getPrice( $appointment->getTime() ),
            get_option( 'wbk_price_fractional', '2' ),
            get_option( 'wbk_price_separator', '.' ),
            ''
        ), $price_format );
        $message = str_replace( '#one_slot_price', $one_slot_price, $message );
        $moment_price = WBK_Db_Utils::getAppointmentMomentPrice( $appointment->getId() );
        $moment_price = str_replace( '#price', number_format(
            $moment_price,
            get_option( 'wbk_price_fractional', '2' ),
            get_option( 'wbk_price_separator', '.' ),
            ''
        ), $price_format );
        $message = str_replace( '#appprice', $moment_price, $message );
        
        if ( !is_null( $app_total_price ) ) {
            $app_total_price = str_replace( '#price', number_format(
                $app_total_price,
                get_option( 'wbk_price_fractional', '2' ),
                get_option( 'wbk_price_separator', '.' ),
                ''
            ), $price_format );
            $message = str_replace( '#apptotalprice', $app_total_price, $message );
        }
        
        $dynamic_placehodlers = get_option( 'wbk_general_dynamic_placeholders' );
        
        if ( $dynamic_placehodlers != '' ) {
            $items = explode( ',', $dynamic_placehodlers );
            if ( is_array( $items ) ) {
                foreach ( $items as $item ) {
                    $message = str_replace( trim( $item ), '', $message );
                }
            }
        }
        
        return $message;
    }
    
    public static function subject_placeholder_processing_multi_service(
        $message,
        $appointment,
        $total_amount = null,
        $current_category = 0
    )
    {
        $service = self::initServiceById( self::getServiceIdByAppointmentId( $appointment->getId() ) );
        return self::subject_placeholder_processing(
            $message,
            $appointment,
            $service,
            $total_amount,
            $current_category
        );
    }
    
    public static function subject_placeholder_processing(
        $message,
        $appointment,
        $service,
        $total_amount = null,
        $current_category = 0
    )
    {
        $current_category = self::getCurrentCateogryByAppointmentId( $appointment->getId() );
        $tax_amount = 0;
        $subtotal_amount = 0;
        global  $wbk_wording ;
        
        if ( $service === FALSE ) {
            $service = new WBK_Service_deprecated();
            if ( !$service->setId( $appointment->getService() ) ) {
                return $message;
            }
            if ( !$service->load() ) {
                return $message;
            }
        }
        
        $date_format = WBK_Date_Time_Utils::getDateFormat();
        $time_format = WBK_Date_Time_Utils::getTimeFormat();
        $coupon_id = (int) WBK_Db_Utils::getCouponByAppointmentId( $appointment->getId() );
        $discount_data = null;
        
        if ( $coupon_id != 0 ) {
            $discount_data = WBK_Db_Utils::getCouponDiscount( $coupon_id );
            $coupon_name = WBK_Db_Utils::getCouponName( $coupon_id );
        } else {
            $coupon_name = '';
        }
        
        // begin total amount
        
        if ( is_null( $total_amount ) ) {
            $total_price = '';
            $payment_methods = json_decode( $service->getPayementMethods() );
            
            if ( is_array( $payment_methods ) && count( $payment_methods ) > 0 ) {
                $total = $appointment->getQuantity() * $service->getPrice();
                if ( !is_null( $discount_data ) ) {
                    
                    if ( $discount_data[0] > 0 ) {
                        $total -= $discount_data[0];
                    } else {
                        $total -= $total * ($discount_data[1] / 100);
                    }
                
                }
                $subtotal_amount = $total;
                $price_format = get_option( 'wbk_payment_price_format', '$#price' );
                $tax_rule = get_option( 'wbk_tax_for_messages', 'paypal' );
                if ( $tax_rule == 'paypal' ) {
                    $tax = get_option( 'wbk_paypal_tax', 0 );
                }
                if ( $tax_rule == 'stripe' ) {
                    $tax = get_option( 'wbk_stripe_tax', 0 );
                }
                if ( $tax_rule == 'none' ) {
                    $tax = 0;
                }
                
                if ( is_numeric( $tax ) && $tax > 0 ) {
                    $tax_amount = $total / 100 * $tax;
                    $total = $total + $tax_amount;
                } else {
                    $tax_amount = 0;
                }
                
                $total_price = str_replace( '#price', number_format(
                    $total,
                    get_option( 'wbk_price_fractional', '2' ),
                    get_option( 'wbk_price_separator', '.' ),
                    ''
                ), $price_format );
            }
        
        } else {
            
            if ( !is_null( $discount_data ) ) {
                $total_amount = self::priceToFloat( $total_amount );
                
                if ( $discount_data[0] > 0 ) {
                    $total_amount -= $discount_data[0];
                } else {
                    $total_amount -= $total_amount * ($discount_data[1] / 100);
                }
                
                $price_format = get_option( 'wbk_payment_price_format', '$#price' );
                $total_amount = str_replace( '#price', number_format(
                    $total_amount,
                    get_option( 'wbk_price_fractional', '2' ),
                    get_option( 'wbk_price_separator', '.' ),
                    ''
                ), $price_format );
            }
            
            $tax_rule = get_option( 'wbk_tax_for_messages', 'paypal' );
            if ( $tax_rule == 'paypal' ) {
                $tax = get_option( 'wbk_paypal_tax', 0 );
            }
            if ( $tax_rule == 'stripe' ) {
                $tax = get_option( 'wbk_stripe_tax', 0 );
            }
            if ( $tax_rule == 'none' ) {
                $tax = 0;
            }
            
            if ( is_numeric( $tax ) && $tax > 0 ) {
                $tax_amount = self::priceToFloat( $total_amount ) / (100 + $tax) * $tax;
                $subtotal_amount = self::priceToFloat( $total_amount ) - $tax_amount;
            }
        
        }
        
        // end total amount
        $price_format = get_option( 'wbk_payment_price_format', '$#price' );
        $tax_amount = str_replace( '#price', number_format(
            $tax_amount,
            get_option( 'wbk_price_fractional', '2' ),
            get_option( 'wbk_price_separator', '.' ),
            ''
        ), $price_format );
        $subtotal_amount = str_replace( '#price', number_format(
            $subtotal_amount,
            get_option( 'wbk_price_fractional', '2' ),
            get_option( 'wbk_price_separator', '.' ),
            ''
        ), $price_format );
        // beging extra data
        $extra_data = trim( $appointment->getExtra() );
        
        if ( $extra_data != '' ) {
            $extra = json_decode( $extra_data );
            foreach ( $extra as $item ) {
                if ( count( $item ) != 3 ) {
                    continue;
                }
                $custom_placeholder = '#field_' . $item[0];
                $message = str_replace( $custom_placeholder, $item[2], $message );
            }
        }
        
        // end extra data
        
        if ( $current_category == 0 ) {
            $current_category_name = '';
        } else {
            $current_category_name = WBK_Db_Utils::getCategoryNameByCategoryId( $current_category );
            if ( $current_category_name == false ) {
                $current_category_name = '';
            }
        }
        
        
        if ( is_null( $total_amount ) ) {
            $message = str_replace( '#total_amount', $total_price, $message );
        } else {
            $message = str_replace( '#total_amount', $total_amount, $message );
        }
        
        $status = self::getStatusByAppointmentId( $appointment->getId() );
        $status_list = self::getAppointmentStatusList();
        
        if ( isset( $status_list[$status] ) ) {
            $status = $status_list[$status][0];
        } else {
            $status = '';
        }
        
        $user_ip = WBK_Db_Utils::getIPByAppointmentId( $appointment->getId() );
        $short_token = WBK_Db_Utils::getTokenByAppointmentId( $appointment->getId() );
        
        if ( strlen( $short_token ) >= 10 ) {
            $short_token = strtoupper( substr( $short_token, 0, 10 ) );
        } else {
            $short_token = '';
        }
        
        $created_on = self::getAppointmentCreatedOn( $appointment->getId() );
        $message = str_replace( '#booked_on_date', wp_date( $date_format, $created_on, new DateTimeZone( date_default_timezone_get() ) ), $message );
        $message = str_replace( '#booked_on_time', wp_date( $time_format, $created_on, new DateTimeZone( date_default_timezone_get() ) ), $message );
        $message = str_replace( '#coupon', $coupon_name, $message );
        $message = str_replace( '#uniqueid', $short_token, $message );
        $message = str_replace( '#user_ip', $user_ip, $message );
        $message = str_replace( '#status', $status, $message );
        $message = str_replace( '#subtotal_amount', $subtotal_amount, $message );
        $message = str_replace( '#tax_amount', $tax_amount, $message );
        $category_names = WBK_Db_Utils::getCategoryNamesByService( $service->getId() );
        $message = str_replace( '#category_names', $category_names, $message );
        $message = str_replace( '#current_category_name', $current_category_name, $message );
        
        if ( function_exists( 'pll__' ) ) {
            $message = str_replace( '#service_name', pll__( $service->getName() ), $message );
        } else {
            $message = str_replace( '#service_name', $service->getName(), $message );
        }
        
        $message = str_replace( '#customer_name', $appointment->getName(), $message );
        $message = str_replace( '#appointment_day', wp_date( $date_format, $appointment->getDay(), new DateTimeZone( date_default_timezone_get() ) ), $message );
        $message = str_replace( '#appointment_time', wp_date( $time_format, $appointment->getTime(), new DateTimeZone( date_default_timezone_get() ) ), $message );
        $message = str_replace( '#appointment_local_time', wp_date( $time_format, $appointment->getLocalTime(), new DateTimeZone( date_default_timezone_get() ) ), $message );
        $message = str_replace( '#appointment_local_date', wp_date( $date_format, $appointment->getLocalTime(), new DateTimeZone( date_default_timezone_get() ) ), $message );
        $message = str_replace( '#customer_phone', $appointment->getPhone(), $message );
        $message = str_replace( '#customer_email', $appointment->getEmail(), $message );
        $message = str_replace( '#customer_comment', $appointment->getDescription(), $message );
        $message = str_replace( '#items_count', $appointment->getQuantity(), $message );
        $message = str_replace( '#appointment_id', $appointment->getId(), $message );
        $message = str_replace( '#customer_custom', $appointment->getFormatedExtra(), $message );
        $time_range = wp_date( $time_format, $appointment->getTime(), new DateTimeZone( date_default_timezone_get() ) ) . ' - ' . wp_date( $time_format, $appointment->getTime() + $service->getDuration() * 60, new DateTimeZone( date_default_timezone_get() ) );
        $message = str_replace( '#time_range', $time_range, $message );
        $message = str_replace( '#invoice_number', get_option( 'wbk_email_current_invoice_number', '1' ), $message );
        $price_format = get_option( 'wbk_payment_price_format', '$#price' );
        $one_slot_price = str_replace( '#price', number_format(
            $service->getPrice(),
            get_option( 'wbk_price_fractional', '2' ),
            get_option( 'wbk_price_separator', '.' ),
            ''
        ), $price_format );
        $message = str_replace( '#one_slot_price', $one_slot_price, $message );
        $message = str_replace( '#duration', $service->getDuration(), $message );
        $moment_price = WBK_Db_Utils::getAppointmentMomentPrice( $appointment->getId() );
        $moment_price = str_replace( '#price', number_format(
            $moment_price,
            get_option( 'wbk_price_fractional', '2' ),
            get_option( 'wbk_price_separator', '.' ),
            ''
        ), $price_format );
        $message = str_replace( '#appprice', $moment_price, $message );
        return $message;
    }
    
    public static function landing_appointment_data_processing( $text, $appointment, $service )
    {
        $time_format = WBK_Date_Time_Utils::getTimeFormat();
        $date_format = WBK_Date_Time_Utils::getDateFormat();
        $time = $appointment->getTime();
        $end = $appointment->getTime() + $service->getDuration() * 60;
        $text = str_replace( '#name', $appointment->getName(), $text );
        $text = str_replace( '#service', $service->getName(), $text );
        $text = str_replace( '#date', wp_date( $date_format, $time, new DateTimeZone( date_default_timezone_get() ) ), $text );
        $text = str_replace( '#time', wp_date( $time_format, $time, new DateTimeZone( date_default_timezone_get() ) ), $text );
        $text = str_replace( '#start_end', wp_date( $time_format, $time, new DateTimeZone( date_default_timezone_get() ) ) . ' - ' . wp_date( $time_format, $end, new DateTimeZone( date_default_timezone_get() ) ), $text );
        $text = str_replace( '#dt', wp_date( $date_format, $time, new DateTimeZone( date_default_timezone_get() ) ) . ' ' . wp_date( $time_format, $time, new DateTimeZone( date_default_timezone_get() ) ), $text );
        $text = str_replace( '#id', $appointment->getId(), $text );
        return $text;
    }
    
    protected static function get_string_between( $string, $start, $end )
    {
        $string = ' ' . $string;
        $ini = strpos( $string, $start );
        if ( $ini == 0 ) {
            return '';
        }
        $ini += strlen( $start );
        $len = strpos( $string, $end, $ini ) - $ini;
        return substr( $string, $ini, $len );
    }
    
    static function prepareThankYouMessage(
        $appointment_ids,
        $service_id,
        $thanks_message,
        $skipped = null
    )
    {
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        
        if ( get_option( 'wbk_multi_booking', 'disabled' ) != 'disabled' ) {
            $looped = self::get_string_between( $thanks_message, '[appointment_loop_start]', '[appointment_loop_end]' );
            $looped_html = '';
            $token_arr = array();
            // get total price
            $total = 0;
            $app_price_total = 0;
            foreach ( $appointment_ids as $appointment_id ) {
                $appointment = new WBK_Appointment_deprecated();
                
                if ( !$appointment->setId( $appointment_id ) ) {
                    date_default_timezone_set( 'UTC' );
                    return;
                }
                
                
                if ( !$appointment->load() ) {
                    date_default_timezone_set( 'UTC' );
                    return;
                }
                
                $booking = new WBK_Booking( $appointment_id );
                
                if ( $booking->get_name() == '' ) {
                    date_default_timezone_set( 'UTC' );
                    return;
                }
                
                $app_price_total += WBK_Db_Utils::getAppointmentMomentPrice( $appointment->getId() );
                $service = new WBK_Service_deprecated();
                if ( !$service->setId( $appointment->getService() ) ) {
                    return $thanks_message;
                }
                if ( !$service->load() ) {
                    return $thanks_message;
                }
                $total += $booking->get_price() * $booking->get_quantity();
                $looped_html .= WBK_Db_Utils::message_placeholder_processing( $looped, $appointment, $service );
                $token_arr[] = self::getTokenByAppointmentId( $appointment_id );
            }
            $service_fee = WBK_Price_Processor::get_servcie_fees( $appointment_ids );
            if ( get_option( 'wbk_do_not_tax_deposit', '' ) != 'true' ) {
                $total += $service_fee[0];
            }
            
            if ( count( $token_arr ) > 0 ) {
                $multi_token = implode( '-', $token_arr );
            } else {
                $multi_token = null;
            }
            
            $price_format = get_option( 'wbk_payment_price_format', '$#price' );
            $tax_rule = get_option( 'wbk_tax_for_messages', 'paypal' );
            if ( $tax_rule == 'paypal' ) {
                $tax = get_option( 'wbk_paypal_tax', 0 );
            }
            if ( $tax_rule == 'stripe' ) {
                $tax = get_option( 'wbk_stripe_tax', 0 );
            }
            if ( $tax_rule == 'none' ) {
                $tax = 0;
            }
            
            if ( is_numeric( $tax ) && $tax > 0 ) {
                $tax_amount = $total / 100 * $tax;
                $total = $total + $tax_amount;
            }
            
            if ( get_option( 'wbk_do_not_tax_deposit', '' ) == 'true' ) {
                $total += $service_fee[0];
            }
            $total_price = str_replace( '#price', number_format(
                $total,
                get_option( 'wbk_price_fractional', '2' ),
                get_option( 'wbk_price_separator', '.' ),
                ''
            ), $price_format );
            $search_tag = '[appointment_loop_start]' . $looped . '[appointment_loop_end]';
            $thanks_message = str_replace( $search_tag, $looped_html, $thanks_message );
            $thanks_message = str_replace( '#selected_count', count( $appointment_ids ), $thanks_message );
            if ( !is_null( $skipped ) ) {
                $thanks_message = str_replace( '#failed_count', $skipped, $thanks_message );
            }
            $thanks_message = WBK_Db_Utils::message_placeholder_processing(
                $thanks_message,
                $appointment,
                $service,
                $total_price,
                null,
                0,
                $multi_token,
                $app_price_total
            );
        } elseif ( get_option( 'wbk_multi_booking', 'disabled' ) == 'disabled' ) {
            
            if ( count( $appointment_ids ) == 0 ) {
                date_default_timezone_set( 'UTC' );
                return $thanks_message;
            }
            
            $appointment = new WBK_Appointment_deprecated();
            
            if ( !$appointment->setId( $appointment_ids[0] ) ) {
                date_default_timezone_set( 'UTC' );
                return $thanks_message;
            }
            
            
            if ( !$appointment->load() ) {
                date_default_timezone_set( 'UTC' );
                return $thanks_message;
            }
            
            $service = new WBK_Service_deprecated();
            
            if ( !$service->setId( $service_id ) ) {
                date_default_timezone_set( 'UTC' );
                return $thanks_message;
            }
            
            
            if ( !$service->load() ) {
                date_default_timezone_set( 'UTC' );
                return $thanks_message;
            }
            
            $thanks_message = WBK_Db_Utils::message_placeholder_processing( $thanks_message, $appointment, $service );
        }
        
        $thanks_message = self::replaceRanges( $thanks_message, $appointment_ids );
        date_default_timezone_set( 'UTC' );
        return $thanks_message;
    }
    
    static function backend_customer_name_processing( $appointment_id, $customer_name )
    {
        $template = get_option( 'wbk_customer_name_output', '#name' );
        $result = str_replace( '#name', $customer_name, $template );
        $appointment = new WBK_Appointment_deprecated();
        if ( !$appointment->setId( $appointment_id ) ) {
            return $customer_name;
        }
        if ( !$appointment->load() ) {
            return $customer_name;
        }
        $service = new WBK_Service_deprecated();
        if ( !$service->setId( $appointment->getService() ) ) {
            return $customer_name;
        }
        if ( !$service->load() ) {
            return $customer_name;
        }
        $result = WBK_Db_Utils::message_placeholder_processing( $result, $appointment, $service );
        // remove not used custom field placeholders
        $field_parts = explode( '#field_', $result );
        foreach ( $field_parts as $part ) {
            $to_replace = '#field_' . $part;
            $result = str_replace( $to_replace, '', $result );
        }
        return $result;
    }
    
    static function get_extra_value_by_appoiuntment_id( $appointment_id, $field_name )
    {
        return self::getExtraValueByAppointmentId( $appointment_id, $field_name );
    }
    
    static function addAppointmentDataToGGCelendar( $service_id, $appointment_id )
    {
    }
    
    static function addAppointmentDataToCustomerGGCelendar( $service_id, $appointment_ids, $code )
    {
        return FALSE;
    }
    
    static function updateAppointmentDataAtGGCelendar( $appointment_id )
    {
        global  $wpdb ;
        $appointment = new WBK_Appointment_deprecated();
        if ( !$appointment->setId( $appointment_id ) ) {
            return FALSE;
        }
        if ( !$appointment->load() ) {
            return FALSE;
        }
        $service_id = $appointment->getService();
        $service = new WBK_Service_deprecated();
        if ( !$service->setId( $service_id ) ) {
            return FALSE;
        }
        if ( !$service->load() ) {
            return FALSE;
        }
        $event_id_json = $wpdb->get_var( $wpdb->prepare( "SELECT gg_event_id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE id = %d", $appointment_id ) );
        if ( $event_id_json == '' ) {
            return;
        }
        if ( $service->getQuantity() > 1 && get_option( 'wbk_gg_group_service_export', 'event_foreach_appointment' ) == 'one_event' ) {
            return;
        }
        $event_id_arr = json_decode( $event_id_json );
        $title = get_option( 'wbk_gg_calendar_event_title', '#customer_name' );
        $title = apply_filters( 'wbk_gg_calendar_event_title', $title, $service_id );
        $description = get_option( 'wbk_gg_calendar_event_description', '#customer_name #customer_phone' );
        $description = apply_filters( 'wbk_gg_calendar_event_description', $description, $service_id );
        $description = str_replace( '{n}', "\n", $description );
        $title = self::subject_placeholder_processing_gg( $title, $appointment, $service );
        $description = WBK_Db_Utils::message_placeholder_processing_gg( $description, $appointment, $service );
        $time_zone = get_option( 'wbk_timezone', 'UTC' );
        $start = date( 'Y-m-d', $appointment->getTime() ) . 'T' . date( 'H:i:00', $appointment->getTime() );
        $end = date( 'Y-m-d', $appointment->getTime() + $service->getDuration() * 60 + $service->getInterval() * 60 ) . 'T' . date( 'H:i:00', $appointment->getTime() + $service->getDuration() * 60 + $service->getInterval() * 60 );
        foreach ( $event_id_arr as $event ) {
            $google = new WBK_Google();
            $google->init( $event[0] );
            $connect_status = $google->connect();
            
            if ( $connect_status[0] == 1 ) {
                $google->updateEvent(
                    $event[1],
                    $title,
                    $description,
                    $start,
                    $end,
                    $time_zone
                );
            } else {
                $noifications = new WBK_Email_Notifications( $service_id, null );
                $noifications->send_gg_calendar_issue_alert_to_admin();
            }
        
        }
    }
    
    static function deleteAppointmentDataAtGGCelendar( $appointment_id, $by_time = true )
    {
    }
    
    public static function message_placeholder_processing_gg( $message, $appointment, $service )
    {
        return WBK_Db_Utils::message_placeholder_processing( $message, $appointment, $service );
    }
    
    public static function subject_placeholder_processing_gg( $message, $appointment, $service )
    {
        return WBK_Db_Utils::message_placeholder_processing( $message, $appointment, $service );
    }
    
    public static function wbk_sanitize( $value )
    {
        $value = str_replace( '"', '', $value );
        $value = str_replace( '<', '', $value );
        $value = str_replace( '\'', '', $value );
        $value = str_replace( '>', '', $value );
        $value = str_replace( '/', '', $value );
        $value = str_replace( '\\', '', $value );
        $value = str_replace( 'and', '', $value );
        $value = str_replace( 'union', '', $value );
        $value = str_replace( 'delete', '', $value );
        $value = str_replace( 'select', '', $value );
        return $value;
    }
    
    public static function getAppointmentStatus( $appointment_id )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return '';
        }
        $sql = $wpdb->prepare( "SELECT status FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE id = %d", $appointment_id );
        $status = $wpdb->get_var( $sql );
        return $status;
    }
    
    public static function getAppointmentMomentPrice( $appointment_id )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return '';
        }
        $sql = $wpdb->prepare( "SELECT moment_price FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE id = %d", $appointment_id );
        $delimiter = get_option( 'wbk_price_separator', '.' );
        $value = $wpdb->get_var( $sql );
        return self::priceToFloat( $value );
    }
    
    public static function getIPByAppointmentId( $appointment_id )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return '';
        }
        $sql = $wpdb->prepare( "SELECT user_ip FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE id = %d", $appointment_id );
        $status = $wpdb->get_var( $sql );
        return $status;
    }
    
    public static function setAppointmentStatus( $appointment_id, $status )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return;
        }
        $result = $wpdb->update(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            array(
            'status' => $status,
        ),
            array(
            'id' => $appointment_id,
        ),
            array( '%s' ),
            array( '%d' )
        );
        return $result;
    }
    
    public static function is_gg_event_added_to_customers_calendar( $appointment_id )
    {
        global  $wpdb ;
        return FALSE;
    }
    
    // get multiple appointments id by grouped token
    static function getAppointmentIdsByGroupToken( $token )
    {
        global  $wpdb ;
        $arr_tokens = explode( '-', $token );
        $result = array();
        foreach ( $arr_tokens as $token ) {
            $appointment_id = $wpdb->get_var( $wpdb->prepare( " SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE token = %s ", $token ) );
            if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
                continue;
            }
            
            if ( $appointment_id == null ) {
                continue;
            } else {
                $result[] = $appointment_id;
            }
        
        }
        return $result;
    }
    
    static function getAppointmentIdsByGroupAdminToken( $token )
    {
        global  $wpdb ;
        $arr_tokens = explode( '-', $token );
        $result = array();
        foreach ( $arr_tokens as $token ) {
            $appointment_id = $wpdb->get_var( $wpdb->prepare( " SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE admin_token = %s ", $token ) );
            if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
                continue;
            }
            
            if ( $appointment_id == null ) {
                continue;
            } else {
                $result[] = $appointment_id;
            }
        
        }
        return $result;
    }
    
    // set coupon to the appointment
    static function setCouponToAppointment( $appointment_id, $coupon )
    {
        global  $wpdb ;
        if ( !is_numeric( $appointment_id ) ) {
            return FALSE;
        }
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return FALSE;
        }
        $result = $wpdb->update(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            array(
            'coupon' => $coupon,
        ),
            array(
            'id' => $appointment_id,
        ),
            array( '%d' ),
            array( '%d' )
        );
        
        if ( $result == false || $result == 0 ) {
            return FALSE;
        } else {
            return TRUE;
        }
    
    }
    
    static function getCouponByAppointmentId( $appointment_id )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return FALSE;
        }
        $coupon = $wpdb->get_var( $wpdb->prepare( " SELECT coupon FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE id = %d ", $appointment_id ) );
        return $coupon;
    }
    
    // set payment_method to the appointment
    static function setPaymentMethodToAppointment( $appointment_id, $payment_method )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return FALSE;
        }
        $result = $wpdb->update(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            array(
            'payment_method' => $payment_method,
        ),
            array(
            'id' => $appointment_id,
        ),
            array( '%s' ),
            array( '%d' )
        );
        
        if ( $result == false || $result == 0 ) {
            return FALSE;
        } else {
            return TRUE;
        }
    
    }
    
    static function getPaymentMethodByAppointmentId( $appointment_id )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return FALSE;
        }
        $payment_method = $wpdb->get_var( $wpdb->prepare( " SELECT payment_method FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE id = %d ", $appointment_id ) );
        return $payment_method;
    }
    
    static function increeaseCouponUsage( $appointment_id )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return FALSE;
        }
        $coupon = $wpdb->get_var( $wpdb->prepare( " SELECT coupon FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE id = %d ", $appointment_id ) );
        if ( $coupon == 0 ) {
            return;
        }
        $used = $wpdb->get_var( $wpdb->prepare( " SELECT used FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_coupons WHERE id = %d ", $coupon ) );
        if ( $used == null ) {
            $used = 0;
        }
        $used = intval( $used );
        $used++;
        $result = $wpdb->update(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_coupons',
            array(
            'used' => $used,
        ),
            array(
            'id' => $coupon,
        ),
            array( '%d' ),
            array( '%d' )
        );
        
        if ( $result == false || $result == 0 ) {
            return FALSE;
        } else {
            return TRUE;
        }
    
    }
    
    static function getCouponDiscount( $coupon_id )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $coupon_id, 'wbk_coupons' ) ) {
            return FALSE;
        }
        $result = $wpdb->get_row( $wpdb->prepare( " SELECT * FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_coupons WHERE id = %d", $coupon_id ), ARRAY_A );
        if ( $result == NULL ) {
            return FALSE;
        }
        return array( $result['amount_fixed'], $result['amount_percentage'] );
    }
    
    static function initServiceById( $service_id )
    {
        $service = new WBK_Service_deprecated();
        if ( !$service->setId( $service_id ) ) {
            return FALSE;
        }
        if ( !$service->load() ) {
            return FALSE;
        }
        return $service;
    }
    
    static function initAppointmentById( $appointment_id )
    {
        $appointment = new WBK_Appointment_deprecated();
        if ( !$appointment->setId( $appointment_id ) ) {
            return FALSE;
        }
        if ( !$appointment->load() ) {
            return FALSE;
        }
        return $appointment;
    }
    
    static function doCacheForGoogleCalendars()
    {
        $calendar_ids = self::getBackwardGGCalendars();
        $start = date( 'c', time() );
        foreach ( $calendar_ids as $calendar_id ) {
            if ( !WBK_Validator::validateId( $calendar_id, 'wbk_gg_calendars' ) ) {
                continue;
            }
            $google = new WBK_Google();
            $google->init( $calendar_id );
            $connect_status = $google->connect();
            
            if ( $connect_status[0] == 1 ) {
                $google->doCache( $start );
            } else {
                $noifications = new WBK_Email_Notifications( $service_id, null );
                $noifications->send_gg_calendar_issue_alert_to_admin();
            }
        
        }
    }
    
    // get calendars
    static function getBackwardGGCalendars()
    {
        global  $wpdb ;
        $result = $wpdb->get_col( "SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_gg_calendars where mode = 'Two-ways' OR mode = 'One-way-import'" );
        return $result;
    }
    
    // get count of appointment by email-time-service
    static function getCountOfAppointmentsByEmailTimeService( $email, $time, $service_id )
    {
        global  $wpdb ;
        $count = $wpdb->get_var( $wpdb->prepare(
            " SELECT COUNT(*) FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE email=%s and time=%d and service_id=%d",
            $email,
            $time,
            $service_id
        ) );
        return $count;
    }
    
    // get count of appointment by email-service
    static function getCountOfAppointmentsByEmailService( $email, $service_id )
    {
        global  $wpdb ;
        $count = $wpdb->get_var( $wpdb->prepare(
            " SELECT COUNT(*) FROM " . get_option( 'wbk_db_prefix', '' ) . " wbk_appointments WHERE email=%s and service_id=%d and time > %d",
            $email,
            $service_id,
            time()
        ) );
        return $count;
    }
    
    // get count of appointment by email-service-day
    static function getCountOfAppointmentsByEmailServiceDay( $email, $service_id, $day )
    {
        global  $wpdb ;
        $count = $wpdb->get_var( $wpdb->prepare(
            " SELECT COUNT(*) FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE email=%s and service_id=%d and day = %d",
            $email,
            $service_id,
            $day
        ) );
        return $count;
    }
    
    // get count of appointment by email-service
    static function getCountOfAppointmentsByDay( $day )
    {
        global  $wpdb ;
        $count = $wpdb->get_var( $wpdb->prepare( " SELECT COUNT(*) FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE  day = %d", $day ) );
        return $count;
    }
    
    // set creted_on to apppointment appointment
    static function setCreatedOnToAppointment( $appointment_id )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return FALSE;
        }
        $result = $wpdb->update(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            array(
            'created_on' => time(),
        ),
            array(
            'id' => $appointment_id,
        ),
            array( '%d' ),
            array( '%d' )
        );
        
        if ( $result == false || $result == 0 ) {
            return FALSE;
        } else {
            return TRUE;
        }
    
    }
    
    // set user  to apppointment appointment
    static function setIPToAppointment( $appointment_id )
    {
        if ( get_option( 'wbk_gdrp', 'disabled' ) == 'enabled' ) {
            return;
        }
        global  $wpdb ;
        if ( !isset( $_SERVER['REMOTE_ADDR'] ) ) {
            return;
        }
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return FALSE;
        }
        $result = $wpdb->update(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            array(
            'user_ip' => $_SERVER['REMOTE_ADDR'],
        ),
            array(
            'id' => $appointment_id,
        ),
            array( '%s' ),
            array( '%d' )
        );
        
        if ( $result == false || $result == 0 ) {
            return FALSE;
        } else {
            return TRUE;
        }
    
    }
    
    // set creted_on to apppointment appointment
    static function setActualDurationToAppointment( $appointment_id, $duration )
    {
        return TRUE;
    }
    
    // set creted_on to apppointment appointment
    static function setServiceCategoryToAppointment( $appointment_id, $category_id )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return FALSE;
        }
        $result = $wpdb->update(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            array(
            'service_category' => $category_id,
        ),
            array(
            'id' => $appointment_id,
        ),
            array( '%d' ),
            array( '%d' )
        );
        
        if ( $result == false || $result == 0 ) {
            return FALSE;
        } else {
            return TRUE;
        }
    
    }
    
    static function getExtraValueByAppointmentId( $appointment_id, $field_id )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
            return FALSE;
        }
        $extra = $wpdb->get_var( $wpdb->prepare( " SELECT extra FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE id = %d ", $appointment_id ) );
        $extra = json_decode( $extra );
        foreach ( $extra as $item ) {
            if ( count( $item ) != 3 ) {
                continue;
            }
            if ( $item[0] == $field_id ) {
                return $item[2];
            }
        }
        return '';
    }
    
    static function setAmountForApppointment( $apppointment_id )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $apppointment_id, 'wbk_appointments' ) ) {
            return FALSE;
        }
        $appointment = new WBK_Appointment_deprecated();
        if ( !$appointment->setId( $apppointment_id ) ) {
            return FALSE;
        }
        if ( !$appointment->load() ) {
            return FALSE;
        }
        $service_id = self::getServiceIdByAppointmentId( $apppointment_id );
        $service = self::initServiceById( $service_id );
        if ( $service == FALSE ) {
            return;
        }
        $price_per_appointment = $appointment->getQuantity() * $service->getPrice( $appointment->getTime() );
        $amount = number_format(
            $price_per_appointment,
            get_option( 'wbk_price_fractional', '2' ),
            '.',
            ''
        );
        $result = $wpdb->update(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            array(
            'moment_price' => $amount,
        ),
            array(
            'id' => $apppointment_id,
        ),
            array( '%s' ),
            array( '%d' )
        );
    }
    
    static function getLangByAppointmentId( $app_id )
    {
        global  $wpdb ;
        $lang = $wpdb->get_var( $wpdb->prepare( 'select lang from ' . get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments where id = %d', $app_id ) );
        return $lang;
    }
    
    static function getCurrentCateogryByAppointmentId( $app_id )
    {
        global  $wpdb ;
        $lang = $wpdb->get_var( $wpdb->prepare( 'select service_category from ' . get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments where id = %d', $app_id ) );
        return $lang;
    }
    
    static function setLangToAppointmentId( $app_id )
    {
        global  $wpdb ;
        if ( !defined( 'ICL_LANGUAGE_CODE' ) ) {
            return;
        }
        if ( !WBK_Validator::validateId( $app_id, 'wbk_appointments' ) ) {
            return FALSE;
        }
        $wpdb->update(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            array(
            'lang' => ICL_LANGUAGE_CODE,
        ),
            array(
            'id' => $app_id,
        ),
            array( '%s' ),
            array( '%d' )
        );
    }
    
    static function switchLanguageByAppointmentId( $app_id )
    {
        if ( !defined( 'ICL_LANGUAGE_CODE' ) ) {
            return;
        }
        $lang = self::getLangByAppointmentId( $app_id );
        if ( $lang == '' || $lang === FALSE ) {
            return;
        }
        global  $sitepress ;
        if ( !is_null( $sitepress ) && method_exists( $sitepress, 'switch_lang' ) ) {
            $sitepress->switch_lang( $lang, true );
        }
    }
    
    static function getAppointmentCreatedOn( $appointment_id )
    {
        global  $wpdb ;
        $value = $wpdb->get_var( $wpdb->prepare( "\n\t\t\tSELECT      created_on\n\t\t\tFROM        " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments\n \t\t\tWHERE       id = %d", $appointment_id ) );
        return $value;
    }
    
    static function filterNotPaidAppointments( $appointment_ids )
    {
        $verified_ids = array();
        foreach ( $appointment_ids as $appointment_id ) {
            $status = self::getAppointmentStatus( $appointment_id );
            if ( !is_null( $status ) ) {
                if ( $status == 'pending' || $status == 'approved' ) {
                    $verified_ids[] = $appointment_id;
                }
            }
        }
        return $verified_ids;
    }
    
    static function getPymentItemNamesByAppoiuntmentIds( $apppointment_ids )
    {
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $time_format = WBK_Date_Time_Utils::getTimeFormat();
        $date_format = WBK_Date_Time_Utils::getDateFormat();
        $item_names = array();
        foreach ( $apppointment_ids as $appointment_id ) {
            $item_name = get_option( 'wbk_payment_item_name', '' );
            if ( $item_name == '' ) {
                $item_name = sanitize_text_field( $wbk_wording['payment_item_name'] );
            }
            $appointment = new WBK_Appointment_deprecated();
            if ( !$appointment->setId( $appointment_id ) ) {
                continue;
            }
            if ( !$appointment->load() ) {
                continue;
            }
            $service = self::initServiceById( $appointment->getService() );
            if ( $service == FALSE ) {
                continue;
            }
            $item_name = str_replace( '#date', wp_date( $date_format, $appointment->getTime(), new DateTimeZone( date_default_timezone_get() ) ), $item_name );
            $item_name = str_replace( '#time', wp_date( $time_format, $appointment->getTime(), new DateTimeZone( date_default_timezone_get() ) ), $item_name );
            $item_name = str_replace( '#tr', wp_date( $time_format, $appointment->getTime(), new DateTimeZone( date_default_timezone_get() ) ) . ' - ' . wp_date( $time_format, $appointment->getTime() + $service->getDuration() * 60, new DateTimeZone( date_default_timezone_get() ) ), $item_name );
            $item_name = str_replace( '#id', $appointment->getId(), $item_name );
            $item_name = str_replace( '#name', $appointment->getName(), $item_name );
            $item_name = str_replace( '#email', $appointment->getEmail(), $item_name );
            $item_name = str_replace( '#quantity', $appointment->getQuantity(), $item_name );
            $item_name = WBK_Db_Utils::message_placeholder_processing( $item_name, $appointment, $service );
            $item_names[] = $item_name;
        }
        date_default_timezone_set( 'UTC' );
        return trim( implode( ', ', $item_names ) );
    }
    
    static function getAmountNoTaxByAppoiuntmentIds( $apppointment_ids )
    {
        $amount = 0;
        foreach ( $apppointment_ids as $appointment_id ) {
            $booking = new WBK_Booking( $appointment_id );
            if ( $booking->get_name() == '' ) {
                continue;
            }
            $amount += $booking->get_quantity() * $booking->get_price();
        }
        return $amount;
    }
    
    static function copyAppointmentToCancelled( $appointment_id, $cancelled_by )
    {
        global  $wpdb ;
        if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
            $cancelled_by .= ' (' . $_SERVER['REMOTE_ADDR'] . ')';
        }
        $appointment_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments  WHERE id = %d", $appointment_id ), ARRAY_A );
        $wpdb->insert( get_option( 'wbk_db_prefix', '' ) . 'wbk_cancelled_appointments', array(
            'id_cancelled'         => $appointment_id,
            'cancelled_by'         => $cancelled_by,
            'name'                 => $appointment_data['name'],
            'email'                => $appointment_data['email'],
            'phone'                => $appointment_data['phone'],
            'description'          => $appointment_data['description'],
            'extra'                => $appointment_data['extra'],
            'attachment'           => $appointment_data['attachment'],
            'service_id'           => $appointment_data['service_id'],
            'time'                 => $appointment_data['time'],
            'day'                  => $appointment_data['day'],
            'duration'             => $appointment_data['duration'],
            'created_on'           => $appointment_data['created_on'],
            'quantity'             => $appointment_data['quantity'],
            'status'               => $appointment_data['status'],
            'payment_id'           => $appointment_data['payment_id'],
            'token'                => $appointment_data['token'],
            'payment_cancel_token' => 'not_used',
            'admin_token'          => 'not_used',
            'expiration_time'      => '0',
            'time_offset'          => '0',
            'gg_event_id'          => $appointment_data['gg_event_id'],
            'coupon'               => '0',
            'payment_method'       => $appointment_data['payment_method'],
            'lang'                 => $appointment_data['lang'],
            'moment_price'         => $appointment_data['moment_price'],
        ), array(
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%s',
            '%f',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
            '%d',
            '%s',
            '%d',
            '%s',
            '%s',
            '%s',
            '%s'
        ) );
        do_action( 'wbk_table_after_add', [ $wpdb->insert_id, get_option( 'wbk_db_prefix', '' ) . 'wbk_cancelled_appointments' ] );
    }
    
    static function getAppointmentsByServiceAndTime( $service_id, $time )
    {
        global  $wpdb ;
        $app_ids = $wpdb->get_col( $wpdb->prepare( "\n\t\t\tSELECT      id\n\t\t\tFROM        " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments\n \t\t\tWHERE       service_id = %d\n\t\t\tAND \t\ttime  = %d\n\t\t\t", $service_id, $time ) );
        return $app_ids;
    }
    
    static function getDurationOfAppointment( $appointment_id, $cancelled = FALSE )
    {
        global  $wpdb ;
        
        if ( !$cancelled ) {
            if ( !WBK_Validator::validateId( $appointment_id, 'wbk_appointments' ) ) {
                return 0;
            }
            $value = $wpdb->get_var( $wpdb->prepare( " SELECT duration FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE id = %d ", $appointment_id ) );
        } else {
            if ( !WBK_Validator::validateId( $apppointment_id, 'wbk_cancelled_appointments' ) ) {
                return 0;
            }
            $value = $wpdb->get_var( $wpdb->prepare( " SELECT duration FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_cancelled_appointments WHERE id = %d ", $appointment_id ) );
        }
        
        if ( is_null( $value ) ) {
            return 0;
        }
        return $value;
    }
    
    static function getCouponName( $coupon_id )
    {
        global  $wpdb ;
        if ( !WBK_Validator::validateId( $coupon_id, 'wbk_coupons' ) ) {
            return FALSE;
        }
        $name = $wpdb->get_var( $wpdb->prepare( " SELECT  name from " . get_option( 'wbk_db_prefix', '' ) . "wbk_coupons WHERE id = %d", $coupon_id ) );
        return $name;
    }
    
    static function getPaymentFields()
    {
        return array(
            'name'        => __( 'Cardholder name', 'wbk' ),
            'city'        => __( 'City', 'wbk' ),
            'country'     => __( 'Country', 'wbk' ),
            'line1'       => __( 'Address line 1', 'wbk' ),
            'line2'       => __( 'Address line 1', 'wbk' ),
            'postal_code' => __( 'Postal code', 'wbk' ),
            'state'       => __( 'State', 'wbk' ),
        );
    }
    
    static function priceToFloat( $s )
    {
        $s = str_replace( ',', '.', $s );
        $s = preg_replace( "/[^0-9\\.]/", "", $s );
        $s = str_replace( '.', '', substr( $s, 0, -3 ) ) . substr( $s, -3 );
        return (double) $s;
    }
    
    static function getAppointmentColumns( $keys_only = false )
    {
        if ( $keys_only ) {
            return array(
                'service_id',
                'day',
                'time',
                'quantity',
                'name',
                'email',
                'description',
                'extra',
                'status',
                'payment_method',
                'moment_price',
                'coupon'
            );
        }
        return array(
            'service_id'     => __( 'Service', 'wbk' ),
            'created_on'     => __( 'Created on', 'wbk' ),
            'day'            => __( 'Date', 'wbk' ),
            'time'           => __( 'Time', 'wbk' ),
            'quantity'       => __( 'Places booked', 'wbk' ),
            'name'           => __( 'Customer name', 'wbk' ),
            'email'          => __( 'Customer email', 'wbk' ),
            'phone'          => __( 'Phone', 'wbk' ),
            'description'    => __( 'Customer comment', 'wbk' ),
            'extra'          => __( 'Custom fields', 'wbk' ),
            'status'         => __( 'Status', 'wbk' ),
            'payment_method' => __( 'Payment method', 'wbk' ),
            'moment_price'   => __( 'Price', 'wbk' ),
            'coupon'         => __( 'Coupon', 'wbk' ),
            'ip'             => __( 'User IP', 'wbk' ),
        );
    }
    
    // get number of appointmner by given service and all connected services
    static function getCountOfAppointmentsByDayService( $service_id, $day )
    {
        global  $wpdb ;
        $arrIds = array();
        $autolock_mode = get_option( 'wbk_appointments_auto_lock_mode', 'all' );
        
        if ( $autolock_mode == 'all' ) {
            $arrIds = WBK_Db_Utils::getServices();
        } elseif ( $autolock_mode == 'categories' ) {
            $arrIds = WBK_Db_Utils::getServicesWithSameCategory( $service_id );
        }
        
        if ( count( $arrIds ) == 0 ) {
            return 0;
        }
        $appts_on_day = $wpdb->get_var( $wpdb->prepare( "SELECT count(*) as cnt\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t where service_id in (" . implode( ',', $arrIds ) . ")  AND day = %d", $day ) );
        return $appts_on_day;
    }
    
    static function convertDateFormatForPicker()
    {
        $format = WBK_Date_Time_Utils::getDateFormat();
        $format = str_replace( 'd', 'dd', $format );
        $format = str_replace( 'j', 'd', $format );
        $format = str_replace( 'l', 'dddd', $format );
        $format = str_replace( 'D', 'ddd', $format );
        $format = str_replace( 'm', 'mm', $format );
        $format = str_replace( 'n', 'm', $format );
        $format = str_replace( 'F', 'mmmm', $format );
        $format = str_replace( 'M', 'mmm', $format );
        $format = str_replace( 'y', 'yy', $format );
        $format = str_replace( 'Y', 'yyyy', $format );
        $format = str_replace( 'S', '', $format );
        $format = str_replace( 's', '', $format );
        return $format;
    }
    
    static function replaceRanges( $message, $appointment_ids )
    {
        $start = 2554146984;
        $end = 0;
        $date_format = WBK_Date_Time_Utils::getDateFormat();
        $time_format = WBK_Date_Time_Utils::getTimeFormat();
        foreach ( $appointment_ids as $id ) {
            $appointment = WBK_Db_Utils::initAppointmentById( $id );
            if ( $appointment == FALSE ) {
                continue;
            }
            $service = WBK_Db_Utils::initServiceById( $appointment->getService() );
            $cur_start = $appointment->getTime();
            $cur_end = $cur_start + $service->getDuration() * 60;
            if ( $cur_start < $start ) {
                $start = $cur_start;
            }
            if ( $cur_end > $end ) {
                $end = $cur_end;
            }
        }
        $time_range = wp_date( $time_format, $start, new DateTimeZone( date_default_timezone_get() ) ) . ' - ' . wp_date( $time_format, $end, new DateTimeZone( date_default_timezone_get() ) );
        $date_time_range = wp_date( $date_format, $start, new DateTimeZone( date_default_timezone_get() ) ) . ' ' . wp_date( $time_format, $start, new DateTimeZone( date_default_timezone_get() ) ) . ' - ' . wp_date( $date_format, $end, new DateTimeZone( date_default_timezone_get() ) ) . ' ' . wp_date( $time_format, $end, new DateTimeZone( date_default_timezone_get() ) );
        $message = str_replace( '#timerange', $time_range, $message );
        $message = str_replace( '#timedaterange', $date_time_range, $message );
        return $message;
    }

}