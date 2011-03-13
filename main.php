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

class CurrencyProperty extends ObjectRefProperty
{
    public $id         = 30012;
    public $name       = 'currency';
    public $desc       = 'Currency';
    public $reqmodules = array();

    public $initialization_refobject    = 'currencies';
    public $initialization_store_prop   = 'iso_code';
    public $initialization_display_prop = 'name';

    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        
        $this->tplmodule = 'auto';
        $this->filepath  = 'auto';
    }
    
    function showInput(Array $data=array())
    {
        if (!empty($data['store_prop'])) $this->initialization_store_prop = $data['store_prop'];
        if (!empty($data['display_prop'])) $this->initialization_display_prop = $data['display_prop'];
        return parent::showInput($data);
    }

    function showOutput(Array $data=array())
    {
        if (!empty($data['store_prop'])) $this->initialization_store_prop = $data['store_prop'];
        if (!empty($data['display_prop'])) $this->initialization_display_prop = $data['display_prop'];
        return parent::showOutput($data);
    }

    function getOptions()
    {
        $options = $this->getFirstline();
        if (count($this->options) > 0) {
            if (!empty($firstline)) $this->options = array_merge($options,$this->options);
            return $this->options;
        }
        
        sys::import('modules.dynamicdata.class.properties.master');
        $property = DataPropertyMaster::getProperty(array('name' => 'objectref'));
        $property->initialization_refobject = 'currencies';
        $property->initialization_store_prop = $this->initialization_store_prop;
        $property->initialization_display_prop = $this->initialization_display_prop;
        $options = $property->getOptions();
        return $options;
    }
}
?>