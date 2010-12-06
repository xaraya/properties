<?php 
/**
 * TimeFrame Property
 *
 * @package properties
 * @subpackage timeframe property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2010 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('modules.base.xarproperties.dropdown');

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
    }

    public function showInput(Array $data = array())
    {
        if (!isset($data['name'])) $data['name'] = 'dd_' . $this->id;
        if (empty($data['frames'])) $data['frames'] = array();
        if (isset($data['frames']) && !is_array($data['frames'])) $data['frames'] = explode(',',$data['frames']);
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
        if (empty($data['frames'])) $data['frames'] = array();
        if (isset($data['frames']) && !is_array($data['frames'])) $data['frames'] = explode(',',$data['frames']);
        $temp = array();
        foreach ($data['frames'] as $frame) {
            $frame = trim($frame);
            $temp[] = $data['name'] . '_frames_' . $frame;
        }
        $data['frames'] = $temp;
        return parent::showOutput($data);
    }
}
?>