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
use Symfony\Component\BrowserKit\CookieJar;

require_once __DIR__ . '/../../phpBB/includes/functions_install.php';

class phpbb_functional_test_case extends phpbb_test_case
{
	static protected $client;
	static protected $cookieJar;
	static protected $root_url;

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
	static protected $last_post_timestamp = 0;

	static public function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		self::$config = phpbb_test_case_helpers::get_test_config();
		self::$root_url = self::$config['phpbb_functional_url'];

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

	/**
	* @return array List of extensions that should be set up
	*/
	static protected function setup_extensions()
	{
		return array();
	}

	public function setUp()
	{
		parent::setUp();

		$this->bootstrap();

		self::$cookieJar = new CookieJar;
		self::$client = new Goutte\Client(array(), null, self::$cookieJar);
		// Reset the curl handle because it is 0 at this point and not a valid
		// resource
		self::$client->getClient()->getCurlMulti()->reset(true);

		// Clear the language array so that things
		// that were added in other tests are gone
		$this->lang = array();
		$this->add_lang('common');
		$this->purge_cache();

		$db = $this->get_db();

		foreach (static::setup_extensions() as $extension)
		{
			$sql = 'SELECT ext_active
				FROM ' . EXT_TABLE . "
				WHERE ext_name = '" . $db->sql_escape($extension). "'";
			$result = $db->sql_query($sql);
			$status = (bool) $db->sql_fetchfield('ext_active');
			$db->sql_freeresult($result);

			if (!$status)
			{
				$this->install_ext($extension);
			}
		}
	}

	protected function tearDown()
	{
		parent::tearDown();

		if ($this->db instanceof \phpbb\db\driver\driver_interface)
		{
			// Close the database connections again this test
			$this->db->sql_close();
		}
	}

	/**
	* Perform a request to page
	*
	* @param string	$method		HTTP Method
	* @param string	$path		Page path, relative from phpBB root path
	* @param array $form_data	An array of form field values
	* @param bool	$assert_response_html	Should we perform standard assertions for a normal html page
	* @return Symfony\Component\DomCrawler\Crawler
	*/
	static public function request($method, $path, $form_data = array(), $assert_response_html = true)
	{
		$crawler = self::$client->request($method, self::$root_url . $path, $form_data);

		if ($assert_response_html)
		{
			self::assert_response_html();
		}

		return $crawler;
	}

	/**
	* Submits a form
	*
	* @param Symfony\Component\DomCrawler\Form $form A Form instance
	* @param array $values An array of form field values
	* @param bool	$assert_response_html	Should we perform standard assertions for a normal html page
	* @return Symfony\Component\DomCrawler\Crawler
	*/
	static public function submit(Symfony\Component\DomCrawler\Form $form, array $values = array(), $assert_response_html = true)
	{
		$crawler = self::$client->submit($form, $values);

		if ($assert_response_html)
		{
			self::assert_response_html();
		}

		return $crawler;
	}

	/**
	* Get Client Content
	*
	* @return string HTML page
	*/
	static public function get_content()
	{
		return self::$client->getResponse()->getContent();
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
		if (!($this->db instanceof \phpbb\db\driver\driver_interface))
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
			global $phpbb_container;

			$phpbb_container = new phpbb_mock_container_builder();
			$phpbb_container->setParameter('core.environment', PHPBB_ENVIRONMENT);

			$this->cache = new \phpbb\cache\driver\file;
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

		$config = new \phpbb\config\config(array());
		$db = $this->get_db();
		$factory = new \phpbb\db\tools\factory();
		$db_tools = $factory->get($db);

		$container = new phpbb_mock_container_builder();
		$migrator = new \phpbb\db\migrator(
			$container,
			$config,
			$db,
			$db_tools,
			self::$config['table_prefix'] . 'migrations',
			$phpbb_root_path,
			$phpEx,
			self::$config['table_prefix'],
			array(),
			new \phpbb\db\migration\helper()
		);
		$container->set('migrator', $migrator);
		$container->set('dispatcher', new phpbb_mock_event_dispatcher());

		$extension_manager = new \phpbb\extension\manager(
			$container,
			$db,
			$config,
			new phpbb\filesystem\filesystem(),
			self::$config['table_prefix'] . 'ext',
			dirname(__FILE__) . '/',
			$phpEx,
			$this->get_cache_driver()
		);

		return $extension_manager;
	}

