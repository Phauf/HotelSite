<?php
if ( !defined( 'ABSPATH' ) ) exit;

$field = $data[0];
$slug = $data[1];

?>
<textarea  id="<?php echo $slug ?>" data-getter="wbk_app_custom_data" data-setter="wbk_app_custom_data"  name="<?php echo $field->get_name(); ?>" data-default="<?php echo $field->get_default_value(); ?>" class="plugion_hidden plugion_property_input" type="text" required data-validation="textarea" data-required="<?php echo $field->get_required(); ?>"></textarea>
<?php
    $ids = get_option( 'wbk_custom_fields_columns', '');
    if( $ids != '' ){
        $ids = explode( ',', $ids  );
        $i = 0;
        foreach( $ids as $id ){
            $i++;
            $title = '';
            preg_match("/\[[^\]]*\]/", $id, $matches);
            if( is_array( $matches ) && count( $matches ) > 0 ){
                $title = rtrim( ltrim( $matches[0], '[' ), ']' );
            }
            $id = explode( '[', $id );
            $id = $id[0];
            if( $title == '' ){
                $title = $id;
            }
            if( $i > 1 ){
                $container_class = 'plugion_field_container';
            } else {
                $container_class = '';
            }
?>
             <div class="<?php echo $container_class; ?>">
                <div class="plugion_input_container">
                    <input  id="<?php echo $slug . '_' . $id; ?>" name="<?php echo $field->get_name(); ?>"  data-title="<?php echo $title ?>" data-field-id="<?php echo trim( $id ) ?>"  class="plugion_input plugion_input_text plugion_simple_text_input wbk_custom_data_item" type="text" required>
                    <label for="<?php echo  $slug . '_' . $id;  ?>" class="plugion_input_text_label"><?php echo $title; ?></label>
                </div>
            </div>

<?php
        }
    }
?>
