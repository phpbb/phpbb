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
			array(
				'EVENT in loop',
				'ext_trivial',
				array('silver'),
				'event_loop.html',
				array(),
				array('event_loop' => array(array(), array(), array())),
				array(),
				'event_loop0|event_loop1|event_loop2',
			),
			array(
				'EVENT with subloop in loop',
				'ext_trivial',
				array('silver'),
				'event_subloop.html',
				array(),
				array(
					'event_loop' => array(array()),
					'event_loop.subloop' => array(array()),
				),
				array(),
				'event_loop[0[subloop:0]]',
			),
		);
	}

	/**
	* @dataProvider template_data
	*/
	public function test_event($desc, $dataset, $style_names, $file, array $vars, array $block_vars, array $destroy, $expected, $incomplete_message = '')
	{
		if ($incomplete_message)
		{
			$this->markTestIncomplete($incomplete_message);
		}

		// Reset the engine state
		$this->setup_engine_for_events($dataset, $style_names);

		// Run test
		$this->run_template($file, $vars, $block_vars, $destroy, $expected);
	}

	protected function setup_engine_for_events($dataset, $style_names, array $new_config = array())
	{
		global $phpbb_root_path, $phpEx, $user;

		$defaults = $this->config_defaults();
		$config = new \phpbb\config\config(array_merge($defaults, $new_config));

		$this->template_path = dirname(__FILE__) . "/datasets/$dataset/styles/silver/template";
		$this->extension_manager = new phpbb_mock_filesystem_extension_manager(
			dirname(__FILE__) . "/datasets/$dataset/"
		);
		$path_helper = new \phpbb\path_helper(
			new \phpbb\symfony_request(
				new phpbb_mock_request()
			),
			new \phpbb\filesystem(),
			$phpbb_root_path,
			$phpEx
		);
		$this->template = new \phpbb\template\twig\twig($path_helper, $config, $user, new \phpbb\template\context, $this->extension_manager);
		$this->template->set_custom_style(((!empty($style_names)) ? $style_names : 'silver'), array($this->template_path));
	}
}
