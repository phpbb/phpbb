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

use phpbb\captcha\plugins\confirm_type;
use phpbb\captcha\plugins\turnstile;
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\language\language;
use phpbb\log\log_interface;
use phpbb\request\request;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\user;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class phpbb_captcha_turnstile_test extends \phpbb_database_test_case
{
	/** @var turnstile */
	protected $turnstile;

	/** @var PHPUnit\Framework\MockObject\MockObject */
	protected $config;

	/** @var PHPUnit\Framework\MockObject\MockObject */
	protected $db;

	/** @var PHPUnit\Framework\MockObject\MockObject */
	protected $language;

	/** @var PHPUnit\Framework\MockObject\MockObject */
	protected $log;

	/** @var PHPUnit\Framework\MockObject\MockObject */
	protected $request;

	/** @var PHPUnit\Framework\MockObject\MockObject */
	protected $template;

	/** @var PHPUnit\Framework\MockObject\MockObject */
	protected $user;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/../fixtures/empty.xml');
	}

	protected function setUp(): void
	{
		// Mock the dependencies
		$this->config = $this->createMock(config::class);
		$this->db = $this->createMock(driver_interface::class);
		$this->language = $this->createMock(language::class);
		$this->log = $this->createMock(log_interface::class);
		$this->request = $this->createMock(request::class);
		$this->template = $this->createMock(template::class);
		$this->user = $this->createMock(user::class);

		$this->language->method('lang')->willReturnArgument(0);

		// Instantiate the turnstile class with the mocked dependencies
		$this->turnstile = new turnstile(
			$this->config,
			$this->db,
			$this->language,
			$this->log,
			$this->request,
			$this->template,
			$this->user
		);
	}

	public function testIsAvailable(): void
	{
		// Test when both sitekey and secret are present
		$this->config->method('offsetGet')->willReturnMap([
			['captcha_turnstile_sitekey', 'sitekey_value'],
			['captcha_turnstile_secret', 'secret_value'],
		]);

		$this->request->method('variable')->willReturnMap([
			['confirm_id', '', false, request_interface::REQUEST, 'confirm_id'],
			['confirm_code', '', false, request_interface::REQUEST, 'confirm_code']
		]);

		$this->assertTrue($this->turnstile->is_available());
	}

	public function test_not_vailable(): void
	{
		$this->request->method('variable')->willReturnMap([
			['confirm_id', '', false, request_interface::REQUEST, 'confirm_id'],
			['confirm_code', '', false, request_interface::REQUEST, 'confirm_code']
		]);

		// Test when sitekey or secret is missing
		$this->config->method('offsetGet')->willReturnMap([
			['captcha_turnstile_sitekey', ''],
			['captcha_turnstile_secret', 'secret_value'],
		]);

		$this->assertFalse($this->turnstile->is_available());
	}

	public function test_get_name(): void
	{
		$this->assertEquals('CAPTCHA_TURNSTILE', $this->turnstile->get_name());
	}

	public function test_set_Name(): void
	{
		$this->turnstile->set_name('custom_service');
		$service_name_property = new \ReflectionProperty($this->turnstile, 'service_name');
		$service_name_property->setAccessible(true);
		$this->assertEquals('custom_service', $service_name_property->getValue($this->turnstile));
	}

	public function test_validate_without_response(): void
	{
		// Test when there is no Turnstile response
		$this->request->method('variable')->with('cf-turnstile-response')->willReturn('');

		$this->assertFalse($this->turnstile->validate());
	}

	public function test_validate_with_response_success(): void
	{
		// Mock the request and response from the Turnstile API
		$this->request->method('variable')->with('cf-turnstile-response')->willReturn('valid_response');
		$this->request->method('header')->with('CF-Connecting-IP')->willReturn('127.0.0.1');

		// Mock the GuzzleHttp client and response
		$client_mock = $this->createMock(Client::class);
		$response_mock = $this->createMock(Response::class);

		$client_mock->method('request')->willReturn($response_mock);
		$response_mock->method('getBody')->willReturn(json_encode(['success' => true]));

		// Mock config values for secret
		$this->config->method('offsetGet')->willReturn('secret_value');

		// Use reflection to inject the mocked client into the turnstile class
		$reflection = new \ReflectionClass($this->turnstile);
		$client_property = $reflection->getProperty('client');
		$client_property->setAccessible(true);
		$client_property->setValue($this->turnstile, $client_mock);

		// Validate that the CAPTCHA was solved successfully
		$this->assertTrue($this->turnstile->validate());
	}

	public function test_has_config(): void
	{
		$this->assertTrue($this->turnstile->has_config());
	}

	public function test_get_client(): void
	{
		$turnstile_reflection = new \ReflectionClass($this->turnstile);
		$get_client_method = $turnstile_reflection->getMethod('get_client');
		$get_client_method->setAccessible(true);
		$client_property = $turnstile_reflection->getProperty('client');
		$client_property->setAccessible(true);

		$this->assertFalse($client_property->isInitialized($this->turnstile));
		$client = $get_client_method->invoke($this->turnstile);
		$this->assertNotNull($client);
		$this->assertInstanceOf(\GuzzleHttp\Client::class, $client);
		$this->assertTrue($client === $get_client_method->invoke($this->turnstile));
	}

	public function test_validate_with_response_failure(): void
	{
		// Mock the request and response from the Turnstile API
		$this->request->method('variable')->with('cf-turnstile-response')->willReturn('valid_response');
		$this->request->method('header')->with('CF-Connecting-IP')->willReturn('127.0.0.1');

		// Mock the GuzzleHttp client and response
		$client_mock = $this->createMock(Client::class);
		$response_mock = $this->createMock(Response::class);

		$client_mock->method('request')->willReturn($response_mock);
		$response_mock->method('getBody')->willReturn(json_encode(['success' => false]));

		// Mock config values for secret
		$this->config->method('offsetGet')->willReturn('secret_value');

		// Use reflection to inject the mocked client into the turnstile class
		$reflection = new \ReflectionClass($this->turnstile);
		$client_property = $reflection->getProperty('client');
		$client_property->setAccessible(true);
		$client_property->setValue($this->turnstile, $client_mock);

		// Validate that the CAPTCHA was not solved
		$this->assertFalse($this->turnstile->validate());
	}

	public function test_get_template(): void
	{
		// Mock is_solved to return false
		$is_solved_property = new \ReflectionProperty($this->turnstile, 'solved');
		$is_solved_property->setAccessible(true);
		$is_solved_property->setValue($this->turnstile, false);

		// Mock the template assignments
		$this->config->method('offsetGet')->willReturnMap([
			['captcha_turnstile_sitekey', 'sitekey_value'],
			['captcha_turnstile_theme', 'light'],
		]);

		$this->request->method('variable')->willReturnMap([
			['confirm_id', '', false, request_interface::REQUEST, 'confirm_id'],
			['confirm_code', '', false, request_interface::REQUEST, 'confirm_code']
		]);

		$this->template->expects($this->once())->method('assign_vars')->with([
			'S_TURNSTILE_AVAILABLE' => $this->turnstile->is_available(),
			'TURNSTILE_SITEKEY' => 'sitekey_value',
			'TURNSTILE_THEME' => 'light',
			'U_TURNSTILE_SCRIPT' => 'https://challenges.cloudflare.com/turnstile/v0/api.js',
			'CONFIRM_TYPE_REGISTRATION' => confirm_type::UNDEFINED->value,
		]);

		$this->assertEquals('captcha_turnstile.html', $this->turnstile->get_template());

		$is_solved_property->setValue($this->turnstile, true);
		$this->assertEquals('', $this->turnstile->get_template());
	}

	public function test_get_demo_template(): void
	{
		// Mock the config assignments
		$this->config->method('offsetGet')->willReturn('light');

		$this->template->expects($this->once())->method('assign_vars')->with([
			'TURNSTILE_THEME' => 'light',
			'U_TURNSTILE_SCRIPT' => 'https://challenges.cloudflare.com/turnstile/v0/api.js',
		]);

		$this->assertEquals('captcha_turnstile_acp_demo.html', $this->turnstile->get_demo_template());
	}
}

