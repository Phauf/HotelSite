<?php
namespace Plugion;
if ( !defined( 'ABSPATH' ) ) exit;
/*
 * This file is part of Plugion framework.
 * (c) plugion.com <hello@plugion.org>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */


if (!defined('ABSPATH')) {
    exit;
}
/**
 * Database table class
 */
class Table {
    /**
     * sections of fields
     *  @var array
     */
    public $sections;

    /**
     * collection of fields
     * @var Collection
     */
    public $fields;

    /**
     * the name of table in the database
     * @var string
     */
    protected $table_name;

    /**
     * cachced data
     * @var array
     */
    protected $data;

    /**
     * Name of the single item in the table
     * i.e. Staff member
     * @var string
     */
    protected $single_item_name;

    /**
     * Name of the multiple items in the table
     * i.e. Staff members
     * @var string
     */
    protected $multiple_item_name;

    /**
     * Defind the roles of wp users that can delete get_rows
     * @var array
     */
    protected $can_delete;

    /**
     * Determines the ability of records in the table to be duplicated
     * @var bool
     */
    protected $duplicatable;

    /**
     * Determines if confirmation is requried when duplicating
     * @var bool
     */
    protected $confirm_duplicate;


    /**
     * Table constructor
     * @param string $table_name name of table in the database
     */

    public function __construct( $table_name ) {
        $this->fields = new Collection( 'Field' );
        $this->sections = [];
        $this->table_name = $table_name;
        $this->data = [];
        $this->can_delete = [];
        $this->duplicatable = true;
    }

    public function get_table_name() {
        return apply_filters( 'plugion_get_table_name', $this->table_name );
    }

