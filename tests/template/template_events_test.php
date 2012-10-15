<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/template_test_case.php';

class phpbb_template_template_events_test extends phpbb_template_template_test_case
{
	public function template_data()
	{
		return array(
			/*
			array(
				'', // File
				array(), // vars
				array(), // block vars
				array(), // destroy
				'', // Expected result
			),
			*/
			array(
				'Test template event',
				'event_test.html',
				array(),
				array(),
				array(),
				'',
			),
		);
	}

	/**
	* @dataProvider template_data
	*/
	public function test_event($desc, $file, array $vars, array $block_vars, array $destroy, $expected)
	{
		// Reset the engine state
		$this->setup_engine();

		// Run test
		$cache_file = $this->template->cachepath . str_replace('/', '.', $file) . '.php';
		$this->run_template($file, $vars, $block_vars, $destroy, $expected, $cache_file);
	}

	protected function setup_engine(array $new_config = array(), $style_tree = false)
	{
		global $phpbb_root_path, $phpEx, $user;

		if ($style_tree == false)
		{
	 		$style_tree = array(
				'./styles/prosilver_inherit',
				'./styles/prosilver',
				'./styles/all',
			);
		}

		$defaults = $this->config_defaults();
		$config = new phpbb_config(array_merge($defaults, $new_config));

		$this->style_resource_locator = new phpbb_style_resource_locator();
		$this->extension_manager = new phpbb_mock_extension_manager(
			dirname(__FILE__) . '/',
			array(
				'trivial' => array(
					'ext_name'      => 'trivial',
					'ext_active'    => true,
					'ext_path'      => 'ext/trivial/',
				),
				'trivial1' => array(
					'ext_name'      => 'trivial1',
					'ext_active'    => true,
					'ext_path'      => 'ext/trivial1/',
				),
				'trivial2' => array(
					'ext_name'      => 'trivial2',
					'ext_active'    => true,
					'ext_path'      => 'ext/trivial2/',
				),
			)
		);
		$this->template = new phpbb_template($phpbb_root_path, $phpEx, $config, $user, $this->style_resource_locator, $this->extension_manager);
		$this->style_provider = new phpbb_style_extension_path_provider($this->extension_manager, new phpbb_style_path_provider());
		$this->style = new phpbb_style($phpbb_root_path, $phpEx, $config, $user, $this->style_resource_locator, $this->style_provider, $this->template);
		$this->style->set_custom_style('tests', $style_tree);
	}
}
