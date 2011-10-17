<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/template_test_case.php';

class phpbb_template_includephp_test extends phpbb_template_template_test_case
{
	public function test_includephp_relative()
	{
		$this->setup_engine(array('tpl_allow_php' => true));

		$cache_file = $this->template->cachepath . 'includephp_relative.html.php';

		$this->run_template('includephp_relative.html', array(), array(), array(), "Path is relative to board root.\ntesting included php", $cache_file);

		$this->template->set_filenames(array('test' => 'includephp_relative.html'));
		$this->assertEquals("Path is relative to board root.\ntesting included php", $this->display('test'), "Testing INCLUDEPHP");
	}

	public function test_includephp_absolute()
	{
		$path_to_php = dirname(__FILE__) . '/templates/_dummy_include.php.inc';
		$this->assertTrue(phpbb_is_absolute($path_to_php));
		$template_text = "Path is absolute.\n<!-- INCLUDEPHP $path_to_php -->";

		$cache_dir = dirname($this->template->cachepath) . '/';
		$fp = fopen($cache_dir . 'includephp_absolute.html', 'w');
		fputs($fp, $template_text);
		fclose($fp);

		$this->setup_engine(array('tpl_allow_php' => true));

		$this->template->set_custom_template($cache_dir, 'tests');
		$cache_file = $this->template->cachepath . 'includephp_absolute.html.php';

		$this->run_template('includephp_absolute.html', array(), array(), array(), "Path is absolute.\ntesting included php", $cache_file);

		$this->template->set_filenames(array('test' => 'includephp_absolute.html'));
		$this->assertEquals("Path is absolute.\ntesting included php", $this->display('test'), "Testing INCLUDEPHP");
	}
}
