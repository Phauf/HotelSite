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
    <select id="<?php echo $slug ?>" name="<?php echo $field->get_name(); ?>" multiple class="plugion_input plugion_input_select plugion_filter_input" data-getter="select" data-validation="select" data-setter="select" data-default="" data-required="1">
<?php
    $items = $field->get_filter_value();
    $i = 0;
    foreach( $items as $value ){
        $title = $value;
        if( !is_null( $filter_extra ) && is_array( $filter_extra ) && isset( $filter_extra[ $value ] ) ){
            $title =  $filter_extra[ $value ];
        }
?>
        <option selected value="<?php echo $value ?>"><?php echo $title ?></option>
<?php
        $i++;
    }
?>
    </select>
    <label for="<?php echo $slug ?>" name="<?php echo $slug ?>" class="plugion_input_select_label"><?php echo $field->get_title() ?></label>
</div>
