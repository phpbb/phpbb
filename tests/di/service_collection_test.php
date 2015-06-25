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

class phpbb_service_collection_test extends \phpbb_test_case
{
	/**
	 * @var \phpbb\di\service_collection
	 */
	protected $service_collection;

	public function setUp()
	{
		$container = new phpbb_mock_container_builder();
		$container->set('foo', new StdClass);
		$container->set('bar', new StdClass);

		$this->service_collection = new \phpbb\di\service_collection($container);
		$this->service_collection->add('foo');
		$this->service_collection->add('bar');

		parent::setUp();
	}

	public function test_service_collection()
	{
		$service_names = array();

		// Test the iterator
		foreach ($this->service_collection as $name => $service)
		{
			$service_names[] = $name;
			$this->assertInstanceOf('StdClass', $service);
		}

		$this->assertSame(array('foo', 'bar'), $service_names);
	}
}
