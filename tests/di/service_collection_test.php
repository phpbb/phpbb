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

namespace
{
	class phpbb_service_collection_test extends \phpbb_test_case
	{
		/**
		 * @var \phpbb\di\service_collection
		 */
		protected $service_collection;

		public function setUp()
		{
			// Set up service container
			$phpbb_root_path = dirname(__FILE__) . '/';

			$filename = $phpbb_root_path . '../tmp/container.php';
			if (is_file($filename))
			{
				unlink($filename);
			}

			$builder = new phpbb_mock_phpbb_di_container_builder($phpbb_root_path . 'fixtures/', 'php');
			$builder->with_config_path($phpbb_root_path . 'fixtures/collection_config/');
			$builder->without_cache();
			$container = $builder->get_container();
			$this->service_collection = $container->get('service_collection');

			parent::setUp();
		}

		public function test_service_collection_get_service_names()
		{
			global $service_initialized;
			$service_initialized = false;

			$service_names = $this->service_collection->get_service_names();
			$this->assertTrue(in_array('collection_service1', $service_names));
			$this->assertTrue(in_array('collection_service2', $service_names));

			$this->assertFalse($service_initialized);
		}

		public function test_service_collection()
		{
			$service1_found = $service2_found = false;
			foreach ($this->service_collection as $name => $instance)
			{
				if ($instance instanceof \test_collection_service\collection_test1 && !$instance instanceof \test_collection_service\collection_test2)
				{
					$service1_found = true;
				}

				if ($instance instanceof \test_collection_service\collection_test2)
				{
					$service2_found = true;
				}
			}

			$this->assertTrue($service1_found);
			$this->assertTrue($service2_found);
		}
	}
}

namespace test_collection_service
{
	class collection_test1
	{
		public function __construct()
		{
			global $service_initialized;
			$service_initialized = true;
		}
	}

	class collection_test2 extends collection_test1
	{

	}
}
