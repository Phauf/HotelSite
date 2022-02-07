<?php
if ( !defined( 'ABSPATH' ) ) exit;
/*
 * This file is part of Plugion framework.
 * (c) plugion.com <hello@plugion.org>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

// text field
add_action( 'plugion_property_field_text', 'plugion_property_field_text_render' );
function plugion_property_field_text_render( $data ){
    Plugion()->renderer->render_field_property( $data );
}
add_action( 'plugion_table_cell_text', 'plugion_table_cell_text_render' );
function plugion_table_cell_text_render( $data ){
    Plugion()->renderer->render_field_tablle( $data );
}

// radio field
add_action( 'plugion_property_field_radio', 'plugion_property_field_radio_render' );
function plugion_property_field_radio_render( $data ){
    Plugion()->renderer->render_field_property( $data );
}
add_action( 'plugion_table_cell_radio', 'plugion_table_cell_radio_render' );
function plugion_table_cell_radio_render( $data ){
    Plugion()->renderer->render_field_tablle( $data );
}

// checkbox
add_action( 'plugion_property_field_checkbox', 'plugion_property_field_checkbox_render' );
function plugion_property_field_checkbox_render( $data ){
    Plugion()->renderer->render_field_property( $data );
}
add_action( 'plugion_table_cell_checkbox', 'plugion_table_cell_checkbox_render' );
function plugion_table_cell_checkbox_render( $data ){
    Plugion()->renderer->render_field_tablle( $data );
}

// select
add_action( 'plugion_property_field_select', 'plugion_property_field_select_render' );
function plugion_property_field_select_render( $data ){
    Plugion()->renderer->render_field_property( $data );
}
add_action( 'plugion_table_cell_select', 'plugion_table_cell_select_render' );
function plugion_table_cell_select_render( $data ){
    Plugion()->renderer->render_field_tablle( $data );
}

// textarea
add_action( 'plugion_property_field_textarea', 'plugion_property_field_textarea_render' );
function plugion_property_field_textarea_render( $data ){
    Plugion()->renderer->render_field_property( $data );
}
add_action( 'plugion_table_cell_textarea', 'plugion_table_cell_textarea_render' );
function plugion_table_cell_textarea_render( $data ){
    Plugion()->renderer->render_field_tablle( $data );
}

// date time
add_action( 'plugion_property_field_datetime', 'plugion_property_field_datetime_render' );
function plugion_property_field_datetime_render( $data ){
    Plugion()->renderer->render_field_property( $data );
}
add_action( 'plugion_table_cell_datetime', 'plugion_table_cell_datetime_render' );
function plugion_table_cell_datetime_render( $data ){
    Plugion()->renderer->render_field_tablle( $data );
}

// date
add_action( 'plugion_property_field_date', 'plugion_property_field_date_render' );
function plugion_property_field_date_render( $data ){
    Plugion()->renderer->render_field_property( $data );
}
add_action( 'plugion_table_cell_date', 'plugion_table_cell_date_render' );
function plugion_table_cell_date_render( $data ){
    Plugion()->renderer->render_field_tablle( $data );
}
// editor
add_action( 'plugion_property_field_editor', 'plugion_property_field_editor_render' );
function plugion_property_field_editor_render( $data ){
    Plugion()->renderer->render_field_property( $data );
}
add_action( 'plugion_table_cell_editor', 'plugion_table_cell_editor_render' );
function plugion_table_cell_editor_render( $data ){
    Plugion()->renderer->render_field_tablle( $data );
}
// date_range
add_action( 'plugion_property_field_date_range', 'plugion_property_field_date_range_render' );
function plugion_property_field_date_range_render( $data ){
    Plugion()->renderer->render_field_property( $data );
}
add_action( 'plugion_table_cell_date_range', 'plugion_table_cell_date_range_render' );
function plugion_table_cell_date_range_render( $data ){
    Plugion()->renderer->render_field_tablle( $data );
}
?>
