<?php
/**
*
* @package controller
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Core controller for error pages
* @package phpBB3
*/
class phpbb_controller_error implements phpbb_controller_interface
{
	/**
	* Controller Helper object
	* @var phpbb_controller_helper
	*/
	protected $helper;

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
	* @param phpbb_controller_helper $helper Controller Helper object
	*/
	public function __construct(phpbb_controller_helper $helper, phpbb_user $user, phpbb_template $template)
	{
		$this->helper = $helper;
		$this->user = $user;
		$this->template = $template;
	}

	/**
	* Handle the loading of the controller page.
	*
	* @return Response
	*/
	public function handle($title = '', $message = '', $status_code = 500)
	{
		$this->template->assign_vars(array(
			'MESSAGE_TITLE'		=> $title ?: $this->user->lang('INFORMATION'),
			'MESSAGE_TEXT'		=> $message,
		));

		return $this->helper->render('message_body.html', $title, $status_code);
	}

	/**
	* Serve a 401 Error page with the given message and title
	*
	* As explained here: http://stackoverflow.com/a/6937030/996876
	* This should only be used when the user is NOT logged in
	* AND does not have access to a page.
	*
	* @param string $message The message to display to the user
	* @param string $title The title of the page
	* @return Response A Response instance
	*/
	public function error_401($message = '', $title = '')
	{
		return $this->handle($title, $message ?: $this->user->lang('NOT_AUTHENTICATED_ERROR'), 401);
	}

	/**
	* Serve a 403 Error page with the given message and title
	*
	* As explained here: http://stackoverflow.com/a/6937030/996876
	* This should only be used when the user IS logged in
	* AND does not have access to a page.
	*
	* @param string $message The message to display to the user
	* @param string $title The title of the page
	* @return Response A Response instance
	*/
	public function error_403($message = '', $title = '')
	{
		return $this->handle($title, $message ?: $this->user->lang('NOT_AUTHORISED_ERROR'), 403);
	}

	/**
	* Serve a 404 Error page with the given message and title
	*
	* This should only be used when the page requested does not exist
	*
	* @param string $message The message to display to the user
	* @param string $title The title of the page
	* @return Response A Response instance
	*/
	public function error_404($message = '', $title = '')
	{
		return $this->handle($title, $message ?: $this->user->lang('PAGE_NOT_FOUND_ERROR'), 404);
	}

	/**
	* Serve a 500 Error page with the given message and title
	*
	* This should only be used when an unknown error occurred or when
	* something happened that should never happen.
	*
	* @param string $message The message to display to the user
	* @param string $title The title of the page
	* @return Response A Response instance
	*/
	public function error_500($message = '', $title = '')
	{
		return $this->handle($title, $message ?: $this->user->lang('INTERNAL_SERVER_ERROR_ERROR'), 500);
	}
}
