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

class phpbb_template_template_test_case extends phpbb_test_case
{
	protected $lang;
	protected $template;
	protected $template_path;
	protected $user;

	protected $test_path = 'tests/template';

	// Keep the contents of the cache for debugging?
	const PRESERVE_CACHE = true;

	protected static $language_reflection_lang;

	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		$reflection = new ReflectionClass('\phpbb\language\language');
		self::$language_reflection_lang = $reflection->getProperty('lang');
		self::$language_reflection_lang->setAccessible(true);
	}

	protected function display($handle)
	{
		ob_start();

		try
		{
			$this->template->display($handle, false);
		}
		catch (Exception $exception)
		{
			// reset output buffering even when an error occurred
			// PHPUnit turns trigger_error into exceptions as well
			ob_end_clean();
			throw $exception;
		}

		$result = self::trim_template_result(ob_get_clean());

		return $result;
	}

	protected static function trim_template_result($result)
	{
		return str_replace("\n\n", "\n", implode("\n", array_map('trim', explode("\n", trim($result)))));
	}

	protected function config_defaults()
	{
		$defaults = array(
			'load_tplcompile'	=> true,
		);
		return $defaults;
	}

	protected function setup_engine(array $new_config = array())
	{
		global $phpbb_root_path, $phpEx;

		$defaults = $this->config_defaults();
		$config = new \phpbb\config\config(array_merge($defaults, $new_config));
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$this->lang = $lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$this->user = $user;

		$filesystem = new \phpbb\filesystem\filesystem();

		$path_helper = new \phpbb\path_helper(
			new \phpbb\symfony_request(
				new phpbb_mock_request()
			),
			$this->createMock('\phpbb\request\request'),
			$phpbb_root_path,
			$phpEx
		);

		$this->template_path = $this->test_path . '/templates';

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
			array(
				'cache'			=> false,
				'debug'			=> false,
				'auto_reload'	=> true,
				'autoescape'	=> false,
			)
		);
		$this->template = new phpbb\template\twig\twig($path_helper, $config, $context, $twig, $cache_path, $this->user, array(new \phpbb\template\twig\extension($context, $twig, $lang)));
		$twig->setLexer(new \phpbb\template\twig\lexer($twig));
		$this->template->set_custom_style('tests', $this->template_path);
	}

	protected function setUp(): void
	{
		// Test the engine can be used
		$this->setup_engine();

		global $phpbb_filesystem;

		$phpbb_filesystem = new \phpbb\filesystem\filesystem();
	}

	protected function run_template($file, array $vars, array $block_vars, array $destroy, $expected, $lang_vars = array())
	{
		$this->template->set_filenames(array('test' => $file));
		$this->template->assign_vars($vars);

		foreach ($block_vars as $block => $loops)
		{
			foreach ($loops as $_vars)
			{
				$this->template->assign_block_vars($block, $_vars);
			}
		}

		foreach ($destroy as $block)
		{
			$this->template->destroy_block_vars($block);
		}

		// Previous functionality was $cachefile (string), which was removed, check to prevent errors
		if (is_array($lang_vars))
		{
			foreach ($lang_vars as $name => $value)
			{
				self::$language_reflection_lang->setValue($this->lang, array_merge(
					self::$language_reflection_lang->getValue($this->lang),
					array($name => $value)
				));
			}
		}

		$expected = str_replace(array("\n", "\r", "\t"), '', $expected);
		$output = str_replace(array("\n", "\r", "\t"), '', $this->display('test'));

		$this->assertEquals($expected, $output, "Testing $file");
	}
}
