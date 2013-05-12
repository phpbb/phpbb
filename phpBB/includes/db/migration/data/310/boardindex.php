<?php
/**
*
* @package migration
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_data_310_boardindex extends phpbb_db_migration
{
	public function effectively_installed()
	{
		return isset($this->config['board_index_text']);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('board_index_text', '')),
		);
	}
}
