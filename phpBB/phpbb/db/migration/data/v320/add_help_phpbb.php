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

namespace phpbb\db\migration\data\v320;

class add_help_phpbb extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v320\v320rc1',
		);
	}

	public function effectively_installed()
	{
		return isset($this->config['help_send_statistics']);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('help_send_statistics', true)),
			array('config.add', array('help_send_statistics_time', 0)),
			array('if', array(
				array('module.exists', array('acp', false, 'ACP_SEND_STATISTICS')),
				array('module.remove', array('acp', false, 'ACP_SEND_STATISTICS')),
			)),
			array('module.add', array(
				'acp',
				'ACP_SERVER_CONFIGURATION',
				array(
					'module_basename'	=> 'acp_help_phpbb',
					'module_langname'	=> 'ACP_HELP_PHPBB',
					'module_mode'		=> 'help_phpbb',
					'module_auth'		=> 'acl_a_server',
				),
			)),
		);
	}
}
