<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// webba booking Google integration class
class WBK_Google{
	protected
	$client;

	protected
	$calendar_id;

	protected
	$gg_calendar_id;

	public function init( $calendar_id ){
		return FALSE;
	}
	public function getAuthUrl(){
		return '';
	}
	public function connect(){
		return 2;
	}
	public function renderCalendarBlock(){
		return '';
	}
	public function processAuthCode( $authCode ){
		return 0;
	}
	protected function getAccessToken(){
		return '';
	}
 	protected function getGGCalendarId(){
		return 0;
	}
	protected function saveAccessToken( $access_token ){
	}
	public function getCalendarName(){
		return '';
	}
	public function clearToken(){
	}
	public function insertEvent( $title, $description, $start, $end, $time_zone, $calendar_id = '' ){
		return FALSE;
	}
	public function updateEvent( $event_id, $title, $description, $start, $end, $time_zone ){
 		return FALSE;
	}
	public function deleteEvent( $event_id ){
		return FALSE;
	}
	public function initCalendarByAuthcode( $code ){
		return FALSE;
	}
	public function getEventsTimeRanges( $start, $end ){
		return FALSE;
	}
}
?>
