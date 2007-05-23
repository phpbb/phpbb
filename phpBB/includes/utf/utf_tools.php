<?php
/** 
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2006 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
* @todo make sure the replacements are called correctly
* already done: strtolower, strtoupper, ucfirst, str_split, strrpos, strlen (hopefully!), strpos, substr, htmlspecialchars
* remaining:	strspn, chr, ord
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
* UTF-8 tools
*
* Whenever possible, these functions will try to use PHP's built-in functions or
* extensions, otherwise they will default to custom routines.
*
* @package phpBB3
*/

if (!extension_loaded('xml'))
{
	/**
	* Implementation of PHP's native utf8_encode for people without XML support
	* This function exploits some nice things that ISO-8859-1 and UTF-8 have in common
	*
	* @param string $str ISO-8859-1 encoded data
	* @return string UTF-8 encoded data
	*/
	function utf8_encode($str)
	{
		$out = '';
		for ($i = 0, $len = strlen($str); $i < $len; $i++)
		{
			$letter = $str[$i];
			$num = ord($letter);
			if ($num < 0x80)
			{
				$out .= $letter;
			}
			else if ($num < 0xC0)
			{
				$out .= "\xC2" . $letter;
			}
			else
			{
				$out .= "\xC3" . chr($num - 64);
			}
		}
		return $out;
	}

	/**
	* Implementation of PHP's native utf8_decode for people without XML support
	*
	* @param string $str UTF-8 encoded data
	* @return string ISO-8859-1 encoded data
	*/
	function utf8_decode($str)
	{
		$pos = 0;
		$len = strlen($str);
		$ret = '';
	
		while ($pos < $len)
		{
			$ord = ord($str[$pos]) & 0xF0;
			if ($ord === 0xC0 || $ord === 0xD0)
			{
				$charval = ((ord($str[$pos]) & 0x1F) << 6) | (ord($str[$pos + 1]) & 0x3F);
				$pos += 2;
				$ret .= (($charval < 256) ? chr($charval) : '?');
			}
			else if ($ord === 0xE0)
			{
				$ret .= '?';
				$pos += 3;
			}
			else if ($ord === 0xF0)
			{
				$ret .= '?';
				$pos += 4;
			}
			else
			{
				$ret .= $str[$pos];
				++$pos;
			}
		}
		return $ret;
	}
}

