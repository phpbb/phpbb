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

namespace phpbb\db\migration\data\v31x;

class v3111rc1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['version'], '3.1.11-RC1', '>=');
	}

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v31x\v3110',
			'\phpbb\db\migration\data\v31x\add_log_time_index',
			'\phpbb\db\migration\data\v31x\increase_size_of_emotion',
			'\phpbb\db\migration\data\v31x\add_jabber_ssl_context_config_options',
			'\phpbb\db\migration\data\v31x\add_smtp_ssl_context_config_options',
			'\phpbb\db\migration\data\v31x\update_hashes',
			'\phpbb\db\migration\data\v31x\remove_duplicate_migrations',
			'\phpbb\db\migration\data\v31x\add_latest_topics_index',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.1.11-RC1')),
		);
	}
}
