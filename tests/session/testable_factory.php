<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../mock/session_testable.php';

/**
* This class exists to setup an instance of phpbb's session class for testing.
*
* The class has rather complex dependencies, so in order to make its tests more
* understandable and to make its dependencies more visible this class sets up
* all the necessary global state & variable contents.
*/
class phpbb_session_testable_factory
{
	protected $config_data;
	protected $cache_data;
	protected $cookies;

	protected $config;
	protected $cache;

	/**
	* Initialises the factory with a set of default config and cache values.
	*/
	public function __construct()
	{
		// default configuration values
		$this->config_data = array(
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
		);

		$this->cache_data = array(
			'_bots' => array(),
		);

		$this->cookies = array();

		$this->server_data = $_SERVER;
	}

	public function get_session(dbal $dbal)
	{
		// set up all the global variables used by session
		global $SID, $_SID, $db, $config, $cache;

		$config = $this->config = $this->get_config_data();
		$db = $dbal;

		$cache = $this->cache = new phpbb_mock_cache($this->get_cache_data());
		$SID = $_SID = null;

		$_COOKIE = $this->cookies;
		$_SERVER = $this->server_data;

		$session = new phpbb_mock_session_testable;
		return $session;
	}

	public function set_cookies($cookies)
	{
		$this->cookies = $cookies;
	}

	public function check(PHPUnit_Framework_Assert $test)
	{
		$this->cache->check($test, $this->get_cache_data());
	}

	public function merge_config_data($config_data)
	{
		$this->config_data = array_merge($this->config_data, $config_data);
	}

	public function get_config_data()
	{
		return $this->config_data;
	}

	public function merge_cache_data($cache_data)
	{
		$this->cache_data = array_merge($this->cache_data, $cache_data);
	}

	public function get_cache_data()
	{
		return $this->cache_data;
	}

	public function merge_server_data($server_data)
	{
		return $this->server_data = array_merge($this->server_data, $server_data);
	}

	public function get_server_data()
	{
		return $this->server_data;
	}
}

