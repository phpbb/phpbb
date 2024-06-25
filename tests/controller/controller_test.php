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

include_once(__DIR__ . '/ext/vendor2/foo/controller.php');
include_once(__DIR__.'/phpbb/controller/foo.php');

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class phpbb_controller_controller_test extends phpbb_test_case
{
	/** @var phpbb_mock_extension_manager */
	protected $extension_manager;

	protected function setUp(): void
	{
		$this->extension_manager = new phpbb_mock_extension_manager(
			__DIR__ . '/',
			array(
				'vendor2/foo' => array(
					'ext_name' => 'vendor2/foo',
					'ext_active' => '1',
					'ext_path' => 'ext/vendor2/foo/',
				),
				'vendor2/bar' => array(
					'ext_name' => 'vendor2/bar',
					'ext_active' => '1',
					'ext_path' => 'ext/vendor2/bar/',
				),
			));
	}

	public function test_router_default_loader()
	{
		$container = new phpbb_mock_container_builder();
		$container->setParameter('core.environment', PHPBB_ENVIRONMENT);

		$loader = new \Symfony\Component\Routing\Loader\YamlFileLoader(
			new \phpbb\routing\file_locator(__DIR__ . '/')
		);
		$resources_locator = new \phpbb\routing\resources_locator\default_resources_locator(__DIR__ . '/', PHPBB_ENVIRONMENT, $this->extension_manager);
		$router = new phpbb_mock_router($container, $resources_locator, $loader, 'php', __DIR__ . '/', true, true);
		$routes = $router->get_routes();

		// This will need to be updated if any new routes are defined
		$this->assertInstanceOf('Symfony\Component\Routing\Route', $routes->get('core_controller'));
		$this->assertEquals('/core_foo', $routes->get('core_controller')->getPath());

		$this->assertInstanceOf('Symfony\Component\Routing\Route', $routes->get('controller1'));
		$this->assertEquals('/foo', $routes->get('controller1')->getPath());

		$this->assertInstanceOf('Symfony\Component\Routing\Route', $routes->get('controller2'));
		$this->assertEquals('/foo/bar', $routes->get('controller2')->getPath());

		$this->assertInstanceOf('Symfony\Component\Routing\Route', $routes->get('controller3'));
		$this->assertEquals('/bar', $routes->get('controller3')->getPath());

		$this->assertNull($routes->get('controller_noroute'));
	}

	protected function get_foo_container()
	{
		$container = new ContainerBuilder();
		// YamlFileLoader only uses one path at a time, so we need to loop
		// through all of the ones we are using.
		foreach (array(__DIR__.'/config', __DIR__ . '/ext/vendor2/foo/config') as $path)
		{
			$loader = new YamlFileLoader($container, new FileLocator($path));
			$loader->load('services.yml');
		}

		return $container;
	}

	public function test_controller_resolver()
	{
		$container = $this->get_foo_container();

		$resolver = new \phpbb\controller\resolver($container, __DIR__ . '/');
		$symfony_request = new Request();
		$symfony_request->attributes->set('_controller', 'foo.controller:handle');

		$this->assertEquals($resolver->getController($symfony_request), array(new foo\controller, 'handle'));
		$this->assertEquals(array('foo'), $resolver->getArguments($symfony_request, $resolver->getController($symfony_request)));

		$symfony_request = new Request();
		$symfony_request->attributes->set('_controller', 'core_foo.controller:bar');

		$this->assertEquals($resolver->getController($symfony_request), array(new phpbb\controller\foo, 'bar'));
		$this->assertEquals(array(), $resolver->getArguments($symfony_request, $resolver->getController($symfony_request)));
	}

	public function data_get_arguments()
	{
		return array(
			array(array(new foo\controller(), 'handle2'), array('foo', 0)),
			array(array(new foo\controller(), 'handle_fail'), array('default'), array('no_default' => 'default')),
			array(new foo\controller(), array(), array()),
			array(array(new foo\controller(), 'handle_fail'), array(), array(), '\phpbb\controller\exception', 'CONTROLLER_ARGUMENT_VALUE_MISSING'),
			array('', array(), array(), '\ReflectionException', 'Function () does not exist'),
			// Before PHP 8: 'Method __invoke does not exist'
			// As of PHP 8: 'Method phpbb\controller\foo::__invoke() does not exist'
			array(new phpbb\controller\foo, array(), array(), '\ReflectionException',
				'Method ' . (version_compare(PHP_VERSION, '8', '>=') ? 'phpbb\controller\foo::__invoke()' : '__invoke') . ' does not exist'),
		);
	}

	/**
	 * @dataProvider data_get_arguments
	 */
	public function test_get_arguments($input, $expected, $set_attributes = array(), $exception = '', $exception_message = '')
	{
		$container = $this->get_foo_container();

		$resolver = new \phpbb\controller\resolver($container, __DIR__ . '/');
		$symfony_request = new Request();

		foreach ($set_attributes as $name => $value)
		{
			$symfony_request->attributes->set($name, $value);
		}

		if (!empty($exception))
		{
			$this->expectException($exception);
			$this->expectExceptionMessage($exception_message);
		}

		$this->assertEquals($expected, $resolver->getArguments($symfony_request, $input));
	}
}
