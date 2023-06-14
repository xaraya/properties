<?php 
/**
 * TimeFrame Property
 *
 * @package properties
 * @subpackage timeframe property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('modules.dynamicdata.class.properties.base');
sys::import('xaraya.structures.datetime');

class TimeFrameProperty extends DataProperty
{
    public $id   = 30106;
    public $name = 'timeframe';
    public $desc = 'Time Frame';
    public $reqmodules = array();

    private $default;
    
    public $display_timeframe_displays = array("calendar");
    
    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->template =  'timeframe';
        $this->filepath   = 'auto';
        
        // We cannot use $this->defaultvalue because that needs to be a string
        $this->default = array(time(), time(), 0);
    }

    public function checkInput($name = '', $value = null)
    {
        $name = empty($name) ? 'dd_'.$this->id : $name;

        $jscalendardate = DataPropertyMaster::getProperty(array('name' => 'jscalendardate'));
        $dropdown = DataPropertyMaster::getProperty(array('name' => 'dropdown'));

        $jscalendardate->checkInput($name . "_start_date"); 
        $startdate = !empty($jscalendardate->value) ? $jscalendardate->value : time();
        $jscalendardate->checkInput($name . "_end_date"); 
        $enddate = !empty($jscalendardate->value) ? $jscalendardate->value : time();
        $dropdown->checkInput($name . "_period"); 
        xarVar::fetch($name . "_period", 'int' ,$period,  0, xarVar::NOT_REQUIRED);
        
        // Give the period precedence if it was chosen
        if (!empty($period)) {
            list($startdate, $enddate) = $this->getTimeperiod($period);
        } else {
            $date = new XarDateTime();
            $date->setTimeStamp($enddate);
            $date->setHour(23);
            $date->setMinute(59);
            $date->setSecond(59);
            $enddate = $date->getTimeStamp();
        }
        
        $value = array($startdate,$enddate,$period);
        $this->setValue($value);    
        return true;
    }

    public function getValue()
    {
        try {
            $value = unserialize($this->value);
            if(!is_array($value)) $value = $this->default;
        } catch (Exception $e) {
            $value = $this->default;
        }
        if (!empty($value[2])) list($value[0],$value[1]) = $this->getTimeperiod($value[2]);
        return $value;
    }

    public function setValue($value=null)
    {
        $this->value = serialize($value);
    }

    public function showInput(Array $data = array())
    {
        if (!isset($data['name'])) $data['name'] = 'dd_' . $this->id;
        if (!isset($data['value'])) $data['value'] = $this->getValue();
        
        // The display widgets to show
        if (empty($data['show'])) $data['show'] = $this->display_timeframe_displays;
        if (!empty($data['show']) && !is_array($data['show'])) $data['show'] = explode(',',$data['show']);

        // Remove any stray blanks, etc.
        foreach ($data['show'] as $k => $v) $data['show'][$k] = trim($v);
        
        // If not displaying the dropdown, set some default values
        if (!in_array('dropdown',$data['show'])) $data['value'][2] = 0;

        // If displaying the dropdown, give it precedence unless it is "no period"
        if (in_array('dropdown',$data['show']) && !empty($data['value'][2])) 
            list($data['value'][0],$data['value'][1]) = $this->getTimeperiod($data['value'][2]);

        // The timeframes to show in the dropdown
        if (empty($data['frames'])) $data['frames'] = array();
        if (!empty($data['frames']) && !is_array($data['frames'])) $data['frames'] = explode(',',$data['frames']);
        $temp = array();
        foreach ($data['frames'] as $frame) {
            $frame = trim($frame);
            $temp[] = $data['name'] . '_frames_' . $frame;
        }
        $data['frames'] = $temp;
        
        return parent::showInput($data);
    }

    public function showOutput(Array $data = array())
    {
        if (!isset($data['name'])) $data['name'] = 'dd_' . $this->id;
        
        if (isset($data['value'])) {
            // Support for an array
            if (is_array($data['value'])) {
                $this->setValue($data['value']);
            } else {
            // Support for a serialized array
                $this->value = $data['value'];
            }
        }
        $data['value'] = $this->getValue();
        return parent::showOutput($data);
    }

    private function getTimeperiod($period)
    {
        $startdate = new XarDateTime();
        $startdate->setnow();
        $startdate->setHour(0);
        $startdate->setMinute(0);
        $startdate->setSecond(0);
        $enddate = clone $startdate;

        switch ($period) {
            case 500:
                $startdate->addYears(1);
                $startdate->setMonth(1);
                $startdate->setDay(1);
                $enddate->addYears(1);
                $enddate->setMonth(12);
                $enddate->setDay(31);
            break;
            case 400:
                $enddate->setMonth(12);
                $enddate->setDay(31);
            break;
            case 300:
                $startdate->addMonths(3);
                $enddate->addMonths(3);
                $month = $enddate->getMonth();                
                $index = $month % 3;
                $startdate->addMonths(1 - $index);
                $startdate->setDay(1);
                $enddate->addMonths(4 - $index);
                $enddate->setDay(1);                
                $enddate->addSeconds(-1);                
            break;
            case 200:
                $startdate->addMonths(1);
                $startdate->setDay(1);
                $enddate->addMonths(3);
                $enddate->setDay(1);                
                $enddate->addSeconds(-1);                
            break;
            case 100:
                $startdate->addMonths(1);
                $startdate->setDay(1);
                $enddate->addMonths(2);
                $enddate->setDay(1);                
                $enddate->addSeconds(-1);                
            break;
            case -100: 
                $startdate->setDay(1); 
                $enddate->addMonths(1);
                $enddate->setDay(1);
                $enddate->addSeconds(-1);                
            break;
            case -200: $startdate->addDays(-7); break;
            case -300: $startdate->addDays(-14); break;
            case -310: $startdate->addMonths(-1); break;
            case -320: $startdate->addMonths(-2); break;
            case -330: $startdate->addMonths(-3); break;
            case -340: $startdate->addMonths(-4); break;
            case -350: $startdate->addMonths(-6); break;
            case -360: $startdate->addMonths(-12); break;
            case -400:
                $month = $enddate->getMonth();                
                $monthoffset = ($month-1) % 3;
                $startdate->setDay(1);                
                $startdate->addMonths(-$monthoffset);
                $enddate->setTimestamp($startdate->getTimestamp());
                $enddate->addMonths(3);
                $enddate->addSeconds(-1);                
            break;
            case -450:
                $month = $enddate->getMonth();                
                $monthoffset = ($month-1) % 3;
                $startdate->setDay(1);                
                $startdate->addMonths(-$monthoffset-3);
                $enddate->setTimestamp($startdate->getTimestamp());
                $enddate->addMonths(3);
                $enddate->addSeconds(-1);                
            break;
            case -500: 
                $startdate->addMonths(-1);
                $startdate->setDay(1);
                $enddate->setDay(1);                
                $enddate->addSeconds(-1);
            break;
            case -600:
                $startdate->addMonths(-2);
                $startdate->setDay(1);
                $enddate->setDay(1);                
                $enddate->addSeconds(-1);
            break;
            case -800:
                $month = $enddate->getMonth();                
                $monthoffset = ($month-1) % 6;
                $startdate->setDay(1);                
                $startdate->addMonths(-$monthoffset);
                $enddate->setTimestamp($startdate->getTimestamp());
                $enddate->addMonths(6);
                $enddate->addSeconds(-1);                
            break;
            case -850:
                $month = $enddate->getMonth();                
                $monthoffset = ($month-1) % 6;
                $startdate->setDay(1);                
                $startdate->addMonths(-$monthoffset-6);
                $enddate->setTimestamp($startdate->getTimestamp());
                $enddate->addMonths(6);
                $enddate->addSeconds(-1);                
            break;
            case -900:
                $startdate->setDay(1);
                $startdate->setMonth(1);
            break;
            case -950:
                $startdate->setDay(1);
                $startdate->setMonth(1);
                $startdate->addYears(-1);
                $enddate = clone $startdate;
                $enddate->addYears(+1);
                $enddate->addSeconds(-1);
            break;
        }
        $return_startdate = $startdate->getTimeStamp();
        
        // The end date needs to go up to the last second 
        $enddate->setHour(23);
        $enddate->setMinute(59);
        $enddate->setSecond(59);
        $return_enddate = $enddate->getTimeStamp();
        
        return array($return_startdate,$return_enddate);
    }
}
?>