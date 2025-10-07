<?php

use phpbb\config\config;
use phpbb\feed\event\http_auth_subscriber;
use phpbb\request\request_interface;
use Symfony\Component\HttpFoundation\Response;

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

class phpbb_feed_http_auth_subscriber_test extends \phpbb_test_case
{
	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\auth\auth */
	protected $auth;

	/** @var \PHPUnit\Framework\MockObject\MockObject|config */
	protected $config;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\language\language */
	protected $language;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\request\request_interface */
	protected $request;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\user */
	protected $user;

	/** @var http_auth_subscriber */
	protected $subscriber;

	protected function setUp(): void
	{
		parent::setUp();

		$this->auth = $this->getMockBuilder('\phpbb\auth\auth')
			->disableOriginalConstructor()
			->getMock();
		$this->auth->method('login')
			->willReturnMap([
				['valid_user', 'valid_password', false, true, false, ['status' => LOGIN_SUCCESS]],
				['invalid_user', 'invalid_password', false, true, false, ['status' => LOGIN_ERROR_USERNAME]],
				['attempts_user', 'valid_password', false, true, false, ['status' => LOGIN_ERROR_ATTEMPTS]],
			]);

		$this->config = new config(array(
			'feed_http_auth' => 1,
			'sitename' => 'Test Site',
		));

		$this->language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();
		$this->language->method('lang')
			->willReturnMap([
				['NOT_AUTHORISED', 'NOT_AUTHORISED'],
				['LOGIN_ERROR_ATTEMPTS', 'LOGIN_ERROR_ATTEMPTS']
			]);

		$this->request = $this->getMockBuilder('\phpbb\request\request_interface')
			->getMock();

		$this->user = $this->getMockBuilder('\phpbb\user')
			->disableOriginalConstructor()
			->getMock();

		$this->user->data = array('is_registered' => false);

		$this->subscriber = new http_auth_subscriber(
			$this->auth,
			$this->config,
			$this->language,
			$this->request,
			$this->user
		);
	}

	public function test_subscriber_events()
	{
		$events = http_auth_subscriber::getSubscribedEvents();
		$this->assertArrayHasKey(\Symfony\Component\HttpKernel\KernelEvents::REQUEST, $events);
	}

	public function test_non_feed_route_skipped()
	{
		$request = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
			->disableOriginalConstructor()
			->getMock();

		$request->attributes = $this->getMockBuilder('\Symfony\Component\HttpFoundation\ParameterBag')
			->disableOriginalConstructor()
			->getMock();

		$request->attributes->expects($this->once())
			->method('get')
			->with('_route')
			->willReturn('not_a_feed_route');

		$event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\RequestEvent')
			->onlyMethods(['getRequest', 'setResponse'])
			->disableOriginalConstructor()
			->getMock();

		$event->expects($this->once())
			->method('getRequest')
			->willReturn($request);

		$event->expects($this->never())
			->method('setResponse');

		$this->subscriber->on_kernel_request($event);
	}

	public function test_insecure_connection_skipped()
	{
		$request = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
			->disableOriginalConstructor()
			->getMock();

		$request->attributes = $this->getMockBuilder('\Symfony\Component\HttpFoundation\ParameterBag')
			->disableOriginalConstructor()
			->getMock();

		$request->attributes->expects($this->once())
			->method('get')
			->with('_route')
			->willReturn('phpbb_feed_overall');

		$request->expects($this->once())
			->method('isSecure')
			->willReturn(false);

		$event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\RequestEvent')
			->disableOriginalConstructor()
			->getMock();

		$event->expects($this->once())
			->method('getRequest')
			->willReturn($request);

		$event->expects($this->never())
			->method('setResponse');

		$this->subscriber->on_kernel_request($event);
	}

