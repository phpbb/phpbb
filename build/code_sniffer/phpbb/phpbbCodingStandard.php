<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
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
