<?php
/*
 * jQuery File Upload Plugin PHP Example 5.7
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

error_reporting(E_ALL | E_STRICT);

/* Begin Xaraya adaptation */
// Load the minimum code required
$systemConfiguration = array();
$diroffset = '../../../../../../';
include $diroffset . 'var/layout.system.php';
set_include_path(realpath($diroffset . $systemConfiguration['rootDir']) . PATH_SEPARATOR . get_include_path());
include $diroffset . 'bootstrap.php';

require('xar.upload.class.php');
$upload_handler = new xarUploadHandler(array(),$diroffset);
/* End Xaraya adaptation */

header('Pragma: no-cache');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Content-Disposition: inline; filename="files.json"');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');

$property_configs = isset($_GET['key']) ? $_GET['key'] : '';
$options = $upload_handler->decrypt($property_configs);
$upload_handler->setoptions($options);

switch ($_SERVER['REQUEST_METHOD']) {
    case 'OPTIONS':
        break;
    case 'HEAD':
    case 'GET':
        $upload_handler->get();
        break;
    case 'POST':
        if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
            $upload_handler->delete();
        } else {
            $upload_handler->post();
        }
        break;
    case 'DELETE':
        $upload_handler->delete();
        break;
    default:
        header('HTTP/1.1 405 Method Not Allowed');
}
