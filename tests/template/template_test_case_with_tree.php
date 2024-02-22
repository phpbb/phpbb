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

require_once __DIR__ . '/template_test_case.php';

class phpbb_template_template_test_case_with_tree extends phpbb_template_template_test_case
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

		$filesystem = new \phpbb\filesystem\filesystem();

		$this->phpbb_path_helper = new \phpbb\path_helper(
			new \phpbb\symfony_request(
				new phpbb_mock_request()
			),
			$this->createMock('\phpbb\request\request'),
			$phpbb_root_path,
			$phpEx
		);

		$this->template_path = $this->test_path . '/templates';
		$this->parent_template_path = $this->test_path . '/parent_templates';

		$cache_path = $phpbb_root_path . 'cache/twig';
		$context = new \phpbb\template\context();
		$loader = new \phpbb\template\twig\loader('');
		$log = new \phpbb\log\dummy();
		$assets_bag = new \phpbb\template\assets_bag();
		$twig = new \phpbb\template\twig\environment(
			$assets_bag,
			$config,
			$filesystem,
			$this->phpbb_path_helper,
			$cache_path,
			null,
			$loader,
			new \phpbb\event\dispatcher(),
			array(
				'cache'			=> false,
				'debug'			=> false,
				'auto_reload'	=> true,
				'autoescape'	=> false,
			)
		);
		$this->template = new phpbb\template\twig\twig($this->phpbb_path_helper, $config, $context, $twig, $cache_path, $this->user, array(new \phpbb\template\twig\extension($context, $twig, $this->user)));
		$twig->setLexer(new \phpbb\template\twig\lexer($twig));
		$this->template->set_custom_style('tests', array($this->template_path, $this->parent_template_path));
	}
}
