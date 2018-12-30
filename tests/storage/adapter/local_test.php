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

	protected $filesystem;

	public function setUp(): void
	{
		parent::setUp();

		$this->filesystem = new \phpbb\filesystem\filesystem();
		$phpbb_root_path = getcwd() . DIRECTORY_SEPARATOR;

		$this->adapter = new \phpbb\storage\adapter\local($this->filesystem, new \FastImageSize\FastImageSize(), new \phpbb\mimetype\guesser(array(new \phpbb\mimetype\extension_guesser)), $phpbb_root_path);
		$this->adapter->configure(['path' => 'test_path', 'subfolders' => false]);

		$this->path = $phpbb_root_path . 'test_path/';
		mkdir($this->path);
	}

	public function tearDown(): void
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

	public function test_exists()
	{
		touch($this->path . 'file.txt');
		$this->assertTrue($this->adapter->exists('file.txt'));
		$this->assertFalse($this->adapter->exists('noexist.txt'));
		unlink($this->path . 'file.txt');
	}

	public function test_delete_file()
	{
		touch($this->path . 'file.txt');
		$this->assertTrue(file_exists($this->path . 'file.txt'));
		$this->adapter->delete('file.txt');
		$this->assertFalse(file_exists($this->path . 'file.txt'));
	}

	public function test_rename()
	{
		touch($this->path . 'file.txt');
		$this->adapter->rename('file.txt', 'file2.txt');
		$this->assertFalse(file_exists($this->path . 'file.txt'));
		$this->assertTrue(file_exists($this->path . 'file2.txt'));
		$this->assertFalse(file_exists($this->path . 'file.txt'));
		unlink($this->path . 'file2.txt');
	}

	public function test_copy()
	{
		file_put_contents($this->path . 'file.txt', 'abc');
		$this->adapter->copy('file.txt', 'file2.txt');
		$this->assertEquals(file_get_contents($this->path . 'file.txt'), 'abc');
		$this->assertEquals(file_get_contents($this->path . 'file2.txt'), 'abc');
		unlink($this->path . 'file.txt');
		unlink($this->path . 'file2.txt');
	}

	public function test_read_stream()
	{
		touch($this->path . 'file.txt');
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
