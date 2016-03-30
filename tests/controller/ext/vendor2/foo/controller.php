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
	public function handle($optional = 'foo')
	{
		return new Response('Test', 200);
	}

	public function handle2($foo = 'foo', $very_optional = 0)
	{
		return new Response('Test2', 200);
	}

	public function handle_fail($no_default)
	{
		return new Response('Test_fail', 200);
	}

	public function __invoke()
	{
		$this->handle();
	}
}
