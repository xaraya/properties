<?php
/**
 * MySQL Config property
 *
 * @package properties
 * @subpackage mysql property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('modules.dynamicdata.class.properties.base');

class MysqlProperty extends DataProperty
{
    public $id         = 30112;
    public $name       = 'mysql';
    public $desc       = 'MySQL Config';
    public $reqmodules = array();
    
    public $properties = array();
    
    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->template  = 'mysql';
        $this->filepath  = 'auto';
        
        $this->setup();
    }

    public function setup(Array $data = array())
    {
        $this->properties['cache'] = DataPropertyMaster::getProperty(array('type' => 'radio', 'name' => 'cache'));
        $options = array(
                    array('id' => 'OFF', 'name' => xarML('Off')),
                    array('id' => 'ON', 'name' => xarML('On')),
                );
        $this->properties['cache']->options = $options;
        $this->properties['size'] = DataPropertyMaster::getProperty(array('type' => 'integerbox', 'name' => 'size'));
        $this->properties['limit'] = DataPropertyMaster::getProperty(array('type' => 'integerbox', 'name' => 'limit'));
    }
    
    public function showInput(Array $data = array())
    {
        if (empty($data['name'])) $data['name'] = 'dd_' . $this->id;
        
        if (!xarVarFetch($data['name'] . '_confirm', 'int',   $confirm, 0, XARVAR_NOT_REQUIRED)) return;
        $dbconn = xarDB::getConn();
        
        if ($confirm) {
            $isvalid = true;
            foreach ($this->properties as $key => $property) {
                $valid = $this->properties[$key]->checkInput($key);
                $isvalid = $isvalid && $valid;
            }
            if ($isvalid) {
                $query = "SET GLOBAL query_cache_type = " . $this->properties['cache']->value;
                echo $this->properties['cache']->value;
                $stmt = $dbconn->prepareStatement($query);
                $result = $stmt->executeQuery();
                $query = "SET GLOBAL query_cache_size = " . $this->properties['size']->value;
                $stmt = $dbconn->prepareStatement($query);
                $result = $stmt->executeQuery();
                $query = "SET GLOBAL query_cache_limit = " . $this->properties['limit']->value;
                $stmt = $dbconn->prepareStatement($query);
                $result = $stmt->executeQuery();
                xarController::redirect(xarServer::getCurrentURL());
            }
            
        }
        
        $query = "show variables like 'query%'";
        $stmt = $dbconn->prepareStatement($query);
        $result = $stmt->executeQuery();
        $data['settings'] = array();
        if ($result) {
            while ($result->next()) {
                list ($name, $value) = $result->getRow();
                $data['settings'][$name] = $value;
            }
            $result->close();
        }

        $data['properties'] = $this->properties;
        return parent::showInput($data);
    }
}

?>