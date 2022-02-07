<?php
if ( !defined( 'ABSPATH' ) ) exit;
/*
 * This file is part of Plugion framework.
 * (c) plugion.com <hello@plugion.org>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$field = $data[0];
$value = $data[2];
$row_data = $data[3];

if( isset( $row_data['duration'] ) && !is_null(isset( $row_data['duration'] ) ) && $row_data['duration'] != '' ){
    $duration = $row_data['duration'] * 60;
    $end = $value + $duration;
} else {
    $end = 0;
}

if ( is_null( $value ) || $value == '' ) {
    return;
}

if( isset( $field->get_extra_data()['time_format'] ) ){
    $time_format =  $field->get_extra_data()['time_format'];
} else {
    $time_format = get_option('time_format');
}

if ( isset( $field->get_extra_data()['time_zone'] ) ) {
    $time_zone =  $field->get_extra_data()['time_zone'];
} else {
    $time_zone =  get_option('timezone_string');
}

$time = wp_date( $time_format, $value, new DateTimeZone( $time_zone ) );
if( get_option( 'wbk_date_format_time_slot_schedule', 'start' ) == 'start-end' && $end != 0){
    $time .= ' - ' . wp_date( $time_format, $end, new DateTimeZone( $time_zone ) );

}
echo $time;
