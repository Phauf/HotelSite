<?php
namespace Plugion;
if ( !defined( 'ABSPATH' ) ) exit;
/*
 * This file is part of Plugion framework.
 * (c) plugion.com <hello@plugion.org>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */


if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Collection class
 * use this class to collect tables, fields and other array elements.
 */
class Collection {
    /**
     * items.
     *
     * @var string
     */
    protected $class_name;

    /**
     * array of elements in collection.
     *
     * @var array()
     */
    protected $items;

    /**
     * constructor.
     *
     * @param string $class_name of elements
     */
    public function __construct( $class_name ) {
        $this->class_name = $class_name;
        $this->items = [];
    }

    /**
     * add new element in collection.
     *
     * @param var    $object object to add in collection
     * @param string $slug   the key in array
     */
    public function add( $object, $slug ) {
        if ( array_key_exists( $slug, $this->items ) ) {
            return false;
        }
        $this->items[$slug] = $object;

        return $this->items[$slug];
    }
    /**
     * remove element from collection.
     *
     * @param string $slug key of the element to remove
     *
     * @return bool returns true is removed successfully
     */
    public function remove( $slug ) {
        if ($tables->has( $slug ) ) {
            return false;
        }
        unset($slug);

        return true;
    }

    /**
     * set the element.
     * @param var    $object object to set  in collection
     * @param string $slug the key in array
     */
    public function set( $object, $slug ) {
        if ( !is_a( $object, $this->class_name ) ) {
            return false;
        }
        $items[$slug] = $object;
    }


    public function get_count() {
        return count( $this->items );
    }
    public function get_elements() {
        return $this->items;
    }
    public function get_element_at( $slug ) {
        if ( isset( $this->items[ $slug ] ) ) {
            return $this->items[ $slug ];
        }

        return false;
    }
}
