<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class WpmfAddonDropbox
 * This class that holds most of the admin functionality for Dropbox
 */
class WpmfAddonDropbox
{
    protected $params;

    protected $appName = 'WpmfAddon/1.0';
    protected $lastError;

    /**
     * WpmfAddonDropbox constructor.
     */
    public function __construct()
    {
        set_include_path(__DIR__ . PATH_SEPARATOR . get_include_path());
        require_once 'Dropbox/autoload.php';
        $this->loadParams();
    }

    /**
     * @return mixed
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Get dropbox config by name
     * @param string $name name of option
     * @return array|null
     */
    public function getDataConfigByDropbox($name)
    {
        return wpmfAddonHelper::getDataConfigByDropbox($name);
    }

    /**
     * get dropbox config
     * @return mixed
     */
    public function getAllDropboxConfigs()
    {
        return wpmfAddonHelper::getAllDropboxConfigs();
    }

    /**
     * save dropbox config
     * @param $data datas value
     * @return bool
     */
    public function saveDropboxConfigs($data)
    {
        return wpmfAddonHelper::saveDropboxConfigs($data);
    }

    /**
     * load parameters
     */
    protected function loadParams()
    {
        $params = $this->getDataConfigByDropbox('dropbox');

        $this->params = new stdClass();

        $this->params->dropboxKey = $params['dropboxKey'];
        $this->params->dropboxSecret = $params['dropboxSecret'];
        $this->params->dropboxToken = isset($params['dropboxToken']) ? $params['dropboxToken'] : "";
    }

    /**
     * save parameters
     */
    protected function saveParams()
    {
        $params = $this->getAllDropboxConfigs();
        $params['dropboxKey'] = $this->params->dropboxKey;
        $params['dropboxSecret'] = $this->params->dropboxSecret;
        $params['dropboxToken'] = $this->params->dropboxToken;
        $this->saveDropboxConfigs($params);
    }

    /**
     * @return \WPMFDropbox\WebAuthNoRedirect
     */
    public function getWebAuth()
    {
        $dropboxKey = "";
        $dropboxSecret = 'dropboxSecret';
        if (!empty($this->params->dropboxKey)) {
            $dropboxKey = $this->params->dropboxKey;
        }
        if (!empty($this->params->dropboxSecret)) {
            $dropboxSecret = $this->params->dropboxSecret;
        }

        $appInfo = new WPMFDropbox\AppInfo($dropboxKey, $dropboxSecret);
        $webAuth = new WPMFDropbox\WebAuthNoRedirect($appInfo, $this->appName);

        return $webAuth;
    }

    /**
     * get author Url allow user
     * @return string
     */
    public function getAuthorizeDropboxUrl()
    {
        $authorizeUrl = $this->getWebAuth()->start();

        return $authorizeUrl;
    }

    /**
     * Convert the authorization code into an access token
     * @param string $authCode authorization code
     * @return array
     */
    public function convertAuthorizationCode($authCode)
    {
        $list = array();
        list($accessToken, $dropboxUserId) = $this->getWebAuth()->finish($authCode);
        $list = array('accessToken' => $accessToken,
            'dropboxUserId' => $dropboxUserId
        );
        return $list;
    }

