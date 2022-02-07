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

?>

<div class="plugion_input_container">
    <label for="<?php echo $slug; ?>" class="plugion_input_label"><?php echo $field->get_title(); ?></label>
    <textarea  id="<?php echo $slug ?>" name="<?php echo $field->get_name(); ?>" data-default="<?php echo $field->get_default_value(); ?>" class="plugion_input plugion_input_text plugion_input_textarea plugion_input_editor plugion_property_input" type="text" required data-validation="editor" data-setter="editor" data-getter="editor" data-required="<?php echo $field->get_required(); ?>"></textarea>
</div>
