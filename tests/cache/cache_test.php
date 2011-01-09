<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once __DIR__ . '/../../phpBB/includes/functions.php';

class phpbb_cache_test extends phpbb_test_case
{
	protected function tearDown()
	{
		$iterator = new DirectoryIterator(__DIR__ . '/tmp');
		foreach ($iterator as $file)
		{
			if (is_file(__DIR__ . '/tmp/' . $file) && $file != '.gitkeep')
			{
				unlink(__DIR__ . '/tmp/' . $file);
			}
		}
	}

	public function test_cache_driver_file()
	{
		$driver = new phpbb_cache_driver_file(__DIR__ . '/tmp/');
		$driver->put('test_key', 'test_value');
		$driver->save();

		$this->assertEquals(
			'test_value',
			$driver->get('test_key'),
			'File ACM put and get'
		);
	}
}
