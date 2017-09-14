<?php
/**
 * JSUpload Property
 *
 * @package properties
 * @subpackage jsupload property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2012 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

 sys::import('modules.dynamicdata.class.properties.base');

/**
 * Featherlight property using jQuery
 */

/**
 * This code:
 */
/**
 * Notes:
 */

class FeatherlightProperty extends DataProperty
{
    public $debug   = false;
    
    public $id         = 30148;
    public $name       = 'featherlight';
    public $desc       = 'Featherlight Picker';
    public $reqmodules = array();


    public function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->template  = 'featherlight';
        $this->filepath  = 'auto';
    }
}

?>