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

abstract class phpbb_console_user_base extends phpbb_database_test_case
{
	protected $db;
	protected $config;
	protected $user;
	protected $language;
	protected $log;
	protected $passwords_manager;
	protected $command_name;
	protected $question;
	protected $user_loader;
	protected $phpbb_root_path;
	protected $php_ext;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/config.xml');
	}

	public function setUp()
	{
		global $auth, $db, $cache, $config, $user, $phpbb_dispatcher, $phpbb_container, $phpbb_root_path, $phpEx;

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$phpbb_container = new phpbb_mock_container_builder();
		$phpbb_container->set('cache.driver', new phpbb_mock_cache());
		$phpbb_container->set('notification_manager', new phpbb_mock_notification_manager());

		$auth = $this->getMock('\phpbb\auth\auth');

		$cache = $phpbb_container->get('cache.driver');

		$config = $this->config = new \phpbb\config\config(array(
			'board_timezone'	=> 'UTC',
			'default_lang'		=> 'en',
			'email_enable'		=> false,
			'min_name_chars'	=> 3,
			'max_name_chars'	=> 10,
			'min_pass_chars'	=> 3,
			'max_pass_chars'	=> 10,
			'pass_complex'		=> 'PASS_TYPE_ANY',
		));

		$db = $this->db = $this->new_dbal();

		$this->language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();
		$this->language->expects($this->any())
			->method('lang')
			->will($this->returnArgument(0));
		$user = $this->user = $this->getMock('\phpbb\user', array(), array(
			$this->language,
			'\phpbb\datetime'
		));

		$this->user_loader = new \phpbb\user_loader($db, $phpbb_root_path, $phpEx, USERS_TABLE);

		$driver_helper = new \phpbb\passwords\driver\helper($this->config);
		$passwords_drivers = array(
			'passwords.driver.bcrypt_2y'	=> new \phpbb\passwords\driver\bcrypt_2y($this->config, $driver_helper),
			'passwords.driver.bcrypt'		=> new \phpbb\passwords\driver\bcrypt($this->config, $driver_helper),
			'passwords.driver.salted_md5'	=> new \phpbb\passwords\driver\salted_md5($this->config, $driver_helper),
			'passwords.driver.phpass'		=> new \phpbb\passwords\driver\phpass($this->config, $driver_helper),
		);

		$passwords_helper = new \phpbb\passwords\helper;
		$this->passwords_manager = new \phpbb\passwords\manager($this->config, $passwords_drivers, $passwords_helper, array_keys($passwords_drivers));

		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;

		$this->log = $this->getMockBuilder('\phpbb\log\log')
			->disableOriginalConstructor()
			->getMock();

		$phpbb_container->set('auth.provider.db', new phpbb_mock_auth_provider());
		$provider_collection = new \phpbb\auth\provider_collection($phpbb_container, $config);
		$provider_collection->add('auth.provider.db');
		$phpbb_container->set(
			'auth.provider_collection',
			$provider_collection
		);

		parent::setUp();
	}

	public function get_user_id($username)
	{
		$sql = 'SELECT user_id
			FROM ' . USERS_TABLE . '
			WHERE ' . 'username = ' . "'" . $username . "'";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row['user_id'];
	}

	public function getInputStream($input)
	{
		$stream = fopen('php://memory', 'r+', false);
		fputs($stream, $input);
		rewind($stream);

		return $stream;
	}
}
