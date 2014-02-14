<?php 
/**
 * Currency Property
 * 
 * @package properties
 * @subpackage currency property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('modules.dynamicdata.xarproperties.objectref');

/**
 * Currency Property
 * @author Marc Lutolf (mfl@netspan.ch)
 */
class CurrencyProperty extends ObjectRefProperty
{
    public $id         = 30012;
    public $name       = 'currency';
    public $desc       = 'Currency';
    public $reqmodules = array();

    public $initialization_refobject    = 'currency';    // Name of the object we want to reference
    public $initialization_store_prop   = 'iso_code';         // Name of the property we want to use for storage
    public $initialization_display_prop = 'name';             // Name of the property we want to use for displaying.

    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->filepath   = 'auto';
    }

    public function showInput(Array $data = array())
    {
        // Set the class parameter and allow it to be overridden
        if (!isset($data['class'])) $data['class'] = 'xar-dropdown-currency';
        return parent::showInput($data);
    }
}

?>