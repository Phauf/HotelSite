<?php

// check if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// include abstract component class
include 'class_wbk_backend_component.php';
require 'solo-framework/solo-framework.php';
// include backend classes from /classes folder
foreach ( glob( dirname( __FILE__ ) . '/classes/*.php' ) as $filename ) {
    try {
        include $filename;
    } catch ( Exception $e ) {
        throw $e;
    }
}
// define main backend class
class WBK_Backend
{
    // 	available components of backend (based on files in classes folder)
    private  $components ;
    public function __construct()
    {
        add_action( 'init', array( $this, 'inline_upload_enquene' ) );
        //add action for wp menu construction
        add_action( 'admin_menu', array( $this, 'createAdminMenu' ) );
        //set components of backend
        $this->components = array();
        $temp_arr = array();
        foreach ( glob( dirname( __FILE__ ) . '/classes/*.php' ) as $filename ) {
            $component_name = str_replace( 'class_', '', basename( $filename, ".php" ) );
            $temp_arr[$component_name] = new $component_name();
        }
        foreach ( $temp_arr as $key => $value ) {
            $this->components[$key] = new $value();
        }
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
        add_action(
            'in_plugin_update_message-webba-booking/webba-booking-lite.php',
            array( $this, 'prefix_plugin_update_message' ),
            10,
            2
        );
        add_action(
            'in_plugin_update_message-webba-booking-lite/webba-booking-lite.php',
            array( $this, 'prefix_plugin_update_message' ),
            10,
            2
        );
    }
    
