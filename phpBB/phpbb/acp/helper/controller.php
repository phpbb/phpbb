<?php

namespace phpbb\acp\helper;

use Symfony\Component\HttpFoundation\Response;

class controller extends \phpbb\controller\helper
{
	/** @var \phpbb\acp\functions\controller */
	protected $functions;

	/**
	 * @param \phpbb\acp\functions\controller	$functions
	 * @return void
	 */
	public function set_functions(\phpbb\acp\functions\controller $functions)
	{
		$this->functions = $functions;
	}

	/**
	 * Automate setting up the page and creating the response object.
	 *
	 * @param string	$template_file				The template handle to render
	 * @param string	$page_title					The title of the page to output
	 * @param int		$status_code				The status code to be sent to the page header
	 * @param bool		$display_online_list		Not used
	 * @param int		$item_id					Not used
	 * @param string	$item						Not used
	 * @param bool		$send_headers				Not used
	 * @return Response								Response object containing the rendered page
	 */
	public function render($template_file, $page_title = '', $status_code = 200, $display_online_list = false, $item_id = 0, $item = 'forum', $send_headers = false)
	{
		$this->functions->adm_page_header($page_title);

		$this->template->set_filenames(['body' => $template_file]);

		$this->functions->adm_page_footer(true);

		return new Response($this->template->assign_display('body'), $status_code);
	}
}
