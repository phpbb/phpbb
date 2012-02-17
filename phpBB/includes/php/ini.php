<?php
/**
*
* @package phpBB
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
* Wrapper class for ini_get function.
*
* Provides easier handling of the different interpretations of ini values.
*
* @package phpBB
*/
class phpbb_php_ini
{
	/**
	* Simple wrapper for ini_get()
	* See http://php.net/manual/en/function.ini-get.php
	*
	* @param string $varname	The configuration option name. 
	* @return bool|string		False if configuration option does not exist,
	*							the configuration option value (string) otherwise. 
	*/
	function get($varname)
	{
		return @ini_get($varname);
	}

	/**
	* Gets configuration option value as a string and performs various
	* normalisation on the returned value.
	*
	* @param string $varname	The configuration option name. 
	* @return bool|string		False if configuration option does not exist,
	*							the configuration option value (string) otherwise.
	*/
	function get_string($varname)
	{
		$value = $this->get($varname);

		if ($value === false)
		{
			return false;
		}

		return trim($value);
	}

	/**
	* Gets configuration option value as a boolean.
	* Interprets the string value 'off' as false.
	*
	* @param string $varname	The configuration option name. 
	* @return bool				False if configuration option does not exist.
	*							False if configuration option is disabled.
	*							True otherwise.
	*/
	function get_bool($varname)
	{
		$value = strtolower($this->get_string($varname));

		if (empty($value) || $value == 'off')
		{
			return false;
		}

		return true;
	}

	/**
	* Gets configuration option value as an integer.
	*
	* @param string $varname	The configuration option name. 
	* @return bool|int			False if configuration option does not exist,
	*							the configuration option value (integer) otherwise.
	*/
	function get_int($varname)
	{
		$value = strtolower($this->get_string($varname));

		if (!is_numeric($value))
		{
			return false;
		}

		return (int) $value;
	}

	/**
	* Gets configuration option value as a float.
	*
	* @param string $varname	The configuration option name. 
	* @return bool|float		False if configuration option does not exist,
	*							the configuration option value (float) otherwise.
	*/
	function get_float($varname)
	{
		$value = strtolower($this->get_string($varname));

		if (!is_numeric($value))
		{
			return false;
		}

		return (float) $value;
	}

	/**
	* Gets configuration option value in bytes.
	* Converts strings like '128M' to bytes (integer or float).
	*
	* @param string $varname	The configuration option name. 
	* @return bool|int|float	False if configuration option does not exist,
	*							the configuration option value otherwise.
	*/
	function get_bytes($varname)
	{
		$value = strtolower($this->get_string($varname));

		if ($value === false)
		{
			return false;
		}

		if (is_numeric($value))
		{
			return $value;
		}
		else if (strlen($value) < 2)
		{
			return false;
		}

		$value_numeric = (int) $value;

		switch ($value[strlen($value) - 1])
		{
			case 'g':
				$value_numeric *= 1024;
			case 'm':
				$value_numeric *= 1024;
			case 'k':
				$value_numeric *= 1024;
			break;

			default:
				// It's not already in bytes (and thus numeric)
				// and does not carry a unit.
				return false;
		}

		return $value_numeric;
	}
}
