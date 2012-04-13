<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/template_test_case_with_tree.php';

class phpbb_template_template_includejs_test extends phpbb_template_template_test_case_with_tree
{
	public function test_includejs_compilation()
	{
		// Reset the engine state
		$this->setup_engine(array('assets_version' => 1));

		// Prepare correct result
		$dir = dirname(__FILE__);
		$scripts = array(
			'<script src="' . $dir . '/templates/parent_and_child.html?assets_version=1"></script>',
			'<script src="' . $dir . '/parent_templates/parent_only.html?assets_version=1"></script>',
			'<script src="' . $dir . '/templates/child_only.html?assets_version=1"></script>'
		);

		// Run test
		$cache_file = $this->template->cachepath . 'includejs.html.php';
		$this->run_template('includejs.html', array('PARENT' => 'parent_only.html'), array(), array(), implode('', $scripts), $cache_file);
	}
}
