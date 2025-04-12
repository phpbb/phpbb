<?php

use phpbb\config\config;
use phpbb\language\language;
use phpbb\language\language_file_loader;
use phpbb\messenger\method\jabber;
use phpbb\messenger\method\messenger_interface;
use phpbb\messenger\queue;
use phpbb\path_helper;
use phpbb\symfony_request;
use phpbb\template\assets_bag;

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

class phpbb_messenger_method_jabber_test extends \phpbb_test_case
{
	protected $assets_bag;
	protected $cache_path;
	protected config $config;
	protected $dispatcher;
	protected $extension_manager;
	protected jabber $method_jabber;
	protected $method_base;
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
			'force_server_vars'	=> false,
			'jab_username'		=> 'test',
			'jab_password'		=> 'password',
			'jab_use_ssl'		=> false,
			'jab_host'			=> 'localhost',
			'jab_port'			=> 5222,
			'jab_verify_peer'	=> true,
			'jab_verify_peer_name' => true,
			'jab_allow_self_signed' => false,
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
		$this->user = $this->getMockBuilder('\phpbb\user')
			->setConstructorArgs([$this->language, '\phpbb\datetime'])
			->getMock();
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
				'cache' => false,
				'debug' => false,
				'auto_reload' => true,
				'autoescape' => false,
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

		$this->method_jabber = new jabber(
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

	public function test_miscellaneous()
	{
		$this->method_jabber->init();
		$this->assertEquals(messenger_interface::NOTIFY_IM, $this->method_jabber->get_id());
		$this->assertEquals('jabber', $this->method_jabber->get_queue_object_name());
		$this->assertFalse($this->method_jabber->is_enabled());
		$this->config->set('jab_enable', true);
		$this->assertTrue($this->method_jabber->is_enabled());
		$this->assertEquals(@extension_loaded('openssl'), $this->method_jabber->can_use_ssl());
	}

	public function test_stream_options()
	{
		$this->method_jabber->init();
		$this->assertEquals($this->method_jabber, $this->method_jabber->stream_options([
			'allow_self_signed' => true,
		]));

		$stream_options_reflection = new \ReflectionProperty($this->method_jabber, 'stream_options');
		$stream_options = $stream_options_reflection->getValue($this->method_jabber);
		$this->assertEquals([
			'ssl' => [
				'allow_self_signed' => false,
				'verify_peer' => true,
				'verify_peer_name' => true,
			],
		], $stream_options);

		$this->method_jabber->ssl(true);

		$this->assertEquals($this->method_jabber, $this->method_jabber->stream_options([
			'allow_self_signed' => true,
		]));
		$stream_options = $stream_options_reflection->getValue($this->method_jabber);
		$this->assertEquals([
			'ssl' => [
				'allow_self_signed' => true,
				'verify_peer' => true,
				'verify_peer_name' => true,
			],
		], $stream_options);
	}

	public function test_port_ssl_switch()
	{
		$port_reflection = new \ReflectionProperty($this->method_jabber, 'port');

		$this->method_jabber->port();
		$this->assertEquals(5222, $port_reflection->getValue($this->method_jabber));

		$this->method_jabber->ssl(true)
			->port();
		$this->assertEquals(5223, $port_reflection->getValue($this->method_jabber));
	}

	public function test_username()
	{
		$jabber_reflection = new \ReflectionClass($this->method_jabber);
		$username_reflection = $jabber_reflection->getProperty('username');
		$jid_reflection = $jabber_reflection->getProperty('jid');

		$this->method_jabber->username('foo@bar');
		$this->assertEquals(['foo', 'bar'], $jid_reflection->getValue($this->method_jabber));
		$this->assertEquals('foo', $username_reflection->getValue($this->method_jabber));

		$this->method_jabber->username('bar@baz@qux');
		$this->assertEquals(['bar', 'baz@qux'], $jid_reflection->getValue($this->method_jabber));
		$this->assertEquals('bar', $username_reflection->getValue($this->method_jabber));
	}

	public function test_server()
	{
		$jabber_reflection = new \ReflectionClass($this->method_jabber);
		$connect_server_reflection = $jabber_reflection->getProperty('connect_server');
		$server_reflection = $jabber_reflection->getProperty('server');

		$this->method_jabber->server();
		$this->assertEquals('localhost', $connect_server_reflection->getValue($this->method_jabber));
		$this->assertEquals('localhost', $server_reflection->getValue($this->method_jabber));

		$this->method_jabber->server('foobar.com');
		$this->assertEquals('foobar.com', $connect_server_reflection->getValue($this->method_jabber));
		$this->assertEquals('foobar.com', $server_reflection->getValue($this->method_jabber));

		$this->method_jabber->username('foo@bar.com');
		$this->method_jabber->server('foobar.com');
		$this->assertEquals('foobar.com', $connect_server_reflection->getValue($this->method_jabber));
		$this->assertEquals('bar.com', $server_reflection->getValue($this->method_jabber));
	}

	public function test_encrypt_password()
	{
		$this->method_jabber->init();
		$this->method_jabber->password('password');
		$data = [
			'realm' => 'example.com',
			'nonce' => '12345',
			'cnonce' => 'abcde',
			'digest-uri' => 'xmpp/example.com',
			'nc' => '00000001',
			'qop' => 'auth',
		];

		$expected = md5(sprintf(
			'%s:%s:%s:%s:%s:%s',
			md5(pack('H32', md5('test:example.com:password')) . ':12345:abcde'),
			$data['nonce'],
			$data['nc'],
			$data['cnonce'],
			$data['qop'],
			md5('AUTHENTICATE:xmpp/example.com')
		));
		$this->assertEquals($expected, $this->method_jabber->encrypt_password($data));
	}

	public function test_parse_data()
	{
		$data = 'key1="value1",key2="value2",key3="value3"';
		$expected = [
			'key1' => 'value1',
			'key2' => 'value2',
			'key3' => 'value3',
		];

		$this->assertEquals($expected, $this->method_jabber->parse_data($data));
	}

	public function test_implode_data()
	{
		$data = [
			'key1' => 'value1',
			'key2' => 'value2',
			'key3' => 'value3',
		];
		$expected = 'key1="value1",key2="value2",key3="value3"';

		$this->assertEquals($expected, $this->method_jabber->implode_data($data));
	}

	public function test_xmlize()
	{
		$xml = '<root><child key="value">content</child></root>';
		$result = $this->method_jabber->xmlize($xml);

		$this->assertArrayHasKey('root', $result);
		$this->assertArrayHasKey('child', $result['root'][0]['#']);
		$this->assertEquals('content', $result['root'][0]['#']['child'][0]['#']);
		$this->assertEquals(['key' => 'value'], $result['root'][0]['#']['child'][0]['@']);
	}

	public function test_send_xml()
	{
		$jabber_mock = $this->getMockBuilder(jabber::class)
			->setConstructorArgs([
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
				'',
				'',
				$this->extension_manager,
				$this->log,
			])
			->onlyMethods(['send_xml'])
			->getMock();

		$jabber_mock->expects($this->once())
			->method('send_xml')
			->with('<message>Test</message>')
			->willReturn(true);

		$this->assertTrue($jabber_mock->send_xml('<message>Test</message>'));
	}
}
