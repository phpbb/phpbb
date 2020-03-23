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

class manager_test extends phpbb_database_test_case
{
	/** @var \phpbb\config\db_text */
	protected $config_text;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\db\tools\tools */
	protected $db_tools;

	/** @var \phpbb\log\log_interface */
	protected $log;

	/** @var \phpbb\profilefields\manager */
	protected $manager;

	/** @var string Table prefix */
	protected $table_prefix;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/manager.xml');
	}

	public function setUp()
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx, $table_prefix;

		$factory = new \phpbb\db\tools\factory();

		$this->db			= $this->new_dbal();
		$this->db_tools		= $factory->get($this->db, true);
		$this->config_text	= new \phpbb\config\db_text($this->db, $table_prefix . 'config_text');
		$this->table_prefix	= $table_prefix;

		$container	= new phpbb_mock_container_builder();
		$dispatcher	= new phpbb_mock_event_dispatcher();

		$request	= $this->getMock('\phpbb\request\request');
		$template	= $this->getMock('\phpbb\template\template');

		$auth		= new \phpbb\auth\auth();
		$language	= new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));
		$collection = new \phpbb\di\service_collection($container);
		$user		= new \phpbb\user($language, '\phpbb\datetime');

		$this->log	= new \phpbb\log\log($this->db, $user, $auth, $dispatcher, $phpbb_root_path, 'adm/', $phpEx, $table_prefix . 'log');

		$this->manager = new \phpbb\profilefields\manager(
			$auth,
			$this->config_text,
			$this->db,
			$this->db_tools,
			$dispatcher,
			$language,
			$this->log,
			$request,
			$template,
			$collection,
			$user,
			$table_prefix . 'profile_fields',
			$table_prefix . 'profile_fields_data',
			$table_prefix . 'profile_fields_lang',
			$table_prefix . 'profile_lang'
		);
	}

	public function test_disable_profilefields()
	{
	#	$this->log->add('admin', 'LOG_PROFILE_FIELD_DEACTIVATE', 'pf_1')->shouldBeCalled();
	#	$this->log->add('admin', 'LOG_PROFILE_FIELD_DEACTIVATE', 'pf_2')->shouldBeCalled();

	#	$this->config_text->set('foo_bar_type.saved', json_encode([1 => 'pf_1', 2 => 'pf_2']));

		$this->manager->disable_profilefields('foo_bar_type');

		$sql = 'SELECT field_id, field_ident
			FROM ' . PROFILE_FIELDS_TABLE . "
			WHERE field_active = 1
				AND field_type = 'foo_bar_type'";

		$this->assertSqlResultEquals([], $sql, 'All profile fields should be disabled');
	}

	public function test_enable_profilefields()
	{
	#	$this->log->add('admin', 'LOG_PROFILE_FIELD_ACTIVATE', 'pf_1')->shouldBeCalled();
	#	$this->log->add('admin', 'LOG_PROFILE_FIELD_ACTIVATE', 'pf_2')->shouldBeCalled();

	#	$this->config_text->get('foo_bar_type.saved')->willReturn(json_encode([1 => 'pf_1', 2 => 'pf_2']));
	#	$this->config_text->delete('foo_bar_type.saved')->shouldBeCalled();

		$this->manager->enable_profilefields('foo_bar_type');

		$sql = 'SELECT field_id
			FROM ' . PROFILE_FIELDS_TABLE . "
			WHERE field_active = 1
				AND field_type = 'foo_bar_type'";

		$this->assertSqlResultEquals([
			['field_id' => '1'],
			['field_id' => '2'],
		], $sql, 'All profile fields should be enabled');
	}

	public function test_purge_profilefields()
	{
	#	$this->log->add('admin', 'LOG_PROFILE_FIELD_REMOVED', 'pf_1')->shouldBeCalled();
	#	$this->log->add('admin', 'LOG_PROFILE_FIELD_REMOVED', 'pf_2')->shouldBeCalled();

	#	$this->config_text->delete('foo_bar_type.saved')->shouldBeCalled();

	#	$this->db_tools->sql_column_remove(PROFILE_FIELDS_DATA_TABLE, 'pf_pf_1')->shouldBeCalled();
	#	$this->db_tools->sql_column_remove(PROFILE_FIELDS_DATA_TABLE, 'pf_pf_2')->shouldBeCalled();

		$sql = 'SELECT field_id
			FROM ' . PROFILE_FIELDS_TABLE . "
			WHERE field_type = 'foo_bar_type'";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$field_ids = array_map('intval', array_column($rowset, 'field_id'));

		$this->manager->purge_profilefields('foo_bar_type');

		$sql = 'SELECT field_id
			FROM ' . PROFILE_FIELDS_TABLE . "
			WHERE field_type = 'foo_bar_type'";

		$this->assertSqlResultEquals([], $sql, 'All profile fields should be removed');

		$sql = 'SELECT field_id
			FROM ' . PROFILE_FIELDS_LANG_TABLE . "
			WHERE field_type = 'foo_bar_type'";

		$this->assertSqlResultEquals([], $sql, 'All profile fields lang should be removed');

		$sql = 'SELECT lang_name
			FROM ' . PROFILE_LANG_TABLE . '
			WHERE ' . $this->db->sql_in_set('field_id', $field_ids);

		$this->assertSqlResultEquals([], $sql, 'All profile fields lang should be removed');

		$sql = 'SELECT field_id, field_order FROM ' . PROFILE_FIELDS_TABLE;

		$this->assertSqlResultEquals([['field_id' => '3', 'field_order' => '1'],], $sql, 'Profile fields order should be recalculated, starting by 1');
	}
}
