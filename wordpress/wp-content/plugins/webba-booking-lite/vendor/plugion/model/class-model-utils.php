<?php
if ( !defined( 'ABSPATH' ) ) exit;
/*
 * This file is part of Plugion framework.
 * (c) plugion.com <hello@plugion.org>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */



if (!defined('ABSPATH')) {
    exit;
}
/**
 * Model class
 */
class Plugion_Model_Utils {
    /**
     * clean up string for using in SQL statements
     * @var string
     * @param mixed $string
     */
    public static function clean_up_string( $string ) {
        $string = str_replace(' ', '_', $string);
        return preg_replace('/[^A-Za-z0-9\_]/', '', $string);
    }

}
