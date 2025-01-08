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
	/** @var Symfony\Component\Console\Helper\QuestionHelper */
	protected $question;
	protected $user_loader;
	protected $phpbb_root_path;
	protected $php_ext;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/config.xml');
	}

	protected function setUp(): void
	{
		global $auth, $db, $cache, $config, $user, $phpbb_dispatcher, $phpbb_container, $phpbb_root_path, $phpEx;

		$phpbb_dispatcher = new \phpbb\event\dispatcher();
		$phpbb_container = new phpbb_mock_container_builder();
		$phpbb_container->set('cache.driver', new phpbb_mock_cache());
		$phpbb_container->set('notification_manager', new phpbb_mock_notification_manager());

		$auth = $this->createMock('\phpbb\auth\auth');

		$cache = $phpbb_container->get('cache.driver');

		$config = $this->config = new \phpbb\config\config(array(
			'board_timezone'	=> 'UTC',
			'default_lang'		=> 'en',
			'email_enable'		=> false,
			'min_name_chars'	=> 3,
			'max_name_chars'	=> 10,
			'min_pass_chars'	=> 3,
			'pass_complex'		=> 'PASS_TYPE_ANY',
		));

		$db = $this->db = $this->new_dbal();

		$this->language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();
		$this->language->expects($this->any())
			->method('lang')
			->will($this->returnArgument(0));

		$user = $this->user = $this->createMock('\phpbb\user', array(), array(
			$this->language,
			'\phpbb\datetime'
		));
		$user->data['user_email'] = '';

		$avatar_helper = $this->getMockBuilder('\phpbb\avatar\helper')
			->disableOriginalConstructor()
			->getMock();

		$this->user_loader = new \phpbb\user_loader($avatar_helper, $db, $phpbb_root_path, $phpEx, USERS_TABLE);

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
		$phpbb_container->setParameter('tables.auth_provider_oauth_token_storage', 'phpbb_oauth_tokens');
		$phpbb_container->setParameter('tables.auth_provider_oauth_states', 'phpbb_oauth_states');
		$phpbb_container->setParameter('tables.auth_provider_oauth_account_assoc', 'phpbb_oauth_accounts');

		$phpbb_container->setParameter('tables.user_notifications', 'phpbb_user_notifications');

		$assets_bag = new \phpbb\template\assets_bag();
		$phpbb_container->set('assets.bag', $assets_bag);

		$phpbb_container->set('dispatcher', $phpbb_dispatcher);

		$core_cache_dir = $phpbb_root_path . 'cache/' . PHPBB_ENVIRONMENT . '/';
		$phpbb_container->setParameter('core.cache_dir', $core_cache_dir);

		$core_messenger_queue_file = $core_cache_dir . 'queue.' . $phpEx;
		$phpbb_container->setParameter('core.messenger_queue_file', $core_messenger_queue_file);

		$messenger_method_collection = new \phpbb\di\service_collection($phpbb_container);
		$messenger_method_collection->add('messenger.method.email');
		$phpbb_container->set('messenger.method_collection', $messenger_method_collection);

		$messenger_queue = new \phpbb\messenger\queue($config, $phpbb_dispatcher, $messenger_method_collection, $core_messenger_queue_file);
		$phpbb_container->set('messenger.queue', $messenger_queue);

		$request = new phpbb_mock_request;
		$phpbb_container->set('request', $request);

		$symfony_request = new \phpbb\symfony_request(
			$request
		);

		$phpbb_path_helper = new \phpbb\path_helper(
			$symfony_request,
			$request,
			$phpbb_root_path,
			$phpEx
		);
		$phpbb_container->set('path_helper', $phpbb_path_helper);

		$factory = new \phpbb\db\tools\factory();
		$db_doctrine = $this->new_doctrine_dbal();
		$db_tools = $factory->get($db_doctrine);
		$migrator = new \phpbb\db\migrator(
			$phpbb_container,
			$config,
			$db,
			$db_tools,
			'phpbb_migrations',
			$phpbb_root_path,
			$this->php_ext,
			'phpbb_',
			self::get_core_tables(),
			[],
			new \phpbb\db\migration\helper()
		);
		$phpbb_container->set('migrator', $migrator);

		$finder_factory = new \phpbb\finder\factory(null, false, $phpbb_root_path, $this->php_ext);
		$extension_manager = new \phpbb\extension\manager(
			$phpbb_container,
			$db,
			$config,
			$finder_factory,
			'phpbb_ext',
			__DIR__ . '/',
			new \phpbb\cache\service(new phpbb_mock_cache(), $config, $db, $phpbb_dispatcher, $phpbb_root_path, $this->php_ext)
		);
		$phpbb_container->set('ext.manager', $extension_manager);

		$context = new \phpbb\template\context();
		$cache_path = $phpbb_root_path . 'cache/' . PHPBB_ENVIRONMENT . '/twig';
		$phpbb_container->setParameter('core.template.cache_path', $cache_path);
		$filesystem = new \phpbb\filesystem\filesystem();
		$phpbb_container->set('filesystem', $filesystem);

		$twig = new \phpbb\template\twig\environment(
			$assets_bag,
			$this->config,
			$filesystem,
			$phpbb_path_helper,
			$cache_path,
			null,
			new \phpbb\template\twig\loader(''),
			$phpbb_dispatcher,
			[
				'cache'			=> false,
				'debug'			=> false,
				'auto_reload'	=> true,
				'autoescape'	=> false,
			]
		);
		$twig_extension = new \phpbb\template\twig\extension($context, $twig, $this->language);
		$phpbb_container->set('template.twig.extensions.phpbb', $twig_extension);

		$twig_extensions_collection = new \phpbb\di\service_collection($phpbb_container);
		$twig_extensions_collection->add('template.twig.extensions.phpbb');
		$phpbb_container->set('template.twig.extensions.collection', $twig_extensions_collection);

		$twig->addExtension($twig_extension);
		$twig_lexer = new \phpbb\template\twig\lexer($twig);
		$phpbb_container->set('template.twig.lexer', $twig_lexer);

		$this->email = new \phpbb\messenger\method\email(
			$assets_bag,
			$this->config,
			$phpbb_dispatcher,
			$this->language,
			$messenger_queue,
			$phpbb_path_helper,
			$request,
			$twig_extensions_collection,
			$twig_lexer,
			$user,
			$phpbb_root_path,
			$cache_path,
			$extension_manager,
			$this->log
		);
		$phpbb_container->set('messenger.method.email', $this->email);

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

		$user_id = $row ? $row['user_id'] : null;
		return $user_id;
	}
}
