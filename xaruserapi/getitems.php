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
 * get a rating for a list of items
 *
 * @param $args['modname'] name of the module you want items from, or
 * @param $args['modid'] module id you want items from
 * @param $args['itemtype'] item type (optional)
 * @param $args['itemids'] array of item IDs
 * @param $args['sort'] string sort by itemid (default), rating or numratings
 * @return array $array[$itemid] = array('numratings' => $numratings, 'rating' => $rating)
 */
function ratings_userapi_getitems(array $args = [], $context = null)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if (!isset($modname) && !isset($modid)) {
        $msg = xarML(
            'Invalid #(1) for #(2) function #(3)() in module #(4)',
            xarML('module name'),
            'user',
            'getitems',
            'ratings'
        );
        throw new Exception($msg);
    }
    if (!empty($modname)) {
        $modid = xarMod::getRegID($modname);
    }
    if (empty($modid)) {
        $msg = xarML(
            'Invalid #(1) for #(2) function #(3)() in module #(4)',
            xarML('module id'),
            'user',
            'getitems',
            'ratings'
        );
        throw new Exception($msg);
    }
    // Bug 5856: is this needed?
    if (!isset($itemtype)) {
        $itemtype = 0;
    }
    if (empty($sort)) {
        $sort = 'itemid';
    }

    // Security Check
    if (!xarSecurity::check('ReadRatings')) {
        return;
    }

    // Database information
    $dbconn = xarDB::getConn();
    $xartable = & xarDB::getTables();
    $ratingstable = $xartable['ratings'];

    // Get items
    $query = "SELECT itemid, rating, numratings
            FROM $ratingstable
            WHERE module_id = ?
              AND itemtype = ?";

    $bindvars[] = (int) $modid;
    $bindvars[] = (int) $itemtype;

    if (isset($itemids) && count($itemids) > 0) {
        $allids = join(', ', $itemids);
        $query .= " AND itemid IN (?)";
        $bindvars[] = $allids;
    }
    if ($sort == 'rating') {
        $query .= " ORDER BY rating DESC, numratings DESC";
    } elseif ($sort == 'numratings') {
        $query .= " ORDER BY numratings DESC, rating DESC";
    } else {
        $query .= " ORDER BY itemid ASC";
    }

    $result = & $dbconn->Execute($query, $bindvars);
    if (!$result) {
        return;
    }

    $getitems = [];
    while (!$result->EOF) {
        [$id, $rating, $numratings] = $result->fields;
        $getitems[$id] = ['numratings' => $numratings, 'rating' => $rating];
        $result->MoveNext();
    }
    $result->close();

    return $getitems;
}
