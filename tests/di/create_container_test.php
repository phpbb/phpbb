<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace
{
	require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
	require_once dirname(__FILE__) . '/../../phpBB/includes/functions_container.php';

	class phpbb_di_container_test extends phpbb_test_case
	{
		public function test_phpbb_create_container()
		{
			$phpbb_root_path = __DIR__ . '/../../phpBB/';
			$extensions = array(
				new \phpbb\di\extension\config(__DIR__ . '/fixtures/config.php'),
				new \phpbb\di\extension\core($phpbb_root_path),
			);
			$container = phpbb_create_container($extensions, $phpbb_root_path, 'php');

			$this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerBuilder', $container);
		}

		public function test_phpbb_create_install_container()
		{
			$phpbb_root_path = __DIR__ . '/../../phpBB/';
			$extensions = array(
				new \phpbb\di\extension\config(__DIR__ . '/fixtures/config.php'),
				new \phpbb\di\extension\core($phpbb_root_path),
			);
			$container = phpbb_create_install_container($phpbb_root_path, 'php');

			$this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerBuilder', $container);
			$this->assertTrue($container->isFrozen());
		}

		public function test_phpbb_create_compiled_container()
		{
			$phpbb_root_path = __DIR__ . '/../../phpBB/';
			$config_file = __DIR__ . '/fixtures/config.php';
			$extensions = array(
				new \phpbb\di\extension\config(__DIR__ . '/fixtures/config.php'),
				new \phpbb\di\extension\core($phpbb_root_path),
			);
			$container = phpbb_create_compiled_container($config_file, $extensions, array(), $phpbb_root_path, 'php');

			$this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerBuilder', $container);
			$this->assertTrue($container->isFrozen());
		}
	}
}

namespace phpbb\db\driver
{
	class container_mock extends \phpbb\db\driver\driver
	{
		public function sql_connect()
		{
		}

		public function sql_query()
		{
		}

		public function sql_fetchrow()
		{
		}

		public function sql_freeresult()
		{
		}
	}
}
