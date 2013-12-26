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

class phpbb_controller_controller_test extends phpbb_test_case
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
		$provider = new \phpbb\controller\provider;
		$routes = $provider
			->import_paths_from_finder($this->extension_manager->get_finder())
			->find(__DIR__);

		// This will need to be updated if any new routes are defined
		$this->assertInstanceOf('Symfony\Component\Routing\Route', $routes->get('core_controller'));
		$this->assertEquals('/core_foo', $routes->get('core_controller')->getPath());

		$this->assertInstanceOf('Symfony\Component\Routing\Route', $routes->get('controller1'));
		$this->assertEquals('/foo', $routes->get('controller1')->getPath());

		$this->assertInstanceOf('Symfony\Component\Routing\Route', $routes->get('controller2'));
		$this->assertEquals('/foo/bar', $routes->get('controller2')->getPath());
	}

	public function test_controller_resolver()
	{
		$container = new ContainerBuilder();
		// YamlFileLoader only uses one path at a time, so we need to loop
		// through all of the ones we are using.
		foreach (array(__DIR__.'/config', __DIR__.'/ext/foo/config') as $path)
		{
			$loader = new YamlFileLoader($container, new FileLocator($path));
			$loader->load('services.yml');
		}

		// Autoloading classes within the tests folder does not work
		// so I'll include them manually.
		if (!class_exists('foo\\controller'))
		{
			include(__DIR__.'/ext/foo/controller.php');
		}
		if (!class_exists('phpbb\\controller\\foo'))
		{
			include(__DIR__.'/phpbb/controller/foo.php');
		}

		$resolver = new \phpbb\controller\resolver(new \phpbb\user, $container);
		$symfony_request = new Request();
		$symfony_request->attributes->set('_controller', 'foo.controller:handle');

		$this->assertEquals($resolver->getController($symfony_request), array(new foo\controller, 'handle'));

		$symfony_request = new Request();
		$symfony_request->attributes->set('_controller', 'core_foo.controller:bar');

		$this->assertEquals($resolver->getController($symfony_request), array(new phpbb\controller\foo, 'bar'));
	}
}
