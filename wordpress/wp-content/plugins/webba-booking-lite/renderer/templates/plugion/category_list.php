<?php
if ( !defined( 'ABSPATH' ) ) exit;

$categories = WBK_Model_Utils::get_service_categories();

?>

<div class="plugion_input_container">
<select id="wbk_category_list"  class="plugion_input plugion_input_select plugion_mt_20">
    <option value="0"><?php echo __('Select category', 'wbk' ) ?></option>

<?php
    foreach( $categories as $key => $value ){
        $services = json_encode( WBK_Model_Utils::get_services_in_category( $key ) );

        ?>
        <option data-services="<?php echo htmlspecialchars( $services ); ?>" value="<?php echo $key; ?>"><?php echo $value; ?></option>
        <?php
    }
 ?>
</select>
<label for="wbk_category_list"  class="plugion_input_select_label"><?php echo __( 'Service catetories', 'wbk' ) ?> </label>
</div>
