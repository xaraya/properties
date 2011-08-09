<?php
/**
 * CityLocation Property
 *
 * @package properties
 * @subpackage citylocation property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('modules.dynamicdata.class.properties.base');

class CityLocationProperty extends DataProperty
{
    public $id         = 30075;
    public $name       = 'citylocation';
    public $desc       = 'CityLocation';
    public $reqmodules = array();

    public $country     = "";
    public $region      = "";
    public $cityname    = "";
    public $citycode    = "";

    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->template =  'citylocation';
        $this->filepath   = 'auto';
    }

    public function checkInput($name = '', $value = null)
    {
        if (!xarVarFetch('country_' . $name, 'str', $country, '', XARVAR_DONT_REUSE)) return;
        if (!xarVarFetch('region_' . $name, 'str', $region, '', XARVAR_DONT_REUSE)) return;
        if (!xarVarFetch('city_' . $name, 'str', $city, '', XARVAR_DONT_REUSE)) return;

        $this->country = $country;
        $this->region = $region;
        $this->city = $city;
        if ($country == 'us') {
            $codefile = sys::code() . "properties/citylocation/data/usa." . strtolower($region) . ".php";
        } else {
            $codefile = sys::code() . "properties/citylocation/data/" . $country . ".php";
        }
        if (file_exists($codefile)) {
            include($codefile);
            $dropdown = DataPropertyMaster::getProperty(array('name' => 'dropdown'));
            $dropdown->options = $city_data;
            $dropdown->value = $this->city;
            $cityname = $dropdown->getOption();
        } else {
            $cityname = '';
        }
        $this->value = serialize(array('country' => $country,'region' => $region,'city' => array('name' => $cityname, 'code' => $city)));
        return true;
    }

    public function showInput(Array $data = array())
    {
        if (empty($data['name'])) $data['name'] = "dd_" . $this->id;

        if (empty($data['value'])) $data['value'] = $this->value;
        try {
            $info = unserialize($data['value']);
            $this->country = $info['country'];
            $this->region = $info['region'];
            $this->cityname = $info['city']['name'];   
            $this->citycode = $info['city']['code'];   
        } catch (Exception $e) {}

        if (!xarVarFetch('country_' . $data['name'], 'str', $data['country'], '', XARVAR_DONT_REUSE)) return;
        if (!xarVarFetch('region_' . $data['name'], 'str', $data['region'], '', XARVAR_DONT_REUSE)) return;
        if (!xarVarFetch('city_' . $data['name'], 'str', $data['city'], '', XARVAR_DONT_REUSE)) return;
        if (empty($data['country'])) $data['country'] = $this->country;
        if (empty($data['region'])) $data['region'] = $this->region;
        if (empty($data['city'])) $data['city'] = $this->citycode;

        if ($data['country'] == 'us') {
            $codefile = sys::code() . "properties/citylocation/data/usa." . strtolower($data['region']) . ".php";
        } else {
            $codefile = sys::code() . "properties/citylocation/data/" . $data['country'] . ".php";
        }
        if (file_exists($codefile)) {
            include($codefile);
            $data['cityoptions'] = $city_data;
            foreach ($data['cityoptions'] as $key => $row) {
                $id[$key]  = $row['id'];
                $name[$key] = $row['name'];
            }
            array_multisort($name, SORT_ASC, $id, SORT_ASC, $data['cityoptions']);
        } else {
            $data['cityoptions'] = array();
        }
        return parent::showInput($data);
    }

    public function showOutput(Array $data = array())
    {
        try {
            $value = unserialize($this->value);
            $this->country = $value['country'];
            $this->region = $value['region'];
            $this->cityname = $value['city']['name'];   
            $this->citycode = $value['city']['code'];   
        } catch (Exception $e) {}

        if (empty($data['country'])) $data['country'] = $this->country;
        if (empty($data['region'])) $data['region'] = $this->region;

        if (empty($data['city'])) $data['city'] = $cityname;
        return parent::showOutput($data);
    }
}

?>