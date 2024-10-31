<?php
if (!class_exists('Nusprite_Admin_Page_Edit_Sprite_Image')) {

    class Nusprite_Admin_Page_Edit_Sprite_Image {

        static function _get_content() {
            $Sprite = Nusprite_Admin_Page_Edit_Sprite::$Sprite;
            $sprite_slug = $Sprite->sprite_slug;
            if (isset($_POST['regenerate_sprite'])) {
                $Sprite->regenerate();
            }
            $Sprite->hydrate_Items(true);
            ob_start();
            ?>
            <form method="post">
                <input type="hidden" name="sprite_slug" value="<?php echo $sprite_slug ?>" />
                <p><button type="submit" name="regenerate_sprite" class="button button-primary"><?php _e("Regenerate this sprite", 'nusprite') ?></button></p>
            </form>
            <?php
            if ($Sprite->current_url) {
                ?>
                <div class="nusprite-image-wrapper" style="background:<?php echo $Sprite->bg_preview ?>"><img src="<?php echo $Sprite->current_url . "?time=" . time() ?>" /></div>

                <div class="nusprite-table-wrapper">
                    <table>
                        <tbody>
                            <tr><th>URL</th><td><a href="<?php echo $Sprite->current_url ?>" target=_blank><?php echo $Sprite->current_url ?></a></td></tr>
                            <tr><th><?php _e("Version", 'nusprite') ?></th><td><?php echo $Sprite->current_version ?></th></tr>
                            <tr><th><?php _e("Sizes", 'nusprite') ?></th><td><?php echo $Sprite->current_width . 'x' . $Sprite->current_height ?></th></tr>
                            <tr><th><?php _e("Weight", 'nusprite') ?></th><td><?php echo Nusprite_Admin::formatBytes($Sprite->current_filesize) ?></th></tr>
                        </tbody>
                    </table>
                </div>
                <?php
            }
            if ($Sprite->Items) {
                ?>
                <h2><?php _e("Items", 'nusprite') ?></h2>
                <div class="nusprite-table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>post ID</th>
                                <th>item_slug</th>
                                <th>width</th>
                                <th>height</th>
                                <th>pos_x</th>
                                <th>pos_y</th>
                                <th>credits</th>
                                <th>extra</th>
                            </tr>                        
                        </thead>
                        <tbody>
                            <?php foreach ($Sprite->Items as $Item) : ?>
                                <tr>
                                    <td><?php
                                        echo '<a href="post.php?post=' . $Item->post_id . '&action=edit">' . $Item->post_id . '</a>';
                                        ?></td>
                                    <td><?php echo $Item->item_slug ?></td>
                                    <td><?php echo $Item->width ?></td>
                                    <td><?php echo $Item->height ?></td>
                                    <td><?php echo $Item->pos_x ?></td>
                                    <td><?php echo $Item->pos_y ?></td>
                                    <td><?php echo htmlentities($Item->credits) ?></td>
                                    <td><?php echo htmlentities($Item->extra) ?></td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>

                    <p><?php _e("Example", 'nusprite') ?> :<code><?php echo 'echo Nusprite::get(\'' . $sprite_slug . '\',\'' . $Item->item_slug . '\')->format(\'background:transparent url($url) $pos_x $pos_y no-repeat;width:$width;height:$height;\');' ?></code></p>
                    <p><?php _e("Example", 'nusprite') ?> :<code><?php echo 'var_dump(Nusprite::get(\'' . $sprite_slug . '\')[\'' . $Item->item_slug . '\']->width());' ?></code></p>
                    <p><?php _e("Example", 'nusprite') ?> :<code><?php echo 'echo Nusprite::get()[\'' . $sprite_slug . '\'][\'' . $Item->item_slug . '\']->post()->post_title;' ?></code></p>
                </div>
                <?php
            }
            return ob_get_clean();
        }

    }

}