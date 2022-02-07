<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class WBK_Model_Updater
{
    static function is_update_required( $version )
    {
        $update_status = get_option( 'wbk_update_status', '' );
        
        if ( $update_status == '' ) {
            return true;
        } else {
            if ( !isset( $update_status[$version] ) ) {
                return true;
            }
        }
        
        return false;
    }
    
    static function set_update_as_complete( $version )
    {
        $update_status = get_option( 'wbk_update_status', '' );
        
        if ( $update_status == '' ) {
            $update_status = array();
            $update_status[$version] = true;
        } else {
            $update_status[$version] = true;
        }
        
        update_option( 'wbk_update_status', $update_status );
    }
    
    public static function run_update()
    {
        self::update_4_0_0();
        self::update_4_0_49();
        self::update_4_0_62();
        self::update_4_0_73();
        self::update_4_1_3();
        self::update_4_1_4();
    }
    
    public static function run_previous_update()
    {
        $update_status = get_option( 'wbk_update_status', '' );
        $run_previous_update = false;
        
        if ( $update_status == '' ) {
            $run_previous_update = true;
            $update_status = array();
        } else {
            if ( !isset( $update_status['run_prev'] ) ) {
                $run_previous_update = true;
            }
        }
        
        
        if ( $run_previous_update ) {
            self::update_1_2_0();
            self::update_1_3_0();
            self::update_3_0_0();
            self::update_3_0_3();
            self::update_3_0_15();
            self::update_3_1_0();
            self::update_3_1_6();
            self::update_3_1_21();
            self::update_3_1_27();
            self::update_3_1_31();
            self::update_3_2_0();
            self::update_3_2_2();
            self::update_3_2_3();
            self::update_3_2_16();
            self::update_3_2_18();
            self::update_3_2_21();
            self::update_3_3_7();
            self::update_3_3_7_1();
            self::update_3_3_9();
            self::update_3_3_12();
            self::update_3_3_14();
            self::update_3_3_14_1();
            self::update_3_3_14_2();
            self::update_3_3_18();
            self::update_3_3_31();
            self::update_3_3_37();
            self::update_3_3_41();
            self::update_3_3_42();
            self::update_3_3_61();
            self::update_3_3_68();
            self::update_3_3_73();
            self::update_3_3_102();
            self::update_3_4_8();
            self::update_3_4_21();
            self::update_3_4_25();
            $update_status['run_prev'] = true;
            update_option( 'wbk_update_status', $update_status );
        }
    
    }
    
    static function update_1_2_0()
    {
        global  $wpdb ;
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_services';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'form' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_services ADD `form` int unsigned NOT NULL default 0" );
        }
    }
    
    // add fields used since 1.3.0
    static function update_1_3_0()
    {
        global  $wpdb ;
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_services';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'quantity' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_services ADD `quantity` int unsigned NOT NULL default 1" );
        }
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'quantity' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments ADD `quantity` int unsigned NOT NULL default 1" );
        }
    }
    
    // add fields used since 3.0.0
    static function update_3_0_0()
    {
        global  $wpdb ;
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_services';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'price' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_services ADD `price` FLOAT NOT NULL DEFAULT '0'" );
        }
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'payment_methods' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_services ADD `payment_methods` varchar(255) NOT NULL DEFAULT ''" );
        }
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'status' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments ADD `status`  varchar(255) NOT NULL DEFAULT 'pending'" );
        }
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'payment_id' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments ADD `payment_id` varchar(255) NOT NULL DEFAULT ''" );
        }
    }
    
    // add tables and fields used since 3.0.3
    static function update_3_0_3()
    {
        global  $wpdb ;
        // email templates table
        $wpdb->query( "CREATE TABLE IF NOT EXISTS " . get_option( 'wbk_db_prefix', '' ) . "wbk_email_templates (\n                id int unsigned NOT NULL auto_increment PRIMARY KEY,\n                name varchar(128) default '',\n                template varchar(2000) default '',\n                UNIQUE KEY id (id)\n            )\n            DEFAULT CHARACTER SET = utf8\n            COLLATE = utf8_general_ci" );
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_services';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'notification_template' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_services ADD `notification_template` int unsigned NOT NULL default 0" );
        }
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'reminder_template' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_services ADD `reminder_template` int unsigned NOT NULL default 0" );
        }
    }
    
    // add fields used since 3.0.15
    static function update_3_0_15()
    {
        global  $wpdb ;
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_services';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'prepare_time' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_services ADD `prepare_time` int unsigned NOT NULL default 0" );
        }
        self::create_ht_file();
    }
    
    // add tables and fields used since 3.1.0
    static function update_3_1_0()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_1_0_upd', '' ) == 'done' ) {
            return;
        }
        // create service category table
        $wpdb->query( "CREATE TABLE IF NOT EXISTS " . get_option( 'wbk_db_prefix', '' ) . "wbk_service_categories(\n                id int unsigned NOT NULL auto_increment PRIMARY KEY,\n                name varchar(128) default '',\n                category_list varchar(512) default '',\n                UNIQUE KEY id (id)\n            )\n            DEFAULT CHARACTER SET = utf8\n            COLLATE = utf8_general_ci" );
        // add token and created_on fields into wbk_appointments db_prefix
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'token' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments ADD `token` varchar(255) NOT NULL DEFAULT ''" );
        }
        // add payment cancel tokend
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'payment_cancel_token' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments ADD `payment_cancel_token` varchar(255) NOT NULL DEFAULT''" );
        }
        // add transaction started
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'expiration_time' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments ADD `expiration_time` int unsigned NOT NULL default 0" );
        }
        // extends description field
        $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments CHANGE `description` `description` VARCHAR(1024) NOT NULL DEFAULT ''" );
        // add triggers
        $wpdb->update(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            array(
            'status' => 'approved',
        ),
            array(
            'status' => 'pending',
        ),
            array( '%s' ),
            array( '%s' )
        );
        $wpdb->update(
            get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
            array(
            'status' => 'paid_approved',
        ),
            array(
            'status' => 'paid',
        ),
            array( '%s' ),
            array( '%s' )
        );
        add_option( 'wbk_3_1_0_upd', 'done' );
        update_option( 'wbk_3_1_0_upd', 'done' );
    }
    
    // add fields used since 3.1.21
    static function update_3_1_21()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_1_21_upd', '' ) == 'done' ) {
            return;
        }
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_services';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'date_range' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_services ADD `date_range` varchar(128) NOT NULL DEFAULT ''" );
        }
        add_option( 'wbk_3_1_21_upd', 'done' );
        update_option( 'wbk_3_1_21_upd', 'done' );
    }
    
    // update db structure according to 3.1.6
    static function update_3_1_6()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_1_6_upd', '' ) == 'done' ) {
            return;
        }
        // extends email templates field
        $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_email_templates CHANGE `template` `template` VARCHAR(20000) NOT NULL DEFAULT ''" );
        add_option( 'wbk_3_1_6_upd', 'done' );
        update_option( 'wbk_3_1_6_upd', 'done' );
    }
    
    static function update_3_1_27()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_1_27_upd', '' ) == 'done' ) {
            return;
        }
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_services';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'gg_calendars' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_services ADD `gg_calendars` varchar(512) NOT NULL DEFAULT ''" );
        }
        add_option( 'wbk_3_1_27_upd', 'done' );
        update_option( 'wbk_3_1_27_upd', 'done' );
    }
    
    static function update_3_1_31()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_1_31_upd', '' ) == 'done' ) {
            return;
        }
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_services';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'invoice_template' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_services ADD `invoice_template` int unsigned NOT NULL default 0" );
        }
        add_option( 'wbk_3_1_31_upd', 'done' );
        update_option( 'wbk_3_1_31_upd', 'done' );
    }
    
    //update db structure to version 3.2.0
    static function update_3_2_0()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_2_0_upd', '' ) == 'done' ) {
            return;
        }
        // google calendar
        $wpdb->query( "CREATE TABLE IF NOT EXISTS " . get_option( 'wbk_db_prefix', '' ) . "wbk_gg_calendars (\n                id int unsigned NOT NULL auto_increment PRIMARY KEY,\n                name varchar(128) default '',\n                access_token varchar(512) default '',\n                calendar_id  varchar(512) default '',\n                user_id int unsigned NOT NULL,\n                UNIQUE KEY id (id)\n            )\n            DEFAULT CHARACTER SET = utf8\n            COLLATE = utf8_general_ci" );
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_services';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'gg_calendars' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_services ADD `gg_calendars` varchar(512) NOT NULL DEFAULT ''" );
        }
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'gg_event_id' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments ADD `gg_event_id` varchar(512) NOT NULL DEFAULT ''" );
        }
        add_option( 'wbk_3_2_0_upd', 'done' );
        update_option( 'wbk_3_2_0_upd', 'done' );
    }
    
    //update db structure to version 3.2.2
    static function update_3_2_2()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_2_2_upd', '' ) == 'done' ) {
            return;
        }
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_locked_time_slots';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'connected_id' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_locked_time_slots ADD `connected_id` int unsigned NOT NULL default 0" );
        }
        add_option( 'wbk_3_2_2_upd', 'done' );
        update_option( 'wbk_3_2_2_upd', 'done' );
    }
    
    //update db structure to version 3.2.3
    static function update_3_2_3()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_2_3_upd', '' ) == 'done' ) {
            return;
        }
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'admin_token' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments ADD `admin_token` varchar(255) NOT NULL DEFAULT ''" );
        }
        add_option( 'wbk_3_2_3_upd', 'done' );
        update_option( 'wbk_3_2_3_upd', 'done' );
    }
    
    // update db structure to version 3.2.16
    static function update_3_2_16()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_2_16_upd', '' ) == 'done' ) {
            return;
        }
        $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_services CHANGE `description` `description` varchar(1024) default ''" );
        add_option( 'wbk_install_cn', time() );
        add_option( 'wbk_3_2_16_upd', 'done' );
        update_option( 'wbk_3_2_16_upd', 'done' );
    }
    
    // update db structure to version 3.2.18
    static function update_3_2_18()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_2_18_upd', '' ) == 'done' ) {
            return;
        }
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'time_offset' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments ADD  time_offset int  NOT NULL default 0" );
        }
        add_option( 'wbk_3_2_18_upd', 'done' );
        update_option( 'wbk_3_2_18_upd', 'done' );
    }
    
    // update db structure to version 3.2.21
    static function update_3_2_21()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_2_21_upd', '' ) == 'done' ) {
            return;
        }
        $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments CHANGE `attachment` `attachment` VARCHAR(1024) NOT NULL DEFAULT ''" );
        add_option( 'wbk_3_2_21_upd', 'done' );
        update_option( 'wbk_3_2_21_upd', 'done' );
    }
    
    // update db structure to version 3.3.7
    static function update_3_3_7()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_3_7_upd', '' ) == 'done' ) {
            return;
        }
        $wpdb->query( "CREATE TABLE IF NOT EXISTS " . get_option( 'wbk_db_prefix', '' ) . "wbk_coupons (\n                id int unsigned NOT NULL auto_increment PRIMARY KEY,\n                name varchar(128) default '',\n                services varchar(512) default '',\n                date_range varchar(256) default '',\n                used int unsigned NOT NULL default 0,\n                amount_fixed int unsigned NOT NULL default 0,\n                amount_percentage int unsigned NOT NULL default 0,\n                maximum int unsigned default NULL,\n                UNIQUE KEY id (id)\n            )\n            DEFAULT CHARACTER SET = utf8\n            COLLATE = utf8_general_ci" );
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'coupon' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments ADD coupon int NOT NULL default 0" );
        }
        add_option( 'wbk_3_3_7_upd', 'done' );
        update_option( 'wbk_3_3_7_upd', 'done' );
    }
    
    // update db structure to version 3.3.7.1
    static function update_3_3_7_1()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_3_7_1_upd', '' ) == 'done' ) {
            return;
        }
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_services';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'multi_mode_limit' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_services ADD multi_mode_limit varchar(128) NOT NULL default ''" );
        }
        add_option( 'wbk_3_3_7_1_upd', 'done' );
        update_option( 'wbk_3_3_7_1_upd', 'done' );
    }
    
    // update db structure to version 3.3.9
    static function update_3_3_9()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_3_9_upd', '' ) == 'done' ) {
            return;
        }
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_gg_calendars';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'mode' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_gg_calendars ADD mode varchar(128) default 'One-way'" );
        }
        add_option( 'wbk_3_3_9_upd', 'done' );
        update_option( 'wbk_3_3_9_upd', 'done' );
    }
    
    // update db structure to version 3.2.12+
    static function update_3_3_12()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_3_12_upd', '' ) == 'done' ) {
            return;
        }
        $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments CHANGE `extra` `extra` VARCHAR(2048) NOT NULL DEFAULT ''" );
        add_option( 'wbk_3_3_12_upd', 'done' );
        update_option( 'wbk_3_3_12_upd', 'done' );
    }
    
    // update db structure to version 3.2.14
    static function update_3_3_14()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_3_14_upd', '' ) == 'done' ) {
            return;
        }
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'payment_method' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments ADD payment_method varchar(255) default ''" );
        }
        add_option( 'wbk_3_3_14_upd', 'done' );
        update_option( 'wbk_3_3_14_upd', 'done' );
    }
    
    // update db structure to version 3.2.14(1)
    static function update_3_3_14_1()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_3_14_1_upd', '' ) == 'done' ) {
            return;
        }
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_gg_calendars';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'cache_content' ) {
                $found = true;
            }
        }
        
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_gg_calendars ADD cache_content longtext NOT NULL default ''" );
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_gg_calendars ADD cache_time int unsigned NOT NULL default 0" );
        }
        
        add_option( 'wbk_3_3_14_1_upd', 'done' );
        update_option( 'wbk_3_3_14_1_upd', 'done' );
    }
    
    static function update_3_4_8()
    {
        global  $wpdb ;
        if ( get_option( 'update_3_4_8', '' ) == 'done' ) {
            return;
        }
        //	$arr_service = self::
        $service_ids = WBK_Model_Utils::get_services();
        foreach ( $service_ids as $service_id ) {
            $service = WBK_Db_Utils::initServiceById( $service_id );
            if ( $service == FALSE ) {
                continue;
            }
            $prepare_time = $service->getPrepareTime();
            
            if ( $prepare_time != 0 ) {
                $service->setPrepareTime( $prepare_time * 60 );
                $service->update();
            }
        
        }
        add_option( 'update_3_4_8', 'done' );
        update_option( 'update_3_4_8', 'done' );
    }
    
    // update db structure to version 3.3.14.2
    static function update_3_3_14_2()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_3_14_2_upd', '' ) == 'done' ) {
            return;
        }
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_services';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'multi_mode_low_limit' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_services ADD multi_mode_low_limit varchar(128) NOT NULL default ''" );
        }
        add_option( 'wbk_3_3_14_2_upd', 'done' );
        update_option( 'wbk_3_3_14_2_upd', 'done' );
    }
    
    // update db structure to version 3.3.18
    static function update_3_3_18()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_3_18_upd', '' ) == 'done' ) {
            return;
        }
        $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments CHANGE `extra` `extra` LONGTEXT NOT NULL DEFAULT ''" );
        add_option( 'wbk_3_3_18_upd', 'done' );
        update_option( 'wbk_3_3_18_upd', 'done' );
    }
    
    // update db structure to version 3.4.0
    static function update_3_3_31()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_3_31_upd', '' ) == 'done' ) {
            return;
        }
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'created_on' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments ADD created_on int unsigned NOT NULL default 0" );
        }
        add_option( 'wbk_3_3_31_upd', 'done' );
        update_option( 'wbk_3_3_31_upd', 'done' );
    }
    
    // update db structure to version 3.3.37
    static function update_3_3_37()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_3_37_upd', '' ) == 'done' ) {
            return;
        }
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_services';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'priority' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_services ADD priority int NOT NULL default 0" );
        }
        add_option( 'wbk_3_3_37_upd', 'done' );
        update_option( 'wbk_3_3_37_upd', 'done' );
    }
    
    // update db structure to version 3.3.41
    static function update_3_3_41()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_3_41_upd', '' ) == 'done' ) {
            return;
        }
        $app_ids = $wpdb->get_col( 'SELECT id from ' . get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments' );
        foreach ( $app_ids as $app_id ) {
            $extra_data = $wpdb->get_var( $wpdb->prepare( " SELECT extra FROM " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments WHERE id = %d ", $app_id ) );
            if ( $extra_data == '' ) {
                continue;
            }
            $extra_data_ids = explode( '###', $extra_data );
            $extras = array();
            foreach ( $extra_data_ids as $extra_id ) {
                if ( trim( $extra_id ) == '' ) {
                    continue;
                }
                $value_pair = explode( ':', $extra_id );
                if ( count( $value_pair ) != 2 ) {
                    continue;
                }
                $field_id = trim( $value_pair[0] );
                $field_id_label = $field_id;
                $field_id_label = explode( ']', $field_id_label );
                if ( count( $field_id_label ) != 2 ) {
                    continue;
                }
                $field_id_label = $field_id_label[1];
                $matches = array();
                preg_match( "/\\[[^\\]]*\\]/", $field_id, $matches );
                $field_id = trim( $matches[0], '[]' );
                $custom_field_value = $value_pair[1];
                $custom_field_value = str_replace( '{colon}', ':', $custom_field_value );
                $extra = array();
                $extra[] = trim( $field_id );
                $extra[] = trim( $field_id_label );
                $extra[] = trim( $custom_field_value );
                $extras[] = $extra;
            }
            $extras = json_encode( $extras );
            $result = $wpdb->update(
                get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments',
                array(
                'extra' => $extras,
            ),
                array(
                'id' => $app_id,
            ),
                array( '%s' ),
                array( '%d' )
            );
        }
        $found = false;
        add_option( 'wbk_3_3_41_upd', 'done' );
        update_option( 'wbk_3_3_41_upd', 'done' );
    }
    
    // update db structure to version 3.3.42
    static function update_3_3_42()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_3_42_upd', '' ) == 'done' ) {
            return;
        }
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'lang' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments ADD lang varchar(255) default ''" );
        }
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'moment_price' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments ADD moment_price varchar(255) default ''" );
        }
        add_option( 'wbk_3_3_42_upd', 'done' );
        update_option( 'wbk_3_3_42_upd', 'done' );
    }
    
    // update db structure to version 3.3.42
    static function update_3_3_61()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_3_61_upd', '' ) == 'done' ) {
            return;
        }
        $wpdb->query( "CREATE TABLE IF NOT EXISTS " . get_option( 'wbk_db_prefix', '' ) . "wbk_cancelled_appointments (\n                    id int unsigned NOT NULL auto_increment PRIMARY KEY,\n                    id_cancelled int unsigned NOT NULL default 0,\n                    cancelled_by varchar(128) default '',\n                    name varchar(128) default '',\n                    email varchar(128) default '',\n                    phone varchar(128) default '',\n                    description varchar(1024) default '',\n                    extra varchar(2048) default '',\n                    attachment varchar(1024) default '',\n                    service_id int unsigned NOT NULL,\n                    time int unsigned NOT NULL,\n                    day int unsigned NOT NULL,\n                    duration int unsigned NOT NULL,\n                    created_on int unsigned NOT NULL default 0,\n                    quantity int unsigned NOT NULL default 1,\n                    status varchar(255) default 'pending',\n                    payment_id varchar(255) default '',\n                    token varchar(255) NOT NULL DEFAULT '',\n                    payment_cancel_token varchar(255) NOT NULL DEFAULT '',\n                    admin_token varchar(255) NOT NULL DEFAULT '',\n                    expiration_time int unsigned NOT NULL default 0,\n                    time_offset int NOT NULL default 0,\n                    gg_event_id varchar(255) default '',\n                    coupon int NOT NULL default 0,\n                    payment_method varchar(255) default '',\n\n                    lang varchar(255) default '',\n                    moment_price varchar(255) default '',\n                    UNIQUE KEY id (id)\n                )\n                    DEFAULT CHARACTER SET = utf8\n                    COLLATE = utf8_general_ci" );
        add_option( 'wbk_3_3_61_upd', 'done' );
        update_option( 'wbk_3_3_61_upd', 'done' );
    }
    
    static function update_3_3_68()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_3_68_upd', '' ) == 'done' ) {
            return;
        }
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_services';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'min_quantity' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_services ADD min_quantity int unsigned NOT NULL default 1" );
        }
        add_option( 'wbk_3_3_68_upd', 'done' );
        update_option( 'wbk_3_3_68_upd', 'done' );
    }
    
    static function update_3_3_73()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_3_73_upd', '' ) == 'done' ) {
            return;
        }
        //	$arr_service = self::
        $service_ids = WBK_Model_Utils::get_services();
        foreach ( $service_ids as $service_id ) {
            $service = WBK_Db_Utils::initServiceById( $service_id );
            if ( $service == FALSE ) {
                continue;
            }
            $prepare_time = $service->getPrepareTime();
            
            if ( $prepare_time != 0 ) {
                $service->setPrepareTime( $prepare_time * 24 );
                $service->update();
            }
        
        }
        add_option( 'wbk_3_3_73_upd', 'done' );
        update_option( 'wbk_3_3_73_upd', 'done' );
    }
    
    static function update_3_3_102()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_3_102_upd', '' ) == 'done' ) {
            return;
        }
        $value = get_option( 'wbk_email_customer_appointment_cancel_message', '' );
        add_option( 'wbk_email_customer_bycustomer_appointment_cancel_message', $value );
        update_option( 'wbk_email_customer_bycustomer_appointment_cancel_message', $value );
        add_option( 'wbk_3_3_102_upd', 'done' );
        update_option( 'wbk_3_3_102_upd', 'done' );
    }
    
    // update db structure to version 3.4.21
    static function update_3_4_21()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_4_21_upd', '' ) == 'done' ) {
            return;
        }
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'service_category' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments ADD service_category int unsigned NOT NULL default 0" );
        }
        add_option( 'wbk_3_4_21_upd', 'done' );
        update_option( 'wbk_3_4_21_upd', 'done' );
    }
    
    // update db structure to version 3.4.25
    static function update_3_4_25()
    {
        global  $wpdb ;
        if ( get_option( 'wbk_3_4_25_upd', '' ) == 'done' ) {
            return;
        }
        $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments';
        $found = false;
        foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
            if ( $column_name == 'user_ip' ) {
                $found = true;
            }
        }
        if ( !$found ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments ADD user_ip varchar(255) default ''" );
        }
        add_option( 'wbk_3_4_25_upd', 'done' );
        update_option( 'wbk_3_4_25_upd', 'done' );
    }
    
    // create export file
    static function create_ht_file()
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . 'export' . DIRECTORY_SEPARATOR . '.htaccess';
        $content = "RewriteEngine On" . "\r\n";
        $content .= "RewriteCond %{HTTP_REFERER} !^" . get_admin_url() . 'admin.php\\?page\\=wbk-appointments' . '.* [NC]' . "\r\n";
        $content .= "RewriteRule .* - [F]";
        file_put_contents( $path, $content );
    }
    
    static function update_4_0_0()
    {
        global  $wpdb ;
        
        if ( self::is_update_required( '4.0.0_service_data_types' ) ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_services CHANGE `multi_mode_limit` `multi_mode_limit` int unsigned NULL default NULL" );
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_services CHANGE `multi_mode_low_limit` `multi_mode_low_limit` int unsigned NULL default NULL" );
            $service_ids = WBK_Model_Utils::get_service_ids();
            foreach ( $service_ids as $service_id ) {
                $service = new WBK_Service( $service_id );
                $value = $business_hours = $service->get( 'multi_mode_limit' );
                if ( $value == 0 ) {
                    $wpdb->update(
                        get_option( 'wbk_db_prefix', '' ) . 'wbk_services',
                        array(
                        'multi_mode_limit' => NULL,
                    ),
                        array(
                        'id' => $service_id,
                    ),
                        array( '%s' ),
                        array( '%d' )
                    );
                }
                $value = $business_hours = $service->get( 'multi_mode_low_limit' );
                if ( $value == 0 ) {
                    $wpdb->update(
                        get_option( 'wbk_db_prefix', '' ) . 'wbk_services',
                        array(
                        'multi_mode_low_limit' => NULL,
                    ),
                        array(
                        'id' => $service_id,
                    ),
                        array( '%s' ),
                        array( '%d' )
                    );
                }
            }
            self::set_update_as_complete( '4.0.0_service_data_types' );
        }
        
        
        if ( self::is_update_required( '4.0.0_service_business_hours' ) ) {
            $service_ids = WBK_Model_Utils::get_service_ids();
            foreach ( $service_ids as $service_id ) {
                $service = new WBK_Service( $service_id );
                $business_hours = $service->get( 'business_hours' );
                if ( $business_hours == '' ) {
                    continue;
                }
                $arr_bh = explode( ';', $business_hours );
                $business_hours = new WBK_Business_Hours();
                $business_hours->setFromArray( $arr_bh );
                $result = array(
                    'dow_availability' => array(),
                );
                $day = 'monday';
                $day_number = 1;
                $interval_count = $business_hours->getIntervalCount( $day );
                if ( $business_hours->isWorkday( $day ) ) {
                    for ( $i = 1 ;  $i <= $interval_count ;  $i++ ) {
                        $interval = $business_hours->getInterval( $day, $i );
                        $start_time = $interval[0] - 2;
                        $end_time = $interval[1] - 2;
                        $result['dow_availability'][] = array(
                            'day_of_week' => $day_number,
                            'start'       => $start_time,
                            'end'         => $end_time,
                            'status'      => 'active',
                        );
                    }
                }
                $day = 'tuesday';
                $day_number = 2;
                $interval_count = $business_hours->getIntervalCount( $day );
                if ( $business_hours->isWorkday( $day ) ) {
                    for ( $i = 1 ;  $i <= $interval_count ;  $i++ ) {
                        $interval = $business_hours->getInterval( $day, $i );
                        $start_time = $interval[0] - 2;
                        $end_time = $interval[1] - 2;
                        $result['dow_availability'][] = array(
                            'day_of_week' => $day_number,
                            'start'       => $start_time,
                            'end'         => $end_time,
                            'status'      => 'active',
                        );
                    }
                }
                $day = 'wednesday';
                $day_number = 3;
                $interval_count = $business_hours->getIntervalCount( $day );
                if ( $business_hours->isWorkday( $day ) ) {
                    for ( $i = 1 ;  $i <= $interval_count ;  $i++ ) {
                        $interval = $business_hours->getInterval( $day, $i );
                        $start_time = $interval[0] - 2;
                        $end_time = $interval[1] - 2;
                        $result['dow_availability'][] = array(
                            'day_of_week' => $day_number,
                            'start'       => $start_time,
                            'end'         => $end_time,
                            'status'      => 'active',
                        );
                    }
                }
                $day = 'thursday';
                $day_number = 4;
                $interval_count = $business_hours->getIntervalCount( $day );
                if ( $business_hours->isWorkday( $day ) ) {
                    for ( $i = 1 ;  $i <= $interval_count ;  $i++ ) {
                        $interval = $business_hours->getInterval( $day, $i );
                        $start_time = $interval[0] - 2;
                        $end_time = $interval[1] - 2;
                        $result['dow_availability'][] = array(
                            'day_of_week' => $day_number,
                            'start'       => $start_time,
                            'end'         => $end_time,
                            'status'      => 'active',
                        );
                    }
                }
                $day = 'friday';
                $day_number = 5;
                $interval_count = $business_hours->getIntervalCount( $day );
                if ( $business_hours->isWorkday( $day ) ) {
                    for ( $i = 1 ;  $i <= $interval_count ;  $i++ ) {
                        $interval = $business_hours->getInterval( $day, $i );
                        $start_time = $interval[0] - 2;
                        $end_time = $interval[1] - 2;
                        $result['dow_availability'][] = array(
                            'day_of_week' => $day_number,
                            'start'       => $start_time,
                            'end'         => $end_time,
                            'status'      => 'active',
                        );
                    }
                }
                $day = 'saturday';
                $day_number = 6;
                $interval_count = $business_hours->getIntervalCount( $day );
                if ( $business_hours->isWorkday( $day ) ) {
                    for ( $i = 1 ;  $i <= $interval_count ;  $i++ ) {
                        $interval = $business_hours->getInterval( $day, $i );
                        $start_time = $interval[0] - 2;
                        $end_time = $interval[1] - 2;
                        $result['dow_availability'][] = array(
                            'day_of_week' => $day_number,
                            'start'       => $start_time,
                            'end'         => $end_time,
                            'status'      => 'active',
                        );
                    }
                }
                $day = 'sunday';
                $day_number = 7;
                $interval_count = $business_hours->getIntervalCount( $day );
                if ( $business_hours->isWorkday( $day ) ) {
                    for ( $i = 1 ;  $i <= $interval_count ;  $i++ ) {
                        $interval = $business_hours->getInterval( $day, $i );
                        $start_time = $interval[0] - 2;
                        $end_time = $interval[1] - 2;
                        $result['dow_availability'][] = array(
                            'day_of_week' => $day_number,
                            'start'       => $start_time,
                            'end'         => $end_time,
                            'status'      => 'active',
                        );
                    }
                }
                $result = json_encode( $result );
                $wpdb->update(
                    get_option( 'wbk_db_prefix', '' ) . 'wbk_services',
                    array(
                    'business_hours_v4' => $result,
                ),
                    array(
                    'id' => $service_id,
                ),
                    array( '%s' ),
                    array( '%d' )
                );
            }
            self::set_update_as_complete( '4.0.0_service_business_hours' );
        }
        
        
        if ( self::is_update_required( '4.0.0_service_array_objects' ) ) {
            $service_ids = WBK_Model_Utils::get_service_ids();
            foreach ( $service_ids as $service_id ) {
                $service = new WBK_Service( $service_id );
                $gg_calendars = $service->get( 'gg_calendars' );
                if ( json_decode( $gg_calendars ) === NULL ) {
                    
                    if ( $gg_calendars != '' ) {
                        $gg_calendars = json_encode( explode( ';', $gg_calendars ) );
                        $wpdb->update(
                            get_option( 'wbk_db_prefix', '' ) . 'wbk_services',
                            array(
                            'gg_calendars' => $gg_calendars,
                        ),
                            array(
                            'id' => $service_id,
                        ),
                            array( '%s' ),
                            array( '%d' )
                        );
                    }
                
                }
                $payment_methods = $service->get( 'payment_methods' );
                if ( json_decode( $payment_methods ) === NULL ) {
                    
                    if ( $payment_methods != '' ) {
                        $payment_methods = json_encode( explode( ';', $payment_methods ) );
                        $wpdb->update(
                            get_option( 'wbk_db_prefix', '' ) . 'wbk_services',
                            array(
                            'payment_methods' => $payment_methods,
                        ),
                            array(
                            'id' => $service_id,
                        ),
                            array( '%s' ),
                            array( '%d' )
                        );
                    }
                
                }
                $users = $service->get( 'users' );
                if ( json_decode( $users ) === NULL ) {
                    
                    if ( $users != '' ) {
                        $users = json_encode( explode( ';', $users ) );
                        $wpdb->update(
                            get_option( 'wbk_db_prefix', '' ) . 'wbk_services',
                            array(
                            'users' => $users,
                        ),
                            array(
                            'id' => $service_id,
                        ),
                            array( '%s' ),
                            array( '%d' )
                        );
                    }
                
                }
            }
            self::set_update_as_complete( '4.0.0_service_array_objects' );
        }
        
        
        if ( self::is_update_required( '4.0.0.coupons' ) ) {
            $coupons = WBK_Model_Utils::get_coupons();
            foreach ( $coupons as $key => $value ) {
                $coupon = new WBK_Coupon( $key );
                if ( $coupon->get( 'services' ) != '' ) {
                    
                    if ( is_null( json_decode( $coupon->get( 'services' ) ) ) ) {
                        $services = json_encode( explode( ',', $coupon->get( 'services' ) ) );
                        $wpdb->update(
                            get_option( 'wbk_db_prefix', '' ) . 'wbk_coupons',
                            array(
                            'services' => $services,
                        ),
                            array(
                            'id' => $key,
                        ),
                            array( '%s' ),
                            array( '%d' )
                        );
                    }
                
                }
                
                if ( $coupon->get( 'date_range' ) != '' ) {
                    $dates = explode( ' - ', $coupon->get( 'date_range' ) );
                    
                    if ( is_array( $dates ) && count( $dates ) == 2 ) {
                        $start = strtotime( $dates[0] );
                        $end = strtotime( $dates[1] );
                        $start = date( 'm/d/Y', $start );
                        $end = date( 'm/d/Y', $end );
                        $result = $start . ' - ' . $end;
                        $wpdb->update(
                            get_option( 'wbk_db_prefix', '' ) . 'wbk_coupons',
                            array(
                            'date_range' => $result,
                        ),
                            array(
                            'id' => $key,
                        ),
                            array( '%s' ),
                            array( '%d' )
                        );
                    }
                
                }
            
            }
            self::set_update_as_complete( '4.0.0.coupons' );
        }
        
        
        if ( self::is_update_required( '4.0.0.service_categories' ) ) {
            $category_ids = WBK_Model_Utils::get_service_category_ids();
            foreach ( $category_ids as $category_id ) {
                $service_category = new WBK_Service_Category( $category_id );
                
                if ( is_null( json_decode( $service_category->get( 'category_list' ) ) ) ) {
                    $list = json_encode( explode( ',', $service_category->get( 'category_list' ) ) );
                    $wpdb->update(
                        get_option( 'wbk_db_prefix', '' ) . 'wbk_service_categories',
                        array(
                        'category_list' => $list,
                    ),
                        array(
                        'id' => $category_id,
                    ),
                        array( '%s' ),
                        array( '%d' )
                    );
                }
            
            }
            self::set_update_as_complete( '4.0.0.service_categories' );
        }
    
    }
    
    static function update_4_0_49()
    {
        global  $wpdb ;
        
        if ( self::is_update_required( '4.0.49' ) ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_coupons CHANGE `amount_fixed` `amount_fixed` float unsigned NULL default NULL" );
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_coupons CHANGE `amount_percentage` `amount_percentage` float unsigned NULL default NULL" );
            self::set_update_as_complete( '4.0.49' );
        }
    
    }
    
    static function update_4_0_62()
    {
        global  $wpdb ;
        
        if ( self::is_update_required( 'update_4_0_62' ) ) {
            $table_name = get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments';
            $found = false;
            foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
                if ( $column_name == 'end' ) {
                    $found = true;
                }
            }
            if ( !$found ) {
                $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_appointments ADD `end` int unsigned DEFAULT null" );
            }
            $ids = $wpdb->get_col( 'SELECT id from ' . get_option( 'wbk_db_prefix', '' ) . 'wbk_appointments' );
            foreach ( $ids as $id ) {
                WBK_Model_Utils::set_end_for_booking( $id );
            }
            self::set_update_as_complete( 'update_4_0_62' );
        }
    
    }
    
    static function update_4_0_73()
    {
        global  $wpdb ;
        
        if ( self::is_update_required( 'update_4_0_73' ) ) {
            $wpdb->query( "ALTER TABLE " . get_option( 'wbk_db_prefix', '' ) . "wbk_services CHANGE `description` `description` MEDIUMTEXT NOT NULL" );
            self::set_update_as_complete( 'update_4_0_73' );
        }
    
    }
    
    static function update_table_names()
    {
        global  $wpdb ;
        
        if ( self::is_update_required( 'update_4_1_0' ) || current_user_can( 'manage_options' ) && isset( $_GET['wbkforceupdate410'] ) ) {
            $prefix = $wpdb->prefix;
            $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( 'wbk_appointments' ) );
            if ( $wpdb->get_var( $query ) == 'wbk_appointments' ) {
                $wpdb->query( "RENAME TABLE `wbk_appointments` TO " . $prefix . "wbk_appointments" );
            }
            $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( 'wbk_cancelled_appointments' ) );
            if ( $wpdb->get_var( $query ) == 'wbk_cancelled_appointments' ) {
                $wpdb->query( "RENAME TABLE `wbk_cancelled_appointments` TO " . $prefix . "wbk_cancelled_appointments" );
            }
            $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( 'wbk_coupons' ) );
            if ( $wpdb->get_var( $query ) == 'wbk_coupons' ) {
                $wpdb->query( "RENAME TABLE `wbk_coupons` TO " . $prefix . "wbk_coupons" );
            }
            $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( 'wbk_days_on_off' ) );
            if ( $wpdb->get_var( $query ) == 'wbk_days_on_off' ) {
                $wpdb->query( "RENAME TABLE `wbk_days_on_off` TO " . $prefix . "wbk_days_on_off" );
            }
            $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( 'wbk_email_templates' ) );
            if ( $wpdb->get_var( $query ) == 'wbk_email_templates' ) {
                $wpdb->query( "RENAME TABLE `wbk_email_templates` TO " . $prefix . "wbk_email_templates" );
            }
            $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( 'wbk_gg_calendars' ) );
            if ( $wpdb->get_var( $query ) == 'wbk_gg_calendars' ) {
                $wpdb->query( "RENAME TABLE `wbk_gg_calendars` TO " . $prefix . "wbk_gg_calendars" );
            }
            $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( 'wbk_locked_time_slots' ) );
            if ( $wpdb->get_var( $query ) == 'wbk_locked_time_slots' ) {
                $wpdb->query( "RENAME TABLE `wbk_locked_time_slots` TO " . $prefix . "wbk_locked_time_slots" );
            }
            $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( 'wbk_pricing_rules' ) );
            if ( $wpdb->get_var( $query ) == 'wbk_pricing_rules' ) {
                $wpdb->query( "RENAME TABLE `wbk_pricing_rules` TO " . $prefix . "wbk_pricing_rules" );
            }
            $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( 'wbk_services' ) );
            if ( $wpdb->get_var( $query ) == 'wbk_services' ) {
                $wpdb->query( "RENAME TABLE `wbk_services` TO " . $prefix . "wbk_services" );
            }
            $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( 'wbk_service_categories' ) );
            if ( $wpdb->get_var( $query ) == 'wbk_service_categories' ) {
                $wpdb->query( "RENAME TABLE `wbk_service_categories` TO " . $prefix . "wbk_service_categories" );
            }
            update_option( 'wbk_db_prefix', $prefix );
            self::set_update_as_complete( 'update_4_1_0' );
        }
    
    }
    
    static function update_4_1_3()
    {
        global  $wpdb ;
    }
    
    static function update_4_1_4()
    {
        
        if ( self::is_update_required( 'update_4_1_4' ) ) {
            $value = get_option( 'wbk_payment_item_name', '' );
            $value = str_replace( '#service ', '#service_name ', $value );
            update_option( 'wbk_payment_item_name', $value );
            self::set_update_as_complete( 'update_4_1_4' );
        }
    
    }

}