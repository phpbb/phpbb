<?php

namespace phpbb\storage\adapter;

interface adapter_interface
{
	public function put_contents($path, $content);
	public function get_contents($path);
	public function exists($path);
	public function delete($path);
	public function rename($path_orig, $path_dest);
	public function copy($path_orig, $path_dest);
	public function create_dir($path);
	public function delete_dir($path);
}
