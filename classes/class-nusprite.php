<?php

if (!class_exists('Nusprite')) {

    class Nusprite {

        static $Sprites = null;
        static $baseurl = '';

        static function init() {
            add_shortcode('nusprite_credits', 'Nusprite::get_credits');
        }

        static function get_credits($args = array()) {
            if (!$args || !is_array($args)) {
                return;
            }
            $sprite_slug = $args[0];
            $path_credits = self::get_basedir('', 'credits-' . $sprite_slug . '.html');
            if (file_exists($path_credits)) {
                return file_get_contents($path_credits);
            }
        }

        static function get_basedir($subfolder = '', $filename = '', $create_folder = false) {
            $wp_get_upload_dir = wp_get_upload_dir();
            $res = trailingslashit($wp_get_upload_dir['basedir']) . 'nusprite';
            $subfolder = trim($subfolder, '/');
            if ($subfolder) {
                $res .= '/' . $subfolder;
            }
            if ($create_folder) {
                wp_mkdir_p($res);
            }
            $filename = ltrim($filename, '/');
            if ($filename) {
                $res .= '/' . $filename;
            }
            return $res;
        }

        static function get_baseurl($subfolder = '', $filename = '') {
            $wp_get_upload_dir = wp_get_upload_dir();
            $res = trailingslashit($wp_get_upload_dir['baseurl']) . 'nusprite';
            $subfolder = trim($subfolder, '/');
            if ($subfolder) {
                $res .= '/' . $subfolder;
            }
            $filename = ltrim($filename, '/');
            if ($filename) {
                $res .= '/' . $filename;
            }
            return $res;
        }

        static function get_Sprite($sprite_slug) {
            self::hydrate_Sprites($sprite_slug);
            if (!isset(self::$Sprites[$sprite_slug])) {
                return false;
            } else {
                return self::$Sprites[$sprite_slug];
            }
        }

        static function hydrate_Sprites($sprite_slug = false) {

            if (self::$Sprites === null) {
                self::$Sprites = array();
            } else {
                if ($sprite_slug == false) {
                    return null;
                }
            }
            require_once('class-nusprite-sprite.php');

            $WHERE = "";
            if ($sprite_slug) {
                if (isset(self::$Sprites[$sprite_slug])) {
                    return null;
                }
                $WHERE = " WHERE sprite_slug='" . addslashes($sprite_slug) . "' ";
            }
            global $wpdb;
            $rows = $wpdb->get_results("SELECT * FROM `" . $wpdb->prefix . "nusprite_sprites`" . $WHERE . ";", ARRAY_A);
            if ($rows == false) {
                return false;
            }
            foreach ($rows as $row) {
                self::$Sprites[$row['sprite_slug']] = new Nusprite_Sprite($row['sprite_slug'], $row);
            }
        }

        /*
         * get all items of all sprites => ['sprite_slug_1'=>['item_slug_1'=>$Item,'item_slug_2'=>$Item],...]
         * get all items of one sprite => ['item_slug_1'=>$Item,'item_slug_2'=>$Item]]
         * get one item of one sprite => $Item
         */

        static function get($sprite_slug = false, $item_slug = false) {
            require_once('class-nusprite-item.php');
            //
            // ALL SPRITES
            //
            if ($sprite_slug === false) {
                // get all sprites
                self::hydrate_Sprites();
                $res = array();
                foreach (self::$Sprites as $Sprite) {
                    $Sprite->hydrate_Items();
                    foreach ($Sprite->Items as $Item) {
                        $res[$Sprite->sprite_slug][$Item->item_slug] = $Item;
                    }
                }
                return $res;
            }
            //
            // ONE SPRITE
            //
            if (self::hydrate_Sprites($sprite_slug) === false) {
                // not found
                if ($item_slug === false) {
                    return false;
                } else {
                    return self::get_pseudo_item();
                }
            }
            self::$Sprites[$sprite_slug]->hydrate_Items();
            if ($item_slug === false) {
                // get all items
                return self::$Sprites[$sprite_slug]->Items;
            } else {
                // get the one specified
                if (isset(self::$Sprites[$sprite_slug]->Items[$item_slug])) {
                    return self::$Sprites[$sprite_slug]->Items[$item_slug];
                } else {
                    return self::get_pseudo_item();
                }
            }
        }

        static function get_pseudo_item() {
            require_once('class-nusprite-item.php');
            $Pseudo_Item = new Nusprite_Item();
            return $Pseudo_Item;
        }

    }

}