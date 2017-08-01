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
            $localeinfo = xarLocaleGetInfo(xarMLSGetCurrentLocale());
            $locale = $localeinfo['lang'] . "_" . $localeinfo['country'];
            $this->formatter = new NumberFormatter($locale, NumberFormatter::PATTERN_DECIMAL);
            
            // Fall back if the constructor failed
            if (!$this->formatter) $this->isOO = false;
        }
    }

    public function validateValue($value = null)
    {
        $this->setValue(trim($value));
        return parent::validateValue($this->value);
    }

    public function showInput(Array $data = array())
    {
        extract($data);

        if ($this->isOO) {
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
        return parent::showOutput($data);
    }

    public function getValue()
    {
        if ($this->isOO) {
            $this->formatter->setPattern($this->display_numberpattern);
            try {
                $value = $this->formatter->format($this->value);
            } catch (Exception $e) {
                throw new Exception(xarML('Incorrect value for getValue method of number property #(1)', $this->name));
            }
            if (xarModVars::get('dynamicdata', 'debugmode') &&
                in_array(xarUserGetVar('id'), xarConfigVars::get(null, 'Site.User.DebugAdmins'))) {
                if (!empty($this->formatter->getErrorCode())) echo $this->formatter->getErrorMessage();
            }
            return $value;
        } else {
            if (empty($this->display_numberformat)) {
                $settings =& xarMLSLoadLocaleData();
            } else {
                $settings = $this->assembleSettings();
            }
            $value = xarLocaleFormatNumber(trim($this->value),$settings);
    
            return xarVarPrepHTMLDisplay($value);
        }
    }

    public function setValue($value=null)
    {
        if ($this->isOO) {
            $this->formatter->setPattern($this->display_numberpattern);
            try {
                $this->value = $this->formatter->parse($value);
            } catch (Exception $e) {
                throw new Exception(xarML('Incorrect value for setValue method of number property #(1)', $this->name));
            }
            if (xarModVars::get('dynamicdata', 'debugmode') &&
                in_array(xarUserGetVar('id'), xarConfigVars::get(null, 'Site.User.DebugAdmins'))) {
                if (!empty($this->formatter->getErrorCode())) echo $this->formatter->getErrorMessage();
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
        $data['value'] = $this->getValue();
        return parent::showHidden($data);
    }    

    function setPattern($pattern="")
    {
        if ($pattern == '') return true;
        $this->display_numberpattern = $pattern;
    }    
}

?>