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

/**
* Replace all UTF-8 chars that are not in ASCII with their NCR
*
* @param	string	$text		UTF-8 string in NFC
* @return	string				ASCII string using NCRs for non-ASCII chars
*/
function utf8_encode_ncr($text)
{
	return preg_replace_callback('#[\\xC2-\\xF4][\\x80-\\xBF]+#', 'utf8_encode_ncr_callback', $text);
}

/**
* Callback used in encode_ncr()
*
* Takes a UTF-8 char and replaces it with its NCR. Attention, $m is an array
*
* @param	array	$m			0-based numerically indexed array passed by preg_replace_callback()
* @return	string				A HTML NCR if the character is valid, or the original string otherwise
*/
function utf8_encode_ncr_callback($m)
{
	switch (strlen($m[0]))
	{
		case 1:
			return '&#' . ord($m[0]) . ';';

		case 2:
			return '&#' . (((ord($m[0][0]) & 0x1F) << 6) | (ord($m[0][1]) & 0x3F)) . ';';

		case 3:
			return '&#' . (((ord($m[0][0]) & 0x0F) << 12) | ((ord($m[0][1]) & 0x3F) << 6) | (ord($m[0][2]) & 0x3F)) . ';';

		case 4:
			return '&#' . (((ord($m[0][0]) & 0x07) << 18) | ((ord($m[0][1]) & 0x3F) << 12) | ((ord($m[0][2]) & 0x3F) << 6) | (ord($m[0][3]) & 0x3F)) . ';';

		default:
			return $m[0];
	}		
}

/**
* Convert Numeric Character References to UTF-8 chars
*
* Notes:
*  - we do not convert NCRs recursively, if you pass &#38;#38; it will return &#38;
*  - we DO NOT check for the existence of the Unicode characters, therefore an entity
*    may be converted to an inexistent codepoint
*
* @param	string	$text		String to convert, encoded in UTF-8 (no normal form required)
* @return	string				UTF-8 string where NCRs have been replaced with the actual chars
*/
function utf8_decode_ncr($text)
{
	return preg_replace_callback('/&#([0-9]{1,6}|x[0-9A-F]{1,5});/i', 'utf8_decode_ncr_callback', $text);
}

/**
* Callback used in decode_ncr()
*
* Takes a NCR (in decimal or hexadecimal) and returns a UTF-8 char. Attention, $m is an array.
* It will ignore most of invalid NCRs, but not all!
*
* @param	array	$m			0-based numerically indexed array passed by preg_replace_callback()
* @return	string				UTF-8 char
*/
function utf8_decode_ncr_callback($m)
{
	$cp = (strncasecmp($m[1], 'x', 1)) ? $m[1] : hexdec(substr($m[1], 1));

	if ($cp > 0xFFFF)
	{
		return chr(0xF0 | ($cp >> 18)) . chr(0x80 | (($cp >> 12) & 0x3F)) . chr(0x80 | (($cp >> 6) & 0x3F)) . chr(0x80 | ($cp & 0x3F));
	}
	elseif ($cp > 0x7FF)
	{
		return chr(0xE0 | ($cp >> 12)) . chr(0x80 | (($cp >> 6) & 0x3F)) . chr(0x80 | ($cp & 0x3F));
	}
	elseif ($cp > 0x7F)
	{
		return chr(0xC0 | ($cp >> 6)) . chr(0x80 | ($cp & 0x3F));
	}
	else
	{
		return chr($cp);
	}
}

?>