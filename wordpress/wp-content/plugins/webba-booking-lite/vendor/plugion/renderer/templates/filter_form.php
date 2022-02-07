<?php
if ( !defined( 'ABSPATH' ) ) exit;
/*
 * This file is part of Plugion framework.
 * (c) plugion.com <hello@plugion.org>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$slug = $data;
do_action( 'plugion_before_filter_form', $slug );
if( count( Plugion()->tables->get_element_at( $slug )->get_data( 'filters' ) ) == 0 ){
    return;
}
?>
<div style="display:none;" class="plugion_filter_form" data-table="<?php echo $slug; ?>">
    <div class="plugion_property_header">
        <span class="plugion_filter_title">
            <?php
                echo plugion_translate_string( 'Filters for' ) . ' ' . Plugion()->tables->get_element_at( $slug )->get_multiple_item_name();
            ?>
        </span>
        <div class="plugion_form_controls">
            <input type="button" class="plugion_transparent_white_button plugion_button" id="plugion_filter_apply" value="<?php echo plugion_translate_string( 'Apply' );?>">
            <input type="button" class="plugion_transparent_white_button plugion_button" id="plugion_filter_apply_close" value="<?php echo plugion_translate_string( 'Apply and close' ) ?>">
        </div>
        <a class="plugion_dark_button plugion_filter_cancel" href="#"></a>
        <div class="plugion_line_loader plugion_hidden"></div>
    </div>
    <div class="plugion_filter_content_outer">
        <div class="plugion_overlay plugion_hidden"></div>
        <div class="plugion_filter_content_inner">
            <div id="plugion_filter_info">
            </div>
                <?php
                    foreach ( Plugion()->tables->get_element_at( $slug )->get_data( 'filters' ) as $field_slug => $field ) {
                        if( $field->get_filter_type() == '' ){
                            continue;
                        }
                        ?>
                        <div class="plugion_filter_container">
                            <?php
                                if ( !has_action( 'plugion_filter_' . $field->get_filter_type() ) ) {
                                    echo '<p>No action found for the <strong>' . 'plugion_filter_' . $field->get_filter_type()  . '</strong></p>';
                                }
                                do_action( 'plugion_filter_' . $field->get_filter_type(), [ $field, $field_slug ] ); ?>
                            </div>
                <?php
                    }
                ?>
        </div>
    </div>
</div>
<?php
do_action( 'plugion_after_filter_form', $slug );

?>
