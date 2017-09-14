<?php
/**
 * JQColorPicker Property
 *
 * @package properties
 * @subpackage jqcolorpicker property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2017 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

/**
 * Autoload function for this property
 */
function featherlight_property_autoload($class)
{
    $class = strtolower($class);

    $class_array = array(
        'featherlightproperty'            => 'properties.featherlight.main',
    );
    
    if (isset($class_array[$class])) {
        sys::import($class_array[$class]);
        return true;
    }
    
    return false;
}

/**
 * Register this function for autoload on import
 */
if (class_exists('xarAutoload')) {
    xarAutoload::registerFunction('featherlight_property_autoload');
} else {
    // guess you'll have to register it yourself :-)
}
?>