<?php
add_action('load-edit.php', 'Nusprite_Admin::list_breadcrumb');


if (!class_exists('Nusprite_Admin')) {

    require_once('class-nusprite-plugin.php');

    class Nusprite_Admin {

        static $sprite_slug_max_length = 14; // 20 (post_type max-length) - 6 (strlen("nusci_"))

        static function init() {

            load_plugin_textdomain('nusprite', false, dirname(plugin_basename(__FILE__)) . '/../lang/');

            Nusprite_Admin::register_post_type_nusci_xxx();

            Nusprite_Admin::register_cron();

            Nusprite_Admin::trigger_on_save_post();

            add_action('admin_enqueue_scripts', function() {
                wp_enqueue_style('nusprite_admin', plugins_url('/../public/css/nusprite-admin.css', __FILE__), array(), '1.1');
            });

            add_action('edit_form_top', 'Nusprite_Admin::post_breadcrumb');
        }

        static function menu() {

            if (is_super_admin()) {

                add_menu_page('nusprite', 'Sprites', 'manage_options', 'nusprite', 'Nusprite_Admin::router', 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 63 63"><polygon points="7 9, 14 9, 21 29, 21 9, 28 9, 28 54, 21 54, 14 34, 14 54, 7 54" fill="black" /><polygon points="35 27, 35 54, 56 54, 56 27, 49 27, 49 45, 42 45, 42 27" fill="black" /><polygon points="35 9, 42 9, 42 18, 35 18" fill="black" /><polygon points="49 9, 56 9, 56 18, 49 18" fill="black" /></svg>') /*, position */);

                Nusprite::hydrate_Sprites();

                foreach (Nusprite::$Sprites as $sprite_slug => $Sprite) {

                    add_submenu_page('nusprite' /* $parent_slug */, '&bull; ' . $sprite_slug /* $page_title */, '&bull; ' . $sprite_slug /*  $menu_title */, 'read' /* $capability */, 'nusprite-edit-sprite--args_' . $sprite_slug /* $menu_slug */, 'Nusprite_Admin::router' /* $function */, null /* $position */);
                }

                add_submenu_page('nusprite' /* $parent_slug */, __("Create a new sprite image", 'nusprite') /* $page_title */, __("Create a sprite image", 'nusprite') /*  $menu_title */, 'read' /* $capability */, 'nusprite-create-sprite' /* $menu_slug */, 'Nusprite_Admin::router' /* $function */, null /* $position */);

                add_submenu_page('nusprite' /* $parent_slug */, __("Space on disk", 'nusprite') /* $page_title */, __("Space on disk", 'nusprite') /*  $menu_title */, 'read' /* $capability */, 'nusprite-space-on-disk' /* $menu_slug */, 'Nusprite_Admin::router' /* $function */, null /* $position */);

                add_submenu_page('nusprite' /* $parent_slug */, __("Documentation", 'nusprite') /* $page_title */, __("Documentation", 'nusprite') /*  $menu_title */, 'read' /* $capability */, 'nusprite-documentation' /* $menu_slug */, 'Nusprite_Admin::router' /* $function */, null /* $position */);

                add_submenu_page('nusprite' /* $parent_slug */, __("Troubleshooting", 'nusprite') /* $page_title */, __("Troubleshooting", 'nusprite') /*  $menu_title */, 'read' /* $capability */, 'nusprite-troubleshooting' /* $menu_slug */, 'Nusprite_Admin::router' /* $function */, null /* $position */);
                add_submenu_page('nusprite' /* $parent_slug */, __("Uninstall", 'nusprite') /* $page_title */, __("Uninstall", 'nusprite') /*  $menu_title */, 'read' /* $capability */, 'nusprite-uninstall' /* $menu_slug */, 'Nusprite_Admin::router' /* $function */, null /* $position */);
            }
        }

        static function router() {
            if (!is_super_admin()) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }
            $plugin = 'nusprite';
            $args = '';
            if (!isset($_GET['page'])) {
                return;
            }
            if ($_GET['page'] == $plugin) {
                $page = 'home';
            } else {
                $exploded = explode('--args_', substr($_GET['page'], strlen($plugin) + 1));
                $page = $exploded[0];
                if (isset($exploded[1])) {
                    $args = $exploded[1];
                }
            }
            $classname_lowercase = $plugin . '-admin-page-' . $page;
            $filename = 'class-' . $classname_lowercase . '.php';
            if (!file_exists(__DIR__ . '/' . $filename)) {
                return;
            }
            require(__DIR__ . '/' . $filename);
            $classname = implode('_', array_map(function($s) {
                        return ucfirst($s);
                    }, explode('-', $classname_lowercase)));
            if (!class_exists($classname)) {
                return;
            }
            if (!method_exists($classname, '_display')) {
                return;
            }
            call_user_func($classname . '::_display', $args);
        }

        static function register_post_type_nusci_xxx() {
            Nusprite::hydrate_Sprites();
            foreach (Nusprite::$Sprites as $sprite_slug => $Sprite) {
                $labels = array(
                    'name' => _x('Sprite Items', 'nusprite'),
                    'singular_name' => _x('Sprite Item', 'nusprite'),
                    'menu_name' => __('Sprite Items', 'nusprite'),
                    'all_items' => __('All sprite items', 'nusprite'),
                    'view_item' => __('View items', 'nusprite'),
                    'add_new_item' => __('Add New item to sprite', 'nusprite'),
                    'add_new' => __('Add New Item'),
                    'edit_item' => __('Edit sprite item', 'nusprite'),
                    'update_item' => __('Update sprite item', 'nusprite'),
                    'search_items' => __('Search items', 'nusprite'),
                    'not_found' => __('No item found', 'nusprite'),
                    'not_found_in_trash' => __('No item found in trash', 'nusprite'),          
                );

                $args = array(
                    'label' => __('Sprite Item', 'nusprite'),
                    'description' => __('Sprite items', 'nusprite'),
                    'labels' => $labels,
                    'menu_position' => 99999,
                    'supports' => array('title', 'thumbnail'),
                    'show_in_rest' => true,
                    'show_in_menu' => false,
                    'hierarchical' => false,
                    'public' => true,
                    'has_archive' => false,
                );

                register_post_type('nusci_' . $sprite_slug, $args);

                add_action('manage_nusci_' . $sprite_slug . '_posts_columns', 'Nusprite_Admin::manage_nusci_xxx_posts_columns', 5, 2);
                add_action('manage_nusci_' . $sprite_slug . '_posts_custom_column', 'Nusprite_Admin::manage_nusci_xxx_posts_custom_columns', 5, 2);

                add_action('admin_head', function() {
                    global $post_type;
                    if (substr($post_type, 0, 6) == 'nusci_') {
                        echo "<style>#edit-slug-box {display:none;}</style>";
                    }
                });
            }
        }

        static function nag($text, $type = 'warning') {
            if (!in_array($type, array('error', 'warning', 'success'))) {
                $type = 'warning';
            }
            echo '<div class="update-nag notice notice-' . $type . ' displayblock">' . $text . '</div>';
        }

        static function nag_error($text) {
            self::nag($text, 'error');
        }

        static function nag_success($text) {
            self::nag($text, 'success');
        }

        static function manage_nusci_xxx_posts_columns($defaults) {
            $defaults['riv_post_thumbs'] = "Image";
            $defaults['riv_post_slug'] = "ID";
            return $defaults;
        }

        static function manage_nusci_xxx_posts_custom_columns($column_name, $id) {
            $post = get_post($id);
            if ($column_name === 'riv_post_thumbs') {
                $sprite_slug=substr($post->post_type,6);
                $Sprite=Nusprite::get_Sprite($sprite_slug);
///                var_dump($Sprite);
 //               echo $Sprite->bg_preview;
                echo '<div class="nusprite-image-wrapper" style="background:'.$Sprite->bg_preview.'">';
                echo the_post_thumbnail('thumbnail');
                echo '</div>';
            }
            $slug = $post->post_name;
            if ($column_name === 'riv_post_slug') {
                echo $slug;
            }
        }

        static function register_cron() {
            register_activation_hook(__FILE__, function() {
                if (!wp_next_scheduled('my_hourly_event')) {
                    wp_schedule_event(time(), 'hourly', 'my_hourly_event');
                }
            });
            add_action('my_hourly_event', function() {
                require_once('class-nusprite-version.php');
                Nusprite_Version::delete_old_files(10);
            });
        }

        static function trigger_on_save_post() {
            Nusprite::hydrate_Sprites();
            foreach (Nusprite::$Sprites as $Sprite) {
                if ($Sprite->regenerate_on_save_post) {
                    add_action('save_post', function($post_id, $post, $update) use ($Sprite) {
                        if ($update == false) {
                            return;
                        }
                        if ($post->post_type == $Sprite->get_real_post_type()) {
                            $Sprite->regenerate();
                        }
                    }, 10, 3);
                }
            }
        }

        /*
         * @source  https://stackoverflow.com/questions/2510434/format-bytes-to-kilobytes-megabytes-gigabytes
         */

        static function formatBytes($bytes, $precision = 2) {
            $units = array('B', 'KB', 'MB', 'GB', 'TB');

            $bytes = max($bytes, 0);
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);

            // Uncomment one of the following alternatives
            $bytes /= pow(1024, $pow);
            // $bytes /= (1 << (10 * $pow)); 

            return round($bytes, $precision) . ' ' . $units[$pow];
        }

        static function post2sprite($post) {
            global $wpdb;
            if (substr($post->post_type, 0, 6) == 'nusci_') {
                $WHERE = " sprite_slug='" . addslashes(substr($post->post_type, 6)) . "'";
            } else {
                $WHERE = " custom_post_type='" . addslashes($post->post_type) . "'";
            }
            $sprite_slug = $wpdb->get_var("SELECT sprite_slug FROM `" . $wpdb->prefix . "nusprite_sprites` WHERE " . $WHERE . ";");
            return Nusprite::get_Sprite($sprite_slug);
        }

        static function list_breadcrumb() {
            if (is_super_admin() == false) {
                return;
            }
            $screen = get_current_screen();
            // Only edit post screen:
            if (substr($screen->id, 0, 11) == 'edit-nusci_') {
                $sprite_slug = substr($screen->id, 11);
                $Sprite = Nusprite::get_Sprite($sprite_slug);
                if ($Sprite == false) {
                    return;
                }
                // Before:
                add_action('all_admin_notices', function() use ($Sprite) {
                    self::breadcrumb($Sprite);
                });
            }
        }

        static function post_breadcrumb($post) {
            if (is_super_admin() == false) {
                return;
            }
            if (substr($post->post_type, 0, 6) == 'nusci_') {
                $Sprite = self::post2sprite($post);
                if ($Sprite == false) {
                    return;
                }
                self::breadcrumb($Sprite, $post);
            }
        }

        static function breadcrumb($Sprite, $post = false) {
            ?><div class="nusprite-breadcrumb"><?php
                echo '<a href="admin.php?page=nusprite-edit-sprite--args_' . $Sprite->sprite_slug . '">Sprite &laquo;&nbsp;' . $Sprite->sprite_slug . '&nbsp;&raquo;</a> <span class="sep">&gt;</sep> ';
                if ($post == false) {
                    // edit.php?post_type=nusci_xxx
                    echo ' <b>Items</b>';
                } else {
                    // post.php?post=999999&action=edit
                    echo '<a href="edit.php?post_type=nusci_' . $Sprite->sprite_slug . '">Items</a> <span class="sep">&gt;</sep> <b>' . $post->post_title . '</b>';
                }
                ?></div><?php
        }

    }
    
}