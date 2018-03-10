<?php 
/**
 * Counter Property
 * 
 * @package properties
 * @subpackage counter property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

/**
 * Args:
 * 
 * int initialization_counter_module the module this counter belongs to
 * string initialization_counter_store the variable the counter value is stored in
 * string initialization_counter_value a comma delimited string consisting of prefix and numeric value of the counter
 * int initialization_counter_allow_prefix_change defines whether the prefix can be changed
 */
sys::import('modules.base.xarproperties.textbox');

class CounterProperty extends TextBoxProperty
{
    public $id   = 30108;
    public $name = 'counter';
    public $desc = 'Counter';
    public $reqmodules = array();

    private $counter_module;
    private $counter_store;

    public $initialization_counter_module;
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

        // The module the counter belongs to; default is DD module
        if (empty($this->initialization_counter_module)) $this->initialization_counter_module = 'dynamicdata';
        $module = $this->initialization_counter_module;
        
        // The variable the counter is stored in
        $store  = $this->initialization_counter_store;
        
        if (!empty($store)) {
            // Might have a variable or function, so evaluate it
            @eval('$evaled = ' . $store .';');
            $store = isset($evaled) ? $evaled : null;
            // We add a prefix to make it more human readable
            $store  = 'counter_' . $store;
        
            // Get the currently stored counter value in the (assumed) current store
            $value = xarModVars::get($module, $store);
        
            // If the value is empty it means the modvar doesn't exist. So we create it.
            if ((NULL == $value)) {
                $parts = explode(',',$this->initialization_counter_store);
                $counterparts = explode(',',$this->initialization_counter_value);
                xarModVars::set($module,$store,serialize($counterparts));
            }
        
            // Store the module and store var for reuse
            $this->counter_module = $module;
            $this->counter_store = $store;
        
            // When no $args['value'] is present, this would make the value the default value,
            // but we cannot use the default value here.
            // We get around this by populating the value directly and so avoid that standard 
            // behavior in the parent
            if(!isset($args['value'])) {
                $args['value'] = $this->getCounterValue();
                $descriptor->setArgs($args);
                $this->value = $args['value'];
            } 
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

    public function updateValue($itemid)
    {
        // With this we update the counter store to contain the latest value
        $this->setCounterValue($this->value);
    }

    private function setCounter()
    {
        if (empty($this->counter_module)) {
            $this->counter = array('',1);
        } else {
            try {
                $counter = unserialize(xarModVars::get($this->counter_module,$this->counter_store));
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
        
        // Save the counter
        xarModVars::set($this->counter_module,$this->counter_store,serialize(array($counterprefix,$counternumber)));
    }
}
?>