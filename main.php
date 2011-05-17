<?php 
/**
 * Captcha Property
 *
 * @package properties
 * @subpackage captcha property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('modules.dynamicdata.class.properties.base');

class CaptchaProperty extends DataProperty
{
    public $id         = 30082;
    public $name       = 'captcha';
    public $desc       = 'Captcha';
    public $reqmodules = array();

    public $display_size                    = 25;
    public $validation_min_length           = 4;
    public $validation_max_length           = 30;

    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule  = 'auto';
        $this->template   ='captcha';
        $this->filepath   = 'auto';
    }

    public function validateValue($value = null)
    {
        sys::import('properties.captcha.class.securimage.securimage');
        $securimage = new Securimage();
        if ($securimage->check($value) == false) {
            $this->invalid = xarML('The characters entered do not correspond to those on the image');
            $this->value = null;
            return false;
        }

        return true;
    }
}

?>