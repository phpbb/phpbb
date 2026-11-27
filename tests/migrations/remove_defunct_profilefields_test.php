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

use phpbb\db\migration\data\v33x\remove_defunct_profilefields;

class phpbb_migrations_remove_defunct_profilefields_test extends phpbb_database_test_case
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var remove_defunct_profilefields */
	protected $migration;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/remove_defunct_profilefields.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		$this->db = $this->new_dbal();
		$factory = new \phpbb\db\tools\factory();
		$db_tools = $factory->get($this->db);

		$this->migration = new remove_defunct_profilefields(
			new \phpbb\config\config([]),
			$this->db,
			$db_tools,
			$phpbb_root_path,
			$phpEx,
			'phpbb_'
		);
	}

	public function test_delete_and_recreate_custom_profile_field_data()
	{
		$this->migration->delete_custom_profile_field_data();

		$sql = 'SELECT field_name
			FROM ' . PROFILE_FIELDS_TABLE . '
			ORDER BY field_id';
		$result = $this->db->sql_query($sql);
		$this->assertEquals([['field_name' => 'phpbb_youtube']], $this->db->sql_fetchrowset($result));
		$this->db->sql_freeresult($result);

		$sql = 'SELECT COUNT(field_id) AS num_rows
			FROM ' . PROFILE_LANG_TABLE . '
			WHERE field_id <> 4';
		$result = $this->db->sql_query($sql);
		$this->assertEquals(0, (int) $this->db->sql_fetchfield('num_rows'));
		$this->db->sql_freeresult($result);

		$sql = 'SELECT COUNT(field_id) AS num_rows
			FROM ' . PROFILE_FIELDS_LANG_TABLE;
		$result = $this->db->sql_query($sql);
		$this->assertEquals(0, (int) $this->db->sql_fetchfield('num_rows'));
		$this->db->sql_freeresult($result);

		// Deleting again must not fail when the fields are already gone
		$this->migration->delete_custom_profile_field_data();

		// Reverting recreates the fields with their language rows
		$this->migration->create_custom_fields();

		$sql = 'SELECT field_name, field_is_contact
			FROM ' . PROFILE_FIELDS_TABLE . '
			ORDER BY field_order';
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$this->assertEquals(
			['phpbb_youtube', 'phpbb_icq', 'phpbb_yahoo', 'phpbb_skype'],
			array_column($rows, 'field_name')
		);

		$sql = 'SELECT lang_name
			FROM ' . PROFILE_LANG_TABLE . '
			WHERE field_id <> 4
			ORDER BY field_id';
		$result = $this->db->sql_query($sql);
		$this->assertEquals(['ICQ', 'YAHOO', 'SKYPE'], array_column($this->db->sql_fetchrowset($result), 'lang_name'));
		$this->db->sql_freeresult($result);
	}
}
