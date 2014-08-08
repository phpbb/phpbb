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
use \Behat\Mink\Session;

require_once __DIR__ . '/../../phpBB/includes/functions_install.php';

abstract class phpbb_mink_test_case extends phpbb_test_case
{
	static protected $driver;
	static protected $client;
	static protected $session;
	static protected $config = array();
	static protected $root_url;

	public function __construct($name = null, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);

		$this->backupStaticAttributesBlacklist += array(
			'phpbb_mink_test_case' => array('config', 'already_installed'),
		);
	}

	static public function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
	}

	public function setUp()
	{
		parent::setUp();

		if (!self::$driver)
		{
			self::markTestSkipped('Mink driver not initialized.');
		}

		if (!self::$session)
		{
			self::$session = new Session(self::$driver);
		}
	}

	static protected function recreate_database($config)
	{
		$db_conn_mgr = new phpbb_database_test_connection_manager($config);
		$db_conn_mgr->recreate_db();
	}

	protected function tearDown()
	{
		self::$session->reset();
		parent::tearDown();
	}

	static protected function visit($path)
	{
		self::$session->visit(self::$root_url . $path);
		self::assertEquals(self::$session->getStatusCode(), '200');
		return self::$session->getPage();
	}

	static protected function click_submit($submit_button_id = 'submit')
	{
		self::$session->getPage()->findById($submit_button_id)->click();
		self::assertEquals(self::$session->getStatusCode(), '200');
		return self::$session->getPage();
	}

	static protected function install_board()
	{
		global $phpbb_root_path, $phpEx;

		self::recreate_database(self::$config);
		self::$session = new Session(self::$driver);

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

		$page = self::visit('install/index.php?mode=install');
		self::assertContains('Welcome to Installation', $page->findById('main')->getText());

		// install/index.php?mode=install&sub=requirements
		$page = self::click_submit();
		self::assertContains('Installation compatibility', $page->findById('main')->getText());

		// install/index.php?mode=install&sub=database
		$page = self::click_submit();
		self::assertContains('Database configuration', $page->findById('main')->getText());

		$page->findById('dbms')->setValue(str_replace('phpbb\db\driver\\', '',  self::$config['dbms']));
		$page->findById('dbhost')->setValue(self::$config['dbhost']);
		$page->findById('dbport')->setValue(self::$config['dbport']);
		$page->findById('dbname')->setValue(self::$config['dbname']);
		$page->findById('dbuser')->setValue(self::$config['dbuser']);
		$page->findById('dbpasswd')->setValue(self::$config['dbpasswd']);
		$page->findById('table_prefix')->setValue(self::$config['table_prefix']);

		// install/index.php?mode=install&sub=database
		$page = self::click_submit();
		self::assertContains('Successful connection', $page->findById('main')->getText());

		// install/index.php?mode=install&sub=administrator
		$page = self::click_submit();
		self::assertContains('Administrator configuration', $page->findById('main')->getText());

		$page->findById('admin_name')->setValue('admin');
		$page->findById('admin_pass1')->setValue('adminadmin');
		$page->findById('admin_pass2')->setValue('adminadmin');
		$page->findById('board_email')->setValue('nobody@example.com');

		// install/index.php?mode=install&sub=administrator
		$page = self::click_submit();
		self::assertContains('Tests passed', $page->findById('main')->getText());

		// install/index.php?mode=install&sub=config_file
		$page = self::click_submit();

		// Installer has created a config.php file, we will overwrite it with a
		// config file of our own in order to get the DEBUG constants defined
		$config_php_data = phpbb_create_config_file_data(self::$config, self::$config['dbms'], true, false, true);
		$config_created = file_put_contents($config_file, $config_php_data) !== false;
		if (!$config_created)
		{
			self::markTestSkipped("Could not write $config_file file.");
		}

		if (strpos($page->findById('main')->getText(), 'The configuration file has been written') === false)
		{
			$page = self::click_submit('dldone');
		}
		self::assertContains('The configuration file has been written', $page->findById('main')->getText());

		// install/index.php?mode=install&sub=advanced
		$page = self::click_submit();
		self::assertContains('The settings on this page are only necessary to set if you know that you require something different from the default.', $page->findById('main')->getText());

		$page->findById('smtp_delivery')->setValue('1');
		$page->findById('smtp_host')->setValue('nxdomain.phpbb.com');
		$page->findById('smtp_user')->setValue('nxuser');
		$page->findById('smtp_pass')->setValue('nxpass');
		$page->findById('server_protocol')->setValue($parseURL['scheme'] . '://');
		$page->findById('server_name')->setValue('localhost');
		$page->findById('server_port')->setValue(isset($parseURL['port']) ? $parseURL['port'] : 80);
		$page->findById('script_path')->setValue($parseURL['path']);

		// install/index.php?mode=install&sub=create_table
		$page = self::click_submit();
		self::assertContains('The database tables used by phpBB', $page->findById('main')->getText());
		self::assertContains('have been created and populated with some initial data.', $page->findById('main')->getText());

		// install/index.php?mode=install&sub=final
		$page = self::click_submit();
		self::assertContains('You have successfully installed', $page->getText());

		copy($config_file, $config_file_test);

		self::$session->stop();
	}
}
