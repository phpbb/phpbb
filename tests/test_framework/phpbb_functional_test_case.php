<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once __DIR__ . '/../../phpBB/includes/functions_install.php';

class phpbb_functional_test_case extends phpbb_test_case
{
	protected $client;
	protected $root_url;

	protected $cache = null;
	protected $db = null;
	protected $extension_manager = null;

	static protected $config = array();
	static protected $already_installed = false;

	static public function setUpBeforeClass()
	{
		if (!extension_loaded('phar'))
		{
			self::markTestSkipped('phar extension is not loaded');
		}

		require_once 'phar://' . __DIR__ . '/../../vendor/goutte.phar';
	}

	public function setUp()
	{
		if (!isset(self::$config['phpbb_functional_url']))
		{
			$this->markTestSkipped('phpbb_functional_url was not set in test_config and wasn\'t set as PHPBB_FUNCTIONAL_URL environment variable either.');
		}

		$this->client = new Goutte\Client();
		$this->root_url = self::$config['phpbb_functional_url'];
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

		if (!static::$already_installed)
		{
			$this->install_board();
			$this->bootstrap();
			static::$already_installed = true;
		}
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

		if (!$this->extension_manager)
		{
			$this->extension_manager = new phpbb_extension_manager(
				$this->get_db(),
				self::$config['table_prefix'] . 'ext',
				$phpbb_root_path,
				".$phpEx",
				$this->get_cache_driver()
			);
		}

		return $this->extension_manager;
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

		file_put_contents($phpbb_root_path . "config.$phpEx", phpbb_create_config_file_data($data, self::$config['dbms'], array(), true));

		$this->do_request('config_file', $data);

		copy($phpbb_root_path . "config.$phpEx", $phpbb_root_path . "config_test.$phpEx");

		$this->do_request('final', $data);
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
}
