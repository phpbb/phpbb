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
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

// Enforce ASCII only string handling
setlocale(LC_CTYPE, 'C');

/**
* Setup the UTF-8 portability layer
*/
Patchwork\Utf8\Bootup::initUtf8Encode();
Patchwork\Utf8\Bootup::initMbstring();
Patchwork\Utf8\Bootup::initIntl();

/**
* UTF-8 tools
*
* Whenever possible, these functions will try to use PHP's built-in functions or
* extensions, otherwise they will default to custom routines.
*
*/

/**
* UTF-8 aware alternative to strrpos
* @ignore
*/
function utf8_strrpos($str,	$needle, $offset = null)
{
	// Emulate behaviour of strrpos rather than raising warning
	if (empty($str))
	{
		return false;
	}

	if (is_null($offset))
	{
		return mb_strrpos($str, $needle);
	}
	else
	{
		return mb_strrpos($str, $needle, $offset);
	}
}

/**
* UTF-8 aware alternative to strpos
* @ignore
*/
function utf8_strpos($str, $needle, $offset = null)
{
	if (is_null($offset))
	{
		return mb_strpos($str, $needle);
	}
	else
	{
		return mb_strpos($str, $needle, $offset);
	}
}

/**
* UTF-8 aware alternative to strtolower
* @ignore
*/
function utf8_strtolower($str)
{
	return mb_strtolower($str);
}

/**
* UTF-8 aware alternative to strtoupper
* @ignore
*/
function utf8_strtoupper($str)
{
	return mb_strtoupper($str);
}

/**
* UTF-8 aware alternative to substr
* @ignore
*/
function utf8_substr($str, $offset, $length = null)
{
	if (is_null($length))
	{
		return mb_substr($str, $offset);
	}
	else
	{
		return mb_substr($str, $offset, $length);
	}
}

/**
* Return the length (in characters) of a UTF-8 string
* @ignore
*/
function utf8_strlen($text)
{
	return mb_strlen($text, 'utf-8');
}

/**
* UTF-8 aware alternative to str_split
* Convert a string to an array
*
* @author Harry Fuecks
* @param string $str UTF-8 encoded
* @param int $split_len number to characters to split string by
* @return array characters in string reverses
*/
function utf8_str_split($str, $split_len = 1)
{
	if (!is_int($split_len) || $split_len < 1)
	{
		return false;
	}

	$len = utf8_strlen($str);
	if ($len <= $split_len)
	{
		return array($str);
	}

	preg_match_all('/.{' . $split_len . '}|[^\x00]{1,' . $split_len . '}$/us', $str, $ar);
	return $ar[0];
}

/**
* UTF-8 aware alternative to strspn
* Find length of initial segment matching the mask
*
* @author Harry Fuecks
*/
function utf8_strspn($str, $mask, $start = null, $length = null)
{
	if ($start !== null || $length !== null)
	{
		$str = utf8_substr($str, $start, $length);
	}

	preg_match('/^[' . $mask . ']+/u', $str, $matches);

	if (isset($matches[0]))
	{
		return utf8_strlen($matches[0]);
	}

	return 0;
}

/**
* UTF-8 aware alternative to ucfirst
* Make a string's first character uppercase
*
* @author Harry Fuecks
* @param string
* @return string with first character as upper case (if applicable)
*/
function utf8_ucfirst($str)
{
	switch (utf8_strlen($str))
	{
		case 0:
			return '';
		break;

		case 1:
			return utf8_strtoupper($str);
		break;

		default:
			preg_match('/^(.{1})(.*)$/us', $str, $matches);
			return utf8_strtoupper($matches[1]) . $matches[2];
		break;
	}
}

/**
* Recode a string to UTF-8
*
* If the encoding is not supported, the string is returned as-is
*
* @param	string	$string		Original string
* @param	string	$encoding	Original encoding (lowered)
* @return	string				The string, encoded in UTF-8
*/
function utf8_recode($string, $encoding)
{
	$encoding = strtolower($encoding);

	if ($encoding == 'utf-8' || !is_string($string) || empty($string))
	{
		return $string;
	}

	// we force iso-8859-1 to be cp1252
	if ($encoding == 'iso-8859-1')
	{
		$encoding = 'cp1252';
	}
	// convert iso-8859-8-i to iso-8859-8
	else if ($encoding == 'iso-8859-8-i')
	{
		$encoding = 'iso-8859-8';
		$string = hebrev($string);
	}

	// First, try iconv()
	if (function_exists('iconv'))
	{
		$ret = @iconv($encoding, 'utf-8', $string);

		if (!empty($ret))
		{
			return $ret;
		}
	}

	// Try the mb_string extension
	if (function_exists('mb_convert_encoding'))
	{
		// mbstring is nasty on PHP4, we must make *sure* that we send a good encoding
		switch ($encoding)
		{
			case 'iso-8859-1':
			case 'iso-8859-2':
			case 'iso-8859-4':
			case 'iso-8859-7':
			case 'iso-8859-9':
			case 'iso-8859-15':
			case 'windows-1251':
			case 'windows-1252':
			case 'cp1252':
			case 'shift_jis':
			case 'euc-kr':
			case 'big5':
			case 'gb2312':
				$ret = @mb_convert_encoding($string, 'utf-8', $encoding);

				if (!empty($ret))
				{
					return $ret;
				}
		}
	}

	// Try the recode extension
	if (function_exists('recode_string'))
	{
		$ret = @recode_string($encoding . '..utf-8', $string);

		if (!empty($ret))
		{
			return $ret;
		}
	}

	// If nothing works, check if we have a custom transcoder available
	if (!preg_match('#^[a-z0-9_ \\-]+$#', $encoding))
	{
		// Make sure the encoding name is alphanumeric, we don't want it to be abused into loading arbitrary files
		trigger_error('Unknown encoding: ' . $encoding, E_USER_ERROR);
	}

	global $phpbb_root_path, $phpEx;

	// iso-8859-* character encoding
	if (preg_match('/iso[_ -]?8859[_ -]?(\\d+)/', $encoding, $array))
	{
		switch ($array[1])
		{
			case '1':
			case '2':
			case '4':
			case '7':
			case '8':
			case '9':
			case '15':
				if (!function_exists('iso_8859_' . $array[1]))
				{
					if (!file_exists($phpbb_root_path . 'includes/utf/data/recode_basic.' . $phpEx))
					{
						trigger_error('Basic reencoder file is missing', E_USER_ERROR);
					}
					include($phpbb_root_path . 'includes/utf/data/recode_basic.' . $phpEx);
				}
				return call_user_func('iso_8859_' . $array[1], $string);
			break;

			default:
				trigger_error('Unknown encoding: ' . $encoding, E_USER_ERROR);
			break;
		}
	}

	// CP/WIN character encoding
	if (preg_match('/(?:cp|windows)[_\- ]?(\\d+)/', $encoding, $array))
	{
		switch ($array[1])
		{
			case '932':
			break;
			case '1250':
			case '1251':
			case '1252':
			case '1254':
			case '1255':
			case '1256':
			case '1257':
			case '874':
				if (!function_exists('cp' . $array[1]))
				{
					if (!file_exists($phpbb_root_path . 'includes/utf/data/recode_basic.' . $phpEx))
					{
						trigger_error('Basic reencoder file is missing', E_USER_ERROR);
					}
					include($phpbb_root_path . 'includes/utf/data/recode_basic.' . $phpEx);
				}
				return call_user_func('cp' . $array[1], $string);
			break;

			default:
				trigger_error('Unknown encoding: ' . $encoding, E_USER_ERROR);
			break;
		}
	}

	// TIS-620
	if (preg_match('/tis[_ -]?620/', $encoding))
	{
		if (!function_exists('tis_620'))
		{
			if (!file_exists($phpbb_root_path . 'includes/utf/data/recode_basic.' . $phpEx))
			{
				trigger_error('Basic reencoder file is missing', E_USER_ERROR);
			}
			include($phpbb_root_path . 'includes/utf/data/recode_basic.' . $phpEx);
		}
		return tis_620($string);
	}

	// SJIS
	if (preg_match('/sjis(?:[_ -]?win)?|(?:cp|ibm)[_ -]?932|shift[_ -]?jis/', $encoding))
	{
		if (!function_exists('sjis'))
		{
			if (!file_exists($phpbb_root_path . 'includes/utf/data/recode_cjk.' . $phpEx))
			{
				trigger_error('CJK reencoder file is missing', E_USER_ERROR);
			}
			include($phpbb_root_path . 'includes/utf/data/recode_cjk.' . $phpEx);
		}
		return sjis($string);
	}

	// EUC_KR
	if (preg_match('/euc[_ -]?kr/', $encoding))
	{
		if (!function_exists('euc_kr'))
		{
			if (!file_exists($phpbb_root_path . 'includes/utf/data/recode_cjk.' . $phpEx))
			{
				trigger_error('CJK reencoder file is missing', E_USER_ERROR);
			}
			include($phpbb_root_path . 'includes/utf/data/recode_cjk.' . $phpEx);
		}
		return euc_kr($string);
	}

	// BIG-5
	if (preg_match('/big[_ -]?5/', $encoding))
	{
		if (!function_exists('big5'))
		{
			if (!file_exists($phpbb_root_path . 'includes/utf/data/recode_cjk.' . $phpEx))
			{
				trigger_error('CJK reencoder file is missing', E_USER_ERROR);
			}
			include($phpbb_root_path . 'includes/utf/data/recode_cjk.' . $phpEx);
		}
		return big5($string);
	}

	// GB2312
	if (preg_match('/gb[_ -]?2312/', $encoding))
	{
		if (!function_exists('gb2312'))
		{
			if (!file_exists($phpbb_root_path . 'includes/utf/data/recode_cjk.' . $phpEx))
			{
				trigger_error('CJK reencoder file is missing', E_USER_ERROR);
			}
			include($phpbb_root_path . 'includes/utf/data/recode_cjk.' . $phpEx);
		}
		return gb2312($string);
	}

	// Trigger an error?! Fow now just give bad data :-(
	trigger_error('Unknown encoding: ' . $encoding, E_USER_ERROR);
}

