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

		$this->adapter->configure(['path' => 'test_path']);
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

		// Clean test
		fclose($stream);
		unlink($this->path . 'file.txt');
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

		// Clean test
		unlink($this->path . 'file.txt');
		unlink($this->path . 'file2.txt');
	}
}
