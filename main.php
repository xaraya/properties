<?php 
/**
 * Number Property
 *
 * @package properties
 * @subpackage number property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('modules.base.xarproperties.floatbox');

class NumberProperty extends FloatBoxProperty
{
    // Store number objects
    private $numberobject;
    private $numberobjectlist;

    public $id         = 30019;
    public $name       = 'number';
    public $desc       = 'Number';
    public $reqmodules = array();

    protected $isOO        = false;
    protected $formatter   = false;
    protected $numbertype  = 'numeric';
    public $currencyformat = null;

    public $display_show_zeros = 1;
    public $display_numberpattern = '#,##0.00';

    public function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule =  'auto';
        $this->template  =  'number';
        $this->filepath  =  'auto';
        
        $this->isOO   = extension_loaded('intl');
        if ($this->isOO) {
            // Create the formatter object using the current locale
            $localeinfo = xarMLS::localeGetInfo(xarMLS::getCurrentLocale());
            $locale = $localeinfo['lang'] . "_" . $localeinfo['country'];
            $this->formatter = new NumberFormatter($locale, NumberFormatter::DEFAULT_STYLE);
            // Force the display to 2 decimals for now
            $this->formatter->setPattern($this->display_numberpattern);
            
            // Fall back if the constructor failed
            if (!$this->formatter) $this->isOO = false;
        }
    }

    public function validateValue($value = null)
    {
        xarLog::message("DataProperty::validateValue: Validating property " . $this->name, xarLog::LEVEL_DEBUG);

        $value = $this->formatter->parse($value);
        return parent::validateValue($value);
    }

    public function showInput(Array $data = array())
    {
        extract($data);

        if ($this->isOO) {
            // Override the locale's display pattern if a pattern was passed
            if (isset($data['pattern'])) $this->display_numberpattern = $data['pattern'];

            if (isset($data['value'])) $this->setValue($data['value']);
            $data['rawvalue'] = $this->value;
            $data['value'] = $this->getValue();
        } else {
            if (isset($format)) $this->format = $format;
            if (isset($value)) $this->setValue($value);
            $data['rawvalue'] = $this->value;
            $data['value'] = $this->getValue();
        }

        $data['style'] = isset($style) ? $style : "";
        return parent::showInput($data);
    }

    public function showOutput(Array $data = array())
    {
        extract($data);
        if (isset($data['show_zeros'])) $this->display_show_zeros = $data['show_zeros'];

        if ($this->isOO) {
            // Override the locale's display pattern if a pattern was passed
            if (isset($data['pattern'])) $this->formatter->setPattern($data['pattern']);

            if (isset($data['value'])) $this->setValue($data['value']);
            $data['rawvalue'] = $this->value;
            $data['value'] = $this->getValue();
        } else {
            if (isset($format)) $this->format = $format;
            if (isset($value)) $this->setValue($value);
            $data['rawvalue'] = $this->value;
            $data['value'] = $this->getValue();
        }
        $data['style'] = isset($style) ? $style : "";
        return parent::showOutput($data);
    }

    public function getValue()
    {
        if ($this->isOO) {
            try {
                $display_string = $this->formatter->format($this->value);
            } catch (Exception $e) {
                throw new Exception(xarML('Incorrect value for getValue method of number property #(1)', $this->name));
            }
            if (xarModVars::get('dynamicdata', 'debugmode') &&
                in_array(xarUser::getVar('id'), xarConfigVars::get(null, 'Site.User.DebugAdmins'))) {
                $error_code = $this->formatter->getErrorCode();
                if (!empty($error_code)) echo $this->formatter->getErrorMessage();
            }
            return $display_string;
        } else {
            if (empty($this->display_numberformat)) {
                $settings =& xarMLSLoadLocaleData();
            } else {
                $settings = $this->assembleSettings();
            }
            $display_string = xarLocaleFormatNumber(trim($this->value),$settings);
    
            return xarVarPrepHTMLDisplay($display_string);
        }
    }

    public function setValue($value=null)
    {
        if ($this->isOO) {
            try {
                $this->value = $value;
            } catch (Exception $e) {
                throw new Exception(xarML('Incorrect value for setValue method of number property #(1)', $this->name));
            }
            if (xarModVars::get('dynamicdata', 'debugmode') &&
                in_array(xarUser::getVar('id'), xarConfigVars::get(null, 'Site.User.DebugAdmins'))) {
                $error_code = $this->formatter->getErrorCode();
                if (!empty($error_code)) echo $this->formatter->getErrorMessage();
            }
        } else {
            if (empty($value)) {
                $this->value = 0;
            } else {
                if (empty($this->display_numberformat)) {
                    $settings =& xarMLSLoadLocaleData();
                } else {
                    $settings = $this->assembleSettings();
                }
                $this->value = $value;
                if (isset($settings["/$this->numbertype/decimalSeparator"]))
                    $this->value = str_replace($settings["/$this->numbertype/decimalSeparator"],'.',$this->value);
                if (isset($settings["/$this->numbertype/groupingSeparator"]))
                    $this->value = str_replace($settings["/$this->numbertype/groupingSeparator"],'',$this->value);
            }
        }
    }

    protected function assembleSettings()
    {
        // We allow giving an ID or a name for the format to be applied
        // Names are to be preferred in the future
        if (is_numeric($this->display_numberformat)) {
            $info = $this->getnumbersetting(array('id' => $this->display_numberformat));
        } else {
            $info = $this->getnumbersetting(array('name' => $this->display_numberformat));
        }

        try {
            $settings["/$this->numbertype/groupingSize"] = $info['groupingsize'];
            $settings["/$this->numbertype/groupingSeparator"] = $info['groupingseparator'];
            $settings["/$this->numbertype/decimalSeparator"] = $info['decimalseparator'];
            $settings["/$this->numbertype/isDecimalSeparatorAlwaysShown"] = $info['dsalwaysshown'];
            $settings["/$this->numbertype/fractionDigits/minimum"] = $info['minfractiondigits'];
            $settings["/$this->numbertype/fractionDigits/maximum"] = $info['maxfractiondigits'];
            $settings['/decimalSymbols/zeroDigit'] = $info['zerodigitsign'];
            $settings['/decimalSymbols/minusSign'] = $info['minussign'];
        } catch (Exception $e) {
            $settings = array();
        }
        return $settings;
    }

    protected function getnumbersetting($args)
    {
        extract($args);
        if (!isset($name) && !isset($id))
            throw new Exception(xarML('Need either an id or name of a number format'));
        
        sys::import('modules.dynamicdata.class.objects.master');
        if (isset($id)) {
            if (empty($this->numberobject[$id])) {
                $object = DataObjectMaster::getObject(array('name'  => 'number'));
                $this->numberobject[$id] = $object;
            } else {
                $object =& $this->numberobject[$id];
            }
            $itemid = $object->getItem(array('itemid' => $id));
            return $object->getFieldValues();
        } else {
            if (empty($this->numberobjectlist[$name])) {
                $object = DataObjectMaster::getObjectList(array('name'  => 'number'));
                $this->numberobjectlist[$name] = $object;
            } else {
                $object =& $this->numberobjectlist[$name];
            }
            $name = "'" . $name . "'"; //argh
            $result = $object->getItems(array('where' => "name eq " . $name));
            return array_pop($result);
        }
    }
    
    function showHidden(Array $data = array())
    {
        // The hidden value is the raw value
        //$data['value'] = $this->getValue();
        return parent::showHidden($data);
    }    
}

?>