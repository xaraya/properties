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

sys::import('properties.number.main');
sys::import('modules.dynamicdata.class.properties.interfaces');

class NumberPropertyInstall extends NumberProperty implements iDataPropertyInstall
{
    public function install(Array $data=array())
    {
        if (!DataObjectMaster::isObject(array('name' => 'numbers'))) {
            $files[] = sys::code() . 'properties/number/data/number-def.xml';
            $files[] = sys::code() . 'properties/number/data/number-dat.xml';
            foreach ($files as $file) {
                try {
                    $objectid = xarMod::apiFunc('dynamicdata','util','import', array('file' => $file));
                } catch (Exception $e) {
                    // We only load the object once
                    break;
                }
            }
        }
        $dat_file = sys::code() . 'properties/number/data/configurations-dat.xml';
        $data = array('file' => $dat_file);
        try {
            $objectid = xarMod::apiFunc('dynamicdata','util','import', $data);
        } catch (Exception $e) {
            //
        }
        return true;
    } 
}

?>