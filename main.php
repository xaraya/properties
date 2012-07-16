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
    public $id         = 30125;
    public $name       = 'jsupload';
    public $desc       = 'JSUpload';
    public $reqmodules = array();

    public $initialization_basedirectory    = 'var/uploads1';
    public $initialization_subdirectories   = array('files','thumbnails');

    public function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->template  = 'jsupload';
        $this->filepath  = 'auto';
    }
    
    function showInput(Array $data=array())
    {
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
        
        // The cache key is the name of the file the cached contents are stored in 
        $data['key'] = $cacheKey;
        return parent::showInput($data);
    }
    
    function encrypt(Array $data=array())
    {
        // Transform the array into a string
        $string = json_encode($data);
        
        // Encrypt the string
        sys::import('xaraya.encryptor');
        $encryptor = xarEncryptor::instance();
        return $encryptor->encrypt($string);
    }
    
    private function createdirs()
    {
        if (!file_exists($this->initialization_basedirectory) || !is_dir($this->initialization_basedirectory)) {
            mkdir($this->initialization_basedirectory);
        }
        foreach($this->initialization_subdirectories as $dir) {
            if (!file_exists($this->initialization_basedirectory . "/" . $dir) || !is_dir($this->initialization_basedirectory . "/" . $dir)) {
                mkdir($this->initialization_basedirectory . "/" . $dir);
            }
        }
        return true;
    }
}

?>