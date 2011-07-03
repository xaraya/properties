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
    
    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->template =  'timeframe';
        $this->filepath   = 'auto';
        
        $this->value = array(time(), time(), 0);        
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
        xarVarFetch($name . "_period", 'int' ,$period,  0, XARVAR_NOT_REQUIRED);
        
        // Give the period precedence if it was chosen
        if (!empty($period)) list($startdate, $enddate) = $this->settimeperiod($period);
        
        $value = array($startdate,$enddate,$period);
        $this->value = $value;    
        return true;
    }

    public function showInput(Array $data = array())
    {
        if (!isset($data['name'])) $data['name'] = 'dd_' . $this->id;
        if (!isset($data['value'])) $data['value'] = $this->value;
        
        // The display widgets to show
        if (empty($data['show'])) $data['show'] = array('calendar');
        if (!empty($data['show']) && !is_array($data['show'])) $data['show'] = explode(',',$data['show']);
        
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
        if (!isset($data['value'])) $data['value'] = $this->value;

        return parent::showOutput($data);
    }

    private function settimeperiod($period)
    {
        $startdate = new XarDateTime();
        $startdate->setnow();
        $enddate = new XarDateTime();
        $enddate->setnow();

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
                $enddate->addDays(-1);                
            break;
            case 200:
                $startdate->addMonths(1);
                $startdate->setDay(1);
                $enddate->addMonths(3);
                $enddate->setDay(1);                
                $enddate->addDays(-1);                
            break;
            case 100:
                $startdate->addMonths(1);
                $startdate->setDay(1);
                $enddate->addMonths(2);
                $enddate->setDay(1);                
                $enddate->addDays(-1);                
            break;
            case -100: $startdate->setDay(1); break;
            case -200: $startdate->addDays(-7); break;
            case -300: $startdate->addDays(-14); break;
            case -400:
                $month = $enddate->getMonth();                
                $index = $month % 3;
                $startdate->addMonths(1 - $index);
                $startdate->setDay(1);
                $enddate->addMonths(4 - $index);
                $enddate->setDay(1);                
                $enddate->addDays(-1);                
            break;
            case -500: 
                $startdate->addMonths(-1);
                $startdate->setDay(1);
                $enddate->setDay(1);                
                $enddate->addDays(-1);
            break;
            case -600:
                $startdate->addMonths(-2);
                $startdate->setDay(1);
                $enddate->setDay(1);                
                $enddate->addDays(-1);
            break;
            case -700:
                $startdate->addMonths(-3);
                $enddate->addMonths(-3);
                $month = $enddate->getMonth();                
                $index = $month % 3;
                $startdate->addMonths(1 - $index);
                $startdate->setDay(1);
                $enddate->addMonths(4 - $index);
                $enddate->setDay(1);                
                $enddate->addDays(-1);                
            break;
            case -800:
                $startdate->setDay(1);
                $startdate->setMonth(1);
            break;
            case -900:
                $startdate->setMonth(1);
                $startdate->addYears(-1);
                $enddate->addYears(-1);
                $enddate->setMonth(12);
                $enddate->setDay(31);
            break;
        }
        $return_startdate = $startdate->getTimeStamp();
        $return_enddate = $enddate->getTimeStamp();
        return array($return_startdate,$return_enddate);
    }
}
?>