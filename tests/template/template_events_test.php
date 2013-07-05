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
				'Simple template event',
				'ext_trivial',
				array(),
				'event_simple.html',
				array(),
				array(),
				array(),
				"Simple in trivial extension.",
			),
			array(
				'Universal template event ("all" style)',
				'ext_trivial',
				array(),
				'event_universal.html',
				array(),
				array(),
				array(),
				"Universal in trivial extension.",
			),
			array(
				'Template event with inheritance - parent',
				'event_inheritance',
				array('silver'),
				'event_test.html',
				array(),
				array(),
				array(),
'Kappa test event in silver
Omega test event in silver
Zeta test event in all',
			),
			array(
				'Template event with inheritance - child',
				'event_inheritance',
				array('silver_inherit', 'silver'),
				'event_test.html',
				array(),
				array(),
				array(),
'Kappa test event in silver_inherit
Omega test event in silver
Zeta test event in all',
			),
			array(
				'Definition in parent style',
				'event_inheritance',
				array('silver_inherit', 'silver'),
				'event_two.html',
				array(),
				array(),
				array(),
'two in silver in omega',
			),
		);
	}

	/**
	* @dataProvider template_data
	*/
	public function test_event($desc, $dataset, $style_names, $file, array $vars, array $block_vars, array $destroy, $expected)
	{
		// Reset the engine state
		$this->setup_engine_for_events($dataset, $style_names);

		// Run test
		$this->run_template($file, $vars, $block_vars, $destroy, $expected);
	}

	protected function setup_engine_for_events($dataset, $style_names, array $new_config = array())
	{
		global $phpbb_root_path, $phpEx, $user;

		$defaults = $this->config_defaults();
		$config = new phpbb_config(array_merge($defaults, $new_config));

		$this->template_path = dirname(__FILE__) . "/datasets/$dataset/styles/silver/template";
		$this->extension_manager = new phpbb_mock_filesystem_extension_manager(
			dirname(__FILE__) . "/datasets/$dataset/"
		);
		$this->template = new phpbb_template_twig($phpbb_root_path, $phpEx, $config, $user, new phpbb_template_context, $this->extension_manager);
		$this->style = new phpbb_style($phpbb_root_path, $phpEx, $config, $user, $this->template);
		$this->style->set_custom_style('silver', array($this->template_path), $style_names, '');
	}
}
