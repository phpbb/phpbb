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

require_once __DIR__ . '/local_test_case.php';

class phpbb_storage_adapter_local_test extends phpbb_local_test_case
{
	protected function setUp(): void
	{
		parent::setUp();

		// Normally this would use $this->adapter->configure(['path' => 'test_path']);
		// but local::configure() uses realpath(), which vfsStream does not support.
		$root_path = new ReflectionProperty($this->adapter, 'root_path');
		$root_path->setValue($this->adapter, $this->path);
	}

	public function test_delete_file(): void
	{
		// Given
		touch($this->path . 'file.txt');
		$this->assertFileExists($this->path . 'file.txt');

		// When
		$this->adapter->delete('file.txt');

		// Then
		$this->assertFileDoesNotExist($this->path . 'file.txt');
	}

	public function test_read()
	{
		// Given
		file_put_contents($this->path . 'file.txt', 'abc');

		// When
		$stream = $this->adapter->read('file.txt');

		// Then
		$this->assertIsResource($stream);
		$this->assertEquals('abc', stream_get_contents($stream));

		fclose($stream);
	}

	public function test_write()
	{
		// Given
		file_put_contents($this->path . 'file.txt', 'abc');
		$stream = fopen($this->path . 'file.txt', 'rb');

		// When
		$this->adapter->write('file2.txt', $stream);
		fclose($stream);

		// Then
		$this->assertFileContains($this->path . 'file2.txt', 'abc');
	}
}
