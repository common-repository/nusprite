<?php
if (!class_exists('Nusprite_Admin_Page_Edit_Sprite_Settings')) {

    class Nusprite_Admin_Page_Edit_Sprite_Settings {

        static function _get_content() {

            $Sprite = Nusprite_Admin_Page_Edit_Sprite::$Sprite;
            $sprite_slug = $Sprite->sprite_slug;

            ob_start();

            if (isset($_POST['nusprite_save_sprite_properties'])) {
                $toupdate = array();
                $registered_post_types = get_post_types();
                if (isset($_POST['post_meta_credits'])) {
                    $sanitized_post_meta_credits = sanitize_key($_POST['post_meta_credits']);
                    if ($sanitized_post_meta_credits !== $_POST['post_meta_credits']) {
                        Nusprite_Admin::nag_error("<b>" . esc_html($_POST['post_meta_credits']) . "</b> : " . __("syntax error", 'nucss'));
                    } else {
                        $toupdate['post_meta_credits'] = $sanitized_post_meta_credits;
                    }
                }
                if (isset($_POST['post_meta_extra'])) {
                    $sanitized_post_meta_extra = sanitize_key($_POST['post_meta_extra']);
                    if ($sanitized_post_meta_extra !== $_POST['post_meta_extra']) {
                        Nusprite_Admin::nag_error("<b>" . esc_html($_POST['post_meta_extra']) . "</b> : " . __("syntax error", 'nucss'));
                    } else {
                        $toupdate['post_meta_extra'] = $sanitized_post_meta_extra;
                    }
                }
                if (isset($_POST['post_meta_image'])) {
                    $sanitized_post_meta_image = sanitize_key($_POST['post_meta_image']);
                    if ($sanitized_post_meta_image !== $_POST['post_meta_image']) {
                        Nusprite_Admin::nag_error("<b>" . esc_html($_POST['post_meta_image']) . "</b> : " . __("syntax error", 'nucss'));
                    } else {
                        $toupdate['post_meta_image'] = $sanitized_post_meta_image;
                    }
                }
                if (!empty($_POST['custom_post_type'])) {
                    $sanitized_custom_post_type = sanitize_key($_POST['custom_post_type']);
                    if (!isset($registered_post_types[$sanitized_custom_post_type])) {
                        Nusprite_Admin::nag_error("post_type <b>" . esc_html($_POST['custom_post_type']) . "</b> " . __("not found", 'nucss') . ' in get_post_types()');
                    } else {
                        $Sprite->custom_post_type = $sanitized_custom_post_type;
                        $toupdate['custom_post_type'] = $sanitized_custom_post_type;
                    }
                }
                if (isset($_POST['max_width_type']) && in_array($_POST['max_width_type'], array('narrowest', 'value', 'widest'))) {
                    $toupdate['max_width_type'] = $_POST['max_width_type'];
                }
                if (isset($_POST['max_width_value'])) {
                    $toupdate['max_width_value'] = (int) $_POST['max_width_value'];
                }
                if (isset($_POST['item_image_size'])) {
                    $toupdate['item_image_size'] = (string) $_POST['item_image_size'];
                }
                if (isset($_POST['security_margins'])) {
                    $toupdate['security_margins'] = (int) $_POST['security_margins'];
                }
                if (isset($_POST['bg_preview'])) {
                    $toupdate['bg_preview'] = sanitize_text_field($_POST['bg_preview']);
                }
                if (isset($_POST['regenerate_nucss_on_regenerate_nusprite'])) {
                    $toupdate['regenerate_nucss_on_regenerate_nusprite'] = sanitize_key($_POST['regenerate_nucss_on_regenerate_nusprite']);
                }

                $toupdate['versioning'] = (int) !empty($_POST['versioning']);
                $toupdate['regenerate_on_save_post'] = (int) !empty($_POST['regenerate_on_save_post']);

                $Sprite->update($toupdate);
            }


            $checked = 'widest';
            if (isset($Sprite->max_width_type)) {
                $checked = $Sprite->max_width_type;
                if ($Sprite->max_width_type == 'value') {
                    
                }
            }
            ?>
            <p></p>                             
            <form method="post">
                <input type="hidden" name="sprite_slug" value="<?php echo $sprite_slug ?>" />
                <?php
                $item_image_sizes = get_intermediate_image_sizes();
                sort($item_image_sizes);
                array_unshift($item_image_sizes, 'full');
                $item_image_sizes = array_unique($item_image_sizes);
                ?>
                item_image_size : <select name="item_image_size">
                    <?php
                    foreach ($item_image_sizes as $item_image_size) {
                        $suffix = "";
                        if ($item_image_size == 'full') {
                            $suffix = " (FULLSIZE)";
                        }
                        $selected = '';
                        if ($Sprite->item_image_size == $item_image_size) {
                            $selected = ' selected ';
                        }
                        echo '<option value="' . $item_image_size . '" ' . $selected . '>' . $item_image_size . $suffix . '</option>';
                    }
                    ?>
                </select>
                <hr />
                <label><?php printf(__("%s of images", 'nusprite'), 'post_type') ?> : <input type="text" name="custom_post_type" value="<?php echo $Sprite->custom_post_type ?>" /> (<?php _e("optional") ?>)</label>

                <hr />
                <label><?php printf(__("%s containing %s", 'nusprite'), 'post_meta', 'attachment_id') ?> : <input type="text" name="post_meta_image" value="<?php echo $Sprite->post_meta_image ?>" /> (<?php _e("optional") ?>) (cf ACF)</label>
                <hr />
                <label><?php printf(__("%s of credits", 'nusprite'), 'post_meta') ?> : <input type="text" name="post_meta_credits" value="<?php echo $Sprite->post_meta_credits ?>" /> (<?php _e("optional") ?>)</label>

                <hr />
                                <label><?php printf(__("%s of extra data", 'nusprite'), 'post_meta') ?> : <input type="text" name="post_meta_extra" value="<?php echo $Sprite->post_meta_extra ?>" /> (<?php _e("optional") ?>)</label>

                
                <hr />
                <label><input type="checkbox" name="versioning" <?php echo $Sprite->versioning ? ' checked ' : '' ?> /> versioning</label>
                <hr />
                <label>
                    <input type="checkbox" name="regenerate_on_save_post" <?php echo $Sprite->regenerate_on_save_post ? ' checked ' : '' ?> /> regenerate_on_save_post</label>
                <hr />

                <label>
                    <?php
                    $disabled = (class_exists('Nucss') == false);
                    ?>
                    regenerate_nucss_on_regenerate_nusprite : <input type="input" name="regenerate_nucss_on_regenerate_nusprite" value="<?php echo htmlspecialchars($Sprite->regenerate_nucss_on_regenerate_nusprite, ENT_QUOTES) ?>" <?php echo ($disabled ? ' disabled' : '') ?> /></label> <?php echo ($disabled ? '(Nucss plugin must be activated)' : '(nucss stylesheet slug)') ?>
                <hr />
                <label>
                    <?php
                    $attrs = '';
                    if ($checked == 'narrowest') {
                        $attrs .= ' checked ';
                    }
                    ?><input type="radio" name="max_width_type" value="narrowest" <?php echo $attrs ?> /> <?php
                    echo __("Narrowest as possible", 'nusprite');
                    ?>
                </label>
                <br /><label>
                    <?php
                    $attrs = '';
                    if ($checked == 'value') {
                        $attrs .= ' checked ';
                    }
                    ?>
                    <input type="radio" name="max_width_type" value="value" <?php echo $attrs ?> /> <?php _e("Max width", 'nusprite') ?> : <input type="input" name="max_width_value" value="<?php echo htmlspecialchars($Sprite->max_width_value, ENT_QUOTES) ?>" pattern="[0-9]+" size="3" />px  
                </label>
                <br /><label>
                    <?php
                    $attrs = '';
                    if ($checked == 'widest') {
                        $attrs .= ' checked ';
                    }
                    ?>
                    <input type="radio" name="max_width_type" value="widest" <?php echo $attrs ?> /> <?php _e("Widest as possible", 'nusprite') ?>
                </label>
                <hr />
                <label>security_margins : <input type="text" name="security_margins" value="<?php echo $Sprite->security_margins ?>" size="1" />px</label>
                <hr />
                <label>bg_preview : <input type="text" name="bg_preview" value="<?php echo htmlspecialchars($Sprite->bg_preview, ENT_QUOTES) ?>" /></label>
                <hr />                
                <br /><button type="submit" name="nusprite_save_sprite_properties" class="button button-primary"><?php _e("Submit") ?></button>

            </form>

            <?php
            return ob_get_clean();
        }

    }

}