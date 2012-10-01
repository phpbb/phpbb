<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

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
	}

	public function test_provider()
	{
		$provider = new phpbb_controller_provider;
		$routes = $provider
			->get_paths($this->extension_manager->get_finder())
			->find('./tests/controller/');

		// This will need to be updated if any new routes are defined
		$this->assertEquals(2, count($routes));
	}

	public function test_controller_resolver()
	{
		$resolver = new phpbb_controller_resolver(new phpbb_user);
		$symfony_request = new Request(array(), array(), array('_controller' => 'foo.controller'));

		$this->assertEquals($resolver->getController($symfony_request), array('foo.controller', 'handle'));

		$symfony_request = new Request(array(), array(), array('_controller' => 'core_foo.controller:bar'));

		$this->assertEquals($resolver->getController($symfony_request), array('core_foo.controller', 'bar'));
	}
}
