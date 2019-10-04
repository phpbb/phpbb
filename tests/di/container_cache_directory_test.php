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
	class container_cache_directory_test extends \phpbb_test_case //phpbb_di_container_test
	{
		protected $config_php;

		/**
		* @var \phpbb\di\container_builder
		*/
		protected $builder;
		protected $phpbb_root_path;
		protected $filename;

		public function setUp(): void
		{
			$this->phpbb_root_path = dirname(__FILE__) . '/';
			$this->config_php = new \phpbb\config_php_file($this->phpbb_root_path . 'fixtures/', 'php');
			$this->builder = new phpbb_mock_phpbb_di_container_builder($this->phpbb_root_path . 'fixtures/', 'php');
			$this->builder->with_config($this->config_php);

			$this->filename = $this->phpbb_root_path . '../tmp/container.php';
			if (is_file($this->filename))
			{
				unlink($this->filename);
			}

			parent::setUp();
		}


		public function test_cache_directory_can_be_overridden()
		{
			$newCacheDirectory = $this->phpbb_root_path . "fixtures/overwrite-cache-directory/test/";

			// This is how one overrides the cache directory.
			// The file cache driver will now write to a new directory.
			$_SERVER['PHPBB____core__cache_dir'] = $newCacheDirectory;

			$container = $this->builder->get_container();

			$this->assertEquals($container->getParameter('core.cache_dir'), $newCacheDirectory);
		}

		/**
		 * By default autoload_xxx.php and container_xxx.php files
		 * will also be written to the default cache directory.
		 * This test demonstrates the default behavior.
		 */
		public function test_container_and_autoload_cache()
		{
			$defaultCacheDirectory = $this->phpbb_root_path . "fixtures/cache/test";

			// Make sure our test directory will be empty.
			if (is_dir($defaultCacheDirectory))
			{
				array_map('unlink', glob($defaultCacheDirectory."/*"));
			} else {
				mkdir($defaultCacheDirectory, 0777, true);
			}

			// Use the normal container_builder
			$builder = new \phpbb\di\container_builder($this->phpbb_root_path . "fixtures/", "php");
			$builder->with_config($this->config_php);

			$container = $builder->get_container();

			$filesWrittenToCache = array_map('basename', glob($defaultCacheDirectory."/*"));

			$this->assertNotEmpty(preg_grep("/autoload_.+.php/", $filesWrittenToCache), "There should be an autoload file in the cache directory.");
			$this->assertNotEmpty(preg_grep("/container_.+.php/", $filesWrittenToCache), "There should be an container file in the cache directory.");

			// Cleanup the cache directory to prevent class redeclaration errors.
			array_map('unlink', glob($defaultCacheDirectory."/*"));
		}


		/**
		 * The desired behavior: When we have a custom cache directory
		 * the autoload and container cache files are also written to the custom cache directory.
		 */
		public function test_autoload_and_container_cache_are_written_to_overriden_cache_directory()
		{
			$newCacheDirectory = $this->phpbb_root_path . "fixtures/overwrite-cache-directory/test/";

			$_SERVER['PHPBB____core__cache_dir'] = $newCacheDirectory;

			// Make sure our test directory will be empty.
			if (is_dir($newCacheDirectory)) {
				array_map('unlink', glob($newCacheDirectory."/*"));
			} else {
				mkdir($newCacheDirectory, 0777, true);
			}

			// Use the normal container_builder
			$builder = new \phpbb\di\container_builder($this->phpbb_root_path . "fixtures/", "php");
			$builder->with_config($this->config_php);

			$container = $builder->get_container();

			$filesWrittenToCache = array_map('basename', glob($newCacheDirectory."/*"));

			$this->assertNotEmpty(preg_grep("/autoload_.+.php/", $filesWrittenToCache), "There should be an autoload file in the cache directory.");
			$this->assertNotEmpty(preg_grep("/container_.+.php/", $filesWrittenToCache), "There should be an container file in the cache directory.");

			array_map('unlink', glob($newCacheDirectory."/*"));

		}
	}
}
