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
	var $assertions_failed = array();

	function assert($assertion, $error_message)
	{
		if (!$assertion)
		{
			$this->assertions_failed[] = $error_message;
		}
	}

	function get_failed_assertions()
	{
		return $this->assertions_failed;
	}
}
