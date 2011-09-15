<?php
/**
 * Language Property
 *
 * @package properties
 * @subpackage language property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('properties.languages.main');
sys::import('modules.dynamicdata.class.properties.interfaces');
sys::import('modules.dynamicdata.class.objects.master');

class LanguagesPropertyInstall extends LanguagesProperty implements iDataPropertyInstall
{
    public function install(Array $data=array())
    {    
        if (!DataObjectMaster::isObject(array('name' => 'languages'))) {
            $files[] = sys::code() . 'properties/languages/data/language-def.xml';
            $files[] = sys::code() . 'properties/languages/data/language-dat.xml';
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