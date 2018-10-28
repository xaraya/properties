<?php 
/**
 * Time Property
 *
 * @package properties
 * @subpackage time property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('modules.dynamicdata.class.properties.base');
sys::import('xaraya.structures.datetime');

class TimeProperty extends DataProperty
{
    public $id         = 30034;
    public $name       = 'time';
    public $desc       = 'Time';
    public $reqmodules = array();
    public $basetype   = 'time';

    public $display_time_format_type = 1;
    public $display_time_format_predef = 0;
    public $display_time_format_custom = 'c';
    public $display_dropdown = 0;
    public $display_hours = 1;
    public $display_minutes = 1;
    public $display_seconds = 1;

    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->template =  'time';
        $this->filepath   = 'auto';

        // Import the predefined display formats here
        sys::import('properties.time.data.formats');
    }

    public function checkInput($name = '', $value = null)
    {
        $name = empty($name) ? 'dd_'.$this->id : $name;
        // store the fieldname for validations who need them (e.g. file uploads)
        $this->fieldname = $name;
        if (!isset($value)) {
            list($isvalid, $ampm) = $this->fetchValue($name . '["ampm"]');
            list($isvalid, $hours) = $this->fetchValue($name . '["hour"]');
            list($isvalid, $minutes) = $this->fetchValue($name . '["minute"]');
            list($isvalid, $seconds) = $this->fetchValue($name . '["second"]');
        }
        if (!isset($ampm) && !isset($hours) && !isset($minutes) && !isset($seconds)) {
            $this->objectref->missingfields[] = $this->name;
            return null;
        }
        if ($ampm == 'pm') $hours += 12;
        $value = $hours*3600 + $minutes*60 + $seconds;
        return $this->validateValue($value);
    }

    public function showInput(Array $data = array())
    {
        $data['value'] = $this->getvaluearray($data);

        if(isset($data['dropdown'])) $this->display_dropdown = $data['dropdown'];
        if(isset($data['hours'])) $this->display_hours = $data['hours'];
        if(isset($data['minutes'])) $this->display_minutes = $data['minutes'];
        if(isset($data['seconds'])) $this->display_seconds = $data['seconds'];

        if(!isset($data['onchange'])) $data['onchange'] = null; // let tpl decide what to do
        $data['extraparams'] =!empty($extraparams) ? $extraparams : "";
        return DataProperty::showInput($data);
    }

    public function showOutput(Array $data = array())
    {
        if (!isset($data['value'])) {
            $value = $this->value;
        } else {
            $value = $data['value'];
        }

        if (empty($value)) {
            $data['value']['date'] = "";
        } else {
            $date = new XarDateTime();
            if (is_array($value)) {
                $timestamp = mktime($value['hour'],$value['minute'],$value['second'],0,0,0);
                $data['value']['time'] = $this->format($timestamp);
            } else {
                $time = $this->format($value);
                $data['value'] = array();
                $data['value']['time'] = $time;
            }
        }

        if(isset($data['dropdown'])) $this->display_dropdown = $data['dropdown'];
        if(isset($data['hours'])) $this->display_hours = $data['hours'];
        if(isset($data['minutes'])) $this->display_minutes = $data['minutes'];
        if(isset($data['seconds'])) $this->display_seconds = $data['seconds'];

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

        if (is_array($value)) return $value;

        $value = $value == 0 ? time() : $value;
        $date = new xarDateTime();
        $date->settimestamp($value);
        $valuearray['hour'] = $date->getHour();
        $valuearray['minute'] = $date->getMinute();
        $valuearray['second'] = $date->getSecond();

        if (isset($data['format']) && ($data['format'] == 'ampm'))
        {
          if ($valuearray['hour'] > 11)
          {
            $valuearray['hour'] -= 12;
            $valuearray['ampm'] = 'pm';
          } else {
            $valuearray['ampm'] = 'am';
          }
        }
        return $valuearray;
    }

    function format($value)
    {
        switch($this->display_time_format_type) {
            case 1:
            default:
                $value = xarLocaleGetFormattedTime('short', $value, false);
            break;
            case 2:
                // If no format chosen, just return the raw value
                if (!empty($this->display_time_format_predef)) {
                    $formats = time_formats();
                    $value = date($formats[$this->display_time_format_predef]['format'], $value);
                }
            break;
            case 3:
                $value = date($this->display_time_format_custom, $value);
            break;
        }
        return $value;
    }

    function showHidden(Array $data = array())
    {
        $data['name']     = !empty($data['name']) ? $data['name'] : 'dd_'.$this->id;
        $data['id']       = !empty($data['id'])   ? $data['id']   : 'dd_'.$this->id;

        $data['invalid']  = !empty($this->invalid) ? xarML('Invalid #(1)', $this->invalid) :'';
        $data['value'] = $this->getvaluearray($data);
        if(!isset($data['module']))   $data['module']   = $this->tplmodule;
        if(!isset($data['template'])) $data['template'] = $this->template;
        if(!isset($data['layout']))   $data['layout']   = $this->layout;

        return parent::showHidden($data);
    }
}

    function options_hours()
    {
        $options = array();
        for($i=0;$i<24;$i++) {
            $hour1 = "00".$i;
            $hour1 = substr($hour1,strlen($hour1)-2);
            $hour2 = "00".($i+1);
            $hour2 = substr($hour2,strlen($hour2)-2);
            $options[] = array('id' => $i*60, 'name' => $hour1 . ':00 - ' . $hour2 . ':00');
        }
        return $options;
    }

    function options_halfhours()
    {
        $options = array();
        for($i=0;$i<=24;$i++) {
            $hour1 = "00".$i;
            $hour1 = substr($hour1,strlen($hour1)-2);
            $options[] = array('id' => 2*$i*30, 'name' => $hour1 . ':00');
            $options[] = array('id' => (2*$i+1)*30, 'name' => $hour1 . ':30');
            /*
            $hour1 = "00".$i;
            $hour1 = substr($hour1,strlen($hour1)-2);
            $hour2 = "00".($i+1);
            $hour2 = substr($hour2,strlen($hour2)-2);
            $options[] = array('id' => $i*30, 'name' => $hour1 . ':00 - ' . $hour1 . ':30');
            $options[] = array('id' => ($i+1)*30, 'name' => $hour1 . ':30 - ' . $hour2 . ':00');
            */
        }
        return $options;
    }

?>