/**
 * Replace some special UTF-8 chars that are not in ASCII with their UCR.
 * using their Numeric Character Reference's Hexadecimal notation.
 *
 * Doesn't interfere with Japanese or Cyrillic etc.
 * Unicode character visualization will depend on the character support
 * of your web browser and the fonts installed on your system.
 *
 * @see https://en.wikibooks.org/wiki/Unicode/Character_reference/1F000-1FFFF
 *
 * @param	string	$text		UTF-8 string in NFC
 * @return	string				ASCII string using NCR for non-ASCII chars
 */
function utf8_encode_ucr($text)
{
	return preg_replace_callback('/[\\xF0-\\xF4].../', 'utf8_encode_ncr_callback', $text);
}

/**
 * Replace all UTF-8 chars that are not in ASCII with their NCR
 * using their Numeric Character Reference's Hexadecimal notation.
 *
 * @param	string	$text		UTF-8 string in NFC
 * @return	string				ASCII string using NCRs for non-ASCII chars
 */
function utf8_encode_ncr($text)
{
	return preg_replace_callback('#[\\xC2-\\xF4][\\x80-\\xBF]{1,3}#', 'utf8_encode_ncr_callback', $text);
}

/**
 * Callback used in utf8_encode_ncr() and utf8_encode_ucr()
 *
 * Takes a UTF-8 char and replaces it with its NCR. Attention, $m is an array
 *
 * @param	array	$m			0-based numerically indexed array passed by preg_replace_callback()
 * @return	string				A HTML NCR if the character is valid, or the original string otherwise
 */
function utf8_encode_ncr_callback($m)
{
	return '&#' . utf8_ord($m[0]) . ';';
}

/**
* Converts a UTF-8 char to an NCR
*
* @param string $chr UTF-8 char
* @return integer UNICODE code point
*/
function utf8_ord($chr)
{
	switch (strlen($chr))
	{
		case 1:
			return ord($chr);
		break;

		case 2:
			return ((ord($chr[0]) & 0x1F) << 6) | (ord($chr[1]) & 0x3F);
		break;

		case 3:
			return ((ord($chr[0]) & 0x0F) << 12) | ((ord($chr[1]) & 0x3F) << 6) | (ord($chr[2]) & 0x3F);
		break;

		case 4:
			return ((ord($chr[0]) & 0x07) << 18) | ((ord($chr[1]) & 0x3F) << 12) | ((ord($chr[2]) & 0x3F) << 6) | (ord($chr[3]) & 0x3F);
		break;

		default:
			return $chr;
	}
}

/**
* Converts an NCR to a UTF-8 char
*
* @param	int		$cp	UNICODE code point
* @return	string		UTF-8 char
*/
function utf8_chr($cp)
{
	if ($cp > 0xFFFF)
	{
		return chr(0xF0 | ($cp >> 18)) . chr(0x80 | (($cp >> 12) & 0x3F)) . chr(0x80 | (($cp >> 6) & 0x3F)) . chr(0x80 | ($cp & 0x3F));
	}
	else if ($cp > 0x7FF)
	{
		return chr(0xE0 | ($cp >> 12)) . chr(0x80 | (($cp >> 6) & 0x3F)) . chr(0x80 | ($cp & 0x3F));
	}
	else if ($cp > 0x7F)
	{
		return chr(0xC0 | ($cp >> 6)) . chr(0x80 | ($cp & 0x3F));
	}
	else
	{
		return chr($cp);
	}
}

/**
* Convert Numeric Character References to UTF-8 chars
*
* Notes:
*	- we do not convert NCRs recursively, if you pass &#38;#38; it will return &#38;
*	- we DO NOT check for the existence of the Unicode characters, therefore an entity may be converted to an inexistent codepoint
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

	return utf8_chr($cp);
}

/**
* Case folds a unicode string as per Unicode 5.0, section 3.13
*
* @param	string	$text	text to be case folded
* @param	string	$option	determines how we will fold the cases
* @return	string			case folded text
*/
function utf8_case_fold($text, $option = 'full')
{
	static $uniarray = array();
	global $phpbb_root_path, $phpEx;

	// common is always set
	if (!isset($uniarray['c']))
	{
		$uniarray['c'] = include($phpbb_root_path . 'includes/utf/data/case_fold_c.' . $phpEx);
	}

	// only set full if we need to
	if ($option === 'full' && !isset($uniarray['f']))
	{
		$uniarray['f'] = include($phpbb_root_path . 'includes/utf/data/case_fold_f.' . $phpEx);
	}

	// only set simple if we need to
	if ($option !== 'full' && !isset($uniarray['s']))
	{
		$uniarray['s'] = include($phpbb_root_path . 'includes/utf/data/case_fold_s.' . $phpEx);
	}

	// common is always replaced
	$text = strtr($text, $uniarray['c']);

	if ($option === 'full')
	{
		// full replaces a character with multiple characters
		$text = strtr($text, $uniarray['f']);
	}
	else
	{
		// simple replaces a character with another character
		$text = strtr($text, $uniarray['s']);
	}

	return $text;
}

