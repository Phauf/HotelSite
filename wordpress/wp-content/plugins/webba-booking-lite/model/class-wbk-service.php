<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class WBK_Service extends WBK_Model_Object{
    public function __construct( $id ) {
        $this->table_name =  get_option('wbk_db_prefix', '' ) . 'wbk_services';
		parent::__construct( $id );

	}
    /**
     * get attached google calendars
     * @return array IDs of the google calendars
     */
    public function get_gg_calendars(){
        if( !is_null( $this->fields['gg_calendars'] ) ){
            $gg_calendars = json_decode( $this->fields['gg_calendars'] );
            if( is_numeric( $gg_calendars ) ){
                $gg_calendars = array( $gg_calendars );
            }
            if( is_array( $gg_calendars) ){
                return $gg_calendars;
            }
        }
        return array();
    }

    /**
     * get interval between $booking_ids
     * @return int interval between bookings
     */
    public function get_interval_between(){
        if( !isset( $this->fields['interval_between'] ) ){
            return null;
        }
        return $this->fields['interval_between'];
    }

    /**
     * get duration
     * @return int get duration
     */
    public function get_duration(){
        if( !isset( $this->fields['duration'] ) ){
            return null;
        }
        return $this->fields['duration'];
    }

    /**
     * get step
     * @return int step
     */
    public function get_step(){
        if( !isset( $this->fields['step'] ) ){
            return null;
        }
        return $this->fields['step'];
    }

    /**
     * get preparation time
     * @return int preparation time
     */
    public function get_prepare_time(){
        if( !isset( $this->fields['prepare_time'] ) ){
            return null;
        }
        return $this->fields['prepare_time'];
    }

    /**
     * get business hours
     * @return array business hours
     */
    public function get_business_hours(){
        if( !isset( $this->fields['business_hours_v4'] ) ){
            return null;
        }
        return $this->fields['business_hours_v4'];

    }

    /**
     * get maximum quantity
     * @return ind quantity
     */
    public function get_quantity( $time = null ){
        if( !isset( $this->fields['quantity'] ) ){
            return null;
        }
        return apply_filters( 'wbk_service_quantity', $this->fields['quantity'], $this->get_id(), $time );
    }

    /**
     * get date_range
     * @return array date_range
     */
    public function get_availability_range(){
        if( !isset( $this->fields['date_range'] ) ){
            return null;
        }
        $date_range = explode( '-',  $this->fields['date_range']  );
        $result = array();
        foreach( $date_range as $item ){
            $result[] = trim( $item );
        }
        return $result;


    }

    /*
     * get price of the service
     * @return float price
     */
    public function get_price(){
        if( !isset( $this->fields['price'] ) ){
            return null;
        }
        return $this->fields['price'];

    }

    /**
     * get pricing rules
     * @return array IDs of the pricing rules
     */
    public function get_pricing_rules(){
        if( !is_null( $this->fields['pricing_rules'] ) ){
            $items = json_decode( $this->fields['pricing_rules'] );
            if( is_numeric( $items ) ){
                $items = array( $items );
            }
            if( is_array( $items) ){
                return $items;
            }
        }
        return array();
    }

    /**
     * get on changes template
     * @return int id of the on changed template
     */
    public function get_on_changes_template(){
        if( !is_null( $this->fields['booking_changed_template'] ) ){
            return $this->fields['booking_changed_template'];

        }
        return false;
    }
    /**
     * get the service fee
     * @return flot service fee
     */
    public function get_fee(){
        if( !isset( $this->fields['service_fee'] ) ){
            return 0;
        }
        return $this->fields['service_fee'];
    }

    public function get_description( $unescaped = false ){
        $value = '';
        if( !isset( $this->fields['description'] ) ){
            $value = $this->fields['description'];
            if( $unescaped ){
                $value = stripcslashes( $value );
            }
        }
        return $value;
    }

    public function get_multi_mode_low_limit(){
        if( !is_null( $this->fields['multi_mode_low_limit'] ) ){
            return $this->fields['multi_mode_low_limit'];

        }
        return '';
    }

    public function get_multi_mode_limit(){
        if( !is_null( $this->fields['multi_mode_limit'] ) ){
            return $this->fields['multi_mode_limit'];
        }
        return '';
    }

}
