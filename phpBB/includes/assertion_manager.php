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

class phpbb_assertion_manager
{
	var $failed_assertions = array();

	function assert($assertion, $error_message)
	{
		if (!$assertion)
		{
			$this->failed_assertions[] = $error_message;
		}
	}

	function get_failed_assertions()
	{
		return $this->failed_assertions;
	}
}
