<?php

/**
 * @package modules\ratings
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Ratings\AdminApi;


use Xaraya\Modules\Ratings\AdminApi;
use Xaraya\Modules\MethodClass;
use xarDB;
use xarModVars;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * ratings adminapi delete function
 * @extends MethodClass<AdminApi>
 */
class DeleteMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * delete a ratings item - hook for ('item','delete','API')
     * @param mixed $args ['itemid'] ID of the item
     * @param mixed $args ['extrainfo'] extra information
     * @param mixed $args ['confirm'] string coming from the delete GUI function
     * @param mixed $args ['modid'] int module id
     * @param mixed $args ['itemtype'] int itemtype
     * @param mixed $args ['itemid'] int item id
     * @return bool true on success, false on failure
     */
    public function __invoke(array $args = [])
    {
        // Get arguments from argument array
        extract($args);

        // if we're coming via a hook call
        if (isset($itemid)) {
            // TODO: cfr. hitcount delete stuff, once we enable item delete hooks
            // Return the extra info
            if (!isset($extrainfo)) {
                $extrainfo = [];
            }
            return $extrainfo;

            // if we're coming from the delete GUI (or elsewhere)
        } elseif (!empty($confirm)) {
            // Database information
            $dbconn = xarDB::getConn();
            $xartable = & xarDB::getTables();
            $ratingstable = $xartable['ratings'];

            $query = "DELETE FROM $ratingstable ";
            $bindvars = [];
            if (!empty($modid)) {
                if (!is_numeric($modid)) {
                    $msg = xarML(
                        'Invalid #(1) for #(2) function #(3)() in module #(4)',
                        'module id',
                        'admin',
                        'delete',
                        'Ratings'
                    );
                    throw new Exception($msg);
                }
                if (empty($itemtype) || !is_numeric($itemtype)) {
                    $itemtype = 0;
                }
                $query .= " WHERE module_id = ?
                              AND itemtype = ?";
                $bindvars[] = $modid;
                $bindvars[] = $itemtype;
                if (!empty($itemid)) {
                    $query .= " AND itemid = ?";
                    $bindvars[] = $itemid;
                }
            }

            $result = & $dbconn->Execute($query, $bindvars);
            if (!$result) {
                return;
            }

            // TODO: delete user votes with xarModVars::delete('ratings',"$modname:$itemtype:$itemid");

            return true;
        }
        return false;
    }
}
