<?php
/**
 * Counter Property
 *
 * @package properties
 * @subpackage counter property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('properties.weeklist.main');
sys::import('modules.dynamicdata.class.properties.interfaces');

class WeekListPropertyInstall extends WeekListProperty implements iDataPropertyInstall
{

    public function install(Array $data=array())
    {
        $dat_file = sys::code() . 'properties/weeklist/data/configurations-dat.xml';
        $data = array('file' => $dat_file);
        try {
        $objectid = xarMod::apiFunc('dynamicdata','util','import', $data);
        } catch (Exception $e) {
        }
        return true;
    }
    
}

?>