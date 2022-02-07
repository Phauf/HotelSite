<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
function wbk_woocommerce_checkout_fields( $fields )
{
    if ( get_option( 'wbk_woo_prefil_fields', '' ) != 'true' ) {
        return $fields;
    }
    if ( !session_id() ) {
        session_start();
    }
    if ( isset( $_SESSION['wbk_name'] ) ) {
        $fields['billing']['billing_first_name']['default'] = $_SESSION['wbk_name'];
    }
    if ( isset( $_SESSION['wbk_last_name'] ) ) {
        $fields['billing']['billing_last_name']['default'] = $_SESSION['wbk_last_name'];
    }
    if ( isset( $_SESSION['wbk_email'] ) ) {
        $fields['billing']['billing_email']['default'] = $_SESSION['wbk_email'];
    }
    if ( isset( $_SESSION['wbk_email'] ) ) {
        $fields['billing']['billing_phone']['default'] = $_SESSION['wbk_phone'];
    }
    return $fields;
}

function wbK_woocommerce_coupon_is_valid( $value, $coupon, $discounts )
{
    foreach ( $discounts->get_items() as $item ) {
        
        if ( isset( $item->object['wbk_appointment_ids'] ) ) {
            $appointment_ids = explode( ',', $item->object['wbk_appointment_ids'] );
            foreach ( $appointment_ids as $appointment_id ) {
                $service_id = WBK_Db_Utils::getServiceIdByAppointmentId( $appointment_id );
                if ( $service_id != false ) {
                    if ( !WBK_Validator::checkCoupon( $coupon->get_code(), $service_id ) ) {
                        $value = false;
                    }
                }
            }
        }
    
    }
    return $value;
}

function wbk_delete_order_item( $item_id )
{
    $order_item = new WC_Order_Item_Product( $item_id );
    
    if ( $order_item->get_product_id() == get_option( 'wbk_woo_product_id', '' ) ) {
        if ( get_option( 'wbk_woo_product_id', '' ) == '' ) {
            return;
        }
        $item_meta = wc_get_order_item_meta( $item_id, 'IDs', true );
        if ( $item_meta == '' ) {
            return;
        }
        $appointment_ids = explode( ',', $item_meta );
        
        if ( get_option( 'wbk_appointments_default_status', 'approved' ) == 'approved' ) {
            $status = 'approved';
        } else {
            $status = 'pending';
        }
        
        foreach ( $appointment_ids as $appointment_id ) {
            WBK_Db_Utils::setAppointmentStatus( $appointment_id, $status );
            WBK_Db_Utils::setPaymentMethodToAppointment( $appointment_id, '' );
            WBK_Db_Utils::setPaymentId( $appointment_id, '' );
        }
    }

}

function wbk_order_deleted( $order_id )
{
    global  $post_type ;
    if ( $post_type !== 'shop_order' ) {
        return;
    }
    $order = new WC_Order( $order_id );
    $appointment_ids = array();
    foreach ( $order->get_items() as $item_id => $item ) {
        
        if ( $item->get_product_id() == get_option( 'wbk_woo_product_id', '' ) ) {
            $appointment_ids_this = explode( ',', wc_get_order_item_meta( $item_id, 'IDs', true ) );
            $appointment_ids = array_merge( $appointment_ids, $appointment_ids_this );
        }
    
    }
    
    if ( get_option( 'wbk_appointments_default_status', 'approved' ) == 'approved' ) {
        $status = 'approved';
    } else {
        $status = 'pending';
    }
    
    foreach ( $appointment_ids as $appointment_id ) {
        WBK_Db_Utils::setAppointmentStatus( $appointment_id, $status );
        WBK_Db_Utils::setPaymentMethodToAppointment( $appointment_id, '' );
        WBK_Db_Utils::setPaymentId( $appointment_id, '' );
    }
}

