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

class exception_listener_test extends phpbb_test_case
{
	/** @var string */
	protected $error_log_file;

	/** @var string */
	protected $original_error_log;

	protected function setUp(): void
	{
		parent::setUp();

		// Redirect error_log() to a temporary file so logged exceptions can be
		// asserted and do not leak to stderr during the test run.
		$this->error_log_file = tempnam(sys_get_temp_dir(), 'phpbb_exception_log_');
		$this->original_error_log = ini_get('error_log');
		ini_set('error_log', $this->error_log_file);
	}

	protected function tearDown(): void
	{
		ini_set('error_log', $this->original_error_log);

		if (file_exists($this->error_log_file))
		{
			unlink($this->error_log_file);
		}

		parent::tearDown();
	}

	public static function phpbb_exception_data()
	{
		return array(
			array(
				true,
				new \Exception(),
				array(
					'status_code' => 500,
				),
			),
			array(
				true,
				new \Exception('AJAX_ERROR_TEXT'),
				array(
					'status_code' => 500,
					'content' => 'AJAX_ERROR_TEXT',
				),
			),
			array(
				true,
				new \phpbb\exception\runtime_exception('AJAX_ERROR_TEXT'),
				array(
					'status_code' => 500,
					'content' => 'Something went wrong when processing your request.',
				),
			),
			array(
				true,
				new \Symfony\Component\HttpKernel\Exception\HttpException(404, 'AJAX_ERROR_TEXT'),
				array(
					'status_code' => 404,
					'content' => 'AJAX_ERROR_TEXT',
				),
			),
			array(
				true,
				new \phpbb\exception\http_exception(404, 'AJAX_ERROR_TEXT'),
				array(
					'status_code' => 404,
					'content' => 'Something went wrong when processing your request.',
				),
			),
			array(
				true,
				new \phpbb\exception\http_exception(404, 'CURRENT_TIME', array('today')),
				array(
					'status_code' => 404,
					'content' => 'It is currently today',
				),
			),
		);
	}

	/**
	 * @dataProvider phpbb_exception_data
	 */
	public function test_phpbb_exception($is_ajax, $exception, $expected)
	{
		$request = \Symfony\Component\HttpFoundation\Request::create('test.php', 'GET', array(), array(), array(), $is_ajax ? array('HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest') : array());

		$template = $this->getMockBuilder('\phpbb\template\twig\twig')
			->disableOriginalConstructor()
			->getMock();

		global $phpbb_root_path, $phpEx;

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');

		$exception_listener = new \phpbb\event\kernel_exception_subscriber($template, $lang, $user);

		$event = new \Symfony\Component\HttpKernel\Event\ExceptionEvent($this->createMock('Symfony\Component\HttpKernel\HttpKernelInterface'), $request, \Symfony\Component\HttpKernel\HttpKernelInterface::MAIN_REQUEST, $exception);
		$exception_listener->on_kernel_exception($event);

		$response = $event->getResponse();

		$this->assertEquals($expected['status_code'], $response->getStatusCode());
		$this->assertEquals($is_ajax, $response instanceof \Symfony\Component\HttpFoundation\JsonResponse);

		if (isset($expected['content']))
		{
			$this->assertStringContainsString($expected['content'], $response->getContent());
		}
	}

	protected function dispatch_exception($exception)
	{
		$request = \Symfony\Component\HttpFoundation\Request::create('test.php', 'GET', array(), array(), array(), array('HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'));

		$template = $this->getMockBuilder('\phpbb\template\twig\twig')
			->disableOriginalConstructor()
			->getMock();

		global $phpbb_root_path, $phpEx;

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');

		$exception_listener = new \phpbb\event\kernel_exception_subscriber($template, $lang, $user);

		$event = new \Symfony\Component\HttpKernel\Event\ExceptionEvent($this->createMock('Symfony\Component\HttpKernel\HttpKernelInterface'), $request, \Symfony\Component\HttpKernel\HttpKernelInterface::MAIN_REQUEST, $exception);
		$exception_listener->on_kernel_exception($event);
	}

	public function test_server_error_is_logged()
	{
		$this->dispatch_exception(new \Exception('Something went very wrong'));

		$logged = file_get_contents($this->error_log_file);

		$this->assertStringContainsString('Exception: Something went very wrong', $logged);
		$this->assertStringContainsString('on line', $logged);
	}

	public function test_client_error_is_not_logged()
	{
		$this->dispatch_exception(new \Symfony\Component\HttpKernel\Exception\HttpException(404, 'This must not be logged'));

		$logged = file_get_contents($this->error_log_file);

		$this->assertStringNotContainsString('This must not be logged', $logged);
	}
}
