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
