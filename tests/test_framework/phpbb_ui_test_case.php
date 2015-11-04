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

require_once __DIR__ . '/mock/phpbb_mock_null_installer_task.php';

class phpbb_ui_test_case extends phpbb_test_case
{
	static protected $host = '127.0.0.1';
	static protected $port = 8910;

	/**
	* @var \RemoteWebDriver
	*/
	static protected $webDriver;

	static protected $config;
	static protected $root_url;
	static protected $already_installed = false;

	static public function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		if (version_compare(PHP_VERSION, '5.3.19', '<'))
		{
			self::markTestSkipped('UI test case requires at least PHP 5.3.19.');
		}
		else if (!class_exists('\RemoteWebDriver'))
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
				$capabilities = array(\WebDriverCapabilityType::BROWSER_NAME => 'firefox');
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

	static public function install_board()
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

		$container_builder = new \phpbb\di\container_builder($phpbb_root_path, $phpEx);
		$container = $container_builder
			->with_environment('installer')
			->without_extensions()
			->without_cache()
			->with_custom_parameters([
				'core.disable_super_globals' => false,
				'installer.create_config_file.options' => [
					'debug' => true,
					'environment' => 'test',
				],
				'cache.driver.class' => 'phpbb\cache\driver\file'
			])
			->without_compiled_container()
			->get_container();

		$container->register('installer.install_finish.notify_user')->setSynthetic(true);
		$container->set('installer.install_finish.notify_user', new phpbb_mock_null_installer_task());
		$container->compile();

		$language = $container->get('language');
		$language->add_lang(array('common', 'acp/common', 'acp/board', 'install', 'posting'));

		$iohandler_factory = $container->get('installer.helper.iohandler_factory');
		$iohandler_factory->set_environment('cli');
		$iohandler = $iohandler_factory->get();

		$parseURL = parse_url(self::$config['phpbb_functional_url']);

		$output = new \Symfony\Component\Console\Output\NullOutput();
		$style = new \Symfony\Component\Console\Style\SymfonyStyle(
			new \Symfony\Component\Console\Input\ArrayInput(array()),
			$output
		);
		$iohandler->set_style($style, $output);

		$installer = $container->get('installer.installer.install');
		$installer->set_iohandler($iohandler);

		// Set data
		$iohandler->set_input('admin_name', 'admin');
		$iohandler->set_input('admin_pass1', 'adminadmin');
		$iohandler->set_input('admin_pass2', 'adminadmin');
		$iohandler->set_input('board_email', 'nobody@example.com');
		$iohandler->set_input('submit_admin', 'submit');

		$iohandler->set_input('default_lang', 'en');
		$iohandler->set_input('board_name', 'yourdomain.com');
		$iohandler->set_input('board_description', 'A short text to describe your forum');
		$iohandler->set_input('submit_board', 'submit');

		$iohandler->set_input('dbms', str_replace('phpbb\db\driver\\', '',  self::$config['dbms']));
		$iohandler->set_input('dbhost', self::$config['dbhost']);
		$iohandler->set_input('dbport', self::$config['dbport']);
		$iohandler->set_input('dbuser', self::$config['dbuser']);
		$iohandler->set_input('dbpasswd', self::$config['dbpasswd']);
		$iohandler->set_input('dbname', self::$config['dbname']);
		$iohandler->set_input('table_prefix', self::$config['table_prefix']);
		$iohandler->set_input('submit_database', 'submit');

		$iohandler->set_input('email_enable', true);
		$iohandler->set_input('smtp_delivery', '1');
		$iohandler->set_input('smtp_host', 'nxdomain.phpbb.com');
		$iohandler->set_input('smtp_auth', 'PLAIN');
		$iohandler->set_input('smtp_user', 'nxuser');
		$iohandler->set_input('smtp_pass', 'nxpass');
		$iohandler->set_input('submit_email', 'submit');

		$iohandler->set_input('cookie_secure', '0');
		$iohandler->set_input('server_protocol', '0');
		$iohandler->set_input('force_server_vars', $parseURL['scheme'] . '://');
		$iohandler->set_input('server_name', $parseURL['host']);
		$iohandler->set_input('server_port', isset($parseURL['port']) ? (int) $parseURL['port'] : 80);
		$iohandler->set_input('script_path', $parseURL['path']);
		$iohandler->set_input('submit_server', 'submit');

		do
		{
			$installer->run();
		}
		while (file_exists($phpbb_root_path . 'store/install_config.php'));

		copy($config_file, $config_file_test);

		if (file_exists($phpbb_root_path . 'cache/install_lock'))
		{
			unlink($phpbb_root_path . 'cache/install_lock');
		}

		global $phpbb_container, $cache, $phpbb_dispatcher, $request, $user, $auth, $db, $config, $phpbb_log, $symfony_request, $phpbb_filesystem, $phpbb_path_helper, $phpbb_extension_manager, $template;
		$phpbb_container->reset();
		unset($phpbb_container, $cache, $phpbb_dispatcher, $request, $user, $auth, $db, $config, $phpbb_log, $symfony_request, $phpbb_filesystem, $phpbb_path_helper, $phpbb_extension_manager, $template);
	}
}
