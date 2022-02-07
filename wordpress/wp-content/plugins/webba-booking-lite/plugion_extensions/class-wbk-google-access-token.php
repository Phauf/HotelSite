<?php
if ( !defined( 'ABSPATH' ) ) exit;

class WBK_Google_Access_Token extends Plugion_Custom_Field{
    public function __construct(){
        $this->init('wbk_google_access_token');
    }

    public function render_cell( $data ){
        echo WBK_Renderer::load_template( 'plugion/cell_pe_google_access_token', $data );
    }

    public function render_field( $data ){
        echo WBK_Renderer::load_template( 'plugion/input_pe_google_access_token', $data );
    }

    public function validate( $input, $value, $slug, $field ) {
        return[ true, $value ];
    }

    public function field_type_to_sql_type( $arr_sql_parts, $type, $field ){
        if( $type == 'wbk_google_access_token' ){
            return [ 'TEXT', 65535, '', '%s' ];
        }
        return $arr_sql_parts;
    }
}

?>
