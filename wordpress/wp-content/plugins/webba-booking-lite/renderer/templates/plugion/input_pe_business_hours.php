<?php
if ( !defined( 'ABSPATH' ) ) exit;
$field = $data[0];
$slug = $data[1];
$time_format = get_option( 'wbk_time_format', 'g:i' );
if ( strpos( $time_format, 'a') != false ||  strpos( $time_format, 'A') != false  ) {
    $time_format = 'ampm';
} else {
    $time_format = 'no_ampm';
}

?>
<div class="plugion_input_container">
    <label for="<?php echo $slug; ?>" class="plugion_input_label"><?php echo $field->get_title(); ?></label>
    <input data-format="<?php echo $time_format; ?>" data-setter="wbk_business_hours" data-getter="wbk_business_hours" data-default="<?php echo $field->get_default_value(); ?>" id="<?php echo $slug ?>" name="<?php echo $field->get_name(); ?>" data-default="<?php echo $field->get_default_value(); ?>" class="plugion_input plugion_property_input" type="hidden" data-validation="wbk_business_hours" required data-required="<?php echo $field->get_required(); ?>">
    <form class="repeater">
        <div data-repeater-list="dow_availability">
            <div data-repeater-item class="wbk_business_hours_group">
                <div class="plugion_input_container_small">
                    <select name="day_of_week" class="plugion_input_select wbk_bh_data_day">
                        <option data-number="7" value="7"><?php echo __( 'Sunday', 'wbk' ) ?></option>
                        <option data-number="1" value="1"><?php echo __( 'Monday', 'wbk'  ) ?></option>
                        <option data-number="2" value="2"><?php echo __( 'Tuesday', 'wbk'  ) ?></option>
                        <option data-number="3" value="3"><?php echo __( 'Wednesday', 'wbk'  ) ?></option>
                        <option data-number="4" value="4"><?php echo __( 'Thursday', 'wbk'  ) ?></option>
                        <option data-number="5" value="5"><?php echo __( 'Friday', 'wbk'  ) ?></option>
                        <option data-number="6" value="6"><?php echo __( 'Saturday', 'wbk'  ) ?></option>
                    </select>
                </div>
                <div class="plugion_input_container_small">
                    <select name="status" class="plugion_input_select wbk_bh_data_status">
                        <option value="active"><?php echo __( 'Active', 'wbk' ) ?></option>
                        <option value="inactive"><?php echo __( 'Inactive', 'wbk'  ) ?></option>
                    </select>
                </div>
                <span data-repeater-delete type="button" class="wbk_repeater_delete_btn">+</span>
                <div class="plugion_input_container">
                    <input type="text" name="start" style="display:none" class="plugion_input_select wbk_bh_data_start">
                    <input type="text" name="end" style="display:none" class="plugion_input_select wbk_bh_data_end">
                </div>
                <div class="plugion_input_container">
                    <span class="wbk_business_hours_group_time"></span>
                    <div class="slider-time"></div><br>
                </div>
          </div>
        </div>
        <input data-repeater-create type="button" class="wbk_repeater_add_btn" value="<?php echo __( 'Add time interval', 'wbk' ) ?>"/>

    </form>
</div>
