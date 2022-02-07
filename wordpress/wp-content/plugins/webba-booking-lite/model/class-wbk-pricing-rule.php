<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class WBK_Pricing_Rule extends WBK_Model_Object{
    public function __construct( $id ) {
        $this->table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_pricing_rules';
		parent::__construct( $id );

	}
    public function get_priority(){
        if( !isset( $this->fields['priority'] ) ){
            return 0;
        }
        return $this->fields['priority'];
    }

    public function get_type(){
        if( !isset( $this->fields['type'] ) ){
            return 0;
        }
        return $this->fields['type'];
    }

    public function get_date_range(){
        if( !isset( $this->fields['date_range'] ) ){
            return 0;
        }
        return $this->fields['date_range'];
    }

    public function get_action(){
        if( !isset( $this->fields['action'] ) ){
            return 0;
        }
        return $this->fields['action'];
    }

    public function get_amount(){
        if( !isset( $this->fields['amount'] ) ){
            return 0;
        }
        return $this->fields['amount'];
    }

    public function get_fixed_percent(){
        if( !isset( $this->fields['fixed_percent'] ) ){
            return 0;
        }
        return $this->fields['fixed_percent'];
    }

    public function get_days_number(){
        if( !isset( $this->fields['days_number'] ) ){
            return 0;
        }
        return $this->fields['days_number'];
    }

    public function get_custom_field_id(){
        if( !isset( $this->fields['custom_field_id'] ) ){
            return '';
        }
        return $this->fields['custom_field_id'];
    }

    public function get_custom_field_operator(){
        if( !isset( $this->fields['custom_field_operator'] ) ){
            return '';
        }
        return $this->fields['custom_field_operator'];
    }

    public function get_custom_field_value(){
        if( !isset( $this->fields['custom_field_value'] ) ){
            return '';
        }
        return $this->fields['custom_field_value'];
    }

    public function get_day_time(){
        if( !isset( $this->fields['day_time'] ) ){
            return null;
        }
        return $this->fields['day_time'];

    }

    public function get_multiply_amount(){
        if( !isset( $this->fields['multiply_amount'] ) ){
            return null;
        }
        return $this->fields['multiply_amount'];
    }

    public function get_number_of_seats_operator(){
        if( !isset( $this->fields['number_of_seats_operator'] ) ){
            return null;
        }
        return $this->fields['number_of_seats_operator'];
    }

    public function get_number_of_seats_value(){
        if( !isset( $this->fields['number_of_seats_value'] ) ){
            return null;
        }
        return $this->fields['number_of_seats_value'];
    }

    public function get_number_of_timeslots_operator(){
        if( !isset( $this->fields['number_of_timeslots_operator'] ) ){
            return null;
        }
        return $this->fields['number_of_timeslots_operator'];
    }

    public function get_number_of_timeslots_value(){
        if( !isset( $this->fields['number_of_timeslots_value'] ) ){
            return null;
        }
        return $this->fields['number_of_timeslots_value'];
    }
    public function get_only_same_service(){
        if( !isset( $this->fields['only_same_service'] ) ){
            return null;
        }
        return $this->fields['only_same_service'];
    }
    public function get_related_to_seats_number(){
        if( !isset( $this->fields['related_to_seats_number'] ) ){
            return null;
        }
        return $this->fields['related_to_seats_number'];
    }
}
