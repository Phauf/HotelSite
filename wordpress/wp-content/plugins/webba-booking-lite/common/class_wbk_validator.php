<?php
//WBK validator class
// check if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
class WBK_Validator {
    // check string size
	public static function checkStringSize( $str, $min, $max ) {
		if ( strlen($str) > $max || strlen($str) < $min ) {
            return false;
        } else {
            return true;
        }
    }
    // check integer
	public static function checkInteger( $int, $min, $max ) {
	  	if ( !is_numeric( $int ) ) {
            return false;
        }
		if ( intval( $int ) <> $int ) {
            return false;
        }
		if ( $int > $max || $int < $min ) {
            return false;
        }
        return true;
    }
    // check if email
	public static function checkEmail( $eml ) {
		if ( !preg_match( '/^([a-z0-9_\.-]+)@([a-z0-9_\.-]+)\.([a-z\.]{2,20})$/', $eml ) ) {
            return false;
        } else {
            return true;
        }
    }
    // check if color
	public static function checkColor( $clr ) {
		if ( !preg_match( '/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/', $clr ) ) {
            return false;
        } else {
            return true;
        }
    }
    // check if day of week
	public static function checkDayofweek( $str ) {
		if ( $str != 'monday' && $str != 'tuesday' && $str != 'wednesday' && $str != 'thursday' && $str != 'friday' && $str != 'saturday' && $str != 'sunday' ) {
            return false;
        } else {
            return true;
        }
    }
    // check business hours array
	public static function checkBusinessHours( $value ) {
        $bh = new WBK_Business_Hours();
		$arr = explode( ';', $value );
		if ( $bh->setFromArray( $arr ) ) {
            return true;
        } else {
            return false;
        }
    }
    // check if current user has access to any of existing service
	public static function checkAccessToSchedule(){
        $user_id = get_current_user_id();
		if ( $user_id == 0 ) {
            return false;
        }
        global $wpdb;
		$users = $wpdb->get_col(  'SELECT users FROM '. get_option('wbk_db_prefix', '' ) . 'wbk_services'   );
		foreach ( $users as $user ) {
			if( is_null( $user ) ){
				continue;
			}
			$user_arr = json_decode( $user );
			if ( in_array( $user_id, $user_arr ) ){
                return true;
            }
        }
        return false;
    }
	public static function checkAccessToGgCalendarPage(){
        global $current_user;
        global $wpdb;
        $user_id = get_current_user_id();
		if ( $user_id == 0 ) {
            return false;
        }
		$user_count = $wpdb->get_var(   $wpdb->prepare( 'SELECT count(*) as cnt FROM ' . get_option( 'wbk_db_prefix', '' ) . 'wbk_gg_calendars where user_id = %d', $user_id ) );
		if( $user_count > 0 ){
            return true;
        }
        return false;
    }
	public static function checkAccessToGgCalendar( $calendar_id ){
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
    // check if current user has access to specified service
	public static function checkAccessToService( $service_id ) {
        global $current_user;
        $user_id = get_current_user_id();
		if ( $user_id == 0 ) {
            return false;
        }
        global $wpdb;
		$user = $wpdb->get_var(  $wpdb->prepare( 'SELECT users FROM '. get_option('wbk_db_prefix', '' ) . 'wbk_services WHERE id = %d', $service_id ) );
		if( $user == ''  || is_null( $user) ){
			return false;
		}
 		$user_arr = json_decode( $user );
		if ( in_array( $user_id, $user_arr ) ){
            return true;
        }
        return false;
    }
    // check price (PayPal format)
	public static function checkPrice( $value ){
		if ( !is_numeric( $value ) ){
            return false;
        }
		if ( $value < 0 || $value > '9999999' ){
            return false;
        }
        return true;
    }
    // check email loop for multiple emails
	public static function checkEmailLoop( $value ){
 		if( substr_count( $value, '[appointment_loop_start]' ) == 1 && substr_count( $value, '[appointment_loop_end]' )  == 1 ){
	 		if( strpos( $value, '[appointment_loop_start]' ) < strpos( $value, '[appointment_loop_end]' ) ){
                return true;
            }
        }
        return false;
    }
    // check if coupon is applicable
	public static function checkCoupon( $coupon, $service_id ){
        global $wpdb;
        $data[0] = " SELECT * FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_coupons WHERE name = %s";
        $data[1] = [ $coupon ];

		$data = apply_filters( 'wbk_check_coupon', $data );
		$result = $wpdb->get_row( $wpdb->prepare( $data[0], $data[1] ) , ARRAY_A );

		if( $result == NULL ){
			return FALSE;
        }
        // check service
		if( $result['services'] != ''){

			$services = json_decode( $result['services'] );
			if( is_array( $services ) && !in_array( $service_id, $services ) ){

				return FALSE;
            }
        }
        // check used
		if( $result['maximum'] != 0 && $result['maximum'] != '' ){
			if( intval( $result['used'] ) >= $result['maximum'] ){
				return FALSE;
            }
        }
        // check date range
		if( $result['date_range'] != '' ){
			$range = explode( ' - ', $result['date_range'] );
			$start = strtotime( trim( $range[0] ) );
			$end = strtotime( trim( $range[1] ) );

			if( time() >= $start && time() <= $end ){
            } else {
				return FALSE;
            }
        }

		return array( $result['id'], $result['amount_fixed'], $result['amount_percentage'] );
    }
	public function getCouponData( $coupon_id ) {
        global $wpdb;
		$result = $wpdb->get_row( $wpdb->prepare( " SELECT * FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_coupons WHERE id = %d", $coupon_id ) , ARRAY_A );
		if( $result == NULL ){
			return FALSE;
        }
		return array( $result['id'], $result['amount_fixed'], $result['amount_percentage'] );
    }
	public static function validateId( $id, $table ) {
		$result = TRUE;
		if ( !is_numeric( $id ) ) {
			$result = FALSE;
        } else {
			$result = apply_filters( 'wbk_validate_id', $result,  array( $id, $table ) );
        }

        return $result;
    }
    /**
     * check if service exists
     * @param  interface  $service_id service id
     * @return boolean true if service exists
     */
    public static function is_service_exists($service_id) {
        if (!is_numeric($service_id)) {
			return false;
        }
        if (!in_array($service_id, WBK_Model_Utils::get_service_ids(), true)) {

            return false;
        }
        return true;
    }
	/**
	 * check if given variable is date and is in in the is_date_in_future
	 * @param  string $date date to be checked
	 * @return boolean true if date and is in future
	 */
    public static function is_date_in_future($date) {
        date_default_timezone_set(get_option('wbk_timezone', 'UTC'));
        $time = strtotime($date);
        if ($time == false) {
            return false;
        }
        if ($time < strtotime('today midnight')) {
            return false;
        }
		date_default_timezone_set('UTC');
        return true;
    }

	/**
	 * check if given variable is date
	 * @param  string $date date to be checked
	 * @return boolean true if date
	 */
	public static function is_date($date) {
		date_default_timezone_set(get_option('wbk_timezone', 'UTC'));
		$time = strtotime($date);
		if ($time == false) {
			return false;
		}
		date_default_timezone_set('UTC');
		return true;
	}
}
?>
