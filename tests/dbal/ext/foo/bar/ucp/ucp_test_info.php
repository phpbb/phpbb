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

namespace foo\bar\ucp;

class ucp_test_info
{
	public function module()
	{
		return array(
			'filename'	=> '\foo\bar\ucp\ucp_test_module',
			'title'		=> 'UCP_NEW_MODULE',
			'modes'		=> array(
				'mode_1' => array(
					'title'	=> 'UCP_NEW_MODULE_MODE_1',
					'auth'	=> '',
					'cat'	=> array('UCP_NEW_MODULE'),
				),
				'mode_2' => array(
					'title'	=> 'UCP_NEW_MODULE_MODE_2',
					'auth'	=> '',
					'cat'	=> array('UCP_NEW_MODULE'),
				),
			),
		);
	}
}
