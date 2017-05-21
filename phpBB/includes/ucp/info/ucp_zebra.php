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

class ucp_zebra_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_zebra',
			'title'		=> 'UCP_ZEBRA',
			'modes'		=> array(
				'friends'		=> array('title' => 'UCP_ZEBRA_FRIENDS', 'auth' => '', 'cat' => array('UCP_ZEBRA')),
				'foes'			=> array('title' => 'UCP_ZEBRA_FOES', 'auth' => '', 'cat' => array('UCP_ZEBRA')),
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
