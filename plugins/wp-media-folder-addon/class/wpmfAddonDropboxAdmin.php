<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
require_once(WPMFAD_PLUGIN_DIR . '/class/wpmfDropbox.php');
require_once(WPMFAD_PLUGIN_DIR . '/class/wpmfHelper.php');
require_once(WPMFAD_PLUGIN_DIR . '/class/Dropbox/autoload.php');

/**
 * Class WpmfAddonDropboxAdmin
 * This class that holds most of the admin functionality for Dropbox
 */
class WpmfAddonDropboxAdmin extends WpmfAddonDropbox
{

    /**
     * WpmfAddonDropboxAdmin constructor.
     */
    public function __construct()
    {
        parent::__construct();
        if (is_plugin_active('wp-media-folder/wp-media-folder.php')) {
            add_action('admin_menu', array($this, 'addMenuPage'));
            add_action('admin_enqueue_scripts', array($this, 'registerStyleScript'));
            add_action('wp_enqueue_scripts', array($this, 'frontendStyleScript'));
            add_filter('media_upload_tabs', array($this, 'addUploadTab'));
            add_action('media_upload_wpmfdbx', array($this, 'mediaUploadDbx'));
        }

        add_action('wp_ajax_wpmf-get-dropboxfilelist', array($this, 'listDropboxFiles'));
        add_action('wp_ajax_wpmf-dropbox-addfolder', array($this, 'createDropFolder'));
        add_action('wp_ajax_wpmf-dropbox-editfolder', array($this, 'changeDropboxFilename'));
        add_action('wp_ajax_wpmf-dropbox-deletefolder', array($this, 'deleteDropbox'));
        add_action('wp_ajax_wpmf_dropbox_movefile', array($this, 'moveDropboxFile'));
        add_action('wp_ajax_wpmf-dbxupload-file', array($this, 'uploadFile'));
        add_action('wp_ajax_wpmf-dbxdownload-file', array($this, 'downloadFile'));
        add_action('wp_ajax_nopriv_wpmf-dbxdownload-file', array($this, 'downloadFile'));
        add_action('wp_ajax_wpmf-dbx-getThumb', array($this, 'dbxGetThumb'));
        add_action('wp_ajax_wpmf_dbximport_file', array($this, 'importFile'));
        add_action('wp_ajax_wpmf_get-detailFile', array($this, 'getDetailFile'));
        add_action('wp_ajax_wpmf_dropbox_sharefile', array($this, 'dropboxSharefile'));
        add_filter('wpmfaddon_dbxsettings', array($this, 'tabDrive'), 10, 2);
        add_filter('the_content', array($this, 'theContent'));
    }

    /**
     * add script to open video in new window
     * @param string $content content of current post/page
     * @return mixed
     */
    public function theContent($content)
    {
        if (strpos($content, 'wpmf_dbx_video') != false) {
            wp_enqueue_script('wpmf-openwindow');
        }
        return $content;
    }

