<?php
if (!class_exists('Nusprite_Admin_Page_Documentation')) {

    class Nusprite_Admin_Page_Documentation {

        static function _display() {
            ?>
            <div class="nusprite">
                <h1><?php _e("Documentation", 'nusprite') ?></h1>

                <p>Nusprite generates sprites with all <b>featured images</b> for a given post type.</p>

                <p>Nusprite registers custom post types (beginning with "nusci_") for each sprite image.</p>

                <h2>Can I use featured images from my own custom post type ?</h2>

                <p>Yes. In settings, fill in the field "<b><?php printf(__("%s of images", 'nusprite'), 'post_type') ?></b>". All featured images from this post type will be used.</p>

                <h2>Can I use images that are not featured images ?</h2>

                <p>Yes. In settings, fill in the field "<b><?php printf(__("%s containing %s", 'nusprite'), 'post_meta', 'attachment_id') ?></b>" (ACF slug for example).</p>

                <h2>Should I activate versioning ?</h2>

                <p>If your sprite image is called by a cached stylesheet, overwriting it may cause troubles. Versioning avoids this problem by creating a new file each time sprite is regenerated.</p>

                <p>A cron job will delete old unused files. Make sure it is activated.</p>

                <p>Versioning make it possible to set long term <b>cache control</b> and <b>expire headers</b> for files.</p>

                <h2>How to get a CSS rule ?</h2>

                <code>echo Nusprite::get('sprite_id','item_id')->format('background:transparent url($url) $pos_x $pos_y no-repeat;width:$width:height:$height;');</code>

                <ul>
                    <li>'sprite_id' is sprite slug</li>
                    <li>'item_id' is item slug (post_name of post with featured image)</li>
                </ul>

                <p>Use $ in 'format' method for any of these variables :</p>

                <ul>
                    <li>$url : URL of sprite image</li>
                    <li>$pos_x : x position of item in sprite</li>
                    <li>$pos_y</li>
                    <li>$width : width of item</li>
                    <li>$height</li>
                    <li>$slug</li>
                </ul>

                <h3>All rules in one loop</h3>

                <code>$items=Nusprite::get('sprite_id');<?php echo PHP_EOL ?><!--
                    -->$pattern='.icon-$slug { background:transparent url($url) $pos_x $pos_y no-repeat;width:$width:height:$height; }';<?php echo PHP_EOL ?><!--
                    -->foreach ($items as $item) {<?php echo PHP_EOL ?><!--
                    -->     echo $item->format($pattern);<?php echo PHP_EOL ?><!--
                    -->}</code>

                <h3>All sprites</h3>

                <code>$sprites=Nusprite::get();<?php echo PHP_EOL ?><!--
                    -->$pattern='icon-$slug { background:transparent url($url) $pos_x $pos_y no-repeat;width:$width:height:$height; }';<?php echo PHP_EOL ?><!--
                    -->foreach ($sprites as $sprite_id=>$sprite) {<?php echo PHP_EOL ?><!--
                    -->     foreach ($sprite as $items) {<?php echo PHP_EOL ?><!--
                    -->          echo '.sprite-'.$sprite_id.'-'.$item->format($pattern);<?php echo PHP_EOL ?><!--
                    -->     }<?php echo PHP_EOL ?><!--
                    -->}<?php echo PHP_EOL ?><!--
                    --></code>


                <h2>How to get credits ?</h2>

                <ol>
                    <li>Add a custom field for your featured images (using Advanced Custom Field for example)</li>
                    <li>In settings, fill in this custom field slug</li>
                    <li>In your featured images, fill in credits</li>
                    <li>Regenerate sprite image</li>
                    <li>Get a concatened block of credits thanks to shortcode <code>[nusprite_credits my_sprite_id]</code></li>
                </ol>
            </div>
            <?php
        }

    }

}