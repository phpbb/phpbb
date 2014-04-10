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
				new \phpbb\di\extension\core($phpbb_root_path . 'config'),
			);
			$container = phpbb_create_container($extensions, $phpbb_root_path, 'php');

			$this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerBuilder', $container);
		}

		public function test_phpbb_create_install_container()
		{
			$phpbb_root_path = __DIR__ . '/../../phpBB/';
			$extensions = array(
				new \phpbb\di\extension\config(__DIR__ . '/fixtures/config.php'),
				new \phpbb\di\extension\core($phpbb_root_path . 'config'),
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
				new \phpbb\di\extension\core($phpbb_root_path . 'config'),
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
		public function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false, $new_link = false)
		{
		}

		public function sql_query($query = '', $cache_ttl = 0)
		{
		}

		public function sql_fetchrow($query_id = false)
		{
		}

		public function sql_freeresult($query_id = false)
		{
		}

		function sql_server_info($raw = false, $use_cache = true)
		{
		}

		function sql_affectedrows()
		{
		}

		function sql_rowseek($rownum, &$query_id)
		{
		}

		function sql_nextid()
		{
		}

		function sql_escape($msg)
		{
		}

		function sql_like_expression($expression)
		{
		}
	}
}
