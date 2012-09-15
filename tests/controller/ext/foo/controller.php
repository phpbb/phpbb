<?php

use Symfony\Component\HttpFoundation\Response;

class phpbb_ext_foo_controller implements phpbb_controller_interface
{
	public function __construct(phpbb_user $user, phpbb_template $template)
	{
		$this->user = $user;
		$this->template = $template;
	}

	public function handle()
	{
		return new Response('Test', 200);
	}
}
