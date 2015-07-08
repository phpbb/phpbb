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

class phpbb_installer_navigation_provider_test extends phpbb_test_case
{
	public function test_navigation()
	{
		// Mock nav interface
		$nav_stub = $this->getMockBuilder('\phpbb\install\helper\navigation\navigation_interface')
			->getMock();
		$nav_stub->method('get')
			->willReturn(array('foo' => 'bar'));

		// Set up dependencies
		$container = new phpbb_mock_container_builder();
		$container->set('foo', $nav_stub);
		$nav_collection = new \phpbb\di\service_collection($container);
		$nav_collection->add('foo');

		// Let's test
		$nav_provider = new \phpbb\install\helper\navigation\navigation_provider($nav_collection);
		$this->assertEquals(array('foo' => 'bar'), $nav_provider->get());
	}
}
