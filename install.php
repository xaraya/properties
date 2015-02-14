<?php
/**
 * Country Property
 * 
 * @package properties
 * @subpackage country property
 * @category Third Party Xaraya Property
 * @version 1.1.0
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('properties.country.main');
sys::import('modules.dynamicdata.class.properties.interfaces');
sys::import('modules.dynamicdata.class.objects.master');

class CountryPropertyInstall extends CountryProperty implements iDataPropertyInstall
{
    public function install(Array $data=array())
    {    
        if (!DataObjectMaster::isObject(array('name' => 'countries'))) {
            $files[] = sys::code() . 'properties/countries/data/countries-def.xml';
            $files[] = sys::code() . 'properties/countries/data/countries-dat.xml';
            foreach ($files as $file) {
                try {
                    $objectid = xarMod::apiFunc('dynamicdata','util','import', array('file' => $file));
                } catch (Exception $e) {
                    // We only load the object once
                    break;
                }
            }
        }
        return true;
    }    
}

?>