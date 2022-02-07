<?php
// Webba Booking time interval class
// check if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
require_once 'class_wbk_date_time_utils.php';
class WBK_Time_Slot	 {
	public $start;
	public $end;
	public $status;
	public $formated_time;
	public $formated_time_local;
	public $formated_time_backend;
	public $free_places;

	public function __construct( $start, $end ) {
		$this->start = absint( $start );
		$this->end = absint( $end );
	}
	public function getStart() {
		return $this->start;
	}
	public function getEnd() {
		return $this->end;
	}
	public function setStatus( $value ) {
		if ( is_array( $value ) ){
			$this->status = array();
			foreach ( $value as $item ) {
				array_push( $this->status, $item );

			}
		} else {
			$this->status = $value;
		}
	}
	public function getStatus() {
		return $this->status;
	}
	public function isTimeIn( $time ){
		if ( $time > $this->start && $time < $this->end ){
			return TRUE;
		}
		return FALSE;
	}
	public function set( $start, $end ){
		$this->start = $start;
		$this->end = $end;
	}
	public function get_formated_time(){
		return $this->formated_time;
	}
	public function set_formated_time( $input ){
		$this->formated_time = $input;
	}
	public function get_formated_time_local(){
		return $this->formated_time_local;
	}
	public function set_formated_time_local( $input ){
		$this->formated_time_local = $input;
	}
	public function get_formated_time_backend(){
		return $this->formated_time_backend;
	}
	public function set_formated_time_backend( $input ){
		$this->formated_time_backend = $input;
	}
	public function get_free_places(){
		return $this->free_places;
	}
	public function set_free_places( $input ){
		$this->free_places = $input;
	}
 

}
?>
