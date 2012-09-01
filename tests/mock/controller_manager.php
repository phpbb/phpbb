<?php

class phpbb_mock_controller_manager extends phpbb_controller_manager
{
	public static $handled = false;

	public function get_controller($access_name)
	{
		parent::get_controller($access_name);
		self::$handled = true;
	}
}
