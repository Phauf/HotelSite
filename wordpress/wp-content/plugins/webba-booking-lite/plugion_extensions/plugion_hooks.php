<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
add_filter(
    'plugion_rows_value',
    'wbk_plugion_rows_value',
    10,
    2
);
function wbk_plugion_rows_value( $input, $table_name )
{
    if ( $table_name == get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments' || $table_name == get_option( 'wbk_db_prefix', '' ) . 'wbk_cancelled_appointments' ) {
        foreach ( $input as $key => $row ) {
            date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
            $row->day = date( 'Y-m-d', $row->day );
            date_default_timezone_set( 'UTC' );
        }
    }
    return $input;
}

add_action(
    'plugion_before_table',
    'my_plugion_before_table',
    10,
    1
);
function my_plugion_before_table( $slug )
{
    if ( $slug != get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments' && $slug != get_option( 'wbk_db_prefix', '' ) . 'wbk_cancelled_appointments' ) {
        return;
    }
    ?>
        <script>
            var wbk_custom_fields = '<?php 
    echo  get_option( 'wbk_custom_fields_columns' ) ;
    ?>';
        </script>
    <?php 
}

add_filter(
    'plugion_formated_row_value',
    'wbk_plugion_formated_row_value',
    10,
    2
);
function wbk_plugion_formated_row_value( $input, $data )
{
    $slug = $data[1];
    $row = $data[3];
    if ( $slug == 'appointment_day' || $slug == 'appointment_time' ) {
        return array(
            'display'     => $input,
            '@data-order' => $row['time'],
        );
    }
    return $input;
}

add_filter(
    'plugion_formated_row_values',
    'wbk_plugion_formated_row_values',
    10,
    2
);
function wbk_plugion_formated_row_values( $input, $row )
{
    if ( !isset( $row->extra ) ) {
        return $input;
    }
    $result = array();
    $custom_data = $row->extra;
    foreach ( $input as $item ) {
        $result[] = $item;
        
        if ( !is_array( $item ) && strpos( $item, 'wbk_app_custom_data_value' ) !== false ) {
            $ids = get_option( 'wbk_custom_fields_columns', '' );
            
            if ( $ids != '' ) {
                $ids = explode( ',', $ids );
                foreach ( $ids as $id ) {
                    $custom_value = '';
                    $id = explode( '[', $id );
                    $id = $id[0];
                    $custom_value = WBK_Model_Utils::extract_custom_field_value( $custom_data, $id );
                    if ( $custom_value === null ) {
                        $custom_value = '';
                    }
                    $result[] = $custom_value;
                }
            }
        
        }
    
    }
    return $result;
}

