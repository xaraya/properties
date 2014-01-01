<?php
/**
 * Listing Property
 *
 * @package properties
 * @subpackage listing property
 * @category Third Party Xaraya Property
 * @version 1.0.0
 * @copyright (C) 2011 Netspan AG
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

        $ipp = xarModVars::get($this->module, 'items_per_page');
        if (!empty($ipp)) $this->display_items_per_page = $ipp;

        // Send any config settings not overwritten to the template
        if (isset($data['show_items_per_page'])) $this->display_show_items_per_page = $data['show_items_per_page'];
        if (isset($data['items_per_page'])) $this->display_items_per_page = $data['items_per_page'];
        if (isset($data['show_primary'])) $this->display_show_primary = $data['show_primary'];
        if (isset($data['show_search'])) $this->display_show_search = $data['show_search'];
        if (isset($data['show_alphabet'])) $this->display_show_alphabet = $data['show_alphabet'];
        if (isset($data['showall_tab'])) $this->display_showall_tab = $data['showall_tab'];
        if (isset($data['showother_tab'])) $this->display_showother_tab = $data['showother_tab'];
        
        // give the template the alphabet chars
        $data['alphabet'] = $this->alphabet;

        $data = array_merge($data, $this->runquery($data));
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
        $baddatasources = array('dynamic_data','dummy','modulevars','');

    //--- 1. Get the args passed to this function

        extract($data);

        // if no object name is passed, bail
        if (!isset($object) && !isset($objectname)) throw new Exception('No object passed to the listing property');

        // We accept both object names and objects, but objectname overrides
        if (isset($objectname)) {
            sys::import('modules.dynamicdata.class.objects.master');
            $object = DataObjectMaster::getObjectList(array('name' => $objectname));
        } elseif (isset($object)) {
            if (!is_object($object)) throw new Exception('No object passed to the listing property');
            else {
                $objectname = $object->name;
                $data['objectname'] = $objectname;
                if (!method_exists($object,'getItems')) {
                    sys::import('modules.dynamicdata.class.objects.master');
                    $object = DataObjectMaster::getObjectList(array('name' => $objectname));
                }
            }
        } else {
            throw new Exception('No object passed to the listing property');
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

        $lastobject = xarSession::getVar('listing.' . $objectname . '.lastlistingsearch');
        $lastmsg = xarSession::getVar('listing.' . $objectname . '.msg') ? xarSession::getVar('listing.' . $objectname . '.msg') : '';
        $sort = xarSession::getVar('listing.' . $objectname . '.sort')?xarSession::getVar('listing.' . $objectname . '.sort'):'DESC';
        $lastorder = xarSession::getVar('listing.' . $objectname . '.lastorder') ? xarSession::getVar('listing.' . $objectname . '.lastorder') : '';
        $q = xarSession::getVar('listing.' . $objectname . '.currentquery');

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

    //--- 6. Assemble the fields to be displayed

        // Check if someone passed a fieldlist attribute or take the one defined in the object
        $nofieldlist = false;
        if (empty($fieldlist)) {
            $fieldlist = $object->getFieldList();
            $nofieldlist = true;
        } elseif (!is_array($fieldlist)) {
        // If a string was passed rather than an array, turn it into an array
            $fieldlist = explode(',',$fieldlist);
        }

        // Someone passed a keyfield attribute
        if (!empty($keyfield)) $defaultkey = $keyfield;

        // Check if the object has a primary key
        if (empty($object->primary)) {
            throw new Exception(xarML("The listing cannot be displayed, because this object has no primary key"));
        }

        // We'll put fields into the output of the query that have status active or list
        $object->properties[$object->primary]->setDisplayStatus(DataPropertyMaster::DD_DISPLAYSTATE_ACTIVE);
//        $object->setFieldlist($fieldlist,array(DataPropertyMaster::DD_DISPLAYSTATE_ACTIVE,DataPropertyMaster::DD_DISPLAYSTATE_VIEWONLY));

        $data['fieldlabels'] = array();
        $data['fieldnames'] = array();
        $data['formfieldnames'] = array();
        $data['formfieldstates'] = array();
        $data['fields'] = array();                  // Deprecated - remove from templates!!!
        $data['columns'] = array();                 // Deprecated - remove from templates!!!
        $sourcefields = array();
//        $fieldnames = array();
        $primarysource = '';
        $primaryalias = '';
        $indices = array();
        $defaultkeyname = '';
        $tablekeyfield = '';
        $keyfieldalias = '';

        $properties =& $object->getProperties(array('status' => array(DataPropertyMaster::DD_DISPLAYSTATE_ACTIVE,DataPropertyMaster::DD_DISPLAYSTATE_VIEWONLY)));
        $noprimary = true;
        foreach ($fieldlist as $fielditem) {
        
            // Explode the single item in the fieldlist
            $parts = explode(':',$fielditem);
            // The name of the field/property
            $fieldname = trim($parts[0]);
            // The name the field will be given on the listing template
            $formfieldname = (isset($parts[1])) ? trim($parts[1]) : $fieldname;
            // The state of the field on the listing template: input/output/hidden
            $formfieldstate = (isset($parts[2])) ? trim($parts[2]) : 'output';

            // Ignore items in the fieldlist that don't corresond to properties
            if (!isset($properties[$fieldname])) continue;
            
            // We have a corresponding property. Check it
            $property = $properties[$fieldname];
            $source = $property->source;
            $alias = $property->name;

            // Ignore fields with "bad" data sources for now
            if (in_array($property->source, $baddatasources)) continue;

            // Special treatment if this is the primary key
            if ($property->type == 21) {
                if ($fieldname == $object->primary) {
                    $noprimary = false;
                    $primarysource = $source;
                    $primaryalias = $alias;
                } else {
                    $indices[] = $source;
                }
                // save the field names for later use
//                $fieldnames[$source] = $alias;
                $data['fieldlabels'][$alias] = $property->label;
                $data['fieldnames'][] = $property->name;
                $data['formfieldnames'][] = $formfieldname;
                $data['formfieldstates'][] = 'output';
                $data['fields'][$alias] = $property->label;                  // Deprecated - remove from templates!!!
                $data['columns'][$alias] = $property->name;                  // Deprecated - remove from templates!!!
                $sourcefields[$alias] = $source;
            }

            // Ignore other fields that don't have active or list status
            if ($nofieldlist) { 
                if (($property->getDisplayStatus() != DataPropertyMaster::DD_DISPLAYSTATE_ACTIVE) &&
                    ($property->getDisplayStatus() != DataPropertyMaster::DD_DISPLAYSTATE_VIEWONLY)
                ) continue;
            }
            
            // Do we need this?
            if ($property->name == 'itemtype') $itemtypefield = $source;

            // We found a property that corresponds to the fieldlist entry
            if ($alias != $primaryalias) {
                // save the field names for later use
//                $fieldnames[$source] = $alias;
                $data['fieldlabels'][$alias] = $property->label;
                $data['fieldnames'][] = $property->name;
                $data['formfieldnames'][] = $formfieldname;
                $data['formfieldstates'][] = $formfieldstate;
                $data['fields'][$alias] = $property->label;                  // Deprecated - remove from templates!!!
                $data['columns'][$alias] = $property->name;                  // Deprecated - remove from templates!!!
                $sourcefields[$alias] = $source;
            }

            // Set the keyfield for use with table selects
            // If a keyfield was passed check if this is it
            // If no key was passed take the first columne
            if (empty($defaultkey) || (!empty($defaultkey) && $property->name == $defaultkey)) {
                // Only pick the primary key if it's being shown
                if ($property->type == 21 && !$this->display_show_primary) continue;
                $defaultkey = $property->name;
                $defaultkeyname = $property->label;
                $tablekeyfield = $source;
                $keyfieldalias = $alias;
            }
        }

        // Check if we had the primary index in the list of fields. Otherwise add it.
        // Its display will be steered by the $show_primary variable
        if ($noprimary) {
            $property = $properties[$object->primary];
            $source = $property->source;
            $alias = $property->name;
            $primarysource = $source;
            $primaryalias = $alias;
//            $fieldnames[$source] = $alias;
            $data['fieldlabels'][$alias] = $property->label;
            $data['fieldnames'][] = $property->name;
            $data['formfieldnames'][] = $alias;
            $data['formfieldstates'][] = 'output';
            $data['fields'][$alias] = $property->label;                  // Deprecated - remove from templates!!!
            $data['columns'][$alias] = $property->name;                  // Deprecated - remove from templates!!!
            $sourcefields[$alias] = $source;
        }

        // Sanity check to make sure we got a key
        if (empty($defaultkey)) {
            throw new Exception(xarML("The listing cannot be displayed, because no select key was found"));
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

                $q = xarSession::getVar('listing.' . $objectname . '.currentquery');
                if (empty($q) || !isset($q)) {
                    $q = new Query('SELECT');
                    $q->setdistinct();
                } else {
                    $q = unserialize($q);
                    $q->open();
                }
                if (!empty($conditions)) $object->dataquery->addconditions($conditions);
                $data['msg'] = $lastmsg;
            break;

    //--- 9. First time visit to this page; empty the sessionvars and reset the categories
            case "newsearch":
                if (!empty($conditions)) {
                    $q = new Query();
                    $q->addconditions($conditions);
                    $object->dataquery->addconditions($conditions);
                    $object->dataquery->addsorts($conditions);
                }

                xarSession::setVar('listing.' . $objectname . '.lastlistingsearch',$objectname);
                xarSession::setVar('listing.' . $objectname . '.msg','');

                // Get any odering from the object's data query if possible
                if (!empty($object->dataquery->sorts)) {
                    $setting = reset($object->dataquery->sorts);
                    $order = $setting['name'];
                    $sort = $setting['order'];
                } else {
                    $order = '';
                    $sort = 'ASC';
                }
            break;

    //--- 10. Any other operation:get the query if it was passed as conditions, or create a new one
            case "lettersearch":
            case "textsearch":

                if (!empty($conditions)) {
                    $object->dataquery->addconditions($conditions);
                }
                $object->dataquery->setdistinct();

    //--- 11. Filter on the objects and itemtypes we'll be displaying

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
        $thisstart = xarSession::getVar('listing.' . $objectname . '.start') ? xarSession::getVar('listing.' . $objectname . '.start') : 1;
        if ($operation == "columnclick") {
            if (isset($order) && $data['startnum']== $thisstart){
                if ($order == $lastorder) {
                    if($sort == 'ASC') $sort = 'DESC';
                       else $sort = 'ASC';
                } else {
                    $sort = 'ASC';
                }
                xarSession::setVar('listing.' . $objectname . '.sort',$sort);
                xarSession::setVar('listing.' . $objectname . '.lastorder',$order);
                $data['search'] = $search; //pass along search
            } elseif (empty($letter) && empty($search)) {
                //if order is not set - set it to the default key field but keep it at 'DESC'
                $order = $keyfieldalias;
                $sort = 'ASC';
                xarSession::setVar('listing.' . $objectname . '.lastorder',$order);
            }
            $data['msg'] = '';
        }
        if (isset($sourcefields[$order])) {
            $object->dataquery->setorder($sourcefields[$order],$sort);
        } else {
            $object->dataquery->setorder($order,$sort);
        }

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
                    $object->dataquery->regex($tablekeyfield, '^(\\\%)*' . $letter);
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
                    foreach ($sourcefields as $sourcefield => $value) {
                        if ($i >0) {
                            $msg .= ' or';
                        }
                        $c[$i]= $object->dataquery->plike($value, $qsearch);
                        $msg .= ' '.$data['fieldlabels'][$sourcefield].' ';
                        $i++;
                    }
                    if (!empty($msg) && $i>0) {
                        if (empty($data['msg'])) $data['msg'] = xarML('Listing where #(1) contain "#(2)"',$msg,$search);
                        else  $data['msg'] .= xarML(' and listing where #(1) contain "#(2)"',$msg,$search);
                    }
                    // take the conditions we decided on above and add them to the query as a bunch of ORs
                    $object->dataquery->qor($c);
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

        // Save the query in a sessionvar for reuse
        xarSession::setVar('listing.' . $objectname,serialize($object->dataquery));
        
        // Set the number of lines to display
        if (!empty($this->display_items_per_page)) $object->dataquery->setrowstodo($this->display_items_per_page);

        // The record to start at needs to come from the template
        $object->dataquery->setstartat($data['startnum']);

        // CHECKME: do we need all 3 of these passed to the template
        $data['order'] = $order;
        $data['letter'] = $letter;
        $data['searchstring'] = $search;

        // display the query if I need to
//        echo "<br />"; $object->dataquery->qecho();
    //    exit;

    // Now we run the query, if that is required
    // Use isset here to check whether a $items param was even passed
    if (!isset($items)) {
        // add conditions if they were passed
        if (!empty($conditions)) $object->dataquery->addconditions($conditions);
        // get the records to be displayed
        $items = $object->getItems();
        // We may need to recalculate the total if we have linked tables
        // Just force it for now
        $data['total'] = $object->dataquery->getrows();
    } else {
        if (!empty($this->display_items_per_page)) {
            // items were passed, but we need to get the correct subset
            // first get the total
            $data['total'] = count($items);
            $tempitems = array();
            $startat = $object->dataquery->startat-1;
            $endat = $startat + $object->dataquery->rowstodo;
            for ($i=$startat;$i<$endat;$i++)  {
                if (!isset($items[$i])) break;
                $tempitems[] = $items[$i];
            }
            $items = $tempitems;
        }
    }
    
    
/*
    $parts = explode('.',$primarysource);
    $primarytable = "**MISSING**";
    foreach ($object->dataquery->tables as $table) {
        if ($parts[0] == $table['alias']) {
            $primarytable = $table;
            break;
        }
    }
    $object->dataquery->addfield('COUNT(' . $primarytable['alias'] . '.id) AS total');
*/
    
        // Add the filter variable to show a filter form
        if (!isset($data['filter'])) $data['filter'] = 0;

        // Add field definitions to the template variables

        $data['tablekeyfield'] = $tablekeyfield;
        $data['keyfieldalias'] = $keyfieldalias;
        $data['defaultkeyname'] = $defaultkeyname;
        $data['properties'] = $properties;

        // Add the array of items to the template variables
        $data['items'] = $items;

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

        // we also need a reference to the primary column for the template
        $data['primaryalias'] = $primaryalias;

        // Add a reference to the object itself
        $data['object'] = $object;

        // Set the session vars to the latest state
        xarSession::setVar('listing.' . $objectname . '.sort',$sort);
        xarSession::setVar('listing.' . $objectname . '.lastorder',$order);
        xarSession::setVar('listing.' . $objectname . '.start',$data['startnum']);
        xarSession::setVar('listing.' . $objectname . '.msg',$data['msg']);

        // Sort of ugly. How can we do better?
        unset($q->dbconn);unset($q->output);unset($q->result);

        return $data;
    }
}

?>