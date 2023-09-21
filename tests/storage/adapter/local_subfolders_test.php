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

class phpbb_storage_adapter_local_subfolders_test extends phpbb_local_test_case
{
	protected function setUp(): void
	{
		parent::setUp();

		$this->adapter->configure(['path' => 'test_path', 'subfolders' => true]);
	}

	public function test_put_contents(): void
	{
		// When
		$this->adapter->put_contents('file.txt', 'abc');

		// Then
		$this->assertFileExists($this->path . '3d/8e/file.txt');
		$this->assertFileContains($this->path . '3d/8e/file.txt', 'abc');

		// Clean test
		unlink($this->path . '3d/8e/file.txt');
		rmdir($this->path . '3d/8e');
		rmdir($this->path . '3d');
	}

	public function test_get_contents(): void
	{
		// Given
		mkdir($this->path . '3d/8e', 0777, true);
		file_put_contents($this->path . '3d/8e/file.txt', 'abc');

		// When
		$content = $this->adapter->get_contents('file.txt');

		// Then
		$this->assertEquals('abc', $content);

		// Clean test
		unlink($this->path . '3d/8e/file.txt');
		rmdir($this->path . '3d/8e');
		rmdir($this->path . '3d');
	}

	public function test_exists(): void
	{
		// Given
		mkdir($this->path . '3d/8e', 0777, true);
		touch($this->path . '3d/8e/file.txt');

		// When
		$existent_file = $this->adapter->exists('file.txt');
		$non_existent_file = $this->adapter->exists('noexist.txt');
		$non_existent_file2 = $this->adapter->exists('3d/8e/file.txt');

		// Then
		$this->assertTrue($existent_file);
		$this->assertFalse($non_existent_file);
		$this->assertFalse($non_existent_file2);

		// Clean test
		unlink($this->path . '3d/8e/file.txt');
		rmdir($this->path . '3d/8e');
		rmdir($this->path . '3d');
	}

	public function test_delete_file(): void
	{
		// Given
		mkdir($this->path . '3d/8e', 0777, true);
		touch($this->path . '3d/8e/file.txt');
		$this->assertFileExists($this->path . '3d/8e/file.txt');

		// When
		$this->adapter->delete('file.txt');

		// Then
		$this->assertFileDoesNotExist($this->path . '3d/8e/file.txt');
		$this->assertFileDoesNotExist($this->path . '3d');
	}

	public function test_rename(): void
	{
		// Given
		mkdir($this->path . '3d/8e', 0777, true);
		touch($this->path . '3d/8e/file.txt');

		// When
		$this->adapter->rename('file.txt', 'file2.txt');

		// Then
		$this->assertFileDoesNotExist($this->path . '3d/8e/file.txt');
		$this->assertFileExists($this->path . '27/36/file2.txt');
		$this->assertFileDoesNotExist($this->path . '3d');

		// Clean test
		unlink($this->path . '27/36/file2.txt');
		rmdir($this->path . '27/36');
		rmdir($this->path . '27');
	}

	public function test_copy(): void
	{
		// Given
		mkdir($this->path . '3d/8e', 0777, true);
		file_put_contents($this->path . '3d/8e/file.txt', 'abc');

		// When
		$this->adapter->copy('file.txt', 'file2.txt');

		// Then
		$this->assertFileContains($this->path . '3d/8e/file.txt', 'abc');
		$this->assertFileContains($this->path . '27/36/file2.txt', 'abc');

		// Clean test
		unlink($this->path . '3d/8e/file.txt');
		rmdir($this->path . '3d/8e');
		rmdir($this->path . '3d');
		unlink($this->path . '27/36/file2.txt');
		rmdir($this->path . '27/36');
		rmdir($this->path . '27');
	}

	public function test_read_stream(): void
	{
		// Given
		mkdir($this->path . '3d/8e', 0777, true);
		file_put_contents($this->path . '3d/8e/file.txt', 'abc');

		// When
		$stream = $this->adapter->read_stream('file.txt');

		// Then
		$this->assertIsResource($stream);
		$this->assertEquals('abc', stream_get_contents($stream));

		// Clean test
		fclose($stream);
		unlink($this->path . '3d/8e/file.txt');
		rmdir($this->path . '3d/8e');
		rmdir($this->path . '3d');
	}

	public function test_write_stream(): void
	{
		// Given
		file_put_contents($this->path . 'file.txt', 'abc');
		$stream = fopen($this->path . 'file.txt', 'rb');

		// When
		$this->adapter->write_stream('file2.txt', $stream);
		fclose($stream);

		// Then
		$this->assertFileContains($this->path . '27/36/file2.txt', 'abc');

		// Clean test
		unlink($this->path . 'file.txt');
		unlink($this->path . '27/36/file2.txt');
		rmdir($this->path . '27/36');
		rmdir($this->path . '27');
	}
}