/**
* Takes the input and does a "special" case fold. It does minor normalization
* and returns NFKC compatable text
*
* @param	string	$text	text to be case folded
* @param	string	$option	determines how we will fold the cases
* @return	string			case folded text
*/
function utf8_case_fold_nfkc($text, $option = 'full')
{
	static $fc_nfkc_closure = array(
		"\xCD\xBA"	=> "\x20\xCE\xB9",
		"\xCF\x92"	=> "\xCF\x85",
		"\xCF\x93"	=> "\xCF\x8D",
		"\xCF\x94"	=> "\xCF\x8B",
		"\xCF\xB2"	=> "\xCF\x83",
		"\xCF\xB9"	=> "\xCF\x83",
		"\xE1\xB4\xAC"	=> "\x61",
		"\xE1\xB4\xAD"	=> "\xC3\xA6",
		"\xE1\xB4\xAE"	=> "\x62",
		"\xE1\xB4\xB0"	=> "\x64",
		"\xE1\xB4\xB1"	=> "\x65",
		"\xE1\xB4\xB2"	=> "\xC7\x9D",
		"\xE1\xB4\xB3"	=> "\x67",
		"\xE1\xB4\xB4"	=> "\x68",
		"\xE1\xB4\xB5"	=> "\x69",
		"\xE1\xB4\xB6"	=> "\x6A",
		"\xE1\xB4\xB7"	=> "\x6B",
		"\xE1\xB4\xB8"	=> "\x6C",
		"\xE1\xB4\xB9"	=> "\x6D",
		"\xE1\xB4\xBA"	=> "\x6E",
		"\xE1\xB4\xBC"	=> "\x6F",
		"\xE1\xB4\xBD"	=> "\xC8\xA3",
		"\xE1\xB4\xBE"	=> "\x70",
		"\xE1\xB4\xBF"	=> "\x72",
		"\xE1\xB5\x80"	=> "\x74",
		"\xE1\xB5\x81"	=> "\x75",
		"\xE1\xB5\x82"	=> "\x77",
		"\xE2\x82\xA8"	=> "\x72\x73",
		"\xE2\x84\x82"	=> "\x63",
		"\xE2\x84\x83"	=> "\xC2\xB0\x63",
		"\xE2\x84\x87"	=> "\xC9\x9B",
		"\xE2\x84\x89"	=> "\xC2\xB0\x66",
		"\xE2\x84\x8B"	=> "\x68",
		"\xE2\x84\x8C"	=> "\x68",
		"\xE2\x84\x8D"	=> "\x68",
		"\xE2\x84\x90"	=> "\x69",
		"\xE2\x84\x91"	=> "\x69",
		"\xE2\x84\x92"	=> "\x6C",
		"\xE2\x84\x95"	=> "\x6E",
		"\xE2\x84\x96"	=> "\x6E\x6F",
		"\xE2\x84\x99"	=> "\x70",
		"\xE2\x84\x9A"	=> "\x71",
		"\xE2\x84\x9B"	=> "\x72",
		"\xE2\x84\x9C"	=> "\x72",
		"\xE2\x84\x9D"	=> "\x72",
		"\xE2\x84\xA0"	=> "\x73\x6D",
		"\xE2\x84\xA1"	=> "\x74\x65\x6C",
		"\xE2\x84\xA2"	=> "\x74\x6D",
		"\xE2\x84\xA4"	=> "\x7A",
		"\xE2\x84\xA8"	=> "\x7A",
		"\xE2\x84\xAC"	=> "\x62",
		"\xE2\x84\xAD"	=> "\x63",
		"\xE2\x84\xB0"	=> "\x65",
		"\xE2\x84\xB1"	=> "\x66",
		"\xE2\x84\xB3"	=> "\x6D",
		"\xE2\x84\xBB"	=> "\x66\x61\x78",
		"\xE2\x84\xBE"	=> "\xCE\xB3",
		"\xE2\x84\xBF"	=> "\xCF\x80",
		"\xE2\x85\x85"	=> "\x64",
		"\xE3\x89\x90"	=> "\x70\x74\x65",
		"\xE3\x8B\x8C"	=> "\x68\x67",
		"\xE3\x8B\x8E"	=> "\x65\x76",
		"\xE3\x8B\x8F"	=> "\x6C\x74\x64",
		"\xE3\x8D\xB1"	=> "\x68\x70\x61",
		"\xE3\x8D\xB3"	=> "\x61\x75",
		"\xE3\x8D\xB5"	=> "\x6F\x76",
		"\xE3\x8D\xBA"	=> "\x69\x75",
		"\xE3\x8E\x80"	=> "\x70\x61",
		"\xE3\x8E\x81"	=> "\x6E\x61",
		"\xE3\x8E\x82"	=> "\xCE\xBC\x61",
		"\xE3\x8E\x83"	=> "\x6D\x61",
		"\xE3\x8E\x84"	=> "\x6B\x61",
		"\xE3\x8E\x85"	=> "\x6B\x62",
		"\xE3\x8E\x86"	=> "\x6D\x62",
		"\xE3\x8E\x87"	=> "\x67\x62",
		"\xE3\x8E\x8A"	=> "\x70\x66",
		"\xE3\x8E\x8B"	=> "\x6E\x66",
		"\xE3\x8E\x8C"	=> "\xCE\xBC\x66",
		"\xE3\x8E\x90"	=> "\x68\x7A",
		"\xE3\x8E\x91"	=> "\x6B\x68\x7A",
		"\xE3\x8E\x92"	=> "\x6D\x68\x7A",
		"\xE3\x8E\x93"	=> "\x67\x68\x7A",
		"\xE3\x8E\x94"	=> "\x74\x68\x7A",
		"\xE3\x8E\xA9"	=> "\x70\x61",
		"\xE3\x8E\xAA"	=> "\x6B\x70\x61",
		"\xE3\x8E\xAB"	=> "\x6D\x70\x61",
		"\xE3\x8E\xAC"	=> "\x67\x70\x61",
		"\xE3\x8E\xB4"	=> "\x70\x76",
		"\xE3\x8E\xB5"	=> "\x6E\x76",
		"\xE3\x8E\xB6"	=> "\xCE\xBC\x76",
		"\xE3\x8E\xB7"	=> "\x6D\x76",
		"\xE3\x8E\xB8"	=> "\x6B\x76",
		"\xE3\x8E\xB9"	=> "\x6D\x76",
		"\xE3\x8E\xBA"	=> "\x70\x77",
		"\xE3\x8E\xBB"	=> "\x6E\x77",
		"\xE3\x8E\xBC"	=> "\xCE\xBC\x77",
		"\xE3\x8E\xBD"	=> "\x6D\x77",
		"\xE3\x8E\xBE"	=> "\x6B\x77",
		"\xE3\x8E\xBF"	=> "\x6D\x77",
		"\xE3\x8F\x80"	=> "\x6B\xCF\x89",
		"\xE3\x8F\x81"	=> "\x6D\xCF\x89",
		"\xE3\x8F\x83"	=> "\x62\x71",
		"\xE3\x8F\x86"	=> "\x63\xE2\x88\x95\x6B\x67",
		"\xE3\x8F\x87"	=> "\x63\x6F\x2E",
		"\xE3\x8F\x88"	=> "\x64\x62",
		"\xE3\x8F\x89"	=> "\x67\x79",
		"\xE3\x8F\x8B"	=> "\x68\x70",
		"\xE3\x8F\x8D"	=> "\x6B\x6B",
		"\xE3\x8F\x8E"	=> "\x6B\x6D",
		"\xE3\x8F\x97"	=> "\x70\x68",
		"\xE3\x8F\x99"	=> "\x70\x70\x6D",
		"\xE3\x8F\x9A"	=> "\x70\x72",
		"\xE3\x8F\x9C"	=> "\x73\x76",
		"\xE3\x8F\x9D"	=> "\x77\x62",
		"\xE3\x8F\x9E"	=> "\x76\xE2\x88\x95\x6D",
		"\xE3\x8F\x9F"	=> "\x61\xE2\x88\x95\x6D",
		"\xF0\x9D\x90\x80"	=> "\x61",
		"\xF0\x9D\x90\x81"	=> "\x62",
		"\xF0\x9D\x90\x82"	=> "\x63",
		"\xF0\x9D\x90\x83"	=> "\x64",
		"\xF0\x9D\x90\x84"	=> "\x65",
		"\xF0\x9D\x90\x85"	=> "\x66",
		"\xF0\x9D\x90\x86"	=> "\x67",
		"\xF0\x9D\x90\x87"	=> "\x68",
		"\xF0\x9D\x90\x88"	=> "\x69",
		"\xF0\x9D\x90\x89"	=> "\x6A",
		"\xF0\x9D\x90\x8A"	=> "\x6B",
		"\xF0\x9D\x90\x8B"	=> "\x6C",
		"\xF0\x9D\x90\x8C"	=> "\x6D",
		"\xF0\x9D\x90\x8D"	=> "\x6E",
		"\xF0\x9D\x90\x8E"	=> "\x6F",
		"\xF0\x9D\x90\x8F"	=> "\x70",
		"\xF0\x9D\x90\x90"	=> "\x71",
		"\xF0\x9D\x90\x91"	=> "\x72",
		"\xF0\x9D\x90\x92"	=> "\x73",
		"\xF0\x9D\x90\x93"	=> "\x74",
		"\xF0\x9D\x90\x94"	=> "\x75",
		"\xF0\x9D\x90\x95"	=> "\x76",
		"\xF0\x9D\x90\x96"	=> "\x77",
		"\xF0\x9D\x90\x97"	=> "\x78",
		"\xF0\x9D\x90\x98"	=> "\x79",
		"\xF0\x9D\x90\x99"	=> "\x7A",
		"\xF0\x9D\x90\xB4"	=> "\x61",
		"\xF0\x9D\x90\xB5"	=> "\x62",
		"\xF0\x9D\x90\xB6"	=> "\x63",
		"\xF0\x9D\x90\xB7"	=> "\x64",
		"\xF0\x9D\x90\xB8"	=> "\x65",
		"\xF0\x9D\x90\xB9"	=> "\x66",
		"\xF0\x9D\x90\xBA"	=> "\x67",
		"\xF0\x9D\x90\xBB"	=> "\x68",
		"\xF0\x9D\x90\xBC"	=> "\x69",
		"\xF0\x9D\x90\xBD"	=> "\x6A",
		"\xF0\x9D\x90\xBE"	=> "\x6B",
		"\xF0\x9D\x90\xBF"	=> "\x6C",
		"\xF0\x9D\x91\x80"	=> "\x6D",
		"\xF0\x9D\x91\x81"	=> "\x6E",
		"\xF0\x9D\x91\x82"	=> "\x6F",
		"\xF0\x9D\x91\x83"	=> "\x70",
		"\xF0\x9D\x91\x84"	=> "\x71",
		"\xF0\x9D\x91\x85"	=> "\x72",
		"\xF0\x9D\x91\x86"	=> "\x73",
		"\xF0\x9D\x91\x87"	=> "\x74",
		"\xF0\x9D\x91\x88"	=> "\x75",
		"\xF0\x9D\x91\x89"	=> "\x76",
		"\xF0\x9D\x91\x8A"	=> "\x77",
		"\xF0\x9D\x91\x8B"	=> "\x78",
		"\xF0\x9D\x91\x8C"	=> "\x79",
		"\xF0\x9D\x91\x8D"	=> "\x7A",
		"\xF0\x9D\x91\xA8"	=> "\x61",
		"\xF0\x9D\x91\xA9"	=> "\x62",
		"\xF0\x9D\x91\xAA"	=> "\x63",
		"\xF0\x9D\x91\xAB"	=> "\x64",
		"\xF0\x9D\x91\xAC"	=> "\x65",
		"\xF0\x9D\x91\xAD"	=> "\x66",
		"\xF0\x9D\x91\xAE"	=> "\x67",
		"\xF0\x9D\x91\xAF"	=> "\x68",
		"\xF0\x9D\x91\xB0"	=> "\x69",
		"\xF0\x9D\x91\xB1"	=> "\x6A",
		"\xF0\x9D\x91\xB2"	=> "\x6B",
		"\xF0\x9D\x91\xB3"	=> "\x6C",
		"\xF0\x9D\x91\xB4"	=> "\x6D",
		"\xF0\x9D\x91\xB5"	=> "\x6E",
		"\xF0\x9D\x91\xB6"	=> "\x6F",
		"\xF0\x9D\x91\xB7"	=> "\x70",
		"\xF0\x9D\x91\xB8"	=> "\x71",
		"\xF0\x9D\x91\xB9"	=> "\x72",
		"\xF0\x9D\x91\xBA"	=> "\x73",
		"\xF0\x9D\x91\xBB"	=> "\x74",
		"\xF0\x9D\x91\xBC"	=> "\x75",
		"\xF0\x9D\x91\xBD"	=> "\x76",
		"\xF0\x9D\x91\xBE"	=> "\x77",
		"\xF0\x9D\x91\xBF"	=> "\x78",
		"\xF0\x9D\x92\x80"	=> "\x79",
		"\xF0\x9D\x92\x81"	=> "\x7A",
		"\xF0\x9D\x92\x9C"	=> "\x61",
		"\xF0\x9D\x92\x9E"	=> "\x63",
		"\xF0\x9D\x92\x9F"	=> "\x64",
		"\xF0\x9D\x92\xA2"	=> "\x67",
		"\xF0\x9D\x92\xA5"	=> "\x6A",
		"\xF0\x9D\x92\xA6"	=> "\x6B",
		"\xF0\x9D\x92\xA9"	=> "\x6E",
		"\xF0\x9D\x92\xAA"	=> "\x6F",
		"\xF0\x9D\x92\xAB"	=> "\x70",
		"\xF0\x9D\x92\xAC"	=> "\x71",
		"\xF0\x9D\x92\xAE"	=> "\x73",
		"\xF0\x9D\x92\xAF"	=> "\x74",
		"\xF0\x9D\x92\xB0"	=> "\x75",
		"\xF0\x9D\x92\xB1"	=> "\x76",
		"\xF0\x9D\x92\xB2"	=> "\x77",
		"\xF0\x9D\x92\xB3"	=> "\x78",
		"\xF0\x9D\x92\xB4"	=> "\x79",
		"\xF0\x9D\x92\xB5"	=> "\x7A",
		"\xF0\x9D\x93\x90"	=> "\x61",
		"\xF0\x9D\x93\x91"	=> "\x62",
		"\xF0\x9D\x93\x92"	=> "\x63",
		"\xF0\x9D\x93\x93"	=> "\x64",
		"\xF0\x9D\x93\x94"	=> "\x65",
		"\xF0\x9D\x93\x95"	=> "\x66",
		"\xF0\x9D\x93\x96"	=> "\x67",
		"\xF0\x9D\x93\x97"	=> "\x68",
		"\xF0\x9D\x93\x98"	=> "\x69",
		"\xF0\x9D\x93\x99"	=> "\x6A",
		"\xF0\x9D\x93\x9A"	=> "\x6B",
		"\xF0\x9D\x93\x9B"	=> "\x6C",
		"\xF0\x9D\x93\x9C"	=> "\x6D",
		"\xF0\x9D\x93\x9D"	=> "\x6E",
		"\xF0\x9D\x93\x9E"	=> "\x6F",
		"\xF0\x9D\x93\x9F"	=> "\x70",
		"\xF0\x9D\x93\xA0"	=> "\x71",
		"\xF0\x9D\x93\xA1"	=> "\x72",
		"\xF0\x9D\x93\xA2"	=> "\x73",
		"\xF0\x9D\x93\xA3"	=> "\x74",
		"\xF0\x9D\x93\xA4"	=> "\x75",
		"\xF0\x9D\x93\xA5"	=> "\x76",
		"\xF0\x9D\x93\xA6"	=> "\x77",
		"\xF0\x9D\x93\xA7"	=> "\x78",
		"\xF0\x9D\x93\xA8"	=> "\x79",
		"\xF0\x9D\x93\xA9"	=> "\x7A",
		"\xF0\x9D\x94\x84"	=> "\x61",
		"\xF0\x9D\x94\x85"	=> "\x62",
		"\xF0\x9D\x94\x87"	=> "\x64",
		"\xF0\x9D\x94\x88"	=> "\x65",
		"\xF0\x9D\x94\x89"	=> "\x66",
		"\xF0\x9D\x94\x8A"	=> "\x67",
		"\xF0\x9D\x94\x8D"	=> "\x6A",
		"\xF0\x9D\x94\x8E"	=> "\x6B",
		"\xF0\x9D\x94\x8F"	=> "\x6C",
		"\xF0\x9D\x94\x90"	=> "\x6D",
		"\xF0\x9D\x94\x91"	=> "\x6E",
		"\xF0\x9D\x94\x92"	=> "\x6F",
		"\xF0\x9D\x94\x93"	=> "\x70",
		"\xF0\x9D\x94\x94"	=> "\x71",
		"\xF0\x9D\x94\x96"	=> "\x73",
		"\xF0\x9D\x94\x97"	=> "\x74",
		"\xF0\x9D\x94\x98"	=> "\x75",
		"\xF0\x9D\x94\x99"	=> "\x76",
		"\xF0\x9D\x94\x9A"	=> "\x77",
		"\xF0\x9D\x94\x9B"	=> "\x78",
		"\xF0\x9D\x94\x9C"	=> "\x79",
		"\xF0\x9D\x94\xB8"	=> "\x61",
		"\xF0\x9D\x94\xB9"	=> "\x62",
		"\xF0\x9D\x94\xBB"	=> "\x64",
		"\xF0\x9D\x94\xBC"	=> "\x65",
		"\xF0\x9D\x94\xBD"	=> "\x66",
		"\xF0\x9D\x94\xBE"	=> "\x67",
		"\xF0\x9D\x95\x80"	=> "\x69",
		"\xF0\x9D\x95\x81"	=> "\x6A",
		"\xF0\x9D\x95\x82"	=> "\x6B",
		"\xF0\x9D\x95\x83"	=> "\x6C",
		"\xF0\x9D\x95\x84"	=> "\x6D",
		"\xF0\x9D\x95\x86"	=> "\x6F",
		"\xF0\x9D\x95\x8A"	=> "\x73",
		"\xF0\x9D\x95\x8B"	=> "\x74",
		"\xF0\x9D\x95\x8C"	=> "\x75",
		"\xF0\x9D\x95\x8D"	=> "\x76",
		"\xF0\x9D\x95\x8E"	=> "\x77",
		"\xF0\x9D\x95\x8F"	=> "\x78",
		"\xF0\x9D\x95\x90"	=> "\x79",
		"\xF0\x9D\x95\xAC"	=> "\x61",
		"\xF0\x9D\x95\xAD"	=> "\x62",
		"\xF0\x9D\x95\xAE"	=> "\x63",
		"\xF0\x9D\x95\xAF"	=> "\x64",
		"\xF0\x9D\x95\xB0"	=> "\x65",
		"\xF0\x9D\x95\xB1"	=> "\x66",
		"\xF0\x9D\x95\xB2"	=> "\x67",
		"\xF0\x9D\x95\xB3"	=> "\x68",
		"\xF0\x9D\x95\xB4"	=> "\x69",
		"\xF0\x9D\x95\xB5"	=> "\x6A",
		"\xF0\x9D\x95\xB6"	=> "\x6B",
		"\xF0\x9D\x95\xB7"	=> "\x6C",
		"\xF0\x9D\x95\xB8"	=> "\x6D",
		"\xF0\x9D\x95\xB9"	=> "\x6E",
		"\xF0\x9D\x95\xBA"	=> "\x6F",
		"\xF0\x9D\x95\xBB"	=> "\x70",
		"\xF0\x9D\x95\xBC"	=> "\x71",
		"\xF0\x9D\x95\xBD"	=> "\x72",
		"\xF0\x9D\x95\xBE"	=> "\x73",
		"\xF0\x9D\x95\xBF"	=> "\x74",
		"\xF0\x9D\x96\x80"	=> "\x75",
		"\xF0\x9D\x96\x81"	=> "\x76",
		"\xF0\x9D\x96\x82"	=> "\x77",
		"\xF0\x9D\x96\x83"	=> "\x78",
		"\xF0\x9D\x96\x84"	=> "\x79",
		"\xF0\x9D\x96\x85"	=> "\x7A",
		"\xF0\x9D\x96\xA0"	=> "\x61",
		"\xF0\x9D\x96\xA1"	=> "\x62",
		"\xF0\x9D\x96\xA2"	=> "\x63",
		"\xF0\x9D\x96\xA3"	=> "\x64",
		"\xF0\x9D\x96\xA4"	=> "\x65",
		"\xF0\x9D\x96\xA5"	=> "\x66",
		"\xF0\x9D\x96\xA6"	=> "\x67",
		"\xF0\x9D\x96\xA7"	=> "\x68",
		"\xF0\x9D\x96\xA8"	=> "\x69",
		"\xF0\x9D\x96\xA9"	=> "\x6A",
		"\xF0\x9D\x96\xAA"	=> "\x6B",
		"\xF0\x9D\x96\xAB"	=> "\x6C",
		"\xF0\x9D\x96\xAC"	=> "\x6D",
		"\xF0\x9D\x96\xAD"	=> "\x6E",
		"\xF0\x9D\x96\xAE"	=> "\x6F",
		"\xF0\x9D\x96\xAF"	=> "\x70",
		"\xF0\x9D\x96\xB0"	=> "\x71",
		"\xF0\x9D\x96\xB1"	=> "\x72",
		"\xF0\x9D\x96\xB2"	=> "\x73",
		"\xF0\x9D\x96\xB3"	=> "\x74",
		"\xF0\x9D\x96\xB4"	=> "\x75",
		"\xF0\x9D\x96\xB5"	=> "\x76",
		"\xF0\x9D\x96\xB6"	=> "\x77",
		"\xF0\x9D\x96\xB7"	=> "\x78",
		"\xF0\x9D\x96\xB8"	=> "\x79",
		"\xF0\x9D\x96\xB9"	=> "\x7A",
		"\xF0\x9D\x97\x94"	=> "\x61",
		"\xF0\x9D\x97\x95"	=> "\x62",
		"\xF0\x9D\x97\x96"	=> "\x63",
		"\xF0\x9D\x97\x97"	=> "\x64",
		"\xF0\x9D\x97\x98"	=> "\x65",
		"\xF0\x9D\x97\x99"	=> "\x66",
		"\xF0\x9D\x97\x9A"	=> "\x67",
		"\xF0\x9D\x97\x9B"	=> "\x68",
		"\xF0\x9D\x97\x9C"	=> "\x69",
		"\xF0\x9D\x97\x9D"	=> "\x6A",
		"\xF0\x9D\x97\x9E"	=> "\x6B",
		"\xF0\x9D\x97\x9F"	=> "\x6C",
		"\xF0\x9D\x97\xA0"	=> "\x6D",
		"\xF0\x9D\x97\xA1"	=> "\x6E",
		"\xF0\x9D\x97\xA2"	=> "\x6F",
		"\xF0\x9D\x97\xA3"	=> "\x70",
		"\xF0\x9D\x97\xA4"	=> "\x71",
		"\xF0\x9D\x97\xA5"	=> "\x72",
		"\xF0\x9D\x97\xA6"	=> "\x73",
		"\xF0\x9D\x97\xA7"	=> "\x74",
		"\xF0\x9D\x97\xA8"	=> "\x75",
		"\xF0\x9D\x97\xA9"	=> "\x76",
		"\xF0\x9D\x97\xAA"	=> "\x77",
		"\xF0\x9D\x97\xAB"	=> "\x78",
		"\xF0\x9D\x97\xAC"	=> "\x79",
		"\xF0\x9D\x97\xAD"	=> "\x7A",
		"\xF0\x9D\x98\x88"	=> "\x61",
		"\xF0\x9D\x98\x89"	=> "\x62",
		"\xF0\x9D\x98\x8A"	=> "\x63",
		"\xF0\x9D\x98\x8B"	=> "\x64",
		"\xF0\x9D\x98\x8C"	=> "\x65",
		"\xF0\x9D\x98\x8D"	=> "\x66",
		"\xF0\x9D\x98\x8E"	=> "\x67",
		"\xF0\x9D\x98\x8F"	=> "\x68",
		"\xF0\x9D\x98\x90"	=> "\x69",
		"\xF0\x9D\x98\x91"	=> "\x6A",
		"\xF0\x9D\x98\x92"	=> "\x6B",
		"\xF0\x9D\x98\x93"	=> "\x6C",
		"\xF0\x9D\x98\x94"	=> "\x6D",
		"\xF0\x9D\x98\x95"	=> "\x6E",
		"\xF0\x9D\x98\x96"	=> "\x6F",
		"\xF0\x9D\x98\x97"	=> "\x70",
		"\xF0\x9D\x98\x98"	=> "\x71",
		"\xF0\x9D\x98\x99"	=> "\x72",
		"\xF0\x9D\x98\x9A"	=> "\x73",
		"\xF0\x9D\x98\x9B"	=> "\x74",
		"\xF0\x9D\x98\x9C"	=> "\x75",
		"\xF0\x9D\x98\x9D"	=> "\x76",
		"\xF0\x9D\x98\x9E"	=> "\x77",
		"\xF0\x9D\x98\x9F"	=> "\x78",
		"\xF0\x9D\x98\xA0"	=> "\x79",
		"\xF0\x9D\x98\xA1"	=> "\x7A",
		"\xF0\x9D\x98\xBC"	=> "\x61",
		"\xF0\x9D\x98\xBD"	=> "\x62",
		"\xF0\x9D\x98\xBE"	=> "\x63",
		"\xF0\x9D\x98\xBF"	=> "\x64",
		"\xF0\x9D\x99\x80"	=> "\x65",
		"\xF0\x9D\x99\x81"	=> "\x66",
		"\xF0\x9D\x99\x82"	=> "\x67",
		"\xF0\x9D\x99\x83"	=> "\x68",
		"\xF0\x9D\x99\x84"	=> "\x69",
		"\xF0\x9D\x99\x85"	=> "\x6A",
		"\xF0\x9D\x99\x86"	=> "\x6B",
		"\xF0\x9D\x99\x87"	=> "\x6C",
		"\xF0\x9D\x99\x88"	=> "\x6D",
		"\xF0\x9D\x99\x89"	=> "\x6E",
		"\xF0\x9D\x99\x8A"	=> "\x6F",
		"\xF0\x9D\x99\x8B"	=> "\x70",
		"\xF0\x9D\x99\x8C"	=> "\x71",
		"\xF0\x9D\x99\x8D"	=> "\x72",
		"\xF0\x9D\x99\x8E"	=> "\x73",
		"\xF0\x9D\x99\x8F"	=> "\x74",
		"\xF0\x9D\x99\x90"	=> "\x75",
		"\xF0\x9D\x99\x91"	=> "\x76",
		"\xF0\x9D\x99\x92"	=> "\x77",
		"\xF0\x9D\x99\x93"	=> "\x78",
		"\xF0\x9D\x99\x94"	=> "\x79",
		"\xF0\x9D\x99\x95"	=> "\x7A",
		"\xF0\x9D\x99\xB0"	=> "\x61",
		"\xF0\x9D\x99\xB1"	=> "\x62",
		"\xF0\x9D\x99\xB2"	=> "\x63",
		"\xF0\x9D\x99\xB3"	=> "\x64",
		"\xF0\x9D\x99\xB4"	=> "\x65",
		"\xF0\x9D\x99\xB5"	=> "\x66",
		"\xF0\x9D\x99\xB6"	=> "\x67",
		"\xF0\x9D\x99\xB7"	=> "\x68",
		"\xF0\x9D\x99\xB8"	=> "\x69",
		"\xF0\x9D\x99\xB9"	=> "\x6A",
		"\xF0\x9D\x99\xBA"	=> "\x6B",
		"\xF0\x9D\x99\xBB"	=> "\x6C",
		"\xF0\x9D\x99\xBC"	=> "\x6D",
		"\xF0\x9D\x99\xBD"	=> "\x6E",
		"\xF0\x9D\x99\xBE"	=> "\x6F",
		"\xF0\x9D\x99\xBF"	=> "\x70",
		"\xF0\x9D\x9A\x80"	=> "\x71",
		"\xF0\x9D\x9A\x81"	=> "\x72",
		"\xF0\x9D\x9A\x82"	=> "\x73",
		"\xF0\x9D\x9A\x83"	=> "\x74",
		"\xF0\x9D\x9A\x84"	=> "\x75",
		"\xF0\x9D\x9A\x85"	=> "\x76",
		"\xF0\x9D\x9A\x86"	=> "\x77",
		"\xF0\x9D\x9A\x87"	=> "\x78",
		"\xF0\x9D\x9A\x88"	=> "\x79",
		"\xF0\x9D\x9A\x89"	=> "\x7A",
		"\xF0\x9D\x9A\xA8"	=> "\xCE\xB1",
		"\xF0\x9D\x9A\xA9"	=> "\xCE\xB2",
		"\xF0\x9D\x9A\xAA"	=> "\xCE\xB3",
		"\xF0\x9D\x9A\xAB"	=> "\xCE\xB4",
		"\xF0\x9D\x9A\xAC"	=> "\xCE\xB5",
		"\xF0\x9D\x9A\xAD"	=> "\xCE\xB6",
		"\xF0\x9D\x9A\xAE"	=> "\xCE\xB7",
		"\xF0\x9D\x9A\xAF"	=> "\xCE\xB8",
		"\xF0\x9D\x9A\xB0"	=> "\xCE\xB9",
		"\xF0\x9D\x9A\xB1"	=> "\xCE\xBA",
		"\xF0\x9D\x9A\xB2"	=> "\xCE\xBB",
		"\xF0\x9D\x9A\xB3"	=> "\xCE\xBC",
		"\xF0\x9D\x9A\xB4"	=> "\xCE\xBD",
		"\xF0\x9D\x9A\xB5"	=> "\xCE\xBE",
		"\xF0\x9D\x9A\xB6"	=> "\xCE\xBF",
		"\xF0\x9D\x9A\xB7"	=> "\xCF\x80",
		"\xF0\x9D\x9A\xB8"	=> "\xCF\x81",
		"\xF0\x9D\x9A\xB9"	=> "\xCE\xB8",
		"\xF0\x9D\x9A\xBA"	=> "\xCF\x83",
		"\xF0\x9D\x9A\xBB"	=> "\xCF\x84",
		"\xF0\x9D\x9A\xBC"	=> "\xCF\x85",
		"\xF0\x9D\x9A\xBD"	=> "\xCF\x86",
		"\xF0\x9D\x9A\xBE"	=> "\xCF\x87",
		"\xF0\x9D\x9A\xBF"	=> "\xCF\x88",
		"\xF0\x9D\x9B\x80"	=> "\xCF\x89",
		"\xF0\x9D\x9B\x93"	=> "\xCF\x83",
		"\xF0\x9D\x9B\xA2"	=> "\xCE\xB1",
		"\xF0\x9D\x9B\xA3"	=> "\xCE\xB2",
		"\xF0\x9D\x9B\xA4"	=> "\xCE\xB3",
		"\xF0\x9D\x9B\xA5"	=> "\xCE\xB4",
		"\xF0\x9D\x9B\xA6"	=> "\xCE\xB5",
		"\xF0\x9D\x9B\xA7"	=> "\xCE\xB6",
		"\xF0\x9D\x9B\xA8"	=> "\xCE\xB7",
		"\xF0\x9D\x9B\xA9"	=> "\xCE\xB8",
		"\xF0\x9D\x9B\xAA"	=> "\xCE\xB9",
		"\xF0\x9D\x9B\xAB"	=> "\xCE\xBA",
		"\xF0\x9D\x9B\xAC"	=> "\xCE\xBB",
		"\xF0\x9D\x9B\xAD"	=> "\xCE\xBC",
		"\xF0\x9D\x9B\xAE"	=> "\xCE\xBD",
		"\xF0\x9D\x9B\xAF"	=> "\xCE\xBE",
		"\xF0\x9D\x9B\xB0"	=> "\xCE\xBF",
		"\xF0\x9D\x9B\xB1"	=> "\xCF\x80",
		"\xF0\x9D\x9B\xB2"	=> "\xCF\x81",
		"\xF0\x9D\x9B\xB3"	=> "\xCE\xB8",
		"\xF0\x9D\x9B\xB4"	=> "\xCF\x83",
		"\xF0\x9D\x9B\xB5"	=> "\xCF\x84",
		"\xF0\x9D\x9B\xB6"	=> "\xCF\x85",
		"\xF0\x9D\x9B\xB7"	=> "\xCF\x86",
		"\xF0\x9D\x9B\xB8"	=> "\xCF\x87",
		"\xF0\x9D\x9B\xB9"	=> "\xCF\x88",
		"\xF0\x9D\x9B\xBA"	=> "\xCF\x89",
		"\xF0\x9D\x9C\x8D"	=> "\xCF\x83",
		"\xF0\x9D\x9C\x9C"	=> "\xCE\xB1",
		"\xF0\x9D\x9C\x9D"	=> "\xCE\xB2",
		"\xF0\x9D\x9C\x9E"	=> "\xCE\xB3",
		"\xF0\x9D\x9C\x9F"	=> "\xCE\xB4",
		"\xF0\x9D\x9C\xA0"	=> "\xCE\xB5",
		"\xF0\x9D\x9C\xA1"	=> "\xCE\xB6",
		"\xF0\x9D\x9C\xA2"	=> "\xCE\xB7",
		"\xF0\x9D\x9C\xA3"	=> "\xCE\xB8",
		"\xF0\x9D\x9C\xA4"	=> "\xCE\xB9",
		"\xF0\x9D\x9C\xA5"	=> "\xCE\xBA",
		"\xF0\x9D\x9C\xA6"	=> "\xCE\xBB",
		"\xF0\x9D\x9C\xA7"	=> "\xCE\xBC",
		"\xF0\x9D\x9C\xA8"	=> "\xCE\xBD",
		"\xF0\x9D\x9C\xA9"	=> "\xCE\xBE",
		"\xF0\x9D\x9C\xAA"	=> "\xCE\xBF",
		"\xF0\x9D\x9C\xAB"	=> "\xCF\x80",
		"\xF0\x9D\x9C\xAC"	=> "\xCF\x81",
		"\xF0\x9D\x9C\xAD"	=> "\xCE\xB8",
		"\xF0\x9D\x9C\xAE"	=> "\xCF\x83",
		"\xF0\x9D\x9C\xAF"	=> "\xCF\x84",
		"\xF0\x9D\x9C\xB0"	=> "\xCF\x85",
		"\xF0\x9D\x9C\xB1"	=> "\xCF\x86",
		"\xF0\x9D\x9C\xB2"	=> "\xCF\x87",
		"\xF0\x9D\x9C\xB3"	=> "\xCF\x88",
		"\xF0\x9D\x9C\xB4"	=> "\xCF\x89",
		"\xF0\x9D\x9D\x87"	=> "\xCF\x83",
		"\xF0\x9D\x9D\x96"	=> "\xCE\xB1",
		"\xF0\x9D\x9D\x97"	=> "\xCE\xB2",
		"\xF0\x9D\x9D\x98"	=> "\xCE\xB3",
		"\xF0\x9D\x9D\x99"	=> "\xCE\xB4",
		"\xF0\x9D\x9D\x9A"	=> "\xCE\xB5",
		"\xF0\x9D\x9D\x9B"	=> "\xCE\xB6",
		"\xF0\x9D\x9D\x9C"	=> "\xCE\xB7",
		"\xF0\x9D\x9D\x9D"	=> "\xCE\xB8",
		"\xF0\x9D\x9D\x9E"	=> "\xCE\xB9",
		"\xF0\x9D\x9D\x9F"	=> "\xCE\xBA",
		"\xF0\x9D\x9D\xA0"	=> "\xCE\xBB",
		"\xF0\x9D\x9D\xA1"	=> "\xCE\xBC",
		"\xF0\x9D\x9D\xA2"	=> "\xCE\xBD",
		"\xF0\x9D\x9D\xA3"	=> "\xCE\xBE",
		"\xF0\x9D\x9D\xA4"	=> "\xCE\xBF",
		"\xF0\x9D\x9D\xA5"	=> "\xCF\x80",
		"\xF0\x9D\x9D\xA6"	=> "\xCF\x81",
		"\xF0\x9D\x9D\xA7"	=> "\xCE\xB8",
		"\xF0\x9D\x9D\xA8"	=> "\xCF\x83",
		"\xF0\x9D\x9D\xA9"	=> "\xCF\x84",
		"\xF0\x9D\x9D\xAA"	=> "\xCF\x85",
		"\xF0\x9D\x9D\xAB"	=> "\xCF\x86",
		"\xF0\x9D\x9D\xAC"	=> "\xCF\x87",
		"\xF0\x9D\x9D\xAD"	=> "\xCF\x88",
		"\xF0\x9D\x9D\xAE"	=> "\xCF\x89",
		"\xF0\x9D\x9E\x81"	=> "\xCF\x83",
		"\xF0\x9D\x9E\x90"	=> "\xCE\xB1",
		"\xF0\x9D\x9E\x91"	=> "\xCE\xB2",
		"\xF0\x9D\x9E\x92"	=> "\xCE\xB3",
		"\xF0\x9D\x9E\x93"	=> "\xCE\xB4",
		"\xF0\x9D\x9E\x94"	=> "\xCE\xB5",
		"\xF0\x9D\x9E\x95"	=> "\xCE\xB6",
		"\xF0\x9D\x9E\x96"	=> "\xCE\xB7",
		"\xF0\x9D\x9E\x97"	=> "\xCE\xB8",
		"\xF0\x9D\x9E\x98"	=> "\xCE\xB9",
		"\xF0\x9D\x9E\x99"	=> "\xCE\xBA",
		"\xF0\x9D\x9E\x9A"	=> "\xCE\xBB",
		"\xF0\x9D\x9E\x9B"	=> "\xCE\xBC",
		"\xF0\x9D\x9E\x9C"	=> "\xCE\xBD",
		"\xF0\x9D\x9E\x9D"	=> "\xCE\xBE",
		"\xF0\x9D\x9E\x9E"	=> "\xCE\xBF",
		"\xF0\x9D\x9E\x9F"	=> "\xCF\x80",
		"\xF0\x9D\x9E\xA0"	=> "\xCF\x81",
		"\xF0\x9D\x9E\xA1"	=> "\xCE\xB8",
		"\xF0\x9D\x9E\xA2"	=> "\xCF\x83",
		"\xF0\x9D\x9E\xA3"	=> "\xCF\x84",
		"\xF0\x9D\x9E\xA4"	=> "\xCF\x85",
		"\xF0\x9D\x9E\xA5"	=> "\xCF\x86",
		"\xF0\x9D\x9E\xA6"	=> "\xCF\x87",
		"\xF0\x9D\x9E\xA7"	=> "\xCF\x88",
		"\xF0\x9D\x9E\xA8"	=> "\xCF\x89",
		"\xF0\x9D\x9E\xBB"	=> "\xCF\x83",
		"\xF0\x9D\x9F\x8A"	=> "\xCF\x9D",
	);

	// do the case fold
	$text = utf8_case_fold($text, $option);

	// convert to NFKC
	Normalizer::normalize($text, Normalizer::NFKC);

	// FC_NFKC_Closure, http://www.unicode.org/Public/5.0.0/ucd/DerivedNormalizationProps.txt
	$text = strtr($text, $fc_nfkc_closure);

	return $text;
}

