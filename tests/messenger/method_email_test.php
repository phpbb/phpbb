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

use phpbb\config\config;
use phpbb\language\language;
use phpbb\language\language_file_loader;
use phpbb\messenger\method\email;
use phpbb\messenger\queue;
use phpbb\path_helper;
use phpbb\symfony_request;
use phpbb\template\assets_bag;
use Symfony\Component\Mime\RawMessage;

class phpbb_messenger_method_email_test extends \phpbb_test_case
{
	protected $assets_bag;
	protected $cache_path;
	protected config $config;
	protected $dispatcher;
	protected $extension_manager;
	protected email $method_email;
	protected $language;
	protected $log;
	protected $path_helper;
	protected queue $queue;
	protected $request;
	protected $twig_extensions_collection;
	protected $twig_lexer;
	protected $user;

	public function setUp(): void
	{
		global $config, $request, $symfony_request, $user, $phpbb_root_path, $phpEx;

		$this->assets_bag = new assets_bag();
		$this->cache_path = $phpbb_root_path . 'cache/' . PHPBB_ENVIRONMENT . '/twig';
		$this->config = new config([
			'force_server_vars' => false,
		]);
		$config = $this->config;
		$this->dispatcher = $this->getMockBuilder('\phpbb\event\dispatcher')
			->disableOriginalConstructor()
			->getMock();
		$this->filesystem = new \phpbb\filesystem\filesystem();
		$this->language = new language(new language_file_loader($phpbb_root_path, $phpEx));
		$this->queue = $this->createMock(queue::class);
		$this->request = new phpbb_mock_request();
		$request = $this->request;
		$this->symfony_request = new symfony_request(new phpbb_mock_request());
		$symfony_request = $this->symfony_request;
		$this->user = new \phpbb\user($this->language, '\phpbb\datetime');
		$user = $this->user;
		$user->page['root_script_path'] = 'phpbb/';
		$this->user->host = 'yourdomain.com';
		$this->path_helper = new path_helper(
			$this->symfony_request,
			$this->request,
			$phpbb_root_path,
			$phpEx
		);
		$phpbb_container = new phpbb_mock_container_builder;
		$this->twig_extensions_collection = new \phpbb\di\service_collection($phpbb_container);
		$twig = new \phpbb\template\twig\environment(
			$this->assets_bag,
			$this->config,
			$this->filesystem,
			$this->path_helper,
			$this->cache_path,
			null,
			new \phpbb\template\twig\loader(''),
			$this->dispatcher,
			array(
				'cache'			=> false,
				'debug'			=> false,
				'auto_reload'	=> true,
				'autoescape'	=> false,
			)
		);
		$this->twig_lexer = new \phpbb\template\twig\lexer($twig);
		$this->extension_manager = new phpbb_mock_extension_manager(
			__DIR__ . '/',
			array(
				'vendor2/foo' => array(
					'ext_name' => 'vendor2/foo',
					'ext_active' => '1',
					'ext_path' => 'ext/vendor2/foo/',
				),
			)
		);
		$this->log = $this->createMock(\phpbb\log\log_interface::class);

		$this->method_email = new email(
			$this->assets_bag,
			$this->config,
			$this->dispatcher,
			$this->language,
			$this->queue,
			$this->path_helper,
			$this->request,
			$this->twig_extensions_collection,
			$this->twig_lexer,
			$this->user,
			$phpbb_root_path,
			$this->cache_path,
			$this->extension_manager,
			$this->log
		);
	}

	public function test_miscellaneous(): void
	{
		$this->assertEquals('email', $this->method_email->get_queue_object_name());
		$this->assertFalse($this->method_email->is_enabled());
		$this->config->offsetSet('email_enable', true);
		$this->assertTrue($this->method_email->is_enabled());
	}

	public function test_set_dsn_from_config()
	{
		$config_values = [
			'smtp_delivery'		=> true,
			'smtp_host'			=> 'smtp.example.com',
			'smtp_username'		=> 'user',
			'smtp_password'		=> 'pass',
			'smtp_port'			=> 587,
		];
		foreach ($config_values as $key => $value)
		{
			$this->config->set($key, $value);
		}

		$this->method_email->set_dsn();
		$this->assertEquals('smtp://user:pass@smtp.example.com:587', $this->method_email->get_dsn());

		$this->config->set('smtp_host', '');
		$this->method_email->set_dsn();
		$this->assertEquals('null://null', $this->method_email->get_dsn());
	}

