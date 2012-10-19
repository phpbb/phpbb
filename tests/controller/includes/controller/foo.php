<?php

use Symfony\Component\HttpFoundation\Response;

class phpbb_controller_foo
{
	/**
	* Constructor
	*/
	public function __construct()
	{	
	}

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
