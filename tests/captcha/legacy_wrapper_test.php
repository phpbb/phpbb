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
use phpbb\captcha\plugins\legacy_wrapper;

class phpbb_captcha_legacy_wrapper_test extends phpbb_test_case
{
	private $legacy_captcha;
	private $legacy_wrapper;

	public function setUp(): void
	{
		$this->legacy_captcha = $this->createMock(stdClass::class);
		$this->legacy_wrapper = new legacy_wrapper($this->legacy_captcha);
	}

	public function test_is_available_with_method_exists(): void
	{
		// Simulate is_available method exists in the legacy captcha
		$this->legacy_captcha = $this->getMockBuilder(stdClass::class)
			->addMethods(['is_available'])
			->getMock();
		$this->legacy_captcha->method('is_available')->willReturn(true);
		$this->legacy_wrapper = new legacy_wrapper($this->legacy_captcha);

		$this->assertTrue($this->legacy_wrapper->is_available());
	}

	public function test_is_available_without_method_exists(): void
	{
		// Simulate is_available method does not exist in the legacy captcha
		$this->assertFalse($this->legacy_wrapper->is_available());
	}

	public function test_has_config_with_method_exists(): void
	{
		// Simulate has_config method exists in the legacy captcha
		$this->legacy_captcha = $this->getMockBuilder(stdClass::class)
			->addMethods(['has_config'])
			->getMock();
		$this->legacy_wrapper = new legacy_wrapper($this->legacy_captcha);
		$this->legacy_captcha->method('has_config')->willReturn(true);

		$this->assertTrue($this->legacy_wrapper->has_config());
	}

	public function test_has_config_without_method_exists(): void
	{
		// Simulate has_config method does not exist in the legacy captcha
		$this->assertFalse($this->legacy_wrapper->has_config());
	}

	public function test_get_name_with_method_exists(): void
	{
		// Simulate get_name method exists in the legacy captcha
		$this->legacy_captcha = $this->getMockBuilder(stdClass::class)
			->addMethods(['get_name'])
			->getMock();
		$this->legacy_wrapper = new legacy_wrapper($this->legacy_captcha);
		$this->legacy_captcha->method('get_name')->willReturn('LegacyCaptchaName');

		$this->assertSame('LegacyCaptchaName', $this->legacy_wrapper->get_name());
	}

	public function test_get_name_without_method_exists(): void
	{
		// Simulate get_name method does not exist in the legacy captcha
		$this->assertSame('', $this->legacy_wrapper->get_name());
	}

	public function test_set_name_with_method_exists(): void
	{
		// Simulate set_name method exists in the legacy captcha
		$this->legacy_captcha = $this->getMockBuilder(stdClass::class)
			->addMethods(['set_name'])
			->getMock();
		$this->legacy_wrapper = new legacy_wrapper($this->legacy_captcha);
		$this->legacy_captcha->expects($this->once())->method('set_name')->with('NewName');

		$this->legacy_wrapper->set_name('NewName');
	}

	public function test_init_with_method_exists(): void
	{
		// Simulate init method exists in the legacy captcha
		$this->legacy_captcha = $this->getMockBuilder(stdClass::class)
			->addMethods(['init'])
			->getMock();
		$this->legacy_wrapper = new legacy_wrapper($this->legacy_captcha);
		$this->legacy_captcha->expects($this->once())->method('init')->with(confirm_type::REGISTRATION->value);

		$this->legacy_wrapper->init(confirm_type::REGISTRATION);
	}

	public function test_get_hidden_fields_with_method_exists(): void
	{
		// Simulate get_hidden_fields method exists in the legacy captcha
		$this->legacy_captcha = $this->getMockBuilder(stdClass::class)
			->addMethods(['get_hidden_fields'])
			->getMock();
		$this->legacy_wrapper = new legacy_wrapper($this->legacy_captcha);
		$this->legacy_captcha->method('get_hidden_fields')->willReturn(['field1' => 'value1']);

		$this->assertSame(['field1' => 'value1'], $this->legacy_wrapper->get_hidden_fields());
	}

	public function test_get_hidden_fields_without_method_exists(): void
	{
		// Simulate get_hidden_fields method does not exist in the legacy captcha
		$this->assertSame([], $this->legacy_wrapper->get_hidden_fields());
	}

	public function test_validate_with_error(): void
	{
		// Simulate validate method returns an error
		$this->legacy_captcha = $this->getMockBuilder(stdClass::class)
			->addMethods(['validate'])
			->getMock();
		$this->legacy_wrapper = new legacy_wrapper($this->legacy_captcha);
		$this->legacy_captcha->method('validate')->willReturn('Captcha Error');

		$this->assertFalse($this->legacy_wrapper->validate());
		$this->assertSame('Captcha Error', $this->legacy_wrapper->get_error());
	}

