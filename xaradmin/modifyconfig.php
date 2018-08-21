<?php
/**
 * Ratings Module
 *
 * @package modules
 * @subpackage ratings module
 * @category Third Party Xaraya Module
 * @version 2.0.0
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://xaraya.com/index.php/release/41.html
 * @author Jim McDonald
 */
/**
 * Update the configuration parameters of the module based on data from the modification form
 *
 * @author Jim McDonald
 * @access public
 * @param no $ parameters
 * @return true on success or void on failure
 * @throws no exceptions
 * @todo nothing
 */
function ratings_admin_modifyconfig()
{
    // Security Check
    if (!xarSecurityCheck('AdminRatings')) return;

    if (!xarVarFetch('phase', 'str:1:100', $phase,       'modify',  XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('tab',   'str:1:100', $data['tab'], 'general', XARVAR_NOT_REQUIRED)) return;

    $data['module_settings'] = xarMod::apiFunc('base','admin','getmodulesettings',array('module' => 'ratings'));
    $data['module_settings']->setFieldList('items_per_page, use_module_alias, module_alias_name, enable_short_urls');
    $data['module_settings']->getItem();

    switch (strtolower($phase)) {
        case 'modify':
        default:
            switch ($data['tab']) {
                case 'general':

                $defaultratingsstyle = xarModVars::get('ratings', 'defaultratingsstyle');
                $defaultseclevel = xarModVars::get('ratings', 'seclevel');
                $defaultshownum = xarModVars::get('ratings', 'shownum');

                $data['settings'] = array();
                $data['settings']['default'] = array('label' => xarML('Default configuration'),
                                                     'ratingsstyle' => $defaultratingsstyle,
                                                     'seclevel' => $defaultseclevel,
                                                     'shownum' => $defaultshownum);

                $hookedmodules = xarMod::apiFunc('modules', 'admin', 'gethookedmodules',
                                               array('hookModName' => 'ratings'));

                if (isset($hookedmodules) && is_array($hookedmodules)) {
                    foreach ($hookedmodules as $modname => $value) {
                        // we have hooks for individual item types here
                        if (!isset($value[0])) {
                            // Get the list of all item types for this module (if any)
                            $mytypes = xarMod::apiFunc($modname,'user','getitemtypes',
                                                     // don't throw an exception if this function doesn't exist
                                                     array(), 0);
                            foreach ($value as $itemtype => $val) {
                                $ratingsstyle = xarModVars::get('ratings', "ratingsstyle.$modname.$itemtype");
                                if (empty($ratingsstyle)) {
                                    $ratingsstyle = $defaultratingsstyle;
                                }
                                $seclevel = xarModVars::get('ratings', "seclevel.$modname.$itemtype");
                                if (empty($seclevel)) {
                                    $seclevel = $defaultseclevel;
                                }
                                $shownum = xarModVars::get('ratings', "shownum.$modname.$itemtype");
                                if (empty($shownum)) {
                                    $shownum = $defaultshownum;
                                    xarModVars::set('ratings',"shownum.$modname.$itemtype", $defaultshownum);
                                }
                                if (isset($mytypes[$itemtype])) {
                                    $type = $mytypes[$itemtype]['label'];
                                    $link = $mytypes[$itemtype]['url'];
                                } else {
                                    $type = xarML('type #(1)',$itemtype);
                                    $link = xarModURL($modname,'user','view',array('itemtype' => $itemtype));
                                }
                                $data['settings']["$modname.$itemtype"] = array('label' => xarML('Configuration for #(1) module - <a href="#(2)">#(3)</a>', $modname, $link, $type),
                                                                                'ratingsstyle' => $ratingsstyle,
                                                                                'seclevel' => $seclevel,
                                                                                'shownum' => $shownum);
                            }
                        } else {
                            $ratingsstyle = xarModVars::get('ratings', 'ratingsstyle.' . $modname);
                            if (empty($ratingsstyle)) {
                                $ratingsstyle = $defaultratingsstyle;
                            }
                            $seclevel = xarModVars::get('ratings', 'seclevel.' . $modname);
                            if (empty($seclevel)) {
                                $seclevel = $defaultseclevel;
                            }
                            $shownum = xarModVars::get('ratings', 'shownum.' . $modname);
                            if (empty($shownum)) {
                                $shownum = $defaultshownum;
                                xarModVars::set('ratings',"shownum.$modname", $defaultshownum);
                            }
                            $link = xarModURL($modname,'user','main');
                            $data['settings'][$modname] = array('label' => xarML('Configuration for <a href="#(1)">#(2)</a> module', $link, $modname),
                                                                'ratingsstyle' => $ratingsstyle,
                                                                'seclevel' => $seclevel,
                                                                'shownum' => $shownum);
                        }
                    }
                }

                $data['secleveloptions'] = array(
                    array('id' => 'low', 'name' => xarML('Low : users can vote multiple times')),
                    array('id' => 'medium', 'name' => xarML('Medium : users can vote once per day')),
                    array('id' => 'high', 'name' => xarML('High : users must be logged in and can only vote once')),
                    );

                $data['authid'] = xarSecGenAuthKey();
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
            if (!xarSecConfirmAuthKey()) {
                return xarTplModule('privileges','user','errors',array('layout' => 'bad_author'));
            }        
            switch ($data['tab']) {
                case 'general':

                    $isvalid = $data['module_settings']->checkInput();
                    if (!$isvalid) {
                        return xarTplModule('eventhub','admin','modifyconfig', $data);
                    } else {
                        $itemid = $data['module_settings']->updateItem();
                    }


                    // Return
                    return true;
                        break;
                    case 'tab2':
                        break;
                    case 'tab3':
                        break;
                    default:
                        break;
            }
            break;
    }
    return $data;
}

?>