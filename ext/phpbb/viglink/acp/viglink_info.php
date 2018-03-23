<?php
/**
 *
 * VigLink extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\viglink\acp;

/**
 * VigLink ACP module info
 */
class viglink_info
{
	public function module()
	{
		return array(
			'filename'	=> '\phpbb\viglink\acp\viglink_module',
			'title'		=> 'ACP_VIGLINK_SETTINGS',
			'modes'		=> array(
				'settings'	=> array(
					'title' => 'ACP_VIGLINK_SETTINGS',
					'auth' => 'ext_phpbb/viglink && acl_a_board',
					'cat' => array('ACP_BOARD_CONFIGURATION')
				),
			),
		);
	}
}
