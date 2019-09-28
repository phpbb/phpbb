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

class phpbb_template_extension_test extends phpbb_template_template_test_case
{
	protected function setup_engine(array $new_config = array())
	{
		global $config, $phpbb_container, $phpbb_dispatcher, $phpbb_root_path, $phpEx;

		$defaults = $this->config_defaults();
		$defaults = array_merge($defaults, [
			'allow_avatar' => true,
			'allow_avatar_upload' => true,
		]);
		$config = new \phpbb\config\config(array_merge($defaults, $new_config));
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$this->lang = $lang = new \phpbb\language\language($lang_loader);
		$this->user = new \phpbb\user($lang, '\phpbb\datetime');

		global $auth, $request, $symfony_request, $user;
		$user = new phpbb_mock_user();
		$user->optionset('user_id', 2);
		$auth = $this->getMockBuilder('phpbb\auth\auth')
			->disableOriginalConstructor()
			->setMethods(['acl_get'])
			->getMock();
		$auth->method('acl_get')
			->willReturn(true);

		$filesystem = new \phpbb\filesystem\filesystem();
		$request = new phpbb_mock_request;
		$symfony_request = new \phpbb\symfony_request(
			$request
		);
		$phpbb_path_helper = new \phpbb\path_helper(
			$symfony_request,
			$filesystem,
			$request,
			$phpbb_root_path,
			$phpEx
		);

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$phpbb_container = new phpbb_mock_container_builder();
		$files = new phpbb\files\factory($phpbb_container);
		$upload_avatar_driver = new phpbb\avatar\driver\upload($config, $phpbb_root_path, $phpEx, $filesystem, $phpbb_path_helper, $phpbb_dispatcher, $files);
		$upload_avatar_driver->set_name('avatar.driver.upload');
		$phpbb_container->set('avatar.manager', new \phpbb\avatar\manager($config, $phpbb_dispatcher, [
			$upload_avatar_driver,
		]));
		$phpbb_container->set('path_helper', $phpbb_path_helper);

		$class = new ReflectionClass('\phpbb\avatar\manager');
		$enabled_drivers = $class->getProperty('enabled_drivers');
		$enabled_drivers->setAccessible(true);
		$enabled_drivers->setValue(false);

		$this->template_path = $this->test_path . '/templates';

		$cache_path = $phpbb_root_path . 'cache/twig';
		$context = new \phpbb\template\context();
		$loader = new \phpbb\template\twig\loader($filesystem);
		$twig = new \phpbb\template\twig\environment(
			$config,
			$filesystem,
			$phpbb_path_helper,
			$cache_path,
			null,
			$loader,
			new \phpbb\event\dispatcher($phpbb_container),
			array(
				'cache'			=> false,
				'debug'			=> false,
				'auto_reload'	=> true,
				'autoescape'	=> false,
			)
		);
		$this->template = new phpbb\template\twig\twig(
			$phpbb_path_helper,
			$config,
			$context,
			$twig,
			$cache_path,
			$this->user,
			[
				new \phpbb\template\twig\extension($context, $twig, $this->lang),
				new \phpbb\template\twig\extension\avatar(),
				new \phpbb\template\twig\extension\config($config),
				new \phpbb\template\twig\extension\username(),
			]
		);
		$twig->setLexer(new \phpbb\template\twig\lexer($twig));
		$this->template->set_custom_style('tests', $this->template_path);
	}

	public function data_template_extensions()
	{
		return [
			[
				'avatar_user.html',
				[
					'row' => [
						'user_avatar' => 'great_avatar.png',
						'user_avatar_type' => 'avatar.driver.upload',
						'user_avatar_width' => 90,
						'user_avatar_height' => 90,
					],
					'alt' => 'foo'
				],
				[],
				[],
				'<img class="avatar" src="phpBB/download/file.php?avatar=great_avatar.png" width="90" height="90" alt="foo" />',
				[]
			],
			[
				'avatar_user.html',
				[
					'row' => [
						'user_avatar' => 'great_avatar.png',
						'user_avatar_type' => 'avatar.driver.upload',
						'user_avatar_width' => 90,
						'user_avatar_height' => 90,
					],
					'alt' => 'foo',
					'ignore_config' => true,
					'lazy' => true,
				],
				[],
				[],
				'<img class="avatar" src="phpBB/styles//theme/images/no_avatar.gif" data-src="phpBB/download/file.php?avatar=great_avatar.png" width="90" height="90" alt="foo" />',
				[]
			],
			[
				'avatar_user.html',
				[
					'row' => [
						'user_avatar' => 'foo@bar.com',
						'user_avatar_type' => 'avatar.driver.gravatar',
						'user_avatar_width' => 90,
						'user_avatar_height' => 90,
					],
					'alt' => 'foo'
				],
				[],
				[],
				'',
				[]
			],
			[
				'extension_username_test.html',
				[
					'mode' => 'profile',
					'user_id' => 2,
					'username' => 'admin',
					'user_colour' => 'abcdef',
					'guest_username' => 'lol',
				],
				[],
				[],
				'phpBB/memberlist.php?mode=viewprofile&amp;u=2',
				[]
			],
			[
				'extension_username_test.html',
				[
					'mode' => 'profile',
					'user_id' => 2,
					'username' => 'admin',
					'user_colour' => 'abcdef',
					'guest_username' => 'lol',
					'custom_profile_url' => 'http://lol.bar',
				],
				[],
				[],
				'http://lol.bar&amp;u=2',
				[]
			],
			[
				'extension_username_test.html',
				[
					'mode' => 'full',
					'user_id' => 2,
					'username' => 'admin',
					'user_colour' => 'abcdef',
					'guest_username' => 'lol',
				],
				[],
				[],
				'<a href="phpBB/memberlist.php?mode=viewprofile&amp;u=2" style="color: #abcdef;" class="username-coloured">admin</a>',
				[]
			],
			[
				'extension_username_test.html',
				[
					'mode' => 'no_profile',
					'user_id' => 2,
					'username' => 'admin',
					'user_colour' => 'abcdef',
					'guest_username' => 'lol',
				],
				[],
				[],
				'<span style="color: #abcdef;" class="username-coloured">admin</span>',
				[]
			],
			[
				'extension_config_test.html',
				[
					'config_name' => 'allow_avatar',
				],
				[],
				[],
				'1',
				[]
			],
			[
				'extension_config_test.html',
				[
					'config_name' => 'does not exist',
				],
				[],
				[],
				'',
				[]
			],
			[
				'extension_config_test.html',
				[
					'config_name' => 'tpl_allow_php',
				],
				[],
				[],
				'',
				[]
			],
		];
	}

	/**
	 * @dataProvider data_template_extensions
	 */
	public function test_get_user_avatar($file, $vars, $block_vars, $destroy_array, $expected, $lang_vars = [])
	{
		$this->run_template($file, $vars, $block_vars, $destroy_array, $expected, $lang_vars);
	}
}