	static protected function install_board()
	{
		global $phpbb_root_path, $phpEx;

		self::recreate_database(self::$config);

		$config_file = $phpbb_root_path . "config.$phpEx";
		$config_file_dev = $phpbb_root_path . "config_dev.$phpEx";
		$config_file_test = $phpbb_root_path . "config_test.$phpEx";

		if (file_exists($config_file))
		{
			if (!file_exists($config_file_dev))
			{
				rename($config_file, $config_file_dev);
			}
			else
			{
				unlink($config_file);
			}
		}

		self::$cookieJar = new CookieJar;
		self::$client = new Goutte\Client(array(), null, self::$cookieJar);
		// Set client manually so we can increase the cURL timeout
		self::$client->setClient(new Guzzle\Http\Client('', array(
			Guzzle\Http\Client::DISABLE_REDIRECTS	=> true,
			'curl.options'	=> array(
				CURLOPT_TIMEOUT	=> 120,
			),
		)));

		// Reset the curl handle because it is 0 at this point and not a valid
		// resource
		self::$client->getClient()->getCurlMulti()->reset(true);

		$parseURL = parse_url(self::$config['phpbb_functional_url']);

		$crawler = self::request('GET', 'install/index.php?mode=install&language=en');
		self::assertContains('Welcome to Installation', $crawler->filter('#main')->text());
		$form = $crawler->selectButton('submit')->form();

		// install/index.php?mode=install&sub=requirements
		$crawler = self::submit($form);
		self::assertContains('Installation compatibility', $crawler->filter('#main')->text());
		$form = $crawler->selectButton('submit')->form();

		// install/index.php?mode=install&sub=database
		$crawler = self::submit($form);
		self::assertContains('Database configuration', $crawler->filter('#main')->text());
		$form = $crawler->selectButton('submit')->form(array(
			// Installer uses 3.0-style dbms name
			'dbms'			=> str_replace('phpbb\db\driver\\', '',  self::$config['dbms']),
			'dbhost'		=> self::$config['dbhost'],
			'dbport'		=> self::$config['dbport'],
			'dbname'		=> self::$config['dbname'],
			'dbuser'		=> self::$config['dbuser'],
			'dbpasswd'		=> self::$config['dbpasswd'],
			'table_prefix'	=> self::$config['table_prefix'],
		));

		// install/index.php?mode=install&sub=database
		$crawler = self::submit($form);
		self::assertContains('Successful connection', $crawler->filter('#main')->text());
		$form = $crawler->selectButton('submit')->form();

		// install/index.php?mode=install&sub=administrator
		$crawler = self::submit($form);
		self::assertContains('Administrator configuration', $crawler->filter('#main')->text());
		$form = $crawler->selectButton('submit')->form(array(
			'default_lang'	=> 'en',
			'admin_name'	=> 'admin',
			'admin_pass1'	=> 'adminadmin',
			'admin_pass2'	=> 'adminadmin',
			'board_email'	=> 'nobody@example.com',
		));

		// install/index.php?mode=install&sub=administrator
		$crawler = self::submit($form);
		self::assertContains('Tests passed', $crawler->filter('#main')->text());
		$form = $crawler->selectButton('submit')->form();

		// We have to skip install/index.php?mode=install&sub=config_file
		// because that step will create a config.php file if phpBB has the
		// permission to do so. We have to create the config file on our own
		// in order to get the DEBUG constants defined.
		$config_php_data = phpbb_create_config_file_data(self::$config, self::$config['dbms'], true, false, true);
		$config_created = file_put_contents($config_file, $config_php_data) !== false;
		if (!$config_created)
		{
			self::markTestSkipped("Could not write $config_file file.");
		}

		// We also have to create a install lock that is normally created by
		// the installer. The file will be removed by the final step of the
		// installer.
		$install_lock_file = $phpbb_root_path . 'cache/install_lock';
		$lock_created = file_put_contents($install_lock_file, '') !== false;
		if (!$lock_created)
		{
			self::markTestSkipped("Could not create $lock_created file.");
		}
		@chmod($install_lock_file, 0666);

		// install/index.php?mode=install&sub=advanced
		$form_data = $form->getValues();
		unset($form_data['submit']);

		$crawler = self::request('POST', 'install/index.php?mode=install&sub=advanced', $form_data);
		self::assertContains('The settings on this page are only necessary to set if you know that you require something different from the default.', $crawler->filter('#main')->text());
		$form = $crawler->selectButton('submit')->form(array(
			'email_enable'		=> true,
			'smtp_delivery'		=> true,
			'smtp_host'			=> 'nxdomain.phpbb.com',
			'smtp_auth'			=> 'PLAIN',
			'smtp_user'			=> 'nxuser',
			'smtp_pass'			=> 'nxpass',
			'cookie_secure'		=> false,
			'force_server_vars'	=> false,
			'server_protocol'	=> $parseURL['scheme'] . '://',
			'server_name'		=> 'localhost',
			'server_port'		=> isset($parseURL['port']) ? (int) $parseURL['port'] : 80,
			'script_path'		=> $parseURL['path'],
		));

		// install/index.php?mode=install&sub=create_table
		$crawler = self::submit($form);
		self::assertContains('The database tables used by phpBB', $crawler->filter('#main')->text());
		self::assertContains('have been created and populated with some initial data.', $crawler->filter('#main')->text());
		$form = $crawler->selectButton('submit')->form();

		// install/index.php?mode=install&sub=final
		$crawler = self::submit($form);
		self::assertContains('You have successfully installed', $crawler->text());

		copy($config_file, $config_file_test);
	}

