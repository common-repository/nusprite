<?php

/*
  Plugin Name: nusprite
  Description: Sprite CSS generator
  Version: 0.2
  Author: nuweb
 */

defined('ABSPATH') or die('No script kiddies please!');

require_once('classes/class-nusprite.php');
Nusprite::init();
if (is_admin()) {
    require_once('classes/class-nusprite-plugin.php');
    require_once('classes/class-nusprite-admin.php');
    register_activation_hook(__FILE__, 'Nusprite_Plugin::activation');
    register_uninstall_hook(__FILE__, 'Nusprite_Plugin::uninstall');
    Nusprite_Plugin::check_version();
    add_action('init', function() {
        Nusprite_Admin::init();
    });
    add_action('admin_menu', function() {
        Nusprite_Admin::menu();
    });
}
