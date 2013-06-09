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
	static protected $exclude;

	static public function setUpBeforeClass()
	{
		$output = array();
		$status = 1;
		exec('(php -v) 2>&1', $output, $status);
		if ($status)
		{
			$output = implode("\n", $output);
			self::markTestSkipped("php is not in PATH or broken: $output");
		}

		self::$exclude = array(
			// PHP Fatal error:  Cannot declare class Container because the name is already in use in /var/www/projects/phpbb3/tests/../phpBB/vendor/symfony/dependency-injection/Symfony/Component/DependencyInjection/Tests/Fixtures/php/services1-1.php on line 20
			// https://gist.github.com/e003913ffd493da63cbc
			dirname(__FILE__) . '/../phpBB/vendor',
		);
	}

	/**
	* @group slow
	*/
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
			if ($filename == '.' || $filename == '..' || $filename == 'git')
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
				// assume php binary is called php and it is in PATH
				$cmd = '(php -l ' . escapeshellarg($path) . ') 2>&1';
				$output = array();
				$status = 1;
				exec($cmd, $output, $status);
				$output = implode("\n", $output);
				$this->assertEquals(0, $status, "php -l failed for $path:\n$output");
			}
		}
	}
}
