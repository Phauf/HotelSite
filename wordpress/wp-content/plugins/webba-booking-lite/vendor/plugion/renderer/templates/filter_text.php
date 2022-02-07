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
    <input  id="<?php echo $slug ?>" name="<?php echo $slug; ?>" class="plugion_input plugion_input_text plugion_filter_input" type="text" data-validation="text" required>
    <label for="<?php echo $slug ?>" name="<?php echo $slug ?>" class="plugion_input_text_label"><?php echo $field->get_title() ?></label>
</div>
