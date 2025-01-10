<?php

/**
 * @package modules\ratings
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Ratings\UserApi;

use Xaraya\Modules\MethodClass;
use xarMod;
use xarSecurity;
use xarDB;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * ratings userapi topitems function
 */
class TopitemsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * get the list of items with top N ratings for a module
     * @param mixed $args ['modname'] name of the module you want items from
     * @param mixed $args ['itemtype'] item type (optional)
     * @param mixed $args ['numitems'] number of items to return
     * @param mixed $args ['startnum'] start at this number (1-based)
     * @return array of array('itemid' => $itemid, 'hits' => $hits)
     */
    public function __invoke(array $args = [])
    {
        // Get arguments from argument array
        extract($args);

        // Argument check
        if (!isset($modname)) {
            $msg = xarML(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                xarML('module name'),
                'user',
                'topitems',
                'ratings'
            );
            throw new Exception($msg);
        }
        $modid = xarMod::getRegID($modname);
        if (empty($modid)) {
            $msg = xarML(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                xarML('module id'),
                'user',
                'topitems',
                'ratings'
            );
            throw new Exception($msg);
        }

        if (!isset($itemtype)) {
            $itemtype = 0;
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
        $query = "SELECT itemid, rating
                FROM $ratingstable
                WHERE module_id = ?
                  AND itemtype = ?
                ORDER BY rating DESC";
        $bindvars = [$modid, $itemtype];
        if (!isset($numitems) || !is_numeric($numitems)) {
            $numitems = 10;
        }
        if (!isset($startnum) || !is_numeric($startnum)) {
            $startnum = 1;
        }

        //$result =& $dbconn->Execute($query);
        $result = $dbconn->SelectLimit($query, $numitems, $startnum - 1, $bindvars);
        if (!$result) {
            return;
        }

        $topitems = [];
        while (!$result->EOF) {
            [$id, $rating] = $result->fields;
            $topitems[] = ['itemid' => $id, 'rating' => $rating];
            $result->MoveNext();
        }
        $result->close();
        return $topitems;
    }
}
