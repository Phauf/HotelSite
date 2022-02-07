<?php
// check if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
class WBK_Time_Math_Utils {
	public static function check_range_intersect( $start, $end, $start_compare, $end_compare ){
		if ( $start_compare == $start ){
			return true;
		}
		if ( $start_compare > $start && $start_compare < $end ){
			return true;
		}
		if ( $end_compare > $start && $end_compare <= $end  ){
			return true;
		}
		if ( $start >= $start_compare && $end <= $end_compare  ){
			return true;
		}
		if ( $start <= $start_compare && $end >= $end_compare  ){
			return true;
		}
		return false;
	}
	public static function adjust_times( $time_1, $time_2, $time_zone ){
		$tz = new DateTimeZone( $time_zone );
		$transition = $tz->getTransitions( $time_1, $time_1 );
		$offset1 = $transition[0]['offset'];
		$transition = $tz->getTransitions( $time_1 + $time_2, $time_1 + $time_2 );
		$offset2 = $transition[0]['offset'];
		$difference = $offset1 - $offset2;
		if( $difference < 0 ){
			$difference = 0;
		}
 		return $time_1 + $time_2 + $difference;
	}

}
?>
