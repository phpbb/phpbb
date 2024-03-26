<?php

namespace phpbb\captcha\plugins;

use phpbb\config\config;
use phpbb\template\template;

class incomplete extends \phpbb\captcha\plugins\captcha_abstract
{
	public function __construct(protected config $config, protected template $template,
								protected string $phpbb_root_path, protected string $phpEx)
	{}

	public function is_available()
	{
		return true;
	}

	public function get_generator_class()
	{
	}

	public static function get_name()
	{
		return 'CAPTCHA_INCOMPLETE';
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

	public function get_demo_template($id)
	{
		return '';
	}

	public function get_template()
	{
		$contact_link = phpbb_get_board_contact_link($this->config, $this->phpbb_root_path, $this->phpEx);

		$this->template->assign_vars([
			'CONFIRM_LANG'	=> $this->type != CONFIRM_POST ? 'CONFIRM_INCOMPLETE' : 'POST_CONFIRM_INCOMPLETE',
			'CONTACT_LINK'	=> $contact_link,
		]);

		return 'captcha_incomplete.html';
	}

	public function validate()
	{
		return false;
	}

	public function is_solved()
	{
		return false;
	}
}