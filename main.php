<?php 
/**
 * IconDropdown Property
 * 
 * @package properties
 * @subpackage icondropdown property
 * @category Third Party Xaraya Property
 * @version 1.1.0
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('modules.base.xarproperties.dropdown');

/**
 * IconCheckbox Property
 * @author Marc Lutolf (mfl@netspan.ch)
 */
class IconDropdownProperty extends SelectProperty
{
    public $id         = 30123;
    public $name       = 'icondropdown';
    public $desc       = 'IconDropdown';
    public $reqmodules = array();

    public $icon_options = array();
    public $initialization_icon_directory    = 'set1';
    public $initialization_icon_options   = '0,red-12.png;
                                             1,yellow-12-png;
                                             2,orange-12.png;
                                             3,green-12.png;
                                             4,blue-12.png;
                                             5,clear-12.png;';

    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule  = 'auto';
        $this->filepath   = 'auto';
        $this->template   = 'icondropdown';
        
    }

    function showInput(Array $data=array())
    {
        $data['template'] = 'dropdown';
        return parent::showInput($data);
    }

    function showOutput(Array $data=array())
    {
        if (!empty($data['value'])) $this->value = $data['value'];
        if (!empty($data['icon_directory'])) $this->initialization_icon_directory = $data['icon_directory'];
        if (!empty($data['icon_options'])) $this->initialization_icon_options = $data['icon_options'];

        // If we have icon options passed, take them.
        if (isset($data['icon_options'])) $this->icon_options = $data['icon_options'];
        // get the icon option corresponding to this value
        $result = $this->getIconOption();
        // only apply xarVarPrepForDisplay on strings, not arrays et al.
        if (!empty($result) && is_string($result)) $result = xarVarPrepForDisplay($result);
        $data['icon_option'] = $result;
        return parent::showOutput($data);
    }

    function getOptions()
    {
        $options = array();
        $lines = explode(';',$this->initialization_icon_options);
        // remove the last (empty) element
        array_pop($lines);
        foreach ($lines as $option)
        {
            // allow escaping \, for values that need a comma
            if (preg_match('/(?<!\\\),/', $option)) {
                // if the option contains a , we'll assume it's an id,name combination
                list($id,$name) = preg_split('/(?<!\\\),/', $option);
                $id = trim(strtr($id,array('\,' => ',')));
                $name = trim(strtr($name,array('\,' => ',')));
                array_push($options, array('id' => $id, 'name' => $name));
            } else {
                // otherwise we'll use the option for both id and name
                $option = trim(strtr($option,array('\,' => ',')));
                array_push($options, array('id' => $option, 'name' => $option));
            }
        }
        return $options;
    }
    /**
     * Retrieve or check an individual option on demand
     *
     * @param  $check boolean
     * @return if check == false:
     *                - display value, if found, of an option whose store value is $this->value
     *                - $this->value, if not found
     * @return if check == true:
     *                - true, if an option exists whose store value is $this->value
     *                - false, if no such option exists
     */
    function getIconOption($check = false)
    {
        if (!isset($this->value)) {
             if ($check) return true;
             return null;
        }

        // we're interested in one of the known options (= default behaviour)
        if (count($this->icon_options) > 0) {
            $options = $this->icon_options;
        } else {
            $options = $this->getIconOptions();
        }
        foreach ($options as $option) {
            if ($option['id'] == $this->value) {
                if ($check) return true;
                return $option['name'];
            }
        }
        if ($check) return false;
        return $this->value;
    }
}
?>