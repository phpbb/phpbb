<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

abstract class phpbb_cache_common_test_case extends phpbb_database_test_case
{
	public function test_get_put_exists()
	{
		$this->assertFalse($this->driver->_exists('test_key'));
		$this->assertSame(false, $this->driver->get('test_key'));

		$this->driver->put('test_key', 'test_value');

		$this->assertTrue($this->driver->_exists('test_key'));
		$this->assertEquals(
			'test_value',
			$this->driver->get('test_key'),
			'File ACM put and get'
		);
	}

	public function test_purge()
	{
		$this->driver->put('test_key', 'test_value');

		$this->assertEquals(
			'test_value',
			$this->driver->get('test_key'),
			'File ACM put and get'
		);

		$this->driver->purge();

		$this->assertSame(false, $this->driver->get('test_key'));
	}
}
