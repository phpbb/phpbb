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

class phpbb_messenger_method_email_test extends \phpbb_test_case
{
	protected config $config;
	protected queue $queue;
	protected $request;

	public function setUp(): void
	{
		global $phpbb_root_path, $phpEx;

		$assets_bag = new assets_bag();
		$cache_path = $phpbb_root_path . 'cache/' . PHPBB_ENVIRONMENT . '/twig';
		$this->config = new config([]);
		$dispatcher = new \phpbb_mock_event_dispatcher();
		$filesystem = new \phpbb\filesystem\filesystem();
		$language = new language(new language_file_loader($phpbb_root_path, $phpEx));
		$this->queue = $this->createMock(queue::class);
		$this->request = new phpbb_mock_request();
		$user = new \phpbb\user($language, '\phpbb\datetime');
		$path_helper = new path_helper(
			new symfony_request(
				new phpbb_mock_request()
			),
			$this->request,
			$phpbb_root_path,
			$phpEx
		);
		$phpbb_container = new phpbb_mock_container_builder;
		$twig_extensions_collection = new \phpbb\di\service_collection($phpbb_container);
		$twig = new \phpbb\template\twig\environment(
			$assets_bag,
			$this->config,
			$filesystem,
			$path_helper,
			$cache_path,
			null,
			new \phpbb\template\twig\loader(''),
			$dispatcher,
			array(
				'cache'			=> false,
				'debug'			=> false,
				'auto_reload'	=> true,
				'autoescape'	=> false,
			)
		);
		$twig_lexer = new \phpbb\template\twig\lexer($twig);
		$extension_manager = new phpbb_mock_extension_manager(
			__DIR__ . '/',
			array(
				'vendor2/foo' => array(
					'ext_name' => 'vendor2/foo',
					'ext_active' => '1',
					'ext_path' => 'ext/vendor2/foo/',
				),
			)
		);
		$log = $this->createMock(\phpbb\log\log_interface::class);

		$this->method_email = new email(
			$assets_bag,
			$this->config,
			$dispatcher,
			$language,
			$this->queue,
			$path_helper,
			$this->request,
			$twig_extensions_collection,
			$twig_lexer,
			$user,
			$phpbb_root_path,
			$cache_path,
			$extension_manager,
			$log
		);
	}

	public function test_miscellaneous(): void
	{
		$this->assertEquals(email::NOTIFY_EMAIL, $this->method_email->get_id());
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
		$this->assertNull($this->method_email->get_transport());
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
		$this->assertNull($email_property->getValue($this->method_email));

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
		if (DIRECTORY_SEPARATOR == '\\')
		{
			$this->assertEmpty($email->getTo()[1]->getName());
		}
		else
		{
			$this->assertEquals('Bar Foo', $email->getTo()[1]->getName());
		}
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
}
