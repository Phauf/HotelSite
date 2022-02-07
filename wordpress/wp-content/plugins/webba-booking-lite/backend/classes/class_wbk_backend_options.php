<?php

// Webba Booking options page class
// check if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
require_once dirname( __FILE__ ) . '/../../common/class_wbk_date_time_utils.php';
require_once dirname( __FILE__ ) . '/../../common/class_wbk_business_hours.php';
class WBK_Backend_Options extends WBK_Backend_Component
{
    public function __construct()
    {
        //set component-specific properties
        $this->name = 'wbk-options';
        $this->title = 'Settings';
        $this->main_template = 'tpl_wbk_backend_options.php';
        $this->capability = 'manage_options';
        // init settings
        add_action( 'admin_init', array( $this, 'initSettings' ) );
        // init scripts
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueueScripts' ), 20 );
        // mce plugin
        add_filter( 'mce_buttons', array( $this, 'wbk_mce_add_button' ) );
        add_filter( 'mce_external_plugins', array( $this, 'wbk_mce_add_javascript' ) );
        add_filter( 'wp_default_editor', 'wbk_default_editor' );
        add_filter( 'tiny_mce_before_init', array( $this, 'customizeEditor' ), 1000 );
    }
    
    public function customizeEditor( $in )
    {
        
        if ( isset( $_GET['page'] ) && $_GET['page'] == 'wbk-options' ) {
            $in['remove_linebreaks'] = false;
            $in['remove_redundant_brs'] = false;
            $in['wpautop'] = false;
            $opts = '*[*]';
            $in['valid_elements'] = $opts;
            $in['extended_valid_elements'] = $opts;
        }
        
        return $in;
    }
    
    public function wbk_mce_add_button( $buttons )
    {
        
        if ( isset( $_GET['page'] ) && $_GET['page'] == 'wbk-options' ) {
            $buttons[] = 'wbk_service_name_button';
            $buttons[] = 'wbk_category_names_button';
            $buttons[] = 'wbk_customer_name_button';
            $buttons[] = 'wbk_appointment_day_button';
            $buttons[] = 'wbk_appointment_time_button';
            $buttons[] = 'wbk_appointment_local_day_button';
            $buttons[] = 'wbk_appointment_local_time_button';
            $buttons[] = 'wbk_appointment_id_button';
            $buttons[] = 'wbk_customer_phone_button';
            $buttons[] = 'wbk_customer_email_button';
            $buttons[] = 'wbk_customer_comment_button';
            $buttons[] = 'wbk_customer_custom_button';
            $buttons[] = 'wbk_items_count';
            $buttons[] = 'wbk_total_amount';
            $buttons[] = 'wbk_payment_link';
            $buttons[] = 'wbk_cancel_link';
            $buttons[] = 'wbk_tomorrow_agenda';
            $buttons[] = 'wbk_group_customer';
            $buttons[] = 'wbk_multiple_loop';
            $buttons[] = 'wbk_admin_cancel_link';
            $buttons[] = 'wbk_admin_approve_link';
            $buttons[] = 'wbk_customer_ggcl_link';
            $buttons[] = 'wbk_time_range';
        }
        
        return $buttons;
    }
    
    public function wbk_mce_add_javascript( $plugin_array )
    {
        if ( isset( $_GET['page'] ) && $_GET['page'] == 'wbk-options' ) {
            if ( !isset( $plugin_array['wbk_tinynce'] ) ) {
                $plugin_array['wbk_tinynce'] = plugins_url( 'js/wbk-tinymce.js', dirname( __FILE__ ) );
            }
        }
        return $plugin_array;
    }
    
