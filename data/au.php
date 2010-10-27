<?php
/**
 * @package modules
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.netspan.ch
 *
 * @subpackage math
 * @author Marc Lutolf
 */
   function getProvinces()
   {
        $options[] = array('id' =>'Australian Capital Territory', 'name' =>'Australian Capital Territory');
        $options[] = array('id' =>'New South Wales', 'name' =>'New South Wales');
        $options[] = array('id' =>'Northern Territory', 'name' =>'Northern Territory');
        $options[] = array('id' =>'Queensland', 'name' =>'Queensland');
        $options[] = array('id' =>'South Australia', 'name' =>'South Australia');
        $options[] = array('id' =>'Tasmania', 'name' =>'Tasmania');
        $options[] = array('id' =>'Victoria', 'name' =>'Victoria');
        $options[] = array('id' =>'Western Australia', 'name' =>'Western Australia');
        return $options;
    }

?>