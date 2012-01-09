<?php
/**
*
* @package phpBB
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Collector class for checking boolean assertions.
*
* Provides ability of assigning error messages for false assertions.
*
* @package phpBB
*/
class phpbb_assertion_manager
{
	var $failed_assertions = array();

	/**
	* Assertion checker and collector
	* If assertion us false, collect corresponding error message into array.
	*
	* @param bool $assertion	Assertion (true|false). 
	* @param string				Error message assigned to false assertion.
	*/
	function assert($assertion, $error_message)
	{
		if (!$assertion)
		{
			$this->failed_assertions[] = $error_message;
		}
	}

	/**
	* Failed assertions output
	*
	* @return array		Array of error messages collected by function assert()
	*					Empty if no failed assertions collected
	*/
	function get_failed_assertions()
	{
		return $this->failed_assertions;
	}
}
