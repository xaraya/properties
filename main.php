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

    public $initialization_country   = 'US';
    public $initialization_region    = 'us';
    public $initialization_language  = 'en';
    public $initialization_lat       = '38.550313';
    public $initialization_lng       = '-121.033859';
    public $initialization_zoom      = '6';

    private $connection_error;

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
        
        // Get the address parts from the template. Note Google maps now has much more infor than we are getting here
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
        $gps = $this->get_gps_coordinates($fields['address']);
        if ($gps == false) return $this->validateValue(false);
        
        $fields['lat'] = $gps[0];
        $fields['lng'] = $gps[1];

        $value = serialize($fields);
        return $this->validateValue($value);
    }

    public function validateValue($value = null)
    {
        if ($value === false) {
//            $this->invalid = xarML('Unreadable or empty address');
            $this->invalid = $this->connection_error;
            xarLog::message($this->invalid, xarLog::LEVEL_ERROR);
            $this->value = null; 
            return false;
        } else {
            $this->setValue($value);
            return true;
        }
    }

    public function showInput(Array $data = array())
    {
        if (isset($data['country'])) $this->initialization_country = $data['country'];
        if (isset($data['region'])) $this->initialization_region = $data['region'];
        if (isset($data['language'])) $this->initialization_language = $data['language'];
        if (isset($data['lat'])) $this->initialization_lat = $data['lat'];
        if (isset($data['lng'])) $this->initialization_lng = $data['lng'];
        
        $name = empty($data['name']) ? 'dd_'.$this->id : $data['name'];
        $data['value'] = $this->getValue();
        return parent::showInput($data);
    }

    public function showOutput(Array $data = array())
    {
        if (isset($data['country'])) $this->initialization_country = $data['country'];
        if (isset($data['region'])) $this->initialization_region = $data['region'];
        if (isset($data['language'])) $this->initialization_language = $data['language'];
        if (isset($data['lat'])) $this->initialization_lat = $data['lat'];
        if (isset($data['lng'])) $this->initialization_lng = $data['lng'];

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
        try {
            $ch = curl_init();            
            curl_setopt($ch, CURLOPT_URL, $url);            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);            
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);            
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);            
            $response = curl_exec($ch);            
            curl_close($ch);
        } catch (Exception $e) {
//            $response = xarML('Connection to Google maps failed');
            $this->connection_error = $e->getMessage();
            return false;
        }
        $json = json_decode($response,true);
 
        if (!isset($json['results'][0]['geometry']['location']['lat'])) return false;
        if (!isset($json['results'][0]['geometry']['location']['lng'])) return false;
        $lat = (string)$json['results'][0]['geometry']['location']['lat'];
        $lng = (string)$json['results'][0]['geometry']['location']['lng'];
 
        return array($lat, $lng);
    }
}

?>