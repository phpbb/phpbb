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
