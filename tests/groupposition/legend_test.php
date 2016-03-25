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


class phpbb_groupposition_legend_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/legend.xml');
	}

	public function get_group_value_data()
	{
		return array(
			array(1, 0, ''),
			array(3, 2, ''),
			array(4, 0, '\phpbb\groupposition\exception'),
		);
	}

	/**
	* @dataProvider get_group_value_data
	*/
	public function test_get_group_value($group_id, $expected, $throws_exception)
	{
		global $cache, $phpbb_root_path, $phpEx;

		$cache = new phpbb_mock_cache;
		$db = $this->new_dbal();
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$user->lang = array();

		if ($throws_exception)
		{
			$this->setExpectedException($throws_exception);
		}

		$test_class = new \phpbb\groupposition\legend($db, $user);
		$this->assertEquals($expected, $test_class->get_group_value($group_id));
	}

	public function test_get_group_count()
	{
		global $cache, $phpbb_root_path, $phpEx;

		$cache = new phpbb_mock_cache;
		$db = $this->new_dbal();
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$user->lang = array();

		$test_class = new \phpbb\groupposition\legend($db, $user);
		$this->assertEquals(2, $test_class->get_group_count());
	}

	public function add_group_data()
	{
		return array(
			array(
				1,
				true,
				array(
					array('group_id' => 1, 'group_legend' => 3),
					array('group_id' => 2, 'group_legend' => 1),
					array('group_id' => 3, 'group_legend' => 2),
				),
			),
			array(
				2,
				false,
				array(
					array('group_id' => 1, 'group_legend' => 0),
					array('group_id' => 2, 'group_legend' => 1),
					array('group_id' => 3, 'group_legend' => 2),
				),
			),
		);
	}

	/**
	* @dataProvider add_group_data
	*/
	public function test_add_group($group_id, $expected_added, $expected)
	{
		global $cache, $phpbb_root_path, $phpEx;

		$cache = new phpbb_mock_cache;
		$db = $this->new_dbal();
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$user->lang = array();

		$test_class = new \phpbb\groupposition\legend($db, $user);
		$this->assertEquals($expected_added, $test_class->add_group($group_id));

		$result = $db->sql_query('SELECT group_id, group_legend
			FROM ' . GROUPS_TABLE . '
			ORDER BY group_id ASC');

		$this->assertEquals($expected, $db->sql_fetchrowset($result));
	}

	public function delete_group_data()
	{
		return array(
			array(
				1,
				false,
				false,
				array(
					array('group_id' => 1, 'group_legend' => 0),
					array('group_id' => 2, 'group_legend' => 1),
					array('group_id' => 3, 'group_legend' => 2),
				),
			),
			array(
				2,
				false,
				true,
				array(
					array('group_id' => 1, 'group_legend' => 0),
					array('group_id' => 2, 'group_legend' => 0),
					array('group_id' => 3, 'group_legend' => 1),
				),
			),
			array(
				3,
				false,
				true,
				array(
					array('group_id' => 1, 'group_legend' => 0),
					array('group_id' => 2, 'group_legend' => 1),
					array('group_id' => 3, 'group_legend' => 0),
				),
			),
			array(
				1,
				true,
				false,
				array(
					array('group_id' => 1, 'group_legend' => 0),
					array('group_id' => 2, 'group_legend' => 1),
					array('group_id' => 3, 'group_legend' => 2),
				),
			),
			array(
				2,
				true,
				true,
				array(
					array('group_id' => 1, 'group_legend' => 0),
					array('group_id' => 2, 'group_legend' => 1),
					array('group_id' => 3, 'group_legend' => 1),
				),
			),
			array(
				3,
				true,
				true,
				array(
					array('group_id' => 1, 'group_legend' => 0),
					array('group_id' => 2, 'group_legend' => 1),
					array('group_id' => 3, 'group_legend' => 2),
				),
			),
		);
	}

	/**
	* @dataProvider delete_group_data
	*/
	public function test_delete_group($group_id, $skip_group, $expected_deleted, $expected)
	{
		global $cache, $phpbb_root_path, $phpEx;

		$cache = new phpbb_mock_cache;
		$db = $this->new_dbal();
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$user->lang = array();

		$test_class = new \phpbb\groupposition\legend($db, $user);
		$this->assertEquals($expected_deleted, $test_class->delete_group($group_id, $skip_group));

		$result = $db->sql_query('SELECT group_id, group_legend
			FROM ' . GROUPS_TABLE . '
			ORDER BY group_id ASC');

		$this->assertEquals($expected, $db->sql_fetchrowset($result));
	}

	public function move_up_data()
	{
		return array(
			array(
				1,
				false,
				array(
					array('group_id' => 1, 'group_legend' => 0),
					array('group_id' => 2, 'group_legend' => 1),
					array('group_id' => 3, 'group_legend' => 2),
				),
			),
			array(
				2,
				false,
				array(
					array('group_id' => 1, 'group_legend' => 0),
					array('group_id' => 2, 'group_legend' => 1),
					array('group_id' => 3, 'group_legend' => 2),
				),
			),
			array(
				3,
				true,
				array(
					array('group_id' => 1, 'group_legend' => 0),
					array('group_id' => 2, 'group_legend' => 2),
					array('group_id' => 3, 'group_legend' => 1),
				),
			),
		);
	}

	/**
	* @dataProvider move_up_data
	*/
	public function test_move_up($group_id, $excepted_moved, $expected)
	{
		global $cache, $phpbb_root_path, $phpEx;

		$cache = new phpbb_mock_cache;
		$db = $this->new_dbal();
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$user->lang = array();

		$test_class = new \phpbb\groupposition\legend($db, $user);
		$this->assertEquals($excepted_moved, $test_class->move_up($group_id));

		$result = $db->sql_query('SELECT group_id, group_legend
			FROM ' . GROUPS_TABLE . '
			ORDER BY group_id ASC');

		$this->assertEquals($expected, $db->sql_fetchrowset($result));
	}

	public function move_down_data()
	{
		return array(
			array(
				1,
				false,
				array(
					array('group_id' => 1, 'group_legend' => 0),
					array('group_id' => 2, 'group_legend' => 1),
					array('group_id' => 3, 'group_legend' => 2),
				),
			),
			array(
				2,
				true,
				array(
					array('group_id' => 1, 'group_legend' => 0),
					array('group_id' => 2, 'group_legend' => 2),
					array('group_id' => 3, 'group_legend' => 1),
				),
			),
			array(
				3,
				false,
				array(
					array('group_id' => 1, 'group_legend' => 0),
					array('group_id' => 2, 'group_legend' => 1),
					array('group_id' => 3, 'group_legend' => 2),
				),
			),
		);
	}

	/**
	* @dataProvider move_down_data
	*/
	public function test_move_down($group_id, $excepted_moved, $expected)
	{
		global $cache, $phpbb_root_path, $phpEx;

		$cache = new phpbb_mock_cache;
		$db = $this->new_dbal();
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$user->lang = array();

		$test_class = new \phpbb\groupposition\legend($db, $user);
		$this->assertEquals($excepted_moved, $test_class->move_down($group_id));

		$result = $db->sql_query('SELECT group_id, group_legend
			FROM ' . GROUPS_TABLE . '
			ORDER BY group_id ASC');

		$this->assertEquals($expected, $db->sql_fetchrowset($result));
	}

	public function move_data()
	{
		return array(
			array(
				1,
				1,
				false,
				array(
					array('group_id' => 1, 'group_legend' => 0),
					array('group_id' => 2, 'group_legend' => 1),
					array('group_id' => 3, 'group_legend' => 2),
				),
			),
			array(
				1,
				-1,
				false,
				array(
					array('group_id' => 1, 'group_legend' => 0),
					array('group_id' => 2, 'group_legend' => 1),
					array('group_id' => 3, 'group_legend' => 2),
				),
			),
			array(
				3,
				3,
				true,
				array(
					array('group_id' => 1, 'group_legend' => 0),
					array('group_id' => 2, 'group_legend' => 2),
					array('group_id' => 3, 'group_legend' => 1),
				),
			),
			array(
				2,
				0,
					false,
				array(
					array('group_id' => 1, 'group_legend' => 0),
					array('group_id' => 2, 'group_legend' => 1),
					array('group_id' => 3, 'group_legend' => 2),
				),
			),
			array(
				2,
				-1,
				true,
				array(
					array('group_id' => 1, 'group_legend' => 0),
					array('group_id' => 2, 'group_legend' => 2),
					array('group_id' => 3, 'group_legend' => 1),
				),
			),
			array(
				2,
				-3,
				true,
				array(
					array('group_id' => 1, 'group_legend' => 0),
					array('group_id' => 2, 'group_legend' => 2),
					array('group_id' => 3, 'group_legend' => 1),
				),
			),
			array(
				3,
				-1,
				false,
				array(
					array('group_id' => 1, 'group_legend' => 0),
					array('group_id' => 2, 'group_legend' => 1),
					array('group_id' => 3, 'group_legend' => 2),
				),
			),
		);
	}

	/**
	* @dataProvider move_data
	*/
	public function test_move($group_id, $increment, $excepted_moved, $expected)
	{
		global $cache, $phpbb_root_path, $phpEx;

		$cache = new phpbb_mock_cache;
		$db = $this->new_dbal();
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$user->lang = array();

		$test_class = new \phpbb\groupposition\legend($db, $user);
		$this->assertEquals($excepted_moved, $test_class->move($group_id, $increment));

		$result = $db->sql_query('SELECT group_id, group_legend
			FROM ' . GROUPS_TABLE . '
			ORDER BY group_id ASC');

		$this->assertEquals($expected, $db->sql_fetchrowset($result));
	}
}

