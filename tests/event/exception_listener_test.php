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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class exception_listener extends phpbb_test_case
{
	public function phpbb_exception_data()
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
		$user->add_lang('common');

		$exception_listener = new \phpbb\event\kernel_exception_subscriber($template, $user);

		$event = new \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent($this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface'), $request, \Symfony\Component\HttpKernel\HttpKernelInterface::MASTER_REQUEST, $exception);
		$exception_listener->on_kernel_exception($event);

		$response = $event->getResponse();

		$this->assertEquals($expected['status_code'], $response->getStatusCode());
		$this->assertEquals($is_ajax, $response instanceof \Symfony\Component\HttpFoundation\JsonResponse);

		if (isset($expected['content']))
		{
			$this->assertContains($expected['content'], $response->getContent());
		}
	}
}
