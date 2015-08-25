<?php
/**
 * Mimic Property
 *
 * @package properties
 * @subpackage mimic property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2015 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('modules.dynamicdata.class.properties.master');

/**
 * Handle a mimic property
 *
 * @author Marc Lutolf <mfl@netspan.ch>
 */
class MimicProperty extends DataProperty
{
    public $id         = 30089;
    public $name       = 'mimic';
    public $desc       = 'Mimic';
    public $reqmodules = array();

    public $container;
    public $decorator = 'static';
    public $initialization_decorator     = 'textbox';

    public function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->filepath   = 'auto';
        $this->reload();
    }

    public function reload()
    {
        // Support both name and id
        if (is_numeric($this->decorator)) {
            //$types = DataPropertyMaster::Retrieve();
            $types = DataPropertyMaster::getPropertyTypes();
            if (isset($types[$this->decorator])) 
                $this->decorator = $types[$this->decorator]['name'];
        }
        
        try {
            $this->container = DataPropertyMaster::getProperty(array('name' => $this->decorator));
        } catch (Exception $e) {
            $this->container = DataPropertyMaster::getProperty(array('name' => 'textbox'));
        }
        if (empty($this->container)) $this->container = DataPropertyMaster::getProperty(array('name' => 'textbox'));
        $this->container->source        = $this->source;
        $this->container->status        = $this->status;
        $this->container->format        = $this->format;
        $this->container->layout        = $this->layout;
        $this->container->tplmodule     = $this->tplmodule;
        $this->container->configuration = $this->configuration;
       	$this->container->id 		    = $this->id;
        $this->container->value 	    = $this->value;
        return 1;
    }

    public function checkInput($name = '', $value = null)
    {
        $container_check = $this->container->checkInput($name, $value);
        $this->previous_value = $this->container->previous_value;
        $this->invalid = $this->container->invalid;
        $this->value = $this->container->value;
        return $container_check;
    }
    
    public function validateValue($value = null)
    {
        $container_verify = $this->container->validateValue($value);
        if ($container_verify) {
            $this->value = $this->container->value;
        } else {
            $this->value = null;
        }
        return $container_verify;
    }
    
    public function showInput(Array $data = array())
    {
        if (isset($data['decorator'])) {
            $this->setDecorator($data['decorator']);
            $this->reload();
        }
        $this->container->value = $this->value;
        return $this->container->showInput($data);
    }
    
    public function showOutput(Array $data = array())
    {
        if (isset($data['decorator'])) {
            $this->setDecorator($data['decorator']);
            $this->reload();
        }
        $this->container->value = $this->value;
        return $this->container->showOutput($data);
    }

    public function getDecorator()
    {
        $decorator = $this->decorator;
        return $decorator;
    }
    public function setDecorator($x)
    {
        $this->decorator = $x;
        return 1;
    }
}
?>
