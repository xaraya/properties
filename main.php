<?php
/**
 * Flot Property
 *
 * @package properties
 * @subpackage flot property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2014 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */
sys::import('modules.dynamicdata.class.properties.base');

class FlotProperty extends DataProperty
{
    public $id         = 30132;
    public $name       = 'flot';
    public $desc       = 'Flot';
    public $reqmodules = array();

    public $initialization_include_time = 0;
    public $display_jqdatetime_format_type = 1;
    public $display_jqdatetime_format_predef = 0;
    public $display_jqdatetime_format_custom = 'c';

    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->template =  'flot';
        $this->filepath   = 'auto';
    }
    public function showOutput(Array $data = array())
    {
        if (empty($data['name'])) $data['name'] = $this->name;
        if (empty($data['value'])) $data['value'] = $this->value;

        return parent::showOutput($data);
    }
}

?>