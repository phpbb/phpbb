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

namespace phpbb\feed\event;

class http_auth_subscriber_test extends \phpbb_test_case
{
	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\auth\auth */
	protected $auth;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\config\config */
	protected $config;

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

		$this->config = new \phpbb\config\config(array(
			'feed_http_auth' => 1,
			'sitename' => 'Test Site',
		));

		$this->request = $this->getMockBuilder('\phpbb\request\request_interface')
			->getMock();

		$this->user = $this->getMockBuilder('\phpbb\user')
			->disableOriginalConstructor()
			->getMock();

		$this->user->data = array('is_registered' => false);

		$this->subscriber = new http_auth_subscriber(
			$this->auth,
			$this->config,
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

		$event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\GetResponseEvent')
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

		$event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\GetResponseEvent')
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

		$request->attributes->expects($this->once())
			->method('get')
			->with('_route')
			->willReturn('phpbb_feed_overall');

		$request->expects($this->once())
			->method('isSecure')
			->willReturn(true);

		$event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\GetResponseEvent')
			->disableOriginalConstructor()
			->getMock();

		$event->expects($this->once())
			->method('getRequest')
			->willReturn($request);

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

		$event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\GetResponseEvent')
			->disableOriginalConstructor()
			->getMock();

		$event->expects($this->once())
			->method('getRequest')
			->willReturn($request);

		$event->expects($this->never())
			->method('setResponse');

		$this->subscriber->on_kernel_request($event);
	}
}
