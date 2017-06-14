<?php
namespace phpbb\storage\adapter;

// Todo:
// Handle errors
// Streams

class local implements adapter_interface
{
	public function __construct()
	{
		// Nothing
	}

	public function put_contents($path, $content)
	{
		return file_put_contents($path, $content);
	}

	public function get_contents($path)
	{
		return file_get_contents($path);
	}

	public function exists($path)
	{
		return file_exists($path);
	}

	public function delete($path)
	{
		unlink($path);
	}

	public function rename($path_orig, $path_dest)
	{
		rename($path_orig, $path_dest);
	}

	public function copy($path_orig, $path_dest)
	{
		copy($path_orig, $path_dest);
	}

	public function create_dir($path)
	{
		mkdir($path);
	}

	public function delete_dir($path)
	{
		rmdir($path);
	}

	// https://github.com/thephpleague/flysystem/blob/master/src/Adapter/Local.php#L147
	public function read_stream($path)
	{
		return fopen($path, 'rb');
	}

	public function write_stream($path, $resource)
	{
		$stream = fopen($path, 'w+b');

		if(!$strean)
		{
			// error
		}

		stream_copy_to_stream($path, $stream);
	}
}
