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

require_once __DIR__ . '/../template/template_test_case.php';

class phpbb_pagination_pagination_test extends phpbb_template_template_test_case
{
	protected $test_path = 'tests/pagination';

	public function return_callback_implode()
	{
		return implode('-', func_get_args());
	}

	protected function setUp(): void
	{
		parent::setUp();

		global $phpbb_dispatcher, $phpbb_root_path, $phpEx;

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$this->user = $this->createMock('\phpbb\user');
		$this->user->expects($this->any())
			->method('lang')
			->will($this->returnCallback(array($this, 'return_callback_implode')));

		$this->config = new \phpbb\config\config(array('enable_mod_rewrite' => '1'));

		$manager = new phpbb_mock_extension_manager(__DIR__ . '/', array());

		$loader = new \Symfony\Component\Routing\Loader\YamlFileLoader(
			new \phpbb\routing\file_locator(__DIR__ . '/')
		);
		$resources_locator = new \phpbb\routing\resources_locator\default_resources_locator(__DIR__ . '/', PHPBB_ENVIRONMENT, $manager);

		$mock_container = new phpbb_mock_container_builder();
		$mock_container->set('cron.task_collection', []);

		$router = new phpbb_mock_router(new phpbb_mock_container_builder(), $resources_locator, $loader, 'php', __DIR__ . '/', true, true);

		$request = new phpbb_mock_request();
		$request->overwrite('SCRIPT_NAME', '/app.php', \phpbb\request\request_interface::SERVER);
		$request->overwrite('SCRIPT_FILENAME', 'app.php', \phpbb\request\request_interface::SERVER);
		$request->overwrite('REQUEST_URI', '/app.php', \phpbb\request\request_interface::SERVER);

		$symfony_request = new \phpbb\symfony_request(
			$request
		);

		$db = $this->getMockBuilder('\phpbb\db\driver\mysqli')
			->disableOriginalConstructor()
			->getMock();

		$this->routing_helper = new \phpbb\routing\helper($this->config, $router, $symfony_request, $request, '', 'php');
		$this->helper = new phpbb_mock_controller_helper(
			new \phpbb\auth\auth(),
			new \phpbb\cache\driver\dummy(),
			$this->config,
			new \phpbb\cron\manager($mock_container, $this->routing_helper, '', 'php', null),
			$db,
			new phpbb_mock_event_dispatcher(),
			new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx)),
			$request,
			$this->routing_helper,
			$symfony_request,
			$this->template,
			$this->user,
			$phpbb_root_path,
			'adm/',
			'php'
		);
		$this->pagination = new \phpbb\pagination($this->template, $this->user, $this->helper, $phpbb_dispatcher);
	}

	public function generate_template_pagination_data()
	{
		return array(
			array(
				'page.php',
				'start',
				95,
				10,
				10,
				'pagination
				:per_page:10
				:current_page:2
				:base_url:page.php
				:previous::page.php
				:else:1:page.php
				:current:2:page.php?start=10
				:else:3:page.php?start=20
				:else:4:page.php?start=30
				:else:5:page.php?start=40
				:ellipsis:9:page.php?start=80
				:else:10:page.php?start=90
				:next::page.php?start=20
				:u_prev:page.php
				:u_next:page.php?start=20',
			),
			array(
				'page.php',
				'start',
				95,
				10,
				20,
				'pagination
				:per_page:10
				:current_page:3
				:base_url:page.php
				:previous::page.php?start=10
				:else:1:page.php
				:else:2:page.php?start=10
				:current:3:page.php?start=20
				:else:4:page.php?start=30
				:else:5:page.php?start=40
				:ellipsis:9:page.php?start=80
				:else:10:page.php?start=90
				:next::page.php?start=30
				:u_prev:page.php?start=10
				:u_next:page.php?start=30',
			),
			array(
				array('routes' => array(
					'core_controller',
					'core_page_controller',
				)),
				'page',
				95,
				10,
				10,
				'pagination
				:per_page:10
				:current_page:2
				:base_url:
				:previous::/test
				:else:1:/test
				:current:2:/test/page/2
				:else:3:/test/page/3
				:else:4:/test/page/4
				:else:5:/test/page/5
				:ellipsis:9:/test/page/9
				:else:10:/test/page/10
				:next::/test/page/3
				:u_prev:/test
				:u_next:/test/page/3',
			),
			array(
				array('routes' => array(
					'core_controller',
					'core_page_controller',
				)),
				'page',
				95,
				10,
				20,
				'pagination
				:per_page:10
				:current_page:3
				:base_url:
				:previous::/test/page/2
				:else:1:/test
				:else:2:/test/page/2
				:current:3:/test/page/3
				:else:4:/test/page/4
				:else:5:/test/page/5
				:ellipsis:9:/test/page/9
				:else:10:/test/page/10
				:next::/test/page/4
				:u_prev:/test/page/2
				:u_next:/test/page/4',
			),
		);
	}

	/**
	* @dataProvider generate_template_pagination_data
	*/
	public function test_generate_template_pagination($base_url, $start_name, $num_items, $per_page, $start_item, $expect)
	{
		$this->pagination->generate_template_pagination($base_url, 'pagination', $start_name, $num_items, $per_page, $start_item);
		$this->template->set_filenames(array('test' => 'pagination.html'));

		$this->assertEquals(str_replace("\t", '', $expect), $this->display('test'));
	}

	/**
	 * @dataProvider generate_template_pagination_data
	 */
	public function test_generate_template_pagination_sub($base_url, $start_name, $num_items, $per_page, $start_item, $expect)
	{
		// Block needs to be assigned before pagination
		$this->template->assign_block_vars('sub', array(
			'FOO'		=> 'bar',
		));

		$this->pagination->generate_template_pagination($base_url, 'sub.pagination', $start_name, $num_items, $per_page, $start_item);
		$this->template->set_filenames(array('test' => 'pagination_sub.html'));

		$this->assertEquals(str_replace("\t", '', $expect), $this->display('test'));
	}

	/**
	 * @dataProvider generate_template_pagination_data
	 */
	public function test_generate_template_pagination_double_nested($base_url, $start_name, $num_items, $per_page, $start_item, $expect)
	{
		// Block needs to be assigned before pagination
		$this->template->assign_block_vars('sub', array(
			'FOO'		=> 'bar',
		));

		$this->template->assign_block_vars('sub.level2', array(
			'BAR'		=> 'foo',
		));

		$this->pagination->generate_template_pagination($base_url, 'sub.level2.pagination', $start_name, $num_items, $per_page, $start_item);
		$this->template->set_filenames(array('test' => 'pagination_double_nested.html'));

		$this->assertEquals(str_replace("\t", '', $expect), $this->display('test'));
	}

	public function on_page_data()
	{
		return array(
			array(
				10,
				10,
				0,
				'PAGE_OF-1-1',
			),
			array(
				'10',
				'10',
				'0',
				'PAGE_OF-1-1',
			),
		);
	}

	/**
	* @dataProvider on_page_data
	*/
	public function test_on_page($num_items, $per_page, $start_item, $expect_return)
	{
		$this->assertEquals($expect_return, $this->pagination->on_page($num_items, $per_page, $start_item));
	}

	public function validate_start_data()
	{
		return array(
			array(
				0,
				0,
				0,
			),
			array(
				-1,
				20,
				0,
			),
			array(
				20,
				-30,
				0,
			),
			array(
				0,
				20,
				0,
			),
			array(
				10,
				20,
				10,
			),
			array(
				20,
				20,
				10,
			),
			array(
				30,
				20,
				10,
			),
		);
	}

	/**
	* @dataProvider validate_start_data
	*/
	public function test_validate_start($start, $num_items, $expect)
	{
		$this->assertEquals($expect, $this->pagination->validate_start($start, 10, $num_items));
	}

	public function reverse_start_data()
	{
		return array(
			array(
				10,
				5,
				15,
				0,
			),
			array(
				10,
				10,
				25,
				5,
			),
		);
	}

	/**
	* @dataProvider reverse_start_data
	*/
	public function test_reverse_start($start, $limit, $num_items, $expect)
	{
		$this->assertEquals($expect, $this->pagination->reverse_start($start, $limit, $num_items));
	}

	public function reverse_limit_data()
	{
		return array(
			array(
				10,
				10,
				15,
				5,
			),
			array(
				20,
				10,
				15,
				1,
			),
		);
	}

	/**
	* @dataProvider reverse_limit_data
	*/
	public function test_reverse_limit($start, $per_page, $num_items, $expect)
	{
		$this->assertEquals($expect, $this->pagination->reverse_limit($start, $per_page, $num_items));
	}
}
