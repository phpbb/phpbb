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

class phpbb_messenger_method_base_test extends \phpbb_test_case
{
	protected $assets_bag;
	protected $cache_path;
	protected config $config;
	protected $dispatcher;
	protected $extension_manager;
	protected email $method_email;
	protected $method_base;
	protected $language;
	protected $log;
	protected $path_helper;
	protected queue $queue;
	protected $request;
	protected $twig_extensions_collection;
	protected $twig_lexer;
	protected $user;
	protected $filesystem;
	protected $symfony_request;

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

		$this->method_base = $this->getMockBuilder(\phpbb\messenger\method\base::class)
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
				$phpbb_root_path,
				$this->cache_path,
				$this->extension_manager,
				$this->log
			])
			->getMockForAbstractClass();
	}

	public function test_header()
	{
		$this->method_base->header('X-AntiAbuse', 'Board servername - ' . $this->user->host);
		$this->assertTrue(true); // No exception should be thrown
	}

	public function test_set_use_queue()
	{
		$use_queue_property = new \ReflectionProperty($this->method_base, 'use_queue');
		$this->method_base->set_use_queue();
		$this->assertTrue($use_queue_property->getValue($this->method_base));
		$this->method_base->set_use_queue(false);
		$this->assertFalse($use_queue_property->getValue($this->method_base));
	}

	public function test_error_wout_session()
	{
		$errors = [];
		$this->log->method('add')
			->willReturnCallback(function($mode, $user_id, $log_ip, $log_operation, $log_time = false, $additional_data = []) use (&$errors) {
				$errors[] = $additional_data[0];
			});

		$this->user->data['user_id'] = 2;
		$this->user->session_id = '';
		$this->user
			->expects($this->once())
			->method('session_begin')
			->willReturnCallback(function() {
				$this->assertTrue(true);
			});

		$this->method_base->error('Test error message');

		$this->assertCount(1, $errors);
		$this->assertEquals('<strong></strong><br><em></em><br><br>Test error message<br>', $errors[0]);
	}

	public function test_save_queue()
	{
		$this->queue->expects($this->once())
			->method('save');
		$this->method_base->set_use_queue(false);
		$this->method_base->save_queue();
		$this->method_base->set_use_queue(true);
		$this->method_base->save_queue();
	}

	public function test_template_no_lang()
	{
		$template_mock = $this->getMockBuilder(\phpbb\template\template::class)
			->disableOriginalConstructor()
			->getMock();
		$filenames = [];
		$template_mock->method('set_filenames')
			->willReturnCallback(function($filename_array) use (&$filenames, $template_mock) {
				$filenames = array_merge($filenames, $filename_array);

				return $template_mock;
			});

		$base_reflection = new \ReflectionClass($this->method_base);
		$template_reflection = $base_reflection->getProperty('template');
		$template_reflection->setValue($this->method_base, $template_mock);

		$this->config->set('default_lang', 'en');
		$this->method_base->template('test');
		$this->assertEquals(['body' => 'test.txt'], $filenames);
	}

	public function test_template_template_path()
	{
		global $phpbb_root_path;

		$template_mock = $this->getMockBuilder(\phpbb\template\template::class)
			->disableOriginalConstructor()
			->getMock();
		$filenames = [];
		$template_mock->method('set_filenames')
			->willReturnCallback(function($filename_array) use (&$filenames, $template_mock) {
				$filenames = array_merge($filenames, $filename_array);

				return $template_mock;
			});
		$template_mock->method('set_custom_style')
			->willReturnCallback(function($path_name, $paths) use($phpbb_root_path) {
				$this->assertEquals([['name' => 'en_email', 'ext_path' => 'language/en/email']], $path_name);
				$this->assertEquals([$phpbb_root_path . 'language/en/email'], $paths);
			});

		$base_reflection = new \ReflectionClass($this->method_base);
		$template_reflection = $base_reflection->getProperty('template');
		$template_reflection->setValue($this->method_base, $template_mock);

		$this->config->set('default_lang', 'en');
		$this->method_base->template('test', '', $phpbb_root_path . 'language/en/email');
		$this->assertEquals(['body' => 'test.txt'], $filenames);
	}

	public function test_template_path_fallback()
	{
		global $phpbb_root_path;

		$template_mock = $this->getMockBuilder(\phpbb\template\template::class)
			->disableOriginalConstructor()
			->getMock();
		$filenames = [];
		$template_mock->method('set_filenames')
			->willReturnCallback(function($filename_array) use (&$filenames, $template_mock) {
				$filenames = array_merge($filenames, $filename_array);

				return $template_mock;
			});
		$template_mock->method('set_custom_style')
			->willReturnCallback(function($path_name, $paths) use($phpbb_root_path) {
				$this->assertEquals([
					['name' => 'de_email', 'ext_path' => 'language/de/email'],
					['name' => 'en_email', 'ext_path' => 'language/en/email'],
				], $path_name);
				$this->assertEquals([
					$phpbb_root_path . 'language/de/email',
					$phpbb_root_path . 'language/en/email'
				], $paths);
			});

		$base_reflection = new \ReflectionClass($this->method_base);
		$template_reflection = $base_reflection->getProperty('template');
		$template_reflection->setValue($this->method_base, $template_mock);

		$this->config->set('default_lang', 'de');
		$this->method_base->template('test', 'de');
		$this->assertEquals(['body' => 'test.txt'], $filenames);
	}
}
