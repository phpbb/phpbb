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

class phpbb_template_template_inheritance_test extends phpbb_template_template_test_case_with_tree
{
	/**
	 * @todo put test data into templates/xyz.test
	 */
	public function template_data()
	{
		return array(
			// First element of the array is test name - keep them distinct
			array(
				'simple inheritance - only parent template exists',
				'parent_only.html',
				array(),
				array(),
				array(),
				"Only in parent.",
			),
			array(
				'simple inheritance - only child template exists',
				'child_only.html',
				array(),
				array(),
				array(),
				"Only in child.",
			),
			array(
				'simple inheritance - both parent and child templates exist',
				'parent_and_child.html',
				array(),
				array(),
				array(),
				"Child template.",
			),
		);
	}

	/**
	* @dataProvider template_data
	*/
	public function test_template($name, $file, array $vars, array $block_vars, array $destroy, $expected)
	{
		$this->run_template($file, $vars, $block_vars, $destroy, $expected);
	}
}
