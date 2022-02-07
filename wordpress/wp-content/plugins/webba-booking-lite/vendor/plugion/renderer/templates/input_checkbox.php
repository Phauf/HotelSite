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
$data = $field->get_extra_data();
$keys = array_keys( $data );
?>
<span class="plugion_input_label"><?php echo $field->get_title(); ?></span>
<div class="plution_checkbox">
    <input id="<?php echo $slug ?>" name="<?php echo $field->get_name(); ?>"  class="plugion_input plugion_input_checkbox plugion_property_input" type="checkbox"  value="<?php echo $keys[0];?>" data-validation="checkbox" data-getter="checkbox"  data-setter="checkbox">
    <label for="<?php echo $slug ?>"><?php echo  $data[ $keys[0] ];?></label>
</div>
