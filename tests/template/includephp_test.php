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

require_once dirname(__FILE__) . '/template_test_case.php';

class phpbb_template_includephp_test extends phpbb_template_template_test_case
{
	public function test_includephp_relative()
	{
		$this->setup_engine(array('tpl_allow_php' => true));

		$this->run_template('includephp_relative.html', array(), array(), array(), "Path is relative to board root.\ntesting included php");

		$this->template->set_filenames(array('test' => 'includephp_relative.html'));
		$this->assertEquals("Path is relative to board root.\ntesting included php", $this->display('test'), "Testing INCLUDEPHP");
	}

	public function test_includephp_variables()
	{
		$this->setup_engine(array('tpl_allow_php' => true));

		$this->run_template('includephp_variables.html', array('TEMPLATES' => 'templates'), array(), array(), "Path includes variables.\ntesting included php");

		$this->template->set_filenames(array('test' => 'includephp_variables.html'));
		$this->assertEquals("Path includes variables.\ntesting included php", $this->display('test'), "Testing INCLUDEPHP");
	}

	public function test_includephp_absolute()
	{
		global $phpbb_root_path;

		$filesystem = new \phpbb\filesystem\filesystem();
		$path_to_php = str_replace('\\', '/', dirname(__FILE__)) . '/templates/_dummy_include.php.inc';
		$this->assertTrue($filesystem->is_absolute_path($path_to_php));
		$template_text = "Path is absolute.\n<!-- INCLUDEPHP $path_to_php -->";

		$cache_dir = $phpbb_root_path . 'cache/';
		$fp = fopen($cache_dir . 'includephp_absolute.html', 'w');
		fputs($fp, $template_text);
		fclose($fp);

		$this->setup_engine(array('tpl_allow_php' => true));

		$this->template->set_custom_style('tests', $cache_dir);

		$this->run_template('includephp_absolute.html', array(), array(), array(), "Path is absolute.\ntesting included php");

		$this->template->set_filenames(array('test' => 'includephp_absolute.html'));
		$this->assertEquals("Path is absolute.\ntesting included php", $this->display('test'), "Testing INCLUDEPHP");
	}
}
