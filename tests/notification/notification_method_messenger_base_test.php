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

	/** @var array Template variables passed to the messenger method */
	protected $assigned_vars = [];

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/messenger_base.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		global $config, $db, $phpbb_container, $phpbb_root_path, $phpEx, $user, $phpbb_dispatcher;

		$db = $this->new_dbal();
		$phpbb_dispatcher = new \phpbb\event\dispatcher();

		$config = new \phpbb\config\config([
			'force_server_vars'	=> true,
			'server_protocol'	=> 'http://',
			'server_name'		=> 'yourdomain.com',
			'server_port'		=> 80,
			'script_path'		=> '/phpbb',
		]);

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');

		$messenger_method = $this->getMockBuilder('\phpbb\messenger\method\email')
			->disableOriginalConstructor()
			->onlyMethods(['template', 'set_addresses', 'assign_vars', 'send', 'save_queue'])
			->getMock();
		$messenger_method->method('assign_vars')
			->willReturnCallback(function (array $vars) {
				$this->assigned_vars = $vars;
			});
		$messenger_method->method('send')
			->willReturn(true);

		$ban_manager = $this->getMockBuilder('\phpbb\ban\manager')
			->disableOriginalConstructor()
			->onlyMethods(['get_banned_users'])
			->getMock();
		$ban_manager->method('get_banned_users')
			->willReturn([]);

		$phpbb_container = new phpbb_mock_container_builder;
		$phpbb_container->set('ban.manager', $ban_manager);
		$phpbb_container->set('messenger.method.email', $messenger_method);
		$messenger_method_collection = new \phpbb\di\service_collection($phpbb_container);
		$messenger_method_collection->add('messenger.method.email');

		$avatar_helper = $this->getMockBuilder('\phpbb\avatar\helper')
			->disableOriginalConstructor()
			->getMock();
		$user_loader = new \phpbb\user_loader($avatar_helper, $db, $phpbb_root_path, $phpEx, 'phpbb_users');

		$this->notification_method_email = new \phpbb\notification\method\email(
			$user_loader,
			$user,
			$config,
			$db,
			$phpbb_root_path,
			$phpEx,
			'phpbb_notification_emails',
			$messenger_method_collection
		);
	}

	public function test_modify_template_vars_event()
	{
		global $phpbb_dispatcher;

		$notification = $this->getMockBuilder('\phpbb\notification\type\post')
			->disableOriginalConstructor()
			->onlyMethods(['get_email_template', 'get_email_template_variables'])
			->getMock();
		$notification->method('get_email_template')
			->willReturn('newtopic_notify');
		$notification->method('get_email_template_variables')
			->willReturn(['TOPIC_TITLE' => 'Some topic']);
		$notification->set_initial_data(['user_id' => 2]);

		$event_data = [];
		$phpbb_dispatcher->addListener('core.notification_method_messenger_modify_template_vars', function ($event) use (&$event_data)
		{
			$event_data = [
				'notification'	=> $event['notification'],
				'user'			=> $event['user'],
				'template_vars'	=> $event['template_vars'],
			];

			$template_vars = $event['template_vars'];
			$template_vars['EXTENSION_VAR'] = 'Added by an extension';
			$event['template_vars'] = $template_vars;
		});

		$this->notification_method_email->add_to_queue($notification);

		$notify_using_messenger = new ReflectionMethod($this->notification_method_email, 'notify_using_messenger');
		$notify_using_messenger->setAccessible(true);
		$notify_using_messenger->invoke($this->notification_method_email, '');

		$this->assertSame($notification, $event_data['notification']);
		$this->assertEquals(2, $event_data['user']['user_id']);
		$this->assertSame('Recipient', $event_data['template_vars']['USERNAME']);
		$this->assertSame('Some topic', $event_data['template_vars']['TOPIC_TITLE']);
		$this->assertArrayHasKey('U_NOTIFICATION_SETTINGS', $event_data['template_vars']);

		$this->assertSame('Recipient', $this->assigned_vars['USERNAME']);
		$this->assertSame('Some topic', $this->assigned_vars['TOPIC_TITLE']);
		$this->assertSame('Added by an extension', $this->assigned_vars['EXTENSION_VAR']);
	}
}
