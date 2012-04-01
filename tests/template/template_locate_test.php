<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/template_test_case.php';

class phpbb_template_template_locate_test extends phpbb_template_template_test_case
{
	public function template_data()
	{
		return array(
			// First element of the array is test name - keep them distinct
			array(
				'simple inheritance - only parent template exists',
				dirname(__FILE__) . '/parent_templates/parent_only.html',
				'parent_only.html',
				false,
				true,
			),
			array(
				'simple inheritance - only child template exists',
				dirname(__FILE__) . '/templates/child_only.html',
				'child_only.html',
				false,
				true,
			),
			array(
				'simple inheritance - both parent and child templates exist',
				dirname(__FILE__) . '/templates/parent_and_child.html',
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
		$result = $this->template->locate($files, $return_default, $return_full_path);
		$this->assertSame($expected, $result);
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
