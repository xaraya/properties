<?php
/**
 * Province Property
 *
 * @package properties
 * @subpackage province property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */
sys::import('modules.dynamicdata.class.properties.base');

/**
 * Handle the Province property
 *
 * Show a dropdown of provinces for a given country
 */
class ProvinceProperty extends DataProperty
{
    public $id         = 30105;
    public $name       = 'province';
    public $desc       = 'Province';
    public $reqmodules = array();

    public $initialization_province_country = 'us';
    
    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);

        $this->tplmodule = 'auto';
        $this->template =  'province';
        $this->filepath   = 'auto';
    }

    public function showInput(Array $data = array())
    {
        if (empty($data['country'])) $data['country'] = $this->initialization_province_country;
        $countries = explode(',',$data['country']);
        $data['options'] = array();
        foreach ($countries as $country) {
            try {
                sys::import('properties.province.data.' . $country);
                $func = 'provinces_'.$country;
                if (function_exists($func))
                    $data['options'] = array_merge($data['options'], $func());
            } catch (Exception $e) {}
        }
        return parent::showInput($data);
    }
 }

?>