<?php
/**
 * Main user GUI function, entry point
 *
 */

    function ckeditor_user_main()
    {
        // Security Check
        if (!xarSecurityCheck('ReadCKEditor')) return;

//        xarResponse::redirect(xarModURL('ckeditor', 'user', 'view'));
        // success
        return array(); //true;
    }

?>
