<?php

namespace phpbb\controller;

use Symfony\Component\HttpFoundation\Response;

class foo
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
