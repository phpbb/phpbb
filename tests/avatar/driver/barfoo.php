<?php

class phpbb_avatar_driver_barfoo extends phpbb_avatar_driver
{
	public function get_data($row)
	{
		return array();
	}

	public function prepare_form($request, $template, $user, $row, &$error)
	{
		return false;
	}

	public function process_form($request, $template, $user, $row, &$error)
	{
		return false;
	}
}
