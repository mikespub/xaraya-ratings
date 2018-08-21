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
 * display rating for a specific item, and request rating
 * @param $args['itemid'] ID of the item this rating is for
 * @param $args['extrainfo'] URL to return to if user chooses to rate
 * @param $args['ratingsstyle'] ratings style to display this rating in (optional)
 * @param $args['shownum'] bool to show number of ratings (optional)
 * @param $args['showdisplay'] bool to show rating result (optional)
 * @param $args['showinput'] bool to show rating form (optional)
 * @param $args['itemtype'] item type
 * @return array output with rating information $numratings, $rating, $rated, $authid
 */
function ratings_user_display($args)
{
    extract($args);

    $data = array();
    $data['itemid'] = $itemid;

    $itemtype = 0;
    if (isset($extrainfo) && is_array($extrainfo)) {
        if (isset($extrainfo['module']) && is_string($extrainfo['module'])) {
            $modname = $extrainfo['module'];
        }
        if (isset($extrainfo['itemtype']) && is_numeric($extrainfo['itemtype'])) {
            $itemtype = $extrainfo['itemtype'];
        }
        if (isset($extrainfo['returnurl']) && is_string($extrainfo['returnurl'])) {
            $data['returnurl'] = $extrainfo['returnurl'];
        }
        if (isset($extrainfo['ratingsstyle']) && is_string($extrainfo['ratingsstyle'])) {
            if(in_array($ratingsstyle, array('outoffive','outoffivestars','outoften','outoftenstars','customised'))) {
                $ratingsstyle = $extrainfo['ratingsstyle'];
            }
        }
        if (isset($extrainfo['shownum']) && ($extrainfo['shownum'] == 0 || $extrainfo['shownum'] == 1)) {
            $shownum = $extrainfo['shownum'];
        }
        if (isset($extrainfo['showdisplay']) && ($extrainfo['showdisplay'] == 0 || $extrainfo['showdisplay'] == 1)) {
            $showdisplay = $extrainfo['showdisplay'];
        }
        if (isset($extrainfo['showinput']) && ($extrainfo['showinput'] == 0 || $extrainfo['showinput'] == 0)) {
            $showinput = $extrainfo['showinput'];
        }
    } else {
        $data['returnurl'] = $extrainfo;
    }

    if (empty($modname)) {
        $modname = xarMod::getName();
    }
//    $args['modname'] = $modname;
//    $args['itemtype'] = $itemtype;

    if (!isset($ratingsstyle)) {
        if (!empty($itemtype)) {
            $ratingsstyle = xarModVars::get('ratings', "ratingsstyle.$modname.$itemtype");
        }
        if (!isset($ratingsstyle)) {
            $ratingsstyle = xarModVars::get('ratings', 'ratingsstyle.'.$modname);
        }
        if (!isset($ratingsstyle)) {
            $ratingsstyle = xarModVars::get('ratings', 'defaultratingsstyle');
        }
    }
    if (!isset($shownum)) {
        if (!empty($itemtype)) {
            $shownum = xarModVars::get('ratings', "shownum.$modname.$itemtype");
        }
        if (!isset($shownum)) {
            $shownum = xarModVars::get('ratings', 'shownum.'.$modname);
        }
        if (!isset($shownum)) {
            $shownum = xarModVars::get('ratings', 'shownum');
        }
    }

    if(isset($showdisplay) && $showdisplay != true) {
        $showdisplay = false;
    } else {
        $showdisplay = true;
    }
    if(isset($showinput) && $showinput != true) {
        $showinput = false;
    } else {
        $showinput = true;
    }

    // if we're not showing anything, bail out early
    if($shownum == false && $showdisplay == false && $showinput == false) {
        return;
    }

    $data['ratingsstyle'] = $ratingsstyle;
    $data['modname'] = $modname;
    $data['itemtype'] = $itemtype;
    $data['shownum'] = $shownum;
    $data['showdisplay'] = $showdisplay;
    $data['showinput'] = $showinput;

    // Select the right rating
    $args['modname'] = $modname;
    $args['itemtype'] = $itemtype;
    $args['itemids'] = array($itemid);

    // Run API function
    // Bug 6160 Use getitems at first, then get if we get weird results
    $rating = xarMod::apiFunc('ratings',
                           'user',
                           'getitems',
                           $args);
    // Select the way to get the rating
    if (!empty($rating[$itemid])) {
//        $key_id = array_keys($rating);
        $data['rawrating'] = $rating[$key_id[0]]['rating'];
        $data['numratings'] = $rating[$key_id[0]]['numratings'];
    } else {
        // Use old fashioned way
        $data['rawrating'] = xarMod::apiFunc('ratings',
                           'user',
                           'get',
                           $args);
        $data['numratings'] = 0;
    }
    if (isset($data['rawrating'])) {
        // Set the cached variable if requested
        if (xarVarIsCached('Hooks.ratings','save') &&
            xarVarGetCached('Hooks.ratings','save') == true) {
            xarVarSetCached('Hooks.ratings','value',$data['rawrating']);
        }

        // Display current rating
        switch($data['ratingsstyle']) {
            case 'percentage':
                $data['rating'] = sprintf("%.1f",$data['rawrating']);
                break;
            case 'outoffive':
                $data['rating'] = round($data['rawrating']/20);
                break;
            case 'outoffivestars':
                $data['rating'] = round($data['rawrating']/20);
                $data['intrating'] = (int)($data['rawrating']/20);
                $data['fracrating'] = $data['rawrating'] - (20 * $data['intrating']);
                break;
            case 'outoften':
                $data['rating'] = (int)($data['rawrating']/10);
                break;
            case 'outoftenstars':
                $data['rating'] = sprintf("%.1f",$data['rawrating']);
                $data['intrating'] = (int)($data['rawrating']/10);
                $data['fracrating'] = $data['rawrating'] - (10 * $data['intrating']);
                break;
            case 'customised':
            default:
                $data['rating'] = sprintf("%.1f",$data['rawrating']);
                break;
        }
    } else {
        $data['rating'] = 0;
        $data['intrating'] = 0;
        $data['fracrating'] = 0;
    }

    // Multiple rate check
    if (!empty($itemtype)) {
        $seclevel = xarModVars::get('ratings', "seclevel.$modname.$itemtype");
        if (!isset($seclevel)) {
            $seclevel = xarModVars::get('ratings', 'seclevel.'.$modname);
        }
    } else {
        $seclevel = xarModVars::get('ratings', 'seclevel.'.$modname);
    }
    if (!isset($seclevel)) {
        $seclevel = xarModVars::get('ratings', 'seclevel');
    }
    if ($seclevel == 'high') {
        // Check to see if user has already voted
        if (xarUserIsLoggedIn()) {
            if (!xarModVars::get('ratings',$modname.':'.$itemtype.':'.$itemid)) {
                xarModVars::set('ratings',$modname.':'.$itemtype.':'.$itemid,1);
            }
            $rated = xarModUserVars::get('ratings',$modname.':'.$itemtype.':'.$itemid);
            if (!empty($rated) && $rated > 1) {
                $data['rated'] = true;
            }
        } else {
            // no rating for anonymous users here
            $data['rated'] = true;
            // bug 5482 Always set the authid, but only a true one if security is met
            $data['authid'] = 0;
        }
    } elseif ($seclevel == 'medium') {
        // Check to see if user has already voted
        if (xarUserIsLoggedIn()) {
            if (!xarModVars::get('ratings',$modname.':'.$itemtype.':'.$itemid)) {
                xarModVars::set('ratings',$modname.':'.$itemtype.':'.$itemid,1);
            }
            $rated = xarModUserVars::get('ratings',$modname.':'.$itemtype.':'.$itemid);
            if (!empty($rated) && $rated > time() - 24*60*60) {
                $data['rated'] = true;
            }
        } else {
            $rated = xarSession::getVar('ratings:'.$modname.':'.$itemtype.':'.$itemid);
            if (!empty($rated) && $rated > time() - 24*60*60) {
                $data['rated'] = true;
            }
        }
    } // No check for low

    // module name is mandatory here, because this is displayed via hooks (= from within another module)
    // set an authid, but only if the current user can rate the item
    if (xarSecurityCheck('CommentRatings', 0, 'Item', "$modname:$itemtype:$itemid")) {
        $data['authid'] = xarSecGenAuthKey('ratings');
    }
    return $data;
}

?>