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

// sys::import('modules.base.xarproperties.fileupload');
 sys::import('modules.dynamicdata.class.properties.base');

/**
 * Upload property using JS
 */
//class JSUploadProperty extends FileUploadProperty
class JSUploadProperty extends DataProperty
{
    protected $debug   = false;
    
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
        if (!empty($data['initialization_basedirectory'])) $this->initialization_basedirectory = $data['initialization_basedirectory'];
        if (!empty($data['debug'])) $this->initialization_debug = $data['debug'];
        $this->createdirs();
        if (empty($data['context'])) $data['context'] = ' ';
        if (empty($data['id'])) $data['id'] = $this->id;
        $data['config'] = md5($data['context'] . "-" . $data['id']);
        $configs = array(
            'upload_dir' => realpath($this->initialization_basedirectory .'/files') . "/",
            'upload_url' => xarServer::getBaseURL() . $this->initialization_basedirectory .'/files/',
            'thumbnail_upload_dir' => realpath($this->initialization_basedirectory .'/thumbnails') . "/",
            'thumbnail_upload_url' => xarServer::getBaseURL() . $this->initialization_basedirectory .'/thumbnails/',
        );
        $data['property_configs'] = $this->encrypt($configs);
        
        // Cache the configuration if it is not already done
        sys::import('xaraya.caching');
        $fileCache = xarCache::getStorage(array(
            'storage' => 'filesystem',
            'cachedir' => sys::varpath() . '/cache',
            'type' => 'ajax',
        ));
        $cacheKey = $data['config'];
        
        if (!$fileCache->isCached($cacheKey)) {
            $fileCache->setCached($cacheKey,$data['property_configs']);
        }
        
        // The key to pass to the ajax server file is the URL of the site in question + the name of the file the cached contents are stored in
        $data['key'] = base64_encode(xarServer::getBaseURL() . '::' . $cacheKey);
        return parent::showInput($data);
    }
    
    function encrypt(Array $data=array())
    {
        // Transform the array into a string
        $string = json_encode($data);
        
        // Encrypt the string
        sys::import('xaraya.encryptor');
        $encryptor = xarEncryptor::instance();
        if (!$this->debug) {
            $string = $encryptor->encrypt($string);
        } else {
            file_put_contents("Sent_" . time() . ".txt", $string);
        }
        return $string;
    }
    
    private function createdirs()
    {
        if (!file_exists($this->initialization_basedirectory) || !is_dir($this->initialization_basedirectory)) {
            mkdir($this->initialization_basedirectory);
        }
        $subdirs = explode(',',$this->initialization_subdirectories);
        foreach($subdirs as $dir) {
            if (!file_exists($this->initialization_basedirectory . "/" . trim($dir)) || !is_dir($this->initialization_basedirectory . "/" . trim($dir))) {
                mkdir($this->initialization_basedirectory . "/" . trim($dir));
            }
        }
        return true;
    }
}

?>