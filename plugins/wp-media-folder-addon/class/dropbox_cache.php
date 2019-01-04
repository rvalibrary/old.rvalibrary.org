<?php
require_once 'Dropbox/autoload.php';
require_once 'includes/mime-types.php';

/**
 * Get thumbnail
 * @param string $dropboxToken dropbox token
 * @param string $path path of dropbox file
 * @return array
 * @throws \WPMFDropbox\Exception_BadResponseCode
 * @throws \WPMFDropbox\Exception_InvalidAccessToken
 * @throws \WPMFDropbox\Exception_RetryLater
 * @throws \WPMFDropbox\Exception_ServerError
 */
function dbxGetThumb($dropboxToken, $path)
{
    $dropbox = getAccount($dropboxToken);
    $info = pathinfo($path);
    $mimetype = getMimeType($info['extension']);
    if ($info['extension'] == 'pdf') {
        $shared_links = $dropbox->create_shared_link($path);
        $preview_link = $shared_links['url'] . '&raw=1';
        $res = array('preview_link' => $preview_link);
    } else {
        $thumbnail = $dropbox->getThumbnail($path, 'jpeg', 'w64h64', $mimetype);
        $shared_links = $dropbox->create_shared_link($path);
        $preview_link = $shared_links['url'] . '&raw=1';
        $res = array('thumb' => $thumbnail, 'preview_link' => $preview_link);
    }
    return $res;
}

/**
 * Get dropbox client
 * @param $dropboxToken
 * @return \WPMFDropbox\Client
 */
function getAccount($dropboxToken)
{
    $dbxClient = new WPMFDropbox\Client($dropboxToken, 'WpmfAddon/1.0');
    return $dbxClient;
}

//get the last-modified-date of this very file
$lastModified = filemtime(__FILE__);
//get a unique hash of this file (etag)
$etagFile = md5_file(__FILE__);
//get the HTTP_IF_MODIFIED_SINCE header if set
$ifModifiedSince = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false);
//get the HTTP_IF_NONE_MATCH header if set (etag: unique file hash)
$etagHeader = (isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);

//set last-modified header
header("Last-Modified: " . gmdate("D, d M Y H:i:s", $lastModified) . " GMT");
//set etag-header
header("Etag: $etagFile");
//make sure caching is turned on
header('Cache-Control: public');

//check if page has changed. If not, send 304 and exit
if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastModified || $etagHeader == $etagFile) {
    header("HTTP/1.1 304 Not Modified");
    exit;
}

$dropboxToken = urldecode($_REQUEST['dropboxToken']);
$path = urldecode($_REQUEST['path']);
$res = dbxGetThumb($dropboxToken, $path);
//your normal code
echo json_encode($res);
die();
