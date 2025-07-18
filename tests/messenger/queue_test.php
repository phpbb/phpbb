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

use phpbb\config\config;
use phpbb\filesystem\exception\filesystem_exception;
use phpbb\messenger\queue;

class phpbb_messenger_queue_test extends phpbb_test_case
{
	protected $config;
	protected $cache_file;
	protected $dispatcher;
	protected $service_collection;

	/** @var queue */
	protected $messenger_queue;

	/**
	 * Set up the test case
	 */
	protected function setUp(): void
	{
		$this->config = new config([
				'last_queue_run'	=> time() - 30,
				'queue_interval'	=> 600,
			]);
		$this->dispatcher = $this->getMockBuilder('phpbb\event\dispatcher')
			->disableOriginalConstructor()
			->getMock();
		$this->service_collection = $this->getMockBuilder('phpbb\di\service_collection')
			->disableOriginalConstructor()
			->onlyMethods(['getIterator', 'add_service_class'])
			->getMock();

		$this->cache_file = __DIR__ . '/../tmp/queue_test';

		if (file_exists($this->cache_file))
		{
			@unlink($this->cache_file);
		}

		$this->messenger_queue = $this->getMockBuilder('phpbb\messenger\queue')
			->setConstructorArgs([
				$this->config,
				$this->dispatcher,
				$this->service_collection,
				$this->cache_file
			])
			->addMethods(['get_data'])
			->getMock();

		$this->messenger_queue->method('get_data')
			->willReturnCallback(function(){
				$data_reflection = new \ReflectionProperty(queue::class, 'data');
				return $data_reflection->getValue($this->messenger_queue);
			});
	}

	public function test_init()
	{
		$this->messenger_queue->init('email', 5);
		$this->assertEquals([
			'email' => [
				'package_size' => 5,
				'data' => [],
			]
		], $this->messenger_queue->get_data());

		$this->messenger_queue->init('jabber', 9);

		$this->assertEquals([
			'email' => [
				'package_size' => 5,
				'data' => [],
			],
			'jabber' => [
				'package_size' => 9,
				'data' => [],
			]
		], $this->messenger_queue->get_data());
	}

	public function test_put()
	{
		$this->messenger_queue->init('email', 5);
		$this->assertEquals([
			'email' => [
				'package_size' => 5,
				'data' => [],
			]
		], $this->messenger_queue->get_data());

		$this->messenger_queue->put('email', ['data1']);

		$this->assertEquals([
			'email' => [
				'package_size' => 5,
				'data' => [
					['data1'],
				],
			],
		], $this->messenger_queue->get_data());
	}

	public function test_process_no_cache_file()
	{
		$this->assertFileDoesNotExist($this->cache_file);
		$this->assertGreaterThan(5, time() - $this->config['last_queue_run']);
		$this->messenger_queue->init('email', 5);
		$this->assertEquals([
			'email' => [
				'package_size' => 5,
				'data' => [],
			]
		], $this->messenger_queue->get_data());

		$this->messenger_queue->process();
		$this->assertFileDoesNotExist($this->cache_file);
		$this->assertLessThan(5, time() - $this->config['last_queue_run']);
	}

	public function test_process_no_queue_handling()
	{
		// First save queue data
		$this->assertFileDoesNotExist($this->cache_file);
		$this->messenger_queue->init('email', 5);
		$this->messenger_queue->init('jabber', 10);
		$this->assertEquals([
			'email' => [
				'package_size' => 5,
				'data' => [],
			],
			'jabber' => [
				'package_size' => 10,
				'data' => [],
			]
		], $this->messenger_queue->get_data());

		$this->messenger_queue->put('email', ['data1']);
		$this->messenger_queue->put('jabber', ['data2']);
		$this->messenger_queue->save();
		$this->assertFileExists($this->cache_file);
		$this->assertEquals([], $this->messenger_queue->get_data());

		$this->config['last_queue_run'] = time() - 1000;

		// Process the queue
		$this->messenger_queue->process();
	}

