<?php

/**
 * @package modules\ratings
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Ratings\AdminGui;


use Xaraya\Modules\Ratings\AdminGui;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarTpl;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * ratings admin overview function
 * @extends MethodClass<AdminGui>
 */
class OverviewMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Overview function that displays the standard Overview page
     */
    public function __invoke(array $args = [])
    {
        /* Security Check */
        if (!$this->checkAccess('AdminRatings', 0)) {
            return;
        }

        $data = [];

        /* if there is a separate overview function return data to it
         * else just call the main function that displays the overview
         */

        $data['context'] = $this->getContext();
        return xarTpl::module('ratings', 'admin', 'main', $data, 'main');
    }
}
