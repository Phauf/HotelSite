<?php
namespace Plugion;
if ( !defined( 'ABSPATH' ) ) exit;
/*
 * This file is part of Plugion framework.
 * (c) plugion.com <hello@plugion.org>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * View class
 */
class Renderer {
    public function __construct() {

    }
    public function render_table( $slug, $pagination = true, $search = true ) {
        echo $this->load_template( 'table', [ $slug, $pagination, $search ] );
        echo $this->load_template( 'properties_add_form', $slug );
        echo $this->load_template( 'properties_update_form', $slug );
        echo $this->load_template( 'filter_form', $slug );
    }
    public function render_field_property( $data ) {
        $field = $data[0];
        echo $this->load_template( 'input_' . $field->get_type(), $data );
    }
    public function render_filter( $data ) {
        $field = $data[0];
        echo $this->load_template( 'filter_' . $field->get_filter_type(), $data );
    }
    public function render_field_tablle( $data ) {
        $field = $data[0];
        $result = $this->load_template( 'cell_' . $field->get_type(), $data );
        echo apply_filters( 'plugion_cell_content', $result, $data );        
    }
    public function render_table_row( $row, $table ) {
        echo $this->load_template( 'table_row', [ $row, $table ] );
    }
    public function load_template( $template, $data ) {
        $file_name = __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $template . '.php';
        $file_name = apply_filters( 'plugion_template_file', $file_name, $template, $data );
        ob_start();
        include $file_name;
        $template_content = ob_get_clean();
        return apply_filters( 'plugion_template_content', $template_content, $template, $data );

    }

}

?>
