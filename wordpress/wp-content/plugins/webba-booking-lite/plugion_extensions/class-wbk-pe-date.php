<?php
if ( !defined( 'ABSPATH' ) ) exit;

class WBK_PE_Date extends Plugion_Custom_Field{
    public function __construct(){
        $this->init('wbk_date');
    }
    public function render_cell( $data ){
        echo WBK_Renderer::load_template( 'plugion/cell_pe_date', $data );

    }
    public function render_field( $data ){
        echo WBK_Renderer::load_template( 'plugion/input_pe_date', $data );

    }
    public function validate( $input, $value, $slug, $field ) {
        date_default_timezone_set( get_option( 'wbk_timezone', 'UTC' ) );
        $value = DateTime::createFromFormat( 'Y-m-d H:i:s',  $value . ' 0:00:00' );
        if ( $value == FALSE ){
            return[ false, sprintf( plugion_translate_string( '%s must be a date' ), $field->get_title() ) ];
        }
        $value = $value->getTimestamp();
        date_default_timezone_set( 'UTC' );
        return [ true, $value ];
    }
    public function field_type_to_sql_type( $arr_sql_parts, $type, $field ){
        if( $type == 'wbk_date' ){
            return [ 'int', 'unsigned NOT NULL', '', '%d' ];
        }
        return $arr_sql_parts;
    }
}


?>
