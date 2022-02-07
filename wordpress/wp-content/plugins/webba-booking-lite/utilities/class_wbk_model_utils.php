<?php
// check if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
class WBK_Model_Utils {
    /**
     * get ids of all services
     * @return array ids of services
     */
    public static function get_service_ids( $restricted = false ) {
        global $wpdb;
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( get_option('wbk_db_prefix', '' ) . 'wbk_services' ) );
        if ( !$wpdb->get_var( $query ) == get_option('wbk_db_prefix', '' ) . 'wbk_services' ) {
            return array();
        }

        $sql = "SELECT id,users FROM " . get_option('wbk_db_prefix', '' ) . "wbk_services ";
        $sql = apply_filters( 'wbk_get_service_ids', $sql );
        $order_type = get_option( 'wbk_order_service_by', 'a-z' );
        if(	$order_type == 'a-z' ){
            $sql .= " order by name asc ";
        }
        if ( $order_type == 'priority') {
            $sql .= " order by priority desc";
        }
        if ( $order_type == 'priority_a') {
            $sql .= " order by priority asc";
        }
        $rows = $wpdb->get_results( $sql, ARRAY_A );
        $result = array();
        foreach( $rows as $item ){
            if( $restricted ){
                $user = wp_get_current_user();
                if( in_array( 'administrator', $user->roles, true ) || ( is_multisite() && !is_super_admin() ) ){
                    $result[] = $item['id'];
                } else {
                    $users = json_decode( $item['users'] );
                    if( is_array( $users) ){
                        if( in_array(  get_current_user_id(), $users ) ){
                            $result[] = $item['id'];
                        }
                    }
                }
            } else {
                $result[] = $item['id'];
            }
        }

