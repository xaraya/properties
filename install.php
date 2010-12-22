<?php
/**
 * Time Property
 *
 * @package properties
 * @subpackage time property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2010 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('properties.date.main');
sys::import('modules.dynamicdata.class.properties.interfaces');

class TimePropertyInstall extends TimeProperty implements iDataPropertyInstall
{

    public function install(Array $data=array())
    {
        $dat_file = sys::code() . 'properties/time/data/configurations-dat.xml';
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