<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class WBK_Service_Category extends WBK_Model_Object{
    public function __construct( $id ) {
        $this->table_name = get_option('wbk_db_prefix', '' ) . 'wbk_service_categories';
		parent::__construct( $id );

	}

}
