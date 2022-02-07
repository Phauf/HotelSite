<?php
if ( !defined( 'ABSPATH' ) ) exit;

class WBK_PE_Business_Hours extends Plugion_Custom_Field{
    public function __construct(){
        $this->init('wbk_business_hours');
    }
    public function render_cell( $data ){
        echo WBK_Renderer::load_template( 'plugion/cell_pe_business_hours', $data );

    }
    public function render_field( $data ){
        echo WBK_Renderer::load_template( 'plugion/input_pe_business_hours', $data );

    }
    public function validate( $input, $value, $slug, $field ) {
        $value = json_decode( $value, TRUE );

        if( !isset( $value['dow_availability'] ) ){
            return[ true, $value ];
        } else {
            $days_with_intersections = array();
            foreach ( $value['dow_availability'] as $item ){
                $current_day = $item['day_of_week'];
                $current_start = $item['start'];
                $current_end = $item['end'];
                $intersect_count = -1;
                foreach ( $value['dow_availability'] as $item_compare ) {
                    $compare_day = $item_compare['day_of_week'];
                    $compare_start = $item_compare['start'];
                    $compare_end = $item_compare['end'];
                    if( $current_day == $compare_day ){
                        if( WBK_Time_Math_Utils::check_range_intersect( $current_start, $current_end, $compare_start, $compare_end ) ){
                            $intersect_count++;
                        }
                    }
                }
                if( $intersect_count > 0 ){
                    $days_with_intersections[] = $current_day;
                }
            }
            $days_with_intersections = array_unique( $days_with_intersections );
            if( count( $days_with_intersections ) > 0 ){
                return[ false, __( 'Please, remove time range intersections.', 'wbk' ) ];
            }
        }
        return[ true, json_encode( $value ) ];
    }
    public function field_type_to_sql_type( $arr_sql_parts, $type, $field ){
        if( $type == 'wbk_business_hours' ){
            return [ 'TEXT', 65535, '', '%s' ];
        }
        return $arr_sql_parts;
    }
}


?>
