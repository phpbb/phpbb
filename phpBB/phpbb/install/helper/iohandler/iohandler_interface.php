<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\install\helper\iohandler;

/**
 * Input-Output handler interface for the installer
 */
interface iohandler_interface
{
	/**
	 * Renders or returns response message
	 *
	 * @param bool	$no_more_output	Whether or not there will be more output in this output unit
	 */
	public function send_response($no_more_output = false);

	/**
	 * Returns input variable
	 *
	 * @param string	$name		Name of the input variable to obtain
	 * @param mixed		$default	A default value that is returned if the variable was not set.
	 * 								This function will always return a value of the same type as the default.
	 * @param bool		$multibyte	If $default is a string this parameter has to be true if the variable may contain any UTF-8 characters
	 *								Default is false, causing all bytes outside the ASCII range (0-127) to be replaced with question marks
	 *
	 * @return mixed	Value of the input variable
	 */
	public function get_input($name, $default, $multibyte = false);

	/**
	 * Returns raw input variable
	 *
	 * @param string	$name		Name of the input variable to obtain
	 * @param mixed		$default	A default value that is returned if the variable was not set.
	 * 								This function will always return a value of the same type as the default.
	 *
	 * @return mixed	Value of the raw input variable
	 */
	public function get_raw_input($name, $default);

	/**
	 * Returns server variable
	 *
	 * This function should work the same as request_interface::server().
	 *
	 * @param string	$name		Name of the server variable
	 * @param mixed		$default	Default value to return when the requested variable does not exist
	 *
	 * @return mixed	Value of the server variable
	 */
	public function get_server_variable($name, $default = '');

	/**
	 * Wrapper function for request_interface::header()
	 *
	 * @param string	$name		Name of the request header variable
	 * @param mixed		$default	Default value to return when the requested variable does not exist
	 *
	 * @return mixed
	 */
	public function get_header_variable($name, $default = '');

	/**
	 * Returns true if the connection is encrypted
	 *
	 * @return bool
	 */
	public function is_secure();

	/**
	 * Adds an error message to the rendering queue
	 *
	 * Note: When an array is passed into the parameters below, it will be
	 * resolved as printf($param[0], $param[1], ...).
	 *
	 * @param string|array		$error_title		Title of the error message.
	 * @param string|bool|array	$error_description	Description of the error (and possibly guidelines to resolve it),
	 * 												or false if the error description is not available.
	 */
	public function add_error_message($error_title, $error_description = false);

	/**
	 * Adds a warning message to the rendering queue
	 *
	 * Note: When an array is passed into the parameters below, it will be
	 * resolved as printf($param[0], $param[1], ...).
	 *
	 * @param string|array		$warning_title			Title of the warning message
	 * @param string|bool|array	$warning_description	Description of the warning (and possibly guidelines to resolve it),
	 * 													or false if the warning description is not available
	 */
	public function add_warning_message($warning_title, $warning_description = false);

	/**
	 * Adds a log message to the rendering queue
	 *
	 * Note: When an array is passed into the parameters below, it will be
	 * resolved as printf($param[0], $param[1], ...).
	 *
	 * @param string|array		$log_title			Title of the log message
	 * @param string|bool|array	$log_description	Description of the log,
	 * 												or false if the log description is not available
	 */
	public function add_log_message($log_title, $log_description = false);

	/**
	 * Adds a success message to the rendering queue
	 *
	 * Note: When an array is passed into the parameters below, it will be
	 * resolved as printf($param[0], $param[1], ...).
	 *
	 * @param string|array		$success_title			Title of the success message
	 * @param string|bool|array	$success_description	Description of the success,
	 * 													or false if the success description is not available
	 *
	 * @return null
	 */
	public function add_success_message($success_title, $success_description = false);

	/**
	 * Adds a requested data group to the rendering queue
	 *
	 * @param string	$title	Language variable with the title of the form
	 * @param array		$form	An array describing the required data (options etc)
	 */
	public function add_user_form_group($title, $form);

	/**
	 * Returns the rendering information for the form
	 *
	 * @param string	$title	Language variable with the title of the form
	 * @param array		$form	An array describing the required data (options etc)
	 *
	 * @return string	Information to render the form
	 */
	public function generate_form_render_data($title, $form);

	/**
	 * Sets the number of tasks belonging to the installer in the current mode.
	 *
	 * @param int	$task_count	Number of tasks
	 * @param bool	$restart	Whether or not to restart the progress bar, false by default
	 */
	public function set_task_count($task_count, $restart = false);

	/**
	 * Sets the progress information
	 *
	 * @param string	$task_lang_key	Language key for the name of the task
	 * @param int		$task_number	Position of the current task in the task queue
	 */
	public function set_progress($task_lang_key, $task_number);

	/**
	 * Sends refresh request to the client
	 */
	public function request_refresh();

	/**
	 * Marks stage as active in the navigation bar
	 *
	 * @param array	$menu_path	Array to the navigation elem
	 */
	public function set_active_stage_menu($menu_path);

	/**
	 * Marks stage as completed in the navigation bar
	 *
	 * @param array	$menu_path	Array to the navigation elem
	 */
	public function set_finished_stage_menu($menu_path);

	/**
	 * Finish the progress bar
	 *
	 * @param string	$message_lang_key	Language key for the message
	 */
	public function finish_progress($message_lang_key);

	/**
	 * Adds a download link
	 *
	 * @param string			$route	Route for the link
	 * @param string			$title	Language key for the title
	 * @param string|null|array	$msg	Language key for the message
	 */
	public function add_download_link($route, $title, $msg = null);

	/**
	 * Redirects the user to a new page
	 *
	 * @param string	$url		URL to redirect to
	 * @param bool		$use_ajax	Whether or not to use AJAX redirect
	 */
	public function redirect($url, $use_ajax = false);

	/**
	 * Renders the status of update files
	 *
	 * @param array	$status_array	Array containing files in groups to render
	 */
	public function render_update_file_status($status_array);

	/**
	 * Sends and sets cookies
	 *
	 * @param string	$cookie_name	Name of the cookie to set
	 * @param string	$cookie_value	Value of the cookie to set
	 */
	public function set_cookie($cookie_name, $cookie_value);
}
