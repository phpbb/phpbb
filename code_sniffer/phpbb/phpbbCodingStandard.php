<?php
/** 
*
* @package code_sniffer
* @version $Id: $
* @copyright (c) 2007 phpBB Group 
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2 
*
*/

/**
 * @ignore
 */
if (class_exists('PHP_CodeSniffer_Standards_CodingStandard', true) === false) {
    throw new PHP_CodeSniffer_Exception(
        'Class PHP_CodeSniffer_Standards_CodingStandard not found'
    );
}

/**
 * Primary class for the phpbb coding standard.
 *
 * @package code_sniffer
 */
class PHP_CodeSniffer_Standards_phpbb_phpbbCodingStandard extends PHP_CodeSniffer_Standards_CodingStandard
{
	/**
    * Return a list of external sniffs to include with this standard.
    *
    * External locations can be single sniffs, a whole directory of sniffs, or
    * an entire coding standard. Locations start with the standard name. For
    * example:
    *  PEAR                              => include all sniffs in this standard
    *  PEAR/Sniffs/Files                 => include all sniffs in this dir
    *  PEAR/Sniffs/Files/LineLengthSniff => include this single sniff
    *
    * @return array
    */
    public function getIncludedSniffs()
    {
        return array();
    }
}
