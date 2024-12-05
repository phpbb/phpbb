<?php

use phpbb\avatar\driver\gravatar;
use phpbb\request\request;
use phpbb\request\request_interface;

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

class phpbb_avatar_driver_gravatar_test extends \phpbb_database_test_case
{

	/** @var \phpbb\config\config */
	private $config;

	/** @var gravatar */
	private $gravatar;

	/** @var request_interface */
	private $request;

	/** @var \phpbb\template\template */
	private $template;

	/** @var \phpbb\user */
	private $user;

	private $template_data = [];

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/users.xml');
	}

	protected function setUp(): void
	{
		global $phpbb_root_path, $phpEx;

		$this->config = new \phpbb\config\config(array());
		$this->request = $this->getMockBuilder(request::class)
			->disableOriginalConstructor()
			->onlyMethods(['get_super_global'])
			->getMock();
		$this->request->method('get_super_global')
			->willReturn([]);
		$this->template = $this->getMockBuilder(\phpbb\template\twig\twig::class)
			->disableOriginalConstructor()
			->onlyMethods(['assign_vars'])
			->getMock();
		$this->template->method('assign_vars')
			->will($this->returnCallback([$this, 'template_assign_vars']));
		$this->user = $this->getMockBuilder(\phpbb\user::class)
			->disableOriginalConstructor()
			->getMock();
		$imagesize = new \FastImageSize\FastImageSize();
		$cache = $this->createMock('\phpbb\cache\driver\driver_interface');
		$path_helper =  new \phpbb\path_helper(
			new \phpbb\symfony_request(
				$this->request
			),
			$this->request,
			$phpbb_root_path,
			$phpEx
		);

		global $phpbb_dispatcher;
		$phpbb_dispatcher = $this->getMockBuilder(\phpbb\event\dispatcher::class)
			->disableOriginalConstructor()
			->onlyMethods(['trigger_event'])
			->getMock();
		$phpbb_dispatcher->method('trigger_event')
			->willReturnArgument(1);

		$this->gravatar = new gravatar($this->config, $imagesize, $phpbb_root_path, $phpEx, $path_helper, $cache);
		$this->gravatar->set_name('avatar.driver.gravatar');
	}

	public function template_assign_vars($data)
	{
		$this->template_data = array_merge($this->template_data, $data);
	}

	public function data_prepare_form(): array
	{
		return [
			[
				// Only default empty values, no request data
				[
					'AVATAR_GRAVATAR_WIDTH'		=> '',
					'AVATAR_GRAVATAR_HEIGHT'	=> '',
					'AVATAR_GRAVATAR_EMAIL'		=> '',
				],
				[],
				[
					'avatar_type'			=> '',
					'avatar_width'			=> '',
					'avatar_height'			=> '',
				]
			],
			[
				// Only default empty values, request data set
				[
					'AVATAR_GRAVATAR_WIDTH'		=> '80',
					'AVATAR_GRAVATAR_HEIGHT'	=> '90',
					'AVATAR_GRAVATAR_EMAIL'		=> '',
				],
				[
					request_interface::POST => [
						'avatar_type'				=> 'avatar.driver.gravatar',
						'avatar_gravatar_width'		=> '80',
						'avatar_gravatar_height'	=> '90',
					],
				],
				[
					'avatar_type'			=> '',
					'avatar_width'			=> '80',
					'avatar_height'			=> '90',
				]
			],
			[
				// Only default empty values, request data set
				[
					'AVATAR_GRAVATAR_WIDTH'		=> '70',
					'AVATAR_GRAVATAR_HEIGHT'	=> '60',
					'AVATAR_GRAVATAR_EMAIL'		=> 'bar@foo.com',
				],
				[
					request_interface::POST => [
						'avatar_type'				=> 'avatar.driver.gravatar',
						'avatar_gravatar_width'		=> '80',
						'avatar_gravatar_height'	=> '90',
					],
				],
				[
					'avatar_type'			=> 'avatar.driver.gravatar',
					'avatar'				=> 'bar@foo.com',
					'avatar_width'			=> '70',
					'avatar_height'			=> '60',
				]
			],
		];
	}

	/**
	 * @dataProvider data_prepare_form
	 */
	public function test_prepare_form($expected_vars, $request_data, $row)
	{
		$error = [];
		$this->template_data = [];

		$request = $this->getMockBuilder(request::class)
			->disableOriginalConstructor()
			->onlyMethods(['get_super_global'])
			->getMock();
		$request->method('get_super_global')
			->willReturn([]);

		$requestInputReflection = new \ReflectionProperty($request, 'input');
		$requestInputReflection->setAccessible(true);
		$request_data[request_interface::GET] = $request_data[request_interface::GET] ?? [];
		$request_data[request_interface::POST] = $request_data[request_interface::POST] ?? [];
		$request_data[request_interface::REQUEST] = $request_data[request_interface::GET] + $request_data[request_interface::POST];
		$requestInputReflection->setValue($request, $request_data);
		$requestTypeCastHelperReflection = new \ReflectionProperty($request, 'type_cast_helper');
		$requestTypeCastHelperReflection->setAccessible(true);
		$requestTypeCastHelperReflection->setValue($request, new \phpbb\request\type_cast_helper());

		$this->gravatar->prepare_form($request, $this->template, $this->user, $row, $error);

		// Error not touched by gravatar
		$this->assertEquals([], $error);

		$this->assertEquals($expected_vars, $this->template_data);
	}

	public function test_gravatar_misc(): void
	{
		$this->assertEquals('ucp_avatar_options_gravatar.html', $this->gravatar->get_template_name());
		$this->assertEquals('acp_avatar_options_gravatar.html', $this->gravatar->get_acp_template_name());

		$row = [
			'avatar_type'			=> 'avatar.driver.gravatar',
			'avatar'				=> 'bar@foo.com',
			'avatar_width'			=> '70',
			'avatar_height'			=> '60',
		];
		$this->assertEquals('<img class="gravatar" src="//gravatar.com/avatar/e0ee9d02824d4320a999507150c5b8a371c635c41f645ba3a7205f36384dc199?s=70" width="70" height="60" alt="" />', $this->gravatar->get_custom_html($this->user, $row));
	}

	public function test_get_data(): void
	{
		$row = [
			'avatar_type'			=> 'avatar.driver.gravatar',
			'avatar'				=> 'bar@foo.com',
			'avatar_width'			=> '70',
			'avatar_height'			=> '60',
		];

		$this->assertEquals([
			'src'		=> '//gravatar.com/avatar/e0ee9d02824d4320a999507150c5b8a371c635c41f645ba3a7205f36384dc199?s=70',
			'width'		=> '70',
			'height'	=> '60',
		], $this->gravatar->get_data($row));
	}
}
