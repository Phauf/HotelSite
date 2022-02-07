<?php
if ( !defined( 'ABSPATH' ) ) exit;
    // add get parameters
    $html_get  = '<script type=\'text/javascript\'>';
    $html_get .= 'var wbk_get_converted = {';
    foreach ( $_GET as $key => $value ) {
        $value = urldecode($value);
        $key = urldecode($key);
        $value = str_replace('"', '', $value);
        $key = str_replace('"', '', $key);
        $value = str_replace('\'', '', $value);
        $key = str_replace('\'', '', $key);
        $value = str_replace('/', '', $value);
        $key = str_replace('/', '', $key);
        $value = str_replace('\\', '', $value);
        $key = str_replace('\\', '', $key);
        $value = sanitize_text_field($value);
        $key = sanitize_text_field($key);
        if ( $key != 'action' && $key != 'time' && $key != 'service' && $key != 'step' ){
        }
        $html_get .= '"'.$key.'"'. ':"' . $value . '",';
    }
    $html_get .= '"blank":"blank"';
    $html_get .= '};</script>';
    echo $html_get;
?>
<div class="wbk-frontend-row wbk_date_container" id="wbk-date-container">
</div>
<?php
    if( get_option( 'wbk_mode', 'extended' ) == 'extended' ){
?>
        <div class="wbk-frontend-row wbk_time_container" id="wbk-time-container">
        </div>
<?php
    }
?>
<div class="wbk-frontend-row wbk_slots_container" id="wbk-slots-container">
</div>
<div class="wbk-frontend-row wbk_booking_form_container" id="wbk-booking-form-container">
</div>
<div class="wbk-frontend-row wbk_booking_done" id="wbk-booking-done">
</div>
<div class="wbk-frontend-row wbk_payment" id="wbk-payment">
</div>
