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

    public $initialization_include_time      = 0;       // Include time sliders in the UI
    public $display_jqdatetime_format_type   = 1;       // Whether to use format from site locale, predefined or custom format
    public $display_jqdatetime_format_predef = 0;       // The predefined format chosen
    public $display_jqdatetime_format_custom = 'c';     // The type of custom format chosen
    public $initialization_add_offset        = false;   // Whether to honor the timezone offset

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

        $this->value = strtotime($this->value);
        	
        if ($this->value === false) {
            $this->invalid = xarML('#(1) date could not be resolved', $this->name);
            xarLog::message($this->invalid, XARLOG_LEVEL_ERROR);
            $this->value = null;
            return false;
        }
        return true;
    }

    public function showInput(Array $data = array())
    {
        $name = empty($data['name']) ? 'dd_'.$this->id : $data['name'];

        if (!empty($data['add_offset']))   $this->initialization_add_offset = $data['add_offset'];
        if (!empty($data['include_time'])) $this->initialization_include_time = $data['include_time'];
        if (empty($data['value'])) $data['value'] = $this->value;

        if (empty($data['value'])) $data['value'] = time();
        $data['value'] = $this->format($data['value']);

        return parent::showInput($data);
    }

    public function showOutput(Array $data = array())
    {
        if (!empty($data['add_offset']))   $this->initialization_add_offset = $data['add_offset'];
        if (!empty($data['include_time'])) $this->initialization_include_time = $data['include_time'];
        if (!empty($data['value']))  $this->value = $data['value'];

        if (empty($data['value'])) $data['value'] = time();
        $data['value'] = $this->format($this->value);
        return parent::showOutput($data);
    }

    function format($value)
    {   
        // Add TZ offset here
        if ($this->initialization_add_offset) {
            $value += xarConfigVars::get(null, 'Site.MLS.DefaultTimeOffset');
        }
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
                        $value = date($formats[$this->display_jqdatetime_format_predef]['format'], $value);
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
}

?>