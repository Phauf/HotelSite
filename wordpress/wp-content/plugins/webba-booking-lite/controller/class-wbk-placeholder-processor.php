<?php
if (! defined('ABSPATH')) {
    exit;
}
class WBK_Placeholder_Processor {

    public static function process_placeholders( $message, $bookings ){
        if( is_numeric( $bookings ) ){
            $appointment = new WBK_Appointment_deprecated();
            if ( !$appointment->setId( $bookings ) ) {
                return '';
            }
            if ( !$appointment->load() ) {
                return '';
            }
            $service = new WBK_Service_deprecated();
            if ( !$service->setId( $appointment->getService() ) ) {
                return '';
            }
            if ( !$service->load() ) {
                return '';
            }
            $message = WBK_Db_Utils::message_placeholder_processing( $message, $appointment, $service );
        } elseif ( is_array( $bookings ) ){
            $price_format = get_option( 'wbk_payment_price_format', '$#price' );
            $total_amount = WBK_Price_Processor::get_total_tax_fess( $bookings );
            $total_amount =  str_replace( '#price', number_format( $total_amount,  get_option( 'wbk_price_fractional', '2' ), get_option( 'wbk_price_separator', '.' ), ''  ), $price_format );
            if( WBK_Validator::checkEmailLoop( $message ) ){
                $looped = self::get_string_between( $message, '[appointment_loop_start]', '[appointment_loop_end]' );
                $looped_html = '';
                foreach ( $bookings as $bookings_id ){
                    $appointment = new WBK_Appointment_deprecated();
                    if ( !$appointment->setId( $bookings_id ) ) {
                        continue;
                    }
                    if ( !$appointment->load() ) {
                        continue;
                    }
                    $service = new WBK_Service_deprecated();
                    if ( !$service->setId( $appointment->getService() ) ) {
        				continue;
        			}
        			if ( !$service->load() ) {
        				continue;
        			}
                    $looped_html .= WBK_Db_Utils::message_placeholder_processing( $looped, $appointment, $service );
                }
            } else {
                return $message;
            }
            if( !is_object( $appointment ) || !is_object( $service ) ){
                return $message;
            }
            $search_tag =  '[appointment_loop_start]' . $looped . '[appointment_loop_end]';
		 	$message = str_replace( $search_tag, $looped_html, $message );

            $message = WBK_Db_Utils::message_placeholder_processing( $message, $appointment, $service, $total_amount );
        }
        return $message;

    }
    public static function get_string_between( $string, $start, $end ){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if( $ini == 0 ){
            return '';
        }
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
}