    /**
     * create new Field and add to fields collection
     * @param string $slug slug
     * @param string $name name of the field in database
     * @param string $title UI name
     * @param string $type type of the field
     * @param string $section section to wich the field belongs (or empty)
     * @param array $extra_data additional information
     * @param mixed $default_value
     * @param mixed $editable
     * @param mixed $in_row
     * @param mixed $required
     */
    public function add_field( $slug, $name, $title, $type, $section = '', $extra_data = null, $default_value = '', $editable = true, $in_row = true, $required = true ) {
        $field = new Field( $name, $title, $type, $section, $extra_data, $default_value, $editable, $in_row, $required );
        $field->set_table_name( $this->table_name );
        $this->fields->add( $field, $slug );
    }
    public function prepare_fields_to_view() {
        $fields = $this->fields->get_elements();
        $this->data['fields_to_view_db'][ 'id' ] = 'id';
        // filter fields list by role

        $fields = $this->filter_fields_by_role( 'view', $fields );

        foreach ( $fields as $slug => $field ) {
            if ( !$field->get_in_row() ) {
                //    continue;
            }
            $this->data['fields_to_view'][ $slug ] = $field;
            $this->data['fields_to_view_db'][ $slug ] = \Plugion_Model_Utils::clean_up_string( $field->get_name() );
        }
        if ( 1 === count( $this->data['fields_to_view_db'] ) ) {
            $this->data['fields_to_view_db'] = [];
            $this->data['fields_to_view'] = [];
        }
    }
    /**
     * main fucntion to get data from the database
     * @param mixed $filters
     * @return array
     */
    public function get_rows( $filters = [] ) {
        global $wpdb;
        $this->prepare_fields_to_view();
        if ( 0 === count( $this->data['fields_to_view'] ) ) {
            $this->data['rows'] = [];
            return false;
        }
        // check for filters with default values
        if( count( $filters ) == 0 ){
            foreach ( $this->data['fields_to_view'] as $slug => $field ) {
                if( count( $field->get_filter_value() ) > 0 ){
                    $filter['value'] = $field->get_filter_value();
                    $filter['name'] = $slug;
                    $filters[] = $filter;
                }
            }
        }
        if ( count( $filters ) > 0 ) {
            foreach ( $filters as $filter ) {
                if ( !isset( $this->data['fields_to_view'] ) ) {
                //    continue;
                }
                $filter_name =  $filter['name'];


                if( is_array( $filter['value'] ) ){
                    $filter_value = $filter['value'];
                } else {
                    $filter_value = array(  $filter['value'] );
                }
                $filter_value = apply_filters( 'plugion_filter_value', $filter_value, $filter_name );
                $this->fields->get_element_at( $filter_name )->set_filter_value( $filter_value );
            }
        }
        $conditions_by_fields = [];
        foreach ( $this->fields->get_elements() as $slug => $field ) {

            $filter_sql = $field->filter_to_sql();

            if ( '' !==  $filter_sql ) {

                $conditions_by_fields[] = '(' . $filter_sql . ')';
            }
        }
        if ( count( $conditions_by_fields ) > 0 ) {
            $conditions = implode( ' AND ', $conditions_by_fields );
        } else {
            $conditions = '';
        }
        // hook conditions
        $conditions = apply_filters( 'plugion_get_rows_conditions', $conditions, $this->get_table_name() );
        if ( '' !== $conditions ) {
            $conditions =  ' WHERE ' . $conditions;
        }
        $sql = 'SELECT ' . implode( ', ', $this->data['fields_to_view_db'] ) . ' from ' . \Plugion_Model_Utils::clean_up_string( $this->get_table_name() ) . $conditions;

        $result = apply_filters( 'plugion_rows_value', $wpdb->get_results( $sql ), $this->table_name );
        $this->data['rows'] = $result;

        if ( is_null( $this->data['rows'] ) ) {
            $this->data['rows'] = [];
            return false;
        }
        return true;
    }
    /**
     * prepare the form for filtering data in the table
     * @return null
     */
    public function prepare_filter_form() {
        $this->data['filters'] = [];
        $fields = $this->fields->get_elements();
        $fields = $this->filter_fields_by_role( 'view', $fields );
        // filter fields list by role
        $fields = $this->filter_fields_by_role( 'view', $fields );
        foreach ( $fields as $slug => $field ) {
            if ( '' !== $field->get_filter_type() ) {
                $this->data['filters'][$slug] = $field;
            }
        }
    }
    /**
     * prepare the form for adding or updating new element in the table
     * based on current user role and 3d party hooks
     * @param  string $action 'add' or 'update'ó
     * @return null
     */
    public function prepare_properties_form( $action ) {
        // filter fields by ediable flag
        $fields = $this->filter_fields_by_ediable( $this->fields->get_elements() );

        // filter fields list by role
        $fields = $this->filter_fields_by_role( $action, $fields );

        $this->data['property_sections_' . $action ] = [];
        $this->data['property_fields_' . $action ] = [];
        foreach ( $fields as $slug => $field ) {
            $this->data['property_sections_' . $action][] = $field->get_section();
            $this->data['property_fields_' . $action][$slug] = $field;
        }
    }
    /**
     * @param mixed $key
     * @return string
     */
    public function get_data( $key ) {
        if ( isset( $this->data[$key] ) ) {
            return $this->data[$key];
        }

        return '';
    }
    /**
     * check if structure of table in database is the same as declared in model
     * and update structure if differences found
     * @var bool
     */
    public function sync_structure() {
        global $wpdb;
        $table_name = \Plugion_Model_Utils::clean_up_string( $this->get_table_name() );
        // check if table exitsts and create if does not
        $wpdb->query( "CREATE TABLE IF NOT EXISTS " . $table_name . " ( id int unsigned NOT NULL auto_increment PRIMARY KEY )" );
        // iterate over fields
        $fields = $this->fields->get_elements();
        foreach ( $fields as $slug => $field ) {
            // check if field exists
            $field_name = \Plugion_Model_Utils::clean_up_string( $field->get_name() );
            $args = [ $field_name  ];
            if ( 0 === $wpdb->query(  $wpdb->prepare( 'SHOW COLUMNS FROM ' . $table_name . ' LIKE %s ', $args ) ) ) {
                $sql_type = $field->field_type_to_sql_type();
                if ( false !== $sql_type ) {
                    $wpdb->query( 'ALTER TABLE `' . $table_name . '`  ADD `' . $field_name .  '`' . $sql_type );
                }
            }
        }
    }

    /**
     * @return string
     */
    public function get_single_item_name() {
        return $this->single_item_name;
    }

    /**
     * @param string $single_item_name
     *
     * @return static
     */
    public function set_single_item_name( $single_item_name ) {
        $this->single_item_name = $single_item_name;

        return $this;
    }

