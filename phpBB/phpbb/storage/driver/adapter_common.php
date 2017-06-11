<?php
namespace phpbb\storage\driver;

class adapter_common
{
	protected $filesystem;

	public function put_contents($path, $content)
	{
		$this->filesystem->put_contents($path, $contents);
	}

	public function get_contents($path)
	{
		$this->filesystem->put_contents($path);
	}

	public function exists($path)
	{
		$this->filesystem->exists($path);
	}

	public function delete($path)
	{
		$this->filesystem->delete($path);
	}

	public function rename($path_orig, $path_dest)
	{
		$this->filesystem->rename($path_orig, $path_dest);
	}

	public function copy($path_orig, $path_dest)
	{
		$this->filesystem->copy($path_orig, $path_dest);
	}

	public function create_dir($path)
	{
		$this->filesystem->create_dir($path);
	}

	public function delete_dir($path)
	{
		$this->filesystem->delete_dir($path);
	}
}
