<?php

//WBK stat class
// check if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class WBK_Admin_Notices
{
    public static function labelUpdate()
    {
        return;
    }
    
    public static function colorUpdate()
    {
        return;
    }
    
    public static function appearanceUpdate()
    {
        if ( get_option( 'wbk_appearance_saved', '' ) != 'true' ) {
            return '<div class="notice notice-warning is-dismissible"><p>Webba Booking: Please setup appearance settings.
					</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }
        return;
    }
    
    public static function emailLandingUpdate()
    {
        if ( get_option( 'wbk_email_landing', '' ) == '' ) {
            return '<div class="notice notice-warning is-dismissible"><p>Webba Booking: Please setup the <strong>Link to the page with Webba Booking form</strong> setting in the Email Notifications tab.
					</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }
        return;
    }
    
    public static function updateNotice()
    {
        return '';
    }
    
    public static function stripe_fields_update_norice()
    {
        $value = get_option( 'wbk_stripe_additional_fields', '' );
        if ( !is_array( $value ) ) {
            return '';
        }
        $payment_fields = WBK_Db_Utils::getPaymentFields();
        foreach ( $value as $item ) {
            if ( !isset( $payment_fields[$item] ) ) {
                return '<div class="notice notice-warning is-dismissible"><p>Webba Booking: please, update the option <strong>Additional payment information</strong> on the Stripe tab of the Settings page.
						</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
            }
        }
    }
    
    public static function wbk_4_0_update()
    {
        return '';
    }
    
    public static function sms_compability()
    {
        return '';
    }
    
    public static function stripe_conflict()
    {
    }
    
    public static function booking_form_label()
    {
        
        if ( get_option( 'wbk_multi_booking', 'disabled' ) != 'disabled' ) {
            $parts = explode( '[split]', get_option( 'wbk_form_label', '[split]' ) );
            
            if ( count( $parts ) == 2 ) {
                $string_to_check = $parts[1];
            } else {
                $string_to_check = $parts[0];
            }
            
            if ( strpos( $string_to_check, '#total_amount' ) !== false ) {
                return '<div class="notice notice-warning is-dismissible"><p>Webba Booking: the format of the option <strong>Booking form label</strong> for the multi-service booking has been changed. The placholder #total_amount should be used before [split].<br>
				</p><p>Example: #total_amount[split]#service.</p>
				<p>If you are not using a multi-service, please ignore this message.</p>
				<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
            }
        }
    
    }

}