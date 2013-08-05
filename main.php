<?php 
/**
 * CurrentTime Property
 * 
 * @package properties
 * @subpackage currenttime property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('modules.base.xarproperties.timezone');

class CurrentTimeProperty extends TimeZoneProperty
{
    public $id         = 30116;
    public $name       = 'currenttime';
    public $desc       = 'CurrentTime';
    public $reqmodules = array();
    
    public $initialization_currenttime_format = 'Y-m-d H:i:s';

    public function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->filepath   = 'auto';
        $this->template   = 'currenttime';
    }
        
    public function showOutput(Array $data = array())
    {
        if (empty($data['format'])) $data['format'] = $this->initialization_currenttime_format;
        if (empty($data['value'])) $data['value'] = $this->value;
        if (empty($data['value'])) $data['value'] = 'UTC';
        $time = new DateTime();
        $tzobject = new DateTimeZone($data['value']);
        $time->setTimezone($tzobject);
        $data['value'] = $time->format($data['format']);
        return DataProperty::showOutput($data);
    }
}

?>