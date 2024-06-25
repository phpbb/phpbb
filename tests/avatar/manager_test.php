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

require_once __DIR__ . '/driver/foobar.php';

class phpbb_avatar_manager_test extends \phpbb_database_test_case
{
	/** @var \phpbb\avatar\manager */
	protected $manager;
	protected $avatar_foobar;
	protected $avatar_barfoo;
	protected $config;
	protected $db;
	protected $user;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/users.xml');
	}

	protected function setUp(): void
	{
		global $phpbb_dispatcher, $phpbb_root_path, $phpEx;

		// Mock phpbb_container
		$phpbb_container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
		$phpbb_container->expects($this->any())
			->method('get')
			->will($this->returnArgument(0));

		$storage = $this->createMock('\phpbb\storage\storage');

		// Prepare dependencies for avatar manager and driver
		$this->config = new \phpbb\config\config(array());
		$cache = $this->createMock('\phpbb\cache\driver\driver_interface');
		$path_helper =  new \phpbb\path_helper(
			new \phpbb\symfony_request(
				new phpbb_mock_request()
			),
			$this->createMock('\phpbb\request\request'),
			$phpbb_root_path,
			$phpEx
		);

		$imagesize = new \FastImageSize\FastImageSize();

		$dispatcher = new phpbb_mock_event_dispatcher();
		$phpbb_dispatcher = $dispatcher;

		$controller_helper = $this->createMock('\phpbb\controller\helper');
		$routing_helper = $this->createMock('\phpbb\routing\helper');

		// $this->avatar_foobar will be needed later on
		$this->avatar_foobar = $this->getMockBuilder('\phpbb\avatar\driver\foobar')
			->setMethods(array('get_name'))
			->setConstructorArgs(array($this->config, $imagesize, $phpbb_root_path, $phpEx, $path_helper, $cache))
			->getMock();
		$this->avatar_foobar->expects($this->any())
			->method('get_name')
			->will($this->returnValue('avatar.driver.foobar'));
		// barfoo driver can't be mocked with constructor arguments
		$this->avatar_barfoo = $this->getMockBuilder('\phpbb\avatar\driver\barfoo')
			->setMethods(array('get_name', 'get_config_name'))
			->getMock();
		$this->avatar_barfoo->expects($this->any())
			->method('get_name')
			->will($this->returnValue('avatar.driver.barfoo'));
		$this->avatar_barfoo->expects($this->any())
			->method('get_config_name')
			->will($this->returnValue('barfoo'));
		$avatar_drivers = array($this->avatar_foobar, $this->avatar_barfoo);

		$files_factory = new \phpbb\files\factory($phpbb_container);

		$php_ini = new \bantu\IniGetWrapper\IniGetWrapper;

		foreach ($this->avatar_drivers() as $driver)
		{
			if ($driver !== 'upload')
			{
				$cur_avatar = $this->getMockBuilder('\phpbb\avatar\driver\\' . $driver)
					->setMethods(array('get_name'))
					->setConstructorArgs(array($this->config, $imagesize, $phpbb_root_path, $phpEx, $path_helper, $cache))
					->getMock();
			}
			else
			{
				$cur_avatar = $this->getMockBuilder('\phpbb\avatar\driver\\' . $driver)
				->setMethods(array('get_name'))
				->setConstructorArgs(array($this->config, $phpbb_root_path, $phpEx, $storage, $path_helper, $routing_helper, $dispatcher, $files_factory, $php_ini))
				->getMock();
			}
			$cur_avatar->expects($this->any())
				->method('get_name')
				->will($this->returnValue('avatar.driver.' . $driver));
			$this->config['allow_avatar_' . get_class($cur_avatar)] = $driver == 'gravatar';
			$avatar_drivers[] = $cur_avatar;
		}

		$this->config['allow_avatar_' . get_class($this->avatar_foobar)] = true;
		$this->config['allow_avatar_' . get_class($this->avatar_barfoo)] = false;

		// Set up avatar manager
		$this->manager = new \phpbb\avatar\manager($this->config, $dispatcher, $avatar_drivers);
		$this->db = $this->new_dbal();
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$this->user = new \phpbb\user($lang, '\phpbb\datetime');
	}

	protected function avatar_drivers()
	{
		return array(
			'local',
			'upload',
			'gravatar',
		);
	}

	public function test_get_all_drivers()
	{
		$drivers = $this->manager->get_all_drivers();
		$this->assertEquals(array(
			'avatar.driver.barfoo' => 'avatar.driver.barfoo',
			'avatar.driver.foobar' => 'avatar.driver.foobar',
			'avatar.driver.local' => 'avatar.driver.local',
			'avatar.driver.upload' => 'avatar.driver.upload',
			'avatar.driver.gravatar' => 'avatar.driver.gravatar',
		), $drivers);
	}

	public function test_get_enabled_drivers()
	{
		$drivers = $this->manager->get_enabled_drivers();
		$this->assertArrayHasKey('avatar.driver.foobar', $drivers);
		$this->assertArrayNotHasKey('avatar.driver.barfoo', $drivers);
		$this->assertEquals('avatar.driver.foobar', $drivers['avatar.driver.foobar']);
	}

	public function get_driver_data_enabled()
	{
		return array(
			array('avatar.driver.foobar', 'avatar.driver.foobar'),
			array('avatar.driver.gravatar', 'avatar.driver.gravatar'),
			array('avatar.driver.foo_wrong', null),
			array('avatar.driver.local', null),
			array(AVATAR_GALLERY, null),
			array(AVATAR_UPLOAD, null),
		);
	}

	/**
	* @dataProvider get_driver_data_enabled
	*/
	public function test_get_driver_enabled($driver_name, $expected)
	{
		$driver = $this->manager->get_driver($driver_name);
		$this->assertEquals($expected, ($driver === null) ? null : $driver->get_name());
	}

	public function get_driver_data_all()
	{
		return array(
			array('avatar.driver.foobar', 'avatar.driver.foobar'),
			array('avatar.driver.foo_wrong', null),
			array('avatar.driver.local', 'avatar.driver.local'),
			array(AVATAR_GALLERY, 'avatar.driver.local'),
			array(AVATAR_UPLOAD, 'avatar.driver.upload'),
		);
	}

	/**
	* @dataProvider get_driver_data_all
	*/
	public function test_get_driver_all($driver_name, $expected)
	{
		$driver = $this->manager->get_driver($driver_name, false);
		$this->assertEquals($expected, ($driver === null) ? $driver : $driver->get_name());
	}

	public function test_get_avatar_settings()
	{
		$avatar_settings = $this->manager->get_avatar_settings($this->avatar_foobar);

		$expected_settings = [
			'allow_avatar_' . get_class($this->avatar_foobar)	=> [
				'lang' => 'ALLOW_' . strtoupper(get_class($this->avatar_foobar)),
				'validate' => 'bool',
				'type' => 'radio:yes_no',
				'explain' => true
			],
		];

		$this->assertEquals($expected_settings, $avatar_settings);
	}

	public function database_row_data()
	{
		return array(
			array(
				array(
					'user_avatar'		=> '',
					'user_avatar_type'	=> '',
					'user_avatar_width'	=> '',
					'user_avatar_height'	=> '',
					'group_avatar'		=> '',
				),
				array(
					'user_avatar'		=> '',
					'user_avatar_type'	=> '',
					'user_avatar_width'	=> '',
					'user_avatar_height'	=> '',
					'group_avatar'		=> '',
				),
				'foobar',
			),
			array(
				array(),
				array(
					'avatar'			=> '',
					'avatar_type'		=> '',
					'avatar_width'		=> 0,
					'avatar_height'		=> 0,
				),
			),
			array(
				array(
					'user_avatar'	=> '',
					'user_id'	=> 5,
					'group_id'	=> 4,
				),
				array(
					'user_avatar'	=> '',
					'user_id'	=> 5,
					'group_id'	=> 4,
				),
			),
			array(
				array(
					'user_avatar'	=> '',
					'user_id'	=> 5,
					'group_id'	=> 4,
				),
				array(
					'avatar'	=> '',
					'id'		=> 5,
					'group_id'	=> 4,
				),
				'user',
			),
			array(
				array(
					'group_avatar'	=> '',
					'user_id'	=> 5,
					'group_id'	=> 4,
				),
				array(
					'avatar'	=> '',
					'id'		=> 'g4',
					'user_id'	=> 5,
				),
				'group',
			),
		);
	}

	/**
	* @dataProvider database_row_data
	*/
	public function test_clean_row(array $input, array $output, $prefix = '')
	{
		$cleaned_row = \phpbb\avatar\manager::clean_row($input, $prefix);
		foreach ($output as $key => $value)
		{
			$this->assertArrayHasKey($key, $cleaned_row);
			$this->assertEquals($cleaned_row[$key], $value);
		}
	}

	public function test_clean_driver_name()
	{
		$this->assertEquals('avatar.driver.local', $this->manager->clean_driver_name('avatar_driver_local'));
	}

	public function test_prepare_driver_name()
	{
		$this->assertEquals('avatar_driver_local', $this->manager->prepare_driver_name('avatar.driver.local'));
	}

	public function test_localize_errors()
	{
		global $phpbb_root_path, $phpEx;

		$user = $this->getMockBuilder('\phpbb\user')
			->setMethods(array())
			->setConstructorArgs(array(new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx)), '\phpbb\datetime'))
			->getMock();
		$lang_array = array(
			array('FOOBAR_OFF', 'foobar_off'),
			array('FOOBAR_EXPLAIN', 'FOOBAR_EXPLAIN %s'),
		);
		$user->expects($this->any())
			->method('lang')
			->will($this->returnValueMap($lang_array));

		// Pass error as string
		$this->assertEquals(array('foobar_off'), $this->manager->localize_errors($user, array('FOOBAR_OFF')));

		// Pass error as array for vsprintf()
		$this->assertEquals(array('FOOBAR_EXPLAIN foo'), $this->manager->localize_errors($user, array(array('FOOBAR_EXPLAIN', 'foo'))));

		// Pass both types
		$this->assertEquals(array('foobar_off', 'FOOBAR_EXPLAIN foo'), $this->manager->localize_errors($user, array(
			'FOOBAR_OFF',
			array('FOOBAR_EXPLAIN', 'foo'),
		)));
	}

	public function data_handle_avatar_delete()
	{
		return array(
			array(
				array(
					'avatar'		=> '',
					'avatar_type'	=> '',
					'avatar_width'	=> 0,
					'avatar_height'	=> 0,
				),
				array(
					'id' => 1,
					'avatar'		=> 'foobar@example.com',
					'avatar_type'	=> 'avatar.driver.gravatar',
					'avatar_width'	=> '16',
					'avatar_height'	=> '16',
				), USERS_TABLE, 'user_',
			),
			array(
				array(
					'avatar'		=> '',
					'avatar_type'	=> '',
					'avatar_width'	=> 0,
					'avatar_height'	=> 0,
				),
				array(
					'id' => 5,
					'avatar'		=> 'g5_1414350991.jpg',
					'avatar_type'	=> 'avatar.driver.upload',
					'avatar_width'	=> '80',
					'avatar_height'	=> '80'
				), GROUPS_TABLE, 'group_',
			),
		);
	}

	/**
	* @dataProvider data_handle_avatar_delete
	*/
	public function test_handle_avatar_delete($expected, $avatar_data, $table, $prefix)
	{
		$this->config['allow_avatar_gravatar'] = true;
		$this->assertNull($this->manager->handle_avatar_delete($this->db, $this->user, $avatar_data, $table, $prefix));

		$sql = 'SELECT * FROM ' . $table . '
				WHERE ' . $prefix . 'id = ' . (int) $avatar_data['id'];
		$result = $this->db->sql_query_limit($sql, 1);

		$row = $this->manager->clean_row($this->db->sql_fetchrow($result), substr($prefix, 0, -1));
		$this->db->sql_freeresult($result);

		foreach ($expected as $key => $value)
		{
			$this->assertEquals($value, $row[$key]);
		}
	}

	/**
	 * @dependsOn test_handle_avatar_delete
	 */
	public function test_user_group_avatar_deleted()
	{
		$sql = 'SELECT * FROM ' . USERS_TABLE . '
				WHERE user_id = 3';
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->manager->clean_row($this->db->sql_fetchrow($result), 'user');
		$this->db->sql_freeresult($result);

		$this->assertEquals(array(
			'avatar'		=> '',
			'avatar_type'	=> '',
			'avatar_width'	=> 0,
			'avatar_height'	=> 0,
		), $row);
	}
}
