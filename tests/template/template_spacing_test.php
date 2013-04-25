<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/template_test_case.php';

class phpbb_template_template_spacing_test extends phpbb_template_template_test_case
{
	public function template_data()
	{
		return array(
			/*
			array(
				'', // Description
				'', // dataset
				array(), // style names
				'', // file
				array(), // vars
				array(), // block vars
				array(), // destroy
				'', // expected result
			),
			*/
			array(
				'Spacing in templates',
				'ext_trivial',
				array(),
				'variable_spacing.html',
				array(
					'VARIABLE'	=> '{}',
				),
				array(),
				array(),
				'|{}|
{}|{}|
|{}
<div class="{}">test</div>',
			),
		);
	}

	/**
	* @dataProvider template_data
	*/
	public function test_template($desc, $dataset, $style_names, $file, array $vars, array $block_vars, array $destroy, $expected)
	{
		// Run test
		$cache_file = $this->template->cachepath . str_replace('/', '.', $file) . '.php';
		$this->run_template($file, $vars, $block_vars, $destroy, $expected, $cache_file);
	}

	/**
	* @dataProvider template_data
	*/
	public function test_event($desc, $dataset, $style_names, $file, array $vars, array $block_vars, array $destroy, $expected)
	{
		// Reset the engine state
		$this->setup_engine_for_events($dataset, $style_names);

		// Run test
		$cache_file = $this->template->cachepath . str_replace('/', '.', $file) . '.php';
		$this->run_template($file, $vars, $block_vars, $destroy, $expected, $cache_file);
	}

	protected function setup_engine_for_events($dataset, $style_names, array $new_config = array())
	{
		global $phpbb_root_path, $phpEx, $user;

		$defaults = $this->config_defaults();
		$config = new phpbb_config(array_merge($defaults, $new_config));

		$this->template_path = dirname(__FILE__) . "/datasets/$dataset/styles/silver/template";
		$this->style_resource_locator = new phpbb_style_resource_locator();
		$this->extension_manager = new phpbb_mock_filesystem_extension_manager(
			dirname(__FILE__) . "/datasets/$dataset/"
		);
		$this->template = new phpbb_template($phpbb_root_path, $phpEx, $config, $user, $this->style_resource_locator, new phpbb_template_context, $this->extension_manager);
		$this->style_provider = new phpbb_style_path_provider();
		$this->style = new phpbb_style($phpbb_root_path, $phpEx, $config, $user, $this->style_resource_locator, $this->style_provider, $this->template);
		$this->style->set_custom_style('silver', array($this->template_path), $style_names, '');
	}
}
