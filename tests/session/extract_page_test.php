<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

require_once dirname(__FILE__) . '/testable_facade.php';

class phpbb_session_extract_page_test extends phpbb_database_test_case
{
	public $session_factory;
	public $db;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/sessions_empty.xml');
	}

	public function setUp()
	{
		$this->session_factory = new phpbb_session_testable_factory;
		$this->db = $this->new_dbal();
	}

	function test_extract_current_page()
	{
		$expected_fields = array(
			'page_name' => "index.php",
			'script_path' => "/phpBB/"
		);

		$output = phpbb_session_testable_facade::extract_current_page(
			$this->db,
			$this->session_factory,
			/* Root Path   */ "./",
			/* PHP Self    */ "/phpBB/index.php",
			/* Query String*/ "",
			/* Request URI */ "/phpBB/"
		);

		foreach($expected_fields as $field => $expected_value)
		{
			$this->assertSame($expected_value, $output[$field]);
		}
	}
}
