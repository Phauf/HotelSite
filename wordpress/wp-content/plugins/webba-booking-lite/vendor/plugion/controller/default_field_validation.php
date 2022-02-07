<?php
if ( !defined( 'ABSPATH' ) ) exit;
/*
 * This file is part of Plugion framework.
 * (c) plugion.com <hello@plugion.org>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

// text field
add_filter( 'plugion_property_field_validation_text', 'plugion_property_field_text_validator', 10, 4 );
function plugion_property_field_text_validator( $input, $value, $slug, $field ) {
    if( $field->get_required() && $value == '' ){
        return[ false, sprintf( plugion_translate_string( '%s is required' ), $field->get_title() ) ];
    }
    $value = trim( sanitize_text_field( $value ) );
    $ed = $field->get_extra_data();
    if( isset( $ed['type'] ) ){
        switch ( $ed['type']) {
            case 'positive_integer':
                if( !Plugion\Validator::check_integer( $value, 1, 2147483647 ) ){
                    return[ false, sprintf( plugion_translate_string( '%s must be a positive integer' ), $field->get_title() ) ];
                }
            break;
            case 'none_negative_integer':
                if( $value == '' && !$field->get_required() ){
                    return [true, null];
                }
                if( !Plugion\Validator::check_integer( $value, 0, 2147483647 ) ){
                    return[ false, sprintf( plugion_translate_string( '%s must be a positive integer or zero' ), $field->get_title() ) ];
                }
            break;
            case 'integer':
                if(  !Plugion\Validator::check_integer( $value, -2147483647, 2147483647 ) ){
                    return[ false, sprintf( plugion_translate_string( '%s must be an integer' ), $field->get_title() ) ];
                }
            break;
            case 'none_negative_float':
                if( !Plugion\Validator::check_float( $value, 0, 21474836470 ) ){
                    return[ false, sprintf( plugion_translate_string( '%s must be a positive number or zero' ), $field->get_title() ) ];
                }
            break;
            default:
                if ( strlen( $value ) > 255 ) {
                    return[ false, sprintf( plugion_translate_string( '%s must be a maximum of 256 characters' ), $field->get_title() ) ];
                }
            break;
        }
    } else {
        if ( strlen( $value ) > 255 ) {
            return[ false, sprintf( plugion_translate_string( '%s must be a maximum of 256 characters' ), $field->get_title() ) ];
        }
    }

    return[ true, $value ];
}

// redio field
add_filter( 'plugion_property_field_validation_radio', 'plugion_property_field_radio_validator', 10, 4 );
function plugion_property_field_radio_validator( $input, $value, $slug, $field ) {
    $value = trim( sanitize_text_field( $value ) );
    $valid = false;
    foreach ( $field->get_extra_data() as $key => $option_value ) {
        if ( $option_value == $key ) {
            $valid = true;
        }
    }
    if ( !$valid ) {
        return[ false, sprintf( plugion_translate_string( 'Value of %s is not acceptable' ), $field->get_title() ) ];
    }

    return[ true, $value ];
}

// checkbox field
add_filter( 'plugion_property_field_validation_checkbox', 'plugion_property_field_checkbox_validator', 10, 4 );
function plugion_property_field_checkbox_validator( $input, $value, $slug, $field ) {
    $value = trim( sanitize_text_field( $value ) );
    $valid = false;
    $default_value = $field->get_extra_data();
    $keys = array_keys( $default_value  );
    if ( $value ===  $keys[0] || '' === $value ) {
        return[ true, $value ];
    }

    return[ false, sprintf( plugion_translate_string( 'Value of %s is not acceptable' ), $field->get_title() ) ];
}

// select field
add_filter( 'plugion_property_field_validation_select', 'plugion_property_field_select_validator', 10, 4 );
function plugion_property_field_select_validator( $input, $value, $slug, $field ) {
    $ed = $field->get_extra_data();
    if( isset( $ed['multiple'] ) ){
        if( $ed['multiple'] == true ){
            $multiple = true;
        } else {
            $multiple = false;
        }
    } else {
        $multiple = false;
    }
    if( !$multiple ){
        if( $value == 'plugion_null' && !$field->get_required() ){
            return[ true, $value ];
        }
    } else {
        if( is_null( $value ) && !$field->get_required() ){
            return[ true, $value ];
        }
    }
    $valid = true;
    if ( isset( $field->get_extra_data()['items'] ) ) {
        $items =  $field->get_extra_data()['items'];
    } elseif ( isset( $field->get_extra_data()['source'] )) {
        $function = $field->get_extra_data()['source'];
        $items = $function();
    }
    if( $multiple ){
        if( $value != '' ){
            foreach( $value as $item ){
                if( !array_key_exists( $item, $items ) ){
                    $valid = false;
                }
            }
        }
    } else {
        $valid = array_key_exists( $value, $items );
        if( $value == $field->get_default_value() ){
            $valid = true;
        }
    }
    if ( $valid ) {
        if( $multiple ){
            if( $value != '' ){
                $value = json_encode( $value );
            }
        }
        return[ true, $value ];
    }
    return[ false, sprintf( plugion_translate_string( 'Value of %s is not acceptable' ), $field->get_title() ) ];
}
// datetime field
add_filter( 'plugion_property_field_validation_datetime', 'plugion_property_field_datetime_validator', 10, 4 );
function plugion_property_field_datetime_validator( $input, $value, $slug, $field ) {
    if( DateTime::createFromFormat( 'Y-m-d H:i:s',  $value ) == false ){
        return[ false, sprintf( plugion_translate_string( 'Value of %s is not acceptable' ), $field->get_title() ) ];
    }
    return[ true, $value ];
}
// date field
add_filter( 'plugion_property_field_validation_date', 'plugion_property_field_date_validator', 10, 4 );
function plugion_property_field_date_validator( $input, $value, $slug, $field ) {
    if( DateTime::createFromFormat( 'Y-m-d',  $value ) == false ){
        return[ false, sprintf( plugion_translate_string( 'Value of %s is not acceptable' ), $field->get_title() ) ];
    }
    return[ true, $value ];
}

// textarea field
add_filter( 'plugion_property_field_validation_textarea', 'plugion_property_field_textarea_validator', 10, 4 );
function plugion_property_field_textarea_validator( $input, $value, $slug, $field ) {
    if( $field->get_required() && $value == '' ){
        return[ false, sprintf( plugion_translate_string( '%s is required' ), $field->get_title() ) ];
    }
    $value = trim( sanitize_text_field( $value ) );
    if ( strlen( $value ) > 65535 ) {
        return[ false, sprintf( plugion_translate_string( '%s must be a maximum of 65535 characters' ), $field->get_title() ) ];
    }
    return[ true, $value ];
}
// editor field
add_filter( 'plugion_property_field_validation_editor', 'plugion_property_field_editor_validator', 10, 4 );
function plugion_property_field_editor_validator( $input, $value, $slug, $field ) {
    $value =  trim( $value );
    if( $field->get_required() && $value == '' ){
        return[ false, sprintf( plugion_translate_string( '%s is required' ), $field->get_title() ) ];
    }
    if ( strlen( $value ) > 65535 ) {
        return[ false, sprintf( plugion_translate_string( '%s must be a maximum of 65535 characters' ), $field->get_title() ) ];
    }
    return[ true, $value ];
}
// date_range field
add_filter( 'plugion_property_field_validation_date_range', 'plugion_property_field_date_range_validator', 10, 4 );
function plugion_property_field_date_range_validator( $input, $value, $slug, $field ) {
    if( $field->get_required() && $value == '' ){
        $parts = explode( ' - ', $value );
        if( DateTime::createFromFormat( 'm/d/Y',  $parts[0] ) == false || DateTime::createFromFormat( 'm/d/Y',  $parts[0] ) == false ){
            return[ false, sprintf( plugion_translate_string( 'Value of %s is not acceptable' ), $field->get_title() ) ];
        }
    }
    return[ true, $value ];
}


?>