    /**
     * update row
     * @param $strin $data new data
     * @param mixed $data
     */
    public function add_row( $data ) {
        global $wpdb;
        $sanitized_data = [];
        // sanitization
        foreach ( $data as $key => $value ) {
            $sanitized_data[ sanitize_text_field( $value['name'] )] = $value['value'];
        }

        // filter fields list by role
        $fields = $this->filter_fields_by_role( 'add', $this->fields->get_elements() );

        // filter fields by dependency
        $fields = $this->filter_fields_by_dependency( $fields, $sanitized_data );

        $fields_before_editable_check = $fields;

        // filter fields list by editable
        $fields = $this->filter_fields_by_ediable( $fields );


        //ob_start();
        //var_dump($sanitized_data);
        //$res = ob_get_clean();

        // validation
        $validation = $this->validate_fields( $fields, $sanitized_data );
        $invalid_fields = $validation[0];

        if ( count( $invalid_fields ) > 0 ) {
            return [ false, $invalid_fields ];
        }

        $values_and_format = [ $validation[1], $validation[2] ];

        // filter by 3dparty (add values)
        $values_and_format = apply_filters( 'plugion_add_row_values', $values_and_format, $this->get_table_name() );
        $table_name = \Plugion_Model_Utils::clean_up_string( $this->get_table_name() );

        $wpdb->insert( $table_name, $values_and_format[0], $values_and_format[1] );

        $id = $wpdb->insert_id;
        $row =  $this->get_row( $id );
        if ( is_null( $row ) ) {
            return null;
        }

        $row = json_decode(json_encode( $row ), true);
        $row_to_action = (object) $row;

        do_action( 'plugion_on_after_row_add', $table_name, $this->table_name, $row_to_action );

        return [ $row, $fields_before_editable_check ];
    }

    public function update_row( $data, $row_id ) {
        global $wpdb;
        $sanitized_data = [];
        // sanitization
        foreach ( $data as $key => $value ) {
            $sanitized_data[ sanitize_text_field( $value['name'] )] =   $value['value'];
        }

        // filter fields list by role
        $fields = $this->filter_fields_by_role( 'update', $this->fields->get_elements() );
        // filter fields by dependency
        $fields = $this->filter_fields_by_dependency( $fields, $sanitized_data );
        $fields_before_editable_check = $fields;
        // filter fields list by editable
        $fields = $this->filter_fields_by_ediable( $fields );

        // validation
        $validation = $this->validate_fields( $fields, $sanitized_data );
        $invalid_fields = $validation[0];
        $valid_fields =  $validation[1];
        $field_formats =  $validation[2];
        if ( count( $invalid_fields ) > 0 ) {
            return [ false, $invalid_fields ];
        }
        // filter by 3dparty
        $conditions = [ [ 'id' => $row_id ], [ '%d' ] ];
        $conditions = apply_filters( 'plugion_update_row_conditions', $conditions, $this->get_table_name() );

        $table_name = \Plugion_Model_Utils::clean_up_string( $this->get_table_name() );
        if ( false === $wpdb->update( $table_name, $valid_fields, $conditions[0], $field_formats, $conditions[1] ) ) {
            return [ false, null ];
        }
        $row = $this->get_row( $row_id );
        if ( is_null( $row ) ) {
            return  null;
        }
        $row = json_decode(json_encode( $row ), true);
        $row_to_action = (object)$row;
        do_action( 'plugion_on_after_row_update', $table_name, $this->table_name, $row_to_action );

        return [ $row, $fields_before_editable_check ];
    }
    public function get_row( $id, $output = OBJECT ) {
        global $wpdb;
        $args = [ $id ];
        $table_name = \Plugion_Model_Utils::clean_up_string( $this->get_table_name() );
        $this->prepare_fields_to_view();
        if ( 0 === count( $this->data['fields_to_view_db'] ) ) {
            return null;
        }
        $fields = implode( ',', $this->data['fields_to_view_db'] );
        $conditions =  ' where id = %d ';
        $conditions = apply_filters( 'plugion_get_row_conditions', $conditions, $this->get_table_name() );

        $value = $wpdb->get_row(  $wpdb->prepare( 'SELECT ' . $fields . ' FROM ' . $table_name . $conditions, $args ), $output );
        $value = array( $value );
        apply_filters( 'plugion_rows_value', $value, $this->table_name );
        return $value[0];

    }
    public function delete_row( $id ) {
        global $wpdb;
        $args = [ $id ];
        $table_name = \Plugion_Model_Utils::clean_up_string( $this->get_table_name() );
        $row = $this->get_row( $id );
        if ( \is_null( $row ) ) {
            return false;
        }
        if ( !$this->current_user_can_delete( $row ) ) {
            return false;
        }
        do_action( 'plugion_on_before_row_delete', $table_name, $this->table_name, $row );
        $result = $wpdb->delete( $table_name, [ 'id' => $id ], '%d' );
        do_action( 'plugion_on_after_row_delete', $table_name, $row );

        return $result;
    }
    public function сurrent_user_can_view() {
        $user = wp_get_current_user();
        if ( current_user_can( 'manage_options' )  ) {
            return true;
        }
        $this->prepare_fields_to_view();
        if ( 0 === count( $this->data['fields_to_view_db'] ) ) {
            return false;
        }

        return true;
    }
    public function current_user_can_delete( $row = null ) {
        $user = wp_get_current_user();
        if( current_user_can( 'manage_options' ) ) {
            return apply_filters( 'plugion_row_can_delete', true, $row,  $this->get_table_name() );
        }
        foreach ( $user->roles as $role ) {
            if ( in_array( $role, $this->can_delete, true ) ) {
                return apply_filters( 'plugion_row_can_delete', true, $row, $this->get_table_name() );
            }
        }
        return apply_filters( 'plugion_row_can_delete', false, $row, $this->get_table_name() );;
    }
    public function current_user_can_add() {
        // filter fields list by editable
        $fields = $this->filter_fields_by_ediable( $this->fields->get_elements() );

        // filter fields list by role
        $fields = $this->filter_fields_by_role( 'add', $fields );

        if ( 0 === count( $fields) ) {
            return false;
        }

        return true;
    }
    public function current_user_can_update() {
        // filter fields list by editable
        $fields = $this->filter_fields_by_ediable( $this->fields->get_elements() );

        // filter fields list by role
        $fields = $this->filter_fields_by_role( 'update', $fields );

        if ( 0 === count( $fields) ) {
            return false;
        }

        return true;
    }
    /**
     * Check role current user for duplicate row
     * @return bool
     */
    public function current_user_can_duplicate() {
        // filter fields list by editable
        $fields = $this->filter_fields_by_ediable( $this->fields->get_elements() );

        // filter fields list by role
        $fields = $this->filter_fields_by_role( 'add', $fields );

        if ( 0 === count( $fields) ) {
            return false;
        }
        return true;
    }

