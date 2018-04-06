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
class AutocompleteProperty extends SelectProperty
{
    public $id         = 30086;
    public $name       = 'autocomplete';
    public $desc       = 'Autocomplete';
    public $reqmodules = array();

    public $initialization_urlmod;                    // Name of the module the dropdown function is in
    public $initialization_urlfunc;                   // Name of the dropdown function (note: type is always 'native'
    public $initialization_store_field   = 'id';      // Name of the field we want to use for storage
    public $initialization_display_field = 'name';    // Name of the field we want to use for displaying.

    // Disable dropdown validations
    public $validation_override          = true;

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
        if (!isset($data['urlmod']))      $data['urlmod'] = $this->initialization_urlmod;
        if (!isset($data['urltype']))     $data['urltype'] = 'ws';
        if (!isset($data['urlfunc']))     $data['urlfunc'] = $this->initialization_urlfunc;
        if (!isset($data['urlargs']))     $data['urlargs'] = '';

        // Assemble the target URL that holds the values we are looking through
        // By convention this is usually a get or getall function in the xarwsapi folder
        if (empty($data['urlmod']) && empty($data['urlmod'])) {
            $data['target_url'] = '';
        } else {
            $args = array(
                'store_field'   => $this->initialization_store_field,
                'display_field' => $this->initialization_display_field,
            );
            $data['target_url'] = xarController::URL($data['urlmod'], 'native', $data['urlfunc'],
                                                            $args,
                                                            null, null,
                                                            'ws.php');
        }

        // Check if the file for this URL exists
        $file = sys::code() . 'modules/' . $data['urlmod'] . '/xarwsapi/' . $data['urlfunc'] . '.php';
        if (!file_exists($file)) {
            // No sense continuing if the file doesn't exist
            $data['target_url'] = '';
        } else if (!empty($this->value)) {
            // We are modifying an existing value. Get its information from the target URL
            $items = xarMod::apiFunc($data['urlmod'], 'ws', $data['urlfunc'], array($this->initialization_store_field => (int)$this->value));
            if (!is_array($items)) {
                $items = json_decode($items, true);
                $items = $items['suggestions'];
                foreach ($items as $k => $v) {
                    $data['name_value'] = $items[$k]['value'];
                }
            }
        }
   
        return parent::showInput($data);
    }

    public function showOutput(Array $data = array())
    {
        // Assemble the function call we use to get the data
        if (!isset($data['urlmod']))      $data['urlmod'] = $this->initialization_urlmod;
        if (!isset($data['urltype']))     $data['urltype'] = 'ws';
        if (!isset($data['urlfunc']))     $data['urlfunc'] = $this->initialization_urlfunc;
        if (!isset($data['urlargs']))     $data['urlargs'] = '';

        
        // Check the data passed
        $url_ok = true;
        if (empty($data['urlmod']) && empty($data['urlmod'])) {
            $url_ok = false;
        } else {
            // Check if the file for this URL exists
            $file = sys::code() . 'modules/' . $data['urlmod'] . '/xarwsapi/' . $data['urlfunc'] . '.php';
            if (!file_exists($file)) $url_ok = false;
        }
        if (!$url_ok) die(xarML("Bad function for autocomplete property: #(1)", $file));

        // URL is OK: get the item(s)
        $items = xarMod::apiFunc($data['urlmod'], 'ws', $data['urlfunc'], array($this->initialization_store_field => (int)$this->value));

        // Unpack the data
        if (!is_array($items)) {
            $items = json_decode($items, true);
            $items = $items['suggestions'];
            foreach ($items as $k => $v) {
                $items[$k]['id'] = (int)$items[$k]['data'];
                unset($items[$k]['data']);
                $items[$k]['name'] = $items[$k]['value'];
                unset($items[$k]['value']);
            }
            $data['options'] = $items;
        }
        
        // Display the result with the drodown template
        $data['template'] = 'dropdown';
        return parent::showOutput($data);
    }
}
?>