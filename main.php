<?php
/**
 * Name Property
 *
 * @package properties
 * @subpackage name property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

/**
 * The property's value is stored as a serialized array of the form
 * array(
 *     [array('id' => <field name>, 'value' => <field value>)]      (one or more elements)
 *
 * The components the property can have are of the form
 * array(
 *     [array('id' => <component name>, 'name' => <component label>)]      (one or more elements)
 * Default components displayed are: salutation, first_name, last_name
 * These are given by $this->display_name_components and can be configured
 *
 */

sys::import('modules.base.xarproperties.textbox');

class NameProperty extends TextBoxProperty
{
    public $id         = 30095;
    public $name       = 'name';
    public $desc       = 'Name';
    public $reqmodules = array();

    public $display_name_components;
    public $display_salutation_options;
    public $validation_ignore_validations;
    public $validation_allowempty = true;

    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->template =  'name';
        $this->filepath   = 'auto';
        
        $this->display_name_components = 'salutation,' . xarML('Salutation') . ';first_name,' . xarML('First Name') . ';last_name,' . xarML('Last Name') . ';';
        $this->display_salutation_options = xarML('Mr.,Mrs.,Ms.');
    }

    public function checkInput($name = '', $value = null)
    {
        $name = empty($name) ? $this->propertyprefix . $this->id : $name;
        $valid = true;
        $invalid = array();
        $value = array();   // We don't allow a value to be passed to this method

        if (!empty($this->display_name_components)) {
            // Use a textbox property for the component values
            $textbox = DataPropertyMaster::getProperty(array('name' => 'textbox'));
            $textbox->validation_allowempty = $this->validation_allowempty;
            $name_components = $this->getNameComponents($this->display_name_components);
            if (!$this->validation_ignore_validations) {
                $textbox->validation_min_length = 2;
            }
            foreach ($name_components as $field) {
                $isvalid = $textbox->checkInput($name . '_' . $field['id']);
                $valid = $valid && $isvalid;
                if ($isvalid) {
                    $value[] = array('id' => $field['id'], 'value' => $textbox->value);
                } else {
                    $invalid[] = strtolower($field['name']);
                }
            }
        }

        if ($valid) {
            $this->value = serialize($value);
        } else {
            $this->value = null;
            $invalid = implode(',',$invalid);
            $this->invalid = xarML('The fields #(1) are not valid', $invalid);
        }
        return $valid;
    }

    public function validateValue($value = null)
    {
        // Dummy method
        xarLog::message("DataProperty::validateValue: Validating property " . $this->name);
        return true;
    }

    public function showInput(Array $data = array())
    {
        if (isset($data['value'])) $this->value = $data['value'];
        $data['value'] = $this->getValueArray();

        // Cater to values as simple strings (errors, old versions etc.)
        if (!is_array($data['value'])) {
            $data['value'] = array(array('id' => 'last_name', 'value' => $data['value']));
        }

        // Rework the arrays to put the id in the index
        $newarray = array();
        foreach($data['value'] as $value) {
            $newarray[$value['id']] = $value;
        }
        $data['value'] = $newarray;

        if (empty($data['name_components'])) $data['name_components'] = $this->display_name_components;
        else $this->display_name_components = $data['name_components'];
        $data['name_components'] = $this->getNameComponents($data['name_components']);

        if (empty($data['salutation_options'])) $data['salutation_options'] = $this->display_salutation_options;
        else $this->display_salutation_options = $data['salutation_options'];
        $data['salutation_options'] = $this->getSalutationOptions($data['salutation_options']);

        return DataProperty::showInput($data);
    }

    public function showOutput(Array $data = array())
    {
        if (isset($data['value'])) $this->value = $data['value'];
        $data['value'] = $this->getValueArray();
        
        // Cater to values as simple strings (errors, old versions etc.)
        if (!is_array($data['value'])) {
            $this->display_name_components = 'last_name,Last Name;';
            $data['value'] = array(array('id' => 'last_name', 'value' => $data['value']));
        }

        if (empty($data['name_components'])) $data['name_components'] = $this->display_name_components;
        else $this->display_name_components = $data['name_components'];
        $data['name_components'] = $this->getNameComponents($data['name_components']);

        // Rework the arrays to put the id in the index
        $newarray = array();
        foreach($data['name_components'] as $component) {
            $newarray[$component['id']] = $component;
        }
        $data['name_components'] = $newarray;
        
        $newarray = array();
        foreach($data['value'] as $value) {
            $newarray[$value['id']] = $value;
        }
        $data['value'] = $newarray;

        if (empty($data['salutation_options'])) $data['salutation_options'] = $this->display_salutation_options;
        else $this->display_salutation_options = $data['salutation_options'];
        $data['salutation_options'] = $this->getSalutationOptions($data['salutation_options']);

        return DataProperty::showOutput($data);
    }

    function showHidden(Array $data = array())
    {
        $data['name']     = !empty($data['name']) ? $data['name'] : 'dd_'.$this->id;
        $data['id']       = !empty($data['id'])   ? $data['id']   : 'dd_'.$this->id;

        $data['invalid']  = !empty($this->invalid) ? xarML('Invalid #(1)', $this->invalid) :'';
        $data['value'] = $this->getValueArray();

        // Cater to values as simple strings (errors, old versions etc.)
        if (!is_array($data['value'])) {
            $data['value'] = array(array('id' => 'last_name', 'value' => $data['value']));
        }

        // Rework the arrays to put the id in the index
        $newarray = array();
        foreach($data['value'] as $value) {
            $newarray[$value['id']] = $value;
        }
        $data['value'] = $newarray;

        if (empty($data['name_components'])) $data['name_components'] = $this->display_name_components;
        else $this->display_name_components = $data['name_components'];
        $data['name_components'] = $this->getNameComponents($data['name_components']);

        if (empty($data['salutation_options'])) $data['salutation_options'] = $this->display_salutation_options;
        else $this->display_salutation_options = $data['salutation_options'];
        $data['salutation_options'] = $this->getSalutationOptions($data['salutation_options']);
        if(!isset($data['module']))   $data['module']   = $this->tplmodule;
        if(!isset($data['template'])) $data['template'] = $this->template;
        if(!isset($data['layout']))   $data['layout']   = $this->layout;

        return DataProperty::showHidden($data);
    }

    public function getValue()
    {
        $valuearray = $this->getValueArray();
        if (!is_array($valuearray)) return $valuearray;

        $value = '';
        foreach ($valuearray as $part) {
            try {
                $name = trim($part['value']);
                if (empty($name)) continue;
                if (empty($value)) $value = $name;
                else $value .= ' ' . $name;
            } catch (Exception $e) {}
        }
        return $value;
    }

