<?php
use Symfony\Component\HttpFoundation\Response;

class phpbb_ext_foo_bar_controller
{
	protected $template;

	public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template)
	{
		$this->template = $template;
		$this->helper = $helper;
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
}
