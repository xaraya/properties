<?php 
/**
 * Listing Property
 *
 * @package properties
 * @subpackage listing property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2010 Netspan AG
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('modules.dynamicdata.class.properties.base');

class ListingProperty extends DataProperty
{
    public $id   = 30100;
    public $name = 'listing';
    public $desc = 'Listing';
    public $reqmodules = array();

    public $objectname;
    public $listing = array();

    public $module;

    public $alphabet = array(
        'A', 'B', 'C', 'D', 'E', 'F',
        'G', 'H', 'I', 'J', 'K', 'L',
        'M', 'N', 'O', 'P', 'Q', 'R',
        'S', 'T', 'U', 'V', 'W', 'X',
        'Y', 'Z'
    );

    public $display_show_primary   = false;
    public $display_show_search    = true;
    public $display_show_alphabet  = true;
    public $display_showall_tab    = true;
    public $display_showother_tab    = true;
    public $display_show_items_per_page = false;
    public $display_items_per_page = 20;

    function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);

        $this->tplmodule = 'auto';
        $this->template =  'listing';
        $this->filepath   = 'auto';
    }

    public function showInput(Array $data = array())
    {
        if (isset($data['module'])) {
            $this->module = $data['module'];
        } else {
            $info = xarController::$request->getInfo();
            $this->module = $info[0];
            $data['module'] = $this->module;
        }
        $data['items_per_page'] = xarModVars::get($this->module, 'items_per_page');

        // Take over these values because we use them below
        if (isset($data['show_primary'])) $this->display_show_primary = $data['show_primary'];
        if (isset($data['items_per_page'])) $this->display_items_per_page = $data['items_per_page'];

        $data = array_merge($data, $this->runquery($data));

        // Send any config settings not overwritten to the template
        if (!isset($data['show_primary'])) $data['show_primary'] = $this->display_show_primary;
        if (!isset($data['show_search'])) $data['show_search'] = $this->display_show_search;
        if (!isset($data['show_alphabet'])) $data['show_alphabet'] = $this->display_show_alphabet;
        if (!isset($data['showall_tab'])) $data['showall_tab'] = $this->display_showall_tab;
        if (!isset($data['showother_tab'])) $data['showother_tab'] = $this->display_showother_tab;
        if (!isset($data['show_items_per_page'])) $data['show_items_per_page'] = $this->display_show_items_per_page;
        if (!isset($data['items_per_page'])) $data['items_per_page'] = $this->display_items_per_page;
        
        // give the template the alphabet chars
        $data['alphabet'] = $this->alphabet;

        return parent::showInput($data);
    }


/*
Notes:
- We support 2 search types: by alphabet, by text search
- Text searches and alphabet searches are mutually exclusive 
- Sorting direction, ordering field is preserved across the search types
- The itemtype is fundamental in determining if we are in an existing context (and have a current query) or a new context
- As a general rule we try to pass everything that has not changed via session vars

*/
    function runquery($data)
    {

    //--- -1. Get the classes we need
        sys::import('xaraya.structures.query');
        sys::import('modules.dynamicdata.class.properties.master');

    //--- 0. Local parameters
        $tablename = 'ft';
        $baddatastores = array('_dynamic_data_','_dummy_');
        $baddatasources = array('dynamic_data','dummy','modulevars');

    //--- 1. Get the args passed to this function

        extract($data);

        // if no object name is passed, bail
        if (!isset($object) && !isset($objectname)) throw new Exception('No object passed to the listing property');

        // We accept both object names and objects, but objectname overrides
        if (isset($objectname)) {
            $object = DataObjectMaster::getObjectList(array('name' => $objectname));
        } else {
            if (!is_object($object)) throw new Exception('No object passed to the listing property');
            else {
                $objectname = $object->name;
                $data['objectname'] = $objectname;
            }
        }

        // itemtype 0 means all itemtypes
        $itemtype = isset($itemtype) ? $itemtype : 0;

        $module = isset($module) ? $module : xarModGetName();
        xarModAPILoad($module);
        $regid = xarMod::getRegID($module);

    /*    $searchandor = xarModVars::get('listing','searchandor'); //toggle for AND /OR logic in category query, default AND for categories
        if ($searchandor != 1) { //default to AND
            $searchop = 'and';
        } else {
            $searchop = 'or';
        }*/

    //--- 2. Retrieve session vars we work with

        $lastobject = xarSession::getVar('listing.lastlistingsearch');
        $lastmsg = xarSession::getVar('listing.msg') ? xarSession::getVar('listing.msg') : '';
        $sort = xarSession::getVar('listing.sort')?xarSession::getVar('listing.sort'):'DESC';
        $lastorder = xarSession::getVar('listing.lastorder') ? xarSession::getVar('listing.lastorder') : '';
        $q = xarSession::getVar('listing.currentquery');

    //--- 3. Get all the parameters we need from the form

        if(!xarVarFetch('startnum',      'int:1', $data['startnum'], 1, XARVAR_NOT_REQUIRED)) {return;}
        if(!xarVarFetch('letter',        'str:1', $letter, '', XARVAR_NOT_REQUIRED)) {return;}
        if(!xarVarFetch('search',        'str:1:100', $search, '', XARVAR_NOT_REQUIRED)) {return;}
        if(!xarVarFetch('order',         'str', $order, '', XARVAR_NOT_REQUIRED)) {return;}
        if(!xarVarFetch('submit',        'str', $submit, '', XARVAR_NOT_REQUIRED)) {return;}
        if(!xarVarFetch('op',            'str', $op, '', XARVAR_NOT_REQUIRED)) {return;}
        if(!xarVarFetch('conditions',    'isset', $conditions, NULL, XARVAR_NOT_REQUIRED)) {return;}
    
    //--- 4. Get configuration settings from modvars and tag attributes

    //--- 5. Assemble the data sources (data tables)

        $tablealiases = array();
        $i = 1;
        foreach ($object->getDataStores() as $key => $value) {
            if (in_array($key, $baddatastores)) continue;
            $tablealiases[$key] = $tablename . $i;
            $i++;
        }

        // if no datasource found we're stuck
        if (empty($tablealiases)) {
            return array();
        }

    //--- 6. Assemble the fields to be displayed

        // Someone passed a fieldlist attribute
        if (!empty($fieldlist) && !is_array($fieldlist)) $fieldlist = explode(',',$fieldlist);
        else $fieldlist = array();

        // Make sure we add the primary even if it is not in the fieldlist
        if (!empty($fieldlist) && !in_array($object->primary, $fieldlist)) $fieldlist[] = $object->primary;

        // Someone passed a keyfield attribute
        if (!empty($keyfield)) $defaultkey = $keyfield;

        // We'll put fields into the output of the query that have status active in the object
        $properties = $object->getProperties();
        $activefields = array();
        $columnfields = array();
        $sourcefields = array();
        $fieldnames = array();
        $primarysource = '';
        $primaryalias = '';
        $indices = array();
        $defaultkeyname = '';
        $tablekeyfield = '';
        $keyfieldalias = '';

        foreach ($properties as $property) {
            $source = $property->source;
            $alias = $property->source;

            foreach ($tablealiases as $key => $value) {
                $source = str_replace($key, $value, $source);
                $alias = str_replace($key . '.', $value . '_', $alias);
            }

            if ($property->name == 'itemtype') $itemtypefield = $source;

            if ($property->type == 21) {
                if ($property->name == $object->primary) {
                    $primarysource = $source;
                    $primaryalias = $alias;
                } else {
                    $indices[] = $source;
                }
                // save the field names for later use
                $fieldnames[$source] = $alias;
                $activefields[$alias] = $property->label;
                $columnfields[$alias] = $property->name;
                $sourcefields[$alias] = $source;
            }

            // Ignore fields with "bad" data sources for now
            if (in_array($property->source, $baddatasources)) continue;

            if (empty($fieldlist)) { 
                // If no field list ignore fields that don't have active display status
                if ($property->getDisplayStatus() != DataPropertyMaster::DD_DISPLAYSTATE_ACTIVE) continue;
            } elseif (!empty($fieldlist) && !in_array($property->name, $fieldlist)) {
                // if a field list was passed, make sure the property is in it
                continue;
            }

            if ($alias != $primaryalias) {
                // save the field names for later use
                $fieldnames[$source] = $alias;
                $activefields[$alias] = $property->label;
                $columnfields[$alias] = $property->name;
                $sourcefields[$alias] = $source;
            }

            // Set the keyfield for use with table selects
            // If a keyfield was passed check if this is it
            // If no key was passed take the first best
            if (empty($defaultkey) || (!empty($defaultkey) && $property->name == $defaultkey)) {
                // Only pick the primary key if it's being shown
                if ($property->type == 21 && !$this->display_show_primary) continue;
                $defaultkey = $property->name;
                $defaultkeyname = $property->label;
                $tablekeyfield = $source;
                $keyfieldalias = $alias;
            }
        }

        // Resort if we have a fieldlist
        if (!empty($fieldlist)) {
            if (!in_array($object->primary, $fieldlist)) {
                $_activefields[$primaryalias] = $activefields[$primaryalias];
                $_columnfields[$primaryalias] = $columnfields[$primaryalias];
                $_sourcefields[$primaryalias] = $sourcefields[$primaryalias];
            }
            $sortkey = array_flip($columnfields);
            foreach ($fieldlist as $field) {
                // If we have a field in the fieldlist that doesn't qualify, just ignore
                if (!isset($sortkey[$field])) continue;
                $_activefields[$sortkey[$field]] = $activefields[$sortkey[$field]];
                $_columnfields[$sortkey[$field]] = $columnfields[$sortkey[$field]];
                $_sourcefields[$sortkey[$field]] = $sourcefields[$sortkey[$field]];
            }
            $activefields = $_activefields;
            $columnfields = $_columnfields;
            $sourcefields = $_sourcefields;        
        }

        // Sanity check to make sure we got a key
        if (empty($defaultkey)) {
            throw new BadParameterException(array($module), "The listing cannot be displayed, either because no object or no select key defined");
        }

    //--- 7. Figure out the operation we are performing

        $firsttime = !isset($lastobject) || ($objectname != $lastobject);       // criterium for first time display
        if ($firsttime) $operation = 'newsearch';                               // we are displaying the page for the first time
        elseif ($op == 'column') $operation = 'columnclick';                    // a  column header was clicked
        elseif ($op == 'letter') $operation = 'lettersearch';                   // an alphabet link was clicked
        elseif ($op == 'submit') $operation = 'textsearch';                     // a string was entered into the text field
        elseif ($op == 'page') $operation = 'pagerclick';                       // the pager was clicked
        elseif (!empty($submit) && !$firsttime) $operation = 'categorysearch';  // the submit button was clicked (= any other search)
        else $operation = 'newsearch';                                          // any other operation: we fall back to new search

        $data['params'] = array();
    //    echo "<br />".$operation;//exit;

        switch ($operation) {

    //--- 8. Prepare a navigation operation (click on the pager or one of the columns)
            case "pagerclick":
            case "columnclick":

                $q = xarSession::getVar('listing.currentquery');
                if (empty($q) || !isset($q)) {
                    $q = new Query('SELECT');
                    $q->setdistinct();
                } else {
                    $q = unserialize($q);
                    $q->open();
                }
                if (!empty($conditions)) $q->addconditions($conditions);
                $data['msg'] = $lastmsg;
            break;

    //--- 9. First time visit to this page; empty the sessionvars and reset the categories
            case "newsearch":
                if (!empty($conditions)) {
                    $q = $conditions;
                } else {
                    $q = new Query('SELECT');
                }
                $q->setdistinct();
                foreach ($tablealiases as $key => $value) $q->addtable($key,$value);
                foreach ($indices as $index) $q->join($primarysource,$index);
                foreach ($fieldnames as $key => $value) $q->addfield($key . ' AS ' . $value);

                xarSession::setVar('listing.lastlistingsearch',$objectname);
                xarSession::setVar('listing.msg','');
                $order = '';
                $sort = 'ASC';
            break;

    //--- 10. Any other operation:get the query if it was passed as conditions, or create a new one
            case "lettersearch":
            case "textsearch":

                if (!empty($conditions)) {
                    $q = $conditions;
                } else {
                    $q = new Query('SELECT');
                }
                $q->setdistinct();

                foreach ($tablealiases as $key => $value) $q->addtable($key,$value);
                foreach ($indices as $index) $q->join($primarysource,$index);
                foreach ($fieldnames as $key => $value) $q->addfield($key . ' AS ' . $value);

    //--- 11. Filter on the objects and itemtypes we'll be displaying

    /*        if (isset($objectidfield) && !empty($object)) {
                $thisobject = DataObjectMaster::getObject(array('name' => $object));
                $q->eq($objectidfield, $thisobject->objectid);
            }
            if (isset($itemtypefield) && !empty($itemtype)) $q->eq($itemtypefield, $itemtype);
    */
                $data['msg'] = '';
            break;

            default:
                throw new Exception(xarML('Illegal operation: #(1)',$operation));
            break;
        }

    //--- 12. Add categories to the query if they are active


    //--- 13. Define which field will be sorted on

        // if there is no order defined use the key field
        if (empty($order))  $order = $keyfieldalias;
        if (empty($sort) && $operation != 'lettersearch')  $sort = 'ASC';
        // change  the sort direction if I clicked one of the column names
        // but only if the column name is the same so it acts like a toggle for that field
        // only change sort if column name is clicked, not a letter which will retain the current settings
        $thisstart = xarSession::getVar('listing.start') ? xarSession::getVar('listing.start') : 1;
        if ($operation == "columnclick") {
            if (isset($order) && $data['startnum']== $thisstart){
                if ($order == $lastorder) {
                    if($sort == 'ASC') $sort = 'DESC';
                       else $sort = 'ASC';
                } else {
                    $sort = 'ASC';
                }
                xarSession::setVar('listing.sort',$sort);
                xarSession::setVar('listing.lastorder',$order);
                $data['search'] = $search; //pass along search
            } elseif (empty($letter) && empty($search)) {
                //if order is not set - set it to the default key field but keep it at 'DESC'
                $order = $keyfieldalias;
                $sort = 'ASC';
                xarSession::setVar('listing.lastorder',$order);
            }
            $data['msg'] = '';
        }
        $q->setorder($order,$sort);

        switch ($operation) {
            case "lettersearch":
    //--- 16. Operation filters: we clicked one of the alphabet links

                if ($letter == 'Other') {
                    // In this case we create a bunch of SQL conditions
                    // this is better than the 1x way: no using SQL functions, and we can accomodate any type of 'alphabet'
                    //foreach ($alphabet as $let) $q->notlike('r.name', $let.'%');
                    foreach ($alphabet as $let) $q->notlike($tablekeyfield, $let.'%');
                    $data['msg'] = xarML(
                        'Listing where #(1) begins with character not listed in alphabet above (labeled as "Other")',$defaultkeyname
                    );
                } elseif ($letter == 'All') {
                    $data['msg'] = xarML("All items");
                } else {
                // TODO: handle case-sensitive databases
                    //$q->like('r.name', $letter.'%');
                    $q->like($tablekeyfield, $letter.'%');
                    $data['msg'] = xarML('Listing where #(1) begins with "#(2)"', $defaultkeyname, $letter);
                }

                //Adjust session vars and parameters
                $data['params']['letter'] = '';
                $data['startnum'] = 1;
            break;

            case "newsearch":
            case "textsearch":

    //--- 17. Operation filters: we are submitting a search text

                if (!empty($search)) {
                    $qsearch = '%'.$search.'%';
                    // Dynamically set on active fields - must have roles id - Search conditions _OR_
                    $i = 0;
                    $msg = '';
                    foreach ($sourcefields as $sourcefield=>$value) {
                        if ($i >0) {
                            $msg .= ' or';
                        }
                        $c[$i]= $q->plike($value, $qsearch);
                        $msg .= ' '.$activefields[$sourcefield].' ';
                        $i++;
                    }
                    if (!empty($msg) && $i>0) {
                        if (empty($data['msg'])) $data['msg'] = xarML('Listing where #(1) contain "#(2)"',$msg,$search);
                        else  $data['msg'] .= xarML(' and listing where #(1) contain "#(2)"',$msg,$search);
                    }
                    // take the conditions we decided on above and add them to the query as a bunch of ORs
                    $q->qor($c);
                }
                if (empty($data['msg'])) $data['msg'] = xarML('All items');

                //Adjust session vars and parameters
                $data['params']['letter'] = '';
                $data['startnum'] = 1;

            break;
            case "pagerclick":
            case "columnclick":

    //--- 18. Operation filters: likely navigation, take last msg

                $data['msg'] = $lastmsg;
            break;

            default:
                throw new Exception(xarML('Illegal operation: #(1)',$operation));
            break;
        }

    //--- 19. Set the number of rows to display and the starting point

        $q->setrowstodo($this->display_items_per_page);

        // The record to start at needs to come from the template
        $q->setstartat($data['startnum']);

        // CHECKME: do we need all 3 of these passed to the template
        $data['order'] = $order;
        $data['letter'] = $letter;
        $data['searchstring'] = $search;

        // display the query if I need to
    //    echo "<br />"; $q->qecho();
    //    exit;

        // run the query
        // it does the bindvars thing automatically away from mine eyes :)
        if (!$q->run()) return;

        // get the total number of rows irrespective of number to be displayed
        $data['total'] = $q->getrows();

        // get the records to be displayed
        $data['items'] = $q->output();

        // Add field definitions to the template variables
        $data['fields'] = $activefields;
        $data['columns'] = $columnfields;

        $data['tablekeyfield'] = $tablekeyfield;
        $data['keyfieldalias'] = $keyfieldalias;
        $data['defaultkeyname'] = $defaultkeyname;
        $data['properties'] = $properties;

        // a bunch of params the pager will want to see in its target url
        // order and sort are used by the up and down arrows
        // items_per_page is needed because we may be using dynamic items per page
        $data['params']['op'] = 'page';
        $data['params']['order'] = $order;
        $data['params']['sort'] = $sort;
    //    $data['params']['items_per_page'] = $items_per_page;
        $data['params']['startnum'] = "%%";

        // need this in case this code is turned into a dprop
        $data['regid'] = $regid;

        // we also need a reference ot the primary column for the template
        $data['primaryalias'] = $primaryalias;

        // Set the session vars to the latest state
        xarSession::setVar('listing.start',$data['startnum']);
        xarSession::setVar('listing.msg',$data['msg']);

        // Sort of ugly. How can we do better?
        unset($q->dbconn);unset($q->output);unset($q->result);
        xarSession::setVar('listing.currentquery',serialize($q));
        return $data;
    }
}

?>