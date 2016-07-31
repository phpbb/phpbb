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

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\WebDriverCurlException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

require_once __DIR__ . '/../../phpBB/includes/functions_install.php';

class phpbb_ui_test_case extends phpbb_test_case
{
	static protected $host = '127.0.0.1';
	static protected $port = 8910;

	/**
	* @var RemoteWebDriver
	*/
	static protected $webDriver;

	static protected $config;
	static protected $root_url;
	static protected $already_installed = false;
	static protected $install_success = false;

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

	static public function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		if (version_compare(PHP_VERSION, '5.3.19', '<'))
		{
			self::markTestSkipped('UI test case requires at least PHP 5.3.19.');
		}
		else if (!class_exists('\Facebook\WebDriver\Remote\RemoteWebDriver'))
		{
			self::markTestSkipped(
				'Could not find RemoteWebDriver class. ' .
				'Run "php ../composer.phar install" from the tests folder.'
			);
		}

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

		if (!self::$webDriver)
		{
			try {
				$capabilities = DesiredCapabilities::firefox();
				self::$webDriver = RemoteWebDriver::create(self::$host . ':' . self::$port, $capabilities);
			} catch (WebDriverCurlException $e) {
				self::markTestSkipped('PhantomJS webserver is not running.');
			}
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
		if (!self::$install_success)
		{
			$this->fail('Installing phpBB has failed.');
		}

		// Clear the language array so that things
		// that were added in other tests are gone
		$this->lang = array();
		$this->add_lang('common');

		$db = $this->get_db();

		foreach (static::setup_extensions() as $extension)
		{
			$this->purge_cache();

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

	static public function visit($path)
	{
		self::$webDriver->get(self::$root_url . $path);
	}

	static protected function recreate_database($config)
	{
		$db_conn_mgr = new phpbb_database_test_connection_manager($config);
		$db_conn_mgr->recreate_db();
	}

	static public function find_element($type, $value)
	{
		return self::$webDriver->findElement(WebDriverBy::$type($value));
	}

	static public function submit($type = 'id', $value = 'submit')
	{
		$element = self::find_element($type, $value);
		$element->click();
	}

	static protected function install_board()
	{
		global $phpbb_root_path, $phpEx, $db;

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

		$parseURL = parse_url(self::$config['phpbb_functional_url']);

		self::visit('install/index.php?mode=install&language=en');
		self::assertContains('Welcome to Installation', self::find_element('id', 'main')->getText());

		// install/index.php?mode=install&sub=requirements
		self::submit();
		self::assertContains('Installation compatibility', self::find_element('id', 'main')->getText());

		// install/index.php?mode=install&sub=database
		self::submit();
		self::assertContains('Database configuration', self::find_element('id', 'main')->getText());

		self::find_element('id','dbms')->sendKeys(str_replace('phpbb\db\driver\\', '',  self::$config['dbms']));
		self::find_element('id','dbhost')->sendKeys(self::$config['dbhost']);
		self::find_element('id','dbport')->sendKeys(self::$config['dbport']);
		self::find_element('id','dbname')->sendKeys(self::$config['dbname']);
		self::find_element('id','dbuser')->sendKeys(self::$config['dbuser']);
		self::find_element('id','dbpasswd')->sendKeys(self::$config['dbpasswd']);

		// Need to clear default phpbb_ prefix
		self::find_element('id','table_prefix')->clear();
		self::find_element('id','table_prefix')->sendKeys(self::$config['table_prefix']);

		// install/index.php?mode=install&sub=database
		self::submit();
		self::assertContains('Successful connection', self::find_element('id','main')->getText());

		// install/index.php?mode=install&sub=administrator
		self::submit();
		self::assertContains('Administrator configuration', self::find_element('id','main')->getText());

		self::find_element('id','admin_name')->sendKeys('admin');
		self::find_element('id','admin_pass1')->sendKeys('adminadmin');
		self::find_element('id','admin_pass2')->sendKeys('adminadmin');
		self::find_element('id','board_email')->sendKeys('nobody@example.com');

		// install/index.php?mode=install&sub=administrator
		self::submit();
		self::assertContains('Tests passed', self::find_element('id','main')->getText());

		// install/index.php?mode=install&sub=config_file
		self::submit();

		// Installer has created a config.php file, we will overwrite it with a
		// config file of our own in order to get the DEBUG constants defined
		$config_php_data = phpbb_create_config_file_data(self::$config, self::$config['dbms'], true, false, true);
		$config_created = file_put_contents($config_file, $config_php_data) !== false;
		if (!$config_created)
		{
			self::markTestSkipped("Could not write $config_file file.");
		}

		if (strpos(self::find_element('id','main')->getText(), 'The configuration file has been written') === false)
		{
			self::submit('id', 'dldone');
		}
		self::assertContains('The configuration file has been written', self::find_element('id','main')->getText());

		// install/index.php?mode=install&sub=advanced
		self::submit();
		self::assertContains('The settings on this page are only necessary to set if you know that you require something different from the default.', self::find_element('id','main')->getText());

		self::find_element('id','smtp_delivery')->sendKeys('1');
		self::find_element('id','smtp_host')->sendKeys('nxdomain.phpbb.com');
		self::find_element('id','smtp_user')->sendKeys('nxuser');
		self::find_element('id','smtp_pass')->sendKeys('nxpass');
		self::find_element('id','server_protocol')->sendKeys($parseURL['scheme'] . '://');
		self::find_element('id','server_name')->sendKeys('localhost');
		self::find_element('id','server_port')->sendKeys(isset($parseURL['port']) ? $parseURL['port'] : 80);
		self::find_element('id','script_path')->sendKeys($parseURL['path']);

		// install/index.php?mode=install&sub=create_table
		self::submit();
		self::assertContains('The database tables used by phpBB', self::find_element('id','main')->getText());
		self::assertContains('have been created and populated with some initial data.', self::find_element('id','main')->getText());

		// install/index.php?mode=install&sub=final
		self::submit();
		self::assertContains('You have successfully installed', self::find_element('id', 'main')->getText());

		copy($config_file, $config_file_test);

		self::$install_success = true;
	}

	public function install_ext($extension)
	{
		$this->login();
		$this->admin_login();

		$ext_path = str_replace('/', '%2F', $extension);

		$this->visit('adm/index.php?i=acp_extensions&mode=main&action=enable_pre&ext_name=' . $ext_path . '&sid=' . $this->sid);
		$this->assertNotEmpty(count(self::find_element('cssSelector', '.submit-buttons')));

		self::find_element('cssSelector', "input[value='Enable']")->submit();
		$this->add_lang('acp/extensions');

		try
		{
			$meta_refresh = self::find_element('cssSelector', 'meta[http-equiv="refresh"]');

			// Wait for extension to be fully enabled
			while (sizeof($meta_refresh))
			{
				preg_match('#url=.+/(adm+.+)#', $meta_refresh->getAttribute('content'), $match);
				self::$webDriver->execute(array('method' => 'post', 'url' => $match[1]));
				$meta_refresh = self::find_element('cssSelector', 'meta[http-equiv="refresh"]');
			}
		}
		catch (\Facebook\WebDriver\Exception\NoSuchElementException $e)
		{
			// Probably no refresh triggered
		}

		$this->assertContainsLang('EXTENSION_ENABLE_SUCCESS', self::find_element('cssSelector', 'div.successbox')->getText());

		$this->logout();
	}

	protected function get_cache_driver()
	{
		if (!$this->cache)
		{
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
		$db_tools = new \phpbb\db\tools($db);

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
		$user = new \phpbb\user('\phpbb\datetime');

		$extension_manager = new \phpbb\extension\manager(
			$container,
			$db,
			$config,
			new phpbb\filesystem(),
			$user,
			self::$config['table_prefix'] . 'ext',
			dirname(__FILE__) . '/',
			$phpEx,
			$this->get_cache_driver()
		);

		return $extension_manager;
	}

	protected function get_db()
	{
		// so we don't reopen an open connection
		if (!($this->db instanceof \phpbb\db\driver\driver_interface))
		{
			$dbms = self::$config['dbms'];
			/** @var \phpbb\db\driver\driver_interface $db */
			$db = new $dbms();
			$db->sql_connect(self::$config['dbhost'], self::$config['dbuser'], self::$config['dbpasswd'], self::$config['dbname'], self::$config['dbport']);
			$this->db = $db;
		}
		return $this->db;
	}

	protected function logout()
	{
		$this->add_lang('ucp');

		if (empty($this->sid))
		{
			return;
		}

		$this->visit('ucp.php?sid=' . $this->sid . '&mode=logout');
		$this->assertContains($this->lang('REGISTER'), self::$webDriver->getPageSource());
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

		self::$webDriver->manage()->deleteAllCookies();

		$this->visit('adm/index.php?sid=' . $this->sid);
		$this->assertContains($this->lang('LOGIN_ADMIN_CONFIRM'), self::$webDriver->getPageSource());

		self::find_element('cssSelector', 'input[name=username]')->clear()->sendKeys($username);
		self::find_element('cssSelector', 'input[type=password]')->sendKeys($username . $username);
		self::find_element('cssSelector', 'input[name=login]')->click();
		$this->assertContains($this->lang('ADMIN_PANEL'), $this->find_element('cssSelector', 'h1')->getText());

		$cookies = self::$webDriver->manage()->getCookies();

		// The session id is stored in a cookie that ends with _sid - we assume there is only one such cookie
		foreach ($cookies as $cookie)
		{
			if (substr($cookie['name'], -4) == '_sid')
			{
				$this->sid = $cookie['value'];

				break;
			}
		}

		$this->assertNotEmpty($this->sid);
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

	protected function login($username = 'admin')
	{
		$this->add_lang('ucp');

		self::$webDriver->manage()->deleteAllCookies();

		$this->visit('ucp.php');
		$this->assertContains($this->lang('LOGIN_EXPLAIN_UCP'), self::$webDriver->getPageSource());

		self::$webDriver->manage()->deleteAllCookies();

		self::find_element('cssSelector', 'input[name=username]')->sendKeys($username);
		self::find_element('cssSelector', 'input[name=password]')->sendKeys($username . $username);
		self::find_element('cssSelector', 'input[name=login]')->click();
		$this->assertNotContains($this->lang('LOGIN'), $this->find_element('className', 'navbar')->getText());

		$cookies = self::$webDriver->manage()->getCookies();

		// The session id is stored in a cookie that ends with _sid - we assume there is only one such cookie
		foreach ($cookies as $cookie)
		{
			if (substr($cookie['name'], -4) == '_sid')
			{
				$this->sid = $cookie['value'];
			}
		}

		$this->assertNotEmpty($this->sid);
	}

	/**
	 * Take screenshot. Can be used for debug purposes.
	 *
	 * @throws Exception When screenshot can't be created
	 */
	public function take_screenshot()
	{
		// Change the Path to your own settings
		$screenshot = time() . ".png";

		self::$webDriver->takeScreenshot($screenshot);
	}
}
