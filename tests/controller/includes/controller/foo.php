<?php

use Symfony\Component\HttpFoundation\Response;

class phpbb_controller_foo implements phpbb_controller_interface
{
	/**
	* User object
	* @var phpbb_user
	*/
	protected $user;

	/**
	* Template object
	* @var phpbb_template
	*/
	protected $template;

	/**
	* Constructor
	*
	* @param phpbb_user $user User object
	* @param phpbb_template $template Template object
	*/
	public function __construct(phpbb_user $user, phpbb_template $template)
	{
		$this->user = $user;
		$this->template = $template;
	}

	/**
	* Handle method
	* Because we specify a method in the route, we don't need to give this a
	* body. But we need to define it because we are implementing the interface
	*
	* @return null
	*/
	public function handle()
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