	public function test_set_dns()
	{
		$this->assertEquals('', $this->method_email->get_dsn());
		$this->method_email->set_dsn('');
		$this->assertEquals('sendmail://default', $this->method_email->get_dsn());

		$this->method_email->set_dsn('smtp://user:pass1@smtp.example.com:587');
		$this->assertEquals('smtp://user:pass1@smtp.example.com:587', $this->method_email->get_dsn());
	}

	public function test_set_transport()
	{
		$this->assertEmpty($this->method_email->get_dsn());

		$config_values = [
			'smtp_delivery'				=> true,
			'smtp_host'					=> 'smtp.example.com',
			'smtp_username'				=> 'user',
			'smtp_password'				=> 'pass',
			'smtp_port'					=> 587,
			'smtp_verify_peer'			=> true,
			'smtp_verify_peer_name'		=> true,
			'smtp_allow_self_signed'	=> false,
		];
		foreach ($config_values as $key => $value)
		{
			$this->config->set($key, $value);
		}

		$this->method_email->set_transport();

		// set_dsn() should have been called in set_transport()
		$this->assertEquals('smtp://user:pass@smtp.example.com:587', $this->method_email->get_dsn());

		$transport = $this->method_email->get_transport();
		$this->assertInstanceOf('\Symfony\Component\Mailer\Transport\Smtp\SmtpTransport', $transport);
		if (method_exists($transport->getStream(), 'getStreamOptions'))
		{
			$this->assertEquals([
				'verify_peer'		=> true,
				'verify_peer_name'	=> true,
				'allow_self_signed'	=> false,
			], $transport->getStream()->getStreamOptions()['ssl'] ?? null);
		}
	}

	public function test_init()
	{
		$this->config->set('email_package_size', 100);
		$email_reflection = new \ReflectionClass($this->method_email);
		$email_property = $email_reflection->getProperty('email');

		$use_queue_property = $email_reflection->getProperty('use_queue');
		$this->assertFalse($use_queue_property->getValue($this->method_email));

		$this->method_email->init();
		/** @var \Symfony\Component\Mime\Email $email */
		$email = $email_property->getValue($this->method_email);
		$this->assertNotNull($email);

		$this->assertTrue($use_queue_property->getValue($this->method_email));
		$this->assertEmpty($email->getTo());

		$this->method_email->to('foo@bar.com');
		$this->assertNotEmpty($email->getTo());

		$this->method_email->init();

		$email = $email_property->getValue($this->method_email);
		$this->assertNotNull($email);
		$this->assertEmpty($email->getTo());
	}

	public function test_get_mailer()
	{
		$email_reflection = new \ReflectionClass($this->method_email);
		$this->method_email->init();
		$this->method_email->set_transport();
		$mailer_method = $email_reflection->getMethod('get_mailer');

		$mailer = $mailer_method->invoke($this->method_email);
		$this->assertInstanceOf(\Symfony\Component\Mailer\Mailer::class, $mailer);
	}

	public function test_set_addresses()
	{
		$email_reflection = new \ReflectionClass($this->method_email);
		$email_property = $email_reflection->getProperty('email');
		$this->method_email->init();
		/** @var \Symfony\Component\Mime\Email $email */
		$email = $email_property->getValue($this->method_email);
		$this->assertNotNull($email);

		$this->method_email->set_addresses([]);
		$this->assertEmpty($email->getTo());

		$this->method_email->set_addresses(['user_email' => 'foo@bar.com']);
		$this->assertNotEmpty($email->getTo());
		$this->assertEquals('foo@bar.com', $email->getTo()[0]->getAddress());
		$this->assertEmpty($email->getTo()[0]->getName());

		$this->method_email->set_addresses(['user_email' => 'bar@foo.com', 'username' => 'Bar Foo']);

		$this->assertEquals('bar@foo.com', $email->getTo()[1]->getAddress());
		$this->assertEquals('Bar Foo', $email->getTo()[1]->getName());
	}

