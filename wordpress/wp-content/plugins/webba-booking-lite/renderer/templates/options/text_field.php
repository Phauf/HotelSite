<?php
if ( !defined( 'ABSPATH' ) ) exit;

$slug = $data[0];
$default_value = $data[1];
$description = $data[2];

$value = get_option( $slug, $default_value );
?>
<input type="text" class="wbk_middle_field" id="<?php echo $slug?>" name="<?php echo $slug?>" value="<?php echo $value; ?>">
<p class="description"><?php echo $description?></p>
