<?php
/**
 * Captcha Property
 *
 * @package properties
 * @subpackage captcha property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

    include_once('class/securimage/securimage.php');
    $img = new securimage();
    $img->show(); // alternate use:  $img->show('/path/to/background.jpg');
    exit();
?>