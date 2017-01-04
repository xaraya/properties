<?php
/**
 * Autocomplete Property
 *
 * @package properties
 * @subpackage autocomplete property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2012 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('modules.base.xarproperties.textbox');

/**
 * Display data via AJAX and autocomplete on user input
 * This property is based on the code at https://github.com/Pixabay/jQuery-autoComplete
 * 
 * The data are retrieved using the ws.php entry point
 * The URL is forced to type = 'native'
 */
class AutocompleteProperty extends TextboxProperty
{
    public $id         = 30086;
    public $name       = 'autocomplete';
    public $desc       = 'Autocomplete';
    public $reqmodules = array();

    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->filepath   = 'auto';
        $this->template   = 'autocomplete';
        $this->tplmodule  = 'auto';
    }

    public function showInput(Array $data = array())
    {
        // Assemble the function call we use to get the data
        if (!isset($data['urlmod']))      $data['urlmod'] = '';
        if (!isset($data['urltype']))     $data['urltype'] = 'ws';
        if (!isset($data['urlfunc']))     $data['urlfunc'] = '';
        if (!isset($data['urlargs']))     $data['urlargs'] = '';

        
        if (empty($data['urlmod']) && empty($data['urlmod'])) {
            $data['target_url'] = '';
        } else {
            xarController::$entryPoint = 'ws.php';
            $data['target_url'] = xarController::URL($data['urlmod'], 'native', $data['urlfunc']);
        }

        // Check if the file for this URL exists
        $file = sys::code() . 'modules/' . $data['urlmod'] . '/xarwsapi/' . $data['urlfunc'] . '.php';
        if (!file_exists($file)) $data['target_url'] = '';

        return parent::showInput($data);
    }

}
?>