/**
* Assume the input is NFC:
* Takes the input and does a "special" case fold. It does minor normalization as well.
*
* @param	string	$text	text to be case folded
* @param	string	$option	determines how we will fold the cases
* @return	string			case folded text
*/
function utf8_case_fold_nfc($text, $option = 'full')
{
	static $uniarray = array();
	static $ypogegrammeni = array(
		"\xCD\xBA"		=> "\x20\xCD\x85",
		"\xE1\xBE\x80"	=> "\xE1\xBC\x80\xCD\x85",
		"\xE1\xBE\x81"	=> "\xE1\xBC\x81\xCD\x85",
		"\xE1\xBE\x82"	=> "\xE1\xBC\x82\xCD\x85",
		"\xE1\xBE\x83"	=> "\xE1\xBC\x83\xCD\x85",
		"\xE1\xBE\x84"	=> "\xE1\xBC\x84\xCD\x85",
		"\xE1\xBE\x85"	=> "\xE1\xBC\x85\xCD\x85",
		"\xE1\xBE\x86"	=> "\xE1\xBC\x86\xCD\x85",
		"\xE1\xBE\x87"	=> "\xE1\xBC\x87\xCD\x85",
		"\xE1\xBE\x88"	=> "\xE1\xBC\x88\xCD\x85",
		"\xE1\xBE\x89"	=> "\xE1\xBC\x89\xCD\x85",
		"\xE1\xBE\x8A"	=> "\xE1\xBC\x8A\xCD\x85",
		"\xE1\xBE\x8B"	=> "\xE1\xBC\x8B\xCD\x85",
		"\xE1\xBE\x8C"	=> "\xE1\xBC\x8C\xCD\x85",
		"\xE1\xBE\x8D"	=> "\xE1\xBC\x8D\xCD\x85",
		"\xE1\xBE\x8E"	=> "\xE1\xBC\x8E\xCD\x85",
		"\xE1\xBE\x8F"	=> "\xE1\xBC\x8F\xCD\x85",
		"\xE1\xBE\x90"	=> "\xE1\xBC\xA0\xCD\x85",
		"\xE1\xBE\x91"	=> "\xE1\xBC\xA1\xCD\x85",
		"\xE1\xBE\x92"	=> "\xE1\xBC\xA2\xCD\x85",
		"\xE1\xBE\x93"	=> "\xE1\xBC\xA3\xCD\x85",
		"\xE1\xBE\x94"	=> "\xE1\xBC\xA4\xCD\x85",
		"\xE1\xBE\x95"	=> "\xE1\xBC\xA5\xCD\x85",
		"\xE1\xBE\x96"	=> "\xE1\xBC\xA6\xCD\x85",
		"\xE1\xBE\x97"	=> "\xE1\xBC\xA7\xCD\x85",
		"\xE1\xBE\x98"	=> "\xE1\xBC\xA8\xCD\x85",
		"\xE1\xBE\x99"	=> "\xE1\xBC\xA9\xCD\x85",
		"\xE1\xBE\x9A"	=> "\xE1\xBC\xAA\xCD\x85",
		"\xE1\xBE\x9B"	=> "\xE1\xBC\xAB\xCD\x85",
		"\xE1\xBE\x9C"	=> "\xE1\xBC\xAC\xCD\x85",
		"\xE1\xBE\x9D"	=> "\xE1\xBC\xAD\xCD\x85",
		"\xE1\xBE\x9E"	=> "\xE1\xBC\xAE\xCD\x85",
		"\xE1\xBE\x9F"	=> "\xE1\xBC\xAF\xCD\x85",
		"\xE1\xBE\xA0"	=> "\xE1\xBD\xA0\xCD\x85",
		"\xE1\xBE\xA1"	=> "\xE1\xBD\xA1\xCD\x85",
		"\xE1\xBE\xA2"	=> "\xE1\xBD\xA2\xCD\x85",
		"\xE1\xBE\xA3"	=> "\xE1\xBD\xA3\xCD\x85",
		"\xE1\xBE\xA4"	=> "\xE1\xBD\xA4\xCD\x85",
		"\xE1\xBE\xA5"	=> "\xE1\xBD\xA5\xCD\x85",
		"\xE1\xBE\xA6"	=> "\xE1\xBD\xA6\xCD\x85",
		"\xE1\xBE\xA7"	=> "\xE1\xBD\xA7\xCD\x85",
		"\xE1\xBE\xA8"	=> "\xE1\xBD\xA8\xCD\x85",
		"\xE1\xBE\xA9"	=> "\xE1\xBD\xA9\xCD\x85",
		"\xE1\xBE\xAA"	=> "\xE1\xBD\xAA\xCD\x85",
		"\xE1\xBE\xAB"	=> "\xE1\xBD\xAB\xCD\x85",
		"\xE1\xBE\xAC"	=> "\xE1\xBD\xAC\xCD\x85",
		"\xE1\xBE\xAD"	=> "\xE1\xBD\xAD\xCD\x85",
		"\xE1\xBE\xAE"	=> "\xE1\xBD\xAE\xCD\x85",
		"\xE1\xBE\xAF"	=> "\xE1\xBD\xAF\xCD\x85",
		"\xE1\xBE\xB2"	=> "\xE1\xBD\xB0\xCD\x85",
		"\xE1\xBE\xB3"	=> "\xCE\xB1\xCD\x85",
		"\xE1\xBE\xB4"	=> "\xCE\xAC\xCD\x85",
		"\xE1\xBE\xB7"	=> "\xE1\xBE\xB6\xCD\x85",
		"\xE1\xBE\xBC"	=> "\xCE\x91\xCD\x85",
		"\xE1\xBF\x82"	=> "\xE1\xBD\xB4\xCD\x85",
		"\xE1\xBF\x83"	=> "\xCE\xB7\xCD\x85",
		"\xE1\xBF\x84"	=> "\xCE\xAE\xCD\x85",
		"\xE1\xBF\x87"	=> "\xE1\xBF\x86\xCD\x85",
		"\xE1\xBF\x8C"	=> "\xCE\x97\xCD\x85",
		"\xE1\xBF\xB2"	=> "\xE1\xBD\xBC\xCD\x85",
		"\xE1\xBF\xB3"	=> "\xCF\x89\xCD\x85",
		"\xE1\xBF\xB4"	=> "\xCF\x8E\xCD\x85",
		"\xE1\xBF\xB7"	=> "\xE1\xBF\xB6\xCD\x85",
		"\xE1\xBF\xBC"	=> "\xCE\xA9\xCD\x85",
	);

	// perform a small trick, avoid further normalization on composed points that contain U+0345 in their decomposition
	$text = strtr($text, $ypogegrammeni);

	// do the case fold
	$text = utf8_case_fold($text, $option);

	return $text;
}

