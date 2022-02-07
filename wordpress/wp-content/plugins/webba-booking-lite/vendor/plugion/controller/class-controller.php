<?php
namespace Plugion;
if ( !defined( 'ABSPATH' ) ) exit;
/*
 * This file is part of Plugion framework.
 * (c) plugion.com <hello@plugion.org>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */


if (!defined('ABSPATH') ) {
    exit;
}
class Controller {
    /**
     * constructor
     */
    public function __construct() {
        // load properties
        add_action( 'rest_api_init', function () {
            register_rest_route( 'plugion/v1', '/load-properties/', [
                'methods' => 'POST',
                'callback' =>  [ $this, 'load_properties' ],
                'permission_callback' => [ $this, 'load_properties_permission' ]
            ] );
        } );
        // add or update row
        add_action( 'rest_api_init', function () {
            register_rest_route( 'plugion/v1', '/save-properties/', [
                'methods' => 'POST',
                'callback' =>  [ $this, 'save_properties' ],
                'permission_callback' => [ $this, 'save_properties_permission' ]
            ] );
        } );
        // duplicate row
        add_action( 'rest_api_init', function () {
            register_rest_route( 'plugion/v1', '/duplicate-row/', [
                'methods' => 'POST',
                'callback' =>  [ $this, 'duplicate_row' ],
                'permission_callback' => [ $this, 'duplicate_properties_permission' ]
            ] );
        } );

        // get rows applying filters (if set)
        add_action( 'rest_api_init', function () {
            register_rest_route( 'plugion/v1', '/get-rows/', [
                'methods' => 'POST',
                'callback' =>  [ $this, 'get_rows' ],
                'permission_callback' => [ $this, 'get_rows_permission' ]
            ] );
        } );

        // delete rows
        add_action( 'rest_api_init', function () {
            register_rest_route( 'plugion/v1', '/delete-rows/', [
                'methods' => 'POST',
                'callback' =>  [ $this, 'delete_rows' ],
                'permission_callback' => [ $this, 'delete_rows_permission' ]
            ] );
        } );
    }
    /**
     * save properties
     * @param  WP_REST_Request $request rest request object
     * @return WP_REST_Response rest response object
     */
    public function save_properties( $request ) {
        $table = trim( sanitize_text_field( $request['table'] ) );
        $fields = $request['fields'];
        $data = null;
        if ( false === Plugion()->tables->get_element_at( $table ) ) {
            $response = new \WP_REST_Response( $data );
            $response->set_status( 400 );

            return $response;
        }
        if ( !is_numeric( $request[ 'row_id' ] ) ) {
            $action_result = Plugion()->tables->get_element_at( $table )->add_row( $fields );
            $row = $action_result[0];
        } else {
            $action_result = Plugion()->tables->get_element_at( $table )->update_row( $fields, $request[ 'row_id' ] );
            $row = $action_result[0];
        }
        $filtered_fields =  $action_result[1];
        if ( is_null( $row ) ) {
            $response = new \WP_REST_Response( null );
            $response->set_status( 400 );

            return $response;
        }
        if (  false === $row ) {
            $response = new \WP_REST_Response( $action_result[1] );
            $response->set_status( 422 );

            return $response;
        }
        if ( !is_null( $row ) ) {

            if( Plugion()->tables->get_element_at( $table )->current_user_can_duplicate() && Plugion()->tables->get_element_at( $table )->get_duplicatable() ) {
                $block_loader = '<div class="plugion_block_loader hide_element"></div>';
                $block_icon = '<div class="plugion_block_icon" title="Copy row"></div>';
                $formated_row_values = [ $row['id'] . $block_loader . $block_icon ];
            } else {
                $block_loader = apply_filters( 'plugion_row_controls', '', $table, $row['id'] );
                $formated_row_values = [ $row['id'] . $block_loader ];

            }
            foreach ( Plugion()->tables->get_element_at( $table )->get_data( 'fields_to_view' ) as $field_slug => $field ) {
                if ( !$field->get_in_row() ) {
                    continue;
                }
                if ( !in_array( $field, $filtered_fields, true ) ) {
                    $formated_row_values[] = '';
                    continue;
                }
                ob_start();
                $value =  $row[ $field->get_name() ];
                if ( !has_action( 'plugion_table_cell_' . $field->get_type() ) ) {
                    echo '<p>No action found for the <strong>' . 'plugion_property_field_' . $field->get_type()  . '</strong></p>';
                }
                do_action( 'plugion_table_cell_' . $field->get_type(), [ $field, $field_slug, $value, $row ] );
                $field_value = ob_get_clean();
                $formated_row_values[] = apply_filters( 'plugion_formated_row_value', $field_value, [ $field, $field_slug, $value, $row ]  );

            }
            $row_options['canedit'] = Plugion()->tables->get_element_at( $table )->current_user_can_update();

            $row_to_filter = (object) $row;
            $formated_row_values = apply_filters( 'plugion_formated_row_values', $formated_row_values, $row_to_filter );

            $response = new \WP_REST_Response( [ 'row_data' => $formated_row_values, 'row_options' => $row_options, 'db_row_data' => $row  ] );
            $response->set_status( 200 );

            return $response;
        }
        $response = new \WP_REST_Response( null );
        $response->set_status( 400 );

        return $response;
    }
    /**
     * load properties
     * @param  WP_REST_Request $request rest request object
     * @return WP_REST_Response rest response object
     */
    public function load_properties( $request ) {
        global $wpdb;
        $data = null;
        $table = sanitize_text_field( $request[ 'table' ] );
        if ( !isset( $request[ 'row_id'] ) ) {
            $response = new \WP_REST_Response( $data );
            $response->set_status( 400 );

            return $response;
        }
        if ( is_numeric( $request[ 'row_id' ] ) ) {
            if ( false === Plugion()->tables->get_element_at( $table ) ) {
                $response = new \WP_REST_Response( $data );
                $response->set_status( 400 );

                return $response;
            }
            $row = Plugion()->tables->get_element_at( $table )->get_row( $request[ 'row_id'] );
            if ( is_null( $row ) ) {
                $response = new \WP_REST_Response( $data );
                $response->set_status( 400 );

                return $response;
            }

            $data = $row;
        } else {
            $response = new \WP_REST_Response( $data );
            $response->set_status( 400 );

            return $response;
        }


        $response = new \WP_REST_Response( $data );
        $response->set_status( 200 );

        return $response;
    }

