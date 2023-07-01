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

class phpbb_template_extension_test extends phpbb_template_template_test_case
{
	protected function setup_engine(array $new_config = [])
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
		$this->user->style['style_path'] = 'chameleon';
		$this->user->style['style_parent_id'] = 0;

		global $auth, $request, $symfony_request, $user;
		$user = $this->createMock(\phpbb\user::class);
		$user->optionset('user_id', 2);
		$user->style['style_path'] = '';
		$user->data['user_id'] = 2;
		$auth = $this->getMockBuilder('phpbb\auth\auth')
			->disableOriginalConstructor()
			->setMethods(['acl_get'])
			->getMock();
		$auth->method('acl_get')
			->willReturn(true);

		$filesystem = $this->createMock('\phpbb\filesystem\filesystem');
		$filesystem->expects($this->any())
			->method('exists')
			->with($this->stringContains('theme/png/'))
			->will($this->returnValueMap([
				['phpBB/styles/chameleon/theme/png/phone.png', true],
				['phpBB/styles/chameleon/theme/png/pencil.png', true],
				['phpBB/styles/chameleon/theme/png/user.png', false],
			]));
		$request = new phpbb_mock_request;
		$symfony_request = new \phpbb\symfony_request(
			$request
		);
		$phpbb_path_helper = new \phpbb\path_helper(
			$symfony_request,
			$request,
			$phpbb_root_path,
			$phpEx
		);
		$storage = $this->getMockBuilder('\phpbb\storage\storage')
			->disableOriginalConstructor()
			->getMock();

		$routing_helper = $this->createMock(\phpbb\routing\helper::class);
		$routing_helper->method('route')
			->willReturnCallback(function($route, $params) {
				return 'download/avatar/' . $params['file'];
			});

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$phpbb_container = new phpbb_mock_container_builder();
		$files = new phpbb\files\factory($phpbb_container);
		$upload_avatar_driver = new phpbb\avatar\driver\upload($config, $phpbb_root_path, $phpEx, $storage, $phpbb_path_helper, $routing_helper, $phpbb_dispatcher, $files, new \bantu\IniGetWrapper\IniGetWrapper());
		$upload_avatar_driver->set_name('avatar.driver.upload');
		$phpbb_container->set('avatar.manager', new \phpbb\avatar\manager($config, $phpbb_dispatcher, [
			$upload_avatar_driver,
		]));
		$phpbb_container->set('path_helper', $phpbb_path_helper);

		$class = new ReflectionClass('\phpbb\avatar\manager');
		$enabled_drivers = $class->getProperty('enabled_drivers');
		$enabled_drivers->setAccessible(true);
		$enabled_drivers->setValue($class, false);
		$avatar_helper = new phpbb\avatar\helper(
			$config,
			$phpbb_dispatcher,
			$lang,
			$phpbb_container->get('avatar.manager'),
			$phpbb_path_helper,
			$user
		);

		$this->template_path = $this->test_path . '/templates';

