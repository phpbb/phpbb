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
use phpbb\captcha\plugins\incomplete;
use phpbb\config\config;
use phpbb\language\language;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;

class phpbb_captcha_incomplete_test extends phpbb_database_test_case
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

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/../fixtures/empty.xml');
	}

	protected function setUp(): void
	{
		global $phpbb_root_path, $phpEx;

		$this->config = new config([]);
		$this->template = $this->getMockBuilder('\phpbb\template\twig\twig')
			->onlyMethods(['assign_vars'])
			->disableOriginalConstructor()
			->getMock();
		$this->template->method('assign_vars')
			->willReturnCallback([$this, 'assign_vars']);
		$db = $this->new_dbal();
		$language = $this->createMock(language::class);
		$request = $this->createMock(request::class);
		$user = $this->createMock(user::class);

		$this->incomplete_captcha = new incomplete(
			$this->config,
			$db,
			$language,
			$request,
			$this->template,
			$user,
			$phpbb_root_path,
			$phpEx
		);
	}

	public function test_miscellaneous_incomplete(): void
	{
		$this->assertTrue($this->incomplete_captcha->is_available());
		$this->assertFalse($this->incomplete_captcha->is_solved());
		$this->assertFalse($this->incomplete_captcha->validate());
		$this->assertFalse($this->incomplete_captcha->has_config());
		$this->incomplete_captcha->set_name('foo');
		$this->assertSame('CAPTCHA_INCOMPLETE', $this->incomplete_captcha->get_name());
		$this->incomplete_captcha->init(confirm_type::UNDEFINED);
		$this->assertEmpty($this->assigned_vars);
		$this->assertEmpty($this->incomplete_captcha->get_demo_template(0));
		$this->assertEmpty($this->incomplete_captcha->get_error());
		$this->assertSame(0, $this->incomplete_captcha->get_attempt_count());
	}

	public function test_get_tempate(): void
	{
		$this->incomplete_captcha->init(confirm_type::REGISTRATION);
		$this->assertSame('captcha_incomplete.html', $this->incomplete_captcha->get_template());
		$this->assertEquals('CONFIRM_INCOMPLETE', $this->assigned_vars['CONFIRM_LANG']);

		$this->assigned_vars = [];

		$this->incomplete_captcha->init(confirm_type::POST);
		$this->assertSame('captcha_incomplete.html', $this->incomplete_captcha->get_template());
		$this->assertEquals('CONFIRM_INCOMPLETE', $this->assigned_vars['CONFIRM_LANG']);
	}
}
