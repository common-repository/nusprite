<?php
if (!class_exists('Nusprite_Admin_Page_Edit_Sprite_Delete')) {

    class Nusprite_Admin_Page_Edit_Sprite_Delete {

        static function _get_content() {
            global $wpdb;
            $Sprite = Nusprite_Admin_Page_Edit_Sprite::$Sprite;

            if (isset($_POST['nusprite_delete_sprite']) && isset($_POST['sprite_slug']) && $_POST['sprite_slug'] == $Sprite->sprite_slug) {
                $Sprite->delete();
                ?>
                <script>window.location.href = 'admin.php?page=nusprite';</script>
                <?php
                return;
            }
            ob_start();
            ?>
            <div class="nusprite">
                <h2>1/3 - <?php _e("Posts", 'nusprite') ?></h2>
                <p><?php printf(__("You could %s delete related posts", 'nusprite'), '<a href="edit.php?post_type='.$Sprite->get_real_post_type(). '">') . '</a>.' ?></p>
                <h2>2/3 - <?php _e("Images", 'nusprite') ?></h2>
                <p><?php printf(__("You could %s delete unattached images", 'nusprite'), '<a href="upload.php?mode=list&attachment-filter=detached">') . '</a>.' ?></p>


                <h2>3/3 - <?php _e("Sprite", 'nusprite') ?></h2>

                <form method="post" onsubmit="return confirm('<?php echo htmlspecialchars(sprintf(__('Delete sprite "%s" ?', 'nusprite'), $Sprite->sprite_slug)) ?>');">
                    <input type="hidden" name="sprite_slug" value="<?php echo $Sprite->sprite_slug ?>" />
                    <button type="submit" name="nusprite_delete_sprite" class="button button-primary"><?php _e("Delete this sprite", 'nusprite') ?></button>
                </form>
            </div>
            <?php
            return ob_get_clean();
        }

    }

}