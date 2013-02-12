<?php

use Symfony\Component\HttpFoundation\Response;

class phpbb_controller_foo
{
	/**
	* Bar method
	*
	* @return null
	*/
	public function bar()
	{
		return new Response('bar()', 200);
	}
}
