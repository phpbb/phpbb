<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
use Symfony\Component\BrowserKit\CookieJar;

require_once __DIR__ . '/../../phpBB/includes/functions_install.php';

class phpbb_functional_test_case extends phpbb_test_case
{
	protected $client;
	protected $root_url;

	protected $cache = null;
	protected $db = null;
	protected $extension_manager = null;

	/**
	* Session ID for current test's session (each test makes its own)
	* @var string
	*/
	protected $sid;

	/**
	* Language array used by phpBB
	* @var array
	*/
	protected $lang = array();

	static protected $config = array();
	static protected $already_installed = false;

	static public function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		self::$config = phpbb_test_case_helpers::get_test_config();

		// Important: this is used both for installation and by
		// test cases for querying the tables.
		// Therefore table prefix must be set before a board is
		// installed, and also before each test case is run.
		self::$config['table_prefix'] = 'phpbb_';

		if (!isset(self::$config['phpbb_functional_url']))
		{
			self::markTestSkipped('phpbb_functional_url was not set in test_config and wasn\'t set as PHPBB_FUNCTIONAL_URL environment variable either.');
		}

		if (!self::$already_installed)
		{
			self::install_board();
			self::$already_installed = true;
		}
	}

	public function setUp()
	{
		parent::setUp();

		$this->bootstrap();

		$this->cookieJar = new CookieJar;
		$this->client = new Goutte\Client(array(), null, $this->cookieJar);
		// Reset the curl handle because it is 0 at this point and not a valid
		// resource
		$this->client->getClient()->getCurlMulti()->reset(true);
		$this->root_url = self::$config['phpbb_functional_url'];
		// Clear the language array so that things
		// that were added in other tests are gone
		$this->lang = array();
		$this->add_lang('common');
		$this->purge_cache();
	}

	public function request($method, $path)
	{
		return $this->client->request($method, $this->root_url . $path);
	}

	// bootstrap, called after board is set up
	// once per test case class
	// test cases can override this
	protected function bootstrap()
	{
	}

	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);

		$this->backupStaticAttributesBlacklist += array(
			'phpbb_functional_test_case' => array('config', 'already_installed'),
		);
	}

	protected function get_db()
	{
		global $phpbb_root_path, $phpEx;
		// so we don't reopen an open connection
		if (!($this->db instanceof phpbb_db_driver))
		{
			$dbms = self::$config['dbms'];
			$this->db = new $dbms();
			$this->db->sql_connect(self::$config['dbhost'], self::$config['dbuser'], self::$config['dbpasswd'], self::$config['dbname'], self::$config['dbport']);
		}
		return $this->db;
	}

	protected function get_cache_driver()
	{
		if (!$this->cache)
		{
			$this->cache = new phpbb_cache_driver_file;
		}

		return $this->cache;
	}

	protected function purge_cache()
	{
		$cache = $this->get_cache_driver();

		$cache->purge();
		$cache->unload();
		$cache->load();
	}

	protected function get_extension_manager()
	{
		global $phpbb_root_path, $phpEx;

		$config = new phpbb_config(array());
		$db = $this->get_db();
		$db_tools = new phpbb_db_tools($db);

		$migrator = new phpbb_db_migrator(
			$config,
			$db,
			$db_tools,
			self::$config['table_prefix'] . 'migrations',
			$phpbb_root_path,
			$php_ext,
			self::$config['table_prefix'],
			array()
		);
		$container = new phpbb_mock_container_builder();
		$container->set('migrator', $migrator);

		$extension_manager = new phpbb_extension_manager(
			$container,
			$db,
			$config,
			new phpbb_filesystem(),
			self::$config['table_prefix'] . 'ext',
			dirname(__FILE__) . '/',
			$php_ext,
			$this->get_cache_driver()
		);

		return $extension_manager;
	}

	static protected function install_board()
	{
		global $phpbb_root_path, $phpEx;

		self::recreate_database(self::$config);

		if (file_exists($phpbb_root_path . "config.$phpEx"))
		{
			if (!file_exists($phpbb_root_path . "config_dev.$phpEx"))
			{
				rename($phpbb_root_path . "config.$phpEx", $phpbb_root_path . "config_dev.$phpEx");
			}
			else
			{
				unlink($phpbb_root_path . "config.$phpEx");
			}
		}

		// begin data
		$data = array();

		$data = array_merge($data, self::$config);

		$data = array_merge($data, array(
			'default_lang'	=> 'en',
			'admin_name'	=> 'admin',
			'admin_pass1'	=> 'admin',
			'admin_pass2'	=> 'admin',
			'board_email'	=> 'nobody@example.com',
		));

		$parseURL = parse_url(self::$config['phpbb_functional_url']);

		$data = array_merge($data, array(
			'email_enable'		=> true,
			'smtp_delivery'		=> true,
			'smtp_host'			=> 'nxdomain.phpbb.com',
			'smtp_auth'			=> '',
			'smtp_user'			=> 'nxuser',
			'smtp_pass'			=> 'nxpass',
			'cookie_secure'		=> false,
			'force_server_vars'	=> false,
			'server_protocol'	=> $parseURL['scheme'] . '://',
			'server_name'		=> 'localhost',
			'server_port'		=> isset($parseURL['port']) ? (int) $parseURL['port'] : 80,
			'script_path'		=> $parseURL['path'],
		));
		// end data

		$content = self::do_request('install');
		self::assertNotSame(false, $content);
		self::assertContains('Welcome to Installation', $content);

		// Installer uses 3.0-style dbms name
		$data['dbms'] = str_replace('phpbb_db_driver_', '', $data['dbms']);
		$content = self::do_request('create_table', $data);
		self::assertNotSame(false, $content);
		self::assertContains('The database tables used by phpBB', $content);
		// 3.0 or 3.1
		self::assertContains('have been created and populated with some initial data.', $content);

		$content = self::do_request('config_file', $data);
		self::assertNotSame(false, $content);
		self::assertContains('Configuration file', $content);
		file_put_contents($phpbb_root_path . "config.$phpEx", phpbb_create_config_file_data($data, self::$config['dbms'], true, true));

		$content = self::do_request('final', $data);
		self::assertNotSame(false, $content);
		self::assertContains('You have successfully installed', $content);
		copy($phpbb_root_path . "config.$phpEx", $phpbb_root_path . "config_test.$phpEx");
	}

	static private function do_request($sub, $post_data = null)
	{
		$context = null;

		if ($post_data)
		{
			$context = stream_context_create(array(
				'http' => array(
					'method'	=> 'POST',
					'header'	=> 'Content-Type: application/x-www-form-urlencoded',
					'content'	=> http_build_query($post_data),
					'ignore_errors' => true,
				),
			));
		}

		return file_get_contents(self::$config['phpbb_functional_url'] . 'install/index.php?mode=install&sub=' . $sub, false, $context);
	}

	static private function recreate_database($config)
	{
		$db_conn_mgr = new phpbb_database_test_connection_manager($config);
		$db_conn_mgr->recreate_db();
	}

	/**
	* Creates a new user with limited permissions
	*
	* @param string $username Also doubles up as the user's password
	* @return int ID of created user
	*/
	protected function create_user($username)
	{
		// Required by unique_id
		global $config;

		$config = new phpbb_config(array());
		$config['rand_seed'] = '';
		$config['rand_seed_last_update'] = time() + 600;

		// Required by user_add
		global $db, $cache, $phpbb_dispatcher, $phpbb_container;
		$db = $this->get_db();
		if (!function_exists('phpbb_mock_null_cache'))
		{
			require_once(__DIR__ . '/../mock/null_cache.php');
		}
		$cache = new phpbb_mock_null_cache;

		$cache_driver = new phpbb_cache_driver_null();
		$phpbb_container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
		$phpbb_container
			->expects($this->any())
			->method('get')
			->with('cache.driver')
			->will($this->returnValue($cache_driver));

		if (!function_exists('utf_clean_string'))
		{
			require_once(__DIR__ . '/../../phpBB/includes/utf/utf_tools.php');
		}
		if (!function_exists('user_add'))
		{
			require_once(__DIR__ . '/../../phpBB/includes/functions_user.php');
		}
		set_config(null, null, null, $config);
		set_config_count(null, null, null, $config);
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();

		$user_row = array(
			'username' => $username,
			'group_id' => 2,
			'user_email' => 'nobody@example.com',
			'user_type' => 0,
			'user_lang' => 'en',
			'user_timezone' => 0,
			'user_dateformat' => '',
			'user_password' => phpbb_hash($username),
		);
		return user_add($user_row);
	}

	protected function remove_user_group($group_name, $usernames)
	{
		global $db, $cache, $auth, $config, $phpbb_dispatcher, $phpbb_log, $phpbb_container, $phpbb_root_path, $phpEx;

		$config = new phpbb_config(array());
		$config['coppa_enable'] = 0;

		$db = $this->get_db();
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$user = $this->getMock('phpbb_user');
		$auth = $this->getMock('phpbb_auth');

		$phpbb_log = new phpbb_log($db, $user, $auth, $phpbb_dispatcher, $phpbb_root_path, 'adm/', $phpEx, LOG_TABLE);
		$cache = new phpbb_mock_null_cache;

		$cache_driver = new phpbb_cache_driver_null();
		$phpbb_container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
		$phpbb_container
			->expects($this->any())
			->method('get')
			->with('cache.driver')
			->will($this->returnValue($cache_driver));

		if (!function_exists('utf_clean_string'))
		{
			require_once(__DIR__ . '/../../phpBB/includes/utf/utf_tools.php');
		}
		if (!function_exists('group_user_del'))
		{
			require_once(__DIR__ . '/../../phpBB/includes/functions_user.php');
		}

		$sql = 'SELECT group_id
			FROM ' . GROUPS_TABLE . "
			WHERE group_name = '" . $db->sql_escape($group_name) . "'";
		$result = $db->sql_query($sql);
		$group_id = (int) $db->sql_fetchfield('group_id');
		$db->sql_freeresult($result);

		return group_user_del($group_id, false, $usernames, $group_name);
	}

	protected function add_user_group($group_name, $usernames, $default = false, $leader = false)
	{
		global $db, $cache, $auth, $config, $phpbb_dispatcher, $phpbb_log, $phpbb_container, $phpbb_root_path, $phpEx;

		$config = new phpbb_config(array());
		$config['coppa_enable'] = 0;

		$db = $this->get_db();
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$user = $this->getMock('phpbb_user');
		$auth = $this->getMock('phpbb_auth');

		$phpbb_log = new phpbb_log($db, $user, $auth, $phpbb_dispatcher, $phpbb_root_path, 'adm/', $phpEx, LOG_TABLE);
		$cache = new phpbb_mock_null_cache;

		$cache_driver = new phpbb_cache_driver_null();
		$phpbb_container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
		$phpbb_container
			->expects($this->any())
			->method('get')
			->with('cache.driver')
			->will($this->returnValue($cache_driver));

		if (!function_exists('utf_clean_string'))
		{
			require_once(__DIR__ . '/../../phpBB/includes/utf/utf_tools.php');
		}
		if (!function_exists('group_user_del'))
		{
			require_once(__DIR__ . '/../../phpBB/includes/functions_user.php');
		}

		$sql = 'SELECT group_id
			FROM ' . GROUPS_TABLE . "
			WHERE group_name = '" . $db->sql_escape($group_name) . "'";
		$result = $db->sql_query($sql);
		$group_id = (int) $db->sql_fetchfield('group_id');
		$db->sql_freeresult($result);

		return group_user_add($group_id, false, $usernames, $group_name, $default, $leader);
	}

	protected function login($username = 'admin')
	{
		$this->add_lang('ucp');

		$crawler = $this->request('GET', 'ucp.php');
		$this->assertContains($this->lang('LOGIN_EXPLAIN_UCP'), $crawler->filter('html')->text());

		$form = $crawler->selectButton($this->lang('LOGIN'))->form();
		$crawler = $this->client->submit($form, array('username' => $username, 'password' => $username));
		$this->assert_response_success();
		$this->assertContains($this->lang('LOGIN_REDIRECT'), $crawler->filter('html')->text());

		$cookies = $this->cookieJar->all();

		// The session id is stored in a cookie that ends with _sid - we assume there is only one such cookie
		foreach ($cookies as $cookie);
		{
			if (substr($cookie->getName(), -4) == '_sid')
			{
				$this->sid = $cookie->getValue();
			}
		}
	}

	protected function logout()
	{
		$this->add_lang('ucp');

		$crawler = $this->request('GET', 'ucp.php?sid=' . $this->sid . '&mode=logout');
		$this->assert_response_success();
		$this->assertContains($this->lang('LOGOUT_REDIRECT'), $crawler->filter('#message')->text());
		unset($this->sid);

	}

	/**
	* Login to the ACP
	* You must run login() before calling this.
	*/
	protected function admin_login($username = 'admin')
	{
		$this->add_lang('acp/common');

		// Requires login first!
		if (empty($this->sid))
		{
			$this->fail('$this->sid is empty. Make sure you call login() before admin_login()');
			return;
		}

		$crawler = $this->request('GET', 'adm/index.php?sid=' . $this->sid);
		$this->assertContains($this->lang('LOGIN_ADMIN_CONFIRM'), $crawler->filter('html')->text());

		$form = $crawler->selectButton($this->lang('LOGIN'))->form();

		foreach ($form->getValues() as $field => $value)
		{
			if (strpos($field, 'password_') === 0)
			{
				$crawler = $this->client->submit($form, array('username' => $username, $field => $username));
				$this->assert_response_success();
				$this->assertContains($this->lang('LOGIN_ADMIN_SUCCESS'), $crawler->filter('html')->text());

				$cookies = $this->cookieJar->all();

				// The session id is stored in a cookie that ends with _sid - we assume there is only one such cookie
				foreach ($cookies as $cookie);
				{
					if (substr($cookie->getName(), -4) == '_sid')
					{
						$this->sid = $cookie->getValue();
					}
				}

				break;
			}
		}
	}

	protected function add_lang($lang_file)
	{
		if (is_array($lang_file))
		{
			foreach ($lang_file as $file)
			{
				$this->add_lang($file);
			}
		}

		$lang_path = __DIR__ . "/../../phpBB/language/en/$lang_file.php";

		$lang = array();

		if (file_exists($lang_path))
		{
			include($lang_path);
		}

		$this->lang = array_merge($this->lang, $lang);
	}

	protected function lang()
	{
		$args = func_get_args();
		$key = $args[0];

		if (empty($this->lang[$key]))
		{
			throw new RuntimeException('Language key "' . $key . '" could not be found.');
		}

		$args[0] = $this->lang[$key];

		return call_user_func_array('sprintf', $args);
	}

	/**
	 * assertContains for language strings
	 *
	 * @param string $needle Search string
	 * @param string $haystack Search this
	 * @param string $message Optional failure message
	 */
	public function assertContainsLang($needle, $haystack, $message = null)
	{
		$this->assertContains(html_entity_decode($this->lang($needle), ENT_QUOTES), $haystack, $message);
	}

	/**
	* Heuristic function to check that the response is success.
	*
	* When php decides to die with a fatal error, it still sends 200 OK
	* status code. This assertion tries to catch that.
	*
	* @return null
	*/
	public function assert_response_success()
	{
		$this->assertEquals(200, $this->client->getResponse()->getStatus());
		$content = $this->client->getResponse()->getContent();
		$this->assertNotContains('Fatal error:', $content);
		$this->assertNotContains('Notice:', $content);
		$this->assertNotContains('Warning:', $content);
		$this->assertNotContains('[phpBB Debug]', $content);
	}

	public function assert_filter($crawler, $expr, $msg = null)
	{
		$nodes = $crawler->filter($expr);
		if ($msg)
		{
			$msg .= "\n";
		}
		else
		{
			$msg = '';
		}
		$msg .= "`$expr` not found in DOM.";
		$this->assertGreaterThan(0, count($nodes), $msg);
		return $nodes;
	}

	/**
	* Asserts that exactly one checkbox with name $name exists within the scope
	* of $crawler and that the checkbox is checked.
	*
	* @param Symfony\Component\DomCrawler\Crawler $crawler
	* @param string $name
	* @param string $message
	*
	* @return null
	*/
	public function assert_checkbox_is_checked($crawler, $name, $message = '')
	{
		$this->assertSame(
			'checked',
			$this->assert_find_one_checkbox($crawler, $name)->attr('checked'),
			$message ?: "Failed asserting that checkbox $name is checked."
		);
	}

	/**
	* Asserts that exactly one checkbox with name $name exists within the scope
	* of $crawler and that the checkbox is unchecked.
	*
	* @param Symfony\Component\DomCrawler\Crawler $crawler
	* @param string $name
	* @param string $message
	*
	* @return null
	*/
	public function assert_checkbox_is_unchecked($crawler, $name, $message = '')
	{
		$this->assertSame(
			'',
			$this->assert_find_one_checkbox($crawler, $name)->attr('checked'),
			$message ?: "Failed asserting that checkbox $name is unchecked."
		);
	}

	/**
	* Searches for an input element of type checkbox with the name $name using
	* $crawler. Contains an assertion that only one such checkbox exists within
	* the scope of $crawler.
	*
	* @param Symfony\Component\DomCrawler\Crawler $crawler
	* @param string $name
	* @param string $message
	*
	* @return Symfony\Component\DomCrawler\Crawler
	*/
	public function assert_find_one_checkbox($crawler, $name, $message = '')
	{
		$query = sprintf('//input[@type="checkbox" and @name="%s"]', $name);
		$result = $crawler->filterXPath($query);

		$this->assertEquals(
			1,
			sizeof($result),
			$message ?: 'Failed asserting that exactly one checkbox with name' .
				" $name exists in crawler scope."
		);

		return $result;
	}
}
