<?php

namespace foo\bar\controller;

class user_controller
{
	protected $helper;

	public function __construct(\phpbb\controller\helper $helper)
	{
		$this->helper = $helper;
	}
	
	public function main()
	{
		return $this->helper->render('@foo_bar/foobar.html', 'Bertie');
	}
}
