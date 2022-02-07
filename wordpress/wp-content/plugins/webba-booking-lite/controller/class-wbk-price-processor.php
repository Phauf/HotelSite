<?php
if (! defined('ABSPATH')) {
    exit;
}
class WBK_Price_Processor {
    static function calculate_single_booking_price( $booking, $bookings ){
        if( !is_object( $booking ) ){
            $booking = new WBK_Booking( $booking );
            if( $booking->get_name() == '' ){
                return array( 'price' => 0 );
            }
            if( is_array( $bookings ) ){
                $booking_temp = array();
                foreach( $bookings as $booking_id ){
                    $booking_this = new WBK_Booking( $booking_id );
                    if( $booking_this->get_name() == '' ){
                        continue;
                    }
                    $booking_temp[] = $booking_this;
                }
                $bookings = $booking_temp;
            }

        }
        $service = new WBK_Service( $booking->get_service() );
        $price_details = array();


        if( $service->get_name() == '' ){
            return array( 'price' => 0 );
        }
        $default_price = $service->get_price();
        $price_details[] =  array( 'type' => 'service_price', 'amount' => $default_price, 'service_id' => $service->get_id() );

        $sort_array = array();
        $pricing_rules = $service->get_pricing_rules();

        $pricing_rules_obj = array();
        foreach(  $pricing_rules as $pricing_rule_id ) {
            $rule = new WBK_Pricing_Rule( $pricing_rule_id );
            $pricing_rules_obj[] = $rule;
        }
        usort( $pricing_rules_obj, function( $first,$second ){
            return $first->get_priority() < $second->get_priority();
        });

        $switch_back = false;
        if( date_default_timezone_get() == 'UTC' ){
            date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
            $switch_back = true;
        }
        $multiplier = 1;
        foreach(  $pricing_rules_obj as $rule ) {
            //$rule = new WBK_Pricing_Rule( $pricing_rule_id );
            $apply_rule = false;
            if( $rule->get_amount() > 0 ){
                switch ( $rule->get_type() ) {
                    case 'date_range':
                        $dates = explode( '-', $rule->get_date_range() );
                        if( is_array( $dates ) && count( $dates ) == 2 ){
                            $start = strtotime( trim( $dates[0] ) );
                            $end = strtotime( trim( $dates[1] ) );
                            if( $booking->get_day() >= $start && $booking->get_day() <= $end ){
                                $apply_rule = true;
                            }
                        }
                        break;
                    case 'early_booking':
                        $days = ( $booking->get_day() - time() ) / 86400;
                        if( $days >= $rule->get_days_number() && $rule->get_days_number() <> 0  ){
                            $apply_rule = true;
                        }
                        break;
                    case 'custom_field':
                        $custom_field_value = $booking->get_custom_field_value( $rule->get_custom_field_id() );
                        if( is_numeric( $custom_field_value ) && $rule->get_multiply_amount() == 'yes' ){
                            $multiplier = $custom_field_value;
                        }
                        if( !is_null( $custom_field_value ) ){
                            switch ( $rule->get_custom_field_operator() ) {
                                case 'equals':
                                    if(  $rule->get_custom_field_value() == $custom_field_value ){
                                        $apply_rule = true;
                                    }
                                break;
                                case 'more_than':
                                    if( is_numeric( $rule->get_custom_field_value() ) && is_numeric( $custom_field_value )
                                        && $rule->get_custom_field_value() <  $custom_field_value ){
                                        $apply_rule = true;
                                    }
                                break;
                                case 'less_than':
                                    if( is_numeric( $rule->get_custom_field_value() ) && is_numeric( $custom_field_value )
                                        && $rule->get_custom_field_value() > $custom_field_value ){
                                        $apply_rule = true;
                                    }
                                break;
                            }
                        }

                        break;
                    case 'number_of_seats':
                        $number_of_seats = $booking->get_quantity();
                        switch ( $rule->get_number_of_seats_operator() ) {
                            case 'equals':
                                if(  $rule->get_number_of_seats_value() == $number_of_seats ){
                                    $apply_rule = true;
                                }
                            break;
                            case 'more_than':
                                if( $rule->get_number_of_seats_value() <  $number_of_seats ){
                                    $apply_rule = true;
                                }
                            break;
                            case 'less_than':
                                if( $rule->get_number_of_seats_value() > $number_of_seats ){
                                    $apply_rule = true;
                                }
                            break;
                        }

                        break;
                    case 'number_of_timeslots':
                        if( $rule->get_only_same_service() == 'yes' ){
                            $i = 0;
                            foreach( $bookings as $booking_this ){
                                if( $booking_this->get_service() == $booking->get_service() ){
                                    $i++;
                                }
                            }
                            $number_of_timeslots = $i;
                        } else {
                            $number_of_timeslots = count( $bookings );

                        }
                        switch ( $rule->get_number_of_timeslots_operator() ) {

                            case 'equals':
                                if(  $rule->get_number_of_timeslots_value() == $number_of_timeslots ){
                                    $apply_rule = true;
                                }
                            break;
                            case 'more_than':

                                if( $rule->get_number_of_timeslots_value() <  $number_of_timeslots ){
                                    $apply_rule = true;
                                }
                            break;
                            case 'less_than':

                                if( $rule->get_number_of_timeslots_value() > $number_of_timeslots ){
                                    $apply_rule = true;
                                }
                            break;
                        }

                        break;
                    case 'day_of_week_and_time':
                        $day_time = json_decode( $rule->get_day_time() );
                        if ( is_object($day_time) ) {
                            $slots = [];
                            $sort_array = [];
                            foreach( $day_time->dow_availability as $item ) {
                                $dow = date( 'N', $booking->get_day() );
                                if( $dow == $item->day_of_week ) {
                                    if( $booking->get_start() >= ( $item->start + $booking->get_day() )  &&
                                        $booking->get_start() <  ( $item->end + $booking->get_day() ) ){
                                        $apply_rule = true;
                                    }
                                }
                            }
                        }
                        break;
                    }
                 if( $apply_rule ){
                    if( $rule->get_fixed_percent() == 'fixed' || $rule->get_action() == 'replace' ){
                        $amount = $rule->get_amount();
                    } else {
                        $amount = ( $default_price / 100 ) *  $rule->get_amount();
                    }
                    $amount = $amount * $multiplier;
                    if( $rule->get_related_to_seats_number() ){
                        $amount = $amount / $booking->get_quantity();
                    }
                    $amount_signed = 0;
                    switch ( $rule->get_action() ) {
                        case 'increase':
                            $default_price += $amount;
                            $amount_signed = $amount;
                            break;
                        case 'reduce':
                            $default_price -= $amount;
                            $amount_signed = $amount * -1;
                            break;
                        case 'replace':
                            $default_price = $amount;
                            $amount_signed = $amount;
                            break;
                    }
                    $price_details[] = array( 'type' => 'pricing_rule', 'amount' => $amount_signed, 'rule_id' => $rule->get_id(), 'rule_name' => $rule->get_name() );
                }
            }
        }
        if( $switch_back ){
            date_default_timezone_set( 'UTC' );
        }
        $default_price = apply_filters( 'webba_after_pricing_rule_applied', $default_price, $booking, $bookings );
        return array( 'price' => $default_price, 'price_details' => $price_details );

    }

