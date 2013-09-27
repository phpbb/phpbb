<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/template_test_case_with_tree.php';

class phpbb_template_template_includecss_test extends phpbb_template_template_test_case_with_tree
{
	public function test_includecss_compilation()
	{
		// Reset the engine state
		$this->setup_engine(array('assets_version' => 1));

		// Prepare correct result
		$scripts = array(
			'<link href="' . $this->phpbb_filesystem->get_web_root_path() . 'tests/template/templates/child_only.css?assets_version=1" rel="stylesheet" type="text/css" media="screen, projection" />',
			'<link href="' . $this->phpbb_filesystem->get_web_root_path() . 'tests/template/parent_templates/parent_only.css?assets_version=1" rel="stylesheet" type="text/css" media="screen, projection" />',
		);

		// Run test
		$this->run_template('includecss.html', array(), array(), array(), implode('', $scripts));
	}
}