    /**
     * Dropbox settings html
     * @param $Dropbox WpmfAddonDropbox class
     * @param array $dropboxconfig dropbox config options
     * @return string
     */
    public function tabDrive($Dropbox, $dropboxconfig)
    {
        if ($Dropbox->checkAuth()) {
            try {
                $url = $Dropbox->getAuthorizeDropboxUrl();
            } catch (Exception $e) {
                $url = '';
            }
        }

        ob_start();
        require_once 'templates/settings_dropbox.php';
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    /**
     * add a tab to media menu in iframe
     * @param array $tabs an array of media tabs
     * @return array
     */
    public function addUploadTab($tabs)
    {
        $newtab = array('wpmfdbx' => __('Insert Dropbox File', 'wpmfAddon'));
        return array_merge($tabs, $newtab);
    }

    /**
     * create iframe
     */
    public function mediaUploadDbx()
    {
        $errors = false;
        wp_iframe(array($this, 'mediaUploadDbxForm'), $errors);
    }

    /**
     * load html iframe
     * @param $errors
     */
    public function mediaUploadDbxForm($errors)
    {
        $dropbox = new WpmfAddonDropbox();
        if ($dropbox->checkAuth()) {
            $message = __('The connection to Dropbox is not established,
             you can do that from the WP Media configuration', 'wpmfAddon');
            $link_setting = admin_url('options-general.php?page=option-folder&tab=wpmf-dropbox');
            $link_document = 'https://www.joomunited.com/documentation/93-wp-media-folder-addon-documentation';
            $open_new = true;
            require_once(WPMFAD_PLUGIN_DIR . '/class/templates/error_message.php');
        } else {
            $this->loadStyleScript();
            $mediatype = 'dropbox';
            require_once(WPMFAD_PLUGIN_DIR . '/class/templates/listfiles.php');
        }
    }

    /**
     * load style and script
     */
    public function loadStyleScript()
    {
        wp_enqueue_style('wpmf-google-icon');
        wp_enqueue_style('wpmf-css-font-material-design');
        wp_enqueue_style('wpmf-css-googlefile');
        wp_enqueue_style('wpmf-css-popup');
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-droppable');
        wp_enqueue_script('wpmf-loaddropboxfile');
        wp_enqueue_script('wpmf-imagesloaded');
        wp_enqueue_script('wpmf-popup');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_style('wpmf-css-dialogs');

        wp_enqueue_script('jQuery.fileupload');
        wp_enqueue_script('jQuery.fileupload-process');
        wp_enqueue_style('wpmf-fileupload-jquery-ui');
        wp_enqueue_style('wpmftree');
    }

    /**
     * Load scripts and style
     */
    public function registerStyleScript()
    {
        wp_register_script(
            'wpmf-imagesloaded',
            plugins_url('/assets/js/imagesloaded.pkgd.min.js', dirname(__FILE__)),
            array(),
            '3.1.5',
            true
        );
        wp_register_script(
            'wpmf-popup',
            plugins_url('/assets/js/jquery.magnific-popup.min.js', dirname(__FILE__)),
            array('jquery'),
            '0.9.9',
            true
        );
        wp_register_script(
            'wpmf-loaddropboxfile',
            plugins_url('/assets/js/loaddropboxfile.js', dirname(__FILE__)),
            array('jquery'),
            WPMFAD_VERSION
        );
        wp_register_script(
            'jQuery.fileupload',
            plugins_url('/assets/js/fileupload/jquery.fileupload.js', dirname(__FILE__)),
            array('jquery'),
            false,
            true
        );
        wp_register_script(
            'jQuery.fileupload-process',
            plugins_url('/assets/js/fileupload/jquery.fileupload-process.js', dirname(__FILE__)),
            array('jquery'),
            false,
            true
        );
        wp_register_style(
            'wpmf-css-googlefile',
            plugins_url('/assets/css/style.css', dirname(__FILE__)),
            array(),
            WPMFAD_VERSION
        );
        wp_register_style(
            'wpmf-css-font-material-design',
            plugins_url('/assets/css/material-design-iconic-font.min.css', dirname(__FILE__)),
            array(),
            WPMFAD_VERSION
        );
        wp_register_style(
            'wpmf-css-popup',
            plugins_url('/assets/css/magnific-popup.css', dirname(__FILE__)),
            array(),
            '0.9.9'
        );
        wp_register_style(
            'wpmf-css-dialogs',
            plugins_url('/assets/css/jquery-ui-1.10.3.custom.css', dirname(__FILE__)),
            array(),
            '1.10.3'
        );
        wp_register_style(
            'wpmftree',
            plugins_url('/assets/css/jaofiletree.css', dirname(__FILE__)),
            array(),
            WPMFAD_VERSION
        );
        wp_register_style(
            'wpmf-fileupload-jquery-ui',
            plugins_url('/assets/css/jquery.fileupload-ui.css', dirname(__FILE__))
        );
        wp_localize_script('wpmf-loaddropboxfile', 'wpmfaddonparams', $this->localizeScript());
    }

    /**
     * Load scripts on frontend
     */
    public function frontendStyleScript()
    {
        wp_register_script(
            'wpmf-openwindow',
            plugins_url('/assets/js/frontend_openwindow.js', dirname(__FILE__)),
            array(),
            WPMFAD_VERSION,
            true
        );
        wp_localize_script('wpmf-openwindow', 'wpmfaddonlang', array(
            'wpmf_images_path' => plugins_url('assets/images', dirname(__FILE__)),
            'ajaxurl' => admin_url('admin-ajax.php')
        ));
    }

    /**
     * Localize a script.
     * Works only if the script has already been added.
     * @return array
     */
    public function localizeScript()
    {
        $wpmfAddon_dropbox_config = get_option('_wpmfAddon_dropbox_config');
        if (!empty($wpmfAddon_dropbox_config['dropboxToken'])) {
            $dropboxToken = $wpmfAddon_dropbox_config['dropboxToken'];
        } else {
            $dropboxToken = '';
        }
        return array(
            'plugin_url' => WPMFAD_PLUGIN_URL,
            'img_path' => WPMFAD_PLUGIN_URL . 'assets/images/',
            'plugin_url_icon' => plugins_url('/assets/images/icons/', dirname(__FILE__)),
            'dropboxToken' => $dropboxToken,
            'newfolder' => __('New Folder', 'wpmfAddon'),
            'addfolder' => __('Add Folder', 'wpmfAddon'),
            'editfolder' => __('Change Filename', 'wpmfAddon'),
            'cancelfolder' => __('Cancel', 'wpmfAddon'),
            'promt' => __('Please give a name to this new folder', 'wpmfAddon'),
            'save' => __('Save', 'wpmfAddon'),
            'delete' => __('Delete', 'wpmfAddon'),
            'deletefolder' => __('Delete Folder', 'wpmfAddon'),
            'upload_nonce' => wp_create_nonce("wpmf-upload-file"),
            'maxNumberOfFiles' => __('Maximum number of files exceeded', 'wpmfAddon'),
            'acceptFileTypes' => __('File type not allowed', 'wpmfAddon'),
            'maxFileSize' => __('File is too large', 'wpmfAddon'),
            'minFileSize' => __('File is too small', 'wpmfAddon'),
            'str_inqueue' => __('In queue', 'wpmfAddon'),
            'str_uploading_local' => __('Uploading to Server', 'wpmfAddon'),
            'str_uploading_cloud' => __('Uploading', 'wpmfAddon'),
            'str_success' => __('Success', 'wpmfAddon'),
            'str_error' => __('Error', 'wpmfAddon'),
            'str_message_delete' => __('These items will be permanently deleted and
             cannot be recovered. Are you sure?', 'wpmfAddon'),
            'maxsize' => 104857600,
            'media_folder' => __('Media Library', 'wpmfAddon'),
            'message_import' => __('Files imported with success!', 'wpmfAddon')
        );
    }

    /**
     * add menu media page
     */
    public function addMenuPage()
    {
        add_media_page('Dropbox', 'Dropbox', 'activate_plugins', 'wpmf-dropbox-page', array($this, 'showDropboxFile'));
    }

    /**
     * Google drive page
     */
    public function showDropboxFile()
    {
        $dropbox = new WpmfAddonDropbox();
        if ($dropbox->checkAuth()) {
            $message = __('The connection to Dropbox is not established,
             you can do that from the WP Media configuration', 'wpmfAddon');
            $link_setting = admin_url('options-general.php?page=option-folder&tab=wpmf-dropbox');
            $link_document = 'https://www.joomunited.com/documentation/93-wp-media-folder-addon-documentation';
            $open_new = false;
            require_once(WPMFAD_PLUGIN_DIR . '/class/templates/error_message.php');
        } else {
            if (isset($_GET['noheader'])) {
                _wp_admin_html_begin();
                global $hook_suffix;
                do_action('admin_enqueue_scripts', $hook_suffix);
                do_action("admin_print_scripts-$hook_suffix");
                do_action('admin_print_scripts');
                ?>
                <style>
                    #wpfooter {
                        display: none;
                    }
                </style>
                <?php
            }

            $this->loadStyleScript();
            $mediatype = 'dropbox';
            require_once(WPMFAD_PLUGIN_DIR . '/class/templates/listfiles.php');
        }
    }

    /**
     * Logout dropbox app
     */
    public function dbxLogout()
    {
        $dropbox = new WpmfAddonDropbox();
        $dropbox->logout();
    }
}
