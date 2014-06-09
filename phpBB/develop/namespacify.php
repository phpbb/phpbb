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

//
// Security message:
//
// This script is potentially dangerous.
// Remove or comment the next line (die(".... ) to enable this script.
// Do NOT FORGET to either remove this script or disable it after you have used it.
//
die("Please read the first lines of this script for instructions on how to enable it");

$namespace_dir = realpath(__DIR__ . '/../phpbb/');
$code_dir = realpath(__DIR__ . '/../');
$test_dir = realpath(__DIR__ . '/../../tests/');
$config_dir = realpath(__DIR__ . '/../config/');

function map_class_name($old_class_name, $code_dir)
{
	$parts = explode('_', $old_class_name);
	$cur_dir = array();
	$cur_name = array();
	$in_name = false;
	foreach ($parts as $i => $part)
	{
		if (empty($part))
		{
			return null;
		}

		if (!$in_name)
		{
			$new_dir = array_merge($cur_dir, array($part));
			$path = $code_dir . '/' . implode('/', $new_dir);

			if (file_exists($path) && is_dir($path))
			{
				$cur_dir = $new_dir;
			}
			else
			{
				$in_name = true;
				$cur_name[] = $part;
			}
		}
		else
		{
			$cur_name[] = $part;
		}
	}

	if (empty($cur_name) && !empty($cur_dir))
	{
		$cur_name[] = $cur_dir[count($cur_dir) - 1];
	}

	if (file_exists($code_dir . '/' . implode('/', $cur_dir) . '/' . implode('_', $cur_name) . '.php'))
	{
		return implode('\\', $cur_dir) . '\\' . implode('_', $cur_name);
	}

	return null;
}

$iterator = new \AppendIterator();
$iterator->append(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($code_dir)));
$iterator->append(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($test_dir)));
$iterator->append(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($config_dir)));

foreach ($iterator as $file)
{
	if (substr($file->getPath(), 0, 6) === 'vendor')
	{
		continue;
	}

	if ($file->getExtension() == 'php')
	{
		$code = file_get_contents($file->getPathname());
		$namespaced_file = false;

		if (preg_match('#^' . preg_quote($namespace_dir, '#') . '#', $file->getPath()))
		{
			if (preg_match('#^(?:interface|(?:abstract )?class) (phpbb_[a-z0-9_]+)#m', $code, $matches))
			{
				$old_class_name = $matches[1];
				$dirs = explode(DIRECTORY_SEPARATOR, preg_replace('#^' . preg_quote(dirname($namespace_dir) . DIRECTORY_SEPARATOR, '#') . '#', '', $file->getPath()));

				$namespace = implode('\\', $dirs);

				if ($dirs[count($dirs) - 1] == substr($file->getFilename(), 0, -4))
				{
					$class_name = preg_replace('#^' . preg_quote(implode('_', $dirs), '#') . '#', $dirs[count($dirs) - 1], $old_class_name);
				}
				else
				{
					$class_name = preg_replace('#^' . preg_quote(implode('_', $dirs), '#') . '_#', '', $old_class_name);
				}

				$code = preg_replace("#^\*/$#m", "*/\n\nnamespace $namespace;", $code, 1, $count);
				if ($count != 1)
				{
					die("Incorrect replacement count for namespace of $old_class_name");
				}
				$code = preg_replace("#^(interface|(?:abstract )?class) $old_class_name#m", "\\1 $class_name", $code, -1, $count);
				if ($count != 1)
				{
					die("Incorrect replacement count for $old_class_name");
				}

				$namespaced_file = true;
			}
		}

		if (preg_match_all('#[^a-z0-9_$](phpbb_[a-z0-9_]+)#', $code, $matches))
		{
			foreach ($matches[1] as $old_class_name)
			{
				$class_name = map_class_name($old_class_name, $code_dir);
				if ($class_name)
				{
					$code = preg_replace("#([^a-z0-9_\$>])$old_class_name([^a-z0-9_])#", '\\1\\\\' . $class_name . '\\2', $code);
				}
			}
		}

		if ($namespaced_file)
		{
			$code = preg_replace('#new ([a-zA-Z0-9_][a-zA-Z0-9_\\\\]+)#', 'new \\\\\\1', $code);
			$code = preg_replace('#([^a-zA-Z0-9_\\\\$])([a-zA-Z0-9_][a-zA-Z0-9_\\\\]+)::#', '\\1\\\\\\2::', $code);
			$code = preg_replace('#catch \(([a-zA-Z0-9_][a-zA-Z0-9_\\\\]+)#', 'catch (\\\\\\1', $code);
			$code = preg_replace('#(\(|, )([a-zA-Z0-9_][a-zA-Z0-9_\\\\]+)(\s\$)#', '\\1\\\\\\2\\3', $code);
			$code = preg_replace('#(implements |extends )([a-zA-Z0-9_][a-zA-Z0-9_\\\\]+)(?=\s*(?:,|\n))#', '\\1\\\\\\2', $code);
			$abs_classes = array(
				'Countable',
				'IteratorAggregate',
				'ArrayAccess',
			);
			$code = preg_replace('#(\s+)(' . implode('|', $abs_classes) . ')#', '\\1\\\\\\2', $code);
			$rel_classes = array(
				'ContainerBuilder',
				'YamlFileLoader',
				'FileLocator',
				'Extension',
				'CompilerPassInterface',
				'EventSubscriberInterface',
				'EventDispatcherInterface',
				'ContainerAwareEventDispatcher',
				'ContainerInterface',
				'KernelEvents',
				'RouteCollection',
				'ControllerResolverInterface',
				'Request',
				'include',
				'array',
				'parent',
				'self',
			);
			$code = preg_replace('#([^a-zA-Z0-9_])\\\\((?:' . implode('|', $rel_classes) . ')(?:\s|\(|::|;))#', '\\1\\2', $code);
		}

		file_put_contents($file->getPathname(), $code);
	}

	if ($file->getExtension() == 'yml')
	{
		$code = file_get_contents($file->getPathname());

		if (preg_match_all('#\s*class:\s*(phpbb_[a-z0-9_]+)\s+#', $code, $matches))
		{
			foreach ($matches[1] as $old_class_name)
			{
				$class_name = map_class_name($old_class_name, $code_dir);
				if ($class_name)
				{
					$code = preg_replace("#(\s*class:\s*)$old_class_name(\s+)#", "\\1$class_name\\2", $code);
				}
			}
		}

		file_put_contents($file->getPathname(), $code);
	}
}

