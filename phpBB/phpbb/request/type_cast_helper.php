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
* A helper class that provides convenience methods for type casting.
*/
class type_cast_helper implements \phpbb\request\type_cast_helper_interface
{
	/**
	* Set variable $result to a particular type.
	*
	* @param mixed	&$result		The variable to fill
	* @param mixed	$var			The contents to fill with
	* @param mixed	$type			The variable type. Will be used with {@link settype()}
	* @param bool	$multibyte		Indicates whether string values may contain UTF-8 characters.
	* 								Default is false, causing all bytes outside the ASCII range (0-127) to be replaced with question marks.
	* @param bool	$trim			Indicates whether trim() should be applied to string values.
	* 								Default is true.
	*/
	public function set_var(&$result, $var, $type, $multibyte = false, $trim = true)
	{
		settype($var, $type);
		$result = $var;

		if ($type == 'string')
		{
			$result = str_replace(array("\r\n", "\r", "\0"), array("\n", "\n", ''), $result);

			if ($trim)
			{
				$result = trim($result);
			}

			$result = htmlspecialchars($result, ENT_COMPAT, 'UTF-8');

			if ($multibyte)
			{
				$result = utf8_normalize_nfc($result);
			}

			if (!empty($result))
			{
				// Make sure multibyte characters are wellformed
				if ($multibyte)
				{
					if (!preg_match('/^./u', $result))
					{
						$result = '';
					}
				}
				else
				{
					// no multibyte, allow only ASCII (0-127)
					$result = preg_replace('/[\x80-\xFF]/', '?', $result);
				}
			}
		}
	}

	/**
	* Recursively sets a variable to a given type using {@link set_var set_var}
	*
	* @param	string	$var		The value which shall be sanitised (passed by reference).
	* @param	mixed	$default	Specifies the type $var shall have.
	* 								If it is an array and $var is not one, then an empty array is returned.
	* 								Otherwise var is cast to the same type, and if $default is an array all
	* 								keys and values are cast recursively using this function too.
	* @param	bool	$multibyte	Indicates whether string keys and values may contain UTF-8 characters.
	* 								Default is false, causing all bytes outside the ASCII range (0-127) to
	* 								be replaced with question marks.
	* @param	bool	$trim		Indicates whether trim() should be applied to string values.
	* 								Default is true.
	*/
	public function recursive_set_var(&$var, $default, $multibyte, $trim = true)
	{
		if (is_array($var) !== is_array($default))
		{
			$var = (is_array($default)) ? array() : $default;
			return;
		}

		if (!is_array($default))
		{
			$type = gettype($default);
			$this->set_var($var, $var, $type, $multibyte, $trim);
		}
		else
		{
			// make sure there is at least one key/value pair to use get the
			// types from
			if (empty($default))
			{
				$var = array();
				return;
			}

			list($default_key, $default_value) = each($default);
			$key_type = gettype($default_key);

			$_var = $var;
			$var = array();

			foreach ($_var as $k => $v)
			{
				$this->set_var($k, $k, $key_type, $multibyte);

				$this->recursive_set_var($v, $default_value, $multibyte, $trim);
				$var[$k] = $v;
			}
		}
	}
}
