<?php
/**
*
* @package migration
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_data_310_jquery_update extends phpbb_db_migration
{
	public function effectively_installed()
	{
		return $this->config['load_jquery_url'] !== '//ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js';
	}

	static public function depends_on()
	{
		return array(
			'phpbb_db_migration_data_310_dev',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('load_jquery_url', '//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js')),
		);
	}

}