/**
 * Get an array of the name property's components
 * 
 * @param index if true return an indexed array of values where the id is the index
 *              otherwise return an array of id and name sutiable for dropdowns
 * return array
 */
    function getValueArray($index=false)
    {
        $value = @unserialize($this->value);
        if (!is_array($value)) return $this->value;

        $components = $this->getNameComponents($this->display_name_components);
        $valuearray = array();
        foreach ($components as $v) {
            $found = false;
            foreach ($value as $part) {
                if ($part['id'] == $v['id']) {
                    if ($index) {
                        $valuearray[$part['id']] = $part['value'];
                    } else {
                        $valuearray[] = array('id' => $v['id'], 'value' => $part['value']);
                    }
                    $found = true;
                    break;
                }
            }
            if (!$found) $valuearray[] = array('id' => $v['id'], 'value' => '');
        }
        
        return $valuearray;
    }

    function getNameComponents($componentstring)
    {
        $components = explode(';', $componentstring);
        
        // remove the last (empty) element
        array_pop($components);
        $componentarray = array();
        foreach ($components as $component)
        {
            // allow escaping \, for values that need a comma
            if (preg_match('/(?<!\\\),/', $component)) {
                // if the component contains a , we'll assume it's an name/displayname combination
                list($name,$displayname) = preg_split('/(?<!\\\),/', $component);
                $name = trim(strtr($name,array('\,' => ',')));
                $displayname = trim(strtr($displayname,array('\,' => ',')));
                $componentarray[] = array('id' => $name, 'name' => $displayname);
            } else {
                // otherwise we'll use the component for both name and displayname
                $component = trim(strtr($component,array('\,' => ',')));
                $componentarray[] = array('id' => $component, 'name' => $component);
            }
        }
        return $componentarray;
    }
  
    function getSalutationOptions($string)
    {
        $items = explode(',', $string);
        $optionarray = array();
        foreach ($items as $item) {
            // allow escaping \, for values that need a comma
            $item = trim(strtr($item,array('\,' => ',')));
            $optionarray[] = array('id' => $item, 'name' => $item);
        }
        return $optionarray;
    }
}
?>