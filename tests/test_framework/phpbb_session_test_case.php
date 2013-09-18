<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

require_once dirname(__FILE__) . '/../session/testable_factory.php';
require_once dirname(__FILE__) . '/../session/testable_facade.php';

abstract class phpbb_session_test_case extends phpbb_database_test_case
{
	protected $session_factory;
	protected $session_facade;
	protected $db;

	function setUp()
	{
		parent::setUp();

		global $symfony_request, $phpbb_filesystem, $request, $phpbb_root_path, $phpEx;
		$symfony_request = new phpbb_symfony_request(
			new phpbb_mock_request()
		);
		$phpbb_filesystem = new phpbb_filesystem($symfony_request, $phpbb_root_path, $phpEx);

		$this->session_factory = new phpbb_session_testable_factory;
		$this->db = $this->new_dbal();
		$this->session_facade =
			new phpbb_session_testable_facade($this->db, $this->session_factory);
	}

	protected function check_sessions_equals($expected_sessions, $message)
	{
		$sql = 'SELECT session_id, session_user_id
				FROM phpbb_sessions
				ORDER BY session_user_id';

		$this->assertSqlResultEquals($expected_sessions, $sql, $message);
	}
}
