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

class acp_logs_info
{
	function module()
	{
		global $phpbb_dispatcher;

		$modes = array(
			'admin'		=> array('title' => 'ACP_ADMIN_LOGS', 'auth' => 'acl_a_viewlogs', 'cat' => array('ACP_FORUM_LOGS')),
			'mod'		=> array('title' => 'ACP_MOD_LOGS', 'auth' => 'acl_a_viewlogs', 'cat' => array('ACP_FORUM_LOGS')),
			'users'		=> array('title' => 'ACP_USERS_LOGS', 'auth' => 'acl_a_viewlogs', 'cat' => array('ACP_FORUM_LOGS')),
			'critical'	=> array('title' => 'ACP_CRITICAL_LOGS', 'auth' => 'acl_a_viewlogs', 'cat' => array('ACP_FORUM_LOGS')),
		);

		/**
		* Event to add or modify ACP log modulemodes
		*
		* @event core.acp_logs_info_modify_modes
		* @var	array	modes	Array with modes info
		* @since 3.1.11-RC1
		* @since 3.2.1-RC1
		*/
		$vars = array('modes');
		extract($phpbb_dispatcher->trigger_event('core.acp_logs_info_modify_modes', compact($vars)));

		return array(
			'filename'	=> 'acp_logs',
			'title'		=> 'ACP_LOGGING',
			'modes'		=> $modes,
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}
