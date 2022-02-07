<?php
//WBK service entity class
// check if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
class WBK_Service_deprecated extends WBK_Entity {
	// e-mail for notifications for service
	protected $email;
	// duration of appointment for service
	protected $duration;
	// interval between appointments
	protected $interval;
	// business hours
	protected $business_hours;
	// users
	protected $users;
	// step
	protected $step;
	// form
	protected $form;
	// quantity
	protected $quantity;
	// quantity_min
	protected $min_quantity;
	// priority
	protected $priority;
	// price
	protected $price;
	// payment methods
	protected $payment_methods;
 	// notification template
 	protected $notification_template;
 	// reminder tamplate
 	protected $reminder_template;
 	// prepare time
 	protected $prepare_time;
 	// date range
 	protected $date_range;
 	// gg calendars
 	protected $gg_calendars;
 	// invoice template
 	protected $invoice_template;
 	// multiple mode up limit
 	protected $multiple_limit;
 	// multiple mode low limit
 	protected $multiple_low_limit;

	public function __construct() {
		parent::__construct();
		$this->table_name = get_option('wbk_db_prefix', '' ) . 'wbk_services';
	}
	// set email
	public function setEmail( $value ) {
		$value = sanitize_email( $value );
		if ( WBK_Validator::checkEmail( $value ) ){
			$this->email = $value;
			return true;
		} else {
			array_push( $this->error_messages, __( 'incorrect email', 'wbk' ) );
			return false;
		}

	}
	// get email
	public function getEmail() {
		$value = sanitize_email( $this->email );
		return $value;
	}
	// set duration
	public function setDuration( $value ) {
		$value = absint( $value );
		if ( WBK_Validator::checkInteger( $value, 1, 1440 ) ){
			$this->duration = $value;
			return true;
		} else {
			array_push( $this->error_messages, __( 'incorrect duration', 'wbk' ) );
			return false;
		}
	}
	// get duration
	public function getDuration() {
		return absint( $this->duration );
	}
	// set multiple_limit
	public function setMultipleLimit( $value ) {
		if( $value == '' ){
			$this->multiple_limit = $value;
			return true;
		}
		$value = absint( $value );
		if ( WBK_Validator::checkInteger( $value, 0, 1000 ) ){
			$this->multiple_limit = $value;
			return true;
		} else {
			array_push( $this->error_messages, __( 'incorrect multiple limit', 'wbk' ) );
			return false;
		}
	}
 	public function setMultipleLowLimit( $value ) {
		if( $value == '' ){
			$this->multiple_low_limit = $value;
			return true;
		}
		$value = absint( $value );
		if ( WBK_Validator::checkInteger( $value, 0, 1000 ) ){
			$this->multiple_low_limit = $value;
			return true;
		} else {
			array_push( $this->error_messages, __( 'incorrect multiple low limit', 'wbk' ) );
			return false;
		}
	}
	// get multiple_limit
	public function getMultipleLimit() {
		return  $this->multiple_limit;
	}
	public function getMultipleLowLimit() {
		return  $this->multiple_low_limit;
	}
	// set prepare_time
	public function setPrepareTime( $value ) {
		$value = absint( $value );
		if ( WBK_Validator::checkInteger( $value, 0, 45000 ) ){
			$this->prepare_time = $value;
			return true;
		} else {
			array_push( $this->error_messages, __( 'incorrect prepare_time', 'wbk' ) );
			return false;
		}
	}
	// get prepare time
	public function getPrepareTime() {
		return absint( $this->prepare_time );
	}

