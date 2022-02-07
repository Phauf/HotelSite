<?php
if ( !defined( 'ABSPATH' ) ) exit;
/*
 * This file is part of Plugion framework.
 * (c) plugion.com <hello@plugion.org>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$value = $data[2];

$value = strip_tags( $value );
if( strlen( $value ) > 150 ){
    $value = substr($value, 0, 150 ) . ' ...';
}
echo $value;
?>
