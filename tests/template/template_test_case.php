<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/template.php';

class phpbb_template_template_test_case extends phpbb_test_case
{
	protected $template;
	protected $template_path;

	// Keep the contents of the cache for debugging?
	const PRESERVE_CACHE = true;

	protected function display($handle)
	{
		ob_start();
		$this->assertTrue($this->template->display($handle, false));
		return self::trim_template_result(ob_get_clean());
	}

	protected static function trim_template_result($result)
	{
		return str_replace("\n\n", "\n", implode("\n", array_map('trim', explode("\n", trim($result)))));
	}

	protected function setup_engine()
	{
		global $phpbb_root_path, $phpEx, $config, $user;

		$this->template_path = dirname(__FILE__) . '/templates';
		$this->template = new phpbb_template($phpbb_root_path, $phpEx, $config, $user);
		$this->template->set_custom_template($this->template_path, 'tests');
	}

	protected function setUp()
	{
		// Test the engine can be used
		$this->setup_engine();

		if (!is_writable(dirname($this->template->cachepath)))
		{
			$this->markTestSkipped("Template cache directory is not writable.");
		}

		foreach (glob($this->template->cachepath . '*') as $file)
		{
			unlink($file);
		}

		$GLOBALS['config'] = array(
			'load_tplcompile'	=> true,
			'tpl_allow_php'		=> false,
		);
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
