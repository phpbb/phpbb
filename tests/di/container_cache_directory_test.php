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
	require_once __DIR__ . '/fixtures/manager_mock.php';

	use org\bovigo\vfs\vfsStream;

	class container_cache_directory_test extends \phpbb_test_case //phpbb_di_container_test
	{
		protected $config_php;

		protected $phpbb_root_path;

		public function setUp(): void
		{
			$this->phpbb_root_path = __DIR__ . '/';
			vfsStream::setup('phpbb', null, array(
				'cache' => array(
					'test' => array(),
					'override' => array(),
					'mock' => array(),
				),
			));
			$this->config_php = new \phpbb\config_php_file($this->phpbb_root_path . 'fixtures/', 'php');

			parent::setUp();
		}

		protected function tearDown(): void
		{
			unset($_SERVER['PHPBB____core__cache_dir']);

			parent::tearDown();
		}

		public function test_cache_directory_can_be_overridden()
		{
			$new_cache_directory = vfsStream::url('phpbb/cache/override') . '/';

			// This is how one overrides the cache directory.
			// The file cache driver will now write to a new directory.
			$_SERVER['PHPBB____core__cache_dir'] = $new_cache_directory;

			$builder = new phpbb_mock_phpbb_di_container_builder($this->phpbb_root_path . 'fixtures/', 'php');
			$builder->with_cache_dir(vfsStream::url('phpbb/cache/mock') . '/');
			$builder->with_config($this->config_php);
			$container = $builder->get_container();

			$this->assertEquals($container->getParameter('core.cache_dir'), $new_cache_directory);
		}

		/**
		 * By default autoload_xxx.php and container_xxx.php files
		 * will also be written to the default cache directory.
		 * This test demonstrates the default behavior.
		 */
		public function test_container_and_autoload_cache()
		{
			$default_cache_directory = vfsStream::url('phpbb/cache/test') . '/';
			unset($_SERVER['PHPBB____core__cache_dir']);

			// Use the normal container_builder
			$builder = new \phpbb\di\container_builder($this->phpbb_root_path . 'fixtures/', 'php');
			$builder->with_cache_dir($default_cache_directory);
			$builder->with_config($this->config_php);

			$container = $builder->get_container();

			$files_written_to_cache = $this->get_cache_files($default_cache_directory);

			$this->assertNotEmpty(preg_grep('/autoload_.+.php/', $files_written_to_cache), 'There should be an autoload file in the cache directory.');
			$this->assertNotEmpty(preg_grep('/container_.+.php/', $files_written_to_cache), 'There should be an container file in the cache directory.');
		}

		/**
		 * The desired behavior: When we have a custom cache directory
		 * the autoload and container cache files are also written to the custom cache directory.
		 */
		public function test_autoload_and_container_cache_are_written_to_overriden_cache_directory()
		{
			$new_cache_directory = vfsStream::url('phpbb/cache/override') . '/';

			$_SERVER['PHPBB____core__cache_dir'] = $new_cache_directory;

			// Use the normal container_builder
			$builder = new \phpbb\di\container_builder($this->phpbb_root_path . 'fixtures/', 'php');
			$builder->with_config($this->config_php);

			$container = $builder->get_container();

			$files_written_to_cache = $this->get_cache_files($new_cache_directory);

			$this->assertNotEmpty(preg_grep('/autoload_.+.php/', $files_written_to_cache), 'There should be an autoload file in the cache directory.');
			$this->assertNotEmpty(preg_grep('/container_.+.php/', $files_written_to_cache), 'There should be an container file in the cache directory.');
		}

		private function get_cache_files(string $cache_directory): array
		{
			return array_diff(scandir($cache_directory), array('.', '..'));
		}
	}
}