    /**
     * Get the value of Name of the multiple items in the table
     *
     * @return string
     */
    public function get_multiple_item_name() {
        return $this->multiple_item_name;
    }

    /**
     * Set the value of Name of the multiple items in the table
     *
     * @param string multiple_item_name
     * @param mixed $multiple_item_name
     *
     * @return self
     */
    public function set_multiple_item_name($multiple_item_name) {
        $this->multiple_item_name = $multiple_item_name;

        return $this;
    }
    /**
     * filter fields based on dependncies betwenn fields
     * @param  array $fields array of fields to filter
     * @param  array $post_data sanitized post data
     * @return array array of filtered fields
     */
    public static function filter_fields_by_dependency( $fields, $post_data ) {
        $fields_filtered = [];
        foreach ( $fields as $slug => $field ) {
            // check if dependency set
            if ( count( $field->get_dependency() ) > 0 ) {
                $arr_dependency = $field->get_dependency();

                // check if dependency set for roles
                if ( isset(  $arr_dependency['administrator'] ) ) {
                    $user = wp_get_current_user();
                    $role = $user->roles[0];
                    if ( isset( $arr_dependency[ $role ] ) ) {
                        $arr_dependency = $arr_dependency[ $role ];
                    }
                }
                $rules_passed = true;
                foreach ( $arr_dependency as $dependency_rule ) {
                    if ( 3 !== count( $dependency_rule ) ) {
                        continue;
                    }
                    switch ( $dependency_rule[1] ) {
                        case '=':
                            if ( isset( $post_data[ $dependency_rule[0] ] ) ) {
                                if ( $post_data[ $dependency_rule[0] ] != $dependency_rule[2] ) {
                                    $rules_passed = false;
                                }
                            }

                            break;
                        case '<':
                            if ( isset(  $post_data[ $dependency_rule[0] ] ) ) {
                                if ( $post_data[ $dependency_rule[0] ] >= $dependency_rule[2] ) {
                                    $rules_passed = false;
                                }
                            }

                            break;
                        case '>':
                            if ( isset(  $post_data[ $dependency_rule[0] ] ) ) {
                                if ( $post_data[ $dependency_rule[0] ] <= $dependency_rule[2] ) {
                                    $rules_passed = false;
                                }
                            }

                            break;
                        case '!=':
                                if ( isset(  $post_data[ $dependency_rule[0] ] ) ) {
                                    if ( $post_data[ $dependency_rule[0] ] == $dependency_rule[2] ) {
                                        $rules_passed = false;
                                    }
                                }

                                break;
                        default:

                                break;
                    }
                }

                if( $rules_passed ){
                    $fields_filtered[ $slug ] = $field;
                }
            } else {
                $fields_filtered[ $slug ] = $field;
            }
        }

        return $fields_filtered;
    }
    /**
     * grant full access to all fields of tables
     * @param array $roles wp user roles that obtain full access
     * @return null
     */
    public function grant_full_access( $roles ) {
        foreach ( $this->fields->get_elements() as $slug => $field ) {
            $field->set_can_add( $roles );
            $field->set_can_update( $roles );
            $field->set_can_view( $roles );
        }
        $this->can_delete = $roles;
    }

