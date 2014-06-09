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

class phpbb_template_template_test_case extends phpbb_test_case
{
	protected $template;
	protected $template_path;
	protected $user;

	protected $test_path = 'tests/template';

	// Keep the contents of the cache for debugging?
	const PRESERVE_CACHE = true;

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
			'tpl_allow_php'		=> false,
		);
		return $defaults;
	}

	protected function setup_engine(array $new_config = array())
	{
		global $phpbb_root_path, $phpEx;

		$defaults = $this->config_defaults();
		$config = new \phpbb\config\config(array_merge($defaults, $new_config));
		$this->user = new \phpbb\user;

		$path_helper = new \phpbb\path_helper(
			new \phpbb\symfony_request(
				new phpbb_mock_request()
			),
			new \phpbb\filesystem(),
			$phpbb_root_path,
			$phpEx
		);

		$this->template_path = $this->test_path . '/templates';
		$this->template = new \phpbb\template\twig\twig($path_helper, $config, $this->user, new \phpbb\template\context());
		$this->template->set_custom_style('tests', $this->template_path);
	}

	protected function setUp()
	{
		// Test the engine can be used
		$this->setup_engine();

		$this->template->clear_cache();
	}

	protected function tearDown()
	{
		if ($this->template)
		{
			$this->template->clear_cache();
		}
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
				$this->user->lang[$name] = $value;
			}
		}

		$expected = str_replace(array("\n", "\r", "\t"), '', $expected);
		$output = str_replace(array("\n", "\r", "\t"), '', $this->display('test'));
		$this->assertEquals($expected, $output, "Testing $file");
	}
}
