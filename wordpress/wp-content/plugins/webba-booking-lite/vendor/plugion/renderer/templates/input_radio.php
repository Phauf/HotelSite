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

<label for="<?php echo $slug; ?>" class="plugion_input_label"><?php echo $field->get_title(); ?></label>
<?php
    $i = 0;
    foreach ( $field->get_extra_data() as $key => $value ) {
        $i++;
        if ( $field->get_default_value() === $key ) {
            $checked = ' checked ';
        } else {
            $checked = '';
        } ?>
        <input class="plugion_input plugion_input_radio plugion_property_input" value="<?php echo $key; ?>" data-setter="radio" data-getter="radio" data-validation="radio"  type="radio" name="<?php echo $field->get_name(); ?>"  id="<?php echo $slug . $i; ?>" <?php  echo $checked; ?>  />
        <label class="plugion_input_radio_label" for="<?php echo $slug . $i; ?>"><?php echo $value ?></label>
<?php
    }
?>
