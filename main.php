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

    public $initialization_basedirectory    = 'var/uploads';

    public function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->template  = 'jsupload';
        $this->filepath  = 'auto';
        
    }
    
    function showInput(Array $data=array())
    {
        $configs = array(
            'upload_dir' => realpath($this->initialization_basedirectory .'/files') . "/",                
            'upload_url' => xarServer::getBaseURL() . $this->initialization_basedirectory .'/files/',   
            'thumbnail_upload_dir' => realpath($this->initialization_basedirectory .'/thumbnails') . "/",                
            'thumbnail_upload_url' => xarServer::getBaseURL() . $this->initialization_basedirectory .'/thumbnails/',                
        );
        $data['property_configs'] = $this->encrypt($configs);
        return parent::showInput($data);
    }
    
    function encrypt(Array $data=array())
    {
        $string = json_encode($data);

        // From http://www.php.net/manual/en/function.mcrypt-encrypt.php
        /*
        $block = mcrypt_get_block_size('des', 'ecb');
        if (($pad = $block - (strlen($string) % $block)) < $block) {
            $string .= str_repeat(chr($pad), $pad);
        }var_dump($string);
        $string = mcrypt_encrypt(MCRYPT_DES, 'dork', $string, MCRYPT_MODE_ECB);
        */
//        $string = mcrypt_encrypt(MCRYPT_DES, '\xc8\xd9', $string, MCRYPT_MODE_ECB);
        $string = base64_encode($string);
        return $string;
    }
}

?>