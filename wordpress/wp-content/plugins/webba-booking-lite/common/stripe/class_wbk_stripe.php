<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Webba Booking Stripe integration class
class WBK_Stripe
{
    protected  $api_key ;
    protected  $api_sectet ;
    protected  $tax ;
    protected  $currency ;
    public function init( $service_id )
    {
        return FALSE;
    }
    
    public static function getCurrencies()
    {
        return array(
            'USD',
            'AED',
            'AFN',
            'ALL',
            'AMD',
            'ANG',
            'AOA',
            'ARS',
            'AUD',
            'AWG',
            'AZN',
            'BAM',
            'BBD',
            'BDT',
            'BGN',
            'BIF',
            'BMD',
            'BND',
            'BOB',
            'BRL',
            'BSD',
            'BWP',
            'BZD',
            'CAD',
            'CDF',
            'CHF',
            'CLP',
            'CNY',
            'COP',
            'CRC',
            'CVE',
            'CZK',
            'DJF',
            'DKK',
            'DOP',
            'DZD',
            'EGP',
            'ETB',
            'EUR',
            'FJD',
            'FKP',
            'GBP',
            'GEL',
            'GIP',
            'GMD',
            'GNF',
            'GTQ',
            'GYD',
            'HKD',
            'HNL',
            'HRK',
            'HTG',
            'HUF',
            'IDR',
            'ILS',
            'INR',
            'ISK',
            'JMD',
            'JPY',
            'KES',
            'KGS',
            'KHR',
            'KMF',
            'KRW',
            'KYD',
            'KZT',
            'LAK',
            'LBP',
            'LKR',
            'LRD',
            'LSL',
            'MAD',
            'MDL',
            'MGA',
            'MKD',
            'MMK',
            'MNT',
            'MOP',
            'MRO',
            'MUR',
            'MVR',
            'MWK',
            'MXN',
            'MYR',
            'MZN',
            'NAD',
            'NGN',
            'NIO',
            'NOK',
            'NPR',
            'NZD',
            'PAB',
            'PEN',
            'PGK',
            'PHP',
            'PKR',
            'PLN',
            'PYG',
            'QAR',
            'RON',
            'RSD',
            'RUB',
            'RWF',
            'SAR',
            'SBD',
            'SCR',
            'SEK',
            'SGD',
            'SHP',
            'SLL',
            'SOS',
            'SRD',
            'STD',
            'SVC',
            'SZL',
            'THB',
            'TJS',
            'TOP',
            'TRY',
            'TTD',
            'TWD',
            'TZS',
            'UAH',
            'UGX',
            'UYU',
            'UZS',
            'VND',
            'VUV',
            'WST',
            'XAF',
            'XCD',
            'XOF',
            'XPF',
            'YER',
            'ZAR',
            'ZMW'
        );
    }
    
    public static function isCurrencyZeroDecimal( $currency )
    {
        $arr_list = array(
            'MGA',
            'BIF',
            'CLP',
            'PYG',
            'DJF',
            'RWF',
            'GNF',
            'JPY',
            'VND',
            'VUV',
            'XAF',
            'KMF',
            'KRW',
            'XOF',
            'XPF'
        );
        
        if ( in_array( $currency, $arr_list ) ) {
            return TRUE;
        } else {
            return FALSE;
        }
    
    }
    
    static function renderPaymentMethods( $service_id, $appointment_ids, $button_class = '' )
    {
        global  $wbk_wording ;
        
        if ( !is_array( $service_id ) ) {
            $services = array( $service_id );
        } else {
            $services = $service_id;
        }
        
        foreach ( $services as $service_id ) {
            $service = new WBK_Service_deprecated();
            if ( !$service->setId( $service_id ) ) {
                return 'Unable to access service: wrong service id.';
            }
            if ( !$service->load() ) {
                return 'Unable to access service: load failed.';
            }
            $arr_items = json_decode( $service->getPayementMethods() );
            if ( !in_array( 'stripe', $arr_items ) ) {
                return '';
            }
        }
        $html = '';
        $stripe_btn_text = get_option( 'wbk_stripe_button_text', 'Pay with credit card' );
        if ( $stripe_btn_text == '' ) {
            $stripe_btn_text = __( 'Pay with credit card', 'wbk' );
        }
        $html .= '<input class="wbk-button wbk-width-100 wbk-mt-10-mb-10 wbk-payment-init ' . $button_class . '" data-method="stripe" data-app-id="' . implode( ',', $appointment_ids ) . '"  value="' . $stripe_btn_text . '" type="button">';
        return $html;
    }
    
    public function createPayment( $method, $app_ids, $coupon )
    {
        $html = '';
        return $html;
    }
    
