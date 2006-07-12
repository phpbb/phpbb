<?php
/** 
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* UTF-8 tools
*
* Whenever possible, these functions will try to use PHP's built-in functions or
* extensions, otherwise they will default to custom routines.
*
* If we go with UTF-8 in 3.2, we will also need a Unicode-aware replacement
* to strtolower()
*
* @package phpBB3
*/

/**
* Return the length (in characters) of a UTF-8 string
*
* @param	string	$text		UTF-8 string
* @return	integer				Length (in chars) of given string
*/
function utf8_strlen($text)
{
	if (function_exists('iconv_strlen'))
	{
		return iconv_strlen($text, 'utf-8');
	}

	if (function_exists('mb_strlen'))
	{
		return mb_strlen($text, 'utf-8');
	}

	return strlen(utf8_decode($text));
}

/**
* Recode a string to UTF-8
*
* If the encoding is not supported, the string is returned as-is
*
* @param	string	$string		Original string
* @param	string	$encoding	Original encoding
* @return	string				The string, encoded in UTF-8
*/
function utf8_recode($string, $encoding)
{
	$encoding = strtolower($encoding);

	if ($encoding == 'utf-8' || !is_string($string) || !isset($string[0]))
	{
		return $string;
	}

	/**
	* PHP has a built-in function for encoding from iso-8859-1, let's use that
	*/
	if ($encoding == 'iso-8859-1')
	{
		return utf8_encode($string);
	}

	/**
	* First, try iconv()
	*/
	if (function_exists('iconv'))
	{
		$ret = @iconv($encoding, 'utf-8', $string);

		if (isset($ret[0]))
		{
			return $ret;
		}
	}

	/**
	* Try the mb_string extension
	*/
	if (function_exists('mb_convert_encoding'))
	{
		$ret = @mb_convert_encoding($string, 'utf-8', $encoding);

		if (isset($ret[0]))
		{
			return $ret;
		}
	}

	/**
	* Try the recode extension
	*/
	if (function_exists('recode_string'))
	{
		$ret = @recode_string($encoding . '..utf-8', $string);

		if (isset($ret[0]))
		{
			return $ret;
		}
	}

	/**
	* If nothing works, check if we have a custom transcoder available
	*/
	if (!preg_match('#^[a-z0-9\\-]+$#', $encoding))
	{
		/**
		* Make sure the encoding name is alphanumeric, we don't want it
		* to be abused into loading arbitrary files
		*/
		trigger_error('Unknown encoding: ' . $encoding);
	}

	global $phpbb_root_path;
	if (!file_exists($phpbb_root_path . 'includes/utf/data/'))
	{
		return $string;
	}

	die('Finish me!! '.basename(__FILE__).' at line '.__LINE__);
}

?>