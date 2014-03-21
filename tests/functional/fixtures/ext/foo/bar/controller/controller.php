<?php

namespace foo\bar\controller;

use Symfony\Component\HttpFoundation\Response;

class controller
{
	protected $template;
	protected $helper;
	protected $path_helper;
	protected $config;
	protected $user;

	public function __construct(\phpbb\controller\helper $helper, \phpbb\path_helper $path_helper, \phpbb\template\template $template, \phpbb\config\config $config, \phpbb\user $user, $root_path, $php_ext)
	{
		$this->template = $template;
		$this->helper = $helper;
		$this->path_helper = $path_helper;
		$this->config = $config;
		$this->user = $user;
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

	public function login_redirect()
	{
		if (!$this->user->data['is_registered'])
		{
			login_box();
		}

		$this->template->assign_var('A_VARIABLE', 'I am a variable');

		return $this->helper->render('foo_bar_body.html');
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
				$this->helper->route('foo_index_controller'),
				$rewrite_prefix . 'index',
			),
			array(
				$this->helper->route('foo_tests_index_controller'),
				$rewrite_prefix . 'tests/index',
			),
			/**
			* Symfony does not allow /../ in routes
			array(
				$this->helper->route('foo_tests_dotdot_index_controller'),
				$rewrite_prefix . 'index',
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
