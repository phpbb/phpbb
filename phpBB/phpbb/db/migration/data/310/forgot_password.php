<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

namespace phpbb\db\migration\data0;

class forgot_password extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['allow_password_reset']);
	}

	static public function depends_on()
	{
		return array('phpbb_db_migration_data_30x_3_0_11');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('allow_password_reset', 1)),
		);
	}
}
