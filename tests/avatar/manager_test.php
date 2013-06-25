<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/driver/foobar.php';

class phpbb_avatar_manager_test extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		global $phpbb_root_path, $phpEx;

		// Mock phpbb_container
		$this->phpbb_container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
		$this->phpbb_container->expects($this->any())
			->method('get')
			->will($this->returnArgument(0));

		// Prepare dependencies for avatar manager and driver
		$config = new phpbb_config(array());
		$request = $this->getMock('phpbb_request');
		$cache = $this->getMock('phpbb_cache_driver_interface');

		// $this->avatar_foobar will be needed later on
		$this->avatar_foobar = $this->getMock('phpbb_avatar_driver_foobar', array('get_name'), array($config, $phpbb_root_path, $phpEx, $cache));
		$this->avatar_foobar->expects($this->any())
			->method('get_name')
			->will($this->returnValue('avatar.driver.foobar'));
		// barfoo driver can't be mocked with constructor arguments
		$this->avatar_barfoo = $this->getMock('phpbb_avatar_driver_barfoo', array('get_name'));
		$this->avatar_barfoo->expects($this->any())
			->method('get_name')
			->will($this->returnValue('avatar.driver.barfoo'));
		$avatar_drivers = array($this->avatar_foobar, $this->avatar_barfoo);

		foreach ($this->avatar_drivers() as $driver)
		{
			$cur_avatar = $this->getMock('phpbb_avatar_driver_' . $driver, array('get_name'), array($config, $phpbb_root_path, $phpEx, $cache));
			$cur_avatar->expects($this->any())
				->method('get_name')
				->will($this->returnValue('avatar.driver.' . $driver));
			$config['allow_avatar_' . get_class($cur_avatar)] = false;
			$avatar_drivers[] = $cur_avatar;
		}

		$config['allow_avatar_' . get_class($this->avatar_foobar)] = true;
		$config['allow_avatar_' . get_class($this->avatar_barfoo)] = false;

		// Set up avatar manager
		$this->manager = new phpbb_avatar_manager($config, $avatar_drivers, $this->phpbb_container);
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
			array('avatar.driver.foo_wrong', NULL),
			array('avatar.driver.foobar', 'avatar.driver.foobar'),
			array('avatar.driver.foo_wrong', NULL),
			array('avatar.driver.local', NULL),
			array(AVATAR_GALLERY, NULL),
			array(AVATAR_UPLOAD, NULL),
			array(AVATAR_REMOTE, NULL),
			array(AVATAR_GALLERY, NULL),
		);
	}

	/**
	* @dataProvider get_driver_data_enabled
	*/
	public function test_get_driver_enabled($driver_name, $expected)
	{
		$driver = $this->manager->get_driver($driver_name);
		$this->assertEquals($expected, $driver);
	}

	public function get_driver_data_all()
	{
		return array(
			array('avatar.driver.foobar', 'avatar.driver.foobar'),
			array('avatar.driver.foo_wrong', NULL),
			array('avatar.driver.foobar', 'avatar.driver.foobar'),
			array('avatar.driver.foo_wrong', NULL),
			array('avatar.driver.local', 'avatar.driver.local'),
			array(AVATAR_GALLERY, 'avatar.driver.local'),
			array(AVATAR_UPLOAD, 'avatar.driver.upload'),
			array(AVATAR_REMOTE, 'avatar.driver.remote'),
			array(AVATAR_GALLERY, 'avatar.driver.local'),
		);
	}

	/**
	* @dataProvider get_driver_data_all
	*/
	public function test_get_driver_all($driver_name, $expected)
	{
		$driver = $this->manager->get_driver($driver_name, false);
		$this->assertEquals($expected, $driver);
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
					'user_avatar'			=> '',
					'user_avatar_type'		=> '',
					'user_avatar_width'		=> '',
					'user_avatar_height'	=> '',
				),
				array(
					'avatar'			=> '',
					'avatar_type'		=> '',
					'avatar_width'		=> '',
					'avatar_height'		=> '',
				),
			),
			array(
				array(
					'group_avatar'			=> '',
					'group_avatar_type'		=> '',
					'group_avatar_width'	=> '',
					'group_avatar_height'	=> '',
				),
				array(
					'avatar'			=> '',
					'avatar_type'		=> '',
					'avatar_width'		=> '',
					'avatar_height'		=> '',
				),
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
					'foobar_avatar'			=> '',
					'foobar_avatar_type'	=> '',
					'foobar_avatar_width'	=> '',
					'foobar_avatar_height'	=> '',
				),
				array(
					'foobar_avatar'			=> '',
					'foobar_avatar_type'	=> '',
					'foobar_avatar_width'	=> '',
					'foobar_avatar_height'	=> '',
				),
			),
		);
	}

	/**
	* @dataProvider database_row_data
	*/
	public function test_clean_row(array $input, array $output)
	{
		$cleaned_row = array();

		$cleaned_row = phpbb_avatar_manager::clean_row($input);
		foreach ($output as $key => $null)
		{
			$this->assertArrayHasKey($key, $cleaned_row);
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
		$user = $this->getMock('phpbb_user');
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
