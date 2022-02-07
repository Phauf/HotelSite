<?php
if ( !defined( 'ABSPATH' ) ) exit;

$slug = $data[0];
$default_value = $data[1];
$description = $data[2];

$value = get_option( $slug, $default_value );
?>


<textarea style="width:350px; height:250px" name="<?php echo $slug; ?>" id="<?php echo $slug; ?>"><?php echo $value ?></textarea>
<p class="description"><?php echo $description; ?></p>
