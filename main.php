<?php 
/**
 * Date Property
 *
 * @package properties
 * @subpackage date property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('modules.dynamicdata.class.properties.base');

class DateProperty extends DataProperty
{
    public $id         = 30022;
    public $name       = 'date';
    public $desc       = 'Date';
    public $reqmodules = array();
    public $basetype   = 'date';

    public $display_date_format_type = 1;
    public $display_date_format_predef = 0;
    public $display_date_format_custom = 'c';
    public $initialization_encrypt     = false;
    public $display_start_year;
    public $display_end_year;

    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->template  = 'date';
        $this->filepath  = 'auto';

        // Import the predefined display formats here
        sys::import('properties.date.data.formats');
    }

    public function checkInput($name = '', $value = null)
    {
        $name = empty($name) ? 'dd_'.$this->id : $name;
        // store the fieldname for validations who need them (e.g. file uploads)
        $this->fieldname = $name;
        if (!isset($value)) {
            list($isvalid, $years) = $this->fetchValue($name . '_year');
            list($isvalid, $months) = $this->fetchValue($name . '_month');
            list($isvalid, $days) = $this->fetchValue($name . '_day');
        }
        if (!isset($years) ||!isset($months) ||!isset($days)) {
            $this->objectref->missingfields[] = $this->name;
            return null;
        }
        $value = mktime(0,0,0,$months,$days,$years);
        return $this->validateValue($value);
    }

    public function showInput(Array $data = array())
    {
        $data['value'] = $this->getvaluearray($data);
        if(!isset($data['onchange'])) $data['onchange'] = null; // let tpl decide what to do
        $data['extraparams'] =!empty($extraparams) ? $extraparams : "";
        
        if($this->display_start_year == null)            
            $this->display_start_year =  min($data['value']['year'], date("Y")) - 5;
        
        if($this->display_end_year == null)          
            $this->display_end_year = max($data['value']['year'], date("Y")) + 5;
            
        $data['start_year'] = isset($data['start_year'])? $data['start_year'] : $this->display_start_year;
        $data['end_year'] = isset($data['end_year'])? $data['end_year'] : $this->display_end_year;
                
        return DataProperty::showInput($data);
    }

    public function showOutput(Array $data = array())
    {
        if (isset($data['format_type']))   $this->display_date_format_type   = $data['format_type'];
        if (isset($data['format_predef'])) $this->display_date_format_predef = $data['format_predef'];
        if (isset($data['format_custom'])) $this->display_date_format_custom = $data['format_custom'];
        if (!isset($data['value'])) {
            $value = $this->value;
        } else {
            $value = $data['value'];
        }
        if (empty($value)) $value = time();
        if (is_array($value)) {
            $timestamp = mktime(0,0,0,$value['month'],$value['day'],$value['year']);
            $value['date'] = $this->format($timestamp);
            $data['value'] = $value;
        } else {
            sys::import('xaraya.structures.datetime');
            $date = new XarDateTime();
            $date->settimestamp($value);
            $valuearray['day'] = $date->getDay();
            $valuearray['month'] = $date->getMonth();
            $valuearray['year'] = $date->getYear();
            $valuearray['date'] = $this->format($value);
            $data['value'] = $valuearray;
        }

        return DataProperty::showOutput($data);
    }

    public function getValue()
    {
        return $this->format($this->value);
    }
    
    function getvaluearray($data)
    {
        if (!isset($data['value'])) $value = $this->value;
        else $value = $data['value'];
        
        // This is already a time array
        if (is_array($value)) return $value;

        if (empty($value)) $value = 0;
// Not a good ideea to force to time()
//        $value = $value == 0 ? time() : $value;
        if (empty($value)) $value = 0;
        sys::import('xaraya.structures.datetime');
        $date = new XarDateTime();
        $date->settimestamp($value);
        $valuearray['day'] = $date->getDay();
        $valuearray['month'] = $date->getMonth();
        $valuearray['year'] = $date->getYear();
        $valuearray['timestamp'] = $value;

        return $valuearray;
    }

    function format($value)
    {
        switch($this->display_date_format_type) {
            case 1:
            default:
                $value = xarLocaleGetFormattedDate('short', $value, false);
            break;
            case 2:
                // If no format chosen, just return the raw value
                if (!empty($this->display_date_format_predef)) {
                    $formats = date_formats();
                    $value = date($formats[$this->display_date_format_predef]['format'], $value);
                }
            break;
            case 3:
                $value = date($this->display_date_format_custom, $value);
            break;
        }
        return $value;
    }

    function showHidden(Array $data = array())
    {
        $data['value'] = $this->getvaluearray($data);
        return parent::showHidden($data);
    }
}

?>
