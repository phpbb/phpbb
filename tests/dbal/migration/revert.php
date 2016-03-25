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

class phpbb_dbal_migration_revert extends \phpbb\db\migration\migration
{
	function update_schema()
	{
		return array(
			'add_columns' => array(
				'phpbb_config' => array(
					'bar_column' => array('UINT', 1),
				),
			),
		);
	}

	function revert_schema()
	{
		return array(
			'drop_columns' => array(
				'phpbb_config' => array(
					'bar_column',
				),
			),
		);
	}

	function update_data()
	{
		return array(
			array('config.add', array('foobartest', 0)),
			array('custom', array(array(&$this, 'my_custom_function'))),
		);
	}

	function my_custom_function()
	{
		global $migrator_test_revert_counter;

		$migrator_test_revert_counter += 1;
	}
}
