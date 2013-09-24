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
	protected $session;

	function setUp()
	{
		parent::setUp();
		$this->session_factory = new phpbb_session_testable_factory;
		$this->db = $this->new_dbal();
		$this->session_facade =
			new phpbb_session_testable_facade($this->db, $this->session_factory);
		$this->session = $this->session_factory->get_session($this->db);
		$this->session->db_synchronize(true);
	}

	protected function check_sessions_equals($expected_sessions, $message)
	{
		$allowed_keys = array('session_id', 'sessions_user_id');
		$sessions = $this->session->db_session->map_all(
			function ($session) use ($allowed_keys) {
				return array_intersect_key($session, array_flip($allowed_keys));
			}
		);
		$this->assert_array_content_equals($expected_sessions, $sessions, $message);
	}
}
