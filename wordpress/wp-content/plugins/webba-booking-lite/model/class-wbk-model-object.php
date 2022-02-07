<?php
if (!defined('ABSPATH')) {
    exit;
}
class WBK_Model_Object {
    /**
     * object name
     * @var string
     */
    protected $name;

    /**
     * name of the table in the db that stores the object of current sqlite_fetch_column_types
     * @var string
     */
    protected $table_name;

    /**
     * fields loaded from the database
     * @var array
     */
    protected $fields;

    /**
     * id of the fields in the db
     * @var integer
     */
    protected $id;

    public function __construct( $id = null ) {
        global $wpdb;
        if( !is_null( $id ) ){
            $this->fields = $wpdb->get_row( $wpdb->prepare( " SELECT * FROM " . $this->table_name . " WHERE id = %d", $id ) , ARRAY_A );
        }
    }

    /**
     * get field value
     * @param string $field_name the name of the field to get
     * @return mixed value of the field
     */
    public function get( $field_name ){
        if( isset( $this->fields[ $field_name ] ) ){
            return  $this->fields[ $field_name ];
        }
        return false;
    }

    /**
     * set field value
     * @param string $field_name field name
     * @param mixed $value value to be set
     */
    public function set( $field_name, $value ){
        $this->fields[ $field_name ] = $value;
        return;
    }

    
    /**
     * Get the value of object name
     *
     * @return string
     */
    public function get_name() {
        if( isset( $this->fields['name'] ) ){
            return $this->fields['name'];
        }
        return '';
    }

    /**
     * Set the value of object name
     *
     * @param string $name
     *
     * @return self
     */
    public function set_name($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of name of the table in the db that stores the object of current sqlite_fetch_column_types
     *
     * @return string
     */
    public function get_table_name() {
        return $this->table_name;
    }

    /**
     * Set the value of name of the table in the db that stores the object of current sqlite_fetch_column_types
     *
     * @param string $table_name
     *
     * @return self
     */
    public function set_table_name($table_name) {
        $this->table_name = $table_name;

        return $this;
    }

    /**
     * Get the value of fields loaded from the database
     *
     * @return array
     */
    public function get_fields() {
        return $this->fields;
    }

    /**
     * Set the value of fields loaded from the database
     *
     * @param array $fields
     *
     * @return self
     */
    public function set_fields(array $fields) {
        $this->fields = $fields;

        return $this;
    }

    /**
     * get object id
     * @return int id
     */
    public function get_id(){
        return $this->fields['id'];
    }

}
