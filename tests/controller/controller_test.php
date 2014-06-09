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

use Symfony\Component\HttpFoundation\Request;
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
				'vendor2/foo' => array(
					'ext_name' => 'vendor2/foo',
					'ext_active' => '1',
					'ext_path' => 'ext/vendor2/foo/',
				),
			));
	}

	public function test_provider()
	{
		$provider = new \phpbb\controller\provider();
		$provider->find_routing_files($this->extension_manager->get_finder());
		$routes = $provider->find(__DIR__)->get_routes();

		// This will need to be updated if any new routes are defined
		$this->assertInstanceOf('Symfony\Component\Routing\Route', $routes->get('core_controller'));
		$this->assertEquals('/core_foo', $routes->get('core_controller')->getPath());

		$this->assertInstanceOf('Symfony\Component\Routing\Route', $routes->get('controller1'));
		$this->assertEquals('/foo', $routes->get('controller1')->getPath());

		$this->assertInstanceOf('Symfony\Component\Routing\Route', $routes->get('controller2'));
		$this->assertEquals('/foo/bar', $routes->get('controller2')->getPath());

		$this->assertNull($routes->get('controller_noroute'));
	}

	public function test_controller_resolver()
	{
		$container = new ContainerBuilder();
		// YamlFileLoader only uses one path at a time, so we need to loop
		// through all of the ones we are using.
		foreach (array(__DIR__.'/config', __DIR__ . '/ext/vendor2/foo/config') as $path)
		{
			$loader = new YamlFileLoader($container, new FileLocator($path));
			$loader->load('services.yml');
		}

		// Autoloading classes within the tests folder does not work
		// so I'll include them manually.
		if (!class_exists('vendor2\\foo\\controller'))
		{
			include(__DIR__ . '/ext/vendor2/foo/controller.php');
		}
		if (!class_exists('phpbb\\controller\\foo'))
		{
			include(__DIR__.'/phpbb/controller/foo.php');
		}

		$resolver = new \phpbb\controller\resolver(new \phpbb\user, $container, dirname(__FILE__) . '/');
		$symfony_request = new Request();
		$symfony_request->attributes->set('_controller', 'foo.controller:handle');

		$this->assertEquals($resolver->getController($symfony_request), array(new foo\controller, 'handle'));

		$symfony_request = new Request();
		$symfony_request->attributes->set('_controller', 'core_foo.controller:bar');

		$this->assertEquals($resolver->getController($symfony_request), array(new phpbb\controller\foo, 'bar'));
	}
}
