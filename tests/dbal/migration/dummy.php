<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

class phpbb_dbal_migration_dummy extends phpbb_db_migration
{
	function depends_on()
	{
		return array('installed_migration');
	}

	function update_schema()
	{
		return array(
			'add_columns' => array(
				'phpbb_config' => array(
					'extra_column' => array('UINT', 0),
				),
			),
		);
	}

	function update_data()
	{
		return array(
			array('if', array(true, array('custom', array(array($this, 'set_extra_column'))))),
		);
	}

	public function set_extra_column()
	{
		$this->sql_query('UPDATE phpbb_config SET extra_column = 1');
	}
}
