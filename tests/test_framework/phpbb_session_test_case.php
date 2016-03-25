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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../session/testable_factory.php';
require_once dirname(__FILE__) . '/../session/testable_facade.php';

abstract class phpbb_session_test_case extends phpbb_database_test_case
{
	/** @var phpbb_session_testable_factory */
	protected $session_factory;

	/** @var phpbb_session_testable_facade */
	protected $session_facade;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	function setUp()
	{
		parent::setUp();

		global $symfony_request, $phpbb_filesystem, $phpbb_path_helper, $request, $phpbb_root_path, $phpEx;
		$symfony_request = new \phpbb\symfony_request(
			new phpbb_mock_request()
		);
		$phpbb_filesystem = new \phpbb\filesystem\filesystem();
		$phpbb_path_helper = new \phpbb\path_helper(
			$symfony_request,
			$phpbb_filesystem,
			$this->getMock('\phpbb\request\request'),
			$phpbb_root_path,
			$phpEx
		);

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
