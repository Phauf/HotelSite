<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Class WBK_Assets_Manager is used to perform requests to the REST API
 */
class WBK_Controller
{
    /**
     * constructor
     */
    public function __construct()
    {
        // register route for getting available timeslots for a day
        add_action( 'rest_api_init', function () {
            register_rest_route( 'wbk/v1', '/get-available-time-slots-day/', [
                'methods'             => 'POST',
                'callback'            => [ $this, 'get_available_time_slots_day' ],
                'permission_callback' => [ $this, 'get_available_time_slots_day_permission' ],
            ] );
        } );
        add_action( 'rest_api_init', function () {
            register_rest_route( 'wbk/v1', '/csv-export/', [
                'methods'             => 'POST',
                'callback'            => [ $this, 'wbk_csv_export' ],
                'permission_callback' => [ $this, 'wbk_csv_export_permission' ],
            ] );
        } );
        add_action( 'wp_ajax_wbk_calculate_amounts', array( $this, 'calculate_amounts' ) );
        add_action( 'wp_ajax_nopriv_wbk_calculate_amounts', array( $this, 'calculate_amounts' ) );
    }
    
    /**
     * getting time slots for a given day
     * @param  WP_REST_Request $request rest request object
     * @return WP_REST_Response rest response object
     */
    public function get_available_time_slots_day( $request )
    {
        $day = $request['date'];
        $service_id = $request['service_id'];
        $current_booking = $request['current_booking'];
        
        if ( !WBK_Validator::is_service_exists( $service_id ) ) {
            $data = array(
                'Reason' => 'Service not exists',
            );
            $response = new \WP_REST_Response( $data );
            $response->set_status( 400 );
            return $response;
        }
        
        
        if ( !WBK_Validator::is_date( $day ) ) {
            $data = array(
                'Reason' => 'Wrong date passed',
            );
            $response = new \WP_REST_Response( $data );
            $response->set_status( 400 );
            return $response;
        }
        
        if ( !Plugion\Validator::check_integer( $current_booking, 1, 2147483647 ) ) {
            $current_booking = '';
        }
        $sp = new WBK_Schedule_Processor();
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $day = strtotime( $day );
        $timeslots = $sp->get_time_slots_by_day(
            $day,
            $service_id,
            false,
            true,
            true
        );
        $time_slots_filtered = array();
        foreach ( $timeslots as $timeslot ) {
            $current_quantity = 0;
            if ( !is_array( $timeslot->getStatus() ) && $timeslot->getStatus() == 0 ) {
                $time_slots_filtered[] = $timeslot;
            }
            
            if ( !is_array( $timeslot->getStatus() ) && Plugion\Validator::check_integer( $timeslot->getStatus(), 1, 2147483647 ) ) {
                
                if ( $current_booking == $timeslot->getStatus() ) {
                    $booking = new WBK_Booking( $current_booking );
                    $current_quantity = $booking->get_quantity();
                }
                
                $timeslot->set_free_places( $timeslot->get_free_places() + $current_quantity );
                $time_slots_filtered[] = $timeslot;
            }
            
            
            if ( is_array( $timeslot->getStatus() ) && in_array( $current_booking, $timeslot->getStatus() ) ) {
                $booking = new WBK_Booking( $current_booking );
                $current_quantity = $booking->get_quantity();
                $timeslot->set_free_places( $timeslot->get_free_places() + $current_quantity );
                $time_slots_filtered[] = $timeslot;
            } elseif ( is_array( $timeslot->getStatus() ) ) {
                $timeslot->set_free_places( $timeslot->get_free_places() + $current_quantity );
                $time_slots_filtered[] = $timeslot;
            }
        
        }
        $data = array(
            'time_slots' => $time_slots_filtered,
        );
        $response = new \WP_REST_Response( $data );
        $response->set_status( 200 );
        date_default_timezone_set( 'UTC' );
        return $response;
    }
    
    /**
     * check if current user can get time slots per day
     * @param  WP_REST_Request $request rest request object
     * @return bool allow or not rest request
     */
    public function get_available_time_slots_day_permission( $request )
    {
        return true;
    }
    
    public function wbk_csv_export_permission()
    {
        return true;
    }
    
    /**
     * function CSV export
     * @return null
     */
    public function wbk_csv_export( $request )
    {
    }
    
    public function calculate_amounts()
    {
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $times = explode( ',', $_POST['times'] );
        $services = explode( ',', $_POST['services'] );
        $quantities = explode( ',', $_POST['quantities'] );
        $extra_data = stripslashes( $_POST['extra_data'] );
        $desc = sanitize_text_field( $_POST['desc'] );
        $phone = sanitize_text_field( $_POST['phone'] );
        $name = sanitize_text_field( $_POST['name'] );
        $i = -1;
        $bookings = array();
        foreach ( $times as $time ) {
            $i++;
            $service = 0;
            if ( isset( $services[$i] ) ) {
                $service = $services[$i];
            }
            $quantity = 0;
            if ( isset( $quantities[$i] ) ) {
                $quantity = $quantities[$i];
            }
            if ( !is_numeric( $time ) || !is_numeric( $service ) || !is_numeric( $quantity ) ) {
                return 0;
            }
            $day = strtotime( date( 'Y-m-d', $time ) . ' 00:00:00' );
            $booking = new WBK_Booking( null );
            $booking->set_parameters(
                $day,
                $time,
                $service,
                $quantity,
                $name,
                $phone,
                $desc,
                $extra_data
            );
            $bookings[] = $booking;
        }
        $sub_total = 0;
        foreach ( $bookings as $booking ) {
            $price = WBK_Price_Processor::calculate_single_booking_price( $booking, $bookings );
            $sub_total += $price['price'] * $booking->get_quantity();
        }
        $service_fees = 0;
        $services = array_unique( $services );
        foreach ( $services as $service ) {
            $service = new WBK_Service( $service );
            $service_fees += $service->get_fee();
        }
        if ( get_option( 'wbk_do_not_tax_deposit', '' ) != 'true' ) {
            $sub_total += $service_fees;
        }
        $price_format = get_option( 'wbk_payment_price_format', '$#price' );
        $sub_total_formated = str_replace( '#price', number_format(
            $sub_total,
            get_option( 'wbk_price_fractional', '2' ),
            get_option( 'wbk_price_separator', '.' ),
            ''
        ), $price_format );
        $tax_amount = WBK_Price_Processor::get_tax_amount( $sub_total, WBK_Price_Processor::get_tax_for_messages() );
        $tax_amount_formated = str_replace( '#price', number_format(
            $tax_amount,
            get_option( 'wbk_price_fractional', '2' ),
            get_option( 'wbk_price_separator', '.' ),
            ''
        ), $price_format );
        $total_amount = WBK_Price_Processor::get_total_amount( $sub_total, WBK_Price_Processor::get_tax_for_messages() );
        if ( get_option( 'wbk_do_not_tax_deposit', '' ) == 'true' ) {
            $total_amount += $service_fees;
        }
        $total_formated = str_replace( '#price', number_format(
            $total_amount,
            get_option( 'wbk_price_fractional', '2' ),
            get_option( 'wbk_price_separator', '.' ),
            ''
        ), $price_format );
        date_default_timezone_set( 'UTC' );
        $result = array(
            'sub_total'          => $sub_total,
            'tax'                => $tax_amount,
            'total'              => $total_amount,
            'sub_total_formated' => $sub_total_formated,
            'tax_formated'       => $tax_amount_formated,
            'total_formated'     => $total_formated,
        );
        $result = json_encode( $result );
        echo  $result ;
        wp_die();
        return;
    }

}