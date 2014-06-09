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

require_once dirname(__FILE__) . '/driver/foobar.php';

class phpbb_avatar_manager_test extends \phpbb_test_case
{
	/** @var \phpbb\avatar\manager */
	protected $manager;
	protected $avatar_foobar;
	protected $avatar_barfoo;

	public function setUp()
	{
		global $phpbb_root_path, $phpEx;

		// Mock phpbb_container
		$phpbb_container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
		$phpbb_container->expects($this->any())
			->method('get')
			->will($this->returnArgument(0));

		// Prepare dependencies for avatar manager and driver
		$config = new \phpbb\config\config(array());
		$cache = $this->getMock('\phpbb\cache\driver\driver_interface');
		$path_helper =  new \phpbb\path_helper(
			new \phpbb\symfony_request(
				new phpbb_mock_request()
			),
			new \phpbb\filesystem(),
			$phpbb_root_path,
			$phpEx
		);

		// $this->avatar_foobar will be needed later on
		$this->avatar_foobar = $this->getMock('\phpbb\avatar\driver\foobar', array('get_name'), array($config, $phpbb_root_path, $phpEx, $path_helper, $cache));
		$this->avatar_foobar->expects($this->any())
			->method('get_name')
			->will($this->returnValue('avatar.driver.foobar'));
		// barfoo driver can't be mocked with constructor arguments
		$this->avatar_barfoo = $this->getMock('\phpbb\avatar\driver\barfoo', array('get_name'));
		$this->avatar_barfoo->expects($this->any())
			->method('get_name')
			->will($this->returnValue('avatar.driver.barfoo'));
		$avatar_drivers = array($this->avatar_foobar, $this->avatar_barfoo);

		foreach ($this->avatar_drivers() as $driver)
		{
			$cur_avatar = $this->getMock('\phpbb\avatar\driver\\' . $driver, array('get_name'), array($config, $phpbb_root_path, $phpEx, $path_helper, $cache));
			$cur_avatar->expects($this->any())
				->method('get_name')
				->will($this->returnValue('avatar.driver.' . $driver));
			$config['allow_avatar_' . get_class($cur_avatar)] = false;
			$avatar_drivers[] = $cur_avatar;
		}

		$config['allow_avatar_' . get_class($this->avatar_foobar)] = true;
		$config['allow_avatar_' . get_class($this->avatar_barfoo)] = false;

		// Set up avatar manager
		$this->manager = new \phpbb\avatar\manager($config, $avatar_drivers, $phpbb_container);
	}

	protected function avatar_drivers()
	{
		return array(
			'local',
			'upload',
			'remote',
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
			'avatar.driver.remote' => 'avatar.driver.remote',
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
			array('avatar.driver.foo_wrong', null),
			array('avatar.driver.local', null),
			array(AVATAR_GALLERY, null),
			array(AVATAR_UPLOAD, null),
			array(AVATAR_REMOTE, null),
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
			array(AVATAR_REMOTE, 'avatar.driver.remote'),
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

		$expected_settings = array(
			'allow_avatar_' . get_class($this->avatar_foobar)	=> array('lang' => 'ALLOW_' . strtoupper(get_class($this->avatar_foobar)), 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => false),
		);

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
					'avatar_width'		=> '',
					'avatar_height'		=> '',
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
		$user = $this->getMock('\phpbb\user');
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
}
