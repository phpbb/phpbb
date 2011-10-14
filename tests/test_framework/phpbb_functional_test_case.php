<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once __DIR__ . '/../../vendor/goutte.phar';

class phpbb_functional_test_case extends phpbb_test_case
{
	protected $client;
	protected $root_url;

	static protected $config;

	public function setUp()
	{
		$this->client = new Goutte\Client();
		$this->root_url = $_SERVER['PHPBB_FUNCTIONAL_URL'];
	}

	public function request($method, $path)
	{
		return $this->client->request($method, $this->root_url . $path);
	}

	static public function setUpBeforeClass()
	{
		global $phpbb_root_path, $phpEx;

		self::$config = phpbb_test_case_helpers::get_test_config();

		if (!isset(self::$config['phpbb_functional_url']))
		{
			self::markTestSkipped('phpbb_functional_url was not set in test_config and wasn\'t set as PHPBB_FUNCTIONAL_URL environment variable either.');
		}

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
			'board_email1'	=> 'nobody@example.com',
			'board_email2'	=> 'nobody@example.com',
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
		self::assertContains('Welcome to Installation', $content);

		self::do_request('create_table', $data);

		self::do_request('config_file', $data);

		if (file_exists($phpbb_root_path . "config.$phpEx"))
		{
			copy($phpbb_root_path . "config.$phpEx", $phpbb_root_path . "config_test.$phpEx");
		}

		self::do_request('final', $data);
	}

	static public function tearDownAfterClass()
	{
		global $phpbb_root_path, $phpEx;

		copy($phpbb_root_path . "config_dev.$phpEx", $phpbb_root_path . "config.$phpEx");
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
}
