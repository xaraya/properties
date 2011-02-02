<?php
/**
 * Language Property
 *
 * @package properties
 * @subpackage language property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2010 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('properties.language.main');
sys::import('modules.dynamicdata.class.properties.interfaces');
sys::import('modules.dynamicdata.class.objects.master');

class LanguagePropertyInstall extends LanguageProperty implements iDataPropertyInstall
{
    public function install(Array $data=array())
    {    
        $object = DataObjectMaster::getObject(array('name' => 'languages'));
        
        // We only load the object once
        if (empty($object)) {
            $files[] = sys::code() . 'properties/language/data/language-def.xml';
            $files[] = sys::code() . 'properties/language/data/language-dat.xml';
            foreach ($files as $file) {
                try {
                    $objectid = xarMod::apiFunc('dynamicdata','util','import', array('file' => $file));
                } catch (Exception $e) {
                    //
                }
            }
        }
        return true;
    }    
}

?>