    /**
     * get_rows
     * @param  WP_REST_Request $request rest request object
     * @return WP_REST_Response rest response object
     */
    public function get_rows( $request ) {
        $table = trim( sanitize_text_field( $request['table'] ) );
        $filters = $request['filters'];
        $data = null;
        if ( false === Plugion()->tables->get_element_at( $table ) ) {
            $response = new \WP_REST_Response( $data );
            $response->set_status( 400 );

            return $response;
        }

        $table_content = '<tbody>';
        ob_start();
        $result = Plugion()->tables->get_element_at( $table )->get_rows( $filters );
        if ( false === $result ) {
            $response = new \WP_REST_Response( $data );
            $response->set_status( 404 );

            return $response;
        }
        foreach ( Plugion()->tables->get_element_at( $table )->get_data( 'rows' ) as $row ) {
            Plugion()->renderer->render_table_row( $row, $table );
        }
        $table_content .= ob_get_clean() . '</tbody>';
        $table_content = apply_filters( 'plugion_table_content', $table_content, $table );
        $data = $table_content;
        $response = new \WP_REST_Response( $data );
        $response->set_status( 200 );

        return $response;
    }
    /**
     * delete_rows
     * @param  WP_REST_Request $request rest request object
     * @return WP_REST_Response rest response object
     */
    public function delete_rows( $request ) {
        $table = trim( sanitize_text_field( $request['table'] ) );
        $data = null;
        if ( false === Plugion()->tables->get_element_at( $table ) ) {
            $response = new \WP_REST_Response( $data );
            $response->set_status( 400 );

            return $response;
        }
        $removed = [];
        if ( is_array( $request[ 'row_ids' ] ) ) {
            foreach( $request[ 'row_ids' ] as $row_id ){
                if( is_numeric( $row_id ) ){
                    if( Plugion()->tables->get_element_at( $table )->delete_row( $row_id ) > 0 ){
                        $removed[] = $row_id;
                    }
                }
            }
        }

        $response = new \WP_REST_Response( $removed );
        $response->set_status( 200 );

        return $response;
    }
    /**
     * check if current user can view the rows
     * @param  WP_REST_Request $request rest request object
     * @return bool allow or not rest request
     */
    public function load_properties_permission( $request ) {
        $table = sanitize_text_field( $request[ 'table' ] );
        if ( false === Plugion()->tables->get_element_at( $table ) ) {
            return false;
        }
        return Plugion()->tables->get_element_at( $table )->сurrent_user_can_view();

    }
    /**
     * check if current user add or edit the rows
     * @param  WP_REST_Request $request rest request object
     * @return bool allow or not rest request
     */
    public function save_properties_permission( $request ) {
        $table = sanitize_text_field( $request[ 'table' ] );
        if ( false === Plugion()->tables->get_element_at( $table ) ) {
            return false;
        }
        if ( is_numeric( $request[ 'row_id' ] ) ) {
            return Plugion()->tables->get_element_at( $table )->current_user_can_update();
        }

        return Plugion()->tables->get_element_at( $table )->current_user_can_add();
    }
    /**
     * check if current user can get rows
     * @param  WP_REST_Request $request rest request object
     * @return bool allow or not rest request
     */
    public function get_rows_permission( $request ) {
        $table = sanitize_text_field( $request[ 'table' ] );
        if ( false === Plugion()->tables->get_element_at( $table ) ) {
            return false;
        }

        return Plugion()->tables->get_element_at( $table )->сurrent_user_can_view();
    }
    /**
     * check if current user can delete rows
     * @param  WP_REST_Request $request rest request object
     * @return bool allow or not rest request
     */
    public function delete_rows_permission( $request ) {
        $table = sanitize_text_field( $request[ 'table' ] );
        if ( false === Plugion()->tables->get_element_at( $table ) ) {

            return false;
        }

        return Plugion()->tables->get_element_at( $table )->current_user_can_delete();
    }

