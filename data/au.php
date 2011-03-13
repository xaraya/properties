<?php
/**
 * Province Property
 *
 * @package properties
 * @subpackage province property
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */
   function provinces_au()
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