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
 * specifies module tables namees
 *
 * @author  Jim McDonald
 * @access  public
 * @param   none
 * @return  $xartable array
 * @todo    nothing
*/
function ratings_xartables(?string $prefix = null)
{
    // Initialise table array
    $xartable = [];
    $prefix ??= xarDB::getPrefix();
    // Name for ratings database entities
    $xartable['ratings'] = $prefix . '_ratings';
    $xartable['ratings_likes'] = $prefix . '_ratings_likes';
    // Return table information
    return $xartable;
}
