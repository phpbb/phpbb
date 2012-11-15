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

	public function setUp()
	{
		if (!isset(self::$config['phpbb_functional_url']))
		{
			$this->markTestSkipped('phpbb_functional_url was not set in test_config and wasn\'t set as PHPBB_FUNCTIONAL_URL environment variable either.');
		}

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

		if (!static::$already_installed)
		{
			$this->install_board();
			$this->bootstrap();
			static::$already_installed = true;
		}
	}

	protected function install_board()
	{
		global $phpbb_root_path, $phpEx;

		self::$config = phpbb_test_case_helpers::get_test_config();

		if (!isset(self::$config['phpbb_functional_url']))
		{
			return;
		}

		self::$config['table_prefix'] = 'phpbb_';
		$this->recreate_database(self::$config);

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

		$content = $this->do_request('install');
		$this->assertContains('Welcome to Installation', $content);

		$this->do_request('create_table', $data);

		$this->do_request('config_file', $data);
		file_put_contents($phpbb_root_path . "config.$phpEx", phpbb_create_config_file_data($data, self::$config['dbms'], array(), true, true));

		$this->do_request('final', $data);
		copy($phpbb_root_path . "config.$phpEx", $phpbb_root_path . "config_test.$phpEx");
	}

	private function do_request($sub, $post_data = null)
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

	private function recreate_database($config)
	{
		$db_conn_mgr = new phpbb_database_test_connection_manager($config);
		$db_conn_mgr->recreate_db();
	}

	protected function login()
	{
		$this->add_lang('ucp');

		$crawler = $this->request('GET', 'ucp.php');
		$this->assertContains($this->lang('LOGIN_EXPLAIN_UCP'), $crawler->filter('html')->text());

		$form = $crawler->selectButton($this->lang('LOGIN'))->form();
		$login = $this->client->submit($form, array('username' => 'admin', 'password' => 'admin'));

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
	}
}
