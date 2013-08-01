<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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

		$path_to_php = str_replace('\\', '/', dirname(__FILE__)) . '/templates/_dummy_include.php.inc';
		$this->assertTrue(phpbb_is_absolute($path_to_php));
		$template_text = "Path is absolute.\n<!-- INCLUDEPHP $path_to_php -->";

		$cache_dir = $phpbb_root_path . 'cache/';
		$fp = fopen($cache_dir . 'includephp_absolute.html', 'w');
		fputs($fp, $template_text);
		fclose($fp);

		$this->setup_engine(array('tpl_allow_php' => true));

		$this->style->set_custom_style('tests', $cache_dir, array(), '');

		$this->run_template('includephp_absolute.html', array(), array(), array(), "Path is absolute.\ntesting included php");

		$this->template->set_filenames(array('test' => 'includephp_absolute.html'));
		$this->assertEquals("Path is absolute.\ntesting included php", $this->display('test'), "Testing INCLUDEPHP");
	}
}
