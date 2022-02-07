<?php
if ( !defined( 'ABSPATH' ) ) exit;
$service_ids = $data;
$label = get_option( 'wbk_service_label',  __( 'Select service', 'wbk' ) );
if( $label == '' ){
    global $wbk_wording;
    $label =  sanitize_text_field( $wbk_wording['service_label'] );
}
?>
<label class="wbk-input-label"><?php echo $label; ?></label>
<select class="wbk-select wbk-input wbk_services" id="wbk-service-id">
<option value="0" selected="selected"><?php echo __( 'select...', 'wbk' ) ?></option>
<?php
foreach ( $service_ids as $id ) {
    $service = new WBK_Service( $id );
    if ( $service->get_name() == '' ) {
        continue;
    }
    if( function_exists('pll__' ) ){
        $service_name =  pll__( $service->get_name( true ) );
    } else{
        $service_name = $service->get_name( true );
    }
    $service_name = apply_filters( 'wpml_translate_single_string', $service_name, 'wbk', 'Service name id ' . $service->get_id() );
    $service_description = apply_filters( 'wpml_translate_single_string', $service->get_description( false ), 'wbk', 'Service description id ' . $service->get_id() );
    if(  get_option( 'wbk_show_service_description', 'disabled' ) == 'disabled' ){
        echo '<option value="' . $service->get_id() . '"  data-multi-low-limit="' . $service->get_multi_mode_low_limit() . '" data-multi-limit="' . $service->get_multi_mode_limit() . '" >' . $service_name . '</option>';
    } else {
        echo '<option data-desc="' . htmlspecialchars( $service_description )  . '" value="' . $service->get_id() . '"  data-multi-low-limit="' . $service->get_multi_mode_low_limit() . '"  data-multi-limit="' . $service->get_multi_mode_limit() . '" >' . $service_name . '</option>';
    }
}
?>
</select>
