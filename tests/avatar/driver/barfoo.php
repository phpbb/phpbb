<?php

namespace phpbb\avatar\driver;

class barfoo extends \phpbb\avatar\driver\driver
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

	public function get_template_name()
	{
		return 'barfoo.html';
	}
}
