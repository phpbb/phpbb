<?php
namespace rubencm\phpbb\storage\driver;

class local extends driver
{
	public function __construct()
	{
		$this->filesystem = new \phpbb\storage\adapter\local();
	}

	public static function get_name()
	{
		return 'LOCAL';
	}

	public static function get_params()
	{
		return array();
	}
}
