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
$ed = $field->get_extra_data();
if( isset( $ed['null_value'] ) ){
    $keys = array_keys( $ed['null_value'] );
    $null_value = $keys[0];
    $null_string = $ed['null_value'][0];
} else {
    $null_string =  plugion_translate_string( 'select option' );
    $null_value = 'plugion_null';
}
?>
<div class="plugion_input_container">
    <select id="<?php echo $slug ?>" name="<?php echo $field->get_name(); ?>"  class="plugion_input plugion_input_select plugion_property_input" data-validation="select" data-setter="select" data-default="<?php echo $field->get_default_value(); ?>" data-required="<?php echo $field->get_required(); ?>">
        <option value="<?php echo $null_value; ?>"><?php echo $null_string; ?></option>     
    </select>
    <label for="<?php echo $slug ?>" name="<?php echo $slug ?>" class="plugion_input_select_label"><?php echo $field->get_title() ?></label>
</div>
