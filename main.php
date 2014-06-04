<?php
/**
 * Address Property
 *
 * @package properties
 * @subpackage address property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

/**
 * The property is stored as a serialized array of the form
 * array(
 *     [array('id' => <fieldname>, 'name' => <field value>)]      (one or more elements)
 *
 * Default fields displayed are: street, city, region, postal_code, country
 *
 * The property has a notion of country and will display a country listing  when it encounters a field called "country"
 * The default layout displays all the fields in a column one below the other in the order configured
 * The layout "country" displays the fields in a country specific layout. In this case the fields must have names 
 * that the property recognizes, and these names can vary according to the country template.
 * If the layout is defined as "country" the property will try and use the specific display associated with the 
 * value of the country field, or fall back to the default display if no such display exists.
 *
 * The difference between the value arrays for country specific and default layouts stems primarily from the fact that
 * in the case of the latter, the sequence of fields is given by the oder in which they appear in the configuration.
 */

sys::import('modules.base.xarproperties.textbox');

class AddressProperty extends TextBoxProperty
{
    public $id         = 30033;
    public $name       = 'address';
    public $desc       = 'Address';
    public $reqmodules = array();

    public $display_address_components = 'street,Street;city,City;postal_code,Postal Code;region,Region;country,Country;';
    public $display_address_default_country = 'us';
    public $validation_ignore_validations;

    public $specified_countries       = array('ch','us');   // The countries that have non-default layout templates in this property

    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->template =  'address';
        $this->filepath   = 'auto';
    }

    public function checkInput($name = '', $value = null)
    {
        $name = empty($name) ? 'dd_'.$this->id : $name;
        $valid = true;
        $invalid = array();
        $value = array();   // We don't allow a value to be passed to this method

        if (!empty($this->display_address_components)) {
            $textbox = DataPropertyMaster::getProperty(array('name' => 'textbox'));
            $address_components = $this->getAddressComponents($this->display_address_components);
            if (!$this->validation_ignore_validations) {
                $textbox->validation_min_length = 2;
            }
            foreach ($address_components as $field) {
                $isvalid = $textbox->checkInput($name . '_' . $field['id']);
                $valid = $valid && $isvalid;
                if ($isvalid) {
                    $value[] = array('id' => $field['id'], 'name' => $textbox->value);
                } else {
                    $invalid[] = strtolower($field['name']);
                }
            }
        }

        if ($valid) {
            $this->value = serialize($value);
        } else {
            $this->value = null;
            $count = count($invalid);
            $invalid = implode(',',$invalid);
            if ($count == 1) {
                $this->invalid = xarML('The field #(1) is not valid', $invalid);
            } else {
                $this->invalid = xarML('The fields #(1) are not valid', $invalid);
            }
        }
        return $valid;
    }

    public function validateValue($value = null)
    {
        // Dummy method
        xarLog::message("DataProperty::validateValue: Validating property " . $this->name);
        return true;
    }

    public function getValue()
    {
        $valuearray = $this->getValueArray();
        $value = '';
        foreach ($valuearray as $part) {
            try {
                if ($part['id'] == 'country') {
                    $country = DataPropertyMaster::getProperty(array('name' => 'countrylisting'));
                    $country->validation_override = true;
                    $country->value = $part['name'];
                    $part['name'] = $country->getOption();
                }
                $name = trim($part['name']);
                if (empty($name)) continue;
                if (empty($value)) $value = $name;
                else $value .= ', ' . $name;
            } catch (Exception $e) {}
        }
        return $value;
    }
    
    public function setValue($value=null) 
    {
        if (empty($value)) $value = array();
        $this->value = serialize($value);
    }

    public function showInput(Array $data = array())
    {
        if (isset($data['module'])) {
            $this->module = $data['module'];
        } else {
            $info = xarController::$request->getInfo();
            $this->module = $info[0];
            $data['module'] = $this->module;
        }
        if (empty($data['address_components'])) $data['address_components'] = $this->display_address_components;
        else $this->display_address_components = $data['address_components'];
        $data['address_components'] = $this->getAddressComponents($data['address_components']);

        if (isset($data['value'])) $this->value = $data['value'];
        $data['value'] = $this->getValueArray();

        // For country specific layouts we need to reformat the value array
        if (empty($data['layout'])) $data['layout'] = $this->display_layout;
        else $this->display_layout = $data['layout'];
        if ($data['layout'] == 'country') {
            $newvalue = array();
            foreach ($data['value'] as $value) {
                $newvalue[$value['id']]['value'] = $value['name'];
            }
            foreach ($data['address_components'] as $component) {
                $newvalue[$component['id']]['label'] = $component['name'];
            }
            $data['value'] = $newvalue;
            if (!empty($data['value']['country']['value']) && file_exists(sys::code() . 'properties/address/xartemplates/includes/' . $data['value']['country']['value'] . '-input.xt')) {
                $data['country_template'] = $data['value']['country']['value'] . '-input';
            } else {
                $data['country_template'] = 'default-input';
            }
        }

        if(empty($data['value']['country']['value'])) $data['value']['country']['value'] = $this->display_address_default_country;

        return DataProperty::showInput($data);
    }
    
    public function showOutput(Array $data = array())
    {
        if (empty($data['address_components'])) $data['address_components'] = $this->display_address_components;
        else $this->display_address_components = $data['address_components'];
        $data['address_components'] = $this->getAddressComponents($data['address_components']);

        if (isset($data['value'])) $this->value = $data['value'];

        // For country specific layouts we need to reformat the value array
        if (empty($data['layout'])) $data['layout'] = $this->display_layout;
        else $this->display_layout = $data['layout'];
        if ($data['layout'] == 'country') {
            $data['value'] = $this->getValueArray();
            $newvalue = array();
            foreach ($data['value'] as $value) {
                $newvalue[$value['id']]['value'] = $value['name'];
            }
            foreach ($data['address_components'] as $component) {
                $newvalue[$component['id']]['label'] = $component['name'];
            }
            $data['value'] = $newvalue;
            if (!empty($data['value']['country']['value']) && file_exists(sys::code() . 'properties/address/xartemplates/includes/' . $data['value']['country']['value'] . '-output.xt')) {
                $data['country_template'] = $data['value']['country']['value'] . '-output';
            } else {
                $data['country_template'] = 'default-output';
            }
        } else {
            $data['value'] = $this->getValue();
        }//var_dump($data['value']);
        
        return DataProperty::showOutput($data);
    }

    function getValueArray()
    {
        $value = @unserialize($this->value);
        if (!is_array($value)) $value = array();

        $components = $this->getAddressComponents($this->display_address_components);
        foreach ($components as $v) {
            $found = false;
            foreach ($value as $part) {
                if ($part['id'] == $v['id']) {
                    $valuearray[] = array('id' => $v['id'], 'name' => $part['name']);
                    $found = true;
                    break;
                }
            }
            if (!$found) $valuearray[] = array('id' => $v['id'], 'name' => '');
        }
        return $valuearray;
    }
    
    function getAddressComponents($componentstring)
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
}

?>