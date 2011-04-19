<?php
/**
 * Editor GUI property
 *
 * @package properties
 * @subpackage celkoposition property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

/**
 * Handle the editor property
 * Utilizes JavaScript based WYSIWYG Editor, CKEditor
 */
sys::import('modules.base.xarproperties.textarea');

class EditorProperty extends TextAreaProperty
{
    public $id         = 30091;
    public $name       = 'ckeditor';
    public $desc       = 'Editor';
    public $reqmodules = array();
    
    public $editor     = null;
    public $height     ='512';
    public $width      ='100%';
    
    public $display_editor_flavor;
    
    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->template  = 'ckeditor';
        $this->filepath  = 'auto';        
    }

    public function showInput(Array $data = array())
    {
        if (!empty($data['flavor'])) $this->display_editor_flavor = $data['flavor'];
        if ($this->display_editor_flavor == 'fckeditor') {
            if (empty($data['height'])) $data['height'] = $this->height;
            if (empty($data['width'])) $data['width'] = $this->width;
            sys::import('properties.ckeditor.templates.includes.fckeditor.fckeditor');
            $editorpath = sys::code() . 'properties/ckeditor/templates/includes/fckeditor/';
            $name = $this->getCanonicalName($data);
            $this->editor = new FCKeditor($name) ;
            $this->editor->BasePath = $editorpath;
            $this->editor->Value = $this->value;
            $this->editor->Width = $data['width'];
            $this->editor->Height = $data['height'];
            $data['editor'] = $this->editor;
        }
        $data['flavor'] = $this->display_editor_flavor;
        return parent::showInput($data);
    }
}

?>