<?php
/**
*
* @package testing
* @version $Id$
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once 'test_framework/framework.php';

class phpbb_cache_test extends phpbb_test_case
{
	protected function tearDown()
	{
		$iterator = new DirectoryIterator('cache/tmp');
		foreach ($iterator as $file)
		{
			if (is_file('cache/tmp/' . $file))
			{
				unlink('cache/tmp/' . $file);
			}
		}
	}

	public function test_acm_file()
	{
		$acm = new phpbb_cache_driver_file('cache/tmp/');
		$acm->put('test_key', 'test_value');
		$acm->save();
		
		$this->assertEquals(
			'test_value',
			$acm->get('test_key'),
			'File ACM put and get'
		);
	}
}
