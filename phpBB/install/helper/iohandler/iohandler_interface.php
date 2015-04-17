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
	 */
	public function send_response();

	/**
	 * Returns input variable
	 *
	 * @param string	$name		Name of the input variable to obtain
	 * @param mixed		$default	A default value that is returned if the variable was not set.
	 * 								This function will always return a value of the same type as the default.
	 * @param bool		$multibyte	If $default is a string this paramater has to be true if the variable may contain any UTF-8 characters
	 *								Default is false, causing all bytes outside the ASCII range (0-127) to be replaced with question marks
	 *
	 * @return mixed	Value of the input variable
	 */
	public function get_input($name, $default, $multibyte = false);

	/**
	 * Returns server variable
	 *
	 * This function should work the same as request_interterface::server().
	 *
	 * @param string	$name		Name of the server variable
	 * @param mixed		$default	Default value to return when the requested variable does not exist
	 *
	 * @return mixed	Value of the server variable
	 */
	public function get_server_variable($name, $default = '');

	/**
	 * Wrapper function for request_interterface::header()
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
	 *
	 * @return null
	 */
	public function add_error_message($error_title, $error_description = false);

	/**
	 * Adds a warning message to the rendering queue
	 *
	 * Note: When an array is passed into the parameters below, it will be
	 * resolved as printf($param[0], $param[1], ...).
	 *
	 * @param string|array		$warning_title			Title of the error message
	 * @param string|bool|array	$warning_description	Description of the error (and possibly guidelines to resolve it),
	 * 													or false if the error description is not available
	 *
	 * @return null
	 */
	public function add_warning_message($warning_title, $warning_description = false);

	/**
	 * Adds a log message to the rendering queue
	 *
	 * Note: When an array is passed into the parameters below, it will be
	 * resolved as printf($param[0], $param[1], ...).
	 *
	 * @param string|array		$log_title			Title of the error message
	 * @param string|bool|array	$log_description	Description of the error (and possibly guidelines to resolve it),
	 * 												or false if the error description is not available
	 *
	 * @return null
	 */
	public function add_log_message($log_title, $log_description = false);

	/**
	 * Adds a requested data group to the rendering queue
	 *
	 * @param string	$title	Language variable with the title of the form
	 * @param array		$form	An array describing the required data (options etc)
	 *
	 * @return null
	 */
	public function add_user_form_group($title, $form);
}
