<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
//require_once 'ShareoneDrive_Processor.php';

/* Load OneDrive SDK and hack around with the include paths a bit so the library always 'just works' */

set_include_path(dirname(__FILE__) . PATH_SEPARATOR . get_include_path());
if (!function_exists('wpmf_onedrive_api_php_client_autoload')) {
    try {
        require_once "OneDrive/autoload.php";
    } catch (Exception $ex) {
        return new WP_Error('broke', __('Something went wrong... See settings page', 'wpmfAddon'));
    }
}

/**
 * Class WpmfAddonOneDrive
 * This class that holds most of the admin functionality for OneDrive
 */
class WpmfAddonOneDrive
{

    /**
     * @var OneDrive_Client
     */
    private $client = null;
    private $oneDriveService;
    protected $apifilefields = 'thumbnails,children(top=1000;expand=thumbnails(select=medium,large,mediumSquare,c1500x1500))';
    protected $apilistfilesfields = 'thumbnails(select=medium,large,mediumSquare,c1500x1500)';
    public $breadcrumb = '';
    private $accessToken;
    private $refreshToken;

    /**
     * WpmfAddonOneDrive constructor.
     */
    public function __construct()
    {
    }

    /**
     * get token from _wpmfAddon_onedrive_config option
     * @return bool|WP_Error
     */
    public function loadToken()
    {
        $onedriveconfig = get_option('_wpmfAddon_onedrive_config');
        if (empty($onedriveconfig['current_token'])) {
            return new WP_Error('broke', __("The plugin isn't yet authorized to use your OneDrive!
             Please (re)-authorize the plugin", 'wpmfAddon'));
        } else {
            $this->accessToken = $onedriveconfig['current_token'];
            $this->refreshToken = $onedriveconfig['refresh_token'];
        }

        return true;
    }

    /**
     * Revoke token
     * To-Do: Revoke Token is not yet possible with OneDrive API
     * @return bool
     */
    public function revokeToken()
    {
        //$this->client->revokeToken();
        $this->accessToken = '';
        $this->refreshToken = '';
        $onedriveconfig = array(
            'current_token' => '',
            'refresh_token' => ''
        );
        $onedriveconfig['current_token'] = '';
        $onedriveconfig['refresh_token'] = '';
        update_option('share_one_drive_lists', array());
        update_option('share_one_drive_cache', array(
            'last_update' => null,
            'last_cache_id' => '',
            'locked' => false,
            'cache' => ''
        ));
        update_option('_wpmfAddon_onedrive_config', $onedriveconfig);
        return true;
    }

    /**
     * Read OneDrive app key and secret
     * @return bool
     */
    public function getClient()
    {
        $onedriveconfig = get_option('_wpmfAddon_onedrive_config');
        $this->client = new OneDrive_Client();

        /* Set Retries */
        $this->client->setClassConfig('OneDrive_Task_Runner', array('retries'), 3);
        /* Load OneDrive API */
        $this->oneDriveService = new OneDrive_Service_Drive($this->client);

        /* Set Client Id & Secret */
        if ((!empty($onedriveconfig['OneDriveClientId'])) && (!empty($onedriveconfig['OneDriveClientSecret']))) {
            $this->client->setClientId($onedriveconfig['OneDriveClientId']);
            $this->client->setClientSecret($onedriveconfig['OneDriveClientSecret']);
        } else {
            return false;
        }

        /* Set other parameters */
        $this->client->setApprovalPrompt('force');
        $this->client->setAccessType('offline');

        $this->client->setScopes(array(
            'wl.offline_access',
            'wl.skydrive',
            'onedrive.readwrite'));

        $page = isset($_GET["page"]) ? '?page=' . $_GET["page"] : '';
        $location = get_admin_url(null, 'admin.php' . $page);
        $this->client->setState(strtr(base64_encode($location), '+/=', '-_~'));
        $this->client->setClassConfig('OneDrive_Logger_Abstract', array(
            'level' => 'debug', //'warning' or 'debug'
            'log_format' => "[%datetime%] %level%: %message% %context%\n",
            'date_format' => 'd/M/Y:H:i:s O',
            'allow_newlines' => true));
        return true;
    }

    /**
     * Start OneDrive API Client with token
     * @return OneDrive_Client|WP_Error
     */
    public function startClient()
    {
        if ($this->accessToken === false) {
            die();
        }

        try {
            $onedriveconfig = get_option('_wpmfAddon_onedrive_config');
            if (empty($onedriveconfig)) {
                $onedriveconfig = array();
            }
            $token = $this->accessToken;
            $this->client->setAccessToken($token);

            /* Is Token still valid? */
            if ($this->client->isAccessTokenExpired()) {
                $tokenobj = json_decode($token);

                /* Refresh token if possible */
                if (isset($tokenobj->refresh_token)) {
                    try {
                        $this->client->refreshToken($tokenobj->refresh_token);
                    } catch (Exception $e) {
                        $onedriveconfig['current_token'] = '';
                        $onedriveconfig['refresh_token'] = '';
                        update_option('_wpmfAddon_onedrive_config', $onedriveconfig);
                        return new WP_Error(
                            'broke',
                            __("Share-one-Drive isn't ready to run", 'wpmfAddon') . $e->getMessage()
                        );
                    }

                    /* Save new token in DB */
                    $this->accessToken = $this->client->getAccessToken();
                    $onedriveconfig['current_token'] = $this->accessToken;
                    update_option('_wpmfAddon_onedrive_config', $onedriveconfig);
                } else {
                    $onedriveconfig['current_token'] = '';
                    $onedriveconfig['refresh_token'] = '';
                    update_option('_wpmfAddon_onedrive_config', $onedriveconfig);
                    return new WP_Error('broke', __("Share-one-Drive isn't ready to run", 'wpmfAddon'));
                }
            }
        } catch (Exception $e) {
            return new WP_Error('broke', __("Couldn't connect to OneDrive API: ", 'wpmfAddon') . $e->getMessage());
        }

        return $this->client;
    }

    /**
     * Get DriveInfo
     * @return bool|null|WP_Error
     */
    public function getDriveInfo()
    {
        if ($this->client === null) {
            return false;
        }

        $driveInfo = null;

        try {
            $driveInfo = $this->oneDriveService->about->get();
        } catch (Exception $ex) {
            return new WP_Error('broke', $ex->getMessage());
        }
        if ($driveInfo !== null) {
            return $driveInfo;
        } else {
            return new WP_Error('broke', 'drive null');
        }
    }

    /**
     * Get a $authorizeUrl
     * @return string|WP_Error
     */
    public function startWebAuth()
    {
        try {
            $authorizeUrl = $this->client->createAuthUrl();
        } catch (Exception $ex) {
            return new WP_Error('broke', __("Could not start authorization: ", 'wpmfAddon') . $ex->getMessage());
        }
        return $authorizeUrl;
    }

    /**
     * Set redirect URL
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

    /**
     * Create token after connected
     * @param string $code code to access to onedrive app
     * @return bool|WP_Error
     */
    public function createToken($code)
    {
        try {
            $onedrive_config = get_option('_wpmfAddon_onedrive_config');
            $client = new OneDrive_Client();
            $client->setClientId($onedrive_config['OneDriveClientId']);
            $client->setClientSecret($onedrive_config['OneDriveClientSecret']);
            $onedriveconfig = get_option('_wpmfAddon_onedrive_config');
            if (empty($onedriveconfig)) {
                $onedriveconfig = array();
            }
            $client->authenticate($code);
            // check isset token
            if (empty($onedriveconfig['current_token']) || empty($onedriveconfig['refresh_token'])) {
                $token = $client->getAccessToken();
                $onedriveconfig['current_token'] = $token;
                $onedriveconfig['refresh_token'] = $token;
                // create root folder
                $newentry = $this->addFolderoot($token, 'WP Media Folder - ' . get_bloginfo('name'));
                $decoded = json_decode($newentry['responsebody'], true);
                $newentry = new OneDrive_Service_Drive_Item($decoded);
                $idroot = $newentry->getId();
                $nameroot = $newentry->getName();
                $onedriveconfig['onedriveBaseFolder'] = array('id' => $idroot, 'name' => $nameroot);
            } else {
                $token = $onedriveconfig['current_token'];
            }
            $this->accessToken = $token;
            $onedriveconfig['connected'] = 1;
            // update _wpmfAddon_onedrive_config option and redirect page
            update_option('_wpmfAddon_onedrive_config', $onedriveconfig);
            $this->redirect(admin_url('options-general.php?page=option-folder&tab=wpmf-onedrive'));
        } catch (Exception $ex) {
            return new WP_Error(
                'broke',
                __("Error communicating with OneDrive API: ", 'wpmfAddon') . $ex->getMessage()
            );
        }

        return true;
    }

    /**
     * Get breadcrumb
     * @param string $folderid id of folder
     * @param object $file current folder
     * @param string $parent folder parent
     * @param string $parentTitle title of folder parent
     * @param $OneDriveBaseFolder
     */
    public function getBreadcrumb(
        $folderid,
        $file,
        $parent,
        $parentTitle,
        $OneDriveBaseFolder
    ) {
        if ($folderid != $OneDriveBaseFolder) {
            $this->breadcrumb .= "<a href='javascript:void(0)' class='wpmf_breadcrumb_folder'
             data-id='" . $OneDriveBaseFolder . "'><i class='wpmf-home zmdi zmdi-home'></i></a> ";
            if ($parent != $OneDriveBaseFolder) {
                $this->breadcrumb .= "<a href='javascript:void(0)' class='wpmf_breadcrumb_folder'
                 data-id='" . $parent . "'>" . $parentTitle . "</a> / ";
            }
        }
        if ($file->id == $OneDriveBaseFolder) {
            $this->breadcrumb .= "<a href='javascript:void(0)' class='wpmf_breadcrumb_folder'
             data-id='" . $file->id . "'><i class='wpmf-home zmdi zmdi-home'></i></a>";
        } else {
            $this->breadcrumb .= "<a href='javascript:void(0)' class='wpmf_breadcrumb_folder'
             data-id='" . $file->id . "'>" . $file->name . "</a> / ";
        }
    }

    /**
     * Get root folders
     * @param bool $folderid id of folder
     * @return bool
     */
    public function getRootFolder($folderid = false)
    {
        try {
            $client_service = $this->getClientServer();
            $service = $client_service['service'];
            $results = $service->items->get($folderid, array("expand" => $this->apifilefields));
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Get folders and files
     * @param bool $root
     * @param bool $folderid id folder
     * @param bool $hardrefresh
     * @param bool $checkauthorized
     * @param string $searchfilename keyword to search
     * @return array|bool
     */
    public function getFolder(
        $root = false,
        $folderid = false,
        $hardrefresh = false,
        $checkauthorized = true,
        $searchfilename = ''
    ) {
        try {
            $client_service = $this->getClientServer();
            $service = $client_service['service'];
            $results = $service->items->get($folderid, array("expand" => $this->apifilefields));
            $parents = $results->getParentReference();
            $contents = $results->getChildren();

            $onedrive_config = get_option('_wpmfAddon_onedrive_config');
            $breadcrumb = __('You are here  : ', 'wpmfAddon');
            $this->getBreadcrumb(
                $folderid,
                $results,
                $parents->id,
                $parents->name,
                $onedrive_config['onedriveBaseFolder']['id']
            );
            $breadcrumb .= $this->breadcrumb;

            if (isset($searchfilename) && $searchfilename != '') {
                $params = array(
                    'id' => $folderid,
                    'q' => stripslashes($searchfilename),
                    "expand" => $this->apilistfilesfields
                );
                $itemsearch = $service->items->search($params);
                $contents = $itemsearch->getValue();
                return array('folder' => $results, 'contents' => $contents, 'parent' => $parents->id);
            }

            return array(
                'folder' => $results,
                'contents' => $contents,
                'parent' => $parents->id,
                'breadcrumb' => $breadcrumb
            );
        } catch (Exception $ex) {
            return false;
        }
    }

    /*
     * Uploads file to OneDrive
     */
    public function uploadFile()
    {
        if (!current_user_can('upload_files')) {
            wp_send_json(array('status' => false));
        }
        if (!empty($_POST['parentID'])) {
            $id_folder = $_POST['parentID'];
        } else {
            $id_folder = '';
        }

        /* Upload File to server */
        require('includes/UploadHandler.php');
        $max_file_size = 104857600;
        $uploadir = wp_upload_dir();
        $options = array(
            'upload_dir' => $uploadir['path'] . '/',
            'upload_url' => $uploadir['url'] . '/',
            'access_control_allow_methods' => array('POST', 'PUT'),
            'accept_file_types' => '/.(.)$/i',
            'inline_file_types' => '/\.____$/i',
            'orient_image' => false,
            'image_versions' => array(),
            'max_file_size' => $max_file_size,
            'print_response' => false
        );

        $error_messages = array(
            1 => __('The uploaded file exceeds the upload_max_filesize directive in php.ini', 'wpmfAddon'),
            2 => __('The uploaded file exceeds the MAX_FILE_SIZE
             directive that was specified in the HTML form', 'wpmfAddon'),
            3 => __('The uploaded file was only partially uploaded', 'wpmfAddon'),
            4 => __('No file was uploaded', 'wpmfAddon'),
            6 => __('Missing a temporary folder', 'wpmfAddon'),
            7 => __('Failed to write file to disk', 'wpmfAddon'),
            8 => __('A PHP extension stopped the file upload', 'wpmfAddon'),
            'post_max_size' => __('The uploaded file exceeds the post_max_size directive in php.ini', 'wpmfAddon'),
            'max_file_size' => __('File is too big', 'wpmfAddon'),
            'min_file_size' => __('File is too small', 'wpmfAddon'),
            'accept_file_types' => __('Filetype not allowed', 'wpmfAddon'),
            'max_number_of_files' => __('Maximum number of files exceeded', 'wpmfAddon'),
            'max_width' => __('Image exceeds maximum width', 'wpmfAddon'),
            'min_width' => __('Image requires a minimum width', 'wpmfAddon'),
            'max_height' => __('Image exceeds maximum height', 'wpmfAddon'),
            'min_height' => __('Image requires a minimum height', 'wpmfAddon')
        );

        /* Upload the file to server */
        $upload_handler = new UploadHandler($options, false, $error_messages);
        $response = @$upload_handler->post(false);
        $client_service = $this->getClientServer();
        $client = $client_service['client'];
        $service = $client_service['service'];

        /* Upload files to OneDrive */
        foreach ($response['files'] as &$file) {
            /* Set return Object */
            $file->hash = $_REQUEST['hash'];
            $return = array(
                'file' => $file,
                'status' => array(
                    'bytes_down_so_far' => 0,
                    'total_bytes_down_expected' => 0,
                    'percentage' => 0,
                    'progress' => 'starting'
                )
            );
            set_transient('wpmfonedrive_upload_' . substr($file->hash, 0, 40), $return, HOUR_IN_SECONDS);

            if (!isset($file->error)) {
                /* Write file */
                $filePath = $file->tmp_path;
                /* Multiple of 320kb, the recommended fragment size is between 5-10 MB. */
                $chunkSizeBytes = 20 * 320 * 1000;

                /* Update Mime-type if needed (for IE8 and lower?) */
                include_once 'includes/mime-types.php';
                $fileExtension = pathinfo($file->name, PATHINFO_EXTENSION);
                $file->type = getMimeType($fileExtension);

                try {
                    /* Create new File with parent */
                    $body = array('item' => array('name' => $file->name, '@name.conflictBehavior' => 'rename'));
                    $client->setDefer(true);
                    $startupload = $service->items->upload($file->name, $id_folder, $body);
                    $media = new OneDrive_Http_MediaFileUpload(
                        $client,
                        $startupload,
                        null,
                        null,
                        true,
                        $chunkSizeBytes
                    );

                    $filesize = filesize($filePath);
                    $media->setFileSize($filesize);

                    /* Start partialy upload
                      Upload the various chunks. $status will be false until the process is
                      complete. */
                    $uploadStatus = false;
                    $bytesup = 0;
                    $handle = fopen($filePath, "rb");
                    while (!$uploadStatus && !feof($handle)) {
                        set_time_limit(60);
                        $chunk = fread($handle, $chunkSizeBytes);
                        $uploadStatus = $media->nextChunk($chunk);

                        /* Update progress */
                        $bytesup += $chunkSizeBytes;
                        $percentage = (round(($bytesup / $file->size) * 100));
                        $return['status'] = array(
                            'bytes_up_so_far' => $bytesup,
                            'total_bytes_up_expected' => $filesize,
                            'percentage' => $percentage,
                            'progress' => 'uploading'
                        );
                        set_transient('wpmfonedrive_upload_' . substr($file->hash, 0, 40), $return, HOUR_IN_SECONDS);
                    }

                    fclose($handle);
                } catch (Exception $ex) {
                    $file->error = __('Not uploaded to OneDrive', 'wpmfAddon') . ': ' . $ex->getMessage();
                    $return['status']['progress'] = 'failed';
                }

                $client->setDefer(false);
            }
        }
    }

    /**
     * ajax delete item
     */
    public function deleteItem()
    {
        if (!current_user_can('upload_files')) {
            wp_send_json(array('status' => false));
        }
        $client_service = $this->getClientServer();
        $service = $client_service['service'];
        /* Delete file */
        try {
            $headers = array();
            $array_ids = explode(',', $_POST['ids']);
            foreach ($array_ids as $id) {
                $item = $service->items->delete($id, $headers);
            }
        } catch (Exception $ex) {
            wp_send_json(array('status' => false, 'msh' => __('Failed to delete item', 'wpmfAddon')));
        }

        wp_send_json(array('status' => true));
    }

    /**
     * Ajax rename item
     * @return void|WP_Error
     */
    public function changeFilename()
    {
        if (!current_user_can('upload_files')) {
            wp_send_json(array('status' => false));
        }
        $client_service = $this->getClientServer();
        $service = $client_service['service'];
        $item = $service->items->get($_POST['id'], array("expand" => $this->apifilefields));
        if (isset($_POST['filename'])) {
            $title = $_POST['filename'];
        } else {
            $title = $item->getName();
        }
        $params = array('name' => $title);
        /* Rename the file */
        try {
            $newfile = $this->updateItem($service, $item, $params);
            wp_send_json(
                array(
                    'status' => true,
                    'newfile' => $newfile
                )
            );
        } catch (Exception $ex) {
            wp_send_json(
                array(
                    'status' => false,
                    'msg' => __('Failed to rename entry', 'wpmfAddon')
                )
            );
        }
    }

    /**
     * Ajax move Item
     */
    public function moveItem()
    {
        if (!current_user_can('upload_files')) {
            wp_send_json(array('status' => false));
        }
        if (isset($_POST['fileIds']) && isset($_POST['newParentId'])) {
            $fileIds = explode(',', $_POST['fileIds']);
            $newParentId = $_POST['newParentId'];
            $client_service = $this->getClientServer();
            $service = $client_service['service'];

            /* Set new parent for item */
            $newParent = new OneDrive_Service_Drive_ItemReference();
            $newParent->setId($newParentId);
            $params = new OneDrive_Service_Drive_Item();
            $params->setParentReference($newParent);
            try {
                foreach ($fileIds as $id) {
                    $item = $service->items->get($id, array("expand" => $this->apifilefields));
                    $this->updateItem($service, $item, $params);
                }
            } catch (Exception $ex) {
                wp_send_json(
                    array(
                        'status' => false,
                        'msg' => __('Failed to move entry', 'wpmfAddon')
                    )
                );
            }
        } else {
            wp_send_json(array('status' => false));
        }
    }

    /**
     * Edit item
     * @param $service OneDrive_Service_Drive class
     * @param object $item current file
     * @param array $params
     * @return mixed
     */
    public function updateItem($service, $item, $params = array())
    {
        $result = $service->items->patch(
            $item->getId(),
            $params,
            array("if-match" => $item->getEtag(), "expand" => $this->apifilefields)
        );
        return $result;
    }

    /**
     * Add folder to OneDrive
     * @param string $token a token to set client
     * @param null $new_folder
     * @return bool|OneDrive_Service_Drive_Item|WP_Error
     */
    public function addFolderoot($token, $new_folder = null)
    {
        $onedrive_config = get_option('_wpmfAddon_onedrive_config');
        $client = new OneDrive_Client();
        $client->setClientId($onedrive_config['OneDriveClientId']);
        $client->setClientSecret($onedrive_config['OneDriveClientSecret']);
        $client->setAccessToken($token);
        if ($client === null) {
            return false;
        }
        $service = new OneDrive_Service_Drive($client);
        /* Create new folder object */
        $newfolder = new OneDrive_Service_Drive_Item();
        $newfolder->setName($new_folder);
        $newfolder->setFolder(new OneDrive_Service_Drive_FolderFacet());
        $newfolder["@name.conflictBehavior"] = "rename";
        /* Do the insert call */
        try {
            $newentry = $service->items->insert('root', $newfolder);
        } catch (Exception $ex) {
            return new WP_Error('broke', __('Failed to add folder', 'wpmfAddon'));
        }

        return $newentry;
    }

    /**
     * Add folder to OneDrive
     * @param null $new_folder
     * @return WP_Error
     */
    public function addFolder($new_folder = null)
    {
        $client_service = $this->getClientServer();
        $service = $client_service['service'];
        /* Create new folder object */
        $newfolder = new OneDrive_Service_Drive_Item();
        $newfolder->setName($new_folder);
        $newfolder->setFolder(new OneDrive_Service_Drive_FolderFacet());
        $newfolder["@name.conflictBehavior"] = "rename";
        /* Do the insert call */
        try {
            $newentry = $service->items->insert('root', $newfolder);
        } catch (Exception $ex) {
            return new WP_Error('broke', __('Failed to add folder', 'wpmfAddon'));
        }

        return $newentry;
    }

    /**
     * Ajax create a folder
     */
    public function ajaxcreateFolder()
    {
        if (!current_user_can('upload_files')) {
            wp_send_json(array('status' => false));
        }
        if (isset($_POST['parentId'])) {
            $parentID = $_POST['parentId'];
            if (isset($_POST['title']) && $_POST['title'] != '') {
                $title = urldecode($_POST['title']);
            } else {
                $title = __('New Folder', 'wpmfAddon');
            }

            $client_service = $this->getClientServer();
            $service = $client_service['service'];
            /* Create new folder object */
            $newfolder = new OneDrive_Service_Drive_Item();
            /* set name */
            $newfolder->setName($title);
            $newfolder->setFolder(new OneDrive_Service_Drive_FolderFacet());
            $newfolder["@name.conflictBehavior"] = "rename";

            try {
                $service->items->insert($parentID, $newfolder);
            } catch (Exception $ex) {
                wp_send_json(
                    array(
                        'status' => false,
                        'msg' => __('Failed to move entry', 'wpmfAddon')
                    )
                );
            }
            wp_send_json(array('status' => true));
        } else {
            wp_send_json(array('status' => false));
        }
    }

    /* import file to media library */
    public function importFile()
    {
        if (!current_user_can('upload_files')) {
            wp_send_json(array('status' => false));
        }
        if (isset($_POST['ids'])) {
            $ids = explode(',', $_POST['ids']);
            $term_id = (!empty($_POST['wpmfcurrentFolderId'])) ? $_POST['wpmfcurrentFolderId'] : 0;
            $client_service = $this->getClientServer();
            $client = $client_service['client'];
            $service = $client_service['service'];

            $upload_dir = wp_upload_dir();
            if (!empty($ids)) {
                $percent = ceil(100 / count($ids));
            } else {
                $percent = 100;
            }
            $i = 0;
            foreach ($ids as $id) {
                if ($i >= 1) {
                    wp_send_json(array('status' => 'error time', 'percent' => $percent)); // run again ajax
                } else {
                    $file = $service->items->get($id, array("expand" => $this->apifilefields));
                    $downloadlink = '';
                    $result = $service->items->download($id);
                    if ($result || isset($result['location'])) {
                        $downloadlink = $result['location'];
                    }
                    $downloadurl = $downloadlink . '?download';
                    if (!empty($downloadurl)) {
                        $filename = sanitize_file_name($file->getName());
                        $list_imported = get_option('wpmf_odvfiles_imported');
                        if (empty($list_imported)) {
                            $list_imported = array();
                        }
                        if (!in_array($term_id . '_' . $filename, $list_imported) || empty($list_imported)) {
                            $request = new OneDrive_Http_Request($downloadurl, 'GET');
                            $httpRequest = $client->getAuth()->authenticatedRequest($request);
                            $content = $httpRequest->getResponseBody();
                            $infos = pathinfo($filename);
                            $extension = $infos['extension'];

                            include_once 'includes/mime-types.php';
                            $mimeType = getMimeType($extension);
                            $status = $this->insertAttachmentMetadata(
                                $id,
                                $upload_dir['path'],
                                $upload_dir['url'],
                                $filename,
                                $content,
                                $mimeType,
                                $extension,
                                $term_id
                            );
                            if ($status) {
                                $i++;
                            }
                        }
                    }
                }
            }
            wp_send_json(array('status' => true, 'percent' => '100')); // run again ajax
        }
        wp_send_json(array('status' => false));
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
        $list_imported = get_option('wpmf_odvfiles_imported');
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
                update_option('wpmf_odvfiles_imported', $list_imported);
            }
            return true;
        }
        return false;
    }

    /**
     * Download file
     */
    public function downloadFile()
    {
        if (empty($_REQUEST['id'])) {
            wp_send_json(array('status' => false));
        }
        $client_service = $this->getClientServer();
        $client = $client_service['client'];
        $service = $client_service['service'];
        $item = $service->items->get($_REQUEST['id'], array("expand" => $this->apifilefields));

        /* get the last-modified-date of this very file */
        $lastModified = strtotime($item->getLastModifiedDateTime());
        /* get a unique hash of this file (etag) */
        $etagFile = $item->getEtag();
        /* get the HTTP_IF_MODIFIED_SINCE header if set */
        $ifModifiedSince = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false);
        /* get the HTTP_IF_NONE_MATCH header if set (etag: unique file hash) */
        $etagHeader = (isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);

        header("Last-Modified: " . gmdate("D, d M Y H:i:s", $lastModified) . " GMT");
        header("Etag: $etagFile");
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 60 * 5) . ' GMT');
        header('Cache-Control: must-revalidate');

        /* check if page has changed. If not, send 304 and exit */
        if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastModified || $etagHeader == $etagFile) {
            header("HTTP/1.1 304 Not Modified");
            exit;
        }
        $downloadlink = '';
        $result = $service->items->download($item->getId());
        if ($result || isset($result['location'])) {
            $downloadlink = $result['location'];
        }

