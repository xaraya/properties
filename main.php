<?php 
/**
 * DateTime Property
 *
 * @package properties
 * @subpackage datetime property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2010 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('modules.dynamicdata.class.properties.base');
sys::import('xaraya.structures.datetime');

class DateTimeProperty extends DataProperty
{
    public $id         = 30059;
    public $name       = 'datetime';
    public $desc       = 'DateTime';
    public $reqmodules = array();

    public $display_dateformat;
    public $initialization_start_year;
    public $initialization_end_year;

    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->template =  'datetime';
        $this->filepath   = 'auto';
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
            list($isvalid, $hours) = $this->fetchValue($name . '_hour');
            list($isvalid, $minutes) = $this->fetchValue($name . '_minute');
            list($isvalid, $seconds) = $this->fetchValue($name . '_second');
        }
        if (!isset($years) ||!isset($months) ||!isset($days) ||!isset($hours) ||!isset($minutes) ||!isset($seconds)) {
            $this->objectref->missingfields[] = $this->name;
            return null;
        }
        $value = mktime($hours,$minutes,$seconds,$months,$days,$years);
        return $this->validateValue($value);
    }

    public function showInput(Array $data = array())
    {
        $data['value'] = $this->getvaluearray($data);
        if(!isset($data['onchange'])) $data['onchange'] = null; // let tpl decide what to do
        $data['extraparams'] =!empty($extraparams) ? $extraparams : "";
        
        if($this->initialization_start_year == null)            
            $this->initialization_start_year =  min($data['value']['year'], date("Y")) - 5;
        
        if($this->initialization_end_year == null)          
            $this->initialization_end_year = max($data['value']['year'], date("Y")) + 5;
                
        $data['start_year'] = isset($data['start_year'])? $data['start_year'] : $this->initialization_start_year;
        $data['end_year'] = isset($data['end_year'])? $data['end_year'] : $this->initialization_end_year;        
        
        return DataProperty::showInput($data);
    }

    public function showOutput(Array $data = array())
    {
        if (!isset($data['value'])) {
            $value = $this->value;
        } else {
            $value = $data['value'];
        }
        if (empty($value)) $value = 0;
        if (!is_array($value)) {
            $valuearray['date'] = $this->format($value);
            $valuearray['time'] = $this->getvaluearray(array('value' => $value));
        } else {
            $valuearray['date'] = $this->format($value['timestamp']);
            $valuearray['time'] = $value;
        }
        $data['value'] = $valuearray;
        return DataProperty::showOutput($data);
    }

    function getvaluearray($data)
    {
        if (!isset($data['value'])) $value = $this->value;
        else $value = $data['value'];

        // This is already a time array
        if (is_array($value)) return $value;

// Not a good ideea to force to time()
//        $value = $value == 0 ? time() : $value;
        if (empty($value)) $value = 0;
        $date = new XarDateTime();
        $date->settimestamp($value);
        $valuearray['second'] = $date->getSecond();
        $valuearray['minute'] = $date->getMinute();
        $valuearray['hour'] = $date->getHour();
        $valuearray['day'] = $date->getDay();
        $valuearray['month'] = $date->getMonth();
        $valuearray['year'] = $date->getYear();
        $valuearray['timestamp'] = $value;

        return $valuearray;
    }

    function format($value)
    {
        if (empty($value)) return "";
        $info = xarController::$request->getInfo();
        $moduleid = xarMod::getRegID($info[0]);
        if (empty($this->dateformat) &&
            (xarModUserVars::get('math','defaultdatesettings',$moduleid) == 'locale')) {
                $settings =& xarMLSLoadLocaleData();
                $value = xarLocaleGetFormattedDate('short', $value, false);
        } else {
            $settings = $this->assembleSettings($moduleid);
            $value = xarLocaleFormatDate($settings['format'],$value,$settings['useoffset']);
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

?>