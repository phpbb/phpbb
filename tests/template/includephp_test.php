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
		$GLOBALS['config']['tpl_allow_php'] = true;

		$cache_file = $this->template->cachepath . 'includephp_relative.html.php';

		$this->run_template('includephp_relative.html', array(), array(), array(), "Path is relative to board root.\ntesting included php", $cache_file);

		$this->template->set_filenames(array('test' => 'includephp_relative.html'));
		$this->assertEquals("Path is relative to board root.\ntesting included php", $this->display('test'), "Testing INCLUDEPHP");

		$GLOBALS['config']['tpl_allow_php'] = false;
	}
}