	public function install_ext($extension)
	{
		$this->login();
		$this->admin_login();

		$ext_path = str_replace('/', '%2F', $extension);

		$crawler = self::request('GET', 'adm/index.php?i=acp_extensions&mode=main&action=enable_pre&ext_name=' . $ext_path . '&sid=' . $this->sid);
		$this->assertGreaterThan(0, $crawler->filter('.submit-buttons')->count());

		$form = $crawler->selectButton('Enable')->form();
		$crawler = self::submit($form);
		$this->add_lang('acp/extensions');

		$meta_refresh = $crawler->filter('meta[http-equiv="refresh"]');

		// Wait for extension to be fully enabled
		while (sizeof($meta_refresh))
		{
			preg_match('#url=.+/(adm+.+)#', $meta_refresh->attr('content'), $match);
			$url = $match[1];
			$crawler = self::request('POST', $url);
			$meta_refresh = $crawler->filter('meta[http-equiv="refresh"]');
		}

		$this->assertContainsLang('EXTENSION_ENABLE_SUCCESS', $crawler->filter('div.successbox')->text());

		$this->logout();
	}

	static private function recreate_database($config)
	{
		$db_conn_mgr = new phpbb_database_test_connection_manager($config);
		$db_conn_mgr->recreate_db();
	}

