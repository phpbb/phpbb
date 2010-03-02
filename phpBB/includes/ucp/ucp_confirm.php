<?php
/**
*
* @package VC
* @version $Id$
* @copyright (c) 2005 2008 phpBB Group
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
		global $db, $user, $phpbb_root_path, $config, $phpEx;

		include($phpbb_root_path . 'includes/captcha/captcha_factory.' . $phpEx);
		$captcha = phpbb_captcha_factory::get_instance($config['captcha_plugin']);
		$captcha->init(request_var('type', 0));
		$captcha->execute();

		garbage_collection();
		exit_handler();
	}
}

?>