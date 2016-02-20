<?php
/**
 * JQAddressPicker Property
 *
 * @package properties
 * @subpackage jqaddresspicker property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2012 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */
sys::import('modules.dynamicdata.class.properties.base');

/**
 * Handle dynamic jqaddresspicker property
 */
class JQAddressPickerProperty extends DataProperty
{
    public $id         = 30128;
    public $name       = 'jqaddresspicker';
    public $desc       = 'JQAddressPicker';
    public $reqmodules = array();

    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->template =  'jqaddresspicker';
        $this->filepath   = 'auto';
    }

    public function checkInput($name = '', $value = null)
    {
        $id = 'dd_'.$this->id;
        $name = empty($name) ? $id : $name;

        // store the fieldname for validations who need them (e.g. file uploads)
        $this->fieldname = $name;
        if (!isset($value)) {
            list($isvalid, $fields['address']) = $this->fetchValue($name . '_address');
            list($isvalid, $fields['locality']) = $this->fetchValue($name . '_locality');
            list($isvalid, $fields['country']) = $this->fetchValue($name . '_country');
            list($isvalid, $fields['lat']) = $this->fetchValue($name . '_lat');
            list($isvalid, $fields['lng']) = $this->fetchValue($name . '_lng');
            list($isvalid, $fields['type']) = $this->fetchValue($name . '_type');
        }
        if (!isset($fields['address']) ||!isset($fields['locality']) ||!isset($fields['country']) ||!isset($fields['lat']) ||!isset($fields['lng']) ||!isset($fields['type'])) {
            $this->objectref->missingfields[] = $this->name;
            return null;
        }

        // Get the GPS coordinates
        $gps = get_gps_coordinates($fields['address']);
        $fields['lat'] = $gps[0];
        $fields['lng'] = $gps[1];

        $value = serialize($fields);
        return $this->validateValue($value);
    }

    public function showInput(Array $data = array())
    {
        $name = empty($data['name']) ? 'dd_'.$this->id : $data['name'];
        $data['value'] = $this->getValue();
        return parent::showInput($data);
    }

    public function showOutput(Array $data = array())
    {
        $data['value'] = $this->getValue();
        return parent::showOutput($data);
    }
    
    public function getValue()
    {
        try {
            $value = unserialize($this->value);
        } catch (Exception $e) {
            $value = array(
                'address' => '',
                'locality' => '',
                'country' => '',
                'lat' => '',
                'lng' => '',
                'type' => '',
            );
        }
        return $value;
    }

    public function get_gps_coordinates($address='')
    {
        $address = urlencode($address);
        $url = "http://maps.google.com/maps/api/geocode/json?sensor=false&address=" . $address;
        $response = file_get_contents($url);
        $json = json_decode($response,true);
 
        if (!isset($json['results'][0]['geometry']['location']['lat'])) return false;
        if (!isset($json['results'][0]['geometry']['location']['lng'])) return false;
        $lat = $json['results'][0]['geometry']['location']['lat'];
        $lng = $json['results'][0]['geometry']['location']['lng'];
 
        return array($lat, $lng);
    }

}

?>