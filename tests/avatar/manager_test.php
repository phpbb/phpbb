<?php
/**
*
* @package testing
* @version $Id$
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_avatar_test extends PHPUnit_Framework_TestCase
{
	protected $avatar_list = array(
		'avatar.driver.gravatar',
		'avatar.driver.local',
		'avatar.driver.remote',
		'avatar.driver.upload',
	);

	protected $dirty_user_row = array(
		'user_avatar' => 'foobar',
		'user_avatar_width' => 50,
		'user_avatar_height' => 50,
	);

	protected $clean_user_row = array(
		'avatar' => 'foobar',
		'avatar_width' => 50,
		'avatar_height' => 50,
	);

	public function setUp()
	{
		global $phpbb_root_path, $phpEx;

		// Mock phpbb_container
		$this->phpbb_container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
		$this->phpbb_container->expects($this->any())
			->method('get')
			->with('avatar.driver.gravatar')->will($this->returnValue('avatar_foo'));

		// Prepare dependencies for avatar manager and driver
		$config = new phpbb_config(array());
		$request = $this->getMock('phpbb_request');
		$cache = $this->getMock('phpbb_cache_driver_interface');

		// Create new avatar driver object for manager
		$this->avatar_gravatar = new phpbb_avatar_driver_gravatar($config, $request, $phpbb_root_path, $phpEx, $cache);
		$this->avatar_gravatar->set_name('avatar.driver.gravatar');
		$avatar_drivers = array($this->avatar_gravatar);

		// Set up avatar manager
		$this->manager = new phpbb_avatar_manager($config, $avatar_drivers, $this->phpbb_container);
	}

	public function test_get_driver()
	{
		$driver = $this->manager->get_driver('avatar.driver.gravatar', true);
		$this->assertEquals('avatar_foo', $driver);

		$driver = $this->manager->get_driver('avatar.driver.foo', true);
		$this->assertNull($driver);
	}

	public function test_get_valid_drivers()
	{
		$valid_drivers = $this->manager->get_valid_drivers(true);
		$this->assertArrayHasKey('avatar.driver.gravatar', $valid_drivers);
		$this->assertEquals('avatar.driver.gravatar', $valid_drivers['avatar.driver.gravatar']);
	}

	public function test_get_avatar_settings()
	{
		$avatar_settings = $this->manager->get_avatar_settings($this->avatar_gravatar);

		$expected_settings = array(
			'allow_avatar_gravatar'	=> array('lang' => 'ALLOW_GRAVATAR', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => false),
		);

		$this->assertEquals($expected_settings, $avatar_settings);
	}
}
