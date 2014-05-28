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

namespace phpbb\php;

/**
* Wrapper class for ini_get function.
*
* Provides easier handling of the different interpretations of ini values.
*/
class ini
{
	/**
	* Simple wrapper for ini_get()
	* See http://php.net/manual/en/function.ini-get.php
	*
	* @param string $varname	The configuration option name.
	* @return bool|string		False if configuration option does not exist,
	*							the configuration option value (string) otherwise.
	*/
	public function get($varname)
	{
		return ini_get($varname);
	}

	/**
	* Gets the configuration option value as a trimmed string.
	*
	* @param string $varname	The configuration option name.
	* @return bool|string		False if configuration option does not exist,
	*							the configuration option value (string) otherwise.
	*/
	public function get_string($varname)
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
	public function get_bool($varname)
	{
		$value = $this->get_string($varname);

		if (empty($value) || strtolower($value) == 'off')
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
	*							false if configuration option value is not numeric,
	*							the configuration option value (integer) otherwise.
	*/
	public function get_int($varname)
	{
		$value = $this->get_string($varname);

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
	*							false if configuration option value is not numeric,
	*							the configuration option value (float) otherwise.
	*/
	public function get_float($varname)
	{
		$value = $this->get_string($varname);

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
	*							false if configuration option value is not well-formed,
	*							the configuration option value otherwise.
	*/
	public function get_bytes($varname)
	{
		$value = $this->get_string($varname);

		if ($value === false)
		{
			return false;
		}

		if (is_numeric($value))
		{
			// Already in bytes.
			return phpbb_to_numeric($value);
		}
		else if (strlen($value) < 2)
		{
			// Single character.
			return false;
		}
		else if (strlen($value) < 3 && $value[0] === '-')
		{
			// Two characters but the first one is a minus.
			return false;
		}

		$value_lower = strtolower($value);
		$value_numeric = phpbb_to_numeric($value);

		switch ($value_lower[strlen($value_lower) - 1])
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