    /**
     * check Author
     * @return bool
     */
    public function checkAuth()
    {
        $dropboxToken = $this->params->dropboxToken;
        if (!empty($dropboxToken)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Logout dropbox app
     */
    public function logout()
    {
        $params = $this->getAllDropboxConfigs();
        $params['dropboxKey'] = $this->params->dropboxKey;
        $params['dropboxSecret'] = $this->params->dropboxSecret;
        $params['dropboxAuthor'] = '';
        $params['dropboxToken'] = '';
        $this->saveDropboxConfigs($params);
        $this->redirect(admin_url('options-general.php?page=option-folder&tab=wpmf-dropbox'));
    }

    /**
     * get dropbox client
     * @return \WPMFDropbox\Client
     */
    public function getAccount()
    {
        $wpmfAddon_dropbox_config = get_option('_wpmfAddon_dropbox_config');
        $dropboxToken = $wpmfAddon_dropbox_config['dropboxToken'];
        $dbxClient = new WPMFDropbox\Client($dropboxToken, $this->appName);
        return $dbxClient;
    }

    /**
     * Create Folder to dropbox
     * @throws \WPMFDropbox\Exception_BadResponseCode
     * @throws \WPMFDropbox\Exception_InvalidAccessToken
     * @throws \WPMFDropbox\Exception_RetryLater
     * @throws \WPMFDropbox\Exception_ServerError
     */
    public function createDropFolder()
    {
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        if (isset($_POST['path'])) {
            if (isset($_POST['title']) && $_POST['title'] != '') {
                $title = urldecode($_POST['title']);
            } else {
                $title = __('New Folder', 'wpmfAddon');
            }
            $dropbox = $this->getAccount();
            try {
                $path = $_POST['path'] . '/' . $title;
                $result = $dropbox->createFolder($path);
            } catch (Exception $e) {
                $path = $_POST['path'] . '/' . $title . '-' . time();
                $result = $dropbox->createFolder($path);
            }

            ob_start();
            $id = $result['path_display'];
            $name = $result['name'];
            $infofile = pathinfo($id);
            if (isset($infofile['extension'])) {
                $extension = $infofile['extension'];
            } else {
                $extension = '';
            }
            $thumbnail = '<img class="" src="' . WPMFAD_PLUGIN_URL . '/assets/images/icons/dropbox_folder.png">';
            require(WPMFAD_PLUGIN_DIR . '/class/templates/htmlfolder.php');
            $html = ob_get_contents();
            ob_end_clean();
            wp_send_json(array('html' => $html, 'path' => $result['path_display']));
        }
        wp_send_json(false);
    }

    /**
     * Delete Folder to dropbox
     * @throws \WPMFDropbox\Exception_BadResponseCode
     * @throws \WPMFDropbox\Exception_InvalidAccessToken
     * @throws \WPMFDropbox\Exception_RetryLater
     * @throws \WPMFDropbox\Exception_ServerError
     */
    public function deleteDropbox()
    {
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        if (isset($_POST['path'])) {
            $listfiles = explode(',', $_POST['path']);
            foreach ($listfiles as $path) {
                $dropbox = $this->getAccount();
                $result = $dropbox->delete($path);
            }
            wp_send_json(true);
        }
        wp_send_json(false);
    }

    /**
     * rename Folder Dropbox
     * @throws \WPMFDropbox\Exception_BadResponseCode
     * @throws \WPMFDropbox\Exception_InvalidAccessToken
     * @throws \WPMFDropbox\Exception_RetryLater
     * @throws \WPMFDropbox\Exception_ServerError
     */
    public function changeDropboxFilename()
    {
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        if (isset($_POST['path']) && isset($_POST['filename'])) {
            $dropbox = $this->getAccount();
            try {
                $filename = urldecode($_POST['filename']);
                $result = $dropbox->move($_POST['path'], $_POST['parent'] . '/' . $filename);
            } catch (Exception $e) {
                $filename = urldecode($_POST['filename']) . '-' . time();
                $result = $dropbox->move($_POST['path'], $_POST['parent'] . '/' . $filename);
            }

            $id = $result['path_display'];
            $name = $result['name'];
            ob_start();
            if ($result['.tag'] == 'folder') {
                $infofile = pathinfo($id);
                if (isset($infofile['extension'])) {
                    $extension = $infofile['extension'];
                } else {
                    $extension = '';
                }
                $thumbnail = '<img class="" src="' . WPMFAD_PLUGIN_URL . '/assets/images/icons/dropbox_folder.png">';
                require(WPMFAD_PLUGIN_DIR . '/class/templates/htmlfolder.php');
            } else {
                require_once 'includes/mime-types.php';
                $downloadlink = admin_url('admin-ajax.php') . "?
                action=wpmf-dbxdownload-file&id=" . urlencode($result['id']) . "&link=true&dl=1";
                $infofile = pathinfo($id);
                $mimeType = getMimeType($infofile['extension']);
                $thumbnail = $_POST['thumbnail'];
                $display_preview = 1;
                if (in_array($infofile['extension'], array('jpg', 'jpeg', 'gif', 'png'))) {
                    $type = 'image';
                    $lightboxlink = $downloadlink;
                } elseif ($infofile['extension'] == 'pdf') {
                    $type = 'pdf';
                    $shared_links = $dropbox->create_shared_link($id);
                    $lightboxlink = $shared_links['url'] . '&raw=1';
                } else {
                    $display_preview = 0;
                    $type = 'download';
                    $lightboxlink = $downloadlink;
                }

                if (isset($result['media_info']['metadata']['.tag'])
                    && $result['media_info']['metadata']['.tag'] == 'video') {
                    $type_insert = 'video';
                    $type = 'download';
                    $lightboxlink = $downloadlink;
                }

                require(WPMFAD_PLUGIN_DIR . '/class/templates/htmlfile.php');
            }

            $html = ob_get_contents();
            ob_end_clean();

            wp_send_json(array('html' => $html, 'path' => $result['path_display']));
        }
        wp_send_json(false);
    }

    /**
     * Move dropbox file
     * @throws \WPMFDropbox\Exception_BadResponseCode
     * @throws \WPMFDropbox\Exception_InvalidAccessToken
     * @throws \WPMFDropbox\Exception_RetryLater
     * @throws \WPMFDropbox\Exception_ServerError
     */
    public function moveDropboxFile()
    {
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        if (isset($_POST['fileIds']) && isset($_POST['newParentId'])) {
            $dropbox = $this->getAccount();
            $fileIds = explode(',', $_POST['fileIds']);
            foreach ($fileIds as $path) {
                $info = pathinfo($path);
                try {
                    if (isset($info['extension']) && $info['extension'] != '') {
                        $newpath = $_POST['newParentId'] . '/' . $info['filename'] . '.' . $info['extension'];
                    } else {
                        $newpath = $_POST['newParentId'] . '/' . $info['filename'];
                    }
                    $dropbox->move($path, $newpath);
                } catch (Exception $e) {
                    if (isset($info['extension']) && $info['extension'] != '') {
                        $newpath = $_POST['newParentId'] . '/' . $info['filename'] . '
                        -' . time() . '.' . $info['extension'];
                    } else {
                        $newpath = $_POST['newParentId'] . '/' . $info['filename'] . '-' . time();
                    }
                    $dropbox->move($path, $newpath);
                }
            }
            wp_send_json(true);
        }
        wp_send_json(false);
    }

    /**
     * upload file to Folder Dropbox
     * @throws \WPMFDropbox\Exception_BadResponseCode
     * @throws \WPMFDropbox\Exception_InvalidAccessToken
     * @throws \WPMFDropbox\Exception_RetryLater
     * @throws \WPMFDropbox\Exception_ServerError
     */
    public function uploadFile()
    {
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        if (isset($_FILES['files'])) {
            if (!empty($_FILES['files']['error'][0])) {
                wp_send_json(false);
            }
            $filename = $_FILES['files']['name'][0];
            $fileTemp = $_FILES['files']['tmp_name'][0];
            $size = $_FILES['files']['size'][0];
            if (isset($_POST['parentID'])) {
                $id_folder = $_POST['parentID'];
                $f = fopen($fileTemp, "rb");
                $dropbox = $this->getAccount();
                $path = $id_folder . "/" . $filename;
                $checkfile = $dropbox->searchFileNames($id_folder, $path);
                if (empty($checkfile['matches'])) {
                    $result = $dropbox->uploadFile($path, WPMFDropbox\WriteMode::add(), $f, $size);
                } else {
                    $info = pathinfo($filename);
                    $path = $id_folder . '/' . $info['filename'] . '-' . time() . '.' . $info['extension'];
                    $result = $dropbox->uploadFile($path, WPMFDropbox\WriteMode::add(), $f, $size);
                }

                wp_send_json($result);
            }
        }
        wp_send_json(false);
    }

    /**
     * Get list dropbox files
     * @throws \WPMFDropbox\Exception_BadResponseCode
     * @throws \WPMFDropbox\Exception_InvalidAccessToken
     * @throws \WPMFDropbox\Exception_RetryLater
     * @throws \WPMFDropbox\Exception_ServerError
     */
    public function listDropboxFiles()
    {
        if (!current_user_can('upload_files')) {
            wp_send_json(array('status' => false));
        }
        if (isset($_POST['path_display'])) {
            $path = $_POST['path_display'];
            $listbreadcrumb = explode('/', $path);
            // get breadcrumb
            $breadcrumb = __('You are here  : ', 'wpmfAddon');
            foreach ($listbreadcrumb as $brea) {
                if ($brea == '') {
                    $breadcrumb .= "<a href='javascript:void(0)' class='wpmf_dbxbreadcrumb_folder'
 data-id=''><i class='wpmf-home zmdi zmdi-home'></i></a> ";
                } else {
                    $breadcrumb .= "<a href='javascript:void(0)' class='wpmf_dbxbreadcrumb_folder'
                     data-id='/" . $brea . "'>" . $brea . "</a> / ";
                }
            }

            $dropbox = $this->getAccount();
            if (isset($_POST['searchfilename']) && $_POST['searchfilename'] != '') {
                $filessearch = $dropbox->searchFileNames($path, $_POST['searchfilename']);
                $filesearch = $filessearch['matches'];
                $fs = array('entries' => array());
                foreach ($filesearch as $file) {
                    $fs['entries'][] = $file['metadata'];
                }
            } else {
                $fs = $dropbox->getMetadataWithChildren($path);
            }

            if (empty($fs)) {
                return false;
            }
            $paths_a = explode('/', $path);
            $count_a = count($paths_a);
            if (isset($paths_a[$count_a])) {
                unset($paths_a[$count_a]);
            }
            if (isset($paths_a[$count_a - 1])) {
                unset($paths_a[$count_a - 1]);
            }
            $parent = implode('/', $paths_a);
            $files = array();
            $html = '';
            ob_start();
            if ($_POST['path_display'] != '') {
                $thumbnail_pre = '<img class="" src="' . WPMFAD_PLUGIN_URL . '/assets
                /images/icons/dropbox_folder-previous.png">';
                require(WPMFAD_PLUGIN_DIR . '/class/templates/htmlprevious.php');
            }

            $foldersarray = array();
            $filesarray = array();
            foreach ($fs['entries'] as $re) {
                if ($re['.tag'] == "file") {
                    $info = pathinfo($re['path_display']);
                    if (in_array($info['extension'], array('jpg', 'jpeg', 'gif', 'png', 'bmp'))) {
                        $re['typepreview'] = 'img_lightbox';
                    } elseif ($info['extension'] == 'pdf') {
                        $re['typepreview'] = 'pdf';
                    } else {
                        $re['typepreview'] = 'download';
                    }

                    if (isset($re['media_info']['metadata']['.tag'])
                        && $re['media_info']['metadata']['.tag'] == 'video') {
                        $re['typepreview'] = 'video';
                    }
                    array_push($filesarray, $re);
                } else {
                    array_push($foldersarray, $re);
                }
            }

            $orderby = 'path_lower';
            if (isset($_POST['sortfilename'])) {
                $order = $_POST['sortfilename'];
            } else {
                $order = 'asc';
            }
            $foldersarray = $this->subValSort($foldersarray, $orderby, $order);
            $filesarray = $this->subValSort($filesarray, $orderby, $order);
            foreach ($foldersarray as $re) {
                $id = $re['path_display'];
                $name = $re['name'];
                $infofile = pathinfo($id);
                $thumbnail = '<img class="" src="' . WPMFAD_PLUGIN_URL . '/assets/images/icons/dropbox_folder.png">';
                if (isset($infofile['extension'])) {
                    $extension = $infofile['extension'];
                } else {
                    $extension = '';
                }
                require(WPMFAD_PLUGIN_DIR . '/class/templates/htmlfolder.php');
            }

            $thumbnail_newfolder = '<img class="" src="' . WPMFAD_PLUGIN_URL . '/assets
            /images/icons/dropbox_newfolder.png">';
            require(WPMFAD_PLUGIN_DIR . '/class/templates/htmladdfolder.php');
            $lists_file_preview = array();
            $videofiles = array();
            $lists_preview = array();
            require_once 'includes/mime-types.php';
            foreach ($filesarray as $re) {
                $id = $re['path_display'];
                $downloadlink = admin_url('admin-ajax.php') . "?
                action=wpmf-dbxdownload-file&id=" . urlencode($re['id']) . "&link=true&dl=1";
                $name = $re['name'];
                $infofile = pathinfo($id);
                $mimeType = getMimeType($infofile['extension']);
                $thumbnail = $this->getThumbnail($infofile['extension']);
                $display_preview = 1;
                switch ($re['typepreview']) {
                    case 'img_lightbox':
                        $lists_file_preview[] = array(
                            'id' => $re['id'],
                            'type' => 'image',
                            'path' => $re['path_display']
                        );
                        $type_insert = $type = 'image';
                        $lightboxlink = $downloadlink;
                        break;

                    case 'download':
                        $display_preview = 0;
                        $type_insert = $type = 'download';
                        $lightboxlink = '#';
                        break;

                    case 'video':
                        $display_preview = 0;
                        $lists_file_preview[] = array(
                            'id' => $re['id'],
                            'type' => 'image',
                            'path' => $re['path_display']
                        );
                        $type_insert = 'video';
                        $type = 'download';
                        $lightboxlink = '#';
                        break;

                    case 'pdf':
                        $lists_file_preview[] = array(
                            'id' => $re['id'],
                            'type' => 'pdf',
                            'path' => $re['path_display']
                        );
                        $type_insert = $type = 'pdf';
                        $lightboxlink = '#';
                        break;
                    default:
                        $display_preview = 0;
                        $type_insert = $type = 'download';
                        $lightboxlink = '#';
                }

                $files[$id] = array('title' => $name, 'ext' => $infofile['extension'], 'type_insert' => $type_insert);
                $checktype = '';
                if ($type_insert == 'video') {
                    /*$mimetype = getMimeType($infofile['extension']);
                    $thumbnail = $dropbox->getThumbnail($path,'jpeg','w640h480',$mimetype);*/
                    $checktype = 'dropbox_video';
                    $files[$id]['thumbnail'] = $thumbnail;
                    $videofiles[] = $re['path_display'];
                }
                require(WPMFAD_PLUGIN_DIR . '/class/templates/htmlfile.php');
            }

            $html = ob_get_contents();
            ob_end_clean();
            wp_send_json(
                array(
                    'status' => true,
                    'message' => $html,
                    'lists_file_preview' => $lists_file_preview,
                    'videofiles' => $videofiles,
                    'files' => $files,
                    'breadcrumb' => $breadcrumb,
                    'lists_preview' => $lists_preview
                )
            );
        }
        wp_send_json(
            array(
                'status' => false
            )
        );
    }

    /**
     * get thumbnail icon file
     * @param string $ext extension of file
     * @return string
     */
    public function getThumbnail($ext)
    {
        switch ($ext) {
            case 'ace':
            case 'arj':
            case 'bz2':
            case 'cab':
            case 'gzip':
            case 'iso':
            case 'jar':
            case 'lzh':
            case 'tar':
            case 'uue':
            case 'xz':
            case 'z':
            case '7-zip':
            case 'x-rar':
            case 'rar':
            case 'zip':
                $thumbnailicon = 'dropbox_archives.png';
                break;
            case 'mp3':
                $thumbnailicon = 'dropbox_audio.png';
                break;
            case 'jpg':
            case 'jpe':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'svg':
            case 'svgz':
            case 'tif':
            case 'tiff':
            case 'ico':
                $thumbnailicon = 'image.png';
                break;
            case 'pptx':
                $thumbnailicon = 'dropbox_powerpoint.png';
                break;
            case 'js':
            case 'css':
            case 'html':
            case 'htm':
            case 'php':
                $thumbnailicon = 'dropbox_js_css.png';
                break;
            case 'mp4':
            case 'm4p':
            case 'wmv':
            case 'm4v':
                $thumbnailicon = 'dropbox_video.png';
                break;

            case 'xlsx':
            case 'xls':
            case 'xlsm':
                $thumbnailicon = 'dropbox_excel.png';
                break;
            case 'doc':
            case 'docx':
            case 'docm':
                $thumbnailicon = 'dropbox_doc.png';
                break;
            case 'pdf':
                $thumbnailicon = 'dropbox_pdf.png';
                break;
            case 'ai':
                $thumbnailicon = 'dropbox_ai.png';
                break;
            case 'psd':
                $thumbnailicon = 'dropbox_psd.png';
                break;
            default:
                $thumbnailicon = 'dropbox_default.png';
                break;
        }

        $thumbnail = WPMFAD_PLUGIN_URL . '/assets/images/icons/' . $thumbnailicon;
        return $thumbnail;
    }

    /**
     * get share link file
     */
    public function dropboxSharefile()
    {
        if (isset($_POST['id'])) {
            $dropbox = $this->getAccount();
            $shared_links = $dropbox->get_shared_links($_POST['id']);
            if (!empty($shared_links['links'][0]['url'])) {
                wp_send_json(array('status' => true, 'src' => $shared_links['links'][0]['url']));
            }
        }
        wp_send_json(array('status' => false));
    }

    /**
     * import dropbox file to media library
     * @throws \WPMFDropbox\Exception_BadResponseCode
     * @throws \WPMFDropbox\Exception_InvalidAccessToken
     * @throws \WPMFDropbox\Exception_RetryLater
     * @throws \WPMFDropbox\Exception_ServerError
     */
    public function importFile()
    {
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        if (isset($_POST['ids']) && isset($_POST['wpmfdbxcurrentFolderId'])) {
            $dropbox = $this->getAccount();
            $ids = explode(',', $_POST['ids']);
            $term_id = (!empty($_POST['wpmfdbxcurrentFolderId'])) ? $_POST['wpmfdbxcurrentFolderId'] : 0;
            $upload_dir = wp_upload_dir();
            if (!empty($ids)) {
                $percent = ceil(100 / count($ids));
            } else {
                $percent = 100;
            }
            $i = 0;
            require_once 'includes/mime-types.php';
            foreach ($ids as $id) {
                if ($i >= 1) {
                    wp_send_json(array('status' => 'error time', 'percent' => $percent)); // run again ajax
                } else {
                    $info = pathinfo($id);
                    $filename = sanitize_file_name($info['basename']);
                    $extension = $info['extension'];
                    $list_imported = get_option('wpmf_dbxfiles_imported');
                    if (empty($list_imported)) {
                        $list_imported = array();
                    }
                    if (!in_array($term_id . '_' . $filename, $list_imported) || empty($list_imported)) {
                        $content = $dropbox->get_filecontent($id);

                        $getMimeType = getMimeType($extension);
                        $status = $this->insertAttachmentMetadata(
                            $id,
                            $upload_dir['path'],
                            $upload_dir['url'],
                            $filename,
                            $content,
                            $getMimeType,
                            $extension,
                            $term_id
                        );
                        if ($status) {
                            $i++;
                        }
                    }
                }
            }
            wp_send_json(array('status' => true, 'percent' => '100')); // run again ajax
        }
        wp_send_json(false);
    }

    /**
     * Get details of file
     */
    public function getDetailFile()
    {
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        if (empty($_POST['path']) || empty($_POST['id']) || empty($_POST['name'])) {
            wp_send_json(false);
        }
        ob_start();
        $dropbox = $this->getAccount();
        $id = $_POST['path'];
        $downloadlink = admin_url('admin-ajax.php') . "?
        action=wpmf-dbxdownload-file&id=" . urlencode($_POST['id']) . "&link=true&dl=1";
        $name = $_POST['name'];
        $infofile = pathinfo($id);
        require_once 'includes/mime-types.php';
        $mimeType = getMimeType($infofile['extension']);
        $thumbnail = $this->getThumbnail($infofile['extension']);
        $display_preview = 1;

        if (in_array($infofile['extension'], array('jpg', 'jpeg', 'gif', 'png', 'bmp'))) {
            $typepreview = 'img_lightbox';
        } elseif ($infofile['extension'] == 'pdf') {
            $typepreview = 'pdf';
        } elseif (in_array($infofile['extension'], array('mp4', 'wmv'))) {
            $typepreview = 'video';
        } else {
            $typepreview = 'download';
        }

        switch ($typepreview) {
            case 'img_lightbox':
                $type_insert = $type = 'image';
                $lightboxlink = $downloadlink;
                break;

            case 'download':
                $display_preview = 0;
                $type_insert = $type = 'download';
                $lightboxlink = '#';
                break;

            case 'video':
                $display_preview = 0;
                $type_insert = 'video';
                $type = 'download';
                $lightboxlink = '#';
                break;

            case 'pdf':
                $type_insert = $type = 'pdf';
                $lightboxlink = '#';
                break;
            default:
                $display_preview = 0;
                $type_insert = $type = 'download';
                $lightboxlink = '#';
        }
        require(WPMFAD_PLUGIN_DIR . '/class/templates/htmlfile.php');
        $html = ob_get_contents();
        ob_end_clean();
        wp_send_json(
            array(
                'html' => $html,
                'type' => $type,
                'title' => $_POST['name'],
                'ext' => $infofile['extension'],
                'type_insert' => $type_insert)
        );
    }

    /**
     * Insert a attachment to database
     * @param string $idfile id of file
     * @param string $upload_path wordpress upload path
     * @param string $upload_url wordpress upload url
     * @param string $file file name
     * @param string $content content of file
     * @param string $mime_type mime type of file
     * @param string $ext extension of file
     * @param int $term_id media folder id to set file to folder
     * @return bool
     */
    public function insertAttachmentMetadata(
        $idfile,
        $upload_path,
        $upload_url,
        $file,
        $content,
        $mime_type,
        $ext,
        $term_id
    ) {
        remove_filter('add_attachment', array($GLOBALS['wp_media_folder'], 'wpmf_after_upload'));
        $list_imported = get_option('wpmf_dbxfiles_imported');
        if (!in_array($term_id . '_' . $idfile, $list_imported) || empty($list_imported)) {
            if (!empty($list_imported) && is_array($list_imported)) {
                $list_imported[] = $term_id . '_' . $idfile;
            } else {
                $list_imported = array($term_id . '_' . $idfile);
            }
            $file = wp_unique_filename($upload_path, $file);
            $upload = file_put_contents($upload_path . '/' . $file, $content);
            if ($upload) {
                $attachment = array(
                    'guid' => $upload_url . '/' . $file,
                    'post_mime_type' => $mime_type,
                    'post_title' => str_replace('.' . $ext, '', $file),
                    'post_status' => 'inherit'
                );

                $image_path = $upload_path . '/' . $file;
                // Insert attachment
                $attach_id = wp_insert_attachment($attachment, $image_path);
                $attach_data = wp_generate_attachment_metadata($attach_id, $image_path);
                wp_update_attachment_metadata($attach_id, $attach_data);
                // set attachment to term
                wp_set_object_terms((int)$attach_id, (int)$term_id, WPMF_TAXO, false);
                update_option('wpmf_dbxfiles_imported', $list_imported);
            }
            return true;
        }
        return false;
    }


    /**
     * Sort files
     * @param array $a array to sort
     * @param string $subkey orderby
     * @param string $direction order
     * @return array
     */
    private function subValSort($a, $subkey, $direction)
    {
        if (empty($a)) {
            return $a;
        }
        foreach ($a as $k => $v) {
            $b[$k] = strtolower($v[$subkey]);
        }
        if ($direction == 'asc') {
            asort($b);
        } else {
            arsort($b);
        }
        if (empty($c)) {
            $c = array();
        }
        foreach ($b as $key => $val) {
            $c[] = $a[$key];
        }
        return $c;
    }

    /**
     * download dropbox file
     * @throws \WPMFDropbox\Exception_BadResponseCode
     * @throws \WPMFDropbox\Exception_InvalidAccessToken
     * @throws \WPMFDropbox\Exception_RetryLater
     * @throws \WPMFDropbox\Exception_ServerError
     */
    public function downloadFile()
    {
        if (isset($_REQUEST['id'])) {
            $id_file = $_REQUEST['id'];
            $dropbox = $this->getAccount();
            $getFile = $dropbox->getMetadata($id_file);
            $pinfo = pathinfo($getFile['path_lower']);
            $tempfile = $pinfo['basename'];
            $fd = fopen($tempfile, "wb");
            $a = $dropbox->getFile($getFile['path_lower'], $fd);
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($tempfile) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($tempfile));
            readfile($tempfile);
            wp_send_json(true);
        } else {
            wp_send_json(false);
        }
    }

    /**
     * redirect url
     * @param $location
     */
    public function redirect($location)
    {
        if (!headers_sent()) {
            header("Location: $location", true, 303);
        } else {
            echo "<script>document.location.href='" . str_replace("'", "&apos;", $location) . "';</script>\n";
        }
    }
}
