<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

class phpbb_dbal_migration_dummy extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('installed_migration');
	}

	function update_schema()
	{
		return array(
			'add_columns' => array(
				'phpbb_config' => array(
					'extra_column' => array('UINT', 1),
				),
			),
		);
	}
}