    public function prefix_plugin_update_message( $data, $response )
    {
        
        if ( isset( $data['upgrade_notice'] ) ) {
            $message = str_replace( array( '<p>', '</p>' ), array( '<div>', '</div>' ), $data['upgrade_notice'] );
            echo  '<style type="text/css">
			#webba-booking-lite-update .update-message p:not(:first-child){
				display: none;
			}
            </style>' ;
        }
    
    }
    
    public function settings_updated()
    {
        
        if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
            date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
            $time_corr = intval( get_option( 'wbk_email_admin_daily_time', '68400' ) );
            $midnight = strtotime( 'today midnight' );
            $timestamp = strtotime( 'today midnight' ) + $time_corr;
            if ( $timestamp < time() ) {
                $timestamp += 86400;
            }
            wp_clear_scheduled_hook( 'wbk_daily_event' );
            wp_schedule_event( $timestamp, 'daily', 'wbk_daily_event' );
            date_default_timezone_set( 'UTC' );
        }
    
    }
    
    public function inline_upload_enquene()
    {
        wp_enqueue_script( 'wp-tinymce' );
        // add common css
        if ( isset( $_GET['page'] ) && ($_GET['page'] == 'wbk-options' || $_GET['page'] == 'wbk-schedule' || $_GET['page'] == 'wbk-gg-calendars' || $_GET['page'] == 'wbk-forms') ) {
            wp_enqueue_style(
                'wbk-backend-style',
                plugins_url( '/css/wbk-backend.css', __FILE__ ),
                array(),
                '4.0.73'
            );
        }
        // edit post/page scripts
        
        if ( $this->is_edit_page() ) {
            wp_enqueue_script( 'jquery-ui-dialog' );
            wp_enqueue_script( 'jquery-ui-core' );
            wp_enqueue_script( 'wbk-service-dialog', plugins_url( '/js/wbk-post-buttons.js', __FILE__ ) );
            $translation_array = array(
                'cancel'    => __( 'Cancel', 'wbk' ),
                'add'       => __( 'Add', 'wbk' ),
                'formtitle' => __( 'Add Webba Booking form', 'wbk' ),
            );
            wp_localize_script( 'wbk-service-dialog', 'wbkl10n', $translation_array );
            wp_enqueue_style( 'wbk-shortcode-dialog-style', plugins_url( '/css/wbk-shortcode-dialog.css', __FILE__ ) );
            wp_enqueue_style( 'wp-jquery-ui-dialog' );
            // add shortcode dialog to admion
            add_action( 'admin_footer', array( $this, 'createServiceDialog' ) );
            // add shortcode button
            add_action( 'media_buttons', array( $this, 'createShortcodeButton' ) );
        }
    
    }
    
    public function createAdminMenu()
    {
        global  $current_user ;
        if ( current_user_can( 'manage_options' ) || WBK_Validator::checkAccessToSchedule() || WBK_Validator::checkAccessToGgCalendarPage() ) {
            
            if ( !empty($this->components) ) {
                $root_name = __( 'Webba Booking', 'wbk' );
                $root_name = apply_filters( 'wbk_root_menu_title', $root_name );
                add_menu_page(
                    $root_name,
                    $root_name,
                    'read',
                    'wbk-main',
                    array( $this->components['wbk_backend_schedule'], 'render' ),
                    plugins_url( 'images/webba-booking.png', __FILE__ )
                );
                foreach ( $this->components as $component ) {
                    if ( is_null( $component->getName() ) ) {
                        continue;
                    }
                    $component_title = $component->getTitle();
                    $hook = add_submenu_page(
                        'wbk-main',
                        $component->getTitle(),
                        $component->getTitle(),
                        $component->getCapability(),
                        $component->getName(),
                        array( $component, 'render' )
                    );
                    if ( $component->getName() == 'wbk-options' ) {
                        add_action( 'load-' . $hook, array( $this, 'settings_updated' ) );
                    }
                }
                add_submenu_page(
                    'wbk-main',
                    __( 'Services', 'wbk' ),
                    __( 'Services', 'wbk' ),
                    'manage_options',
                    'wbk-services',
                    array( 'WBK_Renderer', 'render_backend_page' )
                );
                add_submenu_page(
                    'wbk-main',
                    __( 'Service categories', 'wbk' ),
                    __( 'Service categories', 'wbk' ),
                    'manage_options',
                    'wbk-service-categories',
                    array( 'WBK_Renderer', 'render_backend_page' )
                );
                add_submenu_page(
                    'wbk-main',
                    __( 'Appointments', 'wbk' ),
                    __( 'Appointments', 'wbk' ),
                    'read',
                    'wbk-appointments',
                    array( 'WBK_Renderer', 'render_backend_page' )
                );
                add_submenu_page(
                    'wbk-main',
                    __( 'Email templates', 'wbk' ),
                    __( 'Email templates', 'wbk' ),
                    'manage_options',
                    'wbk-email-templates',
                    array( 'WBK_Renderer', 'render_backend_page' )
                );
                add_submenu_page(
                    'wbk-main',
                    __( 'Coupons', 'wbk' ),
                    __( 'Coupons', 'wbk' ),
                    'read',
                    'wbk-coupons',
                    array( 'WBK_Renderer', 'render_backend_page' )
                );
                add_submenu_page(
                    'wbk-main',
                    __( 'Pricing rules', 'wbk' ),
                    __( 'Pricing rules', 'wbk' ),
                    'read',
                    'wbk-pricing-rules',
                    array( 'WBK_Renderer', 'render_backend_page' )
                );
                global  $submenu ;
                unset( $submenu['wbk-main'][0] );
            }
        
        }
    }
    
    public function createServiceDialog()
    {
        $service_list = '<select class="wbk-input wbk-width-100" id="wbk-service-id">';
        $service_list .= '<option value="0" selected="selected">' . __( 'All services', 'wbk' ) . '</option>';
        $arrIds = WBK_Db_Utils::getServices();
        foreach ( $arrIds as $id ) {
            $service = new WBK_Service_deprecated();
            if ( !$service->setId( $id ) ) {
                continue;
            }
            if ( !$service->load() ) {
                continue;
            }
            $service_list .= '<option value="' . $service->getId() . '"" >' . $service->getName() . '</option>';
        }
        $service_list .= '</select>';
        $caregory_list = '<select class="wbk-input wbk-width-100" id="wbk-category-id">';
        $caregory_list .= '<option value="0" selected="selected">' . __( 'All categories', 'wbk' ) . '</option>';
        $arrIds = WBK_Db_Utils::getServiceCategoryList();
        foreach ( $arrIds as $key => $value ) {
            $service = new WBK_Service_deprecated();
            if ( !$service->setId( $id ) ) {
                continue;
            }
            if ( !$service->load() ) {
                continue;
            }
            $caregory_list .= '<option value="' . $key . '"" >' . $value . '</option>';
        }
        $caregory_list .= '</select>';
        $html = '<div id="wbk-service-dialog" >
				   	<div id="wbk-service-dialog-content">
						<label for="wbk-service">' . __( 'Select service', 'wbk' ) . '<span class="input-error" id="error-name"></span></label><br/>' . $service_list . '</div>
						<label for="wbk-service">' . __( 'Or category', 'wbk' ) . '<span class="input-error" id="error-name"></span></label><br/>' . $caregory_list . '</div>

				</div>';
        echo  $html ;
    }
    
    public function createShortcodeButton()
    {
        
        if ( get_option( 'wbk_backend_add_buttons_in_editor', 'true' ) == 'true' ) {
            echo  '<a href="#" class = "button" id = "wbk-add-shortcode" title = "Webba Booking form">' . __( 'Webba Booking form', 'wbk' ) . '</a>' ;
            echo  '<a href="#" class = "button" id = "wbk-add-shortcode-landing" title = "Webba Booking Email landing">' . __( 'Webba Booking Email landing', 'wbk' ) . '</a>' ;
        }
    
    }
    
    protected function is_edit_page( $new_edit = null )
    {
        global  $pagenow ;
        //make sure we are on the backend
        if ( !is_admin() ) {
            return false;
        }
        
        if ( $new_edit == 'edit' ) {
            return in_array( $pagenow, array( 'post.php' ) );
        } elseif ( $new_edit == 'new' ) {
            return in_array( $pagenow, array( 'post-new.php' ) );
        } else {
            return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
        }
    
    }
    
    public function admin_notices()
    {
        echo  WBK_Admin_Notices::labelUpdate() ;
        echo  WBK_Admin_Notices::appearanceUpdate() ;
        echo  WBK_Admin_Notices::emailLandingUpdate() ;
        echo  WBK_Admin_Notices::stripe_fields_update_norice() ;
        echo  WBK_Admin_Notices::wbk_4_0_update() ;
        echo  WBK_Admin_Notices::sms_compability() ;
        echo  WBK_Admin_Notices::stripe_conflict() ;
    }

}