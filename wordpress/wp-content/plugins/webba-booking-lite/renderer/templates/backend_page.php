<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<meta name="format-detection" content="telephone=no">
<h2><?php echo get_admin_page_title(); ?></h2>
<div class="plugion_backend_page_container">
<?php
    global $plugin_page;
    $db_prefix = get_option( 'wbk_db_prefix', '' );
    switch ( $plugin_page) {
        case 'wbk-services':
            Plugion()->table( $db_prefix . 'wbk_services' );
            break;
        case 'wbk-service-categories':
            Plugion()->table( $db_prefix . 'wbk_service_categories' );
            break;
        case 'wbk-email-templates':
            Plugion()->table( $db_prefix . 'wbk_email_templates' );
            break;
        case 'wbk-appointments':
            $db_prefix = get_option( 'wbk_db_prefix', '' );
            if( isset( $_GET['cancelled'] ) &&  $_GET['cancelled'] == 1 ){
                Plugion()->table( $db_prefix . 'wbk_cancelled_appointments' );
                echo '<p style="display: block; clear: both;" ><a class="wbk_control_link" href="' . admin_url() . 'admin.php?page=wbk-appointments">' . __( 'Active appointments', 'wbk') . '</a>';
            } else {
                Plugion()->table( $db_prefix . 'wbk_appointments' );
                echo '<p style="display: block; clear: both;" ><a class="wbk_control_link" href="' . admin_url() . 'admin.php?page=wbk-appointments&cancelled=1">' . __( 'Cancelled appointments', 'wbk') . '</a>';
            }
            break;
        case 'wbk-gg-calendars':
            if ( version_compare( PHP_VERSION, '5.4.0' ) >= 0) {
                if(  isset( $_GET['clid'] ) && is_numeric( $_GET['clid'] ) ){
                    if( WBK_User_Utils::check_access_to_gg_calendar( $_GET['clid'] ) || current_user_can('manage_options') ){
                        $calendar_id = $_GET['clid'];
                        if( !is_numeric( $calendar_id ) ){
                        	$html = __( 'Error: invalid calendar ID', 'wbk');
                            return $html;
                        }
                        $html = '';
                        $google = new WBK_Google();
                        $google->init( $calendar_id );
                        $html .= '<h2>' . $google->getCalendarName() . '</h2>';
                        if( isset( $_GET['code'] ) ){
                            $auth_code =  $_GET['code'];
                            $fetch_result =  $google->processAuthCode( $auth_code );
                    	}
                        if( isset( $_GET['action'] ) && $_GET['action'] == 'revoke'  && !isset( $_GET['code'] ) ){
                            $google->clearToken();
                        }
                        $html .= $google->renderCalendarBlock();
                   } else {
                       $html = __( 'Calendar not found', 'wbk' );
                   }
                   echo $html;
               } else {
                   if( current_user_can('manage_options') ){
                      Plugion()->table( $db_prefix . 'wbk_gg_calendars' );
                   } else {
                       $user_id = get_current_user_id();
                       if ( $user_id == 0 ) {
                           wp_die();
                           return;
                       }
                       $calendars = Wbk_Db_Utils::getGgCalendarsByUser( $user_id );
                       $html = '';
                       foreach( $calendars as $calendar ){
                           $html .= '<h3>' . $calendar->name.'</h3>';
                           $html .= '<a target="_blank" class="slf_table_link" href="' .  get_admin_url()  . 'admin.php?page=wbk-gg-calendars&clid=' . $calendar->id . '">' . __( 'Manage authorization', 'wbk' ) .'</a></br></br>';

                       }
                       echo $html;
                   }
                }
            } else {
                echo __( 'The Google Calendar API require PHP 5.4 or greater. Your version is ', 'wbk' ) . PHP_VERSION ;
            }
            break;
        case 'wbk-coupons':
            Plugion()->table( $db_prefix . 'wbk_coupons' );
            break;
        case 'wbk-pricing-rules':
            Plugion()->table( $db_prefix . 'wbk_pricing_rules' );
            break;
        default:
            break;
    }

?>
</div>
