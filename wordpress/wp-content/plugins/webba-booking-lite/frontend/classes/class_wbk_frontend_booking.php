<?php

// check if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class WBK_Frontend_Booking
{
    public function __construct()
    {
        // add shortcode
        add_shortcode( 'webba_booking', array( $this, 'shotrcodeBooking' ) );
        add_shortcode( 'webba_feature_appointmens', array( $this, 'shotrcodeFeatureAppointments' ) );
        add_shortcode( 'webba_email_landing', array( $this, 'shotrcodeEmailLanding' ) );
        add_shortcode( 'webba_multi_service_booking', array( $this, 'shotrcodeMultiServiceBooking' ) );
        // init scripts
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueueScripts' ) );
        // param process
        add_action( 'wp_loaded', array( $this, 'paramProcessing' ) );
        // render services in multiple mode
        add_action( 'wbk_render_multi_service_services', array( $this, 'render_multi_service_services' ) );
        add_filter(
            'wbk_pre_render_multi_service_services',
            array( $this, 'pre_render_multi_service_services' ),
            10,
            2
        );
        // Webba Booking native frontent actions
        // frontend category list
        add_action( 'wbk_render_frontend_category_list', array( $this, 'render_frontend_category_list' ) );
        add_filter( 'wbk_pre_render_frontend_category_list', array( $this, 'pre_render_frontend_category_list' ) );
    }
    
    public function paramProcessing()
    {
        if ( class_exists( 'WooCommerce' ) ) {
            if ( !session_id() ) {
                session_start();
            }
        }
        
        if ( isset( $_GET['wbkrefresh'] ) ) {
            $cache_refresh_token = WBK_Db_Utils::wbk_sanitize( $_GET['wbkrefresh'] );
            if ( get_option( 'wbk_gg_cache_token', '' ) == $cache_refresh_token ) {
                WBK_Db_Utils::doCacheForGoogleCalendars();
            }
        }
        
        
        if ( isset( $_GET['error'] ) ) {
            wp_redirect( get_permalink() . '?ggadd_cancelled=1' );
            exit;
        }
        
        
        if ( isset( $_GET['ggeventadd'] ) ) {
            $ggeventadd = $_GET['ggeventadd'];
            $ggeventadd = WBK_Db_Utils::wbk_sanitize( $ggeventadd );
            $appointment_ids = WBK_Db_Utils::getAppointmentIdsByGroupToken( $ggeventadd );
            
            if ( count( $appointment_ids ) == 0 ) {
            } else {
                if ( !session_id() ) {
                    session_start();
                }
                $_SESSION['wbk_ggeventaddtoken'] = $ggeventadd;
            }
        
        }
        
        if ( isset( $_GET['code'] ) ) {
            if ( !session_id() ) {
                session_start();
            }
        }
        // check if called as payment result
        if ( isset( $_GET['pp_aprove'] ) ) {
            
            if ( $_GET['pp_aprove'] == 'true' ) {
                
                if ( isset( $_GET['paymentId'] ) && isset( $_GET['PayerID'] ) ) {
                    $paymentId = $_GET['paymentId'];
                    $PayerID = $_GET['PayerID'];
                    $paypal = new WBK_PayPal();
                    $appointment_ids = WBK_Db_Utils::getAppointmentIdsByPaymentId( $paymentId );
                    $init_result = $paypal->init( false, $appointment_ids );
                    
                    if ( $init_result === FALSE ) {
                        wp_redirect( get_permalink() . '?paypal_status=2' );
                        exit;
                    } else {
                        $execResult = $paypal->executePayment( $paymentId, $PayerID );
                        
                        if ( $execResult === false ) {
                            wp_redirect( get_permalink() . '?paypal_status=3' );
                            exit;
                        } else {
                            $pp_redirect_url = trim( get_option( 'wbk_paypal_redirect_url', '' ) );
                            if ( $pp_redirect_url != '' ) {
                                
                                if ( filter_var( $pp_redirect_url, FILTER_VALIDATE_URL ) !== FALSE ) {
                                    wp_redirect( $pp_redirect_url );
                                    exit;
                                }
                            
                            }
                            wp_redirect( get_permalink() . '?paypal_status=1' );
                            exit;
                        }
                    
                    }
                
                } else {
                    wp_redirect( get_permalink() . '?paypal_status=4' );
                    exit;
                }
            
            } elseif ( $_GET['pp_aprove'] == 'false' ) {
                
                if ( isset( $_GET['cancel_token'] ) ) {
                    $cancel_token = $_GET['cancel_token'];
                    $cancel_token = str_replace( '"', '', $cancel_token );
                    $cancel_token = str_replace( '<', '', $cancel_token );
                    $cancel_token = str_replace( '\'', '', $cancel_token );
                    $cancel_token = str_replace( '>', '', $cancel_token );
                    $cancel_token = str_replace( '/', '', $cancel_token );
                    $cancel_token = str_replace( '\\', '', $cancel_token );
                    WBK_Db_Utils::clearPaymentIdByToken( $cancel_token );
                }
                
                wp_redirect( get_permalink() . '?paypal_status=5' );
                exit;
            }
        
        }
    }
    
    public function render( $template, $data )
    {
        // load and output view template
        ob_start();
        ob_implicit_flush( 0 );
        try {
            include dirname( __FILE__ ) . '/../templates/tpl_wbk_frontend_' . $template . '.php';
        } catch ( Exception $e ) {
            ob_end_clean();
            throw $e;
        }
        return ob_get_clean();
    }
    
    public function shotrcodeBooking( $attr )
    {
        extract( shortcode_atts( array(
            'service' => '0',
        ), $attr ) );
        extract( shortcode_atts( array(
            'category' => '0',
        ), $attr ) );
        extract( shortcode_atts( array(
            'category_list' => '0',
        ), $attr ) );
        $data = array();
        $data[0] = $service;
        $data[1] = $category;
        $data[3] = $category_list;
        return $this->render( 'booking_ui', $data );
    }
    
    public function shotrcodeMultiServiceBooking( $attr )
    {
        extract( shortcode_atts( array(
            'category'      => '0',
            'skip_services' => '0',
            'category_list' => '0',
        ), $attr ) );
        $data = array();
        $data[0] = $category;
        $data[1] = $skip_services;
        $data[2] = $category_list;
        return $this->render( 'multserv_booking_ui', $data );
    }
    
    public function shotrcodeFeatureAppointments( $attr )
    {
        extract( shortcode_atts( array(
            'service' => '0',
        ), $attr ) );
        extract( shortcode_atts( array(
            'category' => '0',
        ), $attr ) );
        
        if ( is_numeric( $service ) && $service != 0 ) {
            $data = array();
            $data[0] = $service;
            return $this->render( 'feature_appointments', $data );
        }
        
        
        if ( is_numeric( $category ) && $category != 0 ) {
            $data = array();
            $data[0] = $category;
            return $this->render( 'feature_appointments_category', $data );
        }
        
        return '';
    }
    
    public function shotrcodeEmailLanding( $attr )
    {
        extract( shortcode_atts( array(
            'service' => '0',
        ), $attr ) );
        if ( !is_numeric( $service ) ) {
            return;
        }
        $data = array();
        $data[0] = $service;
        return $this->render( 'landing', $data );
    }
    
    public function enqueueScripts()
    {
        global  $wbk_wording ;
        
        if ( get_option( 'wbk_load_js_in_footer', '' ) == 'true' ) {
            $in_footer = true;
        } else {
            $in_footer = false;
        }
        
        
        if ( $this->has_shortcode( 'webba_booking' ) || $this->has_shortcode( 'webba_email_landing' ) || $this->has_shortcode( 'webba_multi_service_booking' ) ) {
            if ( get_option( 'wbk_load_stripe_js', 'yes' ) == 'shortcode' ) {
            }
            wp_enqueue_script(
                'wbk-validator',
                plugins_url( '../common/wbk-validator.js', dirname( __FILE__ ) ),
                array( 'jquery', 'jquery-ui-core', 'jquery-effects-core' ),
                '4.1.5',
                $in_footer
            );
            
            if ( get_option( 'wbk_phone_mask', 'enabled' ) == 'enabled' ) {
                wp_enqueue_script( 'jquery-maskedinput', plugins_url( '../common/jquery.maskedinput.min.js', dirname( __FILE__ ) ), array( 'jquery', 'jquery-ui-core', 'jquery-effects-core' ) );
            } elseif ( get_option( 'wbk_phone_mask', 'enabled' ) == 'enabled_mask_plugin' ) {
                wp_enqueue_script( 'jquery-maskedinput', plugins_url( '../common/jquery.mask.js', dirname( __FILE__ ) ), array( 'jquery', 'jquery-ui-core', 'jquery-effects-core' ) );
            }
            
            wp_enqueue_script( 'jquery-effects-fade' );
            wp_enqueue_script(
                'wbk-frontend',
                plugins_url( 'js/wbk-frontend.js', dirname( __FILE__ ) ),
                array( 'jquery', 'jquery-ui-core', 'jquery-effects-core' ),
                '4.1.5',
                $in_footer
            );
            
            if ( get_option( 'wbk_pickadate_load', 'yes' ) == 'yes' ) {
                wp_enqueue_script(
                    'picker',
                    plugins_url( 'js/picker.js', dirname( __FILE__ ) ),
                    array( 'jquery', 'jquery-ui-core', 'jquery-effects-core' ),
                    '4.1.5',
                    $in_footer
                );
                wp_enqueue_script(
                    'picker-date',
                    plugins_url( 'js/picker.date.js', dirname( __FILE__ ) ),
                    array( 'jquery', 'jquery-ui-core', 'jquery-effects-core' ),
                    '4.1.5',
                    $in_footer
                );
                wp_enqueue_script(
                    'picker-legacy',
                    plugins_url( 'js/legacy.js', dirname( __FILE__ ) ),
                    array( 'jquery', 'jquery-ui-core', 'jquery-effects-core' ),
                    '4.1.5',
                    $in_footer
                );
            }
            
            
            if ( get_option( 'wbk_date_input', 'popup' ) == 'popup' ) {
                wp_enqueue_style(
                    'picker-default',
                    plugins_url( 'css/default.css', dirname( __FILE__ ) ),
                    array(),
                    '4.1.5'
                );
                wp_enqueue_style(
                    'picker-default-date',
                    plugins_url( 'css/default.date.css', dirname( __FILE__ ) ),
                    array(),
                    '4.1.5'
                );
            } elseif ( get_option( 'wbk_date_input', 'popup' ) == 'classic' ) {
                wp_enqueue_style(
                    'picker-classic',
                    plugins_url( 'css/classic.css', dirname( __FILE__ ) ),
                    array(),
                    '4.1.5'
                );
                wp_enqueue_style(
                    'picker-classic-date',
                    plugins_url( 'css/classic.date.css', dirname( __FILE__ ) ),
                    array(),
                    '4.1.5'
                );
            }
            
            wp_enqueue_style(
                'wbk-frontend-style-custom',
                plugins_url( 'css/wbk-frontend-custom-style.css', dirname( __FILE__ ) ),
                array(),
                '4.1.5'
            );
            wp_enqueue_style(
                'wbk-frontend-style',
                plugins_url( 'css/wbk-frontend-default-style.css', dirname( __FILE__ ) ),
                array(),
                '4.1.5'
            );
            $startOfWeek = get_option( 'wbk_start_of_week', 'monday' );
            
            if ( $startOfWeek == 'monday' ) {
                $startOfWeek = true;
            } else {
                $startOfWeek = false;
            }
            
            $select_date_extended_label = get_option( 'wbk_date_extended_label', '' );
            if ( $select_date_extended_label == '' ) {
                $select_date_extended_label = sanitize_text_field( $wbk_wording['date_extended_label'] );
            }
            $select_date_basic_label = get_option( 'wbk_date_basic_label', '' );
            if ( $select_date_basic_label == '' ) {
                $select_date_basic_label = sanitize_text_field( $wbk_wording['date_basic_label'] );
            }
            $select_slots_label = get_option( 'wbk_slots_label', '' );
            if ( $select_slots_label == '' ) {
                $select_slots_label = sanitize_text_field( $wbk_wording['slots_label'] );
            }
            $thanks_message = get_option( 'wbk_book_thanks_message', '' );
            if ( $thanks_message == '' ) {
                $thanks_message = sanitize_text_field( $wbk_wording['book_thanks_message'] );
            }
            $select_date_placeholder = get_option( 'wbk_date_input_placeholder', '' );
            if ( $select_date_placeholder == '' ) {
                $select_date_placeholder = sanitize_text_field( $wbk_wording['date_input_placeholder'] );
            }
            $booked_text = get_option( 'wbk_booked_text', '' );
            if ( $booked_text == '' ) {
                $booked_text = sanitize_text_field( $wbk_wording['booked_text'] );
            }
            // Localize the script with new data
            $checkout_label = get_option( 'wbk_checkout_button_text', '' );
            if ( $checkout_label == '' ) {
                $checkout_label = sanitize_text_field( $wbk_wording['checkout'] );
            }
            $checkout_label = str_replace( '#selected_count', '<span class="wbk_multi_selected_count"></span>', $checkout_label );
            $checkout_label = str_replace( '#total_count', '<span class="wbk_multi_total_count"></span>', $checkout_label );
            $checkout_label = str_replace( '#low_limit', '<span class="wbk_multi_low_limit"></span>', $checkout_label );
            $continuous_appointments = get_option( 'wbk_appointments_continuous' );
            
            if ( is_array( $continuous_appointments ) ) {
                $continuous_appointments = implode( ',', $continuous_appointments );
            } else {
                $continuous_appointments = '';
            }
            
            $translation_array = array(
                'mode'                      => get_option( 'wbk_mode', 'extended' ),
                'phonemask'                 => get_option( 'wbk_phone_mask', 'enabled' ),
                'phoneformat'               => get_option( 'wbk_phone_format', '(999) 999-9999' ),
                'ajaxurl'                   => admin_url( 'admin-ajax.php' ),
                'selectdatestart'           => $select_date_extended_label,
                'selectdatestartbasic'      => $select_date_basic_label,
                'selecttime'                => $select_slots_label,
                'selectdate'                => $select_date_placeholder,
                'thanksforbooking'          => $thanks_message,
                'january'                   => __( 'January', 'wbk' ),
                'february'                  => __( 'February', 'wbk' ),
                'march'                     => __( 'March', 'wbk' ),
                'april'                     => __( 'April', 'wbk' ),
                'may'                       => __( 'May', 'wbk' ),
                'june'                      => __( 'June', 'wbk' ),
                'july'                      => __( 'July', 'wbk' ),
                'august'                    => __( 'August', 'wbk' ),
                'september'                 => __( 'September', 'wbk' ),
                'october'                   => __( 'October', 'wbk' ),
                'november'                  => __( 'November', 'wbk' ),
                'december'                  => __( 'December', 'wbk' ),
                'jan'                       => __( 'Jan', 'wbk' ),
                'feb'                       => __( 'Feb', 'wbk' ),
                'mar'                       => __( 'Mar', 'wbk' ),
                'apr'                       => __( 'Apr', 'wbk' ),
                'mays'                      => __( 'May', 'wbk' ),
                'jun'                       => __( 'Jun', 'wbk' ),
                'jul'                       => __( 'Jul', 'wbk' ),
                'aug'                       => __( 'Aug', 'wbk' ),
                'sep'                       => __( 'Sep', 'wbk' ),
                'oct'                       => __( 'Oct', 'wbk' ),
                'nov'                       => __( 'Nov', 'wbk' ),
                'dec'                       => __( 'Dec', 'wbk' ),
                'sunday'                    => __( 'Sunday', 'wbk' ),
                'monday'                    => __( 'Monday', 'wbk' ),
                'tuesday'                   => __( 'Tuesday', 'wbk' ),
                'wednesday'                 => __( 'Wednesday', 'wbk' ),
                'thursday'                  => __( 'Thursday', 'wbk' ),
                'friday'                    => __( 'Friday', 'wbk' ),
                'saturday'                  => __( 'Saturday', 'wbk' ),
                'sun'                       => __( 'Sun', 'wbk' ),
                'mon'                       => __( 'Mon', 'wbk' ),
                'tue'                       => __( 'Tue', 'wbk' ),
                'wed'                       => __( 'Wed', 'wbk' ),
                'thu'                       => __( 'Thu', 'wbk' ),
                'fri'                       => __( 'Fri', 'wbk' ),
                'sat'                       => __( 'Sat', 'wbk' ),
                'today'                     => __( 'Today', 'wbk' ),
                'clear'                     => __( 'Clear', 'wbk' ),
                'close'                     => __( 'Close', 'wbk' ),
                'startofweek'               => $startOfWeek,
                'nextmonth'                 => __( 'Next month', 'wbk' ),
                'prevmonth'                 => __( 'Previous  month', 'wbk' ),
                'hide_form'                 => get_option( 'wbk_hide_from_on_booking', 'disabled' ),
                'booked_text'               => $booked_text,
                'show_booked'               => get_option( 'wbk_show_booked_slots', 'disabled' ),
                'multi_booking'             => get_option( 'wbk_multi_booking', 'disabled' ),
                'checkout'                  => $checkout_label,
                'multi_limit'               => get_option( 'wbk_multi_booking_max', '' ),
                'multi_limit_default'       => get_option( 'wbk_multi_booking_max', '' ),
                'phone_required'            => get_option( 'wbk_phone_required', '3' ),
                'show_desc'                 => get_option( 'wbk_show_service_description', 'disabled' ),
                'date_input'                => get_option( 'wbk_date_input', 'popup' ),
                'allow_attachment'          => get_option( 'wbk_allow_attachemnt', 'no' ),
                'stripe_public_key'         => get_option( 'wbk_stripe_publishable_key', '' ),
                'override_stripe_error'     => get_option( 'wbk_stripe_card_input_mode', 'no' ),
                'stripe_card_error_message' => get_option( 'wbk_stripe_card_element_error_message', 'incorrect input' ),
                'something_wrong'           => __( 'Something went wrong, please try again.', 'wbk' ),
                'time_slot_booked'          => __( 'Time slot(s) already booked.', 'wbk' ),
                'pp_redirect'               => get_option( 'wbk_paypal_auto_redirect', 'disabled' ),
                'show_prev_booking'         => get_option( 'wbk_show_details_prev_booking', 'disabled' ),
                'scroll_container'          => get_option( 'wbk_scroll_container', 'html, body' ),
                'continious_appointments'   => $continuous_appointments,
                'show_suitable_hours'       => get_option( 'wbk_show_suitable_hours', 'yes' ),
                'stripe_redirect_url'       => get_option( 'wbk_stripe_redirect_url', '' ),
                'stripe_mob_size'           => get_option( 'wbk_stripe_mob_font_size', '' ),
                'auto_add_to_cart'          => get_option( 'wbk_woo_auto_add_to_cart', 'disabled' ),
                'range_selection'           => get_option( 'wbk_range_selection', 'disabled' ),
                'picker_format'             => WBK_Db_Utils::convertDateFormatForPicker(),
                'scroll_value'              => get_option( 'wbk_scroll_value', '120' ),
                'field_required'            => get_option( 'wbk_validation_error_message', '' ),
                'error_status_scroll_value' => '0',
                'limit_per_email_message'   => get_option( 'wbk_limit_by_email_reached_message', __( 'You have reached your booking limit.', 'wbk' ) ),
                'stripe_hide_postal'        => get_option( 'wbk_stripe_hide_postal', 'false' ),
                'jquery_no_conflict'        => get_option( 'wbk_jquery_nc', 'disabled' ),
                'no_available_dates'        => get_option( 'wbk_no_dates_label', __( 'No available dates message', 'wbk' ) ),
            );
            $sanitized_array = array();
            foreach ( $translation_array as $key => $value ) {
                $sanitized_array[$key] = wbk_script_escape( $value );
            }
            wp_localize_script( 'wbk-frontend', 'wbkl10n', $sanitized_array );
        }
        
        
        if ( $this->has_shortcode_strong( 'webba_feature_appointmens' ) ) {
            wp_enqueue_style( 'slf-tablesaw', plugins_url( '../../backend/solo-framework/css/tablesaw.css', __FILE__ ) );
            wp_enqueue_script( 'slf-tablesaw', plugins_url( '../../backend/solo-framework/js/tablesaw.js', __FILE__ ), array( 'jquery' ) );
            wp_enqueue_script( 'wbk-feature-appointments', plugins_url( 'js/wbk-feature-appointments.js', dirname( __FILE__ ) ), array( 'jquery', 'jquery-ui-core', 'jquery-effects-core' ) );
        }
    
    }
    
    private function has_shortcode_strong( $shortcode )
    {
        $post_to_check = get_post( get_the_ID() );
        if ( !$post_to_check ) {
            return false;
        }
        $found = false;
        if ( !$shortcode ) {
            return $found;
        }
        if ( stripos( $post_to_check->post_content, '[' . $shortcode ) !== false ) {
            $found = true;
        }
        return $found;
    }
    
    // check if post has shortcode
    private function has_shortcode( $shortcode = '' )
    {
        if ( get_option( 'wbk_check_short_code', 'disabled' ) == 'disabled' ) {
            return true;
        }
        $post_to_check = get_post( get_the_ID() );
        if ( !$post_to_check ) {
            return false;
        }
        $found = false;
        if ( !$shortcode ) {
            return $found;
        }
        if ( stripos( $post_to_check->post_content, '[' . $shortcode ) !== false ) {
            $found = true;
        }
        return $found;
    }
    
    // hook for action wbk_render_frontend_category_list
    public function render_frontend_category_list( $data )
    {
        if ( $data != 1 ) {
            return;
        }
        $render_data = array();
        $render_data = apply_filters( 'wbk_pre_render_frontend_category_list', $render_data, $data );
        $this->render_from_array( $render_data );
    }
    
    // hook for wbk_render_multi_service_services
    public function render_multi_service_services( $arg )
    {
        $render_data = array();
        
        if ( get_option( 'wbk_multi_booking', 'disabled' ) == 'disabled' ) {
            $render_data['error_message'] = 'Multi-service mode required multiple booking to be enabled. Check the Mode tab of Webba Settings page.';
            $this->render_from_array( $render_data );
            return;
        }
        
        $render_data = apply_filters( 'wbk_pre_render_multi_service_services', $render_data, $arg );
        $this->render_from_array( $render_data );
    }
    
    public function pre_render_multi_service_services( $input, $arg )
    {
        if ( $arg[1] == '1' ) {
            $input['hide_open'] = '<div class="wbk_multiserv_hidden_services" style="display:none">';
        }
        if ( $arg[2] != "1" ) {
            $input['title'] = '<label class="wbk-input-label">' . wbk_get_translation_string( 'wbk_service_label', 'service_label', 'Select service' ) . '</label>';
        }
        
        if ( $arg[0] == 0 ) {
            $services = WBK_Db_Utils::getServices();
        } else {
            $services = WBK_Db_Utils::getServicesInCategory( $arg[0] );
            if ( !is_array( $services ) ) {
                $services = array();
            }
        }
        
        $temp = '';
        $filter_used = FALSE;
        
        if ( get_option( 'wbk_allow_service_in_url', 'no' ) == 'yes' && isset( $_GET['service'] ) ) {
            $arr_from_url = explode( '-', $_GET['service'] );
            $filter_used = TRUE;
        }
        
        $item_class = '';
        
        if ( $arg[2] == "1" ) {
            $input['label'] = '<label class="wbk-input-label wbk-category-input-label">' . wbk_get_translation_string( 'wbk_category_label', 'category_label', 'Select category' ) . '</label>';
            $catetories = WBK_Db_Utils::getServiceCategoryList();
            $category_html = '<select class="wbk-select wbk-input" id="wbk-category-id">';
            $category_html .= '<option value="0" selected="selected">' . __( 'select...', 'wbk' ) . '</option>';
            foreach ( $catetories as $key => $value ) {
                $arr_services = WBK_Db_Utils::getServicesInCategory( $key );
                if ( $arr_services === FALSE ) {
                    continue;
                }
                $category_html .= '<option data-services="' . implode( '-', $arr_services ) . '" value="' . $key . '">' . $value . ' </option>';
            }
            $category_html .= '</select>';
            $category_html .= '<label class="wbk-input-label wbk_hidden wbk-service-category-label">' . wbk_get_translation_string( 'wbk_service_label', 'service_label', 'Select service' ) . '</label>';
            $input['categories'] = $category_html;
            $item_class = "wbk_hidden";
        }
        
        foreach ( $services as $service_id ) {
            if ( $filter_used ) {
                if ( !in_array( $service_id, $arr_from_url ) ) {
                    continue;
                }
            }
            $service = WBK_Db_Utils::initServiceById( $service_id );
            if ( $service === FALSE ) {
                continue;
            }
            
            if ( $arg[1] == '1' ) {
                $temp .= '<input type="checkbox" value="' . $service_id . '" class="wbk-checkbox wbk-service-checkbox" id="wbk-service_chk_' . $service_id . '" checked />';
            } else {
                $temp .= '<input type="checkbox" value="' . $service_id . '" class="wbk-checkbox wbk-service-checkbox" id="wbk-service_chk_' . $service_id . '" />';
            }
            
            $temp .= '<label for="wbk-service_chk_' . $service_id . '" class="wbk_service_chk_label_' . $service_id . ' wbk-checkbox-label wbk_service_chk_label ' . $item_class . ' wbk-dayofweek-label">' . $service->getName() . '</label>';
            $temp .= '<div class="wbk_chk_clear_' . $service_id . ' wbk-clear ' . $item_class . '"></div>';
        }
        $input['services'] = $temp;
        if ( $arg[1] == '1' ) {
            $input['hide_close'] = '</div>';
        }
        return $input;
    }
    
    // hook for filter wbk_pre_render_frontend_category_list
    public function pre_render_frontend_category_list( $input )
    {
        $input['label'] = '<label class="wbk-input-label wbk-category-input-label">' . wbk_get_translation_string( 'wbk_category_label', 'category_label', 'Select category' ) . '</label>';
        $catetories = WBK_Db_Utils::getServiceCategoryList();
        $category_html = '<select class="wbk-select wbk-input" id="wbk-category-id">';
        $category_html .= '<option value="0" selected="selected">' . __( 'select...', 'wbk' ) . '</option>';
        foreach ( $catetories as $key => $value ) {
            $arr_services = WBK_Db_Utils::getServicesInCategory( $key );
            if ( $arr_services === FALSE ) {
                continue;
            }
            $services_data = '';
            if ( is_array( $arr_services ) ) {
                $services_data = implode( '-', $arr_services );
            }
            $category_html .= '<option data-services="' . $services_data . '" value="' . $key . '">' . $value . ' </option>';
        }
        $category_html .= '</select>';
        $input['categories'] = $category_html;
        $full_service_list = '<select class="wbk_hidden" id="wbk_service_id_full_list">';
        $arrIds = WBK_Db_Utils::getServices();
        foreach ( $arrIds as $service_id ) {
            $service = WBK_Db_Utils::initServiceById( $service_id );
            if ( $service == FALSE ) {
                continue;
            }
            
            if ( get_option( 'wbk_show_service_description', 'disabled' ) == 'disabled' ) {
                $full_service_list .= '<option value="' . $service->getId() . '"  data-multi-low-limit="' . $service->getMultipleLowLimit() . '" data-multi-limit="' . $service->getMultipleLimit() . '" >' . $service->getName( true ) . '</option>';
            } else {
                $full_service_list .= '<option data-desc="' . htmlspecialchars( $service->getDescription( true ) ) . '" value="' . $service->getId() . '"  data-multi-low-limit="' . $service->getMultipleLowLimit() . '"  data-multi-limit="' . $service->getMultipleLimit() . '" >' . $service->getName( true ) . '</option>';
            }
        
        }
        $full_service_list .= '</select>';
        $input['full_service_list'] = $full_service_list;
        return $input;
    }
    
    // render from array
    private function render_from_array( $input )
    {
        foreach ( $input as $key => $value ) {
            echo  $value ;
        }
    }

}