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

require_once __DIR__ . '/../../phpBB/includes/functions_install.php';

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
	}
}
