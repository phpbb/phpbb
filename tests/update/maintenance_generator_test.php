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
use phpbb\filesystem\filesystem_interface;
use phpbb\language\language_file_helper;
use phpbb\language\language_file_loader;
use phpbb\template\template;
use phpbb\update\maintenance_generator;

class phpbb_update_maintenance_generator_test extends phpbb_test_case
{
	/** @var config */
	private config $config;

	/** @var filesystem_interface&\PHPUnit\Framework\MockObject\MockObject */
	private filesystem_interface $filesystem_mock;

	/** @var language_file_helper&\PHPUnit\Framework\MockObject\MockObject */
	private language_file_helper $lang_helper_mock;

	/** @var language_file_loader&\PHPUnit\Framework\MockObject\MockObject */
	private language_file_loader $lang_loader_mock;

	/** @var template&\PHPUnit\Framework\MockObject\MockObject */
	private template $template_mock;

	/** @var string */
	private string $phpbb_root_path;

	/** @var string */
	private string $php_ext = 'php';

	protected function setUp(): void
	{
		parent::setUp();

		$this->phpbb_root_path = __DIR__ . '/../tmp/';

		if (!is_dir($this->phpbb_root_path . 'store'))
		{
			mkdir($this->phpbb_root_path . 'store', 0777, true);
		}

		$this->config = new config([
			'sitename'		=> 'Test Board',
			'default_lang'	=> 'en',
		]);

		$this->filesystem_mock	= $this->createMock(filesystem_interface::class);
		$this->lang_helper_mock	= $this->createMock(language_file_helper::class);
		$this->lang_loader_mock	= $this->createMock(language_file_loader::class);
		$this->template_mock	= $this->createMock(template::class);
	}

	protected function tearDown(): void
	{
		if (is_dir($this->phpbb_root_path . 'store'))
		{
			rmdir($this->phpbb_root_path . 'store');
		}
	}

	private function create_generator(): maintenance_generator
	{
		return new maintenance_generator(
			$this->config,
			$this->filesystem_mock,
			$this->lang_helper_mock,
			$this->lang_loader_mock,
			$this->template_mock,
			$this->phpbb_root_path,
			$this->php_ext
		);
	}

	public function test_write_maintenance_lock_no_social_links(): void
	{
		$this->lang_helper_mock->expects($this->once())
			->method('get_available_languages')
			->willReturn([]);

		$this->template_mock->expects($this->never())
			->method('assign_block_vars');

		$this->template_mock->expects($this->once())
			->method('assign_vars')
			->with($this->callback(function (array $vars): bool {
				return array_key_exists('DEFAULT_LANG', $vars)
					&& array_key_exists('LANG_DATA', $vars)
					&& array_key_exists('MAINTENANCE_INITIATED', $vars)
					&& array_key_exists('SITENAME', $vars)
					&& $vars['DEFAULT_LANG'] === 'en'
					&& $vars['SITENAME'] === 'Test Board';
			}));

		$this->template_mock->expects($this->once())
			->method('assign_display')
			->with('maintenance_page.html')
			->willReturn('<html>maintenance</html>');

		$this->filesystem_mock->expects($this->once())
			->method('dump_file');

		$this->create_generator()->write_maintenance_lock();
	}

	public function test_write_maintenance_lock_export_exception(): void
	{
		$this->lang_helper_mock->expects($this->once())
			->method('get_available_languages')
			->willReturn([]);

		$this->template_mock->expects($this->never())
			->method('assign_block_vars');

		$this->template_mock->expects($this->once())
			->method('assign_vars')
			->with($this->callback(function (array $vars): bool {
				return array_key_exists('DEFAULT_LANG', $vars)
					&& array_key_exists('LANG_DATA', $vars)
					&& array_key_exists('MAINTENANCE_INITIATED', $vars)
					&& array_key_exists('SITENAME', $vars)
					&& $vars['DEFAULT_LANG'] === 'en'
					&& $vars['SITENAME'] === 'Test Board';
			}));

		$unserializable = function() {
			return "I break things.";
		};
		$this->template_mock->expects($this->once())
			->method('assign_display')
			->with('maintenance_page.html')
			->willReturn($unserializable);

		$generator = $this->create_generator();

		$this->phpbb_root_path = __DIR__ . '/../tmp/';

		$this->expectException(\Symfony\Component\VarExporter\Exception\ExceptionInterface::class);

		$generator->write_maintenance_lock();
	}

