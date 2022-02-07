<?php
if ( !defined( 'ABSPATH' ) ) exit;

$field = $data[0];
$value = $data[2];
?>

<div class="wbk_app_custom_data_value">
<?php
if( get_option( 'wbk_custom_fields_columns', '')  != '' ){
    echo $value;
    return;
}

$value = json_decode( $value );
if( !is_null( $value ) ){
    foreach( $value as $item ){
        if( is_array( $item ) && count( $item ) == 3 && trim( $item[2] ) != '' ){
            echo $item[1] .': ' . $item[2] . '<br>';
        }
    }

}
?>
</div>