function wbk_order_cancelled_refunded( $order_id )
{
    $order = new WC_Order( $order_id );
    $appointment_ids = array();
    foreach ( $order->get_items() as $item_id => $item ) {
        
        if ( $item->get_product_id() == get_option( 'wbk_woo_product_id', '' ) ) {
            $appointment_ids_this = explode( ',', wc_get_order_item_meta( $item_id, 'IDs', true ) );
            $appointment_ids = array_merge( $appointment_ids, $appointment_ids_this );
        }
    
    }
    
    if ( get_option( 'wbk_appointments_default_status', 'approved' ) == 'approved' ) {
        $status = 'approved';
    } else {
        $status = 'pending';
    }
    
    foreach ( $appointment_ids as $appointment_id ) {
        WBK_Db_Utils::setAppointmentStatus( $appointment_id, $status );
        WBK_Db_Utils::setPaymentMethodToAppointment( $appointment_id, '' );
        WBK_Db_Utils::setPaymentId( $appointment_id, '' );
    }
}

function wbk_add_booking_data_to_cart_item( $cart_item_data, $product_id, $variation_id )
{
    return $cart_item_data;
}

function wbk_display_booking_data_text_cart( $item_data, $cart_item )
{
    if ( empty($cart_item['wbk_appointment_ids']) ) {
        return $item_data;
    }
    $appointment_ids = explode( ',', wc_clean( $cart_item['wbk_appointment_ids'] ) );
    $item_names = WBK_Db_Utils::getPymentItemNamesByAppoiuntmentIds( $appointment_ids );
    $meta_key = wbk_get_translation_string( 'wbk_product_meta_key', 'wbk_product_meta_key', 'Appointments' );
    $item_data[] = array(
        'key'     => $meta_key,
        'value'   => $item_names,
        'display' => '',
    );
    return $item_data;
}

function wbk_calculate_booking_product_price( $cart_object )
{
    if ( !WC()->session->__isset( "reload_checkout" ) ) {
        foreach ( $cart_object->cart_contents as $key => $value ) {
            $prod_id = $value['data']->get_id();
            if ( $prod_id == get_option( 'wbk_woo_product_id', '' ) ) {
                
                if ( isset( $value['wbk_appointment_ids'] ) ) {
                    $appointment_ids = explode( ',', $value['wbk_appointment_ids'] );
                    $price = WBK_Price_Processor::get_multiple_booking_price( $appointment_ids );
                    $service_fee = WBK_Price_Processor::get_servcie_fees( $appointment_ids );
                    $price += $service_fee[0];
                    $credits_amount = get_post_meta( $prod_id, '_credits_amount', true );
                    if ( !$credits_amount ) {
                        $value['data']->set_price( $price );
                    }
                    if ( $price == 0 ) {
                        $cart_object->remove_cart_item( $key );
                    }
                }
            
            }
        }
    }
}

function wbk_add_booking_text_to_order_items(
    $item,
    $cart_item_key,
    $values,
    $order
)
{
    if ( empty($values['wbk_appointment_ids']) ) {
        return;
    }
    $appointment_ids = explode( ',', wc_clean( $values['wbk_appointment_ids'] ) );
    $item_names = WBK_Db_Utils::getPymentItemNamesByAppoiuntmentIds( $appointment_ids );
    $meta_key = wbk_get_translation_string( 'wbk_product_meta_key', 'wbk_product_meta_key', 'Appointments' );
    $item->add_meta_data( $meta_key, $item_names );
    $item->add_meta_data( 'IDs', $values['wbk_appointment_ids'] );
}

function wbk_woocommerce_payment_complete( $order_id )
{
    $order = wc_get_order( $order_id );
    $appointment_ids = array();
    foreach ( $order->get_items() as $item_id => $item ) {
        if ( !is_object( $item ) ) {
            continue;
        }
        
        if ( $item->get_product_id() == get_option( 'wbk_woo_product_id', '' ) ) {
            $appointment_ids_this = explode( ',', wc_get_order_item_meta( $item_id, 'IDs', true ) );
            $appointment_ids = array_merge( $appointment_ids, $appointment_ids_this );
        }
    
    }
    $update_status = get_option( 'wbk_woo_update_status', 'paid' );
    foreach ( $appointment_ids as $appointment_id ) {
        
        if ( $update_status == 'disabled' ) {
            WBK_Db_Utils::setAppointmentStatus( $appointment_id, 'woocommerce' );
        } else {
            WBK_Db_Utils::setAppointmentStatus( $appointment_id, $update_status );
        }
        
        WBK_Db_Utils::setPaymentMethodToAppointment( $appointment_id, '<a target="_blank" href="' . get_admin_url() . 'post.php?post=' . $order_id . '&action=edit">#' . $order_id . '</a>' );
        $prev_time_zone = date_default_timezone_get();
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        if ( get_option( 'wbk_gg_when_add', 'onbooking' ) == 'onpaymentorapproval' ) {
            
            if ( !WBK_Db_Utils::idEventAddedToGoogle( $appointment_id ) ) {
                $service_id = WBK_Db_Utils::getServiceIdByAppointmentId( $appointment_id );
                WBK_Db_Utils::addAppointmentDataToGGCelendar( $service_id, $appointment_id );
            }
        
        }
        date_default_timezone_set( $prev_time_zone );
    }
    wbk_email_processing_send_on_payment( $appointment_ids );
    do_action( 'wbk_woocommerce_order_placed', $appointment_ids, $order_id );
}