        return $result;
    }

    /**
     * get pairs of service id - names
     * @return array array of id-name pair
     */
    public static function get_services( $restricted = false ) {
        global $wpdb;
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( get_option('wbk_db_prefix', '' ) . 'wbk_services' ) );
        if ( !$wpdb->get_var( $query ) == get_option( 'wbk_db_prefix', '' ) . 'wbk_services' ) {
            return array();
        }
        $sql = "SELECT id,name,users FROM " . get_option('wbk_db_prefix', '' )  . "wbk_services ";
        $sql = apply_filters( 'wbk_get_services', $sql );
        $order_type = get_option( 'wbk_order_service_by', 'a-z' );
        if(	$order_type == 'a-z' ){
            $sql .= " order by name asc ";
        }
        if ( $order_type == 'priority') {
            $sql .= " order by priority desc";
        }
        if ( $order_type == 'priority_a') {
            $sql .= " order by priority asc";
        }
        $rows = $wpdb->get_results( $sql, ARRAY_A );
        $result = array();
        foreach( $rows as $item ){
            if( $restricted ){
                $user = wp_get_current_user();
                if( in_array( 'administrator', $user->roles, true ) || ( is_multisite() && !is_super_admin() ) ){
                    $result[ $item['id'] ] = $item['name'];
                } else {
                    $users = json_decode( $item['users'] );
                    if( is_array( $users) ){
                        if( in_array(  get_current_user_id(), $users ) ){
                            $result[ $item['id'] ] = $item['name'];
                        }
                    }
                }
            } else {
                $result[ $item['id'] ] = $item['name'];
            }
        }
        return $result;
    }

    /**
     * get pairs of email template id - name
     * @return array  of id-name pair
     */
    public static function get_email_templates(){
        global $wpdb;
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( get_option( 'wbk_db_prefix', '' ) . 'wbk_email_templates' ) );
        if ( !$wpdb->get_var( $query ) == get_option( 'wbk_db_prefix', '' ) . 'wbk_email_templates' ) {
            return array();
        }
        $sql = "SELECT id,name FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_email_templates ";
        $rows = $wpdb->get_results( $sql, ARRAY_A );
        $result_converted = array();
        foreach( $rows as $item ){
            $result_converted[ $item['id'] ] = $item['name'];
        }
        return $result_converted;
    }

    /**
     * get pairs of google calendars id - name
     * @return array  of id-name pair
     */
    public static function get_google_calendars(){
        global $wpdb;
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( get_option( 'wbk_db_prefix', '' ) . 'wbk_gg_calendars' ) );
        if ( !$wpdb->get_var( $query ) == get_option( 'wbk_db_prefix', '' ) . 'wbk_gg_calendars' ) {
            return array();
        }
        $sql = "SELECT id,name FROM  " . get_option( 'wbk_db_prefix', '' ) . "wbk_gg_calendars ";
        $result = $wpdb->get_results( $sql, ARRAY_A );
        $result_converted = array();
        foreach( $result as $item ){
            $result_converted[ $item['id'] ] = $item['name'];
        }
        return $result_converted;

    }

    /**
     * list of available modes of google calendars
     * @return array key -title pair array
     */
    public static function get_gg_calendar_modes(){
        $result = array(
            'One-way'  => __( 'One-way (export)', 'wbk' ),
            'One-way-import'  => __( 'One-way (import)', 'wbk' ),
            'Two-ways'	=> __( 'Two-ways', 'wbk' ),
        );
        return $result;
    }

    /**
     * extract custom fields value from extr-data json_decode
     * @param  string $data extra-data
     * @param  int $id custom fiekd id
     * @return string value of the custom field or null if not set
     */
    public static function extract_custom_field_value( $data, $id ){
        if( $data == '' ){
            return null;
        }
        $data = json_decode( $data );
        if( $data === NULL ){
            return null;
        }
        foreach( $data as $item ){
            if( !is_array( $item ) ){
                continue;
            }
            if( count( $item ) != 3 ){
                contnue;
            }
            if( trim( $item[0] ) == trim( $id ) ){

                return $item[2];
            }
        }

        return null;
    }
    /**
     * get array of available columns on the
     * Appoointments page
     * @param boolean $keys_only return only keys
     * @return array
     */
    public static function get_appointment_columns( $keys_only = false ){
        if( $keys_only ){
            return array(   'service_id', 'day', 'time', 'quantity', 'name', 'email', 'description', 'extra', 'status', 'payment_method', 'moment_price', 'coupon' );
        }
        return array( 'service_id'  => __( 'Service','wbk' ),
                      'created_on'  => __( 'Created on','wbk' ),
                      'day'	        => __( 'Date','wbk' ),
                      'time'        => __( 'Time','wbk' ),
                      'quantity'    => __( 'Places booked', 'wbk' ),
                      'name'        => __( 'Customer name','wbk' ),
                      'email'       => __( 'Customer email', 'wbk' ),
                      'phone'       => __( 'Phone', 'wbk' ),
                      'description' => __( 'Customer comment', 'wbk' ),
                      'extra'       => __( 'Custom fields', 'wbk' ),
                      'status'      => __( 'Status', 'wbk' ),
                      'payment_method' => __( 'Payment method', 'wbk' ),
                      'moment_price' => __( 'Price', 'wbk' ),
                      'coupon' => __( 'Coupon', 'wbk' ),
                      'ip' => __( 'User IP', 'wbk' )
                );
    }


    /**
     * get available appointment statuses
     * @return array array
     */
    static function get_appointment_status_list(){
		$result = array( 'pending' => 	   __( 'Awaiting approval', 'wbk' ),
						 'approved'	=>     __( 'Approved', 'wbk' ),
						 'paid'	=> 		   __( 'Paid (awaiting approval)', 'wbk' ),
						 'paid_approved'=> __( 'Paid (approved)', 'wbk' ),
						 'arrived'	=>     __( 'Arrived', 'wbk' ),
						 'woocommerce'	=> __( 'Managed by WooCommerce', 'wbk' ),
                         'added_by_admin_not_paid'	=> __( 'Added by the administrator (not paid)', 'wbk' ),
                         'added_by_admin_paid'	=> __( 'Added by the administrator (paid)', 'wbk' ),

					   );
		return $result;
	}


    /**
     * get services in category
     * @param  int $category_id id of the category
     * @return array ids of the services
     */
    static function get_services_in_category( $category_id ){
        global $wpdb;
        $list =  $wpdb->get_var( $wpdb->prepare( "SELECT category_list FROM " . get_option('wbk_db_prefix', '' ) . "wbk_service_categories WHERE id = %d", $category_id ) );
        if( $list == '' ){
            return FALSE;
        }
        return json_decode( $list );
    }

    /**
     * get services with the same category
     * as given service
     * @param  int $service_id service id
     * @return array ids of services
     */
    public static function get_services_with_same_category( $service_id ) {
        global $wpdb;
        $result = array();
        $categories = self::get_service_categories();
        foreach ( $categories as $key => $value) {
            $services = self::get_services_in_category( $key );
            if( in_array( $service_id, $services)){
                foreach($services as $current_service ) {
                    if( $current_service != $service_id){
                        $result[] = $current_service;
                    }
                }
            }
        }
        $result = array_unique( $result );
        return $result;
    }

    /**
     * get list of service categories
     * @return array ids of categories
     */
    public static function get_service_categories(){
        global $wpdb;
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( get_option('wbk_db_prefix', '' ) . 'wbk_service_categories' ) );
        if ( !$wpdb->get_var( $query ) == get_option('wbk_db_prefix', '' ) . 'wbk_service_categories' ) {
            return array();
        }
        $sql = "SELECT id FROM " . get_option('wbk_db_prefix', '' ) . "wbk_service_categories";
        $sql =apply_filters( 'wbk_get_categories', $sql );
        $categories = $wpdb->get_col( $sql );
        $result = array();
        foreach( $categories as $category_id ) {
            $name =  $wpdb->get_var( $wpdb->prepare( " SELECT name FROM " . get_option('wbk_db_prefix', '' ) . "wbk_service_categories WHERE id = %d", $category_id ) );
            $result[ $category_id ] = $name;
        }
        return $result;
    }

    /**
     * get IDds of service catgegories
     * @return array array of the service categories
     */
    public static function get_service_category_ids() {
        global $wpdb;
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( get_option('wbk_db_prefix', '' ) . 'wbk_service_categories' ) );
        if ( !$wpdb->get_var( $query ) == get_option('wbk_db_prefix', '' ) . 'wbk_service_categories' ) {
            return array();
        }
        $sql = "SELECT id FROM " . get_option('wbk_db_prefix', '' ) . "wbk_service_categories ";
        $rows = $wpdb->get_results( $sql, ARRAY_A );
        $result = array();
        foreach( $rows as $item ){
            $result[] = $item['id'];
        }
        return $result;
    }

    /**
     * get booking ids by day and array of services
     * @param  int $day timestamp of the day
     * @param  array $service_ids array of service ids
     * @return array id of the bookings
     */
    public static function get_booking_ids_by_day_service( $day, $service_id ){
        global $wpdb;
        $result =  $wpdb->get_col( $wpdb->prepare( "SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments where day=%d and service_id=%d ", $day, $service_id ) );
        return $result;
    }
    public static function get_booking_ids_by_day_service_email( $day, $service_id, $email ){
        global $wpdb;
        $result =  $wpdb->get_col( $wpdb->prepare( "SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments where day=%d and service_id=%d and email=%s", $day, $service_id, $email ) );
        return $result;
    }
    public static function get_booking_ids_by_range_service( $start, $end, $service_id ){
        global $wpdb;
        $result =  $wpdb->get_col( $wpdb->prepare( "SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments where time >= %d AND time < %d AND service_id=%d ", $start, $end, $service_id ) );
        return $result;
    }
    public static function get_booking_ids_by_email( $email ){
        global $wpdb;
        $result = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments where email = %s order by time desc", $email ) );
        return $result;
    }
    public static function get_booking_ids_for_today_by_service( $service_id ){
        global $wpdb;
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $today = strtotime('today midnight');
        $result = $wpdb->get_col( $wpdb->prepare( " SELECT id FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE service_id=%d AND day=%d  ORDER BY time ", $service_id, $today  ) );
        date_default_timezone_set( 'UTC' );
        return $result;
    }
    public static function get_coupons() {
        global $wpdb;
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( get_option( 'wbk_db_prefix', '' ) . 'wbk_coupons' ) );
        if ( !$wpdb->get_var( $query ) == get_option( 'wbk_db_prefix', '' ) . 'wbk_coupons' ) {
            return array();
        }
        $sql = "SELECT id,name FROM  " . get_option( 'wbk_db_prefix', '' ) . "wbk_coupons ";
        $result = $wpdb->get_results( $sql, ARRAY_A );
        $result_converted = array();
        foreach( $result as $item ){
            $result_converted[ $item['id'] ] = $item['name'];
        }
        return $result_converted;
    }

    public static function get_pricing_rules(){
        global $wpdb;
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( get_option( 'wbk_db_prefix', '' ) . 'wbk_pricing_rules' ) );
        if ( !$wpdb->get_var( $query ) == get_option( 'wbk_db_prefix', '' ) . 'wbk_pricing_rules' ) {
            return;
        }
        $sql = "SELECT id,name FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_pricing_rules";
        $result = $wpdb->get_results( $sql, ARRAY_A );
        $result_converted = array();
        foreach( $result as $item ){
            $result_converted[ $item['id'] ] = $item['name'];
        }
        return $result_converted;
    }

    public static function set_amount_for_booking( $booking_id, $amount, $details = '' ){
        global $wpdb;
        $result = $wpdb->update(  get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
                				  array( 'moment_price' => $amount, 'amount_details'  => $details ),
                				  array( 'id' => $booking_id ),
                				  array( '%s', '%s' ),
                				  array( '%d' ) );

    }
    public static function set_status_for_booking( $booking_id, $status ){
        global $wpdb;
        $booking = new WBK_Booking( $booking_id );
        $prev_status = $booking->get('status');

        $wpdb->update(  get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
                                  array( 'status' => $status  ),
                                  array( 'id' => $booking_id ),
                                  array( '%s' ),
                                  array( '%d' ) );

        $wpdb->update(  get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
                                array( 'prev_status' => $prev_status  ),
                                array( 'id' => $booking_id ),
                                array( '%s' ),
                                array( '%d' ) );
    }

    public static function set_end_for_booking( $booking_id ){
        global $wpdb;
        $booking = new WBK_Booking( $booking_id );
        if( $booking->get_name() == '' ){
            return;
        }

        $result = $wpdb->update(  get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
                				  array( 'end' => $booking->get_end() ),
                				  array( 'id' => $booking_id ),
                				  array( '%d' ),
                				  array( '%d' ) );

    }
    static function get_bookings_by_service_and_time( $service_id, $time ){
		global $wpdb;
		$booking_ids = $wpdb->get_col( $wpdb->prepare (
			"
			SELECT      id
			FROM        " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments
 			WHERE       service_id = %d
			AND 		time  = %d
			",
			$service_id,
			$time
		) );
		return $booking_ids;
	}

}
?>
