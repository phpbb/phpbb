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

class lint_test extends phpbb_test_case
{
	protected static $php_binary;

	static public function setUpBeforeClass(): void
	{
		// Try to use PHP_BINARY constant if available so lint tests are run
		// using the same php binary as phpunit. If not available (pre PHP
		// 5.4), assume binary is called 'php' and is in PATH.
		self::$php_binary = defined('PHP_BINARY') ? escapeshellcmd(PHP_BINARY) : 'php';

		$output = array();
		$status = 1;
		exec(sprintf('(%s --version) 2>&1', self::$php_binary), $output, $status);
		if ($status)
		{
			$output = implode("\n", $output);
			if (self::$php_binary === 'php')
			{
				self::markTestSkipped(sprintf('php is not in PATH or broken. Output: %s', $output));
			}
			else
			{
				self::markTestSkipped(sprintf('Could not run PHP_BINARY %s. Output: %s', self::$php_binary, $output));
			}
		}
	}

	/**
	 * @dataProvider lint_data
	 */
	public function test_lint($path)
	{
		$cmd = sprintf('(%s -l %s) 2>&1', self::$php_binary, escapeshellarg($path));
		$output = array();
		$status = 1;
		exec($cmd, $output, $status);
		$output = implode("\n", $output);
		$this->assertEquals(0, $status, "PHP lint failed for $path:\n$output");
	}

	public function lint_data()
	{
		return $this->check(__DIR__ . '/..');
	}

	protected function check($root)
	{
		$files = array();
		$dh = opendir($root);

		if ($dh === false)
		{
			return $files;
		}

		while (($filename = readdir($dh)) !== false)
		{
			if ($filename == '.' || $filename == '..')
			{
				continue;
			}
			$path = $root . '/' . $filename;
			// skip symlinks to avoid infinite loops
			if (is_link($path))
			{
				continue;
			}
			if (is_dir($path) && !in_array($path, array(
					__DIR__ . '/../.git',
					__DIR__ . '/../build/new_version',
					__DIR__ . '/../build/old_versions',
					__DIR__ . '/../phpBB/cache',
					__DIR__ . '/../phpBB/ext',
					__DIR__ . '/../phpBB/store',
					// PHP Fatal error:  Cannot declare class Container because the name is already in use in /var/www/projects/phpbb3/tests/../phpBB/vendor/symfony/dependency-injection/Symfony/Component/DependencyInjection/Tests/Fixtures/php/services1-1.php on line 20
					// https://gist.github.com/e003913ffd493da63cbc
					__DIR__ . '/../phpBB/vendor',
					__DIR__ . '/../node_modules',
				)))
			{
				$files = array_merge($files, $this->check($path));
			}
			else if (substr($filename, strlen($filename)-4) == '.php')
			{
				$files[] = array($path);
			}
		}
		return $files;
	}
}
