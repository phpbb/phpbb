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
		$this->session_factory = new phpbb_session_testable_factory;
		$this->db = $this->new_dbal();
		$this->session_facade =
			new phpbb_session_testable_facade($this->db, $this->session_factory);
	}

	protected function check_sessions_equals($expected_sessions, $message)
	{
		$session = new phpbb_session();
		if (is_a($this->db_session, 'phpbb_session_storage_native'))
		{
			$sql = 'SELECT session_id, session_user_id
					FROM phpbb_sessions
					ORDER BY session_user_id';

			$this->assertSqlResultEquals($expected_sessions, $sql, $message);
		}
		else
		{
			$user_ids = array();
			$sessions =  $session->db_session->map_all(
				function ($session) use ($user_ids)
				{
					$user_id = $session['session_user_id'];
					if (!in_array($user_id, $user_ids))
					{
						$user_ids[] = $user_id;
						return array($session['session_id'], $user_id);
					}
					return null;
				}
			);
			$sessions = array_filter($sessions, 'is_array');
			$this->assert_array_content_equals($expected_sessions, $sessions, $message);
		}
	}
}
