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
			if (is_dir($path))
			{
				$this->check($path);
			}
			else if (substr($filename, strlen($filename)-4) == '.php')
			{
				// assume php binary is called php and it is in PATH
				$cmd = 'php -l ' . escapeshellarg($path);
				$output = array();
				$status = 1;
				exec($cmd, $output, $status);
				$output = implode("\n", $output);
				$this->assertEquals(0, $status, "php -l failed for $path:\n$output");
			}
		}
	}
}
