<?php

if (!class_exists('Nusprite_Version')) {

    class Nusprite_Version {

        public $sprite_slug = '';
        public $version = 1;
        public $path = '';

        function __construct($row) {
            $this->sprite_slug = $row['sprite_slug'];
            $this->version = $row['version'];
            $this->path = $row['path'];
        }

        function delete() {
            global $wpdb;

            $wpdb->query("DELETE FROM `" . $wpdb->prefix . "nusprite_versions` WHERE sprite_slug='" . addslashes($this->sprite_slug) . "' AND version='" . (int) $this->version . "';");

            $md5 = $this->path;
            $dir_path_1 = Nusprite::get_basedir($md5[0]);
            $dir_path_12 = $dir_path_1 . '/' . $md5[1];
            $dir_path_123 = $dir_path_12 . '/' . $md5[2];
            if (!file_exists($dir_path_123)) {
                return;
            }
            $file_path = $dir_path_123 . '/' . $this->sprite_slug . '-' . $this->version . '.png';
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            if (empty(glob($dir_path_123 . '/*'))) {
                // no files left
                rmdir($dir_path_123);
                if (empty(glob($dir_path_12 . '/*'))) {
                    // no files left
                    rmdir($dir_path_12);
                    if (empty(glob($dir_path_1 . '/*'))) {
                        // no files left
                        rmdir($dir_path_1);
                    }
                }
            }
        }

        static function get_expiration() {
            $res = get_option('nusprite_expiration');
            if ($res === null) {
                $res = 60;
            } else {
                $res = (int) $res;
            }
            return $res;
        }

        static function set_expiration($nb_days) {
            update_option('nusprite_expiration', (int) $nb_days);
        }

        static function delete_versions($nb = 10) {
            global $wpdb;

            $nb_days = self::get_expiration();
            Nusprite::hydrate_Sprites();
            $sql = "SELECT * FROM `" . $wpdb->prefix . "nusprite_versions` WHERE ";
            if (Nusprite::$Sprites) {
                $exclude_current_files = array();
                foreach (Nusprite::$Sprites as $Sprite) {
                    $exclude_current_files[] = $Sprite->sprite_slug . '#' . $Sprite->current_version;
                }
                $sql .= " concat(sprite_slug,'#',version) not in ('" . implode("','", $exclude_current_files) . "')";
            }
            $sql .= " AND DATE_ADD(date_insert, INTERVAL " . $nb_days . " DAY)<now() ORDER BY date_insert DESC LIMIT " . max(1, (int) $nb) . ";";
            $rows = $wpdb->get_results($sql, ARRAY_A);
            if ($rows == false) {
                // finished !
                return true;
            }

            foreach ($rows as $nth => $row) {
                $Nusprite_Version = new Nusprite_Version($row);
                $Nusprite_Version->delete();
            }
            return false;
        }

    }

}