	public function test_to()
	{
		$email_reflection = new \ReflectionClass($this->method_email);
		$email_property = $email_reflection->getProperty('email');
		$this->method_email->init();
		/** @var \Symfony\Component\Mime\Email $email */
		$email = $email_property->getValue($this->method_email);
		$this->assertNotNull($email);

		// Empty address
		$this->assertEmpty($email->getTo());
		$this->method_email->to('');
		$this->assertEmpty($email->getTo());

		// Valid address
		$this->method_email->to('foo@bar.com');
		$this->assertNotEmpty($email->getTo());
		$this->assertEquals('foo@bar.com', $email->getTo()[0]->getAddress());
		$this->assertEmpty($email->getTo()[0]->getName());

		// Valid address with name
		$this->method_email->to('bar@foo.com', 'Bar Foo');
		$this->assertEquals('bar@foo.com', $email->getTo()[1]->getAddress());
		$this->assertEquals('Bar Foo', $email->getTo()[1]->getName());
	}

	public function test_cc()
	{
		$email_reflection = new \ReflectionClass($this->method_email);
		$email_property = $email_reflection->getProperty('email');
		$this->method_email->init();
		/** @var \Symfony\Component\Mime\Email $email */
		$email = $email_property->getValue($this->method_email);
		$this->assertNotNull($email);

		// Empty address
		$this->assertEmpty($email->getCc());
		$this->method_email->cc('');
		$this->assertEmpty($email->getCc());

		// Valid address
		$this->method_email->cc('foo@bar.com');
		$this->assertNotEmpty($email->getCc());
		$this->assertEquals('foo@bar.com', $email->getCc()[0]->getAddress());
		$this->assertEmpty($email->getCc()[0]->getName());

		// Valid address with name
		$this->method_email->cc('bar@foo.com', 'Bar Foo');
		$this->assertEquals('bar@foo.com', $email->getCc()[1]->getAddress());
		$this->assertEquals('Bar Foo', $email->getCc()[1]->getName());
	}

	public function test_bcc()
	{
		$email_reflection = new \ReflectionClass($this->method_email);
		$email_property = $email_reflection->getProperty('email');
		$this->method_email->init();
		/** @var \Symfony\Component\Mime\Email $email */
		$email = $email_property->getValue($this->method_email);
		$this->assertNotNull($email);

		// Empty address
		$this->assertEmpty($email->getBcc());
		$this->method_email->bcc('');
		$this->assertEmpty($email->getBcc());

		// Valid address
		$this->method_email->bcc('foo@bar.com');
		$this->assertNotEmpty($email->getBcc());
		$this->assertEquals('foo@bar.com', $email->getBcc()[0]->getAddress());
		$this->assertEmpty($email->getBcc()[0]->getName());

		// Valid address with name
		$this->method_email->bcc('bar@foo.com', 'Bar Foo');
		$this->assertEquals('bar@foo.com', $email->getBcc()[1]->getAddress());
		$this->assertEquals('Bar Foo', $email->getBcc()[1]->getName());
	}

	public function test_reply_to()
	{
		$email_reflection = new \ReflectionClass($this->method_email);
		$email_property = $email_reflection->getProperty('email');
		$this->method_email->init();
		/** @var \Symfony\Component\Mime\Email $email */
		$email = $email_property->getValue($this->method_email);
		$this->assertNotNull($email);

		// Empty address
		$this->assertEmpty($email->getReplyTo());
		$this->method_email->reply_to('');
		$this->assertEmpty($email->getReplyTo());

		// Valid address
		$this->method_email->reply_to('foo@bar.com');
		$this->assertNotEmpty($email->getReplyTo());
		$this->assertEquals('foo@bar.com', $email->getReplyTo()[0]->getAddress());
		$this->assertEmpty($email->getReplyTo()[0]->getName());

		// Valid address with name
		$this->method_email->reply_to('bar@foo.com', 'Bar Foo');
		$this->assertEquals('bar@foo.com', $email->getReplyTo()[1]->getAddress());
		$this->assertEquals('Bar Foo', $email->getReplyTo()[1]->getName());
	}

	public function test_from()
	{
		$email_reflection = new \ReflectionClass($this->method_email);
		$email_property = $email_reflection->getProperty('email');
		$this->method_email->init();
		/** @var \Symfony\Component\Mime\Email $email */
		$email = $email_property->getValue($this->method_email);
		$this->assertNotNull($email);

		// Empty address
		$this->assertEmpty($email->getFrom());
		$this->method_email->from('');
		$this->assertEmpty($email->getFrom());

		// Valid address
		$this->method_email->from('foo@bar.com');
		$this->assertNotEmpty($email->getFrom());
		$this->assertEquals('foo@bar.com', $email->getFrom()[0]->getAddress());
		$this->assertEmpty($email->getFrom()[0]->getName());

		// Valid address with name
		$this->method_email->from('bar@foo.com', 'Bar Foo');
		$this->assertEquals('bar@foo.com', $email->getFrom()[1]->getAddress());
		$this->assertEquals('Bar Foo', $email->getFrom()[1]->getName());
	}

