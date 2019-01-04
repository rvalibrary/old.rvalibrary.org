<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
require_once(WPMFAD_PLUGIN_DIR . '/class/wpmfOneDrive.php');
require_once(WPMFAD_PLUGIN_DIR . '/class/wpmfHelper.php');
require_once(WPMFAD_PLUGIN_DIR . '/class/OneDrive/autoload.php');

/**
 * Class WpmfAddonOneDriveAdmin
 * This class that holds most of the admin functionality for OneDrive
 */
class WpmfAddonOneDriveAdmin extends WpmfAddonOneDrive
{

    /**
     * WpmfAddonOneDriveAdmin constructor.
     */
    public function __construct()
    {
        if (is_plugin_active('wp-media-folder/wp-media-folder.php')) {
            add_action('admin_menu', array($this, 'addMenuPage'));
            add_action('admin_enqueue_scripts', array($this, 'registerStyleScript'));
            add_action('wp_enqueue_scripts', array($this, 'frontendStyleScript'));
            add_filter('media_upload_tabs', array($this, 'addUploadTab'));
            add_action('media_upload_wpmfodv', array($this, 'mediaUpload'));
        }

        add_action('wp_ajax_wpmf_get_onedrive_filelist', array($this, 'getOnedriveFilelist'));
        add_filter('wpmfaddon_onedrivesettings', array($this, 'tabOnedrive'), 10, 2);
        add_action('wp_ajax_wpmf_onedrive_logout', array($this, 'onedriveLogout'));
        add_action('wp_ajax_wpmf_onedrive_addfolder', array($this, 'ajaxcreateFolder'));
        add_action('wp_ajax_wpmf_onedrive_edit', array($this, 'changeFilename'));
        add_action('wp_ajax_wpmf_onedrive_deletefolder', array($this, 'deleteItem'));
        add_action('wp_ajax_wpmf_onedrive_move_file', array($this, 'moveItem'));
        add_action('wp_ajax_wpmf_onedrive_upload_file', array($this, 'uploadFile'));
        add_action('wp_ajax_wpmf_onedrive_import_file', array($this, 'importFile'));
        add_action('wp_ajax_wpmf_onedrive_download', array($this, 'downloadFile'));
        add_action('wp_ajax_nopriv_wpmf_onedrive_download', array($this, 'downloadFile'));
        add_action('wp_ajax_wpmf_onedrive_preview', array($this, 'previewFile'));
        add_action('wp_ajax_wpmf_get_embed_file', array($this, 'getEmbedFile'));
        add_filter('the_content', array($this, 'theContent'));
    }

    /**
     * add script to open video in new window
     * @param string $content content of current post/page
     * @return mixed
     */
    public function theContent($content)
    {
        if (strpos($content, 'wpmf_odv_video') != false) {
            wp_enqueue_script('wpmf-openwindow');
        }
        return $content;
    }

    /**
     * Load scripts
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
     * add a tab to media menu in iframe
     * @param array $tabs an array of media tabs
     * @return array
     */
    public function addUploadTab($tabs)
    {
        $newtab = array('wpmfodv' => __('Insert OneDrive File', 'wpmfAddon'));
        return array_merge($tabs, $newtab);
    }

    /**
     * create iframe
     */
    public function mediaUpload()
    {
        $errors = false;
        wp_iframe(array($this, 'mediaUploadForm'), $errors);
    }

