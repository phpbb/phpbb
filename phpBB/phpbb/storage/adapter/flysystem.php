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
		try
		{
			if(!$this->adapter->write($path, $contents))
			{
				throw new exception('CANNOT_DUMP_FILE', $path);
			}
		}
		catch (\League\Flysystem\FileExistsException $e)
		{
			throw new exception('CANNOT_OPEN_FILE', $path, array(), $e);
		}
	}

	public function get_contents($path)
	{
		try
		{
			if(!$this->adapter->read($path))
			{
				throw new exception('CANNOT_READ_FILE', $path);
			}
		}
		catch (\League\Flysystem\FileNotFoundException $e)
		{
			throw new exception('CANNOT_OPEN_FILE', $path, array(), $e);
		}
	}

	public function exists($path)
	{
		return $this->adapter->has($path);
	}

	public function delete($path)
	{
		try
		{
			if(!$this->adapter->delete($path))
			{
				throw new exception('CANNOT_DELETE_FILES', $path_orig);
			}
		}
		catch (\League\Flysystem\FileNotFoundException $e)
		{
			throw new exception('CANNOT_DELETE_FILES', $path_orig, array(), $e);
		}
	}

	public function rename($path_orig, $path_dest)
	{
		try
		{
			if(!$this->adapter->rename($path_orig, $path_dest))
			{
				throw new exception('CANNOT_RENAME_FILE', $path_orig);
			}
		}
		catch (\Exception $e)
		{
			throw new exception('CANNOT_RENAME_FILE', $path_orig, array(), $e);
		}
		/*
		catch (\League\Flysystem\FileNotFoundException $e)
		{
			throw new exception('CANNOT_RENAME_FILE', $path_orig, array(), $e);
		}
		catch (\League\Flysystem\FileExistsException $e)
		{
			throw new exception('CANNOT_RENAME_FILE', $path_orig, array(), $e);
		}*/
	}

	public function copy($path_orig, $path_dest)
	{
		try
		{
			if(!$this->adapter->copy($path_orig, $path_dest))
			{
				throw new exception('CANNOT_COPY_FILE', $path_orig);
			}
		}
		catch (\Exception $e)
		{
			throw new exception('CANNOT_COPY_FILE', $path_orig, array(), $e);
		}
		/*
		catch (\League\Flysystem\FileNotFoundException $e)
		{
			throw new exception('CANNOT_COPY_FILE', $path_orig, array(), $e);
		}
		catch (\League\Flysystem\FileExistsException $e)
		{
			throw new exception('CANNOT_COPY_FILE', $path_orig, array(), $e);
		}*/
	}

	public function create_dir($path)
	{
		if(!$this->adapter->createDir($path))
		{
			throw new exception('CANNOT_CREATE_DIRECTORY', $path);
		}
	}

	public function delete_dir($path)
	{
		try
		{
			if(!$this->adapter->deleteDir($path))
			{
				throw new exception('CANNOT_DELETE_FILES', $path_orig);
			}
		}
		catch (\League\Flysystem\RootViolationException $e)
		{
			throw new exception('CANNOT_DELETE_FILES', $path_orig, array(), $e);
		}
	}

	public function read_stream($path)
	{
		try
		{
			if(!$this->adapter->readStream($path))
			{
				throw new exception('CANNOT_READ_FILE', $path);
			}
		}
		catch (\League\Flysystem\FileNotFoundException $e)
		{
			throw new exception('CANNOT_OPEN_FILE', $path, array(), $e);
		}
	}

	public function write_stream($path, $resource)
	{
		try
		{
			if(!$this->adapter->writeStream($path, $resource))
			{
				throw new exception('CANNOT_DUMP_FILE', $path);
			}
		}
		catch (\League\Flysystem\FileExistsException $e)
		{
			throw new exception('CANNOT_OPEN_FILE', $path, array(), $e);
		}
	}
}
