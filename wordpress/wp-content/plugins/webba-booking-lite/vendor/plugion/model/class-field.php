<?php

/*
 * This file is part of Plugion framework.
 * (c) plugion.com <hello@plugion.org>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Plugion;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/*
 * This file is part of Plugion framework.
 * (c) plugion.com <hello@plugion.org>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Field class
 */
class Field {
    /**
     * type of field
     * defines type in database table, render and validation
     * @var string
     */
    protected $type;

    /**
     * readable title
     * used in fronend and backend
     * @var string
     */
    protected $title;

    /**
     * field name in the databse
     * @var string
     */
    protected $name;

    /**
     * dependency that defines if this field is used
     * @var array
     */
    protected $dependency;

    /**
     * required or not (validation)
     * @var bool
     */
    protected $required;
    /**
     * array of user roles who can view this field
     * do not add administrator role (it's added by default)
     * @var array
     */
    protected $can_view;
    /**
     * array of user roles who can add this field on adding new row
     * do not add administrator role (it's added by default)
     * @var array
     */
    protected $can_add;
    /**
     * array of user roles who can update this field on updating new row
     * do not add administrator role (it's added by default)
     * @var array
     */
    protected $can_update;
    /**
     * extra data
     * @var array
     */
    protected $extra_data;

    /**
     * The section to which the field belongs
     * if not set, the fild will be shown in the "miscellaneous" section
     * or if all fields do not have section set, will be ignored
     * @var string
     */
    protected $section;

    /**
     * Ddefine if the field will be shown in the rows of the table
     * @var bool
     */
    protected $in_row;

    /**
     * Defind if this can be edited (true) or is for internal usage (false)
     * @var bool
     */
    protected $editable;

    /**
     * default value
     * @var string
     */
    protected $default_value;

    /**
     * type of filter. used in rendering action.
     * @var string
     */
    protected $filter_type;

    /**
     * filter conditions used in filter_to_sql function;
     * @var array
     */
    protected $filter_conditions;

    /**
     * value of filter. used in filter_to_sql function
     * array type used to build compound SQL statements
     * @var array
     */
    protected $filter_value;

    /**
     * delimiter used for compound filters
     * can be 'OR' or 'AND'
     * @var string;
     */
    protected $filter_delimiter;

    /**
     * name of the table to wich the field belong
     * @var string;
     */

    /**
     * extra data for filter
     * @var mixed
     */
    protected $filter_extra;

    protected $table_name;

    /**
     * sort type used by DataTable plugin
     * @var
     */
    protected $sort_type;

    /**
     * default constructor
     *
     * @param mixed $type
     * @param mixed $title
     * @param mixed $Name
     * @param mixed $section
     * @param null|mixed $extra_data
     * @param mixed $name
     * @param mixed $default_value
     * @param mixed $editable
     * @param mixed $in_row
     * @param mixed $required
     */
    public function __construct( $name, $title, $type, $section = '', $extra_data = null, $default_value = '', $editable = true, $in_row = true, $required = true ) {
        $this->type = $type;
        $this->title = $title;
        $this->name = $name;
        $this->extra_data = $extra_data;
        $this->section = $section;
        $this->dependency = [];
        $this->in_row = $in_row;
        $this->editable = $editable;
        $this->default_value = $default_value;
        $this->required = $required;
        $this->filter_type = '';
        $this->can_view = [];
        $this->can_add = [];
        $this->can_update = [];
        $this->filter_value = [];
        $this->filter_conditions = [];
        $this->filter_type = '';
    }

    /**
     * set data related to filters.
     * @param string $type type of filter
     * @param array $conditions template used to convert filter to SQL
     * @param array $value default values
     * @param string $delimiter filter delimiter
     * @param mixed $extra extra-data
     */
    public function set_filter_data( $type, $conditions, $value, $delimiter, $extra = null ) {
        $this->filter_type = $type;
        $this->filter_conditions = $conditions;
        $this->filter_value = $value;
        $this->filter_delimiter = $delimiter;
        $this->filter_extra = $extra;
    }

