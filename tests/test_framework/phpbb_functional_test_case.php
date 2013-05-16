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
require_once __DIR__ . '/../../phpBB/includes/acm/acm_file.php';
require_once __DIR__ . '/../../phpBB/includes/cache.php';

class phpbb_functional_test_case extends phpbb_test_case
{
	protected $client;
	protected $root_url;

	protected $cache = null;
	protected $db = null;

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

	protected function get_db()
	{
		global $phpbb_root_path, $phpEx;
		// so we don't reopen an open connection
		if (!($this->db instanceof dbal))
		{
			if (!class_exists('dbal_' . self::$config['dbms']))
			{
				include($phpbb_root_path . 'includes/db/' . self::$config['dbms'] . ".$phpEx");
			}
			$sql_db = 'dbal_' . self::$config['dbms'];
			$this->db = new $sql_db();
			$this->db->sql_connect(self::$config['dbhost'], self::$config['dbuser'], self::$config['dbpasswd'], self::$config['dbname'], self::$config['dbport']);
		}
		return $this->db;
	}

	protected function get_cache_driver()
	{
		if (!$this->cache)
		{
			$this->cache = new cache();
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

	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);

		$this->backupStaticAttributesBlacklist += array(
			'phpbb_functional_test_case' => array('config', 'already_installed'),
		);
	}

	static protected function install_board()
	{
		global $phpbb_root_path, $phpEx;

		self::$config['table_prefix'] = 'phpbb_';
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
			'email_enable'		=> false,
			'smtp_delivery'		=> false,
			'smtp_host'		=> '',
			'smtp_auth'		=> '',
			'smtp_user'		=> '',
			'smtp_pass'		=> '',
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

		$content = self::do_request('create_table', $data);
		self::assertNotSame(false, $content);
		self::assertContains('The database tables used by phpBB', $content);
		// 3.0 or 3.1
		self::assertContains('have been created and populated with some initial data.', $content);

		$content = self::do_request('config_file', $data);
		self::assertNotSame(false, $content);
		self::assertContains('Configuration file', $content);
		file_put_contents($phpbb_root_path . "config.$phpEx", phpbb_create_config_file_data($data, self::$config['dbms'], array(), true, true));

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

			if ($style_path != 'prosilver' && $style_path != 'subsilver2')
			{
				@mkdir($phpbb_root_path . 'styles/' . $style_path, 0777);
				@mkdir($phpbb_root_path . 'styles/' . $style_path . '/template', 0777);
			}
		}
		else
		{
			$db->sql_multi_insert(STYLES_TABLE, array(
				'style_id' => $style_id,
				'style_name' => $style_path,
				'style_copyright' => '',
				'style_active' => 1,
				'style_path' => $style_path,
				'bbcode_bitfield' => 'kNg=',
				'style_parent_id' => $parent_style_id,
				'style_parent_tree' => $parent_style_path,
			));
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

			if ($style_path != 'prosilver' && $style_path != 'subsilver2')
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

		if (!is_array($config))
		{
			$config = array();
		}

		$config['rand_seed'] = '';
		$config['rand_seed_last_update'] = time() + 600;

		// Required by user_add
		global $db, $cache;
		$db = $this->get_db();
		if (!function_exists('phpbb_mock_null_cache'))
		{
			require_once(__DIR__ . '/../mock/null_cache.php');
		}
		$cache = new phpbb_mock_null_cache;

		if (!function_exists('utf_clean_string'))
		{
			require_once(__DIR__ . '/../../phpBB/includes/utf/utf_tools.php');
		}
		if (!function_exists('user_add'))
		{
			require_once(__DIR__ . '/../../phpBB/includes/functions_user.php');
		}

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
}
