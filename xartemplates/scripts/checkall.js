/**
 * check or uncheck all checkboxes in a form
 * Example :
 * <a href="javascript:xar_base_checkall(true)">Check All</a>
 * <a href="javascript:xar_base_checkall(false)">Uncheck All</a>
 */
function listing_checkall(value) {
    var checkboxes = document.getElementsByTagName('input');
    for (i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i].name == 'optionscheckbox') 
            checkboxes[i].checked = value;
    }
}

/**
 * get the id values of all checked checkboxes below a certain elementand stick them in the target object
 * Example :
 * <a href="javascript:listing_getchecked(targetId)">....</a>
 */
function listing_getchecked(targetId) {
    var checked = "";
    var checkboxes = document.getElementsByTagName('input');
    for (i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i].name == 'optionscheckbox') 
            if (checkboxes[i].checked) 
                checked += checkboxes[i].id + ",";
    }
    if (checked != "") checked = checked.substring(0, checked.length - 1);
    target = document.getElementById(targetId);
    target.value = checked;
}