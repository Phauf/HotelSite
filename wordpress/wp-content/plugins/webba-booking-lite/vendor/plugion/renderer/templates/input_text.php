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

if( isset( $ed['type'] ) ){
    $data_type = $ed['type'];
} else {
    $data_type = '';
}
if( $data_type == 'password' ){
    $type = 'type="password"';
} else {
    $type = 'type="text"';
}
?>
<div class="plugion_input_container">
    <input  id="<?php echo $slug ?>" name="<?php echo $field->get_name(); ?>" <?php echo $type; ?> data-default="<?php echo $field->get_default_value(); ?>" required class="plugion_input plugion_input_text plugion_simple_text_input plugion_property_input"  data-validation="text" data-type="<?php echo $data_type ?>" plugion_input plugion_input_text plugion_simple_text_input plugion_property_input data-required="<?php echo $field->get_required(); ?>">
    <label for="<?php echo $slug ?>" class="plugion_input_text_label"><?php echo $field->get_title() ?></label>
</div>
