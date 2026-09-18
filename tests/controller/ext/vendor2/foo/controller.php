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

	public function handle_union_fail(int|string $no_default)
	{
		return new Response('Test_union_fail', 200);
	}

	public function handle_symfony_request(\phpbb\symfony_request $symfony_request)
	{
		return new Response('Test_symfony_request', 200);
	}

	public function handle_variadic(...$extra)
	{
		return new Response('Test_variadic', 200);
	}

	public static function handle_static_fail(int $no_default)
	{
		return new Response('Test_static_fail', 200);
	}

	public function __invoke()
	{
		$this->handle();
	}
}
