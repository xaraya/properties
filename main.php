<?php 
/**
 * Counter Property
 *
 * @package properties
 * @subpackage counter property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2010 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('modules.base.xarproperties.textbox');

class CounterProperty extends TextBoxProperty
{
    public $id   = 30108;
    public $name = 'counter';
    public $desc = 'Counter';
    public $reqmodules = array();

    public $initialization_counter_store;
    public $initialization_counter_value                  = ',0';
    public $initialization_counter_allow_prefix_change    = 0;

    private $counter;
    
    function __construct(ObjectDescriptor $descriptor)
    {
        // We need to set up the counter. First get all the args for it.
        $args = $descriptor->getArgs();
        
        // Parse the configuration to populate the initialization vars
        if (!empty($args['configuration'])) {
            $this->parseConfiguration($args['configuration']);
        }
        // Get the currently stored counter value in the (assumed) current store
        try {
            $parts = explode(',',$this->initialization_counter_store);
            $value = xarModVars::get($parts[0],$parts[1]);
        } catch (Exception $e) {
            $this->initialization_counter_store = 'dynamicdata,' . $this->id;
            $value = xarModVars::get('dynamicdata,',$this->id);
        }
        
        // If the ID is the value of the property's type, it means we are configuring the property.
        // If the value is empty it means the modvar doesn't exist. So we create it.
        if (empty($value) && $this->id != 30108) {
            $parts = explode(',',$this->initialization_counter_store);
            $counterparts = explode(',',$this->initialization_counter_value);
            xarModVars::set($parts[0],$parts[1],serialize($counterparts));
        }        

        // When no $args['value'] is present, this would make the value the default value,
        // but we cannot use the default value here.
        // We get around this by populating the value directly and so avoid that standard 
        // behavior in the parent
        if(!isset($args['value'])) {
            $args['value'] = $this->getCounterValue();
            $descriptor->setArgs($args);
            $this->value = $args['value'];
        } 

        // Now pass the descriptor to the parent. The default value will not be triggered. See the base property code.
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->filepath   = 'auto';
    }

    public function createValue($itemid)
    {
        // With this we update the counter store to contain the latest value
        $this->setCounterValue($this->value);
    }

    private function setCounter()
    {
        if (!empty($this->counter)) return true;
        if (empty($this->initialization_counter_store)) {
            $this->counter = array('',1);
        } else {
            $parts = explode(',',$this->initialization_counter_store);
            try {
                $counter = unserialize(xarModVars::get($parts[0],$parts[1]));
            } catch (Exception $e) {
                throw new Exception(xarML('Missing a proper store for counter property default value'));
            }
            $this->counter = $counter;
        }
        return true;
    }
    
    private function getCounterPrefix()
    {
        $this->setCounter();
        return $this->counter[0];
    }
    
    private function getCounterNumber()
    {
        $this->setCounter();
        $temp = (string)$this->counter[1];
        if (empty($this->counter[1])) $this->counter[1] = 1;
        else $this->counter[1]++;
        $this->counter[1] = str_pad($this->counter[1], strlen($temp), "0", STR_PAD_LEFT);
        return $this->counter[1];
    }
    
    private function getCounterValue()
    {
        $value = $this->getCounterPrefix() . $this->getCounterNumber();
        return $value;
    }

    private function setCounterValue($value)
    {
        // Replace non-digits with "X"
        $transformedvalue = preg_replace ( '#\D#' , 'X' , $value ) ;
        
        // Get the number to the right of the last "X"
        $counternumber = substr($transformedvalue,strrpos($transformedvalue,'X')+1);
        
        // Get the prefix part
        if ($this->initialization_counter_allow_prefix_change) {
            $counterprefix = substr($value,0,strlen($value) - strlen($counternumber));
        } else {
            $counterparts = explode(',',$this->initialization_counter_value);
            $counterprefix = $counterparts[0];
        }
        
        // Get the parts of the store
        $parts = explode(',',$this->initialization_counter_store);
        
        // Save the counter
        xarModVars::set($parts[0],$parts[1],serialize(array($counterprefix,$counternumber)));
    }
}
?>