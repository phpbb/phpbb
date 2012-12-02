<?php

class phpbb_avatar_driver_foobar extends phpbb_avatar_driver implements phpbb_avatar_driver_interface
{
	public function get_data($row)
	{
		return array();
	}

	public function prepare_form($template, $row, &$error)
	{
		return false;
	}

	public function process_form($template, $row, &$error)
	{
		return false;
	}
}