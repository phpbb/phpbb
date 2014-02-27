<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../template_test_case.php';

class phpbb_template_subdir_includephp_from_subdir_test extends phpbb_template_template_test_case
{
	// Exact copy of test_includephp_relatve from ../includephp_test.php.
	// Verifies that relative php inclusion works when including script
	// (and thus current working directory) is in a subdirectory of
	// board root.
	public function test_includephp_relative()
	{
		$this->setup_engine(array('tpl_allow_php' => true));

		$this->run_template('includephp_relative.html', array(), array(), array(), "Path is relative to board root.\ntesting included php");

		$this->template->set_filenames(array('test' => 'includephp_relative.html'));
		$this->assertEquals("Path is relative to board root.\ntesting included php", $this->display('test'), "Testing INCLUDEPHP");
	}
}
