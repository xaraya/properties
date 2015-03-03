<?php
/**
 * JQDateTime Property
 *
 * @package properties
 * @subpackage jqdatetime property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2012 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */
sys::import('modules.dynamicdata.class.properties.base');

/**
 * Handle dynamic jqdatetime property
 */
class JQDateTimeProperty extends DataProperty
{
    public $id         = 30127;
    public $name       = 'jqdatetime';
    public $desc       = 'JQDateTime';
    public $reqmodules = array();

    public $initialization_include_time = 0;
    public $display_jqdatetime_format_type = 1;
    public $display_jqdatetime_format_predef = 0;
    public $display_jqdatetime_format_custom = 'c';

    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->template =  'jqdatetime';
        $this->filepath   = 'auto';

        // Import the predefined display formats here
        sys::import('properties.jqdatetime.data.formats');
    }

    public function validateValue($value = null)
    {
        if(!isset($value)) $value = $this->getValue();
        else $this->setValue($value);
        if (!parent::validateValue($value)) return false;

        $this->value = strtotime($this->value); echo $this->value;exit;
        	
        if ($this->value === false) {
            $this->invalid = xarML('#(1) cannot have the value #(2)', $this->name,$value);
            xarLog::message($this->invalid, XARLOG_LEVEL_ERROR);
            $this->value = null;
            return false;
        }
        return true;
    }

    public function showInput(Array $data = array())
    {
        $name = empty($data['name']) ? 'dd_'.$this->id : $data['name'];

        if (!empty($data['include_time'])) $this->initialization_include_time = $data['include_time'];
        if (empty($data['value'])) $data['value'] = $this->value;

        if (empty($data['value'])) $data['value'] = time();
        $data['value'] = $this->format($data['value']);

        return parent::showInput($data);
    }

    public function showOutput(Array $data = array())
    {
        if (!empty($data['include_time'])) $this->initialization_include_time = $data['include_time'];
        if (empty($data['value'])) $data['value'] = $this->value;

        if (empty($data['value'])) $data['value'] = time();
        $data['value'] = $this->format($data['value']);
        return parent::showOutput($data);
    }

    function format($value)
    {
        try {
            switch($this->display_jqdatetime_format_type) {
                case 1:
                default:
                    $value = xarLocaleGetFormattedDate('short', $value, false) . " ". xarLocaleGetFormattedTime('short', $value, false);
                break;
                case 2:
                    // If no format chosen, just return the raw value
                    if (!empty($this->display_jqdatetime_format_predef)) {
                        $formats = jqdatetime_formats();
                        $value = date($formats[$this->display_date_format_predef]['format'], $value);
                    }
                break;
                case 3:
                    $value = date($this->display_jqdatetime_format_custom, $value);
                break;
            }
        } catch (Exception $e) {
            $value = 0;
        }
        return $value;
    }
    
    public function calculateTimeOffset(){
    	$siteTZ = xarConfigVars::get(null, 'Site.Core.TimeZone');

    	// Create two timezone objects, one for GMT and one for Site Time Zone
		$dateTimeZoneGMT = new DateTimeZone("GMT");
		$dateTimeZoneSiteTZ = new DateTimeZone($siteTZ);
		
		// Create two DateTime objects that will contain the same Unix timestamp, but
		// have different timezones attached to them.
		$dateTimeGMT = new DateTime($this->value, $dateTimeZoneGMT);
		$dateTime = new DateTime($this->value, $dateTimeZoneSiteTZ);
		
		// Calculate the GMT offset for the date/time contained in the $dateTimeZoneGMT
		// object, but using the timezone rules as defined for Site Time Zone
		$timeOffset = $dateTimeZoneSiteTZ->getOffset($dateTimeGMT);

		// Should calculate the timezone differnce with GMT (for dates after Sat Sep 8 01:00:00 1951 JST).
		return $timeOffset;
    }
}

?>