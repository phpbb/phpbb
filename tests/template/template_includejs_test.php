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
		$scripts = array(
			'<script src="' . $this->test_path . '/templates/parent_and_child.js?assets_version=1"></script>',
			'<script src="' . $this->test_path . '/templates/parent_and_child.js?assets_version=0"></script>',
			'<script src="' . $this->test_path . '/templates/parent_and_child.js?test=1&assets_version=0"></script>',
			'<script src="' . $this->test_path . '/templates/parent_and_child.js?test=1&amp;assets_version=0"></script>',
			'<script src="' . $this->test_path . '/parent_templates/parent_only.js?assets_version=1"></script>',
			'<script src="' . $this->test_path . '/templates/child_only.js?assets_version=1"></script>',
			'<script src="' . $this->test_path . '/templates/subdir/parent_only.js?assets_version=1"></script>',
			'<script src="' . $this->test_path . '/templates/subdir/subsubdir/parent_only.js?assets_version=1"></script>',
			'<script src="' . $this->test_path . '/templates/subdir/parent_only.js?assets_version=1"></script>',
			'<script src="' . $this->test_path . '/templates/child_only.js?test1=1&amp;test2=2&amp;assets_version=1#test3"></script>',
			'<script src="' . $this->test_path . '/parent_templates/parent_only.js?test1=1&amp;test2=2&amp;assets_version=1#test3"></script>',
			'<script src="//a.com/b.js"></script>',
			'<script src="http://a.com/b.js?c=d#f"></script>',
			'<script src="http://a.com/b.js?c=d&assets_version=1#f"></script>',
		);

		// Run test
		$cache_file = $this->template->cachepath . 'includejs.html.php';
		$this->run_template('includejs.html', array('PARENT' => 'parent_only.js', 'SUBDIR' => 'subdir', 'EXT' => 'js'), array(), array(), implode('', $scripts), $cache_file);
	}
}