// mbstring is old and has it's functions around for older versions of PHP.
// if mbstring is not loaded, we go into native mode.
if (extension_loaded('mbstring'))
{
	mb_internal_encoding('UTF-8');

	/**
	* UTF-8 aware alternative to strrpos
	* Find position of last occurrence of a char in a string
	*
	* Notes:
	* - offset for mb_strrpos was added in 5.2.0, we emulate if it is lower
	*/
	if (version_compare(PHP_VERSION, '5.2.0', '>='))
	{
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
	}
	else
	{
		/**
		* UTF-8 aware alternative to strrpos
		* @ignore
		*/
		function utf8_strrpos($str,	$needle, $offset = null)
		{
			// offset for mb_strrpos was added in 5.2.0
			if (is_null($offset))
			{
				// Emulate behaviour of strrpos rather than raising warning
				if (empty($str))
				{
					return false;
				}

				return mb_strrpos($str, $needle);
			}
			else
			{
				if (!is_int($offset))
				{
					trigger_error('utf8_strrpos expects parameter 3 to be long', E_USER_ERROR);
					return false;
				}

				$str = mb_substr($str, $offset);

				if (false !== ($pos = mb_strrpos($str, $needle)))
				{
					return $pos + $offset;
				}

				return false;
			}
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
}
else
{
	/**
	* UTF-8 aware alternative to strrpos
	* Find position of last occurrence of a char in a string
	* 
	* @author Harry Fuecks
	* @param string $str haystack
	* @param string $needle needle
	* @param integer $offset (optional) offset (from left)
	* @return mixed integer position or FALSE on failure
	*/
	function utf8_strrpos($str,	$needle, $offset = null)
	{
		if (is_null($offset))
		{
			$ar	= explode($needle, $str);
			
			if (sizeof($ar) > 1)
			{
				// Pop off the end of the string where the last	match was made
				array_pop($ar);
				$str = join($needle, $ar);

				return utf8_strlen($str);
			}
			return false;
		}
		else
		{
			if (!is_int($offset))
			{
				trigger_error('utf8_strrpos	expects	parameter 3	to be long', E_USER_ERROR);
				return false;
			}

			$str = utf8_substr($str, $offset);

			if (false !== ($pos = utf8_strrpos($str, $needle)))
			{
				return $pos	+ $offset;
			}

			return false;
		}
	}

	/**
	* UTF-8 aware alternative to strpos
	* Find position of first occurrence of a string
	*
	* @author Harry Fuecks
	* @param string $str haystack
	* @param string $needle needle
	* @param integer $offset offset in characters (from left)
	* @return mixed integer position or FALSE on failure
	*/
	function utf8_strpos($str, $needle, $offset = null)
	{
		if (is_null($offset))
		{
			$ar = explode($needle, $str);
			if (sizeof($ar) > 1)
			{
				return utf8_strlen($ar[0]);
			}
			return false;
		}
		else
		{
			if (!is_int($offset))
			{
				trigger_error('utf8_strpos:  Offset must  be an integer', E_USER_ERROR);
				return false;
			}

			$str = utf8_substr($str, $offset);

			if (false !== ($pos = utf8_strpos($str, $needle)))
			{
				return $pos + $offset;
			}

			return false;
		}
	}

	/**
	* UTF-8 aware alternative to strtolower
	* Make a string lowercase
	* Note: The concept of a characters "case" only exists is some alphabets
	* such as Latin, Greek, Cyrillic, Armenian and archaic Georgian - it does
	* not exist in the Chinese alphabet, for example. See Unicode Standard
	* Annex #21: Case Mappings
	* 
	* @param string
	* @return string string in lowercase
	*/
	function utf8_strtolower($string)
	{
		static $UTF8_UPPER_TO_LOWER = array(
			"\xC3\x80" => "\xC3\xA0", "\xC3\x81" => "\xC3\xA1",
			"\xC3\x82" => "\xC3\xA2", "\xC3\x83" => "\xC3\xA3", "\xC3\x84" => "\xC3\xA4", "\xC3\x85" => "\xC3\xA5",
			"\xC3\x86" => "\xC3\xA6", "\xC3\x87" => "\xC3\xA7", "\xC3\x88" => "\xC3\xA8", "\xC3\x89" => "\xC3\xA9",
			"\xC3\x8A" => "\xC3\xAA", "\xC3\x8B" => "\xC3\xAB", "\xC3\x8C" => "\xC3\xAC", "\xC3\x8D" => "\xC3\xAD",
			"\xC3\x8E" => "\xC3\xAE", "\xC3\x8F" => "\xC3\xAF", "\xC3\x90" => "\xC3\xB0", "\xC3\x91" => "\xC3\xB1",
			"\xC3\x92" => "\xC3\xB2", "\xC3\x93" => "\xC3\xB3", "\xC3\x94" => "\xC3\xB4", "\xC3\x95" => "\xC3\xB5",
			"\xC3\x96" => "\xC3\xB6", "\xC3\x98" => "\xC3\xB8", "\xC3\x99" => "\xC3\xB9", "\xC3\x9A" => "\xC3\xBA",
			"\xC3\x9B" => "\xC3\xBB", "\xC3\x9C" => "\xC3\xBC", "\xC3\x9D" => "\xC3\xBD", "\xC3\x9E" => "\xC3\xBE",
			"\xC4\x80" => "\xC4\x81", "\xC4\x82" => "\xC4\x83", "\xC4\x84" => "\xC4\x85", "\xC4\x86" => "\xC4\x87",
			"\xC4\x88" => "\xC4\x89", "\xC4\x8A" => "\xC4\x8B", "\xC4\x8C" => "\xC4\x8D", "\xC4\x8E" => "\xC4\x8F",
			"\xC4\x90" => "\xC4\x91", "\xC4\x92" => "\xC4\x93", "\xC4\x96" => "\xC4\x97", "\xC4\x98" => "\xC4\x99",
			"\xC4\x9A" => "\xC4\x9B", "\xC4\x9C" => "\xC4\x9D", "\xC4\x9E" => "\xC4\x9F", "\xC4\xA0" => "\xC4\xA1",
			"\xC4\xA2" => "\xC4\xA3", "\xC4\xA4" => "\xC4\xA5", "\xC4\xA6" => "\xC4\xA7", "\xC4\xA8" => "\xC4\xA9",
			"\xC4\xAA" => "\xC4\xAB", "\xC4\xAE" => "\xC4\xAF", "\xC4\xB4" => "\xC4\xB5", "\xC4\xB6" => "\xC4\xB7",
			"\xC4\xB9" => "\xC4\xBA", "\xC4\xBB" => "\xC4\xBC", "\xC4\xBD" => "\xC4\xBE", "\xC5\x81" => "\xC5\x82",
			"\xC5\x83" => "\xC5\x84", "\xC5\x85" => "\xC5\x86", "\xC5\x87" => "\xC5\x88", "\xC5\x8A" => "\xC5\x8B",
			"\xC5\x8C" => "\xC5\x8D", "\xC5\x90" => "\xC5\x91", "\xC5\x94" => "\xC5\x95", "\xC5\x96" => "\xC5\x97",
			"\xC5\x98" => "\xC5\x99", "\xC5\x9A" => "\xC5\x9B", "\xC5\x9C" => "\xC5\x9D", "\xC5\x9E" => "\xC5\x9F",
			"\xC5\xA0" => "\xC5\xA1", "\xC5\xA2" => "\xC5\xA3", "\xC5\xA4" => "\xC5\xA5", "\xC5\xA6" => "\xC5\xA7",
			"\xC5\xA8" => "\xC5\xA9", "\xC5\xAA" => "\xC5\xAB", "\xC5\xAC" => "\xC5\xAD", "\xC5\xAE" => "\xC5\xAF",
			"\xC5\xB0" => "\xC5\xB1", "\xC5\xB2" => "\xC5\xB3", "\xC5\xB4" => "\xC5\xB5", "\xC5\xB6" => "\xC5\xB7",
			"\xC5\xB8" => "\xC3\xBF", "\xC5\xB9" => "\xC5\xBA", "\xC5\xBB" => "\xC5\xBC", "\xC5\xBD" => "\xC5\xBE",
			"\xC6\xA0" => "\xC6\xA1", "\xC6\xAF" => "\xC6\xB0", "\xC8\x98" => "\xC8\x99", "\xC8\x9A" => "\xC8\x9B",
			"\xCE\x86" => "\xCE\xAC", "\xCE\x88" => "\xCE\xAD", "\xCE\x89" => "\xCE\xAE", "\xCE\x8A" => "\xCE\xAF",
			"\xCE\x8C" => "\xCF\x8C", "\xCE\x8E" => "\xCF\x8D", "\xCE\x8F" => "\xCF\x8E", "\xCE\x91" => "\xCE\xB1",
			"\xCE\x92" => "\xCE\xB2", "\xCE\x93" => "\xCE\xB3", "\xCE\x94" => "\xCE\xB4", "\xCE\x95" => "\xCE\xB5",
			"\xCE\x96" => "\xCE\xB6", "\xCE\x97" => "\xCE\xB7", "\xCE\x98" => "\xCE\xB8", "\xCE\x99" => "\xCE\xB9",
			"\xCE\x9A" => "\xCE\xBA", "\xCE\x9B" => "\xCE\xBB", "\xCE\x9C" => "\xCE\xBC", "\xCE\x9D" => "\xCE\xBD",
			"\xCE\x9E" => "\xCE\xBE", "\xCE\x9F" => "\xCE\xBF", "\xCE\xA0" => "\xCF\x80", "\xCE\xA1" => "\xCF\x81",
			"\xCE\xA3" => "\xCF\x83", "\xCE\xA4" => "\xCF\x84", "\xCE\xA5" => "\xCF\x85", "\xCE\xA6" => "\xCF\x86",
			"\xCE\xA7" => "\xCF\x87", "\xCE\xA8" => "\xCF\x88", "\xCE\xA9" => "\xCF\x89", "\xCE\xAA" => "\xCF\x8A",
			"\xCE\xAB" => "\xCF\x8B", "\xD0\x81" => "\xD1\x91", "\xD0\x82" => "\xD1\x92", "\xD0\x83" => "\xD1\x93",
			"\xD0\x84" => "\xD1\x94", "\xD0\x85" => "\xD1\x95", "\xD0\x86" => "\xD1\x96", "\xD0\x87" => "\xD1\x97",
			"\xD0\x88" => "\xD1\x98", "\xD0\x89" => "\xD1\x99", "\xD0\x8A" => "\xD1\x9A", "\xD0\x8B" => "\xD1\x9B",
			"\xD0\x8C" => "\xD1\x9C", "\xD0\x8E" => "\xD1\x9E", "\xD0\x8F" => "\xD1\x9F", "\xD0\x90" => "\xD0\xB0",
			"\xD0\x91" => "\xD0\xB1", "\xD0\x92" => "\xD0\xB2", "\xD0\x93" => "\xD0\xB3", "\xD0\x94" => "\xD0\xB4",
			"\xD0\x95" => "\xD0\xB5", "\xD0\x96" => "\xD0\xB6", "\xD0\x97" => "\xD0\xB7", "\xD0\x98" => "\xD0\xB8",
			"\xD0\x99" => "\xD0\xB9", "\xD0\x9A" => "\xD0\xBA", "\xD0\x9B" => "\xD0\xBB", "\xD0\x9C" => "\xD0\xBC",
			"\xD0\x9D" => "\xD0\xBD", "\xD0\x9E" => "\xD0\xBE", "\xD0\x9F" => "\xD0\xBF", "\xD0\xA0" => "\xD1\x80",
			"\xD0\xA1" => "\xD1\x81", "\xD0\xA2" => "\xD1\x82", "\xD0\xA3" => "\xD1\x83", "\xD0\xA4" => "\xD1\x84",
			"\xD0\xA5" => "\xD1\x85", "\xD0\xA6" => "\xD1\x86", "\xD0\xA7" => "\xD1\x87", "\xD0\xA8" => "\xD1\x88",
			"\xD0\xA9" => "\xD1\x89", "\xD0\xAA" => "\xD1\x8A", "\xD0\xAB" => "\xD1\x8B", "\xD0\xAC" => "\xD1\x8C",
			"\xD0\xAD" => "\xD1\x8D", "\xD0\xAE" => "\xD1\x8E", "\xD0\xAF" => "\xD1\x8F", "\xD2\x90" => "\xD2\x91",
			"\xE1\xB8\x82" => "\xE1\xB8\x83", "\xE1\xB8\x8A" => "\xE1\xB8\x8B", "\xE1\xB8\x9E" => "\xE1\xB8\x9F", "\xE1\xB9\x80" => "\xE1\xB9\x81",
			"\xE1\xB9\x96" => "\xE1\xB9\x97", "\xE1\xB9\xA0" => "\xE1\xB9\xA1", "\xE1\xB9\xAA" => "\xE1\xB9\xAB", "\xE1\xBA\x80" => "\xE1\xBA\x81",
			"\xE1\xBA\x82" => "\xE1\xBA\x83", "\xE1\xBA\x84" => "\xE1\xBA\x85", "\xE1\xBB\xB2" => "\xE1\xBB\xB3"
		);

		return strtr(strtolower($string), $UTF8_UPPER_TO_LOWER);
	}

	/**
	* UTF-8 aware alternative to strtoupper
	* Make a string uppercase
	* Note: The concept of a characters "case" only exists is some alphabets
	* such as Latin, Greek, Cyrillic, Armenian and archaic Georgian - it does
	* not exist in the Chinese alphabet, for example. See Unicode Standard
	* Annex #21: Case Mappings
	* 
	* @param string
	* @return string string in uppercase
	*/
	function utf8_strtoupper($string)
	{
		static $UTF8_LOWER_TO_UPPER = array(
			"\xC3\xA0" => "\xC3\x80", "\xC3\xA1" => "\xC3\x81",
			"\xC3\xA2" => "\xC3\x82", "\xC3\xA3" => "\xC3\x83", "\xC3\xA4" => "\xC3\x84", "\xC3\xA5" => "\xC3\x85",
			"\xC3\xA6" => "\xC3\x86", "\xC3\xA7" => "\xC3\x87", "\xC3\xA8" => "\xC3\x88", "\xC3\xA9" => "\xC3\x89",
			"\xC3\xAA" => "\xC3\x8A", "\xC3\xAB" => "\xC3\x8B", "\xC3\xAC" => "\xC3\x8C", "\xC3\xAD" => "\xC3\x8D",
			"\xC3\xAE" => "\xC3\x8E", "\xC3\xAF" => "\xC3\x8F", "\xC3\xB0" => "\xC3\x90", "\xC3\xB1" => "\xC3\x91",
			"\xC3\xB2" => "\xC3\x92", "\xC3\xB3" => "\xC3\x93", "\xC3\xB4" => "\xC3\x94", "\xC3\xB5" => "\xC3\x95",
			"\xC3\xB6" => "\xC3\x96", "\xC3\xB8" => "\xC3\x98", "\xC3\xB9" => "\xC3\x99", "\xC3\xBA" => "\xC3\x9A",
			"\xC3\xBB" => "\xC3\x9B", "\xC3\xBC" => "\xC3\x9C", "\xC3\xBD" => "\xC3\x9D", "\xC3\xBE" => "\xC3\x9E",
			"\xC3\xBF" => "\xC5\xB8", "\xC4\x81" => "\xC4\x80", "\xC4\x83" => "\xC4\x82", "\xC4\x85" => "\xC4\x84",
			"\xC4\x87" => "\xC4\x86", "\xC4\x89" => "\xC4\x88", "\xC4\x8B" => "\xC4\x8A", "\xC4\x8D" => "\xC4\x8C",
			"\xC4\x8F" => "\xC4\x8E", "\xC4\x91" => "\xC4\x90", "\xC4\x93" => "\xC4\x92", "\xC4\x97" => "\xC4\x96",
			"\xC4\x99" => "\xC4\x98", "\xC4\x9B" => "\xC4\x9A", "\xC4\x9D" => "\xC4\x9C", "\xC4\x9F" => "\xC4\x9E",
			"\xC4\xA1" => "\xC4\xA0", "\xC4\xA3" => "\xC4\xA2", "\xC4\xA5" => "\xC4\xA4", "\xC4\xA7" => "\xC4\xA6",
			"\xC4\xA9" => "\xC4\xA8", "\xC4\xAB" => "\xC4\xAA", "\xC4\xAF" => "\xC4\xAE", "\xC4\xB5" => "\xC4\xB4",
			"\xC4\xB7" => "\xC4\xB6", "\xC4\xBA" => "\xC4\xB9", "\xC4\xBC" => "\xC4\xBB", "\xC4\xBE" => "\xC4\xBD",
			"\xC5\x82" => "\xC5\x81", "\xC5\x84" => "\xC5\x83", "\xC5\x86" => "\xC5\x85", "\xC5\x88" => "\xC5\x87",
			"\xC5\x8B" => "\xC5\x8A", "\xC5\x8D" => "\xC5\x8C", "\xC5\x91" => "\xC5\x90", "\xC5\x95" => "\xC5\x94",
			"\xC5\x97" => "\xC5\x96", "\xC5\x99" => "\xC5\x98", "\xC5\x9B" => "\xC5\x9A", "\xC5\x9D" => "\xC5\x9C",
			"\xC5\x9F" => "\xC5\x9E", "\xC5\xA1" => "\xC5\xA0", "\xC5\xA3" => "\xC5\xA2", "\xC5\xA5" => "\xC5\xA4",
			"\xC5\xA7" => "\xC5\xA6", "\xC5\xA9" => "\xC5\xA8", "\xC5\xAB" => "\xC5\xAA", "\xC5\xAD" => "\xC5\xAC",
			"\xC5\xAF" => "\xC5\xAE", "\xC5\xB1" => "\xC5\xB0", "\xC5\xB3" => "\xC5\xB2", "\xC5\xB5" => "\xC5\xB4",
			"\xC5\xB7" => "\xC5\xB6", "\xC5\xBA" => "\xC5\xB9", "\xC5\xBC" => "\xC5\xBB", "\xC5\xBE" => "\xC5\xBD",
			"\xC6\xA1" => "\xC6\xA0", "\xC6\xB0" => "\xC6\xAF", "\xC8\x99" => "\xC8\x98", "\xC8\x9B" => "\xC8\x9A",
			"\xCE\xAC" => "\xCE\x86", "\xCE\xAD" => "\xCE\x88", "\xCE\xAE" => "\xCE\x89", "\xCE\xAF" => "\xCE\x8A",
			"\xCE\xB1" => "\xCE\x91", "\xCE\xB2" => "\xCE\x92", "\xCE\xB3" => "\xCE\x93", "\xCE\xB4" => "\xCE\x94",
			"\xCE\xB5" => "\xCE\x95", "\xCE\xB6" => "\xCE\x96", "\xCE\xB7" => "\xCE\x97", "\xCE\xB8" => "\xCE\x98",
			"\xCE\xB9" => "\xCE\x99", "\xCE\xBA" => "\xCE\x9A", "\xCE\xBB" => "\xCE\x9B", "\xCE\xBC" => "\xCE\x9C",
			"\xCE\xBD" => "\xCE\x9D", "\xCE\xBE" => "\xCE\x9E", "\xCE\xBF" => "\xCE\x9F", "\xCF\x80" => "\xCE\xA0",
			"\xCF\x81" => "\xCE\xA1", "\xCF\x83" => "\xCE\xA3", "\xCF\x84" => "\xCE\xA4", "\xCF\x85" => "\xCE\xA5",
			"\xCF\x86" => "\xCE\xA6", "\xCF\x87" => "\xCE\xA7", "\xCF\x88" => "\xCE\xA8", "\xCF\x89" => "\xCE\xA9",
			"\xCF\x8A" => "\xCE\xAA", "\xCF\x8B" => "\xCE\xAB", "\xCF\x8C" => "\xCE\x8C", "\xCF\x8D" => "\xCE\x8E",
			"\xCF\x8E" => "\xCE\x8F", "\xD0\xB0" => "\xD0\x90", "\xD0\xB1" => "\xD0\x91", "\xD0\xB2" => "\xD0\x92",
			"\xD0\xB3" => "\xD0\x93", "\xD0\xB4" => "\xD0\x94", "\xD0\xB5" => "\xD0\x95", "\xD0\xB6" => "\xD0\x96",
			"\xD0\xB7" => "\xD0\x97", "\xD0\xB8" => "\xD0\x98", "\xD0\xB9" => "\xD0\x99", "\xD0\xBA" => "\xD0\x9A",
			"\xD0\xBB" => "\xD0\x9B", "\xD0\xBC" => "\xD0\x9C", "\xD0\xBD" => "\xD0\x9D", "\xD0\xBE" => "\xD0\x9E",
			"\xD0\xBF" => "\xD0\x9F", "\xD1\x80" => "\xD0\xA0", "\xD1\x81" => "\xD0\xA1", "\xD1\x82" => "\xD0\xA2",
			"\xD1\x83" => "\xD0\xA3", "\xD1\x84" => "\xD0\xA4", "\xD1\x85" => "\xD0\xA5", "\xD1\x86" => "\xD0\xA6",
			"\xD1\x87" => "\xD0\xA7", "\xD1\x88" => "\xD0\xA8", "\xD1\x89" => "\xD0\xA9", "\xD1\x8A" => "\xD0\xAA",
			"\xD1\x8B" => "\xD0\xAB", "\xD1\x8C" => "\xD0\xAC", "\xD1\x8D" => "\xD0\xAD", "\xD1\x8E" => "\xD0\xAE",
			"\xD1\x8F" => "\xD0\xAF", "\xD1\x91" => "\xD0\x81", "\xD1\x92" => "\xD0\x82", "\xD1\x93" => "\xD0\x83",
			"\xD1\x94" => "\xD0\x84", "\xD1\x95" => "\xD0\x85", "\xD1\x96" => "\xD0\x86", "\xD1\x97" => "\xD0\x87",
			"\xD1\x98" => "\xD0\x88", "\xD1\x99" => "\xD0\x89", "\xD1\x9A" => "\xD0\x8A", "\xD1\x9B" => "\xD0\x8B",
			"\xD1\x9C" => "\xD0\x8C", "\xD1\x9E" => "\xD0\x8E", "\xD1\x9F" => "\xD0\x8F", "\xD2\x91" => "\xD2\x90",
			"\xE1\xB8\x83" => "\xE1\xB8\x82", "\xE1\xB8\x8B" => "\xE1\xB8\x8A", "\xE1\xB8\x9F" => "\xE1\xB8\x9E", "\xE1\xB9\x81" => "\xE1\xB9\x80",
			"\xE1\xB9\x97" => "\xE1\xB9\x96", "\xE1\xB9\xA1" => "\xE1\xB9\xA0", "\xE1\xB9\xAB" => "\xE1\xB9\xAA", "\xE1\xBA\x81" => "\xE1\xBA\x80",
			"\xE1\xBA\x83" => "\xE1\xBA\x82", "\xE1\xBA\x85" => "\xE1\xBA\x84", "\xE1\xBB\xB3" => "\xE1\xBB\xB2"
		);

		return strtr(strtoupper($string), $UTF8_LOWER_TO_UPPER);
	}

	/**
	* UTF-8 aware alternative to substr
	* Return part of a string given character offset (and optionally length)
	*
	* Note arguments: comparied to substr - if offset or length are
	* not integers, this version will not complain but rather massages them
	* into an integer.
	*
	* Note on returned values: substr documentation states false can be
	* returned in some cases (e.g. offset > string length)
	* mb_substr never returns false, it will return an empty string instead.
	* This adopts the mb_substr approach
	*
	* Note on implementation: PCRE only supports repetitions of less than
	* 65536, in order to accept up to MAXINT values for offset and length,
	* we'll repeat a group of 65535 characters when needed.
	*
	* Note on implementation: calculating the number of characters in the
	* string is a relatively expensive operation, so we only carry it out when
	* necessary. It isn't necessary for +ve offsets and no specified length
	*
	* @author Chris Smith<chris@jalakai.co.uk>
	* @param string $str
	* @param integer $offset number of UTF-8 characters offset (from left)
	* @param integer $length (optional) length in UTF-8 characters from offset
	* @return mixed string or FALSE if failure
	*/
	function utf8_substr($str, $offset, $length = NULL)
	{
		// generates E_NOTICE
		// for PHP4 objects, but not PHP5 objects
		$str = (string) $str;
		$offset = (int) $offset;
		if (!is_null($length))
		{
			$length = (int) $length;
		}

		// handle trivial cases
		if ($length === 0 || ($offset < 0 && $length < 0 && $length < $offset))
		{
			return '';
		}

		// normalise negative offsets (we could use a tail
		// anchored pattern, but they are horribly slow!)
		if ($offset < 0)
		{
			// see notes
			$strlen = utf8_strlen($str);
			$offset = $strlen + $offset;
			if ($offset < 0)
			{
				$offset = 0;
			}
		}

		$op = '';
		$lp = '';

		// establish a pattern for offset, a
		// non-captured group equal in length to offset
		if ($offset > 0)
		{
			$ox = (int) ($offset / 65535);
			$oy = $offset % 65535;

			if ($ox)
			{
				$op = '(?:.{65535}){' . $ox . '}';
			}

			$op = '^(?:' . $op . '.{' . $oy . '})';
		}
		else
		{	
			// offset == 0; just anchor the pattern
			$op = '^';
		}

		// establish a pattern for length
		if (is_null($length))
		{
			// the rest of the string
			$lp = '(.*)$';
		}
		else
		{
			if (!isset($strlen))
			{
				// see notes
				$strlen = utf8_strlen($str);
			}

			// another trivial case
			if ($offset > $strlen)
			{
				return '';
			}

			if ($length > 0)
			{
				// reduce any length that would
				// go passed the end of the string
				$length = min($strlen - $offset, $length);

				$lx = (int) ($length / 65535);
				$ly = $length % 65535;
				
				// negative length requires a captured group
				// of length characters
				if ($lx)
				{
					$lp = '(?:.{65535}){' . $lx . '}';
				}
				$lp = '(' . $lp . '.{'. $ly . '})';
			}
			else if ($length < 0)
			{
				if ($length < ($offset - $strlen))
				{
					return '';
				}

				$lx = (int)((-$length) / 65535);
				$ly = (-$length) % 65535;

				// negative length requires ... capture everything
				// except a group of  -length characters
				// anchored at the tail-end of the string
				if ($lx)
				{
					$lp = '(?:.{65535}){' . $lx . '}';
				}
				$lp = '(.*)(?:' . $lp . '.{' . $ly . '})$';
			}
		}

		if (!preg_match('#' . $op . $lp . '#us', $str, $match))
		{
			return '';
		}

		return $match[1];
	}

	/**
	* Return the length (in characters) of a UTF-8 string
	*
	* @param	string	$text		UTF-8 string
	* @return	integer				Length (in chars) of given string
	*/
	function utf8_strlen($text)
	{
		// Since utf8_decode is replacing multibyte characters to ? strlen works fine
		return strlen(utf8_decode($text));
	}
}

/**
* UTF-8 aware alternative to str_split
* Convert a string to an array
* 
* @author Harry Fuecks
* @param string $str UTF-8 encoded
* @param int $split_len number to characters to split string by
* @return string characters in string reverses
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
		$string = strrev($string);
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
	if (!preg_match('#^[a-z0-9\\-]+$#', $encoding))
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
	//trigger_error('Unknown encoding: ' . $encoding, E_USER_ERROR);
	return $string;
}

/**
* Replace all UTF-8 chars that are not in ASCII with their NCR
*
* @param	string	$text		UTF-8 string in NFC
* @return	string				ASCII string using NCRs for non-ASCII chars
*/
function utf8_encode_ncr($text)
{
	return preg_replace_callback('#[\\xC2-\\xF4][\\x80-\\xBF]{1,3}#', 'utf8_encode_ncr_callback', $text);
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
* Takes an array of ints representing the Unicode characters and returns
* a UTF-8 string.
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

	$text = strtr($text, $uniarray['c']);
	if ($option === 'full')
	{
		$text = strtr($text, $uniarray['f']);
	}
	else
	{
		$text = strtr($text, $uniarray['s']);
	}
	return $text;
}

/**
* A wrapper function for the normalizer which takes care of including the class if required and modifies the passed strings
* to be in NFC (Normalization Form Composition).
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

	if (!class_exists('utf_normalizer'))
	{
		global $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'includes/utf/utf_normalizer.' . $phpEx);
	}

	if (!is_array($strings))
	{
		utf_normalizer::nfc($strings);
	}
	else if (is_array($strings))
	{
		foreach ($strings as $key => $string)
		{
			utf_normalizer::nfc($strings[$key]);
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
	$text = utf8_case_fold($text);
	
	if (!class_exists('utf_normalizer'))
	{
		global $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'includes/utf/utf_normalizer.' . $phpEx);
	}

	utf_normalizer::nfc($text);

	static $homographs = array(
		"\xC2\xA1"			=>	"\x69",				// EXCLAMATION MARK, INVERTED => LATIN SMALL LETTER I
		"\xC2\xAD"			=>	'',					// HYPHEN, SOFT => empty string
		"\xC4\x90"			=>	"\xC3\x90",			// LATIN CAPITAL LETTER D WITH STROKE => LATIN CAPITAL LETTER ETH
		"\xC7\x83"			=>	"\x21",				// LATIN LETTER RETROFLEX CLICK => EXCLAMATION MARK
		"\xC9\x85"			=>	"\xCE\x9B",			// LATIN CAPITAL LETTER TURNED V => GREEK CAPITAL LETTER LAMDA
		"\xC9\x99"			=>	"\xC7\x9D",			// LATIN SMALL LETTER SCHWA => LATIN SMALL LETTER TURNED E
		"\xCA\x99"			=>	"\xD0\xB2",			// LATIN LETTER SMALL CAPITAL B => CYRILLIC SMALL LETTER VE
		"\xCA\x9C"			=>	"\xD0\xBD",			// LATIN LETTER SMALL CAPITAL H => CYRILLIC SMALL LETTER EN
		"\xCE\x91"			=>	"\x41",				// GREEK CAPITAL LETTER ALPHA => LATIN CAPITAL LETTER A
		"\xCE\x92"			=>	"\x42",				// GREEK CAPITAL LETTER BETA => LATIN CAPITAL LETTER B
		"\xCE\x95"			=>	"\x45",				// GREEK CAPITAL LETTER EPSILON => LATIN CAPITAL LETTER E
		"\xCE\x96"			=>	"\x5A",				// GREEK CAPITAL LETTER ZETA => LATIN CAPITAL LETTER Z
		"\xCE\x97"			=>	"\x48",				// GREEK CAPITAL LETTER ETA => LATIN CAPITAL LETTER H
		"\xCE\x99"			=>	"\x49",				// GREEK CAPITAL LETTER IOTA => LATIN CAPITAL LETTER I
		"\xCE\x9A"			=>	"\x4B",				// GREEK CAPITAL LETTER KAPPA => LATIN CAPITAL LETTER K
		"\xCE\x9C"			=>	"\x4D",				// GREEK CAPITAL LETTER MU => LATIN CAPITAL LETTER M
		"\xCE\x9D"			=>	"\x4E",				// GREEK CAPITAL LETTER NU => LATIN CAPITAL LETTER N
		"\xCE\x9F"			=>	"\x4F",				// GREEK CAPITAL LETTER OMICRON => LATIN CAPITAL LETTER O
		"\xCE\xA1"			=>	"\x50",				// GREEK CAPITAL LETTER RHO => LATIN CAPITAL LETTER P
		"\xCE\xA3"			=>	"\xC6\xA9",			// GREEK CAPITAL LETTER SIGMA => LATIN CAPITAL LETTER ESH
		"\xCE\xA4"			=>	"\x54",				// GREEK CAPITAL LETTER TAU => LATIN CAPITAL LETTER T
		"\xCE\xA5"			=>	"\x59",				// GREEK CAPITAL LETTER UPSILON => LATIN CAPITAL LETTER Y
		"\xCE\xA7"			=>	"\x58",				// GREEK CAPITAL LETTER CHI => LATIN CAPITAL LETTER X
		"\xCE\xB1"			=>	"\x61",				// GREEK SMALL LETTER ALPHA => LATIN SMALL LETTER A
		"\xCE\xB5"			=>	"\xC9\x9B",			// GREEK SMALL LETTER EPSILON => LATIN SMALL LETTER OPEN E
		"\xCE\xB9"			=>	"\xC9\xA9",			// GREEK SMALL LETTER IOTA => LATIN SMALL LETTER IOTA
		"\xCE\xBF"			=>	"\x6F",				// GREEK SMALL LETTER OMICRON => LATIN SMALL LETTER O
		"\xCF\xB3"			=>	"\x6A",				// GREEK LETTER YOT => LATIN SMALL LETTER J
		"\xD0\x85"			=>	"\x53",				// CYRILLIC CAPITAL LETTER DZE => LATIN CAPITAL LETTER S
		"\xD0\x88"			=>	"\x4A",				// CYRILLIC CAPITAL LETTER JE => LATIN CAPITAL LETTER J
		"\xD0\x91"			=>	"\xC6\x82",			// CYRILLIC CAPITAL LETTER BE => LATIN CAPITAL LETTER B WITH TOPBAR
		"\xD0\x93"			=>	"\xCE\x93",			// CYRILLIC CAPITAL LETTER GHE => GREEK CAPITAL LETTER GAMMA
		"\xD0\x9F"			=>	"\xCE\xA0",			// CYRILLIC CAPITAL LETTER PE => GREEK CAPITAL LETTER PI
		"\xD0\xA1"			=>	"\x43",				// CYRILLIC CAPITAL LETTER ES => LATIN CAPITAL LETTER C
		"\xD0\xB0"			=>	"\x61",				// CYRILLIC SMALL LETTER A => LATIN SMALL LETTER A
		"\xD0\xB5"			=>	"\x65",				// CYRILLIC SMALL LETTER IE => LATIN SMALL LETTER E
		"\xD0\xBA"			=>	"\xC4\xB8",			// CYRILLIC SMALL LETTER KA => LATIN SMALL LETTER KRA
		"\xD0\xBE"			=>	"\x6F",				// CYRILLIC SMALL LETTER O => LATIN SMALL LETTER O
		"\xD1\x80"			=>	"\x70",				// CYRILLIC SMALL LETTER ER => LATIN SMALL LETTER P
		"\xD1\x81"			=>	"\x63",				// CYRILLIC SMALL LETTER ES => LATIN SMALL LETTER C
		"\xD1\x83"			=>	"\x79",				// CYRILLIC SMALL LETTER U => LATIN SMALL LETTER Y
		"\xD1\x85"			=>	"\x78",				// CYRILLIC SMALL LETTER HA => LATIN SMALL LETTER X
		"\xD1\x95"			=>	"\x73",				// CYRILLIC SMALL LETTER DZE => LATIN SMALL LETTER S
		"\xD1\x96"			=>	"\x69",				// CYRILLIC SMALL LETTER BYELORUSSIAN-UKRAINIAN I => LATIN SMALL LETTER I
		"\xD1\x98"			=>	"\x6A",				// CYRILLIC SMALL LETTER JE => LATIN SMALL LETTER J
		"\xD2\xBB"			=>	"\x68",				// CYRILLIC SMALL LETTER SHHA => LATIN SMALL LETTER H
		"\xD3\x8F"			=>	"\xC9\xAA",			// CYRILLIC SMALL LETTER PALOCHKA => LATIN LETTER SMALL CAPITAL I
		"\xD3\x94"			=>	"\xC3\x86",			// CYRILLIC CAPITAL LIGATURE A IE => LATIN CAPITAL LETTER AE
		"\xD3\x95"			=>	"\xC3\xA6",			// CYRILLIC SMALL LIGATURE A IE => LATIN SMALL LETTER AE
		"\xD3\x98"			=>	"\xC6\x8E",			// CYRILLIC CAPITAL LETTER SCHWA => LATIN CAPITAL LETTER REVERSED E
		"\xD3\x99"			=>	"\xC7\x9D",			// CYRILLIC SMALL LETTER SCHWA => LATIN SMALL LETTER TURNED E
		"\xD3\xA1"			=>	"\xCA\x92",			// CYRILLIC SMALL LETTER ABKHASIAN DZE => LATIN SMALL LETTER EZH
		"\xD3\xA8"			=>	"\xC6\x9F",			// CYRILLIC CAPITAL LETTER BARRED O => LATIN CAPITAL LETTER O WITH MIDDLE TILDE
		"\xD3\xA9"			=>	"\xC9\xB5",			// CYRILLIC SMALL LETTER BARRED O => LATIN SMALL LETTER BARRED O
		"\xD4\x81"			=>	"\x64",				// CYRILLIC SMALL LETTER KOMI DE => LATIN SMALL LETTER D
		"\xE1\x81\x80"		=>	"\xE1\x80\x9D",		// MYANMAR DIGIT ZERO => MYANMAR LETTER WA
		"\xE1\x9E\xA3"		=>	"\xE1\x9E\xA2",		// KHMER INDEPENDENT VOWEL QAQ => KHMER LETTER QA
		"\xE1\xA1\x95"		=>	"\xE1\xA0\xB5",		// MONGOLIAN LETTER TODO YA => MONGOLIAN LETTER JA
		"\xE1\xA7\x90"		=>	"\xE1\xA6\x9E",		// NEW TAI LUE DIGIT ZERO => NEW TAI LUE LETTER LOW VA
		"\xE1\xAD\x92"		=>	"\xE1\xAC\x8D",		// BALINESE DIGIT TWO => BALINESE LETTER LA LENGA
		"\xE1\xAD\x93"		=>	"\xE1\xAC\x91",		// BALINESE DIGIT THREE => BALINESE LETTER OKARA
		"\xE1\xAD\x98"		=>	"\xE1\xAC\xA8",		// BALINESE DIGIT EIGHT => BALINESE LETTER PA KAPAL
		"\xE1\xAD\x9C"		=>	"\xE1\xAD\x90",		// BALINESE WINDU => BALINESE DIGIT ZERO
		"\xE1\xB4\x8D"		=>	"\xD0\xBC",			// LATIN LETTER SMALL CAPITAL M => CYRILLIC SMALL LETTER EM
		"\xE1\xB4\x9B"		=>	"\xD1\x82",			// LATIN LETTER SMALL CAPITAL T => CYRILLIC SMALL LETTER TE
		"\xE1\xB4\xA6"		=>	"\xD0\xB3",			// GREEK LETTER SMALL CAPITAL GAMMA => CYRILLIC SMALL LETTER GHE
		"\xE1\xB4\xA8"		=>	"\xD0\xBF",			// GREEK LETTER SMALL CAPITAL PI => CYRILLIC SMALL LETTER PE
		"\xE1\xB4\xA9"		=>	"\xE1\xB4\x98",		// GREEK LETTER SMALL CAPITAL RHO => LATIN LETTER SMALL CAPITAL P
		"\xE1\xB4\xAB"		=>	"\xD0\xBB",			// CYRILLIC LETTER SMALL CAPITAL EL => CYRILLIC SMALL LETTER EL
		"\xE2\x8D\xB3"		=>	"\xC9\xA9",			// APL FUNCTIONAL SYMBOL IOTA => LATIN SMALL LETTER IOTA
		"\xE2\x8D\xB4"		=>	"\xCF\x81",			// APL FUNCTIONAL SYMBOL RHO => GREEK SMALL LETTER RHO
		"\xE2\x8D\xB5"		=>	"\xCF\x89",			// APL FUNCcTIONAL SYMBOL OMEGA => GREEK SMALL LETTER OMEGA
		"\xE2\x8D\xBA"		=>	"\xCE\xB1",			// APL FUNCTIONAL SYMBOL ALPHA => GREEK SMALL LETTER ALPHA
		"\xE2\xB1\xA7"		=>	"\xD2\xA2",			// LATIN CAPITAL LETTER H WITH DESCENDER => CYRILLIC CAPITAL LETTER EN WITH DESCENDER
		"\xE2\xB1\xA9"		=>	"\xD2\x9A",			// LATIN CAPITAL LETTER K WITH DESCENDER => CYRILLIC CAPITAL LETTER KA WITH DESCENDER
		"\xF0\x90\x8F\x91"	=>	"\xF0\x90\x8E\x82",	// OLD PERSIAN NUMBER ONE => UGARITIC LETTER GAMLA
		"\xF0\x90\x8F\x93"	=>	"\xF0\x90\x8E\x93",	// OLD PERSIAN NUMBER TEN => UGARITIC LETTER AIN
		"\xF0\x90\x92\xA0"	=>	"\xF0\x90\x92\x86",	// OSMANYA DIGIT ZERO => OSMANYA LETTER DEEL
		"\xF0\x92\x80\xB8"	=>	"\xF0\x90\x8E\x9A",	// CUNEIFORM SIGN ASH => UGARITIC LETTER TO

		"\xC2\xA0"			=>	"\x20",				// NO-BREAK SPACE
		"\xE1\x9A\x80"		=>	"\x20",				// OGHAM SPACE MARK
		"\xE2\x80\x80"		=>	"\x20",				// EN QUAD
		"\xE2\x80\x81"		=>	"\x20",				// EM QUAD
		"\xE2\x80\x82"		=>	"\x20",				// EN SPACE
		"\xE2\x80\x83"		=>	"\x20",				// EM SPACE
		"\xE2\x80\x84"		=>	"\x20",				// THREE-PER-EM SPACE
		"\xE2\x80\x85"		=>	"\x20",				// FOUR-PER-EM SPACE
		"\xE2\x80\x86"		=>	"\x20",				// SIX-PER-EM SPACE
		"\xE2\x80\x87"		=>	"\x20",				// FIGURE SPACE
		"\xE2\x80\x88"		=>	"\x20",				// PUNCTUATION SPACE
		"\xE2\x80\x89"		=>	"\x20",				// THIN SPACE
		"\xE2\x80\x8A"		=>	"\x20",				// HAIR SPACE
		"\xE2\x80\xAF"		=>	"\x20",				// NARROW NO-BREAK SPACE
		"\xE2\x81\x9F"		=>	"\x20",				// MEDIUM MATHEMATICAL SPACE
		"\xE3\x80\x80"		=>	"\x20",				// IDEOGRAPHIC SPACE

		"\xDB\x9D"			=>	'',					// ARABIC END OF AYAH
		"\xDC\x8F"			=>	'',					// SYRIAC ABBREVIATION MARK
		"\xE1\xA0\x86"		=>	'',					// MONGOLIAN TODO SOFT HYPHEN
		"\xE1\xA0\x8E"		=>	'',					// MONGOLIAN VOWEL SEPARATOR
		"\xE2\x80\x8B"		=>	'',					// ZERO WIDTH SPACE
		"\xE2\x80\x8C"		=>	'',					// ZERO WIDTH NON-JOINER
		"\xE2\x80\x8D"		=>	'',					// ZERO WIDTH JOINER
		"\xE2\x80\xA8"		=>	'',					// LINE SEPARATOR
		"\xE2\x80\xA9"		=>	'',					// PARAGRAPH SEPARATOR
		"\xE2\x81\xA0"		=>	'',					// WORD JOINER
		"\xE2\x81\xA1"		=>	'',					// FUNCTION APPLICATION
		"\xE2\x81\xA2"		=>	'',					// INVISIBLE TIMES
		"\xE2\x81\xA3"		=>	'',					// INVISIBLE SEPARATOR
		"\xE2\x81\xAA"		=>	'',					// [CONTROL CHARACTERS]
		"\xE2\x81\xAB"		=>	'',					// [CONTROL CHARACTERS]
		"\xE2\x81\xAC"		=>	'',					// [CONTROL CHARACTERS]
		"\xE2\x81\xAD"		=>	'',					// [CONTROL CHARACTERS]
		"\xE2\x81\xAE"		=>	'',					// [CONTROL CHARACTERS]
		"\xE2\x81\xAF"		=>	'',					// [CONTROL CHARACTERS]
		"\xEF\xBB\xBF"		=>	'',					// ZERO WIDTH NO-BREAK SPACE
		"\xEF\xBF\xB9"		=>	'',					// [CONTROL CHARACTERS]
		"\xEF\xBF\xBA"		=>	'',					// [CONTROL CHARACTERS]
		"\xEF\xBF\xBB"		=>	'',					// [CONTROL CHARACTERS]
		"\xEF\xBF\xBC"		=>	'',					// [CONTROL CHARACTERS]
		"\xF0\x9D\x85\xB3"	=>	'',					// [MUSICAL CONTROL CHARACTERS]
		"\xF0\x9D\x85\xB4"	=>	'',					// [MUSICAL CONTROL CHARACTERS]
		"\xF0\x9D\x85\xB5"	=>	'',					// [MUSICAL CONTROL CHARACTERS]
		"\xF0\x9D\x85\xB6"	=>	'',					// [MUSICAL CONTROL CHARACTERS]
		"\xF0\x9D\x85\xB7"	=>	'',					// [MUSICAL CONTROL CHARACTERS]
		"\xF0\x9D\x85\xB8"	=>	'',					// [MUSICAL CONTROL CHARACTERS]
		"\xF0\x9D\x85\xB9"	=>	'',					// [MUSICAL CONTROL CHARACTERS]
		"\xF0\x9D\x85\xBA"	=>	'',					// [MUSICAL CONTROL CHARACTERS]
	);

	$text = strtr($text, $homographs);

	// Other control characters
	$text = preg_replace('#(?:[\x00-\x1F\x7F]+|(?:\xC2[\x80-\x9F])+)#', '', $text);

	return $text;
}

/**
* A wrapper for htmlspecialchars($value, ENT_COMPAT, 'UTF-8')
*/
function utf8_htmlspecialchars(&$value)
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

?>