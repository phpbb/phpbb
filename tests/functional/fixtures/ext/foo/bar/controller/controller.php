<?php

namespace foo\bar\controller;

use Symfony\Component\HttpFoundation\Response;

class controller
{
	protected $template;
	protected $helper;
	protected $path_helper;
	protected $config;

	public function __construct(\phpbb\controller\helper $helper, \phpbb\path_helper $path_helper, \phpbb\template\template $template, \phpbb\config\config $config, $root_path, $php_ext)
	{
		$this->template = $template;
		$this->helper = $helper;
		$this->path_helper = $path_helper;
		$this->config = $config;
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
		$url_root = generate_board_url();

		$rewrite_prefix = (!empty($this->config['enable_mod_rewrite'])) ? '' : 'app.php/';

		$redirects = array(
			array(
				append_sid($this->root_path . 'index.' . $this->php_ext),
				'index.php',
			),
			array(
				append_sid($this->root_path . 'foo/bar/index.' . $this->php_ext),
				'foo/bar/index.php',
			),
			array(
				append_sid($this->root_path . 'tests/index.' . $this->php_ext),
				'tests/index.php',
			),
			array(
				$this->helper->url('index'),
				$rewrite_prefix . 'index',
			),
			array(
				$this->helper->url('tests/index'),
				$rewrite_prefix . 'tests/index',
			),
			array(
				$this->helper->url('tests/../index'),
				$rewrite_prefix . 'index',
			),
			/*
			// helper URLs starting with  ../ are prone to failure.
			// Do not test them right now.
			array(
				$this->helper->url('../index'),
				'../index',
			),
			array(
				$this->helper->url('../../index'),
				'../index',
			),
			array(
				$this->helper->url('../tests/index'),
				$rewrite_prefix . '../tests/index',
			),
			array(
				$this->helper->url('../tests/../index'),
				'../index',
			),
			array(
				$this->helper->url('../../tests/index'),
				'../tests/index',
			),
			*/
		);

		foreach ($redirects as $redirect)
		{
			$this->template->assign_block_vars('redirects', array(
				'URL'		=> redirect($redirect[0], true),
			));

			$this->template->assign_block_vars('redirects_expected', array(
				'URL'		=> $this->path_helper->clean_url($url_root . '/' . $redirect[1]),
			));
		}

		return $this->helper->render('redirect_body.html');
	}
}
