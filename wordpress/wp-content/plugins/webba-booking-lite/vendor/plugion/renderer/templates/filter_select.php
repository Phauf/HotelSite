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
$filter_extra = $field->get_filter_extra();

?>

<div class="plugion_input_container">
    <select id="<?php echo $slug ?>" name="<?php echo $field->get_name(); ?>" class="plugion_input plugion_input_select plugion_filter_input" data-validation="select" data-default="" data-required="1">
<?php
    $items = $field->get_filter_extra();
    $i = 0;
    foreach( $items as $key => $value ){
 ?>
        <option value="<?php echo $key ?>"><?php echo $value ?></option>
<?php
        $i++;
    }
?>
    </select>
    <label for="<?php echo $slug ?>" name="<?php echo $slug ?>" class="plugion_input_select_label"><?php echo $field->get_title() ?></label>
</div>
