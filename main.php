<?php 
/**
 * IBAN Property
 *
 * @package properties
 * @subpackage number property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('modules.base.xarproperties.textbox');

class IBANProperty extends TextBoxProperty
{
    public $id         = 30143;
    public $name       = 'iban';
    public $desc       = 'IBAN';
    public $reqmodules = array();

    private $iban;

    public function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule =  'auto';
        $this->filepath  =  'auto';

        sys::import('properties.iban.php-iban.oophp-iban');
        $this->iban = new IBAN();
    }

    public function validateValue($value = null)
    {
        if (!parent::validateValue($value)) return false;

        $iban_compressed = str_replace(' ', '', $value);
        if (!$this->iban->Verify($iban_compressed)) {
            if (!empty($this->validation_max_length_invalid)) {
                $this->invalid = xarML($this->validation_max_length_invalid);
            } else {
                $this->invalid = xarML('The string #(1) is not a valid IBAN', $value);
            }
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