    public function filter_to_sql() {
        global $wpdb;

        if ( 0 === count( $this->filter_value ) ) {
            return '';
        }
        $allowed_conditions = [ '>', '<', '=', '<=', '>=', 'LIKE', 'IN' ];
        $allowed_delimiters = [ ' AND ', ' OR ', '' ];
        if ( !in_array( $this->filter_delimiter, $allowed_delimiters, true) ) {
            return '';
        }
        $formated_conditions = [];
        $i = 0;
        foreach ( $this->filter_conditions as $condition ) {
            if ( !is_array(  $this->filter_value ) && '' === trim( $this->filter_value[$i] ) ) {
                continue;
            }
            if ( !in_array( $condition, $allowed_conditions, true ) ) {
                continue;
            }
            $format =  $this->field_type_to_sql_type( true );
            switch ( $format ) {
                case '%s':
                    $condition = strtoupper( $condition );
                    switch ( $condition ) {
                        case 'LIKE':
                            $this->filter_value[$i] = '%' . mb_strtoupper( $this->filter_value[$i] ) . '%';
                            $formated_conditions[] = 'UPPER(' . $this->name . ') ' . $condition . ' "' . $format . '"';

                        break;
                        default:
                            $formated_conditions[] =  $this->name . ' ' . $condition . ' "' . $format . '"';

                        break;
                    }

                    break;
                case '%d':

                    switch ( $condition ) {
                        case 'IN':
                            $temp = [];
                            foreach ( $this->filter_value as $filter_value ) {
                                $temp[] = '(' . $this->name . ' = ' . $format . ')';
                            }
                            $formated_conditions[] = implode( ' OR ', $temp );

                            break;
                        default:
                            $formated_conditions[] =  $this->name . ' ' . $condition . ' "' . $format . '"';

                            break;
                    }

                    break;
                default:
                    $formated_conditions[] =  $this->name . ' ' . $condition . ' ' . $format;

                    break;
            }
            $i++;
        }

        if ( 0 === count( $formated_conditions ) ) {
            return '';
        }
        $condition = implode( $this->filter_delimiter, $formated_conditions );

        return $wpdb->prepare( $condition, $this->filter_value );
    }
    /**
     * get required
     * @return bool
     */
    public function get_required() {
        return $this->required;
    }

    /**
     * @param bool $required
     *
     * @return static
     */
    public function set_required(bool $required) {
        $this->required = $required;

        return $this;
    }

    /**
     * @return array
     */
    public function get_dependency() {
        return $this->dependency;
    }