    /**
     * Get the value of Defind the roles of wp users that can delete get_rows
     *
     * @return array
     */
    public function get_can_delete() {
        return $this->can_delete;
    }

    /**
     * Set the value of Defind the roles of wp users that can delete get_rows
     *
     * @param array can_delete
     * @param mixed $can_delete
     *
     * @return self
     */
    public function set_can_delete( $can_delete ) {
        $this->can_delete = $can_delete;

        return $this;
    }
    public function validate_fields( $fields, $data ) {
        $invalid_fields = [];
        $valid_fields = [];
        $field_formats = [];

        foreach ( $fields as $slug => $field ) {

            if ( isset( $data[ $field->get_name() ] ) ) {
                $validation_result = apply_filters( 'plugion_property_field_validation_' . $field->get_type(), [ false, '' ], $data[ $field->get_name() ], $slug, $field );
                if ( !array( $validation_result ) ) {
                    $invalid_fields[] = [ $slug, sprintf( plugion_translate_string( 'Validation of %s failed' ), $field->get_title() ) ];
                } else {
                    if ( true === $validation_result[0] ) {
                        $format = $field->field_type_to_sql_type( true );
                        if ( false === $format ) {
                            continue;
                        }
                        $valid_fields[ $field->get_name() ] = $validation_result[1];

                        $field_formats[] = $format;
                    } else {
                        $invalid_fields[] = [ $slug, $validation_result[1] ];
                    }
                }

            } else {
                if ( $field->get_required() ) {
                    $invalid_fields[] = [ $slug, sprintf( plugion_translate_string( 'Field %s is empty' ), $field->get_title() ) ];
                }
            }
        }

        return [ $invalid_fields, $valid_fields, $field_formats ];
    }

    protected function filter_fields_by_role( $action, $fields ) {
        $fields_filtered = [];

        foreach ( $fields as $slug => $field ) {
            // check if current user can add the field
            if ( is_user_logged_in() ) {
                $user = wp_get_current_user();
                if ( !current_user_can( 'manage_options' ) ) {
                    $role_found = false;
                    foreach ( $user->roles as $role ) {
                        if ( 'add' === $action ) {
                            $compare_arr =  $field->get_can_add();
                        }
                        if ( 'view' === $action ) {
                            $compare_arr =  $field->get_can_view();
                        }
                        if ( 'update' === $action ) {
                            $compare_arr =  $field->get_can_update();
                        }
                        if ( in_array( $role, $compare_arr, true ) ) {
                            $role_found = true;
                        }
                    }
                    if ( !$role_found ) {
                        continue;
                    }
                } else {
                    $admin_passed = true;
                    $admin_passed = apply_filters( 'plugion_admin_filter_by_role', $admin_passed, $action, $field );
                    if( !$admin_passed ){
                        continue;
                    }
                }
            } else {
                continue;
            }
            $fields_filtered[ $slug ] = $field;
        }

        return $fields_filtered;
    }

    protected function filter_fields_by_ediable( $fields ) {
        $fields_filtered = [];
        foreach ( $fields as $slug => $field ) {
            // check if this field can be edited
            if ( !$field->get_editable() ) {
                continue;
            }
            $fields_filtered[ $slug ] = $field;
        }

        return $fields_filtered;
    }

    /**
     * function to get value var duplicatable
     * @return bool
     */
    public function get_duplicatable() {
        return $this->duplicatable;
    }

    public function set_duplicatable( $duplicatable ) {
        $this->duplicatable = $duplicatable;
    }

    public function get_confirm_duplicate() {
        return $this->confirm_duplicate;
    }

    public function set_confirm_duplicate( $confirm_duplicate ) {
        $this->confirm_duplicate = $confirm_duplicate;
    }




}
