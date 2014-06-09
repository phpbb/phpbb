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

class ucp_pm_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_pm',
			'title'		=> 'UCP_PM',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'view'		=> array('title' => 'UCP_PM_VIEW', 'auth' => 'cfg_allow_privmsg', 'display' => false, 'cat' => array('UCP_PM')),
				'compose'	=> array('title' => 'UCP_PM_COMPOSE', 'auth' => 'cfg_allow_privmsg', 'cat' => array('UCP_PM')),
				'drafts'	=> array('title' => 'UCP_PM_DRAFTS', 'auth' => 'cfg_allow_privmsg', 'cat' => array('UCP_PM')),
				'options'	=> array('title' => 'UCP_PM_OPTIONS', 'auth' => 'cfg_allow_privmsg', 'cat' => array('UCP_PM')),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}
