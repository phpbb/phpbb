<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

class phpbb_dbal_migration_revert extends \phpbb\db\migration\migration
{
	function update_schema()
	{
		return array(
			'add_columns' => array(
				'\phpbb\config\config' => array(
					'bar_column' => array('UINT', 1),
				),
			),
		);
	}

	function revert_schema()
	{
		return array(
			'drop_columns' => array(
				'\phpbb\config\config' => array(
					'bar_column',
				),
			),
		);
	}

	function update_data()
	{
		return array(
			array('config.add', array('foobartest', 0)),
		);
	}
}
