<?php

namespace foo\bar\controller;

class admin_controller
{
	protected $helper;
	protected $lang;

	public function __construct(\phpbb\acp\helper\controller $helper, \phpbb\language\language $lang)
	{
		$this->helper = $helper;
		$this->lang = $lang;
	}

	public function main()
	{
		return $this->helper->render('@foo_bar/foobar.html', $this->lang->lang('ACP_FOO_BAR_MODE'));
	}
}
