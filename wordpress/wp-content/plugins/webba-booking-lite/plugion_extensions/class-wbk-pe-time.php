<?php
if ( !defined( 'ABSPATH' ) ) exit;

class WBK_PE_Time extends Plugion_Custom_Field{
    public function __construct(){
        $this->init('wbk_time');
    }
    public function render_cell( $data ){
        echo WBK_Renderer::load_template( 'plugion/cell_pe_time', $data );

    }
    public function render_field( $data ){
        echo WBK_Renderer::load_template( 'plugion/input_pe_time', $data );

    }
    public function validate( $input, $value, $slug, $field ) {
        if( !Plugion\Validator::check_integer( $value, 0, 2147483647 ) ){
            return[ false,  __( 'Ttime entered incorrectly', 'wbk' ), $field->get_title()  ];
        }
        return [ true, $value ];
    }
    public function field_type_to_sql_type( $arr_sql_parts, $type, $field ){
        if( $type == 'wbk_time' ){
            return [ 'int', 'unsigned NOT NULL', '', '%d' ];
        }
        return $arr_sql_parts;
    }
}


?>
