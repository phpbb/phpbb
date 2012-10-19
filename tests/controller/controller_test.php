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
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

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
		$container = new ContainerBuilder();
		// For some reason, I cannot get it to load more than one services
		// file at a time, even when givein multiple paths
		// So instead, I am looping through all of the paths
		foreach (array(__DIR__.'/config', __DIR__.'/ext/foo/config') as $path)
		{
			$loader = new YamlFileLoader($container, new FileLocator($path));
			$loader->load('services.yml');
		}

		// Autoloading classes within the tests folder does not work
		// so I'll include them manually
		if (!class_exists('phpbb_ext_foo_controller'))
		{
			include(__DIR__.'/ext/foo/controller.php');
		}
		if (!class_exists('phpbb_controller_foo'))
		{
			include(__DIR__.'/includes/controller/foo.php');
		}

		$resolver = new phpbb_controller_resolver(new phpbb_user, $container);
		$symfony_request = new Request(array(), array(), array('_controller' => 'foo.controller:handle'));

		$this->assertEquals($resolver->getController($symfony_request), array(new phpbb_ext_foo_controller, 'handle'));

		$symfony_request = new Request(array(), array(), array('_controller' => 'core_foo.controller:bar'));

		$this->assertEquals($resolver->getController($symfony_request), array(new phpbb_controller_foo, 'bar'));
	}
}
