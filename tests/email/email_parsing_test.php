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
	/** @var \messenger */
	protected $messenger;

	/** @var \ReflectionProperty */
	protected $reflection_template_property;

	protected function setUp(): void
	{
		global $phpbb_container, $config, $phpbb_root_path, $phpEx, $request, $user;

		$phpbb_container = new phpbb_mock_container_builder;

		$config = new \phpbb\config\config(array(
			'board_email_sig'     => '-- Thanks, The Management',
			'sitename' 			=> 'yourdomain.com',
			'default_lang'		=> 'en',
		));
		$phpbb_container->set('config', $config);

		$request = new phpbb_mock_request;
		$symfony_request = new \phpbb\symfony_request(
			$request
		);
		$filesystem = new \phpbb\filesystem\filesystem();
		$phpbb_path_helper = new \phpbb\path_helper(
			$symfony_request,
			$request,
			$phpbb_root_path,
			$phpEx
		);
		$phpbb_container->set('path_helper', $phpbb_path_helper);
		$phpbb_container->set('filesystem', $filesystem);

		$cache_path = $phpbb_root_path . 'cache/' . PHPBB_ENVIRONMENT . '/twig';
		$phpbb_container->setParameter('core.template.cache_path', $cache_path);

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$user->data['user_lang'] = 'en';
		$phpbb_container->set('user', $user);
		$extension_manager = new phpbb_mock_extension_manager(
			__DIR__ . '/',
			array(
				'vendor2/foo' => array(
					'ext_name' => 'vendor2/foo',
					'ext_active' => '1',
					'ext_path' => 'ext/vendor2/foo/',
				),
			)
		);
		$phpbb_container->set('ext.manager', $extension_manager);

		$assets_bag = new \phpbb\template\assets_bag();
		$phpbb_container->set('assets.bag', $assets_bag);

		$context = new \phpbb\template\context();
		$twig = new \phpbb\template\twig\environment(
			$assets_bag,
			$config,
			$filesystem,
			$phpbb_path_helper,
			$cache_path,
			null,
			new \phpbb\template\twig\loader(''),
			new \phpbb\event\dispatcher(),
			array(
				'cache'			=> false,
				'debug'			=> false,
				'auto_reload'	=> true,
				'autoescape'	=> false,
			)
		);
		$twig_extension = new \phpbb\template\twig\extension($context, $twig, $lang);
		$phpbb_container->set('template.twig.extensions.phpbb', $twig_extension);

		$twig_extensions_collection = new \phpbb\di\service_collection($phpbb_container);
		$twig_extensions_collection->add('template.twig.extensions.phpbb');
		$phpbb_container->set('template.twig.extensions.collection', $twig_extensions_collection);

		$twig->addExtension($twig_extension);
		$phpbb_container->set('template.twig.lexer', new \phpbb\template\twig\lexer($twig));

		if (!class_exists('messenger'))
		{
			include($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
		}

		$this->messenger = new \messenger();

		$reflection = new ReflectionObject($this->messenger);
		$this->reflection_template_property = $reflection->getProperty('template');
		$this->reflection_template_property->setAccessible(true);
	}

	public function email_parsing_data()
	{
		return array(
			array('Author username', 'Any forum', 'The topic title', 'Dear user'),
			array('0', 'Any forum', 'The topic title', 'Dear user'),
		);
	}

	/**
	 * @dataProvider email_parsing_data
	 */
	public function test_email_parsing($author_name, $forum_name, $topic_title, $username)
	{
		global $config, $phpEx, $user;

		$this->messenger->set_addresses($user->data);

		$this->messenger->assign_vars(array(
			'EMAIL_SIG'	=> str_replace('<br />', "\n", "-- \n" . html_entity_decode($config['board_email_sig'], ENT_COMPAT)),
			'SITENAME'	=> html_entity_decode($config['sitename'], ENT_COMPAT),

			'AUTHOR_NAME'				=> $author_name,
			'FORUM_NAME'				=> $forum_name,
			'TOPIC_TITLE'				=> $topic_title,
			'USERNAME'					=> $username,

			'U_FORUM'					=> generate_board_url() . "/viewforum.{$phpEx}?f=1",
			'U_STOP_WATCHING_FORUM'		=> generate_board_url() . "/viewforum.{$phpEx}?uid=2&f=1&unwatch=forum",
		));
		$this->messenger->template('newtopic_notify', $user->data['user_lang'], '', '');

		$reflection_template = $this->reflection_template_property->getValue($this->messenger);
		$msg = trim($reflection_template->assign_display('body'));

		$this->assertStringContainsString($author_name, $msg);
		$this->assertStringContainsString($forum_name, $msg);
		$this->assertStringContainsString($topic_title, $msg);
		$this->assertStringContainsString($username, $msg);
		$this->assertStringContainsString(html_entity_decode($config['sitename'], ENT_COMPAT), $msg);
		$this->assertStringContainsString(str_replace('<br />', "\n", "-- \n" . html_entity_decode($config['board_email_sig'], ENT_COMPAT)), $msg);
		$this->assertStringNotContainsString('EMAIL_SIG', $msg);
		$this->assertStringNotContainsString('U_STOP_WATCHING_FORUM', $msg);
	}
}
