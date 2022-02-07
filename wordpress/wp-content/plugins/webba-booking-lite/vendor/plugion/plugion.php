<?php
namespace Plugion;
if ( !defined( 'ABSPATH' ) ) exit;
/*
 * This file is part of Plugion framework.
 * (c) plugion.com <hello@plugion.org>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

include 'model/class-collection.php';
include 'model/class-table.php';
include 'model/class-field.php';
include 'model/class-model-utils.php';
include 'renderer/class-renderer.php';
include 'controller/class-controller.php';
include 'renderer/default_field_renders.php';
include 'renderer/default_filter_renders.php';
include 'controller/default_field_validation.php';
include 'utils/class-validator.php';
include 'utils/class-plugion-custom-field.php';
include 'utils/class-plugion-translator.php';

define( 'PLUGION_VERSION', '0.85' );

final class Plugion {
    /**
     * collection of tables
     * @var Collection
     */
    public $tables;

    /**
     * renderer
     * @var Renderer
     */
    public $renderer;
    /**
     * The single instance of the class.
     * @var Plugion
     */
    protected static $inst = null;

    /**
     * controller to handle Ajax requests
     * @var Controller
     */
    protected $controller;

    /**
     * indicatge if assets was loaded
     * @var bool
     */
    protected $assets_initialized;

    /**
     * constructor
     */
    private function __construct() {
        $this->tables = new Collection( 'Table' );
        $this->renderer = new Renderer();
        $this->controller = new Controller();

        \Plugion_Translator::load_textdomain();
    }

    /**
     * returns instance of Plugion object
     */
    public static function Instance() {
        if ( is_null( self::$inst ) ) {
            self::$inst = new self();
        }

        return self::$inst;
    }
    /**
     * render table
     * @param  string $slug slug of the table to render
     * @return null
     */
    public function Table( $slug ) {
        $this->tables->get_element_at( $slug )->get_rows();
        $this->tables->get_element_at( $slug )->prepare_properties_form( 'add' );
        $this->tables->get_element_at( $slug )->prepare_properties_form( 'update' );
        $this->tables->get_element_at( $slug )->prepare_filter_form();
        $this->renderer->render_table( $slug );
    }
    /**
     * load CSS and JS
     * @return null
     */
    public function initialize_assets() {
        if( $this->assets_initialized ){
            return;
        }
        global $wp_scripts;
        foreach( $wp_scripts->queue as $script ){
            $handle = $wp_scripts->registered[$script]->handle;
            if(  strpos( $handle, 'chosen' ) !== FALSE  ){
                wp_dequeue_script( $handle );
            }
        }
        global $wp_styles;
        foreach( $wp_styles->queue as $style ){
            $handle = $wp_styles->registered[$style]->handle;
            if(  strpos( $handle, 'chosen' ) !== FALSE  ){
                wp_dequeue_style( $handle );

            }
        }
        wp_enqueue_media();
        wp_enqueue_editor();

        wp_enqueue_style( 'accordeon-style', plugins_url( 'vendor/jquery-accordion/css/jquery.accordion.css', __FILE__ ), [], PLUGION_VERSION );
        wp_enqueue_script( 'accordeon', plugins_url( 'vendor/jquery-accordion/js/jquery.accordion.js', __FILE__ ), ['jquery'], PLUGION_VERSION );

        wp_enqueue_script( 'jquery-nice-select', plugins_url( 'vendor/jquery-nice-select/js/jquery.nice-select.js', __FILE__ ), ['jquery'], PLUGION_VERSION );
        wp_enqueue_style( 'jquery-nice-select-style', plugins_url( 'vendor/jquery-nice-select/css/nice-select.css', __FILE__ ), [], PLUGION_VERSION );

        wp_deregister_script( 'datatables' );
        wp_dequeue_script( 'datatables-admin' );

        wp_enqueue_script( 'datatables', plugins_url( 'vendor/DataTables/datatables.min.js', __FILE__ ), ['jquery'], PLUGION_VERSION );
        wp_enqueue_style( 'datatables-style', plugins_url( 'vendor/DataTables/datatables.min.css', __FILE__ ), [], PLUGION_VERSION );

        wp_enqueue_script( 'jquery-chosen', plugins_url( 'vendor/chosen/chosen.jquery.min.js', __FILE__ ), ['jquery'], PLUGION_VERSION );
        wp_enqueue_style( 'jquery-chosen-style', plugins_url( 'vendor/chosen/chosen.min.css', __FILE__ ), [], PLUGION_VERSION );

        wp_enqueue_script( 'jquery-effects-core' );
        wp_enqueue_script( 'jquery-effects-slide' );

        wp_enqueue_script( 'plugion', plugins_url( 'public/plugion.js', __FILE__ ), [ 'jquery', 'jquery-ui-core', 'jquery-effects-core' ], PLUGION_VERSION );
        wp_enqueue_style( 'plugion-style', plugins_url( 'public/plugion.css', __FILE__ ), [], PLUGION_VERSION  );

        wp_enqueue_script( 'pickadate_picker', plugins_url( 'vendor/pickadatejs/lib/compressed/picker.js', __FILE__ ), [ 'jquery', 'jquery-ui-core', 'jquery-effects-core' ], PLUGION_VERSION );
        wp_enqueue_script( 'pickadate_picker_date', plugins_url( 'vendor/pickadatejs/lib/compressed/picker.date.js', __FILE__ ), [ 'jquery', 'jquery-ui-core', 'jquery-effects-core' ], PLUGION_VERSION );
        wp_enqueue_script( 'pickadate_picker_time', plugins_url( 'vendor/pickadatejs/lib/compressed/picker.time.js', __FILE__ ), [ 'jquery', 'jquery-ui-core', 'jquery-effects-core' ], PLUGION_VERSION );

        wp_enqueue_style( 'pickadate_classic', plugins_url( 'vendor/pickadatejs/lib/compressed/themes/classic.css', __FILE__ ), [], PLUGION_VERSION  );
        wp_enqueue_style( 'pickadate_classic_date', plugins_url( 'vendor/pickadatejs/lib/compressed/themes/classic.date.css', __FILE__ ), [], PLUGION_VERSION  );
        wp_enqueue_style( 'pickadate_classic_time', plugins_url( 'vendor/pickadatejs/lib/compressed/themes/classic.time.css', __FILE__ ), [], PLUGION_VERSION  );

        $this->assets_initialized = true;
        plugion_localize_script();
    }

    /**
     * set value for the give field of given table
     * IMPORANT: filters are not called before the value is wtitten to database
     * @param string $table table slug
     * @param string $field field slug
     * @param mixed $value [description]
     */
    public function set_value( $table, $field, $id, $value ){
        global $wpdb;
        $format = Plugion()->tables->get_element_at( $table )->fields->get_element_at( $field )->field_type_to_sql_type( true );
        $field_name = Plugion()->tables->get_element_at( $table )->fields->get_element_at( $field )->get_name();
        $wpdb->query( $wpdb->prepare( "UPDATE $table SET $field_name = $format WHERE ID=%d", $value, $id ) );
    }

    /**
     * set value for the give field of given table
     * IMPORANT: filters are not called before the value is wtitten to database
     * @param string $table table slug
     * @param string $field field slug
     */
    public function get_value( $table, $field, $id ){
        global $wpdb;
        $field_name = Plugion()->tables->get_element_at( $table )->fields->get_element_at( $field )->get_name();
        $value = $wpdb->get_var( $wpdb->prepare( "SELECT $field_name from $table WHERE ID = %d", $id ) );
        return $value;

    }

}