add_filter(
    'plugion_cell_content',
    'wbk_plugion_cell_content',
    10,
    2
);
function wbk_plugion_cell_content( $input, $data )
{
    if ( $data[1] == 'appointment_quantity' ) {
        return $data[2];
    }
    
    if ( $data[1] == 'appointment_created_on' ) {
        $format = get_option( 'date_format' );
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $date = wp_date( $format, $data[2], new DateTimeZone( date_default_timezone_get() ) );
        $format = get_option( 'time_format' );
        $time = wp_date( $format, $data[2], new DateTimeZone( date_default_timezone_get() ) );
        date_default_timezone_set( 'UTC' );
        return $date . ' ' . $time;
    }
    
    if ( $data[1] == 'appointment_coupon' ) {
        
        if ( $data[2] != '' ) {
            $coupon = new WBK_Coupon( $data[2] );
            return $coupon->get_name();
        }
    
    }
    
    if ( $data[1] == 'appointment_name' ) {
        $template = get_option( 'wbk_customer_name_output', '#name' );
        $result = str_replace( '#name', $data[2], $template );
        $row = $data[3];
        $appointment = new WBK_Appointment_deprecated();
        if ( !$appointment->setId( $row['id'] ) ) {
            return $result;
        }
        if ( !$appointment->load() ) {
            return $result;
        }
        $service = new WBK_Service_deprecated();
        if ( !$service->setId( $appointment->getService() ) ) {
            return $result;
        }
        if ( !$service->load() ) {
            return $result;
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
    
    return $input;
}

add_filter(
    'plugion_property_field_validation_text',
    'wbk_plugion_property_field_validation_text',
    20,
    4
);
function wbk_plugion_property_field_validation_text(
    $input,
    $value,
    $slug,
    $field
)
{
    if ( $slug == 'service_service_fee' || $slug == 'service_price' ) {
        if ( $value == '' ) {
            return [ true, '' ];
        }
    }
    return $input;
}

add_filter(
    'plugion_property_field_validation_select',
    'wbk_plugion_property_field_validation_select',
    20,
    4
);
function wbk_plugion_property_field_validation_select(
    $input,
    $value,
    $slug,
    $field
)
{
    if ( $slug == 'appointment_quantity' ) {
        
        if ( Plugion\Validator::check_integer( $value, 1, 2147483647 ) ) {
            return [ true, $value ];
        } else {
            return [ false, sprintf( plugion_translate_string( 'Value of %s is not acceptable' ), $field->get_title() ) ];
        }
    
    }
    
    if ( $slug == 'appointment_service_id' ) {
        $services = WBK_Model_Utils::get_service_ids( true );
        if ( !in_array( $value, $services ) ) {
            return [ false, sprintf( plugion_translate_string( 'Value of %s is not acceptable' ), $field->get_title() ) ];
        }
    }
    
    return $input;
}

add_action( 'plugion_filter_wbk_date_range', 'native_plugion_filter_wbk_date_range' );
function native_plugion_filter_wbk_date_range( $data )
{
    echo  WBK_Renderer::load_template( 'plugion/filter_wbk_date_range', $data ) ;
}

add_filter(
    'plugion_filter_value',
    'wbk_plugion_filter_value',
    10,
    2
);
function wbk_plugion_filter_value( $input, $slug )
{
    
    if ( $slug == 'appointment_day' ) {
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $input[0] = strtotime( $input[0] );
        $input[1] = strtotime( $input[1] );
        date_default_timezone_set( 'UTC' );
        return $input;
    }
    
    return $input;
}

add_action(
    'plugion_on_after_row_add',
    'wbk_plugion_on_after_row_add',
    10,
    3
);
function wbk_plugion_on_after_row_add( $table_name, $table_name_not_filtered, $row )
{
    
    if ( $table_name == get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments' ) {
        $service_id = $row->service_id;
        $service = new WBK_Service( $row->service_id );
        Plugion()->set_value(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            'appointment_created_on',
            $row->id,
            time()
        );
        Plugion()->set_value(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            'appointment_duration',
            $row->id,
            $service->get_duration()
        );
        Plugion()->set_value(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            'appointment_prev_status',
            $row->id,
            $row->status
        );
        if ( get_option( 'wbk_gdrp', 'disabled' ) == 'disabled' ) {
            if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
                Plugion()->set_value(
                    get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
                    'appointment_user_ip',
                    $row->id,
                    $_SERVER['REMOTE_ADDR']
                );
            }
        }
        $auto_lock = get_option( 'wbk_appointments_auto_lock', 'disabled' );
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        if ( $auto_lock == 'enabled' ) {
            WBK_Db_Utils::lockTimeSlotsOfOthersServices( $service_id, $row->id );
        }
        WBK_Db_Utils::addAppointmentDataToGGCelendar( $service_id, $row->id );
        WBK_Db_Utils::setIPToAppointment( $row->id );
        WBK_Model_Utils::set_end_for_booking( $row->id );
        $noifications = new WBK_Email_Notifications( $service_id, $row->id );
        $noifications->sendSingleBookedManually();
        date_default_timezone_set( 'UTC' );
    }

}

add_action(
    'plugion_on_before_row_delete',
    'wbk_plugion_on_before_row_delete',
    10,
    3
);
function wbk_plugion_on_before_row_delete( $table_name, $table_name_not_filtered, $row )
{
    global  $wpdb ;
    
    if ( $table_name == get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments' ) {
        WBK_Db_Utils::deleteAppointmentDataAtGGCelendar( $row->id );
        $noifications = new WBK_Email_Notifications( $row->service_id, $row->id );
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $noifications->prepareOnCancelCustomer();
        $noifications->sendOnCancelCustomer();
        WBK_Db_Utils::copyAppointmentToCancelled( $row->id, __( 'Service administrator', 'wbk' ) );
        date_default_timezone_set( 'UTC' );
        WBK_Db_Utils::freeLockedTimeSlot( $row->id );
        do_action( 'webba_before_cancel_booking', $row->id );
    }
    
    if ( $table_name == get_option( 'wbk_db_prefix', '' ) . 'wbk_services' ) {
        $wpdb->query( $wpdb->prepare( 'DELETE from ' . get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments where service_id = %d', $row->id ) );
    }
}

add_action(
    'plugion_on_after_row_update',
    'wbk_plugion_on_after_row_update',
    10,
    3
);
function wbk_plugion_on_after_row_update( $table_name, $table_name_not_filtered, $row )
{
    
    if ( $table_name == get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments' ) {
        global  $wpdb ;
        $current_status = $row->status;
        $prev_status = $row->prev_status;
        $service_id = $row->service_id;
        if ( $prev_status == 'pending' || $prev_status == 'paid' ) {
            
            if ( $current_status == 'approved' || $current_status == 'paid_approved' ) {
                $noifications = new WBK_Email_Notifications( $service_id, $row->id );
                $noifications->sendOnApprove();
                if ( get_option( 'wbk_email_customer_send_invoice', 'disabled' ) == 'onapproval' ) {
                    $noifications->sendSingleInvoice();
                }
                $expiration_mode = get_option( 'wbk_appointments_delete_not_paid_mode', 'disabled' );
                if ( $expiration_mode == 'on_approve' ) {
                    WBK_Db_Utils::setAppointmentsExpiration( $row->id );
                }
                if ( get_option( 'wbk_gg_when_add', 'onbooking' ) == 'onpaymentorapproval' ) {
                    
                    if ( !WBK_Db_Utils::idEventAddedToGoogle( $row->id ) ) {
                        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
                        WBK_Db_Utils::addAppointmentDataToGGCelendar( $service_id, $row->id );
                        date_default_timezone_set( 'UTC' );
                    }
                
                }
            }
        
        }
        $service_id = WBK_Db_Utils::getServiceIdByAppointmentId( $row->id );
        $noifications = new WBK_Email_Notifications( $service_id, $row->id );
        if ( $prev_status != 'arrived' && $current_status == 'arrived' ) {
            if ( get_option( 'wbk_email_customer_arrived_status', '' ) != '' ) {
                $noifications->sendSingleArrived();
            }
        }
        $service = new WBK_Service( $service_id );
        $template = $service->get_on_changes_template();
        
        if ( $template != false ) {
            $template = WBK_Db_Utils::getEmailTemplate( $template );
            date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
            $noifications->send_single_notification( $row->id, $template, get_option( 'wbk_email_on_update_booking_subject', '' ) );
            date_default_timezone_set( 'UTC' );
        }
        
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        WBK_Db_Utils::updateAppointmentDataAtGGCelendar( $row->id );
        date_default_timezone_set( 'UTC' );
        Plugion()->set_value(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            'appointment_prev_status',
            $row->id,
            $row->status
        );
    }

}

add_filter(
    'plugion_field_can_view',
    'wbk_plugion_field_can_view',
    10,
    3
);
function wbk_plugion_field_can_view( $input, $field_name, $table_name )
{
    if ( $table_name == get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments' || $table_name == get_option( 'wbk_db_prefix', '' ) . 'wbk_cancelled_appointments' ) {
        
        if ( WBK_User_Utils::check_access_to_schedule() ) {
            $user = wp_get_current_user();
            $roles = (array) $user->roles;
            $input = array_unique( array_merge( $input, $roles ) );
        }
    
    }
    return $input;
}

add_filter(
    'plugion_field_can_update',
    'wbk_plugion_field_can_update',
    10,
    3
);
function wbk_plugion_field_can_update( $input, $field_name, $table_name )
{
    if ( $table_name == get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments' || $table_name == get_option( 'wbk_db_prefix', '' ) . 'wbk_cancelled_appointments' ) {
        
        if ( WBK_User_Utils::check_access_to_schedule() ) {
            $user = wp_get_current_user();
            $roles = (array) $user->roles;
            $input = array_unique( array_merge( $input, $roles ) );
        }
    
    }
    return $input;
}

add_filter(
    'plugion_field_can_add',
    'wbk_plugion_field_can_add',
    10,
    3
);
function wbk_plugion_field_can_add( $input, $field_name, $table_name )
{
    if ( $table_name == get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments' ) {
        
        if ( WBK_User_Utils::check_access_to_schedule() ) {
            $user = wp_get_current_user();
            $roles = (array) $user->roles;
            $input = array_unique( array_merge( $input, $roles ) );
        }
    
    }
    if ( $table_name == get_option( 'wbk_db_prefix', '' ) . 'wbk_cancelled_appointments' ) {
        return [];
    }
    return $input;
}

add_filter(
    'plugion_get_rows_conditions',
    'wbk_plugion_get_rows_conditions',
    10,
    2
);
function wbk_plugion_get_rows_conditions( $input, $table_name )
{
    
    if ( $table_name == get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments' || $table_name == get_option( 'wbk_db_prefix', '' ) . 'wbk_cancelled_appointments' ) {
        $user = wp_get_current_user();
        
        if ( in_array( 'administrator', $user->roles, true ) || is_multisite() && !is_super_admin() ) {
            return $input;
        } else {
            $services = WBK_Model_Utils::get_service_ids( true );
            $condition = ' AND service_id in (' . implode( ',', $services ) . ')';
            $input .= $condition;
        }
    
    }
    
    return $input;
}

add_filter(
    'plugion_table_column_wbk_date_ordering',
    'wbk_plugion_table_column_wbk_date_ordering',
    10,
    2
);
function wbk_plugion_table_column_wbk_date_ordering( $input, $data )
{
    $row = $data[3];
    return $row['time'];
}

add_filter(
    'plugion_table_column_wbk_time_ordering',
    'wbk_plugion_table_column_wbk_time_ordering',
    10,
    2
);
function wbk_plugion_table_column_wbk_time_ordering( $input, $data )
{
    return $data[2];
}

add_filter(
    'plugion_row_can_delete',
    'wbk_plugion_row_can_delete',
    10,
    3
);
function wbk_plugion_row_can_delete( $input, $row, $table_name )
{
    
    if ( $table_name == get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments' ) {
        $user = wp_get_current_user();
        
        if ( current_user_can( 'manage_options' ) ) {
            return true;
        } else {
            
            if ( is_null( $row ) ) {
                if ( WBK_User_Utils::check_access_to_schedule() ) {
                    return true;
                }
            } else {
                $services = WBK_Model_Utils::get_service_ids( true );
                
                if ( in_array( $row->service_id, $services ) ) {
                    return true;
                } else {
                    return false;
                }
            
            }
        
        }
        
        return false;
    }
    
    return $input;
}

add_action( 'plugion_filter_multi_select', 'wbk_plugion_filter_multi_select_render' );
function wbk_plugion_filter_multi_select_render( $data )
{
    if ( $data[1] == 'appointment_service_id' ) {
        echo  WBK_Renderer::load_template( 'plugion/category_list', $data ) ;
    }
}

add_action( 'plugion_after_table', 'wbk_plugion_after_table' );
function wbk_plugion_after_table( $slug )
{
}
