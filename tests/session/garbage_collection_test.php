<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

require_once dirname(__FILE__) . '/../test_framework/phpbb_session_test_case.php';

class phpbb_session_garbage_collection_test extends phpbb_session_test_case
{
	public $session;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/sessions_garbage.xml');
	}

	public function setUp()
	{
		parent::setUp();
		$this->session = $this->session_factory->get_session($this->db);
	}

	protected function assert_sessions_equal($expected, $msg)
	{
		$sql = 'SELECT session_id, session_user_id
				FROM phpbb_sessions
				ORDER BY session_user_id';

		$this->assertSqlResultEquals($expected, $sql, $msg);
	}

	public function test_cleanup_all()
	{
		$this->assert_sessions_equal(
			array(
				array
				(
					'session_id' => 'anon_session00000000000000000000',
					'session_user_id' => 1,
				),
				array
				(
					'session_id' => 'bar_session000000000000000000000',
					'session_user_id' => 4,
				)),
			'Before test, should have some sessions.'
		);
		// Set session length so it clears all
		global $config;
		$config['session_length'] = 0;
		// There is an error unless the captcha plugin is set
		$config['captcha_plugin'] = 'phpbb_captcha_nogd';
		$this->session->session_gc();
		$this->assert_sessions_equal(
			array(),
			'After setting session time to 0, should remove all.'
		);
	}
}
