<?php

use Symfony\Component\HttpFoundation\Response;

class phpbb_ext_foo_controller
{
	/**
	* Constructor
	*/
	public function __construct()
	{
	}

	/**
	* Handle method
	*
	* @return null
	*/
	public function handle()
	{
		return new Response('Test', 200);
	}
}