	public function test_subject()
	{
		$email_reflection = new \ReflectionClass($this->method_email);
		$email_property = $email_reflection->getProperty('email');
		$this->method_email->init();
		/** @var \Symfony\Component\Mime\Email $email */
		$email = $email_property->getValue($this->method_email);
		$this->assertNotNull($email);

		// Empty subject
		$this->assertEmpty($email->getSubject());
		$this->method_email->subject('');
		$this->assertEmpty($email->getSubject());

		// Test subject
		$this->method_email->subject('Test email');
		$this->assertNotEmpty($email->getSubject());
		$this->assertEquals('Test email', $email->getSubject());

		// Overwrite subject
		$this->method_email->subject('Reply to test email');
		$this->assertNotEmpty($email->getSubject());
		$this->assertEquals('Reply to test email', $email->getSubject());
	}

	public function test_anti_abuse_headers()
	{
		$email_reflection = new \ReflectionClass($this->method_email);
		$headers_property = $email_reflection->getProperty('headers');
		$this->method_email->init();

		/** @var \Symfony\Component\Mime\Header\Headers $headers */
		$headers = $headers_property->getValue($this->method_email);

		$this->config->set('server_name', 'yourdomain.com');
		$this->user->data['user_id'] = 2;
		$this->user->data['username'] = 'admin';
		$this->user->ip = '127.0.0.1';

		$this->assertEmpty($headers->toArray());
		$this->method_email->anti_abuse_headers($this->config, $this->user);

		$this->assertEquals(
			[
				'X-AntiAbuse: Board servername - yourdomain.com',
				'X-AntiAbuse: User_id - 2',
				'X-AntiAbuse: Username - admin',
				'X-AntiAbuse: User IP - 127.0.0.1',
			],
			$headers->toArray()
		);
	}

	public function test_set_mail_priority()
	{
		$email_reflection = new \ReflectionClass($this->method_email);
		$email_property = $email_reflection->getProperty('email');
		$this->method_email->init();
		/** @var \Symfony\Component\Mime\Email $email */
		$email = $email_property->getValue($this->method_email);
		$this->assertNotNull($email);

		// Default priority
		$this->assertEquals(\Symfony\Component\Mime\Email::PRIORITY_NORMAL, $email->getPriority());

		// Highest priority
		$this->method_email->set_mail_priority(\Symfony\Component\Mime\Email::PRIORITY_HIGHEST);
		$this->assertEquals(\Symfony\Component\Mime\Email::PRIORITY_HIGHEST, $email->getPriority());
	}

	public function test_process_queue_not_enabled()
	{
		$this->method_email->init();

		$queue_data = [
			'email'	=> [
				'data'	=> [
					'message_one',
					'message_two',
				]
			]
		];

		// Process queue will remove emails if email method is not enabled
		$this->method_email->process_queue($queue_data);
		$this->assertEmpty($queue_data);
	}

