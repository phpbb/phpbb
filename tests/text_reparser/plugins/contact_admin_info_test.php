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
require_once __DIR__ . '/../../../phpBB/includes/functions.php';
require_once __DIR__ . '/../../../phpBB/includes/functions_content.php';
require_once __DIR__ . '/../../test_framework/phpbb_database_test_case.php';

class phpbb_textreparser_contact_admin_info_test extends phpbb_database_test_case
{
	protected $db;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/contact_admin_info.xml');
	}

	protected function get_reparser()
	{
		return new \phpbb\textreparser\plugins\contact_admin_info(new \phpbb\config\db_text($this->db, CONFIG_TEXT_TABLE));
	}

	protected function get_rows()
	{
		$sql = 'SELECT config_name, config_value
			FROM ' . CONFIG_TEXT_TABLE . '
			ORDER BY config_name';
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	public function setUp()
	{
		global $config;
		if (!isset($config))
		{
			$config = new \phpbb\config\config(array());
		}
		$this->get_test_case_helpers()->set_s9e_services();
		$this->db = $this->new_dbal();
		parent::setUp();
	}

	public function test_get_max_id()
	{
		$reparser = $this->get_reparser();
		$this->assertEquals(1, $reparser->get_max_id());
	}

	public function test_dry_run()
	{
		$old_rows = $this->get_rows();
		$reparser = $this->get_reparser();
		$reparser->disable_save();
		$reparser->reparse_range(1, 1);
		$new_rows = $this->get_rows();
		$this->assertEquals($old_rows, $new_rows);
	}

	public function test_reparse()
	{
		$reparser = $this->get_reparser();
		$reparser->enable_save();
		$reparser->reparse_range(1, 1);
		$expected = array(
			array(
				'config_name'  => 'contact_admin_info',
				'config_value' => '<r><EMAIL email="admin@example.org"><s>[email]</s>admin@example.org<e>[/email]</e></EMAIL></r>',
			),
			array(
				'config_name'  => 'contact_admin_info_bitfield',
				'config_value' => 'ACA=',
			),
			array(
				'config_name'  => 'contact_admin_info_flags',
				'config_value' => '7',
			),
			array(
				'config_name'  => 'contact_admin_info_uid',
				'config_value' => '1a2hbwf5',
			),
		);
		$this->assertEquals($expected, $this->get_rows());
	}
}
