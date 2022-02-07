<?php
if ( !defined( 'ABSPATH' ) ) exit;
if( !class_exists( 'Plugion\Plugion' ) ){
    include __DIR__ . DIRECTORY_SEPARATOR . 'plugion.php';
} else {
    return;
}

/**
 * return instance of Plugion object
 */
if( !function_exists('Plugion') ){
	function Plugion() {
	    return Plugion\Plugion::instance();
	}
}
