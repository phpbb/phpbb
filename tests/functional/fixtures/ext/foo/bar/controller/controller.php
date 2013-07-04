<?php
use Symfony\Component\HttpFoundation\Response;

class phpbb_ext_foo_bar_controller
{
	protected $template;

	public function __construct(phpbb_controller_helper $helper, phpbb_template $template)
	{
		$this->template = $template;
		$this->helper = $helper;

		$this->helper->set_style(array('ext/foo/bar/styles', 'styles'));
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
		throw new phpbb_controller_exception('Exception thrown from foo/exception route');
	}
}
