<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/template_test_case_with_tree.php';

class phpbb_template_template_locate_test extends phpbb_template_template_test_case_with_tree
{
	public function template_data()
	{
		return array(
			// First element of the array is test name - keep them distinct
			array(
				'simple inheritance - only parent template exists',
				$this->test_path . '/parent_templates/parent_only.html',
				'parent_only.html',
				false,
				true,
			),
			array(
				'simple inheritance - only child template exists',
				$this->test_path . '/templates/child_only.html',
				'child_only.html',
				false,
				true,
			),
			array(
				'simple inheritance - both parent and child templates exist',
				$this->test_path . '/templates/parent_and_child.html',
				'parent_and_child.html',
				false,
				true,
			),
			array(
				'find first template - only child template exists in main style',
				'child_only.html',
				array('parent_only.html', 'child_only.html'),
				false,
				false,
			),
			array(
				'find first template - both templates exist in main style',
				'parent_and_child.html',
				array('parent_and_child.html', 'child_only.html'),
				false,
				false,
			),
		);
	}

	/**
	* @dataProvider template_data
	*/
	public function test_template($name, $expected, $files, $return_default, $return_full_path)
	{
		// Reset the engine state
		$this->setup_engine();

		// Locate template
		$result = $this->style_resource_locator->get_first_template_location($files, $return_default, $return_full_path);
		$this->assertSame($expected, $result);
	}
}
