<?php

namespace foo;

use Symfony\Component\HttpFoundation\Response;

class controller
{
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