function wbk_woocommerce_thankyou( $order_id )
{
    $order = wc_get_order( $order_id );
    $appointment_ids = array();
    foreach ( $order->get_items() as $item_id => $item ) {
        if ( !is_object( $item ) ) {
            continue;
        }
        
        if ( $item->get_product_id() == get_option( 'wbk_woo_product_id', '' ) ) {
            $appointment_ids_this = explode( ',', wc_get_order_item_meta( $item_id, 'IDs', true ) );
            $appointment_ids = array_merge( $appointment_ids, $appointment_ids_this );
        }
    
    }
    foreach ( $appointment_ids as $appointment_id ) {
        if ( WBK_Db_Utils::getPaymentMethodByAppointmentId( $appointment_id ) != '' ) {
            continue;
        }
        WBK_Db_Utils::setAppointmentStatus( $appointment_id, 'woocommerce' );
        WBK_Db_Utils::setPaymentId( $appointment_id, $order_id );
        WBK_Db_Utils::setPaymentMethodToAppointment( $appointment_id, '<a target="_blank" href="' . get_admin_url() . 'post.php?post=' . $order_id . '&action=edit">#' . $order_id . '</a>' );
    }
}

class WBK_WooCommerce
{
    static function renderPaymentMethods( $service_id, $appointment_ids )
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
            if ( $service->getPayementMethods() == '' ) {
                return '';
            }
            $arr_items = json_decode( $service->getPayementMethods() );
            if ( !in_array( 'woocommerce', $arr_items ) ) {
                return '';
            }
        }
        $html = '';
        $woo_btn_text = wbk_get_translation_string( 'wbk_woo_button_text', 'wbk_woo_button_text', 'Add to cart' );
        $html .= '<input class="wbk-button wbk-width-100 wbk-mt-10-mb-10 wbk-payment-init wbk-payment-init-woo" data-method="woocommerce" data-app-id="' . implode( ',', $appointment_ids ) . '"  value="' . $woo_btn_text . '  " type="button">';
        return $html;
    }
    
    static function addToCart( $appointment_ids )
    {
        return json_encode( array(
            'status'  => 0,
            'details' => __( 'Payment method not supported', 'wbk' ),
        ) );
    }

}
function wbk_woocommerce_coupon_options_usage_restriction( $coupon_id, $coupon )
{
    if ( !$coupon->is_type( array( 'percent' ) ) ) {
        return;
    }
    $options = WBK_Model_Utils::get_services( true );
    wbk_woocommerce_wp_multi_select( array(
        'id'      => 'webba_services',
        'name'    => 'webba_services[]',
        'label'   => __( 'Webba Booking services', 'wbk' ),
        'options' => $options,
    ) );
    $options = WBK_Model_Utils::get_pricing_rules( true );
    wbk_woocommerce_wp_multi_select( array(
        'id'      => 'webba_pricing_rules',
        'name'    => 'webba_pricing_rules[]',
        'label'   => __( 'Webba Booking pricing rules', 'wbk' ),
        'options' => $options,
    ) );
}