	public function test_http_auth_disabled()
	{
		$this->config['feed_http_auth'] = 0;

		$request = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
			->disableOriginalConstructor()
			->getMock();

		$request->attributes = $this->getMockBuilder('\Symfony\Component\HttpFoundation\ParameterBag')
			->disableOriginalConstructor()
			->getMock();

		$request->attributes->expects($this->never())
			->method('get');

		$request->expects($this->never())
			->method('isSecure');

		$event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\RequestEvent')
			->disableOriginalConstructor()
			->getMock();

		$event->expects($this->never())
			->method('getRequest');

		$event->expects($this->never())
			->method('setResponse');

		$this->subscriber->on_kernel_request($event);
	}

	public function test_user_already_logged_in()
	{
		$this->user->data = array('is_registered' => true);

		$request = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
			->disableOriginalConstructor()
			->getMock();

		$request->attributes = $this->getMockBuilder('\Symfony\Component\HttpFoundation\ParameterBag')
			->disableOriginalConstructor()
			->getMock();

		$request->attributes->expects($this->once())
			->method('get')
			->with('_route')
			->willReturn('phpbb_feed_overall');

		$request->expects($this->once())
			->method('isSecure')
			->willReturn(true);

		$event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\RequestEvent')
			->disableOriginalConstructor()
			->getMock();

		$event->expects($this->once())
			->method('getRequest')
			->willReturn($request);

		$event->expects($this->never())
			->method('setResponse');

		$this->subscriber->on_kernel_request($event);
	}

	public function test_no_credentials()
	{
		$this->user->data = ['is_registered' => false];

		$request = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
			->disableOriginalConstructor()
			->getMock();

		$request->attributes = $this->getMockBuilder('\Symfony\Component\HttpFoundation\ParameterBag')
			->disableOriginalConstructor()
			->getMock();

		$request->attributes->expects($this->once())
			->method('get')
			->with('_route')
			->willReturn('phpbb_feed_overall');

		$request->expects($this->once())
			->method('isSecure')
			->willReturn(true);

		$event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\RequestEvent')
			->disableOriginalConstructor()
			->getMock();

		$event->expects($this->once())
			->method('getRequest')
			->willReturn($request);

		/** @var Response $response */
		$response = null;
		$event->expects($this->once())
			->method('setResponse')
			->with($this->isInstanceOf('\Symfony\Component\HttpFoundation\Response'))
			->willReturnCallback(function ($newResponse) use (&$response) {
				$response = $newResponse;
			});

		$this->subscriber->on_kernel_request($event);

		$this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
		$this->assertEquals('NOT_AUTHORISED', $response->getContent());
		$this->assertTrue($response->headers->has('WWW-Authenticate'));
	}

	public function test_valid_credentials()
	{
		$this->user->data = ['is_registered' => false];

		$request = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
			->disableOriginalConstructor()
			->getMock();

		$request->attributes = $this->getMockBuilder('\Symfony\Component\HttpFoundation\ParameterBag')
			->disableOriginalConstructor()
			->getMock();

		$request->attributes->expects($this->once())
			->method('get')
			->with('_route')
			->willReturn('phpbb_feed_overall');

		$this->request->method('is_set')
			->willReturnMap([
				['PHP_AUTH_USER', request_interface::SERVER, true],
				['PHP_AUTH_PW', request_interface::SERVER, true],
			]);

		$this->request->method('server')
			->willReturnMap([
				['PHP_AUTH_USER', '', 'valid_user'],
				['PHP_AUTH_PW', '', 'valid_password'],
			]);

		$request->expects($this->once())
			->method('isSecure')
			->willReturn(true);

		$event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\RequestEvent')
			->disableOriginalConstructor()
			->getMock();

		$event->expects($this->once())
			->method('getRequest')
			->willReturn($request);

		/** @var Response $response */
		$response = null;
		$event->expects($this->never())
			->method('setResponse');

		$this->subscriber->on_kernel_request($event);

		$this->assertNull($response);
	}

