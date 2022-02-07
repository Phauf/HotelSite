<?php

/**
* Plugin Name: Webba Booking
* Plugin URI: https://webba-booking.com
* Description: Responsive appointment and reservation plugin.
* Version: 4.1.6
* Author: WebbaPlugins
* Text Domain: wbk
* Author URI: https://webba-booking.com
*/
// check if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// added for the capabilities with the old versions of WordPress
if ( !function_exists( 'wp_date' ) ) {
    function wp_date( $format, $timestamp, $timezone )
    {
        return date_i18n( $format, $timestamp );
    }

}

if ( !function_exists( 'wbk_fs' ) ) {
    // Create a helper function for easy SDK access.
    function wbk_fs()
    {
        global  $wbk_fs ;
        
        if ( !isset( $wbk_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $wbk_fs = fs_dynamic_init( array(
                'id'             => '4728',
                'slug'           => 'webba-booking-lite',
                'premium_slug'   => 'webba-booking',
                'type'           => 'plugin',
                'public_key'     => 'pk_89934c199e211bbc3aa24e2396ef9',
                'is_premium'     => false,
                'premium_suffix' => 'Professional',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'menu'           => array(
                'slug'       => 'wbk-main',
                'first-path' => 'admin.php?page=wbk-options',
                'support'    => false,
            ),
                'is_live'        => true,
            ) );
        }
        
        return $wbk_fs;
    }
    
    // Init Freemius.
    wbk_fs();
    // Signal that SDK was initiated.
    do_action( 'wbk_fs_loaded' );
}


if ( !defined( 'WP_WEBBA_BOOKING__PLUGIN_DIR' ) ) {
    define( 'WP_WEBBA_BOOKING__PLUGIN_DIR', dirname( __FILE__ ) );
    define( 'WP_WEBBA_BOOKING__PLUGIN_URL', plugins_url( plugin_basename( WP_WEBBA_BOOKING__PLUGIN_DIR ) ) );
}

if ( !defined( 'WP_WEBBA_BOOKING__VERSION' ) ) {
    define( 'WP_WEBBA_BOOKING__VERSION', '4.1.5' );
}

if ( !function_exists( 'wbk_load_textdomain' ) ) {
    include 'common/class_wbk_entity.php';
    include 'backend/class_wbk_backend.php';
    include 'common/class_wbk_db_utils.php';
    include 'common/class_wbk_ajax_controller.php';
    include 'frontend/class_wbk_frontend.php';
    include 'common/class_wbk_email_notifications.php';
    include 'backend/class_wbk_admin_notices.php';
    require 'common/wbk_wording.php';
    require 'common/PayPal/class_wbk_paypal.php';
    
    if ( get_option( 'wbk_stripe_publishable_key', '' ) != '' ) {
        require 'common/stripe/class_wbk_stripe.php';
    } else {
        require 'common/stripe/class_wbk_stripe_blank.php';
    }
    
    require 'common/woocommerce/class_wbk_woocommerce.php';
    
    if ( version_compare( PHP_VERSION, '5.4.0' ) >= 0 ) {
        require 'common/google_api/class_wbk_gg.php';
    } else {
        require 'common/google_api/class_wbk_gg_blank.php';
    }
    
    
    if ( version_compare( PHP_VERSION, '5.6.0' ) >= 0 ) {
        include 'common/ical/class_wbk_ical.php';
    } else {
        include 'common/ical/class_wbk_ical_blank.php';
    }
    
    add_action( 'init', 'wbk_update_data', 30 );
    add_action( 'admin_init', 'wbk_admin_init' );
    add_action( 'wbk_daily_event', 'wbk_daily' );
    add_action( 'plugins_loaded', 'wbk_load_textdomain', 10 );
    add_action( 'init', 'wbk_delete_expired_appointments' );
    add_filter(
        'plugion_strings',
        'wbk_plugion_strings',
        10,
        1
    );
    include 'model/class-wbk-model-object.php';
    include 'model/class-wbk-service.php';
    include 'model/class-wbk-service-category.php';
    include 'model/class-wbk-coupon.php';
    include 'model/class-wbk-pricing-rule.php';
    include 'model/class-wbk-booking.php';
    include 'utilities/class_wbk_time_math_utils.php';
    include 'utilities/class_wbk_model_utils.php';
    include 'utilities/class_wbk_user_utils.php';
    include 'vendor/plugion/autoload.php';
    include 'plugion_extensions/class-wbk-pe-business-hours.php';
    include 'plugion_extensions/class-wbk-google-access-token.php';
    include 'plugion_extensions/class-wbk-pe-date.php';
    include 'plugion_extensions/class-wbk-pe-time.php';
    include 'plugion_extensions/class-wbk-pe-app-custom-data.php';
    include 'plugion_extensions/plugion_hooks.php';
    include 'controller/class-wbk-schedule-processor.php';
    include 'controller/class-wbk-price-processor.php';
    include 'controller/class-wbk-placeholder-processor.php';
    include 'controller/class-wbk-options-processor.php';
    include 'controller/class-wbk-controller.php';
    // init plugion extensions
    $wbk_google_access_token_obj = new WBK_Google_Access_Token();
    $wbk_pe_business_hours_obj = new WBK_PE_Business_Hours();
    $wbk_pe_date_obj = new WBK_PE_Date();
    $wbk_pe_time_obj = new WBK_PE_Time();
    $wbk_controller = new WBK_Controller();
    $wbk_pe_appointment_custom_data = new WBK_PE_Appointment_Custom_Data();
    include 'renderer/class-wbk-renderer.php';
    include 'model/class-wbk-model.php';
    include 'model/class-wbk-model-updater.php';
    include 'controller/class-wbk-assets-manager.php';
    $wbk_model = new WBK_Model();
    // common ajax controller
    $ajaxController = new WBK_Ajax_Controller();
    // check frontend / backend
    
    if ( is_admin() ) {
        $backend = new WBK_Backend();
    } else {
        $frontend = new WBK_Frontend();
    }
    
    $wbk_assets_manager = new WBK_Assets_Manager( array( array(
        'backend',
        array(
        'wbk-services',
        'wbk-email-templates',
        'wbk-service-categories',
        'wbk-coupons',
        'wbk-pricing-rules'
    ),
        'wbk-common-style',
        WP_WEBBA_BOOKING__PLUGIN_URL . '/public/wbk.css',
        array(),
        WP_WEBBA_BOOKING__VERSION
    ) ), array(
        array(
        'backend',
        array(
        'wbk-services',
        'wbk-email-templates',
        'wbk-service-categories',
        'wbk-coupons',
        'wbk-pricing-rules'
    ),
        'wbk-tinymce',
        WP_WEBBA_BOOKING__PLUGIN_URL . '/backend/js/wbk-tinymce.js',
        array(),
        WP_WEBBA_BOOKING__VERSION
    ),
        array(
        'backend',
        array(
        'wbk-services',
        'wbk-email-templates',
        'wbk-service-categories',
        'wbk-coupons',
        'wbk-pricing-rules'
    ),
        'jquery-repeater',
        WP_WEBBA_BOOKING__PLUGIN_URL . '/public/vendor/jquery.repeater/jquery.repeater.min.js',
        array( 'jquery' ),
        WP_WEBBA_BOOKING__VERSION
    ),
        array(
        'backend',
        array(
        'wbk-services',
        'wbk-email-templates',
        'wbk-service-categories',
        'wbk-appointments',
        'wbk-coupons',
        'wbk-pricing-rules'
    ),
        'wbk-common-script',
        WP_WEBBA_BOOKING__PLUGIN_URL . '/public/wbk.js',
        array(
        'jquery',
        'jquery-ui-slider',
        'jquery-touch-punch',
        'jquery-ui-draggable'
    ),
        WP_WEBBA_BOOKING__VERSION
    ),
        array(
        'frontend',
        null,
        'wbk-common-script',
        WP_WEBBA_BOOKING__PLUGIN_URL . '/public/wbk.js',
        array( 'jquery' ),
        WP_WEBBA_BOOKING__VERSION
    )
    ) );
}

// activation/deactivation hooks
register_activation_hook( __FILE__, 'wbk_activate' );
register_deactivation_hook( __FILE__, 'wbk_deactivate' );
register_uninstall_hook( __FILE__, 'wbk_uninstall' );
// localization
if ( !function_exists( 'wbk_load_textdomain' ) ) {
    function wbk_load_textdomain()
    {
        load_plugin_textdomain( 'wbk', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
        global  $wbk_wording ;
        $wbk_wording['service_label'] = __( 'Select service', 'wbk' );
        $wbk_wording['category_label'] = __( 'Select category', 'wbk' );
        $wbk_wording['date_extended_label'] = __( 'Book an appointment on or after', 'wbk' );
        $wbk_wording['date_basic_label'] = __( 'Book an appointment on', 'wbk' );
        $wbk_wording['slots_label'] = __( 'Available time slots', 'wbk' );
        $wbk_wording['book_thanks_message'] = __( 'Thanks for booking appointment', 'wbk' );
        $wbk_wording['hours_label'] = __( 'Suitable hours', 'wbk' );
        $wbk_wording['form_label'] = __( 'Fill in a form', 'wbk' );
        $wbk_wording['quantity_label'] = __( 'Booking items count', 'wbk' );
        $wbk_wording['date_input_placeholder'] = __( 'date...', 'wbk' );
        $wbk_wording['booked_text'] = __( 'Booked', 'wbk' );
        $wbk_wording['book_text'] = __( 'Book', 'wbk' );
        $wbk_wording['form_name'] = __( 'Name', 'wbk' );
        $wbk_wording['form_email'] = __( 'Email', 'wbk' );
        $wbk_wording['form_phone'] = __( 'Phone', 'wbk' );
        $wbk_wording['form_comment'] = __( 'Comment', 'wbk' );
        $wbk_wording['thanks_for_booking'] = __( 'Thanks for booking appointment', 'wbk' );
        $wbk_wording['paypal_btn_text'] = __( 'Pay with PayPal', 'wbk' );
        $wbk_wording['payment_details'] = __( 'Payment details', 'wbk' );
        $wbk_wording['payment_item_name'] = __( '#service on #date at #time', 'wbk' );
        $wbk_wording['subtotal'] = __( 'Subtotal', 'wbk' );
        $wbk_wording['total'] = __( 'Total', 'wbk' );
        $wbk_wording['approve_payment'] = __( 'Approve payment', 'wbk' );
        $wbk_wording['payment_title'] = __( 'Payment status', 'wbk' );
        $wbk_wording['payment_complete'] = __( 'Payment completed.', 'wbk' );
        $wbk_wording['payment_canceled'] = __( 'Payment canceled.', 'wbk' );
        $wbk_wording['cancel_label'] = __( 'Cancel booking', 'wbk' );
        $wbk_wording['cancel_label_form'] = __( 'Cancel', 'wbk' );
        $wbk_wording['checkout'] = __( 'Checkout', 'wbk' );
        $wbk_wording['appointment_info'] = __( 'Appointment on #dt', 'wbk' );
        $wbk_wording['nothing_to_pay'] = __( 'There are no bookings available for payment.', 'wbk' );
        $wbk_wording['cancelation_email'] = __( 'Please, enter your email to confirm cancelation', 'wbk' );
        $wbk_wording['booking_canceled'] = __( 'Your appointment booking has been canceled.', 'wbk' );
        $wbk_wording['booking_cancel_error'] = __( 'Unable to cancel booking, please check the email you\'ve entered.', 'wbk' );
        $wbk_wording['paid_booking_cancel'] = __( 'Paid booking couldn\'t be canceled.', 'wbk' );
        $wbk_wording['paid_booking_cancel2'] = __( 'Sorry, you can not cancel because you have exceeded the time allowed to do so.', 'wbk' );
        $wbk_wording['email_landing_anchor'] = __( 'Click here to pay for your booking.', 'wbk' );
        $wbk_wording['email_landing_anchor2'] = __( 'Click here to cancel your booking.', 'wbk' );
        $wbk_wording['invalid_token'] = __( 'Appointment booking doesn\'t exist.', 'wbk' );
        $wbk_wording['add_event_canceled'] = __( 'Appointment data not added to Google Calendar.', 'wbk' );
        $wbk_wording['add_event_sucess'] = __( 'Appointment data added to Google Calendar.', 'wbk' );
        $wbk_wording['wbk_add_gg_button_text'] = __( 'Add to my Google Calendar', 'wbk' );
        $wbk_wording['wbk_email_landing_text_gg_event_add'] = __( 'Click here to add this event to your Google Calendar.', 'wbk' );
        $wbk_wording['wbk_time_slot_available_text'] = __( 'available', 'wbk' );
        $wbk_wording['wbk_coupon_field_placeholder'] = __( 'Coupon code', 'wbk' );
        $wbk_wording['wbk_coupon_applied'] = __( 'Coupon applied', 'wbk' );
        $wbk_wording['wbk_coupon_not_applied'] = __( 'Coupon not applied', 'wbk' );
        $wbk_wording['wbk_payment_discount_item'] = __( 'Discount', 'wbk' );
        $wbk_wording['wbk_woo_button_text'] = __( 'Add to cart', 'wbk' );
        $wbk_wording['wbk_product_meta_key'] = __( 'Appointments', 'wbk' );
        wbk_cleanup_attachements();
        $arrIds = WBK_Db_Utils::getServices();
        if ( function_exists( 'pll_register_string' ) ) {
            foreach ( $arrIds as $id ) {
                $service = new WBK_Service_deprecated();
                if ( !$service->setId( $id ) ) {
                    continue;
                }
                if ( !$service->load() ) {
                    continue;
                }
                pll_register_string( 'webba_service_' . $id, $service->getName(), 'webba-booking' );
            }
        }
        foreach ( $arrIds as $id ) {
            $service = new WBK_Service_deprecated();
            if ( !$service->setId( $id ) ) {
                continue;
            }
            if ( !$service->load() ) {
                continue;
            }
            do_action(
                'wpml_register_single_string',
                'wbk',
                'Service name id ' . $service->getId(),
                $service->getName()
            );
            do_action(
                'wpml_register_single_string',
                'wbk',
                'Service description id ' . $service->getId(),
                $service->getDescription()
            );
        }
    }

}
if ( !function_exists( 'wbk_get_translation_string' ) ) {
    function wbk_get_translation_string( $option, $key, $default_value )
    {
        $string = get_option( $option, __( $default_value, 'wbk' ) );
        
        if ( $string == '' ) {
            global  $wbk_wording ;
            $string = sanitize_text_field( $wbk_wording[$key] );
        }
        
        return $string;
    }

}
if ( !function_exists( 'wbk_activate' ) ) {
    function wbk_activate()
    {
        // add options
        add_option( 'wbk_start_of_week', '' );
        add_option( 'wbk_date_format', '' );
        add_option( 'wbk_time_format', '' );
        add_option( 'wbk_timezone', 'UTC' );
        add_option( 'wbk_email_customer_book_status', '' );
        add_option( 'wbk_email_customer_book_message', '<p>Dear #customer_name,</p><p>You have successfully booked #service_name on #appointment_day at #appointment_time</p><p>Thank you for choosing our company!</p>' );
        add_option( 'wbk_email_customer_book_subject', __( 'You have successfully booked an appointment', 'wbk' ) );
        add_option( 'wbk_email_customer_approve_status', '' );
        add_option( 'wbk_email_customer_approve_message', '<p>Your appointment bookin on #appointment_day at #appointment_time has been approved.</p>' );
        add_option( 'wbk_email_customer_approve_subject', __( 'Your booking has been approved', 'wbk' ) );
        add_option( 'wbk_email_admin_book_status', '' );
        add_option( 'wbk_email_admin_book_status_generate_ical', '' );
        add_option( 'wbk_email_admin_book_message', '<p>Details of booking:</p><p>Date: #appointment_day<br/>Time: #appointment_time<br/>Customer name: #customer_name<br/>Customer phone: #customer_phone<br/>Customer email: #customer_email<br/>Customer comment: #customer_comment</p><p> </p>' );
        add_option( 'wbk_email_admin_daily_status', '' );
        add_option( 'wbk_email_admin_daily_subject', 'Agenda for tomorrow\'s appointments' );
        add_option( 'wbk_email_admin_daily_message', '<p>Your tomorrow\'s appointments:<br/><br/>#tomorrow_agenda</p>' );
        add_option( 'wbk_email_customer_daily_status', '' );
        add_option( 'wbk_email_customer_daily_subject', 'Appointment reminder' );
        add_option( 'wbk_email_customer_daily_message', '<p>Dear, #customer_name!</p><p>We would like to remind that you have booked the #service_name<br/>tomorrow at #appointment_time.</p>' );
        add_option( 'wbk_email_admin_daily_time', '64800' );
        add_option( 'wbk_email_admin_book_subject', __( 'New appointment booking', 'wbk' ) );
        add_option( 'wbk_from_name', get_option( 'blogname' ) );
        add_option( 'wbk_from_email', get_option( 'admin_email' ) );
        add_option( 'wbk_mode', 'extended' );
        add_option( 'wbk_service_label', __( 'Select a service', 'wbk' ) );
        add_option( 'wbk_date_extended_label', __( 'Book an appointment on or after', 'wbk' ) );
        add_option( 'wbk_date_basic_label', __( 'Book an appointment on', 'wbk' ) );
        add_option( 'wbk_hours_label', __( 'Suitable hours', 'wbk' ) );
        add_option( 'wbk_slots_label', __( 'Available time slots', 'wbk' ) );
        add_option( 'wbk_form_label', html_entity_decode( __( 'You are booking #service on #date  at #time <br>Please, fill in a form:', 'wbk' ) ) );
        add_option( 'wbk_book_items_quantity_label', __( 'Booking items count', 'wbk' ) );
        add_option( 'wbk_book_thanks_message', __( 'Thanks for booking appointment', 'wbk' ) );
        add_option( 'wbk_book_not_found_message', __( 'Unfortunately we were unable to meet your search criteria. Please change the criteria and try again.', 'wbk' ) );
        add_option( 'wbk_phone_mask', 'enabled' );
        add_option( 'wbk_phone_format', '(999) 999-9999' );
        add_option( 'wbk_booking_forms', '' );
        add_option( 'wbk_button_background', '#186762' );
        add_option( 'wbk_button_color', '#ffffff' );
        add_option( 'wbk_timeslot_time_string', 'start' );
        add_option( 'wbk_show_booked_slots', 'disabled' );
        add_option( 'wbk_booked_text', __( 'Booked', 'wbk' ) );
        add_option( 'wbk_email_secondary_book_status', '' );
        add_option( 'wbk_email_secondary_book_subject', __( 'Appointment notification', 'wbk' ) );
        add_option( 'wbk_email_secondary_book_message', '<p>Dear #group_customer_name,</p><p>#customer_name invited you to #service_name on #appointment_day at #appointment_time.</p>' );
        add_option( 'wbk_appointments_auto_lock', 'disabled' );
        add_option( 'wbk_name_label', __( 'Name', 'wbk' ) );
        add_option( 'wbk_email_label', __( 'Email', 'wbk' ) );
        add_option( 'wbk_phone_label', __( 'Phone', 'wbk' ) );
        add_option( 'wbk_comment_label', __( 'Comment', 'wbk' ) );
        add_option( 'wbk_date_input_placeholder', __( 'date', 'wbk' ) );
        add_option( 'wbk_book_text_form', __( 'Book', 'wbk' ) );
        add_option( 'wbk_book_text_timeslot', __( 'Book', 'wbk' ) );
        add_option( 'wbk_payment_pay_with_paypal_btn_text', __( 'Pay now with PayPal', 'wbk' ) );
        add_option( 'wbk_payment_pay_with_cc_btn_text', __( 'Pay now with credit card', 'wbk' ) );
        add_option( 'wbk_payment_details_title', __( 'Payment details', 'wbk' ) );
        add_option( 'wbk_payment_item_name', __( '#service on #date at #time', 'wbk' ) );
        add_option( 'wbk_payment_price_format', '$#price' );
        add_option( 'wbk_payment_subtotal_title', __( 'Subtotal', 'wbk' ) );
        add_option( 'wbk_payment_total_title', __( 'Total', 'wbk' ) );
        add_option( 'wbk_paypal_currency', 'USD' );
        add_option( 'wbk_paypal_tax', 0 );
        add_option( 'wbk_paypal_fee', 0 );
        add_option( 'wbk_payment_approve_text', __( 'Approve payment', 'wbk' ) );
        add_option( 'wbk_payment_result_title', __( 'Payment status', 'wbk' ) );
        add_option( 'wbk_payment_success_message', __( 'Payment completed.', 'wbk' ) );
        add_option( 'wbk_payment_cancel_message', __( 'Payment canceled.', 'wbk' ) );
        add_option( 'wbk_paypal_hide_address', 'disabled' );
        add_option( 'wbk_hide_from_on_booking', 'disabled' );
        add_option( 'wbk_check_short_code', 'disabled' );
        add_option( 'wbk_show_cancel_button', 'disabled' );
        add_option( 'wbk_cancel_button_text', __( 'Cancel booking', 'wbk' ) );
        add_option( 'wbk_disable_day_on_all_booked', 'disabled' );
        add_option( 'wbk_super_admin_email', '' );
        add_option( 'wbk_multi_booking', 'disabled' );
        add_option( 'wbk_checkout_button_text', __( 'Checkout', 'wbk' ) );
        add_option( 'wbk_appointments_auto_lock_mode', 'all' );
        add_option( 'wbk_appointment_information', __( 'Appointment on #dt', 'wbk' ) );
        add_option( 'wbk_show_local_time', 'disabled' );
        add_option( 'wbk_local_time_format', __( 'Your local time:<br>#ds<br>#ts', 'wbk' ) );
        add_option( 'wbk_appointments_default_status', 'approved' );
        add_option( 'wbk_appointments_allow_payments', 'disabled' );
        add_option( 'wbk_nothing_to_pay_message', __( 'There are no bookings available for payment.', 'wbk' ) );
        add_option( 'wbk_booking_couldnt_be_canceled', __( 'Paid booking couldn\'t be canceled.', 'wbk' ) );
        add_option( 'wbk_booking_couldnt_be_canceled2', __( 'Sorry, you can not cancel because you have exceeded the time allowed to do so.', 'wbk' ) );
        add_option( 'wbk_booking_cancel_email_label', __( 'Please, enter your email to confirm cancelation', 'wbk' ) );
        add_option( 'wbk_booking_canceled_message', __( 'Your appointment booking has been canceled.', 'wbk' ) );
        add_option( 'wbk_booking_cancel_error_message', __( 'Unable to cancel booking, please check the email you\'ve entered.', 'wbk' ) );
        add_option( 'wbk_email_landing_text', __( 'Click here to pay for your booking.', 'wbk' ) );
        add_option( 'wbk_email_landing_text_cancel', __( 'Click here to cancel your booking.', 'wbk' ) );
        add_option( 'wbk_appointments_delete_not_paid_mode', 'disabled' );
        add_option( 'wbk_appointments_expiration_time', '60' );
        add_option( 'wbk_date_format_backend', 'm/d/y' );
        add_option( 'wbk_csv_delimiter', 'comma' );
        add_option( 'wbk_appointments_auto_lock_group', 'lock' );
        add_option( 'wbk_appointments_delete_payment_started', 'skip' );
        add_option( 'wbk_email_customer_book_multiple_mode', 'foreach' );
        add_option( 'wbk_email_adimn_appointment_cancel_status', '' );
        add_option( 'wbk_email_adimn_appointment_cancel_subject', __( 'Appointment canceled', 'wbk' ) );
        add_option( 'wbk_email_adimn_appointment_cancel_message', '<p>#customer_name canceled the appointment with #service_name on #appointment_day at #appointment_time</p>' );
        add_option( 'wbk_email_customer_appointment_cancel_status', '' );
        add_option( 'wbk_email_customer_appointment_cancel_subject', __( 'Your appointment canceled', 'wbk' ) );
        add_option( 'wbk_email_customer_appointment_cancel_message', '<p>Your appointment with #service_name on #appointment_day at #appointment_time has been canceled</p>' );
        add_option( 'wbk_multi_booking_max', '' );
        add_option( 'wbk_phone_required', '3' );
        add_option( 'wbk_pickadate_load', 'yes' );
        add_option( 'wbk_customer_name_output', '#name' );
        add_option( 'wbk_skip_timeslot_select', 'disabled' );
        add_option( 'wbk_cancellation_buffer', '' );
        add_option( 'wbk_gg_calendar_event_title', '#customer_name' );
        add_option( 'wbk_gg_calendar_event_description', '#customer_name #customer_phone' );
        add_option( 'wbk_places_selection_mode', 'normal' );
        add_option( 'wbk_show_service_description', 'disabled' );
        add_option( 'wbk_email_customer_send_invoice', 'disabled' );
        add_option( 'wbk_email_customer_invoice_subject', __( 'Invoice', 'wbk' ) );
        add_option( 'wbk_allow_manage_by_link', 'no' );
        add_option( 'wbk_email_landing_text_cancel_admin', __( 'Click here to cancel this booking.', 'wbk' ) );
        add_option( 'wbk_email_landing_text_approve_admin', __( 'Click here to approve this booking.', 'wbk' ) );
        add_option( 'wbk_gg_calendar_event_title_customer', '#service_name' );
        add_option( 'wbk_gg_calendar_event_description_customer', 'Your appointment id is #appointment_id' );
        add_option( 'wbk_show_locked_as_booked', 'no' );
        add_option( 'wbk_gg_calendar_add_event_canceled', __( 'Appointment data not added to Google Calendar.', 'wbk' ) );
        add_option( 'wbk_gg_calendar_add_event_success', __( 'Appointment data added to Google Calendar.', 'wbk' ) );
        add_option( 'wbk_add_gg_button_text', __( 'Add to my Google Calendar', 'wbk' ) );
        add_option( 'wbk_email_landing_text_gg_event_add', __( 'Click here to add this event to your Google Calendar.', 'wbk' ) );
        add_option( 'wbk_time_slot_available_text', __( 'available', 'wbk' ) );
        add_option( 'wbk_allow_attachemnt', 'no' );
        add_option( 'wbk_attachment_file_types', 'image/*' );
        add_option( 'wbk_stripe_api_error_message', 'Payment failed: #response' );
        add_option( 'wbk_stripe_card_element_error_message', 'incorrect input' );
        add_option( 'wbk_stripe_publishable_key', '' );
        add_option( 'wbk_stripe_secret_key', '' );
        add_option( 'wbk_stripe_currency', 'USD' );
        add_option( 'wbk_load_stripe_js', 'yes' );
        add_option( 'wbk_stripe_card_input_mode', 'no' );
        add_option( 'wbk_stripe_tax', '0' );
        add_option( 'wbk_stripe_button_text', __( 'Pay with credit card', 'wbk' ) );
        add_option( 'wbk_show_suitable_hours', 'yes' );
        add_option( 'wbk_tax_for_messages', 'paypal' );
        add_option( 'wbk_allow_coupons', 'disabled' );
        add_option( 'wbk_paypal_auto_redirect', 'disabled' );
        add_option( 'wbk_category_label', __( 'Select category', 'wbk' ) );
        add_option( 'wbk_price_fractional', '2' );
        add_option( 'wbk_gg_created_by', 'webba_booking' );
        add_option( 'wbk_night_hours', '0' );
        add_option( 'wbk_appointments_expiration_time_pending', '0' );
        add_option( 'wbk_order_service_by', 'a-z' );
        add_option( 'wbk_gdrp', 'disabled' );
        add_option( 'wbk_gg_when_add', 'onbooking' );
        add_option( 'wbk_woo_button_text', __( 'Add to cart', 'wbk' ) );
        add_option( 'wbk_product_meta_key', __( 'Appointments', 'wbk' ) );
        add_option( 'wbk_allow_ongoing_time_slot', 'disallow' );
        add_option( 'wbk_appointments_autolock_avail_limit', '' );
        add_option( 'wbk_date_input', 'classic' );
        add_option( 'wbk_date_input_dropdown_count', '15' );
        add_option( 'wbk_appointments_lock_timeslot_if_parital_booked', '' );
        add_option( 'wbk_email_current_invoice_number', '1' );
        add_option( 'wbk_show_details_prev_booking', 'disabled' );
        add_option( 'wbk_scroll_container', 'html, body' );
        add_option( 'wbk_gg_ignore_free', 'no' );
        add_option( 'wbk_avaiability_popup_calendar', '360' );
        add_option( 'wbk_server_time_format', '' );
        add_option( 'wbk_server_time_format2', '' );
        add_option( 'wbk_stripe_mob_font_size', '' );
        add_option( 'wbk_backend_select_services_onload', 'disabled' );
        add_option( 'wbk_backend_select_services_onload', 'disabled' );
        add_option( 'wbk_woo_auto_add_to_cart', 'disabled' );
        add_option( 'wbk_email_reminders_only_for_approved', '' );
        add_option( 'wbk_recurring_holidays', '' );
        add_option( 'wbk_day_label', '#date' );
        add_option( 'wbk_price_separator', '.' );
        add_option( 'wbk_scroll_value', '120' );
    }

}
if ( !function_exists( 'wbk_daily' ) ) {
    function wbk_daily()
    {
        $noifications = new WBK_Email_Notifications( 0, 0 );
        $noifications->send( 'daily' );
    }

}

if ( !function_exists( 'wbk_update_data' ) ) {
    if ( is_plugin_active( 'webba-booking-lite/webba-booking-lite.php' ) && is_plugin_active( 'webba-booking/webba-booking-lite.php' ) ) {
        add_action( 'admin_notices', 'wbk_deactivation_alert' );
    }
    function wbk_update_data()
    {
        WBK_Db_Utils::createHtFile();
        WBK_Model_Updater::run_previous_update();
        WBK_Model_Updater::run_update();
    }

}

if ( !function_exists( 'wbk_admin_init' ) ) {
    function wbk_admin_init()
    {
        // update appearance settings
        $slf = new SoloFramework( 'wbk_settings_data' );
        $css_compil_version = get_option( 'wbk_css_compil_version', '' );
        $plugin_data = get_plugin_data( __FILE__ );
        $current_version = $plugin_data['Version'];
        
        if ( strcmp( $current_version, $css_compil_version ) != 0 ) {
            $slf->getSetionsSet( 'wbk_extended_appearance_options' )->compileFrontendCssFromStored();
            update_option( 'wbk_css_compil_version', $current_version );
        }
    
    }

}
if ( !function_exists( 'wbk_deactivate' ) ) {
    function wbk_deactivate()
    {
        wp_clear_scheduled_hook( 'wbk_daily_event' );
    }

}
if ( !function_exists( 'wbk_uninstall' ) ) {
    function wbk_uninstall()
    {
        return;
        // drop tables
        // WBK_Db_Utils::dropTables();
        delete_option( 'wbk_start_of_week' );
        delete_option( 'wbk_date_format' );
        delete_option( 'wbk_time_format' );
        delete_option( 'wbk_timezone' );
        delete_option( 'wbk_email_customer_book_status' );
        delete_option( 'wbk_email_customer_book_message' );
        delete_option( 'wbk_email_customer_book_subject' );
        delete_option( 'wbk_email_admin_book_status' );
        delete_option( 'wbk_email_admin_book_status_generate_ical' );
        delete_option( 'wbk_email_admin_book_message' );
        delete_option( 'wbk_email_admin_daily_status', '' );
        delete_option( 'wbk_email_admin_daily_subject' );
        delete_option( 'wbk_email_admin_daily_message' );
        delete_option( 'wbk_email_customer_daily_status' );
        delete_option( 'wbk_email_customer_daily_subject' );
        delete_option( 'wbk_email_customer_daily_message' );
        delete_option( 'wbk_email_admin_daily_time' );
        delete_option( 'wbk_email_admin_book_subject' );
        delete_option( 'wbk_from_name' );
        delete_option( 'wbk_from_email' );
        delete_option( 'wbk_mode' );
        delete_option( 'wbk_service_label' );
        delete_option( 'wbk_date_extended_label' );
        delete_option( 'wbk_date_basic_label' );
        delete_option( 'wbk_hours_label' );
        delete_option( 'wbk_slots_label' );
        delete_option( 'wbk_form_label' );
        delete_option( 'wbk_book_items_quantity_label' );
        delete_option( 'wbk_book_thanks_message' );
        delete_option( 'wbk_book_not_found_message' );
        delete_option( 'wbk_phone_mask' );
        delete_option( 'wbk_phone_format' );
        delete_option( 'wbk_booking_forms' );
        delete_option( 'wbk_button_background' );
        delete_option( 'wbk_button_color' );
        delete_option( 'wbk_timeslot_time_string' );
        delete_option( 'wbk_show_booked_slots' );
        delete_option( 'wbk_booked_text' );
        delete_option( 'wbk_email_secondary_book_subject' );
        delete_option( 'wbk_email_secondary_book_message' );
        delete_option( 'wbk_appointments_auto_lock' );
        delete_option( 'wbk_name_label' );
        delete_option( 'wbk_email_label' );
        delete_option( 'wbk_phone_label' );
        delete_option( 'wbk_comment_label' );
        delete_option( 'wbk_date_input_placeholder' );
        delete_option( 'wbk_book_text_form' );
        delete_option( 'wbk_book_text_timeslot' );
        delete_option( 'wbk_payment_pay_with_paypal_btn_text' );
        delete_option( 'wbk_payment_pay_with_cc_btn_text' );
        delete_option( 'wbk_payment_details_title' );
        delete_option( 'wbk_payment_item_name' );
        delete_option( 'wbk_payment_price_format' );
        delete_option( 'wbk_payment_subtotal_title' );
        delete_option( 'wbk_payment_total_title' );
        delete_option( 'wbk_paypal_currency' );
        delete_option( 'wbk_paypal_tax' );
        delete_option( 'wbk_paypal_fee' );
        delete_option( 'wbk_payment_approve_text' );
        delete_option( 'wbk_payment_result_title' );
        delete_option( 'wbk_payment_success_message' );
        delete_option( 'wbk_payment_cancel_message' );
        delete_option( 'wbk_paypal_hide_address' );
        delete_option( 'wbk_hide_from_on_booking' );
        delete_option( 'wbk_check_short_code' );
        delete_option( 'wbk_show_cancel_button' );
        delete_option( 'wbk_cancel_button_text' );
        delete_option( 'wbk_disable_day_on_all_booked' );
        delete_option( 'wbk_super_admin_email' );
        delete_option( 'wbk_multi_booking' );
        delete_option( 'wbk_checkout_button_text' );
        delete_option( 'wbk_appointments_auto_lock_mode' );
        delete_option( 'wbk_appointment_information' );
        delete_option( 'wbk_show_local_time' );
        delete_option( 'wbk_local_time_format' );
        delete_option( 'wbk_appointments_allow_payments' );
        delete_option( 'wbk_appointments_default_status' );
        delete_option( 'wbk_nothing_to_pay_message' );
        delete_option( 'wbk_email_customer_approve_status' );
        delete_option( 'wbk_email_customer_approve_message' );
        delete_option( 'wbk_email_customer_approve_subject' );
        delete_option( 'wbk_email_secondary_book_status' );
        delete_option( 'wbk_booking_couldnt_be_canceled' );
        delete_option( 'wbk_booking_couldnt_be_canceled2' );
        delete_option( 'wbk_booking_cancel_email_label' );
        delete_option( 'wbk_booking_canceled_message' );
        delete_option( 'wbk_booking_cancel_error_message' );
        delete_option( 'wbk_email_landing_text' );
        delete_option( 'wbk_email_landing_text_cancel' );
        delete_option( 'wbk_appointments_delete_not_paid_mode' );
        delete_option( 'wbk_appointments_expiration_time' );
        delete_option( 'wbk_date_format_backend' );
        delete_option( 'wbk_csv_delimiter' );
        delete_option( 'wbk_appointments_auto_lock_group' );
        delete_option( 'wbk_appointments_delete_payment_started' );
        delete_option( 'wbk_email_customer_book_multiple_mode' );
        delete_option( 'wbk_email_adimn_appointment_cancel_status' );
        delete_option( 'wbk_email_adimn_appointment_cancel_subject' );
        delete_option( 'wbk_email_adimn_appointment_cancel_message' );
        delete_option( 'wbk_multi_booking_max' );
        delete_option( 'wbk_phone_required' );
        delete_option( 'wbk_pickadate_load' );
        delete_option( 'wbk_customer_name_output' );
        delete_option( 'wbk_skip_timeslot_select' );
        delete_option( 'wbk_cancellation_buffer' );
        delete_option( 'wbk_gg_calendar_event_title' );
        delete_option( 'wbk_gg_calendar_event_description' );
        delete_option( 'wbk_places_selection_mode' );
        delete_option( 'wbk_show_service_description' );
        delete_option( 'wbk_email_customer_send_invoice' );
        delete_option( 'wbk_email_customer_invoice_subject' );
        delete_option( 'wbk_email_customer_appointment_cancel_status' );
        delete_option( 'wbk_email_customer_appointment_cancel_subject' );
        delete_option( 'wbk_email_customer_appointment_cancel_message' );
        delete_option( 'wbk_allow_manage_by_link' );
        delete_option( 'wbk_email_landing_text_cancel_admin' );
        delete_option( 'wbk_email_landing_text_approve_admin' );
        delete_option( 'wbk_gg_calendar_event_title_customer' );
        delete_option( 'wbk_gg_calendar_event_description_customer' );
        delete_option( 'wbk_show_locked_as_booked' );
        delete_option( 'wbk_gg_calendar_add_event_canceled' );
        delete_option( 'wbk_gg_calendar_add_event_success' );
        delete_option( 'wbk_add_gg_button_text' );
        delete_option( 'wbk_email_landing_text_gg_event_add' );
        delete_option( 'wbk_time_slot_available_text' );
        delete_option( 'wbk_allow_attachemnt' );
        delete_option( 'wbk_attachment_file_types' );
        delete_option( 'wbk_stripe_api_error_message' );
        delete_option( 'wbk_stripe_card_element_error_message' );
        delete_option( 'wbk_stripe_publishable_key' );
        delete_option( 'wbk_stripe_secret_key' );
        delete_option( 'wbk_stripe_currency' );
        delete_option( 'wbk_load_stripe_js' );
        delete_option( 'wbk_stripe_card_input_mode' );
        delete_option( 'wbk_stripe_tax' );
        delete_option( 'wbk_stripe_button_text' );
        delete_option( 'wbk_show_suitable_hours' );
        delete_option( 'wbk_tax_for_messages' );
        delete_option( 'wbk_allow_coupons' );
        delete_option( 'wbk_paypal_auto_redirect' );
        delete_option( 'wbk_category_label' );
        delete_option( 'wbk_price_fractional' );
        delete_option( 'wbk_gg_created_by' );
        delete_option( 'wbk_night_hours' );
        delete_option( 'wbk_appointments_expiration_time_pending' );
        delete_option( 'wbk_order_service_by' );
        delete_option( 'wbk_gdrp' );
        delete_option( 'wbk_gg_when_add' );
        delete_option( 'wbk_woo_button_text' );
        delete_option( 'wbk_product_meta_key' );
        delete_option( 'wbk_allow_ongoing_time_slot' );
        delete_option( 'wbk_appointments_autolock_avail_limit' );
        delete_option( 'wbk_date_input' );
        delete_option( 'wbk_date_input_dropdown_count' );
        delete_option( 'wbk_appointments_lock_timeslot_if_parital_booked' );
        delete_option( 'wbk_email_current_invoice_number' );
        delete_option( 'wbk_show_details_prev_booking' );
        delete_option( 'wbk_scroll_container' );
        delete_option( 'wbk_gg_ignore_free', 'no' );
        delete_option( 'wbk_avaiability_popup_calendar' );
        delete_option( 'wbk_server_time_format' );
        delete_option( 'wbk_server_time_format2' );
        delete_option( 'wbk_stripe_mob_font_size' );
        delete_option( 'wbk_backend_select_services_onload' );
        delete_option( 'wbk_woo_auto_add_to_cart' );
        delete_option( 'wbk_email_reminders_only_for_approved' );
        delete_option( 'wbk_day_label' );
        delete_option( 'wbk_price_separator' );
        delete_option( 'wbk_scroll_value' );
    }

}
if ( !function_exists( 'wbk_deactivation_alert' ) ) {
    function wbk_deactivation_alert()
    {
        echo  '<div class="notice notice-error is-dismissible"><p>Webba Booking: Please deactivate the free version of Webba Booking in order to gain access to premium features. All data and settings are already saved.
        </p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>' ;
    }

}
if ( !function_exists( 'wbk_cleanup_attachements' ) ) {
    function wbk_cleanup_attachements()
    {
        if ( get_option( 'wbk_delete_attachemnt', 'no' ) == 'no' ) {
            return;
        }
        global  $wpdb ;
        $prefix = $wpdb->prefix;
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $prefix . $wpdb->esc_like( 'wbk_appointments' ) );
        if ( $wpdb->get_var( $query ) != $prefix . 'wbk_appointments' ) {
            return;
        }
        $result = $wpdb->get_results( "Select * from " . $prefix . "wbk_appointments where attachment  <> '' LIMIT 10 ", ARRAY_A );
        foreach ( $result as $item ) {
            $file = json_decode( $item['attachment'] );
            
            if ( is_array( $file ) ) {
                $file = $file[0];
                try {
                    if ( file_exists( $file ) ) {
                        unlink( $file );
                    }
                } catch ( \Exception $e ) {
                }
                $wpdb->update(
                    get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
                    array(
                    'attachment' => '',
                ),
                    array(
                    'id' => $item['id'],
                ),
                    array( '%s' ),
                    array( '%d' )
                );
            }
        
        }
    }

}
if ( !function_exists( 'wbk_plugion_strings' ) ) {
    function wbk_plugion_strings( $input )
    {
        $input['%s is required'] = __( '%s is required', 'wbk' );
        $input['%s must be a maximum of 256 characters'] = __( '%s must be a maximum of 256 characters', 'wbk' );
        $input['Value of %s is not acceptable'] = __( 'Value of %s is not acceptable', 'wbk' );
        $input['%s must be a maximum of 65535 characters'] = __( '%s must be a maximum of 65535 characters', 'wbk' );
        $input['Validation of %s failed'] = __( 'Validation of %s failed', 'wbk' );
        $input['Field %s is empty'] = __( 'Field %s is empty', 'wbk' );
        $input['The following fields are wrong:'] = 'The following fields are wrong:';
        $input['Loading...'] = __( 'Loading...', 'wbk' );
        $input['Element not found'] = __( 'Element not found', 'wbk' );
        $input['Bad request'] = __( 'Bad request', 'wbk' );
        $input['Failed'] = __( 'Failed', 'wbk' );
        $input['Forbidden'] = __( 'Forbidden', 'wbk' );
        $input['Please, set at least one filter'] = __( 'Please, set at least one filter', 'wbk' );
        $input['No data available'] = __( 'No data available', 'wbk' );
        $input['Here _START_ to _END_ of _TOTAL_ entries'] = __( 'Here _START_ to _END_ of _TOTAL_ entries', 'wbk' );
        $input['Showing 0 to 0 of 0 entries'] = __( 'Showing 0 to 0 of 0 entries', 'wbk' );
        $input['(filtered from _MAX_ total entries)'] = __( '(filtered from _MAX_ total entries)', 'wbk' );
        $input['Show _MENU_ entries'] = __( 'Show _MENU_ entries', 'wbk' );
        $input['Processing...'] = __( 'Processing...', 'wbk' );
        $input['Search'] = __( 'Search', 'wbk' );
        $input['No matching records found'] = __( 'No matching records found', 'wbk' );
        $input['First'] = __( 'First', 'wbk' );
        $input['Last'] = __( 'Last', 'wbk' );
        $input['Next'] = __( 'Next', 'wbk' );
        $input['Previous'] = __( 'Previous', 'wbk' );
        $input[': activate to sort column ascending'] = __( ': activate to sort column ascending', 'wbk' );
        $input[': activate to sort column descending'] = __( ': activate to sort column descending', 'wbk' );
        $input['Filters for'] = __( 'Filters for', 'wbk' );
        $input['Apply'] = __( 'Apply', 'wbk' );
        $input['Apply and close'] = __( 'Apply and close', 'wbk' );
        $input['Date'] = __( 'Date', 'wbk' );
        $input['Time'] = __( 'Time', 'wbk' );
        $input['Filters'] = __( 'Filters', 'wbk' );
        $input['Save and close'] = __( 'Save and close', 'wbk' );
        $input['New'] = __( 'New', 'wbk' );
        $input['Are you sure?'] = __( 'Are you sure?', 'wbk' );
        $input['Yes, delete it.'] = __( 'Yes, delete it.', 'wbk' );
        $input['select option'] = __( 'select option', 'wbk' );
        return $input;
    }

}
if ( !function_exists( 'wbk_delete_expired_appointments' ) ) {
    function wbk_delete_expired_appointments()
    {
        WBK_Db_Utils::deleteExpiredAppointments();
    }

}
if ( !function_exists( 'wbk_script_escape' ) ) {
    function wbk_script_escape( $input )
    {
        $input = str_replace( '<script', '', $input );
        $input = str_replace( '&lt;script', '', $input );
        $input = str_replace( 'src=', '', $input );
        return $input;
    }

}