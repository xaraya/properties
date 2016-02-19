<?php
/**
 * @package modules\base
 * @category Xaraya Web Applications Framework
 * @version 2.4.0
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.info
 * @link http://xaraya.info/index.php/release/68.html
 *
 * @author mikespub <mikespub@xaraya.com>
 */
/* include the base class */
sys::import('modules.base.xarproperties.checkboxlist');
/**
 * Handle check box list property
 */
class WeekListProperty extends CheckboxListProperty
{
    public $id   = 30140;
    public $name = 'weeklist';
    public $desc = 'Week Checkbox List';
    public $reqmodules = array();

    public $display_columns             = 1;
    public $initialization_monday_first = 1;

    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->filepath   = 'auto';
    }

    public function showInput(Array $data = array())
    {
        if (isset($data['monday_first'])) $this->initialization_monday_first = $data['monday_first'];
        return parent::showInput($data);
    }

    public function showOutput(Array $data = array())
    {
        if (isset($data['monday_first'])) $this->initialization_monday_first = $data['monday_first'];
        return parent::showOutput($data);
    }

    public function getOptions()
    {
        $days = array(
                xarML('Monday'),
                xarML('Tuesday'),
                xarML('Wednesday'),
                xarML('Thursday'),
                xarML('Friday'),
                xarML('Saturday'),
                xarML('Sunday'),
                );
        $days = array(
                xarML('M'),
                xarML('T'),
                xarML('W'),
                xarML('T'),
                xarML('F'),
                xarML('S'),
                xarML('S'),
                );
        if (!$this->initialization_monday_first) {
            $sunday = array_pop($days);
            array_unshift($days,$sunday);
        }

        $options = array();
        $i = 0;
        foreach ($days as $day) {
            $options[] = array('id' => $i, 'name' => $days[$i]);
            $i++;
        }
        
        $this->options = $options;
        return $options;
    }
    
}

?>