	public function test_process_queue()
	{
		global $phpbb_root_path;

		$this->config->set('email_enable', true);
		$this->user->data['user_id'] = 2;
		$this->user->session_id = 'abcdef';

		$this->method_email = $this->getMockBuilder($this->method_email::class)
			->setConstructorArgs([$this->assets_bag,
				$this->config,
				$this->dispatcher,
				$this->language,
				$this->queue,
				$this->path_helper,
				$this->request,
				$this->twig_extensions_collection,
				$this->twig_lexer,
				$this->user,
				$phpbb_root_path,
				$this->cache_path,
				$this->extension_manager,
				$this->log
			])
			->onlyMethods(['get_mailer'])
			->getMock();

		$mailer_mock = $this->getMockBuilder(\Symfony\Component\Mailer\MailerInterface::class)
			->disableOriginalConstructor()
			->onlyMethods(['send'])
			->getMock();
		$sent_emails = 0;
		$mailer_mock->method('send')
			->willReturnCallback(function(RawMessage $mail) use(&$sent_emails) {
				if ($mail->toString() === 'throw_exception')
				{
					throw new \Symfony\Component\Mailer\Exception\TransportException('exception');
				}

				$sent_emails++;
			});
		$this->method_email->method('get_mailer')->willReturn($mailer_mock);

		$this->method_email->init();
		$errors = [];
		$this->log->method('add')
			->willReturnCallback(function($mode, $user_id, $log_ip, $log_operation, $log_time = false, $additional_data = []) use (&$errors) {
				$errors[] = $additional_data[0];
			});

		$this->dispatcher
			->expects($this->atLeastOnce())
			->method('trigger_event')
			->willReturnCallback(function($event_name, $value_array) {
				if ($event_name === 'core.notification_message_process' && $value_array['email']->toString() == 'message_three')
				{
					$value_array['break'] = true;
				}

				return $value_array;
			});

		$queue_data = [
			'email'	=> [
				'data'	=> [
					['email' => new RawMessage('message_one')],
					['email' => new RawMessage('message_two')],
					['email' => new RawMessage('message_three')],
					['email' => new RawMessage('throw_exception')],
					['email' => new RawMessage('message_four')],
				]
			]
		];

		$this->assertEmpty($errors);
		$this->method_email->process_queue($queue_data);
		$this->assertEmpty($queue_data);
		$this->assertEquals(3, $sent_emails);

		$this->assertEquals(['<strong>EMAIL</strong><br><em></em><br><br><br>'], $errors);
	}

	public function test_send_break()
	{
		$this->dispatcher
			->expects($this->atLeastOnce())
			->method('trigger_event')
			->willReturnCallback(function($event_name, $value_array) {
				if ($event_name !== 'core.notification_message_email')
				{
					return $value_array;
				}

				$value_array['break'] = true;
				return $value_array;
			});

		$this->config->set('board_email', 'admin@yourdomain.com');

		$this->method_email->init();
		$this->method_email->to('foo@bar.com');
		$this->method_email->subject('Test email');
		$this->method_email->template('test', 'en');
		$this->method_email->assign_block_vars('foo', ['bar' => 'baz']);

		$this->method_email->send();
	}

	public static function email_template_data(): array
	{
		return [
			['test'],
			['admin_send_email'],
			['topic_notify'],
		];
	}

	/**
	 * @dataProvider email_template_data
	 */
	public function test_send_no_queue($email_template)
	{
		global $phpbb_root_path;

		$this->config->set('email_enable', true);
		$this->user->data['user_id'] = 2;
		$this->user->session_id = 'abcdef';

		$this->method_email = $this->getMockBuilder($this->method_email::class)
			->setConstructorArgs([$this->assets_bag,
				$this->config,
				$this->dispatcher,
				$this->language,
				$this->queue,
				$this->path_helper,
				$this->request,
				$this->twig_extensions_collection,
				$this->twig_lexer,
				$this->user,
				$phpbb_root_path,
				$this->cache_path,
				$this->extension_manager,
				$this->log
			])
			->onlyMethods(['get_mailer'])
			->getMock();

		$mailer_mock = $this->getMockBuilder(\Symfony\Component\Mailer\MailerInterface::class)
			->disableOriginalConstructor()
			->onlyMethods(['send'])
			->getMock();
		$sent_emails = 0;
		$mailer_mock->method('send')
			->willReturnCallback(function(RawMessage $mail) use(&$sent_emails) {
				$sent_emails++;
			});
		$this->method_email->method('get_mailer')->willReturn($mailer_mock);

		$this->config->set('board_email', 'admin@yourdomain.com');

		$this->method_email->init();
		$errors = [];
		$this->log->method('add')
			->willReturnCallback(function($mode, $user_id, $log_ip, $log_operation, $log_time = false, $additional_data = []) use (&$errors) {
				$errors[] = $additional_data[0];
			});

		$this->dispatcher
			->expects($this->atLeastOnce())
			->method('trigger_event')
			->willReturnCallback(function($event_name, $value_array) {
				return $value_array;
			});

		$this->method_email->to('foo@bar.com');
		$this->method_email->subject('Test email');
		$this->method_email->template($email_template, 'en');

		$this->assertTrue($this->method_email->send());
		$this->assertEquals(1, $sent_emails);

		$this->assertEmpty($errors);
	}

