<?php 
/**
 * Time Property
 *
 * @package properties
 * @subpackage time property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2010 Netspan AG
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

    public $dateformat      = null;

    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->template =  'time';
        $this->filepath   = 'auto';
    }

    public function checkInput($name = '', $value = null)
    {
        $name = empty($name) ? 'dd_'.$this->id : $name;
        // store the fieldname for validations who need them (e.g. file uploads)
        $this->fieldname = $name;
        if (!isset($value)) {
            list($isvalid, $ampm) = $this->fetchValue($name . '_ampm');
            list($isvalid, $hours) = $this->fetchValue($name . '_hour');
            list($isvalid, $minutes) = $this->fetchValue($name . '_minute');
            list($isvalid, $seconds) = $this->fetchValue($name . '_second');
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

        if(!isset($data['onchange'])) $data['onchange'] = null; // let tpl decide what to do
        $data['extraparams'] =!empty($extraparams) ? $extraparams : "";
        return DataProperty::showInput($data);
    }

    public function showOutput(Array $data = array())
    {
        $time = $this->getvaluearray($data);
        $data['value'] = array();
        $data['value']['time'] = $time;
        return DataProperty::showOutput($data);
    }

    function getvaluearray($data)
    {
        if (!isset($data['value'])) $value = $this->value;
        else $value = $data['value'];

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
        $info = xarController::$request->getInfo();
        $moduleid = xarMod::getRegID($info[0]);
        if (empty($this->dateformat) &&
            (xarModUserVars::get('math','defaultdatesettings',$moduleid) == 'locale')) {
                $settings =& xarMLSLoadLocaleData();
                $value = xarLocaleGetFormattedTime('short', $value, false);
        } else {
            $settings = $this->assembleSettings($moduleid);
            $value = xarLocaleFormatTime($settings['format'],$value,$settings['useoffset']);
        }
        return xarVarPrepHTMLDisplay($value);
    }

    function assembleSettings($moduleid=0)
    {
        if (empty($this->dateformat)) {
            $info = xarMod::apiFunc('math','user','getcurrentdatesetting',array('moduleid' => $moduleid));
        } else {
            $info = xarMod::apiFunc('math','user','getdatesetting',array('moduleid' => $moduleid, 'name' => $this->dateformat));
        }
        return $info;
    }

    function showHidden(Array $data = array())
    {
        $data['name']     = !empty($data['name']) ? $data['name'] : 'dd_'.$this->id;
        $data['id']       = !empty($data['id'])   ? $data['id']   : 'dd_'.$this->id;

        // Add the object's field prefix if there is one
        $prefix = '';
        // Allow 0 as a fieldprefix
        if(!empty($this->_fieldprefix) || $this->_fieldprefix === 0)  $prefix = $this->_fieldprefix . '_';
        // A field prefix added here can override the previous one
        if(isset($data['fieldprefix']))  $prefix = $data['fieldprefix'] . '_';
        if(!empty($prefix)) $data['name'] = $prefix . $data['name'];
        if(!empty($prefix)) $data['id'] = $prefix . $data['id'];

        $data['invalid']  = !empty($this->invalid) ? xarML('Invalid #(1)', $this->invalid) :'';
        $data['value'] = $this->getvaluearray($data);
        if(!isset($data['module']))   $data['module']   = $this->tplmodule;
        if(!isset($data['template'])) $data['template'] = $this->template;
        if(!isset($data['layout']))   $data['layout']   = $this->layout;

        // We cannot call the parent method becaust the value we are passing is an array, not a string
        // TODO: find a way out of this quandary?
        return xarTplProperty($data['module'], $data['template'], 'showhidden', $data);
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