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

require_once dirname(__FILE__) . '/template_test_case_with_tree.php';

class phpbb_template_template_includecss_test extends phpbb_template_template_test_case_with_tree
{
	public function test_includecss_compilation()
	{
		// Reset the engine state
		$this->setup_engine(array('assets_version' => 1));

		// Prepare correct result
		$scripts = array(
			'<link href="tests/template/templates/child_only.css?assets_version=1" rel="stylesheet" type="text/css" media="screen, projection" />',
			'<link href="tests/template/parent_templates/parent_only.css?assets_version=1" rel="stylesheet" type="text/css" media="screen, projection" />',
		);

		// Run test
		$this->run_template('includecss.html', array(), array(), array(), implode('', $scripts));
	}
}