    static function get_multiple_booking_price( $booking_ids ){
        $total = 0;
        foreach( $booking_ids as $booking_id ){
            $booking = new WBK_Booking( $booking_id );
            if( $booking->get_name() == '' ){
                continue;
            }
            $total += $booking->get_price() * $booking->get_quantity();
        }
        return $total;
    }

    static function get_tax_for_messages(){
        $tax_rule = get_option( 'wbk_tax_for_messages', 'paypal' );
        if( $tax_rule == 'paypal' ){
            $tax = get_option( 'wbk_paypal_tax', 0 );
        }
        if( $tax_rule == 'stripe' ){
            $tax = get_option( 'wbk_stripe_tax', 0 );
        }
        if( $tax_rule == 'none' ){
            $tax = 0;
        }
        return $tax;
    }

    static function get_total_amount( $sub_total, $tax ){
        if( is_numeric( $tax ) && $tax > 0 ){
            $tax_amount = ( ( $sub_total ) / 100 ) * $tax;
            $total = $sub_total + $tax_amount;
        } else {
            $total = $sub_total;
        }
        return $total;
    }

    static function get_tax_amount( $sub_total, $tax ){
        if( is_numeric( $tax ) && $tax > 0 ){
            $tax_amount = ( ( $sub_total ) / 100 ) * $tax;
        } else {
            $tax_amount = 0;
        }
        return $tax_amount;
    }
    static function get_servcie_fees( $booking_ids ){
        $service_fees = array();
        $service_fee_descriptions = array();
        if( is_array( $booking_ids ) ){
            foreach( $booking_ids as $booking_id ){
                $booking = new WBK_Booking( $booking_id );
                if( $booking->get_name() == '' ){
                    continue;
                }
                $service = new WBK_Service( $booking->get_service() );
                if( $service->get_name() == '' ){
                    continue;
                }
                if( $service->get_fee() !== null ){
                    if( is_numeric( $service->get_fee() ) ){
                        $service_fees[ $booking->get_service() ] = $service->get_fee();
                        $service_fee_description = get_option( 'wbk_service_fee_description' );
                        $service_fee_description = str_replace( '#service', $service->get_name(), $service_fee_description );
                        $service_fee_descriptions[ $booking->get_service() ] = $service_fee_description;
                    }
                }
            }
        }
        $service_fee_total = 0;
        foreach( $service_fees as $fee ){
            $service_fee_total += $fee;
        }
        return array( $service_fee_total, $service_fees, $service_fee_descriptions );
    }

    static function get_total_tax_fess( $bookings ){
        $total_amount = self::get_multiple_booking_price( $bookings );
        $service_fee = self::get_servcie_fees( $bookings );

        if( get_option( 'wbk_do_not_tax_deposit', '' ) == 'true' ){
            $tax_value = self::get_tax_amount( $total_amount, self::get_tax_for_messages() );
            $total_amount += $tax_value + $service_fee[0];
        } else {
            $total_amount += $service_fee[0];
            $tax_value = self::get_tax_amount( $total_amount, self::get_tax_for_messages() );
            $total_amount += $tax_value;
        }

        return $total_amount;
    }

    static function get_total_detailed( $bookings, $tax = null ){
        $total_amount = self::get_multiple_booking_price( $bookings );
        $service_fee = self::get_servcie_fees( $bookings );
        if( is_null( $tax ) ){
            $tax = self::get_tax_for_messages();
        }
        if( get_option( 'wbk_do_not_tax_deposit', '' ) == 'true' ){
            $tax_value = self::get_tax_amount( $total_amount, $tax );
            $total_amount += $tax_value + $service_fee[0];
        } else {
            $total_amount += $service_fee[0];
            $tax_value = self::get_tax_amount( $total_amount, $tax );
            $total_amount += $tax_value;
        }

        return array( 'total' => $total_amount,
                      'fee' => $service_fee[0],
                      'tax' => $tax_value );
    }

}
