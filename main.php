<?php
/**
 * SubscribeForm Property
 *
 * @package properties
 * @subpackage subscribeform property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2015 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('modules.dynamicdata.class.properties.base');

class SubscribeFormProperty extends DataProperty
{
    public $id   = 30138;
    public $name = 'subscribeform';
    public $desc = 'subscribeform';
    public $reqmodules = array();

    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);

        $this->tplmodule = 'auto';
        $this->template  = 'subscribeform';
        $this->filepath  = 'auto';
    }
}

?>
