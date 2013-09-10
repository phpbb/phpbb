<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

namespace phpbb\db\migration\datax;

class 3_0_2_rc1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return version_compare($this->config['version'], '3.0.2-rc1', '>=');
	}

	static public function depends_on()
	{
		return array('phpbb_db_migration_data_30x_3_0_1');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('referer_validation', '1')),
			array('config.add', array('check_attachment_content', '1')),
			array('config.add', array('mime_triggers', 'body|head|html|img|plaintext|a href|pre|script|table|title')),

			array('config.update', array('version', '3.0.2-rc1')),
		);
	}
}
