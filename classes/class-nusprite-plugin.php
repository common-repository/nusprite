<?php

if (!class_exists('Nusprite_Plugin')) {

    class Nusprite_Plugin {

        const plugin_version = '0.2';
        const db_version = '0.2';

        static function activation() {
            self::create_table_sprites();
            self::create_table_versions();
            self::create_table_items();
        }

        static function uninstall() {
            delete_option('nusprite_plugin_version');
            delete_option('nusprite_db_version');
            delete_option('nusprite_expiration');
            self::delete_tables();
            self::delete_files();
        }

        static function check_version() {
            if (!get_option('nusprite_plugin_version')) {
                add_option('nusprite_plugin_version', self::plugin_version);
            } elseif (get_option('nusprite_plugin_version') != self::plugin_version) {
                update_option('nusprite_plugin_version', self::plugin_version);
            }
            if (!get_option('nusprite_db_version')) {
                add_option('nusprite_db_version', self::db_version);
            } elseif (get_option('nusprite_db_version') != self::db_version) {
                update_option('nusprite_db_version', self::db_version);
            }
        }

        /*
         * @source https://www.php.net/rmdir
         */

        static function rrmdir($dir) {
            if (is_dir($dir)) {
                $objects = scandir($dir);
                foreach ($objects as $object) {
                    if ($object != "." && $object != "..") {
                        if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . "/" . $object))
                            self::rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                        else
                            unlink($dir . DIRECTORY_SEPARATOR . $object);
                    }
                }
                rmdir($dir);
            }
        }

        static function delete_files() {
            self::rrmdir(Nusprite::get_basedir());
        }

        static function delete_tables() {
            global $wpdb;
            $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "nusprite_sprites;");
            $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "nusprite_versions;");
            $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "nusprite_items;");
        }

        static function create_table_sprites() {
            global $wpdb;
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $table_name = $wpdb->prefix . 'nusprite_sprites';
            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
  id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  sprite_slug varchar(" . Nusprite_Admin::$sprite_slug_max_length . ") NOT NULL,
  custom_post_type varchar(255) NOT NULL,
  post_meta_image varchar(255) NOT NULL,
  max_width_type varchar(255) NOT NULL DEFAULT 'widest',
  max_width_value int(10) UNSIGNED NOT NULL,
  item_image_size varchar(255) NOT NULL DEFAULT 'full',
  security_margins tinyint(4) UNSIGNED NOT NULL DEFAULT '2' COMMENT 'in pixels',  
  versioning tinyint(4) UNSIGNED NOT NULL,
  regenerate_on_save_post tinyint(4) UNSIGNED NOT NULL,
  regenerate_nucss_on_regenerate_nusprite varchar(255) NOT NULL,
  post_meta_credits varchar(255) NOT NULL,
  post_meta_extra varchar(255) NOT NULL,
  bg_preview varchar(255) NOT NULL DEFAULT '#fff',
  current_fingerprint varchar(255) NOT NULL,
  current_version int(10) UNSIGNED NOT NULL DEFAULT '1',
  current_url varchar(255) NOT NULL,
  current_path varchar(3) NOT NULL,
  current_width int(10) UNSIGNED NOT NULL,
  current_height int(10) UNSIGNED NOT NULL,
  current_filesize int(10) UNSIGNED NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY  sprite_slug (sprite_slug)
) " . $wpdb->get_charset_collate() . ";";
            dbDelta($sql);
        }

        static function create_table_versions() {
            global $wpdb;
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $table_name = $wpdb->prefix . 'nusprite_versions';
            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
  sprite_slug varchar(" . Nusprite_Admin::$sprite_slug_max_length . ") NOT NULL,
  version int(10) NOT NULL,
  path varchar(3) NOT NULL,
  filesize int(10) NOT NULL,
  date_insert date NOT NULL,
  PRIMARY KEY  (sprite_slug,version)
) " . $wpdb->get_charset_collate() . ";";
            dbDelta($sql);
        }

        static function create_table_items() {
            global $wpdb;
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $table_name = $wpdb->prefix . 'nusprite_items';
            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
  id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  item_slug varchar(200) NOT NULL,
  sprite_slug varchar(" . Nusprite_Admin::$sprite_slug_max_length . ") NOT NULL,
  post_id int(10) UNSIGNED NOT NULL,
  width int(10) UNSIGNED NOT NULL,
  height int(10) UNSIGNED NOT NULL,
  pos_x int(10) UNSIGNED NOT NULL,
  pos_y int(10) UNSIGNED NOT NULL,
  credits text NOT NULL,
  extra text NOT NULL,
  PRIMARY KEY  (id),
  KEY  (item_slug),
  KEY (sprite_slug)
) " . $wpdb->get_charset_collate() . ";";
            dbDelta($sql);
        }

    }

}