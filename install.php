<?php
/**
 * Dynamic Property
 *
 * @package properties
 * @subpackage dynamic property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2015 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('properties.mimic.main');
sys::import('modules.dynamicdata.class.properties.interfaces');

class MimicPropertyInstall extends MimicProperty implements iDataPropertyInstall
{

    public function install(Array $data=array())
    {
        $dat_file = sys::code() . 'properties/mimic/data/configurations-dat.xml';
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