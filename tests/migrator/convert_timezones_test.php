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

class phpbb_migrator_convert_timezones_test extends phpbb_database_test_case
{
	protected $db;
	protected $db_doctrine;

	public function getDataSet()
	{
		$this->db = $this->new_dbal();
		$this->db_doctrine = $this->new_doctrine_dbal();
		$factory = new \phpbb\db\tools\factory();
		$db_tools = $factory->get($this->db_doctrine);

		// user_dst doesn't exist anymore, must re-add it to test this
		$db_tools->sql_column_add('phpbb_users', 'user_dst', array('BOOL', 1));

		return $this->createXMLDataSet(__DIR__ . '/fixtures/convert_timezones.xml');
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'	=> array(
				$this->table_prefix . 'users'			=> array(
					'user_dst',
				),
			),
		);
	}

	public function update_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'users'			=> array(
					'user_dst'		=> array('BOOL', 0),
				),
			),
		);
	}

	protected function setUp(): void
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		$this->db = $this->new_dbal();
		$this->db_doctrine = $this->new_doctrine_dbal();
		$factory = new \phpbb\db\tools\factory();

		$this->migration = new \phpbb\db\migration\data\v310\timezone(
			new \phpbb\config\config(array()),
			$this->db,
			$factory->get($this->db_doctrine),
			$phpbb_root_path,
			$phpEx,
			'phpbb_',
			self::get_core_tables()
		);
	}

	protected $expected_results = array(
		//user_id => user_timezone
		1 => 'Etc/GMT+12',
		2 => 'Etc/GMT+11',
		3 => 'Etc/GMT-3',
		4 => 'Etc/GMT-4',
		5 => 'America/St_Johns',
		6 => 'Australia/Eucla',
	);

	public function test_convert()
	{
		$this->migration->update_timezones(0);

		$sql = 'SELECT user_id, user_timezone
			FROM phpbb_users
			ORDER BY user_id ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->assertEquals($this->expected_results[$row['user_id']], $row['user_timezone']);
		}
		$this->db->sql_freeresult($result);

		$factory = new \phpbb\db\tools\factory();
		$db_tools = $factory->get($this->db_doctrine);

		// Remove the user_dst field again
		$db_tools->sql_column_remove('phpbb_users', 'user_dst');
	}
}
