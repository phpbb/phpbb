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

class phpbb_email_parsing_test extends phpbb_test_case
{
	static protected $reflection;
	protected $messenger;
	protected $reflection_template_property;

	public function setUp()
	{
		global $phpbb_container, $config, $phpbb_root_path, $phpEx, $request, $user; 

		$phpbb_container = new phpbb_mock_container_builder;

		$config = new \phpbb\config\config(array());
		$default_config = array(
			'board_email_sig'     => '-- Thanks, The Management',
			'sitename' 			=> 'yourdomain.com',
			'default_lang'		=> 'en',
		);
		foreach ($default_config as $config_name => $config_value)
		{
			if (!isset($config[$config_name]))
			{
				$config[$config_name] = $config_value;
			}
		}
		$phpbb_container->set('config', $config, phpbb_mock_container_builder::SCOPE_PROTOTYPE);

		$request = new phpbb_mock_request;
		$symfony_request = new \phpbb\symfony_request(
			$request
		);
		$filesystem = new \phpbb\filesystem\filesystem();
		$phpbb_path_helper = new \phpbb\path_helper(
			$symfony_request,
			$filesystem,
			$request,
			$phpbb_root_path,
			$phpEx
		);
		$phpbb_container->set('path_helper', $phpbb_path_helper, phpbb_mock_container_builder::SCOPE_PROTOTYPE);
		$phpbb_container->set('filesystem', $filesystem, phpbb_mock_container_builder::SCOPE_PROTOTYPE);

		$cache_path = 'cache/' . PHPBB_ENVIRONMENT . '/twig';
		$phpbb_container->setParameter('core.template.cache_path', $cache_path, phpbb_mock_container_builder::SCOPE_PROTOTYPE);

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$phpbb_container->set('user', $user, phpbb_mock_container_builder::SCOPE_PROTOTYPE);
		$extension_manager = new phpbb_mock_extension_manager(
			dirname(__FILE__) . '/',
			array(
				'vendor2/foo' => array(
					'ext_name' => 'vendor2/foo',
					'ext_active' => '1',
					'ext_path' => 'ext/vendor2/foo/',
				),
			)
		);
		$phpbb_container->set('ext.manager', $extension_manager, phpbb_mock_container_builder::SCOPE_PROTOTYPE);

		$context = new \phpbb\template\context();
		$phpbb_container->set('template.twig.extensions.collection', array(new \phpbb\template\twig\extension($context, $user)), phpbb_mock_container_builder::SCOPE_PROTOTYPE);

		$twig = new \phpbb\template\twig\environment(
			$config,
			$filesystem,
			$phpbb_path_helper,
			$cache_path,
			null,
			new \phpbb\template\twig\loader($filesystem, ''),
			array(
				'cache'			=> false,
				'debug'			=> false,
				'auto_reload'	=> true,
				'autoescape'	=> false,
			)
		);
		$phpbb_container->set('template.twig.lexer', new \phpbb\template\twig\lexer($twig), phpbb_mock_container_builder::SCOPE_PROTOTYPE);

		if (!class_exists('messenger'))
		{
			include($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
		}
		
		$this->messenger = new \messenger();

		$reflection = new ReflectionObject($this->messenger);
		$this->reflection_template_property = $reflection->getProperty('template');
		$this->reflection_template_property->setAccessible(true);
	}

	public function test_email_parsing()
	{
		global $phpbb_container, $config, $phpbb_root_path, $phpEx, $user, $request; 

		$this->messenger->set_addresses($user->data);

		$this->messenger->assign_vars(array(
			'EMAIL_SIG'	=> str_replace('<br />', "\n", "-- \n" . htmlspecialchars_decode($config['board_email_sig'])),
			'SITENAME'	=> htmlspecialchars_decode($config['sitename']),

			'AUTHOR_NAME'				=> 'Author username',
			'FORUM_NAME'				=> 'Any forum',
			'TOPIC_TITLE'				=> 'The topic title',
			'USERNAME'					=> 'Dear user',

			'U_FORUM'					=> generate_board_url() . "/viewforum.{$phpEx}?f=1",
			'U_STOP_WATCHING_FORUM'		=> generate_board_url() . "/viewforum.{$phpEx}?uid=2&f=1&unwatch=forum",
		));
		$this->messenger->template('newtopic_notify', $user->data['user_lang'], '', '');

		$reflection_template = $this->reflection_template_property->getValue($this->messenger);
		$msg = trim($reflection_template->assign_display('body'));

		$this->assertContains('Author username', $msg);
		$this->assertContains('Any forum', $msg);
		$this->assertContains('The topic title', $msg);
		$this->assertContains('Dear user', $msg);
		$this->assertContains(htmlspecialchars_decode($config['sitename']), $msg);
		$this->assertContains(str_replace('<br />', "\n", "-- \n" . htmlspecialchars_decode($config['board_email_sig'])), $msg);
		$this->assertNotContains('EMAIL_SIG', $msg);
		$this->assertNotContains('U_STOP_WATCHING_FORUM', $msg);
	}
}
