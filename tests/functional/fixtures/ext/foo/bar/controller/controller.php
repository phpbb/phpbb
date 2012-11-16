<?php
use Symfony\Component\HttpFoundation\Response;

class phpbb_ext_foo_bar_controller
{
	public function handle()
	{
		return new Response('foo/bar controller handle() method', 200);
	}
}
