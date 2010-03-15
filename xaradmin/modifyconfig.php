<?php
/**
 * Main configuration page for the ckeditor module
 *
 */

// Use this version of the modifyconfig file when the module is not a  utility module

    function ckeditor_admin_modifyconfig()
    {
        // Security Check
        if (!xarSecurityCheck('AdminCKEditor')) return;
        if (!xarVarFetch('phase', 'str:1:100', $phase, 'modify', XARVAR_NOT_REQUIRED, XARVAR_PREP_FOR_DISPLAY)) return;
        if (!xarVarFetch('tab', 'str:1:100', $data['tab'], 'general', XARVAR_NOT_REQUIRED)) return;

        $data['module_settings'] = xarMod::apiFunc('base','admin','getmodulesettings',array('module' => 'dynamicdata'));
        $data['module_settings']->setFieldList('items_per_page, enable_short_urls, use_module_alias, use_module_icons');
        $data['module_settings']->getItem();
        switch (strtolower($phase)) {
            case 'modify':
            default:
                switch ($data['tab']) {
                    case 'general':
                        break;
                    case 'tab2':
                        break;
                    case 'tab3':
                        break;
                    default:
                        break;
                }

                break;

            case 'update':
                // Confirm authorisation code
                if (!xarSecConfirmAuthKey()) return;
                switch ($data['tab']) {
                    case 'general':
                        if (!xarVarFetch('editorversion', 'str', $editorversion,  xarModVars::get('ckeditor', 'editorversion'), XARVAR_NOT_REQUIRED)) return;

                        $isvalid = $data['module_settings']->checkInput();
                        if (!$isvalid) {
                            return xarTplModule('dynamicdata','admin','modifyconfig', $data);
                        } else {
                            $itemid = $data['module_settings']->updateItem();
                        }

                        xarModVars::set('ckeditor', 'editorversion', $editorversion);
                        break;
                    case 'tab2':
                        break;
                    case 'tab3':
                        break;
                    default:
                        break;
                }

                xarResponse::redirect(xarModURL('ckeditor', 'admin', 'modifyconfig',array('tab' => $data['tab'])));
                // Return
                return true;
                break;

        }
        $data['authid'] = xarSecGenAuthKey();
        return $data;
    }
?>
