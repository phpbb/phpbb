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
	require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

	class phpbb_di_container_test extends \phpbb_test_case
	{
		protected $config_php;

		/**
		* @var \phpbb\di\container_builder
		*/
		protected $builder;
		protected $phpbb_root_path;
		protected $filename;

		public function setUp()
		{
			$this->phpbb_root_path = dirname(__FILE__) . '/';
			$this->config_php = new \phpbb\config_php_file($this->phpbb_root_path . 'fixtures/', 'php');
			$this->builder = new phpbb_mock_phpbb_di_container_builder($this->config_php, $this->phpbb_root_path . 'fixtures/', 'php');

			$this->filename = $this->phpbb_root_path . '../tmp/container.php';
			if (is_file($this->filename))
			{
				unlink($this->filename);
			}

			parent::setUp();
		}

		public function test_default_container()
		{
			$container = $this->builder->get_container();
			$this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerBuilder', $container);

			// Checks the core services
			$this->assertTrue($container->hasParameter('core'));

			// Checks compile_container
			$this->assertTrue($container->isFrozen());

			// Checks inject_config
			$this->assertTrue($container->hasParameter('dbal.dbhost'));

			// Checks use_extensions
			$this->assertTrue($container->hasParameter('enabled'));
			$this->assertFalse($container->hasParameter('disabled'));
			$this->assertFalse($container->hasParameter('available'));

			// Checks set_custom_parameters
			$this->assertTrue($container->hasParameter('core.root_path'));

			// Checks dump_container
			$this->assertTrue(is_file($this->filename));

			// Checks the construction of a dumped container
			$container = $this->builder->get_container();
			$this->assertInstanceOf('phpbb_cache_container', $container);
			$this->assertFalse($container->isFrozen());
			$container->getParameterBag(); // needed, otherwise the container is not marked as frozen
			$this->assertTrue($container->isFrozen());
		}

		public function test_dump_container()
		{
			$this->builder->set_dump_container(false);
			$container = $this->builder->get_container();
			$this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerBuilder', $container);

			// Checks dump_container
			$this->assertFalse(is_file($this->filename));

			// Checks the construction of a dumped container
			$container = $this->builder->get_container();
			$this->assertNotInstanceOf('phpbb_cache_container', $container);
			$this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerBuilder', $container);
			$this->assertTrue($container->isFrozen());
		}

		public function test_use_extensions()
		{
			$this->builder->set_use_extensions(false);
			$container = $this->builder->get_container();
			$this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerBuilder', $container);

			// Checks the core services
			$this->assertTrue($container->hasParameter('core'));

			// Checks use_extensions
			$this->assertFalse($container->hasParameter('enabled'));
			$this->assertFalse($container->hasParameter('disabled'));
			$this->assertFalse($container->hasParameter('available'));
		}

		public function test_compile_container()
		{
			$this->builder->set_compile_container(false);
			$container = $this->builder->get_container();
			$this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerBuilder', $container);

			// Checks compile_container
			$this->assertFalse($container->isFrozen());
		}

		public function test_inject_config()
		{
			$this->builder->set_inject_config(false);
			$container = $this->builder->get_container();
			$this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerBuilder', $container);

			// Checks inject_config
			$this->assertFalse($container->hasParameter('dbal.dbhost'));
		}

		public function test_set_config_path()
		{
			$this->builder->set_config_path($this->phpbb_root_path . 'fixtures/other_config/');
			$container = $this->builder->get_container();
			$this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerBuilder', $container);

			$this->assertTrue($container->hasParameter('other_config'));
			$this->assertFalse($container->hasParameter('core'));
		}

		public function test_set_custom_parameters()
		{
			$this->builder->set_custom_parameters(array('my_parameter' => true));
			$container = $this->builder->get_container();
			$this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerBuilder', $container);

			$this->assertTrue($container->hasParameter('my_parameter'));
			$this->assertFalse($container->hasParameter('core.root_path'));
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

		function sql_not_like_expression($expression)
		{
		}

		function sql_fetchrowset($query_id = false)
		{
			return array(
				array('ext_name' => 'vendor/enabled'),
			);
		}
	}
}
