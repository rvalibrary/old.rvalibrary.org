<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class wpmfAddonHelper
 */
class wpmfAddonHelper
{

    /**
     * Get cloud configs
     * @return mixed
     */
    public static function getAllCloudConfigs()
    {
        $default = array(
            'googleClientId' => '',
            'googleClientSecret' => '');
        return get_option('_wpmfAddon_cloud_config', $default);
    }

    /**
     * Save cloud configs
     * @param $data
     * @return bool
     */
    public static function saveCloudConfigs($data)
    {
        $result = update_option('_wpmfAddon_cloud_config', $data);
        return $result;
    }

    /**
     * Get cloud configs by name
     * @param $name
     * @return array|null
     */
    public static function getDataConfigBySeverName($name)
    {
        $googleDriveParams = array();
        if (self::getAllCloudConfigs()) {
            foreach (self::getAllCloudConfigs() as $key => $val) {
                if (strpos($key, 'google') !== false) {
                    $googleDriveParams[$key] = $val;
                }
            }

            $result = null;
            switch ($name) {
                case 'google':
                    $result = $googleDriveParams;
                    break;
            }
            return $result;
        }
        return null;
    }

    /**
     * Get all cloud configs
     * @return mixed
     */
    public static function getAllCloudParams()
    {
        return get_option('_wpmfAddon_cloud_category_params');
    }

    /**
     * Set cloud configs
     * @param $cloudParams
     * @return bool
     */
    public static function setCloudConfigsParams($cloudParams)
    {
        $result = update_option('_wpmfAddon_cloud_category_params', $cloudParams);
        return $result;
    }

    /**
     * @return mixed
     */
    public static function getGoogleDriveParams()
    {
        $params = self::getAllCloudParams();
        return isset($params['googledrive']) ? $params['googledrive'] : false;
    }

    /**
     * Save Cloud configs
     * @param $key
     * @param $val
     */
    public static function setCloudParam($key, $val)
    {
        $params = self::getAllCloudConfigs();
        $params[$key] = $val;
        self::saveCloudConfigs($params);
    }


    /**
     * Get termID
     * @param $googleDriveId
     * @return bool
     */
    public static function getTermIdGoogleDriveByGoogleId($googleDriveId)
    {
        $returnData = false;
        $googleParams = self::getGoogleDriveParams();
        if ($googleParams) {
            foreach ($googleParams as $key => $val) {
                if ($val['idCloud'] == $googleDriveId) {
                    $returnData = $val['termId'];
                }
            }
        }
        return $returnData;
    }

    /**
     * Get google drive data by term id
     * @param $termId
     * @return bool
     */
    public static function getGoogleDriveIdByTermId($termId)
    {
        $returnData = false;
        $googleParams = self::getGoogleDriveParams();
        if ($googleParams) {
            foreach ($googleParams as $key => $val) {
                if ($val['termId'] == $termId) {
                    $returnData = $val['idCloud'];
                }
            }
        }
        return $returnData;
    }

    /**
     * Get category id by cloud ID
     * @param $cloud_id
     * @return bool
     */
    public static function getCatIdByCloudId($cloud_id)
    {
        $returnData = false;
        $googleParams = self::getGoogleDriveParams();
        if ($googleParams) {
            foreach ($googleParams as $key => $val) {
                if ($val['idCloud'] == $cloud_id) {
                    $returnData = $val['termId'];
                }
            }
        }
        return $returnData;
    }

    /**
     * Get all google drive id
     * @return array
     */
    public static function getAllGoogleDriveId()
    {
        $returnData = array();
        $googleParams = self::getGoogleDriveParams();
        if ($googleParams) {
            foreach ($googleParams as $key => $val) {
                $returnData[] = $val['idCloud'];
            }
        }
        return $returnData;
    }

    /**
     * @return float
     */
    public static function curSyncInterval()
    {
        //get last_log param
        $config = self::getAllCloudConfigs();
        if (isset($config['last_log']) && !empty($config['last_log'])) {
            $last_log = $config['last_log'];
            $last_sync = (int)strtotime($last_log);
        } else {
            $last_sync = 0;
        }

        $time_new = (int)strtotime(date('Y-m-d H:i:s'));
        $timeInterval = $time_new - $last_sync;
        $curtime = $timeInterval / 60;

        return $curtime;
    }

    /**
     * Get extension
     * @param $file
     * @return string
     */
    public static function getExt($file)
    {
        $dot = strrpos($file, '.') + 1;

        return substr($file, $dot);
    }

