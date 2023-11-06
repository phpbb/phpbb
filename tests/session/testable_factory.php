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

require_once __DIR__ . '/../mock/container_builder.php';
require_once __DIR__ . '/../mock/auth_provider.php';

/**
* This class exists to setup an instance of phpbb's session class for testing.
*
* The session class has rather complex dependencies, so in order to make its
* tests more * understandable and to make its dependencies more visible this
* factory class sets up all the necessary global state & variable contents.
*/
class phpbb_session_testable_factory
{
	protected $container;
	protected $config_data;
	protected $cache_data;
	protected $cookies;

	protected $config;
	protected $cache;
	protected $request;

	/**
	* Initialises the factory with a set of default config and cache values.
	*/
	public function __construct()
	{
		// default configuration values
		$this->config_data = [
			'allow_autologin' => false,
			'auth_method' => 'db',
			'forwarded_for_check' => true,
			'active_sessions' => 0, // disable
			'rand_seed' => 'foo',
			'rand_seed_last_update' => 0,
			'max_autologin_time' => 0,
			'session_length' => 100,
			'form_token_lifetime' => 100,
			'cookie_name' => '',
			'limit_load' => 0,
			'limit_search_load' => 0,
			'ip_check' => 3,
			'browser_check' => 1,
		];

		$this->cache_data = [
			'_bots'	=> [],
			'_ban_info'	=> [],
		];

		$this->cookies = [];

		$this->server_data = $_SERVER;
	}

	/**
	* Retrieve the configured session class instance
	*
	* @param \phpbb\db\driver\driver_interface $dbal The database connection to use for session data
	* @return phpbb_mock_session_testable A session instance
	*/
	public function get_session(\phpbb\db\driver\driver_interface $dbal)
	{
		// set up all the global variables used by session
		global $SID, $_SID, $db, $config, $cache, $request, $phpbb_container, $phpbb_dispatcher;
		global $user, $phpbb_root_path, $phpEx;

		$request = $this->request = new phpbb_mock_request(
			array(),
			array(),
			$this->cookies,
			$this->server_data
		);

		$config = $this->config = new \phpbb\config\config($this->get_config_data());

		$db = $dbal;

		$cache = $this->cache = new phpbb_mock_cache($this->get_cache_data());
		$SID = $_SID = null;
		$language = new phpbb\language\language(new phpbb\language\language_file_loader($phpbb_root_path, $phpEx));
		$user = new \phpbb\user($language, '\phpbb\datetime');

		$phpbb_container = $this->container = new phpbb_mock_container_builder();
		$phpbb_container->set(
			'auth.provider.db',
			new phpbb_mock_auth_provider()
		);
		$phpbb_container->setParameter('core.environment', PHPBB_ENVIRONMENT);
		$phpbb_container->setParameter('core.cache_dir', $phpbb_root_path . 'cache/' . PHPBB_ENVIRONMENT . '/');
		$provider_collection = new \phpbb\auth\provider_collection($phpbb_container, $config);
		$provider_collection->add('auth.provider.db');
		$phpbb_container->set(
			'auth.provider_collection',
			$provider_collection
		);

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();

		$ban_type_email = new \phpbb\ban\type\email($db, 'phpbb_bans', 'phpbb_users', 'phpbb_sessions', 'phpbb_sessions_keys');
		$ban_type_user = new \phpbb\ban\type\user($db, 'phpbb_bans', 'phpbb_users', 'phpbb_sessions', 'phpbb_sessions_keys');
		$ban_type_ip = new \phpbb\ban\type\ip($db, 'phpbb_bans', 'phpbb_users', 'phpbb_sessions', 'phpbb_sessions_keys');
		$phpbb_container->set('ban.type.email', $ban_type_email);
		$phpbb_container->set('ban.type.user', $ban_type_user);
		$phpbb_container->set('ban.type.ip', $ban_type_ip);

		$collection = new \phpbb\di\service_collection($phpbb_container);
		$collection->add('ban.type.email');
		$collection->add('ban.type.user');
		$collection->add('ban.type.ip');
		$phpbb_log = new \phpbb\log\dummy();

		$ban_manager = new \phpbb\ban\manager($collection, $cache, $db, $language, $phpbb_log, $user,'phpbb_bans', 'phpbb_users');
		$phpbb_container->set('ban.manager', $ban_manager);

		$session = new phpbb_mock_session_testable;
		return $session;
	}

	/**
	* Set the cookies which should be present in the request data.
	*
	* @param array $cookies The cookie data, structured like $_COOKIE contents.
	*/
	public function set_cookies(array $cookies)
	{
		$this->cookies = $cookies;
	}

	/**
	* Check if the cache used for the generated session contains correct data.
	*
	* @param PHPUnit\Framework\Assert $test The test case to call assert methods
	*                                       on
	*/
	public function check(PHPUnit\Framework\Assert $test)
	{
		$this->cache->check($test, $this->get_cache_data());
	}

	/**
	* Merge config data with the current config data to be supplied to session.
	*
	* New values overwrite new ones.
	*
	* @param array $config_data The config data to merge with previous data
	*/
	public function merge_config_data(array $config_data)
	{
		$this->config_data = array_merge($this->config_data, $config_data);
	}

	/**
	* Retrieve the entire config data to be passed to the session.
	*
	* @return array Configuration
	*/
	public function get_config_data()
	{
		return $this->config_data;
	}

	/**
	* Merge the cache contents with more data.
	*
	* New values overwrite old ones.
	*
	* @param array $cache_data The additional cache data
	*/
	public function merge_cache_data(array $cache_data)
	{
		$this->cache_data = array_merge($this->cache_data, $cache_data);
	}

	/**
	* Retrieve the entire cache data to be passed to the session.
	*
	* @return array Cache contents
	*/
	public function get_cache_data()
	{
		return $this->cache_data;
	}

	/**
	* Merge the current server info ($_SERVER) with more data.
	*
	* New values overwrite old ones.
	*
	* @param array $server_data The additional server variables
	*/
	public function merge_server_data($server_data)
	{
		return $this->server_data = array_merge($this->server_data, $server_data);
	}

	/**
	 * Set cookies, merge config and server data in one step.
	 *
	 * New values overwrite old ones.
	 *
	 * @param $session_id
	 * @param $user_id
	 * @param $user_agent
	 * @param $ip
	 * @param int $time
	 */
	public function merge_test_data($session_id, $user_id, $user_agent, $ip, $time = 0)
	{
		$this->set_cookies(array(
			'_sid' => $session_id,
			'_u' => $user_id,
		));
		$this->merge_config_data(array(
			'session_length' => time() + $time, // need to do this to allow sessions started at time 0
		));
		$this->merge_server_data(array(
			'HTTP_USER_AGENT' => $user_agent,
			'REMOTE_ADDR' => $ip,
		));
	}

	/**
	* Retrieve all server variables to be passed to the session.
	*
	* @return array Server variables
	*/
	public function get_server_data()
	{
		return $this->server_data;
	}
}

