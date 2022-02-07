<?php
if ( !defined( 'ABSPATH' ) ) exit;

$field = $data[0];
$slug = $data[1];

if( isset( $field->get_extra_data()['date_format'] ) ){
    $date_fomrat =  $field->get_extra_data()['date_format'];
} else {
    $date_fomrat = get_option('date_format');
}

$date_fomrat = str_replace( 'd', 'dd', $date_fomrat );
$date_fomrat = str_replace( 'j', 'd', $date_fomrat );
$date_fomrat = str_replace( 'l', 'dddd', $date_fomrat );
$date_fomrat = str_replace( 'D', 'ddd', $date_fomrat );
$date_fomrat = str_replace( 'm', 'mm', $date_fomrat );
$date_fomrat = str_replace( 'n', 'm', $date_fomrat );
$date_fomrat = str_replace( 'F', 'mmmm', $date_fomrat );
$date_fomrat = str_replace( 'M', 'mmm', $date_fomrat );
$date_fomrat = str_replace( 'y', 'yy', $date_fomrat );
$date_fomrat = str_replace( 'Y', 'yyyy', $date_fomrat );
$date_fomrat = str_replace( 'S', '', $date_fomrat );
$date_fomrat = str_replace( 's', '', $date_fomrat );

$start = date('m/d/Y');
$end = date('m/d/Y',  strtotime(' +' . get_option( 'wbk_filter_default_days_number', '14' ) .  ' day'));
$data_default = json_encode( $field->get_filter_value() );

?>

<input  type="hidden"  id="<?php echo $slug ?>" name="<?php echo $field->get_name(); ?>" class="plugion_input plugion_input_date_range plugion_filter_input" type="text" data-dateformat="<?php echo $date_fomrat ?>" data-default="<?php echo $data_default;?>"
 data-start="" data-end="" data-setter="wbk_date_range" data-getter="wbk_date_range" data-validation="date_range" data-required="<?php echo $field->get_required();?>" data-rangedefault="<?php echo get_option( 'wbk_filter_default_days_number', '14' );  ?>">
<label for="<?php echo $slug; ?>" class="plugion_input_label"><?php echo $field->get_title(); ?></label>
<div class="plugion_input_container_small">
    <input id="<?php echo $slug . '_date_range_start'  ?>" class="plugion_input_date_range_start plugion_input_text" required>
    <label for="<?php echo $slug . '_date_range_start' ?>" class="plugion_input_text_label"><?php echo plugion_translate_string( 'Start' ) ?></label>
</div>
<div class="plugion_input_container_small">
    <input id="<?php echo $slug . '_date_range_end'  ?>" class="plugion_input_date_range_end plugion_input_text" required>
    <label for="<?php echo $slug . '_date_range_end' ?>" class="plugion_input_text_label"><?php echo plugion_translate_string( 'End' ) ?></label>
</div>
<div style="clear: both"></div>