/**
* wrapper around PHP's native normalizer from intl
* previously a PECL extension, included in the core since PHP 5.3.0
* http://php.net/manual/en/normalizer.normalize.php
*
* @param	mixed	$strings	a string or an array of strings to normalize
* @return	mixed				the normalized content, preserving array keys if array given.
*/
function utf8_normalize_nfc($strings)
{
	if (empty($strings))
	{
		return $strings;
	}

	if (!is_array($strings))
	{
		if (Normalizer::isNormalized($strings))
		{
			return $strings;
		}
		return (string) Normalizer::normalize($strings);
	}
	else
	{
		foreach ($strings as $key => $string)
		{
			if (is_array($string))
			{
				foreach ($string as $_key => $_string)
				{
					if (Normalizer::isNormalized($strings[$key][$_key]))
					{
						continue;
					}
					$strings[$key][$_key] = (string) Normalizer::normalize($strings[$key][$_key]);
				}
			}
			else
			{
				if (Normalizer::isNormalized($strings[$key]))
				{
					continue;
				}
				$strings[$key] = (string) Normalizer::normalize($strings[$key]);
			}
		}
	}

	return $strings;
}

/**
* This function is used to generate a "clean" version of a string.
* Clean means that it is a case insensitive form (case folding) and that it is normalized (NFC).
* Additionally a homographs of one character are transformed into one specific character (preferably ASCII
* if it is an ASCII character).
*
* Please be aware that if you change something within this function or within
* functions used here you need to rebuild/update the username_clean column in the users table. And all other
* columns that store a clean string otherwise you will break this functionality.
*
* @param	string	$text	An unclean string, mabye user input (has to be valid UTF-8!)
* @return	string			Cleaned up version of the input string
*/
function utf8_clean_string($text)
{
	global $phpbb_root_path, $phpEx;

	static $homographs = array();
	if (empty($homographs))
	{
		$homographs = include($phpbb_root_path . 'includes/utf/data/confusables.' . $phpEx);
	}

	$text = utf8_case_fold_nfkc($text);
	$text = strtr($text, $homographs);
	// Other control characters
	$text = preg_replace('#(?:[\x00-\x1F\x7F]+|(?:\xC2[\x80-\x9F])+)#', '', $text);

	// we need to reduce multiple spaces to a single one
	$text = preg_replace('# {2,}#', ' ', $text);

	// we can use trim here as all the other space characters should have been turned
	// into normal ASCII spaces by now
	return trim($text);
}

