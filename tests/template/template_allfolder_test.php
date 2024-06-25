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

class phpbb_template_allfolder_test extends phpbb_template_template_test_case
{
	public function test_allfolder()
	{
		$this->setup_engine_for_allfolder();

		$this->run_template('foobar_body.html', array(), array(), array(), "All folder");
	}

	protected function setup_engine_for_allfolder(array $new_config = array())
	{
		global $phpbb_root_path, $phpEx;

		$defaults = $this->config_defaults();
		$config = new \phpbb\config\config(array_merge($defaults, $new_config));
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$this->user = $user;

		$filesystem = new \phpbb\filesystem\filesystem();

		$path_helper = new \phpbb\path_helper(
			new \phpbb\symfony_request(
				new phpbb_mock_request()
			),
			$this->createMock('\phpbb\request\request'),
			$phpbb_root_path,
			$phpEx
		);

		$this->extension_manager = new phpbb_mock_extension_manager(
			__DIR__ . '/',
			array(
				'vendor4/bar' => array(
					'ext_name' => 'vendor4/bar',
					'ext_active' => '1',
					'ext_path' => 'ext/vendor4/bar/',
				),
			)
		);

		$cache_path = $phpbb_root_path . 'cache/twig';
		$context = new \phpbb\template\context();
		$loader = new \phpbb\template\twig\loader('');
		$log = new \phpbb\log\dummy();
		$assets_bag = new \phpbb\template\assets_bag();
		$twig = new \phpbb\template\twig\environment(
			$assets_bag,
			$config,
			$filesystem,
			$path_helper,
			$cache_path,
			$this->extension_manager,
			$loader,
			new \phpbb\event\dispatcher(),
			array(
				'cache'			=> false,
				'debug'			=> false,
				'auto_reload'	=> true,
				'autoescape'	=> false,
			)
		);
		$this->template = new \phpbb\template\twig\twig($path_helper, $config, $context, $twig, $cache_path, $this->user, array(new \phpbb\template\twig\extension($context, $twig, $this->user)), $this->extension_manager);
		$twig->setLexer(new \phpbb\template\twig\lexer($twig));

		$this->template_path = $this->test_path . '/templates';
		$this->ext_template_path = 'tests/extension/ext/vendor4/bar/styles/all/template';
		$this->template->set_custom_style('all', array($this->template_path, $this->ext_template_path));
	}
}