        $downloadurl = $downloadlink . '?download';

        $request = new OneDrive_Http_Request($downloadurl, 'GET');

        $httpRequest = $client->getAuth()->authenticatedRequest($request);
        if ($httpRequest->getResponseHttpCode() == 200) {
            $contenType = 'application/octet-stream';
            $this->downloadHeader($item->getName(), (int)$item->getSize(), $contenType);
            echo $httpRequest->getResponseBody();
        }
        die();
    }

    /**
     * Send a raw HTTP header
     * @param string $file file name
     * @param int $size file size
     * @param $contentType
     * @internal param string $contenType content type
     */
    public function downloadHeader($file, $size, $contentType)
    {
        @ob_end_clean();
        ob_start();
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $contentType);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        if ($size != 0) {
            header('Content-Length: ' . $size);
        }
        ob_clean();
        flush();
    }

    /**
     * Preview file
     */
    public function previewFile()
    {
        if (!current_user_can('upload_files')) {
            wp_send_json(array('status' => false));
        }
        //====================
        if (empty($_REQUEST['id'])) {
            wp_send_json(array('status' => false));
        }
        $client_service = $this->getClientServer();
        $client = $client_service['client'];
        $service = $client_service['service'];
        $item = $service->items->get($_REQUEST['id'], array("expand" => $this->apifilefields));

        /* get the last-modified-date of this very file */
        $lastModified = strtotime($item->getLastModifiedDateTime());
        /* get a unique hash of this file (etag) */
        $etagFile = $item->getEtag();
        /* get the HTTP_IF_MODIFIED_SINCE header if set */
        $ifModifiedSince = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false);
        /* get the HTTP_IF_NONE_MATCH header if set (etag: unique file hash) */
        $etagHeader = (isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);

        header("Last-Modified: " . gmdate("D, d M Y H:i:s", $lastModified) . " GMT");
        header("Etag: $etagFile");
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 60 * 5) . ' GMT');
        header('Cache-Control: must-revalidate');

        /* check if page has changed. If not, send 304 and exit */
        if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastModified || $etagHeader == $etagFile) {
            header("HTTP/1.1 304 Not Modified");
            exit;
        }

        $embeddedlink = $this->getembeddedlink($client, $item->getId());
        header('Location: ' . $embeddedlink);
        die();
    }

    /**
     * Our own version of parse_str that allows for multiple variables
     * with the same name.
     * @param $string - the query string to parse
     * @return array
     */
    private function parseQuery($string)
    {
        $return = array();
        $parts = explode("&", $string);
        foreach ($parts as $part) {
            list ($key, $value) = explode('=', $part, 2);
            $value = urldecode($value);
            if (isset($return[$key])) {
                if (!is_array($return[$key])) {
                    $return[$key] = array(
                        $return[$key]
                    );
                }
                $return[$key][] = $value;
            } else {
                $return[$key] = $value;
            }
        }
        return $return;
    }

    /*
     * ajax get embed url
     */
    public function getEmbedFile()
    {
        if (!current_user_can('upload_files')) {
            wp_send_json(array('status' => false));
        }
        if (empty($_REQUEST['id'])) {
            wp_send_json(array('status' => false));
        }
        $client_service = $this->getClientServer();
        $client = $client_service['client'];
        $embeddedlink = $this->getembeddedlink($client, $_REQUEST['id']);
        $embeddedlink .= '&wdHideHeaders=True';
        $embeddedlink .= '&wdDownloadButton=False&o=OneUp';
        $embeddedlink = str_replace('&amp;', '&', $embeddedlink);
        $info_link = parse_url($embeddedlink);
        $queryParams = $this->parseQuery($info_link['query']);

        $new_url = $info_link['scheme'] . '://' . $info_link['host'] . '/?
        authkey=' . $queryParams['authkey'] . '&cid=' . $queryParams['cid'] . '&
        id=' . $queryParams['resid'] . '&o=OneUp';
        wp_send_json(array('status' => true, 'src' => $new_url));
    }

    /**
     * get embed link
     * @param null $client OneDrive_Client class
     * @param int|string $id id of file
     * @return bool
     */
    public function getembeddedlink($client = null, $id = 0)
    {
        $driveId = explode('!', $id);

        /* New OneDrive API removes 0 from start, but is required for Live SDK */
        $driveId = str_pad($driveId[0], 16, "0", STR_PAD_LEFT);

        $httpRequest = new OneDrive_Http_Request(
            'https://apis.live.net/v5.0/file.' . strtolower($driveId) . '.' . $id . '/embed',
            'GET',
            false,
            false
        );
        $httpRequest = $client->getAuth()->sign($httpRequest);
        $response = $client->getIo()->makeRequest($httpRequest);
        $body = json_decode($response->getResponseBody(), true);
        if (!isset($body['embed_html'])) {
            return false;
        }
        preg_match('/src="([^"]+)"/', $body['embed_html'], $match);
        $embeddedlink = $match[1];
        return $embeddedlink;
    }

    /**
     * get client and server
     * @return array|bool
     */
    public function getClientServer()
    {
        $onedrive_config = get_option('_wpmfAddon_onedrive_config');
        $client = new OneDrive_Client();
        $client->setClientId($onedrive_config['OneDriveClientId']);
        $client->setClientSecret($onedrive_config['OneDriveClientSecret']);
        $client->setAccessToken($onedrive_config['current_token']);
        if ($client === null) {
            return false;
        }

        $service = new OneDrive_Service_Drive($client);
        return array('client' => $client, 'service' => $service);
    }

    /**
     * Sort files
     * @param array $a array to sort
     * @param string $subkey orderby
     * @param string $direction order
     * @return array
     */
    public function subValSort($a, $subkey, $direction)
    {
        if (empty($a)) {
            return $a;
        }
        foreach ($a as $k => $v) {
            $b[$k] = strtolower($v->$subkey);
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
}
