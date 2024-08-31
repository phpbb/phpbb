<?php

namespace foo\test_captcha\captcha;

class test_captcha extends \phpbb\captcha\plugins\captcha_abstract
{

	function get_generator_class()
	{
	}

	public function init($type)
	{
	}

	public function execute_demo()
	{
	}

	public function execute()
	{
	}

	public function validate()
	{
		return true;
	}

	public function is_solved()
	{
		return true;
	}
}