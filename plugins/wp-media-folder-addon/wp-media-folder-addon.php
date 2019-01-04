<?php
/*
  Plugin Name: WP Media folder Addon
  Plugin URI: http://www.joomunited.com
  Description: WP media Folder Addon is a WordPress plugin that enhance
 the WordPress media manager by adding a folder manager inside.
  Author: Joomunited
  Version: 2.1.0
  Text Domain: wpmfAddon
  Domain Path: /languages
  Author URI: http://www.joomunited.com
  Licence : GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
  Copyright : Copyright (C) 2014 JoomUnited (http://www.joomunited.com). All rights reserved.
 */
// Prohibit direct script loading
defined('ABSPATH') || die('No direct script access allowed!');
//Check plugin requirements
if (version_compare(PHP_VERSION, '5.3', '<')) {
    if (!function_exists('wpmf_addon_disable_plugin')) {
        function wpmf_addon_disable_plugin()
        {
            if (current_user_can('activate_plugins') && is_plugin_active(plugin_basename(__FILE__))) {
                deactivate_plugins(__FILE__);
                unset($_GET['activate']);
            }
        }
    }

    if (!function_exists('wpmf_addon_show_error')) {
        function wpmf_addon_show_error()
        {
            echo '<div class="error"><p><strong>WP Media Folder Addon</strong>
 need at least PHP 5.3 version, please update php before installing the plugin.</p></div>';
        }
    }

    //Add actions
    add_action('admin_init', 'wpmf_addon_disable_plugin');
    add_action('admin_notices', 'wpmf_addon_show_error');

    //Do not load anything more
    return;
}

//JUtranslation
add_filter('wpmf_get_addons', function ($addons) {
    $addon = new stdClass();
    $addon->main_plugin_file = __FILE__;
    $addon->extension_name = 'WP Media Folder Addon';
    $addon->extension_slug = 'wpmf-addon';
    $addon->text_domain = 'wpmfAddon';
    $addon->language_file = plugin_dir_path(__FILE__) . 'languages' . DIRECTORY_SEPARATOR . 'wpmfAddon-en_US.mo';
    $addons[$addon->extension_slug] = $addon;
    return $addons;
});


add_action('init', function () {
    load_plugin_textdomain(
        'wpmfAddon',
        false,
        dirname(plugin_basename(__FILE__)) . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR
    );
}, 1);

if (!defined('WPMFAD_PLUGIN_DIR')) {
    define('WPMFAD_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

define('WPMFAD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPMFAD_URL', plugin_dir_url(__FILE__));
define('WPMFAD_VERSION', '2.1.0');
require_once(ABSPATH . 'wp-admin/includes/plugin.php');
require_once(WPMFAD_PLUGIN_DIR . '/class/wpmfAddonGoogleAdmin.php');
$wpmfgoogleaddon = new WpmfAddonGoogle;
require_once(WPMFAD_PLUGIN_DIR . '/class/wpmfAddonDropboxAdmin.php');
$wpmfdropboxaddon = new WpmfAddonDropboxAdmin;
require_once(WPMFAD_PLUGIN_DIR . '/class/wpmfAddonOneDriveAdmin.php');
$wpmfonedriveaddon = new WpmfAddonOneDriveAdmin;

require_once(WPMFAD_PLUGIN_DIR . '/class/wpmfOneDrive.php');
$onedriveDrive = new WpmfAddonOneDrive();
if (!empty($_GET['code'])) {
    $createToken = $onedriveDrive->createToken($_GET['code']);
}

if (isset($_GET['task']) && $_GET['task'] == 'wpmf') {
    if (isset($_GET['function'])) {
        switch ($_GET['function']) {
            case 'wpmf_authenticated':
                $wpmfgoogleaddon->ggAuthenticated();
                break;

            case 'wpmf_gglogout':
                $wpmfgoogleaddon->ggLogout();
                break;

            case 'wpmf_dropboxlogout':
                $wpmfdropboxaddon->dbxLogout();
                break;
        }
    }
}

//config section        
if (!defined('JU_BASE')) {
    define('JU_BASE', 'https://www.joomunited.com/');
}

$remote_updateinfo = JU_BASE . 'juupdater_files/wp-media-folder-addon.json';
//end config

require 'juupdater/juupdater.php';
$UpdateChecker = Jufactory::buildUpdateChecker(
    $remote_updateinfo,
    __FILE__
);