    // init wp settings api objects for options page
    public function initSettings()
    {
        // general settings section init
        add_settings_section(
            'wbk_general_settings_section',
            __( 'General', 'wbk' ),
            array( $this, 'wbk_general_settings_section_callback' ),
            'wbk-options'
        );
        // start of week
        add_settings_field(
            'wbk_start_of_week',
            __( 'Week starts on', 'wbk' ),
            array( $this, 'render_start_of_week' ),
            'wbk-options',
            'wbk_general_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_start_of_week', array( $this, 'validate_start_of_week' ) );
        // date format
        add_settings_field(
            'wbk_date_format',
            __( 'Date format', 'wbk' ),
            array( $this, 'render_date_format' ),
            'wbk-options',
            'wbk_general_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_date_format', array( $this, 'validate_date_format' ) );
        // time format
        add_settings_field(
            'wbk_time_format',
            __( 'Time format', 'wbk' ),
            array( $this, 'render_time_format' ),
            'wbk-options',
            'wbk_general_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_time_format', array( $this, 'validate_time_format' ) );
        // timezone
        add_settings_field(
            'wbk_timezone',
            __( 'Timezone', 'wbk' ),
            array( $this, 'render_timezone' ),
            'wbk-options',
            'wbk_general_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_timezone', array( $this, 'validate_timezone' ) );
        // phone mask
        add_settings_field(
            'wbk_phone_mask',
            __( 'Phone number mask input', 'wbk' ),
            array( $this, 'render_phone_mask' ),
            'wbk-options',
            'wbk_general_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_phone_mask', array( $this, 'validate_phone_mask' ) );
        // phone format
        add_settings_field(
            'wbk_phone_format',
            __( 'Phone format', 'wbk' ),
            array( $this, 'render_phone_format' ),
            'wbk-options',
            'wbk_general_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_phone_format', array( $this, 'validate_phone_format' ) );
        // phone required
        add_settings_field(
            'wbk_phone_required',
            __( 'Phone field is required in the booking form', 'wbk' ),
            array( $this, 'render_phone_required' ),
            'wbk-options',
            'wbk_general_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_phone_required', array( $this, 'validate_phone_required' ) );
        // booked slots
        add_settings_field(
            'wbk_show_booked_slots',
            __( 'Show booked time slots', 'wbk' ),
            array( $this, 'render_show_booked_slots' ),
            'wbk-options',
            'wbk_general_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_show_booked_slots', array( $this, 'validate_show_booked_slots' ) );
        // auto lock slots
        add_settings_field(
            'wbk_appointments_auto_lock',
            __( 'Auto lock appointments', 'wbk' ),
            array( $this, 'render_appointments_auto_lock' ),
            'wbk-options',
            'wbk_appointments_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_appointments_auto_lock', array( $this, 'validate_appointments_auto_lock' ) );
        // auto lock mode
        add_settings_field(
            'wbk_appointments_auto_lock_mode',
            __( 'Perform autolock on:', 'wbk' ),
            array( $this, 'render_appointments_auto_lock_mode' ),
            'wbk-options',
            'wbk_appointments_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_appointments_auto_lock_mode', array( $this, 'validate_appointments_auto_lock_mode' ) );
        // auto lock group
        add_settings_field(
            'wbk_appointments_auto_lock_group',
            __( 'Autolock for group booking services:', 'wbk' ),
            array( $this, 'render_appointments_auto_lock_group' ),
            'wbk-options',
            'wbk_appointments_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_appointments_auto_lock_group', array( $this, 'validate_appointments_auto_lock_group' ) );
        add_settings_field(
            'wbk_appointments_auto_lock_allow_unlock',
            __( 'Allow unlock manually:', 'wbk' ),
            array( $this, 'render_appointments_auto_lock_allow_unlock' ),
            'wbk-options',
            'wbk_appointments_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_appointments_auto_lock_allow_unlock', array( $this, 'validate_appointments_auto_lock_allow_unlock' ) );
        add_settings_field(
            'wbk_appointments_default_status',
            __( 'Default appointment status:', 'wbk' ),
            array( $this, 'render_appointments_default_status' ),
            'wbk-options',
            'wbk_appointments_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_appointments_default_status', array( $this, 'validate_appointments_default_status' ) );
        // appointment allow payments for
        add_settings_field(
            'wbk_appointments_allow_payments',
            __( 'Allow payments only for approved appointments:', 'wbk' ),
            array( $this, 'render_appointments_allow_payments' ),
            'wbk-options',
            'wbk_appointments_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_appointments_allow_payments', array( $this, 'validate_appointments_allow_payments' ) );
        // appointment delete not paid apps mode
        add_settings_field(
            'wbk_appointments_delete_not_paid_mode',
            __( 'Delete expired (not paid) appointments', 'wbk' ),
            array( $this, 'render_appointments_delete_not_paid_mode' ),
            'wbk-options',
            'wbk_appointments_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_appointments_delete_not_paid_mode', array( $this, 'validate_appointments_delete_not_paid_mode' ) );
        add_settings_field(
            'wbk_appointments_delete_payment_started',
            __( 'Delete expired appointments with started but not finished transaction', 'wbk' ),
            array( $this, 'render_appointments_delete_payment_started' ),
            'wbk-options',
            'wbk_appointments_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_appointments_delete_payment_started', array( $this, 'validate_appointments_delete_payment_started' ) );
        // appointment expiration
        add_settings_field(
            'wbk_appointments_expiration_time',
            __( 'Expiration time', 'wbk' ),
            array( $this, 'render_appointments_expiration_time' ),
            'wbk-options',
            'wbk_appointments_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_appointments_expiration_time', array( $this, 'validate_appointments_expiration_time' ) );
        // cancellation buffer
        add_settings_field(
            'wbk_cancellation_buffer',
            __( 'Cancellation buffer', 'wbk' ),
            array( $this, 'render_cancellation_buffer' ),
            'wbk-options',
            'wbk_appointments_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_cancellation_buffer', array( $this, 'validate_cancellation_buffer' ) );
        add_settings_field(
            'wbk_appointments_allow_cancel_paid',
            __( 'Allow cancellation of paid appointments:', 'wbk' ),
            array( $this, 'render_appointments_allow_cancel_paid' ),
            'wbk-options',
            'wbk_appointments_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_appointments_allow_cancel_paid', array( $this, 'validate_appointments_allow_cancel_paid' ) );
        add_settings_field(
            'wbk_appointments_only_one_per_slot',
            __( 'Allow only one appointment per slot from email:', 'wbk' ),
            array( $this, 'render_appointments_only_one_per_slot' ),
            'wbk-options',
            'wbk_appointments_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_appointments_only_one_per_slot', array( $this, 'validate_appointments_only_one_per_slot' ) );
        add_settings_field(
            'wbk_appointments_only_one_per_day',
            __( 'Allow only one appointment per day from email:', 'wbk' ),
            array( $this, 'render_appointments_only_one_per_day' ),
            'wbk-options',
            'wbk_appointments_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_appointments_only_one_per_day', array( $this, 'validate_appointments_only_one_per_day' ) );
        add_settings_field(
            'wbk_hide_from_on_booking',
            __( 'Hide form on booking', 'wbk' ),
            array( $this, 'render_hide_from_on_booking' ),
            'wbk-options',
            'wbk_general_settings_section',
            array()
        );
        wbk_opt()->add_option(
            'wbk_appointments_only_one_per_service',
            'select',
            __( 'Allow only one appointment per service from email', 'wbk' ),
            '',
            'wbk_appointments_settings_section',
            'disabled',
            array(
            'disabled' => __( 'Disabled', 'wbk' ),
            'enabled'  => __( 'Enabled', 'wbk' ),
        )
        );
        wbk_opt()->add_option(
            'wbk_appointments_limit_email_service_date',
            'text',
            __( 'Maximum number of bookings per email, service, date', 'wbk' ),
            '',
            'wbk_appointments_settings_section',
            ''
        );
        add_settings_field(
            'wbk_appointments_expiration_time_pending',
            __( 'Delete pending appointments', 'wbk' ),
            array( $this, 'render_appointments_expiration_time_pending' ),
            'wbk-options',
            'wbk_appointments_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_appointments_expiration_time_pending', array( $this, 'validate_appointments_expiration_time_pending' ) );
        add_settings_field(
            'wbk_appointments_autolock_avail_limit',
            __( 'Limit per time', 'wbk' ),
            array( $this, 'render_appointments_autolock_avail_limit' ),
            'wbk-options',
            'wbk_appointments_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_appointments_autolock_avail_limit', array( $this, 'validate_appointments_autolock_avail_limit' ) );
        add_settings_field(
            'wbk_appointments_limit_by_day',
            __( 'Daily limit', 'wbk' ),
            array( $this, 'render_appointments_limit_by_day' ),
            'wbk-options',
            'wbk_appointments_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_appointments_limit_by_day', array( $this, 'validate_appointments_limit_by_day' ) );
        add_settings_field(
            'wbk_appointments_continuous',
            __( 'Continuous appointments', 'wbk' ),
            array( $this, 'render_appointments_continuous' ),
            'wbk-options',
            'wbk_appointments_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_appointments_continuous', array( $this, 'validate_appointments_continuous' ) );
        add_settings_field(
            'wbk_appointments_lock_timeslot_if_parital_booked',
            __( 'Lock time slot if at least one place is booked', 'wbk' ),
            array( $this, 'render_appointments_lock_timeslot_if_parital_booked' ),
            'wbk-options',
            'wbk_appointments_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_appointments_lock_timeslot_if_parital_booked', array( $this, 'validate_appointments_lock_timeslot_if_parital_booked' ) );
        add_settings_field(
            'wbk_appointments_lock_day_if_timeslot_booked',
            __( 'Lock whole day if at least one time slot is booked', 'wbk' ),
            array( $this, 'render_appointments_lock_day_if_timeslot_booked' ),
            'wbk-options',
            'wbk_appointments_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_appointments_lock_day_if_timeslot_booked', array( $this, 'validate_appointments_lock_day_if_timeslot_booked' ) );
        add_settings_field(
            'wbk_appointments_lock_one_before_and_one_after',
            __( 'Lock one time slot before and after appointment', 'wbk' ),
            array( $this, 'render_appointments_lock_one_before_and_one_after' ),
            'wbk-options',
            'wbk_appointments_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_appointments_lock_one_before_and_one_after', array( $this, 'validate_appointments_lock_one_before_and_one_after' ) );
        add_settings_field(
            'wbk_appointments_special_hours',
            __( 'Special business hours', 'wbk' ),
            array( $this, 'render_appointments_special_hours' ),
            'wbk-options',
            'wbk_appointments_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_appointments_special_hours', array( $this, 'validate_appointments_special_hours' ) );
        register_setting( 'wbk_options', 'wbk_hide_from_on_booking', array( $this, 'validate_hide_from_on_booking' ) );
        // shortcode checking
        add_settings_field(
            'wbk_check_short_code',
            __( 'Check shortcode on booking form initialization', 'wbk' ),
            array( $this, 'render_check_short_code' ),
            'wbk-options',
            'wbk_general_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_check_short_code', array( $this, 'validate_check_short_code' ) );
        // show cancel button
        add_settings_field(
            'wbk_show_cancel_button',
            __( 'Show cancel button', 'wbk' ),
            array( $this, 'render_show_cancel_button' ),
            'wbk-options',
            'wbk_general_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_show_cancel_button', array( $this, 'validate_show_cancel_button' ) );
        // disable day on all booked
        add_settings_field(
            'wbk_disable_day_on_all_booked',
            __( 'Disable booked dates in calendar', 'wbk' ),
            array( $this, 'render_disable_day_on_all_booked' ),
            'wbk-options',
            'wbk_general_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_disable_day_on_all_booked', array( $this, 'validate_disable_day_on_all_booked' ) );
        // holyday settings section init
        add_settings_section(
            'wbk_schedule_settings_section',
            __( 'Holidays', 'wbk' ),
            array( $this, 'wbk_schedule_settings_section_callback' ),
            'wbk-options'
        );
        // holydays
        add_settings_field(
            'wbk_holydays',
            __( 'Holidays', 'wbk' ),
            array( $this, 'render_holydays' ),
            'wbk-options',
            'wbk_schedule_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_holydays', array( $this, 'validate_holydays' ) );
        add_settings_field(
            'wbk_recurring_holidays',
            __( 'Recurring holidays', 'wbk' ),
            array( $this, 'render_recurring_holidays' ),
            'wbk-options',
            'wbk_schedule_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_recurring_holidays', array( $this, 'validate_recurring_holidays' ) );
        // email settings section init
        add_settings_section(
            'wbk_email_settings_section',
            __( 'Email notifications', 'wbk' ),
            array( $this, 'wbk_email_settings_section_callback' ),
            'wbk-options'
        );
        add_settings_field(
            'wbk_email_customer_book_multiple_mode',
            __( 'Multiple booking notification mode (customer)', 'wbk' ),
            array( $this, 'render_email_customer_book_multiple_mode' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_book_multiple_mode', array( $this, 'validate_email_customer_book_multiple_mode' ) );
        add_settings_field(
            'wbk_email_admin_book_multiple_mode',
            __( 'Multiple booking notification mode (admin)', 'wbk' ),
            array( $this, 'render_email_admin_book_multiple_mode' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_admin_book_multiple_mode', array( $this, 'validate_email_admin_book_multiple_mode' ) );
        add_settings_field(
            'wbk_email_customer_book_status',
            __( 'Send customer an email (on booking)', 'wbk' ),
            array( $this, 'render_email_customer_book_status' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_book_status', array( $this, 'validate_email_customer_book_status' ) );
        add_settings_field(
            'wbk_email_customer_book_subject',
            __( 'Subject of an email to a customer (on booking)', 'wbk' ),
            array( $this, 'render_email_customer_book_subject' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_book_subject', array( $this, 'validate_email_customer_book_subject' ) );
        add_settings_field(
            'wbk_email_customer_book_message',
            __( 'Message to a customer (on booking)', 'wbk' ),
            array( $this, 'render_email_customer_book_message' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_book_message', array( $this, 'validate_email_customer_book_message' ) );
        add_settings_field(
            'wbk_email_customer_manual_book_subject',
            __( 'Subject of an email to a customer (on manual booking)', 'wbk' ),
            array( $this, 'render_email_customer_manual_book_subject' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_manual_book_subject', array( $this, 'validate_email_customer_manual_book_subject' ) );
        add_settings_field(
            'wbk_email_customer_manual_book_message',
            __( 'Message to a customer (on manual booking)', 'wbk' ),
            array( $this, 'render_email_customer_manual_book_message' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_manual_book_message', array( $this, 'validate_email_customer_manual_book_message' ) );
        add_settings_field(
            'wbk_email_customer_approve_status',
            __( 'Send customer an email (on approval)', 'wbk' ),
            array( $this, 'render_email_customer_approve_status' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_approve_status', array( $this, 'validate_email_customer_approve_status' ) );
        add_settings_field(
            'wbk_email_customer_approve_subject',
            __( 'Subject of an email to a customer (on approval)', 'wbk' ),
            array( $this, 'render_email_customer_approve_subject' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_approve_subject', array( $this, 'validate_email_customer_approve_subject' ) );
        add_settings_field(
            'wbk_email_customer_approve_message',
            __( 'Message to a customer (on approval)', 'wbk' ),
            array( $this, 'render_email_customer_approve_message' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_approve_message', array( $this, 'validate_email_customer_approve_message' ) );
        add_settings_field(
            'wbk_email_customer_approve_copy_status',
            __( 'Send copy of approval notification to administrator', 'wbk' ),
            array( $this, 'render_email_customer_approve_copy_status' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_approve_copy_status', array( $this, 'validate_email_customer_approve_copy_status' ) );
        // *** BEGIN  apppointment cancellation email (admin)
        add_settings_field(
            'wbk_email_adimn_appointment_cancel_status',
            __( 'Send administrator an email (on cancellation)', 'wbk' ),
            array( $this, 'render_email_admin_appointment_cancel_status' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_adimn_appointment_cancel_status', array( $this, 'validate_email_admin_appointment_cancel_status' ) );
        add_settings_field(
            'wbk_email_admin_cancel_multiple_mode',
            __( 'Multiple booking cancellation notification mode (admin)', 'wbk' ),
            array( $this, 'render_email_admin_cancel_multiple_mode' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_admin_cancel_multiple_mode', array( $this, 'validate_email_admin_cancel_multiple_mode' ) );
        add_settings_field(
            'wbk_email_adimn_appointment_cancel_subject',
            __( 'Subject of an email to administrator (on cancellation)', 'wbk' ),
            array( $this, 'render_email_admin_appointment_cancel_subject' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_adimn_appointment_cancel_subject', array( $this, 'validate_email_admin_appointment_cancel_subject' ) );
        add_settings_field(
            'wbk_email_adimn_appointment_cancel_message',
            __( 'Message to administrator (on cancellation)', 'wbk' ),
            array( $this, 'render_email_admin_appointment_cancel_message' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_adimn_appointment_cancel_message', array( $this, 'validate_email_admin_appointment_cancel_message' ) );
        // *** END  apppointment cancellation email (admin)
        // *** BEGIN appointment cancellation email (customer)
        add_settings_field(
            'wbk_email_customer_appointment_cancel_status',
            __( 'Send customer an email (on cancellation)', 'wbk' ),
            array( $this, 'render_email_customer_appointment_cancel_status' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_appointment_cancel_status', array( $this, 'validate_email_customer_appointment_cancel_status' ) );
        add_settings_field(
            'wbk_email_customer_cancel_multiple_mode',
            __( 'Multiple booking cancellation notification mode (customer)', 'wbk' ),
            array( $this, 'render_email_customer_cancel_multiple_mode' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_cancel_multiple_mode', array( $this, 'validate_email_customer_cancel_multiple_mode' ) );
        add_settings_field(
            'wbk_email_customer_appointment_cancel_subject',
            __( 'Subject of an email to customer (on cancellation)', 'wbk' ),
            array( $this, 'render_email_customer_appointment_cancel_subject' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_appointment_cancel_subject', array( $this, 'validate_email_customer_appointment_cancel_subject' ) );
        add_settings_field(
            'wbk_email_customer_appointment_cancel_message',
            __( 'Message to customer (on cancellation by administrator)', 'wbk' ),
            array( $this, 'render_email_customer_appointment_cancel_message' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_appointment_cancel_message', array( $this, 'validate_email_customer_appointment_cancel_message' ) );
        add_settings_field(
            'wbk_email_customer_bycustomer_appointment_cancel_message',
            __( 'Message to customer (on cancellation by customer)', 'wbk' ),
            array( $this, 'render_email_customer_bycustomer_appointment_cancel_message' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_bycustomer_appointment_cancel_message', array( $this, 'validate_email_customer_bycustomer_appointment_cancel_message' ) );
        add_settings_field(
            'wbk_email_secondary_book_status',
            __( 'Send an email to other customers from the group (if provided)', 'wbk' ),
            array( $this, 'render_email_secondary_book_status' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_secondary_book_status', array( $this, 'validate_email_secondary_book_status' ) );
        add_settings_field(
            'wbk_email_secondary_book_subject',
            __( 'Subject of an email to a customers from the group', 'wbk' ),
            array( $this, 'render_email_secondary_book_subject' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_secondary_book_subject', array( $this, 'validate_email_secondary_book_subject' ) );
        add_settings_field(
            'wbk_email_secondary_book_message',
            __( 'Message to a customers from the group', 'wbk' ),
            array( $this, 'render_email_secondary_book_message' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_secondary_book_message', array( $this, 'validate_email_secondary_book_message' ) );
        add_settings_field(
            'wbk_email_admin_book_status',
            __( 'Send administrator an email (on booking)', 'wbk' ),
            array( $this, 'render_email_admin_book_status' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_admin_book_status', array( $this, 'validate_email_admin_book_status' ) );
        add_settings_field(
            'wbk_email_admin_book_subject',
            __( 'Subject of an email to an administrator (on booking)', 'wbk' ),
            array( $this, 'render_email_admin_book_subject' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_admin_book_subject', array( $this, 'validate_email_admin_book_subject' ) );
        add_settings_field(
            'wbk_email_admin_book_message',
            __( 'Message to an administrator (on booking)', 'wbk' ),
            array( $this, 'render_email_admin_book_message' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_admin_book_message', array( $this, 'validate_email_admin_book_message' ) );
        add_settings_field(
            'wbk_email_admin_paymentrcvd_status',
            __( 'Send administrator an email (on payment received)', 'wbk' ),
            array( $this, 'render_email_admin_paymentrecvd_status' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_admin_paymentrcvd_status', array( $this, 'validate_email_admin_paymentrcvd_status' ) );
        add_settings_field(
            'wbk_email_admin_paymentrcvd_subject',
            __( 'Subject of an email to an administrator (on payment received)', 'wbk' ),
            array( $this, 'render_email_admin_paymentrcvd_subject' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_admin_paymentrcvd_subject', array( $this, 'validate_email_admin_paymentrcvd_subject' ) );
        add_settings_field(
            'wbk_email_admin_paymentrcvd_message',
            __( 'Message to an administrator (on payment received)', 'wbk' ),
            array( $this, 'render_email_admin_paymentrcvd_message' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_admin_paymentrcvd_message', array( $this, 'validate_email_admin_paymentrcvd_message' ) );
        add_settings_field(
            'wbk_email_customer_paymentrcvd_status',
            __( 'Send customer an email (on payment received)', 'wbk' ),
            array( $this, 'render_email_customer_paymentrecvd_status' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_paymentrcvd_status', array( $this, 'validate_email_customer_paymentrcvd_status' ) );
        add_settings_field(
            'wbk_email_customer_paymentrcvd_subject',
            __( 'Subject of an email to to a customer (on payment received)', 'wbk' ),
            array( $this, 'render_email_customer_paymentrcvd_subject' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_paymentrcvd_subject', array( $this, 'validate_email_customer_paymentrcvd_subject' ) );
        add_settings_field(
            'wbk_email_customer_paymentrcvd_message',
            __( 'Message to a customer (on payment received)', 'wbk' ),
            array( $this, 'render_email_customer_paymentrcvd_message' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_paymentrcvd_message', array( $this, 'validate_email_customer_paymentrcvd_message' ) );
        add_settings_field(
            'wbk_email_customer_arrived_status',
            __( 'Send notification when status is changed to Arrived', 'wbk' ),
            array( $this, 'render_email_customer_arrived_status' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_arrived_status', array( $this, 'validate_email_customer_arrived_status' ) );
        add_settings_field(
            'wbk_email_customer_arrived_subject',
            __( 'Subject of an email to a customer (on status changed to arrived)', 'wbk' ),
            array( $this, 'render_email_customer_arrived_subject' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_arrived_subject', array( $this, 'validate_email_customer_arrived_subject' ) );
        add_settings_field(
            'wbk_email_customer_arrived_message',
            __( 'Message to a customer (on status is changed to arrived)', 'wbk' ),
            array( $this, 'render_email_customer_arrived_message' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_arrived_message', array( $this, 'validate_email_customer_arrived_message' ) );
        add_settings_field(
            'wbk_email_admin_daily_status',
            __( 'Send administrator reminders', 'wbk' ),
            array( $this, 'render_email_admin_daily_status' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_admin_daily_status', array( $this, 'validate_email_admin_daily_status' ) );
        //
        add_settings_field(
            'wbk_email_admin_daily_subject',
            __( 'Subject of administrator reminders', 'wbk' ),
            array( $this, 'render_email_admin_daily_subject' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_admin_daily_subject', array( $this, 'validate_email_admin_daily_subject' ) );
        add_settings_field(
            'wbk_email_admin_daily_message',
            __( 'Administrator reminders message', 'wbk' ),
            array( $this, 'render_email_admin_daily_message' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_admin_daily_message', array( $this, 'validate_email_admin_daily_message' ) );
        // customer daily
        add_settings_field(
            'wbk_email_customer_daily_status',
            __( 'Send customer reminders', 'wbk' ),
            array( $this, 'render_email_customer_daily_status' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_daily_status', array( $this, 'validate_email_customer_daily_status' ) );
        //
        add_settings_field(
            'wbk_email_reminder_days',
            __( 'Send customers a reminder X days before booking', 'wbk' ),
            array( $this, 'render_email_reminder_days' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_reminder_days', array( $this, 'validate_email_reminder_days' ) );
        add_settings_field(
            'wbk_email_customer_daily_subject',
            __( 'Subject of customer reminders', 'wbk' ),
            array( $this, 'render_email_customer_daily_subject' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_daily_subject', array( $this, 'validate_email_customer_daily_subject' ) );
        add_settings_field(
            'wbk_email_customer_daily_message',
            __( 'Customer reminders message', 'wbk' ),
            array( $this, 'render_email_customer_daily_message' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_daily_message', array( $this, 'validate_email_customer_daily_message' ) );
        // customer daily end
        add_settings_field(
            'wbk_email_admin_daily_time',
            __( 'Time of a daily reminder', 'wbk' ),
            array( $this, 'render_email_admin_daily_time' ),
            'wbk-options',
            'wbk_general_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_admin_daily_time', array( $this, 'validate_email_admin_daily_time' ) );
        add_settings_field(
            'wbk_email_reminders_only_for_approved',
            __( 'Send reminders only for approved appointments', 'wbk' ),
            array( $this, 'render_email_reminders_only_for_approved' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_reminders_only_for_approved', array( $this, 'validate_email_reminders_only_for_approved' ) );
        add_settings_field(
            'wbk_email_customer_send_invoice',
            __( 'Send invoice to customer', 'wbk' ),
            array( $this, 'render_email_customer_send_invoice' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_send_invoice', array( $this, 'validate_email_customer_send_invoice' ) );
        add_settings_field(
            'wbk_email_customer_invoice_subject',
            __( 'Invoice email subject', 'wbk' ),
            array( $this, 'render_email_customer_invoice_subject' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_customer_invoice_subject', array( $this, 'validate_email_customer_invoice_subject' ) );
        add_settings_field(
            'wbk_email_current_invoice_number',
            __( 'Current invoice number', 'wbk' ),
            array( $this, 'render_email_current_invoice_number' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_current_invoice_number', array( $this, 'validate_email_current_invoice_number' ) );
        wbk_opt()->add_option(
            'wbk_email_on_update_booking_subject',
            'text',
            __( 'Notification subject (when booking changes)', 'wbk' ),
            '',
            'wbk_email_settings_section',
            ''
        );
        add_settings_field(
            'wbk_email_send_invoice_copy',
            __( 'Send copies of invoices to administrator', 'wbk' ),
            array( $this, 'render_email_send_invoice_copy' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_send_invoice_copy', array( $this, 'validate_email_send_invoice_copy' ) );
        add_settings_field(
            'wbk_email_override_replyto',
            __( 'Override default reply-to headers with booking-related data', 'wbk' ),
            array( $this, 'render_email_override_replyto' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_override_replyto', array( $this, 'validate_email_override_replyto' ) );
        add_settings_field(
            'wbk_from_name',
            __( 'From: name', 'wbk' ),
            array( $this, 'render_from_name' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_from_name', array( $this, 'validate_from_name' ) );
        add_settings_field(
            'wbk_from_email',
            __( 'From: email', 'wbk' ),
            array( $this, 'render_from_email' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_from_email', array( $this, 'validate_from_email' ) );
        add_settings_field(
            'wbk_super_admin_email',
            __( 'Send copies of service emails to', 'wbk' ),
            array( $this, 'render_super_admin_email' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_super_admin_email', array( $this, 'validate_super_admin_email' ) );
        add_settings_field(
            'wbk_email_landing',
            __( 'Link to the page with Webba Booking shortcode', 'wbk' ),
            array( $this, 'render_email_landing' ),
            'wbk-options',
            'wbk_email_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_landing', array( $this, 'validate_email_landing' ) );
        // mode settings section init
        add_settings_section(
            'wbk_mode_settings_section',
            __( 'Mode', 'wbk' ),
            array( $this, 'wbk_mode_settings_section_callback' ),
            'wbk-options'
        );
        // mode field
        add_settings_field(
            'wbk_mode',
            __( 'Mode', 'wbk' ),
            array( $this, 'render_mode' ),
            'wbk-options',
            'wbk_mode_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_mode', array( $this, 'validate_mode' ) );
        add_settings_field(
            'wbk_show_suitable_hours',
            __( 'Show suitable hours', 'wbk' ),
            array( $this, 'render_show_suitable_hours' ),
            'wbk-options',
            'wbk_mode_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_show_suitable_hours', array( $this, 'validate_show_suitable_hours' ) );
        add_settings_field(
            'wbk_multi_booking',
            __( 'Multiple bookings in one session', 'wbk' ),
            array( $this, 'render_multi_booking' ),
            'wbk-options',
            'wbk_mode_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_multi_booking', array( $this, 'validate_multi_booking' ) );
        add_settings_field(
            'wbk_multi_booking_max',
            __( 'Maximum amount of bookings in one session', 'wbk' ),
            array( $this, 'render_multi_booking_max' ),
            'wbk-options',
            'wbk_mode_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_multi_booking_max', array( $this, 'validate_multi_booking_max' ) );
        add_settings_field(
            'wbk_skip_timeslot_select',
            __( 'Skip time slot selection', 'wbk' ),
            array( $this, 'render_skip_timeslot_select' ),
            'wbk-options',
            'wbk_mode_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_skip_timeslot_select', array( $this, 'validate_skip_timeslot_select' ) );
        add_settings_field(
            'wbk_places_selection_mode',
            __( 'Mode of multiple places selection', 'wbk' ),
            array( $this, 'render_places_selection_mode' ),
            'wbk-options',
            'wbk_mode_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_places_selection_mode', array( $this, 'validate_places_selection_mode' ) );
        add_settings_field(
            'wbk_time_hole_optimization',
            __( 'Time hole optimization', 'wbk' ),
            array( $this, 'render_time_hole_optimization' ),
            'wbk-options',
            'wbk_mode_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_time_hole_optimization', array( $this, 'validate_time_hole_optimization' ) );
        add_settings_field(
            'wbk_show_service_description',
            __( 'Show service description', 'wbk' ),
            array( $this, 'render_show_service_description' ),
            'wbk-options',
            'wbk_mode_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_show_service_description', array( $this, 'validate_show_service_description' ) );
        add_settings_field(
            'wbk_range_selection',
            __( 'Range selection', 'wbk' ),
            array( $this, 'render_range_selection' ),
            'wbk-options',
            'wbk_mode_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_range_selection', array( $this, 'validate_range_selection' ) );
        add_settings_field(
            'wbk_date_input',
            __( 'Date input', 'wbk' ),
            array( $this, 'render_date_input' ),
            'wbk-options',
            'wbk_mode_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_date_input', array( $this, 'validate_date_input' ) );
        // timeslot time string
        add_settings_field(
            'wbk_timeslot_time_string',
            __( 'Time slot time string', 'wbk' ),
            array( $this, 'render_timeslot_time_string' ),
            'wbk-options',
            'wbk_general_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_timeslot_time_string', array( $this, 'validate_timeslot_time_string' ) );
        // timeslot format
        add_settings_field(
            'wbk_timeslot_format',
            __( 'Time slot format', 'wbk' ),
            array( $this, 'render_timeslot_format' ),
            'wbk-options',
            'wbk_general_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_timeslot_format', array( $this, 'validate_timeslot_format' ) );
        // show local time
        add_settings_field(
            'wbk_show_local_time',
            __( 'Show local time in time slots', 'wbk' ),
            array( $this, 'render_show_local_time' ),
            'wbk-options',
            'wbk_general_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_show_local_time', array( $this, 'validate_show_local_time' ) );
        // csv delimiter
        add_settings_field(
            'wbk_csv_delimiter',
            __( 'CSV export delimiter', 'wbk' ),
            array( $this, 'render_csv_export_delimiter' ),
            'wbk-options',
            'wbk_general_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_csv_delimiter', array( $this, 'validate_csv_export_delimiter' ) );
        // jquery no conflict
        add_settings_field(
            'wbk_jquery_nc',
            __( 'jQuery no-conflict mode', 'wbk' ),
            array( $this, 'render_jquery_nc' ),
            'wbk-options',
            'wbk_general_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_jquery_nc', array( $this, 'validate_jquery_nc' ) );
        // pickadate js loading
        add_settings_field(
            'wbk_pickadate_load',
            __( 'Load pickadate javascript', 'wbk' ),
            array( $this, 'render_pickadate_load' ),
            'wbk-options',
            'wbk_general_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_pickadate_load', array( $this, 'validate_pickadate_load' ) );
        // allow manage by link
        add_settings_field(
            'wbk_allow_manage_by_link',
            __( 'Allow cancel or approve by link', 'wbk' ),
            array( $this, 'render_allow_manage_by_link' ),
            'wbk-options',
            'wbk_general_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_allow_manage_by_link', array( $this, 'validate_allow_manage_by_link' ) );
        add_settings_field(
            'wbk_tax_for_messages',
            __( 'Tax used for #total_amount placeholder', 'wbk' ),
            array( $this, 'render_tax_for_messages' ),
            'wbk-options',
            'wbk_general_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_tax_for_messages', array( $this, 'validate_tax_for_messages' ) );
        wbk_opt()->add_option(
            'wbk_do_not_tax_deposit',
            'checkbox',
            __( 'Do not tax the deposit (service fee)', 'wbk' ),
            __( 'Important notice: do not use subtotal and tax placeholders when this option is enabled.', 'wbk' ),
            'wbk_general_settings_section',
            ''
        );
        add_settings_field(
            'wbk_allow_coupons',
            __( 'Coupons', 'wbk' ),
            array( $this, 'render_allow_coupons' ),
            'wbk-options',
            'wbk_general_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_allow_coupons', array( $this, 'validate_allow_coupons' ) );
        add_settings_field(
            'wbk_price_fractional',
            __( 'The number of digits in the fractional part of the price', 'wbk' ),
            array( $this, 'render_price_fractional' ),
            'wbk-options',
            'wbk_general_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_price_fractional', array( $this, 'validate_price_fractional' ) );
        add_settings_field(
            'wbk_price_separator',
            __( 'Fractional separator in prices', 'wbk' ),
            array( $this, 'render_price_separator' ),
            'wbk-options',
            'wbk_general_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_price_separator', array( $this, 'validate_price_separator' ) );
        add_settings_field(
            'wbk_scroll_container',
            __( 'Scroll container', 'wbk' ),
            array( $this, 'render_scroll_container' ),
            'wbk-options',
            'wbk_general_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_scroll_container', array( $this, 'validate_scroll_container' ) );
        add_settings_field(
            'wbk_scroll_value',
            __( 'Scroll value', 'wbk' ),
            array( $this, 'render_scroll_value' ),
            'wbk-options',
            'wbk_general_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_scroll_value', array( $this, 'validate_scroll_value' ) );
        wbk_opt()->add_option(
            'wbk_general_dynamic_placeholders',
            'text',
            __( 'List of dynamic placeholders', 'wbk' ),
            '',
            'wbk_general_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_load_js_in_footer',
            'checkbox',
            __( 'Load javascript files in footer', 'wbk' ),
            '',
            'wbk_general_settings_section',
            ''
        );
        // translation settings section
        add_settings_section(
            'wbk_translation_settings_section',
            __( 'Translation', 'wbk' ),
            array( $this, 'wbk_translation_settings_section_callback' ),
            'wbk-options'
        );
        add_settings_field(
            'wbk_service_label',
            __( 'Select service label', 'wbk' ),
            array( $this, 'render_service_label' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_service_label', array( $this, 'validate_service_label' ) );
        add_settings_field(
            'wbk_category_label',
            __( 'Select category label', 'wbk' ),
            array( $this, 'render_category_label' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_category_label', array( $this, 'validate_category_label' ) );
        add_settings_field(
            'wbk_date_extended_label',
            __( 'Select date label (extended mode)', 'wbk' ),
            array( $this, 'render_date_extended_label' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_date_extended_label', array( $this, 'validate_date_extended_label' ) );
        add_settings_field(
            'wbk_date_basic_label',
            __( 'Select date label (basic mode)', 'wbk' ),
            array( $this, 'render_date_basic_label' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_date_basic_label', array( $this, 'validate_date_basic_label' ) );
        // *** 2.2.8 settings pack
        add_settings_field(
            'wbk_date_input_placeholder',
            __( 'Select date input placeholder', 'wbk' ),
            array( $this, 'render_date_input_placeholder' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_date_input_placeholder', array( $this, 'validate_date_input_placeholder' ) );
        add_settings_field(
            'wbk_date_input_dropdown_count',
            __( 'Count of dates in the dropdown', 'wbk' ),
            array( $this, 'render_date_input_dropdown_count' ),
            'wbk-options',
            'wbk_mode_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_date_input_dropdown_count', array( $this, 'validate_date_input_dropdown_count' ) );
        // end 2.2.8 settings pack
        add_settings_field(
            'wbk_hours_label',
            __( 'Select hours label', 'wbk' ),
            array( $this, 'render_hours_label' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_hours_label', array( $this, 'validate_hours_label' ) );
        add_settings_field(
            'wbk_slots_label',
            __( 'Select time slots label', 'wbk' ),
            array( $this, 'render_slots_label' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_slots_label', array( $this, 'validate_slots_label' ) );
        add_settings_field(
            'wbk_form_label',
            __( 'Booking form label', 'wbk' ),
            array( $this, 'render_form_label' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_form_label', array( $this, 'validate_form_label' ) );
        add_settings_field(
            'wbk_book_items_quantity_label',
            __( 'Booking items count label', 'wbk' ),
            array( $this, 'render_book_items_quantity_label' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_book_items_quantity_label', array( $this, 'validate_book_items_quantity_label' ) );
        // booked slot
        add_settings_field(
            'wbk_booked_text',
            __( 'Booked time slot text', 'wbk' ),
            array( $this, 'render_booked_text' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_booked_text', array( $this, 'validate_booked_text' ) );
        // format of local time (in timeslots)
        // added since 3.1.1
        add_settings_field(
            'wbk_local_time_format',
            __( 'Local time format', 'wbk' ),
            array( $this, 'render_local_time_format' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_local_time_format', array( $this, 'validate_local_time_format' ) );
        add_settings_field(
            'wbk_server_time_format',
            __( 'Text before the time in time slot', 'wbk' ),
            array( $this, 'render_server_time_format' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_server_time_format', array( $this, 'validate_server_time_format' ) );
        add_settings_field(
            'wbk_server_time_format2',
            __( 'Text after the time in time slot', 'wbk' ),
            array( $this, 'render_server_time_format2' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_server_time_format2', array( $this, 'validate_server_time_format2' ) );
        add_settings_field(
            'wbk_time_slot_available_text',
            __( 'Availability label', 'wbk' ),
            array( $this, 'render_time_slot_available_text' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_time_slot_available_text', array( $this, 'validate_time_slot_available_text' ) );
        // booked slot end
        // 2.2.8 settings pack
        // *** book ( timeslot )
        add_settings_field(
            'wbk_book_text_timeslot',
            __( 'Book button text (time slot)', 'wbk' ),
            array( $this, 'render_book_text_timeslot' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_book_text_timeslot', array( $this, 'validate_book_text_timeslot' ) );
        // *** book ( form )
        add_settings_field(
            'wbk_book_text_form',
            __( 'Book button text (form)', 'wbk' ),
            array( $this, 'render_book_text_form' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_book_text_form', array( $this, 'validate_book_text_form' ) );
        // *** name
        add_settings_field(
            'wbk_name_label',
            __( 'Name label (booking form)', 'wbk' ),
            array( $this, 'render_name_label' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_name_label', array( $this, 'validate_name_label' ) );
        // *** email
        add_settings_field(
            'wbk_email_label',
            __( 'Email label (booking form)', 'wbk' ),
            array( $this, 'render_email_label' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_label', array( $this, 'validate_email_label' ) );
        // *** phone
        add_settings_field(
            'wbk_phone_label',
            __( 'Phone label (booking form)', 'wbk' ),
            array( $this, 'render_phone_label' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_phone_label', array( $this, 'validate_phone_label' ) );
        // *** comment
        add_settings_field(
            'wbk_comment_label',
            __( 'Comment label (booking form)', 'wbk' ),
            array( $this, 'render_comment_label' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_comment_label', array( $this, 'validate_comment_label' ) );
        // end 2.2.8 settings pack
        add_settings_field(
            'wbk_book_thanks_message',
            __( 'Booking done message', 'wbk' ),
            array( $this, 'render_book_thanks_message' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_book_not_found_message', array( $this, 'validate_book_not_found_message' ) );
        add_settings_field(
            'wbk_book_not_found_message',
            __( 'Time slots not found message', 'wbk' ),
            array( $this, 'render_book_not_found_message' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_book_thanks_message', array( $this, 'validate_book_thanks_message' ) );
        add_settings_field(
            'wbk_payment_pay_with_paypal_btn_text',
            __( 'PayPal payment button text', 'wbk' ),
            array( $this, 'render_payment_pay_with_paypal_btn_text' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_payment_pay_with_paypal_btn_text', array( $this, 'validate_payment_pay_with_paypal_btn_text' ) );
        add_settings_field(
            'wbk_payment_pay_with_cc_btn_text',
            __( 'Credit card payment button text', 'wbk' ),
            array( $this, 'render_payment_pay_with_cc_btn_text' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_payment_pay_with_cc_btn_text', array( $this, 'validate_payment_pay_with_cc_btn_text' ) );
        add_settings_field(
            'wbk_payment_details_title',
            __( 'Payment details title', 'wbk' ),
            array( $this, 'render_payment_details_title' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_payment_details_title', array( $this, 'validate_payment_details_title' ) );
        add_settings_field(
            'wbk_payment_item_name',
            __( 'Payment item name', 'wbk' ),
            array( $this, 'render_payment_item_name' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_payment_item_name', array( $this, 'validate_payment_item_name' ) );
        add_settings_field(
            'wbk_payment_price_format',
            __( 'Price format', 'wbk' ),
            array( $this, 'render_payment_price_format' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_payment_price_format', array( $this, 'validate_payment_price_format' ) );
        add_settings_field(
            'wbk_payment_subtotal_title',
            __( 'Subtotal title', 'wbk' ),
            array( $this, 'render_payment_subtotal_title' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_payment_subtotal_title', array( $this, 'validate_payment_subtotal_title' ) );
        add_settings_field(
            'wbk_payment_total_title',
            __( 'Total title', 'wbk' ),
            array( $this, 'render_payment_total_title' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_payment_total_title', array( $this, 'validate_payment_total_title' ) );
        add_settings_field(
            'wbk_nothing_to_pay_message',
            __( 'Message if no booking available for payment found', 'wbk' ),
            array( $this, 'render_nothing_to_pay_message' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        add_settings_field(
            'wbk_show_locked_as_booked',
            __( 'Show locked time slots as booked', 'wbk' ),
            array( $this, 'render_show_locked_as_booked' ),
            'wbk-options',
            'wbk_mode_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_show_locked_as_booked', array( $this, 'validate_show_locked_as_booked' ) );
        add_settings_field(
            'wbk_allow_attachemnt',
            __( 'Allow using attachments', 'wbk' ),
            array( $this, 'render_allow_using_attachments' ),
            'wbk-options',
            'wbk_mode_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_allow_attachemnt', array( $this, 'validate_allow_using_attachments' ) );
        add_settings_field(
            'wbk_delete_attachemnt',
            __( 'Automatically delete attachments', 'wbk' ),
            array( $this, 'render_delete_attachemnt' ),
            'wbk-options',
            'wbk_mode_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_delete_attachemnt', array( $this, 'validate_delete_attachemnt' ) );
        add_settings_field(
            'wbk_allow_service_in_url',
            __( 'Allow using the service id in url', 'wbk' ),
            array( $this, 'render_allow_service_in_url' ),
            'wbk-options',
            'wbk_mode_settings_section',
            array()
        );
        add_settings_field(
            'wbk_order_service_by',
            __( 'Order service by', 'wbk' ),
            array( $this, 'render_order_service_by' ),
            'wbk-options',
            'wbk_mode_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_order_service_by', array( $this, 'validate_order_service_by' ) );
        register_setting( 'wbk_options', 'wbk_allow_service_in_url', array( $this, 'validate_allow_service_in_url' ) );
        add_settings_field(
            'wbk_night_hours',
            __( 'Show night hours time slots in previous day', 'wbk' ),
            array( $this, 'render_night_hours' ),
            'wbk-options',
            'wbk_mode_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_night_hours', array( $this, 'validate_night_hours' ) );
        wbk_opt()->add_option(
            'wbk_allow_cross_midnight',
            'checkbox',
            __( 'Allow time slots to cross midnight', 'wbk' ),
            '',
            'wbk_mode_settings_section',
            ''
        );
        add_settings_field(
            'wbk_multi_serv_date_limit',
            __( 'Limit date range for multi-service mode', 'wbk' ),
            array( $this, 'render_multi_serv_date_limit' ),
            'wbk-options',
            'wbk_mode_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_multi_serv_date_limit', array( $this, 'validate_multi_serv_date_limit' ) );
        add_settings_field(
            'wbk_avaiability_popup_calendar',
            __( 'Availability in popup calendar', 'wbk' ),
            array( $this, 'render_avaiability_popup_calendar' ),
            'wbk-options',
            'wbk_mode_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_avaiability_popup_calendar', array( $this, 'validate_avaiability_popup_calendar' ) );
        add_settings_field(
            'wbk_disallow_after',
            __( 'Disable time slots after X hours from the time of booking', 'wbk' ),
            array( $this, 'render_disallow_after' ),
            'wbk-options',
            'wbk_mode_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_disallow_after', array( $this, 'validate_disallow_after' ) );
        add_settings_field(
            'wbk_gdrp',
            __( 'EU GDPR Compliance', 'wbk' ),
            array( $this, 'render_gdrp' ),
            'wbk-options',
            'wbk_mode_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_gdrp', array( $this, 'validate_gdrp' ) );
        add_settings_field(
            'wbk_allow_ongoing_time_slot',
            __( 'Allow book ongoing time slot', 'wbk' ),
            array( $this, 'render_allow_ongoing_time_slot' ),
            'wbk-options',
            'wbk_mode_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_allow_ongoing_time_slot', array( $this, 'validate_allow_ongoing_time_slot' ) );
        add_settings_field(
            'wbk_days_in_extended_mode',
            __( 'Default number of days shown in extended mode', 'wbk' ),
            array( $this, 'render_days_in_extended_mode' ),
            'wbk-options',
            'wbk_mode_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_days_in_extended_mode', array( $this, 'validate_days_in_extended_mode' ) );
        add_settings_field(
            'wbk_show_details_prev_booking',
            __( 'Show details of previous bookings', 'wbk' ),
            array( $this, 'render_show_details_prev_booking' ),
            'wbk-options',
            'wbk_mode_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_show_details_prev_booking', array( $this, 'validate_show_details_prev_booking' ) );
        add_settings_field(
            'wbk_availability_calculation_method',
            __( 'Availability calculation method', 'wbk' ),
            array( $this, 'render_availability_calculation_method' ),
            'wbk-options',
            'wbk_mode_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_availability_calculation_method', array( $this, 'validate_availability_calculation_method' ) );
        wbk_opt()->add_option(
            'wbk_mode_overlapping_availabiliy',
            'checkbox',
            __( 'Consider the availabiliy of overlapping time intervals', 'wbk' ),
            '',
            'wbk_mode_settings_section',
            'true'
        );
        register_setting( 'wbk_options', 'wbk_nothing_to_pay_message', array( $this, 'validate_nothing_to_pay_message' ) );
        add_settings_field(
            'wbk_payment_approve_text',
            __( 'Approve payment', 'wbk' ),
            array( $this, 'render_payment_approve_text' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_payment_approve_text', array( $this, 'validate_payment_approve_text' ) );
        add_settings_field(
            'wbk_payment_result_title',
            __( 'Payment result title', 'wbk' ),
            array( $this, 'render_payment_result_title' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_payment_result_title', array( $this, 'validate_payment_result_title' ) );
        add_settings_field(
            'wbk_payment_success_message',
            __( 'Payment result success message', 'wbk' ),
            array( $this, 'render_payment_success_message' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_payment_success_message', array( $this, 'validate_payment_success_message' ) );
        add_settings_field(
            'wbk_payment_cancel_message',
            __( 'Payment result cancel message', 'wbk' ),
            array( $this, 'render_payment_cancel_message' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_payment_cancel_message', array( $this, 'validate_payment_cancel_message' ) );
        add_settings_field(
            'wbk_cancel_button_text',
            __( 'Booking cancel button text', 'wbk' ),
            array( $this, 'render_cancel_button_text' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_cancel_button_text', array( $this, 'validate_cancel_button_text' ) );
        add_settings_field(
            'wbk_checkout_button_text',
            __( 'Checkout button text', 'wbk' ),
            array( $this, 'render_checkout_button_text' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_checkout_button_text', array( $this, 'validate_checkout_button_text' ) );
        add_settings_field(
            'wbk_appointment_information',
            __( 'Appointment information', 'wbk' ),
            array( $this, 'render_appointment_information' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_appointment_information', array( $this, 'validate_appointment_information' ) );
        add_settings_field(
            'wbk_booking_cancel_email_label',
            __( 'Email input label on cancel booking', 'wbk' ),
            array( $this, 'render_booking_cancel_email_label' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_booking_cancel_email_label', array( $this, 'validate_booking_cancel_email_label' ) );
        add_settings_field(
            'wbk_booking_canceled_message',
            __( 'Booking canceled message (cutomer)', 'wbk' ),
            array( $this, 'render_booking_canceled_message' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_booking_canceled_message', array( $this, 'validate_booking_canceled_message' ) );
        add_settings_field(
            'wbk_booking_canceled_message_admin',
            __( 'Booking canceled message (admin)', 'wbk' ),
            array( $this, 'render_booking_canceled_message_admin' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_booking_canceled_message_admin', array( $this, 'validate_booking_canceled_message_admin' ) );
        add_settings_field(
            'wbk_booking_approved_message_admin',
            __( 'Booking approved message (admin)', 'wbk' ),
            array( $this, 'render_booking_approved_message_admin' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_booking_approved_message_admin', array( $this, 'validate_booking_approved_message_admin' ) );
        add_settings_field(
            'wbk_booking_cancel_error_message',
            __( 'Error message on cancel booking', 'wbk' ),
            array( $this, 'render_booking_cancel_error_message' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_booking_cancel_error_message', array( $this, 'validate_booking_cancel_error_message' ) );
        add_settings_field(
            'wbk_booking_couldnt_be_canceled',
            __( 'Warning message on cancel booking (reason: paid booking)', 'wbk' ),
            array( $this, 'render_booking_couldnt_be_canceled' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_booking_couldnt_be_canceled', array( $this, 'validate_booking_couldnt_be_canceled' ) );
        add_settings_field(
            'wbk_booking_couldnt_be_canceled2',
            __( 'Warning message on cancel booking (buffer)', 'wbk' ),
            array( $this, 'render_booking_couldnt_be_canceled2' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_booking_couldnt_be_canceled2', array( $this, 'validate_booking_couldnt_be_canceled2' ) );
        add_settings_field(
            'wbk_email_landing_text',
            __( 'Text of the payment link (customer)', 'wbk' ),
            array( $this, 'render_email_landing_text' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_landing_text', array( $this, 'validate_email_landing_text' ) );
        add_settings_field(
            'wbk_email_landing_text_cancel',
            __( 'Text of the cancellation link (customer)', 'wbk' ),
            array( $this, 'render_email_landing_text_cancel' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_landing_text_cancel', array( $this, 'validate_email_landing_text_cancel' ) );
        add_settings_field(
            'wbk_email_landing_text_cancel_admin',
            __( 'Text of the cancellation link (administrator)', 'wbk' ),
            array( $this, 'render_email_landing_text_cancel_admin' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_landing_text_cancel_admin', array( $this, 'validate_email_landing_text_cancel_admin' ) );
        add_settings_field(
            'wbk_email_landing_text_approve_admin',
            __( 'Text of the approval link (administrator)', 'wbk' ),
            array( $this, 'render_email_landing_text_approve_admin' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_landing_text_approve_admin', array( $this, 'validate_email_landing_text_approve_admin' ) );
        add_settings_field(
            'wbk_email_landing_text_gg_event_add',
            __( 'Text of the link for adding to customer\'s Google Calendar', 'wbk' ),
            array( $this, 'render_email_landing_text_gg_event_add' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_landing_text_gg_event_add', array( $this, 'validate_email_landing_text_gg_event_add' ) );
        add_settings_field(
            'wbk_add_gg_button_text',
            __( 'Add to customer\'s Google Calendar button text', 'wbk' ),
            array( $this, 'render_add_gg_button_text' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_add_gg_button_text', array( $this, 'validate_add_gg_button_text' ) );
        add_settings_field(
            'wbk_gg_calendar_add_event_success',
            __( 'Google calendar event adding success message', 'wbk' ),
            array( $this, 'render_gg_calendar_add_event_success' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_gg_calendar_add_event_success', array( $this, 'validate_gg_calendar_add_event_success' ) );
        add_settings_field(
            'wbk_gg_calendar_add_event_canceled',
            __( 'Google calendar event adding error message', 'wbk' ),
            array( $this, 'render_gg_calendar_add_event_canceled' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_gg_calendar_add_event_canceled', array( $this, 'validate_gg_calendar_add_event_canceled' ) );
        add_settings_field(
            'wbk_email_landing_text_invalid_token',
            __( 'Appointment token error message', 'wbk' ),
            array( $this, 'render_email_landing_text_invalid_token' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_email_landing_text_invalid_token', array( $this, 'validate_email_landing_text_invalid_token' ) );
        add_settings_field(
            'wbk_gg_calendar_event_title',
            __( 'Google calendar event / iCal summary (administrator)', 'wbk' ),
            array( $this, 'render_gg_calendar_event_title' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_gg_calendar_event_title', array( $this, 'validate_gg_calendar_event_title' ) );
        add_settings_field(
            'wbk_gg_calendar_event_description',
            __( 'Google calendar event / iCal description (administrator)', 'wbk' ),
            array( $this, 'render_gg_calendar_event_description' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_gg_calendar_event_description', array( $this, 'validate_gg_calendar_event_description' ) );
        add_settings_field(
            'wbk_gg_calendar_event_title_customer',
            __( 'Google calendar event / iCal summary (customer)', 'wbk' ),
            array( $this, 'render_gg_calendar_event_title_customer' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_gg_calendar_event_title_customer', array( $this, 'validate_gg_calendar_event_title_customer' ) );
        add_settings_field(
            'wbk_gg_calendar_event_description_customer',
            __( 'Google calendar event / iCal description (customer)', 'wbk' ),
            array( $this, 'render_gg_calendar_event_description_customer' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_gg_calendar_event_description_customer', array( $this, 'validate_gg_calendar_event_description_customer' ) );
        add_settings_field(
            'wbk_stripe_button_text',
            __( 'Stripe button text', 'wbk' ),
            array( $this, 'render_stripe_button_text' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_stripe_button_text', array( $this, 'validate_stripe_button_text' ) );
        add_settings_field(
            'wbk_stripe_card_element_error_message',
            __( 'Stripe card element error message', 'wbk' ),
            array( $this, 'render_stripe_card_element_error_message' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_stripe_card_element_error_message', array( $this, 'validate_stripe_card_element_error_message' ) );
        add_settings_field(
            'wbk_stripe_api_error_message',
            __( 'Stripe API error message', 'wbk' ),
            array( $this, 'render_stripe_api_error_message' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_stripe_api_error_message', array( $this, 'validate_stripe_api_error_message' ) );
        add_settings_field(
            'wbk_pay_on_arrival_button_text',
            __( 'Pay on arrival button text', 'wbk' ),
            array( $this, 'render_pay_on_arrival_button_text' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_pay_on_arrival_button_text', array( $this, 'validate_pay_on_arrival_button_text' ) );
        add_settings_field(
            'wbk_pay_on_arrival_message',
            __( 'Message for Pay on arrival payment method', 'wbk' ),
            array( $this, 'render_pay_on_arrival_message' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_pay_on_arrival_message', array( $this, 'validate_pay_on_arrival_message' ) );
        add_settings_field(
            'wbk_bank_transfer_button_text',
            __( 'Bank transfer button text', 'wbk' ),
            array( $this, 'render_bank_transfer_button_text' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_bank_transfer_button_text', array( $this, 'validate_bank_transfer_button_text' ) );
        add_settings_field(
            'wbk_bank_transfer_message',
            __( 'Message for Bank transfer payment method', 'wbk' ),
            array( $this, 'render_bank_transfer_message' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_bank_transfer_message', array( $this, 'validate_bank_transfer_message' ) );
        add_settings_field(
            'wbk_coupon_field_placeholder',
            __( 'Coupon code field placeholder', 'wbk' ),
            array( $this, 'render_coupon_field_placeholder' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_coupon_field_placeholder', array( $this, 'validate_coupon_field_placeholder' ) );
        add_settings_field(
            'wbk_coupon_applied',
            __( 'Coupon success message', 'wbk' ),
            array( $this, 'render_coupon_applied' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_coupon_applied', array( $this, 'validate_coupon_applied' ) );
        add_settings_field(
            'wbk_coupon_not_applied',
            __( 'Coupon failed message', 'wbk' ),
            array( $this, 'render_coupon_not_applied' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_coupon_not_applied', array( $this, 'validate_coupon_not_applied' ) );
        add_settings_field(
            'wbk_payment_discount_item',
            __( 'Discount in payment calculation', 'wbk' ),
            array( $this, 'render_payment_discount_item' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_payment_discount_item', array( $this, 'validate_payment_discount_item' ) );
        add_settings_field(
            'wbk_product_meta_key',
            __( 'Meta key for WooCommerce product', 'wbk' ),
            array( $this, 'render_product_meta_key' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_product_meta_key', array( $this, 'validate_product_meta_key' ) );
        add_settings_field(
            'wbk_woo_button_text',
            __( 'WooCommerce button text', 'wbk' ),
            array( $this, 'render_woo_button_text' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_woo_button_text', array( $this, 'validate_woo_button_text' ) );
        add_settings_field(
            'wbk_woo_error_add_to_cart',
            __( 'Add to cart error message', 'wbk' ),
            array( $this, 'render_woo_error_add_to_cart' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_woo_error_add_to_cart', array( $this, 'validate_woo_error_add_to_cart' ) );
        add_settings_field(
            'wbk_day_label',
            __( 'Date label above the timeslots', 'wbk' ),
            array( $this, 'render_day_label' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_day_label', array( $this, 'validate_day_label' ) );
        add_settings_field(
            'wbk_validation_error_message',
            __( 'Field validation error message', 'wbk' ),
            array( $this, 'render_validation_error_message' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_validation_error_message', array( $this, 'validate_validation_error_message' ) );
        add_settings_field(
            'wbk_daily_limit_reached_message',
            __( 'Daily limit reached message', 'wbk' ),
            array( $this, 'render_daily_limit_reached_message' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_daily_limit_reached_message', array( $this, 'validate_daily_limit_reached_message' ) );
        add_settings_field(
            'wbk_limit_by_email_reached_message',
            __( 'Limit by email reached message', 'wbk' ),
            array( $this, 'render_limit_by_email_reached_message' ),
            'wbk-options',
            'wbk_translation_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_limit_by_email_reached_message', array( $this, 'validate_limit_by_email_reached_message' ) );
        wbk_opt()->add_option(
            'wbk_service_fee_description',
            'text',
            __( 'Description of the service fee', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            ''
        );
        wbk_opt()->add_option(
            'wbk_tax_label',
            'text',
            __( 'Tax label', 'wbk' ),
            '',
            'wbk_translation_settings_section',
            __( 'Tax', 'wbk' )
        );
        // $slug, $type, $title, $description, $section, $default_value,
        wbk_opt()->add_option(
            'wbk_no_dates_label',
            'text',
            __( 'No available dates message', 'wbk' ),
            __( 'Used with the dropdown date input', 'wbk' ),
            'wbk_translation_settings_section',
            __( 'Sorry, no free dates', 'wbk' )
        );
        // appointments settings section init ******************************************************************************
        add_settings_section(
            'wbk_appointments_settings_section',
            __( 'Appointments', 'wbk' ),
            array( $this, 'wbk_appointments_settings_section_callback' ),
            'wbk-options'
        );
        // ******************************************************************************************************
        // backend interface section init start *******************************************************************
        add_settings_section(
            'wbk_interface_settings_section',
            __( 'Backend interface', 'wbk' ),
            array( $this, 'wbk_backend_interface_settings_section_callback' ),
            'wbk-options'
        );
        add_settings_field(
            'wbk_customer_name_output',
            __( 'Customer name in the backend', 'wbk' ),
            array( $this, 'render_customer_name_output' ),
            'wbk-options',
            'wbk_interface_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_customer_name_output', array( $this, 'validate_customer_name_output' ) );
        // date format backend
        add_settings_field(
            'wbk_date_format_backend',
            __( 'Date format (backend)', 'wbk' ),
            array( $this, 'render_date_format_backend' ),
            'wbk-options',
            'wbk_interface_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_date_format_backend', array( $this, 'validate_date_format_backend' ) );
        add_settings_field(
            'wbk_appointments_table_columns',
            __( 'Columns of Appointment table', 'wbk' ),
            array( $this, 'render_appointments_table_columns' ),
            'wbk-options',
            'wbk_interface_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_appointments_table_columns', array( $this, 'validate_appointments_table_columns' ) );
        add_settings_field(
            'wbk_date_format_time_slot_schedule',
            __( 'Format of timeslots in the Schedule page', 'wbk' ),
            array( $this, 'render_date_format_time_slot_schedule' ),
            'wbk-options',
            'wbk_interface_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_date_format_time_slot_schedule', array( $this, 'validate_date_format_time_slot_schedule' ) );
        add_settings_field(
            'wbk_backend_select_services_onload',
            __( 'Select all services on the Appointments page automatically', 'wbk' ),
            array( $this, 'render_backend_select_services_onload' ),
            'wbk-options',
            'wbk_interface_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_backend_select_services_onload', array( $this, 'validate_backend_select_services_onload' ) );
        add_settings_field(
            'wbk_custom_fields_columns',
            __( 'Custom field columns', 'wbk' ),
            array( $this, 'render_custom_fields_columns' ),
            'wbk-options',
            'wbk_interface_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_custom_fields_columns', array( $this, 'validate_custom_fields_columns' ) );
        add_settings_field(
            'wbk_backend_show_category_name',
            __( 'Show category name after service name', 'wbk' ),
            array( $this, 'render_backend_show_category_name' ),
            'wbk-options',
            'wbk_interface_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_backend_show_category_name', array( $this, 'validate_backend_show_category_name' ) );
        add_settings_field(
            'wbk_filter_default_days_number',
            __( 'The default number of dates to display on the appointment page', 'wbk' ),
            array( $this, 'render_default_days_number' ),
            'wbk-options',
            'wbk_interface_settings_section',
            array()
        );
        register_setting( 'wbk_options', 'wbk_filter_default_days_number', array( $this, 'validate_default_days_number' ) );
        wbk_opt()->add_option(
            'wbk_backend_add_buttons_in_editor',
            'checkbox',
            __( 'Add shortcode buttons to the editor', 'wbk' ),
            '',
            'wbk_interface_settings_section',
            'true'
        );
        // backend interface section init end  ********************************************************************
        date_default_timezone_set( 'UTC' );
        $all_times = array();
        $format = WBK_Date_Time_Utils::getTimeFormat();
        for ( $i = 0 ;  $i < 86400 ;  $i += 600 ) {
            $all_times[$i] = wp_date( $format, $i, new DateTimeZone( date_default_timezone_get() ) );
        }
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $time_description = __( 'Current local time:', 'wbk' ) . ' ' . date( $format );
        date_default_timezone_set( 'UTC' );
        do_action( 'wbk_options_after' );
    }
    
    public function wbk_settings_section_callback()
    {
    }
    
    // init styles and scripts
    public function enqueueScripts()
    {
        
        if ( isset( $_GET['page'] ) && $_GET['page'] == 'wbk-options' ) {
            wp_deregister_script( 'chosen' );
            wp_enqueue_script( 'jquery-plugin', plugins_url( 'js/jquery.plugin.js', dirname( __FILE__ ) ), array( 'jquery' ) );
            //wp_enqueue_script( 'multidate-picker', plugins_url( 'js/jquery.datepick.min.js', dirname( __FILE__ ) ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker' ) );
            wp_enqueue_script(
                'wbk-options',
                plugins_url( 'js/wbk-options.js', dirname( __FILE__ ) ),
                array(
                'jquery',
                'jquery-ui-core',
                'jquery-ui-dialog',
                'jquery-ui-tabs'
            ),
                '4.0.5'
            );
            wp_enqueue_script( 'wbk-minicolors', plugins_url( 'js/jquery.minicolors.min.js', dirname( __FILE__ ) ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-dialog' ) );
            wp_enqueue_style( 'wbk-datepicker-css', plugins_url( 'css/jquery.datepick.css', dirname( __FILE__ ) ) );
            wp_enqueue_script( 'slf-chosen', plugins_url( 'js/chosen.jquery.min.js', dirname( __FILE__ ) ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker' ) );
            wp_enqueue_style( 'slf-chosen-css', plugins_url( 'css/chosen.min.css', dirname( __FILE__ ) ) );
        }
    
    }
    
    // general settings section callback
    public function wbk_general_settings_section_callback( $arg )
    {
    }
    
    // schedule settings section callback
    public function wbk_schedule_settings_section_callback( $arg )
    {
    }
    
    // email settings section callback
    public function wbk_email_settings_section_callback( $arg )
    {
    }
    
    // appearance  settings section callback
    public function wbk_mode_settings_section_callback( $arg )
    {
    }
    
    // appearance  settings section callback
    public function wbk_translation_settings_section_callback( $arg )
    {
    }
    
    // backend interface settings section callback
    public function wbk_backend_interface_settings_section_callback( $arg )
    {
    }
    
    // paypal settings section callback
    public function wbk_paypal_settings_section_callback( $arg )
    {
    }
    
    // stripe settings section callback
    public function wbk_stripe_settings_section_callback( $arg )
    {
    }
    
    // google
    // google calendar settings section callback
    public function wbk_gg_calendar_settings_section_callback( $arg )
    {
    }
    
    // woo settings section callback
    public function wbk_woo_settings_section_callback( $arg )
    {
    }
    
    // appointments settings section callback
    public function wbk_appointments_settings_section_callback( $arg )
    {
    }
    
    public function render_email_admin_paymentrecvd_status()
    {
        $value = get_option( 'wbk_email_admin_paymentrcvd_status' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_admin_paymentrcvd_status" name="wbk_email_admin_paymentrcvd_status" value="true" >';
        $html .= '<label for="wbk_email_admin_paymentrcvd_status">' . __( 'Check if you\'d like to send administrator an email when payment received', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    public function validate_email_admin_paymentrcvd_status( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_admin_paymentrcvd_status',
                'wbk_email_admin_paymentrcvd_status_error',
                __( 'Email status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    public function render_email_admin_paymentrcvd_subject()
    {
        $value = get_option( 'wbk_email_admin_paymentrcvd_subject' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_admin_paymentrcvd_subject" name="wbk_email_admin_paymentrcvd_subject" value="' . $value . '" >';
        $html .= '<p class="description"><a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'wbk' ) . '</a></p>';
        echo  $html ;
    }
    
    public function validate_email_admin_paymentrcvd_subject( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    public function render_email_admin_paymentrcvd_message()
    {
        $value = get_option( 'wbk_email_admin_paymentrcvd_message' );
        $mcesettings = array();
        $mcesettings['valid_elements'] = '*[*]';
        $mcesettings['extended_valid_elements'] = '*[*]';
        $args = array(
            'media_buttons' => false,
            'editor_height' => 300,
            'tinymce'       => $mcesettings,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_email_admin_paymentrcvd_message', $args );
        echo  '</div>' ;
    }
    
    public function validate_email_admin_paymentrcvd_message( $input )
    {
        return $input;
    }
    
    public function render_email_customer_paymentrecvd_status()
    {
        $value = get_option( 'wbk_email_customer_paymentrcvd_status' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_customer_paymentrcvd_status" name="wbk_email_customer_paymentrcvd_status" value="true" >';
        $html .= '<label for="wbk_email_customer_paymentrcvd_status">' . __( 'Check if you\'d like to send customer an email when payment received', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    public function validate_email_customer_paymentrcvd_status( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_customer_paymentrcvd_status',
                'wbk_email_customer_paymentrcvd_status_error',
                __( 'Email status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    public function render_email_customer_paymentrcvd_subject()
    {
        $value = get_option( 'wbk_email_customer_paymentrcvd_subject' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_customer_paymentrcvd_subject" name="wbk_email_customer_paymentrcvd_subject" value="' . $value . '" >';
        $html .= '<p class="description"><a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'wbk' ) . '</a></p>';
        echo  $html ;
    }
    
    public function validate_email_customer_paymentrcvd_subject( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    public function render_email_customer_arrived_subject()
    {
        $value = get_option( 'wbk_email_customer_arrived_subject' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_customer_arrived_subject" name="wbk_email_customer_arrived_subject" value="' . $value . '" >';
        $html .= '<p class="description"><a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'wbk' ) . '</a></p>';
        echo  $html ;
    }
    
    public function validate_email_customer_arrived_subject( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    public function render_email_customer_arrived_message()
    {
        $value = get_option( 'wbk_email_customer_arrived_message' );
        $args = array(
            'media_buttons' => false,
            'editor_height' => 300,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_email_customer_arrived_message', $args );
        echo  '</div>' ;
    }
    
    public function validate_email_customer_arrived_message( $input )
    {
        return $input;
    }
    
    public function render_email_customer_paymentrcvd_message()
    {
        $value = get_option( 'wbk_email_customer_paymentrcvd_message' );
        $args = array(
            'media_buttons' => false,
            'editor_height' => 300,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_email_customer_paymentrcvd_message', $args );
        echo  '</div>' ;
    }
    
    public function validate_email_customer_paymentrcvd_message( $input )
    {
        return $input;
    }
    
    public function render_coupon_field_placeholder()
    {
        $value = get_option( 'wbk_coupon_field_placeholder', __( 'Coupon code', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_coupon_field_placeholder" name="wbk_coupon_field_placeholder" value="' . $value . '" >';
        echo  $html ;
    }
    
    public function validate_coupon_field_placeholder( $input )
    {
        $input = sanitize_text_field( trim( $input ) );
        return $input;
    }
    
    public function render_coupon_applied()
    {
        $value = get_option( 'wbk_coupon_applied', __( 'Coupon applied', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_coupon_applied" name="wbk_coupon_applied" value="' . $value . '" >';
        echo  $html ;
    }
    
    public function validate_coupon_applied( $input )
    {
        $input = sanitize_text_field( trim( $input ) );
        return $input;
    }
    
    public function render_coupon_not_applied()
    {
        $value = get_option( 'wbk_coupon_not_applied', __( 'Coupon not applied', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_coupon_not_applied" name="wbk_coupon_not_applied" value="' . $value . '" >';
        echo  $html ;
    }
    
    public function validate_coupon_not_applied( $input )
    {
        $input = sanitize_text_field( trim( $input ) );
        return $input;
    }
    
    public function render_payment_discount_item()
    {
        $value = get_option( 'wbk_payment_discount_item', __( 'Discount', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_payment_discount_item" name="wbk_payment_discount_item" value="' . $value . '" >';
        echo  $html ;
    }
    
    public function validate_payment_discount_item( $input )
    {
        $input = sanitize_text_field( trim( $input ) );
        return $input;
    }
    
    public function render_start_of_week()
    {
        $html = '<select id="wbk_start_of_week" name="wbk_start_of_week">
				    <option ' . selected( get_option( 'wbk_start_of_week' ), 'sunday', false ) . ' value="sunday">' . __( 'Sunday', 'wbk' ) . '</option>
				    <option ' . selected( get_option( 'wbk_start_of_week' ), 'monday', false ) . ' value="monday">' . __( 'Monday', 'wbk' ) . '</option>
				    <option ' . selected( get_option( 'wbk_start_of_week' ), 'wordpress', false ) . ' value="wordpress">' . __( 'Wordpress default', 'wbk' ) . '</option>
  				</select>';
        echo  $html ;
    }
    
    // validate start of week
    public function validate_start_of_week( $input )
    {
        $input = trim( $input );
        
        if ( $input != 'sunday' && $input != 'monday' && $input != 'wordpress' ) {
            add_settings_error( 'wbk_start_of_week', 'wbk_start_of_week_error', __( 'Incorrect start of week', 'wbk' ) );
            return 'monday';
        } else {
            return $input;
        }
    
    }
    
    // render date format
    public function render_date_format()
    {
        $value = get_option( 'wbk_date_format' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_date_format" name="wbk_date_format" value="' . $value . '" >' . '<a href="https://wordpress.org/support/article/formatting-date-and-time/"   rel="noopener" target="_blank" ><span class="dashicons dashicons-editor-help"></span></a>';
        $html .= '<p class="description">' . __( 'Leave empty to use Wordpress Date Format. ', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate date format
    public function validate_date_format( $input )
    {
        $input = trim( $input );
        
        if ( strlen( $input ) > 20 ) {
            $input = substr( $input, 0, 19 );
            add_settings_error(
                'wbk_date_format',
                'wbk_date_format_error',
                __( 'Date format updated', 'wbk' ),
                'updated'
            );
        }
        
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render multi max
    public function render_multi_booking_max()
    {
        $value = get_option( 'wbk_multi_booking_max' );
        $value = trim( sanitize_text_field( $value ) );
        $html = '<input type="text" id="wbk_multi_booking_max" name="wbk_multi_booking_max" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'The amount of time slots allowed to your customers to reserve at a time.', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'Leave empty to not set the limit. ', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate multi max
    public function validate_multi_booking_max( $input )
    {
        $input = trim( sanitize_text_field( $input ) );
        if ( is_numeric( $input ) ) {
            if ( $input > 1 ) {
                return $input;
            }
        }
        return '';
    }
    
    // render dropdown dates count
    public function render_date_input_dropdown_count()
    {
        $value = get_option( 'wbk_date_input_dropdown_count', '30' );
        $value = trim( sanitize_text_field( $value ) );
        $html = '<input type="text" id="wbk_date_input_dropdown_count" name="wbk_date_input_dropdown_count" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Used only for dropdown date select.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate dropdown dates count
    public function validate_date_input_dropdown_count( $input )
    {
        $input = trim( sanitize_text_field( $input ) );
        if ( is_numeric( $input ) ) {
            if ( $input >= 1 ) {
                return $input;
            }
        }
        add_settings_error(
            'wbk_date_input_dropdown_count',
            'wbk_date_input_dropdown_count',
            __( 'Dropdown dates count updated', 'wbk' ),
            'updated'
        );
        return '30';
    }
    
    // render date format backend backend
    public function render_date_format_backend()
    {
        $value = get_option( 'wbk_date_format_backend', 'm/d/y' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_date_format_backend" name="wbk_date_format_backend">
				    <option ' . selected( $value, 'm/d/y', false ) . ' value="m/d/y">' . __( 'm/d/y', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'y/m/d', false ) . ' value="y/m/d">' . __( 'y/m/d', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'y-m-d', false ) . ' value="y-m-d">' . __( 'y-m-d', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'd.m.y', false ) . ' value="d.m.y">' . __( 'd.m.y', 'wbk' ) . '</option>

   				 </select>';
        $html .= '<p class="description">' . __( 'Used in the "Appointments" page controls. d - day, m - month, y - year.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate date format backend
    public function validate_date_format_backend( $input )
    {
        return $input;
    }
    
    public function render_date_format_time_slot_schedule()
    {
        $value = get_option( 'wbk_date_format_time_slot_schedule', 'start' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_date_format_time_slot_schedule" name="wbk_date_format_time_slot_schedule">
				    <option ' . selected( $value, 'start', false ) . ' value="start">' . __( 'Start', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'start-end', false ) . ' value="start-end">' . __( 'Start - End', 'wbk' ) . '</option>

   				 </select>';
        echo  $html ;
    }
    
    public function validate_date_format_time_slot_schedule( $input )
    {
        return $input;
    }
    
    public function render_backend_select_services_onload()
    {
        $value = get_option( 'wbk_backend_select_services_onload', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_backend_select_services_onload" name="wbk_backend_select_services_onload">
				    <option ' . selected( $value, 'enabled', false ) . ' value="enabled">' . __( 'Enabled', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Disabled', 'wbk' ) . '</option>
				 </select>';
        echo  $html ;
    }
    
    public function validate_backend_select_services_onload( $input )
    {
        return $input;
    }
    
    public function render_appointments_table_columns()
    {
        $value = get_option( 'wbk_appointments_table_columns', '' );
        if ( !is_array( $value ) ) {
            $value = Wbk_Db_Utils::getAppointmentColumns( true );
        }
        echo  '<select class="wbk_appointments_table_columns" name="wbk_appointments_table_columns[]"  id="wbk_appointments_table_columns[]" Multiple>' ;
        $arr_elements = Wbk_Db_Utils::getAppointmentColumns();
        foreach ( $arr_elements as $key => $element ) {
            
            if ( in_array( $key, $value ) ) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            
            echo  '<option ' . $selected . ' value="' . $key . '">' . $element . '</option>' ;
        }
        echo  '</select>' ;
    }
    
    public function validate_appointments_table_columns( $input )
    {
        return $input;
    }
    
    // render time format
    public function render_time_format()
    {
        $value = get_option( 'wbk_time_format' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_time_format" name="wbk_time_format" value="' . $value . '" >' . '<a href="https://wordpress.org/support/article/formatting-date-and-time/" rel="noopener" target="_blank" ><span class="dashicons dashicons-editor-help"></span></a>';
        $html .= '<p class="description">' . __( 'Leave empty to use Wordpress Time Format. ', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate time format
    public function validate_time_format( $input )
    {
        $input = trim( $input );
        
        if ( strlen( $input ) > 20 ) {
            $input = substr( $input, 0, 19 );
            add_settings_error(
                'wbk_time_format',
                'wbk_time_format_error',
                __( 'Time format updated', 'wbk' ),
                'updated'
            );
        }
        
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render phone mask
    public function render_phone_mask()
    {
        $value = get_option( 'wbk_phone_mask' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_phone_mask" name="wbk_phone_mask">
				    <option ' . selected( $value, 'enabled', false ) . ' value="enabled">' . __( 'jQuery Masked Input Plugin', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'enabled_mask_plugin', false ) . ' value="enabled_mask_plugin">' . __( 'jQuery Mask Plugin', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Disabled', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    // validate phone mask
    public function validate_phone_mask( $input )
    {
        $input = trim( $input );
        return $input;
        $value = sanitize_text_field( $value );
        if ( $value != 'enabled' && $value != 'disabled' ) {
            $value = 'enabled';
        }
        return $input;
    }
    
    // render phone format
    public function render_phone_format()
    {
        $value = get_option( 'wbk_phone_format' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_phone_format" name="wbk_phone_format" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'jQuery Masked Input Plugin format example: (999) 999-9999. "9" represents numeric symbol.', 'wbk' ) . ' ';
        $html .= '<p class="description">' . __( 'jQuery Mask Plugin format example: (000) 000-0000. "0" represents numeric symbol.', 'wbk' ) . ' ';
        $html .= '<a href="https://igorescobar.github.io/jQuery-Mask-Plugin/" rel="noopener" target="_blank">' . __( 'More information', 'wbk' ) . '</a>' . '</p>';
        echo  $html ;
    }
    
    // validate phone format
    public function validate_phone_format( $input )
    {
        $input = trim( $input );
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render timezone
    public function render_timezone()
    {
        $value = get_option( 'wbk_timezone' );
        $arr_timezones = timezone_identifiers_list();
        $html = '<select id="wbk_timezone" name="wbk_timezone" >';
        foreach ( $arr_timezones as $timezone ) {
            
            if ( $timezone == $value ) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            
            $html .= "<option {$selected} value=\"{$timezone}\">{$timezone}</option>";
        }
        $html .= '</select>';
        echo  $html ;
    }
    
    // validate timezone
    public function validate_timezone( $input )
    {
        return $input;
    }
    
    // render holydays
    public function render_holydays()
    {
        $value = get_option( 'wbk_holydays' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_holydays" name="wbk_holydays" value="' . $value . '" >';
        $format = get_option( 'wbk_date_format_backend', 'm/d/y' );
        switch ( $format ) {
            case 'm/d/y':
                $format_updated = 'mm/dd/yyyy';
                break;
            case 'y/m/d':
                $format_updated = 'yyyy/mm/dd';
                break;
            case 'y-m-d':
                $format_updated = 'yyyy-mm-dd';
                break;
            case 'd.m.y':
                $format_updated = 'dd.mm.yyyy';
                break;
        }
        $html .= '<input type="hidden" id="wbk_holydays_format" value="' . $format_updated . '">';
        $html .= '<p class="description">' . __( 'Please set this option as a comma-separated list of dates (no spaces). Use the same date format as set in the Backend interface tab.', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'This option should be used to set only holidays. Do not use it to set weekends (there is a Business hours parameter of services for this purpose)', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate holydays
    public function validate_holydays( $input )
    {
        return $input;
    }
    
    public function render_recurring_holidays()
    {
        $value = get_option( 'wbk_recurring_holidays' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_recurring_holidays" name="wbk_recurring_holidays" value="true" >';
        $html .= '<label for="wbk_recurring_holidays">' . __( 'Check if you\'d like to make holidays recurring', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    public function validate_recurring_holidays( $input )
    {
        if ( $input != 'true' && $input != '' ) {
            $input = '';
        }
        return $input;
    }
    
    // render email to customer (on booking)
    public function render_email_customer_book_status()
    {
        $value = get_option( 'wbk_email_customer_book_status' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_customer_book_status" name="wbk_email_customer_book_status" value="true" >';
        $html .= '<label for="wbk_email_customer_book_status">' . __( 'Check if you\'d like to send customer an email on booking', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    // validate email to customer (on booking)
    public function validate_email_customer_book_status( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_customer_book_status',
                'wbk_email_customer_book_status_error',
                __( 'Email status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    // render email to customer (on approve)
    public function render_email_customer_approve_status()
    {
        $value = get_option( 'wbk_email_customer_approve_status' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_customer_approve_status" name="wbk_email_customer_approve_status" value="true" >';
        $html .= '<label for="wbk_email_customer_approve_status">' . __( 'Check if you\'d like to send customer an email on approval', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    // validate email to customer (on approve)
    public function validate_email_customer_approve_status( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_customer_approve_status',
                'wbk_email_customer_approve_status_error',
                __( 'Email (on approval) status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    //****** start admin cacnelation block
    // render email to admin (on cancel)
    public function render_email_admin_appointment_cancel_status()
    {
        $value = get_option( 'wbk_email_adimn_appointment_cancel_status' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_adimn_appointment_cancel_status" name="wbk_email_adimn_appointment_cancel_status" value="true" >';
        $html .= '<label for="wbk_email_adimn_appointment_cancel_status">' . __( 'Check if you\'d like to send administrator an email on appointment cancellation', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    // validate email to customer (on cancel)
    public function validate_email_admin_appointment_cancel_status( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_adimn_appointment_cancel_status',
                'wbk_email_adimn_appointment_cancel_status',
                __( 'Email (on cancellation) status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    // render admin email subject (on cancellation)
    public function render_email_admin_appointment_cancel_subject()
    {
        $value = get_option( 'wbk_email_adimn_appointment_cancel_subject', __( 'Appointment canceled', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_adimn_appointment_cancel_subject" name="wbk_email_adimn_appointment_cancel_subject" value="' . $value . '" >';
        $html .= '<p class="description"><a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'wbk' ) . '</a></p>';
        echo  $html ;
    }
    
    // validate admin email subject (on cancellation)
    public function validate_email_admin_appointment_cancel_subject( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render email to admin message (cancellation)
    public function render_email_admin_appointment_cancel_message()
    {
        $value = get_option( 'wbk_email_adimn_appointment_cancel_message', '<p>#customer_name canceled the appointment with #service_name on #appointment_day at #appointment_time</p>' );
        $args = array(
            'media_buttons' => false,
            'editor_height' => 300,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_email_adimn_appointment_cancel_message', $args );
        echo  '</div>' ;
    }
    
    // validate email toadmin message (cancellation)
    public function validate_email_admin_appointment_cancel_message( $input )
    {
        return $input;
    }
    
    //****** end admin cacnelation block
    //****** start customer cacnelation block
    // render email to customer (on cancel)
    public function render_email_customer_appointment_cancel_status()
    {
        $value = get_option( 'wbk_email_customer_appointment_cancel_status' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_customer_appointment_cancel_status" name="wbk_email_customer_appointment_cancel_status" value="true" >';
        $html .= '<label for="wbk_email_customer_appointment_cancel_status">' . __( 'Check if you\'d like to send customer an email on appointment cancellation', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    // validate email to customer (on cancel)
    public function validate_email_customer_appointment_cancel_status( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_customer_appointment_cancel_status',
                'wbk_email_customer_appointment_cancel_status',
                __( 'Email (on cancellation) status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    public function render_email_customer_cancel_multiple_mode()
    {
        $value = get_option( 'wbk_email_customer_cancel_multiple_mode', 'foreach' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_email_customer_cancel_multiple_mode" name="wbk_email_customer_cancel_multiple_mode">
			    <option ' . selected( $value, 'foreach', false ) . ' value="foreach">' . __( 'Send Email for each cancelled time slot', 'wbk' ) . '</option>
			    <option ' . selected( $value, 'one', false ) . ' value="one">' . __( 'Send one Email for all cancelled time slots', 'wbk' ) . '</option>
				 </select>';
        echo  $html ;
    }
    
    public function validate_email_customer_cancel_multiple_mode( $input )
    {
        return $input;
    }
    
    public function render_email_admin_cancel_multiple_mode()
    {
        $value = get_option( 'wbk_email_admin_cancel_multiple_mode', 'foreach' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_email_admin_cancel_multiple_mode" name="wbk_email_admin_cancel_multiple_mode">
				    <option ' . selected( $value, 'foreach', false ) . ' value="foreach">' . __( 'Send Email for each booked time slot', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'one', false ) . ' value="one">' . __( 'Send one Email for all booked time slots', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    public function validate_email_admin_cancel_multiple_mode( $input )
    {
        return $input;
    }
    
    // render customer email subject (on cancellation)
    public function render_email_customer_appointment_cancel_subject()
    {
        $value = get_option( 'wbk_email_customer_appointment_cancel_subject', __( 'Your appointment canceled', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_customer_appointment_cancel_subject" name="wbk_email_customer_appointment_cancel_subject" value="' . $value . '" >';
        $html .= '<p class="description"><a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'wbk' ) . '</a></p>';
        echo  $html ;
    }
    
    // validate customer email subject (on cancellation)
    public function validate_email_customer_appointment_cancel_subject( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render email to customer message (cancellation)
    public function render_email_customer_appointment_cancel_message()
    {
        $value = get_option( 'wbk_email_customer_appointment_cancel_message', '<p>Your appointment with #service_name on #appointment_day at #appointment_time has been canceled</p>' );
        $args = array(
            'media_buttons' => false,
            'editor_height' => 300,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_email_customer_appointment_cancel_message', $args );
        echo  '</div>' ;
    }
    
    public function validate_email_customer_appointment_cancel_message( $input )
    {
        return $input;
    }
    
    public function render_email_customer_bycustomer_appointment_cancel_message()
    {
        $value = get_option( 'wbk_email_customer_bycustomer_appointment_cancel_message', '<p>Your appointment with #service_name on #appointment_day at #appointment_time has been canceled</p>' );
        $args = array(
            'media_buttons' => false,
            'editor_height' => 300,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_email_customer_bycustomer_appointment_cancel_message', $args );
        echo  '</div>' ;
    }
    
    public function validate_email_customer_bycustomer_appointment_cancel_message( $input )
    {
        return $input;
    }
    
    public function render_email_customer_book_message()
    {
        $value = get_option( 'wbk_email_customer_book_message' );
        $args = array(
            'media_buttons' => false,
            'editor_height' => 300,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_email_customer_book_message', $args );
        echo  '</div>' ;
    }
    
    // validate email to customer message
    public function validate_email_customer_book_message( $input )
    {
        return $input;
    }
    
    public function render_email_customer_manual_book_message()
    {
        $value = get_option( 'wbk_email_customer_manual_book_message' );
        $args = array(
            'media_buttons' => false,
            'editor_height' => 300,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_email_customer_manual_book_message', $args );
        echo  '</div>' ;
    }
    
    public function validate_email_customer_manual_book_message( $input )
    {
        return $input;
    }
    
    // render email to customer message (approve)
    public function render_email_customer_approve_message()
    {
        $value = get_option( 'wbk_email_customer_approve_message', '<p>Your appointment bookin on #appointment_day at #appointment_time has been approved.</p>' );
        $args = array(
            'media_buttons' => false,
            'editor_height' => 300,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_email_customer_approve_message', $args );
        echo  '</div>' ;
    }
    
    // validate email to customer message (approve)
    public function validate_email_customer_approve_message( $input )
    {
        return $input;
    }
    
    public function render_email_customer_approve_copy_status()
    {
        $value = get_option( 'wbk_email_customer_approve_copy_status' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_customer_approve_copy_status" name="wbk_email_customer_approve_copy_status" value="true" >';
        $html .= '<label for="wbk_email_customer_approve_copy_status">' . __( 'Check if you\'d like to send copy of approval notification to administrator', 'wbk' ) . '</a>';
        $html .= '<p class="description">' . __( 'Please, note: copy of notification will be sent if appointment is approved by the approval link.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function validate_email_customer_approve_copy_status( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_customer_approve_copy_status',
                'wbk_email_customer_approve_copy_status_error',
                __( 'Email (on approval) status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    // render customer email subject (on booking)
    public function render_email_customer_book_subject()
    {
        $value = get_option( 'wbk_email_customer_book_subject' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_customer_book_subject" name="wbk_email_customer_book_subject" value="' . $value . '" >';
        $html .= '<p class="description"><a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'wbk' ) . '</a></p>';
        echo  $html ;
    }
    
    // validate email to customer message (on booking)
    public function validate_email_customer_book_subject( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    public function render_email_customer_manual_book_subject()
    {
        $value = get_option( 'wbk_email_customer_manual_book_subject' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_customer_manual_book_subject" name="wbk_email_customer_manual_book_subject" value="' . $value . '" >';
        $html .= '<p class="description"><a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'wbk' ) . '</a></p>';
        echo  $html ;
    }
    
    // validate email to customer message (on booking)
    public function validate_email_customer_manual_book_subject( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render customer email subject (on approve)
    public function render_email_customer_approve_subject()
    {
        $value = get_option( 'wbk_email_customer_approve_subject', __( 'Your booking has been approved', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_customer_approve_subject" name="wbk_email_customer_approve_subject" value="' . $value . '" >';
        $html .= '<p class="description"><a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/#subjectplaceholders">' . __( 'List of available placeholders', 'wbk' ) . '</a></p>';
        echo  $html ;
    }
    
    // validate email to customer message (on approve)
    public function validate_email_customer_approve_subject( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render email to secondary
    public function render_email_secondary_book_status()
    {
        $value = get_option( 'wbk_email_secondary_book_status', '' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_secondary_book_status" name="wbk_email_secondary_book_status" value="true" >';
        $html .= '<label for="wbk_email_secondary_book_status">' . __( 'Check if you\'d like to send an email to a customers from the group', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    // validate email to secondary
    public function validate_email_secondary_book_status( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_secondary_book_status',
                'wbk_email_secondary_book_status_error',
                __( 'Email status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    // render email to secondary message
    public function render_email_secondary_book_message()
    {
        $value = get_option( 'wbk_email_secondary_book_message' );
        $args = array(
            'media_buttons' => false,
            'editor_height' => 300,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_email_secondary_book_message', $args );
        echo  '</div>' ;
    }
    
    // validate email to secondary message
    public function validate_email_secondary_book_message( $input )
    {
        return $input;
    }
    
    // render secondary email subject
    public function render_email_secondary_book_subject()
    {
        $value = get_option( 'wbk_email_secondary_book_subject' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_secondary_book_subject" name="wbk_email_secondary_book_subject" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate email to secondary message
    public function validate_email_secondary_book_subject( $input )
    {
        $input = sanitize_text_field( $input );
        
        if ( !WBK_Validator::checkStringSize( $input, 1, 100 ) ) {
        } else {
            return $input;
        }
    
    }
    
    // render admin email subject
    public function render_email_admin_book_subject()
    {
        $value = get_option( 'wbk_email_admin_book_subject' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_admin_book_subject" name="wbk_email_admin_book_subject" value="' . $value . '" >';
        $html .= '<p class="description"><a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'wbk' ) . '</a></p>';
        echo  $html ;
    }
    
    // validate email to admin message
    public function validate_email_admin_book_subject( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render admin daily subject
    public function render_email_admin_daily_subject()
    {
        $value = get_option( 'wbk_email_admin_daily_subject' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_admin_daily_subject" name="wbk_email_admin_daily_subject" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate email to admin message
    public function validate_email_admin_daily_subject( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render customer daily subject
    public function render_email_customer_daily_subject()
    {
        $value = get_option( 'wbk_email_customer_daily_subject' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_customer_daily_subject" name="wbk_email_customer_daily_subject" value="' . $value . '" >';
        $html .= '<p class="description"><a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'wbk' ) . '</a></p>';
        echo  $html ;
    }
    
    // validate email to customer message
    public function validate_email_customer_daily_subject( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render admin daily time
    public function render_email_admin_daily_time()
    {
        $value = get_option( 'wbk_email_admin_daily_time' );
        $value = sanitize_text_field( $value );
        $html = '<select  id="wbk_email_admin_daily_time" name="wbk_email_admin_daily_time" >';
        $format = WBK_Date_Time_Utils::getTimeFormat();
        date_default_timezone_set( 'UTC' );
        for ( $i = 0 ;  $i < 86400 ;  $i += 600 ) {
            $html .= '<option  ' . selected( $value, $i, false ) . '  value="' . $i . '">' . wp_date( $format, $i, new DateTimeZone( date_default_timezone_get() ) ) . '</option>';
        }
        $html .= '</select>';
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $html .= '<p class="description">' . __( 'Current local time:', 'wbk' ) . ' ' . date( $format ) . '</p>';
        date_default_timezone_set( 'UTC' );
        echo  $html ;
    }
    
    // validate email to admin message
    public function validate_email_admin_daily_time( $input )
    {
        $input = sanitize_text_field( $input );
        
        if ( !WBK_Validator::checkStringSize( $input, 1, 100 ) ) {
            add_settings_error(
                'wbk_email_admin_daily_time',
                'wbk_email_admin_daily_time_error',
                __( 'Administrator daily email time is wrong', 'wbk' ),
                'error'
            );
        } else {
            return $input;
        }
    
    }
    
    public function render_email_reminders_only_for_approved()
    {
        $value = get_option( 'wbk_email_reminders_only_for_approved' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_reminders_only_for_approved" name="wbk_email_reminders_only_for_approved" value="true" >';
        $html .= '<label for="wbk_email_reminders_only_for_approved">' . __( 'Check if you\'d like to send reminders only for approved appointments.', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    public function validate_email_reminders_only_for_approved( $input )
    {
        if ( $input != 'true' && $input != '' ) {
            $input = '';
        }
        return $input;
    }
    
    // render send invoice
    public function render_email_customer_send_invoice()
    {
        $value = get_option( 'wbk_email_customer_send_invoice', 'disabled' );
        $html = '<select id="wbk_email_customer_send_invoice" name="wbk_email_customer_send_invoice" >';
        $html .= '<option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Do not send invoice' ) . '</option>';
        $html .= '<option ' . selected( $value, 'onbooking', false ) . 'value="onbooking">' . __( 'Send invoice on booking' ) . '</option>';
        $html .= '<option ' . selected( $value, 'onapproval', false ) . 'value="onapproval">' . __( 'Send invoice on approval' ) . '</option>';
        $html .= '<option ' . selected( $value, 'onpayment', false ) . 'value="onpayment">' . __( 'Send invoice on payment complete' ) . '</option>';
        $html .= '</select>';
        $html .= '<p class="description">' . __( 'Use this option to control the dispatch of the invoice in parallel with the notification.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate email send invoice
    public function validate_email_customer_send_invoice( $input )
    {
        if ( $input != 'disabled' && $input != 'onbooking' && $input != 'onapproval' && $input != 'onpayment' ) {
            $input = 'disabled';
        }
        return $input;
    }
    
    // render invoice subject //  todo
    public function render_email_customer_invoice_subject()
    {
        $value = get_option( 'wbk_email_customer_invoice_subject', __( 'Invoice', 'wbk' ) );
        $html = '<input type="text" id="wbk_email_customer_invoice_subject" name="wbk_email_customer_invoice_subject" value="' . $value . '" >';
        $html .= '<p class="description"><a rel="noopener" target="_blank" href="https://webba-booking.com/documentation/placeholders/">' . __( 'List of available placeholders', 'wbk' ) . '</a></p>';
        echo  $html ;
    }
    
    // validate invoice subject
    public function validate_email_customer_invoice_subject( $input )
    {
        return $input;
    }
    
    public function render_email_current_invoice_number()
    {
        $value = get_option( 'wbk_email_current_invoice_number', '1' );
        $html = '<input type="text" id="wbk_email_current_invoice_number" name="wbk_email_current_invoice_number" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Set the initial number of invoice. Placeholder for notifications: #invoice_number', 'wbk' ) . '</a></p>';
        $html .= '<p class="description">' . __( 'Each time a customer pay with PayPal or Stripe, the value of this option will be increased by one.', 'wbk' ) . '</a></p>';
        echo  $html ;
    }
    
    public function validate_email_current_invoice_number( $input )
    {
        $input = sanitize_text_field( $input );
        $input = intval( $input );
        if ( !is_numeric( $input ) || $input == 0 ) {
            $input = 1;
        }
        return $input;
    }
    
    public function render_email_send_invoice_copy()
    {
        $value = get_option( 'wbk_email_send_invoice_copy' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_send_invoice_copy" name="wbk_email_send_invoice_copy" value="true" >';
        $html .= '<label for="wbk_email_send_invoice_copy">' . __( 'Check if you\'d like to send copies of invoices to administrator', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    // validate email to customer (on booking)
    public function validate_email_send_invoice_copy( $input )
    {
        if ( $input != 'true' && $input != '' ) {
            $input = '';
        }
        return $input;
    }
    
    public function render_email_override_replyto()
    {
        $value = get_option( 'wbk_email_override_replyto', 'true' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_override_replyto" name="wbk_email_override_replyto" value="true" >';
        $html .= '<label for="wbk_email_override_replyto">' . __( 'Check to override', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    // validate email to customer (on booking)
    public function validate_email_override_replyto( $input )
    {
        if ( $input != 'true' && $input != '' ) {
            $input = '';
        }
        return $input;
    }
    
    // render email to admin
    public function render_email_admin_book_status()
    {
        $value = get_option( 'wbk_email_admin_book_status' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_admin_book_status" name="wbk_email_admin_book_status" value="true" >';
        $html .= '<label for="wbk_email_admin_book_status">' . __( 'Check if you\'d like to send administrator an email', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    // validate email to admin
    public function validate_email_admin_book_status( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_admin_book_status',
                'wbk_email_admin_book_status_error',
                __( 'Email status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    /* START: ICal Generation   */
    public function render_email_admin_book_status_generate_ical()
    {
        $value = get_option( 'wbk_email_admin_book_status_generate_ical' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_admin_book_status_generate_ical" name="wbk_email_admin_book_status_generate_ical" value="true" >';
        $html .= '<label for="wbk_email_admin_book_status_generate_ical">' . __( 'Check if you\'d like to attach iCal file to the notification', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    public function validate_email_admin_book_status_generate_ical( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_admin_book_status_generate_ical',
                'wbk_email_admin_book_status_generate_ical_error',
                __( 'Attach iCal file to the notification status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    public function render_email_customer_book_status_generate_ical()
    {
        $value = get_option( 'wbk_email_customer_book_status_generate_ical' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_customer_book_status_generate_ical" name="wbk_email_customer_book_status_generate_ical" value="true" >';
        $html .= '<label for="wbk_email_customer_book_status_generate_ical">' . __( 'Check if you\'d like to attach iCal file to the notification', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    public function validate_email_customer_book_status_generate_ical( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_customer_book_status_generate_ical',
                'wbk_email_customer_book_status_generate_ical_error',
                __( 'Attach iCal file to the notification status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    /* END: ICal Generation   */
    // render email to admin daily
    public function render_email_admin_daily_status()
    {
        $value = get_option( 'wbk_email_admin_daily_status' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_admin_daily_status" name="wbk_email_admin_daily_status" value="true" >';
        $html .= '<label for="wbk_email_admin_daily_status">' . __( 'Check if you\'d like to send reminders to administrator', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    // validate email to admin
    public function validate_email_admin_daily_status( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_admin_daily_status',
                'wbk_email_admin_daily_status_error',
                __( 'Email status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    public function render_email_customer_arrived_status()
    {
        $value = get_option( 'wbk_email_customer_arrived_status' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_customer_arrived_status" name="wbk_email_customer_arrived_status" value="true" >';
        $html .= '<label for="wbk_email_customer_arrived_status">' . __( 'Check if you\'d like to send notification to customer when status is changed to Arrived', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    public function validate_email_customer_arrived_status( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_customer_arrived_status',
                'wbk_email_customer_arrived_status_error',
                __( 'Email status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    // render email to customer daily
    public function render_email_customer_daily_status()
    {
        $value = get_option( 'wbk_email_customer_daily_status' );
        $html = '<input type="checkbox" ' . checked( 'true', $value, false ) . ' id="wbk_email_customer_daily_status" name="wbk_email_customer_daily_status" value="true" >';
        $html .= '<label for="wbk_email_customer_daily_status">' . __( 'Check if you\'d like to send reminders to customer', 'wbk' ) . '</a>';
        echo  $html ;
    }
    
    // validate email to customer
    public function validate_email_customer_daily_status( $input )
    {
        
        if ( $input != 'true' && $input != '' ) {
            $input = '';
            add_settings_error(
                'wbk_email_customer_daily_status',
                'wbk_email_customer_daily_status_error',
                __( 'Email status updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    public function render_email_reminder_days()
    {
        $value = get_option( 'wbk_email_reminder_days', '1' );
        $html = '<input type="text" id="wbk_email_reminder_days" name="wbk_email_reminder_days" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Number of days', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'Today: 0', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'Tomorrow: 1', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'Day after tomorrow: 2', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'etc', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function validate_email_reminder_days( $input )
    {
        if ( !WBK_Validator::checkInteger( $input, 0, 360 ) ) {
            $input = 1;
        }
        return $input;
    }
    
    // render email to admin message
    public function render_email_admin_book_message()
    {
        $value = get_option( 'wbk_email_admin_book_message' );
        $args = array(
            'media_buttons' => false,
            'editor_height' => 300,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_email_admin_book_message', $args );
        echo  '</div>' ;
    }
    
    // validate email to admin nessage
    public function validate_email_admin_book_message( $input )
    {
        return $input;
    }
    
    // render email to admin  daily message
    public function render_email_admin_daily_message()
    {
        $value = get_option( 'wbk_email_admin_daily_message' );
        $args = array(
            'media_buttons' => false,
            'editor_height' => 300,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_email_admin_daily_message', $args );
        echo  '</div>' ;
    }
    
    // validate email to admin daily nessage
    public function validate_email_admin_daily_message( $input )
    {
        return $input;
    }
    
    // render email to customer  daily message
    public function render_email_customer_daily_message()
    {
        $value = get_option( 'wbk_email_customer_daily_message' );
        $args = array(
            'media_buttons' => false,
            'editor_height' => 300,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_email_customer_daily_message', $args );
        echo  '</div>' ;
    }
    
    // validate email to customer daily nessage
    public function validate_email_customer_daily_message( $input )
    {
        return $input;
    }
    
    // render from email
    public function render_from_email()
    {
        $value = get_option( 'wbk_from_email' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_from_email" name="wbk_from_email" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate from email
    public function validate_from_email( $input )
    {
        return $input;
    }
    
    // render super admin email
    public function render_super_admin_email()
    {
        $value = get_option( 'wbk_super_admin_email' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_super_admin_email" name="wbk_super_admin_email" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate super admin email
    public function validate_super_admin_email( $input )
    {
        $input = trim( $input );
        return $input;
    }
    
    // render email landing
    public function render_email_landing()
    {
        $value = get_option( 'wbk_email_landing', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_landing" name="wbk_email_landing" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'This page will be used as a landing for payment or cancellation. Page should contain [webba_email_landing] or [webba_booking] shortcode.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate email landing
    public function validate_email_landing( $input )
    {
        $input = sanitize_text_field( trim( $input ) );
        return $input;
    }
    
    // render name from
    public function render_from_name()
    {
        $value = get_option( 'wbk_from_name' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_from_name" name="wbk_from_name" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate from name
    public function validate_from_name( $input )
    {
        $input = sanitize_text_field( $input );
        
        if ( !WBK_Validator::checkStringSize( $input, 1, 100 ) ) {
            add_settings_error(
                'wbk_from_name',
                'wbk_from_name_error',
                __( '"From: name" is wrong', 'wbk' ),
                'error'
            );
        } else {
            return $input;
        }
    
    }
    
    // render mode
    public function render_mode()
    {
        $value = get_option( 'wbk_mode', 'extended' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_mode" name="wbk_mode">
				    <option ' . selected( $value, 'extended', false ) . ' value="extended">' . __( 'Extended', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'simple', false ) . ' value="simple">' . __( 'Basic', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    // validate mode
    public function validate_mode( $input )
    {
        return $input;
    }
    
    public function render_show_suitable_hours()
    {
        $value = get_option( 'wbk_show_suitable_hours', 'yes' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_show_suitable_hours" name="wbk_show_suitable_hours">
				    <option ' . selected( $value, 'yes', false ) . ' value="yes">' . __( 'Yes', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'no', false ) . ' value="no">' . __( 'No', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    public function validate_show_suitable_hours( $input )
    {
        return $input;
    }
    
    public function render_email_customer_book_multiple_mode()
    {
        $value = get_option( 'wbk_email_customer_book_multiple_mode', 'foreach' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_email_customer_book_multiple_mode" name="wbk_email_customer_book_multiple_mode">
				    <option ' . selected( $value, 'foreach', false ) . ' value="foreach">' . __( 'Send Email for each booked time slot', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'one', false ) . ' value="one">' . __( 'Send one Email for all booked time slots', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    public function validate_email_customer_book_multiple_mode( $input )
    {
        return $input;
    }
    
    public function render_email_admin_book_multiple_mode()
    {
        $value = get_option( 'wbk_email_admin_book_multiple_mode', 'foreach' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_email_admin_book_multiple_mode" name="wbk_email_admin_book_multiple_mode">
				    <option ' . selected( $value, 'foreach', false ) . ' value="foreach">' . __( 'Send Email for each booked time slot', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'one', false ) . ' value="one">' . __( 'Send one Email for all booked time slots', 'wbk' ) . '</option>
   				 </select>';
        $html .= '<p class="description">' . __( 'IMPORTANT NOTICE: using "Send one Email for all booked time slots" with multi-service booking mode is recommended only if all services has the same e-mail.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function validate_email_admin_book_multiple_mode( $input )
    {
        return $input;
    }
    
    // render skip time slot selection
    public function render_skip_timeslot_select()
    {
        $value = get_option( 'wbk_skip_timeslot_select', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_skip_timeslot_select" name="wbk_skip_timeslot_select">
				    <option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Disabled', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'enabled', false ) . ' value="enabled">' . __( 'Enabled', 'wbk' ) . '</option>

   				 </select>';
        $html .= '<p class="description">' . __( 'Skip time slot selection if only one time slot is available.', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'IMPORTANT: enable this option only with Basic mode and multiple booking disabled.', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'Make sure your service schedule includes ONLY ONE time slot available on a day.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate skip time slot selection
    public function validate_skip_timeslot_select( $input )
    {
        if ( $input != 'disabled' && $input != 'enabled' ) {
            $input = 'disabled';
        }
        return $input;
    }
    
    // render places selection mode
    public function render_places_selection_mode()
    {
        $value = get_option( 'wbk_places_selection_mode', 'normal' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_places_selection_mode" name="wbk_places_selection_mode">
				    <option ' . selected( $value, 'normal', false ) . ' value="normal">' . __( 'Let users select count', 'wbk' ) . '</option>
				    <option ' . selected( $value, '1', false ) . ' value="1">' . __( 'Allow select only one place', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'max', false ) . ' value="max">' . __( 'Allow select only maximum places', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    // validate places selection mode
    public function validate_places_selection_mode( $input )
    {
        if ( $input != 'normal' && $input != '1' && $input != 'max' ) {
            $input = 'normal';
        }
        return $input;
    }
    
    // render time hole optimization
    public function render_time_hole_optimization()
    {
        $value = get_option( 'wbk_time_hole_optimization', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_time_hole_optimization" name="wbk_time_hole_optimization">
				    <option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Disabled', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'enabled', false ) . ' value="enabled">' . __( 'Enabled', 'wbk' ) . '</option>
   				 </select>';
        $html .= '<p class="description">' . __( 'IMPORTANT: if this option is enabled set the "Allow unlock manually" (Appointments tab) to "Disallow".', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate time hole optimization
    public function validate_time_hole_optimization( $input )
    {
        if ( $input != 'enabled' && $input != 'disabled' ) {
            $input = 'disabled';
        }
        return $input;
    }
    
    // render show service description
    public function render_show_service_description()
    {
        $value = get_option( 'wbk_show_service_description', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_show_service_description" name="wbk_show_service_description">
				    <option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Disabled', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'enabled', false ) . ' value="enabled">' . __( 'Enabled', 'wbk' ) . '</option>
   				 </select>';
        $html .= '<p class="description">' . __( 'Enable this option to show service description below the service select on the frontend.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate time hole optimization
    public function validate_show_service_description( $input )
    {
        if ( $input != 'enabled' && $input != 'disabled' ) {
            $input = 'disabled';
        }
        return $input;
    }
    
    public function render_range_selection()
    {
        $value = get_option( 'wbk_range_selection', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_range_selection" name="wbk_range_selection">
				    <option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Disabled', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'enabled', false ) . ' value="enabled">' . __( 'Enabled', 'wbk' ) . '</option>
   				 </select>';
        $html .= '<p class="description">' . __( 'Enable this option to allow range selection in multiple booking mode', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate time hole optimization
    public function validate_range_selection( $input )
    {
        if ( $input != 'enabled' && $input != 'disabled' ) {
            $input = 'disabled';
        }
        return $input;
    }
    
    // render date input field type
    public function render_date_input()
    {
        $value = get_option( 'wbk_date_input', 'popup' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_date_input" name="wbk_date_input">
				    <option ' . selected( $value, 'popup', false ) . ' value="popup">' . __( 'Popup', 'wbk' ) . '</option>
					<option ' . selected( $value, 'classic', false ) . ' value="classic">' . __( 'Classic', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'dropdown', false ) . ' value="dropdown">' . __( 'Dropdown', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    // validate date input field type
    public function validate_date_input( $input )
    {
        if ( $input != 'popup' && $input != 'dropdown' && $input != 'classic' ) {
            $input = 'popup';
        }
        return $input;
    }
    
    // render show locked as booked
    public function render_show_locked_as_booked()
    {
        $value = get_option( 'wbk_show_locked_as_booked', 'no' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_show_locked_as_booked" name="wbk_show_locked_as_booked">
				    <option ' . selected( $value, 'yes', false ) . ' value="yes">' . __( 'Yes', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'no', false ) . ' value="no">' . __( 'No', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    // validate show locked as booked
    public function validate_show_locked_as_booked( $input )
    {
        if ( $input != 'yes' && $input != 'no' ) {
            $input = 'no';
        }
        return $input;
    }
    
    // render load stripe js
    public function render_load_stripe_js()
    {
        $value = get_option( 'wbk_load_stripe_js', 'yes' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_load_stripe_js" name="wbk_load_stripe_js">
				    <option ' . selected( $value, 'yes', false ) . ' value="yes">' . __( 'Yes', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'no', false ) . ' value="no">' . __( 'No', 'wbk' ) . '</option>
					<option ' . selected( $value, 'shortcode', false ) . ' value="shortcode">' . __( 'Only on the booking page (not recommended)', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    // validate load stripe js
    public function validate_load_stripe_js( $input )
    {
        if ( $input != 'yes' && $input != 'no' && $input != 'shortcode' ) {
            $input = 'yes';
        }
        return $input;
    }
    
    public function render_stripe_hide_postal()
    {
        $value = get_option( 'wbk_stripe_hide_postal', 'false' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_stripe_hide_postal" name="wbk_stripe_hide_postal">
		       <option ' . selected( $value, 'true', false ) . ' value="true">' . __( 'Yes', 'wbk' ) . '</option>
		       <option ' . selected( $value, 'false', false ) . ' value="false">' . __( 'No', 'wbk' ) . '</option>
		       </select>';
        echo  $html ;
    }
    
    // validate load stripe js
    public function validate_stripe_hide_postal( $input )
    {
        if ( $input != 'true' && $input != 'false' ) {
            $input = 'false';
        }
        return $input;
    }
    
    public function render_load_stripe_api()
    {
        $value = get_option( 'wbk_load_stripe_api', 'yes' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_load_stripe_api" name="wbk_load_stripe_api">
				    <option ' . selected( $value, 'yes', false ) . ' value="yes">' . __( 'Yes, load version 7.26.0', 'wbk' ) . '</option>
					<option ' . selected( $value, 'old', false ) . ' value="old">' . __( 'Yes, load version 6.21.1 (not recommended)', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'no', false ) . ' value="no">' . __( 'No', 'wbk' ) . '</option>
   				 </select>';
        $html .= '<p class="description">' . __( 'Set \'no\' or 6.21.1 only if there is a conflict with other plugin that uses Stripe.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate load stripe js
    public function validate_load_stripe_api( $input )
    {
        if ( $input != 'yes' && $input != 'no' && $input != 'old' ) {
            $input = 'yes';
        }
        return $input;
    }
    
    // render stripe card input mode
    public function render_stripe_card_input_mode()
    {
        $value = get_option( 'wbk_stripe_card_input_mode', 'no' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_stripe_card_input_mode" name="wbk_stripe_card_input_mode">
				    <option ' . selected( $value, 'yes', false ) . ' value="yes">' . __( 'Yes', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'no', false ) . ' value="no">' . __( 'No', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    // validate  stripe card input mode
    public function validate_stripe_card_input_mode( $input )
    {
        if ( $input != 'yes' && $input != 'no' ) {
            $input = 'no';
        }
        return $input;
    }
    
    public function render_allow_using_attachments()
    {
        $value = get_option( 'wbk_allow_attachemnt', 'no' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_allow_attachemnt" name="wbk_allow_attachemnt">
	                <option ' . selected( $value, 'yes', false ) . ' value="yes">' . __( 'Yes', 'wbk' ) . '</option>
	                <option ' . selected( $value, 'no', false ) . ' value="no">' . __( 'No', 'wbk' ) . '</option>
	             </select>';
        echo  $html ;
    }
    
    public function validate_allow_using_attachments( $input )
    {
        if ( $input != 'yes' && $input != 'no' ) {
            $input = 'no';
        }
        return $input;
    }
    
    public function render_delete_attachemnt()
    {
        $value = get_option( 'wbk_delete_attachemnt', 'no' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_delete_attachemnt" name="wbk_delete_attachemnt">
		            <option ' . selected( $value, 'yes', false ) . ' value="yes">' . __( 'Yes', 'wbk' ) . '</option>
		            <option ' . selected( $value, 'no', false ) . ' value="no">' . __( 'No', 'wbk' ) . '</option>
		         </select>';
        echo  $html ;
    }
    
    public function validate_delete_attachemnt( $input )
    {
        return $input;
    }
    
    public function render_allow_service_in_url()
    {
        $value = get_option( 'wbk_allow_service_in_url', 'no' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_allow_service_in_url" name="wbk_allow_service_in_url">
	                <option ' . selected( $value, 'yes', false ) . ' value="yes">' . __( 'Yes', 'wbk' ) . '</option>
	                <option ' . selected( $value, 'no', false ) . ' value="no">' . __( 'No', 'wbk' ) . '</option>
	             </select>';
        echo  $html ;
    }
    
    public function validate_allow_service_in_url( $input )
    {
        if ( $input != 'yes' && $input != 'no' ) {
            $input = 'no';
        }
        return $input;
    }
    
    public function render_order_service_by()
    {
        $value = get_option( 'wbk_order_service_by', 'a-z' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_order_service_by" name="wbk_order_service_by">
	                <option ' . selected( $value, 'a-z', false ) . ' value="a-z">' . __( 'A-Z', 'wbk' ) . '</option>
	                <option ' . selected( $value, 'priority', false ) . ' value="priority">' . __( 'Priority (descending)', 'wbk' ) . '</option>
					<option ' . selected( $value, 'priority_a', false ) . ' value="priority_a">' . __( 'Priority (ascending)', 'wbk' ) . '</option>
	             </select>';
        echo  $html ;
    }
    
    public function validate_order_service_by( $input )
    {
        if ( $input != 'a-z' && $input != 'priority' && $input != 'priority_a' ) {
            $input = 'a-z';
        }
        return $input;
    }
    
    public function render_night_hours()
    {
        $value = get_option( 'wbk_night_hours', '0' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_night_hours" name="wbk_night_hours" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'The number of hours after midnight', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function validate_night_hours( $input )
    {
        if ( !WBK_Validator::checkInteger( $input, 0, 12 ) ) {
            $input = 0;
        }
        return $input;
    }
    
    public function render_multi_serv_date_limit()
    {
        $value = get_option( 'wbk_multi_serv_date_limit', '360' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_multi_serv_date_limit" name="wbk_multi_serv_date_limit" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'The number of days from today that available for booking', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'IMPORTANT: this option is for multi-service booking only', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function validate_multi_serv_date_limit( $input )
    {
        if ( !WBK_Validator::checkInteger( $input, 7, 3600 ) ) {
            $input = 360;
        }
        return $input;
    }
    
    public function render_avaiability_popup_calendar()
    {
        $value = get_option( 'wbk_avaiability_popup_calendar', '360' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_avaiability_popup_calendar" name="wbk_avaiability_popup_calendar" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'The number of days to check availability for in the popup calendar', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'IMPORTANT: this option is for regular booking only', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function validate_avaiability_popup_calendar( $input )
    {
        if ( !WBK_Validator::checkInteger( $input, 7, 3600 ) ) {
            $input = 360;
        }
        return $input;
    }
    
    public function render_attachment_file_types()
    {
        $value = get_option( 'wbk_attachment_file_types', 'image/*' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_attachment_file_types" name="wbk_attachment_file_types" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Example: file_extension. A file extension starting with the STOP character, e.g: .gif, .jpg, .png, .doc', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'Example: audio/* all sound files are accepted.', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'Example: video/* all video files are accepted.', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'Example: image/* all image files are accepted.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function render_disallow_after()
    {
        $value = get_option( 'wbk_disallow_after', '0' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_disallow_after" name="wbk_disallow_after" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Set 0 to not disable time slots', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function validate_disallow_after( $input )
    {
        if ( !WBK_Validator::checkInteger( $input, 0, 100000 ) ) {
            $input = 0;
        }
        return $input;
    }
    
    public function render_gdrp()
    {
        $value = get_option( 'wbk_gdrp', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_gdrp" name="wbk_gdrp">
                    <option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Disabled', 'wbk' ) . '</option>
                    <option ' . selected( $value, 'enabled', false ) . ' value="enabled">' . __( 'Enabled', 'wbk' ) . '</option>

                 </select>';
        $html .= '<p class="description">' . __( 'Enable this option to clean up database after after the appointment time has passed', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function validate_gdrp( $input )
    {
        if ( $input != 'disabled' && $input != 'enabled' ) {
            $input = 'disabled';
        }
        return $input;
    }
    
    public function render_allow_ongoing_time_slot()
    {
        $value = get_option( 'wbk_allow_ongoing_time_slot', 'disallow' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_allow_ongoing_time_slot" name="wbk_allow_ongoing_time_slot">
				    <option ' . selected( $value, 'allow', false ) . ' value="allow">' . __( 'Allow', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'disallow', false ) . ' value="disallow">' . __( 'Disallow', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    public function render_days_in_extended_mode()
    {
        $value = get_option( 'wbk_days_in_extended_mode', 'default' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_days_in_extended_mode" name="wbk_days_in_extended_mode">
					<option ' . selected( $value, '1', false ) . ' value="1">' . __( '1', 'wbk' ) . '</option>
					<option ' . selected( $value, '2', false ) . ' value="2">' . __( '2', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'default', false ) . ' value="default">' . __( '3', 'wbk' ) . '</option>
					<option ' . selected( $value, '4', false ) . ' value="4">' . __( '4', 'wbk' ) . '</option>
					<option ' . selected( $value, '5', false ) . ' value="5">' . __( '5', 'wbk' ) . '</option>
					<option ' . selected( $value, '6', false ) . ' value="6">' . __( '6', 'wbk' ) . '</option>
					<option ' . selected( $value, '7', false ) . ' value="7">' . __( '7', 'wbk' ) . '</option>
					<option ' . selected( $value, '8', false ) . ' value="8">' . __( '8', 'wbk' ) . '</option>
					<option ' . selected( $value, '9', false ) . ' value="9">' . __( '9', 'wbk' ) . '</option>
					<option ' . selected( $value, '10', false ) . ' value="10">' . __( '10', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'lowlimit', false ) . ' value="lowlimit">' . __( 'Use Low limit value of service', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'uplimit', false ) . ' value="uplimit">' . __( 'Use Up limit value of services', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    public function validate_days_in_extended_mode( $input )
    {
        $input = trim( $input );
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    public function render_show_details_prev_booking()
    {
        $value = get_option( 'wbk_show_details_prev_booking', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_show_details_prev_booking" name="wbk_show_details_prev_booking">
                    <option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Disabled', 'wbk' ) . '</option>
                    <option ' . selected( $value, 'enabled', false ) . ' value="enabled">' . __( 'Enabled', 'wbk' ) . '</option>
                 </select>';
        $html .= '<p class="description">' . __( 'This option applies to services with a multiple places per time slot', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function validate_show_details_prev_booking( $input )
    {
        if ( $input != 'disabled' && $input != 'enabled' ) {
            $input = 'disabled';
        }
        return $input;
    }
    
    public function render_availability_calculation_method()
    {
        $value = get_option( 'wbk_availability_calculation_method', 'sum' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_availability_calculation_method" name="wbk_availability_calculation_method">
					<option ' . selected( $value, 'sum', false ) . ' value="sum">' . __( 'Sum', 'wbk' ) . '</option>
					<option ' . selected( $value, 'highest', false ) . ' value="highest">' . __( 'Highest value', 'wbk' ) . '</option>
				 </select>';
        echo  $html ;
    }
    
    public function validate_availability_calculation_method( $input )
    {
        if ( $input != 'sum' && $input != 'highest' ) {
            $input = 'sum';
        }
        return $input;
    }
    
    // validate lock appointments auto lock allow unlock
    public function validate_allow_ongoing_time_slot( $input )
    {
        $input = trim( $input );
        $input = sanitize_text_field( $input );
        if ( $input != 'allow' && $input != 'disallow' ) {
            $input = 'allow';
        }
        return $input;
    }
    
    public function validate_attachment_file_types( $input )
    {
        return sanitize_text_field( $input );
    }
    
    // render multi booking
    public function render_multi_booking()
    {
        $value = get_option( 'wbk_multi_booking', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_multi_booking" name="wbk_multi_booking">
				    <option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Disabled', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'enabled', false ) . ' value="enabled">' . __( 'Enabled (top bar checkout button)', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'enabled_slot', false ) . ' value="enabled_slot">' . __( 'Enabled (time slot checkout button)', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    // validate multi booking
    public function validate_multi_booking( $input )
    {
        if ( $input != 'disabled' && $input != 'enabled' && $input != 'enabled_slot' ) {
            $input = 'disabled';
        }
        return $input;
    }
    
    // render timeslot time string
    public function render_timeslot_time_string()
    {
        $value = get_option( 'wbk_timeslot_time_string', 'start' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_timeslot_time_string" name="wbk_timeslot_time_string">
				    <option ' . selected( $value, 'start', false ) . ' value="start">' . __( 'Start', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'start_end', false ) . ' value="start_end">' . __( 'Start', 'wbk' ) . ' - ' . __( 'end', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    // validate timeslot time string
    public function validate_timeslot_time_string( $input )
    {
        return $input;
    }
    
    // render timeslot format
    public function render_timeslot_format()
    {
        $value = get_option( 'wbk_timeslot_format', 'detailed' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_timeslot_format" name="wbk_timeslot_format">
				    <option ' . selected( $value, 'detailed', false ) . ' value="detailed">' . __( 'Show details and BOOK button', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'time_only', false ) . ' value="time_only">' . __( 'Show time button only', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    // validate timeslot format
    public function validate_timeslot_format( $input )
    {
        return $input;
    }
    
    // render show local time
    public function render_show_local_time()
    {
        $value = get_option( 'wbk_show_local_time', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_show_local_time" name="wbk_show_local_time">
				    <option ' . selected( $value, 'detailed', false ) . ' value="disabled">' . __( 'Disabled', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'enabled', false ) . ' value="enabled">' . __( 'Enabled', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'enabled_only', false ) . ' value="enabled_only">' . __( 'Enabled (show only local time)', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    // validate show local time
    public function validate_show_local_time( $input )
    {
        return $input;
    }
    
    // render csv delimiter
    public function render_csv_export_delimiter()
    {
        $value = get_option( 'wbk_csv_delimiter', 'comma' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_csv_delimiter" name="wbk_csv_delimiter">
				    <option ' . selected( $value, 'comma', false ) . ' value="comma">' . __( 'Comma', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'semicolon', false ) . ' value="semicolon">' . __( 'Semicolon', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    // validate csv delimiter
    public function validate_csv_export_delimiter( $input )
    {
        return $input;
    }
    
    // render service label
    public function render_service_label()
    {
        $value = get_option( 'wbk_service_label', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_service_label" name="wbk_service_label" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Service frontend label', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate service label
    public function validate_service_label( $input )
    {
        return sanitize_text_field( $input );
    }
    
    public function render_category_label()
    {
        $value = get_option( 'wbk_category_label', __( 'Select category', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_category_label" name="wbk_category_label" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Category frontend label', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function validate_category_label( $input )
    {
        return sanitize_text_field( $input );
    }
    
    // render date extended label
    public function render_date_extended_label()
    {
        $value = get_option( 'wbk_date_extended_label', '' );
        $value = $value;
        $html = '<input type="text" id="wbk_date_extended_label" name="wbk_date_extended_label" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Date frontend label', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate date extended label
    public function validate_date_extended_label( $input )
    {
        return sanitize_text_field( $input );
    }
    
    // render date basic label
    public function render_date_basic_label()
    {
        $value = get_option( 'wbk_date_basic_label', '' );
        $value = $value;
        $html = '<input type="text" id="wbk_date_basic_label" name="wbk_date_basic_label" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Date frontend label', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate date basic label
    public function validate_date_basic_label( $input )
    {
        return sanitize_text_field( $input );
    }
    
    // render hours label
    public function render_hours_label()
    {
        $value = get_option( 'wbk_hours_label', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_hours_label" name="wbk_hours_label" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Hours frontend label', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate hours label
    public function validate_hours_label( $input )
    {
        return sanitize_text_field( $input );
    }
    
    // render slots label
    public function render_slots_label()
    {
        $value = get_option( 'wbk_slots_label', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_slots_label" name="wbk_slots_label" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Time slots frontend label', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate slots label
    public function validate_slots_label( $input )
    {
        return sanitize_text_field( $input );
    }
    
    // render form label
    public function render_form_label()
    {
        $value = get_option( 'wbk_form_label', '' );
        $html = '<input type="text" id="wbk_form_label" name="wbk_form_label" value="' . html_entity_decode( $value ) . '" >';
        $html .= '<p class="description">' . __( 'Message before the booking form', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'Allowed HTML tags', 'wbk' ) . ': strong, em, b, i, br, p, a</p>';
        $html .= '<p class="description">' . __( 'Available placeholders', 'wbk' ) . ': #service (service name), #date (appointment date), #time (appointment time), #dt (appointment date and time).' . '</p>';
        $html .= '<p class="description">#drt (appointment date and time with new line), #dre (appointment date and time range with new line), #price (service price for a single time slot), #total_amount (price for selected time slot(s) + tax), #selected_count (total count of time slots), #local (local time), #dlocal (local date)</p>';
        $html .= '<p class="description">#sd (single day in multiple selection mode), #range (time range in multiple selection mode)</p>';
        $html .= '<p class="description">' . __( 'For the multi-service mode use [split] placeholder to split the string in 2 parts: static and repeatable', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate form label
    public function validate_form_label( $input )
    {
        return $input;
    }
    
    // render local time format
    public function render_local_time_format()
    {
        $value = get_option( 'wbk_local_time_format', '' );
        $value = htmlspecialchars( $value );
        $html = '<input type="text" id="wbk_local_time_format" name="wbk_local_time_format" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Available placeholders', 'wbk' ) . ':  #ts (start local time), #te (end local time), #ds (local date).' . '</p>';
        echo  $html ;
    }
    
    // validate local time format
    public function validate_local_time_format( $input )
    {
        return $input;
    }
    
    public function render_server_time_format()
    {
        $value = get_option( 'wbk_server_time_format', '' );
        $value = htmlspecialchars( $value );
        $html = '<input type="text" id="wbk_server_time_format" name="wbk_server_time_format" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Allowed HTML tags', 'wbk' ) . ': strong, em, b, i, br, p</p>';
        $html .= '<p class="description">' . __( 'Available placeholders', 'wbk' ) . ':  #ts (start local time), #te (end local time), #ds (local date).' . '</p>';
        echo  $html ;
    }
    
    // validate local time format
    public function validate_server_time_format( $input )
    {
        return $input;
    }
    
    public function render_server_time_format2()
    {
        $value = get_option( 'wbk_server_time_format2', '' );
        $value = htmlspecialchars( $value );
        $html = '<input type="text" id="wbk_server_time_format2" name="wbk_server_time_format2" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Allowed HTML tags', 'wbk' ) . ': strong, em, b, i, br, p</p>';
        $html .= '<p class="description">' . __( 'Available placeholders', 'wbk' ) . ':  #ts (start local time), #te (end local time), #ds (local date).' . '</p>';
        echo  $html ;
    }
    
    // validate local time format
    public function validate_server_time_format2( $input )
    {
        return $input;
    }
    
    public function render_time_slot_available_text()
    {
        $value = get_option( 'wbk_time_slot_available_text', __( 'available', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_time_slot_available_text" name="wbk_time_slot_available_text" value="' . $value . '" >';
        echo  $html ;
    }
    
    public function validate_time_slot_available_text( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render appointment information
    public function render_appointment_information()
    {
        $value = get_option( 'wbk_appointment_information', __( 'Appointment on #dt', 'wbk' ) );
        $value = htmlspecialchars( $value );
        $html = '<input type="text" id="wbk_appointment_information" name="wbk_appointment_information" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Appointment information on payment and cancellation forms.', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'Displayed when customers pay for booking or cancel the booking with the link sent in e-mail notification.', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'Allowed HTML tags', 'wbk' ) . ': strong, em, b, i, br, p, a</p>';
        $html .= '<p class="description">' . __( 'Available placeholders', 'wbk' ) . ': #name (customer name), #id (appointment id), #service (service name), #date (appointment date), #time (appointment time), #dt (appointment date and time), #start_end (appointment time in start-end fornmat).' . '</p>';
        echo  $html ;
    }
    
    // validate appointment information
    public function validate_appointment_information( $input )
    {
        return $input;
    }
    
    // render booking coldnt be canceed (paid appointment)
    public function render_booking_couldnt_be_canceled()
    {
        $value = get_option( 'wbk_booking_couldnt_be_canceled', __( 'Paid booking can\'t be canceled.', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_booking_couldnt_be_canceled" name="wbk_booking_couldnt_be_canceled" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Displayed when customer tries to cancel paid booking.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate booking coldnt be canceed
    public function validate_booking_couldnt_be_canceled( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render booking coldnt be canceed (buffer)
    public function render_booking_couldnt_be_canceled2()
    {
        $value = get_option( 'wbk_booking_couldnt_be_canceled2', __( 'Sorry, you can not cancel because you have exceeded the time allowed to do so.', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_booking_couldnt_be_canceled2" name="wbk_booking_couldnt_be_canceled2" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Displayed when a customer tries to cancel an appointment/reservation within less than the time allowed to do so.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate booking coldnt be canceed
    public function validate_booking_couldnt_be_canceled2( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render cancel email label
    public function render_booking_cancel_email_label()
    {
        $value = get_option( 'wbk_booking_cancel_email_label', __( 'Please, enter your email to confirm cancellation', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_booking_cancel_email_label" name="wbk_booking_cancel_email_label" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate cancel email label
    public function validate_booking_cancel_email_label( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render booking canceled
    public function render_booking_canceled_message()
    {
        $value = get_option( 'wbk_booking_canceled_message', __( 'Your appointment booking has been canceled.', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_booking_canceled_message" name="wbk_booking_canceled_message" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate booking canceled
    public function validate_booking_canceled_message( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    public function render_booking_canceled_message_admin()
    {
        $value = get_option( 'wbk_booking_canceled_message_admin', __( 'Appointment canceled #count', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_booking_canceled_message_admin" name="wbk_booking_canceled_message_admin" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Available placeholders:', 'wbk' ) . ' #count</p>';
        echo  $html ;
    }
    
    public function validate_booking_canceled_message_admin( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    public function render_booking_approved_message_admin()
    {
        $value = get_option( 'wbk_booking_approved_message_admin', __( 'Appointment approved #count', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_booking_approved_message_admin" name="wbk_booking_approved_message_admin" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Available placeholders:', 'wbk' ) . ' #count</p>';
        echo  $html ;
    }
    
    public function validate_booking_approved_message_admin( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render cancel booking error message
    public function render_booking_cancel_error_message()
    {
        $value = get_option( 'wbk_booking_cancel_error_message', __( 'Unable to cancel booking, please check the email you\'ve entered.', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_booking_cancel_error_message" name="wbk_booking_cancel_error_message" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate cancel booking error message
    public function validate_booking_cancel_error_message( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render payment link text
    public function render_email_landing_text()
    {
        $value = get_option( 'wbk_email_landing_text', __( 'Click here to pay for your booking.', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_landing_text" name="wbk_email_landing_text" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate payment link text
    public function validate_email_landing_text( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render cancel link text (customer)
    public function render_email_landing_text_cancel()
    {
        $value = get_option( 'wbk_email_landing_text_cancel', __( 'Click here to cancel your booking.', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_landing_text_cancel" name="wbk_email_landing_text_cancel" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate cancel link text (customer)
    public function validate_email_landing_text_cancel( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render cancel link text (admin)
    public function render_email_landing_text_cancel_admin()
    {
        $value = get_option( 'wbk_email_landing_text_cancel_admin', __( 'Click here to cancel this booking.', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_landing_text_cancel_admin" name="wbk_email_landing_text_cancel_admin" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate cancel link text (admin)
    public function validate_email_landing_text_cancel_admin( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render cancel link text (admin)
    public function render_email_landing_text_approve_admin()
    {
        $value = get_option( 'wbk_email_landing_text_approve_admin', __( 'Click here to approve this booking.', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_landing_text_approve_admin" name="wbk_email_landing_text_approve_admin" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate cancel link text (admin)
    public function validate_email_landing_text_approve_admin( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render message for adding event success
    public function render_add_gg_button_text()
    {
        $value = get_option( 'wbk_add_gg_button_text', __( 'Add to my Google Calendar', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_add_gg_button_text" name="wbk_add_gg_button_text" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate message for adding event success
    public function validate_add_gg_button_text( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render link for adding to google calendar
    public function render_email_landing_text_gg_event_add()
    {
        $value = get_option( 'wbk_email_landing_text_gg_event_add', __( 'Click here to add this event to your Google Calendar.', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_landing_text_gg_event_add" name="wbk_email_landing_text_gg_event_add" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate link for adding to google calendar
    public function validate_email_landing_text_gg_event_add( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render message for adding event cancellation
    public function render_gg_calendar_add_event_canceled()
    {
        $value = get_option( 'wbk_gg_calendar_add_event_canceled', __( 'Appointment data not added to Google Calendar.', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_gg_calendar_add_event_canceled" name="wbk_gg_calendar_add_event_canceled" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate message for adding event cancellation
    public function validate_gg_calendar_add_event_canceled( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render message for adding event success
    public function render_gg_calendar_add_event_success()
    {
        $value = get_option( 'wbk_gg_calendar_add_event_success', __( 'Appointment data added to Google Calendar.', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_gg_calendar_add_event_success" name="wbk_gg_calendar_add_event_success" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate message for adding event success
    public function validate_gg_calendar_add_event_success( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render invalid token
    public function render_email_landing_text_invalid_token()
    {
        $value = get_option( 'wbk_email_landing_text_invalid_token', __( 'Appointment booking doesn\'t exist.', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_landing_text_invalid_token" name="wbk_email_landing_text_invalid_token" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate invalid token
    public function validate_email_landing_text_invalid_token( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render gg celendar event title
    public function render_gg_calendar_event_title()
    {
        $value = get_option( 'wbk_gg_calendar_event_title', '#customer_name' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_gg_calendar_event_title" name="wbk_gg_calendar_event_title" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Available placeholders:', 'wbk' ) . ' #customer_name, #customer_phone, #customer_email, #customer_comment, #items_count, #appointment_id, #customer_custom, #total_amount, #service_name, #status' . '</p>';
        $html .= '<p class="description">' . __( 'Placeholder for custom field:', 'wbk' ) . ' #field_ + custom field id. Example: #field_custom-field-1' . '</p>';
        echo  $html ;
    }
    
    // validate gg calendar event title
    public function validate_gg_calendar_event_title( $input )
    {
        $input = sanitize_text_field( trim( $input ) );
        return $input;
    }
    
    // render gg celendar event description
    public function render_gg_calendar_event_description()
    {
        $value = get_option( 'wbk_gg_calendar_event_description', '#customer_name #customer_phone' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_gg_calendar_event_description" name="wbk_gg_calendar_event_description" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Available placeholders:', 'wbk' ) . ' ' . '#customer_name, #customer_phone, #customer_email, #customer_comment, #items_count, #appointment_id, #customer_custom, #total_amount, #service_name, #status' . '</p>';
        $html .= '<p class="description">' . __( 'Placeholder for custom field:', 'wbk' ) . ' #field_ + custom field id. Example: #field_custom-field-1' . '</p>';
        $html .= '<p class="description">' . __( 'Add {n} for new line (only for Google Calendar events)', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate gg calendar event description
    public function validate_gg_calendar_event_description( $input )
    {
        $input = sanitize_text_field( trim( $input ) );
        return $input;
    }
    
    // render gg celendar event title
    public function render_gg_calendar_event_title_customer()
    {
        $value = get_option( 'wbk_gg_calendar_event_title_customer', '#service_name' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_gg_calendar_event_title_customer" name="wbk_gg_calendar_event_title_customer" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Available placeholders:', 'wbk' ) . '#customer_name, #customer_phone, #customer_email, #customer_comment, #items_count, #appointment_id, #customer_custom, #total_amount, #service_name
				 ' . '</p>';
        $html .= '<p class="description">' . __( 'Placeholder for custom field:', 'wbk' ) . ' #field_ + custom field id. Example: #field_custom-field-1' . '</p>';
        echo  $html ;
    }
    
    // validate gg calendar event title
    public function validate_gg_calendar_event_title_customer( $input )
    {
        $input = sanitize_text_field( trim( $input ) );
        return $input;
    }
    
    // render gg celendar event description
    public function render_gg_calendar_event_description_customer()
    {
        $value = get_option( 'wbk_gg_calendar_event_description_customer', 'Your appointment id is #appointment_id' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_gg_calendar_event_description_customer" name="wbk_gg_calendar_event_description_customer" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Available placeholders:', 'wbk' ) . '#customer_name, #customer_phone, #customer_email, #customer_comment, #items_count, #appointment_id, #customer_custom, #total_amount, #service_name
				 ' . '</p>';
        $html .= '<p class="description">' . __( 'Placeholder for custom field:', 'wbk' ) . ' #field_ + custom field id. Example: #field_custom-field-1' . '</p>';
        echo  $html ;
    }
    
    // validate gg calendar event description
    public function validate_gg_calendar_event_description_customer( $input )
    {
        $input = sanitize_text_field( trim( $input ) );
        return $input;
    }
    
    // render card element error message
    public function render_stripe_card_element_error_message()
    {
        $value = get_option( 'wbk_stripe_card_element_error_message', 'incorrect input' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_stripe_card_element_error_message" name="wbk_stripe_card_element_error_message" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate
    public function validate_stripe_card_element_error_message( $input )
    {
        $input = sanitize_text_field( trim( $input ) );
        return $input;
    }
    
    // render api error message
    public function render_stripe_api_error_message()
    {
        $value = get_option( 'wbk_stripe_api_error_message', 'Payment failed: #response' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_stripe_api_error_message" name="wbk_stripe_api_error_message" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Available placeholders', 'wbk' ) . ': ' . '#response';
        echo  $html ;
    }
    
    public function render_stripe_button_text()
    {
        $value = get_option( 'wbk_stripe_button_text', __( 'Pay with credit card', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_stripe_button_text" name="wbk_stripe_button_text" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate
    public function validate_stripe_button_text( $input )
    {
        $input = sanitize_text_field( trim( $input ) );
        return $input;
    }
    
    // validate api error message
    public function validate_stripe_api_error_message( $input )
    {
        $input = sanitize_text_field( trim( $input ) );
        return $input;
    }
    
    public function render_pay_on_arrival_message()
    {
        $value = get_option( 'wbk_pay_on_arrival_message', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_pay_on_arrival_message" name="wbk_pay_on_arrival_message" value="' . $value . '" >';
        echo  $html ;
    }
    
    public function validate_pay_on_arrival_message( $input )
    {
        $input = sanitize_text_field( trim( $input ) );
        return $input;
    }
    
    public function render_bank_transfer_message()
    {
        $value = get_option( 'wbk_bank_transfer_message', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_bank_transfer_message" name="wbk_bank_transfer_message" value="' . $value . '" >';
        echo  $html ;
    }
    
    public function validate_bank_transfer_message( $input )
    {
        $input = sanitize_text_field( trim( $input ) );
        return $input;
    }
    
    public function render_pay_on_arrival_button_text()
    {
        $value = get_option( 'wbk_pay_on_arrival_button_text', __( 'Pay on arrival', 'wbk' ) );
        $html = '<input type="text" id="wbk_pay_on_arrival_button_text" name="wbk_pay_on_arrival_button_text" value="' . $value . '" >';
        echo  $html ;
    }
    
    public function validate_pay_on_arrival_button_text( $input )
    {
        return $input;
    }
    
    public function render_bank_transfer_button_text()
    {
        $value = get_option( 'wbk_bank_transfer_button_text', __( 'Pay by bank transfer', 'wbk' ) );
        $html = '<input type="text" id="wbk_bank_transfer_button_text" name="wbk_bank_transfer_button_text" value="' . $value . '" >';
        echo  $html ;
    }
    
    public function validate_bank_transfer_button_text( $input )
    {
        return $input;
    }
    
    // render booked text
    public function render_booked_text()
    {
        $value = get_option( 'wbk_booked_text', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_booked_text" name="wbk_booked_text" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Text on booked time slot. Available placeholders: #username, #time.', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'Since version 3.3.61 you can use general placeholders in this option. More information: ', 'wbk' ) . '<a href="https://webba-booking.com/documentation/placeholders/" rel="noopener" target="_blank" >' . __( 'Placeholders', 'wbk' ) . '</a>.</p>';
        echo  $html ;
    }
    
    // validate booked text
    public function validate_booked_text( $input )
    {
        return sanitize_text_field( $input );
    }
    
    // 2.2.8 settings pack
    // render book text (timeslot)
    public function render_book_text_timeslot()
    {
        $value = get_option( 'wbk_book_text_timeslot', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_book_text_timeslot" name="wbk_book_text_timeslot" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate book text (timeslot)
    public function validate_book_text_timeslot( $input )
    {
        return sanitize_text_field( $input );
    }
    
    // render book text (form)
    public function render_book_text_form()
    {
        $value = get_option( 'wbk_book_text_form', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_book_text_form" name="wbk_book_text_form" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate book text (form)
    public function validate_book_text_form( $input )
    {
        return sanitize_text_field( $input );
    }
    
    // render name label
    public function render_name_label()
    {
        $value = get_option( 'wbk_name_label', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_name_label" name="wbk_name_label" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate name label
    public function validate_name_label( $input )
    {
        return sanitize_text_field( $input );
    }
    
    // render email label
    public function render_email_label()
    {
        $value = get_option( 'wbk_email_label', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_email_label" name="wbk_email_label" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate email label
    public function validate_email_label( $input )
    {
        return sanitize_text_field( $input );
    }
    
    // render email label
    public function render_date_input_placeholder()
    {
        $value = get_option( 'wbk_date_input_placeholder', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_date_input_placeholder" name="wbk_date_input_placeholder" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate email label
    public function validate_date_input_placeholder( $input )
    {
        $input = sanitize_text_field( $input );
        $input = str_replace( '.', '', $input );
        return $input;
    }
    
    // render phone label
    public function render_phone_label()
    {
        $value = get_option( 'wbk_phone_label', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_phone_label" name="wbk_phone_label" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate phone label
    public function validate_phone_label( $input )
    {
        return sanitize_text_field( $input );
    }
    
    // render comment label
    public function render_comment_label()
    {
        $value = get_option( 'wbk_comment_label', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_comment_label" name="wbk_comment_label" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate comment label
    public function validate_comment_label( $input )
    {
        return sanitize_text_field( $input );
    }
    
    // end 2.2.8 settings pack
    // render quantiy label
    public function render_book_items_quantity_label()
    {
        $value = get_option( 'wbk_book_items_quantity_label', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_book_items_quantity_label" name="wbk_book_items_quantity_label" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Booking items count frontend label', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'Available placeholders: ', 'wbk' ) . '#service' . '</p>';
        echo  $html ;
    }
    
    // validate quantity label
    public function validate_book_items_quantity_label( $input )
    {
        return sanitize_text_field( $input );
    }
    
    // render booking cancel button text
    public function render_cancel_button_text()
    {
        $value = get_option( 'wbk_cancel_button_text', __( 'Cancel booking', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_cancel_button_text" name="wbk_cancel_button_text" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate booking cancel button text
    public function validate_cancel_button_text( $input )
    {
        return sanitize_text_field( $input );
    }
    
    // render checkout button text
    public function render_checkout_button_text()
    {
        $value = get_option( 'wbk_checkout_button_text', __( 'Checkout', 'wbk' ) );
        $html = '<input type="text" id="wbk_checkout_button_text" name="wbk_checkout_button_text" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Available placeholders: ', 'wbk' ) . '#selected_count, #total_count, #low_limit' . '</p>';
        echo  $html ;
    }
    
    // validate checkout button text
    public function validate_checkout_button_text( $input )
    {
        return $input;
    }
    
    // render pay with paypal button text
    public function render_payment_pay_with_paypal_btn_text()
    {
        $value = get_option( 'wbk_payment_pay_with_paypal_btn_text', __( 'Pay now with PayPal', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_payment_pay_with_paypal_btn_text" name="wbk_payment_pay_with_paypal_btn_text" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate pay with paypal button text
    public function validate_payment_pay_with_paypal_btn_text( $input )
    {
        return sanitize_text_field( $input );
    }
    
    // render pay with cc button text
    public function render_payment_pay_with_cc_btn_text()
    {
        $value = get_option( 'wbk_payment_pay_with_cc_btn_text', __( 'Pay now with credit card', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_payment_pay_with_cc_btn_text" name="wbk_payment_pay_with_cc_btn_text" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate pay with cc button text
    public function validate_payment_pay_with_cc_btn_text( $input )
    {
        return sanitize_text_field( $input );
    }
    
    // render payment details title
    public function render_payment_details_title()
    {
        $value = get_option( 'wbk_payment_details_title', __( 'Payment details', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_payment_details_title" name="wbk_payment_details_title" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate payment details title
    public function validate_payment_details_title( $input )
    {
        return sanitize_text_field( $input );
    }
    
    // render payment item name
    public function render_payment_item_name()
    {
        $value = get_option( 'wbk_payment_item_name', '#service' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_payment_item_name" name="wbk_payment_item_name" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Available placeholders: #service_name, #service_description, #date, #time, #tr (time range), #id, #quantity', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate payment item name
    public function validate_payment_item_name( $input )
    {
        return sanitize_text_field( $input );
    }
    
    // render payment price format
    public function render_payment_price_format()
    {
        $value = get_option( 'wbk_payment_price_format', '$#price' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_payment_price_format" name="wbk_payment_price_format" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Required placeholder: #price.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate payment price format
    public function validate_payment_price_format( $input )
    {
        return sanitize_text_field( $input );
    }
    
    // render subtotal title
    public function render_payment_subtotal_title()
    {
        $value = get_option( 'wbk_payment_subtotal_title', 'Subtotal' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_payment_subtotal_title" name="wbk_payment_subtotal_title" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate payment subtotal title
    public function validate_payment_subtotal_title( $input )
    {
        return sanitize_text_field( $input );
    }
    
    // render total title
    public function render_payment_total_title()
    {
        $value = get_option( 'wbk_payment_total_title', 'Total' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_payment_total_title" name="wbk_payment_total_title" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate payment total title
    public function validate_payment_total_title( $input )
    {
        return sanitize_text_field( $input );
    }
    
    // render nothing to pay
    public function render_nothing_to_pay_message()
    {
        $value = get_option( 'wbk_nothing_to_pay_message', __( 'There are no bookings available for payment.', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_nothing_to_pay_message" name="wbk_nothing_to_pay_message" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate nothing to pay
    public function validate_nothing_to_pay_message( $input )
    {
        return sanitize_text_field( $input );
    }
    
    // render approve payment text
    public function render_payment_approve_text()
    {
        $value = get_option( 'wbk_payment_approve_text', __( 'Approve payment', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_payment_approve_text" name="wbk_payment_approve_text" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate approve payment text
    public function validate_payment_approve_text( $input )
    {
        return sanitize_text_field( $input );
    }
    
    // render payment result title
    public function render_payment_result_title()
    {
        $value = get_option( 'wbk_payment_result_title', __( 'Payment status', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_payment_result_title" name="wbk_payment_result_title" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate payment result title
    public function validate_payment_result_title( $input )
    {
        return sanitize_text_field( $input );
    }
    
    // render thanks message
    public function render_book_thanks_message()
    {
        $value = get_option( 'wbk_book_thanks_message', '' );
        $args = array(
            'media_buttons' => true,
            'editor_height' => 300,
        );
        echo  '<a class="button wbk_email_editor_toggle">' . __( 'Toggle editor', 'wbk' ) . '</a><div class="wbk_email_editor_wrap" style="display:none;">' ;
        wp_editor( $value, 'wbk_book_thanks_message', $args );
        echo  '</div>' ;
    }
    
    // validate thanks message
    public function validate_book_thanks_message( $input )
    {
        return $input;
    }
    
    // render not found message
    public function render_book_not_found_message()
    {
        $value = get_option( 'wbk_book_not_found_message', '' );
        $html = '<input type="text" id="wbk_book_not_found_message" name="wbk_book_not_found_message" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Time slots not found message', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'Allowed HTML tags', 'wbk' ) . ': strong, em, b, i, br, p, a</p>';
        echo  $html ;
    }
    
    // validate not found message
    public function validate_book_not_found_message( $input )
    {
        return $input;
    }
    
    // render purchase code
    public function render_purchase_code()
    {
        $value = get_option( 'wbk_purchase_code', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_purchase_code" name="wbk_purchase_code" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate purchase code
    public function validate_purchase_code( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render customer name output (backend)
    public function render_customer_name_output()
    {
        $value = get_option( 'wbk_customer_name_output', '#name' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_customer_name_output" name="wbk_customer_name_output" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Use this option if you need show custom fields near customer name in the appointments table and in the schedules.', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'Example: #name #field_lastname', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'The example above show how to show customer\'s name and last name. The last name is stored in the custom field with id "lastname" in this example.', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'Note, it\'s necessary to include #name placeholder into the value of this option.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate customer name output (backend)
    public function validate_customer_name_output( $input )
    {
        $input = trim( $input );
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render show booked slots
    public function render_show_booked_slots()
    {
        $value = get_option( 'wbk_show_booked_slots', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_show_booked_slots" name="wbk_show_booked_slots">
				    <option ' . selected( $value, 'enabled', false ) . ' value="enabled">' . __( 'Enabled', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Disabled', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    // validate show booked slots
    public function validate_show_booked_slots( $input )
    {
        $input = trim( $input );
        return $input;
        $value = sanitize_text_field( $value );
        if ( $value != 'enabled' && $value != 'disabled' ) {
            $value = 'disabled';
        }
        return $input;
    }
    
    // render phone required
    public function render_phone_required()
    {
        $value = get_option( 'wbk_phone_required', '3' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_phone_required" name="wbk_phone_required">
				    <option ' . selected( $value, '3', false ) . ' value="3">' . __( 'Yes', 'wbk' ) . '</option>
				    <option ' . selected( $value, '0', false ) . ' value="0">' . __( 'No', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    // validate phone required
    public function validate_phone_required( $input )
    {
        $input = trim( $input );
        return $input;
        $value = sanitize_text_field( $value );
        if ( $value != '0' && $value != '3' ) {
            $value = '0';
        }
        return $input;
    }
    
    // render lock appointments
    public function render_appointments_auto_lock()
    {
        $value = get_option( 'wbk_appointments_auto_lock', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_appointments_auto_lock" name="wbk_appointments_auto_lock">
				    <option ' . selected( $value, 'enabled', false ) . ' value="enabled">' . __( 'Enabled', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Disabled', 'wbk' ) . '</option>
   				 </select>';
        $html .= '<p class="description">' . __( 'Enable this option for auto lock time slots of different services on booking (connection between services).', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate lock appointments
    public function validate_appointments_auto_lock( $input )
    {
        $input = trim( $input );
        return $input;
        $value = sanitize_text_field( $value );
        if ( $value != 'enabled' && $value != 'disabled' ) {
            $value = 'disabled';
        }
        return $input;
    }
    
    public function render_custom_fields_columns()
    {
        $value = get_option( 'wbk_custom_fields_columns', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_custom_fields_columns" name="wbk_custom_fields_columns" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Comma-separated list of IDs of custom fields.', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'Use square brackets to set column headers.', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'Example: custom-field1[Title 1],custom-field2[Title 2]', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function validate_custom_fields_columns( $input )
    {
        $input = trim( $input );
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render lock appointments mode
    public function render_appointments_auto_lock_mode()
    {
        $value = get_option( 'wbk_appointments_auto_lock_mode', 'all' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_appointments_auto_lock_mode" name="wbk_appointments_auto_lock_mode">
				    <option ' . selected( $value, 'all', false ) . ' value="all">' . __( 'all services', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'categories', false ) . ' value="categories">' . __( 'services in the same categories', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    public function render_backend_show_category_name()
    {
        $value = get_option( 'wbk_backend_show_category_name', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_backend_show_category_name" name="wbk_backend_show_category_name">
				    <option ' . selected( $value, 'enabled', false ) . ' value="enabled">' . __( 'Enabled', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Disabled', 'wbk' ) . '</option>
				 </select>';
        echo  $html ;
    }
    
    public function validate_backend_show_category_name( $input )
    {
        return $input;
    }
    
    public function render_default_days_number()
    {
        $value = get_option( 'wbk_filter_default_days_number', '14' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_filter_default_days_number" name="wbk_filter_default_days_number" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Tip: set lower value for better performance.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function validate_default_days_number( $input )
    {
        $input = trim( $input );
        $input = sanitize_text_field( $input );
        if ( !is_numeric( $input ) || $input < 0 ) {
            $input = 14;
        }
        return $input;
    }
    
    // validate lock appointments
    public function validate_appointments_auto_lock_mode( $input )
    {
        $input = trim( $input );
        $input = sanitize_text_field( $input );
        if ( $input != 'all' && $input != 'categories' ) {
            $input = 'all';
        }
        return $input;
    }
    
    public function render_appointments_autolock_avail_limit()
    {
        $value = get_option( 'wbk_appointments_autolock_avail_limit', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_appointments_autolock_avail_limit" name="wbk_appointments_autolock_avail_limit" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Maximum number of available bookings at given time', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'Leave empty to not set this limit', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate purchase code
    public function validate_appointments_autolock_avail_limit( $input )
    {
        $input = trim( sanitize_text_field( $input ) );
        
        if ( !is_numeric( $input ) ) {
            $input = '';
        } else {
            $input = intval( $input );
        }
        
        if ( $input < 1 ) {
            $input = '';
        }
        return $input;
    }
    
    public function render_appointments_limit_by_day()
    {
        $value = get_option( 'wbk_appointments_limit_by_day', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_appointments_limit_by_day" name="wbk_appointments_limit_by_day" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Maximum number of bookings of all services at one day', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'Leave empty to not set limit', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate purchase code
    public function validate_appointments_limit_by_day( $input )
    {
        $input = trim( sanitize_text_field( $input ) );
        
        if ( !is_numeric( $input ) ) {
            $input = '';
        } else {
            $input = intval( $input );
        }
        
        if ( $input < 1 ) {
            $input = '';
        }
        return $input;
    }
    
    public function render_appointments_lock_timeslot_if_parital_booked()
    {
        $value = get_option( 'wbk_appointments_lock_timeslot_if_parital_booked', '' );
        if ( !is_array( $value ) ) {
            $value = array();
        }
        $arrIds = WBK_Db_Utils::getServices();
        echo  '<select class="wbk_appointments_lock_timeslot_if_parital_booked" name="wbk_appointments_lock_timeslot_if_parital_booked[]"  id="wbk_appointments_lock_timeslot_if_parital_booked[]" Multiple>' ;
        foreach ( $arrIds as $id ) {
            $service = new WBK_Service_deprecated();
            if ( !$service->setId( $id ) ) {
                continue;
            }
            if ( !$service->load() ) {
                continue;
            }
            
            if ( in_array( $service->getId(), $value ) ) {
                $selected = ' selected ';
            } else {
                $selected = '';
            }
            
            echo  '<option ' . $selected . '  value="' . $service->getId() . '">' . $service->getName( true ) . '</option>' ;
        }
        echo  '</select>' ;
        echo  '<p class="description">' . __( 'Select the services for which this rule is applied', 'wbk' ) . '</p>' ;
        echo  '<p class="description">' . __( 'Note: this option is used by front-end booking', 'wbk' ) . '</p>' ;
    }
    
    public function validate_appointments_lock_timeslot_if_parital_booked( $input )
    {
        return $input;
    }
    
    public function render_appointments_continuous()
    {
        $value = get_option( 'wbk_appointments_continuous', '' );
        if ( !is_array( $value ) ) {
            $value = array();
        }
        $arrIds = WBK_Db_Utils::getServices();
        echo  '<select class="wbk_appointments_continuous" name="wbk_appointments_continuous[]"  id="wbk_appointments_continuous[]" Multiple>' ;
        foreach ( $arrIds as $id ) {
            $service = new WBK_Service_deprecated();
            if ( !$service->setId( $id ) ) {
                continue;
            }
            if ( !$service->load() ) {
                continue;
            }
            
            if ( in_array( $service->getId(), $value ) ) {
                $selected = ' selected ';
            } else {
                $selected = '';
            }
            
            echo  '<option ' . $selected . '  value="' . $service->getId() . '">' . $service->getName( true ) . '</option>' ;
        }
        echo  '</select>' ;
        echo  '<p class="description">' . __( 'Select the services for which this rule is applied', 'wbk' ) . '</p>' ;
    }
    
    public function validate_appointments_continuous( $input )
    {
        return $input;
    }
    
    public function render_appointments_lock_day_if_timeslot_booked()
    {
        $value = get_option( 'wbk_appointments_lock_day_if_timeslot_booked', '' );
        if ( !is_array( $value ) ) {
            $value = array();
        }
        $arrIds = WBK_Db_Utils::getServices();
        echo  '<select class="wbk_appointments_lock_day_if_timeslot_booked" name="wbk_appointments_lock_day_if_timeslot_booked[]"  id="wbk_appointments_lock_day_if_timeslot_booked[]" Multiple>' ;
        foreach ( $arrIds as $id ) {
            $service = new WBK_Service_deprecated();
            if ( !$service->setId( $id ) ) {
                continue;
            }
            if ( !$service->load() ) {
                continue;
            }
            
            if ( in_array( $service->getId(), $value ) ) {
                $selected = ' selected ';
            } else {
                $selected = '';
            }
            
            echo  '<option ' . $selected . '  value="' . $service->getId() . '">' . $service->getName( true ) . '</option>' ;
        }
        echo  '</select>' ;
        echo  '<p class="description">' . __( 'Select the services for which this rule is applied', 'wbk' ) . '</p>' ;
        echo  '<p class="description">' . __( 'Note: if autolock is enabled, appointments of the connected services are taken into amount.', 'wbk' ) . '</p>' ;
    }
    
    public function validate_appointments_lock_day_if_timeslot_booked( $input )
    {
        return $input;
    }
    
    public function render_appointments_lock_one_before_and_one_after()
    {
        $value = get_option( 'wbk_appointments_lock_one_before_and_one_after', '' );
        if ( !is_array( $value ) ) {
            $value = array();
        }
        $arrIds = WBK_Db_Utils::getServices();
        echo  '<select class="wbk_appointments_lock_one_before_and_one_after" name="wbk_appointments_lock_one_before_and_one_after[]"  id="wbk_appointments_lock_one_before_and_one_after[]" Multiple>' ;
        foreach ( $arrIds as $id ) {
            $service = new WBK_Service_deprecated();
            if ( !$service->setId( $id ) ) {
                continue;
            }
            if ( !$service->load() ) {
                continue;
            }
            
            if ( in_array( $service->getId(), $value ) ) {
                $selected = ' selected ';
            } else {
                $selected = '';
            }
            
            echo  '<option ' . $selected . '  value="' . $service->getId() . '">' . $service->getName( true ) . '</option>' ;
        }
        echo  '</select>' ;
        echo  '<p class="description">' . __( 'Select the services for which this rule is applied', 'wbk' ) . '</p>' ;
        echo  '<p class="description">' . __( 'Note: if continuous appointments enabled for the service, time slot will be locked before the first booked timeslot and after last booked time slot.', 'wbk' ) . '</p>' ;
    }
    
    public function validate_appointments_lock_one_before_and_one_after( $input )
    {
        return $input;
    }
    
    public function render_appointments_special_hours()
    {
        $value = get_option( 'wbk_appointments_special_hours', '' );
        echo  '<textarea style="width:350px; height:250px" name="wbk_appointments_special_hours"  id="wbk_appointments_special_hours">' . $value . '</textarea>' ;
        echo  '<p class="description">' . __( 'Set this option to override the business hours of certain services on specific dates.', 'wbk' ) . '</p>' ;
        echo  '<p class="description">' . __( 'Example 1:', 'wbk' ) . '</p>' ;
        echo  '<p class="description">' . __( '1 01/15/2020 15:00-18:00', 'wbk' ) . '</p>' ;
        echo  '<p class="description">' . __( 'Service with the id equals 1 is availeble on 01/15/2020 at 15:00-18:00', 'wbk' ) . '</p>' ;
        echo  '<p class="description">' . __( 'Example 2:', 'wbk' ) . '</p>' ;
        echo  '<p class="description">' . __( '01/15/2020 15:00-18:00', 'wbk' ) . '</p>' ;
        echo  '<p class="description">' . __( 'All services are available on 01/15/2020 at 15:00-18:00', 'wbk' ) . '</p>' ;
    }
    
    public function validate_appointments_special_hours( $input )
    {
        return $input;
    }
    
    // render lock appointments group
    public function render_appointments_auto_lock_group()
    {
        $value = get_option( 'wbk_appointments_auto_lock_group', 'lock' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_appointments_auto_lock_group" name="wbk_appointments_auto_lock_group">
				    <option ' . selected( $value, 'lock', false ) . ' value="lock">' . __( 'lock time slot', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'reduce', false ) . ' value="reduce">' . __( 'reduce count of available places', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    // validate lock appointments group
    public function validate_appointments_auto_lock_group( $input )
    {
        $input = trim( $input );
        $input = sanitize_text_field( $input );
        if ( $input != 'lock' && $input != 'reduce' ) {
            $input = 'reduce';
        }
        return $input;
    }
    
    // render lock appointments auto lock allow unlock
    public function render_appointments_auto_lock_allow_unlock()
    {
        $value = get_option( 'wbk_appointments_auto_lock_allow_unlock', 'allow' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_appointments_auto_lock_allow_unlock" name="wbk_appointments_auto_lock_allow_unlock">
				    <option ' . selected( $value, 'allow', false ) . ' value="allow">' . __( 'Allow', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'disallow', false ) . ' value="disallow">' . __( 'Disallow', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    // validate lock appointments auto lock allow unlock
    public function validate_appointments_auto_lock_allow_unlock( $input )
    {
        $input = trim( $input );
        $input = sanitize_text_field( $input );
        if ( $input != 'allow' && $input != 'disallow' ) {
            $input = 'allow';
        }
        return $input;
    }
    
    public function render_appointments_allow_cancel_paid()
    {
        $value = get_option( 'wbk_appointments_allow_cancel_paid', 'disallow' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_appointments_allow_cancel_paid" name="wbk_appointments_allow_cancel_paid">
				    <option ' . selected( $value, 'allow', false ) . ' value="allow">' . __( 'Allow', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'disallow', false ) . ' value="disallow">' . __( 'Disallow', 'wbk' ) . '</option>
   				 </select>';
        $html .= '<p class="description">' . __( 'Enable this option if you want to allow CUSTOMERS to cancel paid appointments.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function validate_appointments_allow_cancel_paid( $input )
    {
        $input = trim( $input );
        $input = sanitize_text_field( $input );
        if ( $input != 'allow' && $input != 'disallow' ) {
            $input = 'allow';
        }
        return $input;
    }
    
    public function render_appointments_only_one_per_slot()
    {
        $value = get_option( 'wbk_appointments_only_one_per_slot', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_appointments_only_one_per_slot" name="wbk_appointments_only_one_per_slot">
                    <option ' . selected( $value, 'enabled', false ) . ' value="enabled">' . __( 'Enabled', 'wbk' ) . '</option>
                    <option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Disabled', 'wbk' ) . '</option>
                 </select>';
        $html .= '<p class="description">' . __( 'Enable this option to allow only one appointment per time slot from one email.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function validate_appointments_only_one_per_slot( $input )
    {
        $input = trim( $input );
        $input = sanitize_text_field( $input );
        if ( $input != 'enabled' && $input != 'disabled' ) {
            $input = 'disabled';
        }
        return $input;
    }
    
    public function render_appointments_only_one_per_day()
    {
        $value = get_option( 'wbk_appointments_only_one_per_day', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_appointments_only_one_per_day" name="wbk_appointments_only_one_per_day">
                    <option ' . selected( $value, 'enabled', false ) . ' value="enabled">' . __( 'Enabled', 'wbk' ) . '</option>
                    <option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Disabled', 'wbk' ) . '</option>
                 </select>';
        $html .= '<p class="description">' . __( 'Enable this option to allow only one appointment per day from one email.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function validate_appointments_only_one_per_day( $input )
    {
        $input = trim( $input );
        $input = sanitize_text_field( $input );
        if ( $input != 'enabled' && $input != 'disabled' ) {
            $input = 'disabled';
        }
        return $input;
    }
    
    public function render_appointments_expiration_time_pending()
    {
        $value = get_option( 'wbk_appointments_expiration_time_pending', '0' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_appointments_expiration_time_pending" name="wbk_appointments_expiration_time_pending" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Automatically delete appointments with the "Awaiting approval" status after ... minutes.', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'Set 0 to not delete automatically', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate purchase code
    public function validate_appointments_expiration_time_pending( $input )
    {
        $input = trim( sanitize_text_field( $input ) );
        
        if ( !is_numeric( $input ) ) {
            $input = 0;
            add_settings_error(
                'wbk_appointments_expiration_time_pending',
                'wbk_appointments_expiration_time_pending_error',
                __( 'Webba Booking: expiration time setting updated', 'wbk' ),
                'updated'
            );
        } else {
            $input = intval( $input );
        }
        
        
        if ( $input < 5 && $input != 0 ) {
            $input = 5;
            add_settings_error(
                'wbk_appointments_expiration_time_pending',
                'wbk_appointments_expiration_time_pending_error',
                __( 'Webba Booking: expiration time setting updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    public function render_appointments_default_status()
    {
        $value = get_option( 'wbk_appointments_default_status', 'approved' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_appointments_default_status" name="wbk_appointments_default_status">
				    <option ' . selected( $value, 'approved', false ) . ' value="approved">' . __( 'Approved', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'pending', false ) . ' value="pending">' . __( 'Awaiting approval', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    // validate appointments default status
    public function validate_appointments_default_status( $input )
    {
        $input = trim( $input );
        $input = sanitize_text_field( $input );
        if ( $input != 'approved' && $input != 'pending' ) {
            $input = 'approved';
        }
        return $input;
    }
    
    // render allow payments
    public function render_appointments_allow_payments()
    {
        $value = get_option( 'wbk_appointments_allow_payments', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_appointments_allow_payments" name="wbk_appointments_allow_payments">
				    <option ' . selected( $value, 'enabled', false ) . ' value="enabled">' . __( 'Enabled', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Disabled', 'wbk' ) . '</option>
   				 </select>';
        $html .= '<p class="description">' . __( 'Enable this option if you want to allow online payments for the approved appointments <b>ONLY</b>.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate lock appointments
    public function validate_appointments_allow_payments( $input )
    {
        $input = trim( $input );
        $input = sanitize_text_field( $input );
        if ( $input != 'enabled' && $input != 'disabled' ) {
            $input = 'disabled';
        }
        return $input;
    }
    
    // render delete appointments mode
    public function render_appointments_delete_not_paid_mode()
    {
        $value = get_option( 'wbk_appointments_delete_not_paid_mode', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_appointments_delete_not_paid_mode" name="wbk_appointments_delete_not_paid_mode">
				    <option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Disabled', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'on_booking', false ) . ' value="on_booking">' . __( 'Set expiration time on booking', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'on_approve', false ) . ' value="on_approve">' . __( 'Set expiration time on approve', 'wbk' ) . '</option>
   				 </select>';
        $html .= '<p class="description">' . __( 'Enable this option to delete expired (not paid) appointments', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( '*Expiration feature affect only on booking made at the front-end', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( '*Expiration feature will not affect on bookings in the process of payment, except if a customer canceled payment at PayPal side', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // render delete started payment
    public function render_appointments_delete_payment_started()
    {
        $value = get_option( 'wbk_appointments_delete_payment_started', 'skip' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_appointments_delete_payment_started" name="wbk_appointments_delete_payment_started">
				    <option ' . selected( $value, 'skip', false ) . ' value="skip">' . __( 'Do not delete appointments with started transaction', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'delete', false ) . ' value="delete">' . __( 'Delete appointments with started transaction', 'wbk' ) . '</option>

   				 </select>';
        $html .= '<p class="description">' . __( 'IMPORTANT: if you choose "Delete appointments with started transaction", expired appointments will be deleted even if a customer started (and not finished) the payment (initialized transaction).', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // render expiration time
    public function render_appointments_expiration_time()
    {
        $value = get_option( 'wbk_appointments_expiration_time', '60' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_appointments_expiration_time" name="wbk_appointments_expiration_time" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Expiration time in minutes.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate purchase code
    public function validate_appointments_expiration_time( $input )
    {
        $input = trim( sanitize_text_field( $input ) );
        
        if ( !is_numeric( $input ) ) {
            $input = 60;
            add_settings_error(
                'wbk_appointments_expiration_time',
                'wbk_appointments_expiration_time_error',
                __( 'Webba Booking: expiration time setting updated', 'wbk' ),
                'updated'
            );
        } else {
            $input = intval( $input );
        }
        
        
        if ( $input < 5 ) {
            $input = 5;
            add_settings_error(
                'wbk_appointments_expiration_time',
                'wbk_appointments_expiration_time_error',
                __( 'Webba Booking: expiration time setting updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    public function render_price_fractional()
    {
        $value = get_option( 'wbk_price_fractional', '2' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_price_fractional" name="wbk_price_fractional" value="' . $value . '" >';
        echo  $html ;
    }
    
    public function validate_price_fractional( $input )
    {
        $input = intval( trim( sanitize_text_field( $input ) ) );
        if ( $input != 0 && $input != 1 && $input != 2 ) {
            $input = 2;
        }
        return $input;
    }
    
    public function render_price_separator()
    {
        $value = get_option( 'wbk_price_separator', '.' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_price_separator" name="wbk_price_separator" value="' . $value . '" >';
        echo  $html ;
    }
    
    public function validate_price_separator( $input )
    {
        return $input;
    }
    
    public function render_scroll_container()
    {
        $value = get_option( 'wbk_scroll_container', 'html, body' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_scroll_container" name="wbk_scroll_container" value="' . $value . '" >';
        echo  $html ;
    }
    
    public function validate_scroll_container( $input )
    {
        $input = trim( sanitize_text_field( $input ) );
        return $input;
    }
    
    public function render_scroll_value()
    {
        $value = get_option( 'wbk_scroll_value', '120' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_scroll_value" name="wbk_scroll_value" value="' . $value . '" >';
        echo  $html ;
    }
    
    public function validate_scroll_value( $input )
    {
        $input = trim( sanitize_text_field( $input ) );
        return $input;
    }
    
    // render cancellation buffer
    public function render_cancellation_buffer()
    {
        $value = get_option( 'wbk_cancellation_buffer', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_cancellation_buffer" name="wbk_cancellation_buffer" value="' . $value . '" > ' . __( 'minutes', 'wbk' );
        $html .= '<p class="description">' . __( 'Buffer time: minimum time to allow a cancellation before the appointment / reservation.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate cancellation buffer
    public function validate_cancellation_buffer( $input )
    {
        $input = trim( sanitize_text_field( $input ) );
        if ( is_numeric( $input ) ) {
            if ( intval( $input ) > 0 ) {
                return $input;
            }
        }
        $input = '';
        return $input;
    }
    
    // validate delete appointments mode
    public function validate_appointments_delete_not_paid_mode( $input )
    {
        $input = trim( $input );
        $input = sanitize_text_field( $input );
        if ( $input != 'disabled' && $input != 'on_booking' && $input != 'on_approve' ) {
            $input = 'disabled';
        }
        return $input;
    }
    
    // validate delete started appointments
    public function validate_appointments_delete_payment_started( $input )
    {
        $input = trim( $input );
        $input = sanitize_text_field( $input );
        if ( $input != 'delete' && $input != 'delete' ) {
            $input = 'skip';
        }
        return $input;
    }
    
    // render hide form on booking
    public function render_check_short_code()
    {
        $value = get_option( 'wbk_check_short_code', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_check_short_code" name="wbk_check_short_code">
				    <option ' . selected( $value, 'enabled', false ) . ' value="enabled">' . __( 'Enabled', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Disabled', 'wbk' ) . '</option>
   				 </select>';
        $html .= '<p class="description">' . __( 'Enable this option to check if the page has shortcode before booking form initialized.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate hide booking form
    public function validate_check_short_code( $input )
    {
        $input = trim( $input );
        return $input;
        $value = sanitize_text_field( $value );
        if ( $value != 'enabled' && $value != 'disabled' ) {
            $value = 'disabled';
        }
        return $input;
    }
    
    // render show cancel button
    public function render_show_cancel_button()
    {
        $value = get_option( 'wbk_show_cancel_button', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_show_cancel_button" name="wbk_show_cancel_button">
				    <option ' . selected( $value, 'enabled', false ) . ' value="enabled">' . __( 'Enabled', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Disabled', 'wbk' ) . '</option>
   				 </select>';
        $html .= '<p class="description">' . __( 'Enable this option to show cancel button on the steps of the booking process.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate show cancel button
    public function validate_show_cancel_button( $input )
    {
        $input = trim( $input );
        $input = sanitize_text_field( $input );
        if ( $input != 'enabled' && $input != 'disabled' ) {
            $input = 'disabled';
        }
        return $input;
    }
    
    // render jquery nc
    public function render_jquery_nc()
    {
        $value = get_option( 'wbk_jquery_nc', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_jquery_nc" name="wbk_jquery_nc">
				    <option ' . selected( $value, 'enabled', false ) . ' value="enabled">' . __( 'Enabled', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Disabled', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    // validate jquery nc
    public function validate_jquery_nc( $input )
    {
        $input = trim( $input );
        $input = sanitize_text_field( $input );
        if ( $input != 'enabled' && $input != 'disabled' ) {
            $input = 'disabled';
        }
        return $input;
    }
    
    // render pickadate load
    public function render_pickadate_load()
    {
        $value = get_option( 'wbk_pickadate_load', 'yes' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_pickadate_load" name="wbk_pickadate_load">
				    <option ' . selected( $value, 'yes', false ) . ' value="yes">' . __( 'yes', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'no', false ) . ' value="no">' . __( 'no', 'wbk' ) . '</option>
   				 </select>';
        $html .= '<p class="description">' . __( 'Set "no" if there are plugins in your WordPress that are using the pickadate date picker.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate pickadate load
    public function validate_pickadate_load( $input )
    {
        $input = trim( $input );
        $input = sanitize_text_field( $input );
        if ( $input != 'yes' && $input != 'no' ) {
            $input = 'yes';
        }
        return $input;
    }
    
    // render manage by link
    public function render_allow_manage_by_link()
    {
        $value = get_option( 'wbk_allow_manage_by_link', 'no' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_allow_manage_by_link" name="wbk_allow_manage_by_link">
				    <option ' . selected( $value, 'yes', false ) . ' value="yes">' . __( 'yes', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'no', false ) . ' value="no">' . __( 'no', 'wbk' ) . '</option>
   				 </select>';
        $html .= '<p class="description">' . __( 'Set "yes" to allow administrator to cancel or approve appointment with the link sent in notification.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate allow by link
    public function validate_allow_manage_by_link( $input )
    {
        $input = trim( $input );
        $input = sanitize_text_field( $input );
        if ( $input != 'yes' && $input != 'no' ) {
            $input = 'yes';
        }
        return $input;
    }
    
    public function render_allow_coupons()
    {
        $value = get_option( 'wbk_allow_coupons', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_allow_coupons" name="wbk_allow_coupons">
				    <option ' . selected( $value, 'enabled', false ) . ' value="enabled">' . __( 'Enabled', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Disabled', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    public function validate_allow_coupons( $input )
    {
        $input = trim( $input );
        $input = sanitize_text_field( $input );
        if ( $input != 'enabled' && $input != 'disabled' ) {
            $input = 'disabled';
        }
        return $input;
    }
    
    public function render_tax_for_messages()
    {
        $value = get_option( 'wbk_tax_for_messages', 'paypal' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_tax_for_messages" name="wbk_tax_for_messages">
				    <option ' . selected( $value, 'paypal', false ) . ' value="paypal">' . __( 'PayPal tax option', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'stripe', false ) . ' value="stripe">' . __( 'Stripe tax option', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'none', false ) . ' value="none">' . __( 'Do not include tax', 'wbk' ) . '</option>

   				 </select>';
        $html .= '<p class="description">' . __( 'This option is used when calculating the total amount with #total_amount placeholders in email and interface messages', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function validate_tax_for_messages( $input )
    {
        $input = trim( $input );
        $input = sanitize_text_field( $input );
        if ( $input != 'paypal' && $input != 'stripe' && $input != 'none' ) {
            $input = 'paypal';
        }
        return $input;
    }
    
    // render disable date on all booked
    public function render_disable_day_on_all_booked()
    {
        $value = get_option( 'wbk_disable_day_on_all_booked', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_disable_day_on_all_booked" name="wbk_disable_day_on_all_booked">
				    <option ' . selected( $value, 'enabled', false ) . ' value="enabled">' . __( 'Yes', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'No', 'wbk' ) . '</option>
   				 </select>';
        $html .= '<p class="description">' . __( 'Disable date in the calendar if no free time slots found.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate disable date on all booked
    public function validate_disable_day_on_all_booked( $input )
    {
        $input = trim( $input );
        return $input;
        $value = sanitize_text_field( $value );
        if ( $value != 'enabled' && $value != 'disabled' ) {
            $value = 'disabled';
        }
        return $input;
    }
    
    // render check shortcode
    public function render_hide_from_on_booking()
    {
        $value = get_option( 'wbk_hide_from_on_booking', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_hide_from_on_booking" name="wbk_hide_from_on_booking">
				    <option ' . selected( $value, 'enabled', false ) . ' value="enabled">' . __( 'Enabled', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Disabled', 'wbk' ) . '</option>
   				 </select>';
        $html .= '<p class="description">' . __( 'Enable this option to hide all sections of the booking form when booking is done.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate check shortcode
    public function validate_hide_from_on_booking( $input )
    {
        $input = trim( $input );
        return $input;
        $value = sanitize_text_field( $value );
        if ( $value != 'enabled' && $value != 'disabled' ) {
            $value = 'disabled';
        }
        return $input;
    }
    
    // render payment success mesage
    public function render_payment_success_message()
    {
        $value = get_option( 'wbk_payment_success_message', __( 'Payment complete.' ) );
        $value = htmlspecialchars( $value );
        $html = '<input type="text" id="wbk_payment_success_message" name="wbk_payment_success_message" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Allowed HTML tags', 'wbk' ) . ': strong, em, b, i, br, p, a</p>';
        echo  $html ;
    }
    
    // validate form label
    public function validate_payment_success_message( $input )
    {
        return $input;
    }
    
    // render payment cancel mesage
    public function render_payment_cancel_message()
    {
        $value = get_option( 'wbk_payment_cancel_message', __( 'Payment canceled.' ) );
        $value = htmlspecialchars( $value );
        $html = '<input type="text" id="wbk_payment_cancel_message" name="wbk_payment_cancel_message" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate form label
    public function validate_payment_cancel_message( $input )
    {
        return $input;
    }
    
    // paypal options functions ******************************************************************************************************
    // render paypal mode
    public function render_paypal_mode()
    {
        $value = get_option( 'wbk_paypal_mode', 'Sandbox' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_paypal_mode" name="wbk_paypal_mode">
				    <option ' . selected( $value, 'Sandbox', false ) . ' value="Sandbox">' . __( 'Sandbox', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'Live', false ) . ' value="Live">' . __( 'Live', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    // validate paypal mode
    public function validate_paypal_mode( $input )
    {
        $input = trim( $input );
        return $input;
        $value = sanitize_text_field( $value );
        if ( $value != 'Sandbox' && $value != 'Live' ) {
            $value = 'Sandbox';
        }
        return $input;
    }
    
    // render paypal sandbox client id
    public function render_paypal_sandbox_clientid()
    {
        $value = get_option( 'wbk_paypal_sandbox_clientid', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_paypal_sandbox_clientid" name="wbk_paypal_sandbox_clientid" value="' . $value . '" >' . '<a href="https://developer.paypal.com/developer/applications/"  rel="noopener" target="_blank" ><span class="dashicons dashicons-editor-help"></span></a>';
        echo  $html ;
    }
    
    // validate paypal sandbox client id
    public function validate_paypal_sandbox_clientid( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render paypal sandbox secret
    public function render_paypal_sandbox_secret()
    {
        $value = get_option( 'wbk_paypal_sandbox_secret', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="password" id="wbk_paypal_sandbox_secret" name="wbk_paypal_sandbox_secret" value="' . $value . '" >' . '<a href="https://developer.paypal.com/developer/applications/" rel="noopener"  target="_blank" ><span class="dashicons dashicons-editor-help"></span></a>';
        echo  $html ;
    }
    
    // paypal sandbox client id
    public function validate_paypal_sandbox_secret( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render paypal live client id
    public function render_paypal_live_clientid()
    {
        $value = get_option( 'wbk_paypal_live_clientid', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_paypal_live_clientid" name="wbk_paypal_live_clientid" value="' . $value . '" >' . '<a href="https://developer.paypal.com/developer/applications/" rel="noopener" target="_blank" ><span class="dashicons dashicons-editor-help"></span></a>';
        echo  $html ;
    }
    
    // paypal live client id
    public function validate_paypal_live_clientid( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render paypal live secret
    public function render_paypal_live_secret()
    {
        $value = get_option( 'wbk_paypal_live_secret', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="password" id="wbk_paypal_live_secret" name="wbk_paypal_live_secret" value="' . $value . '" >' . '<a href="https://developer.paypal.com/developer/applications/"  rel="noopener" target="_blank"  ><span class="dashicons dashicons-editor-help"></span></a>';
        echo  $html ;
    }
    
    // paypal live secret
    public function validate_paypal_live_secret( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render paypal currency
    public function render_paypal_currency()
    {
        $value = get_option( 'wbk_paypal_currency', 'USD' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_paypal_currency" name="wbk_paypal_currency">
				    <option ' . selected( $value, 'AUD', false ) . ' value="AUD">' . __( 'Australian Dollar', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'BRL', false ) . ' value="BRL">' . __( 'Brazilian Real', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'CAD', false ) . ' value="CAD">' . __( 'Canadian Dollar', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'CZK', false ) . ' value="CZK">' . __( 'Czech Koruna', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'DKK', false ) . ' value="DKK">' . __( 'Danish Krone', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'EUR', false ) . ' value="EUR">' . __( 'Euro', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'HKD', false ) . ' value="HKD">' . __( 'Hong Kong Dollar', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'HUF', false ) . ' value="HUF">' . __( 'Hungarian Forint', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'ILS', false ) . ' value="ILS">' . __( 'Israeli New Sheqel', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'JPY', false ) . ' value="JPY">' . __( 'Japanese Yen', 'wbk' ) . '</option>
					<option ' . selected( $value, 'MYR', false ) . ' value="MYR">' . __( 'Malaysian Ringgit', 'wbk' ) . '</option>
					<option ' . selected( $value, 'MXN', false ) . ' value="MXN">' . __( 'Mexican Peso', 'wbk' ) . '</option>
					<option ' . selected( $value, 'NOK', false ) . ' value="NOK">' . __( 'Norwegian Krone', 'wbk' ) . '</option>
					<option ' . selected( $value, 'NZD', false ) . ' value="NZD">' . __( 'New Zealand Dollar', 'wbk' ) . '</option>
					<option ' . selected( $value, 'PHP', false ) . ' value="PHP">' . __( 'Philippine Peso', 'wbk' ) . '</option>
					<option ' . selected( $value, 'PLN', false ) . ' value="PLN">' . __( 'Polish Zloty', 'wbk' ) . '</option>
					<option ' . selected( $value, 'GBP', false ) . ' value="GBP">' . __( 'Pound Sterling', 'wbk' ) . '</option>
					<option ' . selected( $value, 'SGD', false ) . ' value="SGD">' . __( 'Singapore Dollar', 'wbk' ) . '</option>
					<option ' . selected( $value, 'SEK', false ) . ' value="SEK">' . __( 'Swedish Krona', 'wbk' ) . '</option>
					<option ' . selected( $value, 'CHF', false ) . ' value="CHF">' . __( 'Swiss Franc', 'wbk' ) . '</option>
					<option ' . selected( $value, 'TWD', false ) . ' value="TWD">' . __( 'Taiwan New Dollar', 'wbk' ) . '</option>
					<option ' . selected( $value, 'THB', false ) . ' value="THB">' . __( 'Thai Baht', 'wbk' ) . '</option>
					<option ' . selected( $value, 'USD', false ) . ' value="USD">' . __( 'U.S. Dollar', 'wbk' ) . '</option>
   				 </select>';
        echo  $html ;
    }
    
    // validate mode
    public function validate_paypal_currency( $input )
    {
        return $input;
    }
    
    // render paypal tax
    public function render_paypal_tax()
    {
        $value = get_option( 'wbk_paypal_tax', 0 );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_paypal_tax" name="wbk_paypal_tax" value="' . $value . '" > %';
        $html .= '<p class="description">' . __( 'The tax used for payments made with PayPal. Percentage of an amount', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // paypal tax
    public function validate_paypal_tax( $input )
    {
        $input = sanitize_text_field( $input );
        
        if ( is_numeric( $input ) ) {
            
            if ( $input < 0 || $input > 100 ) {
                $input = 0;
                add_settings_error(
                    'wbk_paypal_tax',
                    'wbk_paypal_tax_error',
                    __( 'PayPal updated', 'wbk' ),
                    'updated'
                );
            }
        
        } else {
            $input = 0;
            add_settings_error(
                'wbk_paypal_tax',
                'wbk_paypal_tax_error',
                __( 'PayPal tax updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    public function render_paypal_redirect_url()
    {
        $value = get_option( 'wbk_paypal_redirect_url', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_paypal_redirect_url" name="wbk_paypal_redirect_url" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Redirect URL for successful payment. Leave empty to use page with booking form.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function validate_paypal_redirect_url( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render stripe tax
    public function render_stripe_tax()
    {
        $value = get_option( 'wbk_stripe_tax', 0 );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_stripe_tax" name="wbk_stripe_tax" value="' . $value . '" > %';
        $html .= '<p class="description">' . __( 'The tax used for payments made with Stripe. Percentage of an amount', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function render_paypal_multiplier()
    {
        $value = get_option( 'wbk_paypal_multiplier', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_paypal_multiplier" name="wbk_paypal_multiplier" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Use this option if you need to update price before data sent to PayPal.', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'This option should be used if the price of your service is set in a currency not supported by PayPal and you need to convert it to currency supported by PayPal before checkout.', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'Leave empty to not convert the currency.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function validate_paypal_multiplier( $input )
    {
        $input = sanitize_text_field( $input );
        
        if ( filter_var( $input, FILTER_VALIDATE_FLOAT ) && $input > 0 ) {
            return $input;
        } else {
            return '';
        }
    
    }
    
    // validate stripe tax
    public function validate_stripe_tax( $input )
    {
        $input = sanitize_text_field( $input );
        
        if ( is_numeric( $input ) ) {
            
            if ( $input < 0 || $input > 100 ) {
                $input = 0;
                add_settings_error(
                    'wbk_stripe_tax',
                    'wbk_stripe_tax_error',
                    __( 'Stripe tax updated', 'wbk' ),
                    'updated'
                );
            }
        
        } else {
            $input = 0;
            add_settings_error(
                'wbk_stripe_tax_tax',
                'wbk_stripe_tax_error',
                __( 'Stripe tax updated', 'wbk' ),
                'updated'
            );
        }
        
        return $input;
    }
    
    public function render_stripe_additional_fields()
    {
        $value = get_option( 'wbk_stripe_additional_fields', '' );
        if ( !is_array( $value ) ) {
            $value = array();
        }
        $payment_fields = WBK_Db_Utils::getPaymentFields();
        echo  '<select class="wbk_stripe_additional_fields" name="wbk_stripe_additional_fields[]"  id="wbk_stripe_additional_fields[]" Multiple>' ;
        foreach ( $payment_fields as $key => $field ) {
            
            if ( in_array( $key, $value ) ) {
                $selected = ' selected ';
            } else {
                $selected = '';
            }
            
            echo  '<option ' . $selected . '  value="' . $key . '">' . $field . '</option>' ;
        }
        echo  '</select>' ;
        echo  '<p class="description">' . __( 'Select fields required for Stripe payment (optional)', 'wbk' ) . '</p>' ;
    }
    
    public function validate_stripe_additional_fields( $input )
    {
        return $input;
    }
    
    public function render_stripe_redirect_url()
    {
        $value = get_option( 'wbk_stripe_redirect_url', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_stripe_redirect_url" name="wbk_stripe_redirect_url" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Redirect URL for successful payment. Leave empty to not redirect.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function validate_stripe_redirect_url( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    public function render_stripe_mob_font_size()
    {
        $value = get_option( 'wbk_stripe_mob_font_size', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_stripe_mob_font_size" name="wbk_stripe_mob_font_size" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Leave empty to use default input field font size', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function validate_stripe_mob_font_size( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    public function render_woo_product_id()
    {
        $value = get_option( 'wbk_woo_product_id', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_woo_product_id" name="wbk_woo_product_id" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'The id of the product used for booking (should be a simple product).', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function validate_woo_product_id( $input )
    {
        $input = sanitize_text_field( $input );
        
        if ( is_numeric( $input ) ) {
            return $input;
        } else {
            return '';
        }
    
    }
    
    public function render_woo_auto_add_to_cart()
    {
        $value = get_option( 'wbk_woo_auto_add_to_cart', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_woo_auto_add_to_cart" name="wbk_woo_auto_add_to_cart">
					<option ' . selected( $value, 'enabled', false ) . ' value="enabled">' . __( 'Enabled', 'wbk' ) . '</option>
					<option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Disabled', 'wbk' ) . '</option>
				 </select>';
        echo  $html ;
    }
    
    public function validate_woo_auto_add_to_cart( $input )
    {
        $input = trim( $input );
        return $input;
        $value = sanitize_text_field( $value );
        if ( $value != 'enabled' && $value != 'disabled' ) {
            $value = 'disabled';
        }
        return $input;
    }
    
    public function render_woo_check_coupons_inwebba()
    {
        $value = get_option( 'wbk_woo_check_coupons_inwebba', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_woo_check_coupons_inwebba" name="wbk_woo_check_coupons_inwebba">
					<option ' . selected( $value, 'enabled', false ) . ' value="enabled">' . __( 'Enabled', 'wbk' ) . '</option>
					<option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Disabled', 'wbk' ) . '</option>
				 </select>';
        echo  $html ;
    }
    
    public function validate_woo_check_coupons_inwebba( $input )
    {
        $input = trim( $input );
        return $input;
        $value = sanitize_text_field( $value );
        if ( $value != 'enabled' && $value != 'disabled' ) {
            $value = 'disabled';
        }
        return $input;
    }
    
    public function render_woo_update_status()
    {
        $value = get_option( 'wbk_woo_update_status', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_woo_update_status" name="wbk_woo_update_status">
					<option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Disabled (do not update status)', 'wbk' ) . '</option>
					<option ' . selected( $value, 'approved', false ) . ' value="approved">' . __( 'Approved', 'wbk' ) . '</option>
					<option ' . selected( $value, 'paid', false ) . ' value="paid">' . __( 'Paid (awaiting approval)', 'wbk' ) . '</option>
					<option ' . selected( $value, 'paid_approved', false ) . ' value="paid_approved">' . __( 'Paid (approved)', 'wbk' ) . '</option>
				 </select>';
        echo  $html ;
    }
    
    public function validate_woo_update_status( $input )
    {
        $input = trim( $input );
        return $input;
    }
    
    public function render_product_meta_key()
    {
        $value = get_option( 'wbk_product_meta_key', __( 'Appointments', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_product_meta_key" name="wbk_product_meta_key" value="' . $value . '" >';
        echo  $html ;
    }
    
    public function validate_product_meta_key( $input )
    {
        $input = sanitize_text_field( trim( $input ) );
        return $input;
    }
    
    public function render_woo_button_text()
    {
        $value = get_option( 'wbk_woo_button_text', __( 'Add to cart', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_woo_button_text" name="wbk_woo_button_text" value="' . $value . '" >';
        echo  $html ;
    }
    
    public function validate_woo_button_text( $input )
    {
        $input = sanitize_text_field( trim( $input ) );
        return $input;
    }
    
    // render stripe publishable key
    public function render_stripe_publishable_key()
    {
        $value = get_option( 'wbk_stripe_publishable_key', '' );
        $html = '<input type="text" id="wbk_stripe_publishable_key" name="wbk_stripe_publishable_key" value="' . $value . '">
  				<a href="https://stripe.com/docs/keys" rel="noopener"  target="_blank" ><span class="dashicons dashicons-editor-help"></span></a>';
        echo  $html ;
    }
    
    public function render_woo_error_add_to_cart()
    {
        $value = get_option( 'wbk_woo_error_add_to_cart', __( 'Booking not added to card', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_woo_error_add_to_cart" name="wbk_woo_error_add_to_cart" value="' . $value . '" >';
        echo  $html ;
    }
    
    public function validate_woo_error_add_to_cart( $input )
    {
        $input = sanitize_text_field( trim( $input ) );
        return $input;
    }
    
    public function render_day_label()
    {
        $value = get_option( 'wbk_day_label', '#date' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_day_label" name="wbk_day_label" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Available placeholders', 'wbk' ) . ': #date, #local_date</p>';
        echo  $html ;
    }
    
    public function validate_day_label( $input )
    {
        $input = sanitize_text_field( trim( $input ) );
        return $input;
    }
    
    public function render_validation_error_message()
    {
        $value = get_option( 'wbk_validation_error_message', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_validation_error_message" name="wbk_validation_error_message" value="' . $value . '" >';
        echo  $html ;
    }
    
    public function validate_validation_error_message( $input )
    {
        $input = sanitize_text_field( trim( $input ) );
        return $input;
    }
    
    public function render_daily_limit_reached_message()
    {
        $value = get_option( 'wbk_daily_limit_reached_message', __( 'Daily booking limit is reached, please select another date.', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_daily_limit_reached_message" name="wbk_daily_limit_reached_message" value="' . $value . '" >';
        echo  $html ;
    }
    
    public function validate_daily_limit_reached_message( $input )
    {
        $input = sanitize_text_field( trim( $input ) );
        return $input;
    }
    
    public function render_limit_by_email_reached_message()
    {
        $value = get_option( 'wbk_limit_by_email_reached_message', __( 'You have reached your booking limit.', 'wbk' ) );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_limit_by_email_reached_message" name="wbk_limit_by_email_reached_message" value="' . $value . '" >';
        echo  $html ;
    }
    
    public function validate_limit_by_email_reached_message( $input )
    {
        $input = sanitize_text_field( trim( $input ) );
        return $input;
    }
    
    // validate stripe publishable key
    public function validate_stripe_publishable_key( $input )
    {
        return $input;
    }
    
    // render stripe secret key
    public function render_stripe_secret_key()
    {
        $value = get_option( 'wbk_stripe_secret_key', '' );
        $html = '<input type="password" id="wbk_stripe_secret_key" name="wbk_stripe_secret_key" value="' . $value . '">
				<a href="https://stripe.com/docs/keys" rel="noopener"  target="_blank" ><span class="dashicons dashicons-editor-help"></span></a>';
        echo  $html ;
    }
    
    // validate stripe secret key
    public function validate_stripe_secret_key( $input )
    {
        return $input;
    }
    
    // render stripe currency
    public function render_stripe_currency()
    {
        $value = get_option( 'wbk_stripe_currency', 'USD' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_stripe_currency" name="wbk_stripe_currency">';
        foreach ( WBK_Stripe::getCurrencies() as $currency ) {
            $html .= '<option  ' . selected( $value, $currency, false ) . ' value="' . $currency . '">' . $currency . '</option>';
        }
        $html .= '</select>';
        echo  $html ;
    }
    
    // validate stripe hide adress
    public function validate_stripe_currency( $input )
    {
        if ( !in_array( $input, WBK_Stripe::getCurrencies() ) ) {
            return 'USD';
        }
        return $input;
    }
    
    // render paypal hide adress
    public function render_paypal_hide_address()
    {
        $value = get_option( 'wbk_paypal_hide_address', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_paypal_hide_address" name="wbk_paypal_hide_address">
				    <option ' . selected( $value, 'enabled', false ) . ' value="enabled">' . __( 'Enabled', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Disabled', 'wbk' ) . '</option>
   				 </select>';
        $html .= '<p class="description">' . __( 'Enable this option to hide adress on PayPal checkout.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    // validate paypal hide adress
    public function validate_paypal_hide_address( $input )
    {
        $input = trim( $input );
        return $input;
        $value = sanitize_text_field( $value );
        if ( $value != 'enabled' && $value != 'disabled' ) {
            $value = 'disabled';
        }
        return $input;
    }
    
    public function render_paypal_auto_redirect()
    {
        $value = get_option( 'wbk_paypal_auto_redirect', 'disabled' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_paypal_auto_redirect" name="wbk_paypal_auto_redirect">
			    <option ' . selected( $value, 'enabled', false ) . ' value="enabled">' . __( 'Enabled', 'wbk' ) . '</option>
			    <option ' . selected( $value, 'disabled', false ) . ' value="disabled">' . __( 'Disabled', 'wbk' ) . '</option>
				 </select>';
        echo  $html ;
    }
    
    public function validate_paypal_auto_redirect( $input )
    {
        $input = trim( $input );
        return $input;
        $value = sanitize_text_field( $value );
        if ( $value != 'enabled' && $value != 'disabled' ) {
            $value = 'disabled';
        }
        return $input;
    }
    
    // paypal options functions end ******************************************************************************************************
    // render gg client id
    public function render_gg_clientid()
    {
        $value = get_option( 'wbk_gg_clientid', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="text" id="wbk_gg_clientid" name="wbk_gg_clientid" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate gg client id
    public function validate_gg_clientid( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    // render gg secret
    public function render_gg_secret()
    {
        $value = get_option( 'wbk_gg_secret', '' );
        $value = sanitize_text_field( $value );
        $html = '<input type="password" id="wbk_gg_secret" name="wbk_gg_secret" value="' . $value . '" >';
        echo  $html ;
    }
    
    // validate gg secret
    public function validate_gg_secret( $input )
    {
        $input = sanitize_text_field( $input );
        return $input;
    }
    
    public function render_gg_sync_cache_time()
    {
        $value = get_option( 'wbk_gg_sync_cache_time', '0' );
        $value = sanitize_text_field( $value );
        $token = get_option( 'wbk_gg_cache_token', '' );
        
        if ( $token == '' ) {
            $token = uniqid();
            add_option( 'wbk_gg_cache_token', $token );
        }
        
        $html = '<input type="text" id="wbk_gg_sync_cache_time" name="wbk_gg_sync_cache_time" value="' . $value . '" >' . __( 'Minutes', 'wbk' );
        $html .= '<p class="description">' . __( 'IMPORTANT: DO NOT ENABLE CACHE IF THERE IS NO ISSUES WITH THE PERFORMANCE', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'Set 0 to disable caching.', 'wbk' ) . '</p>';
        $html .= '<p class="description">' . __( 'To regularly update the caching data, add the following URI to the cron job: ', 'wbk' ) . rtrim( get_site_url(), '/' ) . '/?wbkrefresh=' . $token . '</p>';
        $html .= '<p class="description">' . __( 'The regularity of the cron job should be less than the "Refresh cache after" option.', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function validate_gg_sync_recurrence( $input )
    {
        $input = intval( trim( sanitize_text_field( $input ) ) );
        if ( $input < 0 || $input > 86400 ) {
            $input = 600;
        }
        return $input;
    }
    
    public function render_gg_created_by()
    {
        $value = get_option( 'wbk_gg_created_by', 'webba_booking' );
        $html = '<input type="text" id="wbk_gg_created_by" name="wbk_gg_created_by" value="' . $value . '" >';
        $html .= '<p class="description">' . __( 'Do not change this option if you do not plan to use the same Google calendars on different domains', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function validate_gg_created_by( $input )
    {
        $input = trim( sanitize_text_field( $input ) );
        if ( $input == '' ) {
            $input = 'webba_booking';
        }
        return $input;
    }
    
    public function render_gg_customers_time_zone()
    {
        $value = get_option( 'wbk_gg_customers_time_zone', 'webba' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_gg_customers_time_zone" name="wbk_gg_customers_time_zone">
				    <option ' . selected( $value, 'webba', false ) . ' value="webba">' . __( 'Use Webba Booking time zone', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'customer', false ) . ' value="customer">' . __( 'Use customer\'s calendar time zone', 'wbk' ) . '</option>
					 </select>';
        $html .= '<p class="description">' . __( 'Choose the time zone used for the event\'s added to the customer\'s calendar', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function validate_gg_customers_time_zone( $input )
    {
        if ( $input != 'webba' && $input != 'customer' ) {
            $input = 'webba';
        }
        return $input;
    }
    
    public function render_gg_when_add()
    {
        $value = get_option( 'wbk_gg_when_add', 'onbooking' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_gg_when_add" name="wbk_gg_when_add">
				    <option ' . selected( $value, 'onbooking', false ) . ' value="onbooking">' . __( 'appointment booked', 'wbk' ) . '</option>
				    <option ' . selected( $value, 'onpaymentorapproval', false ) . ' value="onpaymentorapproval">' . __( 'appointment paid or approved', 'wbk' ) . '</option>
					 </select>';
        $html .= '<p class="description">' . __( 'Choose when to add events in administrator\'s Google Calendar', 'wbk' ) . '</p>';
        echo  $html ;
    }
    
    public function validate_gg_when_add( $input )
    {
        if ( $input != 'onbooking' && $input != 'onpaymentorapproval' ) {
            $input = 'onbooking';
        }
        return $input;
    }
    
    public function render_gg_2way_group()
    {
        $value = get_option( 'wbk_gg_2way_group', 'lock' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_gg_2way_group" name="wbk_gg_2way_group">
                    <option ' . selected( $value, 'lock', false ) . ' value="lock">' . __( 'lock time slot', 'wbk' ) . '</option>
                    <option ' . selected( $value, 'reduce', false ) . ' value="reduce">' . __( 'reduce count of available places', 'wbk' ) . '</option>
                 </select>';
        echo  $html ;
    }
    
    public function validate_gg_2way_group( $input )
    {
        $input = trim( $input );
        $input = sanitize_text_field( $input );
        if ( $input != 'lock' && $input != 'reduce' ) {
            $input = 'reduce';
        }
        return $input;
    }
    
    public function render_gg_ignore_free()
    {
        $value = get_option( 'wbk_gg_ignore_free', 'no' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_gg_ignore_free" name="wbk_gg_ignore_free">
                    <option ' . selected( $value, 'no', false ) . ' value="no">' . __( 'No', 'wbk' ) . '</option>
                    <option ' . selected( $value, 'yes', false ) . ' value="yes">' . __( 'Yes', 'wbk' ) . '</option>
                 </select>';
        echo  $html ;
    }
    
    public function validate_gg_ignore_free( $input )
    {
        $input = trim( $input );
        $input = sanitize_text_field( $input );
        if ( $input != 'yes' && $input != 'no' ) {
            $input = 'no';
        }
        return $input;
    }
    
    public function render_gg_send_alerts_to_admin()
    {
        $value = get_option( 'wbk_gg_send_alerts_to_admin', 'no' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_gg_send_alerts_to_admin" name="wbk_gg_send_alerts_to_admin">
	                <option ' . selected( $value, 'no', false ) . ' value="no">' . __( 'No', 'wbk' ) . '</option>
	                <option ' . selected( $value, 'yes', false ) . ' value="yes">' . __( 'Yes', 'wbk' ) . '</option>
	             </select>';
        echo  $html ;
    }
    
    public function validate_gg_send_alerts_to_admin( $input )
    {
        $input = trim( $input );
        $input = sanitize_text_field( $input );
        if ( $input != 'yes' && $input != 'no' ) {
            $input = 'no';
        }
        return $input;
    }
    
    public function render_gg_group_export()
    {
        $value = get_option( 'wbk_gg_group_service_export', 'event_foreach_appointment' );
        $value = sanitize_text_field( $value );
        $html = '<select id="wbk_gg_group_service_export" name="wbk_gg_group_service_export">
					<option ' . selected( $value, 'one_event', false ) . ' value="one_event">' . __( 'Add one event', 'wbk' ) . '</option>
					<option ' . selected( $value, 'event_foreach_appointment', false ) . ' value="event_foreach_appointment">' . __( 'Add event for each appointment', 'wbk' ) . '</option>
				 </select>';
        echo  $html ;
    }
    
    public function validate_gg_group_export( $input )
    {
        return $input;
    }

}
function wbk_default_editor( $param )
{
    return 'tinymce';
}