	public function test_valid_credentials_base64()
	{
		$this->user->data = ['is_registered' => false];

		$request = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
			->disableOriginalConstructor()
			->getMock();

		$request->attributes = $this->getMockBuilder('\Symfony\Component\HttpFoundation\ParameterBag')
			->disableOriginalConstructor()
			->getMock();

		$request->attributes->expects($this->once())
			->method('get')
			->with('_route')
			->willReturn('phpbb_feed_overall');

		$this->request->method('is_set')
			->willReturnMap([
				['Authorization', request_interface::SERVER, true],
			]);

		$this->request->method('server')
			->willReturnMap([
				['Authorization', '', 'Basic dmFsaWRfdXNlcjp2YWxpZF9wYXNzd29yZA=='],
			]);

		$request->expects($this->once())
			->method('isSecure')
			->willReturn(true);

		$event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\RequestEvent')
			->disableOriginalConstructor()
			->getMock();

		$event->expects($this->once())
			->method('getRequest')
			->willReturn($request);

		/** @var Response $response */
		$response = null;
		$event->expects($this->never())
			->method('setResponse');

		$this->subscriber->on_kernel_request($event);

		$this->assertNull($response);
	}

	public function test_too_many_attempts()
	{
		$this->user->data = ['is_registered' => false];

		$request = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
			->disableOriginalConstructor()
			->getMock();

		$request->attributes = $this->getMockBuilder('\Symfony\Component\HttpFoundation\ParameterBag')
			->disableOriginalConstructor()
			->getMock();

		$request->attributes->expects($this->once())
			->method('get')
			->with('_route')
			->willReturn('phpbb_feed_overall');

		$this->request->method('is_set')
			->willReturnMap([
				['PHP_AUTH_USER', request_interface::SERVER, true],
				['PHP_AUTH_PW', request_interface::SERVER, true],
			]);

		$this->request->method('server')
			->willReturnMap([
				['PHP_AUTH_USER', '', 'attempts_user'],
				['PHP_AUTH_PW', '', 'valid_password'],
			]);

		$request->expects($this->once())
			->method('isSecure')
			->willReturn(true);

		$event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\RequestEvent')
			->disableOriginalConstructor()
			->getMock();

		$event->expects($this->once())
			->method('getRequest')
			->willReturn($request);

		/** @var Response $response */
		$response = null;
		$event->expects($this->once())
			->method('setResponse')
			->with($this->isInstanceOf('\Symfony\Component\HttpFoundation\Response'))
			->willReturnCallback(function ($newResponse) use (&$response) {
				$response = $newResponse;
			});

		$this->subscriber->on_kernel_request($event);

		$this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
		$this->assertEquals('LOGIN_ERROR_ATTEMPTS', $response->getContent());
		$this->assertFalse($response->headers->has('WWW-Authenticate'));
	}

	public function test_wrong_credentials()
	{
		$this->user->data = ['is_registered' => false];

		$request = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
			->disableOriginalConstructor()
			->getMock();

		$request->attributes = $this->getMockBuilder('\Symfony\Component\HttpFoundation\ParameterBag')
			->disableOriginalConstructor()
			->getMock();

		$request->attributes->expects($this->once())
			->method('get')
			->with('_route')
			->willReturn('phpbb_feed_overall');

		$this->request->method('is_set')
			->willReturnMap([
				['PHP_AUTH_USER', request_interface::SERVER, true],
				['PHP_AUTH_PW', request_interface::SERVER, true],
			]);

		$this->request->method('server')
			->willReturnMap([
				['PHP_AUTH_USER', '', 'invalid_user'],
				['PHP_AUTH_PW', '', 'invalid_password'],
			]);

		$request->expects($this->once())
			->method('isSecure')
			->willReturn(true);

		$event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\RequestEvent')
			->disableOriginalConstructor()
			->getMock();

		$event->expects($this->once())
			->method('getRequest')
			->willReturn($request);

		/** @var Response $response */
		$response = null;
		$event->expects($this->once())
			->method('setResponse')
			->with($this->isInstanceOf('\Symfony\Component\HttpFoundation\Response'))
			->willReturnCallback(function ($newResponse) use (&$response) {
				$response = $newResponse;
			});

		$this->subscriber->on_kernel_request($event);

		$this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
		$this->assertEquals('NOT_AUTHORISED', $response->getContent());
		$this->assertTrue($response->headers->has('WWW-Authenticate'));
	}
}