	public function test_validate_without_method_exists(): void
	{
		$this->assertFalse($this->legacy_wrapper->validate());
	}

	public function test_validate_without_error(): void
	{
		// Simulate validate method does not return an error
		$this->legacy_captcha = $this->getMockBuilder(stdClass::class)
			->addMethods(['validate'])
			->getMock();
		$this->legacy_wrapper = new legacy_wrapper($this->legacy_captcha);
		$this->legacy_captcha->method('validate')->willReturn(null);

		$this->assertTrue($this->legacy_wrapper->validate());
	}

	public function test_is_solved_with_method_exists(): void
	{
		// Simulate is_solved method exists in the legacy captcha
		$this->legacy_captcha = $this->getMockBuilder(stdClass::class)
			->addMethods(['is_solved'])
			->getMock();
		$this->legacy_wrapper = new legacy_wrapper($this->legacy_captcha);
		$this->legacy_captcha->method('is_solved')->willReturn(true);

		$this->assertTrue($this->legacy_wrapper->is_solved());
	}

	public function test_is_solved_without_method_exists(): void
	{
		// Simulate is_solved method does not exist in the legacy captcha
		$this->assertFalse($this->legacy_wrapper->is_solved());
	}

	public function test_reset_with_method_exists(): void
	{
		// Simulate reset method exists in the legacy captcha
		$this->legacy_captcha = $this->getMockBuilder(stdClass::class)
			->addMethods(['reset'])
			->getMock();
		$this->legacy_wrapper = new legacy_wrapper($this->legacy_captcha);
		$this->legacy_captcha->expects($this->once())->method('reset');

		$this->legacy_wrapper->reset();
	}

	public function test_get_attempt_count_with_method_exists(): void
	{
		// Simulate get_attempt_count method exists in the legacy captcha
		$this->legacy_captcha = $this->getMockBuilder(stdClass::class)
			->addMethods(['get_attempt_count'])
			->getMock();
		$this->legacy_wrapper = new legacy_wrapper($this->legacy_captcha);
		$this->legacy_captcha->method('get_attempt_count')->willReturn(5);

		$this->assertSame(5, $this->legacy_wrapper->get_attempt_count());
	}

	public function test_get_attempt_count_without_method_exists(): void
	{
		// Simulate get_attempt_count method does not exist in the legacy captcha
		$this->assertSame(PHP_INT_MAX, $this->legacy_wrapper->get_attempt_count());
	}

	public function test_get_template_with_method_exists(): void
	{
		// Simulate get_template method exists in the legacy captcha
		$this->legacy_captcha = $this->getMockBuilder(stdClass::class)
			->addMethods(['get_template'])
			->getMock();
		$this->legacy_wrapper = new legacy_wrapper($this->legacy_captcha);
		$this->legacy_captcha->method('get_template')->willReturn('template_content');

		$this->assertSame('template_content', $this->legacy_wrapper->get_template());
	}

	public function test_get_template_without_method_exists(): void
	{
		// Simulate get_template method does not exist in the legacy captcha
		$this->assertSame('', $this->legacy_wrapper->get_template());
	}

	public function test_get_demo_template_with_method_exists(): void
	{
		// Simulate get_demo_template method exists in the legacy captcha
		$this->legacy_captcha = $this->getMockBuilder(stdClass::class)
			->addMethods(['get_demo_template'])
			->getMock();
		$this->legacy_wrapper = new legacy_wrapper($this->legacy_captcha);
		$this->legacy_captcha->method('get_demo_template')->willReturn('demo_template_content');

		$this->assertSame('demo_template_content', $this->legacy_wrapper->get_demo_template());
	}

	public function test_get_demo_template_without_method_exists(): void
	{
		// Simulate get_demo_template method does not exist in the legacy captcha
		$this->assertSame('', $this->legacy_wrapper->get_demo_template());
	}

	public function test_garbage_collect_with_method_exists(): void
	{
		// Simulate garbage_collect method exists in the legacy captcha
		$this->legacy_captcha = $this->getMockBuilder(stdClass::class)
			->addMethods(['garbage_collect'])
			->getMock();
		$this->legacy_wrapper = new legacy_wrapper($this->legacy_captcha);
		$this->legacy_captcha->expects($this->once())->method('garbage_collect')->with(confirm_type::REGISTRATION->value);

		$this->legacy_wrapper->garbage_collect(confirm_type::REGISTRATION);
	}

	public function test_acp_page_with_method_exists(): void
	{
		// Simulate acp_page method exists in the legacy captcha
		$this->legacy_captcha = $this->getMockBuilder(stdClass::class)
			->addMethods(['acp_page'])
			->getMock();
		$this->legacy_wrapper = new legacy_wrapper($this->legacy_captcha);
		$this->legacy_captcha->expects($this->once())->method('acp_page')->with(1, 'module');

		$this->legacy_wrapper->acp_page(1, 'module');
	}
}