	public function test_process_no_queue_handling_chmod_exception()
	{
		// First save queue data
		$this->assertFileDoesNotExist($this->cache_file);
		$this->messenger_queue->init('email', 5);
		$this->messenger_queue->init('jabber', 10);
		$this->assertEquals([
			'email' => [
				'package_size' => 5,
				'data' => [],
			],
			'jabber' => [
				'package_size' => 10,
				'data' => [],
			]
		], $this->messenger_queue->get_data());

		$this->messenger_queue->put('email', ['data1']);
		$this->messenger_queue->put('jabber', ['data2']);
		$this->messenger_queue->save();
		$this->assertFileExists($this->cache_file);
		$this->assertEquals([], $this->messenger_queue->get_data());

		$this->config['last_queue_run'] = time() - 1000;

		// Override the filesystem to simulate a chmod failure
		$filesystem = $this->getMockBuilder('phpbb\filesystem\filesystem')
			->disableOriginalConstructor()
			->onlyMethods(['phpbb_chmod'])
			->getMock();
		$filesystem->method('phpbb_chmod')
			->will($this->throwException(new filesystem_exception('Chmod failed')));
		$filesystem_reflection = new \ReflectionProperty(queue::class, 'filesystem');
		$filesystem_reflection->setAccessible(true);
		$filesystem_reflection->setValue($this->messenger_queue, $filesystem);

		// Process the queue
		$this->messenger_queue->process();
	}

	public function test_process_complete()
	{
		// First save queue data
		$this->assertFileDoesNotExist($this->cache_file);
		$this->messenger_queue->init('email', 5);
		$this->assertEquals([
			'email' => [
				'package_size' => 5,
				'data' => [],
			],
		], $this->messenger_queue->get_data());

		$this->messenger_queue->put('email', ['data1']);
		$this->messenger_queue->save();
		$this->assertFileExists($this->cache_file);
		$this->assertEquals([], $this->messenger_queue->get_data());

		$this->config['last_queue_run'] = time() - 1000;

		// Prepare service iterator and messenger methods
		$email_method = $this->getMockBuilder('phpbb\messenger\method\email')
			->disableOriginalConstructor()
			->onlyMethods(['get_queue_object_name', 'process_queue'])
			->getMock();
		$email_method->method('get_queue_object_name')
			->willReturn('email');
		$email_method->method('process_queue')
			->willReturnCallback(function(array &$queue_data) {
				$this->assertEquals([
					'package_size' => 5,
					'data' => [
						['data1'],
					],
				], $queue_data['email']);
				unset($queue_data['email']);
			});

		$this->service_collection->method('getIterator')
			->willReturn(new \ArrayIterator([
				'email' => $email_method,
			]));

		// Process the queue
		$this->messenger_queue->process();
		$this->assertFileDoesNotExist($this->cache_file);
	}

	public function test_save_no_data()
	{
		$this->assertFileDoesNotExist($this->cache_file);
		$this->messenger_queue->save();
		$this->assertFileDoesNotExist($this->cache_file);
	}

	public function test_save()
	{
		$this->assertFileDoesNotExist($this->cache_file);
		$this->messenger_queue->init('email', 5);
		$this->assertEquals([
			'email' => [
				'package_size' => 5,
				'data' => [],
			]
		], $this->messenger_queue->get_data());

		$this->messenger_queue->put('email', ['data1']);
		$this->messenger_queue->save();
		$this->assertFileExists($this->cache_file);
		$this->assertEquals([], $this->messenger_queue->get_data());
	}

	public function test_save_twice()
	{
		$this->assertFileDoesNotExist($this->cache_file);
		$this->messenger_queue->init('email', 5);
		$this->assertEquals([
			'email' => [
				'package_size' => 5,
				'data' => [],
			]
		], $this->messenger_queue->get_data());

		$this->messenger_queue->put('email', ['data1']);
		$this->messenger_queue->put('jabber', ['data2']);
		$this->messenger_queue->save();
		$this->assertFileExists($this->cache_file);
		$this->assertEquals([], $this->messenger_queue->get_data());

		$this->messenger_queue->put('email', ['data3']);
		$this->messenger_queue->save();
		$this->assertEquals([], $this->messenger_queue->get_data());
	}

	public function test_save_chmod_fail()
	{
		$this->assertFileDoesNotExist($this->cache_file);
		$this->messenger_queue->init('email', 5);
		$this->assertEquals([
			'email' => [
				'package_size' => 5,
				'data' => [],
			]
		], $this->messenger_queue->get_data());

		$this->messenger_queue->put('email', ['data1']);

		// Override the filesystem to simulate a chmod failure
		$filesystem = $this->getMockBuilder('phpbb\filesystem\filesystem')
			->disableOriginalConstructor()
			->onlyMethods(['phpbb_chmod'])
			->getMock();
		$filesystem->method('phpbb_chmod')
			->will($this->throwException(new filesystem_exception('Chmod failed')));
		$filesystem_reflection = new \ReflectionProperty(queue::class, 'filesystem');
		$filesystem_reflection->setAccessible(true);
		$filesystem_reflection->setValue($this->messenger_queue, $filesystem);

		$this->messenger_queue->save();
		$this->assertFileExists($this->cache_file);
		$this->assertEquals([], $this->messenger_queue->get_data());
	}
}
