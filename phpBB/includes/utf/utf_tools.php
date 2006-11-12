<?php
/** 
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2006 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
* @todo make sure the replacements are called correctly
* already done: strtolower, strtoupper, ucfirst, str_split, strrpos, strlen (hopefully!), strpos, substr
* remaining:	clean_username, htmlentities (no longer needed for internal data?), htmlspecialchars (using charset)
*				strspn, chr, ord
*/

/**
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

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
	* @param string $string UTF-8 encoded data
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
	/**
	* UTF-8 aware alternative to strrpos
	* Find position of last occurrence of a char in a string
	*
	* Notes:
	* - offset for mb_strrpos was added in 5.2.0, we emulate if it is lower
	*/
	if (version_compare(phpversion(), '5.2.0', '>='))
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

			return mb_strrpos($str, $search);
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
			if ($offset === false)
			{
				// Emulate behaviour of strrpos rather than raising warning
				if (empty($str))
				{
					return false;
				}

				return mb_strrpos($str, $search);
			}
			else
			{
				if (!is_int($offset))
				{
					trigger_error('utf8_strrpos expects parameter 3 to be long', E_USER_WARNING);
					return false;
				}

				$str = mb_substr($str, $offset);

				if (false !== ($pos = mb_strrpos($str, $search)))
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
		if ($offset === false)
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
	function utf8_substr($str, $offset,	$length	= null)
	{
		if ($length === false)
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
	* @param string haystack
	* @param string needle
	* @param integer (optional) offset (from left)
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
				trigger_error('utf8_strrpos	expects	parameter 3	to be long', E_USER_WARNING);
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
	* @param string haystack
	* @param string needle
	* @param integer offset in characters (from left)
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

	$UTF8_UPPER_TO_LOWER = array(
		"\x41" => "\x61", "\x42" => "\x62", "\x43" => "\x63", "\x44" => "\x64",
		"\x45" => "\x65", "\x46" => "\x66", "\x47" => "\x67", "\x48" => "\x68",
		"\x49" => "\x69", "\x4A" => "\x6A", "\x4B" => "\x6B", "\x4C" => "\x6C",
		"\x4D" => "\x6D", "\x4E" => "\x6E", "\x4F" => "\x6F", "\x50" => "\x70",
		"\x51" => "\x71", "\x52" => "\x72", "\x53" => "\x73", "\x54" => "\x74",
		"\x55" => "\x75", "\x56" => "\x76", "\x57" => "\x77", "\x58" => "\x78",
		"\x59" => "\x79", "\x5A" => "\x7A", "\xC3\x80" => "\xC3\xA0", "\xC3\x81" => "\xC3\xA1",
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

	$UTF8_LOWER_TO_UPPER = array(
		"\x61" => "\x41", "\x62" => "\x42", "\x63" => "\x43", "\x64" => "\x44",
		"\x65" => "\x45", "\x66" => "\x46", "\x67" => "\x47", "\x68" => "\x48",
		"\x69" => "\x49", "\x6A" => "\x4A", "\x6B" => "\x4B", "\x6C" => "\x4C",
		"\x6D" => "\x4D", "\x6E" => "\x4E", "\x6F" => "\x4F", "\x70" => "\x50",
		"\x71" => "\x51", "\x72" => "\x52", "\x73" => "\x53", "\x74" => "\x54",
		"\x75" => "\x55", "\x76" => "\x56", "\x77" => "\x57", "\x78" => "\x58",
		"\x79" => "\x59", "\x7A" => "\x5A", "\xC3\xA0" => "\xC3\x80", "\xC3\xA1" => "\xC3\x81",
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
		global $UTF8_UPPER_TO_LOWER;

		return strtr($string, $UTF8_UPPER_TO_LOWER);
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
		global $UTF8_LOWER_TO_UPPER;

		return strtr($string, $UTF8_LOWER_TO_UPPER);
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
	* @param string
	* @param integer number of UTF-8 characters offset (from left)
	* @param integer (optional) length in UTF-8 characters from offset
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
* @param string UTF-8 encoded
* @param int number to characters to split string by
* @return string characters in string reverses
*/
function utf8_str_split($str, $split_len = 1)
{
	if (!preg_match('/^[0-9]+$/', $split_len) || $split_len < 1)
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
* UTF-8 aware alternative to strcspn
* Find length of initial segment not matching mask
* 
* @author Harry Fuecks
* @param string
* @return int
*/
function utf8_strspn($str, $mask, $start = null, $length = null)
{
	$mask = preg_replace('!([\\\\\\-\\]\\[/^])!', '\\\${1}', $mask);

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

	if ($encoding == 'utf-8' || !is_string($string) || !isset($string[0]))
	{
		return $string;
	}

	// start with something simple
	if ($encoding == 'iso-8859-1')
	{
		return utf8_encode($string);
	}

	// First, try iconv()
	if (function_exists('iconv'))
	{
		$ret = @iconv($encoding, 'utf-8', $string);

		if (isset($ret[0]))
		{
			return $ret;
		}
	}

	// Try the mb_string extension
	if (function_exists('mb_convert_encoding'))
	{
		$ret = @mb_convert_encoding($string, 'utf-8', $encoding);

		if (isset($ret[0]))
		{
			return $ret;
		}
	}

	// Try the recode extension
	if (function_exists('recode_string'))
	{
		$ret = @recode_string($encoding . '..utf-8', $string);

		if (isset($ret[0]))
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

	global $phpbb_root_path;

	if (!file_exists($phpbb_root_path . 'includes/utf/data/'))
	{
		return $string;
	}

	die('Finish me!! ' . basename(__FILE__) . ' at line ' . __LINE__);
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
* Enter description here...
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
* @param integer $cp UNICODE code point
* @return string UTF-8 char
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

	return utf8_chr($cp);
}

/**
* Takes an array of ints representing the Unicode characters and returns
* a UTF-8 string.
*
* @param string $text text to be case folded
* @param string $option determines how we will fold the cases
* @return string case folded text
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
* @param	mixed	$strings Either an array of references to strings, a reference to an array of strings or a reference to a single string
*/
function utf8_normalize_nfc($strings)
{
	if (!is_array($strings) || (sizeof($strings) > 0))
    {	
		if (!class_exists('utf_normalizer'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/utf/utf_normalizer.' . $phpEx);
		}

		if (is_array($strings))
		{
			foreach ($strings as $key => $string)
			{
				$strings[$key] = utf_normalizer::nfc($strings[$key]);
			}
		}
		else
		{
			$strings = utf_normalizer::nfc($strings);
		}
	}
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
* @param	$text	An unclean string, mabye user input (has to be valid UTF-8!)
* @return			Cleaned up version of the input string
*/
function utf8_clean_string($text)
{
	$text = utf8_case_fold($text);
	
	if (!class_exists('utf_normalizer'))
	{
		global $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'includes/utf/utf_normalizer.' . $phpEx);
	}

	$text = utf_normalizer::nfc($text);

	static $homographs = array(
		// cyrllic
		"\xD0\xB0" => "\x61",
		"\xD0\xB5" => "\x65",
		"\xD0\xBE" => "\x6F",
		"\xD1\x80" => "\x70",
		"\xD1\x81" => "\x63",
		"\xD1\x83" => "\x79",
		"\xD1\x85" => "\x78",
		"\xD1\x95" => "\x73",
		"\xD1\x96" => "\x69",
		"\xD1\x98" => "\x6A",
		"\xD2\xBB" => "\x68",
		// greek
		"\xCE\xB1" => "\x61",
		"\xCE\xBF" => "\x6F",
		// other
		"\xC2\xA1" => "\x69",
	);

	$text = strtr($text, $homographs);

	return $text;
}

?>