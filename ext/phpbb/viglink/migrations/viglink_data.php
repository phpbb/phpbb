<?php
/**
*
* VigLink extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\viglink\migrations;

/**
 * Migration to install VigLink data
 */
class viglink_data extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v312');
	}

	public function effectively_installed()
	{
		return isset($this->config['phpbb_viglink_api_key']);
	}

	public function update_data()
	{
		return array(
			// Basic config options
			array('config.add', array('viglink_enabled', 0)),
			array('config.add', array('viglink_api_key', '')),

			// Special config options for phpBB use
			array('config.add', array('allow_viglink_phpbb', 1)),
			array('config.add', array('allow_viglink_global', 1)),
			array('config.add', array('phpbb_viglink_api_key', 'e4fd14f5d7f2bb6d80b8f8da1354718c')),
			array('config.add', array('viglink_convert_account_url', '')),
			array('config.add', array('viglink_api_siteid', md5($this->config['server_name']))),

			// Add the ACP module to Board Configuration
			array('module.add', array(
				'acp',
				'ACP_BOARD_CONFIGURATION',
				array(
					'module_basename'	=> '\phpbb\viglink\acp\viglink_module',
					'modes'				=> array('settings'),
				),
			)),
		);
	}
}
