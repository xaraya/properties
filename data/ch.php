<?php
/**
 * @package modules
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.netspan.ch
 *
 * @subpackage math
 * @author Marc Lutolf
 */

    function getProvinces()
   {
        $options[] = array('id' =>'AG', 'name' =>'Aargau');
        $options[] = array('id' =>'AI', 'name' =>'Appenzell Inner Rhoden');
        $options[] = array('id' =>'AR', 'name' =>'Appenzell Ausser Rhoden');
        $options[] = array('id' =>'BE', 'name' =>'Bern');
        $options[] = array('id' =>'BL', 'name' =>'Baselland');
        $options[] = array('id' =>'BS', 'name' =>'Basel Stadt');
        $options[] = array('id' =>'FR', 'name' =>'Fribourg');
        $options[] = array('id' =>'GE', 'name' =>'Genf');
        $options[] = array('id' =>'GL', 'name' =>'Glarus');
        $options[] = array('id' =>'GR', 'name' =>'Graubünden');
        $options[] = array('id' =>'JU', 'name' =>'Jura');
        $options[] = array('id' =>'LU', 'name' =>'Luzern');
        $options[] = array('id' =>'NE', 'name' =>'Neuchatel');
        $options[] = array('id' =>'NW', 'name' =>'Nidwalden');
        $options[] = array('id' =>'OW', 'name' =>'Obwalden');
        $options[] = array('id' =>'SG', 'name' =>'St. Gallen');
        $options[] = array('id' =>'SH', 'name' =>'Schaffhausen');
        $options[] = array('id' =>'SO', 'name' =>'Solothurn');
        $options[] = array('id' =>'SZ', 'name' =>'Schwyz');
        $options[] = array('id' =>'TG', 'name' =>'Thurgau');
        $options[] = array('id' =>'TI', 'name' =>'Ticino');
        $options[] = array('id' =>'UR', 'name' =>'Uri');
        $options[] = array('id' =>'VD', 'name' =>'Vaud');
        $options[] = array('id' =>'VS', 'name' =>'Wallis');
        $options[] = array('id' =>'ZG', 'name' =>'Zug');
        $options[] = array('id' =>'ZH', 'name' =>'Zürich');

        return $options;
    }

?>