<?php
// check if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
class WBK_User_Utils {
    /**
     * get all wordpress users
     * @return array id-name pair
     */
    public static function get_wp_users() {
        global $wpdb;
        $result = get_users();
        $result_converted = array();
        foreach( $result as $item ) {
            $result_converted[ $item->ID ] = $item->display_name;
        }
        return $result_converted;
    }

    /**
     * get users with the not admin role
     * @return array id-name pair
     */
    public static function get_none_admin_wp_users() {
        global $wpdb;
        $result = get_users( array( 'role__not_in' => array( 'administrator') ) );
        $result_converted = array();
        foreach( $result as $item ) {
            $result_converted[ $item->ID ] = $item->display_name;
        }
        return $result_converted;
    }

    /**
     * check if current user has access google calendar
     * @param int $calendar_id calendar id
     * @return bool true if has access
     */
    public static function check_access_to_gg_calendar( $calendar_id ){
        global $current_user;
        global $wpdb;
        $user_id = get_current_user_id();
        if ( $user_id == 0 ) {
            return false;
        }
        $user_count = $wpdb->get_var(   $wpdb->prepare( 'SELECT count(*) as cnt FROM ' . get_option( 'wbk_db_prefix', '' ) . 'wbk_gg_calendars where user_id = %d AND id = %d', $user_id, $calendar_id ) );
        if( $user_count > 0 ){
            return true;
        }
        return false;
    }

    /**
     * check if current user has access to at least one sevice schedule
     * @return bool true if has access
     */
    public static function check_access_to_schedule(){
        $user_id = get_current_user_id();
        if ( $user_id == 0 ) {
            return false;
        }
        global $wpdb;
        $users = $wpdb->get_col(  'SELECT users FROM ' . get_option('wbk_db_prefix', '' ) . 'wbk_services'   );
        foreach ( $users as $user ) {
            if( is_null( $user ) ){
                continue;
            }
            $user_arr = json_decode( $user );
            if( is_numeric( $user_arr ) ){
                $user_arr = array( $user_arr );
            }
            if ( in_array( $user_id, $user_arr ) ){
                return true;
            }
        }
        return false;
    }




}
?>
