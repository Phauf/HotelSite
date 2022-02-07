<?php
//WBK database entity class
// check if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
// include validator class
require_once  dirname(__FILE__) . '/class_wbk_validator.php';
class WBK_Entity {
	// entity id
	protected $id;
	// entity name
	protected $name;
 	// entity description
	protected $description;
	// table name
	protected $table_name;
	// errors array
	protected $error_messages;
	public function __construct() {
		$this->error_messages = array();
	}
	// set id
	public function setId( $value ) {

		if ( WBK_Validator::checkInteger( $value, 1, 9999999 ) ){
			$this->id = $value;
			return true;
		} else {
			array_push( $this->error_messages, __( 'incorrect id', 'wbk' ) );
			return false;
		}
	}
	// get id
	public function getId() {
		return absint( $this->id );
	}
	// set name
	public function setName( $value ) {
		$value = sanitize_text_field ( $value );
		if ( WBK_Validator::checkStringSize( $value, 0, 128 ) ){
			$this->name = $value;
			return true;
		} else {
			array_push( $this->error_messages, __( 'incorrect name', 'wbk' ) );
			return false;
		}
	}
	// get name
	public function getName( $unescaped = false ) {
		$value = sanitize_text_field( $this->name );
		if( $unescaped ){
			$value = stripcslashes( $value );
		}
		return $value;
	}
	// set description
	public function setDescription( $value ) {
		$this->description = $value;
		return true;
	}
	// get description
	public function getDescription( $unescaped = false ) {
		$value =   $this->description;
		if( $unescaped ){
			$value = stripcslashes( $value );
		}
		return $value;
	}
	 // load row by id
	public function load () {
		global $wpdb;
		if ( !isset( $this->id ) ) {
			return false;
		}
		$data[0] = " SELECT * FROM $this->table_name WHERE id = %d ";
		$data[1] = array( $this->id );

		$data = apply_filters( 'wbk_load_entity_data', $data );
		$result = $wpdb->get_row( $wpdb->prepare( $data[0] , $data[1] ) );


		if ( $result == NULL ) {
			// return false;
		}
		if( is_object( $result ) ){
			if ( !$this->setName( $result->name ) ) {
				// return false;
			}
			if ( !$this->setDescription( $result->description ) ) {
				// return false;
			}			
		}
		return $result;
	}

	// check name duplicate
	protected function nameDuplicate() {
		global $wpdb;
		$count = $wpdb->get_var( $wpdb->prepare( " SELECT COUNT(*) FROM $this->table_name WHERE name = %s AND id <> %d ", $this->getName(), $this->getId() ) );
		if ( $count > 0 ){
			return false;

		} else {
			return true;
		}
	}
	// update entity
	public function update() {
		global $wpdb;
		if ( !isset( $this->id ) ) {
			return false;
		}
		$data[0] = array( 'id' => $this->getId() );
		$data[1] = array( '%d' );
		$data = apply_filters( 'wbk_update_entity_conditions', $data );

		if ( $wpdb->update(
				$this->table_name,

				array(

					'name' => $this->getName(),

					'description' => $this->getDescription()
				),
				$data[0],
				array(

					'%s',
					'%s'
				),
				$data[1]
			) === false ) {
			return false;
		} else {
			return true;
		}
	}
	// delete entity from database
	public function delete() {
		global $wpdb;
		$result = $wpdb->query( $wpdb->prepare( "DELETE FROM $this->table_name WHERE id = %d", $this->getId() ) );

  		return $result;
	}
}
