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

namespace phpbb\tests\template;

use phpbb\filesystem\helper as filesystem_helper;
use phpbb\template\twig\twig;

class twig_test extends \phpbb_test_case
{
	/** @var twig */
	public $twig;
	/**
	 * @var string
	 */
	private $template_path;
	/**
	 * @var twig
	 */
	private $template;
	/**
	 * @var \phpbb\user
	 */
	private $user;
	/**
	 * @var \phpbb\language\language
	 */
	private $lang;

	protected function setUp(): void
	{
		global $phpbb_root_path, $phpEx;

		$config = new \phpbb\config\config([]);
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$this->lang = $lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$this->user = $user;

		$filesystem = new \phpbb\filesystem\filesystem();

		$path_helper = new \phpbb\path_helper(
			new \phpbb\symfony_request(
				new \phpbb_mock_request()
			),
			$this->createMock('\phpbb\request\request'),
			$phpbb_root_path,
			$phpEx
		);

		$this->template_path = 'tests/template/templates';

		$cache_path = $phpbb_root_path . 'cache/twig';
		$context = new \phpbb\template\context();
		$loader = new \phpbb\template\twig\loader('');
		$log = new \phpbb\log\dummy();
		$assets_bag = new \phpbb\template\assets_bag();
		$twig = new \phpbb\template\twig\environment(
			$assets_bag,
			$config,
			$filesystem,
			$path_helper,
			$cache_path,
			null,
			$loader,
			new \phpbb\event\dispatcher(),
			[
				'cache'			=> false,
				'debug'			=> false,
				'auto_reload'	=> true,
				'autoescape'	=> false,
			]
		);
		$this->template = new \phpbb\template\twig\twig($path_helper, $config, $context, $twig, $cache_path, $this->user, array(new \phpbb\template\twig\extension($context, $twig, $this->user)));
		$twig->setLexer(new \phpbb\template\twig\lexer($twig));
	}

	public function test_get_user_style_invalid_user()
	{
		// Add closure to override user method
		$set_user_closure = function ($user) {
			$this->user = $user;
		};

		$run_set_user_closure = $set_user_closure->bindTo($this->template, get_class($this->template));
		$run_set_user_closure(null);

		$this->expectException('\phpbb\template\exception\user_object_not_available');
		$this->template->get_user_style();
		$run_set_user_closure($this->user);
	}

	public function data_get_user_style(): array
	{
		return [
			[['style_path' => 'prosilver', 'style_parent_id' => 0], ['prosilver']],
			[['style_path' => 'prosilver_se', 'style_parent_id' => 5, 'style_parent_tree' => 'prosilver'], ['prosilver_se', 'prosilver']],
		];
	}

	/**
	 * @dataProvider data_get_user_style
	 */
	public function test_get_user_style($user_style, $expected)
	{
		$this->user->style = $user_style;
		$this->assertEquals($expected, $this->template->get_user_style());
	}

	public function test_set_style()
	{
		global $phpbb_root_path;

		// User style is left empty on purpose to see template as valid directory
		$tests_template_relative_path = '../tests/template';
		$test_template_absolute_path = filesystem_helper::realpath($phpbb_root_path . trim($tests_template_relative_path, '/'));

		// Get loader instance
		$template_reflection = new \ReflectionObject($this->template);
		$loader_reflection = $template_reflection->getProperty('loader');
		$loader_reflection->setAccessible(true);
		/** @var \phpbb\template\twig\loader $loader */
		$loader = $loader_reflection->getValue($this->template);

		// set_style() not called yet
		$this->assertEmpty($loader->getSafeDirectories());

		// set_style() to add default elements
		$this->user->style = ['style_path' => '', 'style_parent_id' => 0];
		$this->template->set_style();
		$safe_directories = $loader->getSafeDirectories();
		$this->assertFalse(in_array($test_template_absolute_path, $safe_directories));
		$this->assertFalse(empty($safe_directories));

		// set_style() with tests template folder
		$this->template->set_style([$tests_template_relative_path]);
		$this->assertTrue(in_array($test_template_absolute_path, $loader->getSafeDirectories()));
	}
}