function wbk_woocommerce_coupon_options_save( $post_id )
{
    $coupon = new WC_Coupon( $post_id );
    if ( !$coupon->is_type( array( 'percent' ) ) ) {
        return;
    }
    $ids = $_POST['webba_services'];
    $options = WBK_Model_Utils::get_services( true );
    $validated = array();
    foreach ( $ids as $id ) {
        if ( array_key_exists( $id, $options ) ) {
            $validated[] = $id;
        }
    }
    update_post_meta( $post_id, 'webba_services', $validated );
    $ids = $_POST['webba_pricing_rules'];
    $options = WBK_Model_Utils::get_pricing_rules( true );
    $validated = array();
    foreach ( $ids as $id ) {
        if ( array_key_exists( $id, $options ) ) {
            $validated[] = $id;
        }
    }
    update_post_meta( $post_id, 'webba_pricing_rules', $validated );
}

function wbk_woocommerce_wp_multi_select( $field, $variation_id = 0 )
{
    global  $thepostid, $post ;
    
    if ( $variation_id == 0 ) {
        $the_id = ( empty($thepostid) ? $post->ID : $thepostid );
    } else {
        $the_id = $variation_id;
    }
    
    $field['class'] = ( isset( $field['class'] ) ? $field['class'] : 'select short' );
    $field['wrapper_class'] = ( isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '' );
    $field['name'] = ( isset( $field['name'] ) ? $field['name'] : $field['id'] );
    $meta_data = maybe_unserialize( get_post_meta( $the_id, $field['id'], true ) );
    $meta_data = ( $meta_data ? $meta_data : array() );
    $field['value'] = ( isset( $field['value'] ) ? $field['value'] : $meta_data );
    echo  '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label><select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['name'] ) . '" class="' . esc_attr( $field['class'] ) . '" multiple="multiple">' ;
    foreach ( $field['options'] as $key => $value ) {
        echo  '<option value="' . esc_attr( $key ) . '" ' . (( in_array( $key, $field['value'] ) ? 'selected="selected"' : '' )) . '>' . esc_html( $value ) . '</option>' ;
    }
    echo  '</select> ' ;
    if ( !empty($field['description']) ) {
        
        if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
            echo  '<img class="help_tip" data-tip="' . esc_attr( $field['description'] ) . '" src="' . esc_url( WC()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />' ;
        } else {
            echo  '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>' ;
        }
    
    }
}

function wbk_woocommerce_coupon_get_discount_amount(
    $discount_amount,
    $discounting_amount,
    $cart_item,
    $single,
    $coupon
)
{
    if ( empty($cart_item['wbk_appointment_ids']) ) {
        return $discount_amount;
    }
    $allowed_services = array();
    $allowed_pricing_rules = array();
    if ( is_array( $coupon->get_meta( 'webba_services' ) ) ) {
        $allowed_services = $coupon->get_meta( 'webba_services' );
    }
    if ( is_array( $coupon->get_meta( 'webba_pricing_rules' ) ) ) {
        $allowed_pricing_rules = $coupon->get_meta( 'webba_pricing_rules' );
    }
    if ( count( $allowed_services ) == 0 && count( $allowed_pricing_rules ) == 0 ) {
        return $discount_amount;
    }
    $coupon_amount = $coupon->get_amount();
    $booking_ids = explode( ',', wc_clean( $cart_item['wbk_appointment_ids'] ) );
    $overriden_discount_amount = 0;
    foreach ( $booking_ids as $booking_id ) {
        $booking = new WBK_Booking( $booking_id );
        
        if ( $booking->get_amount_details() != '' ) {
            $amount_details = json_decode( $booking->get_amount_details(), true );
            if ( $amount_details == null || !is_array( $amount_details ) ) {
                continue;
            }
            foreach ( $amount_details as $item ) {
                switch ( $item['type'] ) {
                    case 'service_price':
                        
                        if ( in_array( $booking->get_service(), $allowed_services ) ) {
                            $amount = $item['amount'] * $booking->get_quantity();
                            $overriden_discount_amount += $amount * ($coupon_amount / 100);
                        }
                        
                        break;
                    case 'pricing_rule':
                        
                        if ( in_array( $item['rule_id'], $allowed_pricing_rules ) ) {
                            if ( $item['amount'] < 0 ) {
                                break;
                            }
                            $amount = $item['amount'] * $booking->get_quantity();
                            $overriden_discount_amount += $amount * ($coupon_amount / 100);
                        }
                        
                        break;
                }
            }
        }
    
    }
    return $overriden_discount_amount;
}