/**
* A wrapper for htmlspecialchars($value, ENT_COMPAT, 'UTF-8')
*/
function utf8_htmlspecialchars($value)
{
	return htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
}

/**
* Trying to convert returned system message to utf8
*
* PHP assumes such messages are ISO-8859-1 so we'll do that too
* and if it breaks messages we'll blame it on them ;-)
*/
function utf8_convert_message($message)
{
	// First of all check if conversion is neded at all, as there is no point
	// in converting ASCII messages from ISO-8859-1 to UTF-8
	if (!preg_match('/[\x80-\xFF]/', $message))
	{
		return utf8_htmlspecialchars($message);
	}

	// else we need to convert some part of the message
	return utf8_htmlspecialchars(utf8_recode($message, 'ISO-8859-1'));
}

/**
* UTF8-compatible wordwrap replacement
*
* @param	string	$string	The input string
* @param	int		$width	The column width. Defaults to 75.
* @param	string	$break	The line is broken using the optional break parameter. Defaults to '\n'.
* @param	bool	$cut	If the cut is set to TRUE, the string is always wrapped at the specified width. So if you have a word that is larger than the given width, it is broken apart.
*
* @return	string			the given string wrapped at the specified column.
*
*/
function utf8_wordwrap($string, $width = 75, $break = "\n", $cut = false)
{
	// We first need to explode on $break, not destroying existing (intended) breaks
	$lines = explode($break, $string);
	$new_lines = array(0 => '');
	$index = 0;

	foreach ($lines as $line)
	{
		$words = explode(' ', $line);

		for ($i = 0, $size = count($words); $i < $size; $i++)
		{
			$word = $words[$i];

			// If cut is true we need to cut the word if it is > width chars
			if ($cut && utf8_strlen($word) > $width)
			{
				$words[$i] = utf8_substr($word, $width);
				$word = utf8_substr($word, 0, $width);
				$i--;
			}

			if (utf8_strlen($new_lines[$index] . $word) > $width)
			{
				$new_lines[$index] = substr($new_lines[$index], 0, -1);
				$index++;
				$new_lines[$index] = '';
			}

			$new_lines[$index] .= $word . ' ';
		}

		$new_lines[$index] = substr($new_lines[$index], 0, -1);
		$index++;
		$new_lines[$index] = '';
	}

	unset($new_lines[$index]);
	return implode($break, $new_lines);
}

/**
* UTF8-safe basename() function
*
* basename() has some limitations and is dependent on the locale setting
* according to the PHP manual. Therefore we provide our own locale independent
* basename function.
*
* @param string $filename The filename basename() should be applied to
* @return string The basenamed filename
*/
function utf8_basename($filename)
{
	// We always check for forward slash AND backward slash
	// because they could be mixed or "sneaked" in. ;)
	// You know, never trust user input...
	if (strpos($filename, '/') !== false)
	{
		$filename = utf8_substr($filename, utf8_strrpos($filename, '/') + 1);
	}

	if (strpos($filename, '\\') !== false)
	{
		$filename = utf8_substr($filename, utf8_strrpos($filename, '\\') + 1);
	}

	return $filename;
}
