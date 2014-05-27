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
* Unit test class for the EmptyStatement sniff.
*
* @package code_sniffer
* @author Manuel Pichler <mapi@phpundercontrol.org>
*/
class phpbb_Tests_Commenting_FileCommentUnitTest extends AbstractSniffUnitTest
{
	
	/**
	* Returns the lines where errors should occur.
	*
	* The key of the array should represent the line number and the value
	* should represent the number of errors that should occur on that line.
	*
	* @return array(int => int)
	*/
	public function getErrorList()
	{
		return array(
            7  =>  1 // BSD License error :)
		);
	}//end getErrorList()


	/**
	* Returns the lines where warnings should occur.
	*
	* The key of the array should represent the line number and the value
	* should represent the number of warnings that should occur on that line.
	*
	* @return array(int => int)
	*/
	public function getWarningList()
	{
		return array(
		    4  =>  1,
            8  =>  1
        );
	}//end getWarningList()
}
