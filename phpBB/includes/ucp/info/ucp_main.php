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

class ucp_main_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_main',
			'title'		=> 'UCP_MAIN',
			'modes'		=> array(
				'front'			=> array('title' => 'UCP_MAIN_FRONT', 'auth' => '', 'cat' => array('UCP_MAIN')),
				'subscribed'	=> array('title' => 'UCP_MAIN_SUBSCRIBED', 'auth' => '', 'cat' => array('UCP_MAIN')),
				'bookmarks'		=> array('title' => 'UCP_MAIN_BOOKMARKS', 'auth' => 'cfg_allow_bookmarks', 'cat' => array('UCP_MAIN')),
				'drafts'		=> array('title' => 'UCP_MAIN_DRAFTS', 'auth' => '', 'cat' => array('UCP_MAIN')),
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
