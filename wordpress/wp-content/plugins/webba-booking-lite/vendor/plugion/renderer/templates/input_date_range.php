<?php
if ( !defined( 'ABSPATH' ) ) exit;
/*
 * This file is part of Plugion framework.
 * (c) plugion.com <hello@plugion.org>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$field = $data[0];
$slug = $data[1];

if( isset( $field->get_extra_data()['time_zone'] ) ){
    $time = new \DateTime('now', new DateTimeZone( $field->get_extra_data()['time_zone'] ));
    $time_zone =  'UTC' . $time->format('P');
} else {
    if( get_option('timezone_string') != '' ){
        $time = new \DateTime('now', new DateTimeZone( get_option('timezone_string') ) );
        $time_zone =  'UTC' . $time->format('P');
    } else {
        $time = new \DateTime('now');
        $offset = get_option( 'gmt_offset' );
		if( $offset > 0 ){
			$sign = '+';
		} else {
			$sign = '-';
            $offset = $offset * -1;
        }
		$offset_int = floor( $offset );
        $offset_fractional = $offset - $offset_int;
        switch ( $offset_fractional) {
            case 0.5:
                $offset_fractional = ':30';
                break;
            case 0.75:
                $offset_fractional = ':45';
                break;
            default:
                $offset_fractional = '';
                break;
        }
	    $time_zone =  'UTC' . $sign . $offset_int . $offset_fractional;
    }
}

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


?>

<input type="hidden"  id="<?php echo $slug ?>" name="<?php echo $field->get_name(); ?>" class="plugion_input plugion_input_date_range plugion_property_input" type="text" data-timezone="<?php echo $time_zone ?>" data-dateformat="<?php echo $date_fomrat ?>" data-default="<?php echo $field->get_default_value();?>"
 data-start="" data-end="" data-setter="date_range" data-getter="date_range" data-validation="date_range" data-required="<?php echo $field->get_required(); ?>">
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
