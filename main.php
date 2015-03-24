<?php 
/**
 * Timeperiod Property
 *
 * @package properties
 * @subpackage timeperiod property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('modules.dynamicdata.class.properties.base');

class TimePeriodProperty extends DataProperty
{
    public $id   = 30036;
    public $name = 'timeperiod';
    public $desc = 'Time Period';
    public $reqmodules = array();

    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->template =  'timeperiod';
        $this->filepath   = 'auto';
    }

    public function checkInput($name = '', $value = null)
    {
        $name = empty($name) ? 'dd_'.$this->id : $name;
        // store the fieldname for validations who need them (e.g. file uploads)
        $this->fieldname = $name;
        if (!isset($value)) {
            if (!xarVarFetch($name . '_time', 'isset', $time,  NULL, XARVAR_DONT_SET)) {return;}
            if (!xarVarFetch($name . '_unit', 'isset', $unit,  NULL, XARVAR_DONT_SET)) {return;}
        }
        if (!isset($time)) return;
        $seconds = 1.0;
        switch($unit)
        {
            case "y":
                $seconds *= 52;
            case "w":
                $seconds *= 7;
            case "d":
                $seconds *= 24;
            case "h":
                $seconds *= 60;
            case "periods":
            case "m":
                $seconds *= 60;
            case "s":
            default:
                break;
        }
        return $this->validateValue($time * $seconds);
    }

    public function showInput(Array $data = array())
    {
        if (!isset($data['value'])) $data['value'] = $this->value;
        $data['value'] = $this->getconvertedvalue($data);
        return DataProperty::showInput($data);
    }

    public function showOutput(Array $data = array())
    {
        if (!isset($data['value'])) $data['value'] = $this->value;
        $data['value'] = $this->getconvertedvalue($data);
        return DataProperty::showOutput($data);
    }

    private function getconvertedvalue(Array $data=array())
    {
        if (isset($data['inputunit'])) {
            if ($data['inputunit'] == 'ms') $data['value'] /= 1000;
            elseif ($data['inputunit'] == 'm') $data['value'] *= 60;
            elseif ($data['inputunit'] == 'h') $data['value'] *= 3600;
            elseif ($data['inputunit'] == 'd') $data['value'] *= 86400;
            elseif ($data['inputunit'] == 'w') $data['value'] *= 604800;
            elseif ($data['inputunit'] == 'y') $data['value'] *= 220752000;
        }
        $this->convert($data['value']);
        $valuearray['duration'] = (int)$this->duration;
        $valuearray['unit'] = $this->unit;
        return $valuearray;
    }
    
    private function convert($dur=0)
    {
        if ($dur < 0) {
            $sign = -1;
            $dur = abs($dur);
        } else {
            $sign = 1;
        }
        if($dur >= 60)
        {
            $dur /= 60;
            if($dur >= 60)
            {
                $dur /= 60;
                if(($dur >= 24) && ($dur % 24 == 0))
                {
                    $dur /= 24;
                    if(($dur >= 7) && ($dur % 7 == 0))
                    {
                        $dur /= 7;
                        if(($dur >= 52) && ($dur % 52 == 0))
                        {
                            $dur  /= 52;
                            $units = 'y';
                        } else $units = 'w';
                    } else $units = 'd';
                } else $units = 'h';
            } else $units = 'm';
        } else $units = 's';
        $this->unit = $units;
        $this->duration = $dur * $sign;
    }
}
?>