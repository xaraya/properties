<?php 
/**
 * Link Trail Property
 *
 * @package properties
 * @subpackage linktrail property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2011 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('modules.dynamicdata.class.properties.base');

class LinkTrailProperty extends DataProperty
{
    public $id         = 30110;
    public $name       = 'linktrail';
    public $desc       = 'Link Trail';
    public $reqmodules = array();

    public $links = array();
    public $links_to_show = 5;
    public $links_to_save = 5;

    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->tplmodule = 'auto';
        $this->template =  'linktrail';
        $this->filepath   = 'auto';
        $links = xarSession::getVar('link_trail');
        if (empty($links)) $links = array();
        
        // Get the title of the current page
        $title = xarTplGetPageTitle();
        // Kludge: need to remove unwanted stuff
        $separator  = xarModVars::get('themes', 'SiteTitleSeparator');
        $titlearray = explode($separator,$title);
        $currenttitle = array_pop($titlearray);
        if (!empty($currenttitle)) {            
            $currenturl = xarServer::getCurrentURL(); //xarServer::getVar('HTTP_REFERER');
            foreach ($links as $k => $v) {
                if ($v == $currenturl) unset($links[$k]);
            }
            // Set the array of previous links for display
            $this->links = $links;
            
            // Add the current page to the array to be shown on the next page
            $links[$currenttitle] = $currenturl;
            if (count($links) > $this->links_to_save) array_shift($links);
            xarSession::setVar('link_trail',$links);
        }
    }

    public function showInput(Array $data = array())
    {
        if(!isset($data['links_to_show'])) $data['links_to_show'] = $this->links_to_show; 
        $links = array();
        $links_to_remove = $this->links_to_save - $this->links_to_show;
        for ($i=0;$i<$links_to_remove;$i++) array_shift($this->links);
        $data['links'] = $this->links;
        return parent::showInput($data);
    }

    public function showOutput(Array $data = array())
    {
        return $this->showInput($data);
    }
}
?>