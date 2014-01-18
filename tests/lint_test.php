<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_lint_test extends phpbb_test_case
{
	static protected $php_binary;
	static protected $exclude;

	static public function setUpBeforeClass()
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

		self::$exclude = array(
			dirname(__FILE__) . '/../.git',
			dirname(__FILE__) . '/../build/new_version',
			dirname(__FILE__) . '/../build/old_versions',
			dirname(__FILE__) . '/../phpBB/cache',
			// PHP Fatal error:  Cannot declare class Container because the name is already in use in /var/www/projects/phpbb3/tests/../phpBB/vendor/symfony/dependency-injection/Symfony/Component/DependencyInjection/Tests/Fixtures/php/services1-1.php on line 20
			// https://gist.github.com/e003913ffd493da63cbc
			dirname(__FILE__) . '/../phpBB/vendor',
		);
	}

	public function test_lint()
	{
		if (version_compare(PHP_VERSION, '5.3.0', '<'))
		{
			$this->markTestSkipped('phpBB uses PHP 5.3 syntax in some files, linting on PHP < 5.3 will fail');
		}

		$root = dirname(__FILE__) . '/..';
		$this->check($root);
	}

	protected function check($root)
	{
		$dh = opendir($root);
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
			if (is_dir($path) && !in_array($path, self::$exclude))
			{
				$this->check($path);
			}
			else if (substr($filename, strlen($filename)-4) == '.php')
			{
				$cmd = sprintf('(%s -l %s) 2>&1', self::$php_binary, escapeshellarg($path));
				$output = array();
				$status = 1;
				exec($cmd, $output, $status);
				$output = implode("\n", $output);
				$this->assertEquals(0, $status, "PHP lint failed for $path:\n$output");
			}
		}
	}
}
