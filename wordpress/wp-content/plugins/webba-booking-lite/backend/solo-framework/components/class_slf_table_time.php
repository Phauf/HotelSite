<?php
// Solo Framework table text component
if ( ! defined( 'ABSPATH' ) ) exit;

class SLFTableTime extends SLFTableComponent {


	public function __construct( $title, $name, $value, $validation ) {
		parent::__construct( $title, $name, $validation );
	}
	
    public function renderCell(){
		
    	$format = get_option( 'time_format' );
		return wp_date( $format,   $this->value, new DateTimeZone( date_default_timezone_get() ) );

    }
    public function renderControl(){
    	$format = get_option( 'time_format' );
    	$html = '<label class="slf_table_component_label" >' . $this->title . '</label>';
		$html .= '<input class="slf_table_component_input" name="' . $this->name . '" data-type="time"  type="text" value="' . wp_date( $format,   $this->value, new DateTimeZone( date_default_timezone_get() ) ) . '"  />';
		return $html;
    }


}