	// set step
	public function setStep( $value ) {
		$value = absint( $value );
		if ( WBK_Validator::checkInteger( $value, 1, 1440 ) ){
			$this->step = $value;
			return true;
		} else {
			array_push( $this->error_messages, __( 'incorrect step', 'wbk' ) );
			return false;
		}
	}
	// get step
	public function getStep() {
		return absint( $this->step );
	}
	// set date range
	public function setDateRange( $value ) {
		$items = explode(  ' - ',  $value );
		if( count( $items ) != 2 ){
			$this->date_range = '';
			return true;
		}
 		if( strtotime( $items[0] ) === FALSE || strtotime( $items[1] ) === FALSE ){
			$this->date_range = '';
			return true;
 		}
 		$this->date_range = $value;
		return true;
	}
	// get date range
	public function getDateRange(){
		return $this->date_range;
	}
	public function getDateRangeStart(){
		$items = explode(  ' - ',  $this->date_range );
		if( count( $items ) != 2 ){
			return FALSE;
		}
		return strtotime( $items[0] );
	}
	public function getDateRangeEnd(){
		$items = explode(  ' - ',  $this->date_range );
		if( count( $items ) != 2 ){
			return FALSE;
		}
		return strtotime( $items[1] );
	}
	// set quantity
	public function setQuantity( $value ) {
		$value = absint( $value );
		if ( WBK_Validator::checkInteger( $value, 1, 1000000 ) ){
			$this->quantity = $value;
			return true;
		} else {
			array_push( $this->error_messages, __( 'incorrect quantity', 'wbk' ) );
			return false;
		}
	}
	// get quantity
	public function getQuantity() {
		return absint( $this->quantity );
	}
	// set min_quantity
	public function setMinQuantity( $value ) {
		$value = absint( $value );
		if ( WBK_Validator::checkInteger( $value, 1, 1000000 ) ){
			$this->min_quantity = $value;
			return true;
		} else {
			array_push( $this->error_messages, __( 'incorrect min_quantity', 'wbk' ) );
			return false;
		}
	}
	// get min_quantity
	public function getMinQuantity() {
		return absint( $this->min_quantity );
	}
	// set priority
	public function setPriority( $value ) {
		$value = absint( $value );
		if ( WBK_Validator::checkInteger( $value, 0, 1000000 ) ){
			$this->priority = $value;
			return true;
		} else {
			array_push( $this->error_messages, __( 'incorrect priority', 'wbk' ) );
			return false;
		}
	}
	// get priority
	public function getPriority() {
		return absint( $this->priority );
	}
	// set price
	public function setPrice( $value ) {
		if ( WBK_Validator::checkPrice( $value ) ){
			$this->price = $value;
			return true;
		} else {
			array_push( $this->error_messages, __( 'incorrect price', 'wbk' ) );
			return false;
		}
	}
	// get price
	public function getPrice( $time = null ) {
		$price =  $this->price;
		if( is_null( $time ) ){
			$time = 0;
		}
		return apply_filters( 'wbk_service_price', $price, $time );
	}

	// set interval
	public function setInterval( $value ) {
		$value = absint( $value );
		if ( WBK_Validator::checkInteger( $value, 0, 1440 ) ){
			$this->interval = $value;

			return true;
		} else {
			array_push( $this->error_messages, __( 'incorrect interval', 'wbk' ) );
			return false;
		}

	}
	// get interval
	public function getInterval() {
		return absint( $this->interval );
	}
	// set business hours
	public function setBusinessHours( $value ) {
		$value = trim( $value );
		if ( $value == '' ) {
			return true;
		}
		$this->business_hours = $value;
		return true;
		if ( WBK_Validator::checkBusinessHours( $value ) ){
			$this->business_hours = $value;
			return true;
		} else {
			return false;
		}
	}
	// get business hours
	public function getBusinessHours() {
		return $this->business_hours;
	}
	// set users
	public function setUsers( $value ) {
		if ( $value == '' ){
			$this->users = '';
			return true;
		}
		$arr_items = json_decode( $value );
		if( is_array($arr_items) ){
			foreach( $arr_items as $item ) {
				if ( !WBK_Validator::checkInteger( $item, 0, 9999999 ) ){
					return false;
				}
			}
		}
		$this->users = $value;
		return true;
	}
	// get users
	public function getUsers() {
		return $this->users;
	}


