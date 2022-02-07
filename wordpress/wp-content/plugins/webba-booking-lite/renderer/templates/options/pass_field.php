<?php
if ( !defined( 'ABSPATH' ) ) exit;

$slug = $data[0];
$default_value = $data[1];

$value = get_option( $slug, $default_value );
?>
<input type="password" class="wbk_middle_field" id="<?php echo $slug?>" name="<?php echo $slug?>" value="<?php echo $value; ?>">
