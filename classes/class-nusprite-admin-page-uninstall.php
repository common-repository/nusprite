<?php
if (!class_exists('Nusprite_Admin_Page_Uninstall')) {

    class Nusprite_Admin_Page_Uninstall {

        static function _display() {
            global $wpdb;
            Nusprite::hydrate_Sprites();
            ?>
            <div class="nusprite">
                <h1><?php _e("Uninstall", 'nusprite') ?></h1>

                <?php
                if (Nusprite::$Sprites == false) {
                    ?><p><?php printf(__("Plugin is ready for %s uninstall", 'nusprite'), '<a href="plugins.php">') . '</a> !' ?></p><?php
                } else {
                    ?>
                    <p><?php _e("To avoid erasure of useful data, posts will not be deleted during uninstall process.", 'nusprite') ?></p>
                    <p><?php _e("Before uninstalling this plugin, please manually delete these sprites :", 'nusprite') ?></p>
                    <ul>
                        <?php
                        foreach (Nusprite::$Sprites as $Sprite) {
                            echo '<li><a href="admin.php?page=nusprite-edit-sprite--args_' . $Sprite->sprite_slug . '&tab=delete">' . $Sprite->sprite_slug . '</a></li>';
                        }
                        ?>
                    </ul>
                    <?php
                }
                ?>            

            </div>
            <?php
        }

    }

}