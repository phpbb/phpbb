<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/template_test_case.php';

class phpbb_template_template_inheritance_test extends phpbb_template_template_test_case
{
	/**
	 * @todo put test data into templates/xyz.test
	 */
	public static function template_data()
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
		$cache_file = $this->template->cachepath . str_replace('/', '.', $file) . '.php';

		$this->assertFileNotExists($cache_file);

		$this->run_template($file, $vars, $block_vars, $destroy, $expected, $cache_file);

		// Reset the engine state
		$this->setup_engine();

		$this->run_template($file, $vars, $block_vars, $destroy, $expected, $cache_file);
	}

	protected function setup_engine()
	{
		global $phpbb_root_path, $phpEx, $config, $user;

		$this->template_path = dirname(__FILE__) . '/templates';
		$this->parent_template_path = dirname(__FILE__) . '/parent_templates';
		$this->template_locator = new phpbb_template_locator();
		$this->template = new phpbb_template($phpbb_root_path, $phpEx, $config, $user, $this->template_locator);
		$this->template->set_custom_template($this->template_path, 'tests', $this->parent_template_path);
	}
}
