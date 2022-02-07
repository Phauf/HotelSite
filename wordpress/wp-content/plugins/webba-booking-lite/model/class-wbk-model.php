<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class WBK_Model
{
    public function __construct()
    {
        add_action( 'init', array( $this, 'initalize_model' ), 20 );
    }
    
    public function initalize_model()
    {
        global  $wpdb ;
        $db_prefix = $wpdb->prefix;
        update_option( 'wbk_db_prefix', $db_prefix );
        WBK_Model_Updater::update_table_names();
        // create tables if not created
        WBK_Db_Utils::createTables();
        $db_prefix = get_option( 'wbk_db_prefix', '' );
        $table = new Plugion\Table( $db_prefix . 'wbk_services' );
        $table->set_single_item_name( __( 'service', 'wbk' ) );
        $table->set_multiple_item_name( __( 'services', 'wbk' ) );
        $table->sections['general'] = __( 'General', 'wbk' );
        $table->add_field(
            'service_name',
            'name',
            __( 'Service name', 'wbk' ),
            'text',
            'general'
        );
        $table->add_field(
            'service_description',
            'description',
            __( 'Description', 'wbk' ),
            'editor',
            'general',
            null,
            '',
            true,
            false,
            false
        );
        $table->add_field(
            'service_priority',
            'priority',
            __( 'Priority', 'wbk' ),
            'text',
            'general',
            array(
            'type' => 'none_negative_integer',
        ),
            '0'
        );
        $args = array(
            'post_type'      => 'wpcf7_contact_form',
            'posts_per_page' => -1,
        );
        $forms = array();
        if ( $cf7_forms = get_posts( $args ) ) {
            foreach ( $cf7_forms as $cf7_form ) {
                $form = new stdClass();
                $form->name = $cf7_form->post_title;
                $form->id = $cf7_form->ID;
                $forms[$cf7_form->ID] = $cf7_form->post_title;
            }
        }
        $table->add_field(
            'service_form',
            'form',
            __( 'Booking form', 'wbk' ),
            'select',
            'general',
            array(
            'items'      => $forms,
            'null_value' => array(
            '0' => __( 'Default form', 'wbk' ),
        ),
        ),
            '0',
            true,
            false,
            false
        );
        $table->add_field(
            'service_gg_calendars',
            'gg_calendars',
            __( 'Google calendar', 'wbk' ),
            'select',
            'general',
            array(
            'items'    => WBK_Model_Utils::get_google_calendars(),
            'multiple' => true,
        ),
            null,
            true,
            false,
            false
        );
        $table->add_field(
            'service_users',
            'users',
            __( 'Users', 'wbk' ),
            'select',
            'general',
            array(
            'items'    => WBK_User_Utils::get_none_admin_wp_users(),
            'multiple' => true,
        ),
            0,
            true,
            true,
            false
        );
        $business_hours_default = '{\'dow_availability\':[
                                    {\'start\':\'32400\',\'end\':\'46800\',\'day_of_week\':\'1\',\'status\':\'active\'},
                                    {\'start\':\'50400\',\'end\':\'64800\',\'day_of_week\':\'1\',\'status\':\'active\'},
                                    {\'start\':\'32400\',\'end\':\'46800\',\'day_of_week\':\'2\',\'status\':\'active\'},
                                    {\'start\':\'50400\',\'end\':\'64800\',\'day_of_week\':\'2\',\'status\':\'active\'},
                                    {\'start\':\'32400\',\'end\':\'46800\',\'day_of_week\':\'3\',\'status\':\'active\'},
                                    {\'start\':\'50400\',\'end\':\'64800\',\'day_of_week\':\'3\',\'status\':\'active\'},
                                    {\'start\':\'32400\',\'end\':\'46800\',\'day_of_week\':\'4\',\'status\':\'active\'},
                                    {\'start\':\'50400\',\'end\':\'64800\',\'day_of_week\':\'4\',\'status\':\'active\'},
                                    {\'start\':\'32400\',\'end\':\'46800\',\'day_of_week\':\'5\',\'status\':\'active\'},
                                    {\'start\':\'50400\',\'end\':\'64800\',\'day_of_week\':\'5\',\'status\':\'active\'}]}';
        $table->add_field(
            'service_business_hours',
            'business_hours_v4',
            __( 'Business hours', 'wbk' ),
            'wbk_business_hours',
            'general',
            null,
            $business_hours_default,
            true,
            false,
            false
        );
        $table->add_field(
            'service_date_range',
            'date_range',
            __( 'Availability date range', 'wbk' ),
            'date_range',
            'general',
            array(
            'time_zone' => get_option( 'wbk_timezone', 'UTC' ),
        ),
            '',
            true,
            false,
            false
        );
        $table->add_field(
            'service_min_quantity',
            'min_quantity',
            __( 'Minimum booking count per time slot', 'wbk' ),
            'text',
            'general',
            array(
            'type' => 'positive_integer',
        ),
            '1',
            true,
            false
        );
        $table->add_field(
            'service_quantity',
            'quantity',
            __( 'Maximum booking count per time slot', 'wbk' ),
            'text',
            'general',
            array(
            'type' => 'positive_integer',
        ),
            '1',
            true,
            false
        );
        $table->add_field(
            'service_prepare_time',
            'prepare_time',
            __( 'Preparation time (minutes)', 'wbk' ),
            'text',
            'general',
            array(
            'type' => 'none_negative_integer',
        ),
            '0'
        );
        $table->add_field(
            'service_duration',
            'duration',
            __( 'Duration (minutes)', 'wbk' ),
            'text',
            'general',
            array(
            'type' => 'positive_integer',
        ),
            '30'
        );
        $table->add_field(
            'service_interval_between',
            'interval_between',
            __( 'Gap (minutes)', 'wbk' ),
            'text',
            'general',
            array(
            'type' => 'none_negative_integer',
        ),
            '0'
        );
        $table->add_field(
            'service_step',
            'step',
            __( 'Step (minutes)', 'wbk' ),
            'text',
            'general',
            array(
            'type' => 'positive_integer',
        ),
            '30'
        );
        $payment_methods = array(
            'arrival' => 'On arrival',
            'bank'    => 'Bank transfer',
        );
        $table->add_field(
            'service_email',
            'email',
            __( 'Email', 'wbk' ),
            'text',
            'general',
            array(
            'type' => 'email',
        ),
            get_option( 'new_admin_email' )
        );
        $table->add_field(
            'service_notification_template',
            'notification_template',
            __( '\'On Booking\' notification template', 'wbk' ),
            'select',
            'general',
            array(
            'items'      => WBK_Model_Utils::get_email_templates(),
            'null_value' => array(
            '0' => __( 'Default', 'wbk' ),
        ),
        ),
            '0',
            true,
            false,
            false
        );
        $table->add_field(
            'service_reminder_template',
            'reminder_template',
            __( 'Reminder notification template', 'wbk' ),
            'select',
            'general',
            array(
            'items'      => WBK_Model_Utils::get_email_templates(),
            'null_value' => array(
            '0' => __( 'Default', 'wbk' ),
        ),
        ),
            '0',
            true,
            false,
            false
        );
        $table->add_field(
            'service_invoice_template',
            'invoice_template',
            __( 'Invoice notification template', 'wbk' ),
            'select',
            'general',
            array(
            'items'      => WBK_Model_Utils::get_email_templates(),
            'null_value' => array(
            '0' => __( 'Default', 'wbk' ),
        ),
        ),
            '0',
            true,
            false,
            false
        );
        $table->add_field(
            'service_booking_changed_template',
            'booking_changed_template',
            __( 'Booking changes template', 'wbk' ),
            'select',
            'general',
            array(
            'items'      => WBK_Model_Utils::get_email_templates(),
            'null_value' => array(
            '0' => __( 'Default', 'wbk' ),
        ),
        ),
            '0',
            true,
            false,
            false
        );
        $table->add_field(
            'service_payment_methods',
            'payment_methods',
            __( 'Payment methods', 'wbk' ),
            'select',
            'general',
            array(
            'items'    => $payment_methods,
            'multiple' => true,
        ),
            null,
            true,
            false,
            false
        );
        $table->add_field(
            'service_price',
            'price',
            __( 'Price', 'wbk' ),
            'text',
            'general',
            array(
            'type' => 'none_negative_float',
        ),
            '0',
            true,
            true,
            false
        );
        $table->add_field(
            'service_service_fee',
            'service_fee',
            __( 'Add amount to order (deposit)', 'wbk' ),
            'text',
            'general',
            array(
            'type' => 'none_negative_float',
        ),
            '0',
            true,
            true,
            false
        );
        $table->add_field(
            'service_pricing_rules',
            'pricing_rules',
            __( 'Pricing rules', 'wbk' ),
            'select',
            'general',
            array(
            'items'    => WBK_Model_Utils::get_pricing_rules(),
            'multiple' => true,
        ),
            null,
            true,
            false,
            false
        );
        $table->add_field(
            'service_multi_mode_low_limit',
            'multi_mode_low_limit',
            __( 'Lower limit for multiple mode', 'wbk' ),
            'text',
            'general',
            array(
            'type' => 'none_negative_integer',
        ),
            '',
            true,
            false,
            false
        );
        $table->add_field(
            'service_multi_mode_limit',
            'multi_mode_limit',
            __( 'Upper limit for multiple mode', 'wbk' ),
            'text',
            'general',
            array(
            'type' => 'none_negative_integer',
        ),
            '',
            true,
            false,
            false
        );
        $table->sync_structure();
        if ( $table->fields->get_element_at( 'service_extcalendar_group_mode' ) != false ) {
            $table->fields->get_element_at( 'service_extcalendar_group_mode' )->set_dependency( array( array( 'quantity', '>', '1' ), array( 'extcalendar', '!=', '' ) ) );
        }
        Plugion()->tables->add( $table, $db_prefix . 'wbk_services' );
        // Service categories
        $table = new Plugion\Table( $db_prefix . 'wbk_service_categories' );
        $table->set_single_item_name( __( 'Service category', 'wbk' ) );
        $table->set_multiple_item_name( __( 'Service categories', 'wbk' ) );
        $table->sections['name'] = __( 'Category name', 'wbk' );
        $table->sections['category_list'] = __( 'Services', 'wbk' );
        $table->add_field(
            'category_name',
            'name',
            __( 'Category name', 'wbk' ),
            'text',
            'general'
        );
        $table->add_field(
            'category_list',
            'category_list',
            __( 'Services', 'wbk' ),
            'select',
            'general',
            array(
            'items'    => WBK_Model_Utils::get_services(),
            'multiple' => true,
        ),
            null,
            true,
            true,
            false
        );
        $table->sync_structure();
        Plugion()->tables->add( $table, $db_prefix . 'wbk_service_categories' );
        // Email templates
        $table = new Plugion\Table( $db_prefix . 'wbk_email_templates' );
        $table->set_single_item_name( __( 'Email template', 'wbk' ) );
        $table->set_multiple_item_name( __( 'Email templates', 'wbk' ) );
        $table->add_field(
            'name',
            'name',
            __( 'Name', 'wbk' ),
            'text',
            ''
        );
        $table->add_field(
            'template',
            'template',
            __( 'Template', 'wbk' ),
            'editor',
            '',
            null,
            '',
            true,
            false,
            false
        );
        $table->sync_structure();
        Plugion()->tables->add( $table, $db_prefix . 'wbk_email_templates' );
        // Appointments
        $date_format = get_option( 'wbk_date_format_backend', 'y-m-d' );
        $time_format = get_option( 'wbk_time_format', '' );
        if ( $time_format == '' ) {
            $time_format = get_option( 'time_format' );
        }
        $allowed_fields = get_option( 'wbk_appointments_table_columns', '' );
        if ( !is_array( $allowed_fields ) ) {
            $allowed_fields = WBK_Model_Utils::get_appointment_columns( true );
        }
        $table = new Plugion\Table( $db_prefix . 'wbk_appointments' );
        $table->set_single_item_name( __( 'Appointment', 'wbk' ) );
        $table->set_multiple_item_name( __( 'Appointments', 'wbk' ) );
        $table->set_duplicatable( false );
        $table->add_field(
            'appointment_service_id',
            'service_id',
            __( 'Service', 'wbk' ),
            'select',
            '',
            array(
            'items' => WBK_Model_Utils::get_services( true ),
            'type'  => 'positive_integer',
        ),
            null,
            true,
            in_array( 'service_id', $allowed_fields ),
            true
        );
        $table->add_field(
            'appointment_created_on',
            'created_on',
            __( 'Created on', 'wbk' ),
            'text',
            '',
            array(
            'type' => 'positive_integer',
        ),
            '',
            false,
            in_array( 'created_on', $allowed_fields ),
            false
        );
        $table->add_field(
            'appointment_day',
            'day',
            __( 'Date', 'wbk' ),
            'wbk_date',
            '',
            array(
            'date_format' => $date_format,
            'time_zone'   => get_option( 'wbk_timezone', 'UTC' ),
        ),
            '',
            true,
            in_array( 'day', $allowed_fields )
        );
        $table->add_field(
            'appointment_time',
            'time',
            __( 'Time', 'wbk' ),
            'wbk_time',
            '',
            array(
            'time_format' => $time_format,
            'time_zone'   => get_option( 'wbk_timezone', 'UTC' ),
        ),
            '',
            true,
            in_array( 'time', $allowed_fields )
        );
        $table->add_field(
            'appointment_quantity',
            'quantity',
            __( 'Places booked', 'wbk' ),
            'select',
            '',
            array(
            'type'  => 'positive_integer',
            'items' => array(),
        ),
            null,
            true,
            in_array( 'quantity', $allowed_fields ),
            true
        );
        $table->add_field(
            'appointment_name',
            'name',
            __( 'Name', 'wbk' ),
            'text',
            '',
            null,
            '',
            true,
            in_array( 'name', $allowed_fields )
        );
        $table->add_field(
            'appointment_email',
            'email',
            __( 'Email', 'wbk' ),
            'text',
            '',
            array(
            'type' => 'email',
        ),
            '',
            true,
            in_array( 'email', $allowed_fields )
        );
        $table->add_field(
            'appointment_phone',
            'phone',
            __( 'Phone', 'wbk' ),
            'text',
            '',
            null,
            '',
            true,
            in_array( 'phone', $allowed_fields ),
            false
        );
        $table->add_field(
            'appointment_description',
            'description',
            __( 'Comment', 'wbk' ),
            'textarea',
            '',
            null,
            '',
            true,
            in_array( 'description', $allowed_fields ),
            false
        );
        $table->add_field(
            'appointment_extra',
            'extra',
            __( 'Custom fields', 'wbk' ),
            'wbk_app_custom_data',
            '',
            null,
            '',
            true,
            in_array( 'extra', $allowed_fields ),
            false
        );
        $table->add_field(
            'appointment_coupon',
            'coupon',
            'coupon',
            'text',
            '',
            array(
            'type' => 'positive_integer',
        ),
            '',
            false,
            in_array( 'coupon', $allowed_fields ),
            false
        );
        $table->add_field(
            'appointment_payment_method',
            'payment_method',
            __( 'Payment method', 'wbk' ),
            'text',
            '',
            null,
            '',
            false,
            in_array( 'payment_method', $allowed_fields ),
            false
        );
        $table->add_field(
            'appointment_moment_price',
            'moment_price',
            'Item price',
            'text',
            '',
            null,
            '',
            true,
            in_array( 'moment_price', $allowed_fields ),
            false
        );
        $table->add_field(
            'appointment_user_ip',
            'user_ip',
            __( 'User IP' ),
            'text',
            '',
            null,
            '',
            false,
            in_array( 'ip', $allowed_fields ),
            true
        );
        $table->add_field(
            'appointment_status',
            'status',
            __( 'Status' ),
            'select',
            '',
            array(
            'items' => WBK_Model_Utils::get_appointment_status_list(),
        ),
            get_option( 'wbk_appointments_default_status', 'pending' ),
            true,
            in_array( 'status', $allowed_fields ),
            true
        );
        $table->add_field(
            'appointment_service_category',
            'service_category',
            'service_category',
            'text',
            '',
            null,
            '',
            false,
            false,
            false
        );
        $table->add_field(
            'appointment_lang',
            'lang',
            'lang',
            'text',
            '',
            null,
            '',
            false,
            false,
            false
        );
        $table->add_field(
            'appointment_duration',
            'duration',
            'duration',
            'text',
            '',
            array(
            'type' => 'positive_integer',
        ),
            '',
            false,
            false,
            false
        );
        $table->add_field(
            'appointment_end',
            'end',
            'end',
            'text',
            '',
            array(
            'type' => 'positive_integer',
        ),
            '',
            false,
            false,
            false
        );
        $table->add_field(
            'appointment_attachment',
            'attachment',
            'attachment',
            'textarea',
            '',
            null,
            '',
            false,
            false,
            false
        );
        $table->add_field(
            'appointment_payment_id',
            'payment_id',
            'payment_id',
            'text',
            '',
            null,
            '',
            false,
            false,
            false
        );
        $table->add_field(
            'appointment_token',
            'token',
            'token',
            'text',
            '',
            null,
            '',
            false,
            false,
            false
        );
        $table->add_field(
            'appointment_admin_token',
            'admin_token',
            'admin_token',
            'text',
            '',
            null,
            '',
            false,
            false,
            false
        );
        $table->add_field(
            'appointment_payment_cancel_token',
            'payment_cancel_token',
            'payment_cancel_token',
            'text',
            '',
            null,
            '',
            false,
            false,
            false
        );
        $table->add_field(
            'appointment_expiration_time',
            'expiration_time',
            'expiration_time',
            'text',
            '',
            array(
            'type' => 'positive_integer',
        ),
            '',
            false,
            false,
            false
        );
        $table->add_field(
            'appointment_time_offset',
            'time_offset',
            'time_offset',
            'text',
            '',
            array(
            'type' => 'integer',
        ),
            '',
            false,
            false,
            false
        );
        $table->add_field(
            'appointment_gg_event_id',
            'gg_event_id',
            'gg_event_id',
            'text',
            '',
            null,
            '',
            false,
            false,
            false
        );
        $table->add_field(
            'appointment_prev_status',
            'prev_status',
            'prev_status',
            'text',
            '',
            null,
            '',
            false,
            false,
            false
        );
        $table->add_field(
            'appointment_amount_details',
            'amount_details',
            'amount_details',
            'textarea',
            '',
            null,
            '',
            false,
            false,
            false
        );
        $table->sync_structure();
        $table = Plugion()->tables->add( $table, $db_prefix . 'wbk_appointments' );
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $start = date( 'm/d/Y' );
        $end = date( 'm/d/Y', strtotime( ' +' . get_option( 'wbk_filter_default_days_number', '14' ) . ' day' ) );
        $table->fields->get_element_at( 'appointment_day' )->set_filter_data(
            'wbk_date_range',
            [ '>=', '<=' ],
            [ $start, $end ],
            ' AND '
        );
        date_default_timezone_set( 'UTC' );
        $service_ids = WBK_Model_Utils::get_service_ids( true );
        $services = WBK_Model_Utils::get_services( true );
        $table->fields->get_element_at( 'appointment_service_id' )->set_filter_data(
            'multi_select',
            [ 'IN' ],
            $service_ids,
            '',
            $services
        );
        $table = new Plugion\Table( $db_prefix . 'wbk_cancelled_appointments' );
        $table->set_single_item_name( __( 'Appointment', 'wbk' ) );
        $table->set_multiple_item_name( __( 'Appointments', 'wbk' ) );
        $table->set_duplicatable( false );
        $table->add_field(
            'appointment_id_cancelled',
            'id_cancelled',
            __( 'ID of cancelled appointment', 'wbk' ),
            'text',
            '',
            array(
            'type' => 'positive_integer',
        ),
            '',
            false,
            true,
            false
        );
        $table->add_field(
            'appointment_cancelled_by',
            'cancelled_by',
            __( 'Cancelled by', 'wbk' ),
            'textarea',
            '',
            null,
            '',
            false,
            true,
            false
        );
        $table->add_field(
            'appointment_service_id',
            'service_id',
            __( 'Service', 'wbk' ),
            'select',
            '',
            array(
            'items' => WBK_Model_Utils::get_services(),
            'type'  => 'positive_integer',
        ),
            null,
            false,
            in_array( 'service_id', $allowed_fields ),
            false
        );
        $table->add_field(
            'appointment_created_on',
            'created_on',
            'created_on',
            'text',
            '',
            array(
            'type' => 'positive_integer',
        ),
            '',
            false,
            in_array( 'created_on', $allowed_fields ),
            false
        );
        $table->add_field(
            'appointment_day',
            'day',
            __( 'Date', 'wbk' ),
            'wbk_date',
            '',
            array(
            'date_format' => $date_format,
            'time_zone'   => get_option( 'wbk_timezone', 'UTC' ),
        ),
            '',
            false,
            in_array( 'day', $allowed_fields )
        );
        $table->add_field(
            'appointment_time',
            'time',
            __( 'Time', 'wbk' ),
            'wbk_time',
            '',
            array(
            'time_format' => $time_format,
            'time_zone'   => get_option( 'wbk_timezone', 'UTC' ),
        ),
            '',
            false,
            in_array( 'time', $allowed_fields )
        );
        $table->add_field(
            'appointment_quantity',
            'quantity',
            __( 'Places booked', 'wbk' ),
            'select',
            '',
            array(
            'type'  => 'positive_integer',
            'items' => array(),
        ),
            null,
            false,
            in_array( 'quantity', $allowed_fields ),
            true
        );
        $table->add_field(
            'appointment_name',
            'name',
            __( 'Name', 'wbk' ),
            'text',
            '',
            null,
            '',
            true,
            in_array( 'name', $allowed_fields )
        );
        $table->add_field(
            'appointment_email',
            'email',
            __( 'Email', 'wbk' ),
            'text',
            '',
            array(
            'type' => 'email',
        ),
            '',
            false,
            in_array( 'email', $allowed_fields )
        );
        $table->add_field(
            'appointment_phone',
            'phone',
            __( 'Phone', 'wbk' ),
            'text',
            '',
            null,
            '',
            false,
            in_array( 'phone', $allowed_fields ),
            false
        );
        $table->add_field(
            'appointment_description',
            'description',
            __( 'Comment', 'wbk' ),
            'textarea',
            '',
            null,
            '',
            true,
            in_array( 'description', $allowed_fields ),
            false
        );
        $table->add_field(
            'appointment_extra',
            'extra',
            __( 'Custom fields', 'wbk' ),
            'wbk_app_custom_data',
            '',
            null,
            '',
            false,
            in_array( 'extra', $allowed_fields ),
            false
        );
        $table->add_field(
            'appointment_coupon',
            'coupon',
            'coupon',
            'text',
            '',
            array(
            'type' => 'positive_integer',
        ),
            '',
            false,
            in_array( 'coupon', $allowed_fields ),
            false
        );
        $table->add_field(
            'appointment_payment_method',
            'payment_method',
            __( 'Payment method', 'wbk' ),
            'text',
            '',
            null,
            '',
            false,
            in_array( 'payment_method', $allowed_fields ),
            false
        );
        $table->add_field(
            'appointment_moment_price',
            'moment_price',
            'Price',
            'text',
            '',
            null,
            '',
            false,
            in_array( 'moment_price', $allowed_fields ),
            true
        );
        $table->add_field(
            'appointment_user_ip',
            'user_ip',
            __( 'User IP' ),
            'text',
            '',
            null,
            '',
            false,
            in_array( 'ip', $allowed_fields ),
            true
        );
        $table->add_field(
            'appointment_status',
            'status',
            __( 'Status' ),
            'select',
            '',
            array(
            'items' => WBK_Model_Utils::get_appointment_status_list(),
        ),
            '',
            false,
            in_array( 'status', $allowed_fields ),
            true
        );
        $table->add_field(
            'appointment_service_category',
            'service_category',
            'service_category',
            'text',
            '',
            null,
            '',
            false,
            false,
            false
        );
        $table->add_field(
            'appointment_lang',
            'lang',
            'lang',
            'text',
            '',
            null,
            '',
            false,
            false,
            false
        );
        $table->add_field(
            'appointment_duration',
            'duration',
            'duration',
            'text',
            '',
            array(
            'type' => 'positive_integer',
        ),
            '',
            false,
            false,
            false
        );
        $table->add_field(
            'appointment_attachment',
            'attachment',
            'attachment',
            'textarea',
            '',
            null,
            '',
            false,
            false,
            false
        );
        $table->add_field(
            'appointment_payment_id',
            'payment_id',
            'payment_id',
            'text',
            '',
            null,
            '',
            false,
            false,
            false
        );
        $table->add_field(
            'appointment_token',
            'token',
            'token',
            'text',
            '',
            null,
            '',
            false,
            false,
            false
        );
        $table->add_field(
            'appointment_admin_token',
            'admin_token',
            'admin_token',
            'text',
            '',
            null,
            '',
            false,
            false,
            false
        );
        $table->add_field(
            'appointment_payment_cancel_token',
            'payment_cancel_token',
            'payment_cancel_token',
            'text',
            '',
            null,
            '',
            false,
            false,
            false
        );
        $table->add_field(
            'appointment_expiration_time',
            'expiration_time',
            'expiration_time',
            'text',
            '',
            array(
            'type' => 'positive_integer',
        ),
            '',
            false,
            false,
            false
        );
        $table->add_field(
            'appointment_time_offset',
            'time_offset',
            'time_offset',
            'text',
            '',
            array(
            'type' => 'positive_integer',
        ),
            '',
            false,
            false,
            false
        );
        $table->add_field(
            'appointment_gg_event_id',
            'gg_event_id',
            'gg_event_id',
            'text',
            '',
            null,
            '',
            false,
            false,
            false
        );
        $table->sync_structure();
        $table = Plugion()->tables->add( $table, $db_prefix . 'wbk_cancelled_appointments' );
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $start = date( 'm/d/Y' );
        $end = date( 'm/d/Y', strtotime( ' +' . get_option( 'wbk_filter_default_days_number', '14' ) . ' day' ) );
        $table->fields->get_element_at( 'appointment_day' )->set_filter_data(
            'wbk_date_range',
            [ '>=', '<=' ],
            [ $start, $end ],
            ' AND '
        );
        date_default_timezone_set( 'UTC' );
        $service_ids = WBK_Model_Utils::get_service_ids( true );
        $services = WBK_Model_Utils::get_services( true );
        $table->fields->get_element_at( 'appointment_service_id' )->set_filter_data(
            'multi_select',
            [ 'IN' ],
            $service_ids,
            '',
            $services
        );
        // Google calendars
        $table = new Plugion\Table( $db_prefix . 'wbk_gg_calendars' );
        $table->set_duplicatable( false );
        $table->set_single_item_name( __( 'Google calendar', 'wbk' ) );
        $table->set_multiple_item_name( __( 'Google calendars', 'wbk' ) );
        $table->add_field(
            'calendar_name',
            'name',
            __( 'Name', 'wbk' ),
            'text'
        );
        $table->add_field(
            'calendar_user_id',
            'user_id',
            __( 'User', 'wbk' ),
            'select',
            '',
            array(
            'items'      => WBK_User_Utils::get_wp_users(),
            'null_value' => array(
            '0' => __( 'select option', 'wbk' ),
        ),
        ),
            0,
            true,
            true,
            false
        );
        $table->add_field(
            'calendar_id',
            'calendar_id',
            __( 'Calendar ID', 'wbk' ),
            'text'
        );
        $table->add_field(
            'calendar_mode',
            'mode',
            __( 'Mode', 'wbk' ),
            'select',
            '',
            array(
            'items' => WBK_Model_Utils::get_gg_calendar_modes(),
        ),
            null,
            true,
            true,
            true
        );
        $table->add_field(
            'calendar_access_token',
            'access_token',
            __( 'Authorization', 'wbk' ),
            'wbk_google_access_token',
            null,
            '',
            '',
            false
        );
        $table->sync_structure();
        Plugion()->tables->add( $table, $db_prefix . 'wbk_gg_calendars' );
        // Coupons
        $table = new Plugion\Table( $db_prefix . 'wbk_coupons' );
        $table->set_single_item_name( __( 'Coupon', 'wbk' ) );
        $table->set_multiple_item_name( __( 'Coupons', 'wbk' ) );
        $table->add_field(
            'coupon_name',
            'name',
            __( 'Coupon', 'wbk' ),
            'text',
            ''
        );
        $table->add_field(
            'coupon_date_range',
            'date_range',
            __( 'Available on', 'wbk' ),
            'date_range',
            '',
            array(
            'time_zone' => get_option( 'wbk_timezone', 'UTC' ),
        ),
            '',
            true,
            true,
            false
        );
        $table->add_field(
            'сoupon_services',
            'services',
            __( 'Services', 'wbk' ),
            'select',
            '',
            array(
            'items'    => WBK_Model_Utils::get_services(),
            'multiple' => true,
        ),
            null,
            true,
            true,
            false
        );
        $table->add_field(
            'coupon_maximum',
            'maximum',
            __( 'Usage limit', 'wbk' ),
            'text',
            '',
            array(
            'type' => 'none_negative_integer',
        ),
            '',
            true,
            true,
            false
        );
        $table->add_field(
            'coupon_used',
            'used',
            __( 'Used', 'wbk' ),
            'text',
            '',
            array(
            'type' => 'positive_integer',
        ),
            '',
            false,
            true,
            false
        );
        $table->add_field(
            'coupon_amount_fixed',
            'amount_fixed',
            __( 'Discount (fixed)', 'wbk' ),
            'text',
            '',
            array(
            'type' => 'none_negative_float',
        ),
            '',
            true,
            true,
            true
        );
        $table->add_field(
            'coupon_amount_percentage',
            'amount_percentage',
            __( 'Discount (percentage)', 'wbk' ),
            'text',
            '',
            array(
            'type' => 'none_negative_float',
        ),
            '',
            true,
            true,
            true
        );
        $table->sync_structure();
        Plugion()->tables->add( $table, $db_prefix . 'wbk_coupons' );
        // Payment plans
        $table = new Plugion\Table( $db_prefix . 'wbk_pricing_rules' );
        $table->set_single_item_name( __( 'Pricing rule', 'wbk' ) );
        $table->set_multiple_item_name( __( 'Pricing rules', 'wbk' ) );
        $table->add_field(
            'pricing_rule_name',
            'name',
            __( 'Name', 'wbk' ),
            'text',
            ''
        );
        $table->add_field(
            'pricing_rule_priority',
            'priority',
            __( 'Priority', 'wbk' ),
            'select',
            '',
            array(
            'items' => array(
            '1'  => __( 'low', 'wbk' ),
            '10' => __( 'medium', 'wbk' ),
            '20' => __( 'high', 'wbk' ),
        ),
        ),
            1,
            true,
            true,
            true
        );
        $table->add_field(
            'pricing_rule_type',
            'type',
            __( 'Type', 'wbk' ),
            'select',
            '',
            array(
            'items' => array(
            'date_range'           => __( 'Price for date range', 'wbk' ),
            'early_booking'        => __( 'Price for early booking', 'wbk' ),
            'custom_field'         => __( 'Price based on custom field value', 'wbk' ),
            'day_of_week_and_time' => __( 'Price for day of week and time range', 'wbk' ),
            'number_of_seats'      => __( 'Price based on number of seats booked', 'wbk' ),
            'number_of_timeslots'  => __( 'Price based on number of timeslots booked', 'wbk' ),
        ),
        ),
            null,
            true,
            true,
            true
        );
        $table->add_field(
            'pricing_rule_date_range',
            'date_range',
            __( 'Date range', 'wbk' ),
            'date_range',
            '',
            array(
            'time_zone' => get_option( 'wbk_timezone', 'UTC' ),
        ),
            '',
            true,
            false
        );
        $table->add_field(
            'pricing_rule_days_number',
            'days_number',
            __( 'Minimum number of days before the booked date', 'wbk' ),
            'text',
            '',
            array(
            'type' => 'positive_integer',
        ),
            '',
            true,
            false
        );
        $table->add_field(
            'pricing_rule_custom_field_id',
            'custom_field_id',
            __( 'Custom field ID', 'wbk' ),
            'text',
            '',
            null,
            '',
            true,
            false
        );
        $table->add_field(
            'pricing_rule_custom_field_operator',
            'custom_field_operator',
            __( 'Operator', 'wbk' ),
            'radio',
            '',
            array(
            'equals'    => __( 'equals', 'wbk' ),
            'more_than' => __( 'more than', 'wbk' ),
            'less_than' => __( 'less than', 'wbk' ),
        ),
            'equals',
            true,
            false
        );
        $table->add_field(
            'pricing_rule_custom_field_value',
            'custom_field_value',
            __( 'Custom field value', 'wbk' ),
            'text',
            '',
            null,
            '',
            true,
            false
        );
        $table->add_field(
            'pricing_rule_number_of_seats_operator',
            'number_of_seats_operator',
            __( 'Operator', 'wbk' ),
            'radio',
            '',
            array(
            'equals'    => __( 'equals', 'wbk' ),
            'more_than' => __( 'more than', 'wbk' ),
            'less_than' => __( 'less than', 'wbk' ),
        ),
            'equals',
            true,
            false
        );
        $table->add_field(
            'pricing_rule_number_of_seats_value',
            'number_of_seats_value',
            __( 'Number of seats', 'wbk' ),
            'text',
            '',
            null,
            '',
            true,
            false
        );
        $table->add_field(
            'pricing_rule_number_of_timeslots_operator',
            'number_of_timeslots_operator',
            __( 'Operator', 'wbk' ),
            'radio',
            '',
            array(
            'equals'    => __( 'equals', 'wbk' ),
            'more_than' => __( 'more than', 'wbk' ),
            'less_than' => __( 'less than', 'wbk' ),
        ),
            'equals',
            true,
            false
        );
        $table->add_field(
            'pricing_rule_number_of_timeslots_value',
            'number_of_timeslots_value',
            __( 'Number of timeslots', 'wbk' ),
            'text',
            '',
            null,
            '',
            true,
            false
        );
        $table->add_field(
            'pricing_rule_only_same_service',
            'only_same_service',
            __( 'Only timeslots in the same service', 'wbk' ),
            'checkbox',
            '',
            array(
            'yes' => __( 'Yes', 'wbk' ),
        ),
            '',
            true,
            false,
            false
        );
        $day_time_default = '{\'dow_availability\':[{}]}';
        $table->add_field(
            'pricing_rule_day_time',
            'day_time',
            __( 'Day of week and time range', 'wbk' ),
            'wbk_business_hours',
            '',
            null,
            $day_time_default,
            true,
            false,
            false
        );
        $table->add_field(
            'pricing_rule_action',
            'action',
            __( 'Action', 'wbk' ),
            'radio',
            '',
            array(
            'increase' => __( 'increase', 'wbk' ),
            'reduce'   => __( 'reduce', 'wbk' ),
            'replace'  => __( 'replace', 'wbk' ),
        ),
            'increase'
        );
        $table->add_field(
            'pricing_rule_amount',
            'amount',
            __( 'Amount', 'wbk' ),
            'text',
            '',
            array(
            'type' => 'none_negative_float',
        ),
            '0',
            true,
            true,
            false
        );
        $table->add_field(
            'pricing_rule_fixed_percent',
            'fixed_percent',
            __( 'Fixed / percent', 'wbk' ),
            'radio',
            '',
            array(
            'fixed'   => __( 'fixed', 'wbk' ),
            'percent' => __( 'percent', 'wbk' ),
        ),
            'fixed'
        );
        $table->add_field(
            'pricing_rule_multiply_amount',
            'multiply_amount',
            __( 'Multiply amount by the field value', 'wbk' ),
            'checkbox',
            '',
            array(
            'yes' => __( 'Yes', 'wbk' ),
        ),
            '',
            true,
            false,
            false
        );
        $table->add_field(
            'pricing_rule_related_to_seats_number',
            'related_to_seats_number',
            __( 'The field is related to the number of seats booked', 'wbk' ),
            'checkbox',
            '',
            array(
            'yes' => __( 'Yes', 'wbk' ),
        ),
            '',
            true,
            false,
            false
        );
        $table->sync_structure();
        $table = Plugion()->tables->add( $table, $db_prefix . 'wbk_pricing_rules' );
        $table->fields->get_element_at( 'pricing_rule_date_range' )->set_dependency( array( array( 'type', '=', 'date_range' ) ) );
        $table->fields->get_element_at( 'pricing_rule_days_number' )->set_dependency( array( array( 'type', '=', 'early_booking' ) ) );
        $table->fields->get_element_at( 'pricing_rule_custom_field_id' )->set_dependency( array( array( 'type', '=', 'custom_field' ) ) );
        $table->fields->get_element_at( 'pricing_rule_custom_field_operator' )->set_dependency( array( array( 'type', '=', 'custom_field' ) ) );
        $table->fields->get_element_at( 'pricing_rule_custom_field_value' )->set_dependency( array( array( 'type', '=', 'custom_field' ) ) );
        $table->fields->get_element_at( 'pricing_rule_number_of_seats_operator' )->set_dependency( array( array( 'type', '=', 'number_of_seats' ) ) );
        $table->fields->get_element_at( 'pricing_rule_number_of_seats_value' )->set_dependency( array( array( 'type', '=', 'number_of_seats' ) ) );
        $table->fields->get_element_at( 'pricing_rule_number_of_timeslots_operator' )->set_dependency( array( array( 'type', '=', 'number_of_timeslots' ) ) );
        $table->fields->get_element_at( 'pricing_rule_number_of_timeslots_value' )->set_dependency( array( array( 'type', '=', 'number_of_timeslots' ) ) );
        $table->fields->get_element_at( 'pricing_rule_only_same_service' )->set_dependency( array( array( 'type', '=', 'number_of_timeslots' ) ) );
        $table->fields->get_element_at( 'pricing_rule_multiply_amount' )->set_dependency( array( array( 'type', '=', 'custom_field' ) ) );
        $table->fields->get_element_at( 'pricing_rule_related_to_seats_number' )->set_dependency( array( array( 'type', '=', 'custom_field' ) ) );
        $table->fields->get_element_at( 'pricing_rule_day_time' )->set_dependency( array( array( 'type', '=', 'day_of_week_and_time' ) ) );
        $table->fields->get_element_at( 'pricing_rule_fixed_percent' )->set_dependency( array( array( 'action', '!=', 'replace' ), array( 'action', '!=', 'multiply' ) ) );
    }

}