	public function test_send_exception()
	{
		global $phpbb_root_path;

		$this->config->set('email_enable', true);
		$this->user->data['user_id'] = 2;
		$this->user->session_id = 'abcdef';

		$this->method_email = $this->getMockBuilder($this->method_email::class)
			->setConstructorArgs([$this->assets_bag,
				$this->config,
				$this->dispatcher,
				$this->language,
				$this->queue,
				$this->path_helper,
				$this->request,
				$this->twig_extensions_collection,
				$this->twig_lexer,
				$this->user,
				$phpbb_root_path,
				$this->cache_path,
				$this->extension_manager,
				$this->log
			])
			->onlyMethods(['get_mailer'])
			->getMock();

		$mailer_mock = $this->getMockBuilder(\Symfony\Component\Mailer\MailerInterface::class)
			->disableOriginalConstructor()
			->onlyMethods(['send'])
			->getMock();
		$mailer_mock->method('send')
			->willReturnCallback(function(RawMessage $mail) use(&$sent_emails) {
				throw new \Symfony\Component\Mailer\Exception\TransportException('exception');
			});
		$this->method_email->method('get_mailer')->willReturn($mailer_mock);

		$this->config->set('board_email', 'admin@yourdomain.com');

		$this->method_email->init();
		$errors = [];
		$this->log->method('add')
			->willReturnCallback(function($mode, $user_id, $log_ip, $log_operation, $log_time = false, $additional_data = []) use (&$errors) {
				$errors[] = $additional_data[0];
			});

		$this->dispatcher
			->expects($this->atLeastOnce())
			->method('trigger_event')
			->willReturnCallback(function($event_name, $value_array) {
				return $value_array;
			});

		$this->method_email->to('foo@bar.com');
		$this->method_email->subject('Test email');
		$this->method_email->template('test', 'en');

		$this->assertFalse($this->method_email->send());

		$this->assertEquals(['<strong>EMAIL</strong><br><em></em><br><br><br>'], $errors);
	}

	public function test_send_queue()
	{
		global $phpbb_root_path;

		$this->config->set('email_enable', true);
		$this->config->set('email_package_size', 100);
		$this->user->data['user_id'] = 2;
		$this->user->session_id = 'abcdef';

		$this->method_email = $this->getMockBuilder($this->method_email::class)
			->setConstructorArgs([$this->assets_bag,
				$this->config,
				$this->dispatcher,
				$this->language,
				$this->queue,
				$this->path_helper,
				$this->request,
				$this->twig_extensions_collection,
				$this->twig_lexer,
				$this->user,
				$phpbb_root_path,
				$this->cache_path,
				$this->extension_manager,
				$this->log
			])
			->onlyMethods(['get_mailer'])
			->getMock();

		$mailer_mock = $this->getMockBuilder(\Symfony\Component\Mailer\MailerInterface::class)
			->disableOriginalConstructor()
			->onlyMethods(['send'])
			->getMock();
		$mailer_mock->method('send')
			->willReturnCallback(function(RawMessage $mail) use(&$sent_emails) {
				throw new \Symfony\Component\Mailer\Exception\TransportException('exception');
			});
		$this->method_email->method('get_mailer')->willReturn($mailer_mock);

		$this->config->set('board_email', 'admin@yourdomain.com');

		$this->method_email->init();
		$errors = [];
		$this->log->method('add')
			->willReturnCallback(function($mode, $user_id, $log_ip, $log_operation, $log_time = false, $additional_data = []) use (&$errors) {
				$errors[] = $additional_data[0];
			});

		$this->dispatcher
			->expects($this->atLeastOnce())
			->method('trigger_event')
			->willReturnCallback(function($event_name, $value_array) {
				return $value_array;
			});

		// Mock queue methods
		$this->queue->method('init')
			->willReturnCallback(function(string $object, int $package_size) {
				$this->assertEquals('email', $object);
				$this->assertEquals($this->config['email_package_size'], $package_size);
			});
		$this->queue->method('put')
			->willReturnCallback(function(string $object, array $message_data) {
				$this->assertEquals('email', $object);
				$this->assertStringContainsString('phpBB is correctly configured to send emails', $message_data['email']->getSubject());
			});

		$this->method_email->to('foo@bar.com');
		$this->method_email->subject('Test email');
		$this->method_email->template('test', 'en');

		$this->assertTrue($this->method_email->send());

		$this->assertEmpty($errors);
	}
}
