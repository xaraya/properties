<?php
/*
 * jQuery File Upload Plugin PHP Example
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

require('xarUploadHandler.php');
// Do not initialize the uploader yet. We want to configure stuff
$upload_handler = new UploadHandler(array(),$diroffset,false);
/* End Xaraya adaptation */

/* Begin Xaraya adaptation */
$key = isset($_GET['key']) ? $_GET['key'] : '';

// Set the base URL where the files live
$unpacked_key = base64_decode($key);
$unpacked_key = explode('::',$unpacked_key);
// Get the base URL of this site
$base_url = $unpacked_key[0];
$upload_handler->seturl($base_url);

// Get the file that contains the configuration of storage directories and URLs
$directory_array = $unpacked_key[1];
$directory_array = $upload_handler->decrypt($directory_array);

// Add the file info to the options we already have
$upload_handler->setoptions($directory_array);

// Now go ahead and initialize
$upload_handler->initialize();
/* End Xaraya adaptation */