	public function test_write_maintenance_lock_with_social_links(): void
	{
		$social_links = [
			[
				'ICON'	=> 'fa-twitter',
				'URL'	=> 'https://twitter.com/phpbb',
				'NAME'	=> 'Twitter',
			],
			[
				'URL'	=> 'https://github.com/phpbb',
				'NAME'	=> 'GitHub',
			],
		];

		$this->lang_helper_mock->method('get_available_languages')
			->willReturn([]);

		$block_calls = [];
		$this->template_mock->expects($this->exactly(2))
			->method('assign_block_vars')
			->willReturnCallback(function (string $blockname, array $vars) use (&$block_calls): void {
				$block_calls[] = [$blockname, $vars];
			});

		$this->template_mock->method('assign_vars');
		$this->template_mock->method('assign_display')
			->willReturn('');
		$this->filesystem_mock->method('dump_file');

		$this->create_generator()->write_maintenance_lock($social_links);

		$this->assertCount(2, $block_calls);

		[$first_block, $first_vars] = $block_calls[0];
		$this->assertSame('links', $first_block);
		$this->assertSame('fa-twitter', $first_vars['ICON']);
		$this->assertSame('https://twitter.com/phpbb', $first_vars['URL']);
		$this->assertSame('Twitter', $first_vars['NAME']);

		[$second_block, $second_vars] = $block_calls[1];
		$this->assertSame('links', $second_block);
		$this->assertSame('https://github.com/phpbb', $second_vars['URL']);
		$this->assertSame('GitHub', $second_vars['NAME']);
	}

	public function test_write_maintenance_lock_file_path(): void
	{
		$this->lang_helper_mock->method('get_available_languages')
			->willReturn([]);

		$this->template_mock->method('assign_vars');
		$this->template_mock->method('assign_display')
			->willReturn('');

		$expected_path = $this->phpbb_root_path . 'store/UPDATE_LOCK.' . $this->php_ext;

		$this->filesystem_mock->expects($this->once())
			->method('dump_file')
			->with($expected_path, $this->isType('string'));

		$this->create_generator()->write_maintenance_lock();
	}

	public function test_write_maintenance_lock_file_content_structure(): void
	{
		$template_content = '<html>Board is under maintenance</html>';

		$this->lang_helper_mock->method('get_available_languages')
			->willReturn([]);

		$this->template_mock->method('assign_vars');
		$this->template_mock->expects($this->once())
			->method('assign_display')
			->with('maintenance_page.html')
			->willReturn($template_content);

		$captured_content = null;
		$this->filesystem_mock->expects($this->once())
			->method('dump_file')
			->willReturnCallback(function (string $path, string $content) use (&$captured_content): void {
				$captured_content = $content;
			});

		$this->create_generator()->write_maintenance_lock();

		$this->assertNotNull($captured_content);
		$this->assertStringStartsWith('<?php', $captured_content);
		$this->assertStringContainsString('phpBB Maintenance Lock File', $captured_content);
		$this->assertStringContainsString("if (!defined('IN_PHPBB'))", $captured_content);
		$this->assertStringContainsString('return ', $captured_content);
		$this->assertStringContainsString("'content'", $captured_content);
		$this->assertStringContainsString($template_content, $captured_content);
	}

