<?php

namespace foo\bar\controller;

class admin_controller
{
	protected $helper;
	
	public function __construct(\phpbb\acp\helper\controller $helper)
	{
		$this->helper = $helper;
	}
	
	public function main()
	{
		return $this->helper->render('@foo_bar/foobar.html', 'Bertie');
	}
}
