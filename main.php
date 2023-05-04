<?php
/**
 * @package modules
 * @subpackage math
 */
sys::import('modules.base.xarproperties.calendar');

/**
 * Handle dynamic jscalendar property
 */
class JSCalendarDateProperty extends CalendarProperty
{
    public $id         = 30001;
    public $name       = 'jscalendardate';
    public $desc       = 'JSCalendarDate';
    public $reqmodules = array();

    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->template =  'jscalendardate';
        $this->filepath   = 'auto';
    }

    public function showInput(Array $data = array())
    {
        extract($data);

        $name = empty($name) ? 'dd_'.$this->id : $name;
        if (empty($theme)) {
            $theme = 'calendar-win2k-1';
        }
        if (empty($locale)) {
            $locale = xarMLS::localeGetInfo(xarMLS::getCurrentLocale());
        }
        if (empty($tabindex)) {
            $tabindex = '';
        }
        if (!isset($value)) {
            $value = $this->value;
        }
        // default time is unspecified
        if (empty($value)) {
            $value = -1;
        } elseif (!is_numeric($value) && is_string($value)) {
            // assume dates are stored in UTC format
            // TODO: check if we still need to add "00" for PostgreSQL timestamps or not
            if (!preg_match('/[a-zA-Z]+/',$value)) {
                $value .= ' GMT';
            }
            // this returns -1 when we have an invalid date (e.g. on purpose)
            $value = strtotime($value);
        }
        if (!isset($dateformat)) {
            $dateformat = '%Y-%m-%d %H:%M:%S';
            if ($this->configuration == 'date') {
                $dateformat = '%Y-%m-%d';
            } else {
                $dateformat = '%Y-%m-%d %H:%M:%S';
            }
        }

        // include calendar app
        sys::import('properties.jscalendardate.calendar.calendar');
        $cal = new DHTML_Calendar(sys::code() . 'properties/jscalendardate/calendar/',
                                  $locale['lang'],
                                  $theme,
                                  true);

        $cal->set_option('ifFormat',$dateformat);
        $cal->set_option('daFormat',$dateformat);
        // $timeval = xarLocale::formatDate($dateformat, $value);
        $data['baseuri']    = xarServer::getBaseURI();
        $data['dateformat'] = $dateformat;
        // $data['timeval']    = $timeval;
        $data['name']       = $name;
        $data['link']         = $cal->make_links();
        $data['value']      = $value;
        $data['tabindex']      = $tabindex;
        $data['calendartheme']      = $theme;
        $data['js_options']      = $cal->make_script($data['link']);
        $data['invalid']    = !empty($this->invalid) ? xarML('Invalid #(1)', $this->invalid) :'';

        return parent::showInput($data);
    }
}

?>
