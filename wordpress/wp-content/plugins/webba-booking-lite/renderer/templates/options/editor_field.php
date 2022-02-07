<?php
if ( !defined( 'ABSPATH' ) ) exit;

$slug = $data[0];
$default_value = $data[1];
$description = $data[2];

$value = get_option( $slug, $default_value );

$mcesettings = array();
$mcesettings['valid_elements'] ='*[*]';
$mcesettings['extended_valid_elements'] = '*[*]';


$args = array(
    'media_buttons' => false,
    'editor_height' => 300,
    'tinymce' => $mcesettings

);
wp_editor( $value, $slug, $args );
?>
<p class="description"><?php echo $description; ?></p>
