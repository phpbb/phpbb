<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_template_template_test_case extends phpbb_test_case
{
	protected $template;
	protected $template_path;
	protected $template_locator;

	// Keep the contents of the cache for debugging?
	const PRESERVE_CACHE = true;

	protected function display($handle)
	{
		ob_start();

		try
		{
			$this->assertTrue($this->template->display($handle, false));
		}
		catch (Exception $exception)
		{
			// reset output buffering even when an error occured
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

	protected function setup_engine(array $new_config = array())
	{
		global $phpbb_root_path, $phpEx, $user;

		$defaults = array(
			'load_tplcompile'	=> true,
			'tpl_allow_php'		=> false,
		);

		$config = new phpbb_config(array_merge($defaults, $new_config));

		$this->template_path = dirname(__FILE__) . '/templates';
		$this->template_locator = new phpbb_template_locator();
		$this->template = new phpbb_template($phpbb_root_path, $phpEx, $config, $user, $this->template_locator);
		$this->template->set_custom_template($this->template_path, 'tests');
	}

	protected function setUp()
	{
		// Test the engine can be used
		$this->setup_engine();

		$template_cache_dir = dirname($this->template->cachepath);
		if (!is_writable($template_cache_dir))
		{
			$this->markTestSkipped("Template cache directory ({$template_cache_dir}) is not writable.");
		}

		foreach (glob($this->template->cachepath . '*') as $file)
		{
			unlink($file);
		}

		$this->setup_engine();
	}

	protected function tearDown()
	{
		if (is_object($this->template))
		{
			foreach (glob($this->template->cachepath . '*') as $file)
			{
				unlink($file);
			}
		}
	}

	protected function run_template($file, array $vars, array $block_vars, array $destroy, $expected, $cache_file)
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

		try
		{
			$this->assertEquals($expected, $this->display('test'), "Testing $file");
			$this->assertFileExists($cache_file);
		}
		catch (ErrorException $e)
		{
			if (file_exists($cache_file))
			{
				copy($cache_file, str_replace('ctpl_', 'tests_ctpl_', $cache_file));
			}
			throw $e;
		}

		// For debugging.
		// When testing eval path the cache file may not exist.
		if (self::PRESERVE_CACHE && file_exists($cache_file))
		{
			copy($cache_file, str_replace('ctpl_', 'tests_ctpl_', $cache_file));
		}
	}
}
