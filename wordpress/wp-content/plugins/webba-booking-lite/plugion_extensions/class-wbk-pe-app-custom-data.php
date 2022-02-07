<?php
if ( !defined( 'ABSPATH' ) ) exit;

class WBK_PE_Appointment_Custom_Data extends Plugion_Custom_Field{
    public function __construct(){
        $this->init('wbk_app_custom_data');
    }
    public function render_cell( $data ){
        echo WBK_Renderer::load_template( 'plugion/cell_pe_app_custom_data', $data );

    }
    public function render_field( $data ){
        echo WBK_Renderer::load_template( 'plugion/input_pe_app_custom_data', $data );

    }
    public function validate( $input, $value, $slug, $field ) {
        $value = trim( sanitize_text_field( $value ) );
        return[ true,  $value  ];
    }
    public function field_type_to_sql_type( $arr_sql_parts, $type, $field ){
        if( $type == 'wbk_app_custom_data' ){
            return [ 'TEXT', 65535, '', '%s' ];
        }
        return $arr_sql_parts;
    }
}


?>