    public function getOrderData( $app_ids, $coupon = FALSE )
    {
        $subtotal = 0;
        $time_format = WBK_Date_Time_Utils::getTimeFormat();
        $date_format = WBK_Date_Time_Utils::getDateFormat();
        $result_item_name = array();
        $service_fees = array();
        $service_fee_descriptions = array();
        foreach ( $app_ids as $app_id ) {
            $service_id = WBK_Db_Utils::getServiceIdByAppointmentId( $app_id );
            if ( $service_id === false ) {
                continue;
            }
            $service = new WBK_Service_deprecated();
            if ( !$service->setId( $service_id ) ) {
                continue;
            }
            if ( !$service->load() ) {
                continue;
            }
            $appointment = new WBK_Appointment_deprecated();
            if ( !$appointment->setId( $app_id ) ) {
                continue;
            }
            if ( !$appointment->load() ) {
                continue;
            }
            $price_data = WBK_Price_Processor::calculate_single_booking_price( $app_id, $app_ids );
            $price = $price_data['price'];
            $price = apply_filters(
                'wbk_stripe_price_per_appointment',
                $price,
                $app_id,
                $service_id
            );
            $quantity = $appointment->getQuantity();
            $subtotal += $price * $quantity;
            $item_name = get_option( 'wbk_payment_item_name', '' );
            if ( $item_name == '' ) {
                $item_name = sanitize_text_field( $wbk_wording['payment_item_name'] );
            }
            $item_name = str_replace( '#date', wp_date( $date_format, $appointment->getTime(), new DateTimeZone( date_default_timezone_get() ) ), $item_name );
            $item_name = str_replace( '#time', wp_date( $time_format, $appointment->getTime(), new DateTimeZone( date_default_timezone_get() ) ), $item_name );
            $item_name = str_replace( '#tr', wp_date( $time_format, $appointment->getTime(), new DateTimeZone( date_default_timezone_get() ) ) . ' - ' . wp_date( $time_format, $appointment->getTime() + $service->getDuration() * 60, new DateTimeZone( date_default_timezone_get() ) ), $item_name );
            $item_name = str_replace( '#id', $appointment->getId(), $item_name );
            $item_name = str_replace( '#name', $appointment->getName(), $item_name );
            $item_name = str_replace( '#email', $appointment->getEmail(), $item_name );
            $item_name = str_replace( '#quantity', $appointment->getQuantity(), $item_name );
            $item_name = WBK_Db_Utils::message_placeholder_processing( $item_name, $appointment, $service );
            $result_item_name[] = $item_name;
            $service = new WBK_Service( $appointment->getService() );
            if ( $service->get_fee() !== null ) {
                
                if ( is_numeric( $service->get_fee() ) ) {
                    $service_fees[$appointment->getService()] = $service->get_fee();
                    $service_fee_description = get_option( 'wbk_service_fee_description' );
                    $service_fee_description = str_replace( '#service', $service->get_name(), $service_fee_description );
                    $service_fee_descriptions[$appointment->getService()] = $service_fee_description;
                }
            
            }
        }
        
        if ( $coupon != FALSE ) {
            if ( intval( $coupon[1] ) > 0 ) {
                $subtotal -= intval( $coupon[1] );
            }
            
            if ( intval( $coupon[2] ) > 0 ) {
                $discounted = $subtotal / 100 * intval( $coupon[2] );
                $subtotal -= $discounted;
            }
        
        }
        
        $service_fee_total = 0;
        foreach ( $service_fees as $service_fee ) {
            $service_fee_total += $service_fee;
        }
        if ( $service_fee_total > 0 ) {
            foreach ( $service_fee_descriptions as $service_fee_description ) {
                $result_item_name[] = $service_fee_description;
            }
        }
        if ( get_option( 'wbk_do_not_tax_deposit', '' ) != 'true' ) {
            $subtotal += $service_fee_total;
        }
        $tax_to_pay = $subtotal / 100 * $this->tax;
        $total = $subtotal + $tax_to_pay;
        if ( get_option( 'wbk_do_not_tax_deposit', '' ) == 'true' ) {
            $total += $service_fee_total;
        }
        
        if ( self::isCurrencyZeroDecimal( $this->currency ) ) {
            return array( round( $total ), implode( ', ', $result_item_name ) );
        } else {
            return array( round( $total * 100 ), implode( ', ', $result_item_name ) );
        }
    
    }
    
    public function charge(
        $app_ids,
        $amount,
        $payment_id,
        $intent_id = null
    )
    {
        return array( 0, __( 'Payment method not supported' ) );
    }

}