<?php

namespace foo\bar\controller;

use Symfony\Component\HttpFoundation\Response;

class controller
{
	protected $template;

	public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template, $root_path, $php_ext)
	{
		$this->template = $template;
		$this->helper = $helper;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	public function handle()
	{
		return new Response('foo/bar controller handle() method', 200);
	}

	public function baz($test)
	{
		return new Response('Value of "test" URL argument is: ' . $test);
	}

	public function template()
	{
		$this->template->assign_var('A_VARIABLE', 'I am a variable');

		return $this->helper->render('foo_bar_body.html');
	}

	public function exception()
	{
		throw new \phpbb\controller\exception('Exception thrown from foo/exception route');
	}

	public function redirect()
	{
		$redirects = array(
			append_sid($this->root_path . 'index.' . $this->php_ext),
			append_sid($this->root_path . '../index.' . $this->php_ext),
			append_sid($this->root_path . 'tests/index.' . $this->php_ext),
			append_sid($this->root_path . '../tests/index.' . $this->php_ext),
			$this->helper->url('index'),
			$this->helper->url('../index'),
			$this->helper->url('../../index'),
			$this->helper->url('tests/index'),
			$this->helper->url('../tests/index'),
			$this->helper->url('../../tests/index'),
			$this->helper->url('../tests/../index'),
		);

		foreach ($redirects as $redirect)
		{
			$this->template->assign_block_vars('redirects', array(
				'URL'		=> redirect($redirect, true),
			));
		}

		return $this->helper->render('redirect_body.html');
	}
}