	// set users
	public function setGgCalendars( $value ) {
		if ( $value == '' ){
			$this->gg_calendars = '';
			return true;
		}
		$this->gg_calendars = $value;
		return true;
	}
	// get users
	public function getGgCalendars() {
		return $this->gg_calendars;
	}

    // set payment methods
	public function setPayementMethods( $value ) {

		$this->payment_methods = $value;
		return true;
	}
	// get users
	public function getPayementMethods() {
		return $this->payment_methods;
	}

	// set form
	public function setForm( $value ) {
		if ( !WBK_Validator::checkInteger( $value, 0, 9999999 ) ){
			return false;
		}
		$this->form = $value;
		return true;
	}
	// get form
	public function getForm() {
		return $this->form;
	}
	// set notification template
	public function setNotificationTemplate( $value ) {
		if ( !WBK_Validator::checkInteger( $value, 0, 9999999 ) ){
			return false;
		}
		$this->notification_template = $value;
		return true;
	}
	// get notification template
	public function getNotificationTemplate() {
		return $this->notification_template;
	}
	// set reminder template
	public function setReminderTemplate( $value ) {
		if ( !WBK_Validator::checkInteger( $value, 0, 9999999 ) ){
			return false;
		}
		$this->reminder_template = $value;
		return true;
	}
	// get reminder template
	public function getReminderTemplate() {
		return $this->reminder_template;
	}
	// set invoice template
	public function setInvoiceTemplate( $value ) {
		if ( !WBK_Validator::checkInteger( $value, 0, 9999999 ) ){
			return false;
		}
		$this->invoice_template = $value;
		return true;
	}
	// get invoice template
	public function getInvoiceTemplate() {
		return $this->invoice_template;
	}

	// load row from db and put class properties
	public function load () {

		$result = parent::load();
 		if ( !$result ) {
 			//return false;
 		}
 		if ( !$this->setEmail( $result->email ) ) {
			//return false;
		}
 		if ( !$this->setDuration( $result->duration ) ) {
			//return false;
		}
		if ( !$this->setInterval( $result->interval_between ) ) {
			//return false;
		}
		if ( !$this->setStep( $result->step ) ) {
			//return false;
		}
		if( isset( $result->business_hours ) ){
			if ( !$this->setBusinessHours( $result->business_hours ) ) {
				//return false;
			}
		}

		if ( !$this->setUsers( $result->users ) ) {
			//return false;
		}
		if ( !$this->setForm( $result->form ) ) {
			//return false;
		}
		if ( !$this->setQuantity( $result->quantity ) ) {
			//return false;
		}
		if ( !$this->setMinQuantity( $result->min_quantity ) ) {
			//return false;
		}
		if ( !$this->setPriority( $result->priority ) ) {
			//return false;
		}
		if ( !$this->setPrice( $result->price ) ) {
			//return false;
		}
		if ( !$this->setPayementMethods( $result->payment_methods ) ) {
			//return false;
		}
		if ( !$this->setNotificationTemplate( $result->notification_template ) ) {
			//return false;
		}
		if ( !$this->setReminderTemplate( $result->reminder_template ) ) {
			//return false;
		}
		if ( !$this->setPrepareTime( $result->prepare_time ) ) {
			//return false;
		}
		if ( !$this->setDateRange( $result->date_range ) ) {
			 //return false;
		}
		if( !$this->setGgCalendars( $result->gg_calendars ) ){
			//return false;
		}
		if( !$this->setInvoiceTemplate( $result->invoice_template ) ){
			//return false;
		}
		if( !$this->setMultipleLimit( $result->multi_mode_limit ) ){
			//return false;
		}
		if( !$this->setMultipleLowLimit( $result->multi_mode_low_limit ) ){
			//return false;
		}
 		return true;
  	}
  	// update service
	public function update() {
		global $wpdb;

		if ( parent::update() === false ) {
			return false;
		}

  		if ( $wpdb->update(
				$this->table_name,
				array(
					'email' => $this->getEmail(),
					'duration' => $this->getDuration(),
					'interval_between' => $this->getInterval(),
					'step' => $this->getStep(),
					'business_hours' => $this->getBusinessHours(),
					'users'	=> $this->getUsers(),
					'form'	=> $this->getForm(),
					'quantity' => $this->getQuantity(),
					'min_quantity' => $this->getMinQuantity(),
					'price' => $this->getPrice(),
					'payment_methods' =>$this->getPayementMethods(),
					'notification_template' => $this->getNotificationTemplate(),
					'reminder_template' => $this->getReminderTemplate(),
					'prepare_time' => $this->getPrepareTime(),
					'date_range' => $this->getDateRange(),
					'gg_calendars' => $this->getGgCalendars(),
					'invoice_template' => $this->getInvoiceTemplate(),
					'description' => $this->getDescription(),
					'multi_mode_limit' => $this->getMultipleLimit(),
					'multi_mode_low_limit' => $this->getMultipleLowLimit(),
					'priority' => $this->getPriority()

				),
				array( 'id' => $this->getId() ),

				array(
					'%s',
					'%d',
					'%d',
					'%d',
					'%s',
					'%s',
					'%d',
					'%d',
					'%d',
					'%f',
                    '%s',
                    '%d',
                    '%d',
                    '%d',
                    '%s',
                    '%s',
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                    '%d'
				),
				array( '%d' )
			) === false ) {
			return false;
		} else {
			return true;
		}
	}

