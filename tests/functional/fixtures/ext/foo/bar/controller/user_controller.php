<?php

namespace foo\bar\controller;

class user_controller
{
	protected $helper;
	protected $lang;

	public function __construct(\phpbb\controller\helper $helper, \phpbb\language\language $lang)
	{
		$this->helper = $helper;
		$this->lang = $lang;
	}

	public function main()
	{
		return $this->helper->render('@foo_bar/foobar.html', $this->lang->lang('UCP_FOO_BAR_MODE'));
	}
}
