<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class WBK_Booking extends WBK_Model_Object{
    public function __construct( $id ) {
        $this->table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments';
		parent::__construct( $id );

	}

    /**
     * get start time of the bookings
     * @return int timestamp
     */
    public function get_start(){
        if( isset( $this->fields['time'] ) ){
            return $this->fields['time'];
        }
        return 0;
    }

    /**
     * get end time of the bookings
     * based on start time and duration
     * @return int timestamp
     */
    public function get_end(){
        if( !isset( $this->fields['time'] ) ){
            return null;
        }
        return  $this->fields['time'] + $this->fields['duration'] * 60;
    }

    public function set_parameters( $date,
                                    $time,
                                    $service_id,
                                    $quantity = 1,
                                    $name = '',
                                    $phone = '',
                                    $description = '',
                                    $custom_data = null
                                    ){
       $this->set( 'day', $date);
       $this->set( 'time', $time );
       $this->set( 'service_id', $service_id );
       $this->set( 'quantity', $quantity );
       $this->set( 'name', $name );
       $this->set( 'phone', $phone );
       $this->set( 'description', $description );
       $this->set( 'extra', $custom_data );

    }
    /**
     * get day of the bookings
     * @return int timestamp
     */
    public function get_day(){
        if( isset( $this->fields['day'] ) ){
            return $this->fields['day'];
        }
        return 0;
    }

    /**
     * get end time of the bookings
     * including interval between
     * based on start time and duration
     * @return int timestamp
     */
    public function get_full_end(){
        if( !isset( $this->fields['service_id'] ) ){
            return null;
        }
        $service = new WBK_Service( $this->fields['	service_id'] );
        return  $this->fields['time'] + $this->fields['duration'] * 60 +  $service->get_interval_between() * 60;
    }

    /**
     * get number of booked places
     * @return int number of booked places
     */
    public function get_quantity(){
        if( !isset( $this->fields['quantity'] ) ){
            return 0;
        }
        return $this->fields['quantity'];
    }

    /**
     * get service id
     * @return int service id
     */
    public function get_service(){
        if( !isset( $this->fields['service_id'] ) ){
            return 0;
        }
        return $this->fields['service_id'];
    }
    /**
     * get price
     * @return float booking price
     */
    public function get_price(){
        if( !isset( $this->fields['moment_price'] ) || $this->fields['moment_price']  == '' ){
            return 0;
        }
        return $this->fields['moment_price'];
    }

    /**
     * get phone
     * @return string phone
     */
    public function get_phone(){
        if( !isset( $this->fields['phone'] ) || $this->fields['phone']  == '' ){
            return '';
        }
        return $this->fields['phone'];
    }

    /**
     * get custom fields falue
     * @return mixed value of the custom field or null if the field doesn't exists
     */
    public function get_custom_field_value( $field_id ){
        if( isset( $this->fields['extra'] ) && $this->fields['extra'] != '[]' ){
            $this->fields['extra'];
            $custom_fields = json_decode( $this->fields['extra'] );
            if( $custom_fields != null ){
                foreach( $custom_fields as $custom_field ){
                    if( is_array( $custom_field ) && count( $custom_field ) == 3 ){
                        if( $custom_field[0] == $field_id ){
                            return $custom_field[2];
                        }
                    }
                }
            }
        }
        return null;
    }

    /**
     * get amount_details
     * @return string amount_details
     */
    public function get_amount_details(){
        if( !isset( $this->fields['amount_details'] ) || $this->fields['amount_details']  == '' ){
            return '';
        }
        return $this->fields['amount_details'];
    }

}