    /**
     * check if current user duplicate the rows
     * @param WP_REST_Request $request rest request object
     * @return bool allow or not rest request
     */
    public function duplicate_properties_permission( $request ) {
        $table = sanitize_text_field( $request[ 'table' ] );
        if ( false === Plugion()->tables->get_element_at( $table ) ) {
            return false;
        }
        if ( is_numeric( $request[ 'row_id' ] ) ) {
            return Plugion()->tables->get_element_at( $table )->current_user_can_duplicate();
        } else {
            return false;
        }
    }

    /**
     * duplicate_row
     * @param  WP_REST_Request $request rest request object
     * @return WP_REST_Response rest response object
     */
    public function duplicate_row( $request ) {

        $table = trim( sanitize_text_field( $request['table'] ) );
        $data = null;

        if ( false === Plugion()->tables->get_element_at( $table ) ) {
            $response = new \WP_REST_Response( $data );
            $response->set_status( 400 );

            return $response;
        }
        if ( !is_numeric( $request[ 'row_id' ] ) ) {
            $response = new \WP_REST_Response( null );
            $response->set_status( 400 );

            return $response;
        } else {
            $row_id = (int) $request[ 'row_id' ];
            $row = Plugion()->tables->get_element_at( $table )->get_row( $row_id, ARRAY_A );
            unset($row['id']);
            $row['name'] = 'Duplicate of ' . $row['name'];
            $fields = Plugion()->tables->get_element_at( $table )->fields->get_elements();
            $field_types = array();
            foreach( $fields as $key => $field ){
                $ed = $field->get_extra_data();
                if( isset( $ed['multiple'] ) && $field->get_type() == 'select' ){
                    $field_types[ $field->get_name() ] = 'select_multiple';
                } else {
                    $field_types[ $field->get_name() ] = $field->get_type();
                }
            }
            $fields_to_add = array();
            foreach( $row as $key => $value ){
                if( $field_types[ $key ] == 'select_multiple' ){
                    $fields_to_add[] = array( 'name' => $key, 'value' => json_decode( $value  ) );
                } else {
                    $fields_to_add[] = array( 'name' => $key, 'value' => $value );
                }
            }
            $action_result = Plugion()->tables->get_element_at( $table )->add_row( $fields_to_add );
            $row = $action_result[0];
        }



        $filtered_fields =  $action_result[1];
        if ( is_null( $row ) ) {
            $response = new \WP_REST_Response( null );
            $response->set_status( 400 );

            return $response;
        }
        if (  false === $row ) {
            $response = new \WP_REST_Response( $action_result[1] );
            $response->set_status( 422 );

            return $response;
        }
        if ( !is_null( $row ) ) {

            if( Plugion()->tables->get_element_at( $table )->current_user_can_duplicate() && Plugion()->tables->get_element_at( $table )->get_duplicatable() )
            {
                $block_loader = '<div class="plugion_block_loader hide_element"></div>';
                $block_icon = '<div class="plugion_block_icon" title="Copy row"></div>';
                $formated_row_values = [ $row['id'] . $block_loader . $block_icon ];
            } else {
                $formated_row_values = [ $row['id'] ];
            }

            foreach ( Plugion()->tables->get_element_at( $table )->get_data( 'fields_to_view' ) as $field_slug => $field ) {

                if ( !$field->get_in_row() ) {
                    continue;
                }
                if ( !in_array( $field, $filtered_fields, true ) ) {
                    $formated_row_values[] = '';
                    continue;
                }
                ob_start();
                $value =  $row[ $field->get_name() ];
                if ( !has_action( 'plugion_table_cell_' . $field->get_type() ) ) {
                    echo '<p>No action found for the <strong>' . 'plugion_property_field_' . $field->get_type()  . '</strong></p>';
                }
                do_action( 'plugion_table_cell_' . $field->get_type(), [ $field, $field_slug, $value, $row ] );
                $field_value = ob_get_clean();
                $formated_row_values[] = apply_filters( 'plugion_formated_row_value', $field_value, [ $field, $field_slug, $value, $row ]  );

            }
            //$row_options['canedit'] = Plugion()->tables->get_element_at( $table )->current_user_can_update();
            $row_options['canedit'] = Plugion()->tables->get_element_at( $table )->current_user_can_add();

            $row_to_filter = (object) $row;
            $formated_row_values = apply_filters( 'plugion_formated_row_values', $formated_row_values, $row_to_filter );

            $response = new \WP_REST_Response( [ 'row_data' => $formated_row_values, 'row_options' => $row_options, 'db_row_data' => $row  ] );
            $response->set_status( 200 );

            return $response;
        }
        $response = new \WP_REST_Response( null );
        $response->set_status( 400 );

        return $response;
    }


}

?>
