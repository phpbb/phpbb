<?php

class phpbb_controller_dummy_test_controller extends phpbb_controller_base
{

	public static $handled = false;

	public function handle()
	{
		self::$handled = true;
	}

	public function get_access_name()
	{
		return 'foo';
	}
}
