<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_controller_test extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->extension_manager = new phpbb_mock_extension_manager(
			dirname(__FILE__) . '/',
			array(
				'bar' => array(
					'ext_name'      => 'bar',
					'ext_active'    => true,
					'ext_path'      => 'ext/bar/'
				),
			));
		$this->cache = new phpbb_mock_cache;
		$this->user = $this->getMock('phpbb_user');

		$this->controller_manager = new phpbb_mock_controller_manager($this->cache, $this->user);
		$this->controller_manager->get_controllers_map(array('phpbb_mock_test_controller'));
	}

	public function test_handle_controller()
	{
		$this->controller_manager->get_controller('foo');
		$this->assertEquals(true, phpbb_mock_controller_manager::$handled);
	}
}
