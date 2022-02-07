<?php
if ( !defined( 'ABSPATH' ) ) exit;
/*
 * This file is part of Plugion framework.
 * (c) plugion.com <hello@plugion.org>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$slug = $data;
do_action( 'plugion_before_properties_update_form', $slug );
?>
<div style="display:none;"  class="plugion_property_container_update_form" data-table="<?php echo $slug; ?>">
    <div class="plugion_property_header">
        <span class="plugion_properties_title">
            New service
        </span>
        <div class="plugion_form_controls">
            <input type="button" class="plugion_transparent_white_button plugion_button" id="plugion_properties_save" value="<?php echo plugion_translate_string( 'Save'  );?>">
            <input type="button" class="plugion_transparent_white_button plugion_button" id="plugion_properties_save_close" value="<?php echo plugion_translate_string( 'Save and close'  ) ?>">
            <?php
                if( Plugion()->tables->get_element_at( $slug )->current_user_can_delete() ){
                ?>
                        <input type="button" class="plugion_transparent_white_button plugion_button" id="plugion_properties_delete" value="<?php echo plugion_translate_string( 'Delete'  ); ?>">
                        <div class="plugion_small_popup plugion_delete_conirmation_holder">
                            <?php echo plugion_translate_string( 'Are you sure?'  ) ?><br>
                            <input type="button" class="plugion_transparent_dark_button plugion_button" id="plugion_properties_delete_confirm" value="<?php echo plugion_translate_string( 'Yes, delete it.'  ); ?>">
                        </div>

                <?php
                }
            ?>
            <input type="button" class="plugion_transparent_white_button plugion_hidden plugion_button" id="plugion_properties_discard" value="<?php echo plugion_translate_string( 'Discard changes' ) ?>">
        </div>
        <a class="plugion_dark_button plugion_properties_cancel" href="#"></a>
        <div class="plugion_line_loader plugion_hidden"></div>
    </div>
    <div class="plugion_property_content_outer">
        <div class="plugion_overlay plugion_hidden"></div>
        <div class="plugion_property_content_inner">
            <div id="plugion_propery_info">
            </div>
            <div data-accordion-group>
                <?php
                $sections = array_unique( Plugion()->tables->get_element_at( $slug )->get_data( 'property_sections_update' ) );
                if ( count( $sections ) > 1 ) {
                    foreach ( $sections as $section ) {
                        if ( !array_key_exists( $section, Plugion()->tables->get_element_at( $slug )->sections ) ) {
                            continue;
                        } ?>
                        <div class="plugion_accordion open" data-accordion>
                            <div class="plugion_accordion_control" data-control><?php echo Plugion()->tables->get_element_at( $slug )->sections[ $section ]; ?></div>
                            <div class="plugion_accordion_section" data-content>
                                <?php
                                foreach ( Plugion()->tables->get_element_at( $slug )->get_data( 'property_fields_update' ) as $field_slug => $field ) {
                                    if ( $field->get_section() !== $section ) {
                                        continue;
                                    }
                                    if ( is_array( $field->get_dependency() ) ) {
                                        $arr_dependency = $field->get_dependency();
                                        if ( isset(  $arr_dependency['administrator'] ) ) {
                                            $user = wp_get_current_user();
                                            $role = $user->roles[0];
                                            if ( isset( $arr_dependency[ $role ] ) ) {
                                                $arr_dependency = $arr_dependency[ $role ];
                                                $dependency = '[';
                                                foreach ( $arr_dependency as $value ) {
                                                    $dependency .= '["' . implode( '","', $value ) . '"]';
                                                }
                                                $dependency .= ']';
                                                $dependency = str_replace( '][', '],[', $dependency );
                                            } else {
                                                $dependency = '';
                                            }
                                        } else {
                                            $dependency = '[';
                                            foreach ( $arr_dependency as $value ) {
                                                $dependency .= '["' . implode( '","', $value ) . '"]';
                                            }
                                            $dependency .= ']';
                                            $dependency = str_ireplace( '][', '],[', $dependency );
                                        }
                                    } else {
                                        $dependency = '[]';
                                    } ?>

                                    <div class="plugion_field_container" data-dependency='<?php echo $dependency; ?>'>
                                    <?php
                                        if ( !has_action( 'plugion_property_field_' . $field->get_type() ) ) {
                                            echo '<p>No action found for the <strong>' . 'plugion_property_field_' . $field->get_type()  . '</strong></p>';
                                        }
                                        do_action( 'plugion_property_field_' . $field->get_type(), [ $field, $field_slug ] ); ?>
                                    </div>
                                    <?php
                                } ?>
                                <div style="clear:both;display:block;height:10px;border:none;"></div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    foreach ( Plugion()->tables->get_element_at( $slug )->get_data( 'property_fields_update' ) as $field_slug => $field ) {
                        if ( is_array( $field->get_dependency() ) ) {
                            $arr_dependency = $field->get_dependency();
                            if ( isset(  $arr_dependency['administrator'] ) ) {
                                $user = wp_get_current_user();
                                $role = $user->roles[0];
                                if ( isset( $arr_dependency[ $role ] ) ) {
                                    $arr_dependency = $arr_dependency[ $role ];
                                    $dependency = '[';
                                    foreach ( $arr_dependency as $value ) {
                                        $dependency .= '["' . implode( '","', $value ) . '"]';
                                    }
                                    $dependency .= ']';
                                } else {
                                    $dependency = '';
                                }
                            } else {
                                $dependency = '[';
                                foreach ( $arr_dependency as $value ) {
                                    $dependency .= '["' . implode( '","', $value ) . '"]';
                                }
                                $dependency .= ']';
                                $dependency = str_replace( '][', '],[', $dependency );
                            }
                        } else {
                            $dependency = '';
                        } ?>
                            <div class="plugion_field_container" data-dependency='<?php echo $dependency; ?>'>
                            <?php
                                if ( !has_action( 'plugion_property_field_' . $field->get_type() ) ) {
                                    echo '<p>No action found for the <strong>' . 'plugion_property_field_' . $field->get_type()  . '</strong></p>';
                                }
                                do_action( 'plugion_property_field_' . $field->get_type(), [ $field, $field_slug ] ); ?>
                            </div>
                        <?php
                    }
                }
                ?>

            </div>
        </div>
    </div>
</div>
<?php
do_action( 'plugion_after_properties_update_form', $slug );
?>
