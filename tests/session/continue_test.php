<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/testable_factory.php';

class phpbb_session_continue_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/sessions_full.xml');
	}

	static public function session_begin_attempts()
	{
		// The session_id field is defined as CHAR(32) in the database schema.
		// Thus the data we put in session_id fields has to have a length of 32 characters on stricter DBMSes.
		// Thus we fill those strings up with zeroes until they have a string length of 32.

		return array(
			array(
				'bar_session000000000000000000000', '4', 'user agent', '127.0.0.1',
				array(
					array('session_id' => 'anon_session00000000000000000000', 'session_user_id' => 1),
					array('session_id' => 'bar_session000000000000000000000', 'session_user_id' => 4),
				),
				array(),
				'If a request comes with a valid session id with matching user agent and IP, no new session should be created.',
			),
			array(
				'anon_session00000000000000000000', '4', 'user agent', '127.0.0.1',
				array(
					array('session_id' => '__new_session_id__', 'session_user_id' => 1), // use generated SID
					array('session_id' => 'bar_session000000000000000000000', 'session_user_id' => 4),
				),
				array(
					'u' => array('1', null),
					'k' => array(null, null),
					'sid' => array('__new_session_id__', null),
				),
				'If a request comes with a valid session id and IP but different user id and user agent, a new anonymous session is created and the session matching the supplied session id is deleted.',
			),
		);
	}

	/**
	* @dataProvider session_begin_attempts
	*/
	public function test_session_begin_valid_session($session_id, $user_id, $user_agent, $ip, $expected_sessions, $expected_cookies, $message)
	{
		global $phpbb_container, $phpbb_root_path, $phpEx;

		$db = $this->new_dbal();
		$config = new \phpbb\config\config(array());
		$request = $this->getMock('\phpbb\request\request');
		$user = $this->getMock('\phpbb\user');

		$auth_provider = new \phpbb\auth\provider\db($db, $config, $request, $user, $phpbb_root_path, $phpEx);
		$phpbb_container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
		$phpbb_container->expects($this->any())
			->method('get')
			->with('auth.provider.db')
			->will($this->returnValue($auth_provider));

		$session_factory = new phpbb_session_testable_factory;
		$session_factory->set_cookies(array(
			'_sid' => $session_id,
			'_u' => $user_id,
		));
		$session_factory->merge_config_data(array(
			'session_length' => time(), // need to do this to allow sessions started at time 0
		));
		$session_factory->merge_server_data(array(
			'HTTP_USER_AGENT' => $user_agent,
			'REMOTE_ADDR' => $ip,
		));

		$session = $session_factory->get_session($db);
		$session->page = array('page' => 'page', 'forum' => 0);

		$session->session_begin();

		$sql = 'SELECT session_id, session_user_id
			FROM phpbb_sessions
			ORDER BY session_user_id';

		$expected_sessions = $this->replace_session($expected_sessions, $session->session_id);
		$expected_cookies = $this->replace_session($expected_cookies, $session->session_id);

		$this->assertSqlResultEquals(
			$expected_sessions,
			$sql,
			$message
		);

		$session->check_cookies($this, $expected_cookies);

		$session_factory->check($this);
	}

	/**
	* Replaces recursively the value __new_session_id__ with the given session
	* id.
	*
	* @param array $array An array of data
	* @param string $session_id The new session id to use instead of the
	*                           placeholder.
	* @return array The input array with all occurances of __new_session_id__
	*               replaced.
	*/
	public function replace_session($array, $session_id)
	{
		foreach ($array as $key => &$value)
		{
			if ($value === '__new_session_id__')
			{
				$value = $session_id;
			}

			if (is_array($value))
			{
				$value = $this->replace_session($value, $session_id);
			}
		}

		return $array;
	}
}