	/**
	* Creates a new style
	*
	* @param string $style_id Style ID
	* @param string $style_path Style directory
	* @param string $parent_style_id Parent style id. Default = 1
	* @param string $parent_style_path Parent style directory. Default = 'prosilver'
	*/
	protected function add_style($style_id, $style_path, $parent_style_id = 1, $parent_style_path = 'prosilver')
	{
		global $phpbb_root_path;

		$db = $this->get_db();
		if (version_compare(PHPBB_VERSION, '3.1.0-dev', '<'))
		{
			$sql = 'INSERT INTO ' . STYLES_TABLE . ' ' . $db->sql_build_array('INSERT', array(
				'style_id' => $style_id,
				'style_name' => $style_path,
				'style_copyright' => '',
				'style_active' => 1,
				'template_id' => $style_id,
				'theme_id' => $style_id,
				'imageset_id' => $style_id,
			));
			$db->sql_query($sql);

			$sql = 'INSERT INTO ' . STYLES_IMAGESET_TABLE . ' ' . $db->sql_build_array('INSERT', array(
				'imageset_id' => $style_id,
				'imageset_name' => $style_path,
				'imageset_copyright' => '',
				'imageset_path' => $style_path,
			));
			$db->sql_query($sql);

			$sql = 'INSERT INTO ' . STYLES_TEMPLATE_TABLE . ' ' . $db->sql_build_array('INSERT', array(
				'template_id' => $style_id,
				'template_name' => $style_path,
				'template_copyright' => '',
				'template_path' => $style_path,
				'bbcode_bitfield' => 'kNg=',
				'template_inherits_id' => $parent_style_id,
				'template_inherit_path' => $parent_style_path,
			));
			$db->sql_query($sql);

			$sql = 'INSERT INTO ' . STYLES_THEME_TABLE . ' ' . $db->sql_build_array('INSERT', array(
				'theme_id' => $style_id,
				'theme_name' => $style_path,
				'theme_copyright' => '',
				'theme_path' => $style_path,
				'theme_storedb' => 0,
				'theme_mtime' => 0,
				'theme_data' => '',
			));
			$db->sql_query($sql);

			if ($style_path != 'prosilver')
			{
				@mkdir($phpbb_root_path . 'styles/' . $style_path, 0777);
				@mkdir($phpbb_root_path . 'styles/' . $style_path . '/template', 0777);
			}
		}
		else
		{
			$db->sql_multi_insert(STYLES_TABLE, array(array(
				'style_id' => $style_id,
				'style_name' => $style_path,
				'style_copyright' => '',
				'style_active' => 1,
				'style_path' => $style_path,
				'bbcode_bitfield' => 'kNg=',
				'style_parent_id' => $parent_style_id,
				'style_parent_tree' => $parent_style_path,
			)));
		}
	}

	/**
	* Remove temporary style created by add_style()
	*
	* @param string $style_id Style ID
	* @param string $style_path Style directory
	*/
	protected function delete_style($style_id, $style_path)
	{
		global $phpbb_root_path;

		$db = $this->get_db();
		$db->sql_query('DELETE FROM ' . STYLES_TABLE . ' WHERE style_id = ' . $style_id);
		if (version_compare(PHPBB_VERSION, '3.1.0-dev', '<'))
		{
			$db->sql_query('DELETE FROM ' . STYLES_IMAGESET_TABLE . ' WHERE imageset_id = ' . $style_id);
			$db->sql_query('DELETE FROM ' . STYLES_TEMPLATE_TABLE . ' WHERE template_id = ' . $style_id);
			$db->sql_query('DELETE FROM ' . STYLES_THEME_TABLE . ' WHERE theme_id = ' . $style_id);

			if ($style_path != 'prosilver')
			{
				@rmdir($phpbb_root_path . 'styles/' . $style_path . '/template');
				@rmdir($phpbb_root_path . 'styles/' . $style_path);
			}
		}
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

		$config = new \phpbb\config\config(array());

		/*
		* Add required config entries to the config array to prevent
		* set_config() sending an INSERT query for already existing entries,
		* resulting in a SQL error.
		* This is because set_config() first sends an UPDATE query, then checks
		* sql_affectedrows() which can be 0 (e.g. on MySQL) when the new
		* data is already there.
		*/
		$config['newest_user_colour'] = '';
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

		$cache_driver = new \phpbb\cache\driver\dummy();
		$phpbb_container = new phpbb_mock_container_builder();
		$phpbb_container->set('cache.driver', $cache_driver);
		$phpbb_notifications = new phpbb_mock_notification_manager();
		$phpbb_container->set('notification_manager', $phpbb_notifications);

		if (!function_exists('utf_clean_string'))
		{
			require_once(__DIR__ . '/../../phpBB/includes/utf/utf_tools.php');
		}
		if (!function_exists('user_add'))
		{
			require_once(__DIR__ . '/../../phpBB/includes/functions_user.php');
		}

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$passwords_manager = $this->get_passwords_manager();

		$user_row = array(
			'username' => $username,
			'group_id' => 2,
			'user_email' => 'nobody@example.com',
			'user_type' => 0,
			'user_lang' => 'en',
			'user_timezone' => 'UTC',
			'user_dateformat' => 'r',
			'user_password' => $passwords_manager->hash($username . $username),
		);
		return user_add($user_row);
	}

