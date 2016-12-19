<?php 
/**
 * BIC Property
 *
 * @package properties
 * @subpackage textbox property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2016 Luetolf-Carroll GmbH
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <marc@luetolf-carroll.com>
 */

/**
 * Uses the code at http://stackoverflow.com/questions/15920008/regex-for-bic-check
 */
 
sys::import('modules.base.xarproperties.textbox');

class BICProperty extends TextBoxProperty
{
    public $id         = 30145;
    public $name       = 'bic';
    public $desc       = 'BIC';
    public $reqmodules = array();

    public $validation_min_length           = 8;
    public $validation_max_length           = 11;

    public function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule =  'auto';
        $this->filepath  =  'auto';
        
        $this->validation_min_length_invalid = xarML('A BIC code must have at least 8 characters.');
        $this->validation_max_length_invalid = xarML('A BIC code cannot have more than 11 characters.');
    }

    public function validateValue($value = null)
    {
        if (!parent::validateValue($value)) return false;

        $check = (bool) ( preg_match('/^[a-z]{6}[0-9a-z]{2}([0-9a-z]{3})?\z/i', $value) == 1 );
        if (!$check) {
            $this->invalid = xarML('The string #(1) is not a valid BIC code', $value);
            xarLog::message($this->invalid, XARLOG_LEVEL_ERROR);
            $this->value = null;
            return false;
        } else {
            $this->setValue($value);
            return true;
        }
    }
}

?>