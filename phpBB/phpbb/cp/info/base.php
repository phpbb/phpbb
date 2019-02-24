<?php

namespace phpbb\cp\info;

abstract class base
{
	protected $auth;
	protected $config;
	protected $helper;
	protected $lang;
	protected $request;

	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $lang,
		\phpbb\request\request $request
	)
	{
		$this->auth		= $auth;
		$this->config	= $config;
		$this->helper	= $helper;
		$this->lang		= $lang;
		$this->request	= $request;
	}

	abstract public function get_title();

	public function get_auth()
	{
		return true;
	}

	public function get_route()
	{
		return '';
	}
}