		$cache_path = $phpbb_root_path . 'cache/twig';
		$context = new \phpbb\template\context();
		$loader = new \phpbb\template\twig\loader([]);
		$log = new \phpbb\log\dummy();
		$assets_bag = new \phpbb\template\assets_bag();
		$twig = new \phpbb\template\twig\environment(
			$assets_bag,
			$config,
			$filesystem,
			$phpbb_path_helper,
			$cache_path,
			null,
			$loader,
			new \phpbb\event\dispatcher(),
			[
				'cache'			=> false,
				'debug'			=> false,
				'auto_reload'	=> true,
				'autoescape'	=> false,
			]
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
				new \phpbb\template\twig\extension\avatar($avatar_helper),
				new \phpbb\template\twig\extension\config($config),
				new \phpbb\template\twig\extension\icon($this->user),
				new \phpbb\template\twig\extension\username(),
			]
		);
		$twig->setLexer(new \phpbb\template\twig\lexer($twig));

		$this->template->set_custom_style('tests', [
			$this->template_path,
			$phpbb_root_path . 'styles/all/imgs',
			$phpbb_root_path . 'styles/all/template',
		]);
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
				'<img class="avatar" src="download/avatar/great_avatar.png" width="90" height="90" alt="foo">',
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
				'<img class="avatar" src="phpBB/styles//theme/images/no_avatar.gif" data-src="download/avatar/great_avatar.png" width="90" height="90" alt="foo">',
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
				'avatar_group.html',
				[
					'row' => [
						'group_avatar' => 'great_avatar.png',
						'group_avatar_type' => 'avatar.driver.upload',
						'group_avatar_width' => 90,
						'group_avatar_height' => 90,
					],
					'alt' => 'foo'
				],
				[],
				[],
				'<img class="avatar" src="download/avatar/great_avatar.png" width="90" height="90" alt="foo">',
				[]
			],
			[
				'avatar_group.html',
				[
					'row' => [
						'group_avatar' => 'great_avatar.png',
						'group_avatar_type' => 'avatar.driver.upload',
						'group_avatar_width' => 90,
						'group_avatar_height' => 90,
					],
					'alt' => 'foo',
					'ignore_config' => true,
					'lazy' => true,
				],
				[],
				[],
				'<img class="avatar" src="phpBB/styles//theme/images/no_avatar.gif" data-src="download/avatar/great_avatar.png" width="90" height="90" alt="foo">',
				[]
			],
			[
				'avatar_group.html',
				[
					'row' => [
						'group_avatar' => 'foo@bar.com',
						'group_avatar_type' => 'avatar.driver.gravatar',
						'group_avatar_width' => 90,
						'group_avatar_height' => 90,
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
		];
	}

	/**
	 * @dataProvider data_template_extensions
	 */
	public function test_template_extensions($file, $vars, $block_vars, $destroy_array, $expected, $lang_vars = [])
	{
		$this->run_template($file, $vars, $block_vars, $destroy_array, $expected, $lang_vars);
	}

	public function data_template_icon_extension()
	{
		return [
			/** Font: default */
			[
				[
					'type'			=> 'font',
					'icon'			=> 'phone',
					'title'			=> 'ICON_PHONE',
					'hidden'		=> false,
					'classes'		=> '',
					'attributes'	=> [],
				],
				[
					'ICON_PHONE'	=> 'Phone icon',
				],
				'<i class="o-icon o-icon-font fa-fw fa-phone fas"></i><span>Phone icon</span>',

			],
			/** Font: all options */
			[
				[
					'type'			=> 'font',
					'icon'			=> 'pencil',
					'title'			=> 'ICON_PENCIL',
					'hidden'		=> true,
					'classes'		=> 'a-class another-class',
					'attributes'	=> [
						'data-attr-1'	=> 'true',
						'data-attr-2'	=> 'two',
					],
				],
				[
					'ICON_PENCIL'	=> 'Pencil icon',
				],
				'<i class="o-icon o-icon-font fa-fw fa-pencil fas a-class another-class" title="Pencil icon" aria-hidden="true" data-attr-1="true" data-attr-2="two"></i>
				<span class="sr-only">Pencil icon</span>'
			],
			/** Font: icons array */
			[
				[
					'type'			=> 'font',
					'icon'			=> [
						'bullhorn'		=> false,
						'thumbtack'			=> false,
						'lock'			=> true,
						'fire'			=> false,
						'file'			=> true,
					],
					'title'			=> 'ICON_TOPIC',
					'hidden'		=> false,
					'classes'		=> '',
					'attributes'	=> [],
				],
				[
					'ICON_TOPIC'	=> 'Topic icon',
				],
				'<i class="o-icon o-icon-font fa-fw fa-lock fas"></i>
				<span>Topic icon</span>',
			],
			/** Font: icons array with no key for the default */
			[
				[
					'type'			=> 'font',
					'icon'			=> [
						'bullhorn'		=> false,
						'thumbtack'			=> false,
						'lock'			=> false,
						'fire'			=> false,
						'file',
					],
					'title'			=> 'ICON_TOPIC',
					'hidden'		=> false,
					'classes'		=> '',
					'attributes'	=> [],
				],
				[
					'ICON_TOPIC'	=> 'Topic icon',
				],
				'<i class="o-icon o-icon-font fa-fw fa-file fas"></i>
				<span>Topic icon</span>',
			],
			/** PNG: default */
			[
				[
					'type'			=> 'png',
					'icon'			=> 'phone',
					'title'			=> 'ICON_PHONE',
					'hidden'		=> false,
					'classes'		=> '',
					'attributes'	=> [],
				],
				[
					'ICON_PHONE'	=> 'Phone icon',
				],
				'<img class="o-icon o-icon-png png-phone" src="phpBB/styles/chameleon/theme/png/phone.png" alt="Phone icon" />',
			],
			/** PNG: all options */
			[
				[
					'type'			=> 'png',
					'icon'			=> 'pencil',
					'title'			=> 'ICON_PENCIL',
					'hidden'		=> true,
					'classes'		=> 'my-class',
					'attributes'	=> [
						'data-url'		=> 'my-test-url/test-page.php?u=2',
					],
				],
				[
					'ICON_PENCIL'	=> 'Pencil icon',
				],
				'<img class="o-icon o-icon-png png-pencil my-class" src="phpBB/styles/chameleon/theme/png/pencil.png" alt="Pencil icon" data-url="my-test-url/test-page.php?u=2" />',
			],
			/** PNG: Not found */
			[
				[
					'type'			=> 'png',
					'icon'			=> 'user',
					'title'			=> 'ICON_USER',
					'hidden'		=> false,
					'classes'		=> 'my-class',
					'attributes'	=> [],
				],
				[
					'ICON_USER'		=> 'User icon',
				],
				'<svg class="o-icon o-icon-svg svg-404 my-class" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" aria-labelledby="{unique_id}" role="img">
					<title id="{unique_id}">User icon</title>
					<g fill="none" fill-rule="evenodd">
						<path fill="#D8D8D8" d="M0 0h512v512H0z"></path>
						<path fill="#979797" fill-rule="nonzero" d="M8 6.586l496 496v2.828L8 9.414z"></path>
						<path fill="#979797" fill-rule="nonzero" d="M504 7.586v2.828l-496 496v-2.828z"></path>
					</g>
				</svg>',
			],
			/** SVG: default */
			[
				[
					'type'			=> 'svg',
					'icon'			=> 'phone',
					'title'			=> 'ICON_PHONE',
					'hidden'		=> false,
					'classes'		=> '',
					'attributes'	=> [],
				],
				[
					'ICON_PHONE'	=> 'Phone icon',
				],
				'<svg class="o-icon o-icon-svg svg-phone" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-labelledby="{unique_id}" role="img">
					<title id="{unique_id}">Phone icon</title>
					<path fill="none" d="M0 0h24v24H0z"></path>
					<path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56-.35-.12-.74-.03-1.01.24l-1.57 1.97c-2.83-1.35-5.48-3.9-6.89-6.83l1.95-1.66c.27-.28.35-.67.24-1.02-.37-1.11-.56-2.3-.56-3.53 0-.54-.45-.99-.99-.99H4.19C3.65 3 3 3.24 3 3.99 3 13.28 10.73 21 20.01 21c.71 0 .99-.63.99-1.18v-3.45c0-.54-.45-.99-.99-.99z"></path>
				</svg>',
			],
			/** SVG: all options */
			[
				[
					'type'			=> 'svg',
					'icon'			=> 'pencil',
					'title'			=> 'ICON_PENCIL',
					'hidden'		=> true,
					'classes'		=> 'my-svg-class',
					'attributes'	=> [
						'data-ajax'		=> 'my_ajax_callback',
					],
				],
				[
					'ICON_PENCIL'	=> 'Pencil icon',
				],
				'<svg class="o-icon o-icon-svg svg-pencil my-svg-class" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" aria-labelledby="{unique_id}" role="img" data-ajax="my_ajax_callback">
					<title id="{unique_id}">Pencil icon</title>
					<path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"></path>
					<path d="M0 0h24v24H0z" fill="none"></path>
				</svg>',
			],
			/** SVG: Not found */
			[
				[
					'type'			=> 'svg',
					'icon'			=> 'not-existent',
					'title'			=> 'Just a title',
					'hidden'		=> false,
					'classes'		=> '',
					'attributes'	=> [],
				],
				[],
				'<svg class="o-icon o-icon-svg svg-404" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" aria-labelledby="{unique_id}" role="img">
					<title id="{unique_id}">Just a title</title>
					<g fill="none" fill-rule="evenodd">
						<path fill="#D8D8D8" d="M0 0h512v512H0z"></path>
						<path fill="#979797" fill-rule="nonzero" d="M8 6.586l496 496v2.828L8 9.414z"></path>
						<path fill="#979797" fill-rule="nonzero" d="M504 7.586v2.828l-496 496v-2.828z"></path>
					</g>
				</svg>',
			],
			/** SVG: Sanitization */
			[
				[
					'type'			=> 'svg',
					'icon'			=> 'dirty',
					'title'			=> '',
					'hidden'		=> false,
					'classes'		=> '',
					'attributes'	=> [],
				],
				[],
				'<svg class="o-icon o-icon-svg svg-dirty" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 728 242" role="img">
					<path fill-rule="evenodd" d="M139.05,117.4938 C139.05,129.1958 137.603,139.9498 134.718,149.7578 C131.832,159.5708 127.634,168.0768 122.129,175.2728 C116.623,182.4748 109.764,188.0558 101.554,192.0128 C93.344,195.9698 83.915,197.9538 73.267,197.9538 C65.142,197.9538 57.877,195.9278 51.473,191.8788 C45.064,187.8288 40.057,182.6558 36.45,176.3528 L36.45,240.6138 L25.194,240.6138 C21.976,240.6138 18.85,240.0268 15.811,238.8578 C12.774,237.6858 10.091,236.0228 7.77,233.8638 C5.446,231.7038 3.569,229.0918 2.144,226.0338 C0.713,222.9698 0,219.4608 0,215.5038 L0,127.4828 C0,115.4258 1.393,104.3558 4.185,94.2728 C6.974,84.1948 11.34,75.5548 17.28,68.3538 C23.22,61.1558 30.78,55.5748 39.96,51.6128 C49.14,47.6558 60.117,45.6728 72.9,45.6728 C82.62,45.6728 91.53,47.6978 99.63,51.7488 C107.729,55.7978 114.703,61.1558 120.555,67.8138 C126.402,74.4748 130.95,82.1238 134.19,90.7628 C137.43,99.4038 139.05,108.3128 139.05,117.4938 Z M101.79,126.9438 C101.79,109.6638 98.942,97.1548 93.247,89.4138 C87.552,81.6758 78.831,77.8028 67.087,77.8028 C56.966,77.8028 49.241,81.7178 43.909,89.5478 C38.576,97.3788 35.91,107.5958 35.91,120.1938 C35.91,134.7728 39.205,146.0248 45.803,153.9438 C52.401,161.8658 61.121,165.8238 71.968,165.8238 C80.823,165.8238 88.007,162.2708 93.521,155.1588 C99.031,148.0488 101.79,138.6458 101.79,126.9438 Z M267.5684,194.7134 C260.0084,194.7134 254.0224,192.6464 249.6134,188.5034 C245.2014,184.3644 242.9994,178.3364 242.9994,170.4134 L242.9994,111.0134 C242.9994,105.0734 242.2264,99.9894 240.6914,95.7584 C239.1554,91.5304 237.0754,88.1094 234.4514,85.4984 C231.8274,82.8914 228.7524,80.9544 225.2244,79.6934 C221.6984,78.4364 217.9434,77.8034 213.9644,77.8034 C211.0714,77.8034 208.0844,78.3894 205.0124,79.5584 C201.9374,80.7314 199.0894,82.6634 196.4654,85.3634 C193.8414,88.0634 191.7194,91.5734 190.0904,95.8934 C188.4634,100.2134 187.6484,105.6134 187.6484,112.0934 L187.6484,194.7134 L175.8574,194.7134 C167.2764,194.7134 161.0244,192.5534 157.0914,188.2334 C153.1604,183.9134 151.1984,177.9734 151.1984,170.4134 L151.1984,0.3134 L162.4544,0.3134 C171.0314,0.3134 177.4184,2.4734 181.6204,6.7934 C185.8174,11.1134 187.9194,16.6944 187.9194,23.5334 L188.1884,65.1134 C189.4454,62.9534 191.2014,60.7514 193.4534,58.4984 C195.7024,56.2504 198.1784,54.1784 200.8794,52.2884 C203.5784,50.3984 206.5484,48.8244 209.7894,47.5634 C213.0284,46.3064 216.3574,45.6734 219.7784,45.6734 C238.6784,45.6734 253.3474,51.1194 263.7894,62.0084 C274.2254,72.9014 279.4484,88.6954 279.4484,109.3934 L279.4484,194.7134 L267.5684,194.7134 Z M436.0442,117.4938 C436.0442,129.1958 434.5982,139.9498 431.7122,149.7578 C428.8272,159.5708 424.6282,168.0768 419.1242,175.2728 C413.6182,182.4748 406.7582,188.0558 398.5482,192.0128 C390.3392,195.9698 380.9102,197.9538 370.2622,197.9538 C362.1372,197.9538 354.8722,195.9278 348.4682,191.8788 C342.0592,187.8288 337.0522,182.6558 333.4442,176.3528 L333.4442,240.6138 L322.1882,240.6138 C318.9702,240.6138 315.8442,240.0268 312.8062,238.8578 C309.7682,237.6858 307.0862,236.0228 304.7652,233.8638 C302.4412,231.7038 300.5632,229.0918 299.1382,226.0338 C297.7082,222.9698 296.9942,219.4608 296.9942,215.5038 L296.9942,127.4828 C296.9942,115.4258 298.3872,104.3558 301.1802,94.2728 C303.9682,84.1948 308.3352,75.5548 314.2742,68.3538 C320.2152,61.1558 327.7742,55.5748 336.9542,51.6128 C346.1352,47.6558 357.1112,45.6728 369.8942,45.6728 C379.6142,45.6728 388.5242,47.6978 396.6252,51.7488 C404.7242,55.7978 411.6982,61.1558 417.5502,67.8138 C423.3972,74.4748 427.9442,82.1238 431.1842,90.7628 C434.4252,99.4038 436.0442,108.3128 436.0442,117.4938 Z M398.7842,126.9438 C398.7842,109.6638 395.9362,97.1548 390.2412,89.4138 C384.5462,81.6758 375.8262,77.8028 364.0812,77.8028 C353.9602,77.8028 346.2352,81.7178 340.9032,89.5478 C335.5712,97.3788 332.9042,107.5958 332.9042,120.1938 C332.9042,134.7728 336.1992,146.0248 342.7982,153.9438 C349.3952,161.8658 358.1162,165.8238 368.9622,165.8238 C377.8172,165.8238 385.0022,162.2708 390.5152,155.1588 C396.0252,148.0488 398.7842,138.6458 398.7842,126.9438 Z M581.5745,137.4732 C581.5745,146.8342 579.8615,155.1152 576.4445,162.3132 C573.0225,169.5152 568.3855,175.5892 562.5395,180.5382 C556.6875,185.4912 549.9375,189.2252 542.2895,191.7432 C534.6355,194.2662 526.5835,195.5232 518.1245,195.5232 L498.1435,195.5232 C463.7605,195.5232 446.5745,180.8552 446.5745,151.5132 L446.5745,17.3232 C448.7345,16.6062 451.7925,15.7502 455.7535,14.7582 C459.7105,13.7712 463.9895,12.8262 468.5795,11.9232 C473.1685,11.0252 477.8475,10.3032 482.6195,9.7632 C487.3855,9.2232 491.6645,8.9532 495.4445,8.9532 L517.8535,8.9532 C526.6705,8.9532 534.7715,10.0792 542.1545,12.3282 C549.5325,14.5812 555.9235,17.8212 561.3245,22.0482 C566.7235,26.2802 570.9095,31.4092 573.8785,37.4382 C576.8485,43.4712 578.3345,50.2632 578.3345,57.8232 C578.3345,68.0832 575.7225,76.5002 570.5035,83.0692 C565.2815,89.6412 558.0845,94.2732 548.9045,96.9732 C553.5835,98.2352 557.9025,100.3062 561.8645,103.1832 C565.8215,106.0652 569.2895,109.3512 572.2585,113.0382 C575.2285,116.7302 577.5245,120.7332 579.1435,125.0532 C580.7635,129.3732 581.5745,133.5162 581.5745,137.4732 Z M544.3135,138.5532 C544.3135,128.4752 541.7495,121.5432 536.6195,117.7632 C531.4895,113.9832 523.5245,112.0932 512.7235,112.0932 L483.2935,112.0932 L483.2935,150.1632 C483.2935,153.4042 484.8215,156.1502 487.8835,158.3982 C490.9425,160.6512 494.8115,161.7732 499.4945,161.7732 L514.6145,161.7732 C524.6925,161.7732 532.1645,159.6592 537.0245,155.4282 C541.8835,151.2012 544.3135,145.5742 544.3135,138.5532 Z M541.3435,61.0632 C541.3435,57.1062 540.4875,53.7312 538.7795,50.9382 C537.0665,48.1502 534.8645,45.9432 532.1645,44.3232 C529.4635,42.7032 526.4015,41.5342 522.9845,40.8132 C519.5625,40.0962 516.1415,39.7332 512.7235,39.7332 L498.9545,39.7332 C496.6125,39.7332 494.0005,39.9612 491.1245,40.4082 C488.2425,40.8602 485.5425,41.2642 483.0245,41.6232 L483.0245,83.4732 L511.3745,83.4732 C519.6515,83.4732 526.7175,81.7182 532.5695,78.2082 C538.4165,74.6982 541.3435,68.9862 541.3435,61.0632 Z M727.3733,137.4732 C727.3733,146.8342 725.6603,155.1152 722.2433,162.3132 C718.8213,169.5152 714.1843,175.5892 708.3383,180.5382 C702.4863,185.4912 695.7363,189.2252 688.0883,191.7432 C680.4343,194.2662 672.3823,195.5232 663.9233,195.5232 L643.9423,195.5232 C609.5593,195.5232 592.3733,180.8552 592.3733,151.5132 L592.3733,17.3232 C594.5333,16.6062 597.5913,15.7502 601.5523,14.7582 C605.5093,13.7712 609.7883,12.8262 614.3783,11.9232 C618.9673,11.0252 623.6463,10.3032 628.4183,9.7632 C633.1843,9.2232 637.4633,8.9532 641.2433,8.9532 L663.6523,8.9532 C672.4693,8.9532 680.5703,10.0792 687.9533,12.3282 C695.3313,14.5812 701.7223,17.8212 707.1233,22.0482 C712.5223,26.2802 716.7083,31.4092 719.6773,37.4382 C722.6473,43.4712 724.1333,50.2632 724.1333,57.8232 C724.1333,68.0832 721.5213,76.5002 716.3023,83.0692 C711.0803,89.6412 703.8833,94.2732 694.7033,96.9732 C699.3823,98.2352 703.7013,100.3062 707.6633,103.1832 C711.6203,106.0652 715.0883,109.3512 718.0573,113.0382 C721.0273,116.7302 723.3233,120.7332 724.9423,125.0532 C726.5623,129.3732 727.3733,133.5162 727.3733,137.4732 Z M690.1123,138.5532 C690.1123,128.4752 687.5483,121.5432 682.4183,117.7632 C677.2883,113.9832 669.3233,112.0932 658.5223,112.0932 L629.0923,112.0932 L629.0923,150.1632 C629.0923,153.4042 630.6203,156.1502 633.6823,158.3982 C636.7413,160.6512 640.6103,161.7732 645.2933,161.7732 L660.4133,161.7732 C670.4913,161.7732 677.9633,159.6592 682.8233,155.4282 C687.6823,151.2012 690.1123,145.5742 690.1123,138.5532 Z M687.1423,61.0632 C687.1423,57.1062 686.2863,53.7312 684.5783,50.9382 C682.8653,48.1502 680.6633,45.9432 677.9633,44.3232 C675.2623,42.7032 672.2003,41.5342 668.7833,40.8132 C665.3613,40.0962 661.9403,39.7332 658.5223,39.7332 L644.7533,39.7332 C642.4113,39.7332 639.7993,39.9612 636.9233,40.4082 C634.0413,40.8602 631.3413,41.2642 628.8233,41.6232 L628.8233,83.4732 L657.1733,83.4732 C665.4503,83.4732 672.5163,81.7182 678.3683,78.2082 C684.2153,74.6982 687.1423,68.9862 687.1423,61.0632z"></path>
				</svg>',
			],
		];
	}

	/**
	 * @dataProvider data_template_icon_extension
	 */
	public function test_template_icon_extension($vars, $lang_vars, $expected)
	{
		$file = 'extension_icon_test.html';

		$this->template->set_filenames(['test' => $file]);
		$this->template->assign_vars($vars);

		foreach ($lang_vars as $name => $value)
		{
			self::$language_reflection_lang->setValue($this->lang, array_merge(
				self::$language_reflection_lang->getValue($this->lang),
				[$name => $value]
			));
		}

		$expected = str_replace(["\n", "\r", "\t"], '', $expected);
		$output = str_replace(["\n", "\r", "\t"], '', $this->display('test'));

		/**
		 * SVGs need their random identifier replaced (normalized).
		 * The 'user' is a PNG, but not existent, so it returns a 404 SVG.
		 */
		if ($vars['type'] === 'svg' || $vars['icon'] === 'user')
		{
			if (preg_match('#<title id="([a-z0-9]+)">#', $output, $unique_id))
			{
				$expected = str_replace('{unique_id}', $unique_id[1], $expected);
			}
		}

		$this->assertEquals($expected, $output, "Testing {$file}");
	}
}
