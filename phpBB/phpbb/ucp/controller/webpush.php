<?php

namespace phpbb\ucp\controller;

use phpbb\config\config;
use Symfony\Component\HttpFoundation\Response;

class webpush
{
	/** @var config */
	protected $config;

	public function __construct(config $config)
	{
		$this->config = $config;
	}

	/**
	 * Handle password reset request
	 *
	 * @return Response
	 */
	public function request(): Response
	{
		return new Response('foo');
	}
}