    /**
     * @param array $dependency
     *
     * @return static
     */
    public function set_dependency(array $dependency) {
        $this->dependency = $dependency;

        return $this;
    }
    /**
     * @return string
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return static
     */
    public function set_name(string $name) {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function get_title() {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return static
     */
    public function set_title(string $title) {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function get_type() {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return static
     */
    public function set_type(string $type) {
        $this->type = $type;

        return $this;
    }
    /**
     * @return array
     */
    public function get_can_view() {
        return apply_filters( 'plugion_field_can_view', $this->can_view, $this->name, $this->table_name );
    }

    /**
     * @param array $can_view
     *
     * @return static
     */
    public function set_can_view(array $can_view) {
        $this->can_view = $can_view;

        return $this;
    }

    /**
     * @return array
     */
    public function get_extra_data() {
        return $this->extra_data;
    }

    /**
     * @param array $extra_data
     *
     * @return static
     */
    public function set_extra_data(array $extra_data) {
        $this->extra_data = $extra_data;

        return $this;
    }

    /**
     * @return string
     */
    public function get_section() {
        return $this->section;
    }

    /**
     * @param string $section
     *
     * @return static
     */
    public function set_section(string $section) {
        $this->section = $section;

        return $this;
    }
    /**
     * @return bool
     */
    public function get_in_row() {
        return $this->in_row;
    }

    /**
     * @param bool $in_row
     *
     * @return static
     */
    public function set_in_row(bool $in_row) {
        $this->in_row = $in_row;

        return $this;
    }

    /**
     * @return bool
     */
    public function get_editable() {
        return $this->editable;
    }

    /**
     * @param bool $editable
     *
     * @return static
     */
    public function set_editable(bool $editable) {
        $this->editable = $editable;

        return $this;
    }
    /**
     * @return string
     */
    public function get_default_value() {
        return $this->default_value;
    }

    /**
     * @param string $default_value
     *
     * @return static
     */
    public function set_default_value(string $default_value) {
        $this->default_value = $default_value;

        return $this;
    }
    /**
     * @return array
     */
    public function get_can_add() {
        return apply_filters( 'plugion_field_can_add', $this->can_add, $this->name, $this->table_name );
    }
    /**
     * @param array $can_add
     *
     * @return static
     */
    public function set_can_add(array $can_add) {
        $this->can_add = $can_add;

        return $this;
    }
    /**
     * @return array
     */
    public function get_can_update() {

        return apply_filters( 'plugion_field_can_update', $this->can_update, $this->name, $this->table_name );
    }

    /**
     * @param array $can_update
     *
     * @return static
     */
    public function set_can_update(array $can_update) {
        $this->can_update = $can_update;

        return $this;
    }
    /**
     * convert field type to sql type ready for using in statement
     * include hook for 3d party
     * @param bool $get_format set true if need to return only format
     * @return string  SQL structure type
     */
    public function field_type_to_sql_type( $get_format = false ) {
        $arr_sql_parts = [];

        switch ( $this->type ) {
            case 'text':
                $ed = $this->get_extra_data();
                if ( isset( $ed['type'] ) ) {
                    switch ( $ed['type']) {
                        case 'positive_integer':
                        $arr_sql_parts =  [ 'int', 'unsigned NULL', '', '%d' ];

                        break;
                        case 'none_negative_integer':
                        $arr_sql_parts =  [ 'int', 'unsigned NULL', '', '%d' ];

                        break;
                        case 'integer':
                        $arr_sql_parts =  [ 'int', ' NULL ', '', '%d' ];

                        break;
                        case 'float':
                        $arr_sql_parts =  [ 'float', '', '', '%f' ];

                        break;
                        case 'none_negative_float':
                        $arr_sql_parts =  [ 'float', '', '', '%f' ];

                        break;
                        case 'email':
                        $arr_sql_parts =  [ 'VARCHAR', 256, '', '%s' ];

                        break;
                        default:
                        $arr_sql_parts =  [ 'VARCHAR', 256, '', '%s' ];

                        break;
                    }
                } else {
                    $arr_sql_parts =  [ 'VARCHAR', 256, '', '%s' ];
                }

                break;
            case 'radio':
                $arr_sql_parts =  [ 'VARCHAR', 256, '', '%s' ];

                break;
            case 'checkbox':
                $arr_sql_parts =  [ 'VARCHAR', 256, '', '%s' ];

                break;
            case 'select':
                $ed = $this->get_extra_data();
                if ( isset( $ed['type'] ) &&  'positive_integer' === $ed['type'] ) {
                    $arr_sql_parts =  [ 'int', 'unsigned NULL', '', '%d' ];
                } else {
                    $arr_sql_parts =  [ 'VARCHAR', 1024, '', '%s' ];
                }

                break;
            case 'textarea':
                $arr_sql_parts =  [ 'TEXT', 65535, '', '%s' ];

                break;
            case 'datetime':
                $arr_sql_parts =  [ 'DATETIME', '', '', '%s' ];

                break;
            case 'date':
                $arr_sql_parts =  [ 'DATE', '', '', '%s' ];

                break;
            case 'editor':
                $arr_sql_parts =  [ 'mediumtext', 'NOT NULL', '', '%s' ];

                break;
            case 'date_range':
                $arr_sql_parts =  [ 'varchar', 128, '', '%s' ];

                break;
            default:
                break;
        }

        $arr_sql_parts = apply_filters( 'plugion_type_to_sql_type', $arr_sql_parts, $this->type, $this );

        if ( !is_array( $arr_sql_parts ) ) {
            return false;
        }
        if (  4 !== count( $arr_sql_parts ) ) {
            return false;
        }
        if ( '%s' !== $arr_sql_parts[3] && '%d' !== $arr_sql_parts[3] && '%f' !== $arr_sql_parts[3] ) {
            return false;
        }
        if ( $get_format ) {
            return $arr_sql_parts[3];
        }

        $result =  \Plugion_Model_Utils::clean_up_string( $arr_sql_parts[0] );

        if (  '%s' === $arr_sql_parts[3] ) {
            if ( (int)$arr_sql_parts[1] === $arr_sql_parts[1] && (int)$arr_sql_parts[1] > 0 ) {
                $result .= '(' . $arr_sql_parts[1] . ') ';
            }
            if ( '' !== trim( $arr_sql_parts[2] ) ) {
                $result .= ' default ' . \Plugion_Model_Utils::clean_up_string( $arr_sql_parts[2] );
            }
        } elseif ( '%d' === $arr_sql_parts[3] ) {
            $result .= ' ' . $arr_sql_parts[1];
        }

        return $result;
    }

    /**
     * Get the value of type of filter. used in rendering action.
     *
     * @return string
     */
    public function get_filter_type() {
        return $this->filter_type;
    }

    /**
     * Set the value of type of filter. used in rendering action.
     *
     * @param string filter_type
     * @param mixed $filter_type
     *
     * @return self
     */
    public function set_filter_type($filter_type) {
        $this->filter_type = $filter_type;

        return $this;
    }

    /**
     * Get the value of filter conditions used in filter_to_sql function;
     *
     * @return array
     */
    public function get_filter_conditions() {
        return $this->filter_conditions;
    }

    /**
     * Set the value of filter conditions used in filter_to_sql function;
     *
     * @param array filter_conditions
     *
     * @return self
     */
    public function set_filter_сonditions(array $filter_conditions) {
        $this->filter_conditions = $filter_conditions;

        return $this;
    }

    /**
     * Get the value of value of filter. used in filter_to_sql function
     *
     * @return array
     */
    public function get_filter_value() {
        return $this->filter_value;
    }

    /**
     * Set the value of value of filter. used in filter_to_sql function
     *
     * @param array filter_value
     *
     * @return self
     */
    public function set_filter_value(array $filter_value) {
        $this->filter_value = $filter_value;

        return $this;
    }

    /**
     * Get the value of delimiter used for compound filters
     *
     * @return string;
     */
    public function get_filter_delimiter() {
        return $this->filter_delimiter;
    }

    /**
     * Set the value of delimiter used for compound filters
     *
     * @param string; filter_delimiter
     *
     * @return self
     */
    public function set_filter_delimiter(string $filter_delimiter) {
        $this->filter_delimiter = $filter_delimiter;

        return $this;
    }

    /**
     * Get the value of name of the table to wich the field belong
     *
     * @return string;
     */
    public function get_table_name() {
        return $this->table_name;
    }

    /**
     * Set the value of name of the table to wich the field belong
     *
     * @param string; $table_name
     *
     * @return self
     */
    public function set_table_name( $table_name ) {
        $this->table_name = $table_name;

        return $this;
    }

    /**
     * Get the value of sort type used by DataTable plugin
     *
     * @return mixed
     */
    public function get_sort_type() {
        return $this->sort_type;
    }

    /**
     * Set the value of sort type used by DataTable plugin
     *
     * @param mixed sort_type
     * @param mixed $sort_type
     *
     * @return self
     */
    public function set_sort_type($sort_type) {
        $this->sort_type = $sort_type;

        return $this;
    }

    /**
     * Get the value of extra data for filter
     *
     * @return mixed
     */
    public function get_filter_extra() {
        return $this->filter_extra;
    }

    /**
     * Set the value of extra data for filter
     *
     * @param mixed $filter_extra
     *
     * @return self
     */
    public function set_filter_extra($filter_extra) {
        $this->filter_extra = $filter_extra;

        return $this;
    }
}
