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

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * Sets a configuration option's value.
 *
 * Please note that this function does not update the is_dynamic value for
 * an already existing config option.
 *
 * @param string $config_name   The configuration option's name
 * @param string $config_value  New configuration value
 * @param bool   $is_dynamic    Whether this variable should be cached (false) or
 *                              if it changes too frequently (true) to be
 *                              efficiently cached.
 *
 * @return void
 *
 * @deprecated 3.1.0 (To be removed: 4.0.0)
 */
function set_config($config_name, $config_value, $is_dynamic = false, \phpbb\config\config $set_config = null)
{
	static $config = null;

	if ($set_config !== null)
	{
		$config = $set_config;

		if (empty($config_name))
		{
			return;
		}
	}

	$config->set($config_name, $config_value, !$is_dynamic);
}

/**
 * Increments an integer config value directly in the database.
 *
 * @param string $config_name   The configuration option's name
 * @param int    $increment     Amount to increment by
 * @param bool   $is_dynamic    Whether this variable should be cached (false) or
 *                              if it changes too frequently (true) to be
 *                              efficiently cached.
 *
 * @return void
 *
 * @deprecated 3.1.0 (To be removed: 4.0.0)
 */
function set_config_count($config_name, $increment, $is_dynamic = false, \phpbb\config\config $set_config = null)
{
	static $config = null;
	if ($set_config !== null)
	{
		$config = $set_config;
		if (empty($config_name))
		{
			return;
		}
	}
	$config->increment($config_name, $increment, !$is_dynamic);
}

/**
 * Wrapper function of \phpbb\request\request::variable which exists for backwards compatability.
 * See {@link \phpbb\request\request_interface::variable \phpbb\request\request_interface::variable} for
 * documentation of this function's use.
 *
 * @deprecated 3.1.0 (To be removed: 4.0.0)
 * @param	mixed			$var_name	The form variable's name from which data shall be retrieved.
 * 										If the value is an array this may be an array of indizes which will give
 * 										direct access to a value at any depth. E.g. if the value of "var" is array(1 => "a")
 * 										then specifying array("var", 1) as the name will return "a".
 * 										If you pass an instance of {@link \phpbb\request\request_interface phpbb_request_interface}
 * 										as this parameter it will overwrite the current request class instance. If you do
 * 										not do so, it will create its own instance (but leave superglobals enabled).
 * @param	mixed			$default	A default value that is returned if the variable was not set.
 * 										This function will always return a value of the same type as the default.
 * @param	bool			$multibyte	If $default is a string this paramater has to be true if the variable may contain any UTF-8 characters
 *										Default is false, causing all bytes outside the ASCII range (0-127) to be replaced with question marks
 * @param	bool			$cookie		This param is mapped to \phpbb\request\request_interface::COOKIE as the last param for
 * 										\phpbb\request\request_interface::variable for backwards compatability reasons.
 * @param	\phpbb\request\request_interface|null|false	$request
 * 										If an instance of \phpbb\request\request_interface is given the instance is stored in
 *										a static variable and used for all further calls where this parameters is null. Until
 *										the function is called with an instance it automatically creates a new \phpbb\request\request
 *										instance on every call. By passing false this per-call instantiation can be restored
 *										after having passed in a \phpbb\request\request_interface instance.
 *
 * @return	mixed	The value of $_REQUEST[$var_name] run through {@link set_var set_var} to ensure that the type is the
 * 					the same as that of $default. If the variable is not set $default is returned.
 */
function request_var($var_name, $default, $multibyte = false, $cookie = false, $request = null)
{
	// This is all just an ugly hack to add "Dependency Injection" to a function
	// the only real code is the function call which maps this function to a method.
	static $static_request = null;
	if ($request instanceof \phpbb\request\request_interface)
	{
		$static_request = $request;
		if (empty($var_name))
		{
			return null;
		}
	}
	else if ($request === false)
	{
		$static_request = null;
		if (empty($var_name))
		{
			return null;
		}
	}
	$tmp_request = $static_request;
	// no request class set, create a temporary one ourselves to keep backwards compatibility
	if ($tmp_request === null)
	{
		// false param: enable super globals, so the created request class does not
		// make super globals inaccessible everywhere outside this function.
		$tmp_request = new \phpbb\request\request(new \phpbb\request\type_cast_helper(), false);
	}
	return $tmp_request->variable($var_name, $default, $multibyte, ($cookie) ? \phpbb\request\request_interface::COOKIE : \phpbb\request\request_interface::REQUEST);
}