	// add service
	public function add() {
		global $wpdb;

		$service_dataandformat[] = array(
					'name' => $this->getName(),
					'description' => $this->getDescription(),
					'email' => $this->getEmail(),
					'duration' => $this->getDuration(),
					'step' => $this->getStep(),
					'interval_between' => $this->getInterval(),
					'business_hours' => $this->getBusinessHours(),
					'users'	=> $this->getUsers(),
					'form'	=> $this->getForm(),
					'quantity'	=> $this->getQuantity(),
					'min_quantity'	=> $this->getMinQuantity(),
					'price' => $this->getPrice(),
					'payment_methods' =>$this->getPayementMethods(),
					'notification_template' => $this->getNotificationTemplate(),
					'reminder_template' => $this->getReminderTemplate(),
					'prepare_time' => $this->getPrepareTime(),
					'date_range' => $this->getDateRange(),
					'gg_calendars' => $this->getGgCalendars(),
					'invoice_template' => $this->getInvoiceTemplate(),
					'multi_mode_limit' => $this->getMultipleLimit(),
					'multi_mode_low_limit' => $this->getMultipleLowLimit(),
					'priority'	=> $this->getPriority()
		);

		$service_dataandformat[] = array(
					'%s',
					'%s',
					'%s',
					'%d',
					'%d',
					'%d',
					'%s',
					'%s',
					'%d',
					'%d',
					'%d',
					'%f',
					'%s',
					'%d',
                    '%d',
                    '%d',
                    '%s',
                    '%s',
                    '%d',
                    '%s',
                    '%s',
		        	'%d' );

		$service_dataandformat = apply_filters( 'wbk_add_service_data', $service_dataandformat );

		$wpdb->insert( $this->table_name, $service_dataandformat[0], $service_dataandformat[1] );
		$new_id = $wpdb->insert_id;

			return $new_id;

	}
	// delete service from database
	public function delete() {
		global $wpdb;
		$result = parent::delete();
		$wpdb->query( $wpdb->prepare( "DELETE FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_locked_time_slots WHERE service_id = %d", $this->getId() ) );
  		$wpdb->query( $wpdb->prepare( "DELETE FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_days_on_off WHERE service_id = %d", $this->getId() ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE service_id = %d", $this->getId() ) );
  		return $result;
	}
}
?>
