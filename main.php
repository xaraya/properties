<?php 
/**
 * Secureemail Property
 *
 * @package properties
 * @subpackage secureemail property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('modules.roles.xarproperties.email');


class SecureEmailProperty extends EmailProperty
{
    public $id         = 30083;
    public $name       = 'secureemail';
    public $desc       = 'SecureEmail';
    public $reqmodules = array();

    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->filepath   = 'auto';
        $this->template = 'secureemail';
    }

    public function showOutput(Array $data = array())
    {
        if (!isset($data['value'])) $data['value'] = $this->value;
        $stringarray = explode('@',$data['value']);
        $value[0] = $stringarray[0];
        $value[1] = '';
        $value[2] = '';
        if (!empty($stringarray[1])) {
            $substringarray = explode('.',$stringarray[1]);            
            $value[1] = $substringarray[0];
            if (!empty($substringarray[1])) {
                $value[2] = substr($stringarray[1],strlen($substringarray[0])+1);
            }
        }
        $data['value'] = $value;
        return parent::showOutput($data);
    }
}
?>