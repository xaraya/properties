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

class ESRProperty extends TextBoxProperty
{
    public $id         = 30144;
    public $name       = 'esr';
    public $desc       = 'ESR';
    public $reqmodules = array();

    private $iban;

    public function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule =  'auto';
        $this->filepath  =  'auto';
    }

/**
 * We leave the original value untouched
 *
 */
    public function validateValue($value = null)
    {
        if (!parent::validateValue($value)) return false;
        
        // Remove the spaces
        $compressed_value = str_replace(' ', '', $value);
        // Pad to 27 digits
        $compressed_value = str_pad($compressed_value, 27 ,'0', STR_PAD_LEFT);
        
        // Remove the last (control) digit
        $stripped_value = substr($compressed_value, 0, strlen($compressed_value)-1);
        $control_digit = $this->modulo10($stripped_value);

        $reconstructed_value = $stripped_value . $control_digit;
        if ($compressed_value != $reconstructed_value) {
            
            // Reformat the compressed value. This will help show up any errors
            $error_value = strrev(chunk_split(strrev(str_pad($compressed_value, 27 ,'0', STR_PAD_LEFT)), 5, ' ')); 
            $this->invalid = xarML('The string #(1) is not a valid ESR number', $value);
            xarLog::message($this->invalid, XARLOG_LEVEL_ERROR);
            $this->value = null;
            return false;
        } else {
            $this->setValue($value);
            return true;
        }
    }

/**
 * From https://www.mf1.ch/technik-und-programmierung/353-esr-pruefziffer-mit-php-berechnen.html
 *
 */
    function modulo10($nummer)
    {
        $zahlen = array(0,9,4,6,8,2,7,1,3,5);
        $next = 0;

        for ($i = 0; $i < strlen($nummer); $i++) {
            $next = $zahlen[($next + substr($nummer, $i, 1)) % 10];
        }

        return (10 - $next) % 10;
    }
/*
        $referenznummer = 5020;

        // Ergebnis Modulo = 9
        echo modulo10($referenznummer);
        echo '<br>';

         // ESR-Referenznummer = 000000000000000000000050209
        echo str_pad($referenznummer.modulo10($referenznummer), 27 ,'0', STR_PAD_LEFT);
        echo '<br>';

        // Formatiert = 00 00000 00000 00000 00000 50209
        echo strrev(chunk_split(strrev(str_pad($referenznummer.modulo10($referenznummer), 27 ,'0', STR_PAD_LEFT)), 5, ' ')); 
*/
}

?>