<?php
/**
*
* @package \phpbb\request\request
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\request;

/**
* A helper class that provides convenience methods for type casting.
*
* @package \phpbb\request\request
*/
class type_cast_helper implements \phpbb\request\type_cast_helper_interface
{

	/**
	* @var	string	Whether slashes need to be stripped from input
	*/
	protected $strip;

	/**
	* Initialises the type cast helper class.
	* All it does is find out whether magic quotes are turned on.
	*/
	public function __construct()
	{
		if (version_compare(PHP_VERSION, '5.4.0-dev', '>='))
		{
			$this->strip = false;
		}
		else
		{
			$this->strip = (@get_magic_quotes_gpc()) ? true : false;
		}
	}

	/**
	* Recursively applies addslashes to a variable.
	*
	* @param	mixed	&$var	Variable passed by reference to which slashes will be added.
	*/
	public function addslashes_recursively(&$var)
	{
		if (is_string($var))
		{
			$var = addslashes($var);
		}
		else if (is_array($var))
		{
			$var_copy = $var;
			$var = array();
			foreach ($var_copy as $key => $value)
			{
				if (is_string($key))
				{
					$key = addslashes($key);
				}
				$var[$key] = $value;

				$this->addslashes_recursively($var[$key]);
			}
		}
	}

	/**
	* Recursively applies addslashes to a variable if magic quotes are turned on.
	*
	* @param	mixed	&$var	Variable passed by reference to which slashes will be added.
	*/
	public function add_magic_quotes(&$var)
	{
		if ($this->strip)
		{
			$this->addslashes_recursively($var);
		}
	}

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

			$result = ($this->strip) ? stripslashes($result) : $result;
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
			$value_type = gettype($default_value);
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
