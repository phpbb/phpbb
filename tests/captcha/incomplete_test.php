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

use phpbb\captcha\plugins\incomplete;
use phpbb\config\config;
use phpbb\template\template;

class phpbb_captcha_incomplete_test extends phpbb_test_case
{
	protected config $config;

	protected template $template;


	/** @var incomplete */
	protected incomplete $incomplete_captcha;

	protected array $assigned_vars = [];

	public function assign_vars(array $vars): void
	{
		$this->assigned_vars = array_merge($this->assigned_vars, $vars);
	}

	protected function setUp(): void
	{
		global $phpbb_root_path, $phpEx;

		$this->config = new config([]);
		$this->template = $this->getMockBuilder('\phpbb\template\twig\twig')
			->setMethods(['assign_vars'])
			->disableOriginalConstructor()
			->getMock();
		$this->template->method('assign_vars')
			->willReturnCallback([$this, 'assign_vars']);

		$this->incomplete_captcha = new incomplete(
			$this->config,
			$this->template,
			$phpbb_root_path,
			$phpEx
		);
	}

	public function test_miscellaneous_incomplete(): void
	{
		$this->assertTrue($this->incomplete_captcha->is_available());
		$this->assertFalse($this->incomplete_captcha->is_solved());
		$this->assertFalse($this->incomplete_captcha->validate());
		$this->assertSame('CAPTCHA_INCOMPLETE', incomplete::get_name());
		$this->incomplete_captcha->init(0);
		$this->incomplete_captcha->execute();
		$this->incomplete_captcha->execute_demo();
		$this->assertEmpty($this->assigned_vars);
		$this->assertEmpty($this->incomplete_captcha->get_demo_template(0));
	}

	public function test_get_generator_class(): void
	{
		$this->expectException(\phpbb\exception\runtime_exception::class);
		$this->incomplete_captcha->get_generator_class();
	}

	public function test_get_tempate(): void
	{
		$this->incomplete_captcha->init(CONFIRM_REG);
		$this->assertSame('captcha_incomplete.html', $this->incomplete_captcha->get_template());
		$this->assertEquals('CONFIRM_INCOMPLETE', $this->assigned_vars['CONFIRM_LANG']);

		$this->assigned_vars = [];

		$this->incomplete_captcha->init(CONFIRM_POST);
		$this->assertSame('captcha_incomplete.html', $this->incomplete_captcha->get_template());
		$this->assertEquals('POST_CONFIRM_INCOMPLETE', $this->assigned_vars['CONFIRM_LANG']);
	}
}