	protected function remove_user_group($group_name, $usernames)
	{
		global $db, $cache, $auth, $config, $phpbb_dispatcher, $phpbb_log, $phpbb_container, $phpbb_root_path, $phpEx;

		$config = new \phpbb\config\config(array());
		$config['coppa_enable'] = 0;

		$db = $this->get_db();
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$user = $this->getMock('\phpbb\user', array(), array(
			new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx)),
			'\phpbb\datetime'
		));
		$auth = $this->getMock('\phpbb\auth\auth');

		$phpbb_log = new \phpbb\log\log($db, $user, $auth, $phpbb_dispatcher, $phpbb_root_path, 'adm/', $phpEx, LOG_TABLE);
		$cache = new phpbb_mock_null_cache;

		$cache_driver = new \phpbb\cache\driver\dummy();
		$phpbb_container = new phpbb_mock_container_builder();
		$phpbb_container->set('cache.driver', $cache_driver);
		$phpbb_container->set('notification_manager', new phpbb_mock_notification_manager());

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

		$config = new \phpbb\config\config(array());
		$config['coppa_enable'] = 0;

		$db = $this->get_db();
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$user = $this->getMock('\phpbb\user', array(), array(
			new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx)),
			'\phpbb\datetime'
		));
		$auth = $this->getMock('\phpbb\auth\auth');

		$phpbb_log = new \phpbb\log\log($db, $user, $auth, $phpbb_dispatcher, $phpbb_root_path, 'adm/', $phpEx, LOG_TABLE);
		$cache = new phpbb_mock_null_cache;

		$cache_driver = new \phpbb\cache\driver\dummy();
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

		$crawler = self::request('GET', 'ucp.php');
		$this->assertContains($this->lang('LOGIN_EXPLAIN_UCP'), $crawler->filter('html')->text());

		$form = $crawler->selectButton($this->lang('LOGIN'))->form();
		$crawler = self::submit($form, array('username' => $username, 'password' => $username . $username));
		$this->assertNotContains($this->lang('LOGIN'), $crawler->filter('.navbar')->text());

		$cookies = self::$cookieJar->all();

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

		$crawler = self::request('GET', 'ucp.php?sid=' . $this->sid . '&mode=logout');
		$this->assertContains($this->lang('REGISTER'), $crawler->filter('.navbar')->text());
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

		$crawler = self::request('GET', 'adm/index.php?sid=' . $this->sid);
		$this->assertContains($this->lang('LOGIN_ADMIN_CONFIRM'), $crawler->filter('html')->text());

		$form = $crawler->selectButton($this->lang('LOGIN'))->form();

		foreach ($form->getValues() as $field => $value)
		{
			if (strpos($field, 'password_') === 0)
			{
				$crawler = self::submit($form, array('username' => $username, $field => $username . $username));
				$this->assertContains($this->lang('ADMIN_PANEL'), $crawler->filter('h1')->text());

				$cookies = self::$cookieJar->all();

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

	protected function add_lang_ext($ext_name, $lang_file)
	{
		if (is_array($lang_file))
		{
			foreach ($lang_file as $file)
			{
				$this->add_lang_ext($ext_name, $file);
			}

			return;
		}

		$lang_path = __DIR__ . "/../../phpBB/ext/{$ext_name}/language/en/$lang_file.php";

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
	 * @param string $needle	Search string
	 * @param string $haystack	Search this
	 * @param string $message	Optional failure message
	 */
	public function assertContainsLang($needle, $haystack, $message = null)
	{
		$this->assertContains(html_entity_decode($this->lang($needle), ENT_QUOTES), $haystack, $message);
	}

	/**
	* assertNotContains for language strings
	*
	* @param string $needle		Search string
	* @param string $haystack	Search this
	* @param string $message	Optional failure message
	*/
	public function assertNotContainsLang($needle, $haystack, $message = null)
	{
		$this->assertNotContains(html_entity_decode($this->lang($needle), ENT_QUOTES), $haystack, $message);
	}

	/*
	* Perform some basic assertions for the page
	*
	* Checks for debug/error output before the actual page content and the status code
	*
	* @param mixed $status_code		Expected status code, false to disable check
	* @return null
	*/
	static public function assert_response_html($status_code = 200)
	{
		// Any output before the doc type means there was an error
		$content = self::$client->getResponse()->getContent();
		self::assertNotContains('[phpBB Debug]', $content);
		self::assertStringStartsWith('<!DOCTYPE', trim($content), 'Output found before DOCTYPE specification.');

		if ($status_code !== false)
		{
			self::assert_response_status_code($status_code);
		}
	}

	/*
	* Perform some basic assertions for an xml page
	*
	* Checks for debug/error output before the actual page content and the status code
	*
	* @param mixed $status_code		Expected status code, false to disable check
	* @return null
	*/
	static public function assert_response_xml($status_code = 200)
	{
		// Any output before the xml opening means there was an error
		$content = self::$client->getResponse()->getContent();
		self::assertNotContains('[phpBB Debug]', $content);
		self::assertStringStartsWith('<?xml', trim($content), 'Output found before XML specification.');

		if ($status_code !== false)
		{
			self::assert_response_status_code($status_code);
		}
	}

	/**
	* Heuristic function to check that the response is success.
	*
	* When php decides to die with a fatal error, it still sends 200 OK
	* status code. This assertion tries to catch that.
	*
	* @param int $status_code	Expected status code
	* @return null
	*/
	static public function assert_response_status_code($status_code = 200)
	{
		self::assertEquals($status_code, self::$client->getResponse()->getStatus(), 'HTTP status code does not match');
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
		$this->assertNull(
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

	/**
	* Creates a topic
	*
	* Be sure to login before creating
	*
	* @param int $forum_id
	* @param string $subject
	* @param string $message
	* @param array $additional_form_data Any additional form data to be sent in the request
	* @param string $expected Lang var of expected message after posting
	* @return array|null post_id, topic_id if message is empty
	*/
	public function create_topic($forum_id, $subject, $message, $additional_form_data = array(), $expected = '')
	{
		$posting_url = "posting.php?mode=post&f={$forum_id}&sid={$this->sid}";

		$form_data = array_merge(array(
			'subject'		=> $subject,
			'message'		=> $message,
			'post'			=> true,
		), $additional_form_data);

		return self::submit_post($posting_url, 'POST_TOPIC', $form_data, $expected);
	}

	/**
	* Creates a post
	*
	* Be sure to login before creating
	*
	* @param int $forum_id
	* @param int $topic_id
	* @param string $subject
	* @param string $message
	* @param array $additional_form_data Any additional form data to be sent in the request
	* @param string $expected Lang var of expected message after posting
	* @return array|null post_id, topic_id if message is empty
	*/
	public function create_post($forum_id, $topic_id, $subject, $message, $additional_form_data = array(), $expected = '')
	{
		$posting_url = "posting.php?mode=reply&f={$forum_id}&t={$topic_id}&sid={$this->sid}";

		$form_data = array_merge(array(
			'subject'		=> $subject,
			'message'		=> $message,
			'post'			=> true,
		), $additional_form_data);

		return self::submit_post($posting_url, 'POST_REPLY', $form_data, $expected);
	}

	/**
	* Helper for submitting posts
	*
	* @param string $posting_url
	* @param string $posting_contains
	* @param array $form_data
	* @param string $expected Lang var of expected message after posting
	* @return array|null post_id, topic_id if message is empty
	*/
	protected function submit_post($posting_url, $posting_contains, $form_data, $expected = '')
	{
		$this->add_lang('posting');

		$crawler = $this->submit_message($posting_url, $posting_contains, $form_data);

		if ($expected !== '')
		{
			if (isset($this->lang[$expected]))
			{
				$this->assertContainsLang($expected, $crawler->filter('html')->text());
			}
			else
			{
				$this->assertContains($expected, $crawler->filter('html')->text());
			}
			return null;
		}

		$url = $crawler->selectLink($form_data['subject'])->link()->getUri();

		return array(
			'topic_id'	=> $this->get_parameter_from_link($url, 't'),
			'post_id'	=> $this->get_parameter_from_link($url, 'p'),
		);
	}

	/**
	* Creates a private message
	*
	* Be sure to login before creating
	*
	* @param string $subject
	* @param string $message
	* @param array $to
	* @param array $additional_form_data Any additional form data to be sent in the request
	* @return int private_message_id
	*/
	public function create_private_message($subject, $message, $to, $additional_form_data = array())
	{
		$this->add_lang(array('ucp', 'posting'));

		$posting_url = "ucp.php?i=pm&mode=compose&sid={$this->sid}";

		$form_data = array_merge(array(
			'subject'		=> $subject,
			'message'		=> $message,
			'post'			=> true,
		), $additional_form_data);

		foreach ($to as $user_id)
		{
			$form_data['address_list[u][' . $user_id . ']'] = 'to';
		}

		$crawler = self::submit_message($posting_url, 'POST_NEW_PM', $form_data);

		$this->assertContains($this->lang('MESSAGE_STORED'), $crawler->filter('html')->text());
		$url = $crawler->selectLink($this->lang('VIEW_PRIVATE_MESSAGE', '', ''))->link()->getUri();

		return $this->get_parameter_from_link($url, 'p');
	}

	/**
	* Helper for submitting a message (post or private message)
	*
	* @param string $posting_url
	* @param string $posting_contains
	* @param array $form_data
	* @return \Symfony\Component\DomCrawler\Crawler the crawler object
	*/
	protected function submit_message($posting_url, $posting_contains, $form_data)
	{
		if (time() == self::$last_post_timestamp)
		{
			// Travis is too fast, so we have to wait to not mix up the post/topic order
			sleep(1);
		}
		self::$last_post_timestamp = time();

		$crawler = self::request('GET', $posting_url);
		$this->assertContains($this->lang($posting_contains), $crawler->filter('html')->text());

		if (!empty($form_data['upload_files']))
		{
			for ($i = 0; $i < $form_data['upload_files']; $i++)
			{
				$file = array(
					'tmp_name'	=> __DIR__ . '/../functional/fixtures/files/valid.jpg',
					'name'		=> 'valid.jpg',
					'type'		=> 'image/jpeg',
					'size'		=> filesize(__DIR__ . '/../functional/fixtures/files/valid.jpg'),
					'error'		=> UPLOAD_ERR_OK,
				);

				$crawler = self::$client->request('POST', $posting_url, array('add_file' => $this->lang('ADD_FILE')), array('fileupload' => $file));
			}
			unset($form_data['upload_files']);
		}

		$hidden_fields = array(
			$crawler->filter('[type="hidden"]')->each(function ($node, $i) {
				return array('name' => $node->attr('name'), 'value' => $node->attr('value'));
			}),
		);

		foreach ($hidden_fields as $fields)
		{
			foreach($fields as $field)
			{
				$form_data[$field['name']] = $field['value'];
			}
		}

		// Bypass time restriction that said that if the lastclick time (i.e. time when the form was opened)
		// is not at least 2 seconds before submission, cancel the form
		$form_data['lastclick'] = 0;

		// I use a request because the form submission method does not allow you to send data that is not
		// contained in one of the actual form fields that the browser sees (i.e. it ignores "hidden" inputs)
		// Instead, I send it as a request with the submit button "post" set to true.
		return self::request('POST', $posting_url, $form_data);
	}

	/**
	* Deletes a topic
	*
	* Be sure to login before creating
	*
	* @param int $topic_id
	* @return null
	*/
	public function delete_topic($topic_id)
	{
		$this->add_lang('posting');
		$crawler = $this->get_quickmod_page($topic_id, 'DELETE_TOPIC');
		$this->assertContainsLang('DELETE_PERMANENTLY', $crawler->text());

		$this->add_lang('mcp');
		$form = $crawler->selectButton('Yes')->form();
		$form['delete_permanent'] = 1;
		$crawler = self::submit($form);
		$this->assertContainsLang('TOPIC_DELETED_SUCCESS', $crawler->text());
	}

	/**
	* Deletes a post
	*
	* Be sure to login before creating
	*
	* @param int $forum_id
	* @param int $topic_id
	* @return null
	*/
	public function delete_post($forum_id, $post_id)
	{
		$this->add_lang('posting');
		$crawler = self::request('GET', "posting.php?mode=delete&f={$forum_id}&p={$post_id}&sid={$this->sid}");
		$this->assertContainsLang('DELETE_PERMANENTLY', $crawler->text());

		$form = $crawler->selectButton('Yes')->form();
		$form['delete_permanent'] = 1;
		$crawler = self::submit($form);
		$this->assertContainsLang('POST_DELETED', $crawler->text());
	}

	/**
	* Returns the requested parameter from a URL
	*
	* @param	string	$url
	* @param	string	$parameter
	* @return		string	Value of the parameter in the URL, null if not set
	*/
	public function get_parameter_from_link($url, $parameter)
	{
		if (strpos($url, '?') === false)
		{
			return null;
		}

		$url_parts = explode('?', $url);
		if (isset($url_parts[1]))
		{
			$url_parameters = $url_parts[1];
			if (strpos($url_parameters, '#') !== false)
			{
				$url_parameters = explode('#', $url_parameters);
				$url_parameters = $url_parameters[0];
			}

			foreach (explode('&', $url_parameters) as $url_param)
			{
				list($param, $value) = explode('=', $url_param);
				if ($param == $parameter)
				{
					return $value;
				}
			}
		}
		return null;
	}

	/**
	* Return a passwords manager instance
	*
	* @return phpbb\passwords\manager
	*/
	public function get_passwords_manager()
	{
		// Prepare dependencies for manager and driver
		$config = new \phpbb\config\config(array());
		$driver_helper = new \phpbb\passwords\driver\helper($config);

		$passwords_drivers = array(
			'passwords.driver.bcrypt_2y'	=> new \phpbb\passwords\driver\bcrypt_2y($config, $driver_helper),
			'passwords.driver.bcrypt'		=> new \phpbb\passwords\driver\bcrypt($config, $driver_helper),
			'passwords.driver.salted_md5'	=> new \phpbb\passwords\driver\salted_md5($config, $driver_helper),
			'passwords.driver.phpass'		=> new \phpbb\passwords\driver\phpass($config, $driver_helper),
		);

		$passwords_helper = new \phpbb\passwords\helper;
		// Set up passwords manager
		$manager = new \phpbb\passwords\manager($config, $passwords_drivers, $passwords_helper, array_keys($passwords_drivers));

		return $manager;
	}

	/**
	* Get quickmod page
	*
	* @param int $topic_id
	* @param string $action	Language key for the quickmod action
	* @param Symfony\Component\DomCrawler\Crawler Optional crawler object to use instead of creating new one.
	* @return Symfony\Component\DomCrawler\Crawler
	*/
	public function get_quickmod_page($topic_id, $action, $crawler = false)
	{
		$this->add_lang('viewtopic');

		if ($crawler === false)
		{
			$crawler = self::request('GET', "viewtopic.php?t={$topic_id}&sid={$this->sid}");
		}
		$link = $crawler->filter('#quickmod')->selectLink($this->lang($action))->link()->getUri();

		return self::request('GET', substr($link, strpos($link, 'mcp.')));
	}
}
