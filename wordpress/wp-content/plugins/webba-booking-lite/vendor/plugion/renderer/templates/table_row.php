<?php
if ( !defined( 'ABSPATH' ) ) exit;
/*
 * This file is part of Plugion framework.
 * (c) plugion.com <hello@plugion.org>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @var array $data
 */

$row = $data[0];
$table = $data[1];
$class = 'class="plugion_table_row_item"';

$row = json_decode(json_encode( $row ), true);

if( Plugion()->tables->get_element_at( $table )->current_user_can_update() ){
    $class = 'class="plugion_editable_row plugion_table_row_item"';
}

if( Plugion()->tables->get_element_at( $table )->current_user_can_duplicate() && Plugion()->tables->get_element_at( $table )->get_duplicatable() ) {
    $block_loader = '<div class="plugion_block_loader hide_element"></div>';
    $block_icon = '<div class="plugion_block_icon" title="duplicate"></div>';
} else {
    $block_loader = '<div class="plugion_block_loader hide_element"></div>';
    $block_icon = '';
}

$block_loader = apply_filters( 'plugion_row_controls', $block_loader, $table, $row['id'] );

echo '<tr ' . $class . ' data-id="' . $row['id'] .'"><td class="plugion_exportable">' . $row['id'] . $block_loader . $block_icon . '</td>';
$fields =  Plugion()->tables->get_element_at( $table )->get_data( 'fields_to_view');
$filtered_fields = Plugion\Table::filter_fields_by_dependency( $fields, $row );
foreach ( $fields as $field_slug => $field ) {
    if ( !$field->get_in_row() ) {
        continue;
    }
    $value =  $row[ $field->get_name() ];
    if( !in_array( $field, $filtered_fields ) ){
        $value = '';
    }
    $ordering = apply_filters( 'plugion_table_column_' . $field->get_type() . '_ordering', null, [ $field, $field_slug, $value, $row ] );
    if( is_null( $ordering ) ){
        $ordering = '';
    } else {
        $ordering = 'data-order="' . $ordering  . '"';
    }

    echo '<td ' . $ordering . ' class="plugion_cell plugion_exportable">';
    if ( !has_action( 'plugion_table_cell_' . $field->get_type() ) ) {
        echo '<p>No action found for the <strong>' . 'plugion_property_field_' . $field->get_type()  . '</strong></p>';
    }
    do_action( 'plugion_table_cell_' . $field->get_type(), [ $field, $field_slug, $value, $row ] );
    echo '</td>';
}
echo '</tr>';
?>
