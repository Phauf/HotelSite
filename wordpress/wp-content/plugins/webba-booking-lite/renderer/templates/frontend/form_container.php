<?php
if ( !defined( 'ABSPATH' ) ) exit;
?>
<div class="wbk-outer-container wbk_booking_form_container">
	<div class="wbk-inner-container">
        <?php
            do_action('webba_before_booking_form');
        ?>
     	<img src=<?php echo  WP_WEBBA_BOOKING__PLUGIN_URL . '/frontend/images/loading.svg' ?> style="display:block;width:0px;height:0px;">
        <img src=<?php echo  WP_WEBBA_BOOKING__PLUGIN_URL . '/frontend/images/loading_small.svg' ?> style="display:block;width:0px;height:0px;">
        <?php
            echo $data;
        ?>
    </div>
</div>
