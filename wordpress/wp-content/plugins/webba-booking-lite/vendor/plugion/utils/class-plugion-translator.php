<?php

if ( !defined( 'ABSPATH' ) ) exit;
/*
 * This file is part of Plugion framework.
 * (c) plugion.com <hello@plugion.org>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class Plugion_Translator {

    private static $instance;

    private $strings;

    private function __construct() {
        $this->initialize_default();
    }

    public static function get_instance() {
        if ( is_null( self::$instance ) )  {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function load_textdomain(){
        load_textdomain( 'plugion', __DIR__  . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'plugion-' . get_user_locale() . '.mo' );
    }

    public function localize_script(){
        $translation_array = [
            'rest_url' =>  esc_url_raw( parse_url( rest_url(), PHP_URL_PATH )  ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'properties_error_list_title' => $this->translate_string( 'The following fields are wrong:' ),
            'loading' => $this->translate_string( 'Loading...' ),
            'element_not_found' => $this->translate_string( 'Element not found' ),
            'bad_request' => $this->translate_string( 'Bad request' ),
            'failed' => $this->translate_string( 'Failed' ),
            'forbidden'  => $this->translate_string( 'Forbidden' ),
            'no_filters_selected'  => $this->translate_string( 'Please, set at least one filter' ),
            'no_data_in_table' => $this->translate_string( 'No data available' ),
            'showing_start_to_end' => $this->translate_string( 'Here _START_ to _END_ of _TOTAL_ entries' ),
            'showing_0' => $this->translate_string( 'Showing 0 to 0 of 0 entries' ),
            'filtered_from_total' => $this->translate_string( '(filtered from _MAX_ total entries)' ),
            'show_menu_entries' => $this->translate_string( 'Show _MENU_ entries' ),
            'processing' => $this->translate_string( 'Processing...' ),
            'search' => $this->translate_string( 'Search' ),
            'no_matching_records' => $this->translate_string( 'No matching records found' ),
            'first' => $this->translate_string( 'First' ),
            'last' => $this->translate_string( 'Last' ),
            'next' => $this->translate_string( 'Next' ),
            'previous' => $this->translate_string( 'Previous' ),
            'activate_ascending' =>  $this->translate_string( ': activate to sort column ascending' ),
            'activate_descending'  =>  $this->translate_string( ': activate to sort column descending' ),
            'select_option' => $this->translate_string( 'select option' ),
            'ajax_url' => admin_url( 'admin-ajax.php')
        ];

        wp_localize_script( 'plugion', 'plugionl10n', $translation_array );
    }

    public function initialize_default(){
        $this->strings['%s is required'] = __( '%s is required', 'plugion' );
        $this->strings['%s must be a maximum of 256 characters'] = __( '%s must be a maximum of 256 characters', 'plugion' );
        $this->strings['Value of %s is not acceptable'] = __( 'Value of %s is not acceptable', 'plugion' );
        $this->strings['%s must be a maximum of 65535 characters'] = __( '%s must be a maximum of 65535 characters', 'plugion' );
        $this->strings['Validation of %s failed'] = __( 'Validation of %s failed', 'plugion' );
        $this->strings['Field %s is empty'] = __( 'Field %s is empty', 'plugion' );
        $this->strings['The following fields are wrong:'] = 'The following fields are wrong:';
        $this->strings['Loading...' ] = __( 'Loading...', 'plugion' );
        $this->strings['Element not found'] = __( 'Element not found', 'plugion' );
        $this->strings['Bad request'] = __( 'Bad request', 'plugion' );
        $this->strings['Failed'] = __( 'Failed', 'plugion' );
        $this->strings['Forbidden'] = __( 'Forbidden', 'plugion' );
        $this->strings['Please, set at least one filter'] = __( 'Please, set at least one filter', 'plugion' );
        $this->strings['No data available'] = __( 'No data available', 'plugion' );
        $this->strings['Here _START_ to _END_ of _TOTAL_ entries'] = __( 'Here _START_ to _END_ of _TOTAL_ entries', 'plugion' );
        $this->strings['Showing 0 to 0 of 0 entries'] = __( 'Showing 0 to 0 of 0 entries', 'plugion' );
        $this->strings['(filtered from _MAX_ total entries)'] = __( '(filtered from _MAX_ total entries)', 'plugion' );
        $this->strings['Show _MENU_ entries'] = __( 'Show _MENU_ entries', 'plugion' );
        $this->strings['Processing...'] = __( 'Processing...', 'plugion' );
        $this->strings['Search'] = __( 'Search', 'plugion' );
        $this->strings['No matching records found'] = __( 'No matching records found', 'plugion' );
        $this->strings['First'] = __( 'First', 'plugion' );
        $this->strings['Last'] = __( 'Last', 'plugion' );
        $this->strings['Next'] = __( 'Next', 'plugion' );
        $this->strings['Previous'] = __( 'Previous', 'plugion' );
        $this->strings[': activate to sort column ascending'] = __( ': activate to sort column ascending', 'plugion' );
        $this->strings[': activate to sort column descending'] = __( ': activate to sort column descending', 'plugion' );
        $this->strings['Filters for'] = __( 'Filters for', 'plugion' );
        $this->strings['Apply'] = __( 'Apply', 'plugion' );
        $this->strings['Apply and close'] = __( 'Apply and close', 'plugion' );
        $this->strings['Date'] = __( 'Date', 'plugion' );
        $this->strings['Time'] = __( 'Time', 'plugion' );
        $this->strings['Filters'] = __( 'Filters', 'plugion' );
        $this->strings['Save and close'] = __( 'Save and close', 'plugion' );
        $this->strings['New'] = __( 'New', 'plugion' );
        $this->strings['Are you sure?'] = __( 'Are you sure?', 'plugion' );
        $this->strings['Yes, delete it.'] = __( 'Yes, delete it.', 'plugion' );
        $this->strings['select option'] = __( 'select option', 'plugion' );

        $this->strings = apply_filters( 'plugion_strings', $this->strings );
    }
    public function translate_string( $string ){
        if( isset( $this->strings[$string] ) ){
            return $this->strings[$string];
        }
        return __( $string );
    }

    public function set_string_translation( $string, $value ){
        $this->strings[ $string ] = $value;
        return;

        if( isset( $this->strings[ $string ] ) ){
            $this->strings[ $string ] = $string;
            return true;
        } else {

        }
        return false;
    }

}
function plugion_translate_string( $string ){
    return Plugion_Translator::get_instance()->translate_string( $string );
}
function plugion_set_strings_translations( $strings ){
    foreach( $strings as $key => $value ){
        Plugion_Translator::get_instance()->set_string_translation( $key, $value );
    }
}
function plugion_restore_default_translation(){
    Plugion_Translator::get_instance()->initialize_default();
}
function plugion_localize_script(){
    Plugion_Translator::get_instance()->localize_script();
}



?>
