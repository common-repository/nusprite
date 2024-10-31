<?php
if (!class_exists('Nusprite_Admin_Page_Create_Sprite')) {

    class Nusprite_Admin_Page_Create_Sprite {

        static function _display() {
            ?><h1><?php _e("Create sprite image", 'nusprite') ?></h1><?php
            $success = false;
            if (isset($_POST['nusprite_create_new_sprite']) && isset($_POST['sprite_slug'])) {
                Nusprite::hydrate_Sprites();
                $sprite_slug = sanitize_key($_POST['sprite_slug']);
                if (isset(Nusprite::$Sprites[$sprite_slug])) {
                    Nusprite_Admin::nag_error(__("slug already exists", 'nusprite'));
                } elseif (preg_match('/^[a-z0-9_]{1,' . Nusprite_Admin::$sprite_slug_max_length . '}$/', $sprite_slug) == false) {
                    Nusprite_Admin::nag_error(__("syntax error", 'nusprite'));
                }
                require_once('class-nusprite-sprite.php');
                $Nusprite_Sprite = new Nusprite_Sprite($sprite_slug);
                $Nusprite_Sprite->create();
                $success = true;
                Nusprite_Admin::nag_success('<a href="?page=nusprite-edit-sprite--args_' . $sprite_slug . '">' . __("Edit sprite", 'nusprite') . ' <b>' . $sprite_slug . '</b></a>');
            }

            if ($success == false) {
                if (!isset($sprite_slug)) {
                    $sprite_slug = '';
                }
                ?>
                <form method="post">
                    <?php
                    echo '<p><label>' . __("Slug") . ' : ';
                    echo '<input type="text"';
                    echo ' name="sprite_slug"';
                    echo ' required';
                    echo ' value="' . htmlspecialchars($sprite_slug, ENT_QUOTES) . '"';
                    echo ' pattern="[a-z0-9_]{1,' . Nusprite_Admin::$sprite_slug_max_length . '}"';
                    echo ' maxlength="' . Nusprite_Admin::$sprite_slug_max_length . '"';
                    echo '/> </label> (' . sprintf(__("lowercase, figures, underscores, max %d characters", 'nusprite'), Nusprite_Admin::$sprite_slug_max_length) . ') ';
                    ?>                    
                    <p><button type="submit" name="nusprite_create_new_sprite" class="button button-primary"><?php _e("Create sprite image", 'nusprite') ?></button></p>
                </form>                              
                <?php
            }
        }

    }

}