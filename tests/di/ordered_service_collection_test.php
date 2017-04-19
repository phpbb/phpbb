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

class phpbb_ordered_service_collection_test extends \phpbb_test_case
{
	/**
	 * @var \phpbb\di\ordered_service_collection
	 */
	protected $service_collection;

	public function setUp()
	{
		$container = new phpbb_mock_container_builder();
		$container->set('foo', new StdClass);
		$container->set('bar', new StdClass);
		$container->set('foobar', new StdClass);
		$container->set('barfoo', new StdClass);

		$this->service_collection = new \phpbb\di\ordered_service_collection($container);
		$this->service_collection->add('foo', 7);
		$this->service_collection->add('bar', 3);
		$this->service_collection->add('barfoo', 5);
		$this->service_collection->add('foobar', 2);

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

		$this->assertSame(array('foobar', 'bar', 'barfoo', 'foo'), $service_names);
	}
}
