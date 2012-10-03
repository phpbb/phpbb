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

	public function error_404($message = '', $title = '')
	{
		return $this->handle($title, $message ?: $this->user->lang('PAGE_NOT_FOUND_MESSAGE'), 404);
	}

	public function error_500($message = '', $title = '')
	{
		return $this->handle($title, $message ?: $this->user->lang('INTERNAL_SERVER_ERROR_MESSAGE'), 500);
	}
}
