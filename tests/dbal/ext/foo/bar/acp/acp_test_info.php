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

namespace foo\bar\acp;

class acp_test_info
{
	public function module()
	{
		return array(
			'filename'	=> '\foo\bar\acp\acp_test_module',
			'title'		=> 'ACP_NEW_MODULE',
			'modes'		=> array(
				'mode_1' => array(
					'title'	=> 'ACP_NEW_MODULE_MODE_1',
					'auth'	=> '',
					'cat'	=> array('ACP_NEW_MODULE'),
				),
				'mode_2' => array(
					'title'	=> 'ACP_NEW_MODULE_MODE_2',
					'auth'	=> '',
					'cat'	=> array('ACP_NEW_MODULE'),
				),
			),
		);
	}
}
