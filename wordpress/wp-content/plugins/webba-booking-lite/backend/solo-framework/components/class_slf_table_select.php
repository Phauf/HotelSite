<?php
// Solo Framework table select component
if ( ! defined( 'ABSPATH' ) ) exit;
class SLFTableSelect extends SLFTableComponent {
	public function __construct( $title, $name, $value, $data_source ) {
		parent::__construct( $title, $name, $value, null );
		$this->data_source = $data_source;
	}
    public function renderCell(){
    	if( $this->name == 'time'){
			$format = get_option( 'time_format' );
            $data_source = $this->data_source;
            if( get_option( 'wbk_date_format_time_slot_schedule', 'start' ) == 'start' ){
                return wp_date( $format, $this->value, new DateTimeZone( date_default_timezone_get() ) );
            } else {

                if( isset( $data_source[1] ) ){
                    $app_id = $data_source[1];
                } else {
                    return wp_date( $format, $this->value, new DateTimeZone( date_default_timezone_get() ) );
                }
                if( $data_source[0][1] == 'getDurationOfAppointment' ){

                  $duration =  WBK_Db_Utils::getDurationOfAppointment( $app_id, TRUE );

                } else {

                  $duration =  WBK_Db_Utils::getDurationOfAppointment( $app_id );

                }

                if( $duration == 0 ){
                    return wp_date( $format, $this->value, new DateTimeZone( date_default_timezone_get() ) );
                }
                return  '<div class="wbk_mw_180">' .  date( $format, $this->value ) . ' - ' . date( $format, $this->value + $duration * 60 ) . '</div>';
            }
    	}
   		if( $this->name == 'status'){
   			switch ( $this->value) {
   				case 'pending':
   					return __( 'Awaiting approval', 'wbk' );
   					break;
   				case 'approved':
   					return  __( 'Approved', 'wbk' );
   					break;
   				case 'paid':
   					return __( 'Paid (awaiting approval)', 'wbk' );
   					break;
  				case 'paid_approved':
   					return  __( 'Paid (approved)', 'wbk' );
   				case 'arrived':
   					return  __( 'Arrived', 'wbk' );
   					break;
          case 'woocommerce':
            return  __( 'Managed by WooCommerce', 'wbk' );
            break;
   			}

   		}
		return $this->value;
    }
    public function renderControl(){
    	$html = '<label class="slf_table_component_label" >' . $this->title . '</label>';
		$html .= '<select class="slf_table_component_select slf_table_component_input" name="' . $this->name . '" data-type="select" data-init="' . $this->value . '"  >';
		$data_source = $this->data_source;
		$source_class = $data_source[0][0];
	   	$source_function = $data_source[0][1];
		$source_condition = $data_source[1];
 	 	$options = $source_class::$source_function( $source_condition );
		if( is_array( $options ) ){
			foreach( $options as $key => $value ){
				$selected = '';
				if( $key == $this->value  ){
					$selected = ' selected ';
				}
				$html .= '<option data-ext="' . $value[1] . '" ' . $selected . ' value="' . $key . '" >' . $value[0] . '</option>';
			}
		}
		$html .= '</select>';
		return $html;
    }
}
