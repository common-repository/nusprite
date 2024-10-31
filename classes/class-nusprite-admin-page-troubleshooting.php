<?php
if (!class_exists('Nusprite_Admin_Page_Troubleshooting')) {

    class Nusprite_Admin_Page_Troubleshooting {

        static function _display() {
            ?>
            <h1><?php _e("Troubleshooting", 'nusprite') ?></h1>

            <h2>Credits don't show</h2>

            <p>Make sure you specified a post_meta (see settings).</p>            
            <?php
        }

    }

}