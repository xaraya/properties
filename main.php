<?php
/**
 * JSUpload Property
 *
 * @package properties
 * @subpackage jsupload property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2012 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

 sys::import('modules.dynamicdata.class.properties.base');

/**
 * Upload property using JS
 */

/**
 * This code:
 * - Displays the uploader in a template
 * - Creates the necessary directories and subdirectories to store uploads as per defintions
 * - Creates a entry i nthe ajax cache for uploads from this property
 */
/**
 * Notes:
 * - The ajax cache holds encrypted cache defintions. Each of these gives us the information
 *   as to where the uploads from a given jsupload property are stored
 * - The actually storage directories look to be files and thumbnails subirectories of a given
 *   base directory
 * - None of the code in this file does any uploading. That is done by xarUploadHandler.php
 */

class JSUploadProperty extends DataProperty
{
    public $debug   = false;
    
    public $id         = 30125;
    public $name       = 'jsupload';
    public $desc       = 'JSUpload';
    public $reqmodules = array();

    public $initialization_basedirectory    = 'var/uploads';
    public $initialization_subdirectories   = 'files,thumbnails';

    public function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->template  = 'jsupload';
        $this->filepath  = 'auto';

        // Make sure we have an directory to pass ajax configurations
        if (!file_exists(sys::varpath() . '/cache/ajax')) {
            mkdir(sys::varpath() . '/cache/ajax');
        }
    }
    
    function showInput(Array $data=array())
    {
        // Check for a base directory if passed
        if (!empty($data['basedirectory'])) $this->initialization_basedirectory = $data['basedirectory'];
        // Check for a debug flag if passed
        if (!empty($data['debug'])) $this->debug = $data['debug'];
        
        // If they don't yet exist, then create the base directory and any subdirectories defined above
        $this->createdirs();
        
        // The context is just some string setting a scope for the uploads
        // This can default to something like the template name the property lives on, and so on
        if (empty($data['context'])) $data['context'] = ' ';
        // Make the ID of this property part of the context string
        if (empty($data['id'])) $data['id'] = $this->id;
        // Concatenate the two
        $data['config'] = md5($data['context'] . "-" . $data['id']);
        
        // Get the set of directories based on our definitions
        // CHECKME: This sort of assumes files nd thumbnails subdirectories are given
        $base_url = xarServer::getBaseURL();
        $file_dir = realpath($this->initialization_basedirectory .'/files') . "/";
        $file_url = $base_url . $this->initialization_basedirectory .'/files/';
        $thumbnail_dir = realpath($this->initialization_basedirectory .'/thumbnails') . "/";
        $thumbnail_url = $base_url . $this->initialization_basedirectory .'/thumbnails/';
        $configs = array(
            'upload_dir' => $file_dir,
            'upload_url' => $file_url,
            'thumbnail_upload_dir' => $thumbnail_dir,
            'thumbnail_upload_url' => $thumbnail_url,
        );

        // Create an encrypted string from it
        $data['property_configs'] = $this->encrypt($configs);
        
        // Cache the configuration if it is not already done
        sys::import('xaraya.caching');
        $fileCache = xarCache::getStorage(array(
            'storage' => 'filesystem',
            'cachedir' => sys::varpath() . '/cache',
            'type' => 'ajax',
        ));
        // The key contains the context and property ID as per above
        $cacheKey = $data['config'];
        
        // store it in the ajax cache
        if (!$fileCache->isCached($cacheKey)) {
            $fileCache->setCached($cacheKey,$data['property_configs']);
        }
        
        // The key to pass to the ajax server file is the URL of the site in question + the name of the file the cached contents are stored in
        $data['key'] = base64_encode($base_url . '::' . $cacheKey);

        // Debug code
        $isadmin = xarIsParent('Administrators', xarUser::getVar('uname'));
        if ($isadmin && $this->debug) {
            echo "File directory: " . $file_dir . "<br/>";
            echo "File URL: " . $file_url . "<br/>";
            echo "Thumbnail directory: " . $thumbnail_dir . "<br/>";
            echo "Thumbnail URL: " . $thumbnail_url . "<br/>";
            echo "Cache directory: " . sys::varpath() . '/cache' . "<br/>";
            echo "Context: " . $data['context'] . "<br/>";
            echo "ID: " . $data['id'] . "<br/>";
            echo "Cache key: " . $data['config'] . "<br/>";
            echo "Base URL: " . $base_url . "<br/>";
            echo "AJAX key: " . $data['key'] . "<br/>";
        }

        return parent::showInput($data);
    }
    
    function encrypt(Array $data=array())
    {
        // Transform the array into a string
        $string = json_encode($data);
        
        // Encrypt the string
        sys::import('xaraya.encryptor');
        $encryptor = xarEncryptor::instance();
        try {$encrypted_string = $encryptor->encrypt($string);} catch (Exception $e) {}
        if ($this->debug) {
            xarLog::message("Properties::jsupload: encrypted $string to $encrypted_string", xarLog::LEVEL_DEBUG);
        }
        return $encrypted_string;
    }
    
    private function createdirs()
    {
        if (!file_exists($this->initialization_basedirectory) || !is_dir($this->initialization_basedirectory)) {
            mkdir($this->initialization_basedirectory, 0755, true);
        }
        $subdirs = explode(',',$this->initialization_subdirectories);
        foreach($subdirs as $dir) {
            if (!file_exists($this->initialization_basedirectory . "/" . trim($dir)) || !is_dir($this->initialization_basedirectory . "/" . trim($dir))) {
                mkdir($this->initialization_basedirectory . "/" . trim($dir), 0755, true);
            }
        }
        return true;
    }
}

?>