/**
 * Casts a variable to the given type.
 *
 * @deprecated 3.1 (To be removed 4.0.0)
 */
function set_var(&$result, $var, $type, $multibyte = false)
{
	// no need for dependency injection here, if you have the object, call the method yourself!
	$type_cast_helper = new \phpbb\request\type_cast_helper();
	$type_cast_helper->set_var($result, $var, $type, $multibyte);
}

/**
 * Hashes an email address to a big integer
 *
 * @param string $email		Email address
 *
 * @return string			Unsigned Big Integer
 *
 * @deprecated 3.3.0-b2 (To be removed: 4.0.0)
 */
function phpbb_email_hash($email)
{
	return sprintf('%u', crc32(strtolower($email))) . strlen($email);
}

/**
 * Load the autoloaders added by the extensions.
 *
 * @param string $phpbb_root_path Path to the phpbb root directory.
 */
function phpbb_load_extensions_autoloaders($phpbb_root_path)
{
	$iterator = new \phpbb\finder\recursive_path_iterator(
		$phpbb_root_path . 'ext/',
		\RecursiveIteratorIterator::SELF_FIRST,
		\FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS
	);
	$iterator->setMaxDepth(2);

	foreach ($iterator as $file_info)
	{
		if ($file_info->getFilename() === 'vendor' && $iterator->getDepth() === 2)
		{
			$filename = $file_info->getRealPath() . '/autoload.php';
			if (file_exists($filename))
			{
				require $filename;
			}
		}
	}
}

/**
* Login using http authenticate.
*
* @param array	$param		Parameter array, see $param_defaults array.
*
* @return void
*
* @deprecated 3.2.10 (To be removed 4.0.0)
*/
function phpbb_http_login($param)
{
	global $auth, $user, $request;
	global $config;

	$param_defaults = array(
		'auth_message'	=> '',

		'autologin'		=> false,
		'viewonline'	=> true,
		'admin'			=> false,
	);

	// Overwrite default values with passed values
	$param = array_merge($param_defaults, $param);

	// User is already logged in
	// We will not overwrite his session
	if (!empty($user->data['is_registered']))
	{
		return;
	}

	// $_SERVER keys to check
	$username_keys = array(
		'PHP_AUTH_USER',
		'Authorization',
		'REMOTE_USER', 'REDIRECT_REMOTE_USER',
		'HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION',
		'REMOTE_AUTHORIZATION', 'REDIRECT_REMOTE_AUTHORIZATION',
		'AUTH_USER',
	);

	$password_keys = array(
		'PHP_AUTH_PW',
		'REMOTE_PASSWORD',
		'AUTH_PASSWORD',
	);

	$username = null;
	foreach ($username_keys as $k)
	{
		if ($request->is_set($k, \phpbb\request\request_interface::SERVER))
		{
			$username = html_entity_decode($request->server($k), ENT_COMPAT);
			break;
		}
	}

	$password = null;
	foreach ($password_keys as $k)
	{
		if ($request->is_set($k, \phpbb\request\request_interface::SERVER))
		{
			$password = html_entity_decode($request->server($k), ENT_COMPAT);
			break;
		}
	}

	// Decode encoded information (IIS, CGI, FastCGI etc.)
	if (!is_null($username) && is_null($password) && strpos($username, 'Basic ') === 0)
	{
		list($username, $password) = explode(':', base64_decode(substr($username, 6)), 2);
	}

	if (!is_null($username) && !is_null($password))
	{
		set_var($username, $username, 'string', true);
		set_var($password, $password, 'string', true);

		$auth_result = $auth->login($username, $password, $param['autologin'], $param['viewonline'], $param['admin']);

		if ($auth_result['status'] == LOGIN_SUCCESS)
		{
			return;
		}
		else if ($auth_result['status'] == LOGIN_ERROR_ATTEMPTS)
		{
			send_status_line(401, 'Unauthorized');

			trigger_error('NOT_AUTHORISED');
		}
	}

	// Prepend sitename to auth_message
	$param['auth_message'] = ($param['auth_message'] === '') ? $config['sitename'] : $config['sitename'] . ' - ' . $param['auth_message'];

	// We should probably filter out non-ASCII characters - RFC2616
	$param['auth_message'] = preg_replace('/[\x80-\xFF]/', '?', $param['auth_message']);

	header('WWW-Authenticate: Basic realm="' . $param['auth_message'] . '"');
	send_status_line(401, 'Unauthorized');

	trigger_error('NOT_AUTHORISED');
}

