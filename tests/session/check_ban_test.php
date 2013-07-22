<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

require_once dirname(__FILE__) . '/../test_framework/phpbb_session_test_case.php';

class phpbb_session_check_ban_test extends phpbb_session_test_case
{
	protected $user_id = 4;
	protected $key_id = 4;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/sessions_banlist.xml');
	}

	static function check_banned_data()
	{
		return array(
		    array('All false values, should not be banned',
				 false, false, false, false, /* should be banned? -> */ false),
			array('Matching values in the database, should be banned',
				 4, '127.0.0.1', 'bar@example.org', true, /* should be banned? -> */ true),
			array('IP Banned, should be banned',
			     false, '127.1.1.1', false, false, /* should be banned? -> */ true),
		);
	}

	/** @dataProvider check_banned_data */
	public function test_check_is_banned($test_msg, $user_id, $user_ips, $user_email, $return, $should_be_banned)
	{
		$session = $this->session_factory->get_session($this->db);
		// Change the global cache object for this test because
		// the mock cache object does not hit the database as is
		// needed for this test.
		global $cache, $config, $phpbb_root_path, $php_ext;
		$cache = new phpbb_cache_service(
			new phpbb_cache_driver_file(),
			$config,
			$this->db,
			$phpbb_root_path,
			$php_ext
		);

		try
		{
			$is_banned = $session->check_ban($user_id, $user_ips, $user_email, $return);
		} catch (PHPUnit_Framework_Error_Notice $e)
		{
			// User error was triggered, user must have been banned
			$is_banned = true;
		}
		$this->assertEquals($should_be_banned, $is_banned, $test_msg);

		$cache = new phpbb_mock_cache();
	}
}
