<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

namespace phpbb\db\migration\datax;

class 3_0_1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return version_compare($this->config['version'], '3.0.1', '>=');
	}

	static public function depends_on()
	{
		return array('phpbb_db_migration_data_30x_3_0_1_rc1');
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.0.1')),
		);
	}
}
