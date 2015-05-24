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

namespace phpbb\request;

/**
* An interface through which all application input can be accessed.
*/
interface request_interface
{
	/**#@+
	* Constant identifying the super global with the same name.
	*/
	const POST = 0;
	const GET = 1;
	const REQUEST = 2;
	const COOKIE = 3;
	const SERVER = 4;
	const FILES = 5;
	/**#@-*/

	/**
	* This function allows overwriting or setting a value in one of the super global arrays.
	*
	* Changes which are performed on the super globals directly will not have any effect on the results of
	* other methods this class provides. Using this function should be avoided if possible! It will
	* consume twice the the amount of memory of the value
	*
	* @param	string	$var_name	The name of the variable that shall be overwritten
	* @param	mixed	$value		The value which the variable shall contain.
	* 								If this is null the variable will be unset.
	* @param	\phpbb\request\request_interface::POST|GET|REQUEST|COOKIE	$super_global
	* 								Specifies which super global shall be changed
	*/
	public function overwrite($var_name, $value, $super_global = \phpbb\request\request_interface::REQUEST);

	/**
	* Central type safe input handling function.
	* All variables in GET or POST requests should be retrieved through this function to maximise security.
	*
	* @param	string|array	$var_name	The form variable's name from which data shall be retrieved.
	* 										If the value is an array this may be an array of indizes which will give
	* 										direct access to a value at any depth. E.g. if the value of "var" is array(1 => "a")
	* 										then specifying array("var", 1) as the name will return "a".
	* @param	mixed			$default	A default value that is returned if the variable was not set.
	* 										This function will always return a value of the same type as the default.
	* @param	bool			$multibyte	If $default is a string this paramater has to be true if the variable may contain any UTF-8 characters
	*										Default is false, causing all bytes outside the ASCII range (0-127) to be replaced with question marks
	* @param	\phpbb\request\request_interface::POST|GET|REQUEST|COOKIE	$super_global
	* 										Specifies which super global should be used
	*
	* @return	mixed	The value of $_REQUEST[$var_name] run through {@link set_var set_var} to ensure that the type is the
	*					the same as that of $default. If the variable is not set $default is returned.
	*/
	public function variable($var_name, $default, $multibyte = false, $super_global = \phpbb\request\request_interface::REQUEST);

	/**
	* Shortcut method to retrieve SERVER variables.
	*
	* @param	string|array	$var_name		See \phpbb\request\request_interface::variable
	* @param	mixed			$default		See \phpbb\request\request_interface::variable
	*
	* @return	mixed	The server variable value.
	*/
	public function server($var_name, $default = '');

	/**
	* Shortcut method to retrieve the value of client HTTP headers.
	*
	* @param	string|array	$header_name	The name of the header to retrieve.
	* @param	mixed			$default		See \phpbb\request\request_interface::variable
	*
	* @return	mixed	The header value.
	*/
	public function header($var_name, $default = '');

	/**
	* Checks whether a certain variable was sent via POST.
	* To make sure that a request was sent using POST you should call this function
	* on at least one variable.
	*
	* @param	string	$name	The name of the form variable which should have a
	*							_p suffix to indicate the check in the code that creates the form too.
	*
	* @return	bool			True if the variable was set in a POST request, false otherwise.
	*/
	public function is_set_post($name);

	/**
	* Checks whether a certain variable is set in one of the super global
	* arrays.
	*
	* @param	string	$var	Name of the variable
	* @param	\phpbb\request\request_interface::POST|GET|REQUEST|COOKIE	$super_global
	*							Specifies the super global which shall be checked
	*
	* @return	bool			True if the variable was sent as input
	*/
	public function is_set($var, $super_global = \phpbb\request\request_interface::REQUEST);

	/**
	* Checks whether the current request is an AJAX request (XMLHttpRequest)
	*
	* @return	bool			True if the current request is an ajax request
	*/
	public function is_ajax();

	/**
	* Checks if the current request is happening over HTTPS.
	*
	* @return	bool			True if the request is secure.
	*/
	public function is_secure();

	/**
	* Returns all variable names for a given super global
	*
	* @param	\phpbb\request\request_interface::POST|GET|REQUEST|COOKIE	$super_global
	*					The super global from which names shall be taken
	*
	* @return	array	All variable names that are set for the super global.
	*					Pay attention when using these, they are unsanitised!
	*/
	public function variable_names($super_global = \phpbb\request\request_interface::REQUEST);

	/**
	* Returns the original array of the requested super global
	*
	* @param	\phpbb\request\request_interface::POST|GET|REQUEST|COOKIE	$super_global
	*					The super global which will be returned
	*
	* @return	array	The original array of the requested super global.
	*/
	public function get_super_global($super_global = \phpbb\request\request_interface::REQUEST);

	/**
	 * Escape a string variable.
	 *
	 * @param mixed	$value		The contents to fill with
	 * @param bool	$multibyte	Indicates whether string values may contain UTF-8 characters.
	 * 							Default is false, causing all bytes outside the ASCII range (0-127) to be replaced with question marks.
	 * @return string|array
	 */
	public function escape($value, $multibyte);
}
