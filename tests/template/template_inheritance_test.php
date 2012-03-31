<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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

	protected function setup_engine(array $new_config = array())
	{
		global $phpbb_root_path, $phpEx, $user;

		$defaults = $this->config_defaults();
		$config = new phpbb_config(array_merge($defaults, $new_config));

		$this->template_path = dirname(__FILE__) . '/templates';
		$this->parent_template_path = dirname(__FILE__) . '/parent_templates';
		$this->style_resource_locator = new phpbb_style_resource_locator();
		$this->style_provider = new phpbb_style_path_provider();
		$this->template = new phpbb_style_template($phpbb_root_path, $phpEx, $config, $user, $this->style_resource_locator, $this->style_provider);
		$this->style = new phpbb_style($phpbb_root_path, $phpEx, $config, $user, $this->style_resource_locator, $this->style_provider, $this->template);
		$this->style->set_custom_style('tests', array($this->template_path, $this->parent_template_path), '');
	}
}