    /**
     * load html iframe
     * @param $errors
     */
    public function mediaUploadForm($errors)
    {
        $onedriveDrive = new WpmfAddonOneDrive();
        $onedrive_config = get_option('_wpmfAddon_onedrive_config');
        if (isset($onedrive_config['connected']) && $onedrive_config['connected'] == 1) {
            $this->loadStyleScript();
            $mediatype = 'onedrive';
            require_once(WPMFAD_PLUGIN_DIR . '/class/templates/listfiles.php');
        } else {
            $message = __('The connection to OneDrive is not established,
             you can do that from the WP Media configuration', 'wpmfAddon');
            $link_setting = admin_url('options-general.php?page=option-folder&tab=wpmf-onedrive');
            $link_document = 'https://www.joomunited.com/documentation/93-wp-media-folder-addon-documentation';
            $open_new = false;
            require_once(WPMFAD_PLUGIN_DIR . '/class/templates/error_message.php');
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
        wp_enqueue_script('wpmf-loadonedrivefile');
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
        wp_register_style('wpmf-google-icon', 'https://fonts.googleapis.com/icon?family=Material+Icons');
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
            'wpmf-loadonedrivefile',
            plugins_url('/assets/js/loadonedrivefile.js', dirname(__FILE__)),
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
        wp_localize_script('wpmf-loadonedrivefile', 'wpmfonedriveparams', $this->localizeScript());
    }

    /**
     * Localize a script
     * @return array
     */
    public function localizeScript()
    {
        $onedrive_config = get_option('_wpmfAddon_onedrive_config');
        if (isset($onedrive_config['onedriveBaseFolder']) && isset($onedrive_config['onedriveBaseFolder']['id'])) {
            $onedriveBaseFolder = $onedrive_config['onedriveBaseFolder']['id'];
        } else {
            $onedriveBaseFolder = 'root';
        }
        return array(
            'onedriveBaseFolder' => $onedriveBaseFolder,
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
            'plugin_url' => plugins_url('/assets/images/icons/', dirname(__FILE__)),
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
        add_media_page(
            'OneDrive',
            'OneDrive',
            'activate_plugins',
            'wpmf-onedrive-page',
            array($this, 'showOneDriveFile')
        );
    }

    /**
     * Google drive page
     */
    public function showOneDriveFile()
    {
        $onedriveDrive = new WpmfAddonOneDrive();
        $onedrive_config = get_option('_wpmfAddon_onedrive_config');
        if (isset($onedrive_config['connected']) && $onedrive_config['connected'] == 1) {
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
            $mediatype = 'onedrive';
            require_once(WPMFAD_PLUGIN_DIR . '/class/templates/listfiles.php');
        } else {
            $message = __('The connection to OneDrive is not established,
             you can do that from the WP Media configuration', 'wpmfAddon');
            $link_setting = admin_url('options-general.php?page=option-folder&tab=wpmf-onedrive');
            $link_document = 'https://www.joomunited.com/documentation/93-wp-media-folder-addon-documentation';
            $open_new = false;
            require_once(WPMFAD_PLUGIN_DIR . '/class/templates/error_message.php');
        }
    }

    /**
     * Onedrive settings html
     * @return string
     */
    public function tabOnedrive()
    {
        $onedriveDrive = new WpmfAddonOneDrive();
        $onedriveconfig = get_option('_wpmfAddon_onedrive_config');
        if (empty($onedriveconfig)) {
            $onedriveconfig = array('OneDriveClientId' => '', 'OneDriveClientSecret' => '');
        }

        ob_start();
        require_once 'templates/settings_onedrive.php';
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    /**
     * Logout Onedrive app
     */
    public function onedriveLogout()
    {
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        $params = get_option('_wpmfAddon_onedrive_config');
        $params['connected'] = 0;
        update_option('_wpmfAddon_onedrive_config', $params);
        wp_send_json(array('status' => true));
    }

    /**
     * Ajax get list files
     */
    public function getOnedriveFilelist()
    {
        if (!current_user_can('upload_files')) {
            wp_send_json(array('status' => false));
        }
        $params = get_option('_wpmfAddon_onedrive_config');
        if (isset($_POST['folderID'])) {
            $folderID = $_POST['folderID'];
        } else {
            $folderID = $params['onedriveBaseFolder']['id'];
        }

        $hardrefresh = (isset($_REQUEST['hardrefresh']) && $_REQUEST['hardrefresh'] == 'true') ? true : false;
        $check_root = $this->getRootFolder($params['onedriveBaseFolder']['id']);
        if (!$check_root) {
            $params = get_option('_wpmfAddon_onedrive_config');
            if (!empty($params['current_token'])) {
                $params['current_token'] = '';
            }

            if (!empty($params['refresh_token'])) {
                $params['refresh_token'] = '';
            }

            if (!empty($params['onedriveBaseFolder'])) {
                $params['onedriveBaseFolder'] = array();
            }
            $params['connected'] = 0;
            update_option('_wpmfAddon_onedrive_config', $params);
            wp_send_json(array('status' => false));
        }
        $searchfilename = (!empty($_REQUEST['searchfilename'])) ? $_REQUEST['searchfilename'] : '';
        if (!empty($params['onedriveBaseFolder']['id'])) {
            $folders = $this->getFolder(false, $folderID, $hardrefresh, true, $searchfilename);
        } else {
            $folders = $this->getFolder(false, false, $hardrefresh, true, $searchfilename);
        }
        $foldersarray = array();
        $filesarray = array();
        foreach ($folders['contents'] as $child) {
            $is_dir = ($child->getFolder() !== null) ? true : false;
            if ($is_dir) {
                array_push($foldersarray, $child);
            } else {
                array_push($filesarray, $child);
            }
        }

        // sort items
        $foldersarray = $this->subValSort($foldersarray, 'name', $_POST['sortfilename']);
        $filesarray = $this->subValSort($filesarray, 'name', $_POST['sortfilename']);
        $parent = $folders['parent'];
        $html = '';
        ob_start();
        if ($_POST['folderID'] != $params['onedriveBaseFolder']['id']) {
            $thumbnail_pre = '<i class="material-icons wpmf_skip_previous">skip_previous</i>';
            require(WPMFAD_PLUGIN_DIR . '/class/templates/htmlprevious.php');
        }
        $thumbnail_newfolder = '<i class="material-icons wpmf_create_new_folder">create_new_folder</i>';
        require(WPMFAD_PLUGIN_DIR . '/class/templates/htmladdfolder.php');
        $files = array();
        $videofiles = array();
        $type = '';
        $display_preview = 1;
        foreach ($foldersarray as $child) {
            $id = $child->id;
            $name = $child->name;
            $infofile = pathinfo($name);
            if (isset($infofile['extension'])) {
                $extension = $infofile['extension'];
            } else {
                $extension = '';
            }
            $thumbnail = '<i class="material-icons wpmf_icon_folder">folder</i>';
            require(WPMFAD_PLUGIN_DIR . '/class/templates/htmlfolder.php');
        }
        $display_preview = 1;
        foreach ($filesarray as $child) {
            $is_dir = ($child->getFolder() !== null) ? true : false;
            $id = $child->id;
            $name = $child->name;
            $downloadlink = admin_url('admin-ajax.php') . "?
            action=wpmf_onedrive_download&id=" . urlencode($id) . "&link=true&dl=1";

            /* Set extension Mimetype */
            $extension = false;
            $mimeType = false;
            $infofile = pathinfo($child->getName());
            if ((!$is_dir) && isset($infofile['extension'])) {
                include_once 'includes/mime-types.php';
                $extension = $infofile['extension'];
                $mimeType = getMimeType($infofile['extension']);
            }

            $thumbnails = $this->getListThumbnail($child, $mimeType);
            $thumbnail = $thumbnails->getMedium()->getUrl();
            $openwithonedrive = (!in_array(
                strtolower($extension),
                array(
                    'jpg', 'jpeg', 'gif', 'png',
                    'ace', 'arj', 'bz2', 'cab',
                    'gzip', 'iso', 'jar', 'lzh',
                    'tar', 'uue', 'xz', 'z',
                    '7-zip', 'x-rar', 'rar', 'zip'
                )
            ));
            if ($openwithonedrive) {
                $type = 'iframe';
                if ($this->isMediaFile($extension)) {
                    $lightboxlink = admin_url('admin-ajax.php') . "?
                    action=wpmf_onedrive_preview&id=" . urlencode($id) . "&
                    openwithonedrive=1&mimetype=" . $mimeType . "&ext=" . $extension;
                } elseif ($extension == 'pdf') {
                    $type = 'pdf';
                    $lightboxlink = $downloadlink;
                    $display_preview = 0;
                } else {
                    $lightboxlink = admin_url('admin-ajax.php') . "?
                    action=wpmf_onedrive_preview&id=" . urlencode($id) . "&
                    openwithonedrive=1&mimetype=" . $mimeType . "&ext=" . $extension;
                }

                if ($this->isVideoFile($extension)) {
                    $type = 'video';
                }
                $embedlink = $lightboxlink;
            } elseif (in_array($extension, array('jpg', 'jpeg', 'gif', 'png'))) {
                $type = 'image';
                $lightboxlink = $thumbnails->getLarge()->getUrl();
                $embedlink = $lightboxlink;
            } else {
                $type = 'download';
                $lightboxlink = '#';
                $embedlink = $lightboxlink;
                $display_preview = 0;
            }

            $files[$id] = array(
                'title' => $infofile['filename'],
                'type_insert' => $type,
                'src' => $embedlink,
                'lightboxlink' => $lightboxlink
            );
            $checktype = '';
            if ($type == 'video') {
                $checktype = 'onedrive_video';
                $files[$id]['thumbnail'] = $thumbnails->getLarge()->getUrl();
                $videofiles[] = $id;
            }
            require(WPMFAD_PLUGIN_DIR . '/class/templates/htmlfile.php');
        }
        $html = ob_get_contents();
        ob_end_clean();
        wp_send_json(
            array(
                'status' => true,
                'message' => $html,
                'files' => $files,
                'breadcrumb' => $folders['breadcrumb'],
                'videofiles' => $videofiles
            )
        );
    }

    /**
     * Set default thumbnail if needed
     * @param object $child current file
     * @param string $mimeType mime type of file
     * @return array|mixed
     */
    public function getListThumbnail($child, $mimeType)
    {
        $thumbnailicon = $this->getThumbnail($mimeType);
        $urlsmall = new OneDrive_Service_Drive_Thumbnail();
        $urlsmall->setUrl($thumbnailicon);
        $urlmedium = new OneDrive_Service_Drive_Thumbnail();
        $urlmedium->setUrl($thumbnailicon);
        $url = new OneDrive_Service_Drive_ThumbnailSet();
        $url->setSmall($urlsmall);
        $url->setMedium($urlmedium);
        $url->setLarge($urlmedium);

        $urls = array($url);
        if ($child->getThumbnails() !== null && count($child->getThumbnails()) > 0) {
            $urls = $child->getThumbnails();
        }
        $urls = reset($urls);
        return $urls;
    }

    /**
     * Check media file
     * @param string $ext extension of file
     * @return bool
     */
    public function isMediaFile($ext)
    {
        $media_arr = array('mid', 'midi', 'mp2', 'mp3', 'mpga', 'ram', 'rm', 'rpm', 'ra', 'wav', //,'aif','aifc','aiff'
            'wmv', 'mp4', 'mpeg', 'mpe', 'mpg', 'mov', 'qt', 'rv', 'avi', 'movie', 'flv', 'webm', 'ogv', //'3gp',
            'jpg', 'png', 'gif', 'jpeg', 'jpe', 'bmp', 'ico', 'tiff', 'tif', 'svg', 'svgz');
        if (in_array($ext, $media_arr)) {
            return true;
        }
        return false;
    }

    /**
     * Check video file
     * @param string $ext extension of file
     * @return bool
     */
    public function isVideoFile($ext)
    {
        $media_arr = array(
            'mp3', 'wmv', 'mp4',
            'mpeg', 'mpe', 'mpg',
            'mov', 'qt', 'rv',
            'avi', 'movie', 'flv',
            'webm', 'ogv'
        );
        if (in_array($ext, $media_arr)) {
            return true;
        }
        return false;
    }

    /**
     * Get Thumbnail
     * @param string $mimetype mime type of file
     * @return string
     */
    public function getThumbnail($mimetype)
    {
        switch ($mimetype) {
            case 'application/ace':
            case 'application/arj':
            case 'application/bz2':
            case 'application/cab':
            case 'application/gzip':
            case 'application/iso':
            case 'application/jar':
            case 'application/lzh':
            case 'application/tar':
            case 'application/uue':
            case 'application/xz':
            case 'application/z':
            case 'application/7-zip':
            case 'application/x-rar':
            case 'application/rar':
            case 'application/zip':
                $thumbnailicon = 'archive.png';
                break;
            case 'application/vnd.google-apps.folder':
                $thumbnailicon = 'folder.png';
                break;
            case 'audio/mp3':
            case 'application/vnd.google-apps.audio':
            case 'audio/mpeg':
                $thumbnailicon = 'audio.png';
                break;
            case 'application/vnd.google-apps.document':
            case 'application/vnd.oasis.opendocument.text':
            case 'text/plain':
                $thumbnailicon = 'document.png';
                break;
            case 'application/vnd.google-apps.drawing':
                $thumbnailicon = 'drawing.png';
                break;
            case 'application/vnd.google-apps.form':
                $thumbnailicon = 'form.png';
                break;
            case 'application/vnd.google-apps.fusiontable':
                $thumbnailicon = 'table.png';
                break;
            case 'application/vnd.google-apps.photo':
            case 'image/jpeg':
            case 'image/png':
            case 'image/gif':
            case 'image/bmp':
                $thumbnailicon = 'image.png';
                break;
            case 'application/vnd.google-apps.presentation':
            case 'application/vnd.oasis.opendocument.presentation':
                $thumbnailicon = 'presentation.png';
                break;
            case 'application/vnd.google-apps.script':
            case 'application/x-httpd-php':
            case 'text/js':
                $thumbnailicon = 'script.png';
                break;
            case 'application/vnd.google-apps.sites':
                $thumbnailicon = 'sites.png';
                break;
            case 'application/vnd.google-apps.spreadsheet':
            case 'application/vnd.oasis.opendocument.spreadsheet':
                $thumbnailicon = 'spreadsheet.png';
                break;
            case 'application/vnd.google-apps.video':
                $thumbnailicon = 'video.png';
                break;

            case 'application/vnd.ms-excel':
            case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                $thumbnailicon = 'excel.png';
                break;
            case 'application/msword':
                $thumbnailicon = 'word.png';
                break;


            case 'application/pdf':
                $thumbnailicon = 'pdf.png';
                break;
            default:
                $thumbnailicon = 'generic.png';
                break;
        }

        $thumbnail = WPMFAD_PLUGIN_URL . '/assets/images/icons/' . $thumbnailicon;
        return $thumbnail;
    }
}
