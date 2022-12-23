<?php 
/**
 * QR Code Property
 *
 * @package properties
 * @subpackage qrcode property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2022 Luetolf-Carroll GmbH
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <marc@luetolf-carroll.com> 
 */

sys::import('modules.base.xarproperties.textbox');
sys::import('properties.qrcode.QR_BarCode');

class QRCodeProperty extends TextBoxProperty
{
    public $id         = 30151;
    public $name       = 'qrcode';
    public $desc       = 'QR Code';
    public $reqmodules = array();

    public $initialization_image_size  = 150;
    public $display_url;

//    public $name;
    public $email;
    public $subject;
    public $message;
    public $phone;
    public $msg;
    public $address;
    public $type;
    public $size;
    public $content;
    public $filename;

    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->template =  'qrcode';
        $this->filepath   = 'auto';

		$this->qr = new QR_BarCode($this->initialization_image_size);
    }

    public function showInput(Array $data = array())
    {
    }

    public function showOutput(Array $data = array())
    {
		if (empty($url)) $url = $this->display_url;
        $this->qr->url($url);
        $data['qrimage'] = base64_encode($this->qr->qrCode());
        
		return parent::showOutput($data);
    }
}
?>
