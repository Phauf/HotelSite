<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
use  PayPal\Auth\OAuthTokenCredential ;
use  PayPal\Rest\ApiContext ;
use  PayPal\Api\Amount ;
use  PayPal\Api\Details ;
use  PayPal\Api\Item ;
use  PayPal\Api\ItemList ;
use  PayPal\Api\Payer ;
use  PayPal\Api\Payment ;
use  PayPal\Api\RedirectUrls ;
use  PayPal\Api\Transaction ;
use  PayPal\Api\ExecutePayment ;
use  PayPal\Api\PaymentExecution ;
class WBK_PayPal
{
    protected  $apiContext ;
    protected  $currency ;
    protected  $tax ;
    protected  $fee ;
    protected  $referer ;
    protected  $experience_profile_id ;
    public function init( $referer, $appointment_ids )
    {
        return FALSE;
    }
    
    public function createPaymentPaypal(
        $item_names,
        $price,
        $quantity,
        $sku,
        $amount_of_discount,
        $discount_item_name,
        $service_fee
    )
    {
        return FALSE;
    }
    
    public function createPayment( $method, $app_ids, $coupon )
    {
        return -1;
    }
    
    protected function createWebProfile()
    {
        return FALSE;
    }
    
    public function getWebProfileId()
    {
        return '';
    }
    
    public function executePayment( $paymentId, $payerId )
    {
        return false;
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
            if ( $service->getPayementMethods() == '' ) {
                return '';
            }
            $arr_items = json_decode( $service->getPayementMethods() );
            if ( !in_array( 'paypal', $arr_items ) ) {
                return '';
            }
        }
        $html = '';
        $paypal_btn_text = get_option( 'wbk_payment_pay_with_paypal_btn_text', '' );
        if ( $paypal_btn_text == '' ) {
            $paypal_btn_text = sanitize_text_field( $wbk_wording['paypal_btn_text'] );
        }
        $html .= '<input class="wbk-button wbk-width-100 wbk-mt-10-mb-10 wbk-payment-init' . $button_class . '" data-method="paypal" data-app-id="' . implode( ',', $appointment_ids ) . '"  value="' . $paypal_btn_text . '  " type="button">';
        return $html;
    }
    
    private function conversion( $price )
    {
        $multiplier = get_option( 'wbk_paypal_multiplier', '' );
        
        if ( $multiplier == '' ) {
            return $price;
        } elseif ( filter_var( $multiplier, FILTER_VALIDATE_FLOAT ) && $multiplier > 0 ) {
            return number_format(
                floatval( $multiplier ) * floatval( $price ),
                2,
                '.',
                ''
            );
        }
        
        return $price;
    }

}