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
				'Simple template event',
				'event_simple.html',
				array(),
				array(),
				array(),
				"Simple in trivial extension.",
			),
			array(
				'Universal template event ("all" style)',
				'event_universal.html',
				array(),
				array(),
				array(),
				"Universal in trivial extension.",
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

	protected function setup_engine(array $new_config = array())
	{
		global $phpbb_root_path, $phpEx, $user;

		$defaults = $this->config_defaults();
		$config = new phpbb_config(array_merge($defaults, $new_config));

		$this->template_path = dirname(__FILE__) . '/datasets/ext_trivial/templates';
		$this->style_resource_locator = new phpbb_style_resource_locator();
		$this->extension_manager = new phpbb_mock_extension_manager(
			dirname(__FILE__) . '/datasets/ext_trivial/',
			array(
				'trivial' => array(
					'ext_name'      => 'trivial',
					'ext_active'    => true,
					'ext_path'      => 'ext/trivial/',
				),
			)
		);
		$this->template = new phpbb_template($phpbb_root_path, $phpEx, $config, $user, $this->style_resource_locator, $this->extension_manager);
		$this->style_provider = new phpbb_style_path_provider();
		$this->style = new phpbb_style($phpbb_root_path, $phpEx, $config, $user, $this->style_resource_locator, $this->style_provider, $this->template);
		$this->style->set_custom_style('tests', array($this->template_path), '');
	}
}
