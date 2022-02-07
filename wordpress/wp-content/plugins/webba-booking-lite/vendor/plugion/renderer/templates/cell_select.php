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
$ed = $field->get_extra_data();

if( $value == 'plugion_null' ){
    echo '';
}
if( isset( $field->get_extra_data()['items'] ) ){
    $items = $field->get_extra_data()['items'];
} elseif ( isset( $field->get_extra_data()['source'] )) {
    $function = $field->get_extra_data()['source'];
    $items = $function();
}
if( isset( $ed['multiple'] ) ){
    $result = array();
    $value = json_decode( $value, TRUE );
    if( is_array( $value ) ){
        foreach( $value as $key ){
            if( isset( $items[ $key ] ) ){
                $result[] = $items[ $key ];
            }
        }
    }
    echo implode( ', ', $result );
} else {
    if( isset( $items[$value] ) ){
        echo $items[$value];
    }
}
?>
