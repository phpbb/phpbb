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

 class phpbb_storage_adapter_local_test extends phpbb_test_case
 {
	protected $adapter;

	protected $path;

	public function setUp()
	{
		parent::setUp();

		$filesystem = new \phpbb\filesystem\filesystem();
		$phpbb_root_path = getcwd() . DIRECTORY_SEPARATOR;

		$this->adapter = new \phpbb\storage\adapter\local($filesystem, new \FastImageSize\FastImageSize(), new \phpbb\mimetype\guesser(array(new \phpbb\mimetype\extension_guesser)), $phpbb_root_path);
		$this->adapter->configure(['path' => 'test_path']);

		$this->path = $phpbb_root_path . 'test_path/';
		mkdir($this->path);
	}

	public function data_test_exists()
	{
		yield [$this->path . '../README.md', true];
		yield [$this->path . 'nonexistent_file.php', false];
		yield [$this->path . '../phpBB/phpbb', true];
		yield [$this->path . 'nonexistent/folder', false];
	}

	public function tearDown()
	{
		$this->adapter = null;
		rmdir($this->path);
	}

	public function test_put_contents()
	{
		$this->adapter->put_contents('file.txt', 'abc');
		$this->assertTrue(file_exists($this->path . 'file.txt'));
		$this->assertEquals(file_get_contents($this->path . 'file.txt'), 'abc');
		unlink($this->path . 'file.txt');
	}

	public function test_get_contents()
	{
		file_put_contents($this->path . 'file.txt', 'abc');
		$this->assertEquals($this->adapter->get_contents('file.txt'), 'abc');
		unlink($this->path . 'file.txt');
	}

	/**
	 * @dataProvider data_test_exists
	 */
	public function test_exists($path, $expected)
	{
		$this->assertSame($expected, $this->adapter->exists($path));
	}

	public function test_delete_file()
	{
		file_put_contents($this->path . 'file.txt', '');
		$this->assertTrue(file_exists($this->path . 'file.txt'));
		$this->adapter->delete('file.txt');
		$this->assertFalse(file_exists($this->path . 'file.txt'));
	}

	public function test_delete_folder()
	{
		mkdir($this->path . 'path/to/dir', 0777, true);
		$this->assertTrue(file_exists($this->path . 'path/to/dir'));
		$this->adapter->delete('path');
		$this->assertFalse(file_exists($this->path . 'path/to/dir'));
	}

	public function test_rename()
	{
		file_put_contents($this->path . 'file.txt', '');
		$this->adapter->rename('file.txt', 'file2.txt');
		$this->assertFalse(file_exists($this->path . 'file.txt'));
		$this->assertTrue(file_exists($this->path . 'file2.txt'));
		unlink($this->path . 'file2.txt');
	}

	public function test_copy()
	{
		file_put_contents($this->path . 'file.txt', 'abc');
		$this->adapter->copy('file.txt', 'file2.txt');
		$this->assertEquals(file_get_contents($this->path . 'file.txt'), 'abc');
		$this->assertEquals(file_get_contents($this->path . 'file.txt'), 'abc');
		unlink($this->path . 'file.txt');
		unlink($this->path . 'file2.txt');
	}

	public function test_read_stream()
	{
		file_put_contents($this->path . 'file.txt', '');
		$stream = $this->adapter->read_stream('file.txt');
		$this->assertTrue(is_resource($stream));
		fclose($stream);
		unlink($this->path . 'file.txt');
	}

	public function test_write_stream()
	{
		file_put_contents($this->path . 'file.txt', 'abc');
		$stream = fopen($this->path . 'file.txt', 'rb');
		$this->adapter->write_stream('file2.txt', $stream);
		fclose($stream);
		$this->assertEquals(file_get_contents($this->path . 'file2.txt'), 'abc');
		unlink($this->path . 'file.txt');
		unlink($this->path . 'file2.txt');
	}
 }
