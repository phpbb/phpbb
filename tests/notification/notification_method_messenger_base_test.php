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

class phpbb_notification_method_messenger_base_test extends phpbb_database_test_case
{
	/** @var \phpbb\notification\method\email */
	protected $notification_method_email;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/messenger_base.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		global $phpbb_container, $config, $db, $phpbb_root_path, $phpEx, $request, $user, $phpbb_dispatcher;

		$db = $this->new_dbal();
		$phpbb_container = new phpbb_mock_container_builder;
		$phpbb_dispatcher = new \phpbb\event\dispatcher();

		$config = new \phpbb\config\config(array(
			'board_email_sig'	=> '-- Thanks, The Management',
			'sitename'			=> 'yourdomain.com',
			'default_lang'		=> 'en',
		));
		$phpbb_container->set('config', $config);

		$request = new phpbb_mock_request;
		$symfony_request = new \phpbb\symfony_request(
			$request
		);
		$filesystem = new \phpbb\filesystem\filesystem();
		$phpbb_path_helper = new \phpbb\path_helper(
			$symfony_request,
			$filesystem,
			$request,
			$phpbb_root_path,
			$phpEx
		);
		$phpbb_container->set('path_helper', $phpbb_path_helper);
		$phpbb_container->set('filesystem', $filesystem);

		$cache_path = $phpbb_root_path . 'cache/' . PHPBB_ENVIRONMENT . '/twig';
		$phpbb_container->setParameter('core.template.cache_path', $cache_path);

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$user->data['user_lang'] = 'en';
		$phpbb_container->set('user', $user);
		$phpbb_container->set('ext.manager', new phpbb_mock_extension_manager($phpbb_root_path, array()));

		$context = new \phpbb\template\context();
		$twig = new \phpbb\template\twig\environment(
			$config,
			$filesystem,
			$phpbb_path_helper,
			$cache_path,
			null,
			new \phpbb\template\twig\loader($filesystem, ''),
			$phpbb_dispatcher,
			array(
				'cache'			=> false,
				'debug'			=> false,
				'auto_reload'	=> true,
				'autoescape'	=> false,
			)
		);
		$twig_extension = new \phpbb\template\twig\extension($context, $twig, $lang);
		$phpbb_container->set('template.twig.extensions.phpbb', $twig_extension);

		$twig_extensions_collection = new \phpbb\di\service_collection($phpbb_container);
		$twig_extensions_collection->add('template.twig.extensions.phpbb');
		$phpbb_container->set('template.twig.extensions.collection', $twig_extensions_collection);

		$twig->addExtension($twig_extension);
		$phpbb_container->set('template.twig.lexer', new \phpbb\template\twig\lexer($twig));

		$user_loader = new \phpbb\user_loader($db, $phpbb_root_path, $phpEx, 'phpbb_users');

		$this->notification_method_email = new \phpbb\notification\method\email(
			$user_loader,
			$user,
			$config,
			$db,
			$phpbb_root_path,
			$phpEx,
			'phpbb_notification_emails'
		);
	}

	public function test_modify_template_vars_event()
	{
		global $phpbb_dispatcher;

		$notification = $this->getMockForAbstractClass(
			'\phpbb\notification\type\base',
			array(),
			'',
			false,
			true,
			true,
			array('get_email_template', 'get_email_template_variables')
		);
		$notification->method('get_email_template')
			->willReturn('newtopic_notify');
		$notification->method('get_email_template_variables')
			->willReturn(array('TOPIC_TITLE' => 'Some topic'));
		$notification->set_initial_data(array('user_id' => 2));

		$event_data = array();
		$phpbb_dispatcher->addListener('core.notification_method_messenger_modify_template_vars', function ($event) use (&$event_data)
		{
			$event_data = array(
				'notification'	=> $event['notification'],
				'user'			=> $event['user'],
				'template_vars'	=> $event['template_vars'],
			);

			$template_vars = $event['template_vars'];
			$template_vars['EXTENSION_VAR'] = 'Added by an extension';
			$event['template_vars'] = $template_vars;
		});

		$assigned_vars = array();
		$phpbb_dispatcher->addListener('core.modify_notification_template', function ($event) use (&$assigned_vars)
		{
			$assigned_vars = $event['template']->retrieve_vars(array('USERNAME', 'TOPIC_TITLE', 'EXTENSION_VAR'));
		});

		$this->notification_method_email->add_to_queue($notification);

		$notify_using_messenger = new ReflectionMethod($this->notification_method_email, 'notify_using_messenger');
		$notify_using_messenger->setAccessible(true);
		$notify_using_messenger->invoke($this->notification_method_email, NOTIFY_EMAIL);

		$this->assertSame($notification, $event_data['notification']);
		$this->assertEquals(2, $event_data['user']['user_id']);
		$this->assertSame('Recipient', $event_data['template_vars']['USERNAME']);
		$this->assertSame('Some topic', $event_data['template_vars']['TOPIC_TITLE']);
		$this->assertArrayHasKey('U_NOTIFICATION_SETTINGS', $event_data['template_vars']);

		$this->assertSame(array(
			'USERNAME'		=> 'Recipient',
			'TOPIC_TITLE'	=> 'Some topic',
			'EXTENSION_VAR'	=> 'Added by an extension',
		), $assigned_vars);
	}
}
