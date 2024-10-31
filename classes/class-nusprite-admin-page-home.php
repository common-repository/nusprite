<?php
if (!class_exists('Nusprite_Admin_Page_Home')) {

    class Nusprite_Admin_Page_Home {

        static function _display() {

            Nusprite::hydrate_Sprites();
            ?>
            <h1>Sprites</h1>

            <a href="?page=nusprite-create-sprite" class="button button-primary"><?php _e("Create a new sprite image", 'nusprite') ?></a>

            <?php
            if (Nusprite::$Sprites == false) {
                
            } else {
                ?>
                <div class="nusprite-table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>id</th>
                                <th><?php _e("Version", 'nusprite') ?></th>
                                <th><?php _e("URL", 'nusprite') ?></th>
                            </tr>
                        </thead>
                        <tbody>      
                            <?php foreach (Nusprite::$Sprites as $sprite_slug => $Sprite) : ?>
                                <tr>
                                    <th><?php
                                        echo '<a href="?page=nusprite-edit-sprite--args_' . $Sprite->sprite_slug . '">' . $Sprite->sprite_slug . '</a>';
                                        ?></th>
                                    <td><?php echo $Sprite->current_version ?></td>
                                    <td><?php
                                        if ($Sprite->current_url) {
                                            echo '<a href="' . $Sprite->current_url . '" target=_blank>' . $Sprite->current_url . '</a>';
                                        } else {
                                            _e("Sprite not yet generated", 'nusprite');
                                        }
                                        ?></td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
                <?php
            }
        }

    }

}