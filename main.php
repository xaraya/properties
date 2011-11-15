<?php
/**
 * Qaptcha Property
 *
 * @package properties
 * @subpackage qaptcha property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('modules.dynamicdata.class.properties.base');

class QaptchaProperty extends DataProperty
{
    public $id   = 30119;
    public $name = 'qaptcha';
    public $desc = 'Qaptcha';
    public $reqmodules = array();

    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);

        $this->tplmodule = 'auto';
        $this->template =  'qaptcha';
        $this->filepath   = 'auto';
    }

    public function validateValue($value = null)
    {
        return true;
    }
}

?>