    /**
     * Strips the last extension off of a file name
     *
     * @param   string $file The file name
     *
     * @return  string  The file name without the extension
     *
     * @since   11.1
     */
    public static function stripExt($file)
    {
        return preg_replace('#\.[^.]*$#', '', $file);
    }

    /**
     * write log only in debug mode
     * @param $log
     */
    public static function writeLog($log)
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }

    /*----------- Dropbox -----------------*/
    /**
     * Get all dropbox configs
     * @return mixed
     */
    public static function getAllDropboxConfigs()
    {
        $default = array(
            'dropboxKey' => '',
            'dropboxSecret' => '',
            'dropboxSyncTime' => '5',
            'dropboxSyncMethod' => 'sync_page_curl');
        return get_option('_wpmfAddon_dropbox_config', $default);
    }

    /**
     * Save dropbox config
     * @param $data
     * @return bool
     */
    public static function saveDropboxConfigs($data)
    {

        $result = update_option('_wpmfAddon_dropbox_config', $data);
        return $result;
    }

    /**
     * Get dropbox config
     * @param $name
     * @return array|null
     */
    public static function getDataConfigByDropbox($name)
    {
        $DropboxParams = array();

        if (self::getAllDropboxConfigs()) {
            foreach (self::getAllDropboxConfigs() as $key => $val) {
                if (strpos($key, 'dropbox') !== false) {
                    $DropboxParams[$key] = $val;
                }
            }
            $result = null;
            switch ($name) {
                case 'dropbox':
                    $result = $DropboxParams;
                    break;
            }
            return $result;
        }
        return null;
    }

    /**
     * Set dropbox config
     * @param array $dropboxParams params of dropbox
     * @return bool
     */
    public static function setDropboxConfigsParams($dropboxParams)
    {
        $result = update_option('_wpmfAddon_dropbox_category_params', $dropboxParams);
        return $result;
    }

    /**
     * Get dropbox params
     * @return mixed
     */
    public static function getDropboxParams()
    {
        return get_option('_wpmfAddon_dropbox_category_params', array());
    }

    /**
     * get id by termID
     * @param $termId
     * @return bool
     */
    public static function getDropboxIdByTermId($termId)
    {
        $returnData = false;
        $dropParams = self::getDropboxParams();
        if ($dropParams && isset($dropParams[$termId])) {
            $returnData = $dropParams[$termId]['idDropbox'];
        }
        return $returnData;
    }

    /**
     * get dropbox folder id
     * @param $termId
     * @return bool
     */
    public static function getIdFolderByTermId($termId)
    {
        $returnData = false;
        $dropParams = self::getDropboxParams();
        if ($dropParams && isset($dropParams[$termId])) {
            $returnData = $dropParams[$termId]['id'];
        }
        return $returnData;
    }

    /**
     * get term id by Path
     * @param $path
     * @return bool|int|string
     */
    public static function getTermIdByDropboxPath($path)
    {
        $dropbox_list = self::getDropboxParams();
        $result = false;
        $path = strtolower($path);
        if (!empty($dropbox_list)) {
            foreach ($dropbox_list as $k => $v) {
                if (strtolower($v['idDropbox']) == $path) {
                    $result = $k;
                }
            }
        }
        return $result;
    }

    /**
     * get path by id
     * @param $id
     * @return bool
     */
    public static function getPathByDropboxId($id)
    {
        $dropbox_list = self::getDropboxParams();
        $result = false;
        if (!empty($dropbox_list)) {
            foreach ($dropbox_list as $k => $v) {
                if ($v['id'] == $id) {
                    $result = $v['idDropbox'];
                }
            }
        }

        return $result;
    }

    /**
     * @param $params
     * @return bool
     */
    public static function setDropboxFileInfos($params)
    {
        $result = update_option('_wpmfAddon_dropbox_fileInfo', $params);
        return $result;
    }

    /**
     * get dropbox infos
     * @return mixed
     */
    public static function getDropboxFileInfos()
    {
        return get_option('_wpmfAddon_dropbox_fileInfo');
    }

    /**
     * @return float
     */
    public static function curSyncIntervalDropbox()
    {
        //get last_log param
        $config = self::getAllDropboxConfigs();
        if (isset($config['last_log']) && !empty($config['last_log'])) {
            $last_log = $config['last_log'];
            $last_sync = (int)strtotime($last_log);
        } else {
            $last_sync = 0;
        }

        $time_new = (int)strtotime(date('Y-m-d H:i:s'));
        $timeInterval = $time_new - $last_sync;
        $curtime = $timeInterval / 60;
        return $curtime;
    }
}
