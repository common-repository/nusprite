<?php
if (!class_exists('Nusprite_Admin_Page_Edit_Sprite')) {

    class Nusprite_Admin_Page_Edit_Sprite {
        /*
         * tabs :
         * - image
         * - manage items
         * - settings
         * - delete
         */

        static $Sprite = null;

        static function _display($sprite_slug) {

            $tabs = array('' => __("Current image", 'nusprite'), 'items' => __("Manage images", 'nusprite'), 'settings' => __("Settings"), 'delete' => __("Delete", 'nusprite'));
            $tab = '';
            if (isset($_GET['tab'])) {
                $tab = sanitize_key($_GET['tab']);
            }

            self::$Sprite = Nusprite::get_Sprite($sprite_slug);
            if (self::$Sprite === false) {
                Nusprite_Admin::nag_error(__("Not found", 'nucss') . ' : <b>' . $sprite_slug . '</b>');
                return;
            }

            $content = '';
            switch ($tab) {
                case '' :
                    require_once(__DIR__ . '/class-nusprite-admin-page-edit-sprite-image.php');
                    $content = Nusprite_Admin_Page_Edit_Sprite_Image::_get_content();
                    break;
                case 'settings' :
                    require_once(__DIR__ . '/class-nusprite-admin-page-edit-sprite-settings.php');
                    $content = Nusprite_Admin_Page_Edit_Sprite_Settings::_get_content();
                    break;
                case 'delete' :
                    require_once(__DIR__ . '/class-nusprite-admin-page-edit-sprite-delete.php');
                    $content = Nusprite_Admin_Page_Edit_Sprite_Delete::_get_content();
            }
            ?>

            <h1><?php echo 'Sprite &laquo;&nbsp;' . $sprite_slug . '&nbsp;&raquo;' ?></h1>

            <nav class="nav-tab-wrapper">
                <?php
                foreach ($tabs as $key => $title) {
                    $query_tab = '';
                    if ($key) {
                        $query_tab = '&tab=' . $key;
                    }
                    $class = false;
                    if ($tab == $key) {
                        $class = ' nav-tab-active';
                    }
                    if ($key == 'items') {
                        if (self::$Sprite->custom_post_type == '') {
                            $url = 'edit.php?post_type=nusci_' . self::$Sprite->sprite_slug;
                        } else {
                            $url = 'edit.php?post_type=' . self::$Sprite->custom_post_type;
                        }
                    } else {
                        $url = '?page=nusprite-edit-sprite--args_' . $sprite_slug . $query_tab;
                    }
                    echo '<a href="' . $url . '" class="nav-tab' . $class . '">' . $title . '</a>';
                }
                ?>
            </nav>
            <?php
            echo $content;
        }

    }

}