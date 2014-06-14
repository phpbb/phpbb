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

class phpbb_mink_test_case extends phpbb_test_case
{
	static protected $driver;
	static protected $session;
	static protected $config = array();
	static protected $root_url;

	static public function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
	}

	public function setUp()
	{
		parent::setUp();
	}

	static protected function recreate_database($config)
	{
		$db_conn_mgr = new phpbb_database_test_connection_manager($config);
		$db_conn_mgr->recreate_db();
	}

	static protected function visit($path)
	{
		if(!isset(self::$session))
		{
			self::markTestSkipped('Session not initialized.');
		}

		self::$session->visit(self::$root_url . $path);
		return self::$session->getPage();
	}

	static protected function click_submit()
	{
		self::$session->getPage()->findById('submit')->click();
		return self::$session->getPage();
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


		$client = new \Behat\Mink\Driver\Goutte\Client();

		$client_options = array(
			Guzzle\Http\Client::DISABLE_REDIRECTS	=> true,
			'curl.options'	=> array(
				CURLOPT_TIMEOUT	=> 120,
			),
		);

		$client->setClient(new Guzzle\Http\Client('', $client_options));

		// Reset the curl handle because it is 0 at this point and not a valid
		// resource
		$client->getClient()->getCurlMulti()->reset(true);

		self::$driver = new \Behat\Mink\Driver\GoutteDriver($client);
		self::$session = new Session(self::$driver);

		$parseURL = parse_url(self::$config['phpbb_functional_url']);

		self::visit('install/index.php?mode=install');

		// install/index.php?mode=install&sub=requirements
		$page = self::click_submit();

		// install/index.php?mode=install&sub=database
		$page = self::click_submit();

		$page->findById('dbms')->setValue(str_replace('phpbb\db\driver\\', '',  self::$config['dbms']));
		$page->findById('dbhost')->setValue(self::$config['dbhost']);
		$page->findById('dbport')->setValue(self::$config['dbport']);
		$page->findById('dbname')->setValue(self::$config['dbname']);
		$page->findById('dbuser')->setValue(self::$config['dbuser']);
		$page->findById('dbpasswd')->setValue(self::$config['dbpasswd']);
		$page->findById('table_prefix')->setValue(self::$config['table_prefix']);

		$page = self::click_submit();

		$page = self::click_submit();

		$page->findById('admin_name')->setValue('admin');
		$page->findById('admin_pass1')->setValue('adminadmin');
		$page->findById('admin_pass2')->setValue('adminadmin');
		$page->findById('board_email')->setValue('nobody@example.com');

		$page = self::click_submit();

		$page = self::click_submit();

		// Installer has created a config.php file, we will overwrite it with a
		// config file of our own in order to get the DEBUG constants defined
		$config_php_data = phpbb_create_config_file_data(self::$config, self::$config['dbms'], true, false, true);
		$config_created = file_put_contents($config_file, $config_php_data) !== false;
		if (!$config_created)
		{
			self::markTestSkipped("Could not write $config_file file.");
		}

		$page = self::click_submit();

		$page->findById('smtp_delivery')->setValue('1');
		$page->findById('smtp_host')->setValue('nxdomain.phpbb.com');
		$page->findById('smtp_user')->setValue('nxuser');
		$page->findById('smtp_pass')->setValue('nxpass');
		$page->findById('server_protocol')->setValue($parseURL['scheme'] . '://');
		$page->findById('server_name')->setValue('localhost');
		$page->findById('server_port')->setValue(isset($parseURL['port']) ? $parseURL['port'] : 80);
		$page->findById('script_path')->setValue($parseURL['path']);

		$page = self::click_submit();

		$page = self::click_submit();

		copy($config_file, $config_file_test);

		/*$crawler = self::request('GET', 'install/index.php?mode=install');
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

		copy($config_file, $config_file_test);*/
	}
}
