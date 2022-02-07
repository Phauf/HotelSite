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

if ( is_null( $value ) || $value == '' ) {
    return;
}
if ( isset( $field->get_extra_data()['time_zone'] ) ) {
    $time_zone =  $field->get_extra_data()['time_zone'];
} else {
    $time_zone =  get_option('timezone_string');
}
$date = new DateTime( $value );

$time_zone_obj = new DateTimeZone( $time_zone );

echo wp_date( get_option( 'date_format' ), $date->getTimestamp(), $time_zone_obj ) . ' ' . wp_date( get_option( 'time_format' ), $date->getTimestamp(), $time_zone_obj );
