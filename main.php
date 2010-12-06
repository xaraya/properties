<?php
/**
 * CodeMirror Editor property
 *
 * @package properties
 * @subpackage codemirror property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2010 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author M. Lutolf (mfl@netspan.ch)
 */

/**
 * Handle the codemirror property
 * Utilizes JavaScript based  Editor: CodeMirror
 *
 */
sys::import('modules.base.xarproperties.textarea');

class CodeMirrorProperty extends TextAreaProperty
{
    public $id         = 30101;
    public $name       = 'codemirror';
    public $desc       = 'CodeMirror';
    public $reqmodules = array();
    
    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->template =  'codemirror';
        $this->filepath   = 'auto';
    }

    public function showInput(Array $data = array())
    {
        $data['basepath'] = sys::code() . 'properties/codemirror/templates/includes/codemirror/';
        return parent::showInput($data);
    }
}

?>