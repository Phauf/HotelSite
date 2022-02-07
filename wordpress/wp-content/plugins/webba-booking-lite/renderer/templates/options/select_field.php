<?php
if ( !defined( 'ABSPATH' ) ) exit;

$slug = $data[0];
$default_value = $data[1];
$value = get_option( $slug, $default_value );
$description = $data[2];
$extra = $data[3];

?>
<select id="<?php echo $slug; ?>" name="<?php echo $slug; ?>">
<?php
    foreach( $extra as $key => $value_this ){

        echo '<option '. selected( $value, $key, false ) . ' value="' . $key . '">' . $value_this . '</option>';

    }
?>
</select>
