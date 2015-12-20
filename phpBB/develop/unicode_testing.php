<?php
//
// This file provides some useful functions for debugging the unicode/UTF-8 library
// It requires utf_tools.php to be loaded
//
die("Please read the first lines of this script for instructions on how to enable it");

if (!headers_sent())
{
	header('Content-type: text/html; charset=UTF-8');
}

/**
 * Converts unicode escape sequences (\u0123) into UTF-8 characters
 *
 * @param	string	A unicode sequence
 * @return	string	UTF-8 representation of the given unicode sequence
 */
function unicode_to_utf8($string)
{
	$utf8 = '';
	$chars = array();
	for ($i = 0; $i < strlen($string); $i++)
	{
		if (isset($string[$i + 5]) && substr($string, $i, 2) == '\\u' && ctype_xdigit(substr($string, $i + 2, 4)))
		{
			$utf8 .= utf8_from_unicode(array(base_convert(substr($string, $i + 2, 4), 16, 10)));
			$i += 5;
		}
		else
		{
			$utf8 .= $string[$i];
		}
	}
	return $utf8;
}

/**
 * Takes an array of ints representing the Unicode characters and returns
 * a UTF-8 string.
 *
 * @param array $array array of unicode code points representing a string
 * @return string UTF-8 character string
 */
function utf8_from_unicode($array)
{
	$str = '';
	foreach ($array as $value)
	{
		$str .= utf8_chr($value);
	}
	return $str;
}

/**
* Converts a UTF-8 string to unicode code points
*
* @param	string	$text		UTF-8 string
* @return	string				Unicode code points
*/
function utf8_to_unicode($text)
{
	return preg_replace_callback(
		'#[\\xC2-\\xF4][\\x80-\\xBF]?[\\x80-\\xBF]?[\\x80-\\xBF]#',
		'utf8_to_unicode_callback',
		preg_replace_callback(
			'#[\\x00-\\x7f]#',
			'utf8_to_unicode_callback',
			$text
		)
	);
}

/**
* Takes a UTF-8 char and replaces it with its unicode escape sequence. Attention, $m is an array
*
* @param	array	$m			0-based numerically indexed array passed by preg_replace_callback()
* @return	string				A unicode escape sequence
*/
function utf8_to_unicode_callback($m)
{
	return '\u' . str_pad(base_convert(utf8_ord($m[0]), 10, 16), 4, '0', STR_PAD_LEFT) . '';
}
