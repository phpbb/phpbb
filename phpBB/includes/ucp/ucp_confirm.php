<?php
/**
*
* @package VC
* @version $Id$
* @copyright (c) 2005 phpBB Group
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
* ucp_confirm
* Visual confirmation
*
* Note to potential users of this code ...
*
* Remember this is released under the _GPL_ and is subject
* to that licence. Do not incorporate this within software
* released or distributed in any way under a licence other
* than the GPL. We will be watching ... ;)
*
* @package VC
*/
class ucp_confirm
{
	var $u_action;

	function main($id, $mode)
	{
		global $db, $user, $config;

		// Do we have an id? No, then just exit
		$confirm_id = request_var('id', '');
		$type = request_var('type', 0);

		if (!$confirm_id || !$type)
		{
			exit;
		}

		// Try and grab code for this id and session
		$sql = 'SELECT code, seed
			FROM ' . CONFIRM_TABLE . "
			WHERE session_id = '" . $db->sql_escape($user->session_id) . "'
				AND confirm_id = '" . $db->sql_escape($confirm_id) . "'
				AND confirm_type = $type";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		// If we have a row then grab data else create a new id
		if (!$row)
		{
			exit;
		}

		if ($config['captcha_gd'])
		{
			include(PHPBB_ROOT_PATH . 'includes/captcha/captcha_gd.' . PHP_EXT);
		}
		else
		{
			include(PHPBB_ROOT_PATH . 'includes/captcha/captcha_non_gd.' . PHP_EXT);
		}

		captcha::execute($row['code'], $row['seed']);

		garbage_collection();
		exit_handler();
	}
}

?>