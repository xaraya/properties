<?php
/**
 * ObjectItem Property
 *
 * @package properties
 * @subpackage objectitem property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('modules.dynamicdata.class.properties.base');

class ObjectItemProperty extends DataProperty
{
    public $id         = 30115;
    public $name       = 'objectitem';
    public $desc       = 'ObjectItem';
    public $reqmodules = array();

    public $object     = "";
    public $item       = 0;
    public $cityname    = "";
    public $citycode    = "";

    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->template =  'objectitem';
        $this->filepath   = 'auto';
    }

    public function checkInput($name = '', $value = null)
    {
        if (!xarVarFetch('object_' . $name, 'str', $object, '', XARVAR_DONT_REUSE)) return;
        if (!xarVarFetch('item_' . $name,   'str', $item, '', XARVAR_DONT_REUSE)) return;

        $this->object = $object;
        $this->item = $item;

        $this->value = $this->object . ":" . $this->item;
        return true;
    }

    public function showInput(Array $data = array())
    {
        if (empty($data['name'])) $data['name'] = "dd_" . $this->id;
        if (!isset($data['value'])) $data['value'] = $this->value;

        try {
            $value = explode(':',$data['value']);
            $object = $value[0];
            if (!isset($value[1])) $value[1] = 0;
            $item = $value[1];
        } catch (Exception $e) {}

        if (!xarVarFetch('object_' . $data['name'], 'str', $data['object'], $object, XARVAR_DONT_REUSE)) return;
        if (!xarVarFetch('item_' . $data['name'],   'str', $data['item'], $item, XARVAR_DONT_REUSE)) return;
        if (empty($data['object'])) $data['object'] = $this->object;
        if (empty($data['item'])) $data['item'] = $this->item;
        
        try {
            $object = DataObjectMaster::getObject(array('name' => $data['object']));
        } catch (Exception $e) {
            $data['object'] = '';
        }
        if (!empty($data['object'])) {
            // Special case if the object is Dynamic Objects
            if (!isset($object->properties['id'])) {
                $data['item_store_prop'] = "objectid";
            } else {
                $data['item_store_prop'] = "id";
            }
            // Special case if there the object has no name property
            if (isset($object->properties['name'])) {
                $data['item_display_prop'] = "name";
            } else {
                $data['item_display_prop'] = "id";
            }
        }

        return parent::showInput($data);
    }

    public function showOutput(Array $data = array())
    {
        if (!isset($data['value'])) $data['value'] = $this->value;
        try {
            $value = explode(':',$data['value']);
            $this->object = $value[0];
            if (!isset($value[1])) $value[1] = 0;
            $this->item = $value[1];
        } catch (Exception $e) {}

        if (empty($data['object'])) $data['object'] = $this->object;
        if (empty($data['item'])) $data['item'] = $this->item;

        try {
            $object = DataObjectMaster::getObject(array('name' => $data['object']));
        } catch (Exception $e) {
            $data['object'] = '';
        }
        if (!empty($data['object'])) {
            // Special case if the object is Dynamic Objects
            if (!isset($object->properties['id'])) {
                $data['item_store_prop'] = "objectid";
            } else {
                $data['item_store_prop'] = "id";
            }
            // Special case if there the object has no name property
            if (isset($object->properties['name'])) {
                $data['item_display_prop'] = "name";
            } else {
                $data['item_display_prop'] = "id";
            }
        }

        return parent::showOutput($data);
    }
}

?>