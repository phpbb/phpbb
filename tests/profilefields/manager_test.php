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
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\config\db_text */
	protected $config_text;

	/** @var \phpbb\profilefields\manager */
	protected $manager;

	/** @var \phpbb\log\log_interface */
	protected $log;

	/** @var \phpbb\db\tools */
	protected $db_tools;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/manager.xml');
	}

	public function setUp()
	{
		parent::setUp();

		global $phpbb_log;

		$this->log = $this->prophesize('\phpbb\log\log_interface');
		$phpbb_log = $this->log->reveal();

		$this->db = $this->new_dbal();
		$this->config_text = $this->prophesize('\phpbb\config\db_text');
		$this->db_tools = $this->prophesize('\phpbb\db\tools');

		$this->manager = new \phpbb\profilefields\manager(
			$this->prophesize('phpbb\auth\auth')->reveal(),
			$this->db,
			$this->db_tools->reveal(),
			$this->prophesize('\phpbb\event\dispatcher_interface')->reveal(),
			$this->prophesize('\phpbb\request\request')->reveal(),
			$this->prophesize('\phpbb\template\template')->reveal(),
			$this->prophesize('\phpbb\di\service_collection')->reveal(),
			$this->prophesize('\phpbb\user')->reveal(),
			$this->config_text->reveal(),
			PROFILE_FIELDS_TABLE,
			PROFILE_FIELDS_LANG_TABLE,
			PROFILE_FIELDS_DATA_TABLE
		);
	}

	public function test_disable_profilefields()
	{
		$this->log->add('admin', 'LOG_PROFILE_FIELD_DEACTIVATE', 'pf_1')->shouldBeCalled();
		$this->log->add('admin', 'LOG_PROFILE_FIELD_DEACTIVATE', 'pf_2')->shouldBeCalled();

		$this->config_text->set('foo_bar_type.saved', json_encode([1 => 'pf_1', 2 => 'pf_2']))->shouldBeCalled();

		$this->manager->disable_profilefields('foo_bar_type');

		$sql = 'SELECT field_id, field_ident
			FROM ' . PROFILE_FIELDS_TABLE . "
			WHERE field_active = 1
				AND field_type = 'foo_bar_type'";

		$this->assertSqlResultEquals(false, $sql, 'All profile fields should be disabled');
	}

	public function test_enable_profilefields()
	{
		$this->log->add('admin', 'LOG_PROFILE_FIELD_ACTIVATE', 'pf_1')->shouldBeCalled();
		$this->log->add('admin', 'LOG_PROFILE_FIELD_ACTIVATE', 'pf_2')->shouldBeCalled();

		$this->config_text->get('foo_bar_type.saved')->willReturn(json_encode([1 => 'pf_1', 2 => 'pf_2']));
		$this->config_text->delete('foo_bar_type.saved')->shouldBeCalled();

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
		$this->log->add('admin', 'LOG_PROFILE_FIELD_REMOVED', 'pf_1')->shouldBeCalled();
		$this->log->add('admin', 'LOG_PROFILE_FIELD_REMOVED', 'pf_2')->shouldBeCalled();

		$this->config_text->delete('foo_bar_type.saved')->shouldBeCalled();

		$this->db_tools->sql_column_remove(PROFILE_FIELDS_DATA_TABLE, 'pf_pf_1')->shouldBeCalled();
		$this->db_tools->sql_column_remove(PROFILE_FIELDS_DATA_TABLE, 'pf_pf_2')->shouldBeCalled();

		$this->manager->enable_profilefields('foo_bar_type');

		$sql = 'SELECT field_id
			FROM ' . PROFILE_FIELDS_TABLE . "
			WHERE field_type = 'foo_bar_type'";

		$this->assertSqlResultEquals(false, $sql, 'All profile fields should be removed');

		$sql = 'SELECT field_id
			FROM ' . PROFILE_FIELDS_LANG_TABLE . "
			WHERE field_type = 'foo_bar_type'";

		$this->assertSqlResultEquals(false, $sql, 'All profile fields lang should be removed');

		$sql = 'SELECT field_id
			FROM ' . PROFILE_LANG_TABLE . "
			WHERE field_type = 'foo_bar_type'";

		$this->assertSqlResultEquals(false, $sql, 'All profile fields lang should be removed');

		$sql = 'SELECT field_id, field_order FROM ' . PROFILE_FIELDS_TABLE;

		$this->assertSqlResultEquals([['field_id' => '3', 'field_order' => '1'],], $sql, 'Profile fields order should be recalculated, starting by 1');
	}
}
