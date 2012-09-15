<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

use Symfony\Component\HttpFoundation\Request;

class phpbb_controller_test extends phpbb_test_case
{
	public function setUp()
	{
		$this->extension_manager = new phpbb_mock_extension_manager(
			dirname(__FILE__) . '/',
			array(
				'foo' => array(
					'ext_name' => 'foo',
					'ext_active' => '1',
					'ext_path' => 'ext/foo/',
				),
			));
		$this->route_provider = new phpbb_controller_route_provider($this->extension_manager->get_finder());
	}

	public function test_route_provider()
	{
		$route_files = $this->route_provider->find();

		$this->assertEquals(array(
			'ext/foo/config/routing.yml'
		), $route_files);
	}

	public function test_controller_provider()
	{
		$provider = new phpbb_controller_provider($this->route_provider->find());
		$this->assertEquals($provider->find('./tests/controller/'), array(
			'controller1'	 => array(
				'service'	=> 'foo.controller',
			),
		));
	}

	public function test_controller_resolver()
	{
		$resolver = new phpbb_controller_resolver($this->route_provider, new phpbb_mock_cache, new phpbb_user, './tests/controller/');
		$symfony_request = new Request(array('controller' => 'controller1'));
		
		$this->assertEquals($resolver->getController($symfony_request), array(
			'service'	=> 'foo.controller',
		));
	}
}