/**
* Converts query string (GET) parameters in request into hidden fields.
*
* Useful for forwarding GET parameters when submitting forms with GET method.
*
* It is possible to omit some of the GET parameters, which is useful if
* they are specified in the form being submitted.
*
* sid is always omitted.
*
* @param \phpbb\request\request $request Request object
* @param array $exclude A list of variable names that should not be forwarded
* @return string HTML with hidden fields
*
* @deprecated 3.2.10 (To be removed 4.0.0)
*/
function phpbb_build_hidden_fields_for_query_params($request, $exclude = null)
{
	$names = $request->variable_names(\phpbb\request\request_interface::GET);
	$hidden = '';
	foreach ($names as $name)
	{
		// Sessions are dealt with elsewhere, omit sid always
		if ($name == 'sid')
		{
			continue;
		}

		// Omit any additional parameters requested
		if (!empty($exclude) && in_array($name, $exclude))
		{
			continue;
		}

		$escaped_name = phpbb_quoteattr($name);

		// Note: we might retrieve the variable from POST or cookies
		// here. To avoid exposing cookies, skip variables that are
		// overwritten somewhere other than GET entirely.
		$value = $request->variable($name, '', true);
		$get_value = $request->variable($name, '', true, \phpbb\request\request_interface::GET);
		if ($value === $get_value)
		{
			$escaped_value = phpbb_quoteattr($value);
			$hidden .= "<input type='hidden' name=$escaped_name value=$escaped_value />";
		}
	}
	return $hidden;
}

/**
* Delete all PM(s) for a given user and delete the ones without references
*
* @param	int		$user_id	ID of the user whose private messages we want to delete
*
* @return	boolean		False if there were no pms found, true otherwise.
*
* @deprecated 3.2.10 (To be removed 4.0.0)
*/
function phpbb_delete_user_pms($user_id)
{
	$user_id = (int) $user_id;

	if (!$user_id)
	{
		return false;
	}

	return phpbb_delete_users_pms(array($user_id));
}

/**
* Casts a numeric string $input to an appropriate numeric type (i.e. integer or float)
*
* @param string $input		A numeric string.
*
* @return int|float			Integer $input if $input fits integer,
*							float $input otherwise.
*
* @deprecated 3.2.10 (To be removed 4.0.0)
*/
function phpbb_to_numeric($input)
{
	return ($input > PHP_INT_MAX) ? (float) $input : (int) $input;
}

/**
 * Parse cfg file
 * @param string $filename
 * @param bool|array $lines
 * @return array
 *
 * @deprecated 4.0.0-a1 (To be removed: 5.0.0)
 */
function parse_cfg_file($filename, $lines = false)
{
	$parsed_items = array();

	if ($lines === false)
	{
		$lines = file($filename);
	}

	foreach ($lines as $line)
	{
		$line = trim($line);

		if (!$line || $line[0] == '#' || ($delim_pos = strpos($line, '=')) === false)
		{
			continue;
		}

		// Determine first occurrence, since in values the equal sign is allowed
		$key = htmlspecialchars(strtolower(trim(substr($line, 0, $delim_pos))), ENT_COMPAT);
		$value = trim(substr($line, $delim_pos + 1));

		if (in_array($value, array('off', 'false', '0')))
		{
			$value = false;
		}
		else if (in_array($value, array('on', 'true', '1')))
		{
			$value = true;
		}
		else if (!trim($value))
		{
			$value = '';
		}
		else if (($value[0] == "'" && $value[strlen($value) - 1] == "'") || ($value[0] == '"' && $value[strlen($value) - 1] == '"'))
		{
			$value = htmlspecialchars(substr($value, 1, strlen($value) - 2), ENT_COMPAT);
		}
		else
		{
			$value = htmlspecialchars($value, ENT_COMPAT);
		}

		$parsed_items[$key] = $value;
	}

	if (isset($parsed_items['parent']) && isset($parsed_items['name']) && $parsed_items['parent'] == $parsed_items['name'])
	{
		unset($parsed_items['parent']);
	}

	return $parsed_items;
}