	public function test_write_maintenance_lock_with_multiple_languages(): void
	{
		$available_languages = [
			['iso' => 'en', 'local_name' => 'English'],
			['iso' => 'de', 'local_name' => 'Deutsch'],
		];

		$lang_data = [
			'en' => [
				'BOARD_MAINTENANCE'			=> 'Board under maintenance',
				'BOARD_MAINTENANCE_START'	=> 'Maintenance started',
				'BOARD_MAINTENANCE_TITLE'	=> 'Maintenance',
			],
			'de' => [
				'BOARD_MAINTENANCE'			=> 'Wartungsmodus',
				'BOARD_MAINTENANCE_START'	=> 'Wartung begonnen',
				'BOARD_MAINTENANCE_TITLE'	=> 'Wartung',
			],
		];

		$this->lang_helper_mock->expects($this->once())
			->method('get_available_languages')
			->willReturn($available_languages);

		$this->lang_loader_mock->expects($this->exactly(2))
			->method('load')
			->willReturnCallback(function (string $component, string $iso, array &$lang) use ($lang_data): void {
				$lang = array_merge($lang, $lang_data[$iso] ?? []);
			});

		$captured_vars = null;
		$this->template_mock->expects($this->once())
			->method('assign_vars')
			->willReturnCallback(function (array $vars) use (&$captured_vars): void {
				$captured_vars = $vars;
			});

		$this->template_mock->method('assign_display')
			->willReturn('');
		$this->filesystem_mock->method('dump_file');

		$this->create_generator()->write_maintenance_lock();

		$this->assertNotNull($captured_vars);
		$decoded = json_decode($captured_vars['LANG_DATA'], true);

		$this->assertArrayHasKey('en', $decoded);
		$this->assertSame('Board under maintenance', $decoded['en']['BOARD_MAINTENANCE']);
		$this->assertSame('Maintenance started', $decoded['en']['BOARD_MAINTENANCE_START']);
		$this->assertSame('Maintenance', $decoded['en']['BOARD_MAINTENANCE_TITLE']);

		$this->assertArrayHasKey('de', $decoded);
		$this->assertSame('Wartungsmodus', $decoded['de']['BOARD_MAINTENANCE']);
		$this->assertSame('Wartung begonnen', $decoded['de']['BOARD_MAINTENANCE_START']);
		$this->assertSame('Wartung', $decoded['de']['BOARD_MAINTENANCE_TITLE']);
	}

	public function test_get_language_vars_missing_keys_default_to_empty_string(): void
	{
		$available_languages = [
			['iso' => 'fr', 'local_name' => 'Français'],
		];

		$this->lang_helper_mock->method('get_available_languages')
			->willReturn($available_languages);

		// Loader only populates BOARD_MAINTENANCE; the other two keys are absent.
		$this->lang_loader_mock->method('load')
			->willReturnCallback(function (string $component, string $iso, array &$lang): void {
				$lang['BOARD_MAINTENANCE'] = 'Maintenance du forum';
			});

		$captured_vars = null;
		$this->template_mock->method('assign_vars')
			->willReturnCallback(function (array $vars) use (&$captured_vars): void {
				$captured_vars = $vars;
			});

		$this->template_mock->method('assign_display')
			->willReturn('');
		$this->filesystem_mock->method('dump_file');

		$this->create_generator()->write_maintenance_lock();

		$decoded = json_decode($captured_vars['LANG_DATA'], true);

		$this->assertArrayHasKey('fr', $decoded);
		$this->assertSame('Maintenance du forum', $decoded['fr']['BOARD_MAINTENANCE']);
		$this->assertSame('', $decoded['fr']['BOARD_MAINTENANCE_START']);
		$this->assertSame('', $decoded['fr']['BOARD_MAINTENANCE_TITLE']);
	}

	public function test_write_maintenance_lock_maintenance_initiated_is_current_timestamp(): void
	{
		$this->lang_helper_mock->method('get_available_languages')
			->willReturn([]);

		$time_before = time();

		$captured_vars = null;
		$this->template_mock->expects($this->once())
			->method('assign_vars')
			->willReturnCallback(function (array $vars) use (&$captured_vars): void {
				$captured_vars = $vars;
			});

		$this->template_mock->method('assign_display')
			->willReturn('');
		$this->filesystem_mock->method('dump_file');

		$this->create_generator()->write_maintenance_lock();

		$time_after = time();

		$this->assertNotNull($captured_vars);
		$this->assertGreaterThanOrEqual($time_before, $captured_vars['MAINTENANCE_INITIATED']);
		$this->assertLessThanOrEqual($time_after, $captured_vars['MAINTENANCE_INITIATED']);
	}

	public function test_write_maintenance_lock_lang_data_is_valid_json(): void
	{
		$this->lang_helper_mock->method('get_available_languages')
			->willReturn([]);

		$captured_vars = null;
		$this->template_mock->expects($this->once())
			->method('assign_vars')
			->willReturnCallback(function (array $vars) use (&$captured_vars): void {
				$captured_vars = $vars;
			});

		$this->template_mock->method('assign_display')
			->willReturn('');
		$this->filesystem_mock->method('dump_file');

		$this->create_generator()->write_maintenance_lock();

		$this->assertNotNull($captured_vars);
		$decoded = json_decode($captured_vars['LANG_DATA'], true);
		$this->assertNotNull($decoded, 'LANG_DATA must be valid JSON');
		$this->assertIsArray($decoded);
	}
}

