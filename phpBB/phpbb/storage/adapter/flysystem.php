<?php
namespace phpbb\storage\adapter;

use League\Flysystem\AdapterInterface;

class flysystem implements adapter_interface
{
	protected $adapter;

	public function __construct(AdapterInterface $adapter)
	{
		$this->adapter = $adapter;
	}

	public function put_contents($path, $content)
	{
		$this->adapter->put($path, $contents);
	}

	public function get_contents($path)
	{
		$this->adapter->read($path);
	}

	public function exists($path)
	{
		$this->adapter->has($path);
	}

	public function delete($path)
	{
		$this->adapter->delete($path);
	}

	public function rename($path_orig, $path_dest)
	{
		$this->adapter->rename($path_orig, $path_dest);
	}

	public function copy($path_orig, $path_dest)
	{
		$this->adapter->copy($path_orig, $path_dest);
	}

	public function create_dir($path)
	{
		$this->adapter->createDir($path);
	}

	public function delete_dir($path)
	{
		$this->adapter->deleteDir($path);
	}

	public function read_stream($path)
	{
		$this->driver->readStream($path);
	}

	public function write_stream($path, $resource)
	{
		$this->driver->writeStream($path, $resource);
	}
}
