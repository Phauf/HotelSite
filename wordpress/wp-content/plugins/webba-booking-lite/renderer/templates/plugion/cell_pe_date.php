<?php
if ( !defined( 'ABSPATH' ) ) exit;

$field = $data[0];
$value = $data[2];

if ( is_null( $value ) || $value == '' ) {
    return;
}

if( isset( $field->get_extra_data()['date_format'] ) ){
    $date_format =  $field->get_extra_data()['date_format'];
} else {
    $date_format = get_option('date_format');
}

if ( isset( $field->get_extra_data()['time_zone'] ) ) {
    $time_zone =  $field->get_extra_data()['time_zone'];
} else {
    $time_zone =  get_option('timezone_string');
}

date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
$timestamp  = strtotime ( $value );
date_default_timezone_set( 'UTC' );

$date = wp_date( $date_format, $timestamp, new DateTimeZone( $time_zone ) );

echo $date;
