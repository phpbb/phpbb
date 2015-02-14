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

class phpbb_template_template_includecss_test extends phpbb_template_template_test_case_with_tree
{
	/** @var \phpbb\path_helper */
	protected $phpbb_path_helper;

	/** @var string */
	protected $parent_template_path;

	protected function setup_engine(array $new_config = array())
	{
		global $phpbb_root_path, $phpEx, $user;

		$defaults = $this->config_defaults();
		$config = new \phpbb\config\config(array_merge($defaults, $new_config));

		$this->phpbb_path_helper = new \phpbb\path_helper(
			new \phpbb\symfony_request(
				new phpbb_mock_request()
			),
			new \phpbb\filesystem(),
			$this->getMock('\phpbb\request\request'),
			$phpbb_root_path,
			$phpEx
		);

		$this->template_path = $this->test_path . '/templates';
		$this->parent_template_path = $this->test_path . '/parent_templates';
		$container = new phpbb_mock_container_builder();
		$cache_path = $phpbb_root_path . 'cache/twig';
		$context = new \phpbb\template\context();
		$loader = new \phpbb\template\twig\loader('');
		$twig = new \phpbb\template\twig\environment(
			$config,
			$this->phpbb_path_helper,
			$container,
			$cache_path,
			null,
			$loader,
			array(
				'cache'			=> false,
				'debug'			=> false,
				'auto_reload'	=> true,
				'autoescape'	=> false,
			)
		);
		$this->template = new phpbb\template\twig\twig(
			$this->phpbb_path_helper,
			$config,
			$user,
			$context,
			$twig,
			$cache_path,
			array(new \phpbb\template\twig\extension($context, $this->user)),
			new phpbb_mock_extension_manager(
				dirname(__FILE__) . '/',
				array(
					'include/css' => array(
						'ext_name' => 'include/css',
						'ext_active' => '1',
						'ext_path' => 'ext/include/css/',
					),
				)
			)
		);
		$container->set('template.twig.lexer', new \phpbb\template\twig\lexer($twig));
		$this->template->set_custom_style('tests', array($this->template_path, $this->parent_template_path));
	}

	public function template_data()
	{
		return array(
			/*
			array(
				// vars
				// expected
			),
			*/
			array(
				array('TEST' => 1),
				'<link href="tests/template/templates/child_only.css?assets_version=1" rel="stylesheet" type="text/css" media="screen, projection" />',
			),
			array(
				array('TEST' => 2),
				'<link href="tests/template/parent_templates/parent_only.css?assets_version=1" rel="stylesheet" type="text/css" media="screen, projection" />',
			),
			array(
				array('TEST' => 3),
				'<link href="tests/template/ext/include/css/styles/all/theme/test.css?assets_version=1" rel="stylesheet" type="text/css" media="screen, projection" />',
			),
			array(
				array('TEST' => 4),
				'<link href="tests/template/ext/include/css/styles/all/theme/child_only.css?assets_version=1" rel="stylesheet" type="text/css" media="screen, projection" />',
			),
		);
	}

	/**
	 * @dataProvider template_data
	 */
	public function test_includecss_compilation($vars, $expected)
	{
		// Reset the engine state
		$this->setup_engine(array('assets_version' => 1));

		$this->template->assign_vars($vars);

		// Run test
		$this->run_template('includecss.html', array(), array(), array(), $expected);